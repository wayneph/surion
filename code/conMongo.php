<?php
$callerRoot=$_SERVER['DOCUMENT_ROOT'];
require $callerRoot.'/vendor/autoload.php';
use MongoDB;
$mongoUriMain = "mongodb://user94ca86:24d692i1FoiKob15a73@cluster-pgrs4-0-eu-west-1-scalabledbs.cloudstrap.io:27004,cluster-pgrs4-1-eu-west-1-scalabledbs.cloudstrap.io:27004,cluster-pgrs4-2-eu-west-1-scalabledbs.cloudstrap.io:27004/pg-app-1-eu-c01uo35iwtpg8zae0xv5he36dxavpz?replicaSet=pgrs4&ssl=true";
//$mongoUriAdded = "cluster-pgrs4-1-eu-west-1-scalabledbs.cloudstrap.io:27004";
//$agglomerated="$mongoUriMain,$mongoUriAdded";
//$replicas="pgrs4&readPreference=primary&connectTimeoutMS=10000&authSource=pg-app-1-eu-c01uo35iwtpg8zae0xv5he36dxavpz&authMechanism=SCRAM-SHA-1&3t.uriVersion=3&3t.connection.name=pgrs4&3t.databases=pg-app-1-eu-c01uo35iwtpg8zae0xv5he36dxavpz&3t.alwaysShowAuthDB=true&3t.alwaysShowDBFromUserRole=true&3t.sslTlsVersion=TLS";
// $mongoReplicas = array(
//     "hv200011-1-eu-mdb.cloudstrap.io:27004",
//     "hv200013-1-eu-mdb.cloudstrap.io:27004",
//     "hv200018-1-eu-mdb.cloudstrap.io:27004");
try {
    $mongo = new MongoDB\Driver\Manager($mongoUriMain);
    $mongo->executeCommand('test', new MongoDB\Driver\Command(['ping' => 1]));
    // echo("<br>Array:vvaarr(".__LINE__."({}))<br><pre>"); print_r($mongoManager); echo("</pre><hr>");
    // echo("<hr>");
}
catch (Throwable $e) {
    // catch throwables when the connection is not a success
    echo "<br>ERR::Captured Throwable for connection : " . $e->getMessage() . PHP_EOL;
}
// This $filter will return any id's qualing to 2 but what if we want all the id's above 0.
// $filter = ['id' => 2];
// This is how we would do this.
echo("<br>continuing");
//'_id' => 'JGeaSvnZKT'
$filter = [];
$options = [];
   //'projection' => ['_id' => 0],
//];
$query = new MongoDB\Driver\Query($filter, $options);
$rows = $mongo->executeQuery('pg-app-1-eu-c01uo35iwtpg8zae0xv5he36dxavpz.Camera', $query);
foreach($rows as $r){
    print_r("<br>".$r);
 }
echo("<br>Array:vvaarr(".__LINE__."({}))<br><pre>"); print_r($rows); echo("</pre><hr>");


