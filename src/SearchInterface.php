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

/**
 * SearchInterface
 */
interface SearchInterface
{
    /**
     * Returns the filters.
     *
     * @return FiltersInterface
     */
    public function filters(): FiltersInterface;

    /**
     * Returns the searchables.
     *
     * @return SearchablesInterface
     */
    public function searchables(): SearchablesInterface;

    /**
     * Returns the search results found by the input.
     *
     * @param InputInterface $input
     * @return SearchResultsInterface
     */
    public function search(InputInterface $input): SearchResultsInterface;
}