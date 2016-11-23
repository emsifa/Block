<h1>Widget with js</h1>

<?= Block::start('scripts') ?>
    <?= Block::parent() ?>
    <script>js.here()</script>
<?= Block::stop() ?>