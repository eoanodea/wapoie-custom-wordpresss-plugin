<?php

/**
 * Plugin Name: WooCommerce Billing & Email Extension
 * Plugin URI: https://wspace.ie
 * Description: Remove the billing address fields for free virtual orders, and adds order links to order confirmation emails
 * Author: Eoan O'Dea
 * Author URI: https://wspace.ie/
 * Version: 4.4
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 *
 * @author  Eoan O'Dea
 * @since		1.0
 */


/**
 * Load css stylesheet
 */
function load_styles()
{
	$plugin_url = plugin_dir_url(__FILE__);
	$plugin_data = get_plugin_data(__FILE__);
	$plugin_version = $plugin_data['Version'];

	wp_enqueue_style('style', $plugin_url . 'style.css?' . $plugin_version);
	wp_enqueue_script('script', $plugin_url . 'script.js?' . $plugin_version);
}


/**
 * Removes the billing fields on checkout if the product is free
 */
function billing_fields($fields)
{
	global $woocommerce;

	// if the total is more than 0 then we still need the fields
	if (0 != $woocommerce->cart->total) {
		return $fields;
	}

	// return the regular billing fields if we need shipping fields
	if ($woocommerce->cart->needs_shipping()) {
		return $fields;
	}

	// we don't need the billing fields so empty all of them except the email
	unset($fields['billing_country']);
	unset($fields['billing_first_name']);
	unset($fields['billing_last_name']);
	unset($fields['billing_company']);
	unset($fields['billing_address_1']);
	unset($fields['billing_address_2']);
	unset($fields['billing_city']);
	unset($fields['billing_state']);
	unset($fields['billing_postcode']);
	unset($fields['billing_phone']);

	return $fields;
}

/**
 * Rename dashboard download tab to ebooks
 */
function custom_dashboard_tab_name($items)
{
	$items['downloads'] = __("eBooks", "woocommerce");
	return $items;
}

/**
 * Adds the order link to the email
 */
function add_link_back_to_order($order, $is_admin)
{
	if ($order->get_status() !== "completed") return;
	// Open the section with a paragraph so it is separated from the other content
	$link = '<p style="text-align: center;">';

	// Add the anchor link with the admin path to the order page
	$link .= '<a href="https://wapo.ie/my-account-2">';

	$buttonStyles = '
		background-color: #59bcb8;
		padding: 20px;
		text-align: center;
		color: #fff;
		border: none;';

	// Clickable text
	$link .= '<button style="' . $buttonStyles . '">View Purchase</button>';

	// Close the link
	$link .= '</a>';

	// Close the paragraph
	$link .= '</p>';

	// Return the link into the email
	echo $link;
}

/**
 * Modifying the account dashboard
 */
function modify_dashboard_page()
{

	$result = '<div class="dashboard-box-container">';
	$baseUrl = get_permalink();
	//Check if the courseware plugin is active
	if (is_plugin_active('wp-courseware/wp-courseware.php')) {

		//If so, add a courses button to the dashboard
		$url = $baseUrl . 'courses';
		$course_button = create_button($url, 'View Courses');
		$result .= $course_button;
		//Add the button to the result

	}
	//Create buttons for the orders and ebooks
	$order_button = create_button($baseUrl . 'orders', 'View Orders');
	$ebook_button = create_button($baseUrl . 'downloads', 'View eBooks');

	//Append to the result
	$result .= $ebook_button;
	$result .= $order_button;

	//Close the result div
	$result .= '</div>';

	//Echo the result to the frontend
	echo $result;
}

/**
 * Creates a button using a link and text
 * 
 * @param $link
 * @param $text
 */
function create_button($link, $text)
{
	return '<div class="account-button-container"><a href=' . $link . '><button class="account-button">' . $text . '</button></a></div>';
}

/**
 * Creates a shortcode radio section which determinds which form to display afterward
 */
function wpc7_extension_shortcode()
{
	return '<div id="form-wrapper none-selected" class="form-wrapper"></div>';
}

function get_shortcode_contact_form()
{
	if (isset($_REQUEST['shortcode'])) {
		echo do_shortcode($_REQUEST['shortcode']);
		$api_key = get_option('elementor_pro_convertkit_api_key');
		$tag_id = $_REQUEST['tag_id'];

?>
		<script>
			var form = jQuery('.wpcf7 > form');
			var key = '<?php echo $api_key ?>';
			var tag_id = '<?php echo $tag_id ?>';

			wpcf7.initForm(form);
			var urL = jQuery('.wpcf7 > form').attr('action').split('#');
			jQuery('.wpcf7 > form').attr('action', "#" + urL[1]);

			document.addEventListener("wpcf7mailsent", function(event) {
				const inputs = event.detail.inputs;
				const email = inputs.filter(dat => dat.name === 'your-email')[0]

				jQuery.ajax({
					url: `https://api.convertkit.com/v3/tags/${tag_id}/subscribe`,
					type: "POST",
					data: {
						api_key: key,
						email: email.value
					},
					success: function(res) {
						console.log('success tagging user!', res)
					},
					error: function(err) {
						console.log('error tagging user!', err)

					},
				})
			});
		</script>
<?php
	}
	wp_die();
}


add_shortcode('custom_convertkit_form', 'wpc7_extension_shortcode');

add_action('wp_ajax_request_custom_contact_form', 'get_shortcode_contact_form');
add_action('wp_ajax_nopriv_request_custom_contact_form', 'get_shortcode_contact_form');

add_action('wp_enqueue_scripts', 'load_styles');
add_action('woocommerce_email_after_order_table', 'add_link_back_to_order', 10, 2);

add_filter('woocommerce_account_menu_items', 'custom_dashboard_tab_name', 22, 1);
add_filter('woocommerce_billing_fields', 'billing_fields', 20);
add_filter('woocommerce_account_dashboard', 'modify_dashboard_page');
