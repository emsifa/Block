<h1>Component</h1>

<?= $this->section('scripts') ?>
  <?= $this->parent() ?>
  <script>component.init()</script>
<?= $this->stop() ?>