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

/**
 * SearchResult
 */
final class SearchResult implements SearchResultInterface
{
    /**
     * Create a new SearchResult instance.
     *
     * @param string $searchable
     * @param string $type
     * @param string $title
     * @param string $url
     * @param string $description
     * @param null|string $image
     * @param string $html
     */
    public function __construct(
        private string $searchable,
        private string $type,
        private string $title,
        private string $url,
        private string $description = '',
        private null|string $image = null,
        private string $html = '',
    ) {}
    
    /**
     * Returns the searchable.
     *
     * @return string
     */
    public function searchable(): string
    {
        return $this->searchable;
    }
    
    /**
     * Returns the type.
     *
     * @return string
     */
    public function type(): string
    {
        return $this->type;
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
     * Returns the url.
     *
     * @return string
     */
    public function url(): string
    {
        return $this->url;
    }
    
    /**
     * Returns the description.
     *
     * @return string
     */
    public function description(): string
    {
        return $this->description;
    }
    
    /**
     * Returns the image.
     *
     * @return null|string
     */
    public function image(): null|string
    {
        return $this->image;
    }

    /**
     * Returns the html.
     *
     * @return string
     */
    public function html(): string
    {
        return $this->html;
    }
    
    /**
     * Serializes the object to a value that can be serialized natively by json_encode().
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'searchable' => $this->searchable(),
            'type' => $this->type(),
            'title' => $this->title(),
            'url' => $this->url(),
            'description' => $this->description(),
            'image' => $this->image(),
            'html' => $this->html(),
        ];
    }
}