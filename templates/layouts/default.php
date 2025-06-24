<?php
// Default layout template: templates/layouts/default.php
?>
<!DOCTYPE html>
<html lang="<?= $language ?? 'en' ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'My Berkeley' ?></title>
    <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.1.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://unpkg.com/imask"></script>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link rel="stylesheet" href="/styles/reset.css">
    <link rel="stylesheet" href="/styles/custom.css">
    <link rel="stylesheet" href="/styles/theme.css">-->
    <link rel="icon" href="/favicons/favicon.ico">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <!-- Google Fonts Roboto -->
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700;900&display=swap" />
    <!-- MDB -->
    <link rel="stylesheet" href="/style/mdb.min.css" />
    <script src="/assets/js/mdb.es.min.js"></script>
    <script src="/assets/js/mdb.umd.min.js"></script>

    <?= $assets->renderCss() ?>
    <?php if ($this->hasSection('head')): ?>
        <?= $this->getSection('head') ?>
    <?php endif; ?>
    <?= $this->getSection('head') ?>
</head>

<body class="<?= $bodyClass ?? '' ?>">
    <header class="site-header">
        <?php $this->getSection('header', include APP_ROOT . '/components/header.php') ?>
    </header>

    <div class="container">
        <?php if ($this->hasSection('sidebar')): ?>
            <aside class="sidebar-container">
                <?= $this->getSection('sidebar') ?>
            </aside>
        <?php endif; ?>

        <main class="content">
            <?= $this->getSection('content', '<p>No content provided</p>') ?>
        </main>
    </div>

    <footer class="site-footer">
        <p>Footer</p>
        <!-- <php $this->getSection('footer', include APP_ROOT . '/components/footer.php') ?> -->
    </footer>

    <?= $assets->renderJs() ?>
    <?php if ($this->hasSection('scripts')): ?>
        <?= $this->getSection('scripts') ?>
    <?php endif ?>
</body>

</html>