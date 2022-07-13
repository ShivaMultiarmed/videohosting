<?php 
    $Root = $_SERVER["DOCUMENT_ROOT"] . "/"; // root of the Site

    $host = "sql311.byethost7.com";
    $user = "b7_31982157";
    $pass = "Warrior1984";
    $dbstart = "b7_31982157_"; // start of database title
    $tablestart = ""; // start of table title

    //$MySqlI = new mysqli($host, $user, $pass, $dbstart."main");
    //$Channels = new mysqli($host, $user, $pass, $dbstart."channels");
    //$History = new mysqli($host, $user, $pass, $dbstart."histories");
    //$WatchLater = new mysqli($host, $user, $pass, $dbstart."watchlater");
    //$PlayLists  = new mysqli($host, $user, $pass, $dbstart."playlists");
    //$likedVideos  = new mysqli($host, $user, $pass, $dbstart."likedvideos");

    $Titles = [
        "subChannel" => [
                "history" => "История", 
                "watchLater" => "Смотреть позже",
                "feed" => "Новости",
                "playList" => "Плейлист "// . $MySqlI -> query("SELECT `title` FROM `playlists` WHERE `userId` = ". 2 ." AND `playListId` = " . trim(htmlspecialchars($_GET["playlistnum"])) . ";") -> fetch_assoc()["title"]
            ],
        "user" => [
                "subscriptions" => "Подписки", 
                "playlists" => "Плейлисты"
            ],
        "auth" => [
                "login" => "Авторизация"	
            ]
    ];
?>