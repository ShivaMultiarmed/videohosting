<?php
	class Channel
	{
		function __construct($type, $channelNick)
		{
			global $host, $user, $pass, $dbstart;

			$MySqlI = new mysqli($host, $user, $pass, $dbstart."main");
	
			if($type == "channel"){
				$this -> Props = $MySqlI -> query("SELECT * FROM `{$tablestart}channels` WHERE `channelNick` = '{$channelNick}';") -> fetch_assoc(); 
			}
			else if($type == "subChannel")
			{
				$this -> Props["channelNick"] = $channelNick;
			}
			$this -> Props["pageNum"] = htmlspecialchars(trim($_GET["pagenum"]));
			$this -> Props["type"] = $type;

			$MySqlI -> close();
		}
		function GetWholeVideos()
		{
			global $host, $user, $pass, $dbstart, $tablestart;

			$MySqlI = new mysqli($host, $user, $pass, $dbstart."main");
			$Channels = new mysqli($host, $user, $pass, $dbstart."channels");

			if ($this -> Props["type"] == "channel" and trim(htmlspecialchars($_GET["type"])) != "video")
			{
				$videos = $Channels -> query("SELECT * FROM `{$tablestart}channel" . $this -> Props["channelId"] . "` ORDER BY `id` DESC LIMIT " . ($this -> Props["pageNum"] - 1)*20 . ", 20;");
				$videosnum = $videos -> num_rows;
			}
			else if ($this -> Props["type"] == "subChannel")
			{
				switch($this->Props["channelNick"])
				{
					case "trends":
						//$videos = $
					break;
					case "history":
						$History = new mysqli($host, $user, $pass, $dbstart."histories");

						$sqlfirst = "SELECT * FROM `{$tablestart}{$_COOKIE['userId']}` ORDER BY `datetime` DESC";
						$videosnum = $History -> query($sqlfirst) -> num_rows;
						$sqlfinal = $sqlfirst . " LIMIT " . ($this -> Props["pageNum"] - 1)*20 . ", 20";
						$videos = $History -> query($sqlfinal);
					break;
					case "watchLater":
						$WatchLater = new mysqli($host, $user, $pass, $dbstart."watchlater");

						$sqlfirst = "SELECT * FROM `{$tablestart}{$_COOKIE['userId']}` ORDER BY `id` DESC";
						$videosnum = $WatchLater -> query($sqlfirst) -> num_rows;
						$sqlfinal = $sqlfirst . " LIMIT " . ($this -> Props["pageNum"] - 1)*20 . ", 20";
						$videos = $WatchLater -> query($sqlfinal);
					break;
					case "feed":
						$this -> User = new User($_COOKIE['userId']);
						$channels = $this -> User -> GetSubscriptions();
						// channels are assigned to videos to make convenient transfer to the loop
						$videos = $channels;
					break;
					case "playList":
						$PlayLists  = new mysqli($host, $user, $pass, $dbstart."playlists");
						$videos = $PlayLists -> query("SELECT * FROM `{$tablestart}user{$_COOKIE['userId']}playlist" . trim(htmlspecialchars($_GET["playlistnum"])) . "` ORDER BY `id`;");
					break;
				}

			}

			else if (trim(htmlspecialchars($_GET["type"])) == "video")
			{
				$videos = $Channels -> query("SELECT * FROM `{$tablestart}channel" . $this -> Props["channelId"] . "` ORDER BY `datetime` DESC;");
			}

			$tpl = file_get_contents($Root . "templates/blocks/smallblocks/smallvideo.html");

			if ($videos -> num_rows > 0)
			{
				while ($singleVideo = $videos -> fetch_assoc())
				{
					if ($this -> Props["type"] == "channel")
						$channelId = $this -> Props["channelId"];
					else
						$channelId = $singleVideo["channelId"];

					switch($this->Props["channelNick"])
					{
						case "trends":
							// trends
						break;
						case "history":
							$singleVideo = $Channels -> query("SELECT * FROM `{$tablestart}channel" . $singleVideo["channelId"] . "` WHERE id = " . $singleVideo["videoId"] . ";") -> fetch_assoc();
						break;
						case "watchLater":
							$singleVideo = $Channels -> query("SELECT * FROM `{$tablestart}channel" . $singleVideo["channelId"] . "` WHERE id = " . $singleVideo["videoId"] . ";") -> fetch_assoc();
						break;
						case "feed":						
							$singleVideo = $Channels -> query("SELECT * FROM `{$tablestart}channel" . $singleVideo["channelId"] . "` ORDER BY `datetime` DESC;") -> fetch_assoc();
						break;
						case "playList":		
							$singleVideo = $Channels -> query("SELECT * FROM `{$tablestart}channel" . $singleVideo["channelId"] . "` WHERE `id` = " . $singleVideo["videoId"] . ";") -> fetch_assoc();
						break;
					}
					
					$videoChannel = $MySqlI -> query("SELECT * FROM `{$tablestart}channels` WHERE `channelId` = " . $channelId . ";") -> fetch_assoc();
					$curvideo = $tpl;
					$replacements = [
						"channeltitle" => $videoChannel["title"],
						"channelid" => $videoChannel["channelId"],
						"channelnick" => $videoChannel["channelNick"],
						"videochannel" => $singleVideo["videochannel"],
						"videotitle" => $singleVideo["title"],
						"videoid" => $singleVideo["id"],
					];
					foreach ($replacements as $key => $value)
					{
						$curvideo = str_replace("[" . strtoupper($key) . "]", $value, $curvideo);
					}
					$wholeVideos .= $curvideo;
					
				}
				$pagesnum = ceil($videosnum / 20);
				if ($pagesnum > 1)
					$wholeVideos .= $this -> PageMenu($pagesnum, intval(htmlspecialchars(trim($_GET["pagenum"]))));
			}
			else
				$wholeVideos = "There are no videos.";

			$MySqlI-> close();
			$Channels -> close(); 
			if ($WatchLater != null)
				$WatchLater -> close();				
			if ($PlayLists != null)
				$PlayLists -> close();
			
			return $wholeVideos;
		}
		
		function PageMenu($pageNum, $cur) // num of whole pages and current num
		{
			$cur = intval($cur);
			$getvars = "?" . htmlspecialchars(trim($_SERVER["QUERY_STRING"]));
			$getvars = substr($getvars, 0, strpos($getvars, "pagenum=")+8);
			$menu = "<div id=\"pagination\">";
				$menu .= "<div>";
					if ($cur != 1)
						$menu .= "<a href=\"" . $getvars . ($cur - 1) . "\" style=\"font-weight:bold;\">&lt;</a>";
					for ($i = 1; $i<=$pageNum; $i++)
						if ($cur == $i)
							$menu .= "<a href=\"" . $getvars . $i . "\" style=\"font-weight:bold;\">" . $i . "</a>";
						else
							$menu .= "<a href=\"" . $getvars . $i . "\">" . $i . "</a>";
					if ($cur != $pageNum)
						$menu .= "<a href=\"" . $getvars . ($cur + 1) . "\" style=\"font-weight:bold;\">&gt;</a>";
				$menu .= "</div>";
			$menu .= "</div>";

			return $menu;
		}
	}
?>