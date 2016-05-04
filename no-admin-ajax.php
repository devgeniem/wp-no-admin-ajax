<?php
/*
Plugin Name: No-Admin-Ajax
Plugin URI: https://github.com/devgeniem/wp-no-admin-ajax
Description: A plugin that lightens the WP AJAX routine and directs the requests to front-end rather than admin back-end.
Author: Miika Arponen / Geniem Oy
Author URI: http://www.geniem.com
License: MIT
License URI: https://github.com/devgeniem/wp-no-admin-ajax/blob/master/LICENSE
Version: 1.0.0
*/

class No_Admin_Ajax {
	public $keyword;

	protected static $already_run = false;

	public function __construct() {
		// This class should be run only once
		if ( $this->already_run === true ) {
			return false;
		}
		else {
			$this->already_run = true;
		}

		add_action( "after_setup_theme", array( $this, "init" ) );
	}

	public function init() {
		// Rewrite only public side admin urls
		if ( ! is_admin() ) {
			add_filter( 'admin_url', array( $this, 'redirect_ajax_url' ), 11, 3 );

			add_action( "template_redirect", array( $this, "run_ajax" ) );
		}

		// Register activation hook to flush the rewrites
		register_activation_hook( __FILE__, array( $this, "activate" ) );
		add_action( "init", array( $this, "rewrite" ) );

		// Url keyword to use for the ajax calls. Is modifiable with filter "no-admin-ajax/keyword"
		$default_keyword = "no-admin-ajax";

		$this->keyword = apply_filters( "no-admin-ajax/keyword", $default_keyword );
	}

	// Function that handles rewriting the admin-ajax url to the one we want
	public function redirect_ajax_url( $url, $path, $blog_id ) {
		if( strpos( $url, 'admin-ajax' ) ) {
			return home_url( "/". $this->keyword ."/" );
		}
		else {
			return $url;
		}
	}

	// Creates the rewrite
	public function rewrite() {
		global $wp_rewrite;

		add_rewrite_tag( "%no-admin-ajax%", "([0-9]+)" );

		// The whole ajax url matching pattern can be altered with filter "no-admin-ajax/rule"
		$default_rule = "^". $this->keyword ."/?$";

		$rule = apply_filters( "no-admin-ajax/rule", $default_rule );

		add_rewrite_rule(
			$rule,
			"index.php?no-admin-ajax=true",
			"top"
		);
	}

	// Runs the ajax calls. Equivalent to the real admin-ajax.php
	public function run_ajax() {
		global $wp_query;

		if ( $wp_query->get("no-admin-ajax") ) {
			// Constant for plugins to know that we are on an AJAX request
			define("DOING_AJAX", true);

			// If we don't have an action, do nothing
			if ( ! isset( $_REQUEST["action"] ) ) {
			    die(0);
			}

			// Escape the parameter to prevent disastrous things
			$action = esc_attr( $_REQUEST["action"] );

			// Run customized no-admin-ajax methods with action "no-admin-ajax/before"
			do_action( "no-admin-ajax/before" );

			// Run customized no-admin-ajax methods for specific ajax actions with "no-admin-ajax/before/{action}"
			do_action( "no-admin-ajax/before/". $action );

			// Same headers as WordPress normal AJAX routine sends
			$default_headers = array(
				"Content-Type: text/html; charset=" . get_option( "blog_charset" ),
				"X-Robots-Tag: noindex"
			);

			// Filter to customize the headers sent by ajax calls
			$headers = apply_filters( "no-admin-ajax/headers", $default_headers );

			// Send the headers to the user
			if ( is_array( $headers ) && count( $headers ) > 0 ) {
				foreach ( $headers as $header ) {
					@header( $header );
				}
			}

			send_nosniff_header();
			nocache_headers();

			// Run the actions
			if(is_user_logged_in()) {
			    do_action( "wp_ajax_" . $action );
			}
			else {
			    do_action( "wp_ajax_nopriv_" . $action );
			}

			die(0);
		}
	}

	public function activate() {
		global $wp_rewrite;
		$this->rewrite();
		$wp_rewrite->flush_rules();
	}
}

new No_Admin_Ajax();
