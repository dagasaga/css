<?php

class timetableController {
    //TODO ALL
    private $timetable_html;                // string: html: contains the timetable ready to display in html
    private $timetable_results;             // array: contains query results of timetable
    private $timetable_period_id;           // integer: self-explanatory
    private $student_id;                    // integer: if set, it is used to generate a single student's timetable


    public function __construct(){}
    public function __clone(){}
    public function __destruct(){}

    public function set_timetable_period_id ($timetable_period_id)
    {
        $this->timetable_period_id = $timetable_period_id;
    }
    public function set_student_id ($student_id)
    {
        $this->student_id = $student_id;
    }

    public function add_form ()
    {
        $table = new simple_table_ops();
        $connection = new database();

        // method [show] calls add_form with the following information inside $_GET:
        //      1. weekday_id
        //      2. classroom_id
        //      3. start_time_id
        //

        //  this method is similar to [index] method with top_form, but without previous id
        // TODO: all

        if (isset($_GET['weekday_id']))          $weekday_id          = $_GET['weekday_id'];
        if (isset($_GET['classroom_id']))        $classroom_id        = $_GET['classroom_id'];
        if (isset($_GET['start_time_id']))       $start_time_id       = $_GET['start_time_id'];
        if (isset($_GET['timetable_period_id'])) $timetable_period_id = $_GET['timetable_period_id'];

        $columns = array('Curricula', 'Action');

        if (isset($_GET['edit'])) {
            if ($_GET['edit'] == 'yes') {
                // get timetable_id to pre load drop down menu
                if (isset($_GET['curriculum_id'])) $curriculum_id = $_GET['curriculum_id'];
                $data = array ('curriculum_id' => $curriculum_id);
                $table->set_values_form_manually($data);
                $left_text = "<p>You will update the following curricula:";
                $button_action = "update";
                $columns[] = 'Delete';

            } else {
                $left_text = "<p>Select curricula to be inserted in:";
                $button_action = "add";
            }
        }

        $table->set_html_table_column_names($columns);
        /* end table column neat names */

        $sql = "SELECT weekday FROM weekdays WHERE weekday_id={$weekday_id}";
        $weekday_result = $connection->query($sql);
        $weekday = $weekday_result[0]['weekday'];

        $sql = "SELECT classroom FROM classrooms WHERE classroom_id={$classroom_id}";
        $classroom_result = $connection->query($sql);
        $classroom = $classroom_result[0]['classroom'];

        $sql = "SELECT time_class FROM time WHERE time_id={$start_time_id}";
        $start_time_result = $connection->query($sql);
        $start_time = $start_time_result[0]['time_class'];

        $sql = "SELECT CONCAT (nom, ' from ', date_from, ' to ', date_to) as timetable_period
                FROM timetable_periods
                WHERE timetable_period_id={$timetable_period_id}
                ";

        $timetable_period_result = $connection->query($sql);    // error when coming from timetable controller - no timetable_period_id defined!
        $timetable_period = $timetable_period_result[0]['timetable_period'];


        /* begin of left section */
        $content = "<div class='link_button'>
                        <a href='?controller=teachers&action=export'>Export to EXCEL</a>
                        <a href='?controller=timetable&action=show'>Timetable</a>
                        <a href='?controller=curricula&action=index'>Curricula</a>
                    </div>";

        $content .= "<div class='third_left'>
                    {$left_text}
                    <br>Timetable: {$timetable_period}
                    <br>Week day: {$weekday}
                    <br>Classroom: {$classroom}
                    <br>Start Time: {$start_time}

                    </div>";


        /* CONFIGURES DROP DOWN Menus */

        // Drop down with curricula
        $sql = "SELECT curricula.curriculum_id, CONCAT (subjects.subject, ' ', levels.level, ' - ', teachers.nom, ' ', teachers.prenom, ' ', teachers.nom_khmer, ' ', teachers.prenom_khmer, ' - ', sexes.sex) as curricula
                FROM curricula
                JOIN courses  ON courses.course_id   = curricula.course_id
                JOIN teachers ON teachers.teacher_id = curricula.teacher_id
                JOIN sexes    ON sexes.sex_id        = teachers.sex_id
                JOIN subjects ON subjects.subject_id = curricula.subject_id
                JOIN school_years ON school_years.school_year_id = courses.school_year_id
                JOIN levels   ON levels.level_id = courses.level_id
                WHERE courses.school_year_id = {$_SESSION['current_school_year_id']}
                ORDER BY subjects.subject ASC, levels.level_id ASC, teachers.nom ASC, teachers.prenom ASC
                ";
        $curricula_result = $connection->query($sql);

        $drop_down = array(
            'curriculum_id' => array('curricula' => $curricula_result)
        );

        $table->set_drop_down($drop_down);
        /********************************************************************/
        /* CONFIGURES Form structure */

        $weekday_array          = array ('weekday_id'          => $weekday_id);
        $classroom_array        = array ('classroom_id'        => $classroom_id);
        $start_time_array       = array ('start_time_id'       => $start_time_id);
        $end_time_array         = array ('end_time_id'         => $start_time_id + 1);
        $timetable_period_array = array ('timetable_period_id' => $timetable_period_id);


        $top_form = array(
            'action'     => "?controller=timetable&action={$button_action}&id=" . $_GET['timetable_id'],
            'div'        => "class='solitary_input'",
            'div_button' => "class='submit_button1'",
            'method'     => 'post',
            'id'         => 'top_form',
            'elements'   => array(
                1 => array('drop_down' => 'curriculum_id'),
                2 => array('hidden'    =>  $weekday_array),
                3 => array('hidden'    =>  $classroom_array),
                4 => array('hidden'    =>  $start_time_array),
                5 => array('hidden'    =>  $end_time_array),
                6 => array('hidden'    =>  $timetable_period_array),
                7 => array('submit'    =>  $button_action)
            )
        );

        if (isset($_GET['edit'])) {
            if ($_GET['edit'] == 'yes') {
                $top_form['action_links'] = array(1 => array('delete', "?controller=timetable&action=delete&timetable_period_id={$timetable_period_id}&id={$_GET['timetable_id']}"));
            }
        }
        //$_GET['id'] = $_GET['timetable_id'];
        $table->set_top_form($top_form);
        /* END top form */

        $content .= "<div class='two_thirds_right'>";
        $content .= "<table width='100%'>";
        $content .= $table->get_html_table_column_names();
        $content .= $table->get_form();
        $content .= '</table>';
        $content .= '</div>';

        $output['content'] = $content;
        return $output;
    }

    public function add (){
        //todo: check if time slot is not already occupied
        // receives $_GET['id'] to redirect to page showing same page

        $columns = array('curriculum_id', 'timetable_period_id', 'start_time_id', 'end_time_id', 'weekday_id', 'classroom_id');
        $table = new simple_table_ops();
        $table->set_table_name('timetables');
        $table->set_table_column_names($columns);

        $table->add();
        header("Location: http://".WEBSITE_URL."/index.php?controller=timetable&action=show&submit=yes&timetable_period_id={$_POST['timetable_period_id']}");
    }

    public function delete (){
        // receives ONLY the timetable_id

        $table = new simple_table_ops();
        $table->set_id_column('timetable_id');
        $table->set_table_name('timetables');
        $table->delete();

        header("Location: http://".WEBSITE_URL."/index.php?controller=timetable&action=show&submit=yes&timetable_period_id={$_GET['timetable_period_id']}");
    }

    public function activate (){

    }

    public function inactivate (){

    }

    public function edit (){
//TODO: details in timetable
        $connection = new database();
        $table = new simple_table_ops();

        $id = $_GET['id']; // timetable_id
        $content = "<div class='link_button'>
                        <a href='?controller=teachers&action=export'>Export to EXCEL</a>
                        <a href='?controller=curricula&action=index'>Curricula</a>
                    </div>";

        $content .= "<div class='third_left'>";
        $content .='<p>You can configure the timetable for the following course:<p>';

        $sql = "SELECT curricula.curriculum_id, CONCAT (teachers.nom, ' ', teachers.prenom, ' | ', teachers.nom_khmer, ' ', teachers.prenom_khmer, ' | ', sexes.sex) as teacher, subjects.subject, levels.level
                FROM curricula
                JOIN courses ON curricula.course_id = courses.course_id
                JOIN subjects ON curricula.subject_id = subjects.subject_id
                JOIN teachers ON teachers.teacher_id = curricula.teacher_id
                JOIN sexes  ON teachers.sex_id  = sexes.sex_id
                JOIN levels ON courses.level_id = levels.level_id
                JOIN timetables ON timetables.curriculum_id = curricula.curriculum_id
                WHERE timetables.timetable_id = {$_GET['id']}";

        $curricula_data = $connection->query($sql);

        if ($connection->get_row_num() == 0) {
            header("Location: http://".WEBSITE_URL."/index.php?controller=curricula&action=index");
        }

        $curricula_data = $curricula_data[0];

        $content .='Teacher: '.$curricula_data['teacher'].'<br>';
        $content .='Subject: '.$curricula_data['subject'].'<br>';
        $content .='Level: '.$curricula_data['level'].'<br>';


        $columns = array ('start_time_id, end_time_id, weekday_id, classroom_id, timetable_period_id');

        $neat_columns = array ('Start Time', 'End Time', 'Week Day', 'Classroom', 'Time Period' , 'Update', 'Delete');
        // create curriculum_id array
        $sql = "SELECT curriculum_id FROM timetables WHERE timetable_id = {$id}";

        $curriculum_id_result = $connection->query($sql);
        $curriculum_id_array = $curriculum_id_result[0];

        // time_id, weekday_id, curriculum_id, classroom_id,
        $sql = 'SELECT time_id as start_time_id, time_class as time1 FROM time ORDER BY time_id ASC';
        $time1_result = $connection->query($sql);

        $sql = 'SELECT time_id as end_time_id, time_class as time2 FROM time ORDER BY time_id ASC';
        $time2_result = $connection->query($sql);

        $sql = 'SELECT weekday_id, weekday FROM weekdays ORDER BY weekday_id';
        $weekdays_result = $connection->query($sql);

        $sql = "SELECT timetable_period_id, CONCAT(nom, ', from ', date_from, ' to ', date_to) as timetable_period FROM timetable_periods ORDER BY date_from";
        $timetable_periods_result = $connection->query($sql);

        $sql = 'SELECT classroom_id, classroom FROM classrooms ORDER BY classroom ASC';
        $classrooms_result = $connection->query($sql);

        $drop_down = array(
            'start_time_id' => array('start_time' => $time1_result),
            'end_time_id'   => array('end_time'   => $time2_result),
            'weekday_id'    => array('weekday'    => $weekdays_result),
            'timetable_period_id'=>array('timetable_period'=> $timetable_periods_result),
            'classroom_id'  => array('classroom'  => $classrooms_result)
        );

        /********************************************************************/
        /* CONFIGURES Form structure */

        $form = array(
            'action'       => '?controller=timetable&action=update&id='.$id,
            'div'          => "class='solitary_input'",
            'div_button'   => "class='submit_button1'",
            'method'       => 'post',
            'action_links' => array(1 => array('delete', '?controller=timetable&action=delete&id=')),
            'id'           => 'top_form',
            'elements'     => array(
                1  => array ('hidden'     => $curriculum_id_array),
                3  => array ('drop_down'  => 'start_time_id'),
                4  => array ('drop_down'  => 'end_time_id'),
                5  => array ('drop_down'  => 'weekday_id'),
                6  => array ('drop_down'  => 'classroom_id'),
                7  => array ('drop_down'  => 'timetable_period_id'),
                10 => array ('submit'     => 'update')
            )
        );

        $table->set_top_form($form);

        $table->set_table_name('timetables');
        $table->set_id_column('timetable_id');

        $table->set_table_column_names($columns);
        $table->set_html_table_column_names($neat_columns);

        $table->set_values_form();                          // set values found in database into form elements when building top_form
        $table->set_drop_down($drop_down);
        $table->set_form_array($form);

        $content .= "</div>";

        $content .= " <div class='two_thirds_right'><table>".$table->details().'</table></div>';
        $output['content'] = $content;
        return $output;
    }

    public function details (){
        //TODO: details in timetable
        $connection = new database();
        $table = new simple_table_ops();

        $id = $_GET['id']; // timetable_id
        $content = "<div class='link_button'>
                        <a href='?controller=teachers&action=export'>Export to EXCEL</a>
                        <a href='?controller=curricula&action=index'>Curricula</a>
                    </div>";

        $content .= "<div class='third_left'>";
        $content .='<p>You can configure the timetable for the following course:<p>';

        $sql = "SELECT curricula.curriculum_id, CONCAT (teachers.nom, ' ', teachers.prenom, ' | ', teachers.nom_khmer, ' ', teachers.prenom_khmer, ' | ', sexes.sex) as teacher, subjects.subject, levels.level
                FROM curricula
                JOIN courses ON curricula.course_id = courses.course_id
                JOIN subjects ON curricula.subject_id = subjects.subject_id
                JOIN teachers ON teachers.teacher_id = curricula.teacher_id
                JOIN sexes  ON teachers.sex_id  = sexes.sex_id
                JOIN levels ON courses.level_id = levels.level_id
                JOIN timetables ON timetables.curriculum_id = curricula.curriculum_id
                WHERE timetables.timetable_id = {$_GET['id']}";

        $curricula_data = $connection->query($sql);

        if ($connection->get_row_num() == 0) {
            header("Location: http://".WEBSITE_URL."/index.php?controller=curricula&action=index");
        }

        $curricula_data = $curricula_data[0];

        $content .='Teacher: '.$curricula_data['teacher'].'<br>';
        $content .='Subject: '.$curricula_data['subject'].'<br>';
        $content .='Level: '.$curricula_data['level'].'<br>';


        $columns = array ('start_time_id, end_time_id, weekday_id, classroom_id, timetable_period_id');

        $neat_columns = array ('Start Time', 'End Time', 'Week Day', 'Classroom', 'Time Period' , 'Update', 'Delete');

        // create curriculum_id array
        $sql = "SELECT curriculum_id FROM timetables WHERE timetable_id = {$id}";

        $curriculum_id_result = $connection->query($sql);
        $curriculum_id_array = $curriculum_id_result[0];

        // time_id, weekday_id, curriculum_id, classroom_id,
        $sql = 'SELECT time_id as start_time_id, time_class as time1 FROM time ORDER BY time_id ASC';
        $time1_result = $connection->query($sql);

        $sql = 'SELECT time_id as end_time_id, time_class as time2 FROM time ORDER BY time_id ASC';
        $time2_result = $connection->query($sql);

        $sql = 'SELECT weekday_id, weekday FROM weekdays ORDER BY weekday_id';
        $weekdays_result = $connection->query($sql);

        $sql = "SELECT timetable_period_id, CONCAT(nom, ', from ', date_from, ' to ', date_to) as timetable_period FROM timetable_periods ORDER BY date_from";
        $timetable_periods_result = $connection->query($sql);

        $sql = 'SELECT classroom_id, classroom FROM classrooms ORDER BY classroom ASC';
        $classrooms_result = $connection->query($sql);

        $drop_down = array(
            'start_time_id' => array('start_time' => $time1_result),
            'end_time_id'   => array('end_time'   => $time2_result),
            'weekday_id'    => array('weekday'    => $weekdays_result),
            'timetable_period_id'=>array('timetable_period'=> $timetable_periods_result),
            'classroom_id'  => array('classroom'  => $classrooms_result)
        );

        /********************************************************************/
        /* CONFIGURES Form structure */

        $form = array(
            'action'     => '?controller=timetable&action=update&id='.$id,
            'div'        => "class='solitary_input'",
            'div_button' => "class='submit_button1'",
            'method'     => 'post',
            'action_links' => array(1 => array('delete', '?controller=timetable&action=delete&id=')),
            'id'         => 'top_form',
            'elements'   => array(
                1  => array ('hidden'     => $curriculum_id_array),
                3  => array ('drop_down'  => 'start_time_id'),
                4  => array ('drop_down'  => 'end_time_id'),
                5  => array ('drop_down'  => 'weekday_id'),
                6  => array ('drop_down'  => 'classroom_id'),
                7  => array ('drop_down'  => 'timetable_period_id'),
                10 => array ('submit'     => 'update')
            )
        );

        $table->set_top_form($form);

        $table->set_table_name('timetables');
        $table->set_id_column('timetable_id');

        $table->set_table_column_names($columns);
        $table->set_html_table_column_names($neat_columns);

        $table->set_values_form();                          // set values found in database into form elements when building top_form
        $table->set_drop_down($drop_down);
        $table->set_form_array($form);


        $content .= "</div>";

        $content .= " <div class='two_thirds_right'><table>".$table->details().'</table></div>';
        $output['content'] = $content;
        return $output;

    }

    public function update (){

        if (!isset($_GET['id'])) {
            // id = timetable_period_id, NOT timetable_id !
            $content = "<p>Error in timetable controller, update action - id index is not set!";
            $output['content'] = $content;
            return $output;
        }


        $update = new simple_table_ops();

        $columns = array('curriculum_id', 'start_time_id', 'end_time_id', 'weekday_id', 'classroom_id', 'timetable_period_id');
        $update->set_id_column('timetable_id');
        $update->set_table_name('timetables');
        $update->set_table_column_names($columns);

        $update->update();

        header("Location: http://".WEBSITE_URL."/index.php?controller=timetable&action=show&submit=yes&timetable_period_id={$_POST['timetable_period_id']}");
    }

    public function show (){

        // TODO: when coming back from update, show previous timetable

        $connection = new database();
        $table = new simple_table_ops();

        if (isset($_POST['timetable_period_id'])) {
        //if (isset($_GET['timetable_period_id'])) {
            //$timetable_period_id = $_GET['timetable_period_id'];
            $timetable_period_id = $_POST['timetable_period_id'];

            $post_values = array ('timetable_period_id'=>$timetable_period_id);
            $table->set_values_form_manually($post_values);

        }

        if (isset($_GET['timetable_period_id'])) {

            $timetable_period_id = $_GET['timetable_period_id'];
            //$timetable_period_id = $_POST['timetable_period_id'];

            $post_values = array ('timetable_period_id'=>$timetable_period_id);
            $table->set_values_form_manually($post_values);

        }

        $this->timetable_period_id = $timetable_period_id;


        $content = "<div class='link_button'>
                        <a href='?controller=teachers&action=index'>Teacher</a>
                        <a href='?controller=curricula&action=index'>Curricula</a>
                    </div>";

        /* CONFIGURES DROP DOWN Menu */
        $sql = "SELECT timetable_period_id, CONCAT (nom, ' - ', date_from, ' to ', date_to) as period
                FROM timetable_periods
                WHERE school_year_id = {$_SESSION['current_school_year_id']}
                ";

        $timetable_periods_result = $connection->query($sql);

        $drop_down = array(
            'timetable_period_id' => array('period' => $timetable_periods_result)
        );

        $table->set_drop_down($drop_down);
        /********************************************************************/

        /* CONFIGURES Form structure */

        $top_form = array(
            'action'     => '?controller=timetable&action=show&submit=yes',
            'div'        => "class='solitary_input'",
            'div_button' => "class='submit_button1'",
            'method'     => 'post',
            'id'         => 'top_form',
            'elements'   => array(
                1  => array ('drop_down' => 'timetable_period_id'),
                3  => array ('submit'    => 'Show Timetable')
            )
        );

        $table->set_top_form($top_form);
        /********************************************************************/

        $columns = array('Timetable', 'Action');

        $table->set_html_table_column_names($columns);

        $content .= "<table width='auto'>".$table->get_html_table_column_names().$table->get_form().'</table>';

        if (isset($_GET['submit'])) {
            if ($_GET['submit'] == 'yes') {
                $this->set_timetable_html();
                $content .= $this->get_timetable_html();
            }
        }
        $output['content'] = $content;

        return $output;



    }

    public function set_timetable_html ()
    {
        if (isset($this->timetable_period_id)) {
            $timetable_period_id = $this->timetable_period_id;
        }
        $connection = new database();
        //this function receives timetable_id - then, when showing timetable for a single student,
        //just query the DB for the corresponding timetable_id from student_id AND $_SESSION['current_school_year_id']
        $sql = "SELECT weekday_id, weekday
                        FROM weekdays
                        ORDER BY weekday_id";
        $weekdays_results = $connection->query($sql);
        //var_dump ($weekdays_results);

        $sql = "SELECT time_id, time_class
                        FROM time
                        ORDER BY time_id";
        $time_results = $connection->query($sql);
        //var_dump ($time_results);
        // make table with weekday as title names and time as row names

        // extract classrooms to know how many colspans to use when making table
        $sql = "SELECT classroom_id, classroom
                        FROM classrooms
                ";

        $classroom_results = $connection->query($sql);
        $classroom_array = array();
        foreach ($classroom_results as $classroom){
            $classroom_array[] = $classroom['classroom_id'];
        }
        $total_classrooms = count($classroom_array);
        //echo $total_classrooms;

        //var_dump ($classroom_array);
        //die();

        $table_header = "<tr><td></td>";

        $weekday_array = array();
        foreach ($weekdays_results as $weekday) {
            $table_header .= "<td colspan='{$total_classrooms}'><div class='table_title1'>".$weekday['weekday']."</div></td>";

            // transform array into : weekday_id => 'weekday'
            $weekday_array[$weekday['weekday_id']] = $weekday['weekday'];

        }

        $table_header .= '</tr>';

        $table_header_secondary = "<tr><td><div class='table_title1'>Start-End</div></td>";

        foreach ($weekdays_results as $weekday) {
            foreach ($classroom_results as $classroom) {
                $table_header_secondary .= "<td><div class='table_title1'>".$classroom['classroom']."</div></td>";
            }
        }
        $table_header_secondary .= '</tr>';
        //var_dump ($table_header_secondary);


        // TODO: fetch all info from timetables WHERE timetable_period_id = $timetable_period_id from user

        /******************** make a new method: set_timetable_results ()
        $sql = "SELECT timetables.timetable_id, CONCAT (subjects.subject_abb, levels.level_abb) as sublev, timetables.timetable_period_id, timetables.weekday_id, timetables.classroom_id, timetables.start_time_id, timetables.end_time_id, timetables.curriculum_id
                        FROM timetables
                        JOIN curricula ON curricula.curriculum_id = timetables.curriculum_id
                        JOIN subjects  ON subjects.subject_id     = curricula.subject_id
                        JOIN courses   ON courses.course_id       = curricula.course_id
                        JOIN levels    ON levels.level_id         = courses.level_id
                        WHERE timetable_period_id = {$timetable_period_id}
                ";

        $timetable_results = $connection->query($sql);
        ********************************************************* end of new method */
        $this->set_timetable_results();

        $timetable_results = $this->timetable_results;

        // create a timetable array suitable to iterate with week/time/classroom foreach
        $timetable_array = array();
        foreach ($timetable_results as $row) {
            $timetable_array[$row['weekday_id']][$row['start_time_id']][$row['classroom_id']] = array('sublev' => $row['sublev'], 'timetable_id'=> $row['timetable_id'], 'curriculum_id' => $row['curriculum_id']);

        }

        $table_body = '';

        foreach ($time_results as $time_class) {
            $table_background_flag = '1';
            // loops through all times
            $table_body .= "<tr><td><div class='table_title1'>{$time_class['time_class']}</div></td>";
            foreach ($weekday_array as $day_id => $day_name) {
                // loops through a week
                // $day_id is the weekday_id - use this to display information inside table
                // $time_class['time_id']
                if ($table_background_flag == '2' ) {

                    $table_background_flag = '1';
                } else {

                    $table_background_flag = '2';
                }
                foreach ($classroom_array as $classroom) {
                    // loops through all classrooms

                    if (isset($timetable_array[$day_id][$time_class['time_id']][$classroom])) {
                        $cell_output = $timetable_array[$day_id][$time_class['time_id']][$classroom]['sublev'];
                        $timetable_id = $timetable_array[$day_id][$time_class['time_id']][$classroom]['timetable_id'];
                        $curriculum_id = $timetable_array[$day_id][$time_class['time_id']][$classroom]['curriculum_id'];
                        $edit = 'yes';
                        $time_id = $time_class['time_id'];
                    } else {
                        $time_id = $time_class['time_id'];
                        $edit = 'no';
                        $timetable_id = '';
                        $cell_output = "...";
                        $curriculum_id = '';
                    }
                    //$table_body .= "<td><div class='table_row{$table_background_flag}'>{$curricula}</div></td>";
                    //$table_body .= "<td><div class='table_row{$table_background_flag}'><div class='timetable_link_button'><a href='?controller=timetable&action=add_form&edit=yes&id={$id}'>{$timetable_id}</a></div></div></td>";
                    $table_body .= "<td><div class='timetable_link_button'><div class='table_row{$table_background_flag}'>";
                    if (isset($timetable_period_id)) {
                        $table_body .= "<a href='?controller=timetable&action=add_form&edit={$edit}&curriculum_id={$curriculum_id}&timetable_id={$timetable_id}&weekday_id={$day_id}&classroom_id={$classroom}&start_time_id={$time_id}&timetable_period_id={$timetable_period_id}'>{$cell_output}</a></div></div></td>";
                    } else {
                        $table_body .= $cell_output . "</div></div></td>";
                    }
                }
            }
            $table_body .= '</tr>';
        }

        $final_table = "<p></p><table width='100%'>".$table_header.$table_header_secondary.$table_body;
        $final_table .="</table>";

        $this->timetable_html = $final_table;
    }

    public function set_timetable_results ()
    {
        $connection = new database();
        if (isset($this->student_id)) {
            $sql = "SELECT timetables.timetable_id, CONCAT (subjects.subject_abb, levels.level_abb) as sublev, timetables.timetable_period_id, timetables.weekday_id, timetables.classroom_id, timetables.start_time_id, timetables.end_time_id, timetables.curriculum_id
                    FROM timetables
                    JOIN curricula ON curricula.curriculum_id = timetables.curriculum_id
                    JOIN subjects  ON subjects.subject_id     = curricula.subject_id
                    JOIN courses   ON courses.course_id       = curricula.course_id
                    JOIN levels    ON levels.level_id         = courses.level_id
                    JOIN classes   ON courses.course_id       = classes.course_id
                    JOIN students  ON students.student_id     = classes.student_id
                    JOIN school_years ON school_years.school_year_id = courses.school_year_id
                    WHERE students.student_id = {$this->student_id} AND school_years.school_year_id = {$_SESSION['current_school_year_id']}
                    ";
        } elseif (isset($this->timetable_period_id)) {
            $sql = "SELECT timetables.timetable_id, CONCAT (subjects.subject_abb, levels.level_abb) as sublev, timetables.timetable_period_id, timetables.weekday_id, timetables.classroom_id, timetables.start_time_id, timetables.end_time_id, timetables.curriculum_id
                    FROM timetables
                    JOIN curricula ON curricula.curriculum_id = timetables.curriculum_id
                    JOIN subjects  ON subjects.subject_id     = curricula.subject_id
                    JOIN courses   ON courses.course_id       = curricula.course_id
                    JOIN levels    ON levels.level_id         = courses.level_id
                    WHERE timetable_period_id = {$this->timetable_period_id}
                    ";

        } else {
            // no valid data to work with - missing both student_id and timetable_period_id
            echo "no valid data to work with - missing both student_id and timetable_period_id in timetableController->set_timetable_results method.<br>";
            echo $this->timetable_period_id." - ".$this->student_id;
            die();

        }
        //echo "<p>".$sql."<br>";

        $this->timetable_results = $connection->query($sql);

    }

    public function get_timetable_results ()
    {
        return $this->timetable_results;
    }

    public function get_timetable_html ()
    {
        return $this->timetable_html;
    }

    public function export (){
        // Export ALL courses - a no-brainer

    }
}