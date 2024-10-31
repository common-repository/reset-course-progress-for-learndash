<?php 
/**
 * Reset course result
 */

if( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

/**
 * Class RCPL_Progress_Results
 */
class RCPL_Progress_Results extends WP_List_Table {

	/**
	 * Prepares the list of items for displaying.
	 *
	 * @abstract
	 */
	public function prepare_items() {

		$url_order_by = isset( $_GET['orderby'] ) ? $_GET['orderby'] : '';
		$url_order = isset( $_GET['order'] ) ? $_GET['order'] : '';
		$rlc_search_term = isset( $_POST['s'] ) ? $_POST['s'] : '';
		$datas = $this->rcpl_result_table_data( $url_order_by, $url_order, $rlc_search_term );
		$this->items = $datas;
		$columns = $this->get_columns();
		$hidden = $this->get_hidden_columns();
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable );
	}

	/**
	 * Display columns datas
	 *
	 * @param $url_order_by, $url_order, $search_term
	 * @return Array
	 */
	public function rcpl_result_table_data( $url_order_by = '', $url_order = '', $search_term = '' ) {

		?>
		<div class="rcpl-wrap">
			<div class="rcpl-form-wrap">

				<!-- heading -->
				<div class="rcpl-page-heading">
		            <?php _e( 'Progress Reset Results', 'reset-course-progress-for-learndash' ); ?>
		        </div>
		        <!-- /heading -->

		        <!-- result table section -->
				<section class="rcpl-data-table">
				<?php

				$data_array = [];

				$progress_arr = [ 'rcpl_reset_by_users', 'rcpl_reset_by_roles', 'rcpl_reset_by_groups' ];
				foreach( $progress_arr as $progress_action ) {

					$cron = RCPL_Functions::get_cron_schedule_data();
					if( ! $cron || ! is_array( $cron ) ) {
						continue;
					}

	                $sum = 0;
	                foreach( $cron as $key => $schedule ) {

	                    $event = isset( $schedule[$progress_action] ) ? $schedule[$progress_action] : '';
	                    if( empty( $event ) ) {
	                        continue;
	                    }

	                    $sum += count( $schedule[$progress_action] );
	                }

	                $total = get_option( $progress_action );
	                if( ! $total ) {
	                	continue;
	                }

	                $percentage = ( $sum / $total ) * 100;
	                $message = __( 'Hold on! '.round( $percentage ).'% course reset is remaining...', 'reset-course-progress-for-learndash' );

	                if( ! $percentage || 0 == $percentage ) {
	                    delete_option( $progress_action );
	                    $message = __( 'Course Reset Successfully', 'reset-course-progress-for-learndash' );
	                }

                	$data_array[] = [
						'event'				=> $progress_action,
						'progress_result'	=> $message
					];
				}

		        return $data_array;
		        ?></section>
		        <!-- /result table section -->
			</div>
		</div>
		<?php
	}

	/**
	 * Gets a list of all, hidden and sortable columns
	 */
	public function get_hidden_columns() {
		return [];
	}

	/**
	 * Gets a list of columns.
	 *
	 * @return array
	 */
	public function get_columns() {

		$columns = [
			'event'				=> __( 'Events', 'reset-course-progress-for-learndash' ),
			'progress_result'	=> __( 'Progress', 'reset-course-progress-for-learndash' ),
		];
		return $columns;
	}

	/**
	 * Return column value
	 *
	 * @param object $item
	 * @param string $column_name
	 */
	public function column_default( $item, $column_name ) {

		switch ($column_name) {
			case 'event':
			case 'progress_result':
			return $item[$column_name];
			default:
			return __( 'no columns found.', 'reset-course-progress-for-learndash' );
		}
	}

	/**
	 * Message to be displayed when there are no items
	 */
	public function no_items() {

		_e( 'No Progress Events found.', 'reset-course-progress-for-learndash' );
	}
}

/**
 * WP_list_table instance to functions everywhere
 */
function rcpl_result_table_layout() {

	$rcpl_list_table = new RCPL_Progress_Results();
	$rcpl_list_table->prepare_items();
	$rcpl_list_table->display();
}
rcpl_result_table_layout();