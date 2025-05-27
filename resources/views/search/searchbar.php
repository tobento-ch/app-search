<?php
$view->asset('assets/search/search.css');
$view->asset('assets/search/searchbar.js')->attr('type', 'module');
$view->asset('assets/modal/modals.css');
$form = $view->form();
?>
<div class="searchbar max-width-m">
    <?= $form->form(attributes: [
        'action' => $view->routeUrl('search'),
        'method' => 'GET',
        'data-searchbar' => 'main',
    ]) ?>
    <div class="input has-icons-right">
        <?= $form->input(
            name: 'search.term',
            type: 'search',
            value: $form->getInput('search.term'),
            attributes: [
                'id' => '',
                'autocomplete' => 'off',
                'autocorrect' => 'off',
                'spellcheck' => 'false',
                'aria-label' => $view->trans('Search'),
                'placeholder' => $view->trans($inputPlaceholder),
                'class' => 'small',
            ],
            withInput: true,
        ) ?>
        <button class="icon-right link icon px-s py-xs" aria-label="<?= $view->etrans('Search') ?>"><svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-search"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" /><path d="M21 21l-6 -6" /></svg></button>
    </div>
    <div class="searchbar-dropdown" data-searchbar-dropdown="main">
        <div class="searchbar-body" data-searchbar-results="main"></div>
    </div>
    <?= $form->close() ?>
</div>

<span class="icon searchbar-icon link" data-modal-trigger="searchbar">
    <svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-search"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" /><path d="M21 21l-6 -6" /></svg>
    <span><?= $view->etrans('Search') ?></span>
</span>
<div class="modal modal-fade top" data-modal='{"id": "searchbar"}'>
    <div class="modal-background"></div>
    <div class="modal-content modal-full">
        <div class="modal-head">
            <?= $form->form(attributes: [
                'action' => $view->routeUrl('search'),
                'method' => 'GET',
                'data-searchbar' => 'modal',
                'class' => 'min-width-full',
            ]) ?>
            <div class="input has-icons-right">
                <?= $form->input(
                    name: 'search.term',
                    type: 'search',
                    value: $form->getInput('search.term'),
                    attributes: [
                        'id' => '',
                        'class' => 'small',
                        'autocomplete' => 'off',
                        'autocorrect' => 'off',
                        'spellcheck' => 'false',
                        'aria-label' => $view->trans('Search'),
                        'placeholder' => $view->trans($inputPlaceholder),
                    ],
                    withInput: true,
                ) ?>
                <button class="icon-right link icon px-s py-xs" aria-label="<?= $view->etrans('Search') ?>"><svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-search"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" /><path d="M21 21l-6 -6" /></svg></button>
            </div>
            <?= $form->close() ?>
        </div>
        <div class="modal-body" data-searchbar-results="modal"></div>
        <div class="modal-foot"><span class="link modal-close"><?= $view->etrans('close') ?></span></div>
    </div>
</div>