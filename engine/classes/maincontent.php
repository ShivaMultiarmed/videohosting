<?php
	class MainContent
	{
		function __construct($props)
		{
			global $host, $user, $pass, $dbstart;
			
			$MySqlI = new mysqli($host, $user, $pass, $dbstart."main");

			$this -> Props = $props;

			$MySqlI -> close();
		}
		function ConstructMainContent()
		{

			switch ($this -> Props["type"])
			{
				case "channel":
					$this -> Channel = new Channel("channel", $this -> Props["channelnick"]);
					$mainContent = file_get_contents("templates/blocks/smallblocks/channelnav.html");
					$mainContent .= $this -> Channel -> GetWholeVideos();
				break;
				case "video":
					$this -> Channel = new Channel("channel", $this -> Props["channelnick"]);
					$mainContent = $this -> Channel -> GetVideo($this -> Props["videoid"]);
				break;
				case "subChannel":
					$this -> User = new User($_COOKIE['userId']);
					$this -> Channel = new Channel("subChannel",$this -> Props["channelnick"]);
					$mainContent = "<h1 id=\"PageTitle\">" . $Titles[$this->Props["type"]][$this->Props["channelnick"]] . "</h1>";
					$mainContent .= $this -> Channel -> GetWholeVideos();
				break;
				case "user":
					$this -> User = new User($_COOKIE['userId']);
					$mainContent = "<h1 id=\"PageTitle\">" . $Titles[$this->Props["type"]][$this->Props["userpagetype"]] . "</h1>";
					switch($this -> Props["userpagetype"])
					{
						case "subscriptions":
							$mainContent .= $this -> User -> DisplaySubscriptions();
						break;
						case "playlists":
							$mainContent .= $this -> User -> DisplayPlaylists();
						break;
						case "profile": 
							$mainContent .= $this -> User -> ShowProfile($this -> Props["nickname"]);
						break;
					}
				break;
				case "auth":
					$mainContent = "<h1 id=\"PageTitle\">" . $Titles[$this->Props["type"]][$this->Props["authpagetype"]] . "</h1>";
					
					switch($this -> Props["authpagetype"])
					{
						case "login":
							$mainContent .= Auth::loginForm();
						break;
						case "signup":
							$mainContent .= Auth::signUpForm();
						break;
					}
				break;
			}
			$this -> MainContent = $mainContent;
		}
	}
?>