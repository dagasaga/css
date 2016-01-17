<?php
class timeController {
    public function __construct(){}
    public function __clone(){}
    public function __destruct(){}

    public function index (){
        //  ______________________________
        // | School Year | Level | Action |
        // |---------------------|--------|
        // |             |       | [add]  |
        // |------------------------------|
        // | 2015/2016   |       | details|
        //  ------------------------------
        //
        /* CONFIGURES DROP DOWN Menus (2 in this case: programs and genre) */
        $connection = new database();
        $table = new simple_table_ops();

        $content = "<div class='third_left'><p>Here are the class hours. If you change when a class starts/ends, change it here, but<p>DO NOT change the ORDER!";

        $sql = 'SELECT time_id, time_class FROM time ORDER BY time_id ASC';
        $levels_result = $connection->query($sql);

        $drop_down = array(
            'time_id'       => array('time_class'       => $levels_result)
        );

        $table->set_drop_down($drop_down);
        /********************************************************************/

        /* CONFIGURES Form structure */

        $top_form = array(
            'action'   => '?controller=time&action=add',
            'div'      => "class='solitary_input'",
            'method'   => 'post',
            'id'       => 'top_form',
            'elements' => array(
                1  => array ('drop_down' => 'time_id'),
                2  => array ('submit'    => 'add')
            )
        );

        $table->set_top_form($top_form);
        /********************************************************************/

        /* CONFIGURES Main table contents (from MySQL) */

        $columns = array('Time', 'Action');

        $table->set_html_table_column_names($columns);

        $sql = "SELECT time.time_id, time_class
                FROM time
                ORDER BY time_id ASC
        ";

        $result = $connection->query($sql);

        if ($connection->get_row_num() == 0) {
            $content .= "<p>Currently, you have no time configured. Choose one and click [ADD].";
        } else {
            $content .= "<p>Currently, you have " . $connection->get_row_num() . " time configured.";
        }

        $content .="</div>";
        $details_link = array(1=>array ('details', '?controller=time&action=details&id='));
        $table->set_details_link($details_link);
        $table->set_main_table($result);
        /********************************************************************/

        $content .= "<div class='third_middle'><table>{$table->get_table()}</table></div>";

        $output['content'] = $content;

        return $output;
    }

    public function add (){
        $columns = array('time_id');
        $table = new simple_table_ops();
        $table->set_table_name('time');
        $table->set_table_column_names($columns);
        $table->add();
        header("Location: http://".WEBSITE_URL."/index.php?controller={$_GET['controller']}&action=index");

    }

    public function delete (){
        $table = new simple_table_ops();
        $table->set_id_column('time_id');
        $table->set_table_name('time');
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
        $columns = array ('time_class');

        $neat_columns = array ('Time', 'Action');


        $form = array(
            'action'   => '?controller=time&action=update&id='.$id,
            'div'      => "class='solitary_input'",
            'method'   => 'post',
            'id'       => 'top_form',
            'elements' => array(
                1  => array ('text'   => 'time_class'),
                2  => array ('submit' => 'update')
            )
        );


        $table = new simple_table_ops();

        $table->set_id_column('time_id');
        $table->set_table_column_names($columns);
        $table->set_html_table_column_names($neat_columns);
        $table->set_form_array($form);
        $table->set_table_name('time');
        $table->set_values_form();                          // set values found in database into form elements when building top_form

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
        $columns = array ('time_class');
        $update->set_id_column('time_id');
        $update->set_table_name('time');
        $update->set_table_column_names($columns);

        return $update->update();

    }
    public function export (){
        // TODO: export CSV
        // Export ALL teachers - a no-brainer

    }
}