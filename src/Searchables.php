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
 * Searchables
 */
final class Searchables implements SearchablesInterface
{
    /**
     * @var array<string, SearchableInterface>
     */
    private array $searchables = [];
    
    /**
     * Create a new Searchables instance.
     *
     * @param SearchableInterface ...$searchables
     */
    public function __construct(
        SearchableInterface ...$searchables,
    ) {
        foreach($searchables as $searchable) {
            $this->add($searchable);
        }
    }
    
    /**
     * Adds a searchable.
     *
     * @param SearchableInterface $searchable
     * @return static $this
     */
    public function add(SearchableInterface $searchable): static
    {
        $this->searchables[$searchable->name()] = $searchable;
        return $this;
    }
    
    /**
     * Removes a searchable.
     *
     * @param string $searchable
     * @return static $this
     */
    public function remove(string $searchable): static
    {
        unset($this->searchables[$searchable]);
        return $this;
    }

    /**
     * Returns true if searchable exists, otherwise false.
     *
     * @param string $name
     * @return bool
     */
    public function has(string $name): bool
    {
        return isset($this->searchables[$name]);
    }
    
    /**
     * Returns a searchable by name.
     *
     * @param string $name
     * @return null|SearchableInterface
     */
    public function get(string $name): null|SearchableInterface
    {
        return $this->searchables[$name] ?? null;
    }
    
    /**
     * Returns a searchable names.
     *
     * @return array<array-key, string>
     */
    public function names(): array
    {
        return array_keys($this->searchables);
    }
    
    /**
     * Returns a new instance with the filtered searchable.
     *
     * @param callable $callback
     * @return static
     */
    public function filter(callable $callback): static
    {
        $new = clone $this;
        $new->searchables = array_filter($this->searchables, $callback);
        return $new;
    }
    
    /**
     * Returns a new instance with the searchables sorted.
     *
     * @param null|callable $callback If null, sorts by priority, highest first.
     * @return static
     */
    public function sort(null|callable $callback = null): static
    {
        if (is_null($callback)) {
            $callback = fn(SearchableInterface $a, SearchableInterface $b): int
                => $b->priority() <=> $a->priority();
        }
        
        $new = clone $this;
        uasort($new->searchables, $callback);
        return $new;
    }
    
    /**
     * Returns all searchables.
     *
     * @return array<string, SearchableInterface>
     */
    public function all(): array
    {
        return $this->searchables;
    }
    
    /**
     * Returns the number of searchables.
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->searchables);
    }
    
    /**
     * Returns the iterator. 
     *
     * @return Traversable
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->searchables);
    }
}