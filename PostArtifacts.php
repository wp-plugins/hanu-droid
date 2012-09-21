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
header("Content-type: text/xml");

require( dirname(dirname(dirname(dirname(__FILE__)))) . '/wp-config.php' );

global $wpdb;

$host = DB_HOST;
$user = DB_USER;
$pass = DB_PASSWORD;
$database = DB_NAME;

$linkID = mysql_connect($host, $user, $pass) or die("Could not connect to host.");
mysql_select_db($database, $linkID) or die("Could not find database.");

$mod_time = $_POST[modified_time];

if($mod_time ==  ""){
    die("No input!");
}

$post_table_name = $wpdb->prefix.'posts';
$post_meta_table_name = $wpdb->prefix.'postmeta';
$comments_table_name = $wpdb->prefix.'comments';

$maxPost = get_option("HanuDroid_MaxPost");
if($maxPost == 0){
	$maxPost = 30;
}

$query = "SELECT ID, post_date, post_modified FROM $post_table_name WHERE post_modified_gmt >= '$mod_time' AND post_status = 'publish' ".
			"AND post_type = 'post' ORDER BY post_date_gmt DESC LIMIT $maxPost";
$result = mysql_query($query, $linkID) or die("Records not found.");

$xml_output = "<?xml version=\"1.0\"?>\n";

$xml_output .= "<PostArtificats>\n";

for($x = 0 ; $x < mysql_num_rows($result) ; $x++){

	$row = mysql_fetch_assoc($result);
    $id = $row['ID'];

    $xml_output .= "<PostArtifcatData \n";
    $xml_output .= 'Id="'.$id.'"'."\n";
	$xml_output .= 'PublishDate="'.$row['post_date'].'"'."\n";
	$xml_output .= 'ModifiedDate="'.$row['post_modified'].'"'."\n";

	// Post Meta Data
	$post_meta_query = "SELECT meta_value FROM $post_meta_table_name WHERE post_id = '$id' AND meta_key = 'ratings_average'";
	$post_meta_result = mysql_query($post_meta_query, $linkID);
	$xml_output .= "AverageRating=\"";
	$rating = "0";
	if($post_meta_result){
		// If we have something
		for($y = 0 ; $y < mysql_num_rows($post_meta_result) ; $y++){
			$post_meta_row = mysql_fetch_assoc($post_meta_result);
			$rating = $post_meta_row['meta_value'];
		}
	}
	
	$xml_output .= $rating.'"'."\n";

	// Post Comments Data.
	$comments_query = "SELECT comment_date FROM $comments_table_name WHERE comment_post_ID = '$id' and comment_approved = '1'
						ORDER BY comment_date_gmt desc LIMIT 1";
	$comments_result = mysql_query($comments_query, $linkID);	
	$xml_output .= "CommentDate=\"";
	for($y = 0 ; $y < mysql_num_rows($comments_result) ; $y++){
	
		$comments_data_row = mysql_fetch_assoc($comments_result);
		$xml_output .= $comments_data_row['comment_date'];
	}
	$xml_output .= '"'."\n";
	
	$xml_output .= ">\n";
	$xml_output .= "</PostArtifcatData>\n";
	
}

$xml_output .= "</PostArtificats>\n";

echo $xml_output;

?>