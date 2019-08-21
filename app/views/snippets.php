<!DOCTYPE html>
<html lang="en">
     <head>
          <meta charset="utf-8">
          <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
          <meta name="description" content="">
          <meta name="author" content="Fiand T">
          <title>Snippets by <?= $User->get("fullname") ?> - HTMLRun</title>
          <link href="<?= APPURL ?>/favicon-32x32.png" rel="icon" type="image/png" sizes="32x32">
          <link href="<?= APPURL ?>/favicon-96x96.png" rel="icon" type="image/png" sizes="96x96">
          <link href="<?= APPURL ?>/favicon-16x16.png" rel="icon" type="image/png" sizes="16x16">
          <link href="<?= APPURL ?>/assets/css/plugins.css?v=<?= rand(1,9999) ?>" rel="stylesheet" type="text/css" />
          <link href="<?= APPURL ?>/assets/css/core.css?v=<?= rand(1,9999) ?>" rel="stylesheet" type="text/css" />
     </head>
     <body class="onprogress">
          <div class="navbar navbar-expand-md static-top">
               <a class="navbar-brand" href="<?= APPURL ?>">
                    <img width="100" src="<?= APPURL ?>/assets/img/logo.png" alt="logo">
               </a>
               <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarCollapse" aria-controls="navbarCollapse" aria-expanded="false" aria-label="Toggle navigation">
               <span class="navbar-toggler-icon"></span>
               </button>
               <div class="collapse navbar-collapse" id="navbarCollapse">
                    <ul class="navbar-nav mr-auto">
                         <li class="nav-item active">
                              <a class="nav-link" href="<?= APPURL ?>">
                                   <i class="icon-plus icons"></i>New Snippet
                              </a>
                         </li>
                    </ul>
                    <div class="my-lg-0">
                         <ul class="navbar-nav mr-auto">
                              <?php if ($AuthUser): ?>
                              <li class="nav-item dropdown custom-dropdown menu-profile active">
                                   <a class="nav-link" data-toggle="dropdown" href="#"><i class="icon-user icons"></i><?= $AuthUser->get("fullname") ?></a>
                                   <div class="dropdown-menu">
                                        <a class="dropdown-item" href="<?= APPURL ?>/snippets/<?= $AuthUser->get("username") ?>">All your snippets</a>
                                        <a class="dropdown-item" href="#">Setting</a>
                                        <a class="dropdown-item" href="<?= APPURL ?>/signout">Signout</a>
                                   </div>
                              </li>
                              <?php else: ?>
                              <li class="nav-item active">
                                   <a class="nav-link" href="<?= APPURL ?>/signin"><i class="icon-user icons"></i>Signin</a>
                              </li>
                              <?php endif ?>
                         </ul>
                    </div>
               </div>
          </div>
          <div class="container-fluid">
               <div class="row">
                    <div id="sidebar" class="sidebar-nav col-md-2 d-none d-md-block sidebar">
                         <div class="segment userSidebar">
                           <div class="avatar mt-5">
                             <img src="https://www.gravatar.com/avatar/1d0844afff3e3e3b72b05e69baa322da?s=160" height="80" width="80">
                             <a title="See public fiddles" href="<?= APPURL ?>/snippets/<?= $User->get("username") ?>">
                                 <?= $User->get("fullname") ?>
                             </a>
                             <div class="company">Indonesia</div>
                           </div>
                         </div>
                    </div>
                    <div id="content" role="main" class="col-md-9 ml-sm-auto p-0 col-lg-10">
                         <div class="container pt-3">
                              <?php if ($Snippets->getTotalCount() > 0): ?>
                              <ul class="js-loadmore-content snippets-lits" data-loadmore-id="1">
                                   <?php foreach ($Snippets->getDataAs("Snippet") as $s): ?>
                                   <li class="item">
                                        <a class="overlay" href="<?= APPURL ?>/editor/<?= $s->get("filename") ?>"></a>
                                        <div class="item-iframe">
                                             <iframe scrolling="no" data-src="<?= APPURL ?>/view/<?= $s->get("filename") ?>" class="lozad" width="100%" height="200" frameborder="0" allowtransparency="true" sandbox="allow-forms allow-scripts allow-same-origin" allow="midi; geolocation; microphone; camera; encrypted-media;" allowfullscreen="true" allowpaymentrequest="true" __idm_frm__="892" src="<?= APPURL ?>/view/<?= $s->get("filename") ?>" data-loaded="true"></iframe>
                                        </div>
                                        
                                        <?php if ($AuthUser && ($AuthUser->get("id") == $s->get("user_id"))): ?>
                                        <ul class="actions">
                                             <li>
                                                  <a data-id="<?= $s->get("filename") ?>" data-name="<?= $s->get("title") ?>"
                                                       class="fiddleActions item-remove" href="javascript:void(0)" title="Delete Snipped">
                                                       <i class="icon-trash icons"></i>
                                                  </a>
                                             </li>
                                        </ul>
                                        <?php endif ?>
                                        <div class="meta">
                                             <h3>
                                             <a href="<?= APPURL ?>/editor/<?= $s->get("filename") ?>"><?= $s->get("title") ?></a>
                                             </h3>
                                             <p class="info">
                                                  <?= $s->get("description") ?>
                                             </p>
                                        </div>
                                   </li>
                                   <?php endforeach ?>
                              </ul>


                              <?php if($Snippets->getPage() < $Snippets->getPageCount()): ?>
                                <div class="loadmore mt-5 mb-5 text-center">
                                    <?php 
                                        $url = parse_url($_SERVER["REQUEST_URI"]);
                                        $path = $url["path"];
                                        if(isset($url["query"])){
                                            $qs = parse_str($url["query"], $qsarray);
                                            unset($qsarray["page"]);

                                            $url = $path."?".(count($qsarray) > 0 ? http_build_query($qsarray)."&" : "")."page=";
                                        }else{
                                            $url = $path."?page=";
                                        }
                                    ?>
                                    <a class="btn btn-primary js-loadmore-btn" data-loadmore-id="1" href="<?= $url.($Snippets->getPage()+1) ?>">
                                        <span class="icon sli sli-refresh"></span>
                                        <?= "Load More" ?>
                                    </a>
                                </div>
                            <?php endif; ?>

                              <?php else: ?>
                              <div class="blankslate">
                                   <i class="icon-close icons"></i>
                                   <h3 class="mt-3 text-white"><?= $User->get("fullname") ?> doesn't have any snippets</h3>
                              </div>
                              <?php endif ?>
                         </div>
                    </div>
               </div>
          </div>
          <script type="text/javascript" src="<?= APPURL ?>/assets/js/plugins.js?v=<?= rand(1,9999) ?>"></script>
          <script type="text/javascript" src="<?= APPURL ?>/assets/js/core.js?v=<?= rand(1,9999) ?>"></script>
          <script type="text/javascript" src="<?= APPURL ?>/assets/js/pages.js?v=<?= rand(1,9999) ?>"></script>

          <script type="text/javascript">
               $(function () {
                    <?php if ($AuthUser && ($AuthUser->get("id") == $User->get("id"))): ?>
                         HTMLRun.RemoveSnippet();
                    <?php endif ?>
                    HTMLRun.LoadMore();
                    HTMLRun.WindowResize();
               });
          </script>
     </body>
</html>