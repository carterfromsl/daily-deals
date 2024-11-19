jQuery(document).ready(function($) {
    // Handle navigation buttons to switch between deals
    $('.dd-nav-button').click(function() {
        let day = $(this).data('day');

        // Set the active navigation button
        $('.dd-nav-button').removeClass('active-nav');
        $(this).addClass('active-nav');

        // Set the active deal
        $('.daily-deal').removeClass('active-deal');
        $('#dd-' + day).addClass('active-deal');
    });
});
