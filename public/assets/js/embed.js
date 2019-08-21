/**
 * PHPRunning Embed.js
 * 
 * @date 07 June 2019
 * @version 1.0
 * @copyright Fiand T <fiand96@yahoo.com>
 * @author Fiand T (https://github.com/fiand1996)
 */

var PHPRunning = {};

PHPRunning.EmbedInit = function () {
     $("body").on("click", ".normalRes .nav-embed", function () {
          var li = $(this).parents("li");
          var ul = li.parents("ul");

          ul.find("li").each(function() {
            $(this).removeClass('active');
          });

          li.addClass('active');

          $("#tabs").find(".tCont").each(function() {
            $(this).removeClass('active');
          });
          
          $("#" + $(this).data("id")).addClass('active');
     });
}

$(function () {
     PHPRunning.EmbedInit();
});