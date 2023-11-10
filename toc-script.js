jQuery(document).ready(function ($) {
  // Smooth scrolling for TOC links
  $(".tocpro a").click(function (event) {
    event.preventDefault();
    var target = $(this).attr("href");

    // Scroll to the target element with an additional 20px gap from the top
    $("html, body").animate(
        {
            scrollTop: $(target).offset().top - offset,
        },
        800 // You can adjust the scroll speed by changing the duration (800ms in this example).
    );
});

  // Update the progress bar as the user scrolls
  $(window).scroll(function () {
    var scroll = $(window).scrollTop();
    var contentHeight = $("body").height() - $(window).height();
    var progress = (scroll / contentHeight) * 100;
    $(".tocpro-progress-bar").css("width", progress + "%");
  });
});


