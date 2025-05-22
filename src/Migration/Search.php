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

namespace Tobento\App\Search\Migration;

use Tobento\Service\Migration\Action\DirCopy;
use Tobento\Service\Migration\Action\DirDelete;
use Tobento\Service\Migration\Action\FilesCopy;
use Tobento\Service\Migration\Action\FilesDelete;
use Tobento\Service\Migration\Actions;
use Tobento\Service\Migration\ActionsInterface;
use Tobento\Service\Migration\MigrationInterface;
use Tobento\Service\Dir\DirsInterface;

/**
 * Search migration.
 */
class Search implements MigrationInterface
{
    /**
     * @var array The config files.
     */
    protected array $configFiles;
    
    protected array $transFiles;
    
    /**
     * Create a new Search instance.
     *
     * @param DirsInterface $dirs
     */
    public function __construct(
        protected DirsInterface $dirs,
    ) {
        $resources = realpath(__DIR__.'/../../').'/resources/';
        
        $this->configFiles = [
            $this->dirs->get('config') => [
                $resources.'config/search.php',
            ],
        ];
        
        $this->transFiles = [
            $this->dirs->get('trans').'en/' => [
                $resources.'trans/en/en-search.json',
                $resources.'trans/en/routes.search.json',
            ],
            $this->dirs->get('trans').'de/' => [
                $resources.'trans/de/de-search.json',
                $resources.'trans/de/routes.search.json',
            ],
        ];
    }
    
    /**
     * Return a description of the migration.
     *
     * @return string
     */
    public function description(): string
    {
        return 'Search config, view and asset files.';
    }
        
    /**
     * Return the actions to be processed on install.
     *
     * @return ActionsInterface
     */
    public function install(): ActionsInterface
    {
        $resources = realpath(__DIR__.'/../../').'/resources/';
        
        return new Actions(
            new FilesCopy(
                files: $this->configFiles,
                type: 'config',
                description: 'Search config file.',
            ),
            new FilesCopy(
                files: $this->transFiles,
                type: 'trans',
                description: 'Translation files.',
            ),
            new DirCopy(
                dir: $resources.'views/search/',
                destDir: $this->dirs->get('views').'search/',
                name: 'Search views',
                type: 'views',
                description: 'Search views.',
            ),
            new DirCopy(
                dir: $resources.'assets/search/',
                destDir: $this->dirs->get('public').'assets/search/',
                name: 'Search asset files',
                type: 'assets',
                description: 'Search asset files.',
            ),
            new DirCopy(
                dir: $this->dirs->get('vendor').'tobento/css-modal/src/',
                destDir: $this->dirs->get('public').'assets/modal/',
                name: 'Css modal assets',
                type: 'assets',
                description: 'Css Modal assets.',
            ),
        );
    }

    /**
     * Return the actions to be processed on uninstall.
     *
     * @return ActionsInterface
     */
    public function uninstall(): ActionsInterface
    {
        return new Actions(
            new FilesDelete(
                files: $this->configFiles,
                type: 'config',
                description: 'Search config file.',
            ),
            new FilesDelete(
                files: $this->transFiles,
                type: 'trans',
                description: 'Translation files.',
            ),
            new DirDelete(
                dir: $this->dirs->get('views').'search/',
                name: 'Search views',
                type: 'views',
                description: 'Search views.',
            ),
            new DirDelete(
                dir: $this->dirs->get('public').'assets/search/',
                name: 'Search asset files.',
                type: 'assets',
                description: 'Search asset files.',
            ),
        );
    }
}