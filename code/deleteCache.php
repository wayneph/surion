<?php namespace presentation;
$location="/tmp";
$findTemplate = "jc.json";
$files = scandir($location);
echo("<br>Array:pageArray(".__LINE__.")<br><pre>"); print_r($files); echo("</pre><hr>");
for($n=0;$n<count($files);$n++){
    if(substr($files[$n],-7)==$findTemplate){
        echo("<br>Found -- {$files[$n]}");
        unlink($location."/".$files[$n]);
    }
}