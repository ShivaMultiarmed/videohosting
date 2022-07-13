<?php
	class User 
	{
		function __construct($Id)
		{
			global $host, $user, $pass, $dbstart, $tablestart;

			$MySqlI = new mysqli($host, $user, $pass, $dbstart."main");
			
			$this -> Props = $MySqlI -> query("SELECT * FROM `{$tablestart}users` WHERE userid = {$Id};") -> fetch_assoc();

			$MySqlI -> close();
		}
		function GetSubscriptions ()
		{
			global $host, $user, $pass, $dbstart, $tablestart;

			$MySqlI = new mysqli($host, $user, $pass, $dbstart."main");

			$subsquery = <<<SQL
			SELECT `channels`.`channelId`, `channels`.title, channels.subnum, channels.channelNick FROM `{$tablestart}channels` 
			INNER JOIN `subscriptions` 
			ON `channels`.`channelid` = `subscriptions`.`channelid`
			WHERE `subscriptions`.`userid` = [USERID];
SQL;

			$subs = $MySqlI -> query(str_replace("[USERID]",$_COOKIE['userId'],$subsquery));

			$MySqlI -> close();

			return $subs;
		}
		function DisplaySubscriptions()
		{
			global $host, $user, $pass, $dbstart, $tablestart;

			$MySqlI = new mysqli($host, $user, $pass, $dbstart."main");

			$tpl = file_get_contents("templates/blocks/smallblocks/smallchannel.html");

			$subs = $this -> GetSubscriptions();

			while($subrow = $subs -> fetch_assoc())
			{
				$onesub = $tpl;

				$replace = [
					"subnum" => $subrow["subnum"],
					"channelId" => $subrow["channelId"],
					"channeltitle" => $subrow["title"], 
					"channelnick" => $subrow["channelNick"],
					"substatus" => ($MySqlI -> query("SELECT * FROM `subscriptions` WHERE `channelId` = " . $subrow['channelId'] . " AND `userId` = {$_COOKIE['userId']};") -> num_rows == 1) ? "yes" : "no",
					"btnsubstatus" => ($MySqlI -> query("SELECT * FROM `subscriptions` WHERE `channelId` = " . $subrow['channelId'] . " AND `userId` = {$_COOKIE['userId']};") -> num_rows == 1) ? "Subscribed" : "Subscribe"
				];

				foreach($replace as $key => $value)
				{
					$onesub = str_replace("[" . strtoupper($key) . "]", $value, $onesub);
				}

				$subscriptions .= $onesub;
			}

			$MySqlI -> close();

			return $subscriptions;
		}
		function DisplayPlaylists()
		{
			global $host, $user, $pass, $dbstart, $tablestart;

			$MySqlI = new mysqli($host, $user, $pass, $dbstart."main");

			$tpl = file_get_contents("templates/blocks/smallblocks/smallplaylist.html");

			$playlistsquery = $MySqlI -> query("SELECT * FROM `{$tablestart}playlists` WHERE userId = '{$this -> Props["userid"]}';");

			while ($singleplaylist = $playlistsquery -> fetch_assoc())
			{
				$htmlplaylist = $tpl;

				$replacements = [
					"playlistid" => $singleplaylist["playlistId"],
					"playlisttitle" => $singleplaylist["title"]
				];

				foreach ($replacements as $key => $value)
				{
					$htmlplaylist = str_replace("[" . strtoupper($key) . "]", $value, $htmlplaylist);
				}

				$playlists .= $htmlplaylist;

			}

			$MySqlI -> close();

			return $playlists;
		}
		function ShowProfile($nickname)
		{
			$profile = file_get_contents("templates/blocks/content/profile.html");

			global $host, $user, $pass, $dbstart, $tablestart;

			$MySqlI = new mysqli($host, $user, $pass, $dbstart."main");

			$userprops = $MySqlI -> query("SELECT * FROM `{$tablestart}users` WHERE `nickname` = '{$nickname}';") -> fetch_assoc();

			$MySqlI -> close();

			foreach ($userprops as $key => $value)
				$profile = str_replace("[" . strtoupper($key) . "]", $value, $profile);

			return $profile;
		}
	}
?>