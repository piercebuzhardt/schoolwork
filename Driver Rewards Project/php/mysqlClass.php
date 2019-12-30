<?php
class mysql_db {
	
  public $db_connect_id;
  public $query_result;
  public $row = array();
  public $rowset = array();

  function __construct($dbhost, $dbuser, $dbpass, $database) {
    $this->server = $dbhost;
    $this->user = $dbuser;
    $this->password = $dbpass;
    $this->dbname = $database;
	
    $this->db_connect_id = new mysqli($this->server,$this->user,$this->password,$this->dbname);


    if($this->db_connect_id->connect_error) {
      die("Connection failed: " . $this->db_connect_id->connect_error);
    }
    else { return $this->db_connect_id; }
  }
  
  // Replaces longer query function
  // Usage: $db->query(argument);
  function query($query) {
    return mysqli_query($this->db_connect_id, $query);
  }
  function connection() {
    return $this->db_connect_id;
  }
}

include_once ("config.php");
$db = new mysql_db($dbhost, $dbuser, $dbpass, $database);
$GLOBALS['db'] = $db;

?>

