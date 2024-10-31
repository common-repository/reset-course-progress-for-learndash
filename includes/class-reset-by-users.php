<?php
/**
 * RCPL reset by users tabs content
 *
 * Do not allow directly accessing this file.
 */
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Reset_by_Users
 */
class Reset_by_Users {

    private static  $instance = null ;

    /**
     * @since 1.0
     * @return $this
     */
    public static function instance() {
        
        if ( is_null( self::$instance ) && !self::$instance instanceof Reset_by_Users ) {
            self::$instance = new self();
            self::$instance->hooks();
        }
        
        return self::$instance;
    }
    
    /**
     * Plugin hooks
     */
    private function hooks() {

        add_action( 'wp_ajax_rcpl_reset_course_by_users', [ $this, 'rcpl_reset_course_procress_by_users' ] );
        add_action( 'rcpl_reset_by_users', [ $this, 'rcpl_cron_reset_by_user' ], 10, 1 );
    }
    
    /**
     * Reset course using cron job
     * 
     * @param $args
     */
    public function rcpl_cron_reset_by_user( $args ) {

        @set_time_limit( 3600 );
        $user_id = $args['user_id'];
        $selected_rule = $args['reset_rule'];
        $course_ids = $args['course_ids'];
        foreach ( $course_ids as $course_id ) {
            RCPL_Functions::rcpl_course_resets( $course_id, $user_id );
        }
    }
    
    /**
     * Reset users course progress with users : Ajax 
     */
    public function rcpl_reset_course_procress_by_users() {

        check_ajax_referer( 'rcpl_ajax_nonce', 'security' );
        $response = [];
        $user_ids = '';
        $user_ids = ( isset( $_POST['target_id'] ) ? rest_sanitize_array( $_POST['target_id'] ) : [] );
        
        if ( empty($user_ids) ) {
            $response['status'] = 'false';
            $response['message'] = __( 'User Ids not found.', 'reset-course-progress-for-learndash' );
            echo  json_encode( $response ) ;
            wp_die();
        }
        
        $user_ids = array_map( 'intval', $user_ids );
        $selected_rule = ( isset( $_POST['reset_rule'] ) ? sanitize_text_field( $_POST['reset_rule'] ) : '' );
        
        if ( empty($selected_rule) ) {
            $response['status'] = 'false';
            $response['message'] = __( 'Reset rule not found.', 'reset-course-progress-for-learndash' );
            echo  json_encode( $response ) ;
            wp_die();
        }
        
        $course_ids = '';
        
        if ( 'select_courses' == $selected_rule ) {
            $course_ids = ( isset( $_POST['course_ids'] ) ? rest_sanitize_array( $_POST['course_ids'] ) : [] );
        } elseif ( 'all_courses' == $selected_rule ) {
            $course_ids = RCPL_Functions::get_ids_of_all_courses();
        }
        
        $course_ids = array_map( 'intval', $course_ids );
        $p_args = [];
        foreach ( $user_ids as $key => $user_id ) {
            $f_args = [
                'user_id'    => $user_id,
                'reset_rule' => $selected_rule,
                'course_ids' => $course_ids,
            ];
            $args = array_merge( $f_args, $p_args );
            RCPL_Functions::create_single_events( $key, 'rcpl_reset_by_users', $args );
            RCPL_Functions::update_cron_event_total_count( 'rcpl_reset_by_users' );
        }

        $response['status'] = 'true';
        echo  json_encode( $response ) ;
        wp_die();
    }
    
    /**
     * User reset by Users html content
     */
    public static function rcpl_users_tabs_html() {

        $reset_in_progress = RCPL_Functions::get_disabled_class( 'rcpl_reset_by_users' );
        ?>
        <div class="rcpl-wrap">

            <div class="rcpl-form-wrap">

                <!-- page tab heading -->
                <?php 
        echo  wp_kses( RCPL_Functions::tab_heading( 'Users' ), RCPL_Functions::allowed_html() ) ;
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
                        <input type="hidden" class="rcpl-save-user-ids" name="rcpl_save_user_ids[]" value="">
                        <?php 
        ?>

                        <!-- users dropdown html -->
                        <?php 
        echo  wp_kses( RCPL_Functions::get_users_dropdown_html(), RCPL_Functions::allowed_html() ) ;
        ?>
                        <!-- end users dropdown html -->
                        
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
        echo  wp_kses( RCPL_Functions::reset_note_message_html( 'users', $reset_in_progress, 'user' ), RCPL_Functions::allowed_html() ) ;
        ?>
                    <!-- end reset message html -->

                    <div class="rcpl-form-actions <?php 
        echo  sanitize_html_class( $reset_in_progress ) ;
        ?>">
                        <input type="button" class="rcpl-reset-by-users button button-primary" value="<?php 
        _e( 'Reset Now', 'reset-course-progress-for-learndash' );
        ?>" name="u_rcpl_submit_settings" />
                        
                        <?php 
        /**
         * Fired after reset button
         * 
         * @param users ( tab )
         */
        do_action( 'rcpl_after_reset_button', 'users' );
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
Reset_by_Users::instance();