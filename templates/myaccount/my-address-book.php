<?php
/**
 * My Address Book
 *
 * @author  Khayyam Abdullah
 * @package WooCommerce Universal Address Book/Templates
 * @version 1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

$wc_address_book_init = new WC_Address_Book_Init();

$customer_id  = get_current_user_id();
$address_book = get_user_meta($customer_id, 'wcuab_address_book', true);

// Do not display on address edit pages.
if ( ! $type ) : ?>

	<?php

	$shipping_address = get_user_meta( $customer_id, 'shipping_address_1', true );

	// Only display if primary addresses are set and not on an edit page.
	if ( ! empty( $shipping_address ) ) :
		?>

		<hr />

		<div class="address_book">

			<?php
				wc_add_notice( __( 'Please enter your first name.', 'woocommerce' ), 'error' );
				wc_add_notice( __( 'Please enter your last name.', 'woocommerce' ), 'error' );
				wc_add_notice( __( 'Please enter your country.', 'woocommerce' ), 'error' );
				wc_add_notice( __( 'Please enter your address.', 'woocommerce' ), 'error' );
				wc_add_notice( __( 'Please enter your city.', 'woocommerce' ), 'error' );
				wc_add_notice( __( 'Please enter your state.', 'woocommerce' ), 'error' );
				wc_add_notice( __( 'Please enter your zip code.', 'woocommerce' ), 'error' );
				wc_add_notice( __( 'Please enter your phone number.', 'woocommerce' ), 'error' );
				wc_add_notice( __( 'Please enter your email.', 'woocommerce' ), 'error' );
				wc_add_notice( __( 'Please enter a address nickname.', 'woocommerce' ), 'error' );
				wc_print_notices();
			?>

			<p class="myaccount_address">
				<?php echo apply_filters( 'woocommerce_my_account_my_address_book_description', __( 'The following addresses are available during the checkout process.', 'wc-uab-address-book' ) ); ?>
			</p>

			<div class="new-address-form"><!-- Hidden Form based on original 'edit address' form -->
				<form name="WCUABAddressBookForm">

					<?php
					$address = WC()->countries->get_default_address_fields();

					$extra_address_fields = $wc_address_book_init->add_extra_address_fields($address_fields);

					$new_address = array_merge($address, $extra_address_fields);//include extra fields phone, email, and address name fields
					?>

					<div class="woocommerce-address-fields__field-wrapper"><!-- Based on partial code in /plugins/woocommerce/templates/myaccount/form-edit-address.php -->
						<?php
						foreach ( $new_address as $key => $field ) {
							if ( isset( $field['country_field'], $new_address[ $field['country_field'] ] ) ) {
								$field['country'] = wc_get_post_data_by_key( $field['country_field'], $new_address[ $field['country_field'] ]['value'] );
							}
							woocommerce_form_field( $key, $field, wc_get_post_data_by_key( $key, isset($field['value']) ? $field['value'] : "") );
						}

						?>
					</div>
						<button type="button" class="save-address button" name="save_address" value="<?php esc_attr_e( 'Save address', 'woocommerce' ); ?>"><?php esc_html_e( 'Save address', 'woocommerce' ); ?></button>
						<?php wp_nonce_field( 'woocommerce-edit_address' ); ?>
						<input type="hidden" name="action" value="edit_address">

				</form>

			</div><!--End of Hidden Form-->

			<?php

			foreach ( $address_book as $i => $addresses ) :

				$new_format = array(
					$formats['default'] = "{address_name}\n{first_name} {last_name}\n{company}\n{address_1}\n{address_2}\n{city}, {state} {postcode}\n{address_phone}\n{address_email}"//@TODO if different address formats can be found that are drastically different per country then add in array
				);

				$user_address = array(
					'{address_name}'  => $addresses['address_name'],
					'{first_name}'    => $addresses['first_name'],
					'{last_name}'     => $addresses['last_name'],
					'{company}'       => $addresses['company'],
					'{address_1}'     => $addresses['address_1'],
					'{address_2}'     => $addresses['address_2'],
					'{city}'          => $addresses['city'],
					'{state}'         => $addresses['state'],
					'{postcode}'      => $addresses['postcode'],
					'{address_phone}' => $addresses['address_phone'],
					'{address_email}' => $addresses['address_email']
				);

				// Pieces of code tweaked from the get_formatted_address method
				$formatted_address = str_replace( array_keys( $user_address ), $user_address, $new_format );

				// Clean up white space.
				$formatted_address = preg_replace( '/  +/', ' ', trim( implode('', $formatted_address) ) );
				$formatted_address = preg_replace( '/\n\n+/', "\n", $formatted_address );

				// Break newlines apart and remove empty lines/trim commas and white space.
				$formatted_address = array_filter( array_map( 'trim', explode( "\n", $formatted_address ) ) );

				// Add html breaks.
				$formatted_address = implode( '<br/>', $formatted_address );

				if ( strlen($formatted_address) > 1 ) :
				?>

					</br>
					<div class="wc-uab-address-book-address">
						<div class="wc-uab-address-book-meta">
							<a href="<?php echo esc_url( wc_get_endpoint_url( 'edit-address', 'shipping/?address-book=' . $i ) ); ?>" class="wc-uab-address-book-edit"><?php echo esc_attr__( 'Edit', 'wc-uab-address-book' ); ?></a>
							<a id="<?php echo esc_attr( $i ); ?>" class="wc-uab-address-book-delete"><?php echo esc_attr__( 'Delete', 'wc-uab-address-book' ); ?></a>
							<a id="<?php echo esc_attr( $i ); ?>" class="wc-uab-address-book-make-primary-shipping"><?php echo esc_attr__( 'Make Primary Shipping', 'wc-uab-address-book' ); ?></a>
							<a id="<?php echo esc_attr( $i ); ?>" class="wc-uab-address-book-make-primary-billing"><?php echo esc_attr__( 'Make Primary Billing', 'wc-uab-address-book' ); ?></a>
						</div>
						<address>
							<?php echo wp_kses( $formatted_address, array( 'br' => array() ) ); ?>
						</address>
					</div>
					<?php //var_dump($formatted_address); ?>
				<?php endif; ?>

			<?php endforeach; ?>

		</div>
	<?php endif; ?>

<?php
// Add link/button to the my accounts page for adding addresses.
if ( ! empty( get_user_meta( $customer_id, 'shipping_address_1' ) ) ) {
	$wc_address_book_init->add_additional_address_button();
}
?>

<?php endif; ?>
