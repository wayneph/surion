<?php namespace nanoAPI;
$callerRoot=$_SERVER['DOCUMENT_ROOT'];  /* apache type var */
require $callerRoot.'/vendor/autoload.php';
use GuzzleHttp\Client as GUZ;
use GuzzleHttp\Exception\ClientException;
/* other guzzle functionality -- not used here
use function GuzzleHttp\Promise\queue; -- queuing should not be used for this API
use function GuzzleHttp\Psr7\_parse_message; -- Not Used
use function GuzzleHttp\Psr7\_parse_request_uri; -- parsing should be done in precedent code
*/
class nano
{
    /* control vars */
    public $apiLastCall;

    public function apiGet(array $callArray)
    {
        $started=microtime(true);
        $client=new GUZ(['headers'=>['Authorization' => 'Bearer '.$callArray['bearer']],]);
        $this->apiLastCall=$callArray['call'];
        try{
            $r = $client->request("GET", $callArray['call'], ['http_errors' => true]);
            return $this->getSuccess($r, $started);
        } catch (ClientException $e) {
            $exception = $e->getResponse();
            return $this->getFailed($e,$started);
        }
    }

    public function apiPatch(array $callArray)
    {
        $started=microtime(true);
        $client=new GUZ(['headers'=>['Authorization' => 'Bearer '.$callArray['bearer'],'Content-Type' => 'application/json']]);
        $this->apiLastCall=$callArray['call'];
        try{
            $r = $client->request("PATCH", $this->apiLastCall, ['body' => $callArray['json'],'http_errors' => true]);
            return $this->getSuccess($r, $started);
        } catch (ClientException $e) {
            $exception = $e->getResponse();
            return $this->getFailed($e,$started);
        }
    }

    public function nanoLogin(array $loginArray, array $callArray)
    {
        $started=microtime(true);
        $bodyInJson=json_encode($loginArray);
        $client=new GUZ(['headers' => ['Content-Type' => 'application/json']]);
        $this->apiLastCall=$callArray['nanoHost'].$callArray['nanoAuth'];
        try{
            $r = $client->request("POST", $this->apiLastCall, ['body' => $bodyInJson,'http_errors' => true]);
            $postArray['me']=$loginArray['username'];
            $postArray['callTime']=microtime(true)-$started;
            $postArray['body']=$r->getBody()->getContents();
            $postArray['status']=$r->getStatusCode();
            $postArray['headersOut']=$r->getHeaders();
            $postArray['message']="NanoEscrow active";
            return $postArray;
        } catch (ClientException $e) {
            $exception = $e->getResponse();
            $response['body'] = $exception->getBody()->getContents();
            $response['status'] = $exception->getStatusCode();
            $response['headersOut'] = $exception->getHeaders();
            $response['$message']="Failure for API login for {$loginArray['username']}.";
            return $response;
        }
    }
    private function getFailed($e, $started)
    {
        $exception = $e->getResponse();
        $response['body'] = $exception->getBody()->getContents();
        $response['status'] = $exception->getStatusCode();
        $response['headersOut'] = $exception->getHeaders();
        $response['callTime']=microtime(true)-$started;
        $response['message']="Failure on {$this->apiLastCall}";
        return $response;
    }

    private function  getSuccess($r, $started)
    {
        $response['body']=$r->getBody()->getContents();
        $response['callTime']=microtime(true)-$started;
        $response['status']=$r->getStatusCode();
        $response['headersOut']=$r->getHeaders();
        return $response;
    }
}
