<?php
/**
 * RCPL reset by user role tabs content
 *
 * Do not allow directly accessing this file.
 */
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Reset_by_User_Role
 */
class Reset_by_User_Role {

    private static  $instance = null ;
    /**
     * @since 1.0
     * @return $this
     */
    public static function instance() {
        
        if ( is_null( self::$instance ) && !self::$instance instanceof Reset_by_User_Role ) {
            self::$instance = new self();
            self::$instance->hooks();
        }
        
        return self::$instance;
    }
    
    /**
     * Plugin hooks
     */
    private function hooks() {

        add_action( 'wp_ajax_rcpl_reset_course_by_roles', [ $this, 'rcpl_reset_course_progress_by_user_role' ] );
        add_action( 'rcpl_reset_by_roles', [ $this, 'rcpl_cron_reset_by_user_roles' ], 10, 1 );
    }
    
    /**
     * Course reset using cron job
     * 
     * @param $args
     */
    public function rcpl_cron_reset_by_user_roles( $args ) {

        $user_id = $args['user_id'];
        $selected_rule = $args['reset_rule'];
        $course_ids = $args['course_ids'];
        foreach ( $course_ids as $course_id ) {
            RCPL_Functions::rcpl_course_resets( $course_id, $user_id );
        }
    }
    
    /**
     * Reset users course progress with user roles : Ajax 
     */
    public function rcpl_reset_course_progress_by_user_role() {

        check_ajax_referer( 'rcpl_ajax_nonce', 'security' );
        $response = [];
        $course_ids = '';
        $selected_rule = ( isset( $_POST['reset_rule'] ) ? sanitize_text_field( $_POST['reset_rule'] ) : '' );
        
        if ( empty($selected_rule) ) {

            $response['status'] = 'false';
            $response['message'] = __( 'Reset rule not found.', 'reset-course-progress-for-learndash' );
            echo  json_encode( $response ) ;
            wp_die();
        }
        
        
        if ( 'select_courses' == $selected_rule ) {
            $course_ids = ( isset( $_POST['course_ids'] ) ? rest_sanitize_array( $_POST['course_ids'] ) : [] );
        } elseif ( 'all_courses' == $selected_rule ) {
            $course_ids = RCPL_Functions::get_ids_of_all_courses();
        }
        
        $course_ids = array_map( 'intval', $course_ids );
        if ( empty( $course_ids ) ) {
            $response['status'] = 'false';
            $response['message'] = __( 'Course Ids rule not found.', 'reset-course-progress-for-learndash' );
            echo  json_encode( $response ) ;
            wp_die();
        }
        
        $user_roles = ( isset( $_POST['target_id'] ) ? rest_sanitize_array( $_POST['target_id'] ) : [] );
        if ( empty( $user_roles ) ) {
            $response['status'] = 'false';
            $response['message'] = __( 'User roles not found.', 'reset-course-progress-for-learndash' );
            echo  json_encode( $response ) ;
            wp_die();
        }
        
        $users = RCPL_Functions::get_all_user_ids( $user_roles );
        if ( empty( $users ) ) {
            $response['status'] = 'false';
            $response['message'] = __( 'Users object not found.', 'reset-course-progress-for-learndash' );
            echo  json_encode( $response ) ;
            wp_die();
        }
        
        $p_args = [];
        foreach ( $users as $key => $user ) {

            $user_id = (int) $user;
            $f_args = [
                'user_id'    => $user_id,
                'reset_rule' => $selected_rule,
                'course_ids' => $course_ids,
            ];
            $args = array_merge( $f_args, $p_args );
            RCPL_Functions::create_single_events( $key, 'rcpl_reset_by_roles', $args );
            RCPL_Functions::update_cron_event_total_count( 'rcpl_reset_by_roles' );
        }
        $response['status'] = 'true';
        echo  json_encode( $response ) ;
        wp_die();
    }
    
    /**
     * User reset by role html content
     */
    public static function rcpl_user_role_tabs_html() {

        $reset_in_progress = RCPL_Functions::get_disabled_class( 'rcpl_reset_by_roles' );
        ?>
        <div class="rcpl-wrap">

            <div class="rcpl-form-wrap">

                <!-- page tab heading -->
                <?php 
        echo  wp_kses( RCPL_Functions::tab_heading( 'User Roles' ), RCPL_Functions::allowed_html() ) ;
        ?>
                <!-- end page tab heading -->

                <!-- Ajax Success message html -->
                <?php 
        echo  wp_kses( RCPL_Functions::ajax_success_message_html(), RCPL_Functions::allowed_html() ) ;
        ?>
                <!-- End ajax success message html -->

                <!-- loader on page reload -->
                <?php 
        echo  wp_kses( RCPL_Functions::loader_on_page_reload(), RCPL_Functions::allowed_html() ) ;
        ?>
                <!-- end loader on page reload -->

                <div class="rcpl-form-content">
                    <div class="rcpl-from-material <?php 
        echo  sanitize_html_class( $reset_in_progress ) ;
        ?>">
                        <?php 
        ?>
                        
                        <!-- user role dropdown -->
                        <?php 
        echo  wp_kses( RCPL_Functions::get_user_role_dropdown_html(), RCPL_Functions::allowed_html() ) ;
        ?>
                        <!-- end user role dropdown -->
                        
                        <?php 
        ?>

                        <!-- Get reset rules html -->
                        <?php 
        echo  wp_kses( RCPL_Functions::get_reset_rules_html(), RCPL_Functions::allowed_html() ) ;
        ?>
                        <!-- End reset rules html -->

                        <?php 
        ?>

                        <!-- course dropdown -->
                        <?php 
        echo  wp_kses( RCPL_Functions::get_course_field_dropdown_html(), RCPL_Functions::allowed_html() ) ;
        ?>
                        <!-- end course dropdown -->

                        <?php 
        ?>
                    </div>
                    
                    <!-- Reset message html -->
                    <?php 
        echo  wp_kses( RCPL_Functions::reset_note_message_html( 'roles', $reset_in_progress, 'user role' ), RCPL_Functions::allowed_html() ) ;
        ?>
                    <!-- end reset message html -->

                    <div class="rcpl-form-actions <?php 
        echo  sanitize_html_class( $reset_in_progress ) ;
        ?>">
                        <input class="rcpl-reset-by-roles button button-primary" type="button" class="button button-primary" value="<?php 
        _e( 'Reset Now', 'reset-course-progress-for-learndash' );
        ?>" name="rcpl_submit_settings" />

                        <?php 
        /**
         * Fired after reset button
         * 
         * @param users ( tab )
         */
        do_action( 'rcpl_after_reset_button', 'roles' );
        ?>
                    </div>
                </div>
            </div>  
        </div>
        <?php 
    }

}

/**
 * Class instance.
 */
Reset_by_User_Role::instance();