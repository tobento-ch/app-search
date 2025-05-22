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

namespace Tobento\App\Search\Test;

use PHPUnit\Framework\TestCase;
use Tobento\App\Search\Filter;
use Tobento\App\Search\FilterInterface;
use Tobento\App\Search\Filters;
use Tobento\App\Search\FiltersInterface;

class FiltersTest extends TestCase
{
    public function testConstructorMethod()
    {
        $filters = new Filters();
        $this->assertInstanceof(FiltersInterface::class, $filters);
        
        $filters = new Filters(new Filter\SearchTerm(name: 'search.term'));
        $this->assertSame(1, $filters->count());
    }

    public function testAddMethod()
    {
        $filters = new Filters();
        
        $this->assertSame(0, $filters->count());
        
        $filters->add(new Filter\SearchTerm(name: 'search.term'));
        $filters->add(new Filter\Clear(name: 'search.clear'));
        
        $this->assertSame(2, $filters->count());
    }
    
    public function testFilterMethod()
    {
        $filters = new Filters(
            new Filter\SearchTerm(name: 'search.term'),
            new Filter\Clear(name: 'search.clear'),
        );
        
        $filtered = $filters->filter(fn(FilterInterface $f): bool => $f->name() === 'search.term');
        
        $this->assertFalse($filters === $filtered);
        $this->assertSame(2, $filters->count());
        $this->assertSame(1, $filtered->count());
    }
    
    public function testOnlyMethod()
    {
        $filters = new Filters(
            new Filter\SearchTerm(name: 'search.term'),
            new Filter\Input(name: 'search.input'),
            new Filter\Clear(name: 'search.clear'),
        );
        
        $filtersNew = $filters->only('search.term', 'search.clear');
        
        $this->assertFalse($filters === $filtersNew);
        $this->assertSame(2, $filtersNew->count());
    }
    
    public function testExceptMethod()
    {
        $filters = new Filters(
            new Filter\SearchTerm(name: 'search.term'),
            new Filter\Input(name: 'search.input'),
            new Filter\Clear(name: 'search.clear'),
        );
        
        $filtersNew = $filters->except('search.term', 'search.clear');
        
        $this->assertFalse($filters === $filtersNew);
        $this->assertSame(1, $filtersNew->count());
    }
    
    public function testActiveMethod()
    {
        $filters = new Filters(
            new Filter\SearchTerm(name: 'search.term'),
            new Filter\Input(name: 'search.input', inputValue: 'foo'),
            new Filter\Clear(name: 'search.clear'),
        );
        
        $filtersNew = $filters->active();
        
        $this->assertFalse($filters === $filtersNew);
        $this->assertSame(1, $filtersNew->count());
        $this->assertSame(1, $filters->active(true)->count());
        $this->assertSame(2, $filters->active(false)->count());
    }
    
    public function testDisabledMethod()
    {
        $filters = new Filters(
            new Filter\SearchTerm(name: 'search.term'),
            new Filter\Input(name: 'search.input'),
            new Filter\Clear(name: 'search.clear'),
        );
        
        $filtersNew = $filters->disabled();
        
        $this->assertFalse($filters === $filtersNew);
        $this->assertSame(0, $filtersNew->count());
        $this->assertSame(0, $filters->disabled(true)->count());
        $this->assertSame(3, $filters->disabled(false)->count());
    }
    
    public function testStorableMethod()
    {
        $filters = new Filters(
            new Filter\SearchTerm(name: 'search.term'),
            new Filter\Input(name: 'search.input'),
            new Filter\Clear(name: 'search.clear'),
        );
        
        $filtersNew = $filters->storable();
        
        $this->assertFalse($filters === $filtersNew);
        $this->assertSame(2, $filtersNew->count());
        $this->assertSame(2, $filters->storable(true)->count());
        $this->assertSame(1, $filters->storable(false)->count());
    }
    
    public function testSearchableMethod()
    {
        $filters = new Filters(
            new Filter\SearchTerm(name: 'search.term'),
            new Filter\Input(name: 'search.input', searchable: 'products'),
            new Filter\Clear(name: 'search.clear'),
        );
        
        $filtersNew = $filters->searchable(null);
        
        $this->assertFalse($filters === $filtersNew);
        $this->assertSame(2, $filtersNew->count());
        $this->assertSame(1, $filters->searchable('products')->count());
    }

    public function testHasMethod()
    {
        $filters = new Filters();
        $this->assertFalse($filters->has(name: 'search.input'));
        
        $filters = new Filters(new Filter\Input(name: 'search.input'));
        $this->assertTrue($filters->has(name: 'search.input'));
    }
    
    public function testGetMethod()
    {
        $filters = new Filters();
        $this->assertSame(null, $filters->get(name: 'search.input'));
        
        $filters = new Filters(new Filter\Input(name: 'search.input'));
        $this->assertSame('search.input', $filters->get(name: 'search.input')->name());
    }
    
    public function testAllMethod()
    {
        $filters = new Filters();
        $this->assertSame([], $filters->all());
        
        $foo = new Filter\Input(name: 'foo');
        $filters = new Filters($foo);
        $this->assertSame(['foo' => $foo], $filters->all());
    }
    
    public function testNamesMethod()
    {
        $filters = new Filters(
            new Filter\Input(name: 'foo'),
            new Filter\Input(name: 'bar'),
        );
        
        $this->assertSame(['foo', 'bar'], $filters->names());
    }
    
    public function testCountMethod()
    {
        $filters = new Filters();
        $this->assertSame(0, $filters->count());
        
        $filters = new Filters(new Filter\Input(name: 'foo'));
        $this->assertSame(1, $filters->count());
    }
    
    public function testIteration()
    {
        $filters = new Filters(new Filter\Input(name: 'foo'), new Filter\Input(name: 'bar'));
        
        foreach($filters as $filter) {
            $this->assertInstanceof(FilterInterface::class, $filter);
        }
    }
}