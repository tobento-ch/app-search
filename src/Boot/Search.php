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
 
namespace Tobento\App\Search\Boot;

use Psr\Container\ContainerInterface;
use Tobento\App\Boot;
use Tobento\App\Boot\Config;
use Tobento\App\Migration\Boot\Migration;

/**
 * Search boot
 */
class Search extends Boot
{
    public const INFO = [
        'boot' => [
            'migrates search config, view and asset files',
            'implements search interfaces based on the search config file',
            'boots features defined in config file',
        ],
    ];

    public const BOOT = [
        Config::class,
        Migration::class,
        
        // HTTP:
        \Tobento\App\Http\Boot\Routing::class,
        \Tobento\App\Http\Boot\RequesterResponser::class,
        \Tobento\App\Http\Boot\Cookies::class,
        
        // I18n:
        \Tobento\App\Language\Boot\Language::class,
        \Tobento\App\Translation\Boot\Translation::class,
        
        // VIEW:
        \Tobento\App\View\Boot\View::class,
        \Tobento\App\View\Boot\Form::class,
    ];

    /**
     * Boot application services.
     *
     * @param Config $config
     * @param Migration $migration
     * @return void
     */
    public function boot(Config $config, Migration $migration): void
    {
        // install migration:
        $migration->install(\Tobento\App\Search\Migration\Search::class);
        
        // load the search config:
        $config = $config->load('search.php');
        
        // setting interfaces:
        foreach($config['interfaces'] ?? [] as $interface => $implementation) {
            $this->app->set($interface, $implementation);
        }
        
        // features:
        foreach($config['features'] ?? [] as $feature) {
            $this->app->call($feature);
        }
    }
}