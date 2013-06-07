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
header("Content-type: text/json");

require( dirname(dirname(dirname(dirname(__FILE__)))) . '/wp-config.php' );
global $wpdb;

$host = DB_HOST;
$user = DB_USER;
$pass = DB_PASSWORD;
$database = DB_NAME;

$linkID = mysql_connect($host, $user, $pass) or die("Could not connect to host.");
mysql_select_db($database, $linkID) or die("Could not find database.");

$post_ids = $_POST[post_id];

if($post_ids ==  ""){
    die("No input!");
}

$post_meta_table_name = $wpdb->prefix.'postmeta';

$jsonOutput = '{"PostMetaData":[';

// Post Meta Data
$post_meta_query = "SELECT * FROM $post_meta_table_name WHERE post_id in ($post_ids)";
$post_meta_result = mysql_query($post_meta_query, $linkID);
$index = 0;

if($post_meta_result){
	
	for($y = 0 ; $y < mysql_num_rows($post_meta_result) ; $y++){

		$post_meta_row = mysql_fetch_assoc($post_meta_result);
		// We want only ratings as of now !
		if(strcmp(substr($post_meta_row['meta_key'],0,8),'ratings_') == 0){
		
			$post_rating = array('PostId' => $post_meta_row['post_id'], 'MetaKey' => $post_meta_row['meta_key'], 'MetaValue' => $post_meta_row['meta_value']);
			
			if($index == 0){
				$jsonOutput.=json_encode($post_rating);
			}
			else{
				$jsonOutput.=",".json_encode($post_rating);
			}
			$index++;
		}
	}
}

$jsonOutput.= "]}";
echo $jsonOutput;

?>