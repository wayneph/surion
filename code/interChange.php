<?php namespace presentation;
$file=$_SERVER['DOCUMENT_ROOT']."/include/appControl/Bl.php";
include_once($file);
use BL\BL;
class Present extends BL
{
    private $myName="interChange";
    public function __construct()
    {
        $this->trace[]=$this->myName.".php  @".date("H:i:s");
        parent::__construct($this->myName);
        //parent::getEnv();
        //parent::validateAccess($this->myName);
        $postArray=$_POST;
        if(isset($_POST['postName'])){
            $postArray=$_POST;
            unset($_POST);
            switch ($postArray['postName']) {
                case "login":
                    parent::setLoginAppUser($postArray);
                    break;
                case "ownerPatch":
                    parent::setOwnerPatch($postArray);
                    break;
                default:
                    header("Location:index.php?message=That action was not supported - Apologies",301);
                    exit();
                    break;
              }
        }
        echo("<br>post not set correctly");exit();
    }
}
new Present;