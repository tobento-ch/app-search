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
 * Searchables filter
 */
class Searchables implements FilterInterface
{
    /**
     * @var array<string, string>
     */
    protected array $searchables = [];
    
    /**
     * @var array<array-key, string>
     */
    protected array $selectedSearchables = [];
    
    /**
     * Create a new Searchables instance.
     *
     * @param string $name
     * @param string $label
     * @param string $description
     * @param string $view
     */
    final public function __construct(
        protected string $name,
        protected string $label = 'Content',
        protected string $description = '',
        protected string $view = 'search/filter',
    ) {
        if ((bool) preg_match('/^[a-z-_.]+$/u', $name) === false) {
            throw new \InvalidArgumentException(
                sprintf('The filter name %s must only contain [a-z-_.] characters', $name)
            );
        }
    }
    
    /**
     * Returns the searchables.
     *
     * @return array<string, string>
     */
    public function searchables(): array
    {
        return $this->searchables;
    }
    
    /**
     * Returns the searchables.
     *
     * @return array<array-key, string>
     */
    public function selectedSearchables(): array
    {
        return $this->selectedSearchables;
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
        return !empty($this->selectedSearchables());
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
        foreach($search->searchables() as $searchable) {
            $this->searchables[$searchable->name()] = $searchable->title();
        }
        
        $selected = $input->get($this->name(), []);
                
        if (!is_array($selected)) {
            $selected = [];
        }
        
        // By default, all are selected:
        if (empty($selected)) {
            $selected = array_keys($this->searchables);
        }
        
        foreach($selected as $value) {
            if (is_string($value) && array_key_exists($value, $this->searchables)) {
                $this->selectedSearchables[] = $value;
            }
        }

        foreach(array_keys($this->searchables) as $searchable) {
            if (!in_array($searchable, $this->selectedSearchables)) {
                $search->searchables()->remove($searchable);
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
        return $this->selectedSearchables();
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
        $attributes = [];
        $attributes['id'] ??= $form->nameToId($this->name());
        
        $body = $form->checkboxes(
            name: $form->nameToArray($this->name()),
            items: $this->searchables(),
            selected: $this->selectedSearchables(),
            attributes: $attributes,
            labelAttributes: [],
            withInput: true,
            wrapClass: 'wrap-v',
        );
        
        $body .= $form->input(
            name: $form->nameToArray($this->name()).'[]',
            type: 'hidden',
            value: '_none',
            attributes: ['id' => null],
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