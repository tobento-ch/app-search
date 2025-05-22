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

class SearchTermTest extends TestCase
{    
    public function testThatImplementsFilterInterface()
    {
        $this->assertInstanceof(FilterInterface::class, new Filter\SearchTerm(name: 'search.term'));
    }
    
    public function testConstructMethodThrowsInvalidArgumentExceptionIfInvalidNameCharacters()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The filter name search/foo must only contain [a-z-_.] characters');
        
        new Filter\SearchTerm(name: 'search/foo');
    }
    
    public function testNameMethod()
    {
        $this->assertSame('search.term', (new Filter\SearchTerm(name: 'search.term'))->name());
    }
    
    public function testSearchableMethod()
    {
        $this->assertSame(null, (new Filter\SearchTerm(name: 'search.term'))->searchable());
    }
    
    public function testIsActiveMethod()
    {
        $this->assertFalse((new Filter\SearchTerm(name: 'search.term'))->isActive());
    }
    
    public function testIsDisabledMethod()
    {
        $this->assertFalse((new Filter\SearchTerm(name: 'search.term'))->isDisabled());
    }
    
    public function testIsStorableMethod()
    {
        $this->assertTrue((new Filter\SearchTerm(name: 'search.term'))->isStorable());
    }
    
    public function testApplyMethodAppliesValueIfInputExists()
    {
        $filter = new Filter\SearchTerm(name: 'search.term');
        
        $this->assertNull($filter->value());
        
        $filter->apply(
            input: new Input(['search' => ['term' => 'value']]),
            search: new Search(filters: new Filters(), searchables: new Searchables()),
        );
        
        $this->assertSame('value', $filter->value());
        $this->assertSame('value', $filter->searchTerm());
        $this->assertTrue($filter->isActive());
    }
    
    public function testApplyMethodSkipsValueIfNotString()
    {
        $filter = new Filter\SearchTerm(name: 'search.term');
        
        $filter->apply(
            input: new Input(['search' => ['term' => []]]),
            search: new Search(filters: new Filters(), searchables: new Searchables()),
        );
        
        $this->assertNull($filter->value());
        $this->assertNull($filter->searchTerm());
        $this->assertFalse($filter->isActive());
    }
    
    public function testApplyMethodSkipsValueIfEmptyString()
    {
        $filter = new Filter\SearchTerm(name: 'search.term');
        
        $filter->apply(
            input: new Input(['search' => ['term' => '']]),
            search: new Search(filters: new Filters(), searchables: new Searchables()),
        );
        
        $this->assertNull($filter->value());
        $this->assertNull($filter->searchTerm());
        $this->assertFalse($filter->isActive());
    }
    
    public function testValueMethod()
    {
        $this->assertNull((new Filter\SearchTerm(name: 'search.term'))->value());
    }
    
    public function testRenderMethod()
    {
        $filter = new Filter\SearchTerm(
            name: 'search.term',
            label: 'Label',
            description: 'Desc',
        );
        
        $filter->apply(
            input: new Input(['search' => ['term' => 'Foo']]),
            search: new Search(filters: new Filters(), searchables: new Searchables()),
        );
        
        $rendered = $filter->render(Factory::createView());
        $this->assertStringContainsString('Label', $rendered);
        $this->assertStringContainsString('Desc', $rendered);
        $this->assertStringContainsString('<input autocomplete="off" autocorrect="off" spellcheck="false" id="search_term" placeholder="Search" name="search[term]" type="search" value="Foo">', $rendered);
        $this->assertStringContainsString('label for="search_term"', $rendered);
    }
    
    public function testRenderDoesNotSetValueIfNotApplied()
    {
        $filter = new Filter\SearchTerm(name: 'search.term');
        
        $rendered = $filter->render(Factory::createView());
        $this->assertStringContainsString('<input autocomplete="off" autocorrect="off" spellcheck="false" id="search_term" placeholder="Search" name="search[term]" type="search">', $rendered);
    }
    
    public function testRenderWithoutLabel()
    {
        $filter = new Filter\SearchTerm(name: 'search.term');
        
        $rendered = $filter->render(Factory::createView());
        $this->assertStringNotContainsString('label for', $rendered);
    }
    
    public function testRendersCustomView()
    {
        $filter = new Filter\SearchTerm(name: 'search.term', view: 'custom/search/filter');
        
        // empty as view does not exist, but we know that it is changeable:
        $this->assertSame('', $filter->render(Factory::createView()));
    }
    
    public function testWithSearchTerm()
    {
        $filter = new Filter\SearchTerm(name: 'search.term', searchTerm: 'foo');
        
        $this->assertSame('foo', $filter->value());
        $this->assertSame('foo', $filter->searchTerm());
        $this->assertTrue($filter->isActive());
    }
}