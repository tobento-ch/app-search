<div class="searchbar-results">
    <?php if ($searchResults->count() === 0) { ?>
        <p class="subtitle text-s p-xs"><?= $view->etrans('No results found!') ?></p>
    <?php } else { ?>
        <?php foreach($searchResults as $result) { ?>
            <article class="searchbar-result">
                <a class="display-block" href="<?= $view->esc($result->url()) ?>">
                    <?php if ($result->image()) { ?>
                        <img class="mb-xs max-width-xs" src="<?= $view->esc($result->image()) ?>" alt="<?= $view->esc($result->title()) ?>">
                    <?php } ?>
                    <h2 class="mb-xs title text-s"><?= $view->esc($result->title()) ?></h2>
                    <?php if ($result->description()) { ?>
                        <p class="mb-xs text-xxs"><?= $view->esc($result->description()) ?></p>
                    <?php } ?>
                    <?php if ($result->type()) { ?>
                        <span class="mb-xs searchbar-result-type text-xxs"><?= $view->esc($result->type()) ?></span>
                    <?php } ?>
                </a>
            </article>
        <?php } ?>
    <?php } ?>
</div>