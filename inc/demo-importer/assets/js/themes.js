(function($) {

    "use strict";

    $('.theme-filter-links li a').on('click', function(event) {
        event.preventDefault();

        // Remove 'current' class from the previous nav list items.
        $(this).parent().siblings().removeClass('current');

        // Add the 'current' class to this nav list item.
        $(this).parent().addClass('current');
    });
})(jQuery);