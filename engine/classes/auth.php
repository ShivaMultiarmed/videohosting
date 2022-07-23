<?php
	abstract class Auth
	{
		static function loginForm() // displays auth form
		{
			$form = file_get_contents("templates/blocks/content/auth/login.html");
			return $form;
		}
		static function login($nickname, $password) // authentification
		{
			global $host, $user, $pass, $dbstart;

			$MySqlI = new mysqli($host, $user, $pass, $dbstart."main");

			$sql = $MySqlI -> query("SELECT `password`, `userid` FROM `{$tablestart}users` WHERE `nickname` = '{$nickname}';");
			
			$userinfo = $sql -> fetch_assoc();
			
			$isErr = false;

			if ($sql -> num_rows == 0)
				$msg = "User is not found.";			
			else if ($password != $userinfo["password"])
				$msg = "Password is not correct";
			else
			{
				$userid = $sql -> fetch_assoc()["userid"];
				setrawcookie("userId", trim(htmlspecialchars($userinfo["userid"])), time()+60*60*24*7, "/", "freespace.byethost7.com"); 
				$msg = "You successfuly logged in.";
				$isErr = true;
			}
			$MySqlI -> close();
			return ["msg" => $msg, "isErr" => $isErr];
		}
		static function signUpForm()
		{
			$form = file_get_contents("templates/blocks/content/auth/signUp.html");
			return $form;
		}
		static function signUp($values)
		{
			global $host, $user, $pass, $dbstart;

			$MySqlI = new mysqli($host, $user, $pass, $dbstart."main");

			$msgColor = "red";

			if ($MySqlI -> query("SELECT `userid` FROM `users` WHERE `nickname` = '{$values['nickname']}' OR `email` = '{$values['email']}';") -> num_rows == 1)
				$msg = "User with such email or (and) nickname already exists.";

			else
			{
				foreach ($values as $key => $val)
				{
					$values[$key] = trim(htmlspecialchars($val));
					if (!is_numeric($values[$key]))
						$values[$key] = '"' . $values[$key] . '"';
				}

				$values = ["userid" => "NULL"] + $values;

				$valuesstring = implode(",",$values);
				
				$MySqlI -> query("INSERT INTO `{$tablestart}users` VALUES({$valuesstring});");

				$msg = "You successfully signed up";
				$msgColor = "green";
			}

			$MySqlI -> close();

			return ["msg" => $msg, "msgColor" => $msgColor];
		}
		static function logOut()
		{
			setcookie("userId", "", time()-1, "/", "freespace.byethost7.com");
			header("Location: /");
		}
	}
?>