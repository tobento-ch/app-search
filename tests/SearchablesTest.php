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

namespace Tobento\App\Search\Test;

use PHPUnit\Framework\TestCase;
use Tobento\App\Search\Searchable;
use Tobento\App\Search\SearchableInterface;
use Tobento\App\Search\Searchables;
use Tobento\App\Search\SearchablesInterface;
use Tobento\App\Search\Test\Factory;

class SearchablesTest extends TestCase
{
    public function testConstructorMethod()
    {
        $searchables = new Searchables();
        $this->assertInstanceof(SearchablesInterface::class, $searchables);
        
        $searchables = new Searchables(
            new Searchable\Menu(
                menu: Factory::createMenu('foo'),
            ),
        );
        $this->assertSame(1, $searchables->count());
    }

    public function testAddMethod()
    {
        $searchables = new Searchables();
        
        $this->assertSame(0, $searchables->count());

        $searchables->add(new Searchable\Menu(menu: Factory::createMenu('foo'), name: 'main'));
        $searchables->add(new Searchable\Menu(menu: Factory::createMenu('foo'), name: 'head'));
        
        $this->assertSame(2, $searchables->count());
    }
    
    public function testRemoveMethod()
    {
        $searchables = new Searchables(
            new Searchable\Menu(menu: Factory::createMenu('foo'), name: 'main'),
            new Searchable\Menu(menu: Factory::createMenu('foo'), name: 'head'),
        );
        
        $this->assertSame(2, $searchables->count());

        $searchables->remove(searchable: 'main');
        $this->assertSame(1, $searchables->count());
        
        $searchables->remove(searchable: 'unknown');
        $this->assertSame(1, $searchables->count());
        
        $searchables->remove(searchable: 'head');
        $this->assertSame(0, $searchables->count());
    }
    
    public function testHasMethod()
    {
        $searchables = new Searchables();
        $this->assertFalse($searchables->has(name: 'foo'));
        
        $searchables = new Searchables(new Searchable\Menu(menu: Factory::createMenu('foo'), name: 'main'));
        $this->assertTrue($searchables->has(name: 'main'));
    }
    
    public function testGetMethod()
    {
        $searchables = new Searchables();
        $this->assertSame(null, $searchables->get(name: 'foo'));
        
        $searchables = new Searchables(new Searchable\Menu(menu: Factory::createMenu('foo'), name: 'main'));
        $this->assertSame('main', $searchables->get(name: 'main')->name());
    }
    
    public function testNamesMethod()
    {
        $searchables = new Searchables(
            new Searchable\Menu(menu: Factory::createMenu('foo'), name: 'main'),
            new Searchable\Menu(menu: Factory::createMenu('foo'), name: 'head'),
        );
        
        $this->assertSame(['main', 'head'], $searchables->names());
    }
    
    public function testFilterMethod()
    {
        $searchables = new Searchables(
            new Searchable\Menu(menu: Factory::createMenu('foo'), name: 'main'),
            new Searchable\Menu(menu: Factory::createMenu('foo'), name: 'head'),
        );
        
        $filtered = $searchables->filter(fn(SearchableInterface $s): bool => $s->name() === 'main');
        
        $this->assertFalse($searchables === $filtered);
        $this->assertSame(2, $searchables->count());
        $this->assertSame(1, $filtered->count());
    }

    public function testSortMethod()
    {
        $searchables = new Searchables(
            new Searchable\Menu(menu: Factory::createMenu('foo'), name: 'main', priority: 1),
            new Searchable\Menu(menu: Factory::createMenu('foo'), name: 'head', priority: 3),
            new Searchable\Menu(menu: Factory::createMenu('foo'), name: 'foot', priority: 2),
        );
        
        $searchablesNew = $searchables->sort(fn($a, $b) => $a->name() <=> $b->name());
        
        $this->assertFalse($searchables === $searchablesNew);
        $this->assertSame(['main', 'head', 'foot'], array_keys(array_map(fn ($s) => $s->name(), $searchables->all())));
        $this->assertSame(['foot', 'head', 'main'], array_keys(array_map(fn ($s) => $s->name(), $searchablesNew->all())));
    }
    
    public function testSortMethodHighestFirstByDefault()
    {
        $searchables = new Searchables(
            new Searchable\Menu(menu: Factory::createMenu('foo'), name: 'main', priority: 1),
            new Searchable\Menu(menu: Factory::createMenu('foo'), name: 'head', priority: 3),
            new Searchable\Menu(menu: Factory::createMenu('foo'), name: 'foot', priority: 2),
        );
        
        $searchablesNew = $searchables->sort();
        
        $this->assertFalse($searchables === $searchablesNew);
        $this->assertSame(['main', 'head', 'foot'], array_keys(array_map(fn ($s) => $s->name(), $searchables->all())));
        $this->assertSame(['head', 'foot', 'main'], array_keys(array_map(fn ($s) => $s->name(), $searchablesNew->all())));
    }
    
    public function estAllMethod()
    {
        $searchables = new Searchables();
        $this->assertSame([], $searchables->all());
        
        $searchable = new Searchable\Menu(menu: Factory::createMenu('foo'), name: 'main');
        $searchables = new Searchables($searchable);
        $this->assertSame(['main' => $searchable], $searchables->all());
    }
    
    public function testCountMethod()
    {
        $searchables = new Searchables();
        $this->assertSame(0, $searchables->count());
        
        $searchables = new Searchables(new Searchable\Menu(menu: Factory::createMenu('foo'), name: 'main'));
        $this->assertSame(1, $searchables->count());
    }
    
    public function testIteration()
    {
        $searchables = new Searchables(
            new Searchable\Menu(menu: Factory::createMenu('foo'), name: 'main'),
            new Searchable\Menu(menu: Factory::createMenu('foo'), name: 'head'),
        );
        
        foreach($searchables as $searchable) {
            $this->assertInstanceof(SearchableInterface::class, $searchable);
        }
    }
}