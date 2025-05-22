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

namespace Tobento\App\Search;

use Tobento\Service\View\ViewInterface;

/**
 * FilterInterface
 */
interface FilterInterface
{
    /**
     * Returns the filter name.
     *
     * @return string
     */
    public function name(): string;
    
    /**
     * Returns the searchable the filter belongs to.
     *
     * @return null|string
     */
    public function searchable(): null|string;

    /**
     * Returns whether the filter is active or not.
     *
     * @return bool
     */
    public function isActive(): bool;
    
    /**
     * Return whether the filter is disabled or not.
     *
     * @return bool
     */
    public function isDisabled(): bool;
    
    /**
     * Returns whether the filter is storable or not.
     *
     * @return bool
     */
    public function isStorable(): bool;
    
    /**
     * Applies the data to filter.
     *
     * @param InputInterface $input Might come from user input. So be careful.
     * @param SearchInterface $search
     * @return void
     */
    public function apply(InputInterface $input, SearchInterface $search): void;
    
    /**
     * Returns the value of the filter. Might come from user input. So be careful.
     *
     * @return mixed
     */
    public function value(): mixed;
    
    /**
     * Returns the rendered filter.
     *
     * @param ViewInterface $view
     * @return string
     */
    public function render(ViewInterface $view): string;
}