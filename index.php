<?php
    include 'apiAccess.php'; //load the facebook sdk and check whether user is logged in or not to the app
    if ($user) {
      try {
	// Proceed knowing you have a logged in user who's authenticated.
	$user_albums = $facebook->api('/me/albums');
	$user_profile = $facebook->api('/me');
      } catch (FacebookApiException $e) {
	error_log($e);
	$user = null;
      }
    }
?>

<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Photo Mania</title>
        <!--link href="css/bootstrap-responsive.css" rel="stylesheet" /-->
        <link href="css/bootstrap.css" rel="stylesheet" />
        <!--link rel="stylesheet" href="css/dark.css" type="text/css" media="screen" />
	<link rel="stylesheet" href="css/nivo-slider.css" type="text/css" media="screen" /-->
	<!--<link rel="stylesheet" href="css/slimbox2.css" type="text/css" media="screen" />-->
	<link rel="stylesheet" href="css/style.css" />
    </head>
    <body style="background-color: white" id='docBody'>
        <div class="navbar navbar-form navbar-fixed-top">
            <div class="navbar-inner">
                <div class="container-fluid">
                    <a class="btn btn-navbar" data-target=".nav-collapse" data-toggle="collapse">
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </a>
                    <a class="brand" href="/">
                        Photo Mania
                    </a>
                    <?php
			if($user)
			{
		    ?>
			    <div class="nav-collapse collapse">
				<ul class="nav pull-right">
				    <li>
				      <a target='_blank' class='navbar-link' href="<?php echo $user_profile['link']?>"><img class='profileImage' src="https://graph.facebook.com/<?php echo $user; ?>/picture" title='<?php echo $user_profile['first_name']." ".$user_profile['last_name'];?>'>&nbsp;<?php echo $user_profile['first_name']." ".$user_profile['last_name'];?></a>
				    </li>
				    <li>
				      <a class="navbar-link" href="<?php echo $logoutUrl;?>">Logout</a>
				    </li>
				</ul>
			    </div>
		    <?php
			}
		    ?>
                </div>
            </div>
        </div>
        <div class="container-fluid well mainBody">
            <div class="row-fluid">
		<div class="span12 links hide">
		    
		</div>
                <div class="coverBody span12">
                    <?php 
			if ($user):
			if(count($user_albums['data']))
			//loop through the albums obtained
			for($i = 0;$i < count($user_albums['data']);$i++)
			{
			  ?>
			  <div class='coverPhoto'>
			      <div class='viewAlbum'>
				  <img data_title='<?php echo $user_albums['data'][$i]['name']?>' id='<?php echo $user_albums['data'][$i]['id'];?>' data_limit='<?php echo $user_albums['data'][$i]['count']?>' data_id='<?php echo $user_albums['data'][$i]['id'];?>' class="albumCover" src="https://graph.facebook.com/<?php echo $user_albums['data'][$i]['id'];?>/picture?type=album&access_token=<?php echo $facebook->getAccessToken();?>" />
			      </div>
			      <label class='coverTitle'>
				  <a href='#' data_title='<?php echo $user_albums['data'][$i]['name']?>' data_limit='<?php echo $user_albums['data'][$i]['count']?>' data_id='<?php echo $user_albums['data'][$i]['id'];?>' class='viewAlbum' id='<?php echo $user_albums['data'][$i]['id'];?>'>
				      <?php echo $user_albums['data'][$i]['name'];?>
				  </a>
			      </label>
			      <a class='archiveButton archiveButton-primary archiveButton-large btn-block downloadAlbum' href='#' data_title='<?php echo $user_albums['data'][$i]['name']?>' data_id='<?php echo $user_albums['data'][$i]['id'];?>'><label class='archiveBackground'>Archieve</label></a>
			  </div>
			  <?php
			}
			else{
			    ?>
				<div class='emptyAlbum'>
				    No albums to display.
				</div>
			    <?php
			}
		    ?>
		  <?php else: ?>
		    <strong><em>Click <a href="<?php echo $loginUrl;?>" >here</a> to login to facebook</em></strong>
		  <?php endif ?>
                </div>
            </div>
        </div>
	<div id="imageSlider" class='modal hide fade'>
	</div>
        <div id="process_gif" class="modal hide fade modalClass" style="display:none;">
	  <div class="modal-body" align="center">
	    <img src="img/loading1.gif" style="margin-top:10px;">
	  </div>
	</div>
    </body>
    <script type='text/javascript' language='javascript' src='http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js' ></script>
    <script type='text/javascript' language='javascript' src="js/bootstrap.min.js" ></script>
    <script type='text/javascript' language='javascript' src="js/jquery.touchwipe.min.js" ></script>
    <!--script type="text/javascript" src="js/galleria-1.2.9.min.js"></script-->
    <script type='text/javascript' language='javascript'>
	$(document).ready(function(){
	    //add the click event to the album cover photo and title to make the call to the file to get the album images
	    $('.albumCover').click(function(){
		$('#process_gif').modal('show');
		//call the albumImages to get the json that has the path to the images contained in the album with album_id and the total photos there in the album
		$.get('albumImages.php?album_id='+$(this).attr('data_id')+'&photo_count='+$(this).attr('data_limit'))
		.complete(function(response){
		    var data = JSON.parse(response.responseText);
		    if(data == "login")
			alert('Please login ot facebook.');
		    else if(data == 'empty')
			alert('The album is empty.');
		    else
		    {
			if(data.length > 0)
			{
			    var caption = '';
			    $('#slideImage').remove();
			    $('#imageSlider').html('<div id="slideImage" class="carousel slide"><ol class="carousel-indicators"></ol><div class="carousel-inner"></div><a class="left carousel-control" href="#slideImage" data-slide="prev">‹</a><a class="right carousel-control" href="#slideImage" data-slide="next">›</a></div>');
			    //loop through the json obtained to add images to the slider
			    for(i = 0;i < data.length;i++)
			    {
				caption = '';
				if(data[i]['title'] != null && data[i]['title'] != '')//provide caption if exists.
				    caption = '<div class="carousel-caption"><p>'+data[i]['title']+'</p></div>';
				$('#slideImage >.carousel-indicators').append('<li class="'+((i==0)?'active':'')+'" data-target="#slideImage" data-slide-to="'+i+'"></li>');
				$('#slideImage >.carousel-inner').append('<div class="item '+((i==0)?'active':'')+'"><img title="" src="'+data[i]['image']+'" alt="">'+caption+'</div>');
			    }
			    $('#process_gif').modal('hide');
			    $('#imageSlider').modal('show');
			    //add slider event to the div
			    $('.carousel').carousel();
			    //add swipe to the slider
			    $("#slideImage").touchwipe({
				wipeLeft: function() { $('.carousel').carousel('next'); },
				wipeRight: function() { $('.carousel').carousel('prev'); },
				min_move_x: 10,
				min_move_y: 10,
				preventDefaultEvents: true
			    });
			}
		    }
		});
	    });
	    //add click event to make the call to create the zip
	    $('.downloadAlbum').click(function(){
		$('#process_gif').modal('show');
		var title = $(this).attr('data_title');
		//pass the album id and title to the file to create the zip with the album title
		$.get('downloadAlbum.php?album_id='+$(this).attr('data_id')+'&album_title='+$(this).attr('data_title'))
		.complete(function(response){
		    $('#process_gif').modal('hide');
		    var data = JSON.parse(response.responseText);
		    if(data == 'login')//provide the message to login the facebook
		    {
			alert('Please login to download');
			window.location.reload();
		    }
		    else if(data == 'empty')
			alert('The album is empty');
		    else if(data == 'error')//provide the message that zip either not able to open or can't be created
			alert('Please Note : Some error in archieving images');
		    else
		    {
			if($('.links').empty())
			    $('.links').slideDown(3000);
			//provide the link until next refresh to download the zip
			$('.links').html("Hurrey!!! <a href='"+data+"'>here</a> is the link to the album archive.");
		    }
		});
	    });
	});
    </script>
    <!--script type="text/javascript" src="js/jquery.nivo.slider.js"></script-->
    <script type='text/javascript' language='javascript' src="js/googleAnalytics.js" ></script>
</html>