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
 * @extends IteratorAggregate<string, SearchableInterface>
 */
interface SearchablesInterface extends IteratorAggregate, Countable
{
    /**
     * Adds a searchable.
     *
     * @param SearchableInterface $searchable
     * @return static $this
     */
    public function add(SearchableInterface $searchable): static;
    
    /**
     * Removes a searchable.
     *
     * @param string $searchable
     * @return static $this
     */
    public function remove(string $searchable): static;

    /**
     * Returns true if searchable exists, otherwise false.
     *
     * @param string $name
     * @return bool
     */
    public function has(string $name): bool;
    
    /**
     * Returns a searchable by name.
     *
     * @param string $name
     * @return null|SearchableInterface
     */
    public function get(string $name): null|SearchableInterface;
    
    /**
     * Returns a searchable names.
     *
     * @return array<array-key, string>
     */
    public function names(): array;
    
    /**
     * Returns a new instance with the filtered searchable.
     *
     * @param callable $callback
     * @return static
     */
    public function filter(callable $callback): static;
    
    /**
     * Returns a new instance with the searchables sorted.
     *
     * @param null|callable $callback If null, sorts by priority, highest first.
     * @return static
     */
    public function sort(null|callable $callback = null): static;
    
    /**
     * Returns all searchables.
     *
     * @return array<string, SearchableInterface>
     */
    public function all(): array;
}