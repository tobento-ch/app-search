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
use Tobento\App\Search\SearchResult;
use Tobento\App\Search\SearchResultInterface;
use Tobento\App\Search\SearchResults;
use Tobento\App\Search\SearchResultsInterface;

class SearchResultsTest extends TestCase
{
    public function testConstructorMethod()
    {
        $searchResults = new SearchResults();
        $this->assertInstanceof(SearchResultsInterface::class, $searchResults);
        
        $searchResults = new SearchResults(
            new SearchResult(searchable: 'foo', type: 'type', title: 'Foo', url: 'url'),
        );
        $this->assertSame(1, $searchResults->count());
    }
    
    public function testSearchableMethod()
    {
        $searchResults = new SearchResults(
            new SearchResult(searchable: 'foo', type: 'type', title: 'Foo', url: 'url'),
            new SearchResult(searchable: 'bar', type: 'type', title: 'Bar', url: 'url'),
        );
        
        $searchResultsNew = $searchResults->searchable(name: 'foo');
        
        $this->assertFalse($searchResults === $searchResultsNew);
        $this->assertSame(1, $searchResultsNew->count());
        $this->assertSame(2, $searchResults->count());
    }
    
    public function testFilterMethod()
    {
        $searchResults = new SearchResults(
            new SearchResult(searchable: 'foo', type: 'type', title: 'Foo', url: 'url'),
            new SearchResult(searchable: 'bar', type: 'type', title: 'Bar', url: 'url'),
        );
        
        $filtered = $searchResults->filter(fn(SearchResultInterface $s): bool => $s->title() === 'Bar');
        
        $this->assertFalse($searchResults === $filtered);
        $this->assertSame(2, $searchResults->count());
        $this->assertSame(1, $filtered->count());
    }
    
    public function testAllMethod()
    {
        $searchResults = new SearchResults();
        $this->assertSame([], $searchResults->all());
        
        $searchResult = new SearchResult(searchable: 'foo', type: 'type', title: 'Foo', url: 'url');
        $searchResults = new SearchResults($searchResult);
        $this->assertSame([$searchResult], $searchResults->all());
    }
    
    public function testCountMethod()
    {
        $searchResults = new SearchResults();
        $this->assertSame(0, $searchResults->count());
        
        $searchResults = new SearchResults(new SearchResult(searchable: 'foo', type: 'type', title: 'Foo', url: 'url'));
        $this->assertSame(1, $searchResults->count());
    }
    
    public function testIteration()
    {
        $searchResults = new SearchResults(
            new SearchResult(searchable: 'foo', type: 'type', title: 'Foo', url: 'url'),
            new SearchResult(searchable: 'bar', type: 'type', title: 'Bar', url: 'url'),
        );
        
        foreach($searchResults as $searchResult) {
            $this->assertInstanceof(SearchResultInterface::class, $searchResult);
        }
    }
}