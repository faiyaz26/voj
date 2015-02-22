<?php
require_once __DIR__.'/../vendor/autoload.php';
include_once 'config.php';
use Sunra\PhpSimple\HtmlDomParser;

/*

function checkerUva($submissionId){
	$client = new Client(); // creating guzzle client

	$uvaId = UVA_ID;
	$url = "http://uhunt.felix-halim.net/api/subs-user-last/$uvaId/1000"; // url to which we will request data
	

	$response = $client->get($url);

	if($response->getStatusCode() != 200){

		$ret = array(
			'success' => false,
			'message' => 'error'
			);

		return $ret;
	}
	$data = json_decode($response->getBody());

	foreach ($data->subs as $sub) {
		if($sub[0] == $submissionId){

			$verdict = trim($sub[2]);
			$runtime = trim($sub[3]);
			$runtime = 0.001 * $runtime; // in seconds now

			$ret = array(
				'success' => true,
				'oj' => 'uva',
				'submission_id' => $submissionId,
				'verdict' => $verdict,
				'runtime' => $runtime
				);

			return $ret;
		}
	}

	$ret = array(
		'success' => false,
		'message' => 're-submit'
		);

	return $ret;
	//print_r($data);
}


function checkerSpoj($submissionId){
	$client = new Client(); // creating guzzle client

	$spoj_id = SPOJ_ID;

	$url = "http://www.spoj.com/status/$spoj_id/signedlist/";

	$response = $client->get($url);

	if($response->getStatusCode() != 200){

		$ret = array(
			'success' => false,
			'message' => 'error'
			);

		return $ret;
	}

	$body =  $response->getBody();

	$lines = explode("\n", $body);

	$size = sizeof($lines);
	for($i = 9; $i < $size; $i++){
		if($lines[$i][0] != '|') break;

		$sub = explode('|', $lines[$i]);

		//print_r($sub);
		$id = trim($sub[1]);
		if($id == $submissionId){

			$verdict = trim($sub[4]);
			$runtime = trim($sub[5]); // in seconds
			
			$ret = array(
				'success' => true,
				'oj' => 'spoj',
				'submission_id' => $submissionId,
				'verdict' => $verdict,
				'runtime' => $runtime
				);

			return $ret;
		}
	}

	$ret = array(
		'success' => false,
		'message' => 're-submit'
		);
	return $ret;
}

*/

function vojSubmit ($probId, $language , $code){
	$username = VOJ_ID;
	$pass   = VOJ_PASS;

	$url    = 'http://acm.hust.edu.cn/vjudge/';


	$loginUrl = $url.'user/login.action';


	$problemSubmitUrl = $url.'vjudge/problem/submit.action';
	//echo $loginUrl;

	$ret = array();
    $fields = array(
    	'username' => urlencode($username),
    	'password' => urlencode($pass)
    );

    $client = new Client();
    $response = $client->post($loginUrl, [
	    'headers' => ['X-Foo' => 'Bar'],
	    'body'    => $fields,
	    'allow_redirects' => false
	]);

	//echo $response->getEffectiveUrl();
	echo $response->getBody();
	return $ret;
}


function randStrGen($len){
    $result = "/* ";
    $chars = "abcdefghijklmnopqrstuvwxyz$?!-0123456789";
    $charArray = str_split($chars);
    for($i = 0; $i < $len; $i++){
	    $randItem = array_rand($charArray);
	    $result .= "".$charArray[$randItem];
    }

    $result = $result." */\n";
    return $result;
}

function bnuOjSubmit($probId, $language, $code){

	$username = BNUOJ_ID;
	$password = BNUOJ_PASS;
	$loginUrl = 'http://www.bnuoj.com/v3/ajax/login.php';
	
	$loginData = array(
		'username' => $username,
		'password' => $password,
		'cksave'   => '365',
		'login'    => 'Login'
	);

	$ret = array();
	try{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$loginUrl);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt ($ch, CURLOPT_COOKIEJAR, '-'); // <-- see here
		curl_setopt($ch, CURLOPT_POST, count($loginData));
	    curl_setopt($ch, CURLOPT_POSTFIELDS, $loginData); 
		
		$result = curl_exec($ch);

		if(curl_errno($ch)){
			throw new Exception(curl_error($ch));
		}



		$json = json_decode($result);

		if($json->msg == 'Success...'){

			$submitUrl = 'http://www.bnuoj.com/v3/ajax/problem_submit.php';

			
			$code  = randStrGen(rand(100, 200)).$code;

			//echo $code;
			$codeLen =  mb_strlen($code, '8bit');;
			//return;

			
			$submitData = array(
				'user_id' => urlencode($username),
				'problem_id'=> urlencode($probId),
				'language' => urlencode($language),
				'isshare'  => urlencode('0'),
				'login'    => urlencode('Submit'),
				'source'   => $code
			);
			curl_setopt($ch, CURLOPT_URL,$submitUrl);
			curl_setopt($ch, CURLOPT_POST, count($submitData));
	    	curl_setopt($ch, CURLOPT_POSTFIELDS, $submitData);

	    	$submitResult = curl_exec($ch);


	    	if(curl_errno($ch)){
				throw new Exception(curl_error($ch));
			}

			$json = json_decode('{"code":0,"msg":"Submitted."}');
			//var_dump($json);

			$pos = strpos($submitResult, 'Submitted.');
			if($pos === false){
				throw new Exception('Not Submitted');
			}

	    	$statusUrl = 'http://www.bnuoj.com/v3/ajax/status_data.php?iColumns=10&sColumns=&iDisplayStart=0&iDisplayLength=200';
			curl_setopt($ch, CURLOPT_URL,$statusUrl);

			$status = curl_exec($ch);

			if(curl_errno($ch)){
				throw new Exception(curl_error($ch));
			}

			$json = json_decode($status);

			//print_r($json);
			foreach ($json->aaData as $key => $value) {
				if($value[0]==$username){
					if($value[2]==$probId){
						if($value[7] == $codeLen){
							$ret['success'] = true;
							$ret['submission_id'] =  $value[1];
							break;
						}
					}
				}
			}

	    	//echo $dom;
		}
		curl_close($ch);
	}catch(Exception $e){
		//echo $e;
		$ret['success'] = false;
		$ret['msg'] = "error";
	}

	return $ret;
}

function bnuOJCheck($submission_id){
	$username = BNUOJ_ID;
	$password = BNUOJ_PASS;
	$loginUrl = 'http://www.bnuoj.com/v3/ajax/login.php';
	
	$loginData = array(
		'username' => $username,
		'password' => $password,
		'cksave'   => '365',
		'login'    => 'Login'
	);

	$ret = array();
	try{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$loginUrl);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt ($ch, CURLOPT_COOKIEJAR, '-'); // <-- see here
		curl_setopt($ch, CURLOPT_POST, count($loginData));
	    curl_setopt($ch, CURLOPT_POSTFIELDS, $loginData); 
		
		$result = curl_exec($ch);

		if(curl_errno($ch)){
			throw new Exception(curl_error($ch));
		}

		$json = json_decode($result);
		if($json->msg == 'Success...'){
			$statusUrl = "http://www.bnuoj.com/v3/ajax/get_source.php?runid=$submission_id";
			
				curl_setopt($ch, CURLOPT_URL,$statusUrl);
				$statusResult = curl_exec($ch);
		    	if(curl_errno($ch)){
					throw new Exception(curl_error($ch));
				}

				$json = json_decode($statusResult, true);

				$json['success'] = true;

				$ret = $json;
		}
		curl_close($ch);
	}catch(Exception $e){
		//echo $e;
		$ret['success'] = false;
		$ret['msg'] = "error";
	}
	return $ret;
}

?>