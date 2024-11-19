jQuery(document).ready(function($) {
    // Ensure ajaxurl is defined correctly
    if (typeof ajaxurl === 'undefined') {
        ajaxurl = daily_deals_data.ajax_url;
    }

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

    // Set the current day as active when the page loads based on WordPress timezone
    $.ajax({
        url: ajaxurl, // WordPress AJAX handler
        method: 'POST',
        data: {
            action: 'get_wordpress_timezone_day'
        },
        success: function(response) {
            console.log('AJAX Response:', response); // Debugging log to check AJAX response
            let currentDay = response.day.toLowerCase();
            console.log('Current Day from Response:', currentDay); // Log the current day received from backend
            let todayButton = $('.dd-nav-button[data-day="' + currentDay + '"]');
            
            if (todayButton.length) {
                // If navigation is present and button exists, activate the current day
                todayButton.trigger('click');
            } else {
                // If navigation is not present, directly set the active deal
                $('.daily-deal').removeClass('active-deal');
                $('#dd-' + currentDay).addClass('active-deal');
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', error); // Log any AJAX errors
        }
    });
});
