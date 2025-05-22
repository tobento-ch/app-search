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
 * Select filter
 */
class Select implements FilterInterface
{
    /**
     * Create a new Select instance.
     *
     * @param string $name
     * @param string $label
     * @param string $description
     * @param null|string $searchable
     * @param array $options
     * @param string|array $selected
     * @param array $selectAttributes
     * @param array $optionAttributes
     * @param array $optgroupAttributes
     * @param string $view
     */
    final public function __construct(
        protected string $name,
        protected string $label = '',
        protected string $description = '',
        protected null|string $searchable = null,
        protected array $options = [],
        protected string|array $selected = [],
        protected array $selectAttributes = [],
        protected array $optionAttributes = [],
        protected array $optgroupAttributes = [],
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
        return !empty($this->selected());
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
        
        $selected = $input->get($this->name());
        
        if (is_string($selected) && array_key_exists($selected, $this->options())) {
            $this->selected = $selected;
            return;
        }
        
        if (is_array($selected) && $this->isMultipleSelection()) {
            foreach($selected as $value) {
                if (is_string($value) && array_key_exists($value, $this->options())) {
                    $this->selected[] = $value;
                }
            }
        }
    }
    
    /**
     * Returns the value of the filter. Might come from user input. So be careful.
     *
     * @return mixed
     */
    public function value(): mixed
    {
        return $this->selected();
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
        $attributes = $this->selectAttributes;
        $attributes['id'] ??= $form->nameToId($this->name());
        $name = $form->nameToArray($this->name());
        
        if ($this->isMultipleSelection()) {
            $name = $name.'.';
        }
        
        if (empty($this->label) && !isset($attributes['aria-label'])) {
            $attributes['aria-label'] = $this->name();
        }
        
        $body = $form->select(
            name: $name,
            items: $this->options(),
            selected: $this->selected(),
            selectAttributes: $attributes,
            optionAttributes: $this->optionAttributes,
            optgroupAttributes: $this->optgroupAttributes,
            emptyOption: ['none', '---'],
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
    
    /**
     * Returns true if multiple selection, otherwise false.
     *
     * @return bool
     */
    public function isMultipleSelection(): bool
    {
        if (in_array('multiple', $this->selectAttributes)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * Returns the options.
     *
     * @return array
     */
    public function options(): array
    {
        return $this->options;
    }
    
    /**
     * Returns the selected.
     *
     * @return string|array
     */
    public function selected(): string|array
    {
        return $this->selected;
    }
}