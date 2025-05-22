<?php

/**
 * TOBENTO
 *
 * @copyright   Tobias Strub, TOBENTO
 * @license     MIT License, see LICENSE file distributed with this source code.
 * @author      Tobias Strub
 * @link        https://www.tobento.ch
 */

use Psr\Container\ContainerInterface;
use Tobento\App\Search\Feature;
use Tobento\App\Search\Search;
use Tobento\App\Search\Searchable;
use Tobento\App\Search\Searchables;
use Tobento\App\Search\SearchInterface;
use Tobento\App\Search\Filter;
use Tobento\App\Search\Filters;
use Tobento\App\Search\InputFactory;
use Tobento\App\Search\InputInterface;
use function Tobento\App\Translation\{trans};

return [

    /*
    |--------------------------------------------------------------------------
    | Features
    |--------------------------------------------------------------------------
    |
    | Specify and configure the features you wish to use or remove unneeded.
    |
    | See: https://github.com/tobento-ch/app-search#features
    |
    */
    
    'features' => [
        Feature\Search::class,
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Interfaces
    |--------------------------------------------------------------------------
    |
    | Do not change the interface's names!
    |
    */
    
    'interfaces' => [
        InputInterface::class => static function (InputFactory $factory): InputInterface {
            return $factory->createInput([
                'name' => 'search',
                'storage' => 'cookie',
                // The duration in seconds until the cookie will expire.
                'lifetime' => null, // null|int
                'sameSite' => 'Strict',
            ]);
        },
        
        SearchInterface::class => static function(ContainerInterface $container): SearchInterface {
            return new Search(
                // Name your filters starting with "search." as configured on the InputInterface::class!
                filters: new Filters(
                    new Filter\SearchTerm(name: 'search.term', placeholder: trans('Search')),
                    new Filter\Clear(name: 'search.clear', label: trans('Clear all')),
                    new Filter\Searchables(name: 'search.searchables', label: trans('Content')),
                ),
                searchables: new Searchables(
                    new Searchable\Menu(
                        menu: $container->get(\Tobento\Service\Menu\MenusInterface::class)->menu('main'),
                        title: trans('Menu Items'),
                    ),
                ),
            );
        },
    ],
];