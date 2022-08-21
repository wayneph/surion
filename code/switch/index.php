<?php namespace presentation;
$file=$_SERVER['DOCUMENT_ROOT']."/include/appControl/Bl.php";
include_once($file);
use BL\BL;
class Present extends BL
{
    private $myName="switcher";
    public function __construct()
    {
        parent::getEnv();
        parent::validateAccess($this->myName);
        $this->trace[]=$this->myName.".php  @".date("H:i:s");
        if(strlen($_SERVER['QUERY_STRING'])<64){
            header("Location: ../index.php",301);
            exit();
        }
        $replacesArray=parent::evaluateSwitch();
        parent::__construct($replacesArray['page']);
        $menu=parent::setMenu($replacesArray['page']);
        $specificReplaces=$replacesArray['replace'];
        foreach($specificReplaces as $key => $val) {
            $this->html=str_replace("###$key###", $val, $this->html);
        }
        $this->html=str_replace("###menu###", $menu, $this->html);
        /*  output */
        echo($this->html);
    }
}
new Present;


