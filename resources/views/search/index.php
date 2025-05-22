<?php
$form = $view->form();
?>
<!DOCTYPE html>
<html lang="<?= $view->esc($view->get('htmlLang', 'en')) ?>">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= $view->etrans('Search') ?></title>
        <meta name="description" content="<?= $view->etrans('Search') ?>">
        <link rel="canonical" href="<?= $view->esc($view->routeUrl('search')) ?>">
        <?= $view->render('inc/head') ?>
        <?= $view->assets()->render() ?>
        <?php
        $view->asset('assets/search/search.css');
        $view->asset('assets/search/search.js')->attr('type', 'module');
        ?>
    </head>
    <body<?= $view->tagAttributes('body')->add('class', 'page-asided')->render() ?>>

        <?= $view->render('inc/header') ?>
        <?= $view->render('inc/nav') ?>

        <aside class="page-aside">
            <div class="search-filters">
                <?= $form->form(attributes: [
                    'action' => '',
                    'method' => 'GET',
                    'data-search-filters' => 'aside',
                ]) ?>
                <div class="search-filters-common">
                    <?php foreach($searchFilters->searchable(null)->except('search.term') as $filter) { ?>
                        <div class="search-filter"><?= $filter->render($view) ?></div>
                    <?php } ?>
                </div>
                <div class="search-filters-searchable" data-search-update="aside">
                    <?php foreach($searchables as $name => $searchable) { ?>
                        <?php
                        $searchableFilters = $searchFilters->searchable($name)->storable();
                        ?>
                        <?php if ($searchableFilters->count() > 0) { ?>
                            <div class="my-s title text-s"><?= $view->esc($searchable->title()) ?></div>
                            <?php foreach($searchableFilters as $filter) { ?>
                                <div class="search-filter"><?= $filter->render($view) ?></div>
                            <?php } ?>
                        <?php } ?>
                    <?php } ?>
                </div>
                <div class="field mt-m display-none-if-js">
                    <?= $form->button(text: $view->trans('Search'), attributes: ['class' => 'button fit text-xs']) ?>
                </div>
                <?= $form->close() ?>
            </div>
        </aside>
        
        <main class="page-main">
            <?= $view->render('inc.breadcrumb') ?>
            <?= $view->render('inc.messages') ?>
            
            <h1 class="title text-xl mb-s"><?= $view->etrans('Search') ?></h1>
            
            <div class="search-filters-main">
                <?= $form->form(attributes: [
                    'action' => '',
                    'method' => 'GET',
                    'data-search-filters' => 'main',
                ]) ?>
                <?php if ($searchFilters->has(name: 'search.term')) { ?>
                    <?php $termFilter = $searchFilters->get(name: 'search.term'); ?>
                    <div class="search-filter"><?= $termFilter->render($view) ?></div>
                <?php } ?>
                <?= $form->close() ?>
                <span class="mt-s button" id="btn-search-filters"><?= $view->etrans('Filter') ?></span>
            </div>
            
            <div class="search-results mt-s" data-search-results="all">
                <?php if ($searchResults->count() === 0) { ?>
                    <p class="subtitle text-m"><?= $view->etrans('No results found!') ?></p>
                <?php } else { ?>
                    <?php foreach($searchables as $name => $searchable) { ?>
                        <?php
                        $pagination = $searchable->pagination();
                        ?>
                        <h2 class="subtitle text-m text-100 mb-s">
                            <?= $view->etrans(
                                ':num :type found',
                                [
                                    ':num' => $pagination->getTotalItems(),
                                    ':type' => $searchable->title(),
                                ],
                            ) ?>
                        </h2>
                        <div class="search-results-searchable" data-search-results="<?= $view->esc($name) ?>">
                            <?php foreach($searchResults->searchable($name) as $result) { ?>
                                <?php if ($result->html()) { ?>
                                    <article class="search-result"><?= $result->html() ?></article>
                                <?php } else { ?>
                                    <article class="search-result">
                                        <a class="display-block" href="<?= $view->esc($result->url()) ?>">
                                            <?php if ($result->image()) { ?>
                                                <img class="mb-xs max-width-s" src="<?= $view->esc($result->image()) ?>" alt="<?= $view->esc($result->title()) ?>">
                                            <?php } ?>
                                            <h2 class="mb-xs title text-s"><?= $view->esc($result->title()) ?></h2>
                                            <?php if ($result->description()) { ?>
                                                <p class="text-xxs"><?= $view->esc($result->description()) ?></p>
                                            <?php } ?>
                                        </a>
                                    </article>
                                <?php } ?>
                            <?php } ?>
                        </div>
                        <div data-search-update="<?= $view->esc($name) ?>-pagination">
                            <?php
                            $remainingItems = ($pagination->getTotalItems()-$pagination->getTotalItemsTo());
                            ?>
                            <?php if ($pagination->hasPages() && $remainingItems) { ?>
                                <a 
                                    href="<?= $view->esc($pagination->getNextPageUrl()) ?>"
                                    class="mt-s button fit"
                                    data-search-filter="<?= $view->esc($name) ?>-pagination"
                                    data-search-action="append-items"
                                    data-search-append-to="<?= $view->esc($name) ?>"
                                    data-search-append-end="<?= $pagination->getNextPageUrl() ? '0' : '1' ?>"
                                >
                                    <?= $view->etrans(
                                        'show more :type | :num remaining',
                                        [
                                            ':type' => $searchable->title(),
                                            ':num' => $remainingItems,
                                        ]
                                    ) ?>                            
                                </a>
                            <?php } ?>
                        </div>
                    <?php } ?>
                <?php } ?>
            </div>
        </main>

        <?= $view->render('inc/footer') ?>
    </body>
</html>