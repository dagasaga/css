<?php
class homeController {
    public function __construct(){}
    public function __clone(){}
    public function __destruct(){}

    public function index (){
        $content = '';
        require_once 'views/help/help1.php';



/************  left/middle ************/

        $content.= "<div class='third_middle'>";
        // drop down with school_years and submit button => set $_SESSION['current_year_id'] and $_SESSION['current_year'] variables here
        $connection = new database();
        $table = new simple_table_ops();

        $sql = 'SELECT school_year_id, school_year FROM school_years ORDER BY school_year DESC';
        $school_year_result = $connection->query($sql);

        if ($connection->get_row_num() == 0) {
            // no school year were found, please add one
            $content .= "<p> No school year was found! <p>Please, add a school year in [CONFIGURATION] if you are the administrator.";
        }

        $drop_down = array(
            'school_year_id' => array('school_year' => $school_year_result)
        );


        $form_values = array('school_year_id' => $_SESSION['current_school_year_id']);

        $table->set_values_form_manually($form_values);      //  set drop_down menu with current_school_year_id

        $table->set_drop_down($drop_down);

        /* CONFIGURES top column in neat names and html formatted */
        $neat_column_names = array('School Year', 'Action');
        $table->set_html_table_column_names($neat_column_names);

        /* CONFIGURES Form structure */

        $top_form = array(
            'action'     => '?controller=home&action=setschoolyear',
            'div'        => "class='solitary_input'",
            'div_button' => "class='form_button'",
            'method'     => 'post',
            'id'         => 'top_form',
            'elements'   => array(
                1  => array ('drop_down' => 'school_year_id'),
                11 => array ('submit'    => 'Set School Year')
            )
        );

        $table->set_top_form($top_form);
        /********************************************************************/
        $content .= "<table id=pan>";
        $content .= "<tr><td>";

        $content .= "<table width='226px'>";
        $content .= $table->get_html_table_column_names();
        $content .= $table->get_form()  ."</table>";
        $content .= "<p>Select the School Year to use throughout the application. <p>You can see it in the upper right (in orange). Any operation (class, curriculum, timetable etc creation) are automatically attached to this School Year.";
        $content .= "</td></tr></table>";
        /* CONFIGURES Main table contents */

        $columns = array('Available School Years');

        $table->set_html_table_column_names($columns);

        $sql = 'SELECT school_year_id, school_year
                FROM school_years
                ORDER BY school_year DESC
        ';

        $table->set_main_table($connection->query($sql));
        /********************************************************************/
        $content .= "<table id=pan>";
        $content .= "<tr><td>";
        $content .= "<table width='113px'>";
        $content .= $table->get_html_table_column_names();
        $content .= $table->get_html_main_table();
        $content .= '</table>';
        $content .= "</td><td>List of School Years already in the database.<p>To add a new school year, go to <a href='?controller=school_years&action=index'>Configuration->School Years</a></td></tr></table>";
        $content .= "</div>";

/************  middle/right ************/
        $content .="<div class='third_right'>";

        /* CONFIGURES Main table contents (from MySQL) */

        /* LIST OF ALL PROGRAMS */

        $columns = array('Programs');

        $table->set_html_table_column_names($columns);

        $sql = 'SELECT program_id, program
                FROM programs
                ORDER BY program ASC
        ';

        $table->set_main_table($connection->query($sql));

        $content .= "<table id=pan><tr><td>";
        $content .= "<table width='113px'>";
        $content .= $table->get_html_table_column_names();
        $content .= $table->get_html_main_table();
        $content .= '</table>';
        $content .= "</td><td>List of programs in the database.<p>If a program is changed or created, go to <a href='?controller=program&action=index'>Configuration->Programs</a></td></tr></table>";
        /********************************************************************/

        $columns = array('Program', 'Students');

        $table->set_html_table_column_names($columns);

        $sql= "SELECT students.program_id, programs.program, COUNT(students.student_id)
               FROM students
               JOIN programs ON programs.program_id = students.program_id
               GROUP BY program_id
               ORDER BY programs.program";

        $table->set_main_table($connection->query($sql));

        $content .= "<table id=pan><tr><td>";
        $content .= "<table width='226px'>".$table->get_html_table_column_names().$table->get_html_main_table().'</table>';


        $sql= "SELECT COUNT(DISTINCT program_id), COUNT(student_id)
               FROM students
               ";

        $total_students = $connection->query($sql);

        $content .= "<p>Total programs: ".$total_students[0]['COUNT(DISTINCT program_id)'];
        $content .= "<br>Total students: ".$total_students[0]['COUNT(student_id)'];

        $content .= "</td></tr></table>";
        $content .= "</div>";
        $output['content'] = $content;

        $output['footer']  = 'Welcome to CSS AEC-Foyer Lataste System';

        return $output;
    }
    public function setschoolyear ()
    {
        // set school year : comes from drop down $_POST
        if (isset($_POST['school_year_id'])) {
            if (is_numeric($_POST['school_year_id'])) {
                $school_year_handle = new database();

                $sql = "SELECT school_year FROM school_years WHERE school_year_id=?";
                $data = array($_POST['school_year_id']);

                $result = $school_year_handle->fetchAll($sql, $data);

                if ($school_year_handle->get_row_num()==1) {
                    $_SESSION['current_school_year_id'] = $_POST['school_year_id'];
                    $_SESSION['current_school_year']    = $result[0]['school_year'];
                } else {

                }

            }
        }
        //echo $_SESSION['school_year'].'<br>';
        //var_dump ($result);
        //die();
        header("Location: http://".WEBSITE_URL."/index.php?controller={$_GET['controller']}&action=index");

    }
}