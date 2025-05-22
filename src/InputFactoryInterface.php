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
 * InputFactoryInterface
 */
interface InputFactoryInterface
{    
    /**
     * Returns the created input.
     *
     * @param array $config
     * @return InputInterface
     */
    public function createInput(array $config = []): InputInterface;
}