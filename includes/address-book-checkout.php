<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class WC_Address_Book_Checkout {

	public function __construct(){

		add_filter( 'woocommerce_checkout_fields', array( $this, 'shipping_address_select_field' ), 9999, 1 );

		add_action( 'wp_ajax_nopriv_wc_address_book_checkout_update', array( $this, 'wc_address_book_checkout_update' ) );

		add_action( 'wp_ajax_wc_address_book_checkout_update', array( $this, 'wc_address_book_checkout_update' ) );

	}

/**
 * Adds the address book select fields to the checkout page.
 *
 * @param array $fields - An array of WooCommerce Shipping Address fields.
 * @since 1.0.0
 */
  public function shipping_address_select_field( $fields ){

    $wc_address_book_init = new WC_Address_Book_Init();

		$address_book = $wc_address_book_init->get_address_book();
    $customer_id  = wp_get_current_user();

    $address_selector['address_book'] = array(
      'type'     => 'select',
      'class'    => array( 'form-row-wide', 'address_book' ),
      'label'    => __( 'Address Book', 'wc-uab-address-book' ),
      'order'    => -1,
      'priority' => -1,
    );

    if ( ! empty( $address_book ) && false !== $address_book ) {

      foreach ( $address_book as $name => $address ) {

        if ( ! empty( $address[ $name . '_address_1' ] ) ) {
          $address_selector['address_book']['options'][ $name ] = $this->address_select_label( $address, $name );
        }
      }
    }

    $address_selector['address_book']['options']['add_new'] = __( 'Add New Address', 'wc-uab-address-book' );

    $fields['shipping'] = $address_selector + $fields['shipping'];

    return $fields;
  }

  /**
   * Adds the address book select labels to the checkout page.
   *
   * @param array $address - An array of WooCommerce Shipping Address data.
   * @since 1.0.0
   */
  public function address_select_label( $address, $name ) {

    $show_state = ( isset( $address[ $name . '_state' ] ) ? true : false );

    $label  = $address[ $name . '_first_name' ] . ' ' . $address[ $name . '_last_name' ];
    $label .= ( isset( $address[ $name . '_address_1' ] ) ? ', ' . $address[ $name . '_address_1' ] : '' );
    $label .= ( isset( $address[ $name . '_city' ] ) ? ', ' . $address[ $name . '_city' ] : '' );
    $label .= ( isset( $address[ $name . '_state' ] ) ? ', ' . $address[ $name . '_state' ] : '' );

    return apply_filters( 'wc_address_book_address_select_label', $label, $address, $name );
  }

	/**
 	* Used for updating addresses dynamically on the checkout page.
 	*
 	* @since 1.0.0
 	*/
	public function wc_address_book_checkout_update() {

  	global $woocommerce;

		$wc_address_book_init = new WC_Address_Book_Init();

  	$name = $_POST['name'];
  	$address_book = $wc_address_book_init->get_address_book();

  	$customer_id = get_current_user_id();
  	$shipping_countries = $woocommerce->countries->get_shipping_countries();

  	$response = array();

  	// Get address field values.
  	if ( 'add_new' !== $name ) {

    	foreach ( $address_book[ $name ] as $field => $value ) {

      	$field = preg_replace( '/^[^_]*_\s*/', 'shipping_', $field );

      	$response[ $field ] = $value;
    	}
  	} else {

    	// If only one country is available for shipping, include it in the blank form.
    	if ( 1 === count( $shipping_countries ) ) {
      	$response['shipping_country'] = key( $shipping_countries );
    	}
  	}

  	echo wp_json_encode( $response );

  	die();
	}
}

//Init class
$wc_address_book_checkout = new WC_Address_Book_Checkout();

?>
