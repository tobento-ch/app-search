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
 
namespace Tobento\App\Search\Feature;

use Psr\Http\Message\ResponseInterface;
use Tobento\App\AppInterface;
use Tobento\App\Language\RouteLocalizerInterface;
use Tobento\App\Search\InputInterface;
use Tobento\App\Search\SearchInterface;
use Tobento\Service\Routing\RouterInterface;
use Tobento\Service\Requester\RequesterInterface;
use Tobento\Service\Responser\ResponserInterface;
use Tobento\Service\View\ViewInterface;

/**
 * Search feature.
 */
class Search
{
    /**
     * Create a new Search instance.
     *
     * @param string $view
     * @param string $viewSearchbar
     * @param string $viewSearchbarResults
     * @param string $searchbarInputPlaceholder
     * @param bool $localizeRoute
     */
    public function __construct(
        protected string $view = 'search/index',
        protected string $viewSearchbar = 'search/searchbar',
        protected string $viewSearchbarResults = 'search/searchbar-results',
        protected string $searchbarInputPlaceholder = 'Search',
        protected bool $localizeRoute = false,
    ) {}
    
    /**
     * Boot the feature.
     *
     * @param RouterInterface $router
     * @param AppInterface $app
     * @return void
     */
    public function __invoke(
        RouterInterface $router,
        AppInterface $app,
    ): void {
        // Routes:
        $route = $router->get(
            uri: $this->localizeRoute ? '{?locale}/{search}' : 'search', 
            handler: [$this, 'show'],
        )->name('search');
        
        if ($this->localizeRoute) {
            $app->get(RouteLocalizerInterface::class)->localizeRoute($route, 'search');
        }
        
        $this->configureRoutes($router, $app);
        
        $app->on(
            ViewInterface::class,
            function(ViewInterface $view) {
                $viewName = $this->viewSearchbar;
                $placeholder = $this->searchbarInputPlaceholder;
                $view->on(
                    'search.bar', 
                    static function(array $data, ViewInterface $view, string $key) use ($viewName, $placeholder): array {
                        $view->add(key: $key, view: $viewName);
                        $data['inputPlaceholder'] = $placeholder;
                        return $data;
                    }
                );
            }
        );
    }
    
    /**
     * Handle the search.
     *
     * @param SearchInterface $searchService
     * @param InputInterface $input
     * @param RequesterInterface $requester
     * @param ResponserInterface $responser
     * @return ResponseInterface
     */
    public function show(
        SearchInterface $searchService,
        InputInterface $input,
        RequesterInterface $requester,
        ResponserInterface $responser,
    ): ResponseInterface {
        if ($requester->wantsJson()) {
            $response = $responser->render(
                view: $this->viewSearchbarResults,
                data: [
                    'searchResults' => $searchService->search($input),
                    'searchFilters' => $searchService->filters(),
                    'searchables' => $searchService->searchables(),
                ],
            );
            
            return $responser->json([
                'status' => 200,
                'html' => (string)$response->getBody(),
            ]);
        }
        
        return $responser->render(
            view: $this->view,
            data: [
                'searchResults' => $searchService->search($input),
                'searchFilters' => $searchService->filters(),
                'searchables' => $searchService->searchables(),
            ],
        );
    }
    
    /**
     * Configure routes
     *
     * @param RouterInterface $router
     * @param AppInterface $app
     * @return void
     */
    protected function configureRoutes(RouterInterface $router, AppInterface $app): void
    {
        // $router->getRoute(name: 'search')->middleware();
    }
}