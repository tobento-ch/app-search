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
use Tobento\App\Search\Searchables;
use Tobento\App\Search\Test\Factory;

class ClearTest extends TestCase
{    
    public function testThatImplementsFilterInterface()
    {
        $this->assertInstanceof(FilterInterface::class, new Filter\Clear(name: 'search.clear'));
    }
    
    public function testConstructMethodThrowsInvalidArgumentExceptionIfInvalidNameCharacters()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The filter name search/clear must only contain [a-z-_.] characters');
        
        new Filter\Clear(name: 'search/clear');
    }
    
    public function testConstructMethodThrowsInvalidArgumentExceptionIfNameDoesntEntWithClear()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The filter name foo must end with "clear"');
        
        new Filter\Clear(name: 'foo');
    }
    
    public function testNameMethod()
    {
        $this->assertSame('search.clear', (new Filter\Clear(name: 'search.clear'))->name());
    }
    
    public function testSearchableMethod()
    {
        $this->assertSame(null, (new Filter\Clear(name: 'search.clear'))->searchable());
    }
    
    public function testIsActiveMethod()
    {
        $this->assertFalse((new Filter\Clear(name: 'search.clear'))->isActive());
    }
    
    public function testIsDisabledMethod()
    {
        $this->assertFalse((new Filter\Clear(name: 'search.clear'))->isDisabled());
    }
    
    public function testIsStorableMethod()
    {
        $this->assertFalse((new Filter\Clear(name: 'search.clear'))->isStorable());
    }
    
    public function testApplyMethod()
    {
        $filter = new Filter\Clear(name: 'search.clear');
        
        $filter->apply(
            input: new Input([]),
            search: new Search(filters: new Filters(), searchables: new Searchables()),
        );
        
        $this->assertTrue(true);
    }
    
    public function testValueMethod()
    {
        $this->assertNull((new Filter\Clear(name: 'search.clear'))->value());
    }
    
    public function testRenderMethod()
    {
        $rendered = (new Filter\Clear(name: 'search.clear'))->render(Factory::createView());
        
        $this->assertSame(
            '<a class="button raw text-xs" href="?search[clear]=1" data-search-filter="search.clear" data-search-action="clear">Clear all</a>',
            $rendered
        );
    }
}