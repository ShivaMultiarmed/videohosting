<?php
    $Root = $_SERVER["DOCUMENT_ROOT"] . "/";
    require($Root . "engine/common.php");

    $MySqlI = new mysqli($host, $user, $pass, $dbstart."main");

    $table = $MySqlI -> query("SELECT * FROM `{$tablestart}playlists` WHERE `userId` = {$_COOKIE['userId']};");
    
    $PlayLists = new mysqli($host, $user, $pass, $dbstart."playlists");

    echo "<form method='post' id='playListDialog'>";
    while($row = $table -> fetch_assoc())
    {
        $checked = ($PlayLists -> query("SELECT * FROM `{$tablestart}user{$_COOKIE['userId']}playlist{$row['playlistId']}` WHERE videoId = {$_POST['videoId']} AND channelId = {$_POST['channelId']}") -> num_rows == 1) ? "checked" : "";
        echo "<input type='checkbox' name='list' value='{$row['playlistId']}' {$checked} /><label>{$row['title']}</label>";
    }
    echo "</form>";

    $MySqlI -> close();
    $PlayLists -> close();
?>