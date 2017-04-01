<?= $this->extend('layout-share-data-across-subviews') ?>

<?= $this->section('content') ?>
  <h1><?= $message ?></h1>
  <?= $this->insert('components.message-component') ?>
  <?= $this->component('another::another-message-component') ?>
  <p>Slot</p>
  <?= $this->endcomponent() ?>
<?= $this->stop() ?>
