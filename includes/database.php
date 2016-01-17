<?php

class database {

    protected $connection;
    public $row_count;

    public function __construct() {

        $dsn = "mysql:host=".SERVER.";dbname=".DB_NAME.";charset=UTF8";

        if (!isset($this->connection)) {
            $this->connection = new PDO($dsn, USER, PASSWORD, array(PDO::ATTR_EMULATE_PREPARES => false, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
        } else {
            $_SESSION['log'] .= new timestamp("reusing old connection...");
        }

        if (!$this->connection) {
            throw new Exception("Couldn't connect to database");
        } else {
            $_SESSION['log'] .= new timestamp("connected with db");
        }

    }

    public function query ($query) {
        $_SESSION['log'] .= new timestamp("Querying {$query}...");
        $stmt = $this->connection->query($query);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->row_count = $stmt->rowCount();
        $_SESSION['log'] .= new timestamp("number of rows returned: {$this->row_count}");
        return $result;
    }

    public function fetchAll ($query, $data) {

        $stmt = $this->connection->prepare($query);

        if ($stmt->execute($data)) {
            // executed ok
            $rows             = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $_SESSION['log'] .= new timestamp("query {$query} ordered by [{$_GET['controller']}/{$_GET['action']}] successfully executed.");

        } else {
            // executed not ok
            $_SESSION['log'] .= new timestamp("ERROR: query {$query} FAILED!");
        }

        $this->row_count = $stmt->rowCount();
        $_SESSION['log'] .= new timestamp("number of rows returned: {$this->row_count}");

        return $rows;
    }

    public function last_Inserted_id (){

        return $this->connection->lastInsertId();
    }

    public function insert ($query, $data) {
        $_SESSION['log'] .= new timestamp("Insert query {$query} will be executed...");
        $stmt = $this->connection->prepare($query);

        // since data is an array, iterate through its rows to execute SQL
        foreach ($data as $row) {
            //if ($stmt->execute($data)) {
            if ($stmt->execute($row)) {
                // executed ok
                $_SESSION['log'] .= new timestamp("INSERT query {$query} successfully executed.");


            } else {
                // executed not ok
                $_SESSION['log'] .= new timestamp("ERROR: query {$query} FAILED!");
            }
            $this->row_count = $stmt->rowCount();
            $_SESSION['log'] .= new timestamp("number of rows affected: {$this->row_count}");
        }
        return $this->row_count;
    }


    public function update ($query, $data) {
        $_SESSION['log'] .= new timestamp("Update query {$query} will be executed...");
        $stmt = $this->connection->prepare($query);

        if ($stmt->execute($data)) {
            // executed ok
            $_SESSION['log'] .= new timestamp("UPDATE query {$query} successfully executed.");

        } else {
            // executed not ok
            $_SESSION['log'] .= new timestamp("ERROR: query {$query} FAILED!");
        }
        $this->row_count = $stmt->rowCount();
        $_SESSION['log'] .= new timestamp("number of rows affected: {$this->row_count}");
        //echo "PDO erroInfo: ";
        //var_dump ($stmt->errorInfo());
        return self::get_row_num();
    }

    public function delete ($query, $data){
        $_SESSION['log'] .= new timestamp("Delete query {$query} will be prepared...");
        //$_SESSION['log'] .= new timestamp("Id received by delete function: {$data}");

        $stmt = $this->connection->prepare($query);

        if (!$stmt) {
            $errors = $stmt->errorInfo();
            $_SESSION['log'] .= new timestamp("errorInfo(): ".$errors[0].", ".$errors[1].", ".$errors[2].", errorCode(): ".$stmt->errorCode());
        }

        if ($stmt->execute($data)) {
            // executed ok
            $_SESSION['log'] .= new timestamp("DELETE query {$query} successfully prepared.");

        } else {
            // executed not ok
            $errors = $stmt->errorInfo();
            $_SESSION['log'] .= new timestamp("Query {$query} FAILED! errorInfo(): ".$errors[0].", ".$errors[1].", ".$errors[2].", errorCode(): ".$stmt->errorCode());
        }
        $this->row_count = $stmt->rowCount();
        $_SESSION['log'] .= new timestamp("number of rows affected: {$this->row_count}");

        return self::get_row_num();
    }

    public function get_row_num (){
        return $this->row_count;
    }
}