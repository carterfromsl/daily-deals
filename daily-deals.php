<?php
/*
Plugin Name: Daily Deals Rotator
Description: Manage an automatic daily deal promo rotator with images, captions, and links.
Version: 1.0.5
Author: StratLab Marketing
Author URI: https://strategylab.ca/
Text Domain: daily-deals
Requires at least: 6.0
Requires PHP: 7.0
Update URI: https://github.com/carterfromsl/daily-deals/
*/

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Connect with the StratLab Auto-Updater for plugin updates
add_action('plugins_loaded', function() {
    if (class_exists('StratLabUpdater')) {
        if (!function_exists('get_plugin_data')) {
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        
        $plugin_file = __FILE__;
        $plugin_data = get_plugin_data($plugin_file);

        do_action('stratlab_register_plugin', [
            'slug' => plugin_basename($plugin_file),
            'repo_url' => 'https://api.github.com/repos/carterfromsl/daily-deals/releases/latest',
            'version' => $plugin_data['Version'], 
            'name' => $plugin_data['Name'],
            'author' => $plugin_data['Author'],
            'homepage' => $plugin_data['PluginURI'],
            'description' => $plugin_data['Description'],
            'access_token' => '', // Add if needed for private repo
        ]);
    }
});

// Add a new submenu under Settings
add_action('admin_menu', function () {
    add_options_page('Daily Deals', 'Daily Deals', 'manage_options', 'daily-deals', 'daily_deals_settings_page');
});

// Enqueue the necessary styles and scripts for both admin and frontend
function daily_deals_enqueue_scripts($hook) {
    if ($hook == 'settings_page_daily-deals') {
        // Admin-specific styles and scripts
        wp_enqueue_media();
        wp_enqueue_style('daily-deals-admin-styles', plugin_dir_url(__FILE__) . 'admin-styles.css');
        wp_enqueue_script('daily-deals-admin-script', plugin_dir_url(__FILE__) . 'daily-deals-admin.js', array('jquery'), null, true);
    } else {
        // Frontend-specific styles and scripts
        wp_enqueue_style('daily-deals-frontend-styles', plugin_dir_url(__FILE__) . 'daily-deals.css');
        wp_enqueue_script('daily-deals-frontend-script', plugin_dir_url(__FILE__) . 'daily-deals.js?v=1.1', array('jquery'), null, true);
    }
}
add_action('admin_enqueue_scripts', 'daily_deals_enqueue_scripts');
add_action('wp_enqueue_scripts', 'daily_deals_enqueue_scripts');

// Settings page content
function daily_deals_settings_page()
{
    if (!current_user_can('manage_options')) {
        return;
    }
    
    // Save options
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day) {
            update_option("daily_deals_{$day}_promo_image", $_POST["daily_deals_{$day}_promo_image"] ?? '');
            update_option("daily_deals_{$day}_mobile_image", $_POST["daily_deals_{$day}_mobile_image"] ?? '');
            update_option("daily_deals_{$day}_caption", $_POST["daily_deals_{$day}_caption"] ?? '');
            update_option("daily_deals_{$day}_link", $_POST["daily_deals_{$day}_link"] ?? '');
            update_option("daily_deals_{$day}_enabled", isset($_POST["daily_deals_{$day}_enabled"]) ? 1 : 0);
        }
    }

	// Form for each day
	echo '<div class="wrap"><h1>Daily Deals</h1>';
	echo '<p>Place shortcode <code>[daily_deals nav="1"]</code> anywhere to display the daily deal rotator!</p>';
	echo '<p>Place shortcode <code>[daily_deals_widget image-size="responsive"]</code> to just show today\'s promo. Use image-size="mobile" to show the mobile version on all screen sizes.</p>';
	echo '<form method="post"><div class="day-form-wrap">';
	foreach (['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $day) {
		$lower_day = strtolower($day);
	        $enabled = get_option("daily_deals_{$lower_day}_enabled");
	        $promo_image = get_option("daily_deals_{$lower_day}_promo_image");
	        $mobile_image = get_option("daily_deals_{$lower_day}_mobile_image");
	        $disabled_class = $enabled ? '' : 'disabled';
	        echo "<div class='day-form {$disabled_class}' id='dd-{$lower_day}'><h2>{$day}</h2>";
	        echo "<label class='day-check' for='daily_deals_{$lower_day}_enabled'><input type='checkbox' id='daily_deals_{$lower_day}_enabled' name='daily_deals_{$lower_day}_enabled' value='1' class='day-checkbox' " . checked(1, $enabled, false) . " /> Enable {$day}</label>";
	        echo "<div class='day-content'><div class='day-input'><label for='daily_deals_{$lower_day}_link'>Promo Link:</label><input type='text' id='daily_deals_{$lower_day}_link' name='daily_deals_{$lower_day}_link' placeholder='https://...' value='" . esc_attr(get_option("daily_deals_{$lower_day}_link")) . "' /></div>";
	        echo "<div class='day-images'><div class='day-image'><label>Promo Image:</label>";
	        echo "<input type='hidden' name='daily_deals_{$lower_day}_promo_image' id='daily_deals_{$lower_day}_promo_image' value='" . esc_attr($promo_image) . "' />";
	        echo "<button type='button' class='button select-image' data-target='#daily_deals_{$lower_day}_promo_image'>Select Image</button>";
	        echo "<div class='image-preview' id='preview_{$lower_day}_promo_image'>";
	        if ($promo_image) {
	            echo "<img src='" . esc_url($promo_image) . "' style='max-width: 150px;'><button type='button' class='button remove-image' title='Remove Image'>×</button>";
	        }
	        echo "</div></div>";
	
	        echo "<div class='day-image'><label>Mobile Image:</label>";
	        echo "<input type='hidden' name='daily_deals_{$lower_day}_mobile_image' id='daily_deals_{$lower_day}_mobile_image' value='" . esc_attr($mobile_image) . "' />";
	        echo "<button type='button' class='button select-image' data-target='#daily_deals_{$lower_day}_mobile_image'>Select Image</button>";
	        echo "<div class='image-preview' id='preview_{$lower_day}_mobile_image'>";
	        if ($mobile_image) {
	            echo "<img src='" . esc_url($mobile_image) . "' style='max-width: 150px;'><button type='button' class='button remove-image' title='Remove Image'>×</button>";
	        }
	        echo "</div></div></div>";
	
		echo "<div class='day-input'><label for='daily_deals_{$lower_day}_caption'>Caption:</label><textarea id='daily_deals_{$lower_day}_caption' name='daily_deals_{$lower_day}_caption'>" . esc_textarea(get_option("daily_deals_{$lower_day}_caption")) . "</textarea></div></div></div>";
	}
	echo '</div><input type="submit" value="Save Changes" class="button button-primary"></form></div>';
}

// Shortcode to display daily deals
add_shortcode('daily_deals', function ($atts) {
	$atts = shortcode_atts(array(
		'nav' => true, // Default to true to show navigation
		), $atts, 'daily_deals');

	$output = '<div class="daily-deals-wrap">';
    
	if ($atts['nav']) {
		$output .= '<nav class="dd-nav">';
		$current_day = strtolower(wp_date('l'));
		foreach (['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $day) {
			$lower_day = strtolower($day);
			$enabled = get_option("daily_deals_{$lower_day}_enabled");
			$active_class = ($lower_day === $current_day) ? ' active-nav' : '';
			if ($enabled) {
				$output .= "<button type='button' class='dd-nav-button{$active_class}' data-day='{$lower_day}'>{$day}</button>";
			}
		}
		$output .= '</nav>';
	}
	$output .= '<ul class="daily-deals-list">';
    
	foreach (['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $day) {
		$lower_day = strtolower($day);
		$enabled = get_option("daily_deals_{$lower_day}_enabled");
	        $promo_image = esc_url(get_option("daily_deals_{$lower_day}_promo_image"));
	        $mobile_image = esc_url(get_option("daily_deals_{$lower_day}_mobile_image"));
	        $caption = esc_html(get_option("daily_deals_{$lower_day}_caption"));
	        $link = esc_url(get_option("daily_deals_{$lower_day}_link"));
	        $active_class = ($lower_day === $current_day) ? ' active-deal' : '';
		
		if ($enabled && $promo_image) {
			$output .= "<li id='dd-{$lower_day}' class='daily-deal{$active_class}'>";
			if ($link) {
				$output .= "<a href='{$link}' target='_blank'>";
			}
			if ($mobile_image) {
				$output .= "<img class='dd-mobile' src='{$mobile_image}' alt='{$lower_day}' />";
			}
			$output .= "<img class='dd-promo' src='{$promo_image}' alt='{$lower_day}' />";
			if ($caption) {
				$output .= "<span class='dd-caption'>{$caption}</span>";
			}
			if ($link) {
				$output .= "</a>";
			}
			$output .= "</li>";
		}
	}
	$output .= '</ul></div>';
    
	return $output;
});

// Single day deal widget shortcode
add_shortcode('daily_deals_widget', function ($atts) {
	$atts = shortcode_atts(array(
	    'image-size' => 'responsive', // Default to responsive, can be 'responsive', 'desktop', 'mobile'
	), $atts, 'daily_deals_widget');

	$current_day = strtolower(wp_date('l'));
	$enabled = get_option("daily_deals_{$current_day}_enabled");
	$promo_image = esc_url(get_option("daily_deals_{$current_day}_promo_image"));
	$mobile_image = esc_url(get_option("daily_deals_{$current_day}_mobile_image"));
	$link = esc_url(get_option("daily_deals_{$current_day}_link"));
	$caption = esc_html(get_option("daily_deals_{$current_day}_caption"));

	if (!$enabled || !$promo_image) {
	    return ''; // Return nothing if no deal is enabled for today
	}

	$output = "<div class='daily-deal-widget'>";
	if ($link) {
		$output .= "<a href='{$link}' target='_blank'>";
	}
    
	switch ($atts['image-size']) {
		case 'desktop':
			$output .= "<img class='dd-promo' src='{$promo_image}' alt='$current_day' />";
			break;
		case 'mobile':
			$output .= "<img class='dd-mobile-widget' src='" . ($mobile_image ? $mobile_image : $promo_image) . "' alt='$current_day' />";
			break;
		case 'responsive':
			default:
			if ($mobile_image) {
				$output .= "<img class='dd-mobile' src='{$mobile_image}' alt='$current_day' />";
			}
			$output .= "<img class='dd-promo' src='{$promo_image}' alt='$current_day' />";
			break;
	}
	if ($caption) {
		$output .= "<span class='dd-caption'>{$caption}</span>";
	}
	if ($link) {
		$output .= "</a>";
	}
	$output .= "</div>";
	
	return $output;
});

?>
