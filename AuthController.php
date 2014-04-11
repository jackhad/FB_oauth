<?php
require_once("lib/facebookpost/facebook.php");
require_once("lib/twitter/oauth/twitteroauth.php");
try{

	
	$userid = 0;
	$userdata = array();
    
    switch($action){
    case 'FBlogin':
			//echo "Fb login here";
			
			$facebook = new Facebook(array(
		  		'appId'  => "578246158899925",
		  		'secret' => "ebc1ee9daa51e4648fd29d10da9638ea",
			));
			$user = $facebook->getUser();
			
			if (!empty($user)) {
				$user_profile = $facebook->api('/me');
				//face book id checking
				
				//print_r($user_profile); 
				$profiledata = User::getAll("email = '".$user_profile['email']."'");
				//print_r($user_profile);
				if(!empty($profiledata)){
					//redirect to logged as user page
					$user = new User();
					$user->doLoginFacebook($user_profile['email'], '');
					$userObj = $user->is_loggedin();
					
					
					Url::redirect(Url::site('jobseeker/account'));
					$view = 'account';
					
				}else{
				
					$ser_vb=$_SERVER['REMOTE_ADDR'];
					$created=date('Y-m-d H:i:s');
					$created_st=strtotime($created);
					$fb_data = array();
					$_SESSION['firstname']=$user_profile['first_name'];
					$_SESSION['lastname']=$user_profile['last_name'];
					$_SESSION['email']=$user_profile['email'];
					$_SESSION['username']=$user_profile['email'];
					$_SESSION['aid'] = $user_profile['id'];
					$_SESSION['link'] = $user_profile['link'];
					$_SESSION['type'] = "faceboook";
					$_SESSION['gender'] = $user_profile['gender'];
					Url::redirect(Url::site('jobseeker/register'));
				}
				
			   		
			}else{
				$loginUrl = $facebook->getLoginUrl(array('scope'=>'email','display'=>'popup'));
				Url::redirect($loginUrl);
			}
			//print_r($loginUrl);
			//header('location:'.$loginUrl);
			
			
		break;
		
		case 'twitter':
		$connection = new TwitterOAuth("PEBsIcKWMRqgW9rE2uuA", "yBVu5acNykK80cQVq4Oo060HFovUcvmeRb9STAI6Ro");
		$request_token = $connection->getRequestToken('http://localhost/FIC/jobseeker/twittercalback/');
		$token = $request_token['oauth_token'];
		$oauth_token_secret = $request_token['oauth_token_secret'];
		$_SESSION['oauth_token']=$token;
		$_SESSION['oauth_token_secret']=$oauth_token_secret;
		$url = $connection->getAuthorizeURL($token);
		Url::redirect($url);
		
		break;
			
		case 'twittercalback':
		
		$connection = new TwitterOAuth("PEBsIcKWMRqgW9rE2uuA", "yBVu5acNykK80cQVq4Oo060HFovUcvmeRb9STAI6Ro",$_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);
		$access_token = $connection->getAccessToken($_REQUEST['oauth_verifier']);
		$content = $connection->get('account/verify_credentials');
		//print_r($content);
		$db = DB::getInstance();
		$sql = 'SELECT * FROM gs_socialmedia WHERE aid="'.$content->id.'"';
		$res = $db->query($sql);
		$obj = $db->fetchObject($res);
		
		if(!empty($obj)){
					//redirect to logged as user page
					$profiledata = User::getAll("id = '".$obj->uid."'");
					//print_r($profiledata);echo $profiledata[0]->email;die;
					$user = new User();
					$user->doLoginFacebook($profiledata[0]->email, '');
					
					$userObj = $user->is_loggedin();
					Url::redirect(Url::site('jobseeker/account'));
					$view = 'account';
					
				}else{
				
					$ser_vb=$_SERVER['REMOTE_ADDR'];
					$created=date('Y-m-d H:i:s');
					$created_st=strtotime($created);
					$fb_data = array();
					$_SESSION['firstname'] = $content->name;
					$_SESSION['aid'] = $content->id;
					$_SESSION['link'] = "www.twitter.com/".$content->screen_name;
					$_SESSION['type'] = "twitter";
					$_SESSION['profile_image_url'] = $content->profile_image_url;
					
					Url::redirect(Url::site('jobseeker/register'));
				}
		die;
		
		break;
		 default:
		if((!empty(User::is_loggedin()->id)) && User::is_loggedin()->id > 0 && User::is_loggedin()->access == User::jobSeekerAccess()){
			Url::redirect(Url::site('jobseeker/account'));
		}
       $view = new JobseekerView('jobseeker/home');
       $view->set($_POST);
       $view->render();
    break;
}
