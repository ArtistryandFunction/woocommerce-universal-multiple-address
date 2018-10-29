<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class WC_Address_Book_Update {

	public function __construct(){

		add_action( 'woocommerce_customer_save_address', array( $this, 'update_address_names' ), 10, 2 );

		add_action( 'woocommerce_customer_save_address', array( $this, 'redirect_on_save' ), 9999, 2 );

	}

/**
 * Update Address Book Values
 *
 * @param Int    $user_id - User's ID.
 * @param String $name - The name of the address being updated.
 * @since 1.0.0
 */
  public function update_address_names( $user_id, $name ){

    if ( isset( $_GET['address-book'] ) ) {
      $name = $_GET['address-book'];
    }

    // Only save shipping addresses.
    if ( 'billing' === $name ) {
      return;
    }

    // Get the address book and update the label.
    $wc_address_book_init = new WC_Address_Book_Init();
		$address_names = $wc_address_book_init->get_address_names( $user_id );

    // Build new array if one does not exist.
    if ( ! is_array( $address_names ) || empty( $address_names ) ) {

      $address_names = array();
    }

    // Add shipping name if not already in array.
    if ( ! in_array( $name, $address_names, true ) ) {

      array_push( $address_names, $name );
      $wc_address_book_init->save_address_names( $user_id, $address_names );
    }

  }

  /**
   * Redirect to the Edit Address page on save. Overrides the default redirect to /my-account/
   *
   * @param Int    $user_id - User's ID.
   * @param String $name - The name of the address being updated.
   * @since 1.0.0
   */
  public function redirect_on_save( $user_id, $name ) {

    if ( ! is_admin() && ! defined( 'DOING_AJAX' ) ) {

      wp_safe_redirect( wc_get_account_endpoint_url( 'edit-address' ) );
      exit;
    }
  }
}

// Init Class.
$wc_address_book_update = new WC_Address_Book_Update();

?>
