<html>
<head>
    <title><?= $title ?></title>
    <?= Block::start('css') ?>
    <link href="a.css"/>
    <?= Block::show() ?>
</head>
<body>
    <?= Block::get('content') ?>
        
    <?= Block::start('scripts') ?>
    <script src="a.js"></script>
    <?= Block::show() ?>
</body>
</html>