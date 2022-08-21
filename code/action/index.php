<?php namespace presentation;
$file=$_SERVER['DOCUMENT_ROOT']."/include/appControl/Bl.php";
include_once($file);
use BL\BL;
class Present extends BL
{
    private $myName="action";
    public function __construct()
    {
        parent::getEnv();
        parent::validateAccess($this->myName);
        if(strlen($_SERVER['QUERY_STRING'])<64){
            header("Location: ../index.php",301);
            exit();
        }
        /* write last encoded action to cookie */
        $addCookie['lastAction']=$_SERVER['QUERY_STRING'];
        parent::writeCookie($addCookie);
        /*  get the replaces Array */
        $replacesArray=parent::evaluateAction();
        // echo("<br>Array:replacesArray(".__LINE__."({$this->myName}))<br><pre>"); print_r($replacesArray); echo("</pre><hr>");
        $replacesArray['page']=$this->myName;//"action";
        /* activate base page */
        parent::__construct($replacesArray['page']);
        /* set menu */
        $menu=parent::setMenu($replacesArray['page']);
        $this->html=str_replace("###menu###", $menu, $this->html);
        /* action */
        $this->html=str_replace("###action###", $replacesArray['action'], $this->html);
        /* read */
        if(substr($replacesArray['subForm'],0,4)=="read"){
            $replacesArray['subForm']=$_SERVER['DOCUMENT_ROOT']."/assets/templates/read.html";
            $subFormHtml=file_get_contents($replacesArray['subForm']);
            $this->html=str_replace("###subForm###",$subFormHtml,$this->html);
            $replaceReads="";
            $array=$replacesArray['dataItem'];
            foreach ($array as $key => $value) {
                $replaceReads.="
                <tr>
                    <td>$key</td>
                    <td>$value</td>
                </tr>";
            }
            $this->html=str_replace("###readTDvalues###", $replaceReads, $this->html);
        }
        else{
            $replacesArray['subForm']=$_SERVER['DOCUMENT_ROOT']."/assets/templates/{$replacesArray['subForm']}";
            $subFormHtml=file_get_contents($replacesArray['subForm']);
            $this->html=str_replace("###subForm###",$subFormHtml,$this->html);
            /* data Items Replace */
            foreach ($replacesArray['dataItem'] as $key => $value) {
                $this->html=str_replace("###$key###", $value, $this->html);
            }
            $array=$replacesArray['subFormReplaces'];
            foreach ($array as $key => $value) {
                $this->html=str_replace("~~~$key~~~", $value['ht'], $this->html);
            }
        }
        echo($this->html);
        exit();
    }
}
new Present;