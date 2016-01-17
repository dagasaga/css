<?php

class studentsController {
    public function __construct(){}
    public function __clone(){}
    public function __destruct(){}

    public function test()
    {
        $content = "test [action] in students [controller]<br>";
        $output['content'] = $content;
        return $output;
    }

    public function index(){

        // show current year students
        // plus a selector with all school years
        // connect to db
        // queries all students from current year
        // display them with links on names, and a checking box beside each student
        // offer on top an action selector to process selected students:
        //      1. Details
        //      2. Inactivate (delete is not allowed)
        //      3. Other ?

        $content = "<div class='link_button'>
                        <a href='?controller=students&action=export'>Export to EXCEL</a>
                        <a href='?controller=classes&action=index'>Classes</a>
                        <a href='?controller=attendances&action=index'>Attendance</a>
                        <a href='?controller=results&action=index'>Results</a>
                    </div>";


        /* CONFIGURES DROP DOWN Menus (2 in this case: programs and genre) */
        $connection = new database();
        $table = new simple_table_ops();

        $sql = 'SELECT sex_id, sex FROM sexes';
        $sex_result = $connection->query($sql);

        $sql = 'SELECT active_id, active FROM actives';
        $active_result = $connection->query($sql);

        $sql = 'SELECT program_id, program FROM programs ORDER BY program ASC';
        $programs_result = $connection->query($sql);

        $drop_down = array(
            'sex_id'     => array('sex'     => $sex_result),
            'active_id'  => array('active'  => $active_result),
            'program_id' => array('program' => $programs_result)
        );

        $table->set_drop_down($drop_down);
        /********************************************************************/

        /* CONFIGURES Form structure */

        $top_form = array(
            'action'    => '?controller=students&action=add',
            'div'       => "class='solitary_input'",
            'div_button'=> "class='submit_button1'",
            'method'    => 'post',
            'id'        => 'top_form',
            'elements'  => array(
                1  => array ('text'      => 'nom'),
                2  => array ('text'      => 'prenom'),
                3  => array ('text'      => 'nom_khmer'),
                4  => array ('text'      => 'prenom_khmer'),
                5  => array ('text'      => 'matricule'),
                6  => array ('date'      => 'dob'),
                7  => array ('empty'     => ''),
                8  => array ('drop_down' => 'sex_id'),
                9  => array ('drop_down' => 'program_id'),
                10 => array ('drop_down' => 'active_id'),
                11 => array ('submit'    => 'add')
            )
        );

        $table->set_top_form($top_form);
        /********************************************************************/

        /* CONFIGURES Main table contents (from MySQL) */

        $columns = array('Last Name', 'First Name', 'Last Name Khmer', 'First Name Khmer', 'Matricule', 'Date of Birth', 'Age', 'Genre', 'Program', 'Active', 'Action');

        $table->set_html_table_column_names($columns);

        $sql = "SELECT students.student_id, students.nom, students.prenom, students.nom_khmer, students.prenom_khmer, students.matricule, students.dob, TIMESTAMPDIFF(YEAR,students.dob,NOW()) AS age, sexes.sex, programs.program, actives.active
                FROM sexes
                JOIN students ON sexes.sex_id = students.sex_id
                JOIN programs ON students.program_id = programs.program_id
                JOIN actives ON actives.active_id = students.active_id
                ORDER BY students.nom ASC
        ";

        $details_link = array (1 => array ('edit', '?controller=students&action=details2&id='));
        $table->set_details_link($details_link);
        $table->set_main_table($connection->query($sql));
        /********************************************************************/

        $content .= "<table>".$table->get_table().'</table>';

        $output['content'] = $content;
        return $output;

    }

    public function add (){
        $columns = array('nom', 'prenom', 'nom_khmer', 'prenom_khmer', 'matricule', 'dob', 'sex_id', 'program_id', 'active_id');
        $table = new simple_table_ops();
        $table->set_table_name('students');
        $table->set_table_column_names($columns);
        $table->add();
        header("Location: http://".WEBSITE_URL."/index.php?controller={$_GET['controller']}&action=index");

    }

    public function delete (){
        $table = new simple_table_ops();
        $table->set_id_column('student_id');
        $table->set_table_name('students');
        $table->delete();
        header("Location: http://".WEBSITE_URL."/index.php?controller={$_GET['controller']}&action=index");
    }

    public function activate(){

    }

    public function inactivate(){

    }

    public function details2 ()
    {
        // details combines seeing with editing
        $id = $_GET['id'];
        $columns = array('nom, prenom, nom_khmer, prenom_khmer, matricule, dob, program_id, sex_id,  active_id');

        $neat_columns = array('Last Name', 'First Name', 'Last Name Khmer', 'First Name Khmer', 'Matricule', 'Date of Birth', 'Program', 'Genre', 'Active', 'Update', 'Delete');

        $form = array(
            'action' => '?controller=students&action=update&id=' . $id,
            'div' => "class='solitary_input'",
            'div_button' => "class='submit_button1'",
            'action_links' => array(1 => array('delete', '?controller=students&action=delete&id=')),
            'method' => 'post',
            'id' => 'top_form',
            'elements' => array(
                1 => array('text' => 'nom'),
                2 => array('text' => 'prenom'),
                3 => array('text' => 'nom_khmer'),
                4 => array('text' => 'prenom_khmer'),
                5 => array('text' => 'matricule'),
                6 => array('text' => 'dob'),
                7 => array('drop_down' => 'program_id'),
                8 => array('drop_down' => 'sex_id'),
                9 => array('drop_down' => 'active_id'),
                10 => array('submit' => 'update')
            )
        );

        $connection = new database();
        $table = new simple_table_ops();

        $sql = 'SELECT sex_id, sex FROM sexes';
        $sex_result = $connection->query($sql);

        $sql2 = 'SELECT active_id, active FROM actives';
        $active_result = $connection->query($sql2);

        $sql3 = 'SELECT program_id, program FROM programs';
        $programs_result = $connection->query($sql3);

        $drop_down = array(
            'sex_id' => array('sex' => $sex_result),
            'active_id' => array('active' => $active_result),
            'program_id' => array('program' => $programs_result)
        );

        $table->set_table_name('students');
        $table->set_id_column('student_id');

        $table->set_table_column_names($columns);
        $table->set_html_table_column_names($neat_columns);

        $table->set_values_form();                          // set values found in database into form elements when building top_form
        $table->set_drop_down($drop_down);
        $table->set_form_array($form);

        $content = '<table>';
        $content .= $table->details();
        $content .= '</table>';

        //require_once 'models/studentsModel.php';      // needless to add require

        $studentsModel_handler = new studentsModel();

        $studentsModel_handler->set_student_id($id);

        $content .= $studentsModel_handler->get_timetable_html();

        $content .= $studentsModel_handler->get_attendance()."<br>";
        $content .= $studentsModel_handler->get_results()."<br>";
        $output['content'] = $content;
        return $output;
    }

    public function update (){
        // update student based on $_post variables and $_get['id']
        //UPDATE multiple tables:
        //  UPDATE tables SET table1.col1=table2.col2
        //  WHERE condition;

        $sql = 'UPDATE students SET
            nom=?, prenom=?, nom_khmer=?, prenom_khmer=?, sex_id=?, matricule=?, dob=?, program_id=?, active_id=?
            WHERE student_id=?';

        $nom          = $_POST['nom'];
        $prenom       = $_POST['prenom'];
        $nom_khmer    = $_POST['nom_khmer'];
        $prenom_khmer = $_POST['prenom_khmer'];
        $sex_id       = $_POST['sex_id'];
        $matricule    = $_POST['matricule'];
        $dob          = $_POST['dob'];
        $program_id   = $_POST['program_id'];
        $active_id    = $_POST['active_id'];
        $id           = $_GET['id'];

        $data = array ($nom, $prenom, $nom_khmer, $prenom_khmer, $sex_id, $matricule, $dob, $program_id, $active_id, $id);

        $connection = new database();

        if ($connection->update($sql, $data)) {
            $content = "Affected rows: ";
            $content .=$connection->get_row_num();
        } else {
            $content = "Could not update student!";

        }

        header("Location: http://".WEBSITE_URL."/index.php?controller=students&action=index");

        $output['content'] = $content;
        return $output;

    }
}