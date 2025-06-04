<?php

/**
 * TOBENTO
 *
 * @copyright   Tobias Strub, TOBENTO
 * @license     MIT License, see LICENSE file distributed with this source code.
 * @author      Tobias Strub
 * @link        https://www.tobento.ch
 */

declare(strict_types=1);

namespace Tobento\App\Search;

use ArrayIterator;

/**
 * Search
 */
class Search implements SearchInterface
{
    /**
     * Create a new Search instance.
     *
     * @param FiltersInterface $filters
     * @param SearchablesInterface $searchables
     */
    public function __construct(
        protected FiltersInterface $filters,
        protected SearchablesInterface $searchables,
    ) {
        foreach($searchables as $searchable) {
            foreach($searchable->filters() as $filter) {
                $filters->add($filter);
            }
        }
    }
    
    /**
     * Returns the filters.
     *
     * @return FiltersInterface
     */
    public function filters(): FiltersInterface
    {
        foreach($this->searchables() as $searchable) {
            foreach($searchable->filters() as $filter) {
                $this->filters->add($filter);
            }
        }
        
        return $this->filters;
    }

    /**
     * Returns the searchables.
     *
     * @return SearchablesInterface
     */
    public function searchables(): SearchablesInterface
    {
        return $this->searchables = $this->searchables->sort();
    }
    
    /**
     * Returns the search results found by the input.
     *
     * @param InputInterface $input
     * @return SearchResultsInterface
     */
    public function search(InputInterface $input): SearchResultsInterface
    {
        $filters = $this->filters();
        
        foreach($filters as $filter) {
            $filter->apply($input, $this);
        }
        
        // We combine each searchable result:
        $iterators = [];
        
        foreach($this->searchables as $searchable) {
            $iterators[] = new ArrayIterator($searchable->search($filters));
        }
        
        $results = [];
        $numOfIts = count($iterators);
        
        while($numOfIts > 0) {
            $iterator = array_shift($iterators);
            $result = $iterator->current();
            if ($result) {
                $results[] = $result;
            }
            $iterator->next();
            if($iterator->valid()) {
                $iterators[] = $iterator;
            } else {
                $numOfIts--;
            }
        }
        
        return new SearchResults(...$results);
    }
}