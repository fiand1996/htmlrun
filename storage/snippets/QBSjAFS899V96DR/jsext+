$(".animation-example").on("mouseover", function() {
  var anim = $(this).attr("data-anim");
  $(this).removeClass(anim);
  $(this).addClass(anim);

  $(this).one(
    "webkitAnimationEnd oanimationend MSAnimationEnd animationend",
    function(e) {
      // code to execute after transition ends
      $(this).removeClass(anim);
    }
  );
});