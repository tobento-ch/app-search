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

namespace Tobento\App\Search\Test\Feature;

use Tobento\App\AppInterface;
use Tobento\App\Testing\Http\AssertableJson;
use Tobento\Service\Menu\MenusInterface;
use Tobento\Service\Responser\ResponserInterface;
use Tobento\Service\Routing\RouterInterface;
use Tobento\Service\View\ViewInterface;

class SearchTest extends \Tobento\App\Testing\TestCase
{
    public function createApp(): AppInterface
    {
        $app = $this->createTmpApp(rootDir: __DIR__.'/../..');
        $app->boot(\Tobento\App\Search\Boot\Search::class);
        
        $app->on(MenusInterface::class, function(MenusInterface $menus) {
            $menu = $menus->menu('main');
            for ($i = 1; $i <= 75; $i++) {
                $menu->link('menuitem'.$i, 'Menuitem'.$i);
            }
        });
        
        // searchbar route for testing:
        $app->on(RouterInterface::class, static function(RouterInterface $router): void {
            $router->get('searchbar', function (ResponserInterface $responser, ViewInterface $view) {
                return $responser->html(
                    html: $view->render('search.bar'),
                    code: 200,
                );
            })->name('searchbar');
        });
        
        return $app;
    }
    
    public function testSearchScreenIsRendered()
    {
        $http = $this->fakeHttp();
        $http->request(method: 'GET', uri: 'search');
        $response = $http->response()
            ->assertStatus(200)
            ->assertBodyContains('Search')
            ->assertBodyContains('<form action method="GET" data-search-filters="main">')
            ->assertBodyContains('<input autocomplete="off" autocorrect="off" spellcheck="false" id="search_term" placeholder="Search" name="search[term]" type="search">')
            ->assertBodyContains('Menu Items')
            ->assertBodyContains('show more Menu Items | 50 remaining');
        
        $this->assertCount(25, $response->crawl()->filter('.search-result'));
    }
    
    public function testSearchScreenDisplaysOnlyFilteredSearchItems()
    {
        $http = $this->fakeHttp();
        $http->request(
            method: 'GET',
            uri: 'search',
            query: ['search' => ['term' => 'item1']],
        );
        
        $response = $http->response()
            ->assertStatus(200)
            ->assertBodyContains('11 Menu Items found')
            ->assertBodyNotContains('No results found!');
        
        $this->assertCount(11, $response->crawl()->filter('.search-result'));
    }
    
    public function testSearchScreenDisplaysNoSearchResultsFound()
    {
        $http = $this->fakeHttp();
        $http->request(
            method: 'GET',
            uri: 'search',
            query: ['search' => ['term' => 'abc']],
        );
        
        $response = $http->response()
            ->assertStatus(200)
            ->assertBodyContains('No results found!');
        
        $this->assertCount(0, $response->crawl()->filter('.search-result'));
    }
    
    public function testSearchScreenOnlyDisplaysSelectedSearchablesSearchResults()
    {
        $http = $this->fakeHttp();
        $http->request(
            method: 'GET',
            uri: 'search',
            query: ['search' => ['searchables' => ['_none']]],
        );
        
        $response = $http->response()
            ->assertStatus(200)
            ->assertBodyContains('No results found!');
        
        $this->assertCount(0, $response->crawl()->filter('.search-result'));
    }
    
    public function testSearchReturnsJsonIfRequested()
    {
        $http = $this->fakeHttp();
        $http->request(
            method: 'GET',
            uri: 'search',
            headers: [
                'X-Requested-With' => 'XMLHttpRequest',
                'Accept' => 'application/json',
            ],
        );
        
        $response = $http->response()
            ->assertStatus(200)
            ->assertJson(fn (AssertableJson $json) =>
                $json->has(key: 'html')
            );
    }
    
    public function testSearchbarIsRendered()
    {
        $http = $this->fakeHttp();
        $http->request(method: 'GET', uri: 'searchbar');
        $response = $http->response()
            ->assertStatus(200)
            ->assertBodyContains('<form action="http://localhost/search" method="GET" data-searchbar="modal" class="min-width-full">')
            ->assertBodyContains('<input class="small" autocomplete="off" autocorrect="off" spellcheck="false" aria-label="Search" name="search[term]" type="search">');
    }
}