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
$postData = $_POST['post_data'];
$postData = stripslashes($postData);
if($postData ==  ""){
    die("No input!");
}

//echo "<br> ** Real Json **" . $postData;
$post_data = json_decode($postData,true);

$post_title = $post_data[title];
$post_content = $post_data[content];

$name = $post_data[name];
$user = $post_data[user];
$pwd = $post_data[pwd];

$post_title = stripslashes($post_title);
$post_content = stripslashes($post_content);
$name = stripslashes($name);

if(strcmp($user,"") == 0){
	$post_content .= "\n\n\n Name = " . $name . "\n EMail = " . $post_data[email];
	$user = 'admin';
	$pwd = 'mastram3136';
}

$title = htmlentities( $post_title, ENT_NOQUOTES, 'UTF-8' );
$content = array(
    'post_type' => 'post',
    'post_status' => 'pending',
    'post_title' => $title,
    'post_content' => $post_content,
    'comment_status' => 'closed',
);

$params = array( 0, $user, $pwd, $content );

$request = xmlrpc_encode_request('wp.newPost', $params);  
$ch = curl_init();  

$site_url = get_option("siteurl") . "/xmlrpc.php";

curl_setopt($ch, CURLOPT_POSTFIELDS, $request);  
curl_setopt($ch, CURLOPT_URL, $site_url);  
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);  
curl_setopt($ch, CURLOPT_TIMEOUT, 1);  
$results = curl_exec($ch);  
curl_close($ch);  

$xml = new SimpleXMLElement($results);

$post_id = strval($xml->params[0]->param[0]->value[0]->string[0]);
$output = array('post_id' => $post_id);
echo json_encode($output);

?>