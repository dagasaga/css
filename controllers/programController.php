<?php

class programController {
    public function __construct(){}
    public function __clone(){}
    public function __destruct(){}

    public function index (){
        $connection = new database();
        $table = new simple_table_ops();
            $content  ='<p>Add or Delete a PROGRAM. <p>BE CAREFUL! When deleting, be sure you have just created the program, and has not used it in [STUDENTS] table!';


        /* CONFIGURES Form structure */

        $top_form = array(
            'action'   => '?controller=program&action=add',
            'div'      => "class='solitary_input'",
            'method'   => 'post',
            'id'       => 'top_form',
            'elements' => array(
                1  => array ('text'   => 'program'),
                2  => array ('submit' => 'add')
            )
        );

        $table->set_top_form($top_form);
        /********************************************************************/

        /* CONFIGURES Main table contents (from MySQL) */

        $columns = array('Program', 'Action');

        $table->set_html_table_column_names($columns);

        $sql = 'SELECT program_id, program
                FROM programs
                ORDER BY program DESC
        ';

        $details_link = array(1 => array ('delete', '?controller=program&action=delete&id='));

        $table->set_details_link($details_link);
        $table->set_main_table($connection->query($sql));
        /********************************************************************/
        $content .= '<table>'.$table->get_table().'</table>';
        $output['content'] = $content;
        return $output;
    }

    public function add (){
        $columns = array('program');
        $table = new simple_table_ops();
        $table->set_table_name('programs');
        $table->set_table_column_names($columns);
        $table->add();
        header("Location: http://".WEBSITE_URL."/index.php?controller={$_GET['controller']}&action=index");

    }

    public function delete (){
        $table = new simple_table_ops();
        $table->set_id_column('program_id');
        $table->set_table_name('programs');
        $table->delete();
        header("Location: http://".WEBSITE_URL."/index.php?controller={$_GET['controller']}&action=index");
    }

}