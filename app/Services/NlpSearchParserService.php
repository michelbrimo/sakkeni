<?php

namespace App\Services;

use App\Services\SearchDictionaryService;

class NlpSearchParserService
{
    private array $dictionary;
    private array $synonyms;
    private array $keywordMap;

    public function __construct()
    {
        $dict = SearchDictionaryService::get();
        $this->synonyms = $dict['synonyms'];

        $this->keywordMap = [];
        $keywordCategories = [
            'residential_type' => $dict['residential_types'],
            'commercial_type'  => $dict['commercial_types'],
            'sell_type'     => $dict['sell_types'],
            'is_furnished'  => ['furnished'],
            'amenities'     => $dict['amenities'],
        ];
        foreach ($keywordCategories as $type => $keywords) {
            foreach ($keywords as $keyword) {
                $this->keywordMap[strtolower($keyword)] = $type;
            }
        }

        $this->dictionary = array_merge(
            array_keys($this->keywordMap),
            $dict['attributes'],
            $dict['keywords'],
            $dict['locations'],
            $dict['sorting'],
            $dict['property_types'] 

        );
    }

    public function parse(string $originalQuery): array
    {
        $query = $this->_normalizeQuery($originalQuery);

        $filters = [];
        $negations = [];
        $sorting = [];

        list($query, $sorting)   = $this->_extractSorting($query);
        list($query, $negations) = $this->_extractNegations($query);
        list($query, $filters)   = $this->_extractRanges($query, $filters);
        list($query, $filters)   = $this->_extractLimits($query, $filters); 
        list($query, $filters)   = $this->_extractEntities($query, $filters);
        list($query, $filters)   = $this->_extractKeywords($query, $filters);

        $stopwords = SearchDictionaryService::get()['stopwords'];
        $queryWords = explode(' ', $query);
        $filteredWords = array_diff($queryWords, $stopwords);
        $query = implode(' ', $filteredWords);
        $textQuery = trim(preg_replace('/\s+/', ' ', $query));

        if (empty($textQuery) && (!empty($filters) || !empty($negations) || !empty($sorting))) {
            $textQuery = '*';
        }

        return [
            'query'     => $textQuery,
            'filters'   => $filters,
            'negations' => $negations,
            'sorting'   => $sorting,
        ];
    }

    private function _normalizeQuery(string $query): string
    {
        $query = strtolower($query);
        foreach ($this->synonyms as $syn => $correct) {
            if (str_contains($syn, ' ')) {
                $query = str_replace($syn, $correct, $query);
            }
        }
        $words = explode(' ', $query);
        $sortingKeywords = SearchDictionaryService::get()['sorting']; 
        $correctedWords = [];        
        foreach ($words as $word) {
            if (!in_array($word, $sortingKeywords)) {
                $word = $this->synonyms[$word] ?? $word;
            }            
            if (in_array($word, $this->dictionary) || is_numeric($word)) {
                $correctedWords[] = $word;
                continue;
            }
            $closestMatch = $this->_findClosestWord($word);
            $correctedWords[] = !empty($closestMatch) ? $closestMatch : $word;
        }
        return implode(' ', $correctedWords);
    }
    

    private function _extractSorting(string $query): array
    {
        $sorting = [];
        $dict = SearchDictionaryService::get();
        foreach ($dict['sorting'] as $term) {
            if (preg_match('/\b' . preg_quote($term, '/') . '\b/i', $query)) {
                $attribute = $this->synonyms[$term];
                $direction = in_array($term, ['cheapest', 'newest', 'smallest']) ? 'asc' : 'desc';
                $sorting = ['attribute' => $attribute, 'direction' => $direction];
                
                $query = str_ireplace($term, '', $query);
                break;
            }
        }
        return [$query, $sorting];
    }

    private function _extractNegations(string $query): array
    {
        $negations = [];
        if (preg_match_all('/(?:without|no)\s+((?:[\p{L}\s]+,?\s*(?:and)?\s*)+)/iu', $query, $matches)) {
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
        if (preg_match('/(?:with\s+)?(\d+)\s*-\s*(\d+)\s*(bedrooms|bathrooms|area)/i', $query, $matches)) {
            $attribute = $matches[3];
            $filters['min_' . $attribute] = $matches[1];
            $filters['max_' . $attribute] = $matches[2];
            $query = str_replace($matches[0], '', $query);
        }
        if (preg_match('/price\s+between\s+(\d+)\s+and\s+(\d+)/i', $query, $matches)) {
            $filters['min_price'] = $matches[1];
            $filters['max_price'] = $matches[2];
            $query = str_replace($matches[0], '', $query);
        }
        return [$query, $filters];
    }
    
    private function _extractLimits(string $query, array $filters): array
    {
        $patterns = [
            'min_price' => '/(?:over|above)\s+(\d+)/i',
            'max_price' => '/(?:under|less than)\s+(\d+)/i',
        ];

        foreach($patterns as $key => $pattern) {
            if (preg_match($pattern, $query, $matches)) {
                if(!isset($filters[$key])) {
                    $filters[$key] = $matches[1];
                }
                $query = str_replace($matches[0], '', $query);
            }
        }
        return [$query, $filters];
    }

    private function _extractEntities(string $query, array $filters): array
    {
        $dictionary = SearchDictionaryService::get();
        $validCities = $dictionary['locations'];
        $validCountries = $dictionary['countries'];

        if (preg_match('/in\s+((?:(?!\bwith\b|\bwithout\b|\bno\b)[\p{L}\s\'-])+)/iu', $query, $locationMatches)) {
            $potentialLocation = trim(strtolower($locationMatches[1]));

            if (in_array($potentialLocation, $validCities)) {
                if (!isset($filters['city'])) {
                    $filters['city'] = $potentialLocation;
                }
                $query = str_replace($locationMatches[0], '', $query);
            }
            elseif (in_array($potentialLocation, $validCountries)) {
                if (!isset($filters['country'])) {
                    $filters['country'] = $potentialLocation;
                }
                $query = str_replace($locationMatches[0], '', $query);
            }
        }

        $otherPatterns = [
            'area'      => '/(?:with\s+)?(\d+)\s*(?:sqm|sq\s*m)/i',
            'bedrooms'  => '/(?:with\s+)?(\d+)\s+bedrooms?/i',
            'bathrooms' => '/(?:with\s+)?(\d+)\s+bathrooms?/i',
            'balconies' => '/(?:with\s+)?(\d+)\s+balconies?/i', 

        ];

        foreach ($otherPatterns as $key => $pattern) {
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
        if (preg_match('/with\s+((?:[\p{L}\s]+,?\s*(?:and)?\s*)+)/iu', $query, $matches)) {
            $amenitiesDictionary = SearchDictionaryService::get()['amenities'];
            if (!isset($filters['amenities'])) $filters['amenities'] = [];
            $potentialAmenities = preg_split('/(?:,|\s|and)\s*/', $matches[1], -1, PREG_SPLIT_NO_EMPTY);
            foreach ($potentialAmenities as $item) {
                $item = strtolower(trim($item));
                if ($item !== 'balcony' && $item !== 'balconies' && in_array($item, array_map('strtolower', $amenitiesDictionary))) {
                    $filters['amenities'][] = $item;
                }
            }
            $query = str_replace($matches[0], '', $query);
        }

        $words = explode(' ', $query);
        $remainingWords = [];
        foreach ($words as $word) {
            $word = trim($word);
            if (empty($word) || is_numeric($word)) continue;
            if (isset($this->keywordMap[$word])) {
                $type = $this->keywordMap[$word];
                if ($type === 'is_furnished') {
                    $filters['is_furnished'] = true;
                } else {
                    $filters[$type] = $this->synonyms[$word] ?? $word;
                }
            } else {
                $remainingWords[] = $word;
            }
        }

        if (isset($filters['amenities'])) {
            $filters['amenities'] = array_values(array_unique($filters['amenities']));
        }
        return [implode(' ', $remainingWords), $filters];
    }

    private function _findClosestWord(string $word): string
    {
        $closestMatch = '';
        $shortestDistance = -1;
        foreach ($this->dictionary as $dictWord) {
            $distance = levenshtein($word, $dictWord);
            if ($distance === 0) { return $dictWord; }
            if ($distance === 1 && ($shortestDistance < 0 || $distance < $shortestDistance)) {
                $closestMatch = $dictWord;
                $shortestDistance = $distance;
            }
        }
        return $closestMatch;
    }
}