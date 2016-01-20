<?php
class simple_table_ops {
    private $token;             // STRING token used by all methods in this class for identity check
    private $table_name;        // STRING table name
    private $column_names;      // STRING HTML row with neat column names
    private $cols;              // ARRAY  with table column names
    private $top_form;          // STRING HTML top form (in a <tr></tr>
    private $main_table;        // STRING HTML of all contents of a table
    private $drop_down;         // STRING HTML of drop-down (select option) in an array. called when doing $top_form
    private $html_check_box;         // STRING HTML of check-boxes in an array. called when doing $top_form (which can be used as a big form)
    private $details_link;      //  STRING url of details link, which will have record id appended at the end
    private $id_column;         // STRING id column name used as reference in table operations (delete, update)
    private $form_values;       // ARRAY  contains retrieved values from DB to populate details FORM. set_drop_down will verify
                                // this property to check if a value has been assigned (which happens inside 'details' method
    private $form_array;        // ARRAY  form structure
    private $obligatory_fields; // ARRAY  contains table column names which are obligatory, as well as the expected format.Ex:
                                //          $obligatory_fields = array('nom'=>'text', 'matricule'=>'##AAA###');



    public function __construct(){}
    public function __clone(){}
    public function __destruct(){}

    public function set_obligatory_fields($obligatory_field)
    {
        $this->obligatory_fields = $obligatory_field;
    }

    public function set_html_check_box ($check_box){
        // here, process $check_box array  into html digestible
        // each key is in fact the id column?
        // receives: array (0 => array('ID' => 1, 'nom' => 'GAY', 'prenom' => 'Very'));
        // first column from each array is ALWAYS the id column;
        $html = '';
        foreach ($check_box as $fields){
            $html .="<tr><td>";
            //var_dump($fields);
            //var_dump($this->id_column);
            $id = $fields[$this->id_column];

            $html .= "<input type='checkbox' name='checkbox_array[]' value='{$id}'/></td>";
            $i = 0;
            foreach ($fields as $value){
                if ($i !== 0) {
                    $html .= '<td>' . $value . '</td>';
                }
                $i=1;
            }
            // TODO: foreach details_link
            if (isset($this->details_link)) {
                $html .= "<td><div class='small_link_button'>";
                foreach ($this->details_link as $row) {
                    //var_dump($row);
                    $html .= "<a href='" . $row[1] . $id . "'>" . $row[0] . "</a>";
                }
                $html .= "</div></td>";
            }
            $html .= "</tr>";
        }

        $this->html_check_box = $html;
    }

    //public function set_form_values($form_values)
    //{
        // set form values found in database - set this value directly w/o using set_values_form (yes, bad choice of names)
        // should be refactored to set_form_values_manually
    //    $this->form_values = $form_values;
    //}

    public function set_form_array($form_array){
        $this->form_array = $form_array;
    }
    public function get_form (){
        return $this->top_form;
    }
    public function set_id_column ($column) {
        $this->id_column=$column;
    }

    public function set_table_name ($table){
        $this->table_name = $table;
    }

    public function get_table (){

        $content  = $this->column_names;
        $content .= $this->top_form;
        $content .= $this->main_table;

        return $content;
    }

    public function get_html_main_table (){
        return $this->main_table;
    }
    public function set_table_column_names ($columns){
        $this->cols = $columns;
    }

    public function set_html_table_column_names ($columns){
        // return html in table of columns received. easy peasy
        // var_dump($columns);
        //TODO: add sorting capabilities: redirect with &sort=column (always by ASC)
        // use $_GET['controller'] and $_GET['action'] & sort=column in link

        $html = '<tr>';
        foreach ($columns as $col_name) {
            //echo $col_name.'<br>';

            $html .= "<th><div class='table_title1'>".$col_name."</div></th>";
        }

        $html .= '</tr>';
        //var_dump($html);
        //die();
        $this->column_names = $html;
    }

    public function get_html_table_column_names (){
        return $this->column_names;
    }

    public function get_html_check_box(){
        return $this->html_check_box;
    }
    public function set_main_table($result){
        // specifics: table, columns, sql conditions (join, order, etc)
        //      private $main_table = array(
        //          columns    => array(
        //              student_id => '',           // if empty, field will not be displayed
        //              nom        => 'Name',
        //              prenom     => 'First Name', etc
        //          table      => 'table_name',
        //          sql        => 'SELECT students.student_id, students.nom FROM students WHERE student_id=?',      // decided to go for full SQL
        //
        // if (!isset($this->details_link) {
        //      replace detail link by a check box in a form;
        //
        if (!isset($this->token)) {
            $token_handler = new security;
            $token_handler->set_token();
            $this->token = $token_handler->get_token();
        }


        $html ='';

        foreach ($result as $row => $fields) {

            $i = 1;

            $html .= "<tr>";

            foreach ($fields as $field) {
                if ($i == 1) {
                    // grabs first column to use as id reference to create the details link below
                    $id = $field;
                    $i = 2;
                } else {
                    // display cell contents
                    $cell_content = $field;
                    $html .= "<td><div class='table_row1'>{$cell_content}</div></td>";
                }
            }



            if (isset($this->details_link)) {
                $html .= "<td><div class='small_link_button'>";
                foreach ($this->details_link as $row) {
                    //var_dump($row);
                    $html .= "<a href='".$row[1] . $id . "&token=".$this->token."'>" . $row[0]."</a>";
                }
                $html .= "</div></td>";
            }

            $html .= "</tr>";
        }

        $this->main_table = $html;

    }

    public function set_details_link ($link) {
        // <a href='?controller=teachers&action=details&id={$id}'>details</a>
        $this->details_link = $link;
    }

    public function set_drop_down ($drop_down){
        // Configure drop-down menus. Order is not important, since it will be structured in $this->top_form order by KEY VALUE
        // receives:
        // $drop_down = array(
        //    'control_name AND column of reference for value'  => array('col_name_to_display' => $sql_result_array),
        //    'program_id'                                      => array('program'             => $program_result),
        //    'sex_id'                                          => array('sex'                 => $sex_result));
        //
        // then builds $drop_down_html array:
        //      $drop_down_html = array('program_id' => "<select name='program_id' form='top_form'>
        //                                                  <option value='1'>FA</option>
        //                                                  <option value='2'>FI</option>
        //                                                  ...
        //                                               </select>",
        //                              'sex_id'     => "<select name='sex_id' form='top_form'>
        //                                                  <option value='1'>Boy</option>
        //                                                  <option value='2'>Girl</option>
        //                                               </select>
        //  function set_top_form iterates through element list (div, input, drop_down)

        //var_dump ($drop_down);
        //var_dump ($this->form_values);
        $drop_down_html = '';

        foreach ($drop_down as $column => $key) {       // program_id, sex_id

            //echo 'COLUMN: '.$column.'<br>';
            // here, starts building html code

            $html = "<select name='{$column}' form='top_form' method=''>";
            foreach ($key as $property => $value) {     // program, sex
                //echo '.....PROPERTY: '.$property.'<br>';

                foreach ($value as $fields => $X){      // 0, 1
                    //echo '..........X: '.$fields.'<br>';
                    foreach ($X as $field => $value) {  //
                        //var_dump ($fields);
                        //echo '...............FIELD: '.$field.'<br>';
                        //echo '...............VALUE: '.$value.'<br>';
                        if ($field == $column) {
                            //echo 'value inside column case column: '.$value.'<br>';
                            // $value is the whatever_id value
                            $selected = '';
                            //var_dump($this->form_values);
                            //echo $value.'<br>'  ;

                            if (isset($this->form_values)) {
                                if ($this->form_values[$field] == $value){
                                    $selected = 'selected';
                                }
                            }
                            $html .= "<option value='{$value}' {$selected}>";
                        } else {
                            $html .= "<div class='drop_down'>{$value}</div></option>";
                        }
                    }
                }
            }

            $html .= "</select>";
            $drop_down_html[$column] = $html;

        }
        //var_dump($drop_down_html);
        //echo $drop_down_html['program_id'];
        //echo $drop_down_html['sex_id'];
        //die();
        $this->drop_down = $drop_down_html;
    }

    public function get_drop_down (){
        return $this->drop_down;
    }

    public function get_html_drop_down($key){
        return $this->drop_down[$key];;
    }

    public function set_top_form($top_form){
        //  form: add record form on top: field names, field labels, drop_down menu name (from previous $drop_down set method)
        //  follows the $main_table columns order
        // Also, set_drop_down method does not 'select' value retrieved from DB.
        //
        $html = "<tr>";
        $html .= "<form action='{$top_form['action']}' method='{$top_form['method']}' id='{$top_form['id']}'>";

        $i = 0;
        $autofocus = '';
        foreach ($top_form['elements'] as $key=>$bla) {
            //echo 'key: '.$key.'<br>';
            foreach ($bla as $key2 => $key3) {
                $i++;
                // $key2: value position inside numbered array
                //echo '.....key2:'.$key2.'<br>';
                //echo '.....key3:'.$key3.'<br>';

                $value='';
                switch ($key2) {
                    case 'text':
                        if ($i==1) $autofocus = 'autofocus';
                        if (isset($this->form_values)) $value = $this->form_values[$key3];
                        $html .= '<td>'."<div ".$top_form['div'].">";
                        $html .= "<input type='text' name='{$key3}' value='{$value}' {$autofocus}>";
                        $html .= "</td></div>";
                        $i++;
                        break;
                    case 'date':
                        if ($i==1) $autofocus = 'autofocus';
                        if (isset($this->form_values)) $value = $this->form_values[$key3];
                        $html .= '<td>'."<div ".$top_form['div'].">";
                        $html .= "<input type='date' name='{$key3}' value='{$value}' {$autofocus}>";
                        $html .= "</td></div>";
                        $i++;
                        break;
                    case 'drop_down':
                        $html .= '<td>'."<div ".$top_form['div'].">";
                        $html .= $this->drop_down[$key3];
                        $html .= "</td></div>";
                        break;
                    case 'submit':
                        $html .= "<td><div ";
                        if (isset($top_form['div_button'])) {
                            $html .= $top_form['div_button'];
                        } else {
                            $html .= $top_form['div'];
                        }

                        $html .= ">";
                        $html .= "<input type='submit' value='{$key3}'>";
                        $html .= "</td></div>";
                        break;
                    case 'check_box':

                        // check_box works just like drop_down; needs a proper function to pre make html
                        $html .= '<td>'."<div ".$top_form['div'].">";
                        $html .= $this->check_box[$key3];
                        $html .= "</td></div>";

                        break;
                    case 'label':
                        $html .= '<td>'."<div ".$top_form['div'].">";
                        $html .= $this->form_values[$key3];
                        $html .= "</td></div>";
                        break;

                    case 'hidden':
                        foreach ($key3 as $hidden_name => $hidden_value) {
                            $html .= "<input type='hidden' name='{$hidden_name}' value='{$hidden_value}'>";
                        }

                        break;

                    default:
                        $html .= '<td></td>';
                        break;
                }
            }
        }
        // TODO: insert token check here
        if (!isset($this->token)) {
            $token_handler = new security;
            $token_handler->set_token();
            $this->token = $token_handler->get_token();
        }


        //echo $_SESSION['token'];
        //die();
        // TODO: troubleshoot: if multiple forms at the same time, token check might fail since each form
        // will have produced its own token, but only one will be checked - make an array of tokens then iterate?

        $html .= "<input type='hidden' name='token' value = '{$this->token}'>";

        $html .= "</form>";

        if (isset($_GET['id'])) $id = $_GET['id'];
        if (isset($top_form['action_links'])) {
            $action_links = $top_form['action_links'];

            $html .= "<td><div class='small_link_button'>";
            foreach ($action_links as $row) {
                //var_dump($row);
                $html .= "<a href='".$row[1] . $id . "&token=".$this->token."'>" . $row[0]."</a>";
            }
            $html .= "</div></td>";

        }

        $html .= "</tr>";

        $this->top_form = $html;


    }

    public function add(){
        // general add function
        // needs:
        //      1. columns name - FORM input names are identical to the table column names
        //          ** Use class property $this->cols
        //      2. table name - $this->table_name settled through set_table_name method
        //
        // TODO: Check obligatory fields, returns error or OK message
        // obligatory fields uses $this->obligatory_fields, which contains array ('table_col_name'=>'format')
        // formats: '##AAA###' : number, number, text, text,text, number, number, number
        // if no table_col_name has been found in array, it is considered not obligatory
        //

        $security_handler = new security();
        $security_handler->check_token();

        $i      = 0;
        $cols   = '';
        //$values = array();
        $field_error = array();

        foreach ($this->cols as $column) {
            //TODO: priority high. when inserting single record through form, execute fails
            // restructure array: instead of array = (0 => 'bla', 1 => 'ble', 2 => 'etc') make
            //  array = (0 => 'bla', 'ble', 'etc');

            //array_push ($values, trim($_POST[$column]));
            $values[0][] = trim($_POST[$column]);

            $cols .= $column.',';
            $i++;

            //TODO: input format test - uses preset obligatory_fields property (array) for each column name
            if (isset($this->obligatory_fields)) {
                if ($column = $this->obligatory_fields[$column]) {
                    if (empty($_POST[$column])) {
                        $error = 'Obligatory field empty';
                    } else {
                        // TEST field format
                        // if required format is empty, just skip this step (accepts whatever user wants)
                        if (!empty($this->obligatory_fields[$column])) {
                            // format is not empty, need to test field format
                            // make for loop based on strlen($_POST[$column])
                            for ($i = 0, $j = strlen($_POST[$column]); $i < $j; $i++) {
                                // TODO: test each $_POST[$column] value if is present (if obligatory) and its format.
                                // if not, add the corresponding neat table name to an array, then display this array with the errors found
                                // obligatory / wrong format

                                // to check if a character is inside a string:
                                //      strstr($pattern, $_POST[$column][$i])) {

                                // if input does not agree with pattern, issue an error message

                            }
                        }
                    }
                    array_push($field_error, $error);
                }
            }
        }

        //var_dump($values);
        //echo "<br>";
        //print_r ($values);
        //die();
        $interrogation_mark  = str_repeat('?, ', ($i-1));
        $interrogation_mark .= '?';

        $cols   = substr($cols, 0, -1);

        $sql = "INSERT INTO ".$this->table_name." (".$cols.") VALUES (".$interrogation_mark.")";
        
        $connection = new database();

        if ($connection->insert($sql, $values)) {
            $_SESSION['log'] .= new timestamp("Affected Rows: ".$connection->get_row_num());
        } else {
            $_SESSION['log'] .= new timestamp("Could not add values!");
        }

        //header("Location: http://localhost/css/index.php?controller={$_GET['controller']}&action=index");

    }

    public function delete(){
        // for admin only
        // needs:
        //      1. record id (WHERE condition) in $_GET['id']
        //      2. id column name (in $this->id_column, through set_id_column method
        //      3. table (in $this->table_name, through set_table_name method

        $security_handler = new security();

        $security_handler->check_token();

        $_SESSION['log'] .= new timestamp("Deleting record {$_GET['id']} on table {$this->table_name}");
        if (isset($_GET['id'])) {
            if (is_numeric($_GET['id'])) {
                if (isset($this->id_column)){
                    $id = $_GET['id'];
                    $sql = "DELETE FROM {$this->table_name} WHERE {$this->id_column}=?";

                    $data = array($id);

                    $connection = new database();

                    if ($connection->delete($sql, $data)) {
                        $_SESSION['log'] .= new timestamp("Record deleted");
                    } else {
                        $_SESSION['log'] .= new timestamp("Error: record could not be deleted!!");
                    }
                    //header("Location: http://localhost/css/index.php?controller={$_GET['controller']}&action=index");
                    } else {
                        $_SESSION['log'] .= new timestamp("Error: id_column property not set when deleting record. Cannot delete!");
                    }

            } else {
                $_SESSION['log'] .= new timestamp("Error: id value is not numeric. Aborting operation!");
                header("Location: http://".WEBSITE_URL."/index.php?controller={$_GET['controller']}&action=index");
            }
        } else {
            $_SESSION['log'] .= new timestamp("Error: id is not set. Aborting operation!");
            header("Location: http://".WEBSITE_URL."/index.php?controller={$_GET['controller']}&action=index");
        }
    }

    /**
     * @return mixed
     */
    public function set_values_form()
    {
        // BUG in function: does not work when pre selecting values from joined tables
        // WORKAROUND: set manually the $result value with method set_values_form_manually ($result)
        if (isset($_GET['id'])) {
            if (is_numeric($_GET['id'])) {
                $id = $_GET['id'];

                $data = array($id);                     // for PDO prepared statement, even if it's a single value, needs to be an array

                $cols_str = $this->cols[0];              // removes the [0] from $this->cols = array(0 => 'nom, prenom, nom_khmer etc');

                $sql = 'SELECT ' . $cols_str . ' FROM ' . $this->table_name . ' WHERE ' . $this->id_column . '=?';

                $connection = new database();
                $result = $connection->fetchAll($sql, $data);

                if ($connection->get_row_num() == 1) {
                    $this->form_values = $result[0];    // this removes the [0] from $result array and sets the property form_values
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function set_values_form_manually ($data)
    {
        // ugly workaround to preset values for details/edit forms: set the $results from fetchAll manually
        // format is: array('name' = 'value');
        $this->form_values = $data;
    }
    public function details(){
        // needs:
        //      1. id from $_GET['id']
        //      2. columns names from $this->cols       (set_table_column_names)
        //      3. table name    from $this->table_name (set_table_name method)
        //      4. id_column     from $this->id_column  (set_id_column method)
        //
        // do:
        //      1. retrieves all info based on $this->cols array
        //      2. creates an array ('column_name'=>'retrieved value');

        $security_handler = new security();

        $security_handler->check_token();

        self::set_top_form($this->form_array);

        $content = $this->column_names;

        $content .= self::get_form();

        //$output['content'] = '<table>'.$content.'</table>';

        return $content;

    }
    public function set_details_form ($form){
        // simply set details form structure; NOTE: this is distinct from set_top_form (and its property
        $this->form_values = $form;
    }

    public function update (){
        /* UPDATE multiple tables:
        //  UPDATE tables SET table1.col1=table2.col2
        //  WHERE condition;

        // needs:
        //      1. column names - $this->cols
        //      2. table name   - $this->table_name
        //      3. id           - $_GET['id'] from action form
        example:
        $sql = " UPDATE {$this->table_name} SET
                nom=?, prenom=?, nom_khmer=?, prenom_khmer=?, sex_id=?, matricule=?, dob=?, program_id=?
                WHERE student_id=?";
        /********************************************/

        $security_handler = new security();
        $security_handler->check_token();

        $id = $_GET['id'];
        $i      = 0;
        $cols   = '';
        $values = array();

        foreach ($this->cols as $column) {
            array_push ($values, $_POST[$column]);
            $cols .= $column.'=?,';
            $i++;
        }

        array_push ($values, $id);      // add last value to the array, which corresponds to the record id number.

        $cols   = substr($cols, 0, -1);

        $sql = "UPDATE ".$this->table_name." SET ".$cols." WHERE ".$this->id_column."=?";

        //echo "<p>sql: ".$sql."<br>";
        //echo "<p>values: ";
        //var_dump ($values);


        $connection = new database();

        if ($connection->update($sql, $values)) {
            $_SESSION['log'] .= new timestamp("Affected rows: ".$connection->get_row_num());

        } else {
            $_SESSION['log'] .= new timestamp("Record was not updated in {$_GET['controller']}!");

        }

        //die();
    }
}