<script type="text/javascript" src="<?= APPURL ?>/assets/js/plugins.js?v=<?= rand(1,9999) ?>"></script>
<script type="text/javascript" src="<?= APPURL ?>/assets/js/codemirror.js?v=<?= rand(1,9999) ?>"></script>
<script type="text/javascript" src="<?= APPURL ?>/assets/js/core.js?v=<?= rand(1,9999) ?>"></script>
<script type="text/javascript" src="<?= APPURL ?>/assets/js/editor.js?v=<?= rand(1,9999) ?>"></script>
<script>
     <?php if ($Snippet->isAvailable()): ?>

          $(function () {
               HTMLRun.EditorAutorunSnippet();
               <?php if ($AuthUser && ($AuthUser->get("id") == $Snippet->get("user_id"))): ?>
                    HTMLRun.EditorUpdateSnippet();
                    HTMLRun.EditorUpdateInfoSnippet();
               <?php endif ?>
          });

     <?php else: ?>
          <?php if ($AuthUser): ?>
               $(function () {
                    HTMLRun.EditorSaveSnippet();
                });
           <?php endif ?>
     <?php endif ?>

</script>