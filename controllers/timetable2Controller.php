<?php

class timetable2Controller {
    public function __construct(){}
    public function __clone(){}
    public function __destruct(){}

    public function index (){
        $connection = new database();
        $table = new simple_table_ops();
        $content  ='<p>Add or Delete a Timetable Identifier.';

        /* CONFIGURES Form structure */

        $top_form = array(
            'action'   => '?controller=timetable2&action=add',
            'div'      => "class='solitary_input'",
            'div_button' => "class='submit_button1'",
            'method'   => 'post',
            'id'       => 'top_form',
            'elements' => array(
                1  => array ('text'   => 'nom'),
                2  => array ('date'   => 'date_from'),
                3  => array ('date'   => 'date_to'),
                4  => array ('submit' => 'add')
            )
        );

        $table->set_top_form($top_form);
        /********************************************************************/

        /* CONFIGURES Main table contents (from MySQL) */

        $columns = array('Timetable Name', 'Start Date', 'End Date', 'Action');

        $table->set_html_table_column_names($columns);

        $sql = "SELECT timetable_period_id, nom, date_from, date_to
                FROM timetable_periods
                WHERE school_year_id={$_SESSION['current_school_year_id']}
                ORDER BY date_from DESC, date_to DESC
        ";

        $details_link = array(
            1 => array ('edit', '?controller=timetable2&action=details&id='));

        $table->set_details_link($details_link);
        $table->set_main_table($connection->query($sql));
        /********************************************************************/
        $content .= '<table>'.$table->get_table().'</table>';
        $output['content'] = $content;
        return $output;
    }

    public function details (){
        // TODO: details on timetable2 controller
        $id = $_GET['id'];
        $columns = array ('nom, date_from, date_to');

        $neat_columns = array ('Timetable Name', 'Start Date', 'End Date', 'Action', 'Delete');

        $form = array(
            'action'   => '?controller=timetable2&action=update&id='.$id,
            'div'      => "class='solitary_input'",
            'div_button' => "class='submit_button1'",
            'action_links'=> array(1=>array ('delete',   '?controller=timetable2&action=delete&id=')),
            'method'   => 'post',
            'id'       => 'top_form',
            'elements' => array(
                1  => array ('text'   => 'nom'),
                2  => array ('date'   => 'date_from'),
                3  => array ('date'   => 'date_to'),
                4  => array ('submit' => 'update')
            )
        );


        $table = new simple_table_ops();

        $table->set_id_column('timetable_period_id');
        $table->set_table_column_names($columns);
        $table->set_html_table_column_names($neat_columns);
        $table->set_form_array($form);
        $table->set_table_name('timetable_periods');
        $table->set_values_form();
        $content = '<table>';
        $content .= $table->details();
        $content .='</table>';
        $output['content']=$content;
        return $output;
    }

    public function add (){
        $columns = array('nom', 'school_year_id', 'date_from', 'date_to');
        $_POST['school_year_id']=$_SESSION['current_school_year_id'];
        $table = new simple_table_ops();
        $table->set_table_name('timetable_periods');
        $table->set_table_column_names($columns);
        $table->add();
        header("Location: http://".WEBSITE_URL."/index.php?controller={$_GET['controller']}&action=index");

    }

    public function update (){
        // id value has already been sent into <form action string>
        // just call simple_table_ops class, which will process $_POST['variables']
        $update = new simple_table_ops();
        $columns = array ('nom', 'date_from', 'date_to');
        $update->set_id_column('timetable_period_id');
        $update->set_table_name('timetable_periods');
        $update->set_table_column_names($columns);
        $update->update();
        header("Location: http://".WEBSITE_URL."/index.php?controller={$_GET['controller']}&action=index");

    }


    public function delete (){
        $table = new simple_table_ops();
        $table->set_id_column('timetable_period_id');
        $table->set_table_name('timetable_periods');
        $table->delete();
        header("Location: http://".WEBSITE_URL."/index.php?controller={$_GET['controller']}&action=index");
    }

}