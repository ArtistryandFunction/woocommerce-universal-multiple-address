<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class WC_Address_Book_Menu {

	public function __construct(){

		add_filter( 'woocommerce_account_menu_items', array( $this, 'wc_address_book_add_to_menu' ), 10 );

		add_action( 'woocommerce_account_edit-address_endpoint', array( $this, 'wc_address_book_page' ), 20 );

	}
  /**
   * Replace My Address with the Address Book to My Account Menu.
   *
   * @param Array $items - An array of menu items.
   * @since 1.0.0
   */
  public function wc_address_book_add_to_menu( $items ){

    $new_items = array();

    foreach ( $items as $key => $value ) {

      if ( 'edit-address' === $key ) {
        $new_items[ $key ] = __( 'My Address Book', 'wc-uab-address-book' );
      } else {
        $new_items[ $key ] = $value;
      }
    }

    return $new_items;
  }

  /**
   * Adds Address Book Content.
   *
   * @param String $type - The type of address.
   * @since 1.0.0
   */
  public function wc_address_book_page( $type ) {

    wc_get_template( 'myaccount/my-address-book.php', array( 'type' => $type ), '', PLUGIN_PATH . 'templates/' );

  }
}

// Init Class.
$wc_address_book_menu = new WC_Address_Book_Menu();
?>
