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

class SearchResultTest extends TestCase
{
    public function testRequiredOnlyParams()
    {
        $result = new SearchResult(
            searchable: 'name',
            type: 'type',
            title: 'title',
            url: 'url',
        );
        
        $this->assertInstanceof(SearchResultInterface::class, $result);
        $this->assertSame('name', $result->searchable());
        $this->assertSame('type', $result->type());
        $this->assertSame('title', $result->title());
        $this->assertSame('url', $result->url());
        $this->assertSame('', $result->description());
        $this->assertSame(null, $result->image());
        $this->assertSame('', $result->html());
    }
    
    public function testRequiredAllParams()
    {
        $result = new SearchResult(
            searchable: 'name',
            type: 'type',
            title: 'title',
            url: 'url',
            description: 'description',
            image: 'image.jpg',
            html: 'html',
        );
        
        $this->assertInstanceof(SearchResultInterface::class, $result);
        $this->assertSame('name', $result->searchable());
        $this->assertSame('type', $result->type());
        $this->assertSame('title', $result->title());
        $this->assertSame('url', $result->url());
        $this->assertSame('description', $result->description());
        $this->assertSame('image.jpg', $result->image());
        $this->assertSame('html', $result->html());
    }
    
    public function testJsonSerializeMethod()
    {
        $result = new SearchResult(
            searchable: 'name',
            type: 'type',
            title: 'title',
            url: 'url',
            description: 'description',
            image: 'image.jpg',
            html: 'html',
        );

        $this->assertSame([
            'searchable' => 'name',
            'type' => 'type',
            'title' => 'title',
            'url' => 'url',
            'description' => 'description',
            'image' => 'image.jpg',
            'html' => 'html',
        ], $result->jsonSerialize());
    }    
}