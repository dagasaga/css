<?php
class configurationController {
    public function __construct(){}
    public function __clone(){}
    public function __destruct(){}

    public function index (){
        $content = 'WARNING!<p><p>No record is safe to delete! They are all interdependent! Also, do not DUPLICATE records, such as students, programs, classes, school years, etc.';
        $content .= '<p>Example 1: if a teacher is no longer employed, INACTIVATE him/her in the [Teachers] table. He/she will no longer appear when creating new curricula, only in old curricula.';
        $content .= '<p>Example 2: if a students quits or is expelled, INACTIVATE him/her in the [Students] table. He/she will no longer appear when creating new classes, only in old classes.';
        $content .= "<div class='link_button'><a href='?controller=school_years&action=index'>School Years</a> - Add/Edit/Delete a school year (YYYY/YYYY). Used in the beginning of every school year.</div>";
        $content .= "<div class='link_button'><a href='?controller=courses&action=index'>Courses</a> - Add/Edit/Delete Courses. They are automatically created when a new school year is added. You should have no need to click here.</li></div>";
        $content .= "<div class='link_button'><a href='?controller=timetable2&action=index'>Timetable</a> - Add/Edit/Delete timetable periods. You should create a timetable period to identify the duration of each course EVERY year.</div>";
        $content .= "<div class='link_button'><a href='?controller=levels&action=index'>Levels</a> - Add/Edit/Delete levels (grades) names: 1st, 2nd, 3rd, etc. You should normally leave as is.</div>";
        $content .= "<div class='link_button'><a href='?controller=program&action=index'>Programs</a> - Add/Edit/Delete Programs. FA, FI, MF etc. If a new program is created, add it here.</div>";
        $content .= "<div class='link_button'><a href='?controller=subjects&action=index'>Subjects</a> - Add/Edit/Delete subjects. Math, Khmer, English, French, etc. You can edit the Khmer version as well.</div>";
        $content .= "<div class='link_button'><a href='?controller=time&action=index'>Class Hours</a> - Add/Edit/Delete class hours and minutes. You should normally leave as is.</div>";
        $content .= "<div class='link_button'><a href='?controller=weekdays&action=index'>Weekdays</a> - Edit weekdays. Mon, Tue, Wed, Thu, Fri, Sat, Sun are the default values. You can change for Khmer, if desired.</div>";
        $content .= "<p>-------------------ADMIN SECTION------------------";
        $content .= "<div class='link_button'><a href='?controller=admin&action=users_index'>Users</a> - Add/Edit/Delete users. If a user forgets his/her password, the [admin] can come here. .</div>";
        $content .= "missing: departments, positions";
        $output['content'] = $content;

        return $output;
    }
}