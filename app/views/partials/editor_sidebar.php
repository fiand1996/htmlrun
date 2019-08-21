<div id="sidebar" class="sidebar-nav col-md-2 d-none d-md-block sidebar">
     <div class="form-group mb-0 mt-2 mr-2 ml-2">
          <label for="resource">Resources</label>
          <ul class="mb-1" id="resource-lists">
               <?php if ($Snippet->isAvailable()): ?>
                    <?php foreach (json_decode($Snippet->get("resources")) as $resource): ?>
                         <li>
                              <input type="hidden" class="resources" name="resources[]" value="<?= $resource ?>">
                              <a title="<?= $resource ?>" href="<?= $resource ?>" target="_blank"><?= basename(parse_url($resource, PHP_URL_PATH)) ?></a>
                              <a href="javascript:void(0)" class="remove">remove</a>
                         </li>
                    <?php endforeach ?>
               <?php endif ?>
          </ul>
     </div>
     <div class="form-group btn-insine mr-2 ml-2 mb-1">
          <input type="text"type="text" id="resource-value" class="form-control form-control-sm" placeholder="Javascript/CSS Url">
          <a class="btn-add-resource" href="javascript:void(0)"><i class="icon-plus icons"></i></a>
     </div>
     <small class="form-text mr-2 ml-2 text-muted">
     <ul>
          <li>Paste a direct CSS/JS URL</li>
          <li>Type a library name to fetch from CDNJS</li>
     </ul>
     </small>
</div>