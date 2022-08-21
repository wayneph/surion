<?php namespace BL;

use nanoAPI\nano;
$callerRoot=$_SERVER['DOCUMENT_ROOT'];
require $callerRoot.'/vendor/autoload.php';
include_once $callerRoot.'/include/appControl/nanoEscrowAPI.php';
include_once $callerRoot.'/include/appControl/Dl.php';
use DataLogic\DL as DL;

use function GuzzleHttp\Psr7\_parse_message;

class BL extends nano
{
    /* privates */
    private $apiSortArray;
    private $myName="BL";
    /* control vars */
    public $html;
    public $trace;
    public $debug;
    public $pageArray;
    private $addedUrlPosts;

    public function __construct(string $pageName)
    {
        $this->trace[]=__METHOD__."($pageName)";
        $this->getEnv();
        $root=$_SERVER['DOCUMENT_ROOT']."/assets/templates";
        $this->debug=getenv("debug");
        $middle="$root/$pageName.html";
        if(!file_exists($middle)){
            return;
        }
        $this->html=file_get_contents("$root/header.html");
        $this->html.=file_get_contents($middle);
        $this->html.=file_get_contents("$root/footer.html");
        $message="<!--no Message-->";
        if(isset($_GET['message'])){
            $message=$_GET['message'];
        }
        $this->html=str_replace("###message###", $message, $this->html);
        $this->html=str_replace("###title###","NanoEscrow-$pageName",$this->html);
        $copyStuff=file_get_contents("$root/copy.html");
        $this->html=str_replace("###copyStuff###", $copyStuff, $this->html);
        return;
    }
    public function apiGetAccountActions()
    {
        $this->trace[]=__METHOD__;
        $query="?pageSize=1000";
        $this->apiArray['bearer']=$this->pageArray['bearer'];
        $this->apiArray['call']=getenv("nano-host").getenv("nano-account-actions").$query;
        return parent::apiGet($this->apiArray);
    }
    private function apiGetAccountGroups()
    {
        $this->trace[]=__METHOD__;
        $query="?pageSize=1000";
        $this->apiArray['bearer']=$this->pageArray['bearer'];
        $this->apiArray['call']=getenv("nano-host").getenv("nano-account-groups").$query;
        return parent::apiGet($this->apiArray);
    }

    public function apiGetAccountStatus()
    {
        $query="?pageSize=1000";
        $this->apiArray['bearer']=$this->pageArray['bearer'];
        $this->apiArray['call']=getenv("nano-host").getenv("nano-account-statuses").$query;
        return parent::apiGet($this->apiArray);
    }

    public function apiGetAccountTypes()
    {
        $query="?pageSize=1000";
        $this->apiArray['bearer']=$this->pageArray['bearer'];
        $this->apiArray['call']=getenv("nano-host").getenv("nano-account-types").$query;
        return parent::apiGet($this->apiArray);
    }

    private function apiGetAccountsFor(string $qry)
    {
        $query="?pageSize=1000$qry";
        $this->apiArray['bearer']=$this->pageArray['bearer'];
        $this->apiArray['call']=getenv("nano-host").getenv("nano-accounts").$query;
        return parent::apiGet($this->apiArray);
    }

    private function apiGetAccountAccountsByGuid(int $id)
    {
        echo("ToDO");exit();
    }
    private function apiNanoBanksAll()
    {
        $query="?pageSize=1000";
        $this->apiArray['bearer']=$this->pageArray['bearer'];
        $this->apiArray['call']=getenv("nano-host").getenv("nano-banks").$query;
        return parent::apiGet($this->apiArray);
    }

    private function apiNanoControlEndpointsAll()
    {
        $this->trace[]=__METHOD__;
        $query="?pageSize=1000";
        $this->apiArray['bearer']=$this->pageArray['bearer'];
        $this->apiArray['call']=getenv("nano-host").getenv("nano-control-endpoints").$query;
        return parent::apiGet($this->apiArray);
    }

    private function apiNanoControlApplicationsAll()
    {
        $this->trace[]=__METHOD__;
        $query="?pageSize=1000";
        $this->apiArray['bearer']=$this->pageArray['bearer'];
        $this->apiArray['call']=getenv("nano-host").getenv("nano-control-applications").$query;
        return parent::apiGet($this->apiArray);
    }
    private function apiNanoControlProfilesAll()
    {
        $this->trace[]=__METHOD__;
        $query="?pageSize=1000";
        $this->apiArray['bearer']=$this->pageArray['bearer'];
        $this->apiArray['call']=getenv("nano-host").getenv("nano-control-profiles").$query;
        return parent::apiGet($this->apiArray);
    }
    private function apiNanoControlStatusesAll()
    {
        $this->trace[]=__METHOD__;
        $query="?pageSize=1000";
        $this->apiArray['bearer']=$this->pageArray['bearer'];
        $this->apiArray['call']=getenv("nano-host").getenv("nano-control-statuses").$query;
        return parent::apiGet($this->apiArray);
    }

    private function apiNanoControlUsersAll()
    {
        $this->trace[]=__METHOD__;
        $query="?pageSize=1000";
        $this->apiArray['bearer']=$this->pageArray['bearer'];
        $this->apiArray['call']=getenv("nano-host").getenv("nano-control-users").$query;
        return parent::apiGet($this->apiArray);
    }

    private function apiNanoInvocationSettings()
    {
        $this->trace[]=__METHOD__;
        $query="?pageSize=1000";
        $this->apiArray['bearer']=$this->pageArray['bearer'];
        $this->apiArray['call']=getenv("nano-host").getenv("nano-invocation-settings").$query;
        return parent::apiGet($this->apiArray);
    }

    private function apiGetOwnersAll()
    {
        $query="?pageSize=1000";
        $this->apiArray['bearer']=$this->pageArray['bearer'];
        $this->apiArray['call']=getenv("nano-host").getenv("nano-owners").$query;
        return parent::apiGet($this->apiArray);
    }

    private function apiNanoOwnerTypesAll()
    {
        $query="?pageSize=1000";
        $this->apiArray['bearer']=$this->pageArray['bearer'];
        $this->apiArray['call']=getenv("nano-host").getenv("nano-owner-types").$query;
        return parent::apiGet($this->apiArray);
    }

    private function apiPatchOwner(array $postArray)
    {
        $cookieArray=$this->getCookie();
        $id=$postArray['guid'];
        /*delete items not in update json*/
        unset($postArray['postName']);
        unset($postArray['guid']);
        $json=json_encode($postArray);
        $this->apiArray['bearer']=$cookieArray['headersOut']['Token'][0];;
        $this->apiArray['json']=$json;
        $this->apiArray['call']=getenv("nano-host").getenv("nano-owners")."/$id";
        $actionReturnArray = parent::apiPatch($this->apiArray);
        $this->setAllOwners("",1);  //1 = cache-Only
        unset($_POST);
        $retTxtArray['status']=$actionReturnArray['status'];
        $retTxtArray['message']="Success on last action";
        $cookieArray=$this->getCookie();
        $uri="/index.php?message=Please try again that was not successful";
        if(isset($cookieArray['lastAction'])){
            $retTxtArray['actionString']=$cookieArray['lastAction'];
            $uri="/action/?{$retTxtArray['actionString']}";
        }
        if($actionReturnArray['status']!=200){
            $retTxtArray['body']=print_r(json_decode($actionReturnArray['body']),true);
            $retTxtArray['message']="Last Action <b>not</b> successful <br><pre>\n";
        }
        $this->setDataCache($retTxtArray,"action",1, 0);    /* (array $data, string $category, $force, $append) */
        header("Location:$uri",301);
        exit();
    }

    private function setLogin(array $tryUserArray, string $clink)
    {
        //echo("<br>Array:tryUserArray(".__LINE__."({$this->myName}))<br><pre>"); print_r($tryUserArray); echo("</pre><hr>");exit();
        $tmp=explode("|",$tryUserArray['auth']);
        $guid=$tryUserArray['authGuid'];
        $loginJsonArray['username']=$tmp[0];
        $loginJsonArray['password']=$tmp[1];
        $loginJsonArray['applicationGuid']=$guid;
        $callArray['nanoHost']=getenv("nano-host");
        $callArray['nanoAuth']=getenv("nano-auth");
        $loginStatusArray=parent::nanoLogin($loginJsonArray,$callArray );
        if($loginStatusArray['status']==200){
            $loginStatusArray['clink']=$clink;
            $encryptVal=$this->enCrypt("instruct");
            $this->writeCookie($loginStatusArray);
            header("Location: /switch/?$encryptVal",301);
            exit();
        }
        header("Location: index.php?message={$loginStatusArray['message']}",301);
        exit();
    }

    public function deleteCookie()
    {
        $this->trace[]=__METHOD__;
        $cookieName=getenv("siteSlug")."-pwa";
        setcookie($cookieName, "", time() - 3600);
        return;
    }

    public function deCrypt(string $data)
    {
        for ($i = 1000; $i < 1010; $i++) {
            $extract=md5($i);
            $data=str_replace($extract,"",$data);
        }
        return $data;
    }

    public function enCrypt(string $data, $leaveData=0)
    {
        $randPos1=rand(1000,1007);
        $randPos2=$randPos1+1;
        $randPos3=$randPos1+2;
        if($leaveData==0){
            $data=md5($data);
        }
        $x=rand(3,5);
        if(($x % 2) ==0)
        {
            return md5($randPos2).$data.md5($randPos3);
        }
        if(($x % 3) ==0)
        {
            return md5($randPos3).md5($randPos1).$data;
        }
        if(($x % 5) ==0)
        {
            return md5($randPos2).md5($randPos3).$data.md5($randPos1);
        }
        return md5($randPos3).md5($randPos1).$data.md5($randPos2);
    }

    public function evalCookie()
    {
        $this->trace[]=__METHOD__;
        $this->pageArray['cookie']=array();
        $cookieName=getenv("site-slug")."-pwa";
        if(isset($_COOKIE[$cookieName]))
        {
            $cookieVars = json_decode($_COOKIE[$cookieName], true);
            $this->pageArray['cookie']=$cookieVars;
            return $cookieVars;
        }
        return array();
    }
    public function evaluateAction()
    {
        $possArray=$this->getDataInfo("action");
        $md=$this->deCrypt($_SERVER['QUERY_STRING']);
        for($n=0;$n<count($possArray);$n++){
            $item=$possArray[$n];
            $md=str_replace($item['code'], $item['action']."~", $md);
        }
        $formReplacesArray['debug']=$md;
        $actionArray=explode("~",$md);
        $actions['option']=$actionArray[0];
        $actions['area']=$actionArray[1];
        $actions['dataKey']=$actionArray[2];
        $actions['dataKeyValue']=$actionArray[3];
        $formReplacesArray['action']="{$actions['option']} {$actions['area']}";
        $formReplacesArray['subForm']="{$actions['option']}-{$actions['area']}.html";
        $dataSet=$this->getDataCache($actions['area']);
        $formReplacesArray['dataItem']=$this->getDataItem($dataSet, $actions['dataKey'], $actions['dataKeyValue']);
        //ToDo:: why these two - fix -
        //$formReplacesArray['dataForForm']=$this->getFormReplacements($actions['area'], "options", $actions['dataKeyValue']);
        $formReplacesArray['subFormReplaces']=$this->getFormReplacements($actions['area'],$formReplacesArray['dataItem'], $actions['dataKeyValue']);
        return $formReplacesArray;
    }
    public function evaluateSwitch()
    {
        $mdAll=$this->deCrypt($_SERVER['QUERY_STRING']);
        $lengthMd=strlen($mdAll);
        $md=substr($mdAll,0,32);
        $this->addedUrlPosts=str_replace($md,"",$mdAll);
        //$possArray=json_decode(file_get_contents("gets.json"),true);
        $possArray=DL::R_entitiesInfo("switch");
        if($possArray['status']!=200){
            header("location: /index.php?message={$possArray['status']} .. {$possArray['message']}",301);
            exit();
        }
        $possArray=json_decode($possArray['data'][0]['info'],true);
        for($n=0;$n<count($possArray);$n++){
            $item=$possArray[$n];
            if(substr($item['code'],0,32)==$md){
                //$this->addedUrlPosts=substr($item['code'],32,1000);
                switch ($item['function']) {
                    case "accountsByGroup":
                        $htmlReplacesArray['page']=$item['function'];
                        $htmlReplacesArray['replace']['accounts']=$this->setAccountsByGroupId("accounts", $this->addedUrlPosts ,"Accounts By Group");
                        $htmlReplacesArray['replace']['accountsCall']=$this->apiLastCall;
                        $htmlReplacesArray['replace']['filter']="Group Id = {$this->addedUrlPosts}";
                        return $htmlReplacesArray;
                        break;
                    case "instruct":
                        $location="../instruct.php?message=You have a valid login..";
                        header("location:$location",301);
                        exit();
                        break;
                    case "control":
                        //statuses
                        $htmlReplacesArray['page']=$item['function'];
                        $htmlReplacesArray['replace']['statuses']=$this->setAllControlStatuses("status" ,"Control Status Indicators List (all)");
                        $htmlReplacesArray['replace']['controlStatusesCall']=$this->apiLastCall;
                        //Applications
                        $htmlReplacesArray['replace']['applications']=$this->setAllControlApplications("applications" ,"Control Applications List (all)" );
                        $htmlReplacesArray['replace']['controlApplicationsCall']=$this->apiLastCall;
                        //users
                        $htmlReplacesArray['replace']['users']=$this->setAllControlUsers("users" ,"Users List (all)" );
                        $htmlReplacesArray['replace']['controlUsersCall']=$this->apiLastCall;
                        //profiles
                        $htmlReplacesArray['replace']['controlProfiles']=$this->setAllControlProfiles("profiles" ,"Profiles List (all)" );
                        $htmlReplacesArray['replace']['controlProfilesCall']=$this->apiLastCall;
                        //endpoints
                        $htmlReplacesArray['replace']['controlEndpoints']=$this->setAllControlEndpoints("endpoints" ,"Profiles List (all)" );
                        $htmlReplacesArray['replace']['controlEndpointsCall']=$this->apiLastCall;
                        //return
                        return $htmlReplacesArray;
                        break;
                    case "accounts":
                        $htmlReplacesArray['page']=$item['function'];
                        //accountsAccountTypesCall
                        $htmlReplacesArray['replace']['accountTypes']=$this->setAllAccountTypes("accountTypes" ,"Account Types List (all)");
                        $htmlReplacesArray['replace']['accountsAccountTypesCall']=$this->apiLastCall;
                        // //accountsAccountActionsCall
                        // $htmlReplacesArray['replace']['accountActions']=$this->setAllAccountActions("accountActions" ,"Account Actions List (all)");
                        // $htmlReplacesArray['replace']['accountsAccountActionsCall']=$this->apiLastCall;
                        // //accountsAccountStatusesCall
                        // $htmlReplacesArray['replace']['accountStatuses']=$this->setAllAccountStatusIndicators("accountStatus" ,"Account Statuses (all)");
                        // $htmlReplacesArray['replace']['accountsAccountStatusesCall']=$this->apiLastCall;
                        //accountsAccountGroupsCall
                        // $htmlReplacesArray['replace']['accountGroups']=$this->setAllAccountGroups("accountGroups" ,"Account Groups (all)");
                        // $htmlReplacesArray['replace']['accountsAccountGroupsCall']=$this->apiLastCall;
                        return $htmlReplacesArray;
                        break;
                    case "invocations":
                        $htmlReplacesArray['page']=$item['function'];
                        //###invocationsOwnerTypesCall###
                        $htmlReplacesArray['replace']['ownerTypes']=$this->setAllOwnerTypes("OwnerTypes List (all)",0);
                        $htmlReplacesArray['replace']['invocationsOwnerTypesCall']=$this->apiLastCall;
                        //###invocationsOwnersCall###
                        $htmlReplacesArray['replace']['owners']=$this->setAllOwners("Owners List (all)", 0);
                        $htmlReplacesArray['replace']['invocationsOwnersCall']=$this->apiLastCall;
                        //invocationsInvocationsCall
                        $htmlReplacesArray['replace']['invocations']=$this->setAllInvocationSettings("","Invocation Settings (all)");
                        $htmlReplacesArray['replace']['invocationsInvocationsCall']=$this->apiLastCall;
                        //###invocationsBanksCall###
                        $htmlReplacesArray['replace']['banks']=$this->setAllBanks("Banks&nbsp;&rarr;&nbsp;<b>***name***</b>" ,"Banks (all)");
                        $htmlReplacesArray['replace']['invocationsBanksCall']=$this->apiLastCall;
                        //echo("<br>Array:pageArray(".__LINE__."({$this->myName}))<br><pre>"); print_r($htmlReplacesArray['replace']); echo("</pre><hr>");exit();
                        return $htmlReplacesArray;
                        break;
                    default:
                        header("Location:../index.php?message=Cannot find that functionality -- $md",301);
                        exit();
                }
            }
        }
        header("location:../index.php?message=Missing functionality -- $md",301);
        exit();
    }

    public function xxgetCacheFileData(string $area)
    {
        $cookieValues=$this->getCookie();


        $cacheFile=getenv('site-cache-location')."/$area-{$cookieValues['me']}.json";
        if(!file_exists($cacheFile)){
            return null;
        }
        $data[$area]=json_decode(file_get_contents($cacheFile),true);
        if(isset($data[$area]['data'])){
            return $data[$area]['data'];
        }
        return $data[$area];
    }

    private function getCookie()
    {
        $cookieName=getenv('site-slug')."-pwa";
        if(isset($_COOKIE[$cookieName]))
        {
            return json_decode($_COOKIE[$cookieName], true);
        }
        header("Location:/index.php?message=Your API access has expired please log in again",301);
        exit();
    }
    private function getDataItem(array $dataSet, string $key, mixed $keyValue)
    {
        for($n=0;$n<count($dataSet);$n++){
            $item=$dataSet[$n];
            if($dataSet[$n][$key]==$keyValue){
                return $item;
            }
        }
        return null;
    }
    private function getDataCache(string $dataItem)
    {
        $cookieName=getenv("site-slug")."-pwa";
        $cArray=json_decode($_COOKIE[$cookieName], true);
        $clink=$cArray['clink'];
        $data=DL::R_cacheForVar($clink, $dataItem);
        if($data['status']!==200){
            if(getenv("debug")==1)
            {
                echo("<br>NO DATA FOR $dataItem");
                exit();
            }
            $this->routeIndex($data['message']);
        }
        return json_decode($data['data'][0]['vars'],true);
    }
    public function getEnv()
    {
        $file=$_SERVER['DOCUMENT_ROOT']."/.env";
        $contents=file_get_contents($file);
        $arrayContents=explode("\n",$contents);
        foreach ($arrayContents as $key => $value) {
            $value=trim($value);
            $findEq=strpos($value,"=");
            if($findEq>0){
                $lineItemArray=explode("=",$value);
                $putEnvStr=trim($lineItemArray[0])."=".trim($lineItemArray[1]);
                putenv($putEnvStr);
            }
        }
        date_default_timezone_set(getenv('tz'));
        return;
    }

    private function getDataInfo(string $slug, $debug=0)
    {
        $dataSpec=DL::R_entitiesInfo($slug);
        if($dataSpec['status']!=200){
            $this->routeIndex($dataSpec['message']);
        }
        if($debug==1){
            echo("<br>Array:dataSpec(".__LINE__."({$slug}))<br><pre>"); print_r($dataSpec); echo("</pre><hr>");
        }
        $dataSpec=json_decode($dataSpec['data'][0]['info'],true);
        if($debug==1){
            echo("<br>Array:dataSpec(".__LINE__."({$slug}))<br><pre>"); print_r($dataSpec); echo("</pre><hr>");
        }
        //$dataSpec=$dataSpec[0];
        $dataSpec=$dataSpec;
        return $dataSpec;
    }

    private function getFormReplacements(string $area, array $dataItem, mixed $isValue)
    {
        $options=$this->getDataInfo($area);
        $options=$options[0];
        foreach ($options as $key => $value) {
            if(strlen($value)>20){
                $valueSplit=explode("~",$value);
                $optionsFullArray[$key]=$valueSplit;
            }
        }
        foreach ($optionsFullArray as $key => $value) {
            $field=$key;
            if($value[0]=="translate"){
                $ht="";
                $opts=explode("|",$value[1]);
                for($o=0;$o<count($opts);$o++){
                    $bits=explode("=",$opts[$o]);
                    $valueOnData=$dataItem[$key];
                    if($bits[0]==$valueOnData){
                        $ht.='<option value="';
                        $ht.=$valueOnData;
                        $ht.='" selected >';
                        $ht.=$bits[1];
                        $ht.="({$bits[0]})";
                        $ht.='<i class="fa fa-superpowers" aria-hidden="true"></i></option>';
                    }
                    else
                    {
                        $ht.='<option value="';
                        $ht.=$bits[0];
                        $ht.='">';
                        $ht.=$bits[1];
                        $ht.="({$bits[0]})";
                        $ht.='</option>';
                    }
                }
                $optionsFullArray[$key]['ht']=$ht;
            }
            if($value[0]=="linkTo"){
                $ht="";
                $opts=explode("|",$value[1]);
                //get the data
                $data=$this->getDataCache($opts[0]);
                for($n=0;$n<count($data);$n++){
                    $dataItem=$data[$n];
                    if($dataItem[$opts[1]]==$isValue){
                        $ht.='<option value="';
                        $ht.=$isValue;
                        $ht.='" selected >';
                        $ht.=$dataItem[$opts[2]];
                        $ht.="({$dataItem[$opts[1]]})";
                        $ht.='<i class="fa fa-superpowers" aria-hidden="true"></i></option>';
                    }
                    else
                    {
                        $ht.='<option value="';
                        $ht.=$dataItem[$opts[1]];
                        $ht.='" selected >';
                        $ht.=$dataItem[$opts[2]];
                        $ht.="({$dataItem[$opts[1]]})</option>";
                    }
                }
                $optionsFullArray[$key]['ht']=$ht;
            }
        }
        return $optionsFullArray;
    }
    private function routeIndex(string $message )
    {
        header("Location:/index.php?message=$message",301);
        exit();
    }
    public function setDataCache(array $data, string $setVar)
    {
        $cookieArray=json_decode($_COOKIE[getenv("site-slug")."-pwa"], true);
        $clink=$cookieArray['clink'];
        $user=DL::R_cacheForVar($clink, "user");
        if($user['status']!=200){
            $this->routeIndex($user['message']);
        }
        $user=$user['data']['0']['user'];
        $cache[$setVar]=json_encode($data['data']);
        DL::D_cache($clink, $setVar);
        DL::C_cache($cache,$clink,$user);
        return;
    }

    public function setMenu(string $active)
    {
        $menu='<li><a class="icon solid fa-home" href="logout.php"><span>&rarr;Logout</span></a></li>';
        $menu.='<li><a class="icon solid fa-cog" href="/switch?'.$this->enCrypt("base").'"><span>&rarr;base info</span></a></li>';
        $menu.='<li><a class="icon solid fa-cog" href="/switch?'.$this->enCrypt("alerts").'"><span>&rarr;control</span></a></li>';
        $menu.='<li><a class="icon solid fa-cog" href="/switch?'.$this->enCrypt("camera").'"><span>&rarr;cameras</span></a></li>';
        $menu.='<li><a class="icon solid fa-cog" href="instruct.php"><span>&rarr;About</span></a></li>';
        $menu=str_replace("<span>&rarr;$active</span>","<span class=\"active\">&nbsp;[ $active ]&nbsp;</span>",$menu);
        if($active=="Index"){
            $menu=str_replace("&rarr;Logout","&diams;Login",$menu);
        }
        return $menu;
    }
    private function setAccountsByGroupId(int $groupId ,string $header)
    {
        $qry="&accountGroupId=$groupId";
        $this->pageArray['accountsByGroupId']=$this->apiGetAccountsFor($qry);
        $stats=$this->setApiGetStats($this->pageArray['accountsByGroupId']);
        switch ($this->pageArray['accountsByGroupId']['status']) {
            case 200:
                $dataArray=json_decode($this->pageArray['accountsByGroupId']['body'],true);
                $this->pageArray['bearer']=$this->pageArray['accountsByGroupId']['headersOut']['Token'][0];
                $this->setDataCache($dataArray,"accountsByGroupId",0,0); /* (array $data, string $category, $force, $append) */
                $dataArray['stats']=$stats;
                return $this->setHtml200GetsTable("accounts", $header, $dataArray);
                break;
            case 403:
                $dataArray=json_decode($this->pageArray['accountsByGroupId']['body'],true);
                return $this->setHtml403Gets($header, $dataArray);
                break;
            default:
                header("Location:/index.php?message=Status for $header is {$this->pageArray['accountsByGroupId']['status']} .. Please re-Login",301);
                exit();
        }
    }
    private function setAllAccountActions(string $item ,string $header)
    {
        $this->pageArray['accountActions']=$this->apiGetAccountActions();
        $stats=$this->setApiGetStats($this->pageArray['accountActions']);
        switch ($this->pageArray['accountActions']['status']) {
            case 200:
                $dataArray=json_decode($this->pageArray['accountActions']['body'],true);
                $this->pageArray['bearer']=$this->pageArray['accountActions']['headersOut']['Token'][0];
                $this->setDataCache($dataArray,"accountActions",0,0);/* (array $data, string $category, $force, $append) */
                $dataArray['stats']=$stats;
                return $this->setHtml200GetsTable($item, $header, $dataArray);
                break;
            case 403:
                $dataArray=json_decode($this->pageArray['accountActions']['body'],true);
                return $this->setHtml403Gets($header, $dataArray);
                break;
            default:
                header("Location:/index.php?message=Status for $header is {$this->pageArray['accountActions']['status']} .. Please re-Login",301);
                exit();
        }
    }

    private function setAllAccountGroups(string $item ,string $header)
    {
        $this->trace[]=__METHOD__."($item, $header)";
        $this->pageArray['accountGroups']=$this->apiGetAccountGroups();
        $stats=$this->setApiGetStats($this->pageArray['accountGroups']);
        switch ($this->pageArray['accountGroups']['status']) {
            case 200:
                $dataArray=json_decode($this->pageArray['accountGroups']['body'],true);
                $this->pageArray['bearer']=$this->pageArray['accountGroups']['headersOut']['Token'][0];
                $this->setDataCache($dataArray,"accountGroups",0,0);/* (array $data, string $category, $force, $append) */
                $this->setSortFieldsArray('accountGroups');
                //
                $actualDataArray=$this->setPresentArray($dataArray,$this->apiSortArray['accountGroups'], "accountGroups");
                $dataArray['data']=$actualDataArray;
                $dataArray['stats']=$stats;
                return $this->setHtml200GetsTable($item, $header, $dataArray);
                break;
            case 403:
                $dataArray=json_decode($this->pageArray['accountGroups']['body'],true);
                return $this->setHtml403Gets($header, $dataArray);
                break;
            default:
                header("Location:/index.php?message=Status for $header is {$this->pageArray['accountGroups']['status']} .. Please re-Login",301);
                exit();
        }
    }

    private function setAllPrimaryData(string $paArea)
    {
        $dataArray=json_decode($this->pageArray[$paArea]['body'],true);
        $this->pageArray['bearer']=$this->pageArray[$paArea]['headersOut']['Token'][0];
        $this->setDataCache($dataArray,$paArea);
        $this->apiSortArray[$paArea]=$this->getDataInfo($paArea,0);
        $actualDataArray=$this->setPresentArray($dataArray,$this->apiSortArray[$paArea], $paArea);
        $dataArray['data']=$actualDataArray;
        $dataArray['stats']=$stats=$this->setApiGetStats($this->pageArray[$paArea]);
        return $dataArray;
    }

    private function setAllAccountTypes(string $item ,string $header)
    {
        $this->pageArray['accountTypes']=$this->apiGetAccountTypes();
        switch ($this->pageArray['accountTypes']['status']) {
            case 200:
                $paArea='accountTypes';
                $dataArray=$this->setAllPrimaryData($paArea);
                return $this->setHtml200GetsTable($item, $header, $dataArray);
                break;
            case 403:
                $dataArray=json_decode($this->pageArray['accountTypes']['body'],true);
                return $this->setHtml403Gets($header, $dataArray);
                break;
            default:
                header("Location:/index.php?message=Status for $header is {$this->pageArray['accountTypes']['status']} .. Please re-Login",301);
                exit();
        }
    }

    private function setAllAccountStatusIndicators(string $item ,string $header)
    {
        $this->pageArray['accountStatusIndicators']=$this->apiGetAccountStatus();
        $stats=$this->setApiGetStats($this->pageArray['accountStatusIndicators']);
        switch ($this->pageArray['accountStatusIndicators']['status']) {
            case 200:
                $dataArray=json_decode($this->pageArray['accountStatusIndicators']['body'],true);
                $this->pageArray['bearer']=$this->pageArray['accountStatusIndicators']['headersOut']['Token'][0];
                $this->setDataCache($dataArray,"accountStatusIndicators",0,0);/* (array $data, string $category, $force, $append) */
                //echo("<br>Array:dataArray(".__LINE__."({$this->myName}))<br><pre>"); print_r($dataArray); echo("</pre><hr>");exit();
                $this->setSortFieldsArray('accountStatusIndicators');
                //
                $actualDataArray=$this->setPresentArray($dataArray,$this->apiSortArray['accountStatusIndicators'], "accountStatusIndicators");
                $dataArray['data']=$actualDataArray;
                $dataArray['stats']=$stats;
                return $this->setHtml200GetsTable($item, $header, $dataArray);
                break;
            case 403:
                $dataArray=json_decode($this->pageArray['accountStatusIndicators']['body'],true);
                return $this->setHtml403Gets($header, $dataArray);
                break;
            default:
                header("Location:/index.php?message=Status for $header is {$this->pageArray['accountStatusIndicators']['status']} .. Please re-Login",301);
                exit();
        }
    }

    private function setAllBanks(string $item ,string $header)
    {
        $this->trace[]=__METHOD__."($item, $header)";
        $this->pageArray['banks']=$this->apiNanoBanksAll();
        $stats=$this->setApiGetStats($this->pageArray['banks']);
        switch ($this->pageArray['banks']['status']) {
            case 200:
                $dataArray=json_decode($this->pageArray['banks']['body'],true);
                $this->pageArray['bearer']=$this->pageArray['banks']['headersOut']['Token'][0];
                $this->setDataCache($dataArray,"banks",0,0); /* (array $data, string $category, $force, $append) */
                $dataArray['stats']=$stats;
                return $this->setHtml200GetsTable($item, $header, $dataArray);
                break;
            case 403:
                $dataArray=json_decode($this->pageArray['banks']['body'],true);
                return $this->setHtml403Gets($header, $dataArray);
                break;
            default:
                header("Location:/index.php?message=Status for $header is {$this->pageArray['banks']['status']} .. Please re-Login",301);
                exit();
        }
    }

    private function setAllControlApplications(string $item ,string $header)
    {
        $this->trace[]=__METHOD__."($item, $header)";
        $this->pageArray['controlApplications']=$this->apiNanoControlApplicationsAll();
        $stats=$this->setApiGetStats($this->pageArray['controlApplications']);
        $dataArray['stats']=$stats;
        switch ($this->pageArray['controlApplications']['status']) {
            case 200:
                $dataArray=json_decode($this->pageArray['controlApplications']['body'],true);
                $this->pageArray['bearer']=$this->pageArray['controlApplications']['headersOut']['Token'][0];
                $this->setDataCache($dataArray,"controlApplications",0,0);  /* (array $data, string $category, $force, $append) */
                $this->setSortFieldsArray('controlApplications');
                //
                $actualDataArray=$this->setPresentArray($dataArray,$this->apiSortArray['controlApplications'], "controlApplications");
                $dataArray['data']=$actualDataArray;
                $dataArray['stats']=$stats;
                return $this->setHtml200GetsTable($item, $header, $dataArray);
                break;
            case 403:
                $dataArray=json_decode($this->pageArray['controlApplications']['body'],true);
                return $this->setHtml403Gets($header, $dataArray);
                break;
            default:
            header("Location:/index.php?message=Status for $header is {$this->pageArray['controlApplications']['status']} .. Please re-Login",301);
            exit();
        }
    }

    private function setAllControlEndpoints(string $item ,string $header)
    {
        $this->trace[]=__METHOD__."($item, $header)";
        $this->pageArray['controlEndpoints']=$this->apiNanoControlEndpointsAll();
        $stats=$this->setApiGetStats($this->pageArray['controlEndpoints']);
        switch ($this->pageArray['controlEndpoints']['status']) {
            case 200:
                $dataArray=json_decode($this->pageArray['controlEndpoints']['body'],true);
                $this->pageArray['bearer']=$this->pageArray['controlEndpoints']['headersOut']['Token'][0];
                $this->setDataCache($dataArray,"controlEndpoints",0,0);  /* (array $data, string $category, $force, $append) */
                $this->setSortFieldsArray('controlEndpoints');
                //
                $actualDataArray=$this->setPresentArray($dataArray,$this->apiSortArray['controlEndpoints'], "controlEndpoints");
                $dataArray['data']=$actualDataArray;
                $dataArray['stats']=$stats;
                return $this->setHtml200GetsTable($item, $header, $dataArray);
                break;
            case 403:
                $dataArray=json_decode($this->pageArray['controlEndpoints']['body'],true);
                return $this->setHtml403Gets($header, $dataArray);
                break;
            default:
                header("Location:/index.php?message=Status for $header is {$this->pageArray['controlEndpoints']['status']} .. Please re-Login",301);
                exit();
        }
    }

    private function setAllControlProfiles(string $item ,string $header)
    {
        $this->trace[]=__METHOD__."($item, $header)";
        $this->pageArray['controlProfiles']=$this->apiNanoControlProfilesAll();
        $stats=$this->setApiGetStats($this->pageArray['controlProfiles']);
        switch ($this->pageArray['controlProfiles']['status']) {
            case 200:
                $dataArray=json_decode($this->pageArray['controlProfiles']['body'],true);
                $this->pageArray['bearer']=$this->pageArray['controlProfiles']['headersOut']['Token'][0];
                $this->setDataCache($dataArray,"controlProfiles",0,0);  /* (array $data, string $category, $force, $append) */
                $this->setSortFieldsArray('controlProfiles');
                //
                $actualDataArray=$this->setPresentArray($dataArray,$this->apiSortArray['controlProfiles'], "controlProfiles");
                $dataArray['data']=$actualDataArray;
                $dataArray['stats']=$stats;
                return $this->setHtml200GetsTable($item, $header, $dataArray);
                break;
            case 403:
                $dataArray=json_decode($this->pageArray['controlProfiles']['body'],true);
                return $this->setHtml403Gets($header, $dataArray);
                break;
            default:
                header("Location:/index.php?message=Status for $header is {$this->pageArray['controlProfiles']['status']} .. Please re-Login",301);
                exit();
        }

    }

    private function setAllControlStatuses(string $item ,string $header)
    {
        $this->pageArray['controlStatuses']=$this->apiNanoControlStatusesAll();
        $stats=$this->setApiGetStats($this->pageArray['controlStatuses']);
        switch ($this->pageArray['controlStatuses']['status']) {
            case 200:
                $dataArray=json_decode($this->pageArray['controlStatuses']['body'],true);
                $this->pageArray['bearer']=$this->pageArray['controlStatuses']['headersOut']['Token'][0];
                $this->setDataCache($dataArray,"controlStatuses",0,0);  /* (array $data, string $category, $force, $append) */
                $this->setSortFieldsArray('controlStatuses');
                //
                $actualDataArray=$this->setPresentArray($dataArray,$this->apiSortArray['controlStatuses'], "controlStatuses");
                $dataArray['data']=$actualDataArray;
                $dataArray['stats']=$stats;
                return $this->setHtml200GetsTable($item, $header, $dataArray);
                break;
            case 403:
                $dataArray=json_decode($this->pageArray['controlStatuses']['body'],true);
                return $this->setHtml403Gets($header, $dataArray);
                break;
            default:
                header("Location:/index.php?message=Status for $header is {$this->pageArray['controlStatuses']['status']} .. Please re-Login",301);
                exit();
        }
    }

    private function setAllControlUsers(string $item ,string $header)
    {
        $this->trace[]=__METHOD__."($item, $header)";
        $this->pageArray['controlUsers']=$this->apiNanoControlUsersAll();
        $stats=$this->setApiGetStats($this->pageArray['controlUsers']);
        switch ($this->pageArray['controlUsers']['status']) {
            case 200:
                $dataArray=json_decode($this->pageArray['controlUsers']['body'],true);
                $this->pageArray['bearer']=$this->pageArray['controlUsers']['headersOut']['Token'][0];
                $this->setDataCache($dataArray,"controlUsers",0,0);   /* (array $data, string $category, $force, $append) */
                $this->setSortFieldsArray('controlUsers');
                //
                $actualDataArray=$this->setPresentArray($dataArray,$this->apiSortArray['controlUsers'], "controlUsers");
                $dataArray['data']=$actualDataArray;
                $dataArray['stats']=$stats;
                return $this->setHtml200GetsTable($item, $header, $dataArray);
                break;
            case 403:
                $dataArray=json_decode($this->pageArray['controlUsers']['body'],true);
                return $this->setHtml403Gets($header, $dataArray);
                break;
            default:
                header("Location:/index.php?message=Status for $header is {$this->pageArray['controlUsers']['status']} .. Please re-Login",301);
                exit();
        }
    }

    private function setAllInvocationSettings(string $item ,string $header)
    {
        $this->pageArray['accountInvocationSettings']=$this->apiNanoInvocationSettings();
        $stats=$this->setApiGetStats($this->pageArray['accountInvocationSettings']);
        switch ($this->pageArray['accountInvocationSettings']['status']) {
            case 200:
                $dataArray=json_decode($this->pageArray['accountInvocationSettings']['body'],true);
                $this->pageArray['bearer']=$this->pageArray['accountInvocationSettings']['headersOut']['Token'][0];
                $this->setDataCache($dataArray,"accountInvocationSettings",0,0);   /* (array $data, string $category, $force, $append) */
                $dataArray['stats']=$stats;
                return $this->setHtml200GetsTable($item, $header, $dataArray);
                break;
            case 403:
                $dataArray=json_decode($this->pageArray['accountInvocationSettings']['body'],true);
                return $this->setHtml403Gets($header, $dataArray);
                break;
            default:
                header("Location:/index.php?message=Status for $header is {$this->pageArray['accountInvocationSettings']['status']} .. Please re-Login",301);
                exit();
        }
    }

    private function setAllOwners(string $header)
    {
        $this->pageArray['owners']=$this->apiGetOwnersAll();
        $stats=$this->setApiGetStats($this->pageArray['owners']);
        switch ($this->pageArray['owners']['status']) {
            case 200:
                $dataArray=json_decode($this->pageArray['owners']['body'],true);
                $this->pageArray['bearer']=$this->pageArray['owners']['headersOut']['Token'][0];
                $this->setDataCache($dataArray,"owners");
                $stats=$this->setApiGetStats($this->pageArray['owners']);
                $this->apiSortArray['owners']=$this->getDataInfo("owners");
                $dataArray['stats']=$stats;
                $actualDataArray=$this->setPresentArray($dataArray,$this->apiSortArray['owners'], "owners");
                $dataArray['data']=$actualDataArray;
                return $this->setHtml200GetsTable("owners", $header, $dataArray);
                break;
            case 403:
                $dataArray=json_decode($this->pageArray['owners']['body'],true);
                return $this->setHtml403Gets($header, $dataArray);
                break;
            default:
                header("Location:/index.php?message=Status for $header is {$this->pageArray['owners']['status']} .. Please re-Login",301);
                exit();
        }
    }

    private function setAllOwnerTypes(string $header)
    {
        $this->pageArray['ownerTypes']=$this->apiNanoOwnerTypesAll();
        $stats=$this->setApiGetStats($this->pageArray['ownerTypes']);
        switch ($this->pageArray['ownerTypes']['status']) {
            case 200:
                $dataArray=json_decode($this->pageArray['ownerTypes']['body'],true);
                $this->pageArray['bearer']=$this->pageArray['ownerTypes']['headersOut']['Token'][0];
                $this->setDataCache($dataArray,"ownerTypes");
                $stats=$this->setApiGetStats($this->pageArray['ownerTypes']);
                $this->apiSortArray['ownerTypes']=$this->getDataInfo("ownerTypes",0); // 0=do not debug
                $dataArray['stats']=$stats;
                $actualDataArray=$this->setPresentArray($dataArray,$this->apiSortArray['ownerTypes'], "ownerTypes");
                $dataArray['data']=$actualDataArray;
                return $this->setHtml200GetsTable("ownerTypes", $header, $dataArray);
                break;
            case 403:
                $dataArray=json_decode($this->pageArray['ownerTypes']['body'],true);
                return $this->setHtml403Gets($header, $dataArray);
                break;
            default:
                header("Location:/index.php?message=Status for $header is {$this->pageArray['ownerTypes']['status']} .. Please re-Login",301);
                exit();
        }
    }
    private function setApiGetStats($dataArray)
    {
        if(count($dataArray)==0){
            return "<p>No Stats for last call</p>";
        }
        if($dataArray['status']!=200){
            return "<p>No Stats for last call</p>";
        }
        if(isset($dataArray['body'])){
            $data=json_decode($dataArray['body'],true);
            unset($data['data']);
            $outArray['Page Number']=$data['pageNo'];
            $outArray['Selected Page Size']=$data['pageSize'];
            $outArray['Records Displayed']=$data['numberOfRecords'];
            $outArray['More Records Exist']=$data['pageNo'];
        }
        $replace="Yes";
        if($data['hasMoreRecords']==false){
            $replace="No";
        }
        $outArray['More Records Exist']=$replace;
        $returnHt="<h4>Query Stats</h4>\n";
        $returnHt.='<table class="stats-table">'."\n";
        foreach ($outArray as $key => $value) {
            $returnHt.="<tr><td style=\"text-align:right;\">$key</td><td style=\"text-align:left;\">$value<td><tr>\n";
        }
        $returnHt.="</table>\n";
        $returnHt.="<h4>Query Data</h4>\n";
        return $returnHt;
    }

    private function xxxxxxsetLogFileHTML(string $logFile)
    {

        $this->trace[]=__METHOD__."($logFile)";
        $file=$_SERVER['DOCUMENT_ROOT'];
        if(getenv("debug")==1){
            $file="";
        }
        $file.=getenv("site-cache-location")."/$logFile.json";
        if(getenv("debug")==0){

        }
        $contents=json_decode(file_get_contents($logFile),true);
        $contents=print_r($contents,true);
        return "<hr><pre>$contents</pre><hr>";
    }

    public function setLoginAppUser(array $postArray)
    {
        $findName=$postArray['frmLoginName'];
        $findUserTokensArray=DL::R_userTokens($postArray['frmLoginName']);
        /* validate loginPin */
        if($findUserTokensArray['status']!=200){
            header("Location:/index.php?message={$findUserTokensArray['message']}",301);
            exit();
        }
        $data=$findUserTokensArray['data'];
        for($n=0;$n<count($data);$n++){
            if($data[$n]['type_use']=='loginPin'){
                if($data[$n]['token']!=$postArray['frmLoginPIN']){
                    header("Location:/index.php?message=That did not work ..",301);
                    exit();
                }
            }
        }
        $apiAuthArray=array();
        for($n=0;$n<count($data);$n++){
            if($data[$n]['type_use']=='escrowAdminAuth'){
                $apiAuthArray['auth']=$data[$n]['token'];
            }
            if($data[$n]['type_use']=='escrowAdminAPIGuid'){
                $apiAuthArray['authGuid']=$data[$n]['token'];
            }
        }
        $cacheArray['clink']=$this->enCrypt($postArray['frmLoginName']);
        $this->writeCookie($cacheArray);
        $cacheArray['user']=$postArray['frmLoginName'];
        for($n=0;$n<count($data);$n++){
            $cacheArray[$data[$n]['type_use']]=$data[$n]['token'];
        }
        DL::U_cache($postArray['frmLoginName']);    // deletes old entries for user (will fin app-slug in .env)
        DL::C_cache($cacheArray,$cacheArray['clink'],$postArray['frmLoginName']);
        if(count($apiAuthArray)!=2){
            header("Location:/index.php?message=Unable to utilize the NanoEscrow API::".__LINE__,301);
            exit();
        }
        $successIndicator=$this->setLogin($apiAuthArray, $cacheArray['clink']);
        return $successIndicator;
    }

    private function setHtml200GetsTable(string $area, string $heading, array $dataArray)  //waynep 02
    {
        $stats=null;
        if(isset($dataArray['stats'])){
            $stats="{$dataArray['stats']}";
            unset($dataArray['stats']);
        }
        $dataArray=$dataArray['data'];
        if(count($dataArray)==0){
            header("Location:\index.php?message=No data for that request<br>$heading<br>{$this->apiLastCall}",301);
            exit();
        }
        $ht="<h3>$heading</h3>";
        if(strlen($area)>5){
            $ht.="<p>LinkInfo::$area</p>";
        }
        if(!is_null($stats)){
            $ht.=$stats;
        }
        $tableStart="
            <h5>~~setInfo~~</h5>\n
            <table class=\"data-table\">\n
        ";
        $firstRow=$dataArray[0];
        $rows=count($dataArray);
        /* create heading row */
        $tableStart.='<tr><th class="icon solid fa-cog"><br>&darr;</th>';
        foreach($firstRow as $key => $value) {
            $tableStart.="<th><small>$key</small><br>~$key~</th>\n";
        }
        $tableStart.='</tr>';
        // create data rows
        $rowSets[0]=$rows;
        if($rows >10){
            $sets=(int)($rows/10);
            for($s=0;$s<$sets;$s++){
                $rowSets[$s]=($s+1)*10;
            }
            $modulus = $rows % 10;
            if($modulus>0){
                $rowSets[]=$rows;
            }
        }
        $startAt=0;
        for($s=0;$s<count($rowSets);$s++){
            $ht.=$tableStart;
            for($n=$startAt;$n<$rowSets[$s];$n++){
                $item=$dataArray[$n];
                if(!isset($item['id'])){
                    echo("<br>Array:item(".__LINE__."({$this->myName}))<br><pre>"); print_r($item); echo("</pre><hr>");exit();
                }
                if(!isset($this->pageArray[$area]['actionsArray'])){
                    $actionsArray[]="R";
                }
                else{
                    $actionsArray=$this->pageArray[$area]['actionsArray'];
                }
                $ht.="<tr><td>";
                $idValue=$item['id'];
                $idCd=$this->enCrypt("id");
                $areaCd=$this->enCrypt($area);
                $delete=$this->enCrypt("delete").$areaCd.$idCd.$idValue;
                $clone=$this->enCrypt("clone").$areaCd.$idCd.$idValue;
                $edit=$this->enCrypt("edit").$areaCd.$idCd.$idValue;
                $read=$this->enCrypt("read").$areaCd.$idCd.$idValue;
                $otherActions="otherActions";
                for($t=0;$t<count($actionsArray);$t++){
                    $otherActions.="|{$actionsArray[$t]}";
                    if($actionsArray[$t]=="C"){
                        $ht.="<a href=\"/action/?$clone\"><i class=\"icon solid fa-clone\" aria-hidden=\"true\"></i>&nbsp;\n";
                    }
                    if($actionsArray[$t]=="R"){
                        $ht.="<a href=\"/action/?$read\"><i class=\"icon solid fa-book\" aria-hidden=\"true\"></i>&nbsp;\n";
                    }
                    if($actionsArray[$t]=="U"){
                        $ht.="<a href=\"/action/?$edit\"><i class=\"icon solid fa-edit\" aria-hidden=\"true\"></i></a>&nbsp;\n";
                    }
                    if($actionsArray[$t]=="D"){
                        $ht.="<a href=\"/action/?$delete\"><i class=\"icon solid fa-cut\" aria-hidden=\"true\"></i>&nbsp;\n";
                    }
                }
                $ht.="</td>";
                foreach($item as $key => $value) {
                    $ht.="<td>$value</td>\n";
                }
            }
            $ht.='</tr>';
            $ht.='</table>';
            $from=$startAt +1;
            $info="Records $from  &rarr; $n (of $rows)";
            $ht = str_replace("~~setInfo~~", $info, $ht);
            $startAt=$n;
        }
        $ht=$this->translateFields($ht);
        //echo($ht);exit();
        return $ht;
    }

    private function setHtml403Gets(string $heading, array $dataArray)
    {
        $ht="<h3>$heading</h3>";
        $ht.="\nAPI&nbsp;&rarr;&nbsp;<b>403</b>&nbsp;&larr;&nbsp;<p>
        <ul>";
        foreach($dataArray as $key => $value) {
            $ht.="\n<li>";
            $ht.="<b>~$key~</b>&nbsp;[$key]&nbsp;<b>&rarr;</b>&nbsp;$value <br>";
            $ht.="\n</li>";
        }
        $ht.="</ul>";
        $ht=$this->translateFields($ht);
        return $ht;
    }

    public function setOwnerPatch($postArray)
    {
        $cookieArray=$this->getCookie();
        $this->pageArray['bearer']=$cookieArray['headersOut']['Token'][0];
        $this->pageArray['ownerPatch']=$this->apiPatchOwner($postArray);
        switch ($this->pageArray['ownerPatch']['status']) {
            case 200:
                $this->pageArray['bearer']=$this->pageArray['ownerPatch']['headersOut']['Token'][0];
                return $this->pageArray['ownerPatch']['status'];
                break;
            default:
                if(isset($this->pageArray['ownerPatch']['headersOut']['Token'][0])){
                    $this->pageArray['bearer']=$this->pageArray['ownerPatch']['headersOut']['Token'][0];
                }
                return $this->pageArray['ownerPatch']['status'];
                break;
        }
    }

    // private function setHtml000Gets(string $heading, array $dataArray)
    // {
    //     $ht="<h3>$heading</h3>";
    //     $ht.="\nAPI&nbsp;&rarr;&nbsp;<b>{$dataArray['status']}</b>&nbsp;&larr;&nbsp;<p>
    //     </ul>";
    //     foreach($dataArray as $key => $value) {
    //         $ht.="\n<li>";
    //         if(is_array($value)){
    //             $value=print_r($value,true);
    //         }
    //         $ht.="<b>~$key~</b>&nbsp;[$key]&nbsp;<b>&rarr;</b>&nbsp;$value <br>";
    //         $ht.="\n</li>";
    //     }
    //     $ht.="</ul>";
    //     $ht=$this->translateFields($ht);
    //     return $ht;
    // }

    /*
        Parameters:
            $dataArray=the data to present
            $translateArray = Array making english out of code for fields
            $modArea = the are to modify
        Outputs:
            returns $replaceArray --> array of values to replace in HTML
    */
    private function setPresentArray(array $dataArray, array $sortArray, string $modArea)
    {
        $dataArray=$dataArray['data'];
        $sortArray=$sortArray[0];
        // if($modArea=='ownerTypes'){
        //     echo("<br>FixMeData..$modArea");
        //     echo("<br>Array:sortArray(".__LINE__."({$this->myName}))<br><pre>"); print_r($sortArray); echo("</pre><hr>");
        //     exit();
        // }
        if(isset($sortArray['_sort'])){
            $keys = array_column($dataArray, $sortArray['_sort']);
            array_multisort($keys, SORT_ASC, $dataArray);
            $sortedText="By <b>{$sortArray['_sort']}</b> Ascending";
        }
        else{
            $keys = array_column($dataArray, 'id');
            array_multisort($keys, SORT_ASC, $dataArray);
            $sortedText="By <b>id</b> Ascending";
        }
        $actionsArray[0]="R";
        if(isset($sortArray['_action'])){
            $this->pageArray[$modArea]['actionsArray']=explode("-",$sortArray['_action']); // used in seHTML200*
        }
        $cookieName=getenv("site-slug")."-pwa";
        $cArray=json_decode($_COOKIE[$cookieName], true);
        $clink=$cArray['clink'];
        foreach($sortArray as $key => $value) {
            for($n=0;$n<count($dataArray);$n++){
                    if(isset($dataArray[$n][$key])){
                        $wasValue=$dataArray[$n][$key];
                        $replaceArray[$n][$key]=$dataArray[$n][$key];
                    }
                    if(strlen($value)>3){
                        $replaceOptsArray=explode("~",$value);
                        if($replaceOptsArray[0]=="translate"){
                            $replaceValueNotFound=$replaceOptsArray[2];
                            $found=0;
                            $dataOptionsArray=explode("|",$replaceOptsArray[1]);
                            for($k=0;$k<count($dataOptionsArray);$k++){
                                $specificOptionsArray[$k]=explode("=",$dataOptionsArray[$k]);
                            }
                            for($c=0;$c<count($specificOptionsArray);$c++){
                                if($specificOptionsArray[$c][0]==$dataArray[$n][$key]){
                                    $replaceArray[$n][$key]=$wasValue."&rarr;".$specificOptionsArray[$c][1];
                                    $found=1;
                                }
                                if($found==0){
                                    $replaceArray[$n][$key]=$wasValue."&rarr;".$replaceValueNotFound;
                                }
                            }
                        }
                        if($replaceOptsArray[0]=="linkTo"){  //waynepppp
                            $findArray=explode("|",$replaceOptsArray[1]);
                            // echo("<br> Get cached data here ($modArea) -- ::".__LINE__);exit();
                            $findInCacheArray=DL::R_cacheForVar($clink, $findArray[0]);
                            //echo("<br>Array:findInCacheArray(".__LINE__."({$this->myName}))<br><pre>"); print_r($findInCacheArray); echo("</pre><hr>");
                            $replaceValueNotFound=$replaceOptsArray[2];
                            $found=0;
                            if($findInCacheArray['status']==200){
                                $findInCacheArray=$findInCacheArray['data'][0];
                                $found=1;
                            }
                            if($found==1){
                                $findField=$findArray[1];
                                $findInCacheArray=json_decode($findInCacheArray['vars'],true);
                                for($i=0;$i<count($findInCacheArray);$i++){
                                    if($findInCacheArray[$i][$findField] == $dataArray[$n][$key]){
                                        $replaceArray[$n][$key]=$wasValue."&rarr;".$findInCacheArray[$i][$findArray[2]];
                                        $found=1;
                                    }
                                }
                            }
                            if($found==0){
                                $replaceArray[$n][$key]=$wasValue."&rarr;".$replaceValueNotFound;
                            }
                        }
                    }
            }
        }
        return $replaceArray;
    }

    private function setSortFieldsArray(string $category)
    {
        switch ($category) {
            case "controlEndpoints":
                //
                $this->apiSortArray[$category]=array(
                    "_actions"=>"R",
                    "_sorts"=>"method,url",
                    "_filters"=>"method",
                    "id"=>"",
                    "status"=>"translate~1=active|0=Deprecated~unDefined",
                    "method"=>"",
                    "url"=>"",
                    "slug"=>""
                );
                break;
            case "controlProfiles":
                //
                $this->apiSortArray[$category]=array(
                    "id"=>"",
                    "name"=>"",
                    "slug"=>"",
                    "guid"=>"",
                    "parentId"=>"find~controlProfiles|id|name~Not Found",
                    "status"=>"translate~1=active|0=Deprecated~unDefined",
                    "recordHistoryPreserved"=>"translate~1=maintained|0=notMaintained~unDefined",
                );
                break;
            case "controlUsers":
                //
                $this->apiSortArray[$category]=array(
                    "id"=>"",
                    "username"=>"",
                    "firstName"=>"",
                    "lastName"=>"",
                    "guid"=>"",
                    "email"=>"",
                    "mobile"=>"",
                    "identityNumber"=>"",
                    "preferredMultiFactorChannel"=>"",
                    "legalsAcceptedAt"=>"",
                    "legalsVersionAccepted"=>"",
                    "status"=>"translate~1=active|0=Deprecated~unDefined"
                );
                break;
            case "controlApplications":
                //
                $this->apiSortArray[$category]=array(
                    "id"=>"",
                    "name"=>"",
                    "slug"=>"",
                    "multiFactorAuthRequired"=>"translate~1=active|0=InActive~unDefined",
                    "recordHistoryPreserved"=>"translate~1=maintained|0=notMaintained~unDefined",
                    "guid"=>"",
                    "status"=>"translate~1=active|0=Deprecated~unDefined"
                );
                break;
            case "controlStatuses":
                //
                $this->apiSortArray[$category]=array(
                    "id"=>"",
                    "name"=>"",
                    "guid"=>"",
                    "status"=>"translate~1=active|0=Deprecated~unDefined"
                );
                break;
            case "accountStatusIndicators":
                //
                $this->apiSortArray[$category]=array(
                    "id"=>"",
                    "name"=>"",
                    "slug"=>"",
                    "description"=>"",
                    "guid"=>"",
                    "status"=>"translate~1=active|0=Deprecated~unDefined",
                    "recordHistoryPreserved"=>"translate~1=maintained|0=notMaintained~unDefined",
                );
                break;
            case "accountGroups":
                $this->apiSortArray[$category]=array(
                    "id"=>"",
                    "name"=>"drill~&rarr;Accounts~accountsByGroup~accountGroupId|id",
                    "slug"=>"",
                    "guid"=>"",
                    "status"=>"translate~1=active|0=Deprecated~unDefined",
                    "recordHistoryPreserved"=>"translate~1=maintained|0=notMaintained~unDefined",
                );
                break;
            default:
                break;
          }
          $array['_sort']="name";
          $array['_action']="C-R-U-D";
          $array=array_merge($array,$this->apiSortArray[$category]);
          $j=json_encode($array);
          echo("<br>Sort Me out  $category <br>$j");exit();
          //return;
    }

    public function validateAccess(string $frontName)
    {
        $cookieArray=$this->evalCookie();
        //echo("<br>Array:cookieArray(".__LINE__.")<br><pre>"); print_r($cookieArray); echo("</pre><hr>");exit();
        $this->pageArray=array();
        if(is_null($cookieArray)){
            $message="Login details NOT set. You also need to have <u>cookies enabled</u>";
            header("Location: index.php?message=$message",301);
            exit();
        }
        if(!isset($cookieArray['status'])){
            $root=$_SERVER['DOCUMENT_ROOT'];
            $message="Login status has expired";
            header("Location: /index.php?message=$message",301);
            exit();
        }
        if($cookieArray['status']!=200){
            $message="Login details NOT set ({$cookieArray['status']}). You may have timed-Out";
            header("Location: index.php?message=$message",301);
            exit();
        }
        if(!isset($this->pageArray['auth'])){
            $this->pageArray['auth']=$cookieArray;
        }
        if(!isset($this->pageArray['bearer'])){
            $this->pageArray['bearer']=$cookieArray['headersOut']['Token'][0];
        }
        $this->writeCookie($cookieArray);
        return;
    }

    private function translateFields($ht) //ToDo -- Change to Database
    {
        $translateArray=$this->getDataInfo("translations");
        $translateArray=$translateArray[0];
        // $contentJson=file_get_contents($_SERVER["DOCUMENT_ROOT"]."/translate.json");
        // $translateArray=json_decode($contentJson,true);
        foreach($translateArray as $key => $value) {
            $ht=str_replace("~$key~",$value,$ht);
        }
        return $ht;
    }

    public function writeCookie(array $newCookieValues)
    {
        $cookieName=getenv("site-slug")."-pwa";
        $oldCookieValues=array();
        if(isset($_COOKIE[$cookieName]))
        {
            $oldCookieValues=json_decode($_COOKIE[$cookieName], true);
        }
        if(!isset($oldCookieValues['rewrites'])){
            $oldCookieValues['rewrites']="0";
        }
        $tryCookieArray=array_merge($oldCookieValues,$newCookieValues);
        $tryCookieArray['cCreatedAt']=date("Y-m-d H:i:s");
        if(!isset($tryCookieArray['rewrites'])){
            $tryCookieArray['rewrites']=0;
        }
        $tryCookieArray['rewrites']=(int)$tryCookieArray['rewrites']+1;
        setcookie($cookieName, json_encode($tryCookieArray), time() + 1200, "/");
        return;
    }
    private function writeLogs(string $page)
    {
        $this->trace[]=__METHOD__."($page)";
        $file=$_SERVER['DOCUMENT_ROOT'];
        if(getenv("debug")==1){
            $file="";
        }
        $file.=getenv("site-cache-location")."/page-$page.json";
        if(file_exists($file)){
            unlink($file);
        }
        $outArray['calls']=$this->trace;
        $outArray['page']=$this->pageArray;
        $contents = "[\n";
        $contents .= json_encode($outArray);
        $contents .="\n]";
        file_put_contents($file, $contents);
        return $file;
    }
}
