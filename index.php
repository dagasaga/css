<?php
require_once 'load_lib.php';

if (!session_start()) {
    session_start();

}

// sets the default controller in case it is empty
if (!empty($_GET['controller'])){
    $controller = $_GET['controller'];
} else {
    $controller = 'home';
}

// sets the default action in case it is empty
if (!empty($_GET['action'])){
    $action = $_GET['action'];
} else {
    $action = 'index';
}

if (!isset($_SESSION['acl_map'])) {
    // no acl_map means user has not yet logged in. need to authorize at least the login.login, login.submit, users.register and users.forgot
    // $_SESSION['acl_map'] keeps a controller/action mapped to access codes (0 = denied, 1 = granted).
    // so, controller login with action (method) login => 1 means user has access to login method inside login controller
    $_SESSION['acl_map'] = array(
        'login.login'    => 1,
        'login.submit'   => 1,
        'users.forgot'   => 1,
        'users.register' => 1,
        'home.index'     => 1);
}

// calls routes.php, which will pre-process the url request
require_once 'routes.php';
