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

use Tobento\Service\Dir\Dir;
use Tobento\Service\Dir\Dirs;
use Tobento\Service\Form\Form;
use Tobento\Service\Menu\Menu;
use Tobento\Service\Menu\MenuInterface;
use Tobento\Service\Repository\RepositoryInterface;
use Tobento\Service\Repository\Storage\StorageRepository;
use Tobento\Service\Repository\Storage\StorageEntityFactoryInterface;
use Tobento\Service\Repository\Storage\Column\ColumnsInterface;
use Tobento\Service\Storage\StorageInterface;
use Tobento\Service\Storage\InMemoryStorage;
use Tobento\Service\View\Assets;
use Tobento\Service\View\Data;
use Tobento\Service\View\PhpRenderer;
use Tobento\Service\View\View;
use Tobento\Service\View\ViewInterface;

class Factory
{
    public static function createMenu(
        string ...$items,
    ): MenuInterface {
        $menu = new Menu('name');
        
        foreach($items as $item) {
            $menu->link($item, $item);
        }
        
        return $menu;
    }
    
    public static function createStorageRepository(
        string $table,
        iterable|ColumnsInterface $columns,
        null|StorageInterface $storage = null,
        null|StorageEntityFactoryInterface $entityFactory = null,
    ): RepositoryInterface {
        
        if (is_null($storage)) {
            $storage = new  InMemoryStorage(items: []);
        }
        
        return new class(
            $storage,
            $table,
            $columns,
            $entityFactory,
        ) extends StorageRepository {
            //
        };
    }
    
    public static function createView(): ViewInterface
    {
        $view = new View(
            new PhpRenderer(
                new Dirs(
                    new Dir(realpath(__DIR__.'/../resources/views/')),
                )
            ),
            new Data(),
            new Assets('public/assets/', 'https://www.example.com/assets/')
        );
        
        $view->addMacro('form', function() {
            return new Form();
        });
        
        $view->addMacro('etrans', function(string $message) {
            return $message;
        });
        
        $view->addMacro('trans', function(string $message) {
            return $message;
        });
        
        return $view;
    }
}