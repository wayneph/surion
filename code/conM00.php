<?php
$callerRoot=$_SERVER['DOCUMENT_ROOT'];
require $callerRoot.'/vendor/autoload.php';
//use MongoDB;
$mongoUriMain = "mongodb://user94ca86:24d692i1FoiKob15a73@cluster-pgrs4-0-eu-west-1-scalabledbs.cloudstrap.io:27004,cluster-pgrs4-1-eu-west-1-scalabledbs.cloudstrap.io:27004,cluster-pgrs4-2-eu-west-1-scalabledbs.cloudstrap.io:27004/pg-app-1-eu-c01uo35iwtpg8zae0xv5he36dxavpz?replicaSet=pgrs4&ssl=true";
// $client = new MongoDB\Client($mongoUriMain);
$mongo = new MongoDB\Driver\Manager($mongoUriMain);
$mongo->executeCommand('test', new MongoDB\Driver\Command(['ping' => 1]));
echo("<br>Array:mongo(".__LINE__."({}))<br><pre>"); print_r($mongo); echo("</pre><hr>");
$client = new MongoDB\Client($mongo);
$collection = $client->pgrs4->Camera;
$cursor = $collection->find(
    [
    ],
    [
       'limit' => 5,
       'projection' => [
             'Name' => 1,
             'userName' => 1,
             'Location' => 1,
       ],
    ]
);
 foreach ($cursor as $restaurant) {
    var_dump($restaurant);
}




// $mongoUriMain = "mongodb://user94ca86:24d692i1FoiKob15a73@cluster-pgrs4-0-eu-west-1-scalabledbs.cloudstrap.io:27004,cluster-pgrs4-1-eu-west-1-scalabledbs.cloudstrap.io:27004,cluster-pgrs4-2-eu-west-1-scalabledbs.cloudstrap.io:27004/pg-app-1-eu-c01uo35iwtpg8zae0xv5he36dxavpz?replicaSet=pgrs4&ssl=true";
// $client = new MongoDB\Client($mongoUriMain);
// $collection = $client->pg-app-1-eu-c01uo35iwtpg8zae0xv5he36dxavpzs->restaurants;
// $cursor = $collection->find(
//     [
//     ],
//     [
//        'limit' => 5,
//        'projection' => [
//              'Name' => 1,
//              'userName' => 1,
//              'Location' => 1,
//        ],
//     ]
// );
//  foreach ($cursor as $restaurant) {
//     var_dump($restaurant);
// }
