<?php
    include 'apiAccess.php'; //load the facebook sdk and check whether user is logged in or not to the app
    
    //album details
    $album_id = $_GET['album_id'];
    $album_title = $_GET['album_title'];
    
    if ($user) {
      try {
	//Fetch the images source
	$user_photos = $facebook->api("/".$album_id."?fields=photos.fields(picture)");
	if($user_photos)
	    $user_photos = $user_photos['photos']['data'];
      } catch (FacebookApiException $e) {
	error_log($e); //write error to the error log for future review if any.
	session_unset();
	$user = null;
      }
    }
    $zip_name = ''; // variable that will store the zip name and pass to the user
    if(!$user)
    {
	//destroy the session and pass the json with message login indicating to login to the site.
	echo json_encode('login');
	die();
    }
    //Time limit to create zip 0 for unlimited time limit
    //set_time_limit (0);
    //check if found any photos
    if(count($user_photos))
    {
	//check if zip extension is loaded or not
	if(extension_loaded('zip'))
	{
	    $zip = new ZipArchive();	// Load zip library	
	    $zip_name = "ziparchieve/".$album_title.".zip";	// Zip name
	    if($zip->open($zip_name, ZIPARCHIVE::CREATE)!==TRUE){	// Opening zip file to load files
		echo json_encode('error');//pass the json indicating that the zip cannot be opened
		die();
	    }
	    //loop through the source obtained
	    for($i = 0;$i < count($user_photos);$i++)
	    {
	      $src = $user_photos[$i]['picture'];
	      $raw = file_get_contents($src);
	      //add file to the zip
	      $zip->addFromString(basename($src),$raw);
	    }
	    
	    //close the zip after it's created
	    $zip->close();
	    if(!file_exists($zip_name)){
		echo json_encode('error'); //pass the json indicating that the zip doesn't exists
		die();
	    }
	}
    }
    else
    {	//notify that the album is empty so zip could not be created
	echo json_encode('empty');
	die();
    }
    //pass the zip name as the json for the user to download it.
    echo json_encode($zip_name);
?>