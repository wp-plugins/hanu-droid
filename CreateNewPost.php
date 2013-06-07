<?php
/*  Copyright 2012  Varun Verma  (email : varunverma@varunverma.org)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

require( dirname(dirname(dirname(dirname(__FILE__)))) . '/wp-config.php' );
require( dirname(dirname(dirname(dirname(__FILE__)))) . '/EMailFunctions.php' );

global $wpdb;

$host = DB_HOST;
$user = DB_USER;
$pass = DB_PASSWORD;
$database = DB_NAME;

$linkID = mysql_connect($host, $user, $pass) or die("Could not connect to host.");
mysql_select_db($database, $linkID) or die("Could not find database.");

$post_data = $_POST['post_data'];
$post_data = stripslashes($post_data);

if($post_data ==  ""){
    die("No input!");
}

$post_data = json_decode($post_data,true);
$post_title = $post_data[title];
$post_content = $post_data[content];

$user = $post_data[user];

$name = $post_data[name];
$email = $post_data[email];
$reg_id = $post_data[regid];

// Safety first ... 
$post_title = mysql_real_escape_string($post_title);
$post_content = mysql_real_escape_string($post_content);
$name = mysql_real_escape_string($name);
$reg_id = mysql_real_escape_string($reg_id);

// Create a new entry in Temp Post Table for moderation
$temp_table = $wpdb->prefix.'post_temp';

$post_key =  uniqid();

if($post_title == ""){

	// Something went wrong !!
	createDBConnection();
	$admin = get_admin_info();
	
	send_email_by_gmail($admin['email'], $admin['name'], "Error in joke upload", $_POST['post_data']);

}
else{

	$sql = "INSERT INTO $temp_table (PostKey, Title, Content, user_name, name, email, RegId) VALUES
			('$post_key','$post_title','$post_content','$user','$name','$email','$reg_id')";

	if (!mysql_query($sql,$linkID)) {
		die('Error: ' . mysql_error());
	}
	else{
		$post_id = mysql_insert_id($linkID);
		$output = array('post_id' => $post_id);
		echo json_encode($output);
	
		// After successfull insert in temp table, notify the Admin by email.
		createDBConnection();
		notify_admin_about_new_post($post_id);
	}
}

?>