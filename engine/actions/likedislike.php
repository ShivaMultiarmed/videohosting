<?php
    require("../../engine/common.php");
    require($Root . "engine/classes/channel.php");

    $thisChannel = new Channel("channel", $_POST["channelNick"]);
    
    if ($_POST['likeDislike'] == "like")
        $_POST['likeDislike'] = 1;
    else 
        $_POST['likeDislike']= -1; 

    $likedVideos  = new mysqli($host, $user, $pass, $dbstart."likedvideos");

    $sql = $likedVideos -> query("SELECT `like` FROM {$tablestart}user{$_POST['userId']} WHERE `channelId` = {$_POST['channelId']} AND videoId = {$_POST['videoId']};");

    if ($sql -> num_rows == 0)
    {
        $likedVideos -> query("INSERT INTO `{$tablestart}user{$_POST['userId']}` (`videoId`, `channelId`, `like`) VALUES ({$_POST['videoId']},{$_POST['channelId']}, {$_POST['likeDislike']});");
        if ($_POST['likeDislike'] == 1)
            $like = 1;
        else
            $dislike = 1;
    }
    else
    {
        $likeDislike = $sql -> fetch_assoc()["like"];   
        if ($_POST['likeDislike'] == $likeDislike){
            $likedVideos -> query("DELETE FROM `{$tablestart}user{$_POST['userId']}` WHERE `channelId` = {$_POST['channelId']} AND videoId = {$_POST['videoId']};");
            if ($_POST['likeDislike'] == 1)
                $like = -1;
            else
                $dislike = -1;
        }
        else
        {
            $likedVideos -> query("UPDATE {$tablestart}user{$_POST['userId']} SET `like` = {$_POST["likeDislike"]} WHERE `channelId` = {$_POST['channelId']} AND `videoId` = {$_POST['videoId']};");
            if ($_POST['likeDislike'] == 1)
            {    
                $like = 1; $dislike = -1;    
            }
            else
            {
                $dislike = 1; $like=-1;
            }
        }    
    }

    if (empty($like)) $like = 0;
    if (empty($dislike)) $dislike = 0;

    $likedVideos -> close();

    $Channels = new mysqli($host, $user, $pass, $dbstart."channels");

    $Channels -> query("UPDATE `{$tablestart}channel{$_POST['channelId']}` SET `dislikes` = `dislikes`+{$dislike},`likes` = `likes`+{$like} WHERE id = {$_POST['videoId']}");

    $Channels -> close();

    echo $thisChannel -> GetVidProps($_POST["videoId"]);

?>