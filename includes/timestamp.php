<?php
class timestamp {

    public $ts;

    function __construct ($msg){
        $date = new DateTime();
        if (isset($_SESSION['css_username'])) {
            $username = $_SESSION['css_username'];
        } else {
            $username = 'none';
        }
        if (isset($_GET['controller'])) {
            $controller = $_GET['controller'];
            if (isset($_GET['action'])) {
                $action = $_GET['action'];
            } else {
                $action = "none";
            }
        } else {
                $controller = "none";
        }


        $this->ts =  '<br>['.$date->format('d-m-Y H:i:s').'] ['.$username.']['.$controller.']['.$action.'] '.$msg;
    }

    public function __toString(){
        return $this->ts;

    }
}