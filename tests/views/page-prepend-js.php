<?= $this->extend('layout-simple', ['body_classes' => 'page-test']) ?>

<?= $this->prepend('js') ?>
  <script src="b.js"></script>
<?= $this->stop() ?>