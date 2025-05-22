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

class SelectTest extends TestCase
{    
    public function testThatImplementsFilterInterface()
    {
        $this->assertInstanceof(FilterInterface::class, new Filter\Select(name: 'search.foo'));
    }
    
    public function testConstructMethodThrowsInvalidArgumentExceptionIfInvalidNameCharacters()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The filter name search/foo must only contain [a-z-_.] characters');
        
        new Filter\Select(name: 'search/foo');
    }
    
    public function testNameMethod()
    {
        $this->assertSame('search.foo', (new Filter\Select(name: 'search.foo'))->name());
    }
    
    public function testSearchableMethod()
    {
        $this->assertSame(null, (new Filter\Select(name: 'search.foo'))->searchable());
        $this->assertSame('bar', (new Filter\Select(name: 'search.foo', searchable: 'bar'))->searchable());
    }
    
    public function testIsDisabledMethod()
    {
        $this->assertFalse((new Filter\Select(name: 'search.foo'))->isDisabled());
    }
    
    public function testIsStorableMethod()
    {
        $this->assertTrue((new Filter\Select(name: 'search.foo'))->isStorable());
    }
    
    public function testApplyMethodAppliesValueIfInputExists()
    {
        $filter = new Filter\Select(
            name: 'search.foo',
            options: ['blue' => 'Blue', 'red' => 'Red'],
        );
        
        $this->assertSame([], $filter->value());
        $this->assertSame([], $filter->selected());
        $this->assertFalse($filter->isActive());
        
        $filter->apply(
            input: new Input(['search' => ['foo' => 'red']]),
            search: new Search(filters: new Filters(), searchables: new Searchables()),
        );
        
        $this->assertSame('red', $filter->value());
        $this->assertSame('red', $filter->selected());
        $this->assertTrue($filter->isActive());
    }
    
    public function testApplyMethodSkipsValuesIfOptionDoesNotExist()
    {
        $filter = new Filter\Select(
            name: 'search.foo',
            options: ['blue' => 'Blue', 'red' => 'Red'],
        );
        
        $filter->apply(
            input: new Input(['search' => ['foo' => ['green']]]),
            search: new Search(filters: new Filters(), searchables: new Searchables()),
        );
        
        $this->assertSame([], $filter->value());
        $this->assertSame([], $filter->selected());
        $this->assertFalse($filter->isActive());
    }
    
    public function testApplyMethodWithMuliple()
    {
        $filter = new Filter\Select(
            name: 'search.foo',
            options: ['blue' => 'Blue', 'red' => 'Red'],
            selectAttributes: ['multiple'],
        );
        
        $this->assertSame([], $filter->value());
        $this->assertSame([], $filter->selected());
        $this->assertFalse($filter->isActive());
        
        $filter->apply(
            input: new Input(['search' => ['foo' => ['red', 'green', 'blue']]]),
            search: new Search(filters: new Filters(), searchables: new Searchables()),
        );
        
        $this->assertSame(['red', 'blue'], $filter->value());
        $this->assertSame(['red', 'blue'], $filter->selected());
        $this->assertTrue($filter->isActive());
    }
    
    public function testRenderMethod()
    {
        $filter = new Filter\Select(
            name: 'search.foo',
            label: 'Label',
            description: 'Desc',
            options: ['blue' => 'Blue', 'red' => 'Red'],
        );
        
        $filter->apply(
            input: new Input(['search' => ['foo' => 'blue']]),
            search: new Search(filters: new Filters(), searchables: new Searchables()),
        );
        
        $rendered = $filter->render(Factory::createView());
        $this->assertStringContainsString('Label', $rendered);
        $this->assertStringContainsString('Desc', $rendered);
        $this->assertStringContainsString('<select id="search_foo" name="search[foo]"><option value="none">---</option><option value="blue" selected>Blue</option><option value="red">Red</option></select>', $rendered);
        $this->assertStringContainsString('label for="search_foo"', $rendered);
    }
    
    public function testRenderWithOptionAndOptgroupAttributes()
    {
        $filter = new Filter\Select(
            name: 'search.foo',
            options: ['Primary' => ['blue' => 'Blue']],
            optionAttributes: ['*' => ['data-foo' => 'val']],
            optgroupAttributes: ['data-bar' => 'val'],
        );
        
        $rendered = $filter->render(Factory::createView());
        $this->assertStringContainsString('<select id="search_foo" aria-label="search.foo" name="search[foo]"><option data-foo="val" value="none">---</option><optgroup data-bar="val" label="Primary"><option data-foo="val" value="blue">Blue</option></optgroup></select>', $rendered);
    }
    
    public function testRenderWithoutLabel()
    {
        $filter = new Filter\Select(name: 'search.foo');
        
        $rendered = $filter->render(Factory::createView());
        $this->assertStringNotContainsString('label for', $rendered);
    }
    
    public function testRendersCustomView()
    {
        $filter = new Filter\Select(name: 'search.foo', view: 'custom/search/filter');
        
        // empty as view does not exist, but we know that it is changeable:
        $this->assertSame('', $filter->render(Factory::createView()));
    }
    
    public function testWithSelectedValue()
    {
        $filter = new Filter\Select(
            name: 'search.foo',
            options: ['blue' => 'Blue', 'red' => 'Red'],
            selected: 'red',
        );
        
        $this->assertSame('red', $filter->value());
        $this->assertSame('red', $filter->selected());
        $this->assertTrue($filter->isActive());
    }
}