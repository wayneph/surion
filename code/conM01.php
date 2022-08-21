<?php
$callerRoot=$_SERVER['DOCUMENT_ROOT'];
require $callerRoot.'/vendor/autoload.php';
use MongoDB\Client;
$mongoUriMain = "mongodb://user94ca86:24d692i1FoiKob15a73@cluster-pgrs4-0-eu-west-1-scalabledbs.cloudstrap.io:27004,cluster-pgrs4-1-eu-west-1-scalabledbs.cloudstrap.io:27004,cluster-pgrs4-2-eu-west-1-scalabledbs.cloudstrap.io:27004/pg-app-1-eu-c01uo35iwtpg8zae0xv5he36dxavpz?replicaSet=pgrs4&ssl=true";
$client = new Client($mongoUriMain);
$db_name = "pg-app-1-eu-c01uo35iwtpg8zae0xv5he36dxavpz";
$db = $client->$db_name;
$collection = $db->Camera;

$where = array();
$select_fields = array(
    'Name' => 1,
    'userName' => 1
);
$options = array(
    'projection' => $select_fields
);
$cursor = $collection->find($where, $options);   //This is the main line
$docs = $cursor->toArray();
echo("<br>Array:vvaarr(".__LINE__."({}))<br><pre>"); print_r($docs); echo("</pre><hr>");
//print_r($docs);
//===============================================================

// $callerRoot=$_SERVER['DOCUMENT_ROOT'];
// require $callerRoot.'/vendor/autoload.php';
// use MongoDB\Client;
// //use MongoDB\Driver\ServerApi;
// $mongoUriMain = "mongodb://user94ca86:24d692i1FoiKob15a73@cluster-pgrs4-0-eu-west-1-scalabledbs.cloudstrap.io:27004,cluster-pgrs4-1-eu-west-1-scalabledbs.cloudstrap.io:27004,cluster-pgrs4-2-eu-west-1-scalabledbs.cloudstrap.io:27004/pg-app-1-eu-c01uo35iwtpg8zae0xv5he36dxavpz?replicaSet=pgrs4&ssl=true";
// // $client = new MongoDB\Client($mongoUriMain);
// $client = new Client($mongoUriMain);
// echo("<br>Array:mongo(".__LINE__."({}))<br><pre>"); print_r($client); echo("</pre><hr>");
// exit();

// $client->database->collection->find([]);
// echo("<br>Array:mongo(".__LINE__."({}))<br><pre>"); print_r($client); echo("</pre><hr>");
// exit();



//

// //use MongoDB;
// $mongoUriMain = "mongodb://user94ca86:24d692i1FoiKob15a73@cluster-pgrs4-0-eu-west-1-scalabledbs.cloudstrap.io:27004,cluster-pgrs4-1-eu-west-1-scalabledbs.cloudstrap.io:27004,cluster-pgrs4-2-eu-west-1-scalabledbs.cloudstrap.io:27004/pg-app-1-eu-c01uo35iwtpg8zae0xv5he36dxavpz?replicaSet=pgrs4&ssl=true";
// // $client = new MongoDB\Client($mongoUriMain);
// $mongo = new MongoDB\Driver\Manager($mongoUriMain);
// $mongo->executeCommand('test', new MongoDB\Driver\Command(['ping' => 1]));
// echo("<br>Array:mongo(".__LINE__."({}))<br><pre>"); print_r($mongo); echo("</pre><hr>");

// foreach ($mongo->listDatabases() as $databaseInfo) {
//     var_dump($databaseInfo);
// }



// // $client = new MongoDB\Client($mongo);
// // $collection = $client->pgrs4->Camera;
// // $cursor = $collection->find(
// //     [
// //     ],
// //     [
// //        'limit' => 5,
// //        'projection' => [
// //              'Name' => 1,
// //              'userName' => 1,
// //              'Location' => 1,
// //        ],
// //     ]
// // );
// //  foreach ($cursor as $restaurant) {
// //     var_dump($restaurant);
// // }




// // // $mongoUriMain = "mongodb://user94ca86:24d692i1FoiKob15a73@cluster-pgrs4-0-eu-west-1-scalabledbs.cloudstrap.io:27004,cluster-pgrs4-1-eu-west-1-scalabledbs.cloudstrap.io:27004,cluster-pgrs4-2-eu-west-1-scalabledbs.cloudstrap.io:27004/pg-app-1-eu-c01uo35iwtpg8zae0xv5he36dxavpz?replicaSet=pgrs4&ssl=true";
// // // $client = new MongoDB\Client($mongoUriMain);
// // // $collection = $client->pg-app-1-eu-c01uo35iwtpg8zae0xv5he36dxavpzs->restaurants;
// // // $cursor = $collection->find(
// // //     [
// // //     ],
// // //     [
// // //        'limit' => 5,
// // //        'projection' => [
// // //              'Name' => 1,
// // //              'userName' => 1,
// // //              'Location' => 1,
// // //        ],
// // //     ]
// // // );
// // //  foreach ($cursor as $restaurant) {
// // //     var_dump($restaurant);
// // // }
