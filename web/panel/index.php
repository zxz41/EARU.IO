<?php
    ini_set("display_errors", 1);
    ini_set("display_startup_errors", 1);
    error_reporting(E_ALL);

    session_start();

    $basepath      = "/home/earu/";
    $userfilepath  = $basepath . "/cloud-users.json";
    $blacklistpath = $basepath . "/ip-blacklist.json";
    $userfile      = fopen($userfilepath, "r") or die("Unable to get list of users");
    $blacklistfile = fopen($blacklistpath, "r") or die("Unable to get ip blacklist");
    $users         = json_decode(fread($userfile,filesize($userfilepath)))->Users;
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
	
	function DisplayHeader()
	{
		echo $_SESSION["Username"] . "'s Panel";
	}

    function IsNullOrEmptyString($str)
    {
        return (!isset($str) || trim($str) === "");
    }

    function StringStartsWith($haystack, $needle)
    {
        return strpos($haystack,$needle) === 0;
		// If needle is not a string, it is converted to an integer and applied as the ordinal value of a character.
		// This behavior is deprecated as of PHP 7.3.0, and relying on it is highly discouraged.
		// Depending on the intended behavior, the needle should either be explicitly cast to string,
		// or an explicit call to chr() should be performed.
    }

    $postcallbaks = [
        "ChangeCredentials" => function()
        {
            if($_SESSION["LoggedIn"])
            {
				global $users;
				// TODO: Fix escape patterns etc.
				$newname = $_POST["NewName"];

				if( isset($newname) )
				{
					foreach($users as $user)
					{
						if( $newname === $user->Name )
						{
							// TODO: There's no fancy system where two people can share the same name without
							// Invading each cloud folder. Just throw this very temp warning at them.
							echo "That name is already taken!";
							return true;
						}
					}
					echo "test";
					// TODO: Actually encode back into JSON.
				}
            }
            return true;
        }
    ];

    // Executes the FIRST action it finds (Multiple actions are undefined behavior)
    function HandleActions()
    {
        global $postcallbaks;
        foreach($postcallbaks as $name => $callback)
            if(isset($_POST[$name]))
                return $callback();

        return true;
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
        $load = HandleActions();

        if($load) include("index.html");
    }
    else
    {
        header("Location: /auth?ref=panel");
    }
?>