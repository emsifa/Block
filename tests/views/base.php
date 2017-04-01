<html>
<head>
  <title><?= $title ?></title>
  <?= $this->section('css') ?>
  <link href="a.css"/>
  <?= $this->show() ?>
</head>
<body>
  <?= $this->insert('another::component') ?>
  <?= $this->get('content') ?>

  <?= $this->section('scripts') ?>
  <script src="a.js"></script>
  <?= $this->show() ?>
</body>
</html>