(function( $ ) { 'use strict';
    $( document ).ready( function() {
        var RCPLbackEnd = {
            init: function() {
                this.addExternalSelectBox();
                this.liveSearchUsersList();
                this.liveSearchCourseList();
                this.liveSearchLessonList();
                this.liveSearchTopicList();
                this.liveSearchQuizList();
                this.liveSearchGroups();
            },

            /**
             * Live searh groups
             */
            liveSearchGroups: function() {

                $( '.rcpl-select-group-list, .rcpl-exclude-group-list' ).select2({
                    placeholder: "Search Group",
                    allowClear: true,
                    ajax: {
                            url: ajaxurl,
                            dataType: 'json',
                            delay: 250,
                            data: function (params) {
                                return {
                                    q: params.term,
                                    action: 'rcpl_search_groups',
                                    user_id: $( '.rcpl-select-group-field' ).attr( 'data-user_id' )
                                };
                            },
                            processResults: function( data ) {
                            var options = [];
                            if ( data ) {
                        
                                $.each( data, function( index, text ) { 
                                    options.push( { id: text[0], text: text[1]  } );
                                });
                            
                            }
                            return {
                                results: options
                            };
                        },
                        cache: true
                    },
                    minimumInputLength: 2
                });
            },

            /**
             * Live search Quizzes list
             */
            liveSearchQuizList: function() {

                $( '.rcpl-select-quiz-list' ).select2({
                    placeholder: "Search Quizzes",
                    allowClear: true,
                    ajax: {
                            url: ajaxurl,
                            dataType: 'json',
                            delay: 250,
                            data: function (params) {
                                return {
                                    q: params.term,
                                    action: 'rcpl_search_quizzes',
                                    'course_ids' : $( '.rcpl-select-course-list' ).val(),
                                    'lesson_ids' : $( '.rcpl-select-lesson-list' ).val(),
                                    'topic_ids'  : $( '.rcpl-select-topic-list' ).val()
                                };
                            },
                            processResults: function( data ) {
                            var options = [];
                            if ( data ) {
                        
                                $.each( data, function( index, text ) { 
                                    options.push( { id: text[0], text: text[1]  } );
                                });
                            
                            }
                            return {
                                results: options
                            };
                        },
                        cache: true
                    },
                    minimumInputLength: 2
                });
            },

            /**
             * Live search Topics list
             */
            liveSearchTopicList: function() {

                $( '.rcpl-select-topic-list' ).select2({
                    placeholder: "Search Topics",
                    allowClear: true,
                    ajax: {
                            url: ajaxurl,
                            dataType: 'json',
                            delay: 250,
                            data: function (params) {
                                return {
                                    q: params.term,
                                    action: 'rcpl_search_topic',
                                    'course_ids' : $( '.rcpl-select-course-list' ).val(),
                                    'lesson_ids' : $( '.rcpl-select-lesson-list' ).val()
                                };
                            },
                            processResults: function( data ) {
                            var options = [];
                            if ( data ) {
                        
                                $.each( data, function( index, text ) { 
                                    options.push( { id: text[0], text: text[1]  } );
                                });
                            
                            }
                            return {
                                results: options
                            };
                        },
                        cache: true
                    },
                    minimumInputLength: 2
                });
            },

            /**
             * Live search lesson list
             */
            liveSearchLessonList: function() {

                $( '.rcpl-select-lesson-list' ).select2({
                    placeholder: "Search Lessons",
                    allowClear: true,
                    ajax: {
                            url: ajaxurl,
                            dataType: 'json',
                            delay: 250,
                            data: function (params) {
                                return {
                                    q: params.term,
                                    action: 'rcpl_search_lessons',
                                    'course_ids' : $( '.rcpl-select-course-list' ).val()
                                };
                            },
                            processResults: function( data ) {
                            var options = [];
                            if ( data ) {
                        
                                $.each( data, function( index, text ) { 
                                    options.push( { id: text[0], text: text[1]  } );
                                });
                            
                            }
                            return {
                                results: options
                            };
                        },
                        cache: true
                    },
                    minimumInputLength: 2
                });
            },

            /**
             * Live search course list
             */
            liveSearchCourseList: function() {

                $( '.rcpl-select-course-list' ).select2({
                    placeholder: "Search Courses",
                    allowClear: true,
                    ajax: {
                            url: ajaxurl,
                            dataType: 'json',
                            delay: 250,
                            data: function (params) {
                                return {
                                    q: params.term,
                                    action: 'rcpl_search_courses',
                                    group_ids : $( '.rcpl-save-group-ids' ).val()
                                };
                            },
                            processResults: function( data ) {
                            var options = [];
                            if ( data ) {
                        
                                $.each( data, function( index, text ) { 
                                    options.push( { id: text[0], text: text[1]  } );
                                });
                            
                            }
                            return {
                                results: options
                            };
                        },
                        cache: true
                    },
                    minimumInputLength: 2
                });
            },

            /**
             * Live search user list
             */
            liveSearchUsersList: function() {

                $('.rcpl-select-user-list, .rcpl-exclude-users-list').select2({
                    placeholder: "Search Users",
                    allowClear: true,
                    ajax: {
                            url: ajaxurl,
                            dataType: 'json',
                            delay: 250,
                            data: function (params) {
                                return {
                                    q: params.term,
                                    action: 'rcpl_search_users'
                                };
                            },
                            processResults: function( data ) {
                            var options = [];
                            if ( data ) {
                        
                                $.each( data, function( index, text ) { 
                                    options.push( { id: text[0], text: text[1]  } );
                                });
                            
                            }
                            return {
                                results: options
                            };
                        },
                        cache: true
                    },
                    minimumInputLength: 2
                });
            },

            /**
             * Add select2 Features
             */
            addExternalSelectBox: function() {

                $( '.rcpl-wrapper #select_role' ).select2({
                    placeholder: 'Select User Roles',
                    allowClear: true
                });
            },
        };

        RCPLbackEnd.init();
    });
})( jQuery );