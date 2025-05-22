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
use Tobento\Service\Tag\Attributes;
use Tobento\Service\View\ViewInterface;

/**
 * Clear filter
 */
class Clear implements FilterInterface
{
    /**
     * Create a new Clear instance.
     *
     * @param string $name
     * @param string $label
     * @param array $attributes
     * @param string $view
     */
    final public function __construct(
        protected string $name,
        protected string $label = 'Clear all',
        protected array $attributes = [],
    ) {
        if ((bool) preg_match('/^[a-z-_.]+$/u', $name) === false) {
            throw new \InvalidArgumentException(
                sprintf('The filter name %s must only contain [a-z-_.] characters', $name)
            );
        }
        
        if (!str_ends_with($name, 'clear')) {
            throw new \InvalidArgumentException(
                sprintf('The filter name %s must end with "clear"', $name)
            );
        }
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
        return null;
    }

    /**
     * Returns whether the filter is active or not.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return false;
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
        //
    }
    
    /**
     * Returns the value of the filter. Might come from user input. So be careful.
     *
     * @return mixed
     */
    public function value(): mixed
    {
        return null;
    }
    
    /**
     * Returns the rendered filter.
     *
     * @param ViewInterface $view
     * @return string
     * @psalm-suppress UndefinedInterfaceMethod
     */
    public function render(ViewInterface $view): string
    {
        $form = $view->form();
        $href = sprintf('?%s=1', $form->nameToArray($this->name()));
        
        $attributes = new Attributes($this->attributes);
        
        if (! $attributes->has('class')) {
            $attributes->add('class', 'button raw text-xs');
        }
        
        $attributes->add('href', $href);
        $attributes->add('data-search-filter', $this->name());
        $attributes->add('data-search-action', 'clear');
        
        $html = '<a'.$attributes.'>';
        $html .= $view->esc($this->label);
        $html .= '</a>';
        return $html;
    }
}