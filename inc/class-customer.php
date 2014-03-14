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
            $userdata = array(
                'user_login'    => $email,
                'user_email'    => $email,
                'first_name'    => $first_name,
                'last_name'     => $last_name,
                'role'          => 'sell_media_customer'
            );

            // add the user
            $user_id = wp_insert_user( $userdata );

            // email the user their account registration details
            $this->email_details( $user_id );

            // log the user in automatically
            // $this->signon( $email, $password );

            // hook for when new users are created
            do_action( 'sell_media_after_insert_user', $user_id, $email, $first_name, $last_name );

            return false;
        }
    }


    /**
    * Email the user after registration to request a new password
    *
    * @param $user_id
    * @return (bool)
    *
    */
    public function email_details( $user_id=null ){

        $settings = sell_media_get_plugin_options();
        $user = get_user_by( 'id', $user_id );
        $email = $user->user_email;
        $site_name = esc_attr( get_bloginfo( 'name' ) );

        $message['subject'] = __( 'Account registration at', 'sell_media' ) . ' ' . get_bloginfo( 'name' );
        $message['body'] = __( 'Welcome', 'sell_media' ) . ' ' . $user->first_name . '!' . "\n\n";
        $message['body'] .= __( 'Here are your login credentials', 'sell_media' ) . ':' . "\n\n";
        $message['body'] .= __( 'Username', 'sell_media' ) . ': ' . $user->user_login . "\n\n";
        $message['body'] .= __( 'Create a password if you want to login to our dashboard to download any of your purchases in the future.', 'sell_media' ) . ':' . "\n\n";
        $message['body'] .= esc_url( site_url( 'wp-login.php?action=lostpassword' ) ) . "\n\n";
        $message['body'] .= __( 'Any purchases your make will be available on your account dashboard.', 'sell_media' ) . "\n\n";
        $message['body'] .= esc_url( get_permalink( $settings->dashboard_page ) ) . "\n\n";
        $message['body'] .= __( 'Thanks', 'sell_media' ) . ',' . "\n";
        $message['body'] .= $site_name;

        $message['headers'] = "From: " . $site_name . "\r\n";
        $message['headers'] .= "Reply-To: ". get_option( 'admin_email' ) . "\r\n";
        $message['headers'] .= "MIME-Version: 1.0\r\n";
        $message['headers'] .= "Content-Type: text/html; charset=utf-8\r\n";

        // Send the email to buyer
        $r = wp_mail( $email, $message['subject'], nl2br( $message['body'] ), $message['headers'] );

        return ( $r ) ? "Sent to: {$email}" : "Failed to send to: {$email}";
    }

    /**
    * Auto login user
    *
    * @param $user_id
    * @return (bool)
    * @todo get password, automatically log user in
    *
    */
    public function signon( $email=null, $password=null ){

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

}