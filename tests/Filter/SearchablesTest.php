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

namespace Tobento\App\Search\Test\Filter;

use PHPUnit\Framework\TestCase;
use Tobento\App\Search\Filter;
use Tobento\App\Search\FilterInterface;
use Tobento\App\Search\Filters;
use Tobento\App\Search\Input;
use Tobento\App\Search\Search;
use Tobento\App\Search\Searchable;
use Tobento\App\Search\Searchables;
use Tobento\App\Search\Test\Factory;

class SearchablesTest extends TestCase
{    
    public function AtestThatImplementsFilterInterface()
    {
        $this->assertInstanceof(FilterInterface::class, new Filter\Searchables(name: 'search.searchables'));
    }
    
    public function testConstructMethodThrowsInvalidArgumentExceptionIfInvalidNameCharacters()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The filter name search/foo must only contain [a-z-_.] characters');
        
        new Filter\Searchables(name: 'search/foo');
    }
    
    public function testNameMethod()
    {
        $this->assertSame('search.searchables', (new Filter\Searchables(name: 'search.searchables'))->name());
    }
    
    public function testSearchableMethod()
    {
        $this->assertSame(null, (new Filter\Searchables(name: 'search.searchables'))->searchable());
    }
    
    public function testIsDisabledMethod()
    {
        $this->assertFalse((new Filter\Searchables(name: 'search.searchables'))->isDisabled());
    }
    
    public function testIsStorableMethod()
    {
        $this->assertTrue((new Filter\Searchables(name: 'search.searchables'))->isStorable());
    }
    
    public function testApplyMethodAppliesValueIfInputAndSearchableExists()
    {
        $filter = new Filter\Searchables(name: 'search.searchables');
        
        $this->assertSame([], $filter->value());
        $this->assertFalse($filter->isActive());
        $this->assertSame([], $filter->searchables());
        $this->assertSame([], $filter->selectedSearchables());
        
        $filter->apply(
            input: new Input(['search' => ['searchables' => ['foo', 'bar', 'baz']]]),
            search: new Search(
                filters: new Filters(),
                searchables: new Searchables(
                    new Searchable\Menu(
                        menu: Factory::createMenu('foo'),
                        name: 'foo',
                    ),
                    new Searchable\Menu(
                        menu: Factory::createMenu('bar'),
                        name: 'bar',
                    ),
                ),
            ),
        );
        
        $this->assertSame(['foo', 'bar'], $filter->value());
        $this->assertTrue($filter->isActive());
        $this->assertSame(['foo' => 'Menu Items', 'bar' => 'Menu Items'], $filter->searchables());
        $this->assertSame(['foo', 'bar'], $filter->selectedSearchables());
    }
    
    public function testApplyMethodSkipsSearchableIfNotExistsOrInvalid()
    {
        $filter = new Filter\Searchables(name: 'search.searchables');
        
        $this->assertSame([], $filter->value());
        $this->assertFalse($filter->isActive());
        $this->assertSame([], $filter->searchables());
        $this->assertSame([], $filter->selectedSearchables());
        
        $filter->apply(
            input: new Input(['search' => ['searchables' => ['foo', 'bar', [], 456]]]),
            search: new Search(
                filters: new Filters(),
                searchables: new Searchables(
                    new Searchable\Menu(
                        menu: Factory::createMenu('foo'),
                        name: 'foo',
                    ),
                ),
            ),
        );
        
        $this->assertSame(['foo'], $filter->value());
        $this->assertTrue($filter->isActive());
        $this->assertSame(['foo' => 'Menu Items'], $filter->searchables());
        $this->assertSame(['foo'], $filter->selectedSearchables());
    }
    
    public function testRenderMethod()
    {
        $filter = new Filter\Searchables(
            name: 'search.searchables',
            label: 'Label',
            description: 'Desc',
        );
        
        $filter->apply(
            input: new Input(['search' => ['searchables' => ['foo', 'bar', 'baz']]]),
            search: new Search(
                filters: new Filters(),
                searchables: new Searchables(
                    new Searchable\Menu(
                        menu: Factory::createMenu('foo'),
                        name: 'foo',
                    ),
                    new Searchable\Menu(
                        menu: Factory::createMenu('bar'),
                        name: 'bar',
                    ),
                ),
            ),
        );
        
        $rendered = $filter->render(Factory::createView());
        $this->assertStringContainsString('Label', $rendered);
        $this->assertStringContainsString('Desc', $rendered);
        $this->assertStringContainsString('<span class="wrap-v"><input id="search_searchables_1" name="search[searchables][]" type="checkbox" value="foo" checked><label for="search_searchables_1">Menu Items</label></span><span class="wrap-v"><input id="search_searchables_2" name="search[searchables][]" type="checkbox" value="bar" checked><label for="search_searchables_2">Menu Items</label></span><input name="search[searchables][]" type="hidden" value="_none">', $rendered);
        $this->assertStringContainsString('label for="search_searchables"', $rendered);
    }
    
    public function testRenderWithoutLabel()
    {
        $filter = new Filter\Searchables(name: 'search.searchables', label: '');
        
        $rendered = $filter->render(Factory::createView());
        $this->assertStringNotContainsString('label for', $rendered);
    }
    
    public function testRendersCustomView()
    {
        $filter = new Filter\Searchables(name: 'search.searchables', view: 'custom/search/filter');
        
        // empty as view does not exist, but we know that it is changeable:
        $this->assertSame('', $filter->render(Factory::createView()));
    }
}