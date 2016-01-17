<?php

class subjectsController {
    public function __construct(){}
    public function __clone(){}
    public function __destruct(){}

    public function index (){
        $connection = new database();
        $table = new simple_table_ops();
        $content  ='<p>Add or Delete a SUBJECT. <p>BE CAREFUL! When deleting, be sure you have just created the subject, and has not used it in [CURRICULA] table!';


        /* CONFIGURES Form structure */

        $top_form = array(
            'action'   => '?controller=subjects&action=add',
            'div'      => "class='solitary_input'",
            'method'   => 'post',
            'id'       => 'top_form',
            'elements' => array(
                1  => array ('text'   => 'subject'),
                2  => array ('text'   => 'subject_abb'),
                3  => array ('submit' => 'add')
            )
        );

        $table->set_top_form($top_form);
        /********************************************************************/

        /* CONFIGURES Main table contents (from MySQL) */

        $columns = array('Subject', 'Abbreviation', 'Action');

        $table->set_html_table_column_names($columns);

        $sql = 'SELECT subject_id, subject, subject_abb
                FROM subjects
                ORDER BY subject ASC
        ';

        $details_link = array(1=>array ('edit', '?controller=subjects&action=details&id='));
        $table->set_details_link($details_link);
        $table->set_main_table($connection->query($sql));
        /********************************************************************/
        $content .= '<table>'.$table->get_table().'</table>';
        $output['content'] = $content;
        return $output;
    }

    public function add (){
        $columns = array('subject', 'subject_abb');
        $table = new simple_table_ops();
        $table->set_table_name('subjects');
        $table->set_table_column_names($columns);
        $table->add();
        header("Location: http://".WEBSITE_URL."/index.php?controller={$_GET['controller']}&action=index");

    }

    public function delete (){
        $table = new simple_table_ops();
        $table->set_id_column('subject_id');
        $table->set_table_name('subjects');
        $table->delete();
        header("Location: http://".WEBSITE_URL."/index.php?controller={$_GET['controller']}&action=index");
    }

    public function details (){
        // details combines seeing with editing
        $id = $_GET['id'];
        $columns = array ('subject_id, subject, subject_abb');

        $neat_columns = array ('Subject', 'Abbreviation', 'Action');


        $form = array(
            'action'   => '?controller=subjects&action=update&id='.$id,
            'div'      => "class='solitary_input'",
            'method'   => 'post',
            'id'       => 'top_form',
            'elements' => array(
                1  => array ('text'   => 'subject'),
                2  => array ('text'   => 'subject_abb'),
                3  => array ('submit' => 'update')
            )
        );


        $table = new simple_table_ops();


        $table->set_id_column('subject_id');
        $table->set_table_column_names($columns);
        $table->set_html_table_column_names($neat_columns);
        $table->set_form_array($form);
        $table->set_table_name('subjects');
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
        $columns = array ('subject', 'subject_abb');
        $update->set_id_column('subject_id');
        $update->set_table_name('subjects');
        $update->set_table_column_names($columns);
        $update->update();
        header("Location: http://".WEBSITE_URL."/index.php?controller={$_GET['controller']}&action=index");
    }

}