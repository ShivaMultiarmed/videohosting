<?php
	ini_set('display_errors', 'On');

	header("Content-Type: text/html; charset=utf-8");

	require("engine/common.php");

	require("engine/engine.php");

	require("engine/classes/maincontent.php");
	require("engine/classes/channel.php");
	require("engine/classes/user.php");
	require("engine/classes/auth.php");

	// Not prepaired list of properties
	$preprops = explode("&amp;", htmlspecialchars(trim($_SERVER["QUERY_STRING"])));
	// property list
	foreach ($preprops as $value)
	{
		$subarr = explode("=", $value);
		$props[$subarr[0]] = $subarr[1];
	}

	$Page = new Page($props);
	$Page -> Init();
	$Page -> ConstructPage();
	print($Page -> WholePage);
?>