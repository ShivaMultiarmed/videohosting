<?php
	require("common.php");

	class Page
	{
		function __construct($props)
		{
			$this -> Props = $props;
		}
		function Init()
		{
			switch ($this -> Props["type"])
			{
				case "channel":
					$this -> Channel = new Channel("channel",$this -> Props["channelnick"]);
				break;
			}
		}
		function ConstructPage()
		{		
			global $Titles;

			$wholepage = file_get_contents("templates/page.html");
			
			$Replacements = [
				"contenttype" => $this -> Props["type"],
				"headlinks" => $this -> createLinks(), // links in <head>
				"currentheader" => $this -> ConstructCurrentHeader(),
				"profilemenu" => $this -> CreateProfileMenu(),
				"maincontent" => $this -> ConstructMainContent(),
				"pagetitle" => $Titles[$this -> Props["type"]][$this -> Props["channelNick"]]
			];

			foreach ($Replacements as $key => $value)
			{
				$wholepage = str_replace("[" . strtoupper($key) . "]", $value, $wholepage);
			}

			$this -> WholePage = $wholepage;
		}
		function CreateProfileMenu()
		{
			global $host, $user, $pass, $dbstart;

			if (!isset($_COOKIE["userId"]))
			{
				$menu = "<a style='color:#ffffff' href='/?type=auth&authpagetype=login'>Login</a>";
			}
			else
			{
				$MySqlI = new mysqli($host, $user, $pass, $dbstart."main");
				$nickname = $MySqlI -> query("SELECT `nickname` FROM `users` WHERE `userid` = {$_COOKIE['userId']};") -> fetch_assoc()["nickname"];
				$menu = "<a style='color:#ffffff' href='/?type=user&userpagetype=profile&nickname={$nickname}'>Profile</a>";
				$menu .= "<a style='color:#ffffff' href='/?type=auth&authpagetype=logout'>Log out</a>";
				$MySqlI -> close();
			}

			return $menu;
		}
		function createLinks() // return links in <head> (css, js ...)
		{
			$link = "";
			$linksUrls = [
				"video" => [["css", "SingleVideo"], ["js", "player"], ["js", "comments"]],
				"channel" => [["js","channel"]],
				"auth" => [["js", "auth"], ["css", "Auth"]],
				"user" => [["js", "channel"]]
			];
			if (array_key_exists($this -> Props["type"], $linksUrls)){
				foreach ($linksUrls[$this -> Props["type"]] as $file)
				{
				
						switch($file[0])
						{
							case "js":
								$links .= "<script type=\"text/javascript\" src=\"js/" . $file[1] ."." . $file[0] . "\"></script>";
							break;
							case "css":
								$links .= "<link rel=\"stylesheet\" href=\"Css/"  . $file[1] . "." .$file[0]. "\" />";
							break;
						}
				
				}
			}
			return $links;
		}
		function ConstructCurrentHeader()
		{
			global $host, $user, $pass, $dbstart;

			$MySqlI = new mysqli($host, $user, $pass, $dbstart."main");

			if (file_exists("templates/blocks/headers/" . $this -> Props["type"] . ".html")) 
			{
				$curheader = file_get_contents("templates/blocks/headers/" . $this -> Props["type"] . ".html");
				
				switch($this -> Props["type"])
				{
					case "channel":
						$replacements = [
							"channeltitle" => $this -> Channel -> Props["title"],
							"subnum" =>  $this -> Channel -> Props["subnum"],
							"channelid" => $this -> Channel -> Props["channelId"],
							"substatus" => ($MySqlI -> query("SELECT * FROM `subscriptions` WHERE `channelId` = " . $this -> Channel -> Props['channelId'] . " AND `userId` = {$_COOKIE['userId']};") -> num_rows == 1) ? "yes" : "no",
							"btnsubstatus" => ($MySqlI -> query("SELECT * FROM `subscriptions` WHERE `channelId` = " . $this -> Channel -> Props['channelId'] . " AND `userId` = {$_COOKIE['userId']};") -> num_rows == 1) ? "Subscribed" : "Subscribe"
						];
					break;
				}
				
				foreach ($replacements as $key => $value)
				{
					$curheader = str_replace("[" . strtoupper($key) . "]", $value, $curheader);
				}
			}
			else
			{
				$curheader = "";
			}

			$MySqlI -> close();

			return $curheader;
		}
		function ConstructMainContent()
		{
			$contentobj = new MainContent($this -> Props);
			$contentobj -> ConstructMainContent();
			return $contentobj -> MainContent;
		}
		
	}
?>