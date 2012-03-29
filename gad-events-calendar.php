<?php
/*
Plugin Name: External Events Calendar
Plugin URI: http://goldenapplesdesign.com/projects/upcoming-events-calendar-plugin/
Description: Adds the ability to display links of time-stamped events as a calendar.
Author: Nathaniel Taintor
Version: 0.4.0
Author URI: http://goldenapplesdesign.com
*/

global $wpdb;
define( "EXTEVTCAL_PLUGIN_DIR", path_join( WP_PLUGIN_URL, basename( dirname( __FILE__ ) ) ) );
define( "EXTEVTCAL_PLUGIN_PATH", path_join( WP_PLUGIN_DIR, basename( dirname( __FILE__ ) ) ) );
define( "EXTEVTCAL_TABLE", $wpdb->prefix . 'linkmeta' );

register_activation_hook( __FILE__, 'extevtcal_install' );

function extevtcal_install( ) {
	global $wp_version;
	if (version_compare( $wp_version, "2.9", "<" )) {
		deactivate_plugins( basename( __FILE__ ) );
		wp_die( "This plugin requires WordPress version 2.9 or higher." );
	}

	if (!term_exists( 'Events Calendar' )) wp_insert_term( 'Events Calendar', 'link_category' );

	// create linkmeta table if it doesn't exist
	global $wpdb;
	if ($wpdb->get_var( "SHOW TABLES LIKE '" . EXTEVTCAL_TABLE . "'" ) != EXTEVTCAL_TABLE) {
		$sql = "CREATE TABLE `" . EXTEVTCAL_TABLE . "` (
				`meta_id` BIGINT( 20 ) NOT NULL AUTO_INCREMENT ,
				`link_id` BIGINT( 20 ) NOT NULL ,
				`meta_key` VARCHAR( 255 ) NULL ,
				`meta_value` LONGTEXT NULL ,
				PRIMARY KEY ( `meta_id` ) ,
				UNIQUE ( `meta_id` )
				)";
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
		update_option( 'extevtcal_db_version', '0.1' );
		if (!get_option( 'extevtcal_date_formatting' )) update_option( 'extevtcal_date_formatting', 'builtin' );
		if (!get_option( 'extevtcal_link_position' )) update_option( 'extevtcal_link_position', 'title_only' );
		if (!get_option( 'extevtcal_use_css' )) update_option( 'extevtcal_use_css', true );
		if (!get_option( 'extevtcal_currentday_behavior' )) update_option( 'extevtcal_currentday_behavior', 'default' );
	}
}

/**
 * Defining some get and update meta functions.
 * These function similarly to get_post_meta and update_post_meta
 *
 * @package external-events-calendar
 * @since 0.1
 *
 * @param int	 	$link	The ID of the link to check
 * @param string	$key	The name of the key to check
 * @param string	$value	The value to update $key for on $link
 */
function get_link_meta( $link, $key ) {
	if (!$link = absint( $link )) return false;
	global $wpdb;
	$linkmeta = $wpdb->get_var( "SELECT meta_value FROM `".EXTEVTCAL_TABLE."` WHERE `link_id` = '$link' AND `meta_key` = '$key'" );
	return $linkmeta;
}

function update_link_meta( $link, $key, $value ) {
	global $wpdb;
	$exists = $wpdb->get_results( "SELECT meta_key,meta_value FROM `".EXTEVTCAL_TABLE."` WHERE `link_id` = '$link' AND `meta_key` = '$key'" );
	if (!$exists) $wpdb->insert( EXTEVTCAL_TABLE, array('link_id' => $link, 'meta_key' => $key, 'meta_value' => $value), array('%d', '%s', '%s') );
	else $wpdb->update( EXTEVTCAL_TABLE, array('meta_value' => $value), array('link_id' => $link, 'meta_key' => $key), array('%s'), array('%d', '%s') );
}

function dateformat( ) {
	if (get_option( 'extevtcal_date_formatting' ) == 'WP_setting')
		$dateformat = get_option( 'date_format' );
	elseif (get_option( 'extevtcal_date_formatting' ) == 'WP_setting_time')
		$dateformat = get_option( 'links_updated_date_format' );
	elseif (get_option( 'extevtcal_date_formatting' ) != 'builtin')
		$dateformat = get_option( 'extevtcal_date_customformat' );
	else $dateformat = false;
	return $dateformat;
}

/**
 * Sorts an array of events by date.
 * Used in admin page and widget output
 *
 * @package external-events-calendar
 * @since 0.3
 *
 * @param array		$eventslist		Array of links returned by wp_get_bookmarks or get_objects_in_term
 * @param ASC|DEC	$sortorder		Sort events current to future, or future to current
 * @param string	$showevents		Whether or not to include past events in returned array.
 * 									Possible values: false / 'upcomingonly' | true / 'allevents' | 'pastonly'
 */
function sortListings( $eventslist, $sortorder = 'DESC', $showevents = 'allevents' ) {
	$sortarray = array();
	$eventlisting = array();
	$behavior = get_option('extevtcal_currentday_behavior');
	if ( $behavior == 'today' )
		$currenttime = strtotime( 'yesterday 11:59pm' );
	elseif ( $behavior == 'thisweek' ) {
		$days = array( 'Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday' );
		$currenttime = strtotime( 'last '.$days[get_option('start_of_week')].', 12:00am' );
	} else $currenttime = time( );
	foreach ($eventslist as $link) {
		$location = get_link_meta( $link, 'extevt_location' );
		$startdate = get_link_meta( $link, 'extevt_start_date' );
		$enddate = get_link_meta( $link, 'extevt_end_date' );
		$sortnumber = strtotime( $startdate );
		if ( ( ( $showevents != 'pastonly' ) && (
				( ( $showevents == 'allevents' ) || ( $showevents === true ) || ( $sortnumber > $currenttime ) )
				|| ( ( $behavior == 'enddate' ) && !empty( $enddate ) && ( strtotime( $enddate ) > $currenttime ) ) ) )
				|| ( ( $showevents == 'pastonly') && ( $sortnumber < time() ) ) ) {
			array_push( $sortarray, $sortnumber );
			array_push( $eventlisting, array(
				'id' => $link,
				'sortnumber' => $sortnumber,
				'location' => $location,
				'startdate' => $startdate,
				'enddate' => $enddate) );
		}
	}
	if ($sortorder == 'DESC') array_multisort( $sortarray, SORT_DESC, SORT_NUMERIC, $eventlisting );
	else array_multisort( $sortarray, $eventlisting );
	return $eventlisting;
}

/**
 * Displays date listings as a range.
 * Used in admin page and widget output
 *
 * @package external-events-calendar
 * @since 0.3
 *
 * @param string			$startdate		Start date of event
 * @param string			$senddate		End date of event
 * @param bool|string		$customformat	False (use dates as entered)|Date format string to reformat dates
 */
function processDateListing( $startdate = '', $enddate = '', $customformat = false ) {
	if (!$enddate) { // single date only
		if ($customformat) $startdate = date_i18n( __( $customformat ), strtotime( $startdate ) );
		return $startdate;
	} else {
		$enddatearray = getdate( strtotime( $enddate ) );
		$startdatearray = getdate( strtotime( $startdate ) );
		$difference = array_diff( $startdatearray, $enddatearray );
		if ($customformat) {
			$startdate = date_i18n( __( $customformat ), strtotime( $startdate ) );
			$enddate = date_i18n( __( $customformat ), strtotime( $enddate ) );
		}
	}
	if (!isset( $difference['mon'] ) && !isset( $difference['year'] ) && !isset( $difference['mday'] ) &&
			( isset( $difference['hours'] ) || isset( $difference['minutes'] ) )) {
		// same day, range should reflect time only
		if (preg_match( '/(?<=[^a-zA-Z0-9])(' . $startdatearray['hours'] . $startdatearray['minutes'] . ')(?=[^a-zA-Z0-9])/', $startdate )) {
			//simplest possibility; military time
			return preg_replace( '/(?<=[^a-zA-Z0-9])(' . $difference['hours'] . $difference['minutes'] . ')(?=[^a-zA-Z0-9])/',
					'$1&ndash;' . $enddatearray['hours'] . $enddatearray['minutes'], $startdate );
		} elseif (preg_match( '/(?<=[^a-zA-Z0-9])(' . date( 'g:i', mktime( $startdatearray['hours'], $startdatearray['minutes'] ) ) . ')(?=[^a-zA-Z0-9])/', $startdate )) {
			//g:ia format
			return preg_replace( '/(?<=[^a-zA-Z0-9])(' . date( 'g:i', mktime( $startdatearray['hours'], $startdatearray['minutes'] ) ) . ')(?=[^a-zA-Z0-9])/', '$1&ndash;' . date( 'g:i', mktime( $enddatearray['hours'], $enddatearray['minutes'] ) ), $startdate );
		}
	} elseif (!isset( $difference['mon'] ) && !isset( $difference['year'] ) && isset( $difference['mday'] )) {
		// no difference in month or year; find day and append endash & enddate
		if (preg_match( '/(?<=[^a-zA-Z0-9])(' . $difference['mday'] . ')(?=[^a-zA-Z0-9])/', $startdate )) {
			return preg_replace( '/(?<=[^a-zA-Z0-9])(' . $difference['mday'] . ')(?=[^a-zA-Z0-9])/', '$1&ndash;' . $enddatearray['mday'],
				$startdate );
		}
	} elseif (!isset( $difference['year'] ) && isset( $difference['mon'] ) && isset( $difference['mday'] )) {
		// no difference in year, add end month and date
		if (preg_match( '/(?<![a-zA-Z0-9+])(' . $difference['mday'] . ')([^a-zA-Z0-9]+)(' . $difference['month'] . ')(?=[^a-zA-Z0-9])/',
			$startdate )) {
			// j F format:
			return preg_replace( '/(?<![a-zA-Z0-9+])(' . $difference['mday'] . ')([^a-zA-Z0-9]+)(' . $difference['month'] . ')(?=[^a-zA-Z0-9])/',
					'$1$2$3&ndash;' . $enddatearray['mday'] . '$2' . $enddatearray['month'], $startdate );
		} elseif (preg_match( '/(?<![a-zA-Z0-9+])(' . $difference['month'] . ')([^a-zA-Z0-9+])(' . $difference['mday'] . ')(?=[^a-zA-Z0-9])/',
			$startdate )) {
			// F j format:
			return preg_replace( '/(?<![a-zA-Z0-9+])(' . $difference['month'] . ')([^a-zA-Z0-9+])(' . $difference['mday'] . ')(?=[^a-zA-Z0-9])/',
					'$1$2$3&ndash;' . $enddatearray['month'] . ' ' . $enddatearray['mday'], $startdate );
		} elseif (preg_match( '/(?<![a-zA-Z0-9+])(' . $difference['mday'] . ')([^a-zA-Z0-9]+)(' . substr( $difference['month'], 0, 3 ) .
				')([^a-zA-Z0-9]+)/', $startdate )) {
			// j M format:
			return preg_replace( '/(?<![a-zA-Z0-9+])(' . $difference['mday'] . ')([^a-zA-Z0-9]+)(' . substr( $difference['month'], 0, 3 ) .
					')(?=[^a-zA-Z0-9])/', '$1$2$3&ndash;' . $enddatearray['mday'] . ' ' . substr( $enddatearray['month'], 0, 3 ),
				$startdate );
		} elseif (preg_match( '/(?<![a-zA-Z0-9+])(' . substr( $difference['month'], 0, 3 ) . ')([^a-zA-Z0-9]+)(' .
				$difference['mday'] . ')(?=[^a-zA-Z0-9])/', $startdate )) {
			// M j format:
			return preg_replace( '/(?<![a-zA-Z0-9+])(' . substr( $difference['month'], 0, 3 ) . ')([^a-zA-Z0-9]+)(' . $difference['mday'] .
					')(?=[^a-zA-Z0-9])/', '$1$2$3&ndash;' . substr( $enddatearray['month'], 0, 3 ) . ' ' . $enddatearray['mday'],
				$startdate );
		}
	}
	// if all fields are different, or we can't parse date format
	return $startdate . ' &ndash; ' . $enddate;
}

add_action( 'admin_init', 'add_extevtcal_metabox' );
add_action( 'edit_link', 'save_extevtcal_metabox' );
add_action( 'add_link', 'save_extevtcal_metabox' );
add_action( 'admin_menu', 'add_extevtcal_submenu_pages' );
add_action( 'widgets_init', 'extevtcal_register_widgets' );
add_action( 'admin_head-link-add.php', 'check_extevtcal_category' );

function check_extevtcal_category() {
	if (!isset($_GET['type']) || ($_GET['type'] != 'external-event'))
		return;
	?>
	<script type="text/javascript">
		jQuery(document).ready(function() {
			jQuery("#in-link-category-<?php echo term_exists('Events Calendar'); ?>").attr('checked',"checked");
		});
	</script>
	<?php
}

function add_extevtcal_submenu_pages( ) {
	add_links_page( __( 'Manage Event Listings', 'extevtcal_plugin' ), __( 'Events Calendar', 'extevtcal_plugin' ), 'manage_links', 'extevtcal_manage_events', 'extevtcal_manage_events' );
	add_options_page( __( 'Events Calendar Options', 'extevtcal_plugin' ), __( 'Events Calendar', 'extevtcal_plugin' ), 'manage_options', 'extevtcal_settings_page', 'extevtcal_settings_page' );
	add_action( 'admin_init', 'register_extevtcal_settings' );
}

function add_extevtcal_metabox( ) {
	add_meta_box( 'extevtcal_metabox', __( 'Event Information', 'extevtcal_plugin' ), 'extevtcal_metabox', 'link', 'normal', 'high' );
}

function register_extevtcal_settings( ) {
	register_setting( 'extevtcal-settings', 'extevtcal_date_formatting' );
	register_setting( 'extevtcal-settings', 'extevtcal_date_customformat' );
	register_setting( 'extevtcal-settings', 'extevtcal_link_position' );
	register_setting( 'extevtcal-settings', 'extevtcal_use_css' );
	register_setting( 'extevtcal-settings', 'extevtcal_currentday_behavior' );
}

function extevtcal_settings_page( ) {
	include( 'admin.options.php');
}

function extevtcal_manage_events( ) {
	echo '<div class="wrap"><div id="icon-link-manager" class="icon32"></div><h2>' . __( 'Manage Event Listings', 'extevtcal_plugin' );
	echo ' <a class="button add-new-h2" href="link-add.php?type=external-event">' . __( 'Add new', 'extevtcal_plugin' ) . '</a></h2>';
	include( EXTEVTCAL_PLUGIN_PATH . '/pagination.class.php' );
	$ext_events = get_objects_in_term( term_exists( 'Events Calendar' ), 'link_category' );
	$items = count( $ext_events );
	$eventslist = sortListings( $ext_events );
	$page = isset( $_GET['page'] ) ? $_GET['page'] : 1;
	if ($items > 0) {
		$p = new pagination;
		$p->items( $items );
		$p->limit( 20 ); // Limit entries per page
		$p->target( "link-manager.php?page=extevtcal_manage_events" );
		$p->currentPage( $page ); // Gets and validates the current page
		$p->calculate( ); // Calculates what to show
		$p->parameterName( 'paging' );
		$p->adjacents( 1 ); //No. of page away from the current page

		if (!isset( $_GET['paging'] )) {
			$p->page = 1;
		} else {
			$p->page = $_GET['paging'];
		}

		//Array slice for paging
		$eventslist = array_slice( $eventslist, ( $p->page - 1 ) * $p->limit, $p->limit );
		?>
		<div class="tablenav">
			<div class='tablenav-pages'>
			<?php echo $p->show( );  // Echo out the list of paging. ?>
			</div>
		</div><?php

	} else {
		_e( "No Event Listings Found", 'extevtcal_plugin' );
	} ?>
	<p>
		<em><?php _e( 'Please note: if you add a link for a new event, you will still have to set the category to "Events Calendar" in order for it to show up in this list.', 'extevtcal_plugin' ); ?></em>
	</p>
	<table class="widefat">
	<?php $dateformat = dateformat( );
	echo '
    	<thead><tr><th scope="col">' . __( 'Event Name', 'extevtcal_plugin' ) . '</th><th scope="col">' . __( 'Date', 'extevtcal_plugin' ) . '</th><th scope="col">' . __( 'Link', 'extevtcal_plugin' ) . '</th><th scope="col">' . __( 'Location / Description', 'extevtcal_plugin' ) . '</th></tr></thead><tbody>';
	foreach ($eventslist as $event) {
		$link = get_bookmark( $event['id'] );
		echo '<tr><td ><strong><a href="'.get_edit_bookmark_link( $event['id'] ). '">';
		echo apply_filters( 'the_title', stripslashes( $link->link_name ) ) . '</a></strong>';
		echo '<div class="row-actions"><span class="edit"><a href="link.php?action=edit&link_id=' . $event['id'] . '">Edit</a> | </span>';
		echo '<span class="trash"><a class="submitdelete" href="' . wp_nonce_url( "link.php?action=delete&amp;link_id=" . $event['id'], 'delete-bookmark_' . $event['id'] );
		echo '" onclick="if ( confirm(\'' . esc_js( sprintf( __( "You are about to delete this link '%s'\n  'Cancel' to stop, 'OK' to delete." ), $link->link_name ) ) . '\') ) { return true;}return false;">' . __( 'Delete' ) . '</a></span></div></td>';
		echo '<td>' . processDateListing( $event['startdate'], $event['enddate'], $dateformat ) . '</td>';
		echo '<td><a href="' . $link->link_url . '" target="_blank">' . str_replace( 'http://', '', $link->link_url ) . '</a></td>';
		echo '<td>' . $event['location'] . '<br><em>' . apply_filters( 'the_excerpt', stripslashes( $link->link_description ) ) . '</em></td></tr>';
	}
	echo '</tbody>
    	<tfoot><tr><th scope="col">' . __( 'Event Name', 'extevtcal_plugin' ) . '</th><th scope="col">' . __( 'Date', 'extevtcal_plugin' ) . '</th><th scope="col">' . __( 'Link', 'extevtcal_plugin' ) . '</th><th scope="col">' . __( 'Location / Description', 'extevtcal_plugin' ) . '</th></tr></tfoot>'; ?>
	</table>
	</div>
	<?php

}


function extevtcal_metabox( ) {
	$link = (isset( $_GET['link_id'])) ? $_GET['link_id'] : false;
	$location = get_link_meta( $link, 'extevt_location' );
	$startdate = get_link_meta( $link, 'extevt_start_date' );
	$enddate = get_link_meta( $link, 'extevt_end_date' );
	echo '<table class="form-table"><tbody>';
	echo '<tr class="form-field"><th scope="row">' . __( 'Location', 'extevtcal_plugin' ) . '</th> ';
	echo '<td><input type="text" name="extevtcal_location" value="' . $location . '" size="80" /></td></tr>';
	echo '<tr class="form-field"><th scope="row">' . __( 'Start Date', 'extevtcal_plugin' ) . '</th> ';
	echo '<td><input type="text" name="extevtcal_startdate" value="' . $startdate . '" size="80" /></td></tr>';
	echo '<tr class="form-field"><th scope="row">' . __( 'End Date (optional)', 'extevtcal_plugin' ) . '</th> ';
	echo '<td><input type="text" name="extevtcal_enddate" value="' . $enddate . '" size="80" /></td></tr></tbody></table>';
	echo '<p><em>' . __( 'Note: if you are having problems with your events displaying properly, enter dates in a simple format like MM/DD/YYYY.', 'extevtcal_plugin' ) . '</em></p>';

}

function save_extevtcal_metabox( $link ) {
	update_link_meta( $link, 'extevt_location', $_POST['extevtcal_location'] );
	update_link_meta( $link, 'extevt_start_date', $_POST['extevtcal_startdate'] );
	update_link_meta( $link, 'extevt_end_date', $_POST['extevtcal_enddate'] );
}

// a very minimal stylesheet for widget - either modify this stylesheet or just delete it and define these styles in your own css
add_action( 'wp_print_styles', 'extevtcal_load_stylesheet' );

function extevtcal_load_stylesheet( ) {
	if (get_option( 'extevtcal_use_css' ) == true) {
		// check first in theme directory, then in plugins/gad-events-custom/.
		// If stylesheet not found in either of those locations, include the one in this folder.
		if ( file_exists( get_stylesheet_directory() . '/gad-events-calendar.css' ) )
			$custom_css_file = get_stylesheet_directory_uri() . '/gad-events-calendar.css';
		elseif ( file_exists( WP_PLUGIN_DIR . '/gad-events-custom/gad-events-calendar.css' ))
			$custom_css_file = WP_PLUGIN_URL . '/gad-events-custom/gad-events-calendar.css';
		else
			$custom_css_file = EXTEVTCAL_PLUGIN_DIR . '/gad-events-calendar.css';
		wp_enqueue_style( 'gad-events-calendar', $custom_css_file );
	}
}

//register shortcode to display events on pages/posts
add_shortcode( 'eventslisting', 'eventslisting' );

function eventslisting( $atts ) {
	if ( isset( $atts['show_events'] ) )
		$atts['show_past_events'] = $atts['show_events'];
	$instance = shortcode_atts( array(
		'display_title' => false,
		'link_category' => term_exists( 'Events Calendar' ),
		'show_past_events' => 'upcomingonly',
		'show_descriptions' => true,
		'show_images' => false,
		'orderby' => 'ASC',
		'limit' => false), $atts );
	$category_id = $instance['link_category'];
	$eventslist = get_objects_in_term( $category_id, 'link_category' );
	$eventlisting = sortListings( $eventslist, $instance['orderby'], $instance['show_past_events'] );
	$tmp = '';
	if ($instance['limit']) $eventlisting = array_slice( $eventlisting, 0, $instance['limit'] );
	$link_position = get_option( 'extevtcal_link_position' );
	$tmp .= '<div class="div_extevtcal_div">';
	if (!empty( $instance['display_title'] )) $tmp .= '<h2>' . apply_filters( 'the_title', $instance['display_title'] ) . '</h2>';
	$tmp .= extevtcal_showlist( $eventlisting, $instance, $link_position, false );
	$tmp .= '</div>';
	return $tmp;
}

function extevtcal_showlist( $eventlisting, $instance, $link_position, $echo = true ) {
	$tmp = '<ul>';
	foreach ($eventlisting as $event) {
		$link = get_bookmark( $event['id'] );
		$link_before_title = array('<a href="' . $link->link_url . '" rel="' . $link->link_rel . '" target="' . $link->link_target . '">', '</a>');
		$link_before_li = array('', '');
		if ($link_position == 'entire_li') {
			$link_before_li = $link_before_title;
			$link_before_title = array('', '');
		}
		$tmp .= $link_before_li[0] . '<li>';
		if ($instance['show_images'] && $link->link_image) $tmp .= '<img src="' . $link->link_image . '" class="link-image" >';
		$tmp .= '<h4>' . $link_before_title[0] . $link->link_name . $link_before_title[1] . '</h4>';
		if ($event['location']) $tmp .= '<span class="event-location">' . $event['location'] . '</span>';
		if ($instance['show_descriptions'] && $link->link_description) {
			$tmp .= '<span class="event-description">' . $link->link_description . '</span>';
		}
		$tmp .= '<span class="event-date">' . processDateListing( $event['startdate'], $event['enddate'], dateformat( ) ) . '</span></li>' . $link_before_li[1];
	}
	$tmp .= '</ul>';
	if ($echo) echo $tmp;
	else return $tmp;
}

//register the widget form and widget display
function extevtcal_register_widgets( ) {
	register_widget( 'extevtcal_widget' );
}

class extevtcal_widget extends WP_Widget {

	function extevtcal_widget( ) {
		$widget_ops = array('classname' => 'extevtcal_widget',
			'description' => __( 'Widget to display upcoming events from the "Upcoming Events" link category', 'extevtcal_plugin' ));
		$this->WP_Widget( 'extevtcal_widget', __( 'External Events Calendar', 'extevtcal_plugin' ) );
	}

	function form( $instance ) {
		$defaults = array('display_title' => __( 'Upcoming Events', 'extevtcal_plugin' ),
			'link_category' => term_exists( 'Events Calendar' ),
			'show_past_events' => "upcoming",
			'show_descriptions' => true,
			'show_images' => false,
			'orderby' => 'ASC',
			'limit' => false );
		$instance = wp_parse_args( (array) $instance, $defaults );
		$displaytitle = strip_tags( $instance['display_title'] );
		$showpastevents = strip_tags( $instance['show_past_events'] );
		$showdescriptions = strip_tags( $instance['show_descriptions'] );
		$showimages = strip_tags( $instance['show_images'] );
		$orderby = strip_tags( $instance['orderby'] );
		$limit = strip_tags( $instance['limit'] );
		?>
		<p><?php _e( 'Title', 'extevtcal_plugin' ) ?>:
			<input class="widefat"
				   name="<?php echo $this->get_field_name( 'display_title' ); ?>"
				   type="text"
				   value="<?php echo esc_attr( $displaytitle ); ?>"/></p>
		<h4><?php _e( 'Listings to Show', 'extevtcal_plugin' ); ?>:</h4>
		<p><input class="radio"
				  name="<?php echo $this->get_field_name( 'show_past_events' ); ?>"
				  type="radio"
				  value="upcomingonly" <?php checked( empty( $showpastevents ) || ( $showpastevents == 'upcomingonly' ) ); ?>  /> <?php _e( 'Upcoming Only (default)', 'extevtcal_plugin' ) ?>
			<br/>
			<input class="radio"
				   name="<?php echo $this->get_field_name( 'show_past_events' ); ?>"
				   type="radio"
				   value="allevents" <?php checked( ( $showpastevents == 'allevents' ) || ( 1 === $showpastevents ), true ); ?>  /> <?php _e( 'All Events', 'extevtcal_plugin' ) ?>
			<br/>
			<input class="radio"
				   name="<?php echo $this->get_field_name( 'show_past_events' ); ?>"
				   type="radio"
				   value="pastonly" <?php checked( $showpastevents == 'pastonly' ); ?>  /> <?php _e( 'Past Events Only', 'extevtcal_plugin' ) ?>
		</p>
		<p><input class="checkbox"
				  name="<?php echo $this->get_field_name( 'show_descriptions' ); ?>" value="1"
				  type="checkbox" <?php checked( $showdescriptions, 1 ); ?>  /> <?php _e( 'Show Descriptions?', 'extevtcal_plugin' ) ?>
		</p>
		<p><input class="checkbox"
				  name="<?php echo $this->get_field_name( 'show_images' ); ?>" value="1"
				  type="checkbox" <?php checked( $showimages, 1 ); ?>  /> <?php _e( 'Show Images?', 'extevtcal_plugin' ) ?>
		</p>
		<h4><?php _e( 'Order Events by:', 'extevtcal_plugin' ) ?></h4>
		<p><input class="radio"
				  name="<?php echo $this->get_field_name( 'orderby' ); ?>"
				  type="radio"
				  value="ASC" <?php checked( $orderby != 'DEC', true ); ?>  /><?php _e( 'Current to future (default)', 'extevtcal_plugin' ); ?>
			<br/>
			<input class="radio"
				   name="<?php echo $this->get_field_name( 'orderby' ); ?>"
				   type="radio"
				   value="DESC" <?php checked( ($orderby == 'DESC') || ($orderby == 'DEC') ); ?>  /><?php _e( 'Future to current', 'extevtcal_plugin' ); ?>
		</p>
		<p><?php _e( 'Max Number of listings to show', 'extevtcal_plugin' ) ?>:
			<input class="widefat"
				   name="<?php echo $this->get_field_name( 'limit' ); ?>"
				   type="text"
				   value="<?php echo esc_attr( $limit ); ?>"/>
		</p>
		<?php

	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['display_title'] = strip_tags( $new_instance['display_title'] );
		$instance['show_past_events'] = strip_tags( $new_instance['show_past_events'] );
		$instance['show_descriptions'] = strip_tags( $new_instance['show_descriptions'] );
		$instance['show_images'] = strip_tags( $new_instance['show_images'] );
		$instance['orderby'] = strip_tags( $new_instance['orderby'] );
		$instance['limit'] = strip_tags( $new_instance['limit'] );
		return $instance;
	}

	function widget( $args, $instance ) {
		extract( $args );
		$title = apply_filters( 'widget_title', $instance['display_title'] );
		$category_id = term_exists( 'Events Calendar' );
		$eventslist = get_objects_in_term( $category_id, 'link_category' );
		$eventlisting = sortListings( $eventslist, $instance['orderby'], $instance['show_past_events'] );
		$link_position = get_option( 'extevtcal_link_position' );
		if ($instance['limit']) $eventlisting = array_slice( $eventlisting, 0, $instance['limit'] );
		if ($eventlisting) {
			echo $before_widget . $before_title . $title . $after_title;
			extevtcal_showlist( $eventlisting, $instance, $link_position );
			echo $after_widget;
		}
	}


}


?>