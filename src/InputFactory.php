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

use JsonException;
use Tobento\Service\Requester\RequesterInterface;
use Tobento\Service\Cookie\CookiesInterface;
use Tobento\Service\Cookie\CookieValuesInterface;
use Tobento\Service\Session\SessionInterface;

/**
 * InputFactory
 */
final class InputFactory implements InputFactoryInterface
{
    /**
     * Create a new InputFactory instance.
     *
     * @param RequesterInterface $requester
     */
    public function __construct(
        private RequesterInterface $requester,
        private SearchInterface $search,
    ) {}

    /**
     * Returns the created input.
     *
     * @param array $config
     * @return InputInterface
     */
    public function createInput(array $config = []): InputInterface
    {
        $storage = $config['storage'] ?? 'cookie';

        if ($storage === 'cookie') {
            return $this->createUsingCookieStorage(config: $config);
        }
        
        return $this->createUsingNullStorage(config: $config);
    }
    
    /**
     * Returns the created input using cookie storage.
     *
     * @param array $config
     * @return InputInterface
     */
    private function createUsingCookieStorage(array $config): InputInterface
    {
        $name = $config['name'] ?? 'search';
        
        $cookieValues = $this->requester->request()->getAttribute(CookieValuesInterface::class);
        $cookies = $this->requester->request()->getAttribute(CookiesInterface::class);
        $input = $this->requester->input();
        $resourceName = sha1($this->requester->request()->getUri()->getPath());
        
        try {
            $data = json_decode($cookieValues->get($name.'-'.$resourceName, '{}'), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            $data = [];
        }
        
        $inputData = $input->get($name, []);
        
        // clear filter data:
        if ($input->has($name.'.clear')) {
            $data = [];
            $inputData = [];
        }
                
        if ($input->has($name)) {
            // we combine cookie data with input data
            // so that indiviual filter forms can be sumbitted
            // without losing previously filtered values.
            $data = array_replace_recursive($data, $inputData);
            
            $storableInput = new Input(input: [$name => $data]);
                
            foreach($this->search->filters() as $filter) {
                if (!$filter->isStorable()) {
                    $storableInput->delete($filter->name());
                }
            }
            
            $cookies->add(
                name: $name.'-'.$resourceName,
                value: json_encode($storableInput->get($name)),
                lifetime: $config['lifetime'] ?? null,
                sameSite: $config['sameSite'] ?? 'LAX',
            );
        }
        
        return new Input(input: [$name => $data]);
    }
    
    /**
     * Returns the created input using null storage.
     *
     * @param array $config
     * @return InputInterface
     */
    private function createUsingNullStorage(array $config): InputInterface
    {
        $name = $config['name'] ?? 'search';
        $input = $this->requester->input();
        return new Input(input: [$name => $input->get($name, [])]);
    }
}