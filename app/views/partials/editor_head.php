<head>
     <meta charset="utf-8">
     <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
     <meta name="description" content="">
     <meta name="author" content="Fiand T">

     <?php if ($Snippet->isAvailable()): ?>
          <title><?= $Snippet->get("title") ?> from <?= $User->get("fullname") ?> - HTMLRun</title>
     <?php else: ?>
          <title>HTMLRun - Test your JavaScript, CSS or HTML online</title>
     <?php endif ?>

     <link href="<?= APPURL ?>/favicon-32x32.png" rel="icon" type="image/png" sizes="32x32">
     <link href="<?= APPURL ?>/favicon-96x96.png" rel="icon" type="image/png" sizes="96x96">
     <link href="<?= APPURL ?>/favicon-16x16.png" rel="icon" type="image/png" sizes="16x16">
     <link href="<?= APPURL ?>/assets/css/plugins.css?v=<?= rand(1,9999) ?>" rel="stylesheet" type="text/css" />
     <link href="<?= APPURL ?>/assets/css/core.css?v=<?= rand(1,9999) ?>" rel="stylesheet" type="text/css" />
</head>