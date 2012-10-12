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

header("Content-type: text/json");
$pr = $_POST['post_ratings'];
$pr = stripslashes($pr);

if($pr ==  ""){
    die("No input!");
}
$jsonOutput = '{"PostRatings":[';

$postRatings = json_decode($pr,true);
$index = 0;

foreach($postRatings[PostRatings] as $postRating){

	$postId = $postRating[PostId];
	$rating = $postRating[Rating];
	
	$post_rating = add_user_rating($postId,$rating);
	
	if($index == 0){
		$jsonOutput.=json_encode($post_rating);
	}
	else{
		$jsonOutput.=",".json_encode($post_rating);
	}
	$index++;
}

$jsonOutput.= "]}";

echo $jsonOutput;

function add_user_rating($postId, $rating){

	$ratings_users = get_post_meta($postId, "ratings_users", true);
	$ratings_score = get_post_meta($postId, "ratings_score", true);
	$ratings_average = get_post_meta($postId, "ratings_average", true);
	
	$ratings_users = $ratings_users + 1;
	$ratings_score = $ratings_score + $rating;
	$ratings_average = $ratings_score / $ratings_users;
	
	update_post_meta($postId, "ratings_users", $ratings_users);
	update_post_meta($postId, "ratings_score", $ratings_score);
	update_post_meta($postId, "ratings_average", $ratings_average);
	
	$post_rating = array('ratings_users' => $ratings_users, 'ratings_score' => $ratings_score, 'ratings_average' => $ratings_average);
	return $post_rating;
}

?>