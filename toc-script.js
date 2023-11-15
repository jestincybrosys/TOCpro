jQuery(document).ready(function ($) {
  // Smooth scrolling for TOC links


  // Update the progress bar as the user scrolls
  $(window).scroll(function () {
    var scroll = $(window).scrollTop();
    var contentHeight = $("body").height() - $(window).height();
    var progress = (scroll / contentHeight) * 100;
    $(".tocpro-progress-bar").css("width", progress + "%");
  });
  

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


});

    document.addEventListener('DOMContentLoaded', function () {
        function syncFields(inputFields, sourceField) {
            var sourceValue = sourceField.value;
            inputFields.forEach(function (field) {
                field.value = sourceValue;
            });
        }

        function setupSync(linkButton, fields, isSyncing) {
            linkButton.addEventListener('click', function (event) {
                event.preventDefault();
                isSyncing = !isSyncing;
                linkButton.classList.toggle('sync', isSyncing);
                
                // If syncing is enabled, sync the fields
                if (isSyncing) {
                    syncFields(fields, fields[0]);
                }
            });

            fields.forEach(function (inputField) {
                inputField.addEventListener('input', function () {
                    // Check if syncing is enabled before syncing
                    if (isSyncing) {
                        syncFields(fields, inputField);
                    }
                });
            });
        }

        // Padding Fields
        var paddingLinkButton = document.querySelector('.padding-link-values');
        if (paddingLinkButton) {
            var isPaddingSyncing = false;
            var paddingFields = document.querySelectorAll('.padding-fields input');
            setupSync(paddingLinkButton, paddingFields, isPaddingSyncing);
        }

        // Margin Fields
        var marginLinkButton = document.querySelector('.margin-link-values');
        if (marginLinkButton) {
            var isMarginSyncing = false;
            var marginFields = document.querySelectorAll('.margin-fields input');
            setupSync(marginLinkButton, marginFields, isMarginSyncing);
        }
    });
