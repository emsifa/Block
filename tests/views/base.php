<html>
<head>
    <title><?= $title ?></title>
    <?= Block::section('css') ?>
    <link href="a.css"/>
    <?= Block::show() ?>
</head>
<body>
    <?= Block::insert('another::component') ?>
    <?= Block::get('content') ?>
        
    <?= Block::section('scripts') ?>
    <script src="a.js"></script>
    <?= Block::show() ?>
</body>
</html>