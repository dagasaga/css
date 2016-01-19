<?php
// dispatch controller/action

// Verify permission based on $_SESSION['css_user_id']
// IF NULL: redirect to login page or register or forgot

if (empty($_SESSION['css_user_id'])) {
    if ($action == 'submit') {
        // do nothing, means user wants to be authenticated
    } elseif ($controller == 'users') {
        // do nothing, means user either forgot or want to register
    } else {
        $controller = 'login';
        $action = 'login';
    }
} else {
    // resets user login time. value is defined in /config/config.php as global
    $date = new DateTime;

    if (($date->format('U') - $_SESSION['last_login'])/60 > SESSION_DURATION) {
        // if last time router.php was called (that is, that the user has done anything on the website),
        // automatically logs out user
        $controller = 'login';
        $action = 'logout';
    } else {
        // if session time has not expired, just reset time counting
        $_SESSION['last_login'] = $date->format('U');

    }
}

// controller/action map
// ----------------------
// TODO: replace this legacy map by the database array - not a priority, just because every controller/action must be added 2 times (here + database).
// The data in the database is being used to build ACLs, based on users and profiles (each profile has its own ACL components)
// This database information can be edited through the [Configuration]->[Admin] area in the website (you must have ADMIN privileges, of course)
//
// $controller_list is an array used locally, i.e., only inside routes.php. It serves to have a visual concept of the website, as well as
// when adding controllers/action code.
//
$controller_list = array (
    'login'         => 'login', 'logout', 'submit', 'forgot', 'register',
    'home'          => 'index', 'setschoolyear',
    'school_years'  => 'index', 'add',                      'delete',
    'program'       => 'index', 'add',                      'delete',
    'subjects'      => 'index', 'add',                      'delete',
    'levels'        => 'index', 'add',                      'delete',
    'weekdays'      => 'index', 'add', 'details', 'update',
    'students'      => 'index', 'add', 'details', 'update', 'delete', 'export', 'details2', 'test',
    'attendances'   => 'index', 'save', 'details', 'update', 'delete', 'export', 'form_table',
    'teachers'      => 'index', 'add', 'details', 'update', 'delete', 'export',
    'courses'       => 'index', 'add', 'details', 'update', 'delete',
    'curricula'     => 'index', 'add', 'details', 'update', 'delete',
    'classes'       => 'index', 'add', 'details', 'update', 'delete', 'move',   'remove',
    'time'          => 'index', 'add', 'details', 'update',
    'timetable'     => 'index', 'add', 'details', 'update', 'delete', 'export', 'show', 'edit', 'add_form',
    'timetable2'    => 'index', 'add', 'details', 'update', 'delete',
    'exams'         => 'index', 'add', 'details', 'update', 'delete', 'export',
    'results'       => 'index', 'add', 'details', 'update', 'delete', 'export',
    'configuration' => 'index',
    'about'         => 'index',
    'users'         =>          'register', 'forgot', 'reset', 'submit',
    'contact'       => 'index',
    'export'        => 'students', 'teachers', 'attendances', 'results', 'timetables',
    'admin'         => 'index', 'log', 'users_index', 'users_add', 'users_details', 'users_update', 'users_delete', 'acl_index', 'acl_add', 'acl_details', 'acl_delete', 'acl_update', 'controllers_index', 'controllers_add', 'controllers_details', 'controllers_delete', 'controllers_update', 'profiles_index', 'profiles_add', 'profiles_details', 'profiles_delete', 'profiles_update', 'show_acl_map'
);


// Test if [controller] and [action] are found in controller/action map above
if (array_key_exists($controller, $controller_list)){
    // controller exists in controller_list array
    // configure internal controller references
    $controller_filename = 'controllers/'.$controller.'Controller.php';
    $controller_class = $controller.'Controller';

    // configure internal model references
    $model_filename = 'models/'.$controller.'Model.php';
    $model_class = $controller.'Model';

    if (file_exists($controller_filename)) {
        // controller file exists, require it once to include its code

        require_once $controller_filename;

        // creates the application object: $app
        $app = new $controller_class;

        if (in_array($action, $controller_list)){
            // action exists inside controller_list array
            /* Output is the array that contains all data returned from [controller].
             * It means that my system is not a PURE MVC, since the View does not talks with the Model.
             * Thought it would be simpler like this.
             *
             * So any controller must return an $output[] variable, with the following keys (you can add more, just
             * remember to use them in your View file):
             *
             * $output = array(
             *  'page'   => $string,    // php view file
             *  'title'  => $string,    // page title
             *  'content'=> $string,    // whole html content
             *  'message'=> $string,    // error/success/etc msg
             *  'footer' => $string,    // footer msg
             * );
             */

            // ACCESS CONTROL LIST - ACL
            // controls user access to controller/actions based on database information
            // this array is stored in $_SESSION['acl_map'], which lasts until user logs out
            // and is loaded when user logs in - that's when the information on the user is
            // collected, including its profile and ACCESS CONTROL LIST associated with it
            //
            // $_SESSION['acl_map'] =
            //      array ('controller.action1' => X,
            //             'controller.action2' => Y...);
            // where 'X' corresponds to 2 (access denied) or 1 (access granted)

            // In order to check, creates $acl_key based on controller and action
            $acl_key = $controller.'.'.$action;

            // if the variable has been set AND current controller/action are found in the acl_map, test its
            if (isset($_SESSION['acl_map'][$acl_key])) {
                if ($_SESSION['acl_map'][$acl_key] == 2) {
                    // user has access denied
                    $output['content'] = "Sorry, you do not have enough clearance to {$action} {$controller}.";
                    $output['menu2'] = ' - ACCESS DENIED (strict)- ';
                } else {
                    // user has access granted (actually, anything different from 2 grants access)
                    // execute action
                    $output = $app->{$action} ();
                    $output['menu2'] = ' - ACCESS GRANTED (positive grant) - ';
                }
            } else {
                // user has access granted by default; that is, if the controller.action is not configured for the profile
                // load model (if file exists)
                if (file_exists($model_filename)) {
                    require_once $model_filename;
                }

                // execute action
                $output = $app->{$action} ();
                $output['menu2'] = ' - ACCESS GRANTED (absence of denial) -';
            }

            // TODO: if $action = delete, should be SURE to delete: BEFORE executing a delete action,
            // ask 'Are you sure you want to $action $controller ?'
            // for this, hook from action ? hook here or inside each controller ?
            // for practicality, hook here based on action. thus, IT IS imperative that when creating actions
            // the correct verbs must be used

            // call view
            if (empty($output['page']))
            {
                // call standard page (views/main.php) if no page was specified
                $output['page'] = 'views/main.php';
            }
            elseif (!file_exists($output['page']))
            {
                // view file was specified, however it does not exist
                $output['content'] = 'view file does not exist';
                $output['page'] = 'views/main.php';
            }

            // TODO: priority: high, reason: clarity
            // move main_menu OUT from routes.php
            // should be accessible to login.login, so when an admin logs in he/she can see additional links
            $output['main_menu'] = "
                <li><a href='?controller=home&action=index'><img src='ico/house204.png' alt='' width='20'> Home</a></li>
                <li><a href='?controller=students&action=index'><img src='ico/schoolboy1.png' alt='' width='20'> Students</a></li>
                <li><a href='?controller=teachers&action=index'><img src='ico/teacher4.png' alt='' width='20'> Teachers</a></li>
                <li><a href='?controller=configuration&action=index'><img src='ico/configuration24.png' alt='' width='20'> Configuration</a></li>
                <li></li>";

            $output['upright_menu'] = "
                <a href='?controller=about&action=index'>About</a> |
                <a href='?controller=contact&action=index'>Contact</a> |
                <a href='?controller=admin&action=log'>log</a> |
                <a href='?controller=login&action=logout'>Logout</a>
                ";

            require_once $output['page'];
        } else {
            echo "controller [{$controller_class}] is in controller array; however, action {$action} is not.<br>";
        }

    } else {
        echo "controller [{$controller_class}] is in controller array; however, its file was not found: [{$controller_filename}]<br>";
    }

} else {
    // controller not found
    echo "controller [{$controller}] not in controller array<br>";
}