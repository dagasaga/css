<?php
class htmloutput {
    /**
     * @param $data - mysqli result
     * @param $title - true / false (show or not the title)

     * @return string
     */
    public function query_output($data)
    {



        if (!$data) {
            $table_output = 'No data to display, quitting.<br>';
            return 'error in htmloutput.php library class: query argument required';

        }


        //$table_output = 'Data was passed to query_output method.<br>';


        // $table_output = $table_output.'------------------------------------------------------------------------------------------------------------------------------------<br>';
        // display columns field names
        $i = 0;

        $table_output = '<html><body><table><tr>';

        while ($i < mysqli_num_fields($data)) {

            $meta = mysqli_fetch_field($data);

            if ($title == 'true') $table_output = $table_output.'<td>' . $meta->name . '</td>';

            $i = $i + 1;
        }
        $table_output = $table_output.'</tr>';



        // SHOULD Display only the selected range, thus making 'pages'.
        //$i     = $range['first_row'];
        //$count = $range['last_row'];

        // echo "from htmloutput.php: from ".$range['first_row']." to ".$range['last_row']." rows.<br>";
        //mysqli_data_seek($data, $range['first_row']);

        //if ($range['last_row'] !== -1 ) {
        //    $last_row = $range['last_row'];
        //} else {
        //    $last_row = mysqli_num_rows ($data);
        //}


        //$r = $range['first_row'];

        $r = 0;

        $last_row = mysqli_num_rows($data);

        while ($r < $last_row) {
            $row = mysqli_fetch_row($data);
            $table_output .= '<tr>';
            $count = count($row);

            $y = 0; //columns

            while ($y < $count) {
                $c_row = current($row);
                $table_output .= '<td>' . $c_row . '</td>';
                next($row);
                $y = $y + 1;    // walk the columns one by one
            }
            $table_output .= '</tr>';
            $r = $r + 1;

        }
        echo '</table></body></html>';

        return $table_output;


    }
}