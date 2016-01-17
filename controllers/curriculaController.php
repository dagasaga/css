<?php
class curriculaController {
    public function __construct(){}
    public function __clone(){}
    public function __destruct(){}

    public function index (){
        $content = "<div class='link_button'>
                        <a href='?controller=curricula&action=export'>Export to EXCEL</a>
                        <a href='?controller=timetable&action=show'>Timetable</a>
                        <a href='?controller=teachers&action=index'>Teachers</a>
                    </div>";

        /* CONFIGURES DROP DOWN Menus (2 in this case: programs and genre) */
        $connection = new database();
        $table = new simple_table_ops();

        // 3 drop down menus:
        //      1. level_id
        //      2. teacher_id
        //      3. subject_id

        $sql = "SELECT courses.course_id, levels.level AS course
                FROM courses
                JOIN levels ON courses.level_id = levels.level_id
                WHERE courses.school_year_id = {$_SESSION['current_school_year_id']}
                ORDER BY levels.level_id ASC";
        $courses_result = $connection->query($sql);

        $sql = "SELECT teachers.teacher_id, CONCAT (teachers.nom, ' ', teachers.prenom, ' | ', teachers.nom_khmer, ' ', teachers.prenom_khmer, ' | ', sexes.sex) as teacher
                FROM teachers
                JOIN sexes ON teachers.sex_id = sexes.sex_id
                WHERE teachers.active_id=1
                ORDER BY teachers.nom ASC, teachers.prenom ASC";
        $teachers_result = $connection->query($sql);

        $sql = "SELECT subject_id, subject FROM subjects ORDER BY subject";
        $subjects_result = $connection->query($sql);

        $drop_down = array(
            'course_id'    => array('course'  => $courses_result),
            'teacher_id'   => array('teacher' => $teachers_result),
            'subject_id'   => array('subject' => $subjects_result),
        );

        $table->set_drop_down($drop_down);
        /********************************************************************/

        /* CONFIGURES Form structure */

        $top_form = array(
            'action'     => '?controller=curricula&action=add',
            'div'        => "class='solitary_input'",
            'div_button' => "class='submit_button1'",
            'method'     => 'post',
            'id'         => 'top_form',
            'elements'   => array(
                1  => array ('drop_down' => 'teacher_id'),
                2  => array ('drop_down' => 'subject_id'),
                3  => array ('drop_down' => 'course_id'),
                4  => array ('submit'    => 'add')
            )
        );

        $table->set_top_form($top_form);
        /********************************************************************/

        /* CONFIGURES Main table contents (from MySQL) */

        $columns = array('Teacher', 'Subject', 'Course', 'Action');

        $table->set_html_table_column_names($columns);

        $sql = "SELECT curricula.curriculum_id, CONCAT (teachers.nom, ' ', teachers.prenom, ' | ', teachers.nom_khmer, ' ', teachers.prenom_khmer, ' | ', sexes.sex) as teacher, subjects.subject, levels.level
                FROM curricula
                JOIN courses ON curricula.course_id = courses.course_id
                JOIN subjects ON curricula.subject_id = subjects.subject_id
                JOIN teachers ON teachers.teacher_id = curricula.teacher_id
                JOIN sexes  ON teachers.sex_id  = sexes.sex_id
                JOIN levels ON courses.level_id = levels.level_id
                WHERE teachers.active_id=1 AND courses.school_year_id = {$_SESSION['current_school_year_id']}
                ORDER BY subjects.subject ASC, levels.level_id ASC, teachers.nom ASC, teachers.prenom ASC
        ";

        $details_link = array (
            1 => array('edit',       '?controller=curricula&action=details&id=')
        );

        $table->set_details_link($details_link);
        $table->set_main_table($connection->query($sql));
        /********************************************************************/

        $content .= '<table>'.$table->get_table().'</table>';

        $output['content'] = $content;

        return $output;
    }

    public function add (){
        $columns = array('course_id', 'teacher_id', 'subject_id');
        $table = new simple_table_ops();
        $table->set_table_name('curricula');
        $table->set_table_column_names($columns);
        $table->add();
        header("Location: http://".WEBSITE_URL."/index.php?controller={$_GET['controller']}&action=index");
    }

    public function delete (){
        $table = new simple_table_ops();
        $table->set_id_column('curriculum_id');
        $table->set_table_name('curricula');
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

        $connection = new database();
        $table = new simple_table_ops();

        /* Drop down menus */
        $sql = "SELECT courses.course_id, levels.level AS level
                FROM courses
                JOIN levels ON courses.level_id = levels.level_id
                WHERE courses.school_year_id = {$_SESSION['current_school_year_id']}
                ORDER BY levels.level_id ASC";
        $levels_result = $connection->query($sql);

        $sql_teachers = "SELECT teachers.teacher_id, CONCAT (teachers.nom, ' ', teachers.prenom, ' | ', teachers.nom_khmer, ' ', teachers.prenom_khmer, ' | ', sexes.sex) as teacher
                FROM teachers
                JOIN sexes ON teachers.sex_id = sexes.sex_id
                WHERE teachers.active_id=1";
        $teachers_result = $connection->query($sql_teachers);

        $sql = "SELECT subject_id, subject FROM subjects ORDER BY subject";
        $subjects_result = $connection->query($sql);

        $drop_down = array(
            'course_id'    => array('level'  => $levels_result),
            'teacher_id'   => array('teacher' => $teachers_result),
            'subject_id'   => array('subject' => $subjects_result),
        );
        /* end of drop down menus definition */

        $columns = array('course_id, teacher_id, subject_id');

        $neat_columns = array('Course', 'Teacher', 'Subject', 'Update', 'Action');

        $form = array(
            'action'       => '?controller=curricula&action=update&id='.$id,
            'div'          => "class='solitary_input'",
            'div_button'   => "class='submit_button1'",
            'action_links' => array(1 => array('delete', '?controller=curricula&action=delete&id=')),
            'method'       => 'post',
            'id'           => 'top_form',
            'elements' => array(
                1  => array ('drop_down' => 'course_id'),
                2  => array ('drop_down' => 'teacher_id'),
                3  => array ('drop_down' => 'subject_id'),
                4  => array ('submit'    => 'update')
            )
        );

        $sql = "SELECT curricula.course_id as course_id, curricula.teacher_id as teacher_id, curricula.subject_id as subject_id
                FROM curricula
                JOIN teachers ON curricula.teacher_id = teachers.teacher_id
                JOIN subjects ON curricula.subject_id = subjects.subject_id
                JOIN courses  ON curricula.course_id  = courses.course_id
                WHERE curricula.curriculum_id={$_GET['id']}
                ";
        $courses_teachers_result = $connection->query($sql);
        $courses_teachers_result = $courses_teachers_result[0];

        $table->set_table_name('curricula');
        $table->set_id_column('curriculum_id');
        $table->set_table_column_names($columns);
        $table->set_html_table_column_names($neat_columns);


        $table->set_values_form_manually($courses_teachers_result);   // set values found in database into form elements when building top_for
        $table->set_drop_down($drop_down);
        $table->set_form_array($form);

        $content = "<div class='link_button'>
                        <a href='?controller=curricula&action=export'>Export to EXCEL</a>
                        <a href='?controller=timetable&action=show'>Timetable</a>
                        <a href='?controller=teachers&action=index'>Teachers</a>
                    </div>";

        $content .= '<table>';
        $content .= $table->details();
        $content .='</table>';
        $output['content']=$content;
        return $output;
    }

    public function update (){
        // id value has already been sent into <form action string>
        // just call simple_table_ops class, which will process $_POST['variables']

        $update = new simple_table_ops();
        $columns = array('course_id', 'teacher_id', 'subject_id');
        $update->set_id_column('curriculum_id');
        $update->set_table_name('curricula');
        $update->set_table_column_names($columns);

        $update->update();
        header("Location: http://".WEBSITE_URL."/index.php?controller={$_GET['controller']}&action=index");

    }
    public function export (){
        // TODO: export CSV
        // Export ALL teachers - a no-brainer

    }
}