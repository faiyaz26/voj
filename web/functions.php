<?php
require_once __DIR__.'/../vendor/autoload.php';
include_once 'config.php';
use GuzzleHttp\Client;




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

?>