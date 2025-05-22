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
use Tobento\App\Search\SearchResult;
use Tobento\App\Search\SearchResultInterface;
use Tobento\App\Search\Test\Factory;
use Tobento\Service\Repository\RepositoryInterface;
use Tobento\Service\Repository\Storage\Column;

class RepositoryTest extends TestCase
{
    protected function createRepository(): RepositoryInterface
    {
        return Factory::createStorageRepository(
            table: 'users',
            columns: [
                Column\Id::new(),
                Column\Text::new('email'),
                Column\Text::new('firstname'),
            ],
        );
    }
    
    protected function createRepositoryItems(RepositoryInterface $repository, int $createItems = 10): RepositoryInterface
    {
        for ($i = 1; $i <= $createItems; $i++) {
            $repository->create([
                'email' => sprintf('tom%s@example.com', $i),
                'firstname' => sprintf('Tom%s', $i),
            ]);
        }
        
        return $repository;
    }
    
    protected function createSearchable(RepositoryInterface $repository): Searchable\Repository
    {
        return new Searchable\Repository(
            repository: $repository,
            name: 'users',
            title: 'Users',
            searchAttributes: ['email', 'firstname'],
            toSearchResult: function(object $item, Searchable\Repository $searchable): SearchResultInterface {
                return new SearchResult(
                    searchable: $searchable->name(),
                    type: $searchable->title(),
                    title: $item->get('email'),
                    url: 'url',
                );
            },
            priority: 5,
        );
    }
    
    public function testThatImplementsSearchableInterfaceAndGetterMethods()
    {
        $searchable = $this->createSearchable($this->createRepository());
        
        $this->assertInstanceof(SearchableInterface::class, $searchable);
        $this->assertSame('users', $searchable->name());
        $this->assertSame('Users', $searchable->title());
        $this->assertSame(5, $searchable->priority());
        $this->assertSame(1, count($searchable->filters()));
        $this->assertSame('search.users-page', $searchable->filters()[0]->name());
    }
    
    public function testSearchMethodReturnsCreatedItems()
    {
        $repository = $this->createRepositoryItems(repository: $this->createRepository(), createItems: 1);
        $searchable = $this->createSearchable($repository);
        
        $results = $searchable->search(
            filters: new Filters(),
        );
        
        $this->assertSame('users', $results[0]->searchable());
        $this->assertSame('Users', $results[0]->type());
        $this->assertSame('tom1@example.com', $results[0]->title());
    }
    
    public function testSearchMethodReturnsAllWithoutActiveFilters()
    {
        $repository = $this->createRepositoryItems(repository: $this->createRepository(), createItems: 3);
        $searchable = $this->createSearchable($repository);
        
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
        $repository = $this->createRepositoryItems(repository: $this->createRepository(), createItems: 32);
        $searchable = $this->createSearchable($repository);
        
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
        $repository = $this->createRepositoryItems(repository: $this->createRepository(), createItems: 32);
        $searchable = $this->createSearchable($repository);
        
        $termFilter = new Filter\SearchTerm(name: 'search.term');
        $termFilter->apply(
            input: new Input(['search' => ['term' => 'tom1']]),
            search: new Search(filters: new Filters(), searchables: new Searchables()),
        );
        
        $results = $searchable->search(
            filters: new Filters($termFilter),
        );
        
        $this->assertSame(32, $searchable->totalItems());
        $this->assertSame(11, count($results));
        $this->assertSame(32, $searchable->pagination()->getTotalItems());
    }
    
    public function testSearchMethodReturnsItemsFoundByFilters()
    {
        $repository = $this->createRepositoryItems(repository: $this->createRepository(), createItems: 30);
        $searchable = $this->createSearchable($repository);
        
        $this->assertSame(30, $searchable->totalItems());
        
        $results = $searchable->search(
            filters: new Filters(
                new Filter\SearchTerm(name: 'search.term', searchTerm: '11 22'),
            ),
        );
        
        $this->assertSame(30, $searchable->totalItems());
        
        $this->assertSame(
            ['tom11@example.com', 'tom22@example.com'],
            array_map(fn($result) => $result->title(), $results)
        );
    }
    
    public function testSearchMethodReturnsItemsPaginated()
    {
        $repository = $this->createRepositoryItems(repository: $this->createRepository(), createItems: 30);
        $searchable = $this->createSearchable($repository);
        
        $filters = new Filters(
            new Filter\SearchTerm(name: 'search.term'),
        );
        
        foreach($searchable->filters() as $filter) {
            $filters->add($filter);
        }
        
        $input = new Input(['search' => ['term' => 'tom', 'users-page' => '2']]);
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