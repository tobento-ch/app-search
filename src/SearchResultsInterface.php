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

use Countable;
use IteratorAggregate;

/**
 * @extends IteratorAggregate<array-key, SearchResultInterface>
 */
interface SearchResultsInterface extends IteratorAggregate, Countable
{
    /**
     * Returns a new instance with the search results filtered by searchable.
     *
     * @param $name
     * @return static
     */
    public function searchable(string $name): static;
    
    /**
     * Returns a new instance with the filtered search results.
     *
     * @param callable $callback
     * @return static
     */
    public function filter(callable $callback): static;
    
    /**
     * Returns all search results.
     *
     * @return array<array-key, SearchResultInterface>
     */
    public function all(): array;
}