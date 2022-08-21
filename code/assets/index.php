<?php
    $addr="https://".$_SERVER['SERVER_NAME']."index.php";
    header("Location:$addr",301);
    exit();