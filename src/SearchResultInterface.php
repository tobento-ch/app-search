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

use JsonSerializable;

/**
 * SearchResultInterface
 */
interface SearchResultInterface extends JsonSerializable
{
    /**
     * Returns the searchable.
     *
     * @return string
     */
    public function searchable(): string;
    
    /**
     * Returns the type.
     *
     * @return string
     */
    public function type(): string;

    /**
     * Returns the title.
     *
     * @return string
     */
    public function title(): string;
    
    /**
     * Returns the url.
     *
     * @return string
     */
    public function url(): string;
    
    /**
     * Returns the description.
     *
     * @return string
     */
    public function description(): string;
    
    /**
     * Returns the image.
     *
     * @return null|string
     */
    public function image(): null|string;

    /**
     * Returns the html.
     *
     * @return string
     */
    public function html(): string;
}