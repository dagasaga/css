<?php

class school_yearsController {
    public function __construct(){}
    public function __clone(){}
    public function __destruct(){}

    public function index (){
        $connection = new database();
        $table = new simple_table_ops();
        $content  ='<p>Add or Delete a School Year. <p>BE CAREFUL! When deleting, be sure you have just created the school year, and has not used it in [COURSES] table!';


        /* CONFIGURES Form structure */

        $top_form = array(
            'action'   => '?controller=school_years&action=add',
            'div'      => "class='solitary_input'",
            'method'   => 'post',
            'id'       => 'top_form',
            'elements' => array(
                1  => array ('text'   => 'school_year'),
                2  => array ('submit' => 'add')
            )
        );

        $table->set_top_form($top_form);
        /********************************************************************/

        /* CONFIGURES Main table contents (from MySQL) */

        $columns = array('School Year', 'Action');

        $table->set_html_table_column_names($columns);

        $sql = 'SELECT school_year_id, school_year
                FROM school_years
                ORDER BY school_year DESC
        ';

        $details_link = array(1=>array ('delete', '?controller=school_years&action=delete&id='));
        $table->set_details_link($details_link);
        $table->set_main_table($connection->query($sql));
        /********************************************************************/
        $content .= '<table>'.$table->get_table().'</table>';
        $output['content'] = $content;

        return $output;
    }

    public function add (){
        // First, add the new school_year to [school_years] table:
        $columns = array('school_year');
        $table = new simple_table_ops();
        $table->set_table_name('school_years');
        $table->set_table_column_names($columns);
        $table->add();

        // then, gets the last inserted id (not through lastInsertedId function, because it may be buggy:
        /*
        SELECT article, dealer, price
        FROM   shop
        WHERE  price=(SELECT MAX(price) FROM shop);
        */

        $sql = "SELECT school_year_id, school_year
                FROM school_years
                WHERE school_year_id=(SELECT MAX(school_year_id) FROM school_years)
                ";

        $school_years_handle = new database();

        $new_school_year_result = $school_years_handle->query($sql);

        $new_school_year_id = $new_school_year_result[0]['school_year_id'];
        $new_school_year    = $new_school_year_result[0]['school_year'];

        /* Populate automatically [courses] table with values from level_id and $_SESSION['current_school_year_id'] */

        $levels_handler = new database();
        $sql = "SELECT level_id FROM levels";

        $result = $levels_handler->query($sql);

        $insert_sql = "INSERT INTO courses (school_year_id, level_id) VALUES (?, ?)";

        foreach ($result as $row) {
            foreach ($row as $value) {
                // insert $value into courses and $_SESSION['current_school_year_id']
                $data = array ($new_school_year_id, $value);
                $levels_handler->insert($insert_sql, $data);
            }
        }

        // Update current_school_year_id to the newly added school_year:
        $_SESSION['current_school_year_id'] = $new_school_year_id;
        $_SESSION['current_school_year']    = $new_school_year;

        header("Location: http://".WEBSITE_URL."/index.php?controller={$_GET['controller']}&action=index");
    }

    public function delete (){
        // When deleting check if the selected school_year_id is present in courses table
        // if yes, do not delete, and issue a warning
        $content ='';
        $delete_handler = new database();

        $sql = "SELECT school_year_id FROM school_years";
        $result = $delete_handler->query($sql);

        if ($delete_handler->get_row_num() == 1) {
            $content .= '<p>You cannot delete the last school year!';
            $output['content'] = $content;
            return $output;
        }

        $sql = "SELECT school_year_id FROM courses WHERE school_year_id={$_GET['id']}";
        echo $sql.'<br>';

        $result = $delete_handler->query($sql);
;
        if ($delete_handler->get_row_num() !== 0) {
            $content .= '<p>You cannot delete this school year!<p>You need first to delete ALL the courses attached to this year.';
            $content .= "<p>There are currently {$delete_handler->get_row_num()} courses.";
            $output['content'] = $content;
            return $output;
        }

        $table = new simple_table_ops();
        $table->set_id_column('school_year_id');
        $table->set_table_name('school_years');
        $table->delete();

        // Check that when deleting, $_SESSION['current_school_year_id'] should be updated
        // Forbid last school_year deletion if last in school_years
        $sql = "SELECT MAX(school_year_id), school_year FROM school_years";
        $school_years_handle = new database();

        $new_school_year_result = $school_years_handle->query($sql);

        $new_school_year_id = $new_school_year_result[0]['MAX(school_year_id)'];
        $new_school_year    = $new_school_year_result[0]['school_year'];

        $_SESSION['current_school_year_id'] = $new_school_year_id;
        $_SESSION['current_school_year']    = $new_school_year;
        header("Location: http://".WEBSITE_URL."/index.php?controller={$_GET['controller']}&action=index");


    }

}