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

use Closure;
use Tobento\App\Search\Filter;
use Tobento\App\Search\FilterInterface;
use Tobento\App\Search\FiltersInterface;
use Tobento\App\Search\SearchableInterface;
use Tobento\App\Search\SearchResult;
use Tobento\App\Search\SearchResultInterface;
use Tobento\Service\Pagination\Pagination;
use Tobento\Service\Pagination\PaginationInterface;
use Tobento\Service\Pagination\UrlGenerator;
use Tobento\Service\Repository\RepositoryInterface;

class Repository implements SearchableInterface
{
    /**
     * @var null|PaginationInterface
     */
    protected null|PaginationInterface $pagination = null;
    
    /**
     * Create a new Repository instance.
     *
     * @param RepositoryInterface $repository
     * @param string $name
     * @param string $title
     * @param array<array-key, string> $searchAttributes
     * @param Closure $toSearchResult
     * @param int $priority
     */
    public function __construct(
        protected RepositoryInterface $repository,
        protected string $name,
        protected string $title,
        protected array $searchAttributes,
        protected Closure $toSearchResult,
        protected int $priority = 0,
    ) {
        if ((bool) preg_match('/^[a-z-_.]+$/u', $name) === false) {
            throw new \InvalidArgumentException(
                sprintf('The searchable name %s must only contain [a-z-_.] characters', $name)
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
        $where = [];
        $orderBy = [];
        $limit = [$this->pagination()->getItemsPerPage(), $this->pagination()->getItemsOffset()];
        
        foreach($filters as $filter) {
            switch ($filter::class) {
                case Filter\SearchTerm::class:
                    if (empty($filter->searchTerm())) {
                        break;
                    }
                    
                    // e.g. 'title' => ['or like' => ['%bar%', '%foo%']],
                    foreach($this->searchAttributes as $searchAttribute) {
                        foreach(explode(' ', $filter->searchTerm()) as $value) {
                            $where[$searchAttribute]['or like'][] = '%'.$value.'%';
                        }
                    }
                    
                    break;
            }
        }
        
        if ($filters->has(sprintf('search.%s-page', $this->name()))) {
            $paginationFilter = $filters->get(sprintf('search.%s-page', $this->name()));
            $paginationFilter->updatePaginationTotalItems($this->repository->count(where: $where));
            $this->pagination = $paginationFilter->pagination();
            $limit = [$this->pagination->getItemsPerPage(), $this->pagination->getItemsOffset()];
        }
        
        $results = [];
        
        foreach($this->repository->findAll(where: $where, orderBy: $orderBy, limit: $limit) as $item) {
            $results[] = call_user_func($this->toSearchResult, $item, $this);
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
            totalItems: $this->repository->count(),
            currentPage: 1,
            itemsPerPage: 25,
            maxPagesToShow: 6,
            maxItemsPerPage: 100,
            urlGenerator: (new UrlGenerator())->addPageUrl(sprintf('?search[%s-page]={num}', $this->name())),
        );
    }
}