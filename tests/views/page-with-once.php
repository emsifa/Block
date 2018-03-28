<?= $this->extend('layout-simple', ['body_classes' => 'page-test']) ?>

<?= $this->section('content') ?>
  <?= $this->put('components.select2', ['name' => 'select_a']) ?>
  <?= $this->put('components.select2', ['name' => 'select_b']) ?>
<?= $this->stop() ?>