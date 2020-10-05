<?php 
require_once 'config.php';
if(isset($_GET['code']) && FB_APP_STATE==$_GET['state']){
  //$accessToken = getAccessTokenWithCode($_GET['code']);
 // echo '<pre>';
  //print_r($accessToken);
  //die();
  $fbLogin = tryAndLoginWithFacebook($_GET);
}
//loggedInRedirect();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>
</head>
<body>
  <a href="<?= getFacebookLoginUrl()?>">FACEBOOK</a>
</body>
</html>