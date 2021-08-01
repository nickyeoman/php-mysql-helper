<?php
namespace Nickyeoman\Ddbhelper;

/**
* MySQL helper
* v2.0.0
* Last Updated: Jul 31, 2021
* URL: https://www.nickyeoman.com/blog/php/php-mysql-insert-from-array/
*
* Changelog:
* v2 now in composer
**/

class Dbhelp {

  public $con = null;

  function __construct($host = 'localhost', $username = 'root', $password = null, $db = null, $port = null) {
    echo "constuct started";
    $this->con = new mysqli($host, $username, $password, $db);
    echo "connection created";
    if ($this->con->connect_error) {
      die("Connection failed: " . $conn->connect_error);
    } else {
      echo "Connected successfully";
    }
    echo "done construct";
  }

  function testquery() {
    $result = mysqli_query($this->mysqli, "SELECT 'A world full of ' AS _msg FROM DUAL");
    print_r($result); die();
  }

  public function create($table, $array, $insert = "INSERT INTO") {

    //Check if user wants to insert or update
    if ($insert != "UPDATE") {
      $insert = "INSERT INTO";
    }

    $columns = array();
    $data = array();

    foreach ( $array as $key => $value) {
      $columns[] = $key;
      if ($value != "") {
        $data[] = "'" . $value . "'";
      } else {
        $data[] = "NULL";
      }

      //TODO: ensure no commas are in the values
    }

    $cols = implode(",",$columns);
    $values = implode(",",$data);

  $sql = &lt;&lt;&lt;EOSQL
    $insert `$table`
    ($cols)
    VALUES
    ($values)
  EOSQL;
        return $sql;

  } //end create

} //end class
