<?= Block::extend('base') ?>

<?= Block::section('content') ?>
    <h1><?= $title ?></h1>
    <div id="container">
        <div id="sidebar">
            <?= Block::insert('another::widget-with-js') ?>
        </div>
        <div id="content">
            page content
        </div>
    </div>
<?= Block::stop() ?>

<?= Block::section('scripts') ?>
    <?= Block::parent() ?>
    <script src="b.js"></script>
<?= Block::stop() ?>

<?= Block::section('css') ?>
    <?= Block::parent() ?>
    <link href="b.css"/>
<?= Block::stop() ?>
