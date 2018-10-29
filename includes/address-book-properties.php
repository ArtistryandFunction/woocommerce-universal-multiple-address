<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class WC_Address_Book_Properties {

	public function __construct(){

	}

	/**
	 * Grabs saved billing and shipping addresses and creates multidimensional associative array
	 *
	 * @param Int $user_id - Users post ID
	 *
	 * @since 1.0.0
	 */
	public function get_my_saved_addresses($user_id){

		global $wpdb;

		$date_created = date("F j, Y, g:i.s a");

		$billing_key = 'billing_%';
		$shipping_key = 'shipping_%';


		$billing_array = array();
		$shipping_array = array();

		$billing_statement = $wpdb->prepare(
																		"SELECT meta_key, meta_value
																	 	 FROM wp_usermeta
																	 	 WHERE user_id = %d
																	 	 AND meta_key LIKE %s",
																	 	 $user_id, $billing_key
																 );

		$shipping_statement = $wpdb->prepare(
																		"SELECT meta_key, meta_value
																	 	 FROM wp_usermeta
																	 	 WHERE user_id = %d
																	 	 AND meta_key LIKE %s",
																	 	 $user_id, $shipping_key
																 );

		$billing_query = $wpdb->get_results($billing_statement, ARRAY_A);
		$shipping_query = $wpdb->get_results($shipping_statement, ARRAY_A);

		$billing_array = array_column( $billing_query, 'meta_value', 'meta_key' );
		$billing_array['make_primary'] = 'billing';
		$billing_array['date_created'] = $date_created;

  	$shipping_array = array_column( $shipping_query, 'meta_value', 'meta_key' );
		$shipping_array['make_primary'] = 'shipping';
		$shipping_array['date_created'] = $date_created;

		$address_book = array($billing_array, $shipping_array);

		return $address_book;
	}

	/**
	 * Grabs saved new addresses and pushes into multidimensional associative array
	 *
	 * @param Int $user_id - Users post ID
	 *
	 * @since 1.0.0
	 */
	public function save_my_new_address($user_id, $new_address){

		$address_book = get_user_meta($user_id, 'wcuab_address_book', true);

		$date_created = date("F j, Y, g:i.s a");

		if (!isset($new_address)){
			return;
		} else {
			$address_string = $_POST[$new_address];

			$clean_address_string = stripslashes($address_string);
			$address_from_form = json_decode($clean_address_string, true);

			//start debugging
			error_log(print_r($clean_address_string,true));
			error_log(print_r($address_book,true));
			error_log(print_r($address_from_form,true));
			//end debugging

			$address_from_form['date_created'] = $date_created;
			$address_book[] = $address_from_form;
			update_user_meta( $user_id, 'wcuab_address_book', $address_book );

		}

	}

}

?>
