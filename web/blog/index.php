<?php

function LoadArticles()
{
    $files = array_reverse(scandir("./articles"));
    foreach($files as $file)
    {
        if($file == ".." || $file == ".") continue;

        $path = "./articles/" . $file;
        $handle = fopen($path,"r");
        $html = fread($handle,filesize($path));
        fclose($handle);

        $editdate = "Last edited on " . date("M d Y H:i:s", filectime($path));
        echo("<article>" . $html . "<span class=\"last-edited\">" . $editdate . "</span></article><div class=\"separator\"></div>");
    }
}

include("index.html");
?>