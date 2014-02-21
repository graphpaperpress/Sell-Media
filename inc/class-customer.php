<?php

Class SellMediaCustomer {

    private $settings;

    public function __construct(){
        $this->settings = sell_media_get_plugin_options();
    }

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
                'user_login'    => $email,
                'user_email'    => $email,
                'first_name'    => $first_name,
                'last_name'     => $last_name,
                'role'          => 'sell_media_customer',
                'password'      => $password
            );

            $user_id = wp_insert_user( $userdata );
            
            return false;
        }
    }

    /**
    * Auto login user
    *
    * @param $user_id
    * @return (bool)
    *
    */
    public function signon( $user_id ){

        if ( ! is_user_logged_in() ) {

            $user = get_user_by( 'id', $user_id ); 

            if ( $user ) {

                $creds = array();
                $creds['user_login'] = $user->user_login;
                $creds['user_password'] = $user->user_pass;
                $creds['remember'] = true;
                $user = wp_signon( $creds, false );
                wp_set_current_user( $user );
                if ( is_wp_error($user) ) {
                    return $user->get_error_message();
                } else {
                    return false;
                }
            }
        }
    }

    /**
    * Email user registration
    *
    * @param $user_id
    * @return (bool)
    *
    */
    public function email_registration( $user_id ){

        $user = get_user_by( 'id', $user_id );

        if ( $user ) {

            if ( ! email_exists( $user->user_email ) ) {

                $subject = __( 'Account Registration at', 'sell_media' ) . ' ' . get_bloginfo( 'name' );
                $message = __( 'Hello', 'sell_media' ) . ' ' . $user->first_name . '!' . "\n\n";
                $message .= __( 'Here are your login credentials', 'sell_media' ) . ':' . "\n\n";
                $message .= __( 'Username', 'sell_media' ) . ': ' . $user->user_login . "\n";
                $message .= __( 'Password', 'sell_media' ) . ': ' . $user->user_pass . "\n\n";
                $message .= __( 'Any purchases your make will be available on your account dashboard', 'sell_media' ) . ': ' . get_permalink( $this->settings->dashboard_page ) . "\n\n";
                $message .= __( 'Thanks', 'sell_media' ) . ',' . "\n";
                $message .= get_bloginfo( 'name' );
                wp_mail( $user->user_email, $subject, $message );

                return false;
            }
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

        $meta = get_post_meta( $post_id, '_sell_media_payment_meta', true );

        // $user = get_user_by( 'email', $user_id );
        // if ( get_userdata( get_post_meta( $post_id, '_sell_media_user_id', true ) ) ){
        //     $edit_link = '<a href="' . get_edit_user_link( get_post_meta( $post_id, '_sell_media_user_id', true ) ) . '">Edit</a>';
        // } else {
        //     $edit_link = null;
        // }

        // $contact = array(
        //     'first_name' => get_post_meta( $post_id, '_sell_media_payment_first_name', true ),
        //     'last_name' => get_post_meta( $post_id, '_sell_media_payment_last_name', true ),
        //     'user_edit_link' => $edit_link,
        //     'email' => get_post_meta( $post_id, '_sell_media_payment_user_email', true )
        // );

        // $info = sprintf(
        //     '<ul>
        //     <li>%s: '.$contact['first_name'] . ' ' . $contact['last_name'] . '</li>
        //     <li>%s: <a href="mailto:' . $contact['email'] . '">' . $contact['email'] . '</a></li>
        //     <li>%s: '.$this->total( $post_id ).'</li></ul>',
        //     __( 'Name', 'sell_media' ),
        //     __( 'Email', 'sell_media' ),
        //     __( 'Total', 'sell_media' )
        // );
        // return $info;
    }
}