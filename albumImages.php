<?php
    require 'apiAccess.php'; //load the facebook sdk and check whether user is logged in or not to the app
    
    //the requested albums details
    $album_id = $_GET['album_id'];
    $album_photo_count = $_GET['photo_count'];
    
    if ($user) {
      try {
	// Fetch the photos link and content
	$user_photos = $facebook->api("/".$album_id."?fields=photos.limit(".$album_photo_count.").fields(height,name,source,picture,width)");
	if($user_photos)
	    $user_photos = $user_photos['photos']['data'];
      } catch (FacebookApiException $e) {
	error_log($e);//write the error in the error log for review if any error occures
	$user = null;
	echo 'error';
	die();
      }
    }
    
    //Array to pass the link
    $images = array();
    
    if ($user){
	//loop through the data and create the images array to pass the json data.
	for($i = 0;$i < count($user_photos);$i++)
	{
	  $images[$i]['thumb'] = $user_photos[$i]['picture'];
	  $images[$i]['image'] = $user_photos[$i]['source'];
	  //$images[$i]['height'] = $user_photos[$i]['height'];
	  //$images[$i]['width'] = $user_photos[$i]['width'];
	  $images[$i]['title'] = $user_photos[$i]['name'];
	  $images[$i]['url'] = $images[$i]['image'];
	}
    }
    
    if($user && count($images))//check if user is logged in and is there the data in the images array then pass the json data
	echo json_encode($images);
    else if(!$user)//check if user is not logged in then destroy the session and pass the json as login to indicate that the user needs to login
    {
	session_unset();
	session_destroy();
	echo json_encode('login');
    }
    else //pass the json if the images array is empty and user is logged in
	echo json_encode('empty');
?>