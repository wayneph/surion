<?php namespace presentation;
$file=$_SERVER['DOCUMENT_ROOT']."/include/appControl/Bl.php";
include_once($file);
use BL\BL;
class Present extends BL
{
    private $myName="login";
    public function __construct()
    {
        $this->trace[]="({$this->myName})::@".date("H:i:s");
        parent::__construct($this->myName);
        $menu=parent::setMenu("Index");
        $this->html=str_replace("###menu###", $menu, $this->html);
        //parent::setHtmlLogs($this->myName);
        echo($this->html);
        exit();
    }
}
new Present;


