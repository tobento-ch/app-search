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
 * FiltersInterface
 */
interface FiltersInterface extends IteratorAggregate, Countable
{
    /**
     * Adds a filter.
     *
     * @param FilterInterface $filter
     * @return static $this
     */
    public function add(FilterInterface $filter): static;
    
    /**
     * Returns a new instance with the filters filtered.
     *
     * @param callable $callback
     * @return static
     */
    public function filter(callable $callback): static;

    /**
     * Returns a new instance with with only the filters specified.
     *
     * @param string ...$name
     * @return static
     */
    public function only(string ...$name): static;
    
    /**
     * Returns a new instance with the filters except those specified.
     *
     * @param string ...$name
     * @return static
     */
    public function except(string ...$name): static;
    
    /**
     * Returns a new instance with (in)active filters only.
     *
     * @param bool $active
     * @return static
     */
    public function active(bool $active = true): static;
    
    /**
     * Returns a new instance with disabled or (enabled) filters only.
     *
     * @param bool $disabled
     * @return static
     */
    public function disabled(bool $disabled = true): static;
    
    /**
     * Returns a new instance with (un)storable filters only.
     *
     * @param bool $active
     * @return static
     */
    public function storable(bool $storable = true): static;
    
    /**
     * Returns a new instance with the specified searchable filtered.
     *
     * @param null|string $name
     * @return static
     */
    public function searchable(null|string $name): static;
    
    /**
     * Returns true if filter exists, otherwise false.
     *
     * @param string $name
     * @return bool
     */
    public function has(string $name): bool;
    
    /**
     * Returns a filter by name.
     *
     * @return null|object
     */
    public function get(string $name): null|object;
    
    /**
     * Returns all filters.
     *
     * @return array<string, FilterInterface>
     */
    public function all(): array;
    
    /**
     * Returns the filter names.
     *
     * @return array<array-key, string>
     */
    public function names(): array;
}