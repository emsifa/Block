<select name="<?= $get('name') ?>" class="select2"></select>

<?= $this->append('js') ?>
  <?= $this->once('script.select2') ?>
  <script src="select2.js"></script>
  <script>
    $('select.select2').select2();
  </script>
  <?= $this->endonce() ?>
<?= $this->stop() ?>