<?php
class adminController {
    public function __construct(){}
    public function __clone(){}
    public function __destruct(){}

    public function users_index(){
        // show tables: users, profiles, positions and departments
        //
        $connection = new database();
        $table = new simple_table_ops();

        $content = "<div class='link_button'>
                        <a href='?controller=admin&action=acl_index'>Access Control List</a>
                        <a href='?controller=admin&action=profiles_index'>Profiles</a>
                    </div>";

        /* CONFIGURES DROP DOWN Menus (2 in this case: programs and genre) */


        $sql = 'SELECT profile_id, profile FROM profiles ORDER BY profile';
        $profiles_result = $connection->query($sql);

        $sql = 'SELECT position_id, position FROM positions ORDER BY position';
        $positions_result = $connection->query($sql);

        $sql = 'SELECT department_id, department FROM departments ORDER BY department';
        $departments_result = $connection->query($sql);

        $drop_down = array(
            'profile_id'    => array('profile'    => $profiles_result),
            'position_id'   => array('position'   => $positions_result),
            'department_id' => array('department' => $departments_result)
        );

        $table->set_drop_down($drop_down);
        /********************************************************************/

        /* CONFIGURES Form structure */

        $top_form = array(
            'action'   => '?controller=admin&action=users_add',
            'div'      => "class='solitary_input'",
            'div_button' => "class='submit_button1'",
            'method'   => 'post',
            'id'       => 'top_form',
            'elements' => array(
                //username, password, profile_id (drop_down), email, phone, position_id, department_id
                1  => array ('text'      => 'username'),
                2  => array ('text'      => 'password'),
                3  => array ('drop_down' => 'profile_id'),
                4  => array ('text'      => 'email'),
                5  => array ('text'      => 'phone'),
                6  => array ('drop_down' => 'position_id'),
                7  => array ('drop_down' => 'department_id'),
                8  => array ('submit'    => 'add')
            )
        );

        $table->set_top_form($top_form);
        /********************************************************************/

        /* CONFIGURES Main table contents (from MySQL) */

        $columns = array('Username', 'Password', 'Profile', 'e-mail', 'Phone', 'Position', 'Department', 'Action');

        $table->set_html_table_column_names($columns);

        $sql = "SELECT users.user_id, users.username, '*****', profiles.profile, users.email, users.phone, positions.position, departments.department
                FROM users
                LEFT JOIN profiles ON profiles.profile_id = users.profile_id
                LEFT JOIN positions ON positions.position_id = users.position_id
                LEFT JOIN departments ON departments.department_id = users.department_id
                ORDER BY users.username ASC, profiles.profile ASC
        ";


        $details_link = array (
            1 => array('edit', '?controller=admin&action=users_details&id=')
        );
        $table->set_details_link($details_link);
        $table->set_main_table($connection->query($sql));
        /********************************************************************/

        $content .= '<table>'.$table->get_table().'</table>';

        /******************************* END OF USERS TABLE ******************************************************/
        /* CONFIGURE profiles TABLE */




        $output['content'] = $content;

        return $output;
    }

    public function users_add(){
        // TODO: hash $_POST['password']
        // use password_hash ($password, $algo [, $options)
        // needs PHP >= 5.5.0
        //$_POST['password'] = password_hash ($_POST['password'], PASSWORD_DEFAULT);

        // for PHP <=5.5.0 :
        $_POST['password'] = crypt ($_POST['password'], MY_SALT);


        $columns = array('username', 'password', 'profile_id', 'email', 'phone', 'position_id', 'department_id');

        $table = new simple_table_ops();
        $table->set_table_name('users');
        $table->set_table_column_names($columns);
        $table->add();
        header("Location: http://".WEBSITE_URL."/index.php?controller={$_GET['controller']}&action=users_index");

    }

    public function users_details(){

        $connection=new database();
        $table = new simple_table_ops();

        $id = $_GET['id'];

        $columns = array("username, '', profile_id, email, phone, position_id, department_id");

        $neat_columns = array('Username', 'Password', 'Profile', 'e-mail', 'Phone', 'Position', 'Department', 'Action', 'Delete');


        $sql = 'SELECT profile_id, profile FROM profiles ORDER BY profile';
        $profiles_result = $connection->query($sql);

        $sql = 'SELECT position_id, position FROM positions ORDER BY position';
        $positions_result = $connection->query($sql);

        $sql = 'SELECT department_id, department FROM departments ORDER BY department';
        $departments_result = $connection->query($sql);

        $drop_down = array(
            'profile_id'    => array('profile'    => $profiles_result),
            'position_id'   => array('position'   => $positions_result),
            'department_id' => array('department' => $departments_result)
        );

        $table->set_drop_down($drop_down);
        /********************************************************************/

        /* CONFIGURES Form structure */

        $form = array(
            'action'   => '?controller=admin&action=users_update&id='.$id,
            'div'      => "class='solitary_input'",
            'div_button' => "class='submit_button1'",
            'action_links' => array(1 => array('delete', '?controller=admin&action=users_delete&id=')),
            'method'   => 'post',
            'id'       => 'top_form',
            'elements' => array(
                //username, password, profile_id (drop_down), email, phone, position_id, department_id
                1  => array ('text'      => 'username'),
                2  => array ('text'      => 'password'),
                3  => array ('drop_down' => 'profile_id'),
                4  => array ('text'      => 'email'),
                5  => array ('text'      => 'phone'),
                6  => array ('drop_down' => 'position_id'),
                7  => array ('drop_down' => 'department_id'),
                8  => array ('submit'    => 'update')
            )
        );

        $table->set_table_name('users');
        $table->set_id_column('user_id');

        $table->set_table_column_names($columns);
        $table->set_html_table_column_names($neat_columns);

        $table->set_values_form();                          // set values found in database into form elements when building top_form
        $table->set_drop_down($drop_down);
        $table->set_form_array($form);

        $content = '<table>';
        $content .= $table->details();
        $content .='</table>';
        $output['content']=$content;
        return $output;

    }

    public function users_delete(){

        $table = new simple_table_ops();
        $table->set_id_column('user_id');
        $table->set_table_name('users');
        $table->delete();
        header("Location: http://".WEBSITE_URL."/index.php?controller={$_GET['controller']}&action=users_index");
    }

    public function users_update()
    {
        $_POST['password'] = crypt ($_POST['password'], MY_SALT);
        $update = new simple_table_ops();
        $columns = array('username', 'password', 'profile_id', 'email', 'phone', 'position_id', 'department_id');
        $update->set_id_column('user_id');
        $update->set_table_name('users');
        $update->set_table_column_names($columns);

        $update->update();
        header("Location: http://".WEBSITE_URL."/index.php?controller={$_GET['controller']}&action=users_index");

    }

    public function acl_index ()
    {

        // [controllers] -> [acl] <- [profiles]
        // on the left: show ALL [controllers] table contents with a check box to select
        // on the right: [acl] table with details link
        $connection = new database();
        $table = new simple_table_ops();

        $content = "<div class='link_button'>
                        <a href='?controller=admin&action=users_index'>Users</a>
                        <a href='?controller=admin&action=controllers_index'>Controllers</a>
                        <a href='?controller=admin&action=profiles_index'>Profiles</a>
                    </div>";


        /* CONFIGURES DROP DOWN Menus (2 in this case: programs and genre) */

        $sql = 'SELECT profile_id, profile FROM profiles';
        $profiles_result = $connection->query($sql);

        $sql = 'SELECT active_id, active FROM actives';
        $actives_result = $connection->query($sql);


        $drop_down = array(
            'profile_id' => array('profile' => $profiles_result),
            'active_id'  => array('active'   => $actives_result)
        );

        $table->set_drop_down($drop_down);
        /********************************************************************/

        /* CONFIGURES Form structure */
        // add form: presents only profile_id, active_id and submit button. controller_id comes from $_POST['checkbox_array'] configured
        // by simple_table_ops->set_html_checkbox

        $sql = "SELECT controller_id, controller, c_action FROM controllers ORDER BY controller ASC, c_action ASC";

        $table->set_id_column('controller_id');
        $table->set_html_check_box($connection->query($sql));


        /* CONFIGURES Main table contents (from MySQL) */

        $table->set_id_column('controller_id');



        /********************************************************************/

        $content .= "<div class='third_left'>";
        $content .= "<div class='submit_top_acl'><table>";
        $content .="<form action = '?controller=admin&action=acl_add' method = 'post' id='top_form'>";
        $content .= "<td>Profile: </td><td>".$table->get_html_drop_down('profile_id')."</td>";
        $content .= "<td>Access?: </td><td>".$table->get_html_drop_down('active_id')."</td>";
        $content .= "<td><input type ='submit' value='Move ->'></td>";
        $content .= "</table></div>";

        $columns = array('Select', 'Controller', 'Action');
        $table->set_html_table_column_names($columns);

        $content .= "<table width='100%'>".$table->get_html_table_column_names();

        $content .= $table->get_html_check_box();
        $content .="</form></table>";
        $content .= "</div>";

        // MIDDLE_SECTION START
        $content .= "<div class='column_margin'></div>";
        // MIDDLE_SETION END

        // START OF RIGHT TABLE - [acl] and its appendices *******************************************************************/

        $content .= "<div class='two_thirds_right'>";
        $columns = array('Profile', 'Controller', 'Action','Access', 'Details');

        $table->set_html_table_column_names($columns);

        $sql = "SELECT acl.acl_id, profiles.profile, controllers.controller, controllers.c_action, actives.active
                FROM acl
                JOIN controllers ON controllers.controller_id = acl.controller_id
                JOIN profiles    ON profiles.profile_id = acl.profile_id
                JOIN actives     ON actives.active_id = acl.active_id
                ORDER BY profiles.profile ASC, controllers.controller ASC, controllers.c_action ASC
        ";


        $details_link = array (
            1 => array('edit', '?controller=admin&action=acl_details&id=')
        );
        $table->set_details_link($details_link);
        $table->set_main_table($connection->query($sql));
        /********************************************************************/

        $content .= "<table>".$table->get_html_table_column_names().$table->get_html_main_table().'</table>';

        /******************************** END OF RIGHT TABLE **********************************************/


        $content .= "</div>";

        /* CONFIGURE profiles TABLE */


        $output['content'] = $content;

        return $output;

    }

    public function acl_add(){


        if (isset($_POST['checkbox_array'])) {

            $selected_controllers = $_POST['checkbox_array'];
            $profile_id = $_POST['profile_id'];
            $active_id = $_POST['active_id'];

            $columns = 'controller_id, profile_id, active_id';

            $sql = "INSERT INTO acl (".$columns.") VALUES (?, ?, ?)";

            $connection = new database();

            foreach ($selected_controllers as $controller_id) {
                $data[] = array ($controller_id, $profile_id, $active_id);
                //$connection->insert($sql, $data);
            }
            $connection->insert($sql, $data);
        }

        header("Location: http://".WEBSITE_URL."/index.php?controller={$_GET['controller']}&action=acl_index");


        $columns = array('controller_id', 'profile_id', 'active_id');
        $table = new simple_table_ops();
        $table->set_table_name('acl');
        $table->set_table_column_names($columns);
        $table->add();
        header("Location: http://".WEBSITE_URL."/index.php?controller={$_GET['controller']}&action=acl_index");

    }

    public function acl_details(){
        //todo: details

        $connection=new database();
        $table = new simple_table_ops();

        $id = $_GET['id'];

        $columns = array('controller_id, profile_id, active_id');

        $neat_columns = array('Controller.Action', 'Profile', 'Access?', 'Update', 'Delete');

        $sql = "SELECT controller_id, CONCAT (controller, '.', c_action) as controller_action FROM controllers ORDER BY controller_action";
        $controllers_result = $connection->query($sql);

        $sql = 'SELECT profile_id, profile FROM profiles ORDER BY profile';
        $profiles_result = $connection->query($sql);

        $sql = 'SELECT active_id, active FROM actives';
        $actives_result = $connection->query($sql);

        $drop_down = array(
            'controller_id' => array('controller_action'   => $controllers_result),
            'profile_id'    => array('profile'    => $profiles_result),
            'active_id'     => array('active' => $actives_result)
        );

        $table->set_drop_down($drop_down);
        /********************************************************************/

        /* CONFIGURES Form structure */

        $form = array(
            'action'   => '?controller=admin&action=acl_update&id='.$id,
            'div'      => "class='solitary_input'",
            'div_button' => "class='submit_button1'",
            'action_links' => array(1 => array('delete', '?controller=admin&action=acl_delete&id=')),
            'method'   => 'post',
            'id'       => 'top_form',
            'elements' => array(
                1  => array ('drop_down' => 'controller_id'),
                2  => array ('drop_down' => 'profile_id'),
                3  => array ('drop_down' => 'active_id'),
                4  => array ('submit'    => 'update')
            )
        );

        $table->set_table_name('acl');
        $table->set_id_column('acl_id');

        $table->set_table_column_names($columns);
        $table->set_html_table_column_names($neat_columns);

        $table->set_values_form();                          // set values found in database into form elements when building top_form
        $table->set_drop_down($drop_down);
        $table->set_form_array($form);

        $content = '<table>';
        $content .= $table->details();
        $content .='</table>';
        $output['content']=$content;
        return $output;

    }

    public function acl_delete(){
        // todo: delete
        $table = new simple_table_ops();
        $table->set_id_column('acl_id');
        $table->set_table_name('acl');
        $table->delete();
        header("Location: http://".WEBSITE_URL."/index.php?controller={$_GET['controller']}&action=acl_index");
    }

    public function acl_update()
    {

        $update = new simple_table_ops();
        $columns = array('controller_id', 'profile_id', 'active_id');
        $update->set_id_column('acl_id');
        $update->set_table_name('acl');
        $update->set_table_column_names($columns);

        $update->update();
        header("Location: http://".WEBSITE_URL."/index.php?controller={$_GET['controller']}&action=acl_index");

    }

    public function controllers_index ()
    {
        // TODO: controllers_index
        // show tables: users, profiles, positions and departments
        //
        $connection = new database();
        $table = new simple_table_ops();

        $content = "<div class='link_button'>
                        <a href='?controller=admin&action=acl_index'>Access Control List</a>
                        <a href='?controller=admin&action=users_index'>Users</a>
                        <a href='?controller=admin&action=profiles_index'>Profiles</a>
                    </div>";

        /* CONFIGURES Form structure */

        $top_form = array(
            'action'   => '?controller=admin&action=controllers_add',
            'div'      => "class='solitary_input'",
            'div_button' => "class='submit_button1'",
            'method'   => 'post',
            'id'       => 'top_form',
            'elements' => array(
                1  => array ('text'      => 'controller'),
                2  => array ('text'      => 'c_action'),
                3  => array ('submit'    => 'add')
            )
        );

        $table->set_top_form($top_form);
        /********************************************************************/

        /* CONFIGURES Main table contents (from MySQL) */

        $columns = array('Controller', 'Action', 'add');

        $table->set_html_table_column_names($columns);

        $sql = "SELECT controller_id, controller, c_action FROM controllers ORDER BY controller ASC, c_action ASC";

        $details_link = array (
            1 => array('edit', '?controller=admin&action=controllers_details&id=')
        );
        $table->set_details_link($details_link);
        $table->set_main_table($connection->query($sql));
        /********************************************************************/

        $content .= '<table>'.$table->get_table().'</table>';

        /******************************* END OF USERS TABLE ******************************************************/
        /* CONFIGURE profiles TABLE */


        $output['content'] = $content;

        return $output;


    }

    public function controllers_add ()
    {
        $columns = array('controller', 'c_action');
        $table = new simple_table_ops();
        $table->set_table_name('controllers');
        $table->set_table_column_names($columns);
        $table->add();
        header("Location: http://".WEBSITE_URL."/index.php?controller={$_GET['controller']}&action=controllers_index");
    }

    public function controllers_details ()
    {
        $table = new simple_table_ops();

        $id = $_GET['id'];

        $columns = array('controller, c_action');

        $neat_columns = array('Controller', 'Action', 'Update', 'Delete');


        /* CONFIGURES Form structure */

        $form = array(
            'action'   => '?controller=admin&action=controllers_update&id='.$id,
            'div'      => "class='solitary_input'",
            'div_button' => "class='submit_button1'",
            'action_links' => array(1 => array('delete', '?controller=admin&action=controllers_delete&id=')),
            'method'   => 'post',
            'id'       => 'top_form',
            'elements' => array(
                //username, password, profile_id (drop_down), email, phone, position_id, department_id
                1  => array ('text'      => 'controller'),
                2  => array ('text'      => 'c_action'),
                3  => array ('submit'    => 'update')
            )
        );

        $table->set_table_name('controllers');
        $table->set_id_column('controller_id');

        $table->set_table_column_names($columns);
        $table->set_html_table_column_names($neat_columns);

        $table->set_values_form();                          // set values found in database into form elements when building top_form

        $table->set_form_array($form);

        $content = '<table>';
        $content .= $table->details();
        $content .='</table>';
        $output['content']=$content;
        return $output;

    }

    public function controllers_delete()
    {
        $table = new simple_table_ops();
        $table->set_id_column('controller_id');
        $table->set_table_name('controllers');
        $table->delete();
        header("Location: http://".WEBSITE_URL."/index.php?controller={$_GET['controller']}&action=controllers_index");
    }

    public function controllers_update ()
    {
        $update = new simple_table_ops();
        $columns = array('controller', 'c_action');
        $update->set_id_column('controller_id');
        $update->set_table_name('controllers');
        $update->set_table_column_names($columns);

        $update->update();
        header("Location: http://".WEBSITE_URL."/index.php?controller={$_GET['controller']}&action=controllers_index");
    }
    public function log (){
        // show log contents
        $content = "<div class='link_button'>
                        <a href='?controller=admin&action=show_acl_map'>Show ACL map</a>
                    </div>";

        $content .= $_SESSION['log'];
        $output['content'] = $content;

        return $output;
    }

    public function show_acl_map ()
    {
        $content = "<div class='link_button'>
                        <a href='?controller=admin&action=log'>Show LOG</a>
                    </div>";
        $content .= "<table><tr><td>Controller.action</td><td>Access?</td></tr>";
        foreach ($_SESSION['acl_map'] as $row => $value) {
            $content .= '<tr><td>'.$row."</td><td align='center'>".$value.'</td></tr>';
        }
        $content .= '</table>';
        $output['content'] = $content;

        return $output;
    }

    public function profiles_index (){
        $connection = new database();
        $table = new simple_table_ops();

        $content = "<div class='link_button'>
                        <a href='?controller=admin&action=users_index'>Users</a>
                        <a href='?controller=admin&action=acl_index'>Access Control List</a>
                        <a href='?controller=admin&action=controllers_index'>Controllers</a>
                    </div>";

        $content .="<p>Add/Edit or Delete a Profile";
        $content .="<p>Profiles are like badges: if you are the ADMIN, you can do whatever you want, delete any record etc.";
        $content .="<p>After adding a new profile, you should configure the Access Control List.";
        $content .="<p>Note that ADMIN profile is not listed; however, it is present in Profiles table. ADMIN is non-editable/non-deletable/non-appraisable.";
        $content .="<p>You should avoid deleting a profile. Instead, if needed, create another profile and negate access in Access Control List page.";


        /* CONFIGURES Form structure */

        $top_form = array(
            'action'   => '?controller=admin&action=profiles_add',
            'div'      => "class='solitary_input'",
            'method'   => 'post',
            'id'       => 'top_form',
            'elements' => array(
                1  => array ('text'   => 'profile'),
                2  => array ('submit' => 'add')
            )
        );

        $table->set_top_form($top_form);
        /********************************************************************/

        /* CONFIGURES Main table contents (from MySQL) */

        $columns = array('Profile', 'Action');

        $table->set_html_table_column_names($columns);

        $sql = 'SELECT profile_id, profile
                FROM profiles
                WHERE NOT profile_id = 1
                ORDER BY profile ASC
        ';

        $details_link = array(1=>array ('edit', '?controller=admin&action=profiles_details&id='));
        $table->set_details_link($details_link);
        $table->set_main_table($connection->query($sql));
        /********************************************************************/
        $content .= '<table>'.$table->get_table().'</table>';
        $output['content'] = $content;

        return $output;
    }

    public function profiles_add (){
        // First, add the new school_year to [school_years] table:
        $columns = array('profile');
        $table = new simple_table_ops();
        $table->set_table_name('profiles');
        $table->set_table_column_names($columns);
        $table->add();

        header("Location: http://".WEBSITE_URL."/index.php?controller={$_GET['controller']}&action=profiles_index");
    }


    public function profiles_delete (){
        // When deleting check if the selected school_year_id is present in courses table
        // if yes, do not delete, and issue a warning

        $table = new simple_table_ops();
        $table->set_id_column('profile_id');
        $table->set_table_name('profiles');
        $table->delete();

        header("Location: http://".WEBSITE_URL."/index.php?controller={$_GET['controller']}&action=profiles_index");


    }

    public function profiles_details (){
        // details combines seeing with editing
        $id = $_GET['id'];
        $columns = array ('profile');

        $neat_columns = array ('Profile', 'Update', 'Delete');


        $form = array(
            'action'   => '?controller=admin&action=profiles_update&id='.$id,
            'div'      => "class='solitary_input'",
            'div_button'   => "class='submit_button1'",
            'action_links' => array(1 => array('delete', '?controller=admin&action=profiles_delete&id=')),
            'method'   => 'post',
            'id'       => 'top_form',
            'elements' => array(
                1  => array ('text'      => 'profile'),
                2  => array ('submit'    => 'update')
            )
        );

        $table = new simple_table_ops();

        $table->set_id_column('profile_id');
        $table->set_table_column_names($columns);
        $table->set_html_table_column_names($neat_columns);
        $table->set_form_array($form);
        $table->set_table_name('profiles');
        $table->set_values_form();
        $content = '<table>';
        $content .= $table->details();
        $content .='</table>';
        $output['content']=$content;
        return $output;
    }

    public function profiles_update (){
        // id value has already been sent into <form action string>
        // just call simple_table_ops class, which will process $_POST['variables']
        $update = new simple_table_ops();
        $columns = array ('profile');
        $update->set_id_column('profile_id');
        $update->set_table_name('profiles');
        $update->set_table_column_names($columns);

        $update->update();
        header("Location: http://".WEBSITE_URL."/index.php?controller={$_GET['controller']}&action=profiles_index");

    }
}
