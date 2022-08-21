<?php namespace mongoDataLogic;

//use Illuminate\Support\Facades\DB;

use MongoDB\Client;
class MongoDataLogic
{
    static $myName="mongoDataLogic";

    private static function _setCapsule(string $useArea, string $useDatabase)
    {
        $connectString=getenv("mongoConnectString");
        $capsule = new Client();
        $capsule->addConnection([
            'driver' => 'mysql',
            'host' => getenv("$useArea-db-host"),
            'database' => getenv("$useArea-db-$useDatabase"),
            'username' => getenv("$useArea-db-username"),
            'password' => getenv("$useArea-db-password"),
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => '',
        ]);
        $capsule->setAsGlobal();
    }
    private static function ACgetUserActiveTokens(int $userId)
    {
        $tokens=Capsule::table('tokens')
        ->select('token_type_id','user_id','type_use','token')
        ->where('user_id', $userId)
        ->where('status',  1)
        ->get();
        $tokens=json_decode(json_encode($tokens),true);
        if(isset($tokens[0])){
            return $tokens;
        }
        return null;
    }
    private static function ENgetInfo(int $id)
    {
        $info=Capsule::table('info')
        ->select()
        ->where('category_id',$id)
        ->where('status', 1)
        ->limit(1)
        ->get();
        $info=json_decode(json_encode($info),true);
        if(isset($info[0])){
            return $info;
        }
        return null;
    }
    private static function ENgetInfoCategory(string $slug)
    {
        $category=Capsule::table('info_categories')
            ->select()
            ->where('slug',$slug)
            ->where('status', 1)
            ->limit(1)
            ->get();
        $category=json_decode(json_encode($category),true);
        if(isset($category[0])){
            return $category[0];
        }
        return null;
    }

    public static function D_cache($clink, $setVar)
    {
        self::_setCapsule("skunks","appcontrol");
        Capsule::table('app_cache')
            ->where('clink', $clink)
            ->where('var',$setVar)
            ->delete();
    }

    public static function C_cache(array $cacheArray, string $clink, string $user)
    {
        self::_setCapsule("skunks","appcontrol");
        $appSlug=getenv("site-slug");
        foreach ($cacheArray as $key => $value) {
            $inputs['clink']=$clink;
            $inputs['user']=$user;
            $inputs['epoch']=time();
            $inputs['app_slug']=$appSlug;
            $inputs['var']=$key;
            $inputs['vars']=$value;
            $inputs['status']=1;
            Capsule::table('app_cache')->insert($inputs);
        }
    }

    public static function R_cacheForVar(string $clink, string $user)
    {
        self::_setCapsule("skunks","appcontrol");
        $userData=Capsule::table('app_cache')
            ->select()
            ->where('clink',$clink)
            ->where('var',$user)
            ->where('status', 1)
            ->limit(1)
            ->get();
        $userData=json_decode(json_encode($userData),true);
        if(count($userData)==0){
            $return['status']=404;
            $return['message']="Cannot find information for [$user]";
            return $return;
        }
        $return['status']=200;
        $return['message']="[$user] - Active";
        $return['data']=$userData;
        return $return;
    }

    public static function R_entitiesInfo(string $slug)
    {
        self::_setCapsule("skunks","entities");
        $info=Capsule::table('info')
        ->select()
        ->where('slug',$slug)
        ->where('status', 1)
        ->limit(1)
        ->get();
        $data=json_decode(json_encode($info),true);
        if(!isset($data[0])){
            $info['status']=404;
            $info['message']="Cannot find information for [$slug]::D".__LINE__;
            return $info;
        }
        $info['status']=200;
        $info['message']="Got Tokens";
        $info['data']=$data;
        return $info;
    }

    public static function R_userTokens(string $userName)
    {
        self::_setCapsule("skunks","appcontrol");
        $user=Capsule::table('users')
            ->select("id","status")
            ->where('userid', $userName)
            ->get();
        $user=json_decode(json_encode($user),true);
        if(!isset($user[0])){
            $user['status']=404;
            $user['message']="Invalid User::D".__LINE__;
            return $user;
        }
        $user=$user[0];
        if($user['status']!=1){
            $user['status']=403;
            $user['message']="User Not Enabled::D".__LINE__;
            return $user;
        }
        $data=self::ACgetUserActiveTokens($user['id']);  /* all ACTIVE tokens */
        if(is_null([$data])){
            $tokensArray['status']=406;
            $tokensArray['message']="Invalid Credentials::D".__LINE__;
            return $tokensArray;
        }
        $tokensArray['status']=200;
        $tokensArray['message']="Got Tokens";
        $tokensArray['data']=$data;
        return $tokensArray;
    }

    public static function U_cache(string $user)
    {
        self::_setCapsule("skunks","appcontrol");
        $appSlug=getenv("site-slug");
        Capsule::table('app_cache')
        ->where('user',$user)
        ->where ('app_slug',$appSlug)
        ->update(['status' => 0]);
    }
    ///======waynep===================================================================================================================

    public static function allCallsValidateTokens(object $request)
    {
        $returnArray['status']=400;
        $inputs['header']['usageToken']=$request->header("usageToken");
        $inputs['header']['apiToken']=$request->header("api-key");
        $inputs['uri']=$request->path();
        if(!isset($inputs['header']['apiToken'])){
            $returnArray['status']=401;
            $returnArray['msg']="No API key (api-key) in Header - login required";
            $returnArray['header']['remedy']="Include header(api-key)";
            return $returnArray;
        }
        if(!isset($inputs['header']['usageToken'])){
            $returnArray['status']=401;
            $returnArray['msg']="No usageToken set in header - Login required - set Usage Token in header";
            return $returnArray;
        }
        $apiTokenArray=self::getApiKeyByKey($inputs['header']['apiToken']);
        if(is_null($apiTokenArray)){
            $returnArray['status']=401;
            $returnArray['msg']="No such API key";
            $returnArray['401DlException']=__LINE__;
            $returnArray['apiKey']=401;
            return $returnArray;
        }
        //api-key ok..
        $userId=$apiTokenArray['user_id'];
        $returnArray['user_id']=$userId;
        $shortTokenArray=self::getShortToken($inputs['header']['usageToken'],$userId);
        if(is_null($shortTokenArray)){
            // api-key BUT usageToken not found - rer -login
            $returnArray['status']=401;
            $returnArray['401DlException']=__LINE__;
            $returnArray['usageToken']=401;
            return $returnArray;
        }
        if($shortTokenArray['status']==1){
            //api-key ok .. short token still valid
            $returnArray['status']=200;
            $returnArray['usageToken']=$shortTokenArray['token'];
            return $returnArray;
        }
        // API-Key ok .. see if long token exists and is valid
        $longTokenArray=self::getLongToken($userId);
        if(is_null($longTokenArray)){
            // api-key BUT short token not there and long token not found
            $returnArray['status']=401;
            $returnArray['msg']="Session Expired Re Login Required";
            $returnArray['401DlException']=__LINE__;
            $returnArray['usageToken']=401;
            return $returnArray;
        }
        if($longTokenArray['status']==1){
            //api-key ok .. short token not long token still valid re-create
            $shortTokenArray=self::setShortToken($userId);
            self::extendLongTokenLife($longTokenArray['id']);
            $returnArray['status']=200;
            $returnArray['200Success']=__LINE__;
            $returnArray['usageToken']=$shortTokenArray['token'];
            return $returnArray;
        }
        //api-key ok .. short token not valid - long token not valid
        $shortTokenArray=self::setShortToken($userId);
        $returnArray['status']=401;
        $returnArray['msg']="Token Expired - login required";
        $returnArray['usageToken']=401;
        return $returnArray;
    }

    private static function deCryp(string $data)
    {
        for ($i = 1000; $i < 1010; $i++) {
            $extract=md5($i);
            $data=str_replace($extract,"",$data);
        }
        return $data;
    }


    private static function xxxxextendLongTokenLife(int $tokenId)
    {
        $longTime=time()+ env("LONG_TOKEN_LIFE");
        $longDate=date('Y-m-d H:i:s', $longTime);
        DB::connection('appControl')->table('tokens')
            ->where('id',$tokenId)
            ->update(['expires' => $longDate]);
    }

    private static function flattenArray(array $array, int $index=0)
    {
        $returnArray=null;
        if(isset($array[$index])){
            $returnArray=$array[$index];
        }
        return $returnArray;
    }

    public static function getEntity(string $usageToken, string $invocationSlug, string $entitySlug)
    {
        $validInvocation=self::setValidInvocation($usageToken, $invocationSlug);
        if(is_null($validInvocation)){
            return null;
        }
        $dbInvoked="entities_$invocationSlug";
        $data=DB::connection($dbInvoked)->table('entities')
        ->select()
        ->where('slug', $entitySlug)
        ->get();
        $data=json_decode(json_encode($data),true);
        if(!isset($data[0])){
            return null;
        }
        return $data;
    }
    private static function getEntityById(string $invocationSlug, int $entityId)
    {
        $dbInvoked="entities_$invocationSlug";
        $data=DB::connection($dbInvoked)->table('entities')
        ->select()
        ->where('id', $entityId)
        ->get();
        $data=json_decode(json_encode($data),true);
        if(!isset($data[0])){
            return null;
        }
        return $data;
    }
    public static function getEntityInfo(string $usageToken, string $invocationSlug, string $entitySlug)
    {
        $validInvocation=self::setValidInvocation($usageToken, $invocationSlug);
        if(is_null($validInvocation)){
            return null;
        }
        $entity=self::getEntity($usageToken, $invocationSlug, $entitySlug);
        //echo("<br>Array:pageArray(".__LINE__."({\$entity}))<br><pre>"); print_r($entity); echo("</pre><hr>");
        if(!isset($entity[0]['id'])){
            return null;
        }
        $data['entity']=$entity[0]['entity'];
        $data['slug']=$entity[0]['slug'];
        $entityId=$entity[0]['id'];
        $dbInvoked="entities_$invocationSlug";
        $info=DB::connection($dbInvoked)->table('info')
        ->select()
        ->where('entity_id', $entityId)
        ->orderBy('seq')
        ->get();
        $info=json_decode(json_encode($info),true);
        if(!isset($info[0])){
            return null;
        }
        for($d=0;$d<count($info);$d++){
            $infoCategoryId=$info[$d]['category_id'];
            $infoCategoryData=self::getEntityInfoCategoryById($invocationSlug, $infoCategoryId);
            $info[$d]['infoCategory']="UnkNown";
            if(isset($infoCategoryData[0])){
                $info[$d]['infoCategory']=$infoCategoryData[0]['category'];
                $info[$d]['infoCategorySlug']=$infoCategoryData[0]['slug'];
                $infoCategoryDataMandate=self::getEntityInfoCategoryMandateById($invocationSlug, $infoCategoryData[0]['mandate']);
                $info[$d]['infoMandate']='UnKnown';
                if(!is_null($infoCategoryDataMandate)){
                    $info[$d]['infoMandate']=$infoCategoryDataMandate[0]['slug'];
                }
            }
        }
        for($d=0;$d<count($info);$d++){
            unset($info[$d]['id'],$info[$d]['entity_id'],$info[$d]['category_id'],$info[$d]['status'],$info[$d]['updated'],$info[$d]['created']);
        }
        $data['info']=$info;
        return $data;
    }
    public static function getEntityRelate(string $usageToken, string $invocationSlug, string $entitySlug)
    {
        $validInvocation=self::setValidInvocation($usageToken, $invocationSlug);
        if(is_null($validInvocation)){
            return null;
        }
        $entityData=self::getEntity($usageToken,$invocationSlug, $entitySlug);
        //echo("<br>Array:pageArray(".__LINE__."(dataLogic)<br><pre>"); print_r($entityData); echo("</pre><hr>");
        if(!isset($entityData['0']['id'])){
            return null;
        }
        $entityId=$entityData[0]['id'];
        $data['entity']=$entityData[0]['entity'];
        $data['slug']=$entityData[0]['slug'];
        $entityRelateFrom=self::getEntityRelateFrom($invocationSlug, $entityId);
        //echo("<br>Array:pageArray(".__LINE__."(dataLogic)<br><pre>"); print_r($entityRelateFrom); echo("</pre><hr>");
        $data['relatedEntities']=$entityRelateFrom;
        $entityRelateTo=self::getEntityRelateTo($invocationSlug, $entityId);
        $data['relatedFromEntities']=$entityRelateTo;
        //echo("<br>Array:pageArray(".__LINE__."(dataLogic)<br><pre>"); print_r($data); echo("</pre><hr>");
        return $data;
    }
    private static function getEntityRelateFrom(string $invocationSlug, int $entityId)
    {
        $dbInvoked="entities_$invocationSlug";
        $data=DB::connection($dbInvoked)->table('relationships')
        ->select()
        ->where('entity_id', $entityId)
        ->orderby('seq')
        ->get();
        $entityRelateFrom=json_decode(json_encode($data),true);
        if(!isset($entityRelateFrom[0])){
            return null;
        }
        for($d=0;$d<count($entityRelateFrom);$d++){
            $relationshipId=$entityRelateFrom[$d]['relationship_id'];
            $relationshipData=self::getRelationshipTypesById($invocationSlug, $relationshipId);
            if(!isset($relationshipData['0']['id'])){
                $entityRelateFrom[$d]['relationship']='UnKnown';
            }else{
                $entityRelateFrom[$d]['relationship']=$relationshipData['0']['type'];
            }
        }
        for($d=0;$d<count($entityRelateFrom);$d++){
            $slaveEntityId=$entityRelateFrom[$d]['entity_slave_id'];
            $slaveEntity=self::getEntityById($invocationSlug, $slaveEntityId);
            //echo("<br>Array:(".__LINE__."(DL)<br><pre>"); print_r($relationshipData); echo("</pre><hr>");
            if(!isset($slaveEntity['0']['id'])){
                $entityRelateFrom[$d]['slaveEntitySlug']='';
                $entityRelateFrom[$d]['slaveEntityName']='UnKnown';
            }else{
                $entityRelateFrom[$d]['slaveEntitySlug']=$slaveEntity['0']['slug'];
                $entityRelateFrom[$d]['slaveEntityName']=$slaveEntity['0']['entity'];
            }
        }
        for($d=0;$d<count($entityRelateFrom);$d++){
            unset($entityRelateFrom[$d]['entity_id']);
            unset($entityRelateFrom[$d]['entity_slave_id']);
            unset($entityRelateFrom[$d]['relationship_id']);
            unset($entityRelateFrom[$d]['updated']);
            unset($entityRelateFrom[$d]['created']);
        }
        return $entityRelateFrom;
    }
    private static function getEntityRelateTo(string $invocationSlug, string $entityId)
    {
        $dbInvoked="entities_$invocationSlug";
        $data=DB::connection($dbInvoked)->table('relationships')
        ->select()
        ->where('entity_slave_id', $entityId)
        ->orderby('seq')
        ->get();
        $entityRelateTo=json_decode(json_encode($data),true);
        if(!isset($entityRelateTo[0])){
            return null;
        }
        for($d=0;$d<count($entityRelateTo);$d++){
            $relationshipId=$entityRelateTo[$d]['relationship_id'];
            $relationshipData=self::getRelationshipTypesById($invocationSlug, $relationshipId);
            if(!isset($relationshipData['0']['id'])){
                $entityRelateTo[$d]['relationship']='UnKnown';
            }else{
                $entityRelateTo[$d]['relationship']=$relationshipData['0']['type'];
            }
        }
        for($d=0;$d<count($entityRelateTo);$d++){
            $masterEntityId=$entityRelateTo[$d]['entity_id'];
            $masterEntity=self::getEntityById($invocationSlug, $masterEntityId);
            //echo("<br>Array:(".__LINE__."(DL)<br><pre>"); print_r($relationshipData); echo("</pre><hr>");
            if(!isset($masterEntity['0']['id'])){
                $entityRelateTo[$d]['slaveEntitySlug']='';
                $entityRelateTo[$d]['slaveEntityName']='UnKnown';
            }else{
                $entityRelateTo[$d]['slaveEntitySlug']=$masterEntity['0']['slug'];
                $entityRelateTo[$d]['slaveEntityName']=$masterEntity['0']['entity'];
            }
        }
        for($d=0;$d<count($entityRelateTo);$d++){
            unset($entityRelateTo[$d]['entity_id']);
            unset($entityRelateTo[$d]['entity_slave_id']);
            unset($entityRelateTo[$d]['relationship_id']);
            unset($entityRelateTo[$d]['updated']);
            unset($entityRelateTo[$d]['created']);
        }
        return $entityRelateTo;
    }

    private static function getEntityInfoCategoryById(string $invocationSlug, int $infoCategoryId)  //k8s
    {
        $dbInvoked="entities_$invocationSlug";
        $data=DB::connection($dbInvoked)->table('info_categories')
        ->select()
        ->where('id', $infoCategoryId)
        ->get();
        return json_decode(json_encode($data),true);
    }

    private static function getEntityInfoCategoryMandateById(string $invocationSlug, int $infoCategoryMandate)      //k8s
    {
        $dbInvoked="entities_$invocationSlug";
        $data=DB::connection($dbInvoked)->table('mandates')
        ->select()
        ->where('id', $infoCategoryMandate)
        ->get();
        return json_decode(json_encode($data),true);
    }
    public static function getMessagesBySiteSlugPublic(string $siteSlug, array $qParams)        //k8s
    {
        $wantedPage=1;
        $pageSize=20;
        $orderby="";
        if(isset($qParams['page'])){
            $wantedPage=$qParams['page'];
        }
        if(isset($qParams['size'])){
            $pageSize=$qParams['size'];
        }
        if(!isset($qParams['orderBy'])){
            $qParams['orderBy']['field']="updated";
            $qParams['orderBy']['order']="desc";
        }
        $offset=($wantedPage-1)*$pageSize;
        $data=DB::connection('appControl')->table('user_coms_messages')
        ->select('slug','comms_by_slug','site_slug','comms_topic','comms_text','updated','created')
        ->where('site_slug', $siteSlug)
        ->where('status', 3)
        ->offset($offset)
        ->orderBy($qParams['orderBy']['field'],$qParams['orderBy']['order'])
        ->limit($pageSize+1)
        ->get();
        $data=json_decode(json_encode($data),true);
        if(!isset($data[0])){
            return null;
        }
        $returnData=self::evalPagedDataQuery($data,$qParams,$wantedPage,$pageSize);
        return $returnData;
    }
    public static function getPageByNameForSiteId(string $pageName, int $siteId)
    {
        $data=DB::connection('sites')->table('pages')
        ->select('id','entity','slug','entity_type','fk_json','status')
        ->where('site_id', $siteId)
        ->where('page_name', $pageName)
        ->where('status', 1)
        ->orderBy('seq')
        ->get();
        $data=json_decode(json_encode($data),true);
        if(is_null($data)){
            return null;
        }
        return self::flattenArray($data,0);
    }

    private static function getRelationshipTypesById(string $invocationSlug, int $relationshipId)                  //k8s
    {
        $dbInvoked="entities_$invocationSlug";
        $data=DB::connection($dbInvoked)->table('relationship_types')
        ->select()
        ->where('id', $relationshipId)
        ->get();
        return json_decode(json_encode($data),true);
    }

    public static function getValidTokensFromUser(string $idStr)                               //   21 02 05
    {
        date_default_timezone_set ("Africa/Johannesburg");
        $userArray=DB::connection('appControl')->table('users')
        ->select('id','status')
        ->where('userid', $idStr)
        ->limit(1)
        ->get();
        $userArray=json_decode(json_encode($userArray),true);
        if(!isset($userArray[0])){
            return null;
        }
        $id=$userArray[0]['id'];
        $tokens=DB::connection('appControl')->table('tokens')
        ->select('id','token', 'user_id', 'token_type_id','type_use','usage_count')
        ->where('user_id', $id)
        ->where('status',  1)
        ->whereIn('token_type_id', [2, 3])
        ->get();
        $tokens=json_decode(json_encode($tokens),true);
        if(isset($tokens[0])){
            return $tokens;
        }
        return null;
    }
    private static function setExpiredTokens(){
        date_default_timezone_set ("Africa/Johannesburg");
        $now=date("Y-m-d H:i:s");
        $daysTime=time()-(5*24*60*60);
        $deleteDate=date("Y-m-d H:i:s",$daysTime);
        DB::connection('appControl')->table('tokens')
              ->where('expires', '<', $now)
              ->where('status',1)
              ->update(['status' => -9]);
        DB::connection('appControl')->table('tokens')
              ->where('expires', '<', $deleteDate)
              ->where('status',-9)
              ->delete();
    }
    public static function setTokenExpires(array $token)                                    //  21    02  05
    {
        date_default_timezone_set ("Africa/Johannesburg");
        $id=$token['id'];
        $uses=$token['usage_count']+1;
        $daysAdd=getenv('TOKEN_EXTEND_DAYS');
        $time=time()+(60*60*24*$daysAdd);
        $ddtt=date("Y-m-d H:i:s", $time);
        DB::connection('appControl')->table('tokens')
              ->where('id', $id)
              ->update(['status' => 1, 'expires' => $ddtt,'usage_count' => $uses]);
    }
    private static function setShortToken(int $userId)                                       //  21    02  17
    {
        date_default_timezone_set ("Africa/Johannesburg");
        $shortTime=time() + env("SHORT_TOKEN_LIFE");
        $shortToken=self::generateWHPKey(32);
        $shortDate=date('Y-m-d H:i:s', $shortTime);
        DB::connection('appControl')->table('tokens')->insert([
            'token_type_id' => self::$shortTokenType,
            'user_id' => $userId,
            'type_use' => 'usageTkn',
            'token' => $shortToken,
            'expires' => $shortDate,
            'usage_count' => 0,
            'status' =>1]
        );
        return self::getValidToken(self::$shortTokenType,$userId);
    }
    public static function setSiteWish(array $wishArray)                    //  01  04  26
    {
        $wish=DB::connection('sites')->table('wish')
        ->select()
        ->where('slug', $wishArray['slug'])
        ->get();
        $wish=json_decode(json_encode($wish),true);
        if(isset($wish[0]['id'])){
            $ct=$wish[0]['count']+1;
            $id=$wish[0]['id'];
            DB::connection('sites')->table('wish')
            ->where('id',$id)
            ->update([
                'last_log' => json_encode($wishArray),
                'count'=>$ct]
            );
            return;
        }
        DB::connection('sites')->table('wish')->insert([
            'slug'=>$wishArray['slug'],
            'called'=>$wishArray['call'],
            'last_log' => json_encode($wishArray),
            'status' =>1]
        );
        return;
    }
    public static function patchComms(array $inputs,$slug)                          //OK
    {
        $affectedBool=DB::connection('appControl')->table('user_coms_messages')
        ->where('slug',$slug)
        ->update($inputs);
        if($affectedBool){
            return 202;
        }
        return 403;
    }
    public static function postComms(array $dataIn, string $userId)
    {
        date_default_timezone_set ("Africa/Johannesburg");
        $slug=self::generateWHPKey(16);
        $commsText=substr($dataIn['comms_text'],0,300);
        $commsTopic=substr($dataIn['comms_topic'],0,50);
        $recId=DB::connection('appControl')->table('user_coms_messages')->insertGetId([
            'comms_by_slug'=>$dataIn['comms_by_slug'],
            'comms_text'=>$commsText,
            'comms_topic'=>$commsTopic,
            'site_slug'=>$dataIn['site_slug'],
            'source_slug'=>$dataIn['source_slug'],
            'for_slug'=>$dataIn['for_slug'],
            'comms_log'=>$dataIn['comms_log'],
            'slug'=>$slug,
            'status' =>5]
        );
        $outputs['status']=201;
        $outputs['id']=$recId;
        $outputs['comms_topic']=$commsTopic;
        $outputs['comms_ref']=$slug;
        return $outputs;
    }
    public static function setUsageTokens(int $userId)                                     //  21  02  17
    {
        date_default_timezone_set ("Africa/Johannesburg");
        self::setExpiredTokens();
        $longTime=time()+ env("LONG_TOKEN_LIFE");
        $longDate=date('Y-m-d H:i:s', $longTime);
        $longToken=self::generateWHPKey(64);
        $affected = DB::connection('appControl')->table('tokens')
            ->whereRaw('token_type_id in(4,5)')
            ->where('user_id',$userId)
            ->update(['status' => -9]);
        DB::connection('appControl')->table('tokens')->insert([
            'token_type_id' => 5,
            'user_id' => $userId,
            'type_use' => 'longUsage',
            'token' => $longToken,
            'usage_count' => 0,
            'expires' => $longDate,
            'status' =>1]
        );
        return self::setShortToken($userId);
    }
    private static function getUsageToken(string $token)
    {
        // do NOT set expired tokens
        $tokens=DB::connection('appControl')->table('tokens')
        ->select()
        ->where('token', $token)
        ->where('status', 1)
        ->limit(1)
        ->get();
        $tokens=json_decode(json_encode($tokens),true);
        if(isset($tokens[0])){
            return $tokens[0];
        }
        return null;
    }
    public static function getAccounts($params)
    {
        $pg = self::$offset;
        $rs = self::$limit;
        $stsOperator = "<";
        $stsValue = 200;             /* max value not settable (tinyInt) */
        $slugOperator = "like";
        $slugValue = '%';
        if(isset($params['page'])){
            $pg = (int)$params['page'];
        }
        if(isset($params['pageSize'])){
            $rs = (int)$params['pageSize'];
        }
        if(isset($params['status'])){
            $stsOperator = "=";
            $stsValue = (int)$params['status'];
        }
        if(isset($params['slug'])){
            $slugOperator = "=";
            $slugValue = $params['slug'];
        }
        $skipValue=($pg-1)*$rs;
        $data=DB::connection('balance_pjn')->table('accounts')
            ->select()
            ->where('status', $stsOperator, $stsValue)
            ->where('slug', $slugOperator, $slugValue)
            ->orderby('id')
            ->skip($skipValue)
            ->take($rs+1)
            ->get();
        $data=json_decode(json_encode($data),true);
        $retArray['parameters']['page'] = $pg;
        $retArray['parameters']['pageSize'] = $rs;
        $retArray['parameters']['status'] = "$stsOperator $stsValue";
        $retArray['parameters']['slug'] = "$slugOperator $slugValue";
        $moreRecs=false;
        $countRecs=count($data);
        if($countRecs>$rs){
            $moreRecs=true;
            unset($data[$countRecs-1]);
        }
        $retArray['parameters']['moreRecordsExist']=$moreRecs;
        if(!isset($data[0]['id'])){
            $retArray['data']=null;
            return $retArray;
        }
        $retArray['parameters']['moreRecordsExist'] = $moreRecs;
        $retArray['data'] = $data;
        return $retArray;
    }
    private static function getApiKeyByKey(string $apiKey)
    {
        self::setExpiredTokens();
        $token=DB::connection('appControl')->table('tokens')
        ->select('id','token', 'user_id', 'token_type_id','type_use','status')
        ->where('token_type_id', self::$apiTokenType)
        ->where('token', $apiKey)
        ->where('status', 1)
        ->limit(1)
        ->get();
        $apiKey=json_decode(json_encode($token),true);
        if(isset($token[0])){
            return self::flattenArray($apiKey);
        }
        return null;
    }
    public static function getEntitiesByType(string $usageToken, array $qParams, string $invocationSlug, string $typeSlug)
    {
        $validInvocation=self::setValidInvocation($usageToken, $invocationSlug);
        if(is_null($validInvocation)){
            return null;
        }
        $wantedPage=1;
        $pageSize=20;
        if(isset($qParams['page'])){
            $wantedPage=$qParams['page'];
        }
        if(isset($qParams['size'])){
            $pageSize=$qParams['size'];
        }
        $offset=($wantedPage-1)*$pageSize;
        $dbInvoked="entities_$invocationSlug";
        $typeId=DB::connection($dbInvoked)->table('types')
        ->select('id')
        ->where('slug', $typeSlug)
        ->get();
        $typeId=json_decode(json_encode($typeId),true);
        if(!isset($typeId[0]['id'])){
            return null;
        }
        $data=DB::connection($dbInvoked)->table('entities')
        ->select('entity','slug','fk_json')
        ->where('entity_type', $typeId[0]['id'])
        ->offset($offset)
        ->orderBy('entity', 'asc')
        ->limit($pageSize+1)
        ->get();
        $data=json_decode(json_encode($data),true);
        if(!isset($data[0])){
            return null;
        }
        $returnData=self::evalPagedDataQuery($data,$qParams,$wantedPage,$pageSize);
        return $returnData;
    }
    private static function evalPagedDataQuery(array $data, array $qParams, int $wantedPage, int $wantedRecs)
    {
        $packDrill['data']=$data;
        $inputs['page']=$wantedPage;
        $inputs['recs_per_page']=$wantedRecs;
        if(isset($inputs['crud_token'])){
            unset($inputs['crud_token']);
        }
        $packDrill['inputs']=$qParams;
        $packDrill['hasMoreRecords']=0;
        $records=count($data);
        if($records>$wantedRecs){
            $packDrill['hasMoreRecords']=1;
            unset($packDrill['data'][$records-1]);
        }
        return $packDrill;
    }

    private static function generateWHPKey(int $length)                 //ok
    {
        $token = bin2hex(random_bytes(64));
        $token=substr($token,0,$length);
        return $token;
    }

    public static function getEntityRelationshipTypes(string $usageToken, string $crudToken)
    {
        $validInvocation=self::setValidInvocation($usageToken, $crudToken);
        if(is_null($validInvocation)){
            return null;
        }
        $dbInvoked="entities_{$validInvocation['invocation']}";
        $data=DB::connection($dbInvoked)->table('relationship_types')
        ->select()
        ->where('status', 1)
        ->get();
        $data=json_decode(json_encode($data),true);
        if(!isset($data[0])){
            return null;
        }
        return $data;
    }
    public static function getEntityTypes(string $usageToken, string $invocationSlug)
    {
        $validInvocation=self::setValidInvocation($usageToken, $invocationSlug);
        if(!isset($validInvocation[0])){
            return null;
        }
        $dbInvoked="entities_{$validInvocation[0]['invocation']}";
        $data=DB::connection($dbInvoked)->table('types')
        ->select('seq','selector','slug','uuid','descriptor')
        ->where('status', 1)
        ->orderBy('seq')
        ->get();
        $data=json_decode(json_encode($data),true);
        if(!isset($data[0])){
            return null;
        }
        return $data;
    }
    private static function  getSpecificInvocation($userId,$crudToken)
    {
        $data=DB::connection('appControl')->table('user_invocations')
        ->select()
        ->where('user_id', $userId)
        ->where('invocation', $crudToken)
        ->get();
        $data=json_decode(json_encode($data),true);
        if(!isset($data[0])){
            return null;
        }
        return $data;
    }
    public static function getInvocationsByUser(int $userId)
    {
        self::setExpiredTokens();
        $data=DB::connection('appControl')->table('user_invocations')
        ->select('invocation')
        ->where('user_id', $userId)
        ->where('status', 1)
        ->get();
        $data=json_decode(json_encode($data),true);
        if(!isset($data[0])){
            return null;
        }
        return $data;
    }
    private static function getLongToken(int $userId)
    {
        self::setExpiredTokens();
        $token=DB::connection('appControl')->table('tokens')
        ->select('id','token', 'user_id', 'token_type_id','type_use','status')
        ->where('token_type_id', self::$longTokenType)
        ->where('user_id', $userId)
        ->limit(1)
        ->get();
        $token=json_decode(json_encode($token),true);
        if(isset($token[0])){
            return self::flattenArray($token);
        }
        return null;
    }
    public static function getRatingDefs($params)
    {
        $pg = self::$offset;
        $rs = self::$limit;
        $stsOperator = "<";
        $stsValue = 200;             /* max value not settable (tinyInt) */
        $slugOperator = "like";
        $slugValue = '%';
        if(isset($params['page'])){
            $pg = (int)$params['page'];
        }
        if(isset($params['pageSize'])){
            $rs = (int)$params['pageSize'];
        }
        if(isset($params['status'])){
            $stsOperator = "=";
            $stsValue = (int)$params['status'];
        }
        if(isset($params['slug'])){
            $slugOperator = "=";
            $slugValue = $params['slug'];
        }
        $skipValue=($pg-1)*$rs;
        $data=DB::connection('balance_pjn')->table('transaction_ratings')
            ->select()
            ->where('status', $stsOperator, $stsValue)
            ->where('slug', $slugOperator, $slugValue)
            ->orderby('id')
            ->skip($skipValue)
            ->take($rs+1)
            ->get();
        $data=json_decode(json_encode($data),true);
        $retArray['parameters']['page'] = $pg;
        $retArray['parameters']['pageSize'] = $rs;
        $retArray['parameters']['status'] = "$stsOperator $stsValue";
        $retArray['parameters']['slug'] = "$slugOperator $slugValue";
        $moreRecs=false;
        $countRecs=count($data);
        if($countRecs>$rs){
            $moreRecs=true;
            unset($data[$countRecs-1]);
        }
        $retArray['parameters']['moreRecordsExist']=$moreRecs;
        if(!isset($data[0]['id'])){
            $retArray['data']=null;
            return $retArray;
        }
        $retArray['parameters']['moreRecordsExist'] = $moreRecs;
        $retArray['data'] = $data;
        return $retArray;
    }
    private static function getShortToken(string $tkn,int $userId)
    {
        self::setExpiredTokens();
        $token=DB::connection('appControl')->table('tokens')
        ->select('id','token', 'user_id', 'token_type_id','type_use','status')
        ->where('token_type_id', self::$shortTokenType)
        ->where('token', $tkn)
        ->where('user_id', $userId)
        ->limit(1)
        ->get();
        $token=json_decode(json_encode($token),true);
        if(isset($token[0])){
            return self::flattenArray($token);
        }
        return null;
    }
    private static function getTokenByUserId(int $tokenTypeId, int $userId)         //OK
    {
        $token=DB::connection('appControl')->table('tokens')
        ->select('id','token', 'token_type_id','type_use','expires','usage_count','status')
        ->where('user_id', $userId)
        ->where('token_type_id', $tokenTypeId)
        ->orderBy('id','desc')
        ->limit(1)
        ->get();
        $token=json_decode(json_encode($token),true);
        if(isset($token[0])){
            return self::flattenArray($token);
        }
        return null;
    }
    private static function getValidTokenByUserId(int $tokenTypeId, int $userId)         //OK
    {
        $token=DB::connection('appControl')->table('tokens')
        ->select('id','token', 'token_type_id','type_use','expires','usage_count','status')
        ->where('user_id', $userId)
        ->where('status',1)
        ->where('token_type_id', $tokenTypeId)
        ->limit(1)
        ->get();
        $token=json_decode(json_encode($token),true);
        if(isset($token[0])){
            return self::flattenArray($token);
        }
        return null;
    }
    static function getToken(string $token, int $typeId)                     //21    02  07
    {
        self::setExpiredTokens();
        $token=DB::connection('appControl')->table('tokens')
        ->select('id','token', 'user_id','type_use','status')
        ->where('token', $token)
        ->where('token_type_id',$typeId)
        ->get();
        $token=json_decode(json_encode($token),true);
        if(count($token)==1){
            return self::flattenArray($token);
        }
        return null;
    }
    static function get_setShortToken(string $sToken, int $userId)
    {
        $retArray['status']=401;
        $longTokenLife=env('LONG_TOKEN_LIFE');
        $shortTokenLife=env('SHORT_TOKEN_LIFE');
        self::setExpiredTokens();
        $shortTokenValid=self::getValidToken(self::$shortTokenType,$userId);
        if(isNull($shortTokenValid)){
            $retArray[self::$myName]="remove::".__LINE__;
            $retArray['shortToken']="Invalid";
            return $retArray;
        }
        if(isset($shortTokenValid['id'])){
            // st token still valid

            $expiresAt=date('Y-m-d H:i:s', time()+$shortTokenLife);
            self::updateXpireDateToken($shortTokenValid['id'],$expiresAt);
            $expiresAt=date('Y-m-d H:i:s', time()+$longTokenLife);
            self::updateXpireDateToken($longToken['id'],$expiresAt);
            $retArray['status']=200;
            $retArray[self::$myName]="remove::".__LINE__;
            $retArray['shortToken']=$shortToken['token'];
            $retArray['user_id']=$userId;
            return $retArray;
        }
        $longToken=self::getValidToken(self::$longTokenType,$userId);

        /* valid short token not found - regen */
        if(isset($longToken['id'])){
            $expiresAt=date('Y-m-d H:i:s', time()+$longTokenLife);
            self::updateXpireDateToken($longToken['id'],$expiresAt);
            $stoken= self::generateWHPKey(32);
            self::setShortToken($sToken,$userId);
            $shortToken=self::getValidToken(self::$shortTokenType,$userId);
            $retArray['status']=200;
            $retArray['user_id']=$userId;
            $retArray['shortToken']=$shortToken['token'];
            $retArray[self::$myName]="remove::".__LINE__;
            return $retArray;
        }
        $retArray['tokens']='failure';
        $retArray[self::$myName]="remove::".__LINE__;
        return $retArray;
    }
    public static function getUserByHash(string $hash)
    {
        $hash=self::deCryp($hash);
        $user=DB::connection('appControl')->table('users')
            ->select()
            ->where('mcde', $hash)
            ->get();
        $user=json_decode(json_encode($user),true);
        if(count($user)==1){
            $user[0]['user_hash']=self::enCryp($user[0]['email']);
            return $user[0];
        }
        return null;
    }
    public static function getSitesForUserId(int $userId){
        $sites=DB::connection('sites')->table('sites_users')
        ->select('id','user_id','site_id','status','updated')
        ->where('user_id', $userId)
        ->where('status', 1)
        ->get();
        $sites=json_decode(json_encode($sites),true);
        for($i=0;$i<count($sites);$i++){
            $siteDataArray=self::getSiteById($sites[$i]['site_id']);
            $sites[$i]['slug']=$siteDataArray['slug'];
            $sites[$i]['uuid']=$siteDataArray['uuid'];
            unset($sites[$i]['site_id']);
        }
        return $sites;
    }
    private static function getSingleSitesUserIdBySiteId(int $userId,int $siteId){
        $findSites=DB::connection('sites')->table('sites_users')
        ->select()
        ->where('user_id', $userId)
        ->where('site_id', $siteId)
        ->get();
        $findSites=json_decode(json_encode($findSites),true);
        if(isset($findSites[0])){
            return 1;
        }
        return 0;
    }
    private static function getPageById(int $pageId)
    {
        $data=DB::connection('sites')->table('pages')
        ->select()
        ->where('id', $pageId)
        ->where('status', 1)
        ->get();
        $data=json_decode(json_encode($data),true);
        if(is_null($data)){
            return null;
        }
        return self::flattenArray($data);
    }
    public static function getSiteBySlug(string $siteSlug)
    {
        $data=DB::connection('sites')->table('sites')
        ->select()
        ->where('slug', $siteSlug)
        ->get();
        $data=json_decode(json_encode($data),true);
        if(is_null($data)){
            return null;
        }
        return $data[0];
    }
    public static function getSiteById(int $siteId)
    {
        $data=DB::connection('sites')->table('sites')
        ->select()
        ->where('id', $siteId)
        ->get();
        $data=json_decode(json_encode($data),true);
        if(is_null($data)){
            return null;
        }
        return $data[0];
    }
    public static function getSitePage(int $userId, string $siteSlug, string $pageSlug)
    {
        $data=null;
        $siteAccessArray=self::getSiteBySlug($siteSlug);
        if(isset($siteAccessArray['id'])){
            $valid=self::getSingleSitesUserIdBySiteId($userId,$siteAccessArray['id']);
            if($valid==1){
                $data=DB::connection('sites')->table('pages')
                ->select()
                ->where('site_id', $siteAccessArray['id'])
                ->where('slug', $pageSlug)
                ->where('status',1)
                ->get();
                $data=json_decode(json_encode($data),true);
            }
            if(!isset($data[0])){
                return null;
            }
            $data=$data[0];
            $pageArray['site']=$siteAccessArray;
            $pageArray['static']=self::getSiteStatics($siteAccessArray['id']);
            $pageArray['page']=$data;
            $pageArray['elements']=self::getPageTexts($data['id']);
            return $pageArray;
        }
        return null;
    }
    // public static function getSitePages(int $userId, string $siteUUID)
    // {
    //     $data=null;
    //     $siteAccessArray=self::getSiteByUUID($siteUUID);
    //     if(isset($siteAccessArray['id'])){
    //         $valid=self::getSingleSitesUserIdBySiteId($userId,$siteAccessArray['id']);
    //         if($valid==1){
    //             $data=DB::connection('sites')->table('pages')
    //             ->select()
    //             ->where('site_id', $siteAccessArray['id'])
    //             ->orderBy('seq')
    //             ->get();
    //             $data=json_decode(json_encode($data),true);
    //         }
    //         return $data;
    //     }
    //     return null;
    // }
    private static function getSiteStatics(int $siteId)
    {
        $data=DB::connection('sites')->table('site_static')
        ->select()
        ->where('site_id', $siteId)
        ->orderBy('seq')
        ->where('status', 1)
        ->get();
        $data=json_decode(json_encode($data));
        return $data;
    }
    private static function getPageTexts(int $pageId)
    {
        $data=DB::connection('sites')->table('page_elements')
        ->select()
        ->where('page_id', $pageId)
        ->orderBy('seq')
        ->where('status', 1)
        ->get();
        $data=json_decode(json_encode($data),true);
        return $data;
    }
    private static function getPjnAccountById(int $id)
    {
        $account=DB::connection('balance_pjn')->table('accounts')
            ->select()
            ->where('id',$id)
            ->limit(1)
            ->get();
        $account=json_decode(json_encode($account),true);
        return $account;

    }
    public static function getPjnRatingsByCompositeCheck(array $params)
    {
        // $returnArray['status']=400;
        // $ratings=DB::connection('balance_pjn')->table('transaction_ratings')
        //     ->select()
        //     ->where('slug',$requestSlug)
        //     ->where('slug',$requestSlug)
        //     ->where('slug',$requestSlug)
        //     ->where('slug',$requestSlug)
        //     ->orderBy('seq')
        //     ->get();
        // $ratings=json_decode(json_encode($ratings),true);
        // DB::disconnect();
    }
    public static function getPjnRatingsBySlug(string $requestSlug)             /* 21    04  26 */
    {
        $returnArray['status']=400;
        $ratings=DB::connection('balance_pjn')->table('transaction_ratings')
            ->select()
            ->where('slug',$requestSlug)
            ->orderBy('seq')
            ->get();
        $ratings=json_decode(json_encode($ratings),true);
        DB::disconnect();
        if(isset($ratings[0]['id'])){
            for($n=0;$n<count($ratings);$n++){
                $account=self::getPjnAccountById($ratings[$n]['account_id']);
                $ratings[$n]['destinationAccountName']=$account[0]['name'];
                $ratings[$n]['destinationAccountSlug']=$account[0]['slug'];
                $transactionType=self::getTransactionTypeById($ratings[$n]['transaction_type_id']);
                $ratings[$n]['transactionTypeName']=$transactionType[0]['name'];
                $ratings[$n]['transactionTypeSlug']=$transactionType[0]['slug'];
            }
            $returnArray['status']=200;
            $returnArray['data']=$ratings;
        }
        return $returnArray;
    }
    public static function getStatuses()
    {
        $dataArray=DB::connection('balance_pjn')->table('record_statuses')
        ->select()
        ->orderby('id')
        ->get();
        $dataArray=json_decode(json_encode($dataArray),true);
        if(isset($dataArray[0]['id'])){
            return $dataArray;
        }
        return null;
    }
    private static function getTransactionTypeById(int $id)
    {
        $data=DB::connection('balance_pjn')->table('transaction_types')
        ->select()
        ->where('id', $id)
        ->limit(1)
        ->get();
        $data = json_decode(json_encode($data),true);
        if(isset($data[0]['id'])){
            return $data;
        }
        return null;
    }
    public static function getTransactionTypes()
    {
        $trxTypesArray=DB::connection('balance_pjn')->table('transaction_types')
        ->select()
        ->orderby('id')
        ->get();
        $trxTypesArray=json_decode(json_encode($trxTypesArray),true);
        if(isset($trxTypesArray[0]['id'])){
            return $trxTypesArray;
        }
        return null;
    }
    public static function getSiteUserIdByUUID(int $userId, string $siteUUID)
    {
        $data=null;
        $siteAccessArray=self::getSiteByUUID($siteUUID);
        if(isset($siteAccessArray['id'])){
            $valid=self::getSingleSitesUserIdBySiteId($userId,$siteAccessArray['id']);
            if($valid==1){
                $data=DB::connection('sites')->table('sites')
                ->select()
                ->where('uuid', $siteUUID)
                ->where('status', 1)
                ->limit(1)
                ->get();
                $data=self::flattenArray(json_decode(json_encode($data),true));
                if(!isset($data['id'])){
                    return null;
                }
                $domain=null;
                if(isset($data['id'])){
                    $domArray=DB::connection('appControl')->table('domains')
                    ->select('domain')
                    ->where('id', $data['domain_id'])
                    ->where('status', 1)
                    ->get();
                    $domArray=self::flattenArray(json_decode(json_encode($domArray),true));
                    $domain=$domArray['domain'];
                }
                $data['domainName']=$domain;
            }
            return $data;
        }
        return Null;
    }

    public static function handleGetRequestInput(array $input)
    {
        $errArray=array();
        if(isset($input['errs'])){
            unset($input['errs']);
        }
        foreach ($input as $key => $value) {
            if(!in_array($key,self::$queryPaginationArray)){
                $errArray[]="$key -> not understood";
            }
            else{
                if(isset($input['page'])){
                    self::$offset=(int) $input['page'];
                }
                if(isset($input['records'])){
                    self::$limit=(int) $input['records'];
                }
            }
        }
        $returnArray['errs']=$errArray;
        return $returnArray;
    }


    static function setPin(int $userId)
    {
        self::setExpiredTokens();
        DB::connection('appControl')->table('tokens')
        ->where('token_type_id', self::$pinTokenType)
        ->where('user_id',$userId)
        ->update(['status' => -9]);
        $cde=md5(time());
        $pin=substr($cde,rand(1,25),6);
        $pinDays=5;
        $pinexp=time()+($pinDays*24*60*60);
        $expires=date('Y-m-d H:i:s', $pinexp);
        DB::connection('appControl')->table('tokens')->insert([
            'token_type_id' => self::$pinTokenType,
            'user_id' => $userId,
            'type_use' => 'loginPin',
            'token' => $pin,
            'expires' => $expires,
            'usage_count' => 0,
            'status' =>1]
        );
        return self::getValidToken(self::$pinTokenType,$userId);
    }
    static function setToken(array $setArray)
    {
        self::setExpiredTokens();
        $user=self::getUserByHash($setArray['user_hash']);
        if(is_null($user)){
            return null;
        }
        $userId=$user['id'];
        DB::connection('appControl')->table('tokens')
        ->where('token_type_id', $setArray['type_id'])
        ->where('user_id',$userId)
        ->update(['status' => -9]);
        $pinDays=1000;
        $pinExp=time()+($pinDays*24*60*60);
        $expires=date('Y-m-d H:i:s', $pinExp);
        if(!isset(self::$userTokenTypesArray[$setArray['type_id']])){
            return null;
        }
        $usage=self::$userTokenTypesArray[$setArray['type_id']];
        DB::connection('appControl')->table('tokens')->insert([
            'token_type_id' =>  $setArray['type_id'],
            'user_id' => $userId,
            'type_use' => $usage,
            'token' => $setArray['token'],
            'expires' => $expires,
            'usage_count' => 0,
            'status' =>1]
        );
        return self::getValidToken($setArray['type_id'],$userId);
    }
    public static function getUserByUserId(string $userId)                     //  21  02  22
    {
        $user=DB::connection('appControl')->table('users')
            ->select('id','userid','email','fullname','e_entity_id','status')
            ->where('id', $userId)
            ->where('status',  1)
            ->get();
        $user=json_decode(json_encode($user),true);
        if(count($user)==1){
            return self::flattenArray($user);
        }
        return null;
    }
    public static function getUserByApiKey(string $apiKey)
    {
        $apiKeyArray=DB::connection('appControl')->table('tokens')
        ->select('id','user_id','status')
        ->where('token', $apiKey)
        ->where('token_type_id', 1)
        ->limit(1)
        ->get();
        $user=json_decode(json_encode($apiKeyArray),true);
        if(count($user)==0){
            return null;
        }
        $userId=$user[0]['user_id'];
        return self::getUserByUserId($userId);
    }
    public static function validateOutputFields(array $data, array $outFields)
    {
        //'token'=>'pin','type_use'=>'used_for'
        $retArray=array();
        foreach ($outFields as $key => $value) {
            $retArray[$value]=$data[$key];
        }
        return $retArray;
    }
    private static function setValidInvocation(string $usageToken, string $invocationSlug)
    {
        $tokenArray=self::getUsageToken($usageToken);
        if(is_null($tokenArray)){
            return null;
        }
        $userId=$tokenArray['user_id'];
        $invocation=self::getSpecificInvocation($userId,$invocationSlug);
        if(is_null($invocation)){
            return null;
        }
        return $invocation;
    }
    private static function getUserByUserName(string $userName)
    {
        $user=DB::connection('appControl')->table('users')
            ->select('id','userid','email','fullname','status')
            ->where('userid', $userName)
            ->get();
        $user=json_decode(json_encode($user),true);
        if(count($user)==1){
            return self::flattenArray($user);
        }
        return null;
    }
    public static function getUserById(int $id)
    {
        $user=DB::connection('appControl')->table('users')
            ->select('userid','fullname','email','status')
            ->where('id', $id)
            ->get();
        $user=json_decode(json_encode($user),true);
        if(count($user)==1){
            $user[0]['user_hash']=self::enCryp($user[0]['email']);
            unset($user[0]['email']);
            return self::flattenArray($user);
        }
        return null;
    }
    public static function getUserByEmail(string $email)
    {
        $user=DB::connection('appControl')->table('users')
            ->select('id','userid','mcde','fullname','status')
            ->where('email', $email)
            ->get();
        $user=json_decode(json_encode($user),true);
        if(count($user)==1){
            $user[0]['user_hash']=self::enCryp($email);
            unset($user[0]['mcde']);
            return self::flattenArray($user);
        }
        return null;
    }
    // private static function getUserByHash(string $hash)
    // {
    //     $hash=self::deCryp($hash);
    //     $user=DB::connection('appControl')->table('users')
    //         ->select()
    //         ->where('mcde', $hash)
    //         ->whereBetween('status',[1,3])
    //         ->get();
    //     $user=json_decode(json_encode($user),true);
    //     if(count($user)==1){
    //         $user[0]['user_hash']=self::enCryp($user[0]['email']);
    //         return self::flattenArray($user);
    //     }
    //     return null;
    // }

    public static function getUserByPin(string $userHash) //waynep
    {
        self::setExpiredTokens();
        $userArray=self::getUserByHash($userHash);
        if(!isset($userArray['id'])){
            return null;
        }
        $tokensArray=self::getTokenByUserId(self::$pinTokenType,$userArray['id']);
        if(!isset($tokensArray['id'])){
            return null;
        }
        $validUntil=time()+(5*24*60*60); //3 days
        $expires=date("Y-m-d H:i:s",$validUntil);
        $id=$tokensArray['id'];
        $uses=$tokensArray['usage_count']+1;
        DB::connection('appControl')->table('tokens')
        ->where('id', $id)
        ->update(['status' => 1, 'expires' => $expires,'usage_count' => $uses]);
        $tokensArray=self::getValidToken(self::$pinTokenType,$userArray['id']);
        unset($tokensArray['id']);
        unset($tokensArray['user_id']);
        unset($tokensArray['token_type_id']);
        $tokensArray['apiDL']=__LINE__;
        return $tokensArray;
    }

    public static function xxxsetUser(array $inputs)
    {
        $retArray['status']=409;
        date_default_timezone_set ("Africa/Johannesburg");
        // try a get for the user
        $user=self::getUserByUserName($inputs['userid']);
        if(isset($user['id'])){
            $retArray['message']="User Name exits";
            return $retArray;
        }
        $user=self::getUserByEmail($inputs['email']);
        if(isset($user['id'])){
            $retArray['message']="User email exits";
            return $retArray;
        }
        $inputs['mcde']=md5($inputs['email']);
        DB::connection('appControl')->table('users')->insert($inputs);
        $user=self::getUserByEmail($inputs['email']);
        if(isset($user['id'])){
            $returnArray['status']=201;
            $returnArray['data']=$user;
            return $returnArray;
        }
        return null;
    }

    public static function setUserPin(string $hash)
    {
        $hash=self::deCryp($hash);
        $user=self::getUserByHash($hash);
        if(is_null($user)){
            $return['status']=404;
            $return['message']="Invalid Hash";
            return $return;
        }
        $pinArray=self::setPin($user['id']);
        if(is_null($pinArray)){
            $pinArray['status']=404;
            $pinArray['msg']="Pin Not Found";
            return $pinArray;
        }
        $pinArray['status']=201;
        unset($pinArray['id']);
        unset($pinArray['user_id']);
        unset($pinArray['token_type_id']);
        $pinArray['apiDL']=__LINE__;
        return $pinArray;
    }
    private static function updateXpireDateToken(int $id, $expiresAt)
    {
        DB::connection('appControl')->table('tokens')
        ->where('id', $id)
        ->update(['expires' => $expiresAt]);
    }

    public static function validateInputs(array $pattern, array $inputs)
    {
        $returnArray['status']=400;
        foreach ($pattern as $key => $value) {
            $checkArray=explode("~",$value);
            switch ($checkArray[0]) {
                case "1":
                    if(!isset($inputs[$key])){
                        $returnArray['msg']="notSetJson($key)";
                        return $returnArray;
                    }
            }
            switch ($checkArray[1]) {
                case 'str':
                    if(!is_string($inputs[$key])){
                        $returnArray['msg']="JsonNotString($key)";
                        return $returnArray;
                        break;
                    }
                    if(strlen($inputs[$key])>(int)$checkArray[2]){
                        $returnArray['msg']="JsonStringLength($key exceeds {$checkArray[2]})";
                        return $returnArray;
                        break;
                    }
                    break;
                case 'eml':
                    $posAt=strpos($inputs[$key],"@");
                    if(($posAt==0) OR (!$posAt)){
                        $returnArray['error']=__LINE__;
                        $returnArray['msg']="eMail ($posAt) does not appear valid";
                        return $returnArray;
                        break;
                    }
                    $dotAt=strpos($inputs[$key],".");
                    if(($dotAt==0) OR (!$dotAt)){
                        $returnArray['error']=__LINE__;
                        $returnArray['msg']="eMail ($posAt) does not appear valid";
                        return $returnArray;
                        break;
                    }
                    if(strlen($inputs[$key])>(int)$checkArray[2]){
                        $returnArray['msg']="JsonStringLength($key exceeds {$checkArray[2]}";
                        return $returnArray;
                        break;
                    }
                    break;
                case 'int':
                    if(!is_int((int)$inputs[$key])){
                        $returnArray['msg']="JsonNotInteger($key)";
                        return $returnArray;
                        break;
                    }
                    if(strlen($inputs[$key])>(int)$checkArray[2]){
                        $returnArray['msg']="JsonInteger($key exceeds {$checkArray[2]}";
                        return $returnArray;
                        break;
                    }
                    break;
                case 'bool':
                    if(!is_bool($inputs[$key])){
                        $returnArray['msg']="JsonNotBool($key must be (true -OR- false) for {$checkArray[1]}";
                        return $returnArray;
                        break;
                    }
                    break;
            }
        }
        $returnArray['status']=200;
        return $returnArray;
    }
}