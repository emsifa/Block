<html>
<head>
  <title><?= $title ?></title>
  <?= $this->section('css') ?>
  <link href="a.css"/>
  <?= $this->show() ?>
</head>
<body class="<?= $get('body_classes') ?>">
  <?= $this->get('content') ?>

  <?= $this->section('js') ?>
  <script src="a.js"></script>
  <?= $this->show() ?>
</body>
</html>