<?php

namespace App\Services;

use Carbon\Carbon;

class NlpSearchParserService
{
    private array $dictionary;
    private array $synonyms;
    private array $keywordMap;

    public function __construct()
    {
        $dict = SearchDictionaryService::get();
        $this->synonyms = $dict['synonyms'];

        // --- Build a reverse-lookup map for keywords for faster processing ---
        $this->keywordMap = [];
        $keywordCategories = [
            'property_type' => $dict['property_types'],
            'sell_type'     => $dict['sell_types'],
            'is_furnished'  => ['furnished'],
            'amenities'     => $dict['amenities'],
        ];
        foreach ($keywordCategories as $type => $keywords) {
            foreach ($keywords as $keyword) {
                $this->keywordMap[$keyword] = $type;
            }
        }

        // --- Flatten the full dictionary for typo correction ---
        $this->dictionary = array_merge(
            $dict['attributes'], $dict['keywords'], $dict['locations'],
            $dict['property_types'], $dict['sell_types'], $dict['amenities'], $dict['sorting']
        );
    }

    public function parse(string $originalQuery): array
    {
        $query = $this->_normalizeQuery($originalQuery);

        $filters = [];
        $negations = [];
        $sorting = [];

        list($query, $sorting) = $this->_extractSorting($query);
        list($query, $negations) = $this->_extractNegations($query);
        list($query, $filters) = $this->_extractRanges($query, $filters);
        list($query, $filters) = $this->_extractEntities($query, $filters);
        list($query, $filters) = $this->_extractKeywords($query, $filters);

        $textQuery = trim(preg_replace('/\s+/', ' ', $query));

        if (empty($textQuery) && (!empty($filters) || !empty($sorting) || !empty($negations))) {
            $textQuery = '*';
        }

        return [
            'query'     => $textQuery,
            'filters'   => $filters,
            'negations' => $negations,
            'sorting'   => $sorting,
        ];
    }
    
    // --- Private Helper Methods for Parsing ---

    private function _normalizeQuery(string $query): string
    {
        $query = strtolower($query);
        foreach ($this->synonyms as $syn => $correct) {
            if (str_contains($syn, ' ')) {
                $query = str_replace($syn, $correct, $query);
            }
        }
        $words = explode(' ', $query);
        $correctedWords = [];
        foreach ($words as $word) {
            $word = $this->synonyms[$word] ?? $word;
            if (in_array($word, $this->dictionary) || is_numeric($word)) {
                $correctedWords[] = $word;
                continue;
            }
            $closestMatch = '';
            $shortestDistance = -1;
            foreach ($this->dictionary as $dictWord) {
                $distance = levenshtein($word, $dictWord);
                if ($distance === 0) { $closestMatch = $dictWord; break; }
                if ($distance <= 2 && ($shortestDistance < 0 || $distance < $shortestDistance)) {
                    $closestMatch = $dictWord;
                    $shortestDistance = $distance;
                }
            }
            $correctedWords[] = !empty($closestMatch) ? $closestMatch : $word;
        }
        return implode(' ', $correctedWords);
    }
    
    private function _extractSorting(string $query): array
    {
        $sorting = [];
        $dict = SearchDictionaryService::get();
        foreach ($dict['sorting'] as $term) {
            if (str_contains($query, ' ' . $term . ' ')) {
                $attribute = $this->synonyms[$term];
                $direction = in_array($term, ['cheapest', 'newest', 'smallest']) ? 'asc' : 'desc';
                $sorting = ['attribute' => $attribute, 'direction' => $direction];
                $query = str_replace($term, '', $query);
                break;
            }
        }
        return [$query, $sorting];
    }

    private function _extractNegations(string $query): array
    {
        $negations = [];
        if (preg_match_all('/(?:without|no)\s+([\p{L}\s,]+(?:and\s+[\p{L}\s,]+)*)/iu', $query, $matches)) {
            foreach($matches[1] as $i => $match) {
                $negatedItems = preg_split('/(?:,|and)\s+/', $match, -1, PREG_SPLIT_NO_EMPTY);
                $negations = array_merge($negations, array_map('trim', $negatedItems));
                $query = str_replace($matches[0][$i], '', $query);
            }
        }
        return [$query, $negations];
    }
    
    private function _extractRanges(string $query, array $filters): array
    {
        if (preg_match('/(\d+)\s*-\s*(\d+)\s*(bedrooms|bathrooms)/i', $query, $matches)) {
            $filters['min_' . $matches[3]] = $matches[1];
            $filters['max_' . $matches[3]] = $matches[2];
            $query = str_replace($matches[0], '', $query);
        }
        if (preg_match('/price\s+between\s+(\d+)\s+and\s+(\d+)/i', $query, $matches)) {
            $filters['min_price'] = $matches[1];
            $filters['max_price'] = $matches[2];
            $query = str_replace($matches[0], '', $query);
        }
        return [$query, $filters];
    }
    
    private function _extractEntities(string $query, array $filters): array
    {
        $patterns = [
            'area'      => '/(\d+)\s*(?:sqm|sq\s*m)/i',
            'bedrooms'  => '/(\d+)\s+bedrooms?/i',
            'bathrooms' => '/(\d+)\s+bathrooms?/i',
            'city'      => '/in\s+([\p{L}\s\'-]+)/iu',
        ];
        foreach ($patterns as $key => $pattern) {
             if (preg_match($pattern, $query, $matches)) {
                if(!isset($filters[$key]) && !isset($filters['min_'.$key])) {
                    $filters[$key] = trim($matches[1]);
                }
                $query = str_replace($matches[0], '', $query);
            }
        }
        return [$query, $filters];
    }

 private function _extractKeywords(string $query, array $filters): array
    {
        $words = explode(' ', $query);
        $remainingWords = [];

        // Add amenities found with the "with" keyword
        if (preg_match('/with\s+((?:[\p{L}\s]+,?\s*(?:and)?\s*)+)/iu', $query, $matches)) {
            $features = preg_split('/(?:,|and)\s+/', $matches[1], -1, PREG_SPLIT_NO_EMPTY);
            if (!isset($filters['amenities'])) {
                $filters['amenities'] = [];
            }
            $filters['amenities'] = array_merge($filters['amenities'], array_map('trim', $features));
            $query = str_replace($matches[0], '', $query);
            $words = explode(' ', $query); // Re-split words after removing the 'with' clause
        }

        foreach ($words as $word) {
            $word = trim($word);
            if (empty($word)) continue;

            // Check if the word is a known keyword
            if (isset($this->keywordMap[$word])) {
                $type = $this->keywordMap[$word];
                if ($type === 'is_furnished') {
                    $filters['is_furnished'] = true;
                } elseif ($type === 'amenities') {
                    if (!isset($filters['amenities'])) $filters['amenities'] = [];
                    $filters['amenities'][] = $word;
                } else {
                    $filterValue = $this->synonyms[$word] ?? $word;
                    $filters[$type] = ucfirst($filterValue);
                }
            } else {
                $remainingWords[] = $word;
            }
        }

        return [implode(' ', $remainingWords), $filters];
    }
}