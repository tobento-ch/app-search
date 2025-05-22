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
 * SearchableInterface
 */
interface SearchableInterface
{
    /**
     * Returns the name.
     *
     * @return string
     */
    public function name(): string;
    
    /**
     * Returns the title.
     *
     * @return string
     */
    public function title(): string;
    
    /**
     * Returns the priority.
     *
     * @return int
     */
    public function priority(): int;
    
    /**
     * Returns the specific filters for the searchable.
     *
     * @return array<array-key, FilterInterface>
     */
    public function filters(): array;
    
    /**
     * Returns the search results found.
     *
     * @param FiltersInterface $filters
     * @return array<array-key, SearchResultInterface>
     */
    public function search(FiltersInterface $filters): array;
}