<?php

require_once __DIR__.'/../vendor/autoload.php';
include_once 'functions.php';

$app = new Silex\Application();

$app->get('/', function() use($app) {

	$request = $app['request'];
	$oj = $request->get('oj');
	if($oj == 'uva'){
		$submissionId = $request->get('id');
		return $app->json(checkerUva($submissionId), 200);
	}else if($oj == 'spoj'){
		$submissionId = $request->get('id');
		return $app->json(checkerSpoj($submissionId), 200);
	}
	
    return 'Hello World!';
});


$app->run();