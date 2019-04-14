<?php // phpcs:ignore WordPress.Files.FileName.InvalidClassFileName
/**
 * Plugin Name: No-Admin-Ajax
 * Version:     1.0.2
 * Description: Lightens WP AJAX routine and directs the requests to front-end rather than admin.
 * Plugin URI:  https://github.com/devgeniem/wp-no-admin-ajax
 * Author:      Miika Arponen / Geniem Oy
 * Author URI:  http://www.geniem.com
 * License:     MIT
 * License URI: https://github.com/devgeniem/wp-no-admin-ajax/blob/master/LICENSE
 */

namespace Geniem\Helper;

class No_Admin_Ajax {

    /**
     * URL keyword.
     *
     * @var string
     */
    public $keyword;

    /**
     * Whether class has been instantiated.
     *
     * @var bool
     */
    protected static $already_run = false;

    /**
     * Instantiate class.
     */
    public function __construct() {
        // This class should be run only once
        if ( true === self::$already_run ) {
            return;
        }

        self::$already_run = true;

        add_action( 'after_setup_theme', array( $this, 'init' ) );
    }

    /**
     * Initialize plugin.
     */
    public function init() {
        // Register activation hook to flush the rewrites.
        register_activation_hook( __FILE__, array( $this, 'activate' ) );
        add_action( 'init', array( $this, 'rewrite' ) );

        // Rewrite admin URLs only on the frontend.
        if ( ! is_admin() ) {
            add_filter( 'admin_url', array( $this, 'redirect_ajax_url' ), 11, 3 );
            add_action( 'template_redirect', array( $this, 'run_ajax' ) );
        }

        // URL keyword to use for ajax calls. It's modifiable with "no-admin-ajax/keyword" filter.
        if ( defined( 'WP_NO_ADMIN_AJAX_URL' ) ) {
            // Keyword doesn't need to contain slashes because they are set in redirect_ajax_url().
            // Trim slashes to avoid confusion.
            $default_keyword = trim( WP_NO_ADMIN_AJAX_URL, '/' );
        } else {
            $default_keyword = 'no-admin-ajax';
        }
        $this->keyword = apply_filters( 'no-admin-ajax/keyword', $default_keyword );
    }

    /**
     * Handle rewriting the admin-ajax URL.
     *
     * @param string $url
     * @param string $path
     * @param string $blog_id
     */
    public function redirect_ajax_url( $url, $path, $blog_id ) {
        if ( strpos( $url, 'admin-ajax' ) ) {
            return home_url( '/' . $this->keyword . '/' );
        }

        return $url;
    }

    /**
     * Create the rewrite.
     */
    public function rewrite() {
        global $wp_rewrite;

        add_rewrite_tag( '%no-admin-ajax%', '([0-9]+)' );

        // The whole ajax URL matching pattern can be altered with "no-admin-ajax/rule" filter.
        $default_rule = '^' . $this->keyword . '/?$';
        $rule         = apply_filters( 'no-admin-ajax/rule', $default_rule );

        add_rewrite_rule( $rule, 'index.php?no-admin-ajax=true', 'top' );
    }

    /**
     * Run the ajax calls, equivalent to core's admin-ajax.php.
     */
    public function run_ajax() {
        global $wp_query;

        if ( $wp_query->get( 'no-admin-ajax' ) ) {
            // Constant for plugins to know that we are on an AJAX request.
            define( 'DOING_AJAX', true );

            // If we don't have an action, do nothing.
            if ( ! isset( $_REQUEST['action'] ) ) {
                die( 0 );
            }

            // Escape the parameter to prevent disastrous things.
            $action = esc_attr( $_REQUEST['action'] );

            // Run customized no-admin-ajax methods with "no-admin-ajax/before" action.
            do_action( 'no-admin-ajax/before' );

            // Run customized no-admin-ajax methods for specific ajax actions with "no-admin-ajax/before/{action}".
            do_action( 'no-admin-ajax/before/' . $action );

            // Same headers as core's AJAX routine.
            $default_headers = array(
                'Content-Type: text/html; charset=' . get_option( 'blog_charset' ),
                'X-Robots-Tag: noindex',
            );

            // Filter to customize the headers sent by AJAX calls.
            $headers = apply_filters( 'no-admin-ajax/headers', $default_headers );

            // Send the headers.
            if ( is_array( $headers ) && count( $headers ) > 0 ) {
                foreach ( $headers as $header ) {
                    // phpcs:ignore WordPress.PHP.NoSilencedErrors
                    @header( $header );
                }
            }

            send_nosniff_header();
            nocache_headers();

            // Run the actions
            if ( is_user_logged_in() ) {
                do_action( 'wp_ajax_' . $action );
            } else {
                do_action( 'wp_ajax_nopriv_' . $action );
            }

            die( 0 );
        }
    }

    /**
     * Run activate during plugin activation.
     */
    public function activate() {
        global $wp_rewrite;

        $this->rewrite();
        $wp_rewrite->flush_rules();
    }
}

new No_Admin_Ajax();
