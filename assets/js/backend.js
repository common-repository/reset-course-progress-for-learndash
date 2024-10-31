(function( $ ) { 'use strict';
    $( document ).ready( function() {
        var RCPLbackEnd = {
            init: function() {
                this.addLoaderOnPageLoad();
                this.onChangeOnGroup();
                this.onChangeOnRuleOptions();
                this.resetCourseByUsers();
                this.resetCourseByUserRoles();
                this.onChangeUsersField();
                this.resetCourseByGroup();
            },

            /**
             * Save user IDs on change users field
             */
            onChangeUsersField: function() {

                if( $( '.rcpl-select-user-list' ).length > 0 ) {

                    $( '.rcpl-select-user-list' ).on( 'change', function() {

                        let self = $( this );
                        let userIDs = self.val();

                        let parent = self.parents( '.rcpl-form-wrap' );

                        $( '.rcpl-save-user-ids' ).val( userIDs );

                        

                    } );
                }
            },

            /**
             * Execute js functionality after page load
             */
            addLoaderOnPageLoad: function() {

                $( document ).ready( function() {
                    $( '.rcpl-form-content' ).css( 'visibility', 'visible' );
                    $( '.rcpl-page-loader' ).hide();
                });
            },

            /**
             * Reseting course by group
             */
            resetCourseByGroup: function() {

                if( $( '.rcpl-reset-by-group' ).length > 0 ) {

                    $( '.rcpl-reset-by-group' ).on( 'click', function() {

                        $( '.rcpl-validate-msg' ).html( '' );

                        let error = 0;

                        let self = $( this );
                        let parent = self.parents( '.rcpl-form-wrap' );

                        let groupID = parent.find( '.rcpl-save-group-ids' ).val();
                        if( RCPLbackEnd.formValidation( groupID, parent, '.rcpl-select-group-list' ) ) {

                            error++;
                        }

                        let resetRule = parent.find( '.rcpl-select-rules-option:checked' ).val();
                        if( RCPLbackEnd.formValidation( resetRule, parent, '.rcpl-select-rules-option' ) ) {

                            error++;
                        }

                        let coursesIDs = '';
                        if( 'select_courses' == resetRule ) {

                            coursesIDs = parent.find( '.rcpl-select-course-list' ).val();
                            if( RCPLbackEnd.formValidation( coursesIDs, parent, '.rcpl-select-course-list' ) ) {

                                error++;
                            }
                        }

                        let lessonIDs = '';
                        let topicIDs = '';
                        let quizID = '';
                        let lessonReset = false;
                        let topicReset = false;
                        let progressOption = '';

                        

                        if( error != 0 )
                            return false;

                        RCPLbackEnd.executeAjaxAfterErrorRemove( 'rcpl_reset_course_by_group', groupID, resetRule, coursesIDs, progressOption, lessonIDs, topicIDs, quizID, lessonReset, topicReset );
                    } );
                }
            },

            /**
             * Reseting course progress by user roles
             */
            resetCourseByUserRoles: function() {

                if( $( '.rcpl-reset-by-roles' ).length > 0 ) {

                    $( '.rcpl-reset-by-roles' ).on( 'click', function() {

                        $( '.rcpl-validate-msg' ).html( '' );

                        let error = 0;

                        let self = $( this );
                        let parent = self.parents( '.rcpl-form-wrap' );

                        let userRoles = parent.find( '.rcpl-select-user-roles' ).val();
                        if( RCPLbackEnd.formValidation( userRoles, parent, '.rcpl-select-user-roles' ) ) {

                            error++;
                        }

                        let resetRule = parent.find( '.rcpl-select-rules-option:checked' ).val();
                        if( RCPLbackEnd.formValidation( resetRule, parent, '.rcpl-select-rules-option' ) ) {

                            error++;
                        }

                        let coursesIDs = '';
                        if( 'select_courses' == resetRule ) {

                            coursesIDs = parent.find( '.rcpl-select-course-list' ).val();
                            if( RCPLbackEnd.formValidation( coursesIDs, parent, '.rcpl-select-course-list' ) ) {

                                error++;
                            }
                        }

                        let lessonIDs = '';
                        let topicIDs = '';
                        let quizID = '';
                        let progressOption = '';
                        let lessonReset = false;
                        let topicReset = false;

                        /* </fs_premium_only> */
                        progressOption = parent.find( '.rcpl-reset-progress-option:checked' ).val();
                        if( 'reset_quiz_progress' == progressOption ) {

                            lessonIDs = parent.find( '.rcpl-select-lesson-list' ).val();
                            if( lessonIDs != '' ) {

                                topicIDs = parent.find( '.rcpl-select-topic-list' ).val();
                            }

                            quizID = parent.find( '.rcpl-select-quiz-list' ).val();
                            if( $( '.rcpl-reset-lessons' ).prop( 'checked' ) == true ) {

                                lessonReset = true;
                            }

                            if( $( '.rcpl-reset-topics' ).prop( 'checked' ) == true ) {

                                topicReset = true;
                            }
                        }
                        /* </fs_premium_only> */

                        if( error != 0 )
                            return false;
                        RCPLbackEnd.executeAjaxAfterErrorRemove( 'rcpl_reset_course_by_roles', userRoles, resetRule, coursesIDs, progressOption, lessonIDs, topicIDs, quizID, lessonReset, topicReset );
                    } );
                }
            },

            /**
             * Reseting course progress by users
             */
            resetCourseByUsers: function() {

                if( $( '.rcpl-reset-by-users' ).length > 0 ) {

                    $( '.rcpl-reset-by-users' ).on( 'click', function() {

                        $( '.rcpl-validate-msg' ).html( '' );

                        let error = 0;

                        let self = $( this );
                        let parent = self.parents( '.rcpl-form-wrap' );

                        

                        let userIDs = $( '.rcpl-save-user-ids' ).val();
                        if( RCPLbackEnd.formValidation( userIDs, parent, '.rcpl-select-user-list' ) ) {

                            error++;
                        }

                        let resetRule = parent.find( '.rcpl-select-rules-option:checked' ).val();
                        if( RCPLbackEnd.formValidation( resetRule, parent, '.rcpl-select-rules-option' ) ) {

                            error++;
                        }

                        let coursesIDs = '';
                        if( 'select_courses' == resetRule ) {

                            coursesIDs = parent.find( '.rcpl-select-course-list' ).val();
                            if( RCPLbackEnd.formValidation( coursesIDs, parent, '.rcpl-select-course-list' ) ) {

                                error++;
                            }
                        }

                        let progressOption = '';
                        let lessonIDs = '';
                        let topicIDs = '';
                        let quizID = '';
                        let lessonReset = false;
                        let topicReset = false;

                        

                        if( error != 0 ) {
                            return false;
                        }

                        RCPLbackEnd.executeAjaxAfterErrorRemove( 'rcpl_reset_course_by_users', userIDs, resetRule, coursesIDs, progressOption, lessonIDs, topicIDs, quizID, lessonReset, topicReset );

                    } );
                }
            },

            /**
             * Exicute Ajax functionality after error message.
             *
             * @param action
             * @param targetID
             * @param resetRule
             * @param courseIDs
             * @param progressOption    ( is_premium )
             * @param lessonIDs         ( is_premium )
             * @param topicIDs          ( is_premium )
             * @param quizIds           ( is_mremium )
             */
            executeAjaxAfterErrorRemove: function(action, targetID, resetRule, courseIDs, progressOption, lessonIDs, topicIDs, quizIds, lessonReset, topicReset ) {

                // let resetMessage = $( '.rcpl-note' ).text();
                let resetMessage = 'Do you really want to reset the course/courses?';

                setTimeout( function() {

                    $.confirm({
                        title: false,
                        content: resetMessage,
                        buttons: {
                            Yes: function () {

                                RCPLbackEnd.successResponseAlert( 'before' );

                                let ajaxNonce = RcplNonce.security;

                                let data = {
                                    'action'            : action,
                                    'security'          : ajaxNonce,
                                    'target_id'         : targetID,
                                    'course_ids'        : courseIDs,
                                    'reset_rule'        : resetRule,
                                    'progress_option'   : progressOption,
                                    'lesson_ids'        : lessonIDs,
                                    'topic_ids'         : topicIDs,
                                    'quiz_id'           : quizIds,
                                    'topic_reset'       : topicReset
                                };

                                jQuery.post( ajaxurl, data, function( response ) {

                                    RCPLbackEnd.successResponseAlert( response );
                                } );
                            },
                            No: function () {},
                        }
                    });

                },50 );
            },

            /**
             * Create Validation message
             *
             * @param existsVal
             * @param parent
             * @param beforeClass
             */
            formValidation: function( existsVal, parent, beforeClass ) {

                if( '' == existsVal || ! existsVal ) {
                    parent.find( beforeClass ).parents( '.rcpl-wrapper' ).find( '.rcpl-validate-msg' ).html( 'Please fill out this field.' );

                    return true;
                }
            },

            /**
             * Alert message before/after ajax response
             *
             * @param response
             */
            successResponseAlert: function( response ) {

                if( 'before' == response ) {

                    $( '.rcpl-form-content' ).css( 'visibility', 'hidden' );
                    $( '.rcpl-success-message' ).show();
                } else {

                    let jsonEncode = JSON.parse( response );
                    if( jsonEncode.status == 'false' ) {
                        console.log( jsonEncode.message );
                    } else {
                        location.reload( true );
                    }
                }
            },

            /**
             * Hide/show courses select field
             * Save course ids in hidden field
             */
            onChangeOnRuleOptions: function() {

                if( $( '.rcpl-select-rules-option' ).length > 0 ) {

                    $( '.rcpl-select-rules-option' ).on( 'change', function() {

                        let self = $( this );
                        let parent = self.parents( '.rcpl-form-wrap' );
                        let userOption = $( '.rcpl-selected-users-option:checked' ).val();
                        if( ! userOption ) {
                            userOption = $( '.rcpl-selected-group-option:checked' ).val();
                        }

                        let progressOption = '';
                        progressOption = parent.find( '.rcpl-reset-progress-option:checked' ).val();
                        if( ! progressOption ) {
                            progressOption = 'reset_course_progress';
                        }

                        let userRole = parent.find( '.rcpl-select-user-roles' ).val();
                        let targetSel = 'user/users';
                        if( userRole ) {
                            targetSel = 'user role/roles';
                        } else if( $(".rcpl-select-group-list")[0] ) {
                            targetSel = 'group/groups';
                        }

                        if( 'select_courses' == self.val() ) {
                            
                            // Update reset progress text
                            if( 'selected_users' == userOption && 'reset_quiz_progress' == progressOption  ) {
                                $( '.rcpl-note' ).html( 'Quizzes will be reset with selected '+targetSel+' and selected courses.' );
                            } else if( 'all_users' == userOption && 'reset_quiz_progress' == progressOption ) {
                                $( '.rcpl-note' ).html( 'Quizzes will be reset with all '+targetSel+' and selected courses.' );
                            } else if( 'selected_users' == userOption && 'reset_course_progress' == progressOption ) {
                                $( '.rcpl-note' ).html( 'Selected course/courses will be reset with selected '+targetSel+'.' );
                            } else if( 'all_users' == userOption && 'reset_course_progress' == progressOption ) {
                                $( '.rcpl-note' ).html( 'Selected course/courses will be reset with all '+targetSel+'.' );
                            } else {
                                $( '.rcpl-note' ).html( 'Selected course/courses will be reset with selected '+targetSel+'.' );
                            }
                            // End update reset progress text

                            $( '.rcpl-select-course-fields' ).show();

                        } else if( 'all_courses' == self.val() ) {

                            // Update reset progress text
                            if( 'selected_users' == userOption && 'reset_quiz_progress' == progressOption  ) {
                                $( '.rcpl-note' ).html( 'Quizzes will be reset with selected '+targetSel+' and all courses.' );
                            } else if( 'all_users' == userOption && 'reset_quiz_progress' == progressOption ) {
                                $( '.rcpl-note' ).html( 'Quizzes will be reset with all '+targetSel+' and all courses.' );
                            } else if( 'selected_users' == userOption && 'reset_course_progress' == progressOption ) {
                                $( '.rcpl-note' ).html( 'All the courses will be reset with selected '+targetSel+'.' );
                            } else if( 'all_users' == userOption && 'reset_course_progress' == progressOption ) {
                                $( '.rcpl-note' ).html( 'All the courses will be reset with all '+targetSel+'.' );
                            } else {
                                $( '.rcpl-note' ).html( 'All the courses will be reset with selected '+targetSel+'.' );
                            }
                            // End update reset progress text
                            
                            $( '.rcpl-select-course-fields' ).hide();
                            $( '.rcpl-select-course-fields' ).find( '#select_course' ).removeAttr( 'required' );
                            $( '.rcpl-form-content' ).css( 'height', 'unset' );
                        }
                    } );
                }
            },

            /**
             * Save group id on change select group option
             */
            onChangeOnGroup: function() {

                if( $( '.rcpl-select-group-list' ).length > 0 ) {
                    $( '.rcpl-select-group-list' ).on( 'change', function() {

                        let self = $( this );
                        let groupID = self.val();

                        let parent = self.parents( '.rcpl-form-wrap' );

                        let groupSelectOption = parent.find( '.rcpl-selected-group-option:checked' ).val();
                        if( 'selected_users' == groupSelectOption ) {
                            $( '.rcpl-save-group-ids' ).val( groupID );
                        } else if( 'all_users' == groupSelectOption ) {
                            $( '.rcpl-save-group-ids' ).val( '' );
                        } else {
                            $( '.rcpl-save-group-ids' ).val( groupID );
                        }
                    } );
                }
            },
        };

        RCPLbackEnd.init();
    });
})( jQuery );