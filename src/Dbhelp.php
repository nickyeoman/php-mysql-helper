<?php
namespace Nickyeoman\Dbhelper;

/**
* MySQL helper
* v2.3.0
* URL: https://github.com/nickyeoman/php-mysql-helper
**/

class Dbhelp {

  public $con = null; // connection
  public $debug = false; // Show debugging info?
  public $sql = array(); //Last Run Query

  function __construct($host = 'localhost', $username = 'root', $password = null, $db = null, $port = '3306', $debug = false) {

    // Check for dotenv variables and use those
    if ( !empty($_ENV['DBHOST']) )
      $host = $_ENV['DBHOST'];
    
    if ( !empty($_ENV['DBUSER']) )
      $username = $_ENV['DBUSER'];

    if ( !empty($_ENV['DBPASSWORD']) )
      $password = $_ENV['DBPASSWORD'];

    if ( !empty($_ENV['DB']) )
      $db = $_ENV['DB'];
      
    if ( !empty($_ENV['DBPORT']) )
      $port = $_ENV['DBPORT'];

    // Create connection
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
      die("Error, nickyeoman:php-mysql-helper - no match (third param) supplied");

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

        if ( is_null($value) ){
          $set .= "`$key` = NULL,";
        } else {
          $set .= "`$key` = '$cleanValue',";
        }

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
    
    if ( !array_key_exists( 0,$array ) )
      $array[0] = null;

    if ( is_array($array[0])) {
      
      foreach($array as $row){
        $newarr = array();
        foreach($row as $key => $value) {
          if(!in_array("`" . $key . "`", $columns)){
            $columns[] = "`" . $key . "`";
          }

          $newarr[] = $value;
        }  
        
        $tostring = implode(",",$newarr);
        $values[] = "($tostring)";

        
      }
      $cols = implode(",",$columns);
      $values = implode(",",$values);
      $sql = <<<EOSQL
      $insert `$table`
      ($cols)
      VALUES
      $values
EOSQL;

    } else {
      
      unset($array[0]);
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
    } // end if array

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

 public function query($query = '') {

  if (empty($query)) {
    return false;
  } else {
    $result = $this->con->query($query);
    if ($result) {
      if (is_object($result)) {
        while ($fetched = $result->fetch_assoc()) {
          $rows[] = $fetched;
        }
        if (!empty($rows)) {
          return $rows;
        }
      } else {
        return $result;
      }
    } else {
      return false;
    }
  }

} // end query

// Checks if a table exists
public function tableExists($table = '') {

  if (empty($table))
    return false;

  $sql = 'SELECT count(TABLE_NAME) as `value` FROM TABLES WHERE TABLE_NAME = "' . $table . '"';

  $tabletest = new \mysqli($_ENV['DBHOST'], $_ENV['DBUSER'], $_ENV['DBPASSWORD'], 'information_schema', $_ENV['DBPORT']);
  
  $result = $tabletest->query($sql); 
  
  $arr = $result->fetch_array(MYSQLI_ASSOC);
  $tabletest->close();

  if ( $arr['value'] )
    return true;
  else
    return false;

}

/**
 * update or insert tags
 *
 * $table is the table you are tagging from
 * $tableid is the id of the row from above table
 * $relationTable = tag_tablename
 * $tagTable = tags table
 * $tags = array containing the tags to tag the tableid with
 * 
 * Notes: 
 * relation table must be named tag_$table
 * relation table column must be named $table_id
 */
public function modifyTags($table = null, $rowid = null, $tags = array() ) {
  
  // Check parameters were given
  if ( empty($table) || empty($rowid) )
    return false;

  $cleantags = array();
  // Clean the tag
  foreach ($tags as $value) {
    $cleantags[] = strtolower(trim($value));
  }
  
  
  // remove existing relations
  $sql = "DELETE FROM tag_{$table} WHERE `tag_{$table}`.`{$table}_id` = $rowid";
  $this->query($sql);

  if ( count($cleantags) < 1 )
    return true;

  // Make sure tags exist
  $sql = "INSERT IGNORE INTO `tags` (`title`) VALUES ";
  $i=0;
  foreach($cleantags as $tag) {
    if ($i < 1 ) 
      $sql .= "('$tag')";
    else
      $sql .= ", ('$tag')";

   $i++;
  }
  
  
  $this->query($sql);

  // Get Tag ids
  $sql = "SELECT title, id FROM `tags` WHERE ";
  for ($i=0; $i < count($cleantags); $i++) {
    if ($i == 0) {
      $sql .= "`title` = '$cleantags[$i]'";
    } else {
      $sql .= " OR `title` = '$cleantags[$i]'";
    } 
  }
  
  $result = $this->query($sql);
  
  $insertArray = array();
  foreach ($result as $row) {
    $insertArray[] = array(
      'id' => 'NULL',
      "{$table}_id" => $rowid,
      'tag_id' => $row['id']
    );
  }
  
  $this->create("tag_{$table}", $insertArray, 'INSERT INTO');

  return true;

}

/**
 * Gets the tags, returns an array
 */
public function getTags($table = null, $id = null) {

  $rtn = array();

  $query = "SELECT t.title FROM tags t JOIN tag_{$table} tp ON t.id = tp.tag_id WHERE tp.{$table}_id = '$id'";
  $result = $this->con->query($query);

  if ( !empty($result) ) {

      foreach($rows as $v) {
        $rtn[] = $v['title'];
      }

  }
  
  return ($rtn);
}

/**
 * Migration helper
 *
 */
public function migrate($table , $sch = array() ) {

  echo "<h1>Migrate table: $table</h1><ul>";

  // make sure table exists
  $query = "CREATE TABLE IF NOT EXISTS `$table` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    PRIMARY KEY (`id`)
  ) AUTO_INCREMENT=1;";

  if ($this->con->query($query) === TRUE) {
    echo "<li>Table created: $table</li>";
  } else {
    die("Error: Create table didn't work: " . $query . "<br>" . $this->con->error);
  }

  // make sure columns are there
  foreach ( $sch as $col) {
    //dump($col);

    $column = $col['name'];
    $coltype = $col['type'];
    $coltype = ( !empty($col['size'])) ? "${coltype}(${col['size']})" : "$coltype";
    $coldefault = ( !empty($col['default'])) ? "DEFAULT ${col['default']}" : NULL;
    $colnull = ( $col['null'] == "No")  ? "NOT NULL" : NULL;
    $colcomment = ( !empty($col['comment'])) ? "COMMENT '${col['comment']}'" : NULL;

    // drop column
    if ($col['type'] == 'drop' ) {

      $query = "ALTER TABLE `$table` DROP COLUMN IF EXISTS `$column`;";

      if ($this->con->query($query) === TRUE) {
        echo "<li>Dropped $column</li>";
      } else {
        die("Error: Create table didn't work: " . $query . "<br>" . $this->con->error);
      }

      break;

    }

    $query = "ALTER TABLE `$table` CHANGE IF EXISTS `$column` $column $coltype $colnull $coldefault $colcomment;";
    if ($this->con->query($query) === TRUE) {
      echo "<li>Changed column $column</li>";
    } else {
      die("Error: Create table didn't work: " . $query . "<br>" . $this->con->error);
    }

    $query = "ALTER TABLE `$table` ADD COLUMN IF NOT EXISTS `$column` $coltype $colnull $coldefault $colcomment;";
    if ($this->con->query($query) === TRUE) {
      echo "<li>Added column $column</li>";
    } else {
      die("Error: Create table didn't work: " . $query . "<br>" . $this->con->error);
    }

    // If Unique
    if ( !empty($col['unique']) ){
      if ( $col['unique'] == "Yes" ) {
        $query = "ALTER TABLE `$table` ADD UNIQUE(`${col['name']}`);";
        if ($this->con->query($query) === TRUE) {
          echo "<li>Made unique $column</li>";
        } else {
          die("Error: Create table didn't work: " . $query . "<br>" . $this->con->error);
        }
      }
    }

  }
  echo "</ul>";

}


}
//end class
