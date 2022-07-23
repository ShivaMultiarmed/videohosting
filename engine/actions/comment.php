<?php
    $Root = $_SERVER["DOCUMENT_ROOT"] . "/";

    require($Root . "engine/common.php");
    require($Root . "engine/classes/video.php");
    require($Root . "engine/classes/channel.php");

    $text = htmlspecialchars(trim($_POST["text"]));
    $userChannelId = htmlspecialchars(trim($_POST["userChannelId"]));

    if (is_bool(strpos($userChannelId, "user")))
        $isUser = 0;
    else    
        {
            $isUser = 1;
            $userChannelId = str_replace("user", "", $userChannelId);
        }

    $videoId = htmlspecialchars(trim($_POST["videoId"]));
    $channelId = htmlspecialchars(trim($_POST["channelId"]));
    $datetime = date('Y-m-d H:i:s');

    $MySqlI = new mysqli($host, $user, $pass, $dbstart."main");

    $channelNick = $MySqlI -> query("SELECT `channelNick` FROM `{$tablestart}channels` WHERE `channelId` = {$channelId};") -> fetch_assoc()["channelNick"];

    $MySqlI -> close();

    $Channels = new mysqli($host, $user, $pass, $dbstart."channels");

    // UserChannelId is User or Channel id
    $Channels -> query("INSERT INTO `{$tablestart}comments{$channelId}` (`id`,`userChannelId`, `videoId`, `text`, `datetime`, `isUser`) VALUES (NULL,{$userChannelId}, {$videoId}, '{$text}', '{$datetime}', {$isUser});");

    $Channels -> close();

    $ThisVideo = new Video($channelId, $videoId);
    echo $ThisVideo -> GetComments();
?>