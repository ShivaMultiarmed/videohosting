<?php
    $Root = $_SERVER["DOCUMENT_ROOT"] . "/";
    require($Root . "engine/common.php");
    require($Root . "engine/classes/user.php");

    $thisUser = new User(2);
    echo $_POST["checked"];
    $MySqlI -> query("INSERT INTO `{$tablestart}user{$_COOKIE['userId']}playlist{$_POST['playlist']}` (`videoId`, channelId) VALUES ({$_POST['videoId']},{$_POST['channelId']});");
    //$WatchLater -> query("INSERT INTO `" . 2 . "` (`videoId`, channelId) VALUES ({$_POST['videoId']},{$_POST['channelId']});");
?>