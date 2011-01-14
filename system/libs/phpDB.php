<?
$sys_folder = str_replace("/libs/phpDB.php", "", str_replace("\\","/",__FILE__));
require_once($sys_folder."/config/cnf_system.php");

// --------------------------------------------------
// --- DB Class for postgres and mysql

class phpDBConnection {
	var $dbConn	= null;
	var $debug 	= false;
	var $dbtype;
	var $dbVersion;
	var $res_data;
	var $res_errMsg;
	var $res_affectedRows;
	var $res_rowCount;
	var $res_fieldCount;
	var $res_fields;
	
	function __construct($dbtype) {
		$dbtype = strtolower($dbtype);
		if ($dbtype != 'postgres' && $dbtype != 'mysql') {
			die('Only two database types are supported, postgres and mysql');
		}
		$this->dbtype = $dbtype;
	}
	
	// connect to the db
	function connect($dbIP, $dbUser, $dbPass, $dbName, $dbPort=null) {
		// check parameters
		if ($dbIP   == '') die('no database host provided');
		if ($dbName == '') die('no database name provided');
		if ($dbUser == '') die('no database user provided');
		if ($dbPass == '') die('no database password provided');
		
		// connect
		if ($this->dbtype == 'postgres') {
			$this->dbConn = pg_connect("host=$dbIP ".($dbPort != null ? "port=$dbPort " : "")."dbname=$dbName user=$dbUser password=$dbPass");
			if (!$this->dbConn) {
				$this->dbConn = null;
				return false;
			}
			$this->dbVersion = pg_version($this->dbConn);
		}
		if ($this->dbtype == 'mysql') {
		}
	}	

	// clean up
	function __destruct() {
		if ($this->dbConn == null) return;
		if ($this->dbtype == 'postgres') { 
			@pg_close($this->dbConn);
		}
	}
	
	function execute($sql) {
		// hide errors
		$ini_err = ini_get('display_errors');
		ini_set('display_errors', 0);
		// process sql
		$res = false;
		if ($this->dbtype == 'postgres') {
			$this->res_data = pg_query($this->dbConn, $sql);
			if (!$this->res_data) {
				$this->res_errMsg 		= pg_last_error($this->dbConn);
			} else {
				$this->res_errMsg 		= pg_result_error($this->res_data);
				$this->res_affectedRows = pg_affected_rows($this->res_data);
				$this->res_rowCount		= pg_num_rows($this->res_data);
				$this->res_fieldCount	= pg_num_fields($this->res_data);
				$res = new phpRecordSet($this->dbtype, $this->res_data, $this->res_rowCount, $this->res_fieldCount);
				// -- parse field names
				if ($this->dbtype == 'postgres') {
					for ($i=0; $i<$this->res_fieldCount; $i++) {
						$this->res_fields[$i] = pg_field_name($this->res_data, $i);
					}
				}
			}
			if ($this->debug == true) {
				print("<pre>".$sql."<hr>");
				if ($this->res_errMsg != '') print("<span style='color: red'>".$this->res_errMsg."</span><hr>");
				print("</pre>");
			}
		}
		// restore errors
		ini_set('display_errors', $ini_err);
		
		return $res;
	}
}
class phpRecordSet {
	var $dbtype;
	var $data;
	var $rowCount;
	var $fieldCount;
	var $EOF;
	var $fields;
	var $current;
	
	function __construct($dbtype, $res, $rowCount, $fieldCount) {
		$this->dbtype 		= $dbtype;
		$this->data 		= $res;
		$this->rowCount		= $rowCount;
		$this->fieldCount	= $fieldCount;
		if ($rowCount == 0) {
			$this->EOF = true; 
		} else {
			$this->EOF = false;		
			$this->moveFirst();
		}
	}
	
	function __destruct() {
		if ($this->dbtype == 'postgres') pg_free_result($this->data);
	}
	
	function moveFirst() {
		if ($this->dbtype == 'postgres') {
			if ($this->EOF) return;
			$this->current = 0;
			$this->fields = pg_fetch_array($this->data, 0);
		}
	}
	
	function moveLast() {
		if ($this->dbtype == 'postgres') {
			$this->current = $this->rowCount -1;
			$this->fields = pg_fetch_array($this->data, $this->current);
		}
	}
	
	function moveNext() {
		if ($this->dbtype == 'postgres') {
			if ($this->EOF) return;
			$this->current++;
			if ($this->current >= $this->rowCount) { $this->EOF = true; $this->fields = Array(); return; }
			$this->fields = pg_fetch_array($this->data, $this->current);
		}
	}
	
	function movePrevious() {
		if ($this->dbtype == 'postgres') {
			if ($this->current == 0) { return; }
			$this->current--;
			$this->fields = pg_fetch_array($this->data, $this->current);
		}
	}
}
?>