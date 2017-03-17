<?php

	require __DIR__ . '/../vendor/autoload.php';

	require __DIR__ . '/../include/functions.php';

	require_once __DIR__ . '/../include/DbOperations.php';


	$env_var = simplexml_load_file("../../env_var.xml");
	$_ENV['DB'] = $env_var->db;
  	$_ENV['GMAIL'] = $env_var->gmail_smtp;
  	$_ENV['DOMAIN'] = $env_var->domain;
  	$_ENV['KEY'] = $env_var->key;
	
	
	$app = new \Slim\App
	([
		'settings' => 
		[
			'displayErrorDetails' => true,
		]
	]);




	