<?php
    $Root = $_SERVER["DOCUMENT_ROOT"] . "/";
    require($Root . "engine/common.php");

    $MySqlI = new mysqli($host, $user, $pass, $dbstart."main");

    $subscribed = $MySqlI -> query("SELECT id FROM `{$tablestart}subscriptions` WHERE `channelId` = {$_POST['channelId']} AND `userId` = {$_COOKIE['userId']};") -> num_rows == 1;

    if ($subscribed)
    {
        $MySqlI -> query("DELETE FROM `{$tablestart}subscriptions` WHERE `channelId` = {$_POST['channelId']} AND `userId` = {$_COOKIE['userId']};");
        echo "no";
    }
    else
    {
        $MySqlI -> query("INSERT INTO `{$tablestart}subscriptions` VALUES (NULL,{$_COOKIE['userId']}, {$_POST['channelId']});");
        echo "yes";
    }

    $MySqlI -> close();

?>