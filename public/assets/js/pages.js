/**
 * HTMLRun Pages.js
 * 
 * @date 07 June 2019
 * @version 1.0
 * @copyright Fiand T <fiand96@yahoo.com>
 * @author Fiand T (https://github.com/fiand1996)
 */

/**
 * Load More
 * @var window.loadmore Global object to hold callbacks etc.
 */
window.loadmore = {}
HTMLRun.LoadMore = function()
{
    $("body").on("click", ".js-loadmore-btn", function(){
        var _this = $(this);
        var _parent = _this.parents(".loadmore");
        var id = _this.data("loadmore-id");

        if(!_parent.hasClass("onprogress")){
            _parent.addClass("onprogress");
            topbar.show();
            $.ajax({
                url: _this.attr("href"),
                dataType: 'html',
                error: function(){
                    _parent.fadeOut(200);

                    if (typeof window.loadmore.error === "function") {
                        window.loadmore.error(); // Error callback
                    }
                },
                success: function(Response){
                    var resp = $(Response);
                    var $wrp = resp.find(".js-loadmore-content[data-loadmore-id='"+id+"']");

                    if($wrp.length > 0){
                        var wrp = $(".js-loadmore-content[data-loadmore-id='"+id+"']");
                        wrp.append($wrp.html());

                        if (typeof window.loadmore.success === "function") {
                            window.loadmore.success(); // Success callback
                        }
                    }

                    if(resp.find(".js-loadmore-btn[data-loadmore-id='"+id+"']").length == 1){
                        _this.attr("href", resp.find(".js-loadmore-btn[data-loadmore-id='"+id+"']").attr("href"));
                        _parent.removeClass('onprogress none');
                    }else{
                        _parent.hide();
                    }
                    topbar.hide();
                }
            });
        }

        return false;
    });

    $(".js-loadmore-btn.js-autoloadmore-btn").trigger("click");
}

HTMLRun.RemoveSnippet = function () {
     $("body").on("click", "a.item-remove", function () {
          var item = $(this).parents(".item");
          var id = $(this).data("id");

          HTMLRun.Confirm({
               title: "Are you sure to remove " + $(this).data("name") + "?",
               columnClass: "col-md-12",
               confirm: function () {
                    $.ajax({
                         url: window.location,
                         type: 'POST',
                         dataType: 'json',
                         data: {
                              action: "remove",
                              id: id
                         },
                         success: function (resp) {
                              if (resp.status == 1) {
                                   // HTMLRun.Alert({
                                   //      title: "Success",
                                   //      content: resp.message
                                   // });

                                   // item.fadeOut(500, function () {
                                   //      item.remove();
                                   // });
                                   // 
                                   item.addClass('removingItem');
                                   setTimeout(function () {
                                        item.addClass('removedItem');
                                   }, 300)
                              } else {
                                   HTMLRun.Alert({
                                        title: "Error",
                                        content: resp.message
                                   });
                              }
                         }
                    });
               }
          });
     });
}

HTMLRun.WindowResize = function () {
     $(window).on("resize", function (e) {
          var height = $(window).height() - $(".navbar").outerHeight();
          $("#content").height(height);
          $("#content").css("overflow", "auto");
          $("#sidebar").height(height);
     }).trigger("resize");
}