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

        // Build a reverse-lookup map for keywords for faster processing
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

        // Flatten the full dictionary for typo correction
        $this->dictionary = array_merge(
            array_keys($this->keywordMap),
            $dict['attributes'],
            $dict['keywords'],
            $dict['locations'],
            $dict['sorting']
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

    // --- All Necessary Private Helper Methods ---

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
            // Use a regex with word boundaries to find the term anywhere in the string
            if (preg_match('/\b' . preg_quote($term, '/') . '\b/i', $query)) {
                $attribute = $this->synonyms[$term];
                $direction = in_array($term, ['cheapest', 'newest', 'smallest']) ? 'asc' : 'desc';
                $sorting = ['attribute' => $attribute, 'direction' => $direction];
                // Replace only the matched term
                $query = preg_replace('/\b' . preg_quote($term, '/') . '\b/i', '', $query, 1);
                break;
            }
        }
        return [$query, $sorting];
    }

    private function _extractNegations(string $query): array
    {
        $negations = [];
        $dictionary = SearchDictionaryService::get();
        $allKeywords = array_merge($dictionary['amenities'], ['furnished']); // Keywords that can be negated

        if (preg_match_all('/(?:without|no)\s+((?:[\p{L}\s]+,?\s*(?:and)?\s*)+)/iu', $query, $matches)) {
            foreach($matches[1] as $i => $match) {
                // Split the captured phrase into individual words
                $negatedItems = preg_split('/(?:,|\s|and)\s*/', $match, -1, PREG_SPLIT_NO_EMPTY);

                foreach ($negatedItems as $item) {
                    $item = trim($item);
                    // IMPORTANT: Only add the item if it's a valid, negatable keyword
                    if (in_array($item, $allKeywords)) {
                        $negations[] = $item;
                    }
                }

                $query = str_replace($matches[0][$i], '', $query);
            }
        }
        return [$query, array_unique($negations)];
    }
    

    private function _extractRanges(string $query, array $filters): array
    {
        // This regex can now handle ranges for area, bedrooms, or bathrooms
        if (preg_match('/(?:with\s+)?(\d+)\s*-\s*(\d+)\s*(bedrooms|bathrooms|area)/i', $query, $matches)) {
            $attribute = rtrim($matches[3], 's'); // Normalize to singular (e.g., bedrooms -> bedroom)
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
    


    private function _extractEntities(string $query, array $filters): array
    {
        $patterns = [
            'area'      => '/(?:with\s+)?(\d+)\s*(?:sqm|sq\s*m)/i',
            'bedrooms'  => '/(?:with\s+)?(\d+)\s+bedrooms?/i',
            'bathrooms' => '/(?:with\s+)?(\d+)\s+bathrooms?/i',
            // This regex is now non-greedy and will stop before "with"
            'city'      => '/in\s+((?:(?!\bwith\b)[\p{L}\s\'-])+)/iu',
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
        // Regex to find phrases after "with"
        if (preg_match('/with\s+((?:[\p{L}\s]+,?\s*(?:and)?\s*)+)/iu', $query, $matches)) {
            // Get the list of actual amenities from the dictionary to validate against
            $amenitiesDictionary = SearchDictionaryService::get()['amenities'];
            if (!isset($filters['amenities'])) {
                $filters['amenities'] = [];
            }

            // Split the captured phrase (e.g., "gym and pool") into individual items
            $potentialAmenities = preg_split('/(?:,|\s|and)\s*/', $matches[1], -1, PREG_SPLIT_NO_EMPTY);

            foreach ($potentialAmenities as $item) {
                $item = trim($item);
                // Only add the item if it's a real amenity
                if (in_array($item, $amenitiesDictionary)) {
                    $filters['amenities'][] = $item;
                }
            }
            // Remove the entire "with..." phrase from the query
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
                } elseif ($type === 'amenities') {
                    // This handles standalone amenities not following "with"
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

        // Clean up the amenities list
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
            if ($distance <= 2 && ($shortestDistance < 0 || $distance < $shortestDistance)) {
                $closestMatch = $dictWord;
                $shortestDistance = $distance;
            }
        }
        return $closestMatch;
    }
}