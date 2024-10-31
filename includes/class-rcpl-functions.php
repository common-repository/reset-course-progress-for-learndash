<?php
/**
 * RCPL functions
 *
 * Do not allow directly accessing this file.
 */

if( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class RCPL_Functions
 */
class RCPL_Functions {

    private static $instance = null;

    /**
     * @since 1.0
     * @return $this
     */
    public static function instance() {

        if ( is_null( self::$instance ) && ! ( self::$instance instanceof RCPL_Functions ) ) {

            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Get disabled class
     * 
     * @param $action
     */
    public static function get_disabled_class( $action ) {

        $reset_in_progress = '';
        $event_count = get_option( $action );
        if( $event_count && 0 != $event_count ) {
            $reset_in_progress = 'rcpl-disabled';
        }

        return $reset_in_progress;
    }

    /**
     * Update cron event count in db
     * 
     * @param $action
     */
    public static function update_cron_event_total_count( $action ) {

        $sum = 0;
        $cron = self::get_cron_schedule_data();
        if( ! $cron || ! is_array( $cron ) ) {
            return false;
        }

        foreach( $cron as $key => $schedule ) {

            $event = isset( $schedule[$action] ) ? $schedule[$action] : '';
            if( empty( $event ) ) {
                continue;
            }
            $sum += count( $schedule[$action] );
        }
        
        update_option( $action, $sum );
    }

    /**
     * Get cron schedules data
     */
    public static function get_cron_schedule_data() {

        $cron = get_option( 'cron' );
        if( ! $cron ) {
            return false;
        }
        return $cron;
    }

    /**
     * Get increament time
     * 
     * @param $index
     */
    public static function create_single_events( $index, $action, $args ) {

        $time = time();
        $increment = $index * 10;
        $times = $time + $increment;
        wp_schedule_single_event( $times, $action, [ $args ] );
    }

    /**
     * Reset note message html content
     * 
     * @param $tab
     * @param $reset_in_progress
     */
    public static function reset_note_message_html( $tab, $reset_in_progress, $tab_id ) {

        ob_start();

        $message = __( '<span>Note:<span> <span class="rcpl-note">Selected course/courses will be reset with selected '.$tab_id.'/'.$tab.'.</span>', 'reset-course-progress-for-learndash' );

        if( 'rcpl-disabled' == $reset_in_progress ) {
            $link = admin_url( 'admin.php?page=reset-course-progress-for-learndash&tab=progress_result' );
            $message = __( '<span>Note:<span> <span class="rcpl-note">Reset is being processed, please check reset result by clicking this <a href="'.$link.'">button.</a></span>', 'reset-course-progress-for-learndash' );
        }
        ?>
        <div class="rcpl-reset-note">
            <?php echo wp_kses( $message, self::allowed_html() ); ?>
        </div>
        <?php

        $content = ob_get_contents();
        ob_get_clean();

        return $content;
    }

    /**
     * Create html for tabs heading
     * 
     * @param $heading
     */
    public static function tab_heading( $heading ) {

        ob_start();

        ?>
        <div class="rcpl-page-heading">
            <?php _e( 'Reset Course by '.$heading.' ', 'reset-course-progress-for-learndash' ); ?>
        </div>
        <?php

        $content = ob_get_contents();
        ob_get_clean();

        return $content;
    }

    /**
     * Create loader html till page reload
     */
    public static function loader_on_page_reload() {

        ob_start()

        ?>
        <div class="rcpl-page-loader">
            <div class="dot-pulse"></div>
        </div>
        <?php

        $content = ob_get_contents();
        ob_get_clean();

        return $content;
    }

    /**
     * Allowed html attributes for wp_kses function 
     */
    public static function allowed_html() {

        $allowed_html = [
            'div'   => [
                'class'     => [],
                'data-user_id'  => [],
                'id'   => [],
            ],
            'label' => [
                'class'     => [],
                'for'       => []
            ],
            'select' => [
                'class'     => [],
                'id'        => [],
                'name'      => [],
                'multiple'  => []
            ],
            'option' => [
                'disabled'   => [],
                'value'      => []
            ],
            'input' => [
                'class'     => [],
                'type'      => [],
                'name'      => [],
                'checked'   => [],
                'value'     => []
            ],
            'span' => [
                'class'     => []
            ],
            'img' => [
                'class'     => [],
                'src'      => []
            ],
            'h1' => [
                'class'   => [],
            ],
            'a' => [
                'class'   => [],
                'href'    => []
            ]
        ];

        return $allowed_html;
    }

    /**
     * Fetching group course dropdown html
     */
    public static function fetching_group_course_html() {
        
        ob_start();
        ?>
        <div class="rcpl-wrapper rcpl-select-course-fields">
            <label for="user_course"><?php _e( 'Select Course(s) :', 'reset-course-progress-for-learndash' ); ?></label>
            <select id="select_course" class="rcpl-select-course-list" multiple="multiple">
                <option disabled="disabled"><?php _e( 'Select Course', 'reset-course-progress-for-learndash' ); ?></option> 
            </select>
            <div class="rcpl-validate-msg"></div>
        </div>
        <?php

        $content = ob_get_contents();
        ob_get_clean();

        return $content;
    }

    /**
     * Create group dropdown html
     */
    public static function get_group_dropdown_html( $user_id ) {

        ob_start();

        ?>
        <div data-user_id="<?php echo esc_html( $user_id ); ?>" class="rcpl-wrapper rcpl-select-group-field">
            <label for="group"><?php _e( 'Select Group :', 'reset-course-progress-for-learndash' ); ?></label>
            <select id="select_groups" class="rcpl-select-group-list" multiple="multiple"></select>
            <div class="rcpl-validate-msg"></div>
        </div>
        <?php

        $content = ob_get_contents();
        ob_get_clean();

        return $content;
    }

    /**
     * Create ajax success message html
     */
    public static function ajax_success_message_html() {

        ob_start();

        ?>
        <div class="rcpl-success-message">
            <img class="rcpl-success-loader" src="<?php echo RCPL_ASSETS_URL .'images/spinner-2x.gif'; ?>" />
            <div class="rcpl-loading-wrap"><?php _e( 'Please wait! Reset is being processed.' ); ?>
                <div class="rcpl-animated-dot">
                    <div class="dot-pulse"></div>
                </div>
            </div>
        </div>
        <?php

        $content = ob_get_contents();
        ob_get_clean();

        return $content;
    }

    /**
     * Create users dropdown html
     */
    public static function get_users_dropdown_html() {

        ob_start();
        ?>
        <div class="rcpl-wrapper rcpl-select-users-fields">
            <label for="users"><?php _e( 'Select User(s) :', 'reset-course-progress-for-learndash' ); ?></label>
            <select id="select_users" class="rcpl-select-user-list" multiple="multiple">
              
            </select>
            <div class="rcpl-validate-msg"></div>
        </div>
        <?php
        
        $content = ob_get_contents();
        ob_get_clean();

        return $content;
    }

    /**
     * Create user role dropdown html
     */
    public static function get_user_role_dropdown_html() {

        global $wp_roles; 
        ob_start();
        
        ?>
        <div class="rcpl-wrapper rcpl-user-role-fields">
            <label for="user_role"><?php _e( 'Select User Role(s) :', 'reset-course-progress-for-learndash' ); ?></label>
            <select id="select_role" class="rcpl-select-user-roles" multiple="multiple">
                <option disabled="disabled"><?php _e( 'Select User Role', 'reset-course-progress-for-learndash' ); ?></option>
                <?php
                if( $wp_roles ) {
                    foreach( $wp_roles->roles as $user_role => $role_name ) {

                        if( $user_role == 'administrator' ) {
                            continue;
                        }
                        ?>
                        <option value="<?php echo esc_html( $user_role ); ?>" ><?php echo esc_html( $role_name['name'] ); ?></option>
                        <?php
                    }
                }
                ?>
            </select>
            <div class="rcpl-validate-msg"></div>
        </div>
        <?php

        $content = ob_get_contents();
        ob_get_clean();

        return $content;
    }

    /**
     * Create course field dropdown html
     * 
     * @param $name    ( attr )
     */
    public static function get_course_field_dropdown_html() {

        ob_start();
        ?>
        <div class="rcpl-wrapper rcpl-select-course-fields">
            <label for="user_course"><?php _e( 'Select Course(s) :', 'reset-course-progress-for-learndash' ); ?></label>
            <select id="select_course" class="rcpl-select-course-list" multiple="multiple"></select>
            <div class="rcpl-validate-msg"></div>
        </div>
        <?php

        $content = ob_get_contents();
        ob_get_clean();

        return $content;
    }

    /**
     * Create reset rules html
     */
    public static function get_reset_rules_html() {

        ob_start();

        ?>
        <!-- Select user rules -->
        <div class="rcpl-wrapper">
            <label for="users"><?php _e( 'Select reset rule :', 'reset-course-progress-for-learndash' ); ?></label>
            <div class="rcpl-rule-list">
                <input checked="checked" type="radio" value="select_courses" class="rcpl-select-rules-option" name="rcpl_select_rules_option">
                <span><?php _e( 'Selected courses', 'reset-course-progress-for-learndash' ); ?></span>
                <input type="radio" value="all_courses" class="rcpl-select-rules-option rcpl-rule-left" name="rcpl_select_rules_option">
                <span><?php _e( 'All courses', 'reset-course-progress-for-learndash' ); ?></span>
            </div>
            <div class="rcpl-validate-msg"></div>
        </div>
        <!-- end user rules -->
        <?php

        $content = ob_get_contents();
        ob_get_clean();

        return $content;
    }

    /**
     * Get learndash groups
     */
    public static function get_ids_of_all_groups() {

        $post_groups = [
            'posts_per_page'    => -1,
            'post_type'         => 'groups',
            'post_status'       => 'publish',
            'fields'            => 'ids'
        ];

        $group_ids = get_posts( $post_groups );

        if( $group_ids ) {

            return $group_ids;
        }
    }

    /**
     * Get all user Ids
     * 
     * @param $user_roles
     */
    public static function get_all_user_ids( $user_roles = '' ) {

        $role_args = [
            'role__not_in'  => 'administrator'  
        ];

        if( ! empty( $user_roles ) && is_array( $user_roles ) ) {
            $role_args = [
                'role__in'  => $user_roles  
            ];
        }

        $args = [
            'fields'      => 'IDs',
            'number'      => -1
        ];

        $arguments = array_merge( $args, $role_args );
        $user_query = new WP_User_Query( $arguments );   
        $users = $user_query->get_results();

        return $users;
    }

    /**
     * Get ids of all courses
     */
    public static function get_ids_of_all_courses() {

        $post_course = [
            'posts_per_page'    => -1,
            'post_type'         => 'sfwd-courses',
            'post_status'       => 'publish',
            'fields'            => 'ids'
        ];

        $course_ids = get_posts( $post_course );
        if( $course_ids ) {
            
            return $course_ids;
        }
    }

    /**
     * Reset LD course by schedule
     *
     * @param $course_id
     * @param $user_id
     */
    public static function rcpl_course_resets( $course_id, $user_id ) {

        if( ! sfwd_lms_has_access( $course_id, $user_id ) ) {
            return false;
        }

        $lesson_list = learndash_get_lesson_list( $course_id );
        if( $lesson_list ) {
            foreach( $lesson_list as $lessons ) {

                /**
                 * Mark lesson as incomplete
                 */
                learndash_process_mark_incomplete( $user_id, $course_id, $lessons->ID );
                
                $lesson_quiz_list = learndash_get_lesson_quiz_list( $lessons->ID, $user_id, $course_id );
                if( $lesson_quiz_list ) {
                    foreach( $lesson_quiz_list as $lesson_quizzes ) {

                        /**
                         * Mark lesson quiz as incomplete
                        */
                        learndash_delete_quiz_progress( $user_id, $lesson_quizzes['post']->ID );
                    }   
                }
            
                $topic_list = learndash_get_topic_list( $lessons->ID , $course_id );
                if( $topic_list ) {
                    foreach ( $topic_list as $topics ) {

                        /**
                         * Mark topic as incomplete
                         */
                        learndash_process_mark_incomplete( $user_id, $course_id, $topics->ID );
                    
                        $topic_quiz_list = learndash_get_lesson_quiz_list( $topics->ID, $user_id, $course_id );
                        if( ! $topic_quiz_list ) {
                            continue;
                        }

                        foreach( $topic_quiz_list as $topic_quizzes ) {

                            /**
                             * Mark topic quiz as incomplete
                             */
                            learndash_delete_quiz_progress( $user_id, $topic_quizzes['post']->ID );
                        }
                    }   
                }
            }
        }

        $quiz_list = learndash_get_course_quiz_list( $course_id, $user_id );
        if( $quiz_list ) {
            foreach( $quiz_list as $quizzes ) {

                /**
                 * Mark course quiz as incomplete
                */
                learndash_delete_quiz_progress( $user_id, $quizzes['post']->ID );
            } 
        }
    }
}

/**
 * Class instance.
 */
RCPL_Functions::instance();