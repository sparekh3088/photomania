<?php
    require 'lib/facebook.php';//Facebook Library to load
    require 'apiConfig.php';//Consists API Key & Secret
    
    //Create the facebook instance
    $facebook = new Facebook(array(
      'appId'  => $APIKEY,//Declared in apiConfig.php
      'secret' => $APISECRET,//Declared in apiConfig.php
    ));

    // Get User ID
    $user = $facebook->getUser();
    if(!isset($_SESSION['dir']))
    {
	$_SESSION['dir'] = '';
	//Get the directory where the app is running
	$dir = explode('/',$_SERVER['PHP_SELF']);
	for($i = 1;$i < (count($dir) - 1);$i++)
	    $_SESSION['dir'] .= $dir[$i].'/';
    }
    if ($user) {//redirect to your apps logout page
      $logoutUrl = $facebook->getLogoutUrl(array('next' => 'http://'.$_SERVER['HTTP_HOST'].'/'.$_SESSION['dir'].'logout.php'));
    } else {
      $loginUrl = $facebook->getLoginUrl($params);//Declared in apiConfig.php
    }
?>