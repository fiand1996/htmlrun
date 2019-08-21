/**
 * HTMLRun Core.js
 * 
 * @date 07 June 2019
 * @version 1.0
 * @copyright Fiand T <fiand96@yahoo.com>
 * @author Fiand T (https://github.com/fiand1996)
 */

$(function () {
     HTMLRun.preloader(0);
});

var HTMLRun = {};

topbar.config({
    autoRun      : false, 
    barThickness : 2,
    barColors    : {
      '0'        : 'rgb(41, 121, 255)',
      '.3'       : 'rgb(41, 121, 255)',
      '1.0'      : 'rgb(41, 121, 255)'
    },
    shadowBlur   : 0,
    shadowColor  : 'rgba(0, 0, 0, .5)'
  });

HTMLRun.preloader = function (status = 1) {
     $(document).ready(function () {
          if (status == 1) {
               $("body").addClass('onprogress');
          } else {
               setTimeout(function () {
                    $("body").removeClass('onprogress');
               }, 300);
          }
     });
}

HTMLRun.Confirm = function (data = {}) {
     data = $.extend({}, {
          title: "Are you sure?",
          content: "It is not possible to get back removed data!",
          confirmText: "Yes, Delete",
          cancelText: "Cancel",
          confirm: function () {},
          cancel: function () {},
     }, data);

     $.confirm({
          title: data.title,
          content: data.content,
          theme: 'supervan',
          animation: 'opacity',
          closeAnimation: 'opacity',
          columnClass: typeof data.columnClass !== 'undefined' ? data.columnClass : "col-md-4",
          buttons: {
               confirm: {
                    text: data.confirmText,
                    btnClass: typeof data.btnClass !== 'undefined' ? data.btnClass : "btn btn-sm btn-danger mr-3",
                    keys: ['enter'],
                    action: typeof data.confirm === 'function' ? data.confirm : function () {}
               },
               cancel: {
                    text: data.cancelText,
                    btnClass: "btn btn-sm btn-dark",
                    keys: ['esc'],
                    action: typeof data.cancel === 'function' ? data.cancel : function () {}
               },
          }
     });
}

HTMLRun.Alert = function (data = {}) {
     data = $.extend({}, {
          title: "Error",
          content: "Oops! An error occured. Please try again later!",
          confirmText: "Close",
          confirm: function () {},
     }, data);

     $.alert({
          title: data.title,
          content: data.content,
          theme: 'supervan',
          animation: 'opacity',
          closeAnimation: 'opacity',
          buttons: {
               confirm: {
                    text: data.confirmText,
                    btnClass: "btn btn-sm btn-dark",
                    keys: ['enter'],
                    action: typeof data.confirm === 'function' ? data.confirm : function () {}
               },
          }
     });
}