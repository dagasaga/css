<?php

class loginController {
    public function __construct(){}
    public function __clone(){}
    public function __destruct(){}

    public function login (){
        // 1st time (&submit is not set) or error=true - just show form and/or error message
        // 2nd time (&submit==yes) - check
            // if ok, redirect to home
            // if not, set $content to error message and just show form again
        if (!isset($_SESSION['log'])) $_SESSION['log'] = new timestamp("login");
        $content = "";

        $output ['page'] = 'views/login/index.php';

        //$header = 'CSS AEC-Foyer Lataste ADTJK System V2.0';

        $token_handler = new security();
        $token_handler->set_token();
        $token = $token_handler->get_token();

        $login_form = "
        <form action='?controller=login&action=submit' method='post'>
          Username: <input type='text' name='username' placeholder='ex: john' autofocus>
            <br>
          Password: <input type='password' name='password'>
          <br><br>
          <input type='submit' value='login'>
          <input type='hidden' value='{$token}' name='token'>
        </form>

        ";

        $current_year = date("Y");
        $footer = "CSS AEC-Foyer Lataste ADTJK Copyright {$current_year} All Rights Reserved - Webmaster: ivan.bragatto@gmail.com";

        // $output ['header']     = $header;
        $output ['login_form'] = $login_form;
        $output ['content']    = $content;
        $output ['footer']     = $footer;

        return $output;
    }

    public function submit ()
    {

        $output = array();
        if (isset($_POST['username']) and isset($_POST['password'])) {
            $username = trim($_POST['username']);
            $password = trim($_POST['password']);

            // TODO: implement password hashing check
            //

            $sql = 'SELECT user_id, email, profile_id, password FROM users WHERE username=?';
            $data = array ($username);
            $connection = new database();
            $result = $connection->fetchAll($sql, $data);

            $hash = $result[0]['password'];

            /* REQUIRES php >= 5.5.0

            if (password_verify($password, $hash)) {
                echo "ok";
            } else {
                echo "not ok";
            }
            //die();
            */


            //if ($connection->get_row_num()==1 ) {
            if (crypt ($password, MY_SALT) == $hash) {       // for PHP <=5.5.0
                /* GATEWAY: define here all session variables based on user:
                 * User Variables:
                 *      1. css_username
                 *      2. css_user_id
                 *      3. css_email
                 *      4. css_profile_id
                 * System variables:
                 *      1. main_menu
                 *      2. upright_menu
                 *      3. last_login
                 *      4. controllers->actions (array)
                 *          [controller][action][permission] where [profile_id]=[user_profile_id]
                 *      5. current_school_year -> max school year. If NO school year is configured, insert current year and select it.
                 */

                $_SESSION['css_username']   = $username;

                $_SESSION['log'] .= new timestamp("user {$username} has logged in");

                $_SESSION['css_user_id']    = $result[0]['user_id'];
                $_SESSION['css_email']      = $result[0]['email'];
                $_SESSION['css_profile_id'] = $result[0]['profile_id'];

                $_SESSION['user_ip']        = $_SERVER['REMOTE_ADDR'];     // small security control

                // Requires PECL  extension to work
                //$country = geoip_country_name_by_name($_SESSION['user_ip']);

                $sql = "INSERT INTO login_activity (user_id, profile_id, username, email, ip_address) VALUES ('".$result[0]['user_id']."', '".$result[0]['profile_id']."', '".$username."', '".$result[0]['email']."', '".$_SESSION['user_ip']."')";

                $login_activity = $connection->query($sql);

                // TODO: acl structure


                // results comes as:
                // $acl_results = array (
                //      0 => array (
                //          'controller' => 'about',
                //          'c_action'   => 'index',
                //          'active_id'  => 1),
                //      1 => array (....

                // Refactor to:
                // $acl_results_refactored = array(
                //      'about' => array('index'       => 1),
                //
                //      'admin' => array('index'       => 1,
                //                       'log'         => 1,
                //                       'users_index' => 1));

                // 1. extract all controllers from DB which corresponds to user
                // 2. foreach $controllers['controller'] add $c_action and corresponding permission


                $sql = "SELECT controllers.controller, controllers.c_action, acl.active_id
                        FROM acl
                        JOIN controllers ON controllers.controller_id = acl.controller_id
                        WHERE acl.profile_id=?
                        GROUP BY controllers.controller_id ASC
                        ";

                $data = array($_SESSION['css_profile_id']);

                $acl_results = $connection->fetchAll($sql, $data);
                //var_dump($acl_results);

                $acl_map = array();
                $i=0;
                foreach ($acl_results as $row) {
                    $acl_map[$row['controller'].'.'.$row['c_action']] = $row['active_id'];     // preferable way to add a single row to an existing array
                    $i++;
                }

                //var_dump ($acl_map);

                $_SESSION['acl_map'] = $acl_map;

                $date = new DateTime();

                $_SESSION['last_login'] = $date->format('U');

                $sql = "SELECT school_year_id, school_year
                        FROM school_years
                        ORDER BY school_year DESC
                        LIMIT 1";

                $school_years_result = $connection->query($sql);

                if ($connection->get_row_num() == 0) {
                    // no school year has been registered, INSERT INTO school_years the current school year
                    $date = new DateTime();
                    $current_year = $date->format('Y');
                    $current_month= $date->format('m');

                    if ($current_month>=9 and $current_month<=12){
                        $current_school_year = $current_year.'/'.($current_year +1);
                    } else {
                        $current_school_year = ($current_year -1).'/'.$current_year;
                    }
                    $current_school_year = strval($current_school_year);

                    $insert_school_year_sql = "INSERT INTO school_years (school_year)
                                               VALUES ('".$current_school_year."')";
                    $connection->query($insert_school_year_sql);
                    // Get last school_year_id and assign to $_SESSION['current....
                    $_SESSION['current_school_year_id'] = $connection->last_Inserted_id();
                    $_SESSION['current_school_year'] = $current_school_year;
                } else {

                    $_SESSION['current_school_year_id'] = $school_years_result[0]['school_year_id'];
                    $_SESSION['current_school_year']    = $school_years_result[0]['school_year'];
                }

                // TODO: load main_menu and upright_menu htmls in $_SESSION['main_menu etc
                // hits DB, retrieves htmls from profiles and menus tables etc
                //  1. tables: profiles, menus, htmls
                //  2. fields: menus(menu_id, name (main, upright etc), html_id (from htmls table, sort of html library), profile_id)
                //     from other tables, the corresponding IDs
                // SQL should select all html from htmls table where profile in menus table is the same as current user profile_id
                // $sql = 'SELECT menus.name, htmls.html from htmls JOIN menus ORDER BY menu_id WHERE $_SESSION['css_profile_id'] = menus.profile_id';
                // retrieve $menu_name from query
                // concatenate html records sequentially (query was ordered by menu_id, which is NOT Auto-incremented)
                // do while etc $html;
                // $output [$menu_name]=$html;

                // TODO: retrieve controller/action permissions from profile, permissions and ctrl_actions tables
                // Assign $_SESSION['controller']['action'] CRUD, so index.php can check permission for
                // current user to execute controller/action

                header('Location: http://'.WEBSITE_URL.'/index.php?controller=home&action=index');

            } else {
                // username and password do not match
                // return error page with link to retry
                $output['page'] = 'views/login/index.php';
                $header = 'CSS AEC-Foyer Lataste ADTJK System V1.0';
                // $content ='no matches (or more than one, which means inconsistencies in the DB!)<br>';
                $content = "Credentials do not match<br><br>Click <a href='?controller=login&action=login'>here</a> to retry<br><br>";
                $footer = 'CSS AEC-Foyer Lataste ADTJK Copyright and stuff. Webmastermind: ivan.bragatto@gmail.com';

                $output['header']  = $header;
                $output['content'] = $content;
                $output['footer']  = $footer;

            }
            }
        return $output;
    }


    public function logout (){
        session_start();
        session_destroy();

        header('Location: http://'.WEBSITE_URL.'/index.php?controller=login&action=login') ;
    }
}