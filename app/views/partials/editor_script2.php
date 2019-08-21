<script type="text/javascript" src="<?= APPURL ?>/assets/plugins/jquery/jquery-3.4.1.min.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<script type="text/javascript" src="<?= APPURL ?>/assets/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script type="text/javascript" src="<?= APPURL ?>/assets/plugins/codemirror/lib/codemirror.js"></script>
<script type="text/javascript" src="<?= APPURL ?>/assets/plugins/codemirror/keymap/sublime.js"></script>
<script type="text/javascript" src="<?= APPURL ?>/assets/plugins/codemirror/mode/xml/xml.js"></script>
<script type="text/javascript" src="<?= APPURL ?>/assets/plugins/codemirror/mode/javascript/javascript.js"></script>
<script type="text/javascript" src="<?= APPURL ?>/assets/plugins/codemirror/mode/htmlmixed/htmlmixed.js"></script>
<script type="text/javascript" src="<?= APPURL ?>/assets/plugins/codemirror/mode/css/css.js"></script>
<script type="text/javascript" src="<?= APPURL ?>/assets/plugins/codemirror/addon/emmet/dist/emmet-codemirror-plugin.js"></script>
<script type="text/javascript" src="<?= APPURL ?>/assets/plugins/codemirror/addon/selection/active-line.js"></script>
<script type="text/javascript" src="<?= APPURL ?>/assets/plugins/codemirror/addon/selection/mark-selection.js"></script>
<script type="text/javascript" src="<?= APPURL ?>/assets/plugins/codemirror/addon/selection/selection-pointer.js"></script>
<script type="text/javascript" src="<?= APPURL ?>/assets/plugins/codemirror/addon/edit/closetag.js"></script>
<script type="text/javascript" src="<?= APPURL ?>/assets/plugins/codemirror/addon/edit/closebrackets.js"></script>
<script type="text/javascript" src="<?= APPURL ?>/assets/plugins/codemirror/addon/edit/matchbrackets.js"></script>
<script type="text/javascript" src="<?= APPURL ?>/assets/plugins/codemirror/addon/edit/matchtags.js"></script>
<script type="text/javascript" src="<?= APPURL ?>/assets/plugins/codemirror/addon/edit/trailingSpace.js"></script>
<script type="text/javascript" src="<?= APPURL ?>/assets/plugins/codemirror/addon/hint/jshint.js"></script>
<script type="text/javascript" src="<?= APPURL ?>/assets/plugins/codemirror/addon/hint/csshint.js"></script>
<script type="text/javascript" src="<?= APPURL ?>/assets/plugins/codemirror/addon/hint/htmlhint.js"></script>
<script type="text/javascript" src="<?= APPURL ?>/assets/plugins/codemirror/addon/hint/show-hint.js"></script>
<script type="text/javascript" src="<?= APPURL ?>/assets/plugins/codemirror/addon/hint/css-hint.js"></script>
<script type="text/javascript" src="<?= APPURL ?>/assets/plugins/codemirror/addon/hint/javascript-hint.js"></script>
<script type="text/javascript" src="<?= APPURL ?>/assets/plugins/codemirror/addon/hint/xml-hint.js"></script>
<script type="text/javascript" src="<?= APPURL ?>/assets/plugins/codemirror/addon/hint/html-hint.js"></script>
<script type="text/javascript" src="<?= APPURL ?>/assets/plugins/codemirror/addon/lint/lint.js"></script>
<script type="text/javascript" src="<?= APPURL ?>/assets/plugins/codemirror/addon/lint/html-lint.js"></script>
<script type="text/javascript" src="<?= APPURL ?>/assets/plugins/codemirror/addon/lint/javascript-lint.js"></script>
<script type="text/javascript" src="<?= APPURL ?>/assets/plugins/codemirror/addon/lint/css-lint.js"></script>
<script type="text/javascript" src="<?= APPURL ?>/assets/plugins/codemirror/addon/fold/foldcode.js"></script>
<script type="text/javascript" src="<?= APPURL ?>/assets/plugins/codemirror/addon/fold/foldgutter.js"></script>
<script type="text/javascript" src="<?= APPURL ?>/assets/plugins/codemirror/addon/fold/brace-fold.js"></script>
<script type="text/javascript" src="<?= APPURL ?>/assets/plugins/codemirror/addon/fold/xml-fold.js"></script>
<script type="text/javascript" src="<?= APPURL ?>/assets/plugins/codemirror/addon/fold/indent-fold.js"></script>
<script type="text/javascript" src="<?= APPURL ?>/assets/plugins/codemirror/addon/fold/markdown-fold.js"></script>
<script type="text/javascript" src="<?= APPURL ?>/assets/plugins/codemirror/addon/fold/comment-fold.js"></script>
<script type="text/javascript" src="<?= APPURL ?>/assets/plugins/jquery-confirm/jquery-confirm.min.js"></script>
<script type="text/javascript" src="<?= APPURL ?>/assets/plugins/clipboard/clipboard.min.js"></script>
<script type="text/javascript" src="<?= APPURL ?>/assets/plugins/split/split.min.js"></script>
<script type="text/javascript" src="<?= APPURL ?>/assets/plugins/shortcut/shortcut.js"></script>
<script type="text/javascript" src="<?= APPURL ?>/assets/plugins/topbar/topbar.min.js"></script>
<script type="text/javascript" src="<?= APPURL ?>/assets/js/core.js?v=<?= rand(1,9999) ?>"></script>
<script type="text/javascript" src="<?= APPURL ?>/assets/js/anu.js?v=<?= rand(1,9999) ?>"></script>
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