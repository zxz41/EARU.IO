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

    if(!isset($_SESSION["CurrentDirectory"]))
    {
        $_SESSION["CurrentDirectory"] = $basepath . "/Cloud";
        if(!is_dir($_SESSION["CurrentDirectory"]))
            mkdir($_SESSION["CurrentDirectory"],0777,true);
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
                    $cloudpath = GetUserCloudPath();
                    $_SESSION["CurrentDirectory"] = $cloudpath;

                    if(!is_dir($cloudpath))
                        mkdir($cloudpath, 0777, true);

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

    function HandleUploadedFiles()
    {
        foreach($_FILES as $uploadedfile)
        {
            $path = BuildPath(trim($uploadedfile["name"]));
            if($path !== false)
                move_uploaded_file($uploadedfile["tmp_name"],$path);
        }
    }

    function IsNullOrEmptyString($str)
    {
        return (!isset($str) || trim($str) === "");
    }

    function StringStartsWith($haystack, $needle)
    {
        return strpos($haystack,$needle) === 0;
    }

    function DeleteDirectory($path)
    {
        $blacklist = [ "." => true, ".." => true ];
        if(isset($blacklist[basename($path)])) return;

        if(is_dir($path))
        {
            $files = scandir($path);
            foreach($files as $file)
                DeleteDirectory($path . "/" . $file);
            rmdir($path);
        }
        else
        {
            unlink($path);
        }
    }

    function DisplayProperBaseDirectory()
    {
        echo(htmlentities($_SESSION["Username"] . "'s Cloud"));
    }

    function DisplayProperDirectory()
    {
        $cur = $_SESSION["CurrentDirectory"];
        echo(htmlentities(str_replace("\\","/",substr($cur,strlen(GetUserCloudPath()) + 1))));
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

            $path     = $_SESSION["CurrentDirectory"] . "/" . $f;
            $filesize = filesize($path); // bytes
            $filesize = round($filesize / 1024, 2);
            $type      = is_dir($path) ? "folder" : mime_content_type($path);

            echo("<div style=\"background-color:" . $color . ";\" class=\"row file-row\">");
                echo("<div class=\"file-row-category col-sm-4\">" . htmlentities($f)                      . "</div>");
                echo("<div class=\"file-row-category col-sm-4\">" . date("m/d/Y H:i:s", filectime($path)) . "</div>");
                echo("<div class=\"file-row-category col-sm-2\">" . $filesize                          . "KB </div>");
                echo("<div class=\"file-row-category col-sm-2\">" . $type                                 . "</div>");
            echo("</div>");
	    }
	    echo("</div>");
    }

    function LoadModals()
    {
        $files = scandir("modals");
        $blacklist = [ "." => true, ".." => true, "base_modal.html" => true];
        foreach($files as $f)
        {
            if(isset($blacklist[$f])) continue;

            $path       = "modals/" . $f;
            $modalid    = pathinfo($path,PATHINFO_FILENAME);
            $file       = fopen($path,"r");
            $modalbody  = fread($file,filesize($path));
            $modaltitle = ucfirst(explode("_",$modalid)[0]) . " Content";
            fclose($file);

            include("modals/base_modal.html");
        }
    }

    function IsTraversal($path)
    {
        $cloudpath = GetUserCloudPath();
        return !StringStartsWith($path,$cloudpath);
    }

    function BuildPath($input)
    {
        $path = realpath($_SESSION["CurrentDirectory"] . "/" . $input);
        $new = false;
        if($path === false)
        {
            $parts = explode("/",str_replace("\\","/",$input));
            $len = count($parts);
            $new = $parts[$len - 1];
            unset($parts[$len - 1]);
            $path = realpath($_SESSION["CurrentDirectory"] . "/" . implode("/",$parts));

            if($path === false) $new = false;
        }

        $path = str_replace("\\","/",$path);
        if(IsTraversal($path)) return false;

        if($new !== false)
            return $path . "/" . $new;
        else
            return $path;
    }

    $postcallbaks = [
        "Download" => function()
        {
            if(!IsNullOrEmptyString($_POST["SelectedFile"]))
            {
                $path = BuildPath($_POST["SelectedFile"]);
                if($path !== false && file_exists($path))
                {
                    $iszipfolder = false;
                    if(is_dir($path))
                    {
                        return true; //temporary until i fix zipping folders

                        $zipname = ZipFolder($path);
                        if($zipname != false)
                        {
                            $path = BuildPath($zipname);
                            $_POST["SelectedFile"] = $zipname;
                            $iszipfolder = true;
                        }
                        else
                        {
                            return true;
                        }
                    }

                    $contentype = mime_content_type($path);
                    header("Content-disposition: attachment; filename=" . $_POST["SelectedFile"]);
                    header("Content-type: " . $contentype);
                    readfile($path);

                    if($iszipfolder)
                        unlink($_POST["SelectedFile"]);

                    return false;
                }
            }

            return true;
        },
        "Create" => function()
        {
            if(!IsNullOrEmptyString($_POST["Name"]) && !IsNullOrEmptyString($_POST["Type"]))
            {
                $path = BuildPath(trim($_POST["Name"]));
                $isfile = $_POST["Type"] === "File";

                if($path === false) return true;
                if(!file_exists($path))
                {
                    if($isfile)
                        fopen($path,"w");
                    else
                        mkdir($path,0777,true);
                }
                else
                {
                    if(!$isfile && !is_dir($path))
                        mkdir($path,0777,true);

                    if($isfile && is_dir($path))
                        fopen($path,"w");
                }
            }

            return true;
        },
        "Rename" => function()
        {
            if(!IsNullOrEmptyString($_POST["SelectedFile"]) && !IsNullOrEmptyString($_POST["NewName"]))
            {
                $oldpath = BuildPath($_POST["SelectedFile"]);
                $newpath = BuildPath(trim($_POST["NewName"]));
                if($oldpath != false && $newpath != false && file_exists($oldpath))
                    rename($oldpath,$newpath);
            }

            return true;
        },
        "ChangeDirectory" => function()
        {
            if(!IsNullOrEmptyString($_POST["DirectoryName"]))
            {
                $dir = $_POST["DirectoryName"];

                if($dir === ".") return true;
                if($dir === "..")
                {
                    $dir = str_replace("\\","/",dirname($_SESSION["CurrentDirectory"]));
                    if(!IsTraversal($dir))
                        $_SESSION["CurrentDirectory"] = $dir;
                }
                else
                {
                    $path = BuildPath($dir);
                    if($path != false)
                        $_SESSION["CurrentDirectory"] = $path;
                }
            }

            return true;
        },
        "Remove" => function()
        {
            if(!IsNullOrEmptyString($_POST["SelectedFile"]))
            {
                $path = BuildPath($_POST["SelectedFile"]);
                if($path != false && file_exists($path))
                {
                    if(!is_dir($path))
                        unlink($path);
                    else
                        DeleteDirectory($path);
                }
            }

            return true;
        },
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
        HandleUploadedFiles();
        $load = HandleActions();

        if($load) include("index.html");
    }
    else
    {
        include("login.html");
    }
?>