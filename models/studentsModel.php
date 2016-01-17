<?php

class studentsModel {
    private $student_id;        // student_id

    public function set_student_id ($student_id){
        $this->student_id = $student_id;
    }

    public function get_student_id (){
        return $this->student_id;
    }

    public function get_timetable_html () {

        require_once 'controllers/timetableController.php';     // this however is necessary, go figure

        $timetableController_handler = new timetableController();

        $timetableController_handler->set_student_id($this->student_id);
        $timetableController_handler->set_timetable_results();
        $timetableController_handler->set_timetable_html();
        $timetable_html = $timetableController_handler->get_timetable_html();

        return $timetable_html;

    }

    public function get_timetable_period_id ()
    {
        //return $this->timetable_period_id;
        return "hypothetical timetable_period_id<br>";
    }

    public function get_attendance () {
        //TODO: returns $html string with student timetable
        $attendance = "attendance based on [current_school_year_id], [student_id] - display per curriculum_id";
        $connection = new database();
        $sql = "SELECT ";
        return $attendance;
    }

    public function get_results () {
        //TODO: returns $html string with student timetable
        $results = "results based on [current_school_year_id], [student_id], [result_id]";
        return $results;
    }
}