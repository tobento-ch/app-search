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

namespace Tobento\App\Search\Filter;

use Tobento\App\Search\FilterInterface;
use Tobento\App\Search\SearchInterface;
use Tobento\App\Search\InputInterface;
use Tobento\Service\Pagination\PaginationInterface;
use Tobento\Service\View\ViewInterface;

/**
 * Pagination filter
 */
class Pagination implements FilterInterface
{
    /**
     * @var null|string
     */
    protected null|string $searchTerm = null;
    
    /**
     * Create a new Pagination instance.
     *
     * @param string $name
     * @param null|string $searchable
     * @param PaginationInterface $pagination
     */
    final public function __construct(
        protected string $name,
        protected null|string $searchable,
        protected PaginationInterface $pagination,
    ) {
        if ((bool) preg_match('/^[a-z-_.]+$/u', $name) === false) {
            throw new \InvalidArgumentException(
                sprintf('The filter name %s must only contain [a-z-_.] characters', $name)
            );
        }
    }
    
    /**
     * Returns the pagination.
     *
     * @return PaginationInterface
     */
    public function pagination(): PaginationInterface
    {
        return $this->pagination;
    }
    
    /**
     * Updates the pagination total number of items.
     *
     * @param PaginationInterface $pagination
     * @return static $this
     */
    public function updatePaginationTotalItems(int $totalItems): static
    {
        $this->pagination = $this->pagination->withTotalItems($totalItems);
        
        if (! $this->pagination->hasCurrentPage()) {
            $this->pagination = $this->pagination->withCurrentPage(1);
        }
        
        return $this;
    }
    
    /**
     * Returns the filter name.
     *
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }
    
    /**
     * Returns the searchable the filter belongs to.
     *
     * @return null|string
     */
    public function searchable(): null|string
    {
        return $this->searchable;
    }

    /**
     * Returns whether the filter is active or not.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->pagination()->hasPages();
    }
    
    /**
     * Return whether the filter is disabled or not.
     *
     * @return bool
     */
    public function isDisabled(): bool
    {
        return false;
    }
    
    /**
     * Returns whether the filter is storable or not.
     *
     * @return bool
     */
    public function isStorable(): bool
    {
        return false;
    }
    
    /**
     * Applies the data to filter.
     *
     * @param InputInterface $input Might come from user input. So be careful.
     * @param SearchInterface $search
     * @return void
     */
    public function apply(InputInterface $input, SearchInterface $search): void
    {
        $page = $input->get($this->name());
        $page = is_scalar($page) ? (int)$page : 1;
        
        $this->pagination = $this->pagination->withCurrentPage($page);
        
        if (! $this->pagination->hasCurrentPage()) {
            $this->pagination = $this->pagination->withCurrentPage(1);
        }
    }
    
    /**
     * Returns the value of the filter. Might come from user input. So be careful.
     *
     * @return mixed
     */
    public function value(): mixed
    {
        return $this->pagination->getCurrentPage();
    }
    
    /**
     * Returns the rendered filter.
     *
     * @param ViewInterface $view
     * @return string
     */
    public function render(ViewInterface $view): string
    {
        // Do not render at all so as to refresh
        // if other filters are changed.
        return '';
    }
}