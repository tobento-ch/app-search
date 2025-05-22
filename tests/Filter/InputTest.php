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

class InputTest extends TestCase
{    
    public function testThatImplementsFilterInterface()
    {
        $this->assertInstanceof(FilterInterface::class, new Filter\Input(name: 'search.foo'));
    }
    
    public function testConstructMethodThrowsInvalidArgumentExceptionIfInvalidNameCharacters()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The filter name search/foo must only contain [a-z-_.] characters');
        
        new Filter\Input(name: 'search/foo');
    }
    
    public function testNameMethod()
    {
        $this->assertSame('search.foo', (new Filter\Input(name: 'search.foo'))->name());
    }
    
    public function testSearchableMethod()
    {
        $this->assertSame(null, (new Filter\Input(name: 'search.foo'))->searchable());
        $this->assertSame('bar', (new Filter\Input(name: 'search.foo', searchable: 'bar'))->searchable());
    }
    
    public function testIsActiveMethod()
    {
        $this->assertFalse((new Filter\Input(name: 'search.foo'))->isActive());
        $this->assertTrue((new Filter\Input(name: 'search.foo', inputValue: 'bar'))->isActive());
        $this->assertTrue((new Filter\Input(name: 'search.foo', inputValue: ''))->isActive());
    }
    
    public function testIsDisabledMethod()
    {
        $this->assertFalse((new Filter\Input(name: 'search.foo'))->isDisabled());
    }
    
    public function testIsStorableMethod()
    {
        $this->assertTrue((new Filter\Input(name: 'search.foo'))->isStorable());
    }
    
    public function testApplyMethodAppliesValueIfInputExists()
    {
        $filter = new Filter\Input(name: 'search.foo');
        
        $this->assertNull($filter->value());
        
        $filter->apply(
            input: new Input(['search' => ['foo' => 'value']]),
            search: new Search(filters: new Filters(), searchables: new Searchables()),
        );
        
        $this->assertSame('value', $filter->value());
        $this->assertTrue($filter->isActive());
    }
    
    public function testApplyMethodSkipsValueIfNotString()
    {
        $filter = new Filter\Input(name: 'search.foo');
        
        $filter->apply(
            input: new Input(['search' => ['foo' => []]]),
            search: new Search(filters: new Filters(), searchables: new Searchables()),
        );
        
        $this->assertNull($filter->value());
        $this->assertFalse($filter->isActive());
    }
    
    public function testApplyMethodSkipsValueIfEmptyString()
    {
        $filter = new Filter\Input(name: 'search.foo');
        
        $filter->apply(
            input: new Input(['search' => ['foo' => '']]),
            search: new Search(filters: new Filters(), searchables: new Searchables()),
        );
        
        $this->assertNull($filter->value());
        $this->assertFalse($filter->isActive());
    }
    
    public function testValueMethod()
    {
        $this->assertNull((new Filter\Input(name: 'search.foo'))->value());
    }
    
    public function testRenderMethod()
    {
        $filter = new Filter\Input(
            name: 'search.foo',
            label: 'Label',
            description: 'Desc',
        );
        
        $filter->apply(
            input: new Input(['search' => ['foo' => 'Foo']]),
            search: new Search(filters: new Filters(), searchables: new Searchables()),
        );
        
        $rendered = $filter->render(Factory::createView());
        $this->assertStringContainsString('Label', $rendered);
        $this->assertStringContainsString('Desc', $rendered);
        $this->assertStringContainsString('<input id="search_foo" name="search[foo]" type="text" value="Foo">', $rendered);
        $this->assertStringContainsString('label for="search_foo"', $rendered);
    }
    
    public function testRenderDoesNotSetValueIfNotApplied()
    {
        $filter = new Filter\Input(name: 'search.foo');
        
        $rendered = $filter->render(Factory::createView());
        $this->assertStringContainsString('<input id="search_foo" aria-label="search.foo" name="search[foo]" type="text">', $rendered);
    }
    
    public function testRenderWithAttributes()
    {
        $filter = new Filter\Input(
            name: 'search.foo',
            inputAttributes: ['placeholder' => 'value', 'required', 'data-foo' => ['key' => 'val']],
        );
        
        $rendered = $filter->render(Factory::createView());
        $this->assertStringContainsString('<input placeholder="value" required data-foo=\'{&quot;key&quot;:&quot;val&quot;}\' id="search_foo" aria-label="search.foo" name="search[foo]" type="text">', $rendered);
    }
    
    public function testRenderWithoutLabel()
    {
        $filter = new Filter\Input(name: 'search.foo');
        
        $rendered = $filter->render(Factory::createView());
        $this->assertStringNotContainsString('label for', $rendered);
    }
    
    public function testRendersCustomView()
    {
        $filter = new Filter\Input(name: 'search.foo', view: 'custom/search/filter');
        
        // empty as view does not exist, but we know that it is changeable:
        $this->assertSame('', $filter->render(Factory::createView()));
    }
}