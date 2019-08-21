<div class="navbar navbar-expand-md static-top">
     <a class="navbar-brand" href="<?= APPURL ?>"><img width="100" src="<?= APPURL ?>/assets/img/logo.png" alt="logo"></a>
     <button  class="navbar-toggler" type="button" data-toggle="collapse"
     data-target="#navbarCollapse" aria-controls="navbarCollapse"
     aria-expanded="false" aria-label="Toggle navigation">
     <span class="navbar-toggler-icon"></span></button>
     <div class="collapse navbar-collapse" id="navbarCollapse">
          <ul class="navbar-nav mr-auto">
               <li class="nav-item active">
                    <a class="btn-run nav-link" title="Shortcut: Command/Ctrl + Enter" href="javascript:void(0)">
                         <i class="icon-control-play icons"></i>Run
                    </a>
               </li>
               <?php if (!$Snippet->isAvailable()): ?>
               <?php if ($AuthUser): ?>
               <li class="nav-item dropdown custom-dropdown menu-save active">
                    <a href="javascript:void(0)" title="Shortcut: Command/Ctrl + S" class="nav-link" data-toggle="dropdown" aria-expanded="false">
                         <i class="icon-cloud-upload icons"></i>Save
                    </a>
                    <div class="dropdown-menu">
                         <form class="form-save" action="<?= APPURL ?>/" method="post" autocomplete="off">
                              <div class="row">
                                   <div class="col-md-12 mt-2">
                                        <label for="title">Title</label>
                                        <div class="form-group">
                                             <input class="form-control required" id="title" name="title" type="text">
                                        </div>
                                   </div>
                                   <div class="col-md-12">
                                        <label for="description">Description</label>
                                        <div class="form-group">
                                             <textarea class="form-control required" id="description"  name="description" cols="30" rows="5"></textarea>
                                        </div>
                                   </div>
                                   <div class="col-md-12 mb-3">
                                        <button class="btn btn-sm btn-success" type="submit">Save Snippet</button>
                                   </div>
                              </div>
                         </form>
                    </div>
               </li>
               <?php endif ?>
               <?php else: ?>
               <?php if ($AuthUser && ($AuthUser->get("id") == $Snippet->get("user_id"))): ?>
               <li class="nav-item active">
                    <a class="btn-update nav-link" title="Shortcut: Command/Ctrl + S" href="javascript:void(0)">
                         <i class="icon-cloud-upload icons"></i>Update
                    </a>
               </li>
               <?php endif ?>
               
               <li class="nav-item active">
                    <a class="btn-download nav-link" href="javascript:void(0)" data-link="<?= APPURL ?>/raw/<?= $Snippet->get("filename") ?>/download">
                         <i class="icon-cloud-download icons"></i>Download
                    </a>
               </li>
               <li class="nav-item active">
                    <a class="nav-link" href="<?= APPURL ?>">
                         <i class="icon-plus icons"></i>New
                    </a>
               </li>
               <li class="nav-item dropdown custom-dropdown menu-embed active">
                    <a href="javascript:void(0)" class="nav-link" data-toggle="dropdown" aria-expanded="false">
                         <i class="icon-frame icons"></i>Embed
                    </a>
                    <div class="dropdown-menu">
                         <div class="row">
                              <div class="col-md-12 mt-2">
                                   <label for="exampleInputPassword1">Visual</label>
                              </div>
                              <div class="col-md-6 pr-1">
                                   <div class="form-group">
                                        <input type="text" class="form-control" placeholder="Code background">
                                   </div>
                              </div>
                              <div class="col-md-6 pl-1">
                                   <div class="form-group">
                                        <input type="text" class="form-control" placeholder="Menu background">
                                   </div>
                              </div>
                              <div class="col-md-12 mt-2">
                                   <label for="exampleInputPassword1">Embed iframe</label>
                                   <div class="form-group">
                                        <textarea class="embed-code form-control" name="" id="" cols="30" rows="5" readonly><iframe width="100%" height="300" src="<?= str_replace(['http:','https:'], '', APPURL) ?>/embedded/<?= $Snippet->get("filename") ?>" allowfullscreen="allowfullscreen" allowpaymentrequest frameborder="0"></iframe></textarea>
                                   </div>
                              </div>
                              <div class="col-md-12 mt-2">
                                   <label for="exampleInputPassword1">Embed script</label>
                                   <div class="form-group">
                                        <textarea class="embed-code form-control" name="" id="" cols="30" rows="5" readonly><script async src="<?= str_replace(['http:','https:'], '', APPURL) ?>/embed/<?= $Snippet->get("filename") ?>"></script></textarea>
                                   </div>
                              </div>
                         </div>
                    </div>
               </li>
               <li class="nav-item dropdown custom-dropdown menu-update active">
                    <a href="javascript:void(0)" class="nav-link" data-toggle="dropdown" aria-expanded="false">
                         <i class="icon-info icons"></i>Info
                    </a>
                    <div class="dropdown-menu">
                         <form class="form-update" action="<?= APPURL ?>/editor/<?= $Snippet->get('filename') ?>" method="post" autocomplete="off">
                              <div class="row">
                                   <div class="col-md-12 mt-2">
                                        <label for="title">Title</label>
                                        <div class="form-group">
                                             <input class="form-control required" name="title" id="title" type="text" value="<?= $Snippet->get('title') ?>">
                                        </div>
                                   </div>
                                   <div class="col-md-12">
                                        <label for="description">Description</label>
                                        <div class="form-group">
                                             <textarea class="form-control required" name="description" id="description" cols="30" rows="5"><?= $Snippet->get('description') ?></textarea>
                                        </div>
                                   </div>
                                   <?php if ($AuthUser && ($AuthUser->get("id") == $Snippet->get("user_id"))): ?>
                                   <div class="col-md-12 mb-3">
                                        <button class="btn btn-sm btn-success">Update information</button>
                                   </div>
                                   <?php endif ?>
                              </div>
                         </form>
                    </div>
               </li>
               <?php endif ?>
          </ul>
          <div class="my-lg-0">
               <ul class="navbar-nav mr-auto">
                    <li class="nav-item dropdown custom-dropdown menu-setting active">
                         <a href="javascript:void(0)" class="nav-link" data-toggle="dropdown" aria-expanded="false">
                              <i class="icon-equalizer icons"></i>Settings
                         </a>
                         <div class="dropdown-menu">
                              <div class="row mt-2">
                              <div class="col-md-6 pr-1">
                                   <label for="exampleInputPassword1">Indent size:</label>
                                   <div class="form-group">
                                        <select class="form-control" name="" id="">
                                             <option value="1">2 Spaces</option>
                                             <option value="2">3 Spaces</option>
                                             <option value="3">4 Spaces</option>
                                        </select>
                                   </div>
                              </div>
                              <div class="col-md-6 pl-1">
                                   <div class="form-group">
                                        <label for="exampleInputPassword1">Font Size</label>
                                        <select class="changefont form-control" name="" id="">
                                             <option value="1">Default</option>
                                             <option value="2">Big</option>
                                             <option value="3">Bigger</option>
                                             <option value="4">Jabba</option>
                                        </select>
                                   </div>
                              </div>

                              <div class="col-md-12 mt-2">
                                   <label for="exampleInputPassword1">Embed script</label>
                                   <div class="form-group">
                                        <label class="checkboxCont">
                                          <input type="checkbox" name="darkTheme" checked="">
                                          <span class="checkbox"></span>
                                          Dark theme
                                        </label>
                                   </div>
                              </div>
                         </div>
                         </div>
                    </li>
                    <?php if ($AuthUser): ?>
                    <li class="nav-item dropdown custom-dropdown menu-profile active">
                         <a class="nav-link" data-toggle="dropdown" href="javascript:void(0)"><i class="icon-user icons"></i><?= $AuthUser->get("fullname") ?></a>
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