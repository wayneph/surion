<?php namespace presentation;
$file=$_SERVER['DOCUMENT_ROOT']."/include/appControl/Bl.php";
include_once($file);
$callerRoot=$_SERVER['DOCUMENT_ROOT'];
require $callerRoot.'/vendor/autoload.php';
use BL\BL;
use Parse\ParseClient;

class Present extends BL
{
    private $myName="index";
    public function __construct()
    {
        $sureApplicationId = "r3xfic94kiMe9HM8P38OEL7pafOECDokJiVpQEra";
        $sureRestApiKey = "HnXojK8VD0vbIV2LrRdA0oD5rOoeFiQ3aECO9aJE";
        $sureMasterKey = "W8zdB700c2uLIh6a913P4bUczCJrQKoTaOaiQEJE";
        $sureClientKey = "eK0GlJ95XedoolrxB0XxXZjwraQJiNOEoM5EaBCP";
        $sureServer = "https://pg-app-c01uo35iwtpg8zae0xv5he36dxavpz.scalabl.cloud/1/";
        /* Good code
            ParseClient::initialize(
            'r3xfic94kiMe9HM8P38OEL7pafOECDokJiVpQEra',
            'HnXojK8VD0vbIV2LrRdA0oD5rOoeFiQ3aECO9aJE',
            'W8zdB700c2uLIh6a913P4bUczCJrQKoTaOaiQEJE'
          );
          ParseClient::setServerURL('https://pg-app-c01uo35iwtpg8zae0xv5he36dxavpz.scalabl.cloud/', '1');
        */

        //ParseClient::initialize( $app_id, $rest_key, $master_key );
        // ParseClient::initialize($sureApplicationId, $sureClientKey, $sureMasterKey );
        // ParseClient::setServerURL($sureServer,'parse');
        ParseClient::initialize(
            $sureApplicationId,
            $sureRestApiKey,
            $sureMasterKey
          );
        ParseClient::setServerURL('https://pg-app-c01uo35iwtpg8zae0xv5he36dxavpz.scalabl.cloud/', '1');
        $health = ParseClient::getServerHealth();
        echo("<br>Array:vvaarr(".__LINE__."({$this->myName}))<br><pre>"); print_r($health); echo("</pre><hr>");
    }
}
new Present;


