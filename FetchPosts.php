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

$post_ids = $_POST[post_id];

if($post_ids ==  ""){
    die("No input!");
}

$post_table_name = $wpdb->prefix.'posts';
$post_meta_table_name = $wpdb->prefix.'postmeta';
$comments_table_name = $wpdb->prefix.'comments';
$comments_meta_table_name = $wpdb->prefix.'commentsmeta';
$user_table_name = $wpdb->prefix.'users';
$terms_view_name = $wpdb->prefix.'hanu_term_data';

$query = "SELECT * FROM $post_table_name WHERE ID in ($post_ids) AND post_status = 'publish'";
$result = mysql_query($query, $linkID) or die("Records not found.");

$xml_output = "<?xml version=\"1.0\"?>\n";

$xml_output .= "<PostInfo>\n";

for($x = 0 ; $x < mysql_num_rows($result) ; $x++){
	
	$xml_output .= "<PostsInfoRow>\n";
	
	$row = mysql_fetch_assoc($result);
    $id = $row['ID'];

    $xml_output .= "<PostData \n";
    $xml_output .= 'Id="'.$id.'"'."\n";
	$xml_output .= 'PublishDate="'.$row['post_date'].'"'."\n";
	
	// Select Author Name
	$author_id = $row['post_author'];
	$user_name_query = "SELECT * FROM $user_table_name WHERE ID = $author_id";
	$user_name_result = mysql_query($user_name_query, $linkID) or die("User Data not found");
	$user_name_result_row = mysql_fetch_assoc($user_name_result);
	
    $xml_output .= 'Author="'.$user_name_result_row['display_name'].'"'."\n";
	$xml_output .= 'ModifiedDate="'.$row['post_modified'].'"'."\n";
	$xml_output .= 'Title="'.$row['post_title'].'"'."\n";
	$xml_output .= ">\n";
	$xml_output .= "</PostData>\n";

	// Content may have HTML tags !
	$post_content = "<![CDATA[".$row['post_content']."]]>";
	//$post_content = "<![CDATA[".nl2br($row['post_content'])."]]>";
	$xml_output .= '<PostContent>'.$post_content."</PostContent>"."\n";
	
	// Post Meta Data
	$post_meta_query = "SELECT * FROM $post_meta_table_name WHERE post_id = '$id'";
	$post_meta_result = mysql_query($post_meta_query, $linkID);
	$xml_output .= "<PostMetaData>\n";
	
	if($post_meta_result){
		
		for($y = 0 ; $y < mysql_num_rows($post_meta_result) ; $y++){
	
			$post_meta_row = mysql_fetch_assoc($post_meta_result);
			// We want only ratings as of now !
			if(strcmp(substr($post_meta_row['meta_key'],0,8),'ratings_') == 0){
				$xml_output .= "<PostMetaDataRow \n";
				$xml_output .= "PostId=\"".$post_meta_row['post_id'].'"'."\n";
				$xml_output .= "MetaKey=\"".$post_meta_row['meta_key'].'"'."\n";
				$xml_output .= "MetaValue=\"".$post_meta_row['meta_value'].'"'."\n";
				$xml_output .= ">\n</PostMetaDataRow>\n";
			}
		}
	}
	
	$xml_output .= "</PostMetaData>";

	// Post Comments Data.
	$comments_query = "SELECT * FROM $comments_table_name WHERE comment_post_ID = '$id' and comment_approved = '1'";
	$comments_result = mysql_query($comments_query, $linkID);
	$xml_output .= "<CommentsData>\n";
	
	for($y = 0 ; $y < mysql_num_rows($comments_result) ; $y++){
	
		$comments_data_row = mysql_fetch_assoc($comments_result);
		$xml_output .= "<CommentsDataRow \n";
		$xml_output .= "CommentId=\"".$comments_data_row['comment_ID'].'"'."\n";
		$xml_output .= "PostId=\"".$comments_data_row['comment_post_ID'].'"'."\n";
		$xml_output .= "Author=\"".$comments_data_row['comment_author'].'"'."\n";
		$xml_output .= "AuthorEmail=\"".$comments_data_row['comment_author_email'].'"'."\n";
		$xml_output .= "CommentDate=\"".$comments_data_row['comment_date'].'"'."\n";
		$comment_content = strip_tags($comments_data_row['comment_content']);
		$xml_output .= "CommentContent=\"".$comment_content.'"'."\n";
		$xml_output .= "CommentParent=\"".$comments_data_row['comment_parent'].'"'."\n";
		$xml_output .= ">\n</CommentsDataRow>\n";
	}
	$xml_output .= "</CommentsData>";
	
	// Comments Meta Data - don't know why this is required.
	
	// Terms Data
	$terms_query = "SELECT * FROM $terms_view_name WHERE object_id = $id and taxonomy IN ('category','post_tag')";
	$terms_result = mysql_query($terms_query, $linkID);
	$xml_output .= "<TermsData>\n";
	
	for($y = 0 ; $y < mysql_num_rows($terms_result) ; $y++){
	
		$term_data_row = mysql_fetch_assoc($terms_result);
		$xml_output .= "<TermsDataRow \n";
		$xml_output .= "PostId=\"".$term_data_row['object_id'].'"'."\n";
		$xml_output .= "Taxonomy=\"".$term_data_row['taxonomy'].'"'."\n";
		$xml_output .= "Name=\"".$term_data_row['name'].'"'."\n";
		$xml_output .= ">\n</TermsDataRow>\n";
	}
	$xml_output .= "</TermsData>";

	$xml_output .= "</PostsInfoRow>\n";
	
}

$xml_output .= "</PostInfo>\n";

echo $xml_output;

?>