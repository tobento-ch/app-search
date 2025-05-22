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

namespace Tobento\App\Search\Searchable;

use Tobento\App\Search\Filter;
use Tobento\App\Search\FilterInterface;
use Tobento\App\Search\FiltersInterface;
use Tobento\App\Search\SearchableInterface;
use Tobento\App\Search\SearchResult;
use Tobento\App\Search\SearchResultInterface;
use Tobento\Service\Menu\ItemInterface;
use Tobento\Service\Menu\MenuInterface;
use Tobento\Service\Menu\Link;
use Tobento\Service\Pagination\Pagination;
use Tobento\Service\Pagination\PaginationInterface;
use Tobento\Service\Pagination\UrlGenerator;

class Menu implements SearchableInterface
{
    /**
     * @var MenuInterface
     */
    protected MenuInterface $menu;
    
    /**
     * @var null|PaginationInterface
     */
    protected null|PaginationInterface $pagination = null;
    
    /**
     * Create a new Menu instance.
     *
     * @param MenuInterface $menu
     * @param string $name
     * @param string $title
     * @param int $priority
     */
    public function __construct(
        MenuInterface $menu,
        protected string $name = 'menu',
        protected string $title = 'Menu Items',
        protected int $priority = 0,
    ) {
        $this->menu = clone $menu;
        
        $this->menu->filter(function(ItemInterface $item): bool {
            return $item instanceof Link;
        });
        
        if ((bool) preg_match('/^[a-z-_.]+$/u', $name) === false) {
            throw new \InvalidArgumentException(
                sprintf('The name %s must only contain [a-z-_.] characters', $name)
            );
        }
    }
    
    /**
     * Returns the name.
     *
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }
    
    /**
     * Returns the title.
     *
     * @return string
     */
    public function title(): string
    {
        return $this->title;
    }
    
    /**
     * Returns the priority.
     *
     * @return int
     */
    public function priority(): int
    {
        return $this->priority;
    }
    
    /**
     * Returns the specific filters for the searchable.
     *
     * @return array<array-key, FilterInterface>
     */
    public function filters(): array
    {
        return [
            new Filter\Pagination(
                name: sprintf('search.%s-page', $this->name()),
                searchable: $this->name(),
                pagination: $this->pagination(),
            ),
        ];
    }
    
    /**
     * Returns the search results found.
     *
     * @param FiltersInterface $filters
     * @return array<array-key, SearchResultInterface>
     */
    public function search(FiltersInterface $filters): array
    {
        foreach($filters as $filter) {
            switch ($filter::class) {
                case Filter\SearchTerm::class:
                    if (empty($filter->searchTerm())) {
                        break;
                    }
                    
                    $this->menu->filter(function(ItemInterface $item) use ($filter): bool {
                        foreach(explode(' ', $filter->searchTerm()) as $value) {
                            if (stripos($item->text(), $value) !== false) {
                                return true;
                            }
                        }
                        
                        return false;
                    });
                    break;
            }
        }
        
        $menuItems = $this->menu->all();
        
        if ($filters->has(sprintf('search.%s-page', $this->name()))) {
            $itemsCount = count($menuItems);
            $paginationFilter = $filters->get(sprintf('search.%s-page', $this->name()));
            $paginationFilter->updatePaginationTotalItems($itemsCount);
            $this->pagination = $paginationFilter->pagination();
            $limit = $this->pagination->getItemsPerPage();
            $offset = $this->pagination->getItemsOffset();
            $menuItems = array_slice($menuItems, $offset, $limit, true);
        } else {
            $limit = $this->pagination()->getItemsPerPage();
            $offset = $this->pagination()->getItemsOffset();
            $menuItems = array_slice($menuItems, $offset, $limit, true);
        }
        
        $results = [];
        
        foreach($menuItems as $item) {
            $results[] = new SearchResult(
                searchable: $this->name(),
                type: $this->title(),
                title: $item->text(),
                url: $item->url(),
            );
        }
        
        return $results;
    }
    
    /**
     * Returns the total items.
     *
     * @return int
     */
    public function totalItems(): int
    {
        return $this->pagination()->getTotalItems();
    }
    
    /**
     * Returns the pagination.
     *
     * @return PaginationInterface
     */
    public function pagination(): PaginationInterface
    {
        if ($this->pagination) {
            return $this->pagination;
        }

        return $this->pagination = new Pagination(
            totalItems: count($this->menu->all()),
            currentPage: 1,
            itemsPerPage: 25,
            maxPagesToShow: 6,
            maxItemsPerPage: 100,
            urlGenerator: (new UrlGenerator())->addPageUrl(sprintf('?search[%s-page]={num}', $this->name())),
        );
    }
}