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
use Tobento\Service\View\ViewInterface;

/**
 * Input filter
 */
class Input implements FilterInterface
{
    /**
     * Create a new SearchTerm instance.
     *
     * @param string $name
     * @param string $label
     * @param string $description
     * @param null|string $searchable
     * @param string $inputType
     * @param array $inputAttributes
     * @param null|string $inputValue
     * @param string $view
     */
    final public function __construct(
        protected string $name,
        protected string $label = '',
        protected string $description = '',
        protected null|string $searchable = null,
        protected string $inputType = 'text',
        protected array $inputAttributes = [],
        protected null|string $inputValue = null,
        protected string $view = 'search/filter',
    ) {
        if ((bool) preg_match('/^[a-z-_.]+$/u', $name) === false) {
            throw new \InvalidArgumentException(
                sprintf('The filter name %s must only contain [a-z-_.] characters', $name)
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
        return $this->searchable;
    }

    /**
     * Returns whether the filter is active or not.
     *
     * @return bool
     */
    public function isActive(): bool
    {
        return is_null($this->value()) ? false : true;
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
        return true;
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
        if (! $input->has($this->name())) {
            return;
        }
        
        $inputValue = $input->get($this->name());
        
        if (is_string($inputValue) && $inputValue !== '') {
            $this->inputValue = $inputValue;
        }
    }
    
    /**
     * Returns the value of the filter. Might come from user input. So be careful.
     *
     * @return mixed
     */
    public function value(): mixed
    {
        return $this->inputValue;
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
        $attributes = $this->inputAttributes;
        $attributes['id'] ??= $form->nameToId($this->name());
        
        if (empty($this->label) && !isset($attributes['aria-label'])) {
            $attributes['aria-label'] = $this->name();
        }
        
        $body = $form->input(
            name: $form->nameToArray($this->name()),
            type: $this->inputType,
            value: $this->value(),
            attributes: $attributes,
            selected: null,
            withInput: true,
        );
        
        return $view->render(
            view: $this->view,
            data: [
                'name' => $this->name(),
                'label' => $this->label,
                'labelFor' => $this->label ? $attributes['id'] : '',
                'body' => $body, // must be escaped!
                'description' => $this->description,
                'filter' => $this,
            ],
        );
    }
}