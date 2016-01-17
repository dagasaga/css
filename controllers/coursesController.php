<?php
class coursesController {
    public function __construct(){}
    public function __clone(){}
    public function __destruct(){}

    public function index (){
        //  ______________________________
        // | School Year | Level | Action |
        // |---------------------|--------|
        // |             |       | [add]  |
        // |------------------------------|
        // | 2015/2016   |       | details|
        //  ------------------------------
        //
        /* CONFIGURES DROP DOWN Menus (2 in this case: programs and genre) */
        $connection = new database();
        $table = new simple_table_ops();

        $content = '<p>Here you can add courses to the school year '.$_SESSION['current_school_year'].'.<p>Each course is a level of school grade.';

        $sql = 'SELECT level_id, level FROM levels ORDER BY level_id ASC';
        $levels_result = $connection->query($sql);

        $drop_down = array(
            'level_id'       => array('level'       => $levels_result)
        );

        $table->set_drop_down($drop_down);
        /********************************************************************/

        /* CONFIGURES Form structure */

        $top_form = array(
            'action'   => '?controller=courses&action=add',
            'div'      => "class='solitary_input'",
            'method'   => 'post',
            'id'       => 'top_form',
            'elements' => array(
                1  => array ('drop_down' => 'level_id'),
                2  => array ('submit'    => 'add')
            )
        );

        $table->set_top_form($top_form);
        /********************************************************************/

        /* CONFIGURES Main table contents (from MySQL) */

        $columns = array('Level', 'Action');

        $table->set_html_table_column_names($columns);

        $sql = "SELECT courses.course_id, levels.level
                FROM courses
                JOIN levels ON courses.level_id = levels.level_id
                WHERE courses.school_year_id={$_SESSION['current_school_year_id']}
        ";

        $result = $connection->query($sql);

        if ($connection->get_row_num() == 0) {
            $content .= "<p>Currently, you have no courses configured. Choose one and click [ADD].";
        } else {
            $content .= "<p>Currently, you have " . $connection->get_row_num() . " courses configured.";
        }

        $details_link = array(1=>array ('details', '?controller=courses&action=details&id='));
        $table->set_details_link($details_link);
        $table->set_main_table($result);
        /********************************************************************/

        $content .= "<div class='submit_top_left'><table>{$table->get_table()}</table></div>";

        $output['content'] = $content;

        return $output;
    }

    public function add (){
        $columns = array('school_year_id', 'level_id');
        $_POST['school_year_id']=$_SESSION['current_school_year_id'];
        $table = new simple_table_ops();
        $table->set_table_name('courses');
        $table->set_table_column_names($columns);
        $table->add();
        header("Location: http://".WEBSITE_URL."/index.php?controller={$_GET['controller']}&action=index");

    }

    public function delete (){
        $table = new simple_table_ops();
        $table->set_id_column('course_id');
        $table->set_table_name('courses');
        $table->delete();
        header("Location: http://".WEBSITE_URL."/index.php?controller={$_GET['controller']}&action=index");
    }

    public function activate (){

    }

    public function inactivate (){

    }

    public function details (){
        // details combines seeing with editing
        $id = $_GET['id'];
        $columns = array ('level_id');

        $neat_columns = array ('Level', 'Action');


        $form = array(
            'action'   => '?controller=courses&action=update&id='.$id,
            'div'      => "class='solitary_input'",
            'method'   => 'post',
            'id'       => 'top_form',
            'elements' => array(
                1  => array ('drop_down' => 'level_id'),
                2  => array ('submit'    => 'update')
            )
        );

        $connection = new database();
        $table = new simple_table_ops();

        $sql = 'SELECT level_id, level FROM levels ORDER BY level_id ASC';
        $levels_result = $connection->query($sql);

        $drop_down = array(
            'level_id'       => array('level' => $levels_result)
        );

        $table->set_id_column('course_id');
        $table->set_table_column_names($columns);
        $table->set_html_table_column_names($neat_columns);
        $table->set_form_array($form);
        $table->set_table_name('courses');
        $table->set_values_form();                          // set values found in database into form elements when building top_form
        $table->set_drop_down($drop_down);

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
        $columns = array ('level_id');
        $update->set_id_column('course_id');
        $update->set_table_name('courses');
        $update->set_table_column_names($columns);

        return $update->update();

    }
    public function export (){
        // TODO: export CSV
        // Export ALL teachers - a no-brainer

    }
}