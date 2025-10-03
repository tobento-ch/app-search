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

use Traversable;
use ArrayIterator;

/**
 * Filters
 */
class Filters implements FiltersInterface
{
    /**
     * @var array<string, FilterInterface>
     */
    protected array $filters = [];
    
    /**
     * Create a new Fields.
     *
     * @param FilterInterface $filters
     */
    public function __construct(
        FilterInterface ...$filters,
    ) {
        foreach($filters as $filter) {
            $this->filters[$filter->name()] = $filter;
        }
    }

    /**
     * Adds a filter.
     *
     * @param FilterInterface $filter
     * @return static $this
     */
    public function add(FilterInterface $filter): static
    {
        $this->filters[$filter->name()] = $filter;
        return $this;
    }
    
    /**
     * Returns a new instance with the filters filtered.
     *
     * @param callable $callback
     * @return static
     */
    public function filter(callable $callback): static
    {
        $new = clone $this;
        $new->filters = array_filter($this->filters, $callback);
        return $new;
    }
    
    /**
     * Returns a new instance with with only the filters specified.
     *
     * @param string ...$name
     * @return static
     */
    public function only(string ...$name): static
    {
        return $this->filter(
            fn(FilterInterface $f): bool => in_array($f->name(), $name)
        );
    }
    
    /**
     * Returns a new instance with the filters except those specified.
     *
     * @param string ...$name
     * @return static
     */
    public function except(string ...$name): static
    {
        return $this->filter(
            fn(FilterInterface $f): bool => !in_array($f->name(), $name)
        );
    }

    /**
     * Returns a new instance with (in)active filters only.
     *
     * @param bool $active
     * @return static
     */
    public function active(bool $active = true): static
    {
        return $this->filter(
            fn(FilterInterface $f): bool => $f->isActive() === $active
        );
    }
    
    /**
     * Returns a new instance with disabled or (enabled) filters only.
     *
     * @param bool $disabled
     * @return static
     */
    public function disabled(bool $disabled = true): static
    {
        return $this->filter(
            fn(FilterInterface $f): bool => $f->isDisabled() === $disabled
        );
    }
    
    /**
     * Returns a new instance with (un)storable filters only.
     *
     * @param bool $storable
     * @return static
     */
    public function storable(bool $storable = true): static
    {
        return $this->filter(
            fn(FilterInterface $f): bool => $f->isStorable() === $storable
        );
    }
    
    /**
     * Returns a new instance with the specified searchable filtered.
     *
     * @param null|string $name
     * @return static
     */
    public function searchable(null|string $name): static
    {
        return $this->filter(
            fn(FilterInterface $f): bool => $f->searchable() === $name
        );
    }
    
    /**
     * Returns true if filter exists, otherwise false.
     *
     * @param string $name
     * @return bool
     */
    public function has(string $name): bool
    {
        return isset($this->filters[$name]);
    }
    
    /**
     * Returns a filter by name.
     *
     * @return null|object
     */
    public function get(string $name): null|object
    {
        return $this->filters[$name] ?? null;
    }
    
    /**
     * Returns all filters.
     *
     * @return array<string, FilterInterface>
     */
    public function all(): array
    {
        return $this->filters;
    }
    
    /**
     * Returns the filter names.
     *
     * @return array<array-key, string>
     */
    public function names(): array
    {
        return array_keys($this->filters);
    }
    
    /**
     * Returns the number of filters.
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->all());
    }
    
    /**
     * Get iterator.
     *
     * @return Traversable<string, FilterInterface>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->all());
    }
}