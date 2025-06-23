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

namespace Tobento\App\Search\Test\Searchable;

use PHPUnit\Framework\TestCase;
use Tobento\App\Search\Filter;
use Tobento\App\Search\Filters;
use Tobento\App\Search\Input;
use Tobento\App\Search\Search;
use Tobento\App\Search\Searchable;
use Tobento\App\Search\SearchableInterface;
use Tobento\App\Search\Searchables;
use Tobento\App\Search\Test\Factory;
use Tobento\Service\Menu\Menu;

class MenuTest extends TestCase
{
    public function testThatImplementsSearchableInterface()
    {
        $searchable = new Searchable\Menu(
            menu: Factory::createMenu('foo', 'bar', 'baz'),
        );
        
        $this->assertInstanceof(SearchableInterface::class, $searchable);
    }
    
    public function testNameMethod()
    {
        $this->assertSame('menu', (new Searchable\Menu(menu: Factory::createMenu('foo')))->name());
        $this->assertSame('menu-head', (new Searchable\Menu(menu: Factory::createMenu('foo'), name: 'menu-head'))->name());
    }
    
    public function testTitleMethod()
    {
        $this->assertSame('Menu Items', (new Searchable\Menu(menu: Factory::createMenu('foo')))->title());
        $this->assertSame('Foo', (new Searchable\Menu(menu: Factory::createMenu('foo'), title: 'Foo'))->title());
    }
    
    public function testPriorityMethod()
    {
        $this->assertSame(0, (new Searchable\Menu(menu: Factory::createMenu('foo')))->priority());
        $this->assertSame(5, (new Searchable\Menu(menu: Factory::createMenu('foo'), priority: 5))->priority());
    }
    
    public function testFiltersMethod()
    {
        $searchable = new Searchable\Menu(menu: Factory::createMenu('foo'));
        
        $this->assertSame(1, count($searchable->filters()));
        $this->assertSame('search.menu-page', $searchable->filters()[0]->name());
        
        $searchable = new Searchable\Menu(menu: Factory::createMenu('foo'), name: 'menu-head');
        
        $this->assertSame('search.menu-head-page', $searchable->filters()[0]->name());
    }
    
    public function testSearchMethodReturnsCreatedItems()
    {
        $searchable = new Searchable\Menu(menu: Factory::createMenu('foo'));
        
        $results = $searchable->search(
            filters: new Filters(),
        );
        
        $this->assertSame('menu', $results[0]->searchable());
        $this->assertSame('Menu Items', $results[0]->type());
        $this->assertSame('foo', $results[0]->title());
        $this->assertSame('foo', $results[0]->url());
    }
    
    public function testSearchMethodReturnsCreatedItemsWithTree()
    {
        $menu = new Menu('name');
        $menu->link('foo', 'Foo')->id('foo');
        $menu->link('bar', 'Bar')->id('bar')->parent('foo');
        $menu->link('baz', 'Baz')->id('baz')->parent('bar');
        
        $searchable = new Searchable\Menu(menu: $menu);
        
        $results = $searchable->search(
            filters: new Filters(),
        );
        
        $this->assertSame('menu', $results[2]->searchable());
        $this->assertSame('Menu Items', $results[2]->type());
        $this->assertSame('Foo / Bar / Baz', $results[2]->title());
        $this->assertSame('baz', $results[2]->url());
    }
    
    public function testSearchMethodReturnsAllWithoutActiveFilters()
    {
        $searchable = new Searchable\Menu(menu: Factory::createMenu('foo', 'bar', 'baz'));
        
        $this->assertSame(3, $searchable->totalItems());
        
        $results = $searchable->search(
            filters: new Filters(),
        );
        
        $this->assertSame(3, $searchable->totalItems());
        $this->assertSame(3, count($results));
        $this->assertSame(3, $searchable->pagination()->getTotalItems());
    }
    
    public function testSearchMethodReturnsItemsLimitedToPagination()
    {
        $items = ['bar', 'baz'];
        for ($i = 1; $i <= 30; $i++) {
            $items[] = 'foo'.$i;
        }
        
        $searchable = new Searchable\Menu(menu: Factory::createMenu(...$items));
        
        $this->assertSame(32, $searchable->totalItems());
        
        $results = $searchable->search(
            filters: new Filters(),
        );
        
        $this->assertSame(32, $searchable->totalItems());
        $this->assertSame(25, count($results));
        $this->assertSame(32, $searchable->pagination()->getTotalItems());
    }
    
    public function testSearchMethodReturnsItemsFilteredBySearchTerm()
    {
        $items = ['bar', 'baz'];
        for ($i = 1; $i <= 30; $i++) {
            $items[] = 'foo'.$i;
        }
        
        $searchable = new Searchable\Menu(menu: Factory::createMenu(...$items));
        
        $termFilter = new Filter\SearchTerm(name: 'search.term');
        $termFilter->apply(
            input: new Input(['search' => ['term' => 'foo']]),
            search: new Search(filters: new Filters(), searchables: new Searchables()),
        );
        
        $results = $searchable->search(
            filters: new Filters($termFilter),
        );
        
        $this->assertSame(30, $searchable->totalItems());
        $this->assertSame(25, count($results));
        $this->assertSame(30, $searchable->pagination()->getTotalItems());
    }
    
    public function testSearchMethodReturnsItemsFoundByFilters()
    {
        $searchable = new Searchable\Menu(menu: Factory::createMenu('foo', 'foo1', 'bar', 'baz'));
        
        $this->assertSame(4, $searchable->totalItems());
        
        $results = $searchable->search(
            filters: new Filters(
                new Filter\SearchTerm(name: 'search.term', searchTerm: 'foo baz'),
            ),
        );
        
        $this->assertSame(4, $searchable->totalItems());
        
        $this->assertSame(
            ['foo', 'foo1', 'baz'],
            array_map(fn($result) => $result->title(), $results)
        );
    }
    
    public function testSearchMethodReturnsItemsPaginated()
    {
        $items = ['bar', 'baz'];
        for ($i = 1; $i <= 30; $i++) {
            $items[] = 'foo'.$i;
        }
        
        $searchable = new Searchable\Menu(menu: Factory::createMenu(...$items));
        
        $filters = new Filters(
            new Filter\SearchTerm(name: 'search.term'),
        );
        
        foreach($searchable->filters() as $filter) {
            $filters->add($filter);
        }
        
        $input = new Input(['search' => ['term' => 'foo', 'menu-page' => '2']]);
        $search = new Search(filters: $filters, searchables: new Searchables());
        
        foreach($filters as $filter) {
            $filter->apply(input: $input, search: $search);
        }
        
        $results = $searchable->search(filters: $filters);
        
        $this->assertSame(30, $searchable->totalItems());
        $this->assertSame(5, count($results));
        $this->assertSame(30, $searchable->pagination()->getTotalItems());
    }    
}