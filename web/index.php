<?php

require_once __DIR__.'/../vendor/autoload.php';
include_once 'functions.php';
use Symfony\Component\HttpFoundation\Request;
$app = new Silex\Application();
//$app['debug'] = true;

$app->get('/', function(Request $request){
	$oj = $request->get('oj');
	if($oj == 'uva'){
		$submissionId = $request->get('id');
		return $app->json(checkerUva($submissionId), 200);
	}else if($oj == 'spoj'){
		$submissionId = $request->get('id');
		return $app->json(checkerSpoj($submissionId), 200);
	}else if($oj== 'bnuoj'){
		$submissionId = $request->get('id');
		return json_encode(bnuOJCheck($submissionId));
	}
});

$app->post('/', function (Request $request) {

	$code = $request->get('code');
	$probId = $request->get('prob_id');
	$language = $request->get('language');

	$var = bnuOjSubmit($probId, $language, $code);
    return json_encode($var);
});


$app->run();