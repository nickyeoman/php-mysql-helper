<?php
namespace Nickyeoman\Dbhelper;

/**
* MySQL helper
* v2.1.3
* URL: https://github.com/nickyeoman/php-mysql-helper
**/

class Dbhelp {

  public $con = null; // connection
  public $debug = false; // Show debugging info?
  public $sql = array(); //Last Run Query

  function __construct($host = 'localhost', $username = 'root', $password = null, $db = null, $port = '3306', $debug = false) {

    $this->con = new \mysqli($host, $username, $password, $db, $port);

    if ( $this->con->connect_error && $this->debug ) {

      echo "<h1>mysql connection error</h1>";
      print_r([$host, $username, $password, $db, $port]);
      die("<pre>Connection failed: " . $this->con->connect_error . "</pre>");

    } elseif ( $this->con->connect_error && !$this->debug ) {

      die("<pre>Connection failed: " . $this->con->connect_error . "</pre>");

    }
  }
  //end construct function

  // Return the number of rows of a table based on a where
  public function count($table = null, $where = null) {

    if ( empty($table) )
      die("Error, no table supplied");

    $query = "SELECT COUNT(*) AS count FROM `$table`";

    if (!empty($where)){
      $query = $query . " WHERE $where";
    }

    // debugging
    $this->sql[] = $query;

    $result = $this->con->query($query);

    if ( !empty($result) ) {

        $arr = $result->fetch_array(MYSQLI_ASSOC);
        $return = $arr['count'];

    } else {

      $return = 0;

    }

    return $return;

  }
  // end count function

  public function findall($table = null, $select = '*', $where = null, $order = null, $limit = null){

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

    // debugging
    $this->sql[] = $query;

    // Execute query
    $result = $this->con->query($query);

    if ( !empty($result) ) {

      while( $fetched = $result->fetch_array(MYSQLI_ASSOC) ) {
        $rows[] = $fetched;
      }
      //end while

      if ( !empty($rows) )
        return $rows;

    } else {

      return null;

    }
    //end if


    if ( empty($rows))
      return null;
    else
      return $rows;

  }
  //end findall

  public function findone($table = null, $col = null, $match = null){

    if ( empty($table) )
      die("Error, no table supplied");

    if ( empty($col) )
      die("Error, no column supplied");

    if ( empty($match) )
      die("Error, no match supplied");

    $arrwithone = $this->findall($table, '*', "`$col` LIKE '$match'", null, "1");
    if ( !empty($arrwithone[0]) )
      return($arrwithone[0]); //just one array
    else
      return(null);

  }
  //end findone

  public function close() {
    $this->con->close();
  }
  //end close


  //id is col name to update
  public function update($table, $array, $id) {

    if (empty($table))
      die("no table supplied");

    $where = "$id = '$array[$id]'";

    $set = '';

    //array to string
    foreach ($array as $key => $value) {

      if ( $key == $id )
        continue;

      if ( $value == 'NOW()' ) {
        $cleanValue = 'NOW()';
        $set .= "`$key` = $cleanValue,";
      } else {

        if ( !empty($value) )
          $cleanValue = mysqli_real_escape_string($this->con, $value);
        else if ( $value == 0 )
          $cleanValue = '0';
        else
          $cleanValue = '';

        $set .= "`$key` = '$cleanValue',";

      }

    }

    //remove final comma
    $set = rtrim($set, ',');

    $sql = <<<EOSQL
      UPDATE `$table`
      SET $set

      WHERE $where
      ;
EOSQL;

    //debug
    //dump($sql);die();

    if ( $this->con->query($sql) === TRUE ) {
      return $this->con->insert_id;
    } else {
      die("Error: " . $sql . "<br>" . $this->con->error);
    } //end if

  }
  // end function update

  public function create($table, $array, $insert = "INSERT INTO") {

    if ( empty($table) )
      die("no table supplied");

    if ( empty($array) )
      die("no insert array supplied");

    //Check if user wants to insert or update
    if ($insert != "UPDATE") {
      $insert = "INSERT INTO";
    }

    $columns  = array();
    $data     = array();

    foreach ( $array as $key => $value) {

      $columns[] = "`" . $key . "`";

      if ( $value == 'NOW()' )
        $data[] = 'NOW()';
      elseif ($value != "")
        $data[] = "'" . $value . "'";
      else
        $data[] = "NULL";

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

  if ($this->con->query($sql) === TRUE)
    return $this->con->insert_id;
  else
    die("Error: " . $sql . "<br>" . $this->con->error);

 }
 //end create

 public function delete($table, $where) {
   if (empty($table))
     die("no table supplied");

   $query = "DELETE FROM `$table` WHERE $where";

   if ($this->con->query($query) === TRUE) {
     return true;
   } else {
     die("Error: " . $query . "<br>" . $this->con->error);
   }
 }

}
//end class
