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

			$sql = $MySqlI -> query("SELECT `password` FROM `{$tablestart}users` WHERE `nickname` = '{$nickname}';");
			
			$isErr = false;

			if ($sql -> num_rows == 0)
				$msg = "User is not found.";			
			else if ($password != $sql -> fetch_assoc()["password"])
				$msg = "Password is not correct";
			else
			{
				setrawcookie("userId", trim(htmlspecialchars("2")), time()+60*60*24*7, "/", "freespace.byethost7.com"); // fix time for cookie (in seconds)
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

			foreach ($values as $key => $val)
			{
				$values[$key] = trim(htmlspecialchars($val));
				if (!is_numeric($values[$key]))
					$values[$key] = '"' . $values[$key] . '"';
			}

			$values = ["userid" => "NULL"] + $values;

			$valuesstring = implode(",",$values);
			
			$MySqlI -> query("INSERT INTO `{$tablestart}users` VALUES({$valuesstring});");

			$MySqlI -> close();
		}
	}
?>