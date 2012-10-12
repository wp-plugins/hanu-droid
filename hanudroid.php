<?php
/*
Plugin Name: Hanu-Droid
Plugin URI: http://hanu-droid.varunverma.org/hanu-droid-wordpress-plugin/
Description: Wordpress plugin to create Android apps from your word press blog.
Version: 1.4.1
Author: Varun Verma
Author URI: http://varunverma.org
License: GPL2
*/

register_activation_hook(__FILE__,'hanudroid_install');
register_deactivation_hook( __FILE__, 'hanudroid_uninstall' );

// create custom plugin settings menu
add_action('admin_menu', 'HanuDroid_Admin_Page');

function hanudroid_install(){

	global $wpdb;

	$host = DB_HOST;
	$user = DB_USER;
	$pass = DB_PASSWORD;
	$database = DB_NAME;

	$linkID = mysql_connect($host, $user, $pass) or die("Could not connect to host.");
	mysql_select_db($database, $linkID) or die("Could not find database.");

	$term_relationship = $wpdb->prefix.'term_relationships';
	$term_taxonomy = $wpdb->prefix.'term_taxonomy';
	$terms = $wpdb->prefix.'terms';
	$view_name = $wpdb->prefix.'hanu_term_data';

	$sql = "CREATE OR REPLACE VIEW $view_name AS SELECT a.object_id  AS object_id, b.taxonomy as taxonomy, c.name as name 
		FROM $term_relationship AS a, $term_taxonomy as b, $terms AS c 
		WHERE a.term_taxonomy_id = b.term_taxonomy_id AND b.term_id = c.term_id";

	$result = mysql_query($sql, $linkID) or die("Error while Installing, Please try again.");
	if($result){
		add_option('HanuDroid_Version', '1.1');
		add_option('HanuDroid_MaxPost', '30');
		add_option('HanuDroid_Categories','ALL');
		add_option('HanuDroid_Tags','ALL');
	}
	else{
	}
	
}

function hanudroid_uninstall(){

	global $wpdb;

	$host = DB_HOST;
	$user = DB_USER;
	$pass = DB_PASSWORD;
	$database = DB_NAME;

	$linkID = mysql_connect($host, $user, $pass) or die("Could not connect to host.");
	mysql_select_db($database, $linkID) or die("Could not find database.");

	$view_name = $wpdb->prefix.'hanu_term_data';

	$sql = "DROP VIEW $view_name";

	$result = mysql_query($sql, $linkID) or die("Error while deactivating the Plugin!");
	if($result){
		delete_option('HanuDroid_Version');
		delete_option('HanuDroid_MaxPost');
		delete_option('HanuDroid_Categories');
		delete_option('HanuDroid_Tags');
	}
	else{
	}
	
}

function HanuDroid_Admin_Page(){
	
	// Create Options Page
	add_options_page('HanuDroid Settings Page','Hanu-Droid Settings','manage_options','SendGCM','HanuDroid_SendGCM');

}

// display the admin options page
function HanuDroid_SendGCM(){
?>
<div>
<h2>Hanu-Droid Settings</h2>
Send notifications to devices about new Posts and comments:
<form action="http://hanu-droid.varunverma.org/Applications/SendGCM.php" method="post">
<input type="hidden" name="blogurl" value="<?php esc_attr_e(get_option("siteurl"));?>" />
<input name="Submit" type="submit" value="Send Notifications about new posts" />
</form>
</div>
<?php
}?>