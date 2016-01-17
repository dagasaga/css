<?php

class levelsController {
    public function __construct(){}
    public function __clone(){}
    public function __destruct(){}

    public function index (){
        $connection = new database();
        $table = new simple_table_ops();
        $content  ='<p>Add or Delete a LEVEL. It corresponds to a CLASS in French. <p>BE CAREFUL! When deleting, be sure you have just created the level, and has not used it in [COURSES] table!';


        /* CONFIGURES Form structure */

        $top_form = array(
            'action'   => '?controller=levels&action=add',
            'div'      => "class='solitary_input'",
            'method'   => 'post',
            'id'       => 'top_form',
            'elements' => array(
                1  => array ('text'   => 'level'),
                2  => array ('submit' => 'add')
            )
        );

        $table->set_top_form($top_form);
        /********************************************************************/

        /* CONFIGURES Main table contents (from MySQL) */

        $columns = array('Level', 'Action');

        $table->set_html_table_column_names($columns);

        $sql = 'SELECT level_id, level
                FROM levels
                ORDER BY LENGTH (level), level
        ';

        $details_link = array(1=>array ('delete', '?controller=levels&action=delete&id='));
        $table->set_details_link($details_link);
        $table->set_main_table($connection->query($sql));
        /********************************************************************/
        $content .= '<table>'.$table->get_table().'</table>';
        $output['content'] = $content;
        return $output;
    }

    public function add (){
        $columns = array('level');
        $table = new simple_table_ops();
        $table->set_table_name('levels');
        $table->set_table_column_names($columns);
        $table->add();
        header("Location: http://".WEBSITE_URL."/index.php?controller={$_GET['controller']}&action=index");
    }

    public function delete (){
        $table = new simple_table_ops();
        $table->set_id_column('level_id');
        $table->set_table_name('levels');
        $table->delete();
        header("Location: http://".WEBSITE_URL."/index.php?controller={$_GET['controller']}&action=index");
    }

}