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

            // add the user
            $user_id = wp_insert_user( $userdata );

            // hook for when new users are created
            do_action( 'sell_media_after_insert_user', $user_id, $email, $first_name, $last_name );

            // log the user in
            $this->signon( $email, $password );

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
    public function signon( $email, $password ){

        if ( ! is_user_logged_in() && email_exists( $email ) ) {

            $creds = array();
            $creds['user_login'] = $email;
            $creds['user_password'] = $password;
            $creds['remember'] = true;
            $user = wp_signon( $creds, false );

            if ( is_wp_error( $user  ) ) {
                return;
            } else {
                wp_set_current_user( $user->ID, $user->user_login );
                wp_set_auth_cookie( $user->ID, true, false );
                do_action( 'wp_login', $user->user_login );
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

}