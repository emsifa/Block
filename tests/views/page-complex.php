<?= $this->extend('base', ['body_classes' => 'page-test']) ?>

<?= $this->section('content') ?>
  <h1><?= $title ?></h1>
  <div id="container">
    <div id="sidebar">
      <?= $this->insert('another::widget-with-js') ?>
    </div>
    <div id="content">
      page content
    </div>
  </div>
<?= $this->stop() ?>

<?= $this->section('scripts') ?>
  <?= $this->parent() ?>
  <script src="b.js"></script>
<?= $this->stop() ?>

<?= $this->section('css') ?>
  <?= $this->parent() ?>
  <link href="b.css"/>
<?= $this->stop() ?>