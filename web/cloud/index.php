<?php
    session_start();

    $basepath     = "/home/earu/";
    $userfilepath = $basepath . "/cloud-users.json";
    $userfile     = fopen($userfilepath, "r") or die("Unable to get list of users");
    $users        = json_decode(fread($userfile,filesize($userfilepath)))->Users;
    fclose($myfile);

    if(!isset($_SESSION["CurrentDirectory"]))
    {
        $_SESSION["CurrentDirectory"] = $basepath . "/Cloud";
        if(!is_dir($_SESSION["CurrentDirectory"]))
            mkdir($_SESSION["CurrentDirectory"],0777,true);
    }

    function GetUserCloudPath()
    {
        global $basepath;
        return $basepath . "Cloud/" . $_SESSION["Username"];
    }

    function ChangeDirectory($dir)
    {
        $_SESSION["CurrentDirectory"] = GetUserCloudPath() . "/" . $dir;
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
                $_SESSION["LoggedIn"] = $_POST["Email"] === $user->Email && $_POST["Password"] === $user->Password;
                if($_SESSION["LoggedIn"])
                {
                    $_SESSION["Username"] = $user->Name;
                    $cloudpath = GetUserCloudPath();
                    $_SESSION["CurrentDirectory"] = $cloudpath;

                    if(!is_dir($cloudpath))
                        mkdir($cloudpath, 0777, true);

                    break;
                }
            }
        }
        else
        {
            $_SESSION["LoggedIn"] = false;
        }
    }

    function DisplayProperBaseDirectory()
    {
        echo($_SESSION["Username"] . "'s Cloud");
    }

    function DisplayProperDirectory()
    {
        $cur = $_SESSION["CurrentDirectory"];
        echo(substr($cur,strlen(GetUserCloudPath()) + 1));
    }

    function DisplayFiles($files)
    {
	    echo("<div class=\"col-lg-12\">");
        $i = 0;
        foreach($files as $f)
        {
            if($f === ".") continue;
            if($_SESSION["CurrentDirectory"] === GetUserCloudPath() && $f === "..") continue;

            $color = $i % 2 == 0 ? "#ffffff" : "#eeeeee";
            $i++;
            $path = $_SESSION["CurrentDirectory"] . "/" . $f;
            echo("<div style=\"background-color:" . $color . ";\" class=\"row file-row\">");
                echo("<div class=\"file-row-category col-lg-4\">" . $f . "</div>");
                echo("<div class=\"file-row-category col-lg-4\">" . date("m/d/Y H:i:s", filectime($path)) . "</div>");
                $filesize = filesize($path); // bytes
                $filesize = round($filesize / 1024, 2);
                echo("<div class=\"file-row-category col-lg-2\">" . $filesize . "KB </div>");
                $ext = is_dir($path) ? "folder" : pathinfo($path,PATHINFO_EXTENSION);
                echo("<div class=\"file-row-category col-lg-2\">" . $ext . "</div>");
            echo("</div>");
	    }
	    echo("</div>");
    }

    if(!isset($_SESSION["LoggedIn"]) || !$_SESSION["LoggedIn"])
        VerifyCredentials();

    if(isset($_POST["Disconnect"]))
    {
        $_SESSION["LoggedIn"] = false;
        unset($_SESSION["Email"]);
        unset($_SESSION["Password"]);
    }

    if($_SESSION["LoggedIn"])
        include("index.html");
    else
        include("login.html");
?>