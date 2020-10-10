<?php

	$username = $_GET['username'];
	$password = $_GET['password'];
	if(empty($username) && empty($password)){ 
die(json_encode(array('status' => 'ready')));

}
	$headers = array();
	$headers[] = "User-Agent: Mozilla/5.0 (Linux; Android 4.3; MediaPad 7 Youth 2 Build/HuaweiMediaPad) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.83 Safari/537.36";
	$headers[] = "X-Csrftoken: ".get_csrftoken();
	$login = instagram(0, 0, 'https://www.instagram.com/accounts/login/ajax/', 0, "username={$username}&password={$password}&queryParams=%7B%7D",$headers);
	$header = $login[0];
  
	$login = json_decode($login[1]);
	if($login->authenticated == true){
	
		preg_match_all('%Set-Cookie: (.*?);%',$header,$d);
		$cookies = '';
		for($o=0;$o<count($d[0]);$o++){
			$cookies.=$d[1][$o].";";
		}
		$search  = ['csrftoken="";','target="";'];
		$cookies = str_replace($search,'', $cookies);
    $data = curl('https://www.instagram.com/'.$username);
    $data = preg_match('/window._sharedData = (.*?);<\/script>/', $data, $dielz) ? $dielz[1] : null;
    $json = json_decode($data);
    $data = $json->entry_data->ProfilePage[0];
    $id = $data->graphql->user->id;
    $cookie = $cookies;
    	if($data->graphql->user->full_name==null){
      		$name = 'null';
    	}else{
      		$name = $data->graphql->user->full_name;
    	}
    	$following = $data->graphql->user->edge_follow->count;
    	$followers = $data->graphql->user->edge_followed_by->count;
    	if($data->graphql->user->biography==null){
      		$biography = 'null';
    	}else{
      		$biography = $data->graphql->user->biography;
    	}
     $username = $data->graphql->user->username;
     $picture = $data->graphql->user->profile_pic_url;     
		die(json_encode(array('status' => 'ok',
             'id' => $id,
             'cookie' => $cookie,
             'name' => $name,
             'following' => $following,
             'followers' => $followers,
             'biography' => $biography,
             'picture' => $picture,
             'username' => $username,
)));
		}elseif (strpos($login->message, 'checkpoint_required') !== false) {
die(json_encode(array('status' => 'fail', 'message' => 'Login checkpoint. Please open Instagram there and click this is me ...')));
}else{
die(json_encode(array('status' => 'fail', 'message' => 'Username dan password yang anda masukan salah!')));
}

function curl($url, $data=null) {
	$c = curl_init();
	curl_setopt($c, CURLOPT_URL, $url);
	if($data != null){
        curl_setopt($c, CURLOPT_POST, true);
        curl_setopt($c, CURLOPT_POSTFIELDS, $data);
    }
    curl_setopt($c, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
    $result = curl_exec($c);
    curl_close($c);
    return $result;
}
		
/* INSTAGRAM FUNCTION */
function instagram($ighost, $useragent, $url, $cookie = 0, $data = 0, $httpheader = array(), $proxy = 0, $userpwd = 0, $is_socks5 = 0)
{
	$url = $ighost ? 'https://i.instagram.com/api/v1/' . $url : $url;
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_USERAGENT, $useragent);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT, 20);
	if($proxy) curl_setopt($ch, CURLOPT_PROXY, $proxy);
	if($userpwd) curl_setopt($ch, CURLOPT_PROXYUSERPWD, $userpwd);
	if($is_socks5) curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
	if($httpheader) curl_setopt($ch, CURLOPT_HTTPHEADER, $httpheader);
	curl_setopt($ch, CURLOPT_HEADER, 1);
	if($cookie) curl_setopt($ch, CURLOPT_COOKIE, $cookie);
	if ($data):
		curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
	endif;
	$response = curl_exec($ch);
	$httpcode = curl_getinfo($ch);
	if(!$httpcode) return false; else{
		$header = substr($response, 0, curl_getinfo($ch, CURLINFO_HEADER_SIZE));
		$body = substr($response, curl_getinfo($ch, CURLINFO_HEADER_SIZE));
		curl_close($ch);
		return array($header, $body);
	}
}

function get_csrftoken()
{
	$fetch = instagram(1, 0 ,'si/fetch_headers/?challenge_type=signup');
	$header = $fetch[0];
	if (!preg_match('#Set-Cookie: csrftoken=([^;]+)#', $fetch[0], $token)) {
		return json_encode(array('result' => false, 'content' => 'Missing csrftoken'));
	} else {
		return substr($token[0], 22);
	}
}