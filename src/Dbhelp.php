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

      //debug
      if (true){
        echo "<h1>mysql connection error</h1>";
        print_r([$host, $username, $password, $db, $port]);
      }

      die("<pre>Connection failed: " . $this->con->connect_error . "</pre>");

    }

  } //end construct

  function findall($table = null, $select = '*', $where = null, $order = null, $limit = null){
    if ( empty($table) )
      die("Error, no table supplied");

    $query = "SELECT $select FROM `$table`";

    if (!empty($where)){
      $query = $query . " WHERE $where";
    }

    if (!empty($order)){
      $query = $query . " ORDER BY $order";
    }

    if (!empty($limit)){
      $query = $query . " LIMIT $limit";
    }

    //debug
    //print_r($query); die();

    $result = $this->con->query($query);
    while($fetched = $result->fetch_array(MYSQLI_ASSOC)) {
      $rows[] = $fetched;
    }

    return $rows;
  } //end get

  function findone($table = null, $col = null, $match = null){

    if ( empty($table) )
      die("Error, no table supplied");

    if ( empty($col) )
      die("Error, no column supplied");

    if ( empty($match) )
      die("Error, no match supplied");

    $arrwithone = $this->findall($table, '*', "`$col` LIKE '$match'", null, "1");
    $returnone = $arrwithone[0]; //just one array

    return $returnone;

  }

  function close() {
    $this->con->close();
  } //end close


  /**
  * array is what to update
  * id is col name to update
  **/
  public function update($table, $array, $id) {

    if (empty($table)){
      die("no table supplied");
    }

    $where = "$id = $array[$id]";

    $set = '';

    //array to string
    foreach ($array as $key => $value) {
        $set .= "`$key` = '$value',";
    }

    //remove final comma
    $set = rtrim($set, ',');

    $sql = <<<EOSQL
      UPDATE `$table`
      SET $set
      WHERE $where;
    EOSQL;

    if ($this->con->query($sql) === TRUE) {
      return $this->con->insert_id;
    } else {
      die("Error: " . $sql . "<br>" . $this->con->error);
    } //end if

  } // end function update

  public function create($table, $array, $insert = "INSERT INTO") {

    if (empty($table)){
      die("no table supplied");
    }

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

  if ($this->con->query($sql) === TRUE) {
    return $this->con->insert_id;
  } else {
    die("Error: " . $sql . "<br>" . $this->con->error);
  }

  } //end create

} //end class
