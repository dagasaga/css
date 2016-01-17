<?php
class contactController {
    public function index(){
        $content = "<p>CONTACTS";

        $content.= "<p>web mastermind: <br>";
        $content.= "=> email: ivan.bragatto@gmail.com<br>";
        $content.= "=> facebook: ivan.bragatto<br>";
        $content.= "=> skype: ivan.bragatto<br>";

        $output['content']=$content;
        return $output;
    }
}