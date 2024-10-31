<?php
/**
 * RCPL reset by group tabs content
 *
 * Do not allow directly accessing this file.
 */
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Reset_by_Group
 */
class Reset_by_Group {

    private static  $instance = null ;

    /**
     * @since 1.0
     * @return $this
     */
    public static function instance() {
        
        if ( is_null( self::$instance ) && !self::$instance instanceof Reset_by_Group ) {
            self::$instance = new self();
            self::$instance->hooks();
        }
        
        return self::$instance;
    }
    
    /**
     * plugin hooks
     */
    private function hooks() {

        add_action( 'wp_ajax_rcpl_reset_course_by_group', [ $this, 'rcpl_reset_course_progress_by_group' ] );
        add_action( 'rcpl_reset_by_groups', [ $this, 'rcpl_cron_reset_by_groups' ], 10, 1 );
    }
    
    /**
     * Course reset using cron job
     * 
     * @param $args
     */
    public function rcpl_cron_reset_by_groups( $args ) {

        $course_ids = $args['course_ids'];
        $selected_rule = $args['reset_rule'];
        $user_id = $args['user_id'];
        foreach ( $course_ids as $course_id ) {
            RCPL_Functions::rcpl_course_resets( $course_id, $user_id );
        }
    }
    
    /**
     * Reset users course progress by group : Ajax
     */
    public function rcpl_reset_course_progress_by_group() {

        check_ajax_referer( 'rcpl_ajax_nonce', 'security' );
        $response = [];
        $group_ids = ( isset( $_POST['target_id'] ) ? rest_sanitize_array( $_POST['target_id'] ) : [] );
        
        if ( empty($group_ids) ) {
            $response['status'] = 'false';
            $response['message'] = __( 'Group ID not found.', 'reset-course-progress-for-learndash' );
            echo  json_encode( $response ) ;
            wp_die();
        }
        
        $group_ids = array_map( 'intval', $group_ids );
        $selected_rule = ( isset( $_POST['reset_rule'] ) ? sanitize_text_field( $_POST['reset_rule'] ) : '' );
        
        if ( empty($selected_rule) ) {
            $response['status'] = 'false';
            $response['message'] = __( 'Reset rule not found.', 'reset-course-progress-for-learndash' );
            echo  json_encode( $response ) ;
            wp_die();
        }
        
        $p_args = [];
        foreach ( $group_ids as $group_id ) {

            $user_ids = learndash_get_groups_users( $group_id );
            if ( !$user_ids ) {
                continue;
            }

            foreach ( $user_ids as $key => $user ) {
                $user_id = (int) $user->data->ID;
                $course_ids = '';
                
                if ( 'select_courses' == $selected_rule ) {
                    $course_ids = ( isset( $_POST['course_ids'] ) ? rest_sanitize_array( $_POST['course_ids'] ) : [] );
                } elseif ( 'all_courses' == $selected_rule ) {
                    $course_ids = learndash_get_group_courses_list( $group_id );
                }
                
                $course_ids = array_map( 'intval', $course_ids );
                $f_args = [
                    'user_id'    => $user_id,
                    'reset_rule' => $selected_rule,
                    'course_ids' => $course_ids,
                ];
                $args = array_merge( $f_args, $p_args );
                RCPL_Functions::create_single_events( $key, 'rcpl_reset_by_groups', $args );
                RCPL_Functions::update_cron_event_total_count( 'rcpl_reset_by_groups' );
            }
        }
        $response['status'] = 'true';
        echo  json_encode( $response ) ;
        wp_die();
    }
    
    /**
     * User reset by Users html content
     */
    public static function rcpl_group_tabs_html() {

        $reset_in_progress = RCPL_Functions::get_disabled_class( 'rcpl_reset_by_groups' );
        ?>
        <div class="rcpl-wrap">

            <div class="rcpl-form-wrap">

                <!-- page tab heading -->
                <?php 
        echo  wp_kses( RCPL_Functions::tab_heading( 'Group' ), RCPL_Functions::allowed_html() ) ;
        ?>
                <!-- end page tab heading -->

                <!-- loader on page reload -->
                <?php 
        echo  wp_kses( RCPL_Functions::loader_on_page_reload(), RCPL_Functions::allowed_html() ) ;
        ?>
                <!-- end loader on page reload -->

                <!-- Ajax Success message html -->
                <?php 
        echo  wp_kses( RCPL_Functions::ajax_success_message_html(), RCPL_Functions::allowed_html() ) ;
        ?>
                <!-- End ajax success message html -->

                <div class="rcpl-form-content">
                    <div class="rcpl-from-material <?php 
        echo  sanitize_html_class( $reset_in_progress ) ;
        ?>">
                        <input type="hidden" class="rcpl-save-group-ids" name="rcpl_save_group_ids[]" value="">

                        <?php 
        ?>

                        <!-- group dropdown html -->
                        <?php 
        echo  wp_kses( RCPL_Functions::get_group_dropdown_html( '' ), RCPL_Functions::allowed_html() ) ;
        ?>
                        <!-- end group dropdown html -->

                        <?php 
        ?>

                        <!-- Get reset rules html -->
                        <?php 
        echo  wp_kses( RCPL_Functions::get_reset_rules_html(), RCPL_Functions::allowed_html() ) ;
        ?>
                        <!-- End reset rules html -->
                        
                        <?php 
        ?>

                        <!-- group course dropdown -->
                        <?php 
        echo  wp_kses( RCPL_Functions::fetching_group_course_html(), RCPL_Functions::allowed_html() ) ;
        ?>
                        <!-- end group course dropdown -->

                        <?php 
        ?>
                    </div>

                    <!-- Reset message html -->
                    <?php 
        echo  wp_kses( RCPL_Functions::reset_note_message_html( 'groups', $reset_in_progress, 'group' ), RCPL_Functions::allowed_html() ) ;
        ?>
                    <!-- end reset message html -->

                    <div class="rcpl-form-actions <?php 
        echo  sanitize_html_class( $reset_in_progress ) ;
        ?>">
                        <input type="button" class="rcpl-reset-by-group button button-primary" value="<?php 
        _e( 'Reset Now', 'reset-course-progress-for-learndash' );
        ?>" name="g_rcpl_submit_settings" />

                        <?php 
        /**
         * Fired after reset button
         * 
         * @param users ( tab )
         */
        do_action( 'rcpl_after_reset_button', 'groups' );
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
Reset_by_Group::instance();