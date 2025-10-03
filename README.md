# App Search

The search app provides interfaces to search resources by filters. It comes with a default implementation and some basic [searchables](#available-searchables) and [filters](#available-filters).

## Table of Contents

- [Getting Started](#getting-started)
    - [Requirements](#requirements)
- [Documentation](#documentation)
    - [App](#app)
    - [Search Boot](#search-boot)
        - [Search Config](#search-config)
    - [Basic Usage](#basic-usage)
        - [Searching](#searching)
    - [Features](#features)
        - [Search Feature](#search-feature)
    - [Search](#search)
        - [Adding Searchables](#adding-searchables)
        - [Creating Searchables](#creating-searchables)
        - [Adding Filters](#adding-filters)
        - [Creating Filters](#creating-filters)
        - [Searching](#searching)
    - [Available Searchables](#available-searchables)
        - [Menu Searchable](#menu-searchable)
        - [Repository Searchable](#repository-searchable)
    - [Available Filters](#available-filters)
        - [Clear Filter](#clear-filter)
        - [Input Filter](#input-filter)
        - [Pagination Filter](#pagination-filter)
        - [Searchables Filter](#searchables-filter)
        - [Search Term Filter](#search-term-filter)
        - [Select Filter](#select-filter)
- [Credits](#credits)
___

# Getting Started

Add the latest version of the app search project running this command.

```
composer require tobento/app-search
```

## Requirements

- PHP 8.4 or greater

# Documentation

## App

Check out the [**App Skeleton**](https://github.com/tobento-ch/app-skeleton) if you are using the skeleton.

You may also check out the [**App**](https://github.com/tobento-ch/app) to learn more about the app in general.

## Search Boot

The search boot does the following:

* migrates search config, view and asset files
* implements search interfaces based on the search config file
* boots features defined in config file

```php
use Tobento\App\AppFactory;
use Tobento\App\Search\InputInterface;
use Tobento\App\Search\SearchInterface;

// Create the app
$app = new AppFactory()->createApp();

// Add directories:
$app->dirs()
    ->dir(realpath(__DIR__.'/../'), 'root')
    ->dir(realpath(__DIR__.'/../app/'), 'app')
    ->dir($app->dir('app').'config', 'config', group: 'config')
    ->dir($app->dir('root').'public', 'public')
    ->dir($app->dir('root').'vendor', 'vendor');

// Adding boots:
$app->boot(\Tobento\App\Search\Boot\Search::class);
$app->booting();

// Implemented interfaces:
$search = $app->get(SearchInterface::class);
$input = $app->get(InputInterface::class);

// Run the app
$app->run();
```

### Search Config

The configuration for the search is located in the ```app/config/search.php``` file at the default App Skeleton config location where you can configure the implemented search interfaces for your application and more.

## Basic Usage

### Searching

This example shows how a search process works in general. If you use the [Search Feature](#search-feature), there is no need to implement anything.

```php
use Tobento\App\Search\Filters;
use Tobento\App\Search\FiltersInterface;
use Tobento\App\Search\Input;
use Tobento\App\Search\InputInterface;
use Tobento\App\Search\Search;
use Tobento\App\Search\Searchables;
use Tobento\App\Search\SearchablesInterface;
use Tobento\App\Search\SearchInterface;
use Tobento\App\Search\SearchResultsInterface;

$search = new Search(
    filters: new Filters(), // FiltersInterface
    searchables: new Searchables(), // SearchablesInterface
);

var_dump($search instanceof SearchInterface);
// bool(true)

// Searching:
$searchResults = $search->search(
    input: new Input(['search' => ['term' => 'foo']]), // InputInterface
);

var_dump($searchResults instanceof SearchResultsInterface);
// bool(true)

// You may get the filters:
var_dump($search->filters() instanceof FiltersInterface);
// bool(true)

// You may get the searchables:
var_dump($search->searchables() instanceof SearchablesInterface);
// bool(true)
```

## Features

### Search Feature

The search feature provides a simple search page where users can search the applications content. Furthermore, it provides a searchbar which you may render on all pages. 

Check out the [Search](#search) section on how to add searchables, filters and more as this feature uses the implemented search interfaces.

**Config**

In the [config file](#search-config) you can configure this feature:

```php
'features' => [
    new Feature\Search(
        // The default views to render:
        view: 'search/index',
        viewSearchbar: 'search/searchbar',
        viewSearchbarResults: 'search/searchbar-results',
        
        // You may customize the searchbar input placeholder:
        searchbarInputPlaceholder: 'Search...',
        
        // If true, routes are being localized.
        localizeRoute: false,
    ),
],
```

**Search Page**

The search page is available under the ```https://example.com/search``` url.

**Searchbar**

In any view file, render the view named ```search.bar``` which will display a search input element:

```php
<header><?= $view->render('search.bar') ?></header>
```

## Search

### Adding Searchables

You may use and add the [Available Searchables](#available-searchables) or [create your own searchables](#creating-searchables) if desired.

**Option 1**

In the [Search Config](#search-config) file you can configure your searchables:

```php
use Psr\Container\ContainerInterface;
use Tobento\App\Search\Search;
use Tobento\App\Search\Searchable;
use Tobento\App\Search\Searchables;
use Tobento\App\Search\SearchInterface;
use Tobento\App\Search\Filter;
use Tobento\App\Search\Filters;

'interfaces' => [
    SearchInterface::class => static function(ContainerInterface $container): SearchInterface {
        return new Search(
            filters: new Filters(
                new Filter\SearchTerm(name: 'search.term'),
            ),
            searchables: new Searchables(
                new Searchable\Menus($container->get(\Tobento\Service\Menu\MenusInterface::class)->menu('main')),
            ),
        );
    },
],
```

**Option 2**

Use the [App on](https://github.com/tobento-ch/app#on) method to add searchables only on demand:

```php
use Tobento\App\Search\Searchable;
use Tobento\App\Search\SearchablesInterface;
use Tobento\App\Search\SearchInterface;

$app->on(
    SearchInterface::class,
    static function(SearchInterface $search) use ($app): void {
        // Get the searchables:
        $searchables = $search->searchables();
        // SearchablesInterface
        
        // Adding a searchable:
        $menu = $app->get(\Tobento\Service\Menu\MenusInterface::class)->menu('main');
        $searchables->add(searchable: new Searchable\Menus(menu: $menu));
    }
);
```

### Creating Searchables

You may create searchables for specific resources if you need full search and filter control.

Inspect the following files to see different searchable implementations:

* ```Tobento\App\Search\Searchable\Menu::class```
* ```Tobento\App\Search\Searchable\Repository::class```

**Example**

```php
use Tobento\App\Search\Filter;
use Tobento\App\Search\FilterInterface;
use Tobento\App\Search\FiltersInterface;
use Tobento\App\Search\SearchableInterface;
use Tobento\App\Search\SearchResult;
use Tobento\App\Search\SearchResultInterface;
use Tobento\Service\Pagination\Pagination;
use Tobento\Service\Pagination\PaginationInterface;
use Tobento\Service\Pagination\UrlGenerator;
use Tobento\Service\View\ViewInterface;

class ProductsSearchable implements SearchableInterface
{
    protected null|PaginationInterface $pagination = null;
    
    public function __construct(
        protected ProductRepository $repository,
        protected ViewInterface $view,
        protected int $priority = 0,
    ) {}
    
    public function name(): string
    {
        return 'products';
    }
    
    public function title(): string
    {
        return 'Products';
    }
    
    public function priority(): int
    {
        return $this->priority;
    }
    
    /**
     * Returns the filters.
     *
     * @return array<array-key, FilterInterface>
     */
    public function filters(): array
    {
        // Configure specific products filters defining the searchable attribute on each filter:
        return [
            new Filter\Select(
                name: 'search.product-color',
                label: 'Colors',
                searchable: $this->name(),
                options: ['red' => 'Red', 'blue' => 'Blue'],
            ),
            new Filter\Pagination(
                name: 'search.product-page',
                searchable: $this->name(),
                pagination: $this->pagination(),
            ),
        ];
    }
    
    /**
     * Returns the search results found.
     *
     * @param FiltersInterface $filters
     * @return array<array-key, SearchResultInterface>
     */
    public function search(FiltersInterface $filters): array
    {
        $where = [];
        $orderBy = [];
        $limit = [];
        
        // Use the filters you wish to apply to your query:
        foreach($filters as $filter) {
            switch ($filter::class) {
                case Filter\Select::class:
                    if ($filter->name() === 'search.product-color') {
                        // apply filter to query:
                    }
                    break;
                case Filter\SearchTerm::class:
                    // apply filter to query:
                    break;
                case Filter\Pagination::class:
                    if ($filter->name() === 'search.product-page') {
                        $this->pagination = $filter->pagination();
                        $limit = [$this->pagination->getItemsPerPage(), $this->pagination->getItemsOffset()];
                    }
                    break;
            }
        }
        
        if ($filters->has('search.product-page')) {
            $paginationFilter = $filters->get('search.product-page');
            $paginationFilter->updatePaginationTotalItems($this->repository->count(where: $where));
            $this->pagination = $paginationFilter->pagination();
            $limit = [$this->pagination->getItemsPerPage(), $this->pagination->getItemsOffset()];
        }
        
        // Do the query and create the search results:
        $results = [];
        
        foreach($this->repository->findAll(where: $where, orderBy: $orderBy, limit: $limit) as $item) {
            $results = new SearchResult(
                searchable: $this->name(),
                type: $this->title(),
                title: $item->get('title'),
                url: $item->get('url'),
                image: $item->get('image'),
                
                // you may add html for custom search result:
                html: $this->view->render(
                    view: 'product/search-result',
                    data: $item,
                ),
            );
        }
        
        return $results;
    }

    public function totalItems(): int
    {
        return $this->pagination()->getTotalItems();
    }
    
    public function pagination(): PaginationInterface
    {
        if ($this->pagination) {
            return $this->pagination;
        }
        
        return $this->pagination = new Pagination(
            totalItems: $this->repository->count(),
            currentPage: 1,
            itemsPerPage: 25,
            maxPagesToShow: 6,
            maxItemsPerPage: 100,
            urlGenerator: new UrlGenerator()->addPageUrl('?search[product-page]={num}'),
        );
    }
}
```

### Adding Filters

**Option 1**

In the [Search Config](#search-config) file you can configure your filters:

```php
use Psr\Container\ContainerInterface;
use Tobento\App\Search\Search;
use Tobento\App\Search\Searchable;
use Tobento\App\Search\Searchables;
use Tobento\App\Search\SearchInterface;
use Tobento\App\Search\Filter;
use Tobento\App\Search\Filters;

'interfaces' => [
    SearchInterface::class => static function(ContainerInterface $container): SearchInterface {
        return new Search(
            filters: new Filters(
                new Filter\SearchTerm(name: 'search.term'),
            ),
            searchables: new Searchables(
                new Searchable\Menus($container->get(\Tobento\Service\Menu\MenusInterface::class)->menu('main')),
            ),
        );
    },
],
```

**Option 2**

Use the [App on](https://github.com/tobento-ch/app#on) method to add filters only on demand:

```php
use Tobento\App\Search\Filter;
use Tobento\App\Search\FiltersInterface;
use Tobento\App\Search\SearchInterface;

$app->on(
    SearchInterface::class,
    static function(SearchInterface $search) use ($app): void {
        // Get the filters:
        $filters = $search->filters();
        // FiltersInterface
        
        // Adding a filter:
        $filters->add(filter: new Filter\SearchTerm(name: 'search.term'));
    }
);
```

### Creating Filters

You may create custom filters to fit your requirements.

Inspect the following files to see different filter implementations:

* ```Tobento\App\Search\Filter\Clear::class```
* ```Tobento\App\Search\Filter\Searchables::class```
* ```Tobento\App\Search\Filter\SearchTerm::class```

## Available Searchables

### Menu Searchable

The ```Menu``` searchable allows you to search the provided menu items using the [Search Term Filter](#search-term-filter).

```php
use Tobento\App\Search\Searchable;
use Tobento\Service\Menu\MenuInterface;

$searchable = new Searchable\Menu(
    menu: $menu, // MenuInterface
    
    // Set a unique name:
    name: 'menu',
    
    // You may set a custom title:
    title: 'Menu Items',
    
    // You may set a priority:
    priority: 100,
);
```

You may check out the [Menu Service](https://github.com/tobento-ch/service-menu) for more information.

### Repository Searchable

The ```Repository``` searchable allows you to search the provided repository using the [Search Term Filter](#search-term-filter).

```php
use Tobento\App\Search\Searchable;
use Tobento\App\Search\SearchResult;
use Tobento\App\Search\SearchResultInterface;
use Tobento\Service\Repository\RepositoryInterface;

$searchable = new Searchable\Repository(
    repository: $repository, // RepositoryInterface
    
    // Set a unique name:
    name: 'products',
    
    // Set a title:
    title: 'Products',
    
    // Define the search attributes:
    searchAttributes: ['title', 'description'],
    
    // Map the repository items to the search results:
    toSearchResult: function(object $item, Searchable\Repository $searchable): SearchResultInterface {
        return new SearchResult(
            searchable: $searchable->name(),
            type: $searchable->title(),
            title: $item->get('title'),
            description: $item->get('description'),
            url: $item->get('url'),
            image: $item->get('image'),
        );
    },
    
    // You may set a priority:
    priority: 100,
);
```

You may check out the [Repository Service](https://github.com/tobento-ch/service-repository) for more information.

## Available Filters

### Clear Filter

This filter displays a button to clear all filters.

```php
use Tobento\App\Search\Filter;

$filter = new Filter\Clear(
    // Set a unique name:
    name: 'search.clear',
    
    // You may set a custom label:
    label: 'Clear all',
    
    // You may set attributes for the button element:
    attributes: ['data-foo' => 'value'],
);
```

### Input Filter

This filter displays a HTML input element to filter searchables.

```php
use Tobento\App\Search\Filter;

$filter = new Filter\Input(
    // Set a unique name:
    name: 'search.price.from',
    
    // You may set a label:
    label: 'Price From',
    
    // You may set a description:
    description: 'Searches prices from.',
    
    // You may set a searchable the filter belongs to:
    searchable: 'products', // or null
    
    // You may change the default input type (text):
    inputType: 'range',
    
    // You may set attributes for the input element:
    inputAttributes: ['min' => '5'],
    
    // You may set a default input value:
    inputValue: '10', // or null
    
    // You may set a custom view to render:
    view: 'search/filter',
);
```

### Pagination Filter

The main purpose of this filter is to use for the searchables pagination. Check out the [Available Searchables](available-searchables) class files to see its usage in action.

### Searchables Filter

This filter displays checkboxes with the searchables to enable or disable for searching.

```php
use Tobento\App\Search\Filter;

$filter = new Filter\Searchables(
    // Set a unique name:
    name: 'search.searchables',
    
    // You may set a custom label:
    label: 'Content',
    
    // You may set a description:
    description: 'Choose the contents you wish to search.',
    
    // You may set a custom view to render:
    view: 'search/filter',
);
```

### Search Term Filter

This filter displays a search input element to search for the provided search term.

```php
use Tobento\App\Search\Filter;

$filter = new Filter\SearchTerm(
    // Set a unique name:
    name: 'search.term',
    
    // You may set a label:
    label: 'Search',
    
    // You may set a description:
    description: 'Search for ...',
    
    // You may set a custom placeholder text:
    placeholder: 'Search ...',
    
    // You may set a custom view to render:
    view: 'search/filter',
);
```

### Select Filter

This filter displays a HTML select element with options to filter searchables.

```php
use Tobento\App\Search\Filter;

$filter = new Filter\Select(
    // Set a unique name:
    name: 'search.colors',
    
    // You may set a label:
    label: 'Colors',
    
    // You may set a description:
    description: 'Choose any colors you wish to search for.',
    
    // You may set a searchable the filter belongs to:
    searchable: 'products', // or null
    
    // Set the options to choose from:
    options: ['blue' => 'Blue', 'red' => 'Red'],
    // with optgroup options:
    // options: ['Primary' => ['blue' => 'Blue']],
    
    // You may set a default value:
    selected: ['blue'],
    
    // You may set attributes for the select element:
    selectAttributes: ['multiple'],
    
    // You may set attributes for the option elements:
    optionAttributes: ['*' => ['data-foo' => 'val']],
    
    // You may set attributes for the optgroup elements:
    optgroupAttributes: ['data-foo' => 'value'],
    
    // You may set a custom view to render:
    view: 'search/filter',
);
```

# Credits

- [Tobias Strub](https://www.tobento.ch)
- [All Contributors](../../contributors)
- [Tabler Icons](https://github.com/tabler/tabler-icons)