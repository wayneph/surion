<?php namespace presentation;
/* check for cookies -- YOU MUST EDIT THE COOKIE NAME */
$cookieName="escrowadmin-pwa";
if(isset($_COOKIE[$cookieName]))
{
    $cArray=json_decode($_COOKIE[$cookieName], true);
    echo("<br>Array:<u><b>COOKIE</b></u><br><pre>"); print_r($cArray); echo("</pre><hr>");
    exit();
}
