<?php 

  
function paddle_payment_gateway_dashboard_widgets() {

	// Globalize the metaboxes array, this holds all the widgets for wp-admin.
	global $wp_meta_boxes;
	wp_add_dashboard_widget('paddle_woocommerce_widget', 'Paddle Payment Gateway for WooCommerce', function() {
		echo '<p><a href="https://themebing.com/shop/plugins/paddle-payment-gateway-plugin-for-woocommerce/" target="_blank">
		<img style="width: 100%" src="https://ps.w.org/themebing-paddle-payment-gateway-for-woocommerce/assets/banner-772x250.jpg" alt="Paddle Payment Gateway for EDD"></a></p>';
	});

    // Then we make a backup of your widget.
    $wporg_widget = $wp_meta_boxes['dashboard']['normal']['core']['paddle_woocommerce_widget'];
 
    // We then unset that part of the array.
    unset( $wp_meta_boxes['dashboard']['normal']['core']['paddle_woocommerce_widget'] );
 
    // Now we just add your widget back in.
    $wp_meta_boxes['dashboard']['side']['core']['paddle_woocommerce_widget'] = $wporg_widget;
}
add_action('wp_dashboard_setup', 'paddle_payment_gateway_dashboard_widgets');
