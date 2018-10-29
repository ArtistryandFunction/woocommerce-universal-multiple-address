<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class WC_Address_Book_Options {

	public function __construct(){

		add_action( 'wp_ajax_nopriv_wc_address_book_delete', array( $this, 'wc_address_book_delete' ) );

		add_action( 'wp_ajax_wc_address_book_delete', array( $this, 'wc_address_book_delete' ) );

		add_action( 'wp_ajax_nopriv_wc_address_book_make_primary', array( $this, 'wc_address_book_make_primary' ) );

		add_action( 'wp_ajax_wc_address_book_make_primary', array( $this, 'wc_address_book_make_primary' ) );
		
	}

/**
 * Used for deleting addresses from the my-account page.
 *
 * @param string $address_name - The name of a specific address in the address book.
 * @since 1.0.0
 */
  public function wc_address_book_delete( $address_name ){

    $address_name  = $_POST['name'];
    $customer_id   = get_current_user_id();
    $address_book  = $this->get_address_book( $customer_id );
    $address_names = $this->get_address_names( $customer_id );

    foreach ( $address_book as $name => $address ) {

      if ( $address_name === $name ) {

        // Remove address from address book.
        $key = array_search( $name, $address_names, true );
        if ( ( $key ) !== false ) {
          unset( $address_names[ $key ] );
        }

        $this->save_address_names( $customer_id, $address_names );

        // Remove specific address values.
        foreach ( $address as $field => $value ) {

          delete_user_meta( $customer_id, $field );
        }

        break;
      }
    }

    if ( is_ajax() ) {
      die();
    }
  }

  /**
   * Used for setting the primary shipping addresses from the my-account page.
   *
   * @since 1.0.0
   */
  public function wc_address_book_make_primary() {

    $customer_id  = get_current_user_id();
    $address_book = $this->get_address_book( $customer_id );

    $primary_address_name = 'shipping';
    $alt_address_name     = $_POST['name'];

    // Loop through and swap values between shipping names.
    foreach ( $address_book[ $primary_address_name ] as $field => $value ) {

      $alt_field = preg_replace( '/^[^_]*_\s*/', $alt_address_name . '_', $field );
      $resp      = update_user_meta( $customer_id, $field, $address_book[ $alt_address_name ][ $alt_field ] );
    }

    foreach ( $address_book[ $alt_address_name ] as $field => $value ) {

      $primary_field = preg_replace( '/^[^_]*_\s*/', $primary_address_name . '_', $field );
      $resp = update_user_meta( $customer_id, $field, $address_book[ $primary_address_name ][ $primary_field ] );
    }

    die();
  }
}

//Init class
$wc_address_book_options = new WC_Address_Book_Options();
?>
