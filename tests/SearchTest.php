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
use Tobento\App\Search\Filters;
use Tobento\App\Search\Input;
use Tobento\App\Search\Search;
use Tobento\App\Search\Searchable;
use Tobento\App\Search\Searchables;
use Tobento\App\Search\SearchablesInterface;
use Tobento\App\Search\SearchInterface;
use Tobento\App\Search\Test\Factory;

class SearchTest extends TestCase
{    
    public function testThatImplementsSearchInterfaces()
    {
        $search = new Search(
            filters: new Filters(),
            searchables: new Searchables(),
        );
        
        $this->assertInstanceof(SearchInterface::class, $search);
    }
    
    public function testFiltersMethod()
    {
        $filters = new Filters();
        
        $search = new Search(
            filters: $filters,
            searchables: new Searchables(),
        );
        
        $this->assertTrue($filters === $search->filters());
    }
    
    public function testSearchablesMethod()
    {
        $search = new Search(
            filters: new Filters(),
            searchables: new Searchables(),
        );
        
        $this->assertInstanceof(SearchablesInterface::class, $search->searchables());
    }

    public function testSearchMethodWithFiltersReturnsFilteredOnly()
    {
        $search = new Search(
            filters: new Filters(
                new Filter\SearchTerm(name: 'search.term'),
            ),
            searchables: new Searchables(
                new Searchable\Menu(
                    menu: Factory::createMenu('foo', 'bar', 'baz'),
                    name: 'menu-main',
                    title: 'Menu Main',
                ),
            ),
        );
        
        $searchResults = $search->search(
            input: new Input(['search' => ['term' => 'foo']]),
        );
        
        $this->assertSame(1, $searchResults->count());
    }
    
    public function testSearchMethodWithoutFiltersReturnsAll()
    {
        $search = new Search(
            filters: new Filters(),
            searchables: new Searchables(
                new Searchable\Menu(
                    menu: Factory::createMenu('foo', 'bar', 'baz'),
                    name: 'menu-main',
                    title: 'Menu Main',
                ),
            ),
        );
        
        $searchResults = $search->search(
            input: new Input(['search' => ['term' => 'foo']]),
        );
        
        $this->assertSame(3, $searchResults->count());
    }
    
    public function testSearchMethodReturnsResultsCombined()
    {
        $search = new Search(
            filters: new Filters(),
            searchables: new Searchables(
                new Searchable\Menu(
                    menu: Factory::createMenu('foo', 'foo1'),
                    name: 'menu-main',
                    title: 'Menu Main',
                ),
                new Searchable\Menu(
                    menu: Factory::createMenu('head-foo', 'head-foo1'),
                    name: 'menu-head',
                    title: 'Menu Head',
                ),
            ),
        );
        
        $searchResults = $search->search(
            input: new Input(['search' => ['term' => 'foo']]),
        );

        $this->assertSame(
            ['foo', 'head-foo', 'foo1', 'head-foo1'],
            array_map(fn($result) => $result->title(), $searchResults->all())
        );
    }
}