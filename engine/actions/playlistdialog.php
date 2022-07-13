<?php
    $Root = $_SERVER["DOCUMENT_ROOT"] . "/";
    require($Root . "engine/common.php");

    $table = $MySqlI -> query("SELECT * FROM `{$tablestart}playlists` WHERE `userId` = " . 2 . ";");
    
    echo "<form>";
    while($row = $table -> fetch_assoc())
    {
        $checked = ($PlayLists -> query("SELECT * FROM `{$tablestart}user" . 2 . "playlist{$row['playlistId']}` WHERE videoId = {$_POST['videoId']} AND channelId = {$_POST['channelId']}") -> num_rows == 1) ? "checked" : "";
        echo "<input type='checkbox' name='list' value='{$row['playlistId']}' {$checked} /><label>{$row['title']}</label>";
    }
    echo "</form>";
?>