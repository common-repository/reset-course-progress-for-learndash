<?php
/**
 * Plugin Name: Reset Course Progress For LearnDash
 * Description: This add-on helps you reset course progress by users, user roles, and groups for all or selected courses.
 * Version: 1.3
 * Author: LDninjas
 * Author URI: ldninjas.com
 * Plugin URI: https://ldninjas.com/ld-plugins/
 * Text Domain: reset-course-progress-for-learndash
 */

if ( !defined( 'ABSPATH' ) ) {
    exit;
}

include_once ABSPATH . 'wp-admin/includes/plugin.php';
if ( function_exists( 'is_plugin_active' ) ) {
    if ( is_plugin_active( 'sfwd-lms/sfwd_lms.php' ) ) {
        
        if ( !function_exists( 'rcpfl_fs' ) ) {

            function rcpfl_fs() {

                global  $rcpfl_fs ;
                
                if ( !isset( $rcpfl_fs ) ) {

                    require_once dirname( __FILE__ ) . '/freemius/start.php';

                    $rcpfl_fs = fs_dynamic_init( array(
                        'id'             => '9013',
                        'slug'           => 'reset-course-progress-for-learndash',
                        'premium_slug'   => 'reset-course-progress-pro-for-learndash',
                        'type'           => 'plugin',
                        'public_key'     => 'pk_b8487fd6f4b0946d6f7df843ec028',
                        'is_premium'     => false,
                        'premium_suffix' => 'Reset Course Progress Pro for LearnDash',
                        'has_addons'     => false,
                        'has_paid_plans' => true,
                        'menu'           => array(
                            'slug'   => 'reset-course-progress-for-learndash',
                            'parent' => array(
                                'slug' => 'learndash_lms_overview',
                            ),
                        ),

                        'is_live'        => true,
                    ) );
                }
                
                return $rcpfl_fs;
            }
            
            rcpfl_fs();
            do_action( 'rcpfl_fs_loaded' );
        }
    
    }
}

/**
 * Reset_Course_Progress_For_LearnDash
 */
class Reset_Course_Progress_For_LearnDash {

    const  VERSION = '1.3' ;

    /**
     * @var self
     */
    private static  $instance = null ;

    /**
     * @since 1.0
     * @return $this
     */
    public static function instance() {
        
        if ( is_null( self::$instance ) && !self::$instance instanceof Reset_Course_Progress_For_LearnDash ) {
            self::$instance = new self();
            self::$instance->setup_constants();
            self::$instance->includes();
        }
        
        return self::$instance;
    }
    
    /**
     * Plugin Constants
     */
    private function setup_constants() {

        /**
         * Directory
         */
        define( 'RCPL_DIR', plugin_dir_path( __FILE__ ) );
        define( 'RCPL_DIR_FILE', RCPL_DIR . basename( __FILE__ ) );
        define( 'RCPL_INCLUDES_DIR', trailingslashit( RCPL_DIR . 'includes' ) );
        define( 'RCPL_TEMPLATES_DIR', trailingslashit( RCPL_DIR . 'templates' ) );
        define( 'RCPL_BASE_DIR', plugin_basename( __FILE__ ) );

        /**
         * URLs
         */
        define( 'RCPL_URL', trailingslashit( plugins_url( '', __FILE__ ) ) );
        define( 'RCPL_ASSETS_URL', trailingslashit( RCPL_URL . 'assets' ) );

        /**
         * Plugin version
         */
        define( 'RCPL_VERSION', self::VERSION );
    }
    
    /**
     * Plugin requiered files
     */
    private function includes() {

        if ( file_exists( RCPL_INCLUDES_DIR . 'admin/class-rcpl-admin.php' ) ) {
            require_once RCPL_INCLUDES_DIR . 'admin/class-rcpl-admin.php';
        }

        if ( file_exists( RCPL_INCLUDES_DIR . 'class-rcpl-functions.php' ) ) {
            require_once RCPL_INCLUDES_DIR . 'class-rcpl-functions.php';
        }

        if ( file_exists( RCPL_INCLUDES_DIR . 'class-reset-by-users.php' ) ) {
            require_once RCPL_INCLUDES_DIR . 'class-reset-by-users.php';
        }

        if ( file_exists( RCPL_INCLUDES_DIR . 'class-reset-by-role.php' ) ) {
            require_once RCPL_INCLUDES_DIR . 'class-reset-by-role.php';
        }

        if ( file_exists( RCPL_INCLUDES_DIR . 'class-reset-by-group.php' ) ) {
            require_once RCPL_INCLUDES_DIR . 'class-reset-by-group.php';
        }
    }

}
/**
 * Display admin notifications if dependency not found.
 */
function rcpl_ready() {

    if ( !is_admin() ) {
        return;
    }
    
    if ( !class_exists( 'SFWD_LMS' ) ) {

        deactivate_plugins( plugin_basename( __FILE__ ), true );
        $class = 'notice is-dismissible error';
        $message = __( 'Reset Course Progress For Learndash Add-on requires Learndash Plugin to be activated.', 'reset-course-progress-for-learndash' );
        printf( '<div id="message" class="%s"> <p>%s</p></div>', $class, $message );
    }
}

/**
 * @return bool
 */
function RCPL() {
    
    if ( !class_exists( 'SFWD_LMS' ) ) {

        add_action( 'admin_notices', 'rcpl_ready' );
        return false;
    }
    
    return Reset_Course_Progress_For_LearnDash::instance();
}
add_action( 'plugins_loaded', 'RCPL' );