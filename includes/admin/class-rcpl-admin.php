<?php
/**
 * Admin template for rest course progress for leardash
 * 
 * Do not allow directly accessing this file.
 */

if( ! defined( 'ABSPATH' ) ) exit;

/**
 * RCPL_Admin
 */
class RCPL_Admin {

    /**
     * @var self
     */
    private static $instance = null;

    /**
     * @since 1.0
     * @return $this
     */
    public static function instance() {

        if ( is_null( self::$instance ) && ! ( self::$instance instanceof RCPL_Admin ) ) {
            self::$instance = new self;

            self::$instance->hooks();
        }

        return self::$instance;
    }

    /**
     * Plugin hooks
    */
    private function hooks() {

        add_action( 'admin_enqueue_scripts', [ $this, 'rcpl_admin_enqueue_scripts' ] );
        add_action( 'admin_menu', [ $this, 'rcpl_add_submenu_page_under_ld' ] );
        add_filter( 'plugin_action_links_'. RCPL_BASE_DIR, [ $this, 'rcpl_plugin_setting_links' ] );
        add_action( 'in_admin_header', [ $this, 'rcpl_remove_admin_notices' ], 100 );

        /**
         * Live ajax search hooks
         */
        add_action( 'wp_ajax_rcpl_search_users', [ $this, 'rcpl_live_search_users' ] );
        add_action( 'wp_ajax_rcpl_search_courses', [ $this, 'rcpl_live_search_course' ] );
        add_action( 'wp_ajax_rcpl_search_groups', [ $this, 'rcpl_live_search_groups' ] );
    }

    /**
     * Live search learndash groups
     */
    public function rcpl_live_search_groups() {

        $return = [];
        $search_query = isset( $_GET['q'] ) ? $_GET['q'] : '';
        $user_id = isset( $_GET['user_id'] ) ? ( int ) $_GET['user_id'] : 0;

        $arg = array(
            's'                 => $search_query,
            'post_status'       => 'publish',
            'post_type'         => 'groups',
            'fields'            => 'ids',
        );

        if( $user_id && 0 != $user_id ) {

            $group_ids = learndash_get_administrators_group_ids( $user_id );
            $arg['post__in'] = $group_ids;
        }

        $search_results = new WP_Query( $arg );

        if( $search_results->have_posts() ) {

            while( $search_results->have_posts() ) {

                $search_results->the_post();

                $ids = get_the_id();
                $title = get_the_title();

                $return[] = [ $ids, $title ];
            }
        }

        echo json_encode( $return );
        wp_die();
    }

    /**
     * Live search course list using :Ajax
     */
    public function rcpl_live_search_course() {

        $return = [];

        $group_ids = isset( $_GET['group_ids'] ) ? rest_sanitize_array( $_GET['group_ids'] ) : [];
        $search_query = isset( $_GET['q'] ) ? $_GET['q'] : '';

        $query_args = [
            's'                 => $search_query,
            'post_status'       => 'publish',
            'post_type'         => learndash_get_post_type_slug( 'course' ),
            'posts_per_page'    => -1,
            'fields'            => 'ids'
        ];

        if( ! empty( $group_ids ) ) {

            $loop_count = 1;
            foreach( $group_ids as $group_id ) {

                $query_args['meta_query'][$group_id] = [

                    'key' => 'learndash_group_enrolled_' . $group_id,
                    'compare' => 'EXISTS'
                ];

                $loop_count++;
            }

            if( $loop_count > 1 ) {
                $query_args['meta_query']['relation'] = 'OR';
            }
        }

        $search_results = new WP_Query( $query_args );
        if( $search_results->have_posts() ) {

            while( $search_results->have_posts() ) {

                $search_results->the_post();

                $ids = get_the_id();
                $title = get_the_title();

                $return[] = [ $ids, $title ];
            }
        }

        echo json_encode( $return );
        wp_die();
    }

    /**
     * Live search users list using :Ajax
     */
    public function rcpl_live_search_users() {

        global $wpdb;
        
        $return = [];
        $search_query = isset( $_GET['q'] ) ? $_GET['q'] : '';
        $capabilities = $wpdb->prefix.'capabilities';
        $get_users = $wpdb->get_results( "SELECT users.ID, users.display_name
        FROM {$wpdb->users} as users INNER JOIN {$wpdb->usermeta} as usermeta
        ON users.ID = usermeta.user_id 
        WHERE usermeta.meta_key = '$capabilities' 
        AND usermeta.meta_value NOT LIKE '%administrator%'
        AND users.user_login LIKE '%{$search_query}%' LIMIT 5", ARRAY_N );

        if( $get_users ) {
            $return = $get_users;
        }

        echo json_encode( $return );
        wp_die();
    }

    /**
     * Remove Admin notices on reset course progress submenu
     */
    public function rcpl_remove_admin_notices() {

        $screen = get_current_screen();

        if( $screen && $screen->id == 'learndash-lms_page_reset-course-progress-for-learndash' ) {

            remove_all_actions( 'admin_notices' );
        }
    }

    /**
     * Add Settings option on plugin activation
     *
     * @param $links
     * @return href
     */
    public function rcpl_plugin_setting_links( $links ) {

        $settings_link = '<a href="'. admin_url( 'admin.php?page=reset-course-progress-for-learndash' ) .'">'. __( 'Settings', 'reset-course-progress-for-learndash' ) .'</a>';
        array_unshift( $links, $settings_link );

        return $links;
    }

    /**
     * Add Reset Course Progress submenu page under learndash menus
     */
    public function rcpl_add_submenu_page_under_ld() {

        /**
         * Register a menu page.
         *
         * @see add_sub_menu_page()
         */
        add_submenu_page(
            'learndash-lms',
            __( 'Reset Course Progress', 'reset-course-progress-for-learndash' ),
            __( 'Reset Course Progress', 'reset-course-progress-for-learndash' ),
            'manage_options',
            'reset-course-progress-for-learndash',
            [ $this,'rcpl_reset_ld_course_content_cb']
        );
    }

    /**
     * Add RCPL setting page Tabs
     *
     * @param $current
     */
    public static function rcpl_setting_page_tabs( $current ) {

        $tabs = [
            'reset_by_users'        => __( 'Reset by Users', 'reset-course-progress-for-learndash' ),
            'reset_by_user_roles'   => __( 'Reset by User Roles', 'reset-course-progress-for-learndash' ),
            'reset_by_group'        => __( 'Reset by Group', 'reset-course-progress-for-learndash' ),
            'progress_result'       => __( 'Progress Result', 'reset-course-progress-for-learndash' )
        ];

        ob_start();

        ?>
        <!-- Tab Headers -->
        <div id="rcpl-main-wrap">
            <div class="rcpl-global-header">
                <h1><?php _e( 'Reset Course Progress For LearnDash.', 'reset-course-progress-for-learndash' ); ?></h1>
                <div class="rcpl-tab-buttons">
                    <?php
                    foreach( $tabs as $tab => $name ) {

                        $class = ( $tab == $current ) ? 'is-primary' : '';
                        ?>
                        <a href="<?php echo admin_url( '/admin.php?page=reset-course-progress-for-learndash&tab=' . esc_html( $tab ) ); ?>" class="is-button button components-button <?php echo $class; ?> "><?php echo $name; ?></a>
                        <?php
                    }
                    ?>
                </div>
            </div>
        </div>
        <!-- Tab Headers -->
        <?php

        $content = ob_get_contents();
        ob_get_clean();
        return $content;
    }

    /**
     * reset course menu page content
     */
    public function rcpl_reset_ld_course_content_cb() {

        $tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'reset_by_users';
        echo wp_kses( $this->rcpl_setting_page_tabs( $tab ), RCPL_Functions::allowed_html() );

        if ( $tab == 'reset_by_user_roles' ) {

            if( class_exists( 'Reset_by_User_Role' ) ) {
                Reset_by_User_Role::rcpl_user_role_tabs_html();
            }
        } else if( $tab == 'reset_by_users' ) {

            if( class_exists( 'Reset_by_Users' ) ) {
                Reset_by_Users::rcpl_users_tabs_html();
            }   
        } else if( $tab == 'reset_by_group' ) {

            if( class_exists( 'Reset_by_Group' ) ) {
                Reset_by_Group::rcpl_group_tabs_html();
            }   
        } else if( $tab == 'progress_result' ) {

            if( file_exists( RCPL_INCLUDES_DIR .'admin/class-rcpl-result.php' ) ) {

                require_once RCPL_INCLUDES_DIR . 'admin/class-rcpl-result.php';
            }
        }
    }
    
    /**
     * Enqueue admin scripts
     *
     * @return bool
     */
    public function rcpl_admin_enqueue_scripts() {

        $screen = get_current_screen();
        if( $screen ) {

            if( $screen->id == 'learndash-lms_page_reset-course-progress-for-learndash'
                || $screen->id == 'learndash-lms_page_rcpl-schedule-reset' ) {

                /**
                 * enqueue admin css
                 */
                wp_enqueue_style( 'rcpl-backend-css', RCPL_ASSETS_URL . 'css/backend.css', [], RCPL_VERSION, null );
                wp_enqueue_style( 'external-select-min-css', RCPL_ASSETS_URL .'css/select2.min.css' );
                wp_enqueue_style( 'rcpl-custom-popup-css-link', 'https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.css', [], RCPL_VERSION, null );

                /**
                 * add slect2 js
                 */
                wp_enqueue_script( 'external-select2-jquery-js', RCPL_ASSETS_URL. 'js/select2.full.min.js', ['jquery'], RCPL_VERSION, true );
                wp_enqueue_script( 'rcpl-backend-js', RCPL_ASSETS_URL . 'js/backend.js', [ 'jquery' , 'external-select2-jquery-js' ], RCPL_VERSION, true ); 
                wp_enqueue_script( 'rcpl-select2-addition', RCPL_ASSETS_URL . 'js/backend-select2-addition.js', [ 'jquery' , 'external-select2-jquery-js' ], RCPL_VERSION, true ); 
                wp_enqueue_script('wt-custom-bootstrap-pop-up-js', 'https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.js', ['jquery'], RCPL_VERSION, true);
                
                wp_localize_script( 'rcpl-backend-js', 'RcplNonce', array(
                    'security' => wp_create_nonce( 'rcpl_ajax_nonce' )
                ) );
            }
        }  
    }
}

RCPL_Admin::instance();