<?php
session_start();
require_once('inc/db.php');
global $dbconn;
if (isset($_SESSION['userid'])) {
  $userid = $_SESSION['userid'];
} else {
	header('Location: login.php');
}

include ("inc/header.php");
?>

<h1 class="title">Create</h1>
<?php
  $id = get('id', -1);
  $title = get('title', '');
  $artist = get('artist', '');
  $url ="";

  $ch = curl_init();

  // set URL and other appropriate options
  curl_setopt($ch, CURLOPT_URL, "http://demoapi.smk.dk/api/artworks?refnum=$id&start=0&rows=10");
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_HEADER, 0);

  // grab URL and pass it to the browser
  $apidata_ind=curl_exec($ch);
  $nye = json_decode($apidata_ind);

   if(isset($nye->error)) {
       print_r($nye);
	   die($nye->error);
   } else {
       $cur_doc = $nye->response->docs[0];
       $url = $cur_doc->medium_image_url;
	   $nr = 0;
       echo "<image src=\"$url\" width=\"420\" >";
       foreach($cur_doc->artist_name as $cur_artist) {
	       if($nr==0)
		       echo "<br />$cur_artist";
           else
			   echo ", $cur_artist";
		    $nr++;
       }
	   echo ": ";

       if($cur_doc->title_first)
           echo "$cur_doc->title_first";

       if($cur_doc->object_type_dk)
		  echo " ($cur_doc->object_type_dk)";
      }
?>

<img id="preview" />

<form class="uploadform" action="image.php" method="post" enctype="multipart/form-data" onchange="loadFile(event)">
	<label id="uploadbtn" class="uploadform__btn uploadform__btn--fileuploadbtn" for="fileToUpload">Upload your own:</label>
	<input class="uploadform__fileinput" type="file" name="fileToUpload" id="fileToUpload">
        <input type="hidden" name="image_id" value="<?php print $id; ?>">
        <input type="hidden" name="profile_id" value="<?php print $userid; ?>">
        <input type="hidden" name="title" value="<?php print $title; ?>"> 
        <input type="hidden" name="artist" value="<?php print $artist; ?>"> 
        <input type="hidden" name="url" value="<?php print $url; ?>">
        <input type="hidden" name="user_title" value="<?php $title;?>">

	<div id="accept">
	<button class="uploadform__btn uploadform__btn--accept" type="submit" name="submit">Accept</button>
	<button class="uploadform__btn uploadform__btn--decline" type="button" name="submit" onclick="deleteimg()">Delete</button>
	</div>
</form>


<div class="explore">
<?php
  $query = "select archive_id, url, profile_id, email from adaptation,user_image,original_image,profile  where profile_id=profile.id and original_image.id = original_image_id and user_image.id= user_image_id and original_image.source_image_url='".$url."'";
  $res = pg_query($dbconn, $query);
  while ($data=pg_fetch_object($res)) {
      echo '<a class="explore__box" href="profile.php?id='.$data->profile_id.'"> <img src="'.$data->url.'" width="420"></a>';
  }
?>
</div>
</body>
</html>
