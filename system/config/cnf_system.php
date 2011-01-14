<?
/****************************************************************
*
*       This is main configuration file for the system
*
*****************************************************************/

// -- general defaults
$def_serverName = "boomba";
$def_defaultCSS = "simple-blue.css?ver=12";
$def_encoding   = "text/html; charset=utf-8";
$def_timeToLive = 1201;
$def_dbSession  = false;

// -- hits and logins are rotated veryt 100k records
$int_slow		= '2 weeks'; // ho long to keep slow pages log
$int_events    	= '2 weeks'; // how long to keep events log
$int_load		= '2 weeks'; // how long to keep load log


?>