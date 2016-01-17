<?php

class weekdaysController {
    public function __construct(){}
    public function __clone(){}
    public function __destruct(){}

    public function index (){
        $connection = new database();
        $table = new simple_table_ops();
        $content  ='<p>Edit the names of the WEEK, which starts always on Mondays.';
        $content .='<p>BE CAREFUL not to mistake the days, they are used in the [TIMETABLE] table.';
        $content .='<p>The order they appear here are of utmost importance: change this and you will mess all timetables!';
        $content .='<p>You obviously cannot add or delete a day, simply because in a week you will always have 7 days :)';


        /* CONFIGURES Main table contents (from MySQL) */

        $columns = array('Week Day', 'Action');

        $table->set_html_table_column_names($columns);

        $sql = 'SELECT weekday_id, weekday
                FROM weekdays
                ORDER BY weekday_id ASC
        ';

        $details_link = array(1=>array ('edit', '?controller=weekdays&action=details&id='));
        $table->set_details_link($details_link);
        $table->set_main_table($connection->query($sql));
        /********************************************************************/
        $content .= '<table width="250px">'.$table->get_table().'</table>';
        $output['content'] = $content;
        return $output;
    }

    public function add (){
        $columns = array('weekday');
        $table = new simple_table_ops();
        $table->set_table_name('weekdays');
        $table->set_table_column_names($columns);
        $table->add();

    }

    public function details (){
        // details combines seeing with editing
        $id = $_GET['id'];
        $columns = array ('weekday');

        $neat_columns = array ('Week Day', 'Action');


        $form = array(
            'action'   => '?controller=weekdays&action=update&id='.$id,
            'div'      => "class='solitary_input'",
            'method'   => 'post',
            'id'       => 'top_form',
            'elements' => array(
                1  => array ('text'      => 'weekday'),
                2  => array ('submit'    => 'update')
            )
        );

        $table = new simple_table_ops();

        $table->set_id_column('weekday_id');
        $table->set_table_column_names($columns);
        $table->set_html_table_column_names($neat_columns);
        $table->set_form_array($form);
        $table->set_table_name('weekdays');
        $table->set_values_form();
        $content = '<table>';
        $content .= $table->details();
        $content .='</table>';
        $output['content']=$content;
        return $output;
    }

    public function update (){
        // id value has already been sent into <form action string>
        // just call simple_table_ops class, which will process $_POST['variables']
        $update = new simple_table_ops();
        $columns = array ('weekday');
        $update->set_id_column('weekday_id');
        $update->set_table_name('weekdays');
        $update->set_table_column_names($columns);

        $update->update();
        header("Location: http://".WEBSITE_URL."/index.php?controller={$_GET['controller']}&action=index");

    }
}