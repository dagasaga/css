<?php

class classesController {
    // create classe: choose course_id from drop down menu and assign batch to [classes] table
    // basically the same, but instead of level_id as drop down, use course_id
    public function __construct(){}
    public function __clone(){}
    public function __destruct(){}

    public function index (){

        $content = "";

        /* CONFIGURES DROP DOWN Menus (2 in this case: programs and genre) */
        $connection = new database();
        $table = new simple_table_ops();

        $sql = "SELECT courses.course_id, levels.level
                FROM courses
                JOIN levels ON courses.level_id = levels.level_id
                WHERE courses.school_year_id = {$_SESSION['current_school_year_id']}
                ORDER BY levels.level_id ASC

        ";



        $courses_result = $connection->query($sql);

        if ($connection->get_row_num() == 0) {
            // no courses were setup - redirect
            $content .= "<p> No courses were found for the year {$_SESSION['current_school_year']}.<p>Go to [COURSES] to add a new one.";
            $output['content'] = $content;
            return $output;
        }

        $drop_down = array(
            'course_id'       => array('course'  => $courses_result)
        );

        $table->set_drop_down($drop_down);

        /* CONFIGURES top column in neat names and html formatted */
        $neat_column_names = array('Select', 'Surname', 'Name', 'Surname Kh', 'Name Kh', 'Genre', 'Program', 'Age');
        $table->set_html_table_column_names($neat_column_names);

        /********************************************************************/

        /* CONFIGURES main left table */
        // selects ALL students from students where student_id is not inside [COURSES] table
        // need: select students that are not found in [COURSES] of the current_year_id.


        /*
        $sql = "SELECT students.student_id, students.nom, students.prenom, students.nom_khmer, students.prenom_khmer, sexes.sex, programs.program, TIMESTAMPDIFF(YEAR,students.dob,NOW()) AS age
                FROM students
                LEFT JOIN courses ON courses.student_id=students.student_id
                JOIN sexes ON students.sex_id=sexes.sex_id
                JOIN programs ON students.program_id=programs.program_id
                WHERE courses.school_year_id Is Null AND students.active_id=1
                ORDER BY age ASC, nom ASC, prenom ASC";

        */


        $sql = "SELECT students.student_id, students.nom, students.prenom, students.nom_khmer, students.prenom_khmer, sexes.sex, programs.program, TIMESTAMPDIFF(YEAR,students.dob,NOW()) AS age
                FROM students
                JOIN sexes    ON students.sex_id=sexes.sex_id
                JOIN programs ON students.program_id=programs.program_id
                WHERE students.active_id=1 AND NOT students.student_id IN (
                    SELECT students.student_id
                    FROM students
                    JOIN classes ON classes.student_id                 = students.student_id
                    JOIN courses ON courses.course_id = classes.course_id
                    JOIN school_years ON school_years.school_year_id = courses.school_year_id
                    WHERE courses.school_year_id = {$_SESSION['current_school_year_id']})
                ORDER BY age ASC, students.prenom, students.nom";

        // CHECK: if no result, warn that you need to add students to students table before making courses!
        // $content .= 'Before building courses, you need to add STUDENTS to the [STUDENTS] table!';
        $result = $connection->query($sql);

        if ($connection->get_row_num() == 0) {
            // no records found -
            $output['menu2'] = "No free students were found. To add a new STUDENT, click <a href='?controller=students&action=index'>HERE</a>.";

        }
        $table->set_id_column('student_id');
        $table->set_html_check_box($result);

        $content .= "<div class='submit_top_left'><table width='100%'><tr><td>".$table->get_html_drop_down('course_id')."</td></tr></table></div>";

        $content .= "<div class='half_left'><br><br><table width='100%'>";
        $content .= $table->get_html_table_column_names();
        $content .="<form action = '?controller=classes&action=move' method = 'post' id='top_form'>";

        $content .= $table->get_html_check_box();


        $content .= "<div class='submit_top_left2'><input type ='submit' value='Move selected STUDENTS to Course ->'></div>";

        $content .="</form></table></div>";

        //TODO (secondary):
        //  foreach (total of courses.levels_id)
        //      select student from classes
        //      assemble table with new tables from select and add row containing levels.level on top
        //      alternate div class table_row1/table_row2 for each group of class
        //  end foreach
        //
        $courses_sql = "SELECT classes.classe_id,
                               levels.level,
                               students.nom, students.prenom, students.nom_khmer, students.prenom_khmer,
                               sexes.sex,
                               programs.program

                        FROM courses
                        JOIN classes ON classes.course_id                  = courses.course_id
                        JOIN students ON classes.student_id               = students.student_id
                        JOIN programs ON programs.program_id             = students.program_id
                        JOIN sexes    ON sexes.sex_id                    = students.sex_id
                        JOIN school_years ON school_years.school_year_id = courses.school_year_id
                        JOIN levels   ON levels.level_id                 = courses.level_id
                        WHERE courses.school_year_id = ".$_SESSION['current_school_year_id']."
                        ORDER BY school_years.school_year DESC, levels.level_id ASC, students.nom ASC, students.prenom ASC
                        ";

        // TODO: priority high - generate tables per level_id



        $courses_table = new simple_table_ops();
        $columns = array('Surname', 'Name', 'Surname Kh', 'Name Kh', 'Genre', 'Program', 'Action');
        $courses_table->set_html_table_column_names($columns);

        $details_link = array(1 => array ('details', '?controller=classes&action=details&id='));
        $courses_table->set_details_link($details_link);

        $content .= "<div class='half_right'>";


        $sql = "SELECT level_id FROM courses GROUP BY level_id ORDER BY level_id";
        $levels_result = $connection->query($sql);
        foreach ($levels_result as $row) {
            $content .= "<table width='100%'>";
            foreach ($row as $field=>$value) {
                $courses_sql = "SELECT classes.classe_id,

                               students.nom, students.prenom, students.nom_khmer, students.prenom_khmer,
                               sexes.sex,
                               programs.program

                        FROM courses
                        JOIN classes ON classes.course_id                  = courses.course_id
                        JOIN students ON classes.student_id               = students.student_id
                        JOIN programs ON programs.program_id             = students.program_id
                        JOIN sexes    ON sexes.sex_id                    = students.sex_id
                        JOIN school_years ON school_years.school_year_id = courses.school_year_id
                        JOIN levels   ON levels.level_id                 = courses.level_id
                        WHERE courses.school_year_id = ".$_SESSION['current_school_year_id']." AND courses.level_id={$value}
                        ORDER BY school_years.school_year DESC, levels.level_id ASC, students.nom ASC, students.prenom ASC
                        ";

                $courses_table->set_main_table($connection->query($courses_sql));

                $content .= "<tr><td colspan='7'>Grade: ".$value."</td></tr>";
                $content .= $courses_table->get_html_table_column_names();

                $content .= $courses_table->get_html_main_table();
                $content .= '</td></tr>';


            }
            $content .= "</table>";
        }

        $content .= "</div>";

        /********************************************************************/
        $output['content'] = $content;
        return $output;
    }


    public function details ()
    {
        // details combines seeing with editing
        $id = $_GET['id'];

        $connection = new database();
        $table = new simple_table_ops();

        /* Drop down menu */
        $sql = "SELECT courses.course_id, levels.level AS level
                FROM courses
                JOIN levels ON courses.level_id = levels.level_id
                WHERE courses.school_year_id = {$_SESSION['current_school_year_id']}
                ORDER BY levels.level_id ASC";
        $levels_result = $connection->query($sql);

        $drop_down = array(
            'course_id'    => array('level'  => $levels_result)
        );
        /* end of drop down menus definition */

        $columns = array('course_id');      // only columns to be updated

        $neat_columns = array('Student', 'Genre', 'Program', 'Course', 'Update', 'Remove');

        $form = array(
            'action'   => '?controller=classes&action=update&id='.$id,
            'div'      => "class='solitary_input'",
            'div_button'=> "class='submit_button1'",
            'action_links' => array(1 => array('remove', '?controller=classes&action=remove&id=')),
            'method'   => 'post',
            'id'       => 'top_form',
            'elements' => array(
                1  => array ('label'     => 'student'),
                3  => array ('label'     => 'sex'),
                4  => array ('label'     => 'program'),
                6  => array ('drop_down' => 'course_id'),
                7  => array ('submit'    => 'update')
            )
        );

        // select row based on id value
        $sql = "SELECT classes.course_id as course_id, CONCAT (students.nom, ' ', students.prenom, ', ', students.nom_khmer, ' ', students.prenom_khmer) as student, sexes.sex as sex, programs.program as program
                FROM classes
                JOIN courses ON classes.course_id = courses.course_id
                JOIN students ON classes.student_id = students.student_id
                JOIN sexes ON students.sex_id = sexes.sex_id
                JOIN programs ON students.program_id = programs.program_id
                WHERE classes.classe_id={$_GET['id']}
                ";
        $result = $connection->query($sql);
        $result = $result[0];

        $table->set_table_name('classes');

        $table->set_table_column_names($columns);
        $table->set_html_table_column_names($neat_columns);


        $table->set_values_form_manually($result);                          // set values found in database into form elements when building top_for
        $table->set_drop_down($drop_down);
        $table->set_form_array($form);

        $content = '<table>';
        $content .= $table->details();
        $content .='</table>';
        $output['content']=$content;
        return $output;
    }
    public function move(){
        // receives array from input form
        // if (isset($_POST['students'])) {
        //      $selected_students = $_POST['students'];
        //      foreach ($selected_students as $student_id) {
        //          echo $student_id.'<br>';
        //      }
        // }
        // builds array (

        if (isset($_POST['checkbox_array'])) {

            $course_id         = $_POST['course_id'];
            $selected_students = $_POST['checkbox_array'];

            $columns = 'course_id, student_id';

            $sql = "INSERT INTO classes (".$columns.") VALUES (?, ?)";

            $connection = new database();

            foreach ($selected_students as $student_id) {
                $data[] = array ($course_id, $student_id);
                //$connection->insert($sql, $data);
            }
            $connection->insert($sql, $data);
        }
        header("Location: http://".WEBSITE_URL."/index.php?controller={$_GET['controller']}&action=index");
    }

    public function remove (){
        // removes student from [classes] table
        $table = new simple_table_ops();
        $table->set_id_column('classe_id');
        $table->set_table_name('classes');
        $table->delete();
        header("Location: http://".WEBSITE_URL."/index.php?controller={$_GET['controller']}&action=index");
    }


    public function update ()
    {
        $update = new simple_table_ops();
        $columns = array('course_id');
        $update->set_id_column('classe_id');
        $update->set_table_name('classes');
        $update->set_table_column_names($columns);

        $update->update();
        header("Location: http://".WEBSITE_URL."/index.php?controller={$_GET['controller']}&action=index");

    }
    public function export (){
        // Export either assigned and non-assigned students to csv file

    }
}