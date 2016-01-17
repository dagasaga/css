<?php

class security {
    private $token;

    public function __construct () {}

    public function set_token (){

        //$generated_token = bin2hex(random_bytes(20));       // PHP 7
        $generated_token = bin2hex(openssl_random_pseudo_bytes(20));  // PHP >= 5.3.0

        $this->token = $generated_token;

        $_SESSION['token'] = $generated_token;

    }

    public function get_token (){
        return $this->token;
    }

    public function check_token () {

        if (isset($_POST['token'])) {
            // token is set into $_POST mode
            $token = $_POST['token'];
        } elseif (isset($_GET['token'])) {
            $token = $_GET['token'];
        } else {
            // no token could be recovered!!
            echo "NO TOKEN WAS PASSED!<br>";
            die();
        }

        if (!isset($_SESSION['token'])) {
            echo "SESSION token is not set!<br>";
            die();
        }

        if ($_SESSION['token'] == $token) {
            // token checks
            // Just keep going

        } else {
            // for some reason (cross hacking) token does not check
            $content  = "Security breach!<br>";
            $content .= "The operation was CANCELLED. This is necessary to safeguard the database from unwarranted actions.<br>";
            $content .= "<p>To avoid such errors, do not REFRESH (F5 key) pages neither go back in page history; use instead the buttons and links provided.<br>";
            $content .= "<p>If this message persists, contact the administrator.<br>";
            $content .= "<img src='/css/images/aec_sad_logo.png' alt='sad logo'><br>";
            $content .= "Click <a href='?controller=home&action=index'>HERE</a> to continue.";

            $_SESSION['log'] .= new timestamp("Security breach - token does not match!");
            echo $content;
            die ();
        }
    }
}