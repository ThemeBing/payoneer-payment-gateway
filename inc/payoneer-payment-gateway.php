<?php 

/**
 * Checked Woocommerce activation
 */
if( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ){

	 /*
	 * This action hook registers our PHP class as a WooCommerce payment gateway
	 */
	function payoneer_payment_gateway_class( $gateways ) {
		$gateways[] = 'WC_Payoneer_Gateway'; // your class name is here
		return $gateways;
	}

	add_filter( 'woocommerce_payment_gateways', 'payoneer_payment_gateway_class' );
	 
	/*
	 * The class itself, please note that it is inside plugins_loaded action hook
	 */
	function payoneer_init_gateway_class() {
	 
		class WC_Payoneer_Gateway extends WC_Payment_Gateway {
	 
	 		/**
	 		 * Class constructor
	 		 */
	 		public function __construct() {
	 
				$this->id = 'payoneer'; // payment gateway plugin ID
				$this->icon = plugin_dir_url( __DIR__ ) . 'assets/images/logo.png'; // URL of the icon that will be displayed on checkout page near your gateway name
				$this->has_fields = true; // in case you need a custom credit card form
				$this->method_title = 'Payoneer Gateway';
				$this->method_description = 'Take payoneer payments on your store.'; // will be displayed on the options page
			 
				// gateways can support subscriptions, refunds, saved payment methods,
				// but in this tutorial we begin with simple payments
				$this->supports = array(
					'products'
				);
			 
				// Method with all the options fields
				$this->init_form_fields();
			 
				// Load the settings.
				$this->init_settings();
				$this->enabled = $this->get_option( 'enabled' );
				$this->title = $this->get_option( 'title' );
				$this->description = $this->get_option( 'description' );
				$this->order_status = $this->get_option( 'order_status' );
				$this->payoneer_email = $this->get_option( 'payoneer_email' );
			 
				// This action hook saves the settings
				add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
	 
	 		}
	 
			/**
	 		 * Plugin options
	 		 */
	 		public function init_form_fields(){

	 			$this->form_fields = array(
					'enabled' => array(
						'title'       => esc_html__( 'Enable/Disable', 'wc-payoneer-payment-gateway' ),
						'label'       => esc_html__( 'Enable Payoneer Gateway', 'wc-payoneer-payment-gateway' ),
						'type'        => 'checkbox',
						'description' => '',
						'default'     => 'no'
					),
					'title' => array(
						'title'       => esc_html__( 'Title', 'wc-payoneer-payment-gateway' ),
						'type'        => 'text',
						'description' => esc_html__( 'This controls the title which the user sees during checkout.', 'wc-payoneer-payment-gateway' ),
						'default'     => esc_html__( 'Payoneer', 'wc-payoneer-payment-gateway' ),
						'desc_tip'    => true,
					),
					'description' => array(
						'title'       => esc_html__( 'Description', 'wc-payoneer-payment-gateway' ),
						'type'        => 'textarea',
						'description' => esc_html__( 'This controls the description which the user sees during checkout.', 'wc-payoneer-payment-gateway' ),
						'default'     => esc_html__( 'Please make a' , 'wc-payoneer-payment-gateway' ).' <a href="https://myaccount.payoneer.com/MainPage/Widget.aspx?w=MakeAPayment#/pay/makeapayment" target="_blank">'.esc_html__( 'payment' , 'wc-payoneer-payment-gateway' ).'</a> '. esc_html__( 'first, then fill up the form below.', 'wc-payoneer-payment-gateway' )
					),
					'order_status' => array(
	                    'title'       => esc_html__( 'Order Status', 'wc-payoneer-payment-gateway' ),
	                    'type'        => 'select',
	                    'class'       => 'wc-enhanced-select',
	                    'description' => esc_html__( 'Choose whether status you wish after checkout.', 'wc-payoneer-payment-gateway' ),
	                    'default'     => 'wc-on-hold',
	                    'desc_tip'    => true,
	                    'options'     => wc_get_order_statuses()
	                ),
	                'payoneer_email'  => array(
	                    'title'       => esc_html__( 'Payoneer Email', 'wc-payoneer-payment-gateway' ),
	                    'description' => esc_html__( 'Add your payoneer email address which will be shown in checkout page for receiving payments', 'wc-payoneer-payment-gateway' ),
	                    'type'        => 'text',
	                    'desc_tip'    => true
	                ),
				);
		 	}
	 
			/**
			 * You will need it if you want your custom credit card form
			 */
			public function payment_fields() {  ?>
				<div class="payment-method-payoneer">
					<?php
					// ok, let's display some description before the payment form
					if ( $this->description ) {
						echo wpautop( wptexturize( wp_kses( $this->description, array(
							'a' => array(
								'class' => array(),
								'href'  => array(),
								'rel'   => array(),
								'title' => array(),
								'target'=> array('_blank'),
							))
						)));
					}
					if ( $this->payoneer_email ) {
						echo wpautop( wptexturize( "Recipient payoneer account: ". $this->payoneer_email ) ); 
					} ?>
					
			    	<input class="widefat" type="email" name="payoneer_email" id="payoneer_email" placeholder="Payoneer email">
			
			    	<input class="widefat" type="text" name="payoneer_transaction_id" id="payoneer_transaction_id" placeholder="Transaction ID">

				</div>
	 
			<?php }

			/*
			 * We're processing the payments here
			 */
			public function process_payment( $order_id ) {

	            global $woocommerce;
	            $order = new WC_Order( $order_id );

	            $status = 'wc-' === substr( $this->order_status, 0, 3 ) ? substr( $this->order_status, 3 ) : $this->order_status;
	            // Mark as on-hold (we're awaiting the Payoneer)
	            $order->update_status( $status, esc_html__( 'Checkout with payoneer payment. ', 'wc-payoneer-payment-gateway' ) );

	            // Reduce stock levels
	            $order->reduce_order_stock();

	            // Remove cart
	            $woocommerce->cart->empty_cart();

	            // Return thankyou redirect
	            return array(
	                'result' => 'success',
	                'redirect' => $this->get_return_url( $order )
	            );

	        }
	 
	 	}

	} 
	
	add_action( 'plugins_loaded', 'payoneer_init_gateway_class' );


 	/**
     * Empty field validation
     */
    function payoneer_checkout_process(){

        if($_POST['payment_method'] != 'payoneer')
            return;

        $payoneer_email = sanitize_text_field( $_POST['payoneer_email'] );

        if( !isset($payoneer_email) || empty($payoneer_email) )
            wc_add_notice( esc_html__( 'Please enter your payoneer email address', 'wc-payoneer-payment-gateway'), 'error' );



        $payoneer_transaction_id = sanitize_text_field( $_POST['payoneer_transaction_id'] );

        if( !isset($payoneer_transaction_id) || empty($payoneer_transaction_id) )
            wc_add_notice( esc_html__( 'Please enter your payoneer transaction ID', 'wc-payoneer-payment-gateway' ), 'error' );



        $match_email = isset($payoneer_email) ? $payoneer_email : '';
        $validate_email = preg_match( '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/i',  $match_email );

        if( !empty($payoneer_email) && $validate_email == false )
            wc_add_notice( esc_html__( 'Incorrect email address.', 'wc-payoneer-payment-gateway'), 'error' );



        $match_id = isset($payoneer_transaction_id) ? $payoneer_transaction_id : '';
        $validate_id = preg_match( '/^[0-9]*$/',  $match_id );

		if( !empty($payoneer_transaction_id) && $validate_id == false )
            wc_add_notice( esc_html__( 'Only number is acceptable', 'wc-payoneer-payment-gateway'), 'error' );
        
    }

    add_action( 'woocommerce_checkout_process', 'payoneer_checkout_process' );


 	/**
     * Update Payoneer field to database
     */
    function payoneer_additional_field_update( $order_id ){

        if($_POST['payment_method'] != 'payoneer' )
            return;

        $payoneer_email = sanitize_text_field( $_POST['payoneer_email'] );
        $payoneer_transaction_id = sanitize_text_field( $_POST['payoneer_transaction_id'] );

        $email = isset($payoneer_email) ? $payoneer_email : '';
        $transaction = isset($payoneer_transaction_id) ? $payoneer_transaction_id : '';

        update_post_meta($order_id, '_payoneer_email', $email);
        update_post_meta($order_id, '_payoneer_transaction_id', $transaction);

    }

    add_action( 'woocommerce_checkout_update_order_meta', 'payoneer_additional_field_update' );



    /**
     * Admin order page Payoneer data output
     */
    function payoneer_admin_order_data( $order ){

        if( $order->get_payment_method() != 'payoneer' )
            return;


        $email = (get_post_meta(sanitize_text_field($_GET['post']), '_payoneer_email', true)) ? get_post_meta(sanitize_text_field($_GET['post']), '_payoneer_email', true) : '';
        $transaction = (get_post_meta(sanitize_text_field($_GET['post']), '_payoneer_transaction_id', true)) ? get_post_meta(sanitize_text_field($_GET['post']), '_payoneer_transaction_id', true) : '';

        ?>
        <div class="form-field form-field-wide">
            <img src='<?php echo plugin_dir_url( __DIR__ ) . 'assets/images/logo.png'; ?>' alt="payoneer">
            <table class="wp-list-table widefat fixed striped">
                <tbody>
                    <tr>
                        <th><strong><?php esc_html_e('Payoneer Email :', 'wc-payoneer-payment-gateway') ;?></strong></th>
                        <td><?php echo esc_attr( $email );?></td>
                    </tr>
                    <tr>
                        <th><strong><?php esc_html_e('Transaction ID :', 'wc-payoneer-payment-gateway') ;?></strong></th>
                        <td><?php echo esc_attr( $transaction );?></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <?php

    }

    add_action('woocommerce_admin_order_data_after_billing_address', 'payoneer_admin_order_data' );


    /**
     * Order review page Payoneer data output
     */
    function payoneer_order_details( $order ){

        if( $order->get_payment_method() != 'payoneer' )
            return;

        global $wp;

        // Get the order ID
        $order_id  = absint( $wp->query_vars['order-received'] );

        $email = (get_post_meta($order_id, '_payoneer_email', true)) ? get_post_meta($order_id, '_payoneer_email', true) : '';
        $transaction = (get_post_meta($order_id, '_payoneer_transaction_id', true)) ? get_post_meta($order_id, '_payoneer_transaction_id', true) : '';

        ?>
        <table>
            <tr>
                <th><?php esc_html_e('Payoneer Email:', 'wc-payoneer-payment-gateway');?></th>
                <td><?php echo esc_attr( $email );?></td>
            </tr>
            <tr>
                <th><?php esc_html_e('Transaction ID:', 'wc-payoneer-payment-gateway');?></th>
                <td><?php echo esc_attr( $transaction );?></td>
            </tr>
        </table>
        <?php

    }

    add_action('woocommerce_order_details_after_customer_details', 'payoneer_order_details' );

    /**
     * Register new order column
     */
    function payoneer_shop_order_columns($columns){

        $new_columns = (is_array($columns)) ? $columns : array();
        unset( $new_columns['order_actions'] );
        $new_columns['column_email_address']     = esc_html__('Payoneer Email:', 'wc-payoneer-payment-gateway');
        $new_columns['column_transaction_id']     = esc_html__('Transaction ID:', 'wc-payoneer-payment-gateway');

        $new_columns['order_actions'] = $columns['order_actions'];
        return $new_columns;

    }

    add_filter( 'manage_edit-shop_order_columns', 'payoneer_shop_order_columns' );


    /**
     * Data in order column
     */
    function payoneer_order_posts_custom_column($column){

        global $post;

        $column_email_address = (get_post_meta($post->ID, '_payoneer_email', true)) ? get_post_meta($post->ID, '_payoneer_email', true) : '';
        $column_transaction_id = (get_post_meta($post->ID, '_payoneer_transaction_id', true)) ? get_post_meta($post->ID, '_payoneer_transaction_id', true) : '';

        if ( $column == 'column_email_address' ) {
            echo esc_attr( $column_email_address );
        }
        if ( $column == 'column_transaction_id' ) {
            echo esc_attr( $column_transaction_id );
        }
    }

    add_action( 'manage_shop_order_posts_custom_column', 'payoneer_order_posts_custom_column', 2 );

    // Enqueue script
	function payoneer_plugin_enqueue_script() {
		// CSS
		wp_enqueue_style('payoneer-plugn', plugin_dir_url( __DIR__ ) . 'assets/css/payoneer-payment-gateway.css');
	}
	add_action('wp_enqueue_scripts', 'payoneer_plugin_enqueue_script');

} else {

    /**
     * Admin Notice
     */
    add_action( 'admin_notices', 'payoneer_admin_notice__error' );
    function payoneer_admin_notice__error() {
        ?>
        <div class="notice notice-error">
            <p><a href="http://wordpress.org/extend/plugins/woocommerce/"><?php esc_html_e( 'Woocommerce', 'wc-payoneer-payment-gateway' ); ?></a> <?php esc_html_e( 'plugin required to actived if you want to install this plugin.', 'wc-payoneer-payment-gateway' ); ?></p>
        </div>
        <?php
    }

    /**
     * Deactivate Plugin
     */
    function payoneer_deactivate() {
        deactivate_plugins( plugin_basename( __DIR__ ) );
        unset( $_GET['activate'] );
    }
    add_action( 'admin_init', 'payoneer_deactivate' );
} ?>