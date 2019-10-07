<?php

$config = array(
	"db" => array(
		"dbname" => "usmnet",
		"username" => "root",
		"password" => "password",
		"host" => "localhost"
	),
	"urls" => array(
		"baseUrl" => "http://10.224.241.84"
	),
	"paths" => array(
		"resources" => "/var/www/html/usm.illinois.edu/resources/",
		"images" => "/var/www/html/usm.illinois.edu/public_html/img"
	),
	"lens" => array(
		"user" => "lens-portlet", // Talk to REMOVED to see if he'll let you use this account which has read-only access to all networks
		"password" => "REMOVED" // I had passwords in each of these
	),
	"ca" => array(
		"user" => "teesh",
		"password" => "REMOVED"//talk to REMOVED about getting access for your netid or a psuedo account
	),
	"ipam" => array(
		"user" => "ct-ipam-user",// just an AD account I made and asked REMOVED to give same access as my netid
		"password" => "REMOVED"
	),
	"extron_controller" => array(
		"user" => "admin", // the rest are local accounts for the Basic Auth thing I did
		"password" => "REMOVED"
	),
	"amx_controller" => array(
		"user" => "admin",
		"password" => 'REMOVED'
	),
	"extron_lecture_capture" => array(
		"user" => "admin",
		"password" => "REMOVED"
	),
	"axis_camera" => array(
		"user" => "root",
		"password" => "REMOVED"
	)
);

ini_set("error_reporting", "true");
error_reporting(E_ALL|E_STRICT);

?>
