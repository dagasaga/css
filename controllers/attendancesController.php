<?php
class attendancesController
{

    public function __construct()
    {
    }

    public function __clone()
    {
    }

    public function __destruct()
    {
    }

    public function index()
    {
        $content = "<div class='link_button'>
                        <a href='?controller=attendances&action=export'>Export to EXCEL</a>

                    </div>";

        // attendances index:
        //   Receives curriculum_id -

        /* CONFIGURES DROP DOWN Menus (2 in this case: programs and genre) */
        $connection = new database();
        $table = new simple_table_ops();

        $sql = "SELECT month_id, month FROM months ORDER BY month_id";
        $months_result = $connection->query($sql);

        $sql = "SELECT curricula.curriculum_id,
                    CONCAT (teachers.nom, ' ', teachers.prenom, ' ', teachers.nom_khmer, ' ', teachers.prenom_khmer, ' ',
                    subjects.subject, ' ', levels.level) as curricula
                FROM curricula
                JOIN teachers ON teachers.teacher_id = curricula.teacher_id
                JOIN subjects ON subjects.subject_id = curricula.subject_id
                JOIN courses  ON courses.course_id   = curricula.course_id
                JOIN levels   ON levels.level_id     = courses.level_id
                WHERE courses.school_year_id = {$_SESSION['current_school_year_id']}
                ORDER BY levels.level_id ASC, teachers.nom ASC";

        $curricula_result = $connection->query($sql);


        $drop_down = array(
            'curriculum_id' => array('curricula' => $curricula_result),
            'month_id' => array('month' => $months_result)
        );

        // TODO: set values from $_POST if $_POST isset in order to preset drop down values
        if (isset($_POST['curriculum_id']) and isset($_POST['month_id'])) {
            $curriculum_id = $_POST['curriculum_id'];
            $month_id = $_POST['month_id'];
            $post_values = array('curriculum_id' => $curriculum_id, 'month_id' => $month_id);

            $table->set_values_form_manually($post_values);
        }

        $table->set_drop_down($drop_down);
        /********************************************************************/

        /* CONFIGURES Form structure */

        $top_form = array(
            'action' => '?controller=attendances&action=index&submit=yes',
            'div' => "class='solitary_input'",
            'div_button' => "class='submit_button1'",
            'method' => 'post',
            'id' => 'top_form',
            'elements' => array(
                1 => array('drop_down' => 'curriculum_id'),
                2 => array('drop_down' => 'month_id'),
                3 => array('submit' => 'Show Attendance Table')
            )
        );

        $table->set_top_form($top_form);
        /********************************************************************/

        $columns = array('Curricula', 'Month', 'Action');

        $table->set_html_table_column_names($columns);

        $content .= "<table width='auto'>" . $table->get_html_table_column_names() . $table->get_form() . '</table>';

        if (isset($_GET['submit'])) {
            if ($_GET['submit'] == 'yes') {
                //$second_table = new simple_table_ops();
                // TODO:REFACTOR
                // show table
                // 1. Get records from [attendance] table corresponding to
                // $_POST['course_id'] groups the records in classes->attendances.
                // From classes we get the students list and creates the attendance list with a 'text' input in array

                // COUNT DAYS IN MONTH - separate in first/second school year
                $year_first_semester = substr($_SESSION['current_school_year'], 0, 4);
                $year_second_semester = substr($_SESSION['current_school_year'], -4, 4);

                if ($month_id < 9) {
                    // user wants attendance list of first semester
                    $total_days = cal_days_in_month(CAL_GREGORIAN, $month_id, $year_first_semester);
                } else {
                    // user wants attendance list of second semester
                    $total_days = cal_days_in_month(CAL_GREGORIAN, $month_id, $year_second_semester);
                }
                $input_form = '';

                // retrieves info (if any) from [attendances] table
                //      using:
                //          1. curriculum_id
                //          2. month_id
                //
                $days_month = '';
                for ($i = 1; $i <= 31; $i++) {
                    $days_month .= "attendances.day" . $i . ",";
                }

                $days_month = substr($days_month, 0, -1);


                $sql = "SELECT classes.student_id, {$days_month}
                        FROM attendances
                        JOIN classes ON classes.classe_id = attendances.classe_id
                        JOIN curricula ON curricula.curriculum_id = attendances.curriculum_id
                        WHERE curricula.curriculum_id = {$curriculum_id} AND attendances.month_id = {$month_id}";

                $sda = $connection->query($sql);


                // Now, should extract students (in the form of classes.classe_id) found in classes absent in [attendances]
                // This is necessary if a student starts studying in the middle of the school year

                $missing_students_sql = "SELECT classes.classe_id
                        FROM classes
                        JOIN courses  ON courses.course_id   = classes.course_id
                        JOIN curricula ON curricula.course_id = courses.course_id
                        WHERE curricula.curriculum_id = {$curriculum_id} AND NOT classes.student_id IN
                            (SELECT classes.student_id
                            FROM attendances
                            JOIN classes ON classes.classe_id = attendances.classe_id
                            JOIN curricula ON curricula.curriculum_id = attendances.curriculum_id
                            WHERE curricula.curriculum_id = {$curriculum_id} AND attendances.month_id = {$month_id})";

                $missing_classes_id = $connection->query($missing_students_sql);

                // then, insert missing students into [attendances] table, and present it to user
                $sql = "INSERT INTO attendances (classe_id, curriculum_id, month_id) VALUES (?, ?, ?)";

                foreach ($missing_classes_id as $row) {
                    $classe_id = $row['classe_id'];
                    echo $classe_id.", ";
                    $data[] = array($classe_id, $curriculum_id, $month_id);
                    // $connection->insert($sql, $data);

                }

                $connection->insert($sql, $data);

                // this selects all students found in [attendances], which should be now up-to-date
                $sql = "SELECT attendances.attendance_id, CONCAT(students.nom, ' ', students.prenom) as nom_prenom, programs.program, sexes.sex, {$days_month}
                        FROM curricula
                        JOIN courses  ON curricula.course_id = courses.course_id
                        JOIN classes  ON courses.course_id   = classes.course_id
                        JOIN students ON classes.student_id  = students.student_id
                        JOIN attendances ON attendances.classe_id = classes.classe_id
                        JOIN sexes ON sexes.sex_id = students.sex_id
                        JOIN programs ON programs.program_id = students.program_id
                        WHERE curricula.curriculum_id = {$curriculum_id} AND attendances.month_id = {$month_id}
                        ORDER BY students.nom ASC, students.prenom ASC";


                $sql = "SELECT attendances.attendance_id, CONCAT(students.nom, ' ', students.prenom) as nom_prenom, programs.program, sexes.sex, {$days_month}
                        FROM curricula
                        JOIN courses  ON curricula.course_id = courses.course_id
                        JOIN classes  ON courses.course_id   = classes.course_id
                        JOIN students ON classes.student_id  = students.student_id
                        JOIN attendances ON attendances.classe_id = classes.classe_id
                        JOIN sexes ON sexes.sex_id = students.sex_id
                        JOIN programs ON programs.program_id = students.program_id
                        WHERE curricula.curriculum_id = {$curriculum_id} AND attendances.month_id = {$month_id}
                        ORDER BY students.nom ASC, students.prenom ASC";


                $sql = "SELECT attendances.attendance_id, CONCAT(students.nom, ' ', students.prenom) as nom_prenom, programs.program, sexes.sex, attendances.day1,attendances.day2,attendances.day3,attendances.day4,attendances.day5,attendances.day6,attendances.day7,attendances.day8,attendances.day9,attendances.day10,attendances.day11,attendances.day12,attendances.day13,attendances.day14,attendances.day15,attendances.day16,attendances.day17,attendances.day18,attendances.day19,attendances.day20,attendances.day21,attendances.day22,attendances.day23,attendances.day24,attendances.day25,attendances.day26,attendances.day27,attendances.day28,attendances.day29,attendances.day30,attendances.day31
                        FROM attendances
                        JOIN curricula ON curricula.curriculum_id = attendances.curriculum_id
                        JOIN classes   ON classes.classe_id  = attendances.classe_id
                        JOIN students  ON classes.student_id = students.student_id
                        JOIN sexes     ON sexes.sex_id = students.sex_id
                        JOIN programs  ON programs.program_id = students.program_id
                        WHERE curricula.curriculum_id = {$curriculum_id} AND attendances.month_id = {$month_id}
                        ORDER BY students.nom ASC, students.prenom ASC";

                $students_id = $connection->query($sql);


                /* CONFIGURES DROP DOWN Menu ****************************************/
                // Bug: drop down menu on ALL input elements is PLAIN UGLY. TODO: better insert info on large form
                $sql = 'SELECT attendance_check_id, attendance_abb FROM attendance_checks';
                $attendances_result = $connection->query($sql);
                /********************************************************************/


                $input_form .= "<tr><td><center>Student</center></td><td>Program</td><td>Genre</td>";
                for ($i = 1; $i <= $total_days; $i++) {
                    $input_form .= "<td><center>{$i}</center></td>";
                }
                $input_form .= "</tr>";

                foreach ($students_id as $student_id) {
                    $input_form .= "<tr><td>" . $student_id['nom_prenom'] . "</td><td><center>" . $student_id['program'] . "</center></td><td>" . $student_id['sex'] . "</td>";
                    for ($i = 1; $i <= $total_days; $i++) {
                        $name_day = "attendance[" . $student_id['attendance_id'] . "][" . $i . "]";
                        $current_day = 'day' . $i;

                        $value = $student_id[$current_day];

                        // drop down menu on EACH cell is just too ugly - leave as text, and do a validation later in 'save' method
                        $input_form .= "<td><input type = 'text' name = '{$name_day}' value = '{$value}' size='1'></td>";

                    }
                    $input_form .= "</tr>";
                }

                $input_form .= "<input type='submit' value='save'>";

                $content .= "<table><form method='post' action='?controller=attendances&action=save&id={$curriculum_id}&total_days={$total_days}' id='top_form'>";
                $content .= $input_form;
                $content .= "</form></table>";
            }
        }

        $output['content'] = $content;

        return $output;
    }


    public function save()
    {
        // Receives $_POST[attendance_id][day#] and curriculum_id ($_GET['id'])
        // UPDATE (since records were produced from [attendances] table itself
        //
        //TODO: date validation - should comply with attendances table

        $connection = new database();
        $columns = '';

        for ($i = 1; $i <= $_GET['total_days']; $i++) {
            $columns .= "day" . $i . "=?,";
        }
        $columns = substr($columns, 0, -1);

        $sql = "UPDATE attendances SET {$columns} WHERE attendance_id=?";
        //var_dump($sql);
        //var_dump($_POST);

        foreach ($_POST as $attendances) {
            foreach ($attendances as $attendance_id => $month) {
                // $attendance_id;
                $data = array();
                foreach ($month as $day) {
                    $data[] .= $day;
                }
                $data[] .= $attendance_id;
                $connection->update($sql, $data);
            }
        }
        //var_dump($data);
        //die();
        // TODO: to redirect, need set $_POST['curriculum'] and $_POST['month']
        header("Location: http://".WEBSITE_URL."/index.php?controller=attendances&action=index");
    }
}