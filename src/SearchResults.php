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
use Traversable;

/**
 * SearchResults
 */
final class SearchResults implements SearchResultsInterface
{
    /**
     * @var array<array-key, SearchResultInterface>
     */
    private array $results = [];
    
    /**
     * Create a new SearchResults.
     *
     * @param SearchResultInterface ...$results
     */
    public function __construct(
        SearchResultInterface ...$results,
    ) {
        $this->results = $results;
    }
    
    /**
     * Returns a new instance with the search results filtered by searchable.
     *
     * @param $name
     * @return static
     */
    public function searchable(string $name): static
    {
        return $this->filter(fn(SearchResultInterface $r): bool => $r->searchable() === $name);
    }
    
    /**
     * Returns a new instance with the filtered search results.
     *
     * @param callable $callback
     * @return static
     */
    public function filter(callable $callback): static
    {
        $new = clone $this;
        $new->results = array_filter($this->results, $callback);
        return $new;
    }
    
    /**
     * Returns all search results.
     *
     * @return array<array-key, SearchResultInterface>
     */
    public function all(): array
    {
        return $this->results;
    }
    
    /**
     * Returns the number of search results.
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->results);
    }
    
    /**
     * Returns the iterator. 
     *
     * @return Traversable<array-key, SearchResultInterface>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->results);
    }
}