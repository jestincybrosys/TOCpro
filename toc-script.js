jQuery(document).ready(function($) {
    // Smooth scrolling for TOC links
    $('.toc a').click(function(event) {
        event.preventDefault();
        var target = $(this).attr('href');
        $('html, body').animate({
            scrollTop: $(target).offset().top
        }, 800); // You can adjust the scroll speed by changing the duration (800ms in this example).
    });
});
