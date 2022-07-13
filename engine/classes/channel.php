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
		function GetWholeVideos() // in variable WholeVideos it creates a list of whole videos
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
						if ($videos -> num_rows > 0)				
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
			
			if ($videos -> num_rows == 0)
				$wholeVideos = "There are no videos.";

			$MySqlI-> close();
			$Channels -> close(); 
			if ($WatchLater != null)
				$WatchLater -> close();				
			if ($PlayLists != null)
				$PlayLists -> close();
			
			return $wholeVideos;
		}
		function GetVideo($Id) //  for a page of a single video
		{
			global $host, $user, $pass, $dbstart;

			$Channels = new mysqli($host, $user, $pass, $dbstart."channels");
			
			$videoProps = $Channels -> query("SELECT * FROM `{$tablestart}channel" . $this -> Props["channelId"] . "` WHERE `id` = " . $Id . ";") ->fetch_assoc();
			
			$video = file_get_contents($Root."templates/blocks/content/video.html");

			$replacements = [
				"channelnick" => $this -> Props["channelNick"],
				"CHANNELTITLE" => $this -> Props["title"],
				"CHANNELID" => $this -> Props["channelId"],
				"videotitle" => $videoProps["title"], 
				"likes" => $videoProps["likes"],
				"dislikes" => $videoProps["dislikes"], 
				"views" => $videoProps["views"], 
				"videoid" => $Id, 
				"recommendations" => $this -> GetWholeVideos(),
				"comments" => $this -> GetComments($Id),
				"num" => $this -> GetNumComments($Id),
				"userid" => 2,
				"mainvidprops" => $this -> GetVidProps($Id),
				"vidtools" => $this -> GetVidTools($Id)
			];

			foreach ($replacements as $key => $value)
			{
				$video = str_replace("[" . strtoupper($key) . "]", $value, $video);
			}

			// write to watch history
			$History = new mysqli($host, $user, $pass, $dbstart."histories");
			if ($History -> query("SELECT * FROM `{$tablestart}{$_COOKIE['userId']}` WHERE videoId = " . $Id . " AND channelId = " . $this -> Props['channelId'] . ";") -> num_rows == 1)
				$History -> query("DELETE FROM `{$tablestart}{$_COOKIE['userId']}` WHERE videoId = " . $Id ." AND channelId = " . $this -> Props['channelId'] . ";");
			$History -> query("INSERT INTO `{$tablestart}{$_COOKIE['userId']}` VALUES (" . $Id .", \"" . date('Y-m-d H:i:s') ."\", " . $this -> Props['channelId'] . ", NULL);");
			$Channels -> query("UPDATE `{$tablestart}channel" . $this -> Props["channelId"] . "` SET `views` = `views`+1 WHERE `id` = " . $videoProps["id"] . ";");
			
			$Channels -> close();
			$History -> close();
			
			return $video;
		}
		function GetVidProps($Id)
		{
			global $Root, $host, $user, $pass, $dbstart;

			$Channels = new mysqli($host, $user, $pass, $dbstart."channels");

			$mainvidprops = file_get_contents($Root."templates/blocks/smallblocks/MainVidProps.html");

			$videoProps = $Channels -> query("SELECT * FROM `{$tablestart}channel" . $this -> Props["channelId"] . "` WHERE `id` = " . $Id . ";") ->fetch_assoc();

			$replacements = [
				"channelnick" => $this -> Props["channelNick"],
				"CHANNELID" => $this -> Props["channelId"],
				"videotitle" => $videoProps["title"], 
				"likes" => $videoProps["likes"],
				"dislikes" => $videoProps["dislikes"], 
				"views" => $videoProps["views"], 
				"videoid" => $Id, 
				"userid" => $_COOKIE['userId'],
			];

			$Channels -> close();

			foreach ($replacements as $key => $value)
			{
				$mainvidprops = str_replace("[" . strtoupper($key) . "]", $value, $mainvidprops);
			}

			return $mainvidprops;
		}
		function GetVidTools($Id)
		{
			$tools = file_get_contents($Root."templates/blocks/smallblocks/vidtools.html");

			$replacements = [
				"videoid" => $Id,
				"channelid" => $this -> Props["channelId"],
				"url" => $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]
			];

			foreach($replacements as $key => $value)
				$tools = str_replace("[" . strtoupper($key) . "]", $value, $tools);

			return $tools;
		}
		function GetComments($Id)
		{
			global $Root, $host, $user, $pass, $dbstart;

			$MySqlI = new mysqli($host, $user, $pass, $dbstart."main");
			$Channels = new mysqli($host, $user, $pass, $dbstart."channels");

			$commentsQuery = $Channels -> query("SELECT * FROM `{$tablestart}comments" . $this -> Props["channelId"] ."` WHERE `videoid` = \"" . $Id . "\" ORDER BY `datetime` DESC;");
			$comments = "<div id='commentsHeader'><h6>{$this->GetNumComments($Id)} Comments</h6></div>";
			$tpl = file_get_contents($Root . "templates/blocks/smallblocks/comment.html");
			if ($commentsQuery -> num_rows > 0)
			{
				while ($curcommsql = $commentsQuery -> fetch_assoc())
				{
					$curcomm = $tpl;

					if ($curcommsql["isUser"] == 1)
					{
						$nick = $MySqlI -> query("SELECT `nickname` FROM `{$tablestart}users` WHERE `userid` = " . $curcommsql["userChannelId"] . ";") -> fetch_assoc()["nickname"];
						$isUser = "users";
					}
					else
					{
						$nick = $MySqlI -> query("SELECT `title` FROM `{$tablestart}channels` WHERE `channelId` = " . $curcommsql["userChannelId"] . ";") -> fetch_assoc()["title"];
						$isUser = "channels";
					}	

					$replacements = [
						"userchannelid" => $curcommsql["userChannelId"],
						"nick" => $nick,
						"isuser" => $isUser,
						"text" => $curcommsql["text"],
						"datetime" => $curcommsql["datetime"]
					];
					foreach ($replacements as $key => $value)
					{
						$curcomm = str_replace("[" . strtoupper($key) . "]", $value, $curcomm);
					}
					$comments .= $curcomm;
				}
			}

			$MySqlI -> close();
			$Channels -> close();

			$form = $this -> getForm($Id);

			$comments = $form . $comments;

			return $comments;
		}
		function GetNumComments($Id)
		{
			global $host, $user, $pass, $dbstart;
			$Channels = new mysqli($host, $user, $pass, $dbstart."channels");
			$num = $Channels -> query("SELECT * FROM `{$tablestart}comments" . $this -> Props["channelId"] . "` WHERE `videoId` = " . $Id . ";") -> num_rows;
			$Channels -> close();
			return $num;
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
		function getForm($Id)
		{
			global $Root, $host, $user, $pass, $dbstart;

			$MySqlI = new mysqli($host, $user, $pass, $dbstart."main");

			$form = file_get_contents($Root . 'templates/blocks/smallblocks/commentForm.html');

			$channelsOptions = $MySqlI -> query("SELECT `channelId`,`title` FROM `{$tablestart}channels` WHERE `userId` = '{$_COOKIE['userId']}';");

			while($row = $channelsOptions -> fetch_assoc())
				$options .= "<option value='{$row["channelId"]}'>{$row['title']}</option>";

			$replacements = [
				"videoid" => $Id,
				"channelid" => $this -> Props["channelId"],
				"userid" => $_COOKIE['userId'],
				"userNick" => $MySqlI -> query("SELECT `nickname` FROM `{$tablestart}users` WHERE `userid` = " . 2 . ";") -> fetch_assoc()["nickname"],
				"channelsoptions" => $options
			];

			$MySqlI -> close();

			foreach ($replacements as $key => $value)
				$form = str_replace("[" . strtoupper($key) . "]", $value,$form);

			return $form;
		}
	}
?>