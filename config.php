<?php
session_start();

define('FB_APP_ID', '832470887291302');
define('FB_APP_SECRET', '196e88e4a6b0ff752679338e516101ae');
define('FB_REDIRECT_URI', 'https://app.kiteradar.com.br/');
define('FB_GRAPH_VERSION','v6.0');
define('FB_GRAPH_DOMAIN','https://graph.facebook.com/');
define('FB_APP_STATE','eciphp');

define('DB_HOST','host');
define('DB_NAME','db');
define('DB_USER','user');
define('DB_PASS','pass');


function getDatabaseConnection() {
  try { // connect to database and return connections
    $conn = new PDO( 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS );
    return $conn;
  } catch ( PDOException $e ) { // connection to database failed, report error message
    return $e->getMessage();
  }
}
function getRowWithValue($tableName, $column, $value, $id){
    $db = getDatabaseConnection();
    $stmt = $db->prepare("UPDATE ".$tableName." SET ".$column ."=:value WHERE id=:id");
    $params = [
      'value'=>trim($value),
      'id'=>trim($id)
    ];
    $stmt->execute($params);
}

function makeFacebookApiCall($endpoint, $params){
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL,$endpoint.'?'.http_build_query($params));
  curl_setopt($ch, CURLOPT_RETURNTRANSFER,TRUE);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,FALSE);
  $fbResponse = curl_exec($ch);
  $fbResponse = json_decode($fbResponse, TRUE);
  curl_close($ch);
  return [
    'endpoint'=>$endpoint,
    'params'=>$params,
    'has_errors'=>isset($fbResponse['error'])?TRUE:FALSE,
    'error_message'=>isset($fbResponse['error'])?$fbResponse['error']['message']:'',
    'fb_response'=>$fbResponse,
  ];
}

function getFacebookLoginUrl(){
  $endpoint = 'https://www.facebook.com/'.FB_GRAPH_VERSION .'/dialog/oauth';

  $params =[
    'client_id'=>FB_APP_ID,
    'redirect_uri'=>FB_REDIRECT_URI,
    'state'=>FB_APP_STATE,
    'scope'=>'email',
    'auth_type'=>'rerequest'
  ];

  return $endpoint.'?'.http_build_query($params);
}

function getAccessTokenWithCode($code){
  $endpoint = "https://graph.facebook.com/".FB_GRAPH_VERSION.'/oauth/access_token';

  $params = [
    'client_id'=>FB_APP_ID,
    'client_secret'=>FB_APP_SECRET,
    'redirect_uri'=>FB_REDIRECT_URI,
    'code'=>$code
  ];
    return makeFacebookApiCall($endpoint, $params);
}

function getFacebookUserInfo($accessToken){
  $endpoint = FB_GRAPH_DOMAIN.'me';
  $params = [
    'fields'=>'first_name, last_name, email, picture',
    'access_token'=>$accessToken
  ];
  return makeFacebookApiCall($endpoint, $params);
}

function tryAndLoginWithFacebook($get){
  $status = 'fail';
  if(isset($get['error'])){
    $message = $get['error_description'];
  }else{
    $accessTokenInfo = getAccessTokenWithCode($get['code']);
    if ($accessTokenInfo['has_erros']) {
        $message = $accessTokenInfo['error_message'];
    } else {
        $_SESSION['fb_access_token'] = $accessTokenInfo['fb_response']['access_token'];
        $fbUserInfo = getFacebookUserInfo($_SESSION['fb_access_token']);
       /* echo '<pre>';
        print_r($fbUserInfo);
        die();*/
        if(!$fbUserInfo['has_erros'] && !empty($fbUserInfo['fb_response']['id']) && !empty($fbUserInfo['fb_response']['email'])){
          $status = 'ok';
          $_SESSION['fb_user_info'] = $fbUserInfo['fb_response'];
          $userInfoWithId = getRowWithValue('users','fb_user_id',$fbUserInfo['fb_response']['id']);
          $userInfoWithEmail = getRowWithValue('users','email',$fbUserInfo['fb_response']['email']);

          if ($userInfoWithId) {
            $userId = $userInfoWithId['id'];
            updateRow('users', 'fb_access_token',$_SESSION['fb_access_token'],$userId);
          } elseif($userInfoWithEmail && !$userInfoWithEmail['fb_user_id']) {
            # code...
          }
          
        }
    }
    
  }

  return [
    'status'=>$status,
    'message'=>$message
  ];
}
