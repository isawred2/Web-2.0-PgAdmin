<?
/****************************************************************
*
*  This is security.php file, that needs to be included into
*  all pages written for the system
* 
*  -- PREPROCESS VARIABLES
*   $outsite 			= false; -- if true, it will not forward to the login page
*   $output  			= true;  -- if false, will not output any HTML 
*   $outputCloseHead 	= true -- if false, the header tag will not be closed
*   $title   			= '';    -- tilte of the page for the browser
*   $initSecurity 		= true   -- if false, then security will not be initializaed
*   &initSession 		= true   -- if false, then sesssion will not be autostarted
*
*****************************************************************/

import_request_variables('GP');

// session global variables 
$sys_db 		  = null;
$ses_update 	  = false;
$ses_data   	  = '';
$ses_userid 	  = '';

// system variables
$sys_stats  	  = null;
$sys_folder 	  = str_replace("/security.php", "", str_replace("\\","/",__FILE__));
$sys_path  	 	  = substr($sys_folder, strlen($_SERVER["DOCUMENT_ROOT"]));

require_once($sys_folder."/config/cnf_system.php");
require_once($sys_folder."/libs/phpDB.php");

class phpSecurity {
	// public properties
	public $sys_db;
	public $time_start;
	public $time_end;
	public $time_total;

	function __construct() {
		global $db, $sys_db, $sys_folder, $sys_path, $sys_stats, $def_dbSession;
		global $sys_dbType, $sys_dbIP, $sys_dbLogin, $sys_dbPass, $sys_dbName, $sys_dbPrefix;
		
	    // page start time
	    list($usec, $sec) = explode(" ", microtime());
	    $this->time_start = (float)$usec + (float)$sec;
	    	    
		// system database
		if ($def_dbSession == true) {
			$sys_db = new phpDBConnection($sys_dbType);
			$sys_db->connect($sys_dbIP, $sys_dbLogin, $sys_dbPass, $sys_dbName);
			$this->sys_db = $sys_db;
			$db = $sys_db;
			$sys_stats = new phpStatistics($sys_db);
		}
		require_once($sys_folder."/session.php");
	}
	
	function __destruct() {		
		global $sys_stats, $ses_update;
	    // time variable for page processing
        list($usec, $sec) = explode(" ", microtime());
        $this->time_end   = (float)$usec + (float)$sec;
        $this->time_total = round(((float)$this->time_end - (float)$this->time_start) * 1000)/1000;
		// save web statistics
		if ($sys_stats) $sys_stats->saveHit($this->time_total);
		// close the session	
		session_write_close();
	}

	function start() {
		global $title, $def_defaultCSS, $def_encoding;
		global $sys_path, $sys_folder;
		global $output, $outside;
		global $sys_home;
		if ($sys_home != '') $_SESSION['sys_home'] = $sys_home;
		if ($outside !== true && $_SESSION['ses_userid'] == "") {
			print("top.location = '$sys_path/login.php?r=".$_SESSION['sys_home']."';");
			print("<script> top.location = '$sys_path/login.php?r=".$_SESSION['sys_home']."'; </script>");
			die();
		}
		// start output if needed
		if ($output !== 'no' && $output !== false) {
			print("<html>\n");
			print("<head>\n");
		    print("   <title>$title</title>\n");
			print("   <link rel=\"stylesheet\" href=\"$sys_path/css/$def_defaultCSS\" type=\"text/css\" />\n");
			print("   <meta http-equiv=\"Content-Type\" content=\"$def_encoding\" />\n");
			if ($outputCloseHeader !== false) print("</head>\n");
		}
	}
}

/****************************************************************
*
*  This file should be called when session starts
*
*****************************************************************/

class phpStatistics {
	var $db;
    var $browser     	= false;
    var $browserName 	= "-unknown-";

    function phpStatistics($sysdb) {
    	global $sys_dbPrefix;
		$this->db = $sysdb;
		$this->getBrowserName();
	}

	function saveHit($PProcessTime) {
    	global $sys_dbPrefix;
		global $ses_userid;
		
		if ($PProcessTime < 0) $PProcessTime = "null";
		$userid    = ($ses_userid != null ? $ses_userid : 'null');
		// if time is over  seconds, record it inslow
		if ($PProcessTime > 3) {
			$sql = "INSERT INTO ".$sys_dbPrefix."log_slow(domain, url, render, userid)
					VALUES('".$_SERVER["HTTP_HOST"]."', '".$_SERVER["REQUEST_URI"]."', $PProcessTime, $userid);";
			$this->db->execute($sql);	
		}
	}
	
	function getBrowserName() {
	    $agent = strtoupper(trim($_SERVER["HTTP_USER_AGENT"]));
		$found = false;
		$browserName = $this->browserName;
	    if (!$found && strpos("-".$agent, " MSIE") > 0) 	{ $found = true; $browserName = 'IE'; }
	    if (!$found && strpos("-".$agent, "FIREFOX") > 0)   { $found = true; $browserName = 'Firefox'; }
	    if (!$found && strpos("-".$agent, "OPERA") > 0)     { $found = true; $browserName = 'Opera'; }
	    if (!$found && strpos("-".$agent, "CHROME") > 0)    { $found = true; $browserName = 'Chrome'; }
	    if (!$found && strpos("-".$agent, "SAFARI") > 0)    { $found = true; $browserName = 'Safari'; }
	    if (!$found && strpos("-".$agent, "NETSCAPE") > 0)  { $found = true; $browserName = 'Netscape'; }
	    if (!$found && strpos("-".$agent, "KONQUEROR") > 0) { $found = true; $browserName = 'Konqueror'; }
	    if (!$found && strpos("-".$agent, "GECKO") > 0)     { $found = true; $browserName = 'Gecko'; }	
		if ($found) $this->browser = true;
		$this->browserName = $browserName;
		return $browserName;
	}
}

// --- start security

$security = new phpSecurity();
if ($initSecurity !== false) $security->start();

?>