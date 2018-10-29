<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class WC_Address_Book_Init extends WC_Address_Book_Properties {

	public function __construct(){

		add_action( 'woocommerce_new_address_book', array( $this, 'create_address_book' ) );

	}

	/**
	* Save a users billing and shipping addresses to the address book upon init
	*
	* @since 1.0.0
	*/
  public function create_address_book(){

		$user    = wp_get_current_user();
		$user_id = $user->ID;

    $address_book = $this->get_my_saved_addresses($user_id);

		$check_address_book_exists = metadata_exists( 'user', $user_id, 'wcuab_address_book' );

		if ( $check_address_book_exists === true ) {
			return;
		} else {
			update_user_meta( $user_id, 'wcuab_address_book', $address_book );
		}
  }


  /**
   * Adds a link/button to the my account page under the addresses for adding additional addresses to their account.
   *
   * @since 1.0.0
   */
  public function add_additional_address_button() {
    ?>

    <div class="add-new-address">
      <button type="button" class="add-address button"><?php echo esc_html_e( 'Add New Address', 'wc-uab-address-book' ); ?></button>
    </div>

    <?php
  }

	/**
	* Adds extra fields to 'add address' form
	*
	* @param Array $address_fields - An array of address fields
	*
	* @since 1.0.0
	*/
 public function add_extra_address_fields($address_fields = null) {

	 $address_fields['address_phone'] = array(
  		'label'        => __( 'Phone', 'woocommerce' ),
			'placeholder'  => __('Phone', 'placeholder', 'woocommerce'),
      'required'     => true,
      'type'         => 'tel',
      'class'        => array( 'form-row-first' ),
      'validate'     => array( 'phone' ),
      'autocomplete' => 'tel',
      'priority'     => 100,
			'clear'        => false
	 );

	 $address_fields['address_email'] = array(
      'label'        => __( 'Email address', 'woocommerce' ),
			'placeholder'  => __('Email', 'placeholder', 'woocommerce'),
      'required'     => true,
      'type'         => 'email',
      'class'        => array( 'form-row-last' ),
      'validate'     => array( 'email' ),
      'autocomplete' => 'no' === get_option( 'woocommerce_registration_generate_username' ) ? 'email' : 'email username',
      'priority'     => 110,
			'clear'        => false
	 );

	 $address_fields['address_name'] = array(
		  'label'        => __( 'Address name', 'woocommerce' ),
			'placeholder'  => __('Address nickname... For example: My Address, Main St Address, South Address...', 'placeholder', 'woocommerce'),
			'required'     => true,
			'type'         => 'text',
			'class'        => array( 'form-row-wide' ),
			'priority'     => 120,
			'clear'        => true
	 );

	 $address_fields['make_primary'] = array(
		  'label'        => __( 'Make this a default address?', 'woocommerce' ),
			'required'     => false,
			'type'         => 'select',
			'options'      => array(
				''           => __('Select for billing or shipping...'),
				'billing'    => __('Default Billing', 'woocommerce'),
				'shipping'   => __('Default Shipping', 'woocommerce')),
			'class'        => array( 'form-row-wide' ),
			'priority'     => 130,
			'clear'        => true
	 );

	 $address_fields['date_created'] = array(
		 'type'          => 'hidden',
		 'priority'      => 140
	 );

	 return $address_fields;

	 add_filter( 'woocommerce_default_address_fields' , 'add_extra_address_fields' );
 }
}

// Init Class.
$wc_address_book_init = new WC_Address_Book_Init();

?>
