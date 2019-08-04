<?php
	ini_set("display_errors", 1);
	ini_set("display_startup_errors", 1);
	error_reporting(E_ALL);

	session_start();

	$basepath	  = "/home/earu/";
	$userfilepath  = $basepath . "/cloud-users.json";
	$blacklistpath = $basepath . "/ip-blacklist.json";
	$userfile	  = fopen($userfilepath, "r") or die("Unable to get list of users");
	$blacklistfile = fopen($blacklistpath, "r") or die("Unable to get ip blacklist");
	$users		 = json_decode(fread($userfile,filesize($userfilepath)))->Users;
	$blacklistips  = json_decode(fread($blacklistfile,filesize($blacklistpath)))->IPs;
	fclose($userfile);
	fclose($blacklistfile);

	function GetIP()
	{
		if (!empty($_SERVER["HTTP_CLIENT_IP"]))
			return $_SERVER["HTTP_CLIENT_IP"];
		elseif (!empty($_SERVER["HTTP_X_FORWARDED_FOR"]))
			return $_SERVER["HTTP_X_FORWARDED_FOR"];
		else
			return $_SERVER["REMOTE_ADDR"];
	}

	function IsBlackListed($adr)
	{
		global $blacklistips;
		foreach($blacklistips as $ip)
			if($ip === $adr)
				return true;

		return false;
	}

	if(IsBlackListed(GetIP()))
	{
		include("heythere.html");
		return;
	}

	function ZipFolder($path)
	{
		$zipname = basename($path) . ".zip";
		$destination = dirname($path) . $zipname;
		if (!extension_loaded("zip") || !file_exists($path))
			return false;

		$zip = new ZipArchive();
		if (!$zip->open($destination, ZIPARCHIVE::CREATE))
			return false;

		if (is_dir($path) === true)
		{
			$files = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator($path),
				RecursiveIteratorIterator::SELF_FIRST
			);

			foreach ($files as $file)
			{
				// Ignore "." and ".." folders
				if(in_array(substr($file, strrpos($file, "/") + 1),[ ".", ".." ]))
					continue;

				$file = realpath($file);
				if (is_dir($file) === true)
					$zip->addEmptyDir(str_replace($path . "/", "", $file . "/"));
				else if (is_file($file) === true)
					$zip->addFromString(str_replace($path . "/", "", $file), file_get_contents($file));
			}
		}
		else if (is_file($path) === true)
		{
			$zip->addFromString(basename($path), file_get_contents($path));
		}

		if($zip->close())
			return $zipname;
		else
			return false;
	}

	function GetUserCloudPath()
	{
		global $basepath;
		return $basepath . "Cloud/" . $_SESSION["Username"];
	}

	function GetFiles()
	{
		return scandir($_SESSION["CurrentDirectory"]);
	}

	function VerifyCredentials()
	{
		global $users;
		if(isset($_POST["Email"]) && isset($_POST["Password"]))
		{
			foreach($users as $user)
			{
				$_SESSION["LoggedIn"] = $_POST["Email"] === $user->Email && password_verify($_POST["Password"],$user->Password);
				if($_SESSION["LoggedIn"])
				{
					$_SESSION["Username"] = $user->Name;

					// Do something

					break;
				}
			}

			if(!$_SESSION["LoggedIn"])
				$_SESSION["WrongCredentials"] = true;
		}
		else
		{
			$_SESSION["LoggedIn"] = false;
			unset($_SESSION["WrongCredentials"]);
		}
	}

	if(!isset($_SESSION["LoggedIn"]) || !$_SESSION["LoggedIn"])
		VerifyCredentials();

	if(isset($_POST["Disconnect"]))
	{
		$_SESSION["LoggedIn"] = false;
		unset($_SESSION["Username"]);
	}

	if($_SESSION["LoggedIn"])
	{
		$redirect = htmlspecialchars($_GET['ref']); //fixme

		if($redirect)
			header("Location: /" . $_GET['ref']);
		else
			header("Location: /panel/");
		exit();
	}
	else
	{
		include("index.html");
	}
?>