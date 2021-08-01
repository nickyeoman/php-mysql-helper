<?php
namespace Nickyeoman\Dbhelper;

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

  function __construct($host = 'localhost', $username = 'root', $password = null, $db = null, $port = '3306') {

    $this->con = new \mysqli($host, $username, $password, $db, $port);

    if ($this->con->connect_error) {
      die("<p>Connection failed: </p>" . $this->con->connect_error);
    }

  } //end onstruct

  function get($table = null, $select = '*', $order = null, $limit = null){
    if ( empty($table) )
      die("Error, no table supplied");

    $query = "SELECT $select FROM $table";
    $result = $this->con->query($query);
    while($fetched = $result->fetch_array(MYSQLI_ASSOC)) {
      $rows[] = $fetched;
    }

    return $rows;
  } //end get

  function close() {
    $this->con->close();
  } //end close

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

  $sql = <<<EOSQL
    $insert `$table`
    ($cols)
    VALUES
    ($values)
  EOSQL;
        return $sql;

  } //end create

} //end class
