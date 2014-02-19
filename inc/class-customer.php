<?php

Class SellMediaCustomer {

	/**
	* Insert a new customer
	*
	* @param $email (string)
	* @param $first_name (string)
	* @param $last_name (string)
	* @return $user_id (int)
	*
	*/
	public function insert( $email=null, $first_name=null, $last_name=null ){

		if ( $email && email_exists( $email ) == false ) {
			$password = wp_generate_password( $length=12, $include_standard_special_chars=false );
			$userdata = array(
				'user_login'	=> $email,
				'user_email'	=> $email,
				'first_name'	=> $first_name,
				'last_name'		=> $last_name,
				'role'			=> 'sell_media_customer',
				'password'		=> $password
			);

			$user_id = wp_insert_user( $userdata );
		}

	}

	/**
	* Get all purchases by a customer
	*
	* @param $post_id (int) The post_id for a post of post type "sell_media_payment"
	* @return $purchases (array)
	*/
	public function get_purchases( $user_id=null ){

		$meta = get_user_meta( $user_id, '_sell_media_user_purchases' );

		if ( $meta ) {
			foreach ( $meta as $k => $v ) {
				$purchases[] = $v;
			}
			return $purchases;
		}

	}

	/**
	* Get a single purchase by a customer
	*
	* @param $post_id (int) The post_id for a post of post type "sell_media_payment"
	* @return $purchases (array)
	*/
	public function get_purchase( $user_id=null, $post_id=null ){

		$user_meta = get_user_meta( $user_id, '_sell_media_user_purchases' );

	}

	/**
	* Update purchases by a customer
	*
	* @param $post_id (int) The post_id for a post of post type "sell_media_payment"
	* @return null
	*/
	public function update_purchases( $user_id=null, $post_id=null ){

		$user_meta = get_user_meta( $user_id, '_sell_media_user_purchases' );

	}


	/**
	* Retrieve the customer contact info associated with a payment
	*
	* @param $post_id (int) The post_id for a post of post type "sell_media_payment"
	* @todo This should not include the total
	* @return
	*/
	public function customer_payment( $post_id=null ){
		$meta = get_post_meta( $post_id, '_sell_media_payment_meta', true ) );

		$user = get_user_by( 'email',  );
		if ( get_userdata( get_post_meta( $post_id, '_sell_media_user_id', true ) ) ){
			$edit_link = '<a href="' . get_edit_user_link( get_post_meta( $post_id, '_sell_media_user_id', true ) ) . '">Edit</a>';
		} else {
			$edit_link = null;
		}

		$contact = array(
			'first_name' => get_post_meta( $post_id, '_sell_media_payment_first_name', true ),
			'last_name' => get_post_meta( $post_id, '_sell_media_payment_last_name', true ),
			'user_edit_link' => $edit_link,
			'email' => get_post_meta( $post_id, '_sell_media_payment_user_email', true )
		);

		$info = sprintf(
			'<ul>
			<li>%s: '.$contact['first_name'] . ' ' . $contact['last_name'] . '</li>
			<li>%s: <a href="mailto:' . $contact['email'] . '">' . $contact['email'] . '</a></li>
			<li>%s: '.$this->total( $post_id ).'</li></ul>',
			__( 'Name', 'sell_media' ),
			__( 'Email', 'sell_media' ),
			__( 'Total', 'sell_media' )
		);
		return $info;
	}
}