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
use Tobento\Service\Pagination\Pagination;

class PaginationTest extends TestCase
{    
    public function testThatImplementsFilterInterface()
    {
        $this->assertInstanceof(
            FilterInterface::class,
            new Filter\Pagination(name: 'search.page', searchable: null, pagination: new Pagination(totalItems: 5))
        );
    }
    
    public function testConstructMethodThrowsInvalidArgumentExceptionIfInvalidNameCharacters()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The filter name search/page must only contain [a-z-_.] characters');
        
        new Filter\Pagination(name: 'search/page', searchable: null, pagination: new Pagination(totalItems: 5));
    }

    public function testPaginationMethod()
    {
        $pagination = new Pagination(totalItems: 5);
        
        $filter = new Filter\Pagination(
            name: 'search.page',
            searchable: null,
            pagination: $pagination,
        );
        
        $this->assertTrue($pagination === $filter->pagination());
    }
    
    public function testUpdatePaginationTotalItemsMethod()
    {
        $pagination = new Pagination(totalItems: 5);
        
        $filter = new Filter\Pagination(
            name: 'search.page',
            searchable: null,
            pagination: $pagination,
        );
        
        $filter->updatePaginationTotalItems(totalItems: 10);
        
        $this->assertFalse($pagination === $filter->pagination());
        $this->assertSame(10, $filter->pagination()->getTotalItems());
    }
    
    public function testUpdatePaginationTotalItemsMethodSetsCurrentPageToOneIfInvalid()
    {
        $pagination = new Pagination(totalItems: 100, itemsPerPage: 10, currentPage: 9);
        
        $filter = new Filter\Pagination(
            name: 'search.page',
            searchable: null,
            pagination: $pagination,
        );
        
        $filter->updatePaginationTotalItems(totalItems: 10);
        
        $this->assertSame(9, $pagination->getCurrentPage());
        $this->assertSame(10, $filter->pagination()->getTotalItems());
        $this->assertSame(1, $filter->pagination()->getCurrentPage());
    }
    
    public function testNameMethod()
    {
        $filter = new Filter\Pagination(
            name: 'search.page',
            searchable: null,
            pagination: new Pagination(totalItems: 5),
        );
        
        $this->assertSame('search.page', $filter->name());
    }
    
    public function testSearchableMethod()
    {
        $this->assertSame(
            null,
            (new Filter\Pagination(name: 'page', searchable: null, pagination: new Pagination(totalItems: 5)))->searchable()
        );
        
        $this->assertSame(
            'bar',
            (new Filter\Pagination(name: 'page', searchable: 'bar', pagination: new Pagination(totalItems: 5)))->searchable()
        );
    }
    
    public function testIsActiveMethod()
    {
        $this->assertTrue(
            (new Filter\Pagination(name: 'page', searchable: null, pagination: new Pagination(totalItems: 5)))->isActive()
        );
        
        $this->assertFalse(
            (new Filter\Pagination(name: 'page', searchable: null, pagination: new Pagination(totalItems: 0)))->isActive()
        );
    }
    
    public function testIsDisabledMethod()
    {
        $this->assertFalse(
            (new Filter\Pagination(name: 'page', searchable: null, pagination: new Pagination(totalItems: 5)))->isDisabled()
        );
    }
    
    public function testIsStorableMethod()
    {
        $this->assertFalse(
            (new Filter\Pagination(name: 'page', searchable: null, pagination: new Pagination(totalItems: 5)))->isStorable()
        );
    }
    
    public function testApplyMethodAppliesCurrentPageIfInputExistsAndValid()
    {
        $filter = new Filter\Pagination(
            name: 'search.page',
            searchable: null,
            pagination: new Pagination(totalItems: 100, itemsPerPage: 10),
        );
        
        $this->assertSame(1, $filter->value());
        
        $filter->apply(
            input: new Input(['search' => ['page' => '3']]),
            search: new Search(filters: new Filters(), searchables: new Searchables()),
        );
        
        $this->assertSame(3, $filter->value());
        $this->assertTrue($filter->isActive());
    }
    
    public function testApplyMethodSkipsPageIfNotValid()
    {
        $filter = new Filter\Pagination(
            name: 'search.page',
            searchable: null,
            pagination: new Pagination(totalItems: 100, itemsPerPage: 10),
        );
        
        $filter->apply(
            input: new Input(['search' => ['page' => []]]),
            search: new Search(filters: new Filters(), searchables: new Searchables()),
        );
        
        $this->assertSame(1, $filter->value());
        $this->assertTrue($filter->isActive());
    }
    
    public function testApplyMethodSetsCurrentPageToOneIfInvalid()
    {
        $filter = new Filter\Pagination(
            name: 'search.page',
            searchable: null,
            pagination: new Pagination(totalItems: 100, itemsPerPage: 10, currentPage: 3),
        );
        
        $this->assertSame(3, $filter->value());
        
        $filter->apply(
            input: new Input(['search' => ['page' => '200']]),
            search: new Search(filters: new Filters(), searchables: new Searchables()),
        );
        
        $this->assertSame(1, $filter->value());
        $this->assertTrue($filter->isActive());
    }
    
    public function testRenderMethodReturnsEmptyString()
    {
        $filter = new Filter\Pagination(
            name: 'search.page',
            searchable: null,
            pagination: new Pagination(totalItems: 5),
        );
        
        $this->assertStringContainsString('', $filter->render(Factory::createView()));
    }
}