<!DOCTYPE HTML>
<html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <meta http-equiv="edit-Type" edit="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>HTMLRun</title>
    <meta name="robots" content="index, follow">
    <meta name="googlebot" content="index, follow">
    <link href="https://fonts.googleapis.com/css?family=Roboto+Mono:300,400,500,700|Roboto:300,400,500,700&display=swap" rel="stylesheet">
    <link href="<?= APPURL ?>/assets/css/embed.css?v=<?= rand(1,9999) ?>" rel="stylesheet" media="screen" />
  </head>
  <body>
    <div id="wrapper">
        <header>
          <h1><a href="<?= APPURL ?>/editor/<?= $Snippet->get("filename") ?>" target="_blank">Edit in HTMLRun</a></h1>
          <div id="actions">
            <ul class="normalRes">
               <li class="active">
                  <a class="nav-embed" data-id="result" href="javascript:void(0)">Result</a>
                </li>
                <li>
                  <a class="nav-embed" data-id="html" href="javascript:void(0)">HTML</a>
                </li>
                <li >
                  <a class="nav-embed" data-id="css" href="javascript:void(0)">CSS</a>
                </li>
                <li >
                  <a class="nav-embed" data-id="js" href="javascript:void(0)">Javascript</a>
                </li>
            </ul>
          </div>
        </header>
        <div id="tabs">
               <div id="result" class="tCont active"><iframe name="result" allow="midi *; geolocation *; microphone *; camera *; encrypted-media *;" sandbox="allow-modals allow-forms allow-scripts allow-same-origin allow-popups allow-top-navigation-by-user-activation" allowfullscreen="" allowpaymentrequest="" frameborder="0" src="<?= APPURL ?>/view/<?= $Snippet->get("filename") ?>"></iframe></div>
               <pre id="html" class="tCont"><code class="language-html"><?= $html ?></code></pre>
               <pre id="css" class="tCont"><code class="language-css"><?= $css ?></code></pre>
               <pre id="js" class="tCont"><code class="language-js"><?= $js ?></code></pre>
        </div>
    </div>
    <script type="text/javascript" src="<?= APPURL ?>/assets/plugins/jquery/jquery-3.4.1.min.js"></script>
    <script type="text/javascript" src="<?= APPURL ?>/assets/plugins/prism/prism.js"></script>
    <script type="text/javascript" src="<?= APPURL ?>/assets/js/embed.js?v=<?= rand(1,9999) ?>"></script>
  </body>
</html>
