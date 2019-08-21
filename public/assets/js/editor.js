/**
 * HTMLRun Editor.js
 * 
 * @date 07 June 2019
 * @version 1.0
 * @copyright Fiand T <fiand96@yahoo.com>
 * @author Fiand T (https://github.com/fiand1996)
 */

$(function () {
     HTMLRun.EditorInit();
});

var htmlSelector = '#cm-html';
var cssSelector = '#cm-css';
var jsSelector = '#cm-js';
var delay;

var editorHtml = CodeMirror.fromTextArea($(htmlSelector).get(0), {
     styleActiveLine: true,
     styleSelectedText: true,
     selectionPointer: true,
     showTrailingSpace: true,
     autoCloseTags: true,
     autoCloseBrackets: true,
     foldGutter: true,
     dragDrop: true,
     lint: true,
     matchBrackets: true,
     autofocus: false,
     lineNumbers: true,
     lineWrapping: true,
     mode: "text/html",
     theme: "material",
     keyMap: "sublime",
     matchTags: true,
     gutters: ["CodeMirror-lint-markers", "CodeMirror-linenumbers", "CodeMirror-foldgutter"],
     extraKeys: {
          "Ctrl-Space": "autocomplete",
          'Enter': 'emmetInsertLineBreak',
          'Tab': 'emmetExpandAbbreviation',
          "Ctrl-J": "toMatchingTag",
          "Ctrl-Q": function (cm) {
               cm.foldCode(cm.getCursor());
          },
          "Ctrl-Enter": function (cm) {
               $('.btn-run').trigger("click");
               return false;
          }
     }
});

var editorCss = CodeMirror.fromTextArea($(cssSelector).get(0), {
     styleActiveLine: true,
     styleSelectedText: true,
     selectionPointer: true,
     showTrailingSpace: true,
     autoCloseTags: true,
     autoCloseBrackets: true,
     foldGutter: true,
     dragDrop: true,
     lint: true,
     matchBrackets: true,
     autofocus: false,
     lineNumbers: true,
     lineWrapping: true,
     mode: "text/css",
     theme: "material",
     keyMap: "sublime",
     gutters: ["CodeMirror-lint-markers", "CodeMirror-linenumbers", "CodeMirror-foldgutter"],
     extraKeys: {
          "Ctrl-Space": "autocomplete",
          'Enter': 'emmetInsertLineBreak',
          'Tab': 'emmetExpandAbbreviation',
          "Ctrl-Q": function (cm) {
               cm.foldCode(cm.getCursor());
          },
          "Ctrl-Enter": function (cm) {
               $('.btn-run').trigger("click");
               return false;
          }
     }
});

var editorJs = CodeMirror.fromTextArea($(jsSelector).get(0), {
     styleActiveLine: true,
     styleSelectedText: true,
     selectionPointer: true,
     showTrailingSpace: true,
     autoCloseTags: true,
     autoCloseBrackets: true,
     foldGutter: true,
     dragDrop: true,
     lint: true,
     matchBrackets: true,
     autofocus: false,
     lineNumbers: true,
     lineWrapping: true,
     mode: "text/javascript",
     lint: {
         esversion: 6
     },
     theme: "material",
     keyMap: "sublime",
     gutters: ["CodeMirror-lint-markers", "CodeMirror-linenumbers", "CodeMirror-foldgutter"],
     extraKeys: {
          "Ctrl-Space": "autocomplete",
          'Enter': 'emmetInsertLineBreak',
          'Tab': 'emmetExpandAbbreviation',
          "Ctrl-Q": function (cm) {
               cm.foldCode(cm.getCursor());
          },
          "Ctrl-Enter": function (cm) {
               $('.btn-run').trigger("click");
               return false;
          }
     }
});

HTMLRun.EditorInit = function () {

     $("body").on("input focus", "form .form-control", function () {
          if ($(this).val().length > 0) {
               $(this).css("box-shadow", "0 0 0 1px #e9e9ea");
          }
     });

     $('body').on("click", ".dropdown-menu", function (e) {
          $(this).parent().is(".show") && e.stopPropagation();
     });

     $("#tabs ul").sortable();
     //$("#tabs ul").disableSelection();

     $("body").on("click", "#tabs .tabItem a", function () {
          var li = $(this).parents("li");
          var ul = li.parents("ul");

          ul.find("li").each(function() {
            $(this).removeClass('active');
          });

          li.addClass('active');

          $(".tabsContainer").find(".tabCont").each(function() {
            $(this).addClass('hidden');
          });
          
          $("#" + $(this).data("lang")).removeClass('hidden');
          return false;
     });

     $('body').on("change", ".changefont", function (e) {
          for (var i = 1; i < 5; i++) {
              $(".CodeMirror-wrap").removeClass('fontSize_' + i);
          }

          if ($(this).val() == 1) {
               $(".CodeMirror-wrap").addClass('fontSize_1');
          } else if ($(this).val() == 2) {
               $(".CodeMirror-wrap").addClass('fontSize_2');
          } else if ($(this).val() == 3) {
               $(".CodeMirror-wrap").addClass('fontSize_3');
          } else if ($(this).val() == 4) {
               $(".CodeMirror-wrap").addClass('fontSize_4');
          }
          
     });

      editorHtml.on("change", function () {
          $("#tabs ul").find("[data-lang='html']").addClass('modified');
     });

     editorCss.on("change", function () {
          $("#tabs ul").find("[data-lang='css']").addClass('modified');
     });

     editorJs.on("change", function () {
          $("#tabs ul").find("[data-lang='js']").addClass('modified');
     });

     HTMLRun.EditorResize();
     HTMLRun.EditorAutoPreview();
     HTMLRun.EditorPreview();
     HTMLRun.EditorResource();
     HTMLRun.EditorSortcut();
     HTMLRun.EditorRunSnippet();
     HTMLRun.EditorDownloadSnippet();
     HTMLRun.EditorEmbedSnippet();
     HTMLRun.EditorClear();
}

HTMLRun.EditorAutoPreview = function () {
     editorHtml.on("change", function () {
          clearTimeout(delay);
          delay = setTimeout(HTMLRun.EditorPreview(), 300);
     });

     editorCss.on("change", function () {
          clearTimeout(delay);
          delay = setTimeout(HTMLRun.EditorPreview(), 300);
     });

     editorJs.on("change", function () {
          clearTimeout(delay);
          delay = setTimeout(HTMLRun.EditorPreview(), 300);
     });

     setTimeout(HTMLRun.EditorPreview(), 300);
}

HTMLRun.EditorPreview = function () {
     if (true == false) {
          var previewFrame = document.getElementById('preview');
          var preview = previewFrame.contentDocument || previewFrame.contentWindow.document;
          preview.open();
          $('.resources').each(function () {
               if ($(this).val().search(".css") > 0) {
                    preview.write('<link href="'+$(this).val()+'" rel="stylesheet" type="text/css" />');
               } else {
                    preview.write('<script type="text/javascript" src="'+$(this).val()+'"></script>');
               }  
          });
          preview.write('<style>' + editorCss.getValue() + '</style>');
          preview.write(editorHtml.getValue());
          preview.write('<script>' + editorJs.getValue() + '</script>');
          preview.close();
     }
}

HTMLRun.EditorResource = function () {
     $("#resource-lists").sortable();
     $("#resource-lists").disableSelection();

     $('body').on("click", ".btn-add-resource", function (e) {
          var resource = $('#resource-value').val();
          var li = "";

          if (!resource || !validURL(resource)) {
               return false;
          }

          resource = resource.replace(/\/$/, '');

          var resource_name = resource.substring(resource.lastIndexOf('/') + 1);

          li = '<li><input type="hidden" class="resources" name="resources[]" value="' + resource + '">' +
               '<a title="' + resource + '" href="' + resource + '" target="_blank">' + resource_name + '</a>' +
               '<a href="javascript:void(0)" class="remove">remove</a></li>';

          $('#resource-lists').append(li);
          $('#resource-value').val("");
     });

     $('body').on("keyup", "#resource-value", function (e) {
          if ($(this).is(":focus") && e.key == "Enter") {
             $('.btn-add-resource').trigger("click");
         }
     });

     $('#resource-value').autocomplete({
          scroll: true,
          minLength: 2,
          source: function (request, response) {
               $.ajax({
                    url: "https://api.cdnjs.com/libraries",
                    dataType: "json",
                    data: {
                         search: $('#resource-value').val(),
                         fields: "name,filename,version"
                    },
                    success: function (data) {
                         response($.map(data.results, function (item) {
                              return {
                                   label: item.filename + ' (' + item.version + ')',
                                   value: item.latest
                              }
                         }));
                    }
               });
          }
     });

     $('body').on("click", ".remove", function (e) {
          $(this).parents("li").remove();
     });
}

HTMLRun.EditorSortcut = function () {
     shortcut.add("ctrl+s", function () {
          $('.btn-update').trigger("click");
     });

     shortcut.add("ctrl+enter", function () {
          $('.btn-run').trigger("click");
     });
}

HTMLRun.EditorRunSnippet = function () {
     $("body").on("click", ".btn-run", function () {
          var _this = $(this);
          var resources = [];

          $('.resources').each(function () {
               resources.push($(this).val());
          });

          $.ajax({
               url: window.location,
               type: "post",
               dataType: 'json',
               data: {
                    action: 'run',
                    html: editorHtml.getValue(),
                    css: editorCss.getValue(),
                    js: editorJs.getValue(),
                    resources: resources
               },
               beforeSend: function () {
                    $("#editor .resultsPanel img").css("display", "block");
                    _this.attr("disabled", true);
                    topbar.show();
               },
               complete: function () {
                    $("#editor .resultsPanel img").css("display", "none");
                    _this.attr("disabled", false);
                    $("#editor .resultsPanel").addClass('bg-white');
                    topbar.hide();
               },
               success: function (resp) {
                    if (resp.status == 1) {
                         $("#editor .resultsPanel iframe").attr("src", resp.result);
                    } else {
                         HTMLRun.Alert({
                              title: "Error",
                              content: resp.message
                         })
                    }
               }
          });
     });
}

HTMLRun.EditorAutorunSnippet = function () {
     $.ajax({
          url: window.location,
          type: "post",
          dataType: 'json',
          data: {
               action: 'autorun'
          },
          beforeSend: function () {
               $("#editor .resultsPanel img").css("display", "block");
          },
          success: function (resp) {
               $("#editor .resultsPanel img").css("display", "none");
               $("#editor .resultsPanel").addClass('bg-white');
               $("#editor .resultsPanel iframe").attr("src", resp.result);
          }
     });
}

HTMLRun.EditorSaveSnippet = function () {
     $("body").on("submit", ".form-save", function () {
          var submitable = true;
          $(this).find(".form-control.required").not(":disabled").each(function () {
               if (!$(this).val()) {
                    $(this).css("box-shadow", "0 0 0 1px red");
                    submitable = false;
               }
          });

          if (!submitable) {
               return false;
          }

          var form = $(this).serializeArray();

          $('.resources').each(function () {
               form.push({
                    name: 'resources[]',
                    value: $(this).val()
               });
          });

          form.push({
               name: 'action',
               value: 'save'
          });
          form.push({
               name: 'html',
               value: editorHtml.getValue()
          });
          form.push({
               name: 'css',
               value: editorCss.getValue()
          });
          form.push({
               name: 'js',
               value: editorJs.getValue()
          });

          $.ajax({
               url: $(this).attr("action"),
               type: "post",
               dataType: 'jsonp',
               data: form,
               beforeSend: function () {
                    //_this.attr("disabled", true);
               },
               success: function (resp) {
                    if (resp.status == 1) {
                         window.location.href = resp.result;
                    } else {
                         HTMLRun.Alert({
                              title: "Error",
                              content: resp.message
                         })
                    }
               }
          });

          return false;
     });
}

HTMLRun.EditorUpdateSnippet = function () {
     $("body").on("click", ".btn-update", function () {
          var _this = $(this);
          var resources = [];

          $('.resources').each(function () {
               resources.push($(this).val());
          });

          $.ajax({
               url: window.location,
               type: "post",
               dataType: 'json',
               data: {
                    action: 'update',
                    html: editorHtml.getValue(),
                    css: editorCss.getValue(),
                    js: editorJs.getValue(),
                    resources: resources
               },
               beforeSend: function () {
                    _this.attr("disabled", true);
                    topbar.show();
               },
               complete: function () {
                    _this.attr("disabled", false);
                    topbar.hide();
               }, 
               success: function (resp) {
                    if (resp.status == 1) {
                         // HTMLRun.Alert({
                         //      title: "Success",
                         //      content: resp.message
                         // });
                         $("#tabs ul").find("[data-lang='html']").removeClass('modified');
                         $("#tabs ul").find("[data-lang='css']").removeClass('modified');
                         $("#tabs ul").find("[data-lang='js']").removeClass('modified');
                    } else {
                         HTMLRun.Alert({
                              title: "Error",
                              content: resp.message
                         });
                    }
               }
          });
     });
}

HTMLRun.EditorUpdateInfoSnippet = function () {
     $("body").on("submit", ".form-update", function () {
          var submitable = true;
          var url = $(this).attr("action");

          $(this).find(".form-control.required").not(":disabled").each(function () {
               if (!$(this).val()) {
                    $(this).css("box-shadow", "0 0 0 1px red");
                    submitable = false;
               }
          });

          if (!submitable) {
               return false;
          }

          $.ajax({
               url: url,
               type: "post",
               dataType: 'jsonp',
               data: $(this).serialize() + '&action=updateinfo',
               beforeSend: function () {
                    //_this.attr("disabled", true);
               },
               success: function (resp) {
                    if (resp.status == 1) {
                         HTMLRun.Alert({
                              title: "Success",
                              content: resp.message
                         });
                         $(".col-history").load(url + " .col-history>");
                         window.history.pushState({}, $("title").text(), url);
                    } else {
                         HTMLRun.Alert({
                              title: "Error",
                              content: resp.message
                         });
                    }
               }
          });

          return false;
     });
}

HTMLRun.EditorDownloadSnippet = function () {
     $("body").on("click", ".btn-download", function () {
          var iOS = /iPad|iPhone|iPod/.test(navigator.userAgent) && !window.MSStream;
          if (iOS) {
               window.open($(this).attr('data-link'), '_blank');
          } else {
               $.ajax({
                    url: $(this).attr('data-link'),
                    method: 'GET',
                    xhrFields: {
                         responseType: 'blob'
                    },
                    beforeSend: function () {
                         topbar.show();
                    },
                    complete: function () {
                         topbar.hide();
                    }, 
                    success: function (data, status, xhr) {
                         var a = document.createElement('a');
                         var url = window.URL.createObjectURL(data);
                         a.href = url;
                         a.download = xhr.getResponseHeader('filename');
                         document.body.append(a);
                         a.click();
                         a.remove();
                         window.URL.revokeObjectURL(url);
                    }
               });
               return false;
          }
     });
}

HTMLRun.EditorEmbedSnippet = function () {
     $("body").on("focus", ".embed-code", function () {
          $(this).select();
     });
}

HTMLRun.EditorClear = function () {
     $("body").on("click", ".btn-clear-editor", function () {
          editor.setValue("<?php\n");
          editor.clearHistory();
          editor.focus();
          editor.setCursor(editor.lineCount(), 0);
          $("#result").html("");
     });
}

HTMLRun.EditorResize = function () {
     var rowsplitsizes = localStorage.getItem('rowsplitsizes');

     rowsplitsizes = rowsplitsizes = rowsplitsizes ? JSON.parse(rowsplitsizes) : [50, 50];

     var rowsplit = Split(['#panel-left', '#panel-right'], {
          sizes: rowsplitsizes,
          gutterSize: 1,
          cursor: 'col-resize',
          onDragEnd: function () {
               localStorage.setItem('rowsplitsizes', JSON.stringify(rowsplit.getSizes()));
               setTimeout(function() {
                    $('.windowLabelCont .size').addClass('hidden');
               }, 1000)
          },
          onDrag: function () {
               $('.windowLabelCont .size').removeClass('hidden')
               .text(Math.round($('#panel-right').width()) + 'px');
          }
     });

     $('.windowLabelCont .size').removeClass('hidden')
     .text(Math.round($('#panel-right').width()) + 'px');

     setTimeout(function() {
          $('.windowLabelCont .size').addClass('hidden');
     }, 2000)

     $(window).on("resize", function (e) {
          var height = $(window).height() - $(".navbar").outerHeight();
          $("#sidebar").height(height);
          $(".tabsContainer").height(height - 30);
     }).trigger("resize");
}

HTMLRun.EditorThemes = function () {
     $("body").on("keyup", "#search", function () {

     });
}

function validURL(str) {
     var pattern = new RegExp('^(https?:\\/\\/)?' + // protocol
          '((([a-z\\d]([a-z\\d-]*[a-z\\d])*)\\.)+[a-z]{2,}|' + // domain name
          '((\\d{1,3}\\.){3}\\d{1,3}))' + // OR ip (v4) address
          '(\\:\\d+)?(\\/[-a-z\\d%_.~+]*)*' + // port and path
          '(\\?[;&a-z\\d%_.~+=-]*)?' + // query string
          '(\\#[-a-z\\d_]*)?$', 'i'); // fragment locator
     return !!pattern.test(str);
}