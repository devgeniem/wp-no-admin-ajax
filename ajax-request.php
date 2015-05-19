<?php
// Constant for plugins to know that we are on an AJAX request
define('DOING_AJAX', true);

// If we don't have an action, do nothing
if ( ! isset( $_REQUEST['action'] ) ) {
    die(0);
}
 
// Load the WordPress core
require_once('../../../wp-load.php'); 
 
// Same headers as WordPress normal AJAX routine sends
@header( 'Content-Type: text/html; charset=' . get_option( 'blog_charset' ) );
@header( 'X-Robots-Tag: noindex' );
send_nosniff_header();
nocache_headers();

// Escape the parameter to prevent disastrous things
$action = esc_attr( $_REQUEST['action'] );
 
// Run the actions
if(is_user_logged_in()) {
    do_action( 'wp_ajax_' . $action );
}
else {
    do_action( 'wp_ajax_nopriv_' . $action );
}

die(0);