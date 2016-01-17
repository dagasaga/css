<?php
class teachersController {
    public function __construct(){}
    public function __clone(){}
    public function __destruct(){}

    public function index (){
        $content = "<div class='link_button'>
                        <a href='?controller=teachers&action=export'>Export to EXCEL</a>
                        <a href='?controller=timetable&action=show'>Timetable</a>
                        <a href='?controller=curricula&action=index'>Curricula</a>
                    </div>";

        /* CONFIGURES DROP DOWN Menus (2 in this case: programs and genre) */
        $connection = new database();
        $table = new simple_table_ops();

        $sql = 'SELECT sex_id, sex FROM sexes';
        $sex_result = $connection->query($sql);

        $sql2 = 'SELECT active_id, active FROM actives';
        $active_result = $connection->query($sql2);

        $drop_down = array(
            'sex_id'     => array('sex'     => $sex_result),
            'active_id'  => array('active'  => $active_result)
        );

        $table->set_drop_down($drop_down);
        /********************************************************************/

        /* CONFIGURES Form structure */

        $top_form = array(
            'action'   => '?controller=teachers&action=add',
            'div'      => "class='solitary_input'",
            'div_button' => "class='submit_button1'",
            'method'   => 'post',
            'id'       => 'top_form',
            'elements' => array(
                1  => array ('text'      => 'nom'),
                2  => array ('text'      => 'prenom'),
                3  => array ('text'      => 'nom_khmer'),
                4  => array ('text'      => 'prenom_khmer'),
                5  => array ('drop_down' => 'sex_id'),
                6  => array ('drop_down' => 'active_id'),
                7  => array ('submit'    => 'add')
            )
        );

        $table->set_top_form($top_form);
        /********************************************************************/

        /* CONFIGURES Main table contents (from MySQL) */

        $columns = array('Last Name', 'First Name', 'Last Name Khmer', 'First Name Khmer', 'Genre', 'Active', 'Action');

        $table->set_html_table_column_names($columns);

        $sql = 'SELECT teachers.teacher_id, teachers.nom, teachers.prenom, teachers.nom_khmer, teachers.prenom_khmer, sexes.sex, actives.active
                FROM sexes
                LEFT JOIN teachers ON teachers.sex_id     = sexes.sex_id
                LEFT JOIN actives  ON teachers.active_id  = actives.active_id
                ORDER BY teachers.nom ASC, teachers.prenom ASC
        ';


        $details_link = array (
            1 => array('edit', '?controller=teachers&action=details&id=')
        );
        $table->set_details_link($details_link);
        $table->set_main_table($connection->query($sql));
        /********************************************************************/

        $content .= '<table>'.$table->get_table().'</table>';

        $output['content'] = $content;

        return $output;
    }

    public function add (){
        $columns = array('nom', 'prenom', 'nom_khmer', 'prenom_khmer', 'sex_id', 'active_id');
        $table = new simple_table_ops();
        $table->set_table_name('teachers');
        $table->set_table_column_names($columns);
        $table->add();
        header("Location: http://".WEBSITE_URL."/index.php?controller={$_GET['controller']}&action=index");

    }

    public function delete (){
        $table = new simple_table_ops();
        $table->set_id_column('teacher_id');
        $table->set_table_name('teachers');
        $table->delete();
        header("Location: http://".WEBSITE_URL."/index.php?controller={$_GET['controller']}&action=index");
    }

    public function details (){
        // details combines seeing with editing
        $id = $_GET['id'];
        $columns = array ('nom, prenom, nom_khmer, prenom_khmer, sex_id,  active_id');

        $neat_columns = array ('Last Name', 'First Name', 'Last Name Khmer', 'First Name Khmer', 'Genre', 'Active', 'Update', 'Delete');


        $form = array(
            'action'   => '?controller=teachers&action=update&id='.$id,
            'div'      => "class='solitary_input'",
            'div_button'   => "class='submit_button1'",
            'action_links' => array(1 => array('delete', '?controller=teachers&action=delete&id=')),
            'method'   => 'post',
            'id'       => 'top_form',
            'elements' => array(
                1  => array ('text'      => 'nom'),
                2  => array ('text'      => 'prenom'),
                3  => array ('text'      => 'nom_khmer'),
                4  => array ('text'      => 'prenom_khmer'),
                5  => array ('drop_down' => 'sex_id'),
                6  => array ('drop_down' => 'active_id'),
                7  => array ('submit'    => 'update')
            )
        );

        $connection = new database();
        $table = new simple_table_ops();

        $sql = 'SELECT sex_id, sex FROM sexes';
        $sex_result = $connection->query($sql);

        $sql2 = 'SELECT active_id, active FROM actives';
        $active_result = $connection->query($sql2);

        $drop_down = array(
            'sex_id'     => array('sex'     => $sex_result),
            'active_id'  => array('active'  => $active_result)
        );

        $table->set_table_name('teachers');
        $table->set_id_column('teacher_id');

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

    public function update (){
        // id value has already been sent into <form action string>
        // just call simple_table_ops class, which will process $_POST['variables']

        $update = new simple_table_ops();
        $columns = array ('nom', 'prenom', 'nom_khmer', 'prenom_khmer', 'sex_id', 'active_id');
        $update->set_id_column('teacher_id');
        $update->set_table_name('teachers');
        $update->set_table_column_names($columns);

        $update->update();
        header("Location: http://".WEBSITE_URL."/index.php?controller={$_GET['controller']}&action=index");

    }
    public function export (){
        // TODO: export CSV
        // Export ALL teachers - a no-brainer

    }
}