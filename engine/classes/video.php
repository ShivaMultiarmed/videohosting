<?php
    class Video
    {
        public function __construct($channelId, $videoId)
        {
            global $host, $user, $pass, $dbstart;
            
            $Channels = new mysqli($host, $user, $pass, $dbstart."channels");
            
            $this -> Props = $Channels -> query("SELECT * FROM `channel{$channelId}` WHERE `id` = {$videoId};") -> fetch_assoc();

            $Channels -> close();

            $MySqlI = new mysqli($host, $user, $pass, $dbstart."main");

            $channelNick = $MySqlI -> query("SELECT `channelNick` FROM `channels` WHERE `channelId` = {$channelId};") -> fetch_assoc()["channelNick"];
            
            $MySqlI -> close();
            
            $this -> ThisChannel = new Channel("channel",$channelNick);            
        }
        public function viewVideo()
        {
            global $host, $user, $pass, $dbstart, $tablestart;

            $Channels = new mysqli($host, $user, $pass, $dbstart."channels");

			$Channels -> query("UPDATE `{$tablestart}channel" . $this -> ThisChannel -> Props["channelId"] . "` SET `views` = `views`+1 WHERE `id` = " . $this->Props["id"] . ";");
        
            $Channels -> close();

            $video = file_get_contents($Root."templates/blocks/content/video.html");

			$replacements = [
				"channelnick" => $this -> ThisChannel -> Props["channelNick"],
				"CHANNELTITLE" => $this -> ThisChannel -> Props["title"],
				"CHANNELID" => $this -> ThisChannel -> Props["channelId"],
				"videotitle" => $this -> Props["title"], 
				"likes" => $this -> Props["likes"],
				"dislikes" => $this -> Props["dislikes"], 
				"views" => $this -> Props["views"], 
				"videoid" => $this -> Props["id"], 
                "mainvidprops" => $this -> GetVidProps(),
                "comments" => $this -> GetComments(),
				"recommendations" => $this -> ThisChannel -> GetWholeVideos(),
				"userid" => $_COOKIE['userId'],
				"vidtools" => $this -> GetVidTools()
			];

			foreach ($replacements as $key => $value)
			{
				$video = str_replace("[" . strtoupper($key) . "]", $value, $video);
			}

            $History = new mysqli($host, $user, $pass, $dbstart."histories");

			if ($History -> query("SELECT * FROM `{$tablestart}{$_COOKIE['userId']}` WHERE videoId = {$this -> Props['id']} AND channelId = {$this -> ThisChannel -> Props['channelId']};") -> num_rows == 1)
				$History -> query("DELETE FROM `{$tablestart}{$_COOKIE['userId']}` WHERE videoId = {$this -> Props['id']} AND channelId = " . $this -> ThisChannel -> Props['channelId'] . ";");
            $History -> query("INSERT INTO `{$tablestart}{$_COOKIE['userId']}` VALUES ({$this -> Props['id']}, \"" . date('Y-m-d H:i:s') ."\", " . $this -> ThisChannel -> Props['channelId'] . ", NULL);");
			
			
			$History -> close();
			
			return $video;
        }
        function GetVidProps() // display likes, dislikes and so on
		{
			global $Root, $host, $user, $pass, $dbstart, $tablestart;

			$Channels = new mysqli($host, $user, $pass, $dbstart."channels");

			$mainvidprops = file_get_contents($Root."templates/blocks/smallblocks/MainVidProps.html");

			$videoProps = $Channels -> query("SELECT * FROM `{$tablestart}channel" . $this -> ThisChannel -> Props["channelId"] . "` WHERE `id` = {$this -> Props["id"]};") ->fetch_assoc();

			$replacements = [
				"channelnick" => $this -> ThisChannel -> Props["channelNick"],
				"CHANNELID" => $this -> ThisChannel -> Props["channelId"],
				"videotitle" => $this -> Props["title"], 
				"likes" => $this -> Props["likes"],
				"dislikes" => $this -> Props["dislikes"], 
				"views" => $this -> Props["views"], 
				"videoid" => $this -> Props["id"], 
				"userid" => $_COOKIE['userId'],
			];

			$Channels -> close();

			foreach ($replacements as $key => $value)
			{
				$mainvidprops = str_replace("[" . strtoupper($key) . "]", $value, $mainvidprops);
			}

			return $mainvidprops;
		}
        function GetComments()
		{
			global $Root, $host, $user, $pass, $dbstart, $tablestart;

			$MySqlI = new mysqli($host, $user, $pass, $dbstart."main");
			$Channels = new mysqli($host, $user, $pass, $dbstart."channels");

			$commentsQuery = $Channels -> query("SELECT * FROM `{$tablestart}comments" . $this ->ThisChannel -> Props["channelId"] ."` WHERE `videoid` = \"" . $this -> Props['id'] . "\" ORDER BY `datetime` DESC;");
			$num = $commentsQuery -> num_rows;
            $comments = "<div id='commentsHeader'><h6>{$num} Comments</h6></div>";
			$tpl = file_get_contents($Root . "templates/blocks/smallblocks/comment.html");
			if ($commentsQuery -> num_rows > 0)
			{
				while ($curcommsql = $commentsQuery -> fetch_assoc())
				{
					$curcomm = $tpl;

					if ($curcommsql["isUser"] == 1)
					{
						$nickortitle = $MySqlI -> query("SELECT `nickname` FROM `{$tablestart}users` WHERE `userid` = " . $curcommsql["userChannelId"] . ";") -> fetch_assoc()["nickname"];
						$isUser = "users";
					}
					else
					{
						$nickortitle = $MySqlI -> query("SELECT `title` FROM `{$tablestart}channels` WHERE `channelId` = " . $curcommsql["userChannelId"] . ";") -> fetch_assoc()["title"];
						$isUser = "channels";
					}	

					$replacements = [
						"userchannelid" => $curcommsql["userChannelId"],
						"nickortitle" => $nickortitle,
						"isuser" => $isUser,
						"text" => $curcommsql["text"],
						"datetime" => $curcommsql["datetime"]
					];

					if ($curcommsql["isUser"] == 1)
					{
						$replacements["authorlink"] = "?type=user&userpagetype=profile&nickname={$nickortitle}";
					}
					else
					{
						$replacements["authorlink"] = "?type=channel&channelnick={$this -> ThisChannel -> Props["channelNick"]}&pagenum=1";
					}

					foreach ($replacements as $key => $value)
					{
						$curcomm = str_replace("[" . strtoupper($key) . "]", $value, $curcomm);
					}
					$comments .= $curcomm;
				}
			}

			$MySqlI -> close();
			$Channels -> close();

			$form = $this -> getForm();

			$comments = $form . $comments;

			return $comments;
		}
        function getForm()
		{
			global $Root, $host, $user, $pass, $dbstart;

			$MySqlI = new mysqli($host, $user, $pass, $dbstart."main");

			$form = file_get_contents($Root . 'templates/blocks/smallblocks/commentForm.html');

			$channelsOptions = $MySqlI -> query("SELECT `channelId`,`title` FROM `{$tablestart}channels` WHERE `userId` = '{$_COOKIE['userId']}';");

			while($row = $channelsOptions -> fetch_assoc())
				$options .= "<option value='{$row["channelId"]}'>{$row['title']}</option>";

			$replacements = [
				"videoid" => $this -> Props['id'],
				"channelid" => $this -> ThisChannel -> Props["channelId"],
				"userid" => $_COOKIE['userId'],
				"userNick" => $MySqlI -> query("SELECT `nickname` FROM `{$tablestart}users` WHERE `userid` = {$_COOKIE['userId']};") -> fetch_assoc()["nickname"],
				"channelsoptions" => $options
			];

			$MySqlI -> close();

			foreach ($replacements as $key => $value)
				$form = str_replace("[" . strtoupper($key) . "]", $value,$form);

			return $form;
		}
		function GetVidTools()
		{
			$tools = file_get_contents($Root."templates/blocks/smallblocks/vidtools.html");

			$replacements = [
				"videoid" => $this -> Props["id"],
				"channelid" => $this -> ThisChannel -> Props["channelId"],
				"url" => $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]
			];

			foreach($replacements as $key => $value)
				$tools = str_replace("[" . strtoupper($key) . "]", $value, $tools);

			return $tools;
		}
    }
?>