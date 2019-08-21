<!DOCTYPE html>
<html lang="en">
     <?php require_once(APPPATH.'/views/partials/editor_head.php'); ?>
     <body class="onprogress">
          
          <?php require_once(APPPATH.'/views/partials/editor_nav.php'); ?>
          <div class="container-fluid">
               <div class="row">
                    
                    <?php require_once(APPPATH.'/views/partials/editor_sidebar.php'); ?>
                    <div id="content" role="main" class="col-md-9 ml-sm-auto p-0 col-lg-10">
                         <div id="editor">
                              <div id="tabs">
                                   <ul>
                                        <li class="active tabItem"><a href="" data-lang="html">HTML</a></li>
                                        <li class="tabItem"><a href="" data-lang="css">CSS</a></li>
                                        <li class="tabItem"><a href="" data-lang="js">JavaScript</a></li>
                                        <li class="dragInfo"></li>
                                   </ul>
                              </div>
                              <div class="tabsContainer">
                                   <div id="panel-left" class="panel-v panel">
                                        <div id="html" class="tabCont panel">
                                             <textarea class="d-none" id="cm-html"><?= isset($html) ? $html : "" ?></textarea>
                                        </div>
                                        <div id="css" class="tabCont panel hidden">
                                             <textarea class="d-none" id="cm-css"><?= isset($css) ? $css : "" ?></textarea>
                                        </div>
                                        <div id="js" class="tabCont panel hidden">
                                             <textarea class="d-none" id="cm-js"><?= isset($js) ? $js : "" ?></textarea>
                                        </div>
                                   </div>
                                   <div id="panel-right" class="panel-v panel resultsPanel">
                                        <iframe id="preview" name="result" allow="midi *; geolocation *; microphone *; camera *; encrypted-media *;" sandbox="allow-modals allow-forms allow-scripts allow-same-origin allow-popups allow-top-navigation-by-user-activation" allowfullscreen="" allowpaymentrequest="" frameborder="0" src="<?= APPURL ?>/view/null"></iframe>
                                        <div class="windowLabelCont">
                                             <span class="windowLabel hidden">Result</span>
                                             <em class="size hidden">655px</em>
                                        </div>
                                   </div>
                              </div></div>
                         </div>
                    </div>
               </div>
               
               <?php  require_once(APPPATH.'/views/partials/editor_script.php'); ?>
               
          </body>
     </html>