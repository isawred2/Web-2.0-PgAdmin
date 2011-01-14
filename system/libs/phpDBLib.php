<?
/***********************************
*
* -- This the db library for jsList adn jsEdit
*
***********************************/

$sys_folder = str_replace("/libs/phpDBLib.php", "", str_replace("\\","/",__FILE__));

// =============----------------------------------
// ---------- GENERAL DB FUNCTIONS

function buildOptions($db, $reqParams, $SQL, $out=true, $not_selected=true) {
	// Build List
	$rs = $db->execute($SQL);
	if (!$rs) {
		$db->debug = true;
		$db->execute($SQL);
		return false;
	}
	if ($not_selected) $select = "<option value=\"\">-- not selected --</option>"; else $select = "";
	while ($rs && !$rs->EOF) {
		$select .= "<option value=\"".$rs->fields[0]."\" ".$rs->fields[2].">".$rs->fields[1]."</option> ";
		$rs->moveNext();
	}
	if ($out) {
		$select = str_replace("'", "\\'", $select);
		$select = str_replace("\n", "\\n", $select);
		$select = str_replace("\r", "", $select);
		print("top.elements['".$reqParams["req_name"]."'].getListDone('".$reqParams["req_field"]."', '$select');\n");
	}
	return $select;
}

function buildRadioList($db, $reqParams, $SQL, $out=true) {
	// Build List
	$rs = $db->execute($SQL);
	if (!$rs) {
		$db->debug = true;
		$db->execute($SQL);
		return false;
	}
	$i = 0;
	$radio = "<table cellpadding=\"0\" cellspacing=\"0\"><tr>";
	while ($rs && !$rs->EOF) {
		$radio .= "<td class=\"rText\" style=\"width: 10px\">".
				  "	<input type=\"radio\" id=\"".$reqParams["req_name"]."_field".$reqParams["req_index"]."_radio$i\" ".
				  "		name=\"".$reqParams["req_field"]."_radio\" value=\"".$rs->fields[0]."\"".
				  "		onclick=\"document.getElementById('".$reqParams["req_name"]."_field".$reqParams["req_index"]."').value = '".$rs->fields[0]."';\" ".$rs->fields[2].">".
				  "</td><td class=\"rText\">".
				  "	<label for=\"".$reqParams["req_name"]."_field".$reqParams["req_index"]."_radio$i\" class=\"rText\" ".$rs->fields[3].">".$rs->fields[1]."</label>".
				  "</td><td style=\"paddin-left: 5px; padding-right: 5px\"></td>";
		$rs->moveNext(); $i++;
	}
	$radio .= "</tr></table>";
	if ($out) {
		$radio = str_replace("'", "\\'", $radio);
		$radio = str_replace("\n", "\\n", $radio);
		$radio = str_replace("\r", "", $radio);
		print("top.elements['".$reqParams["req_name"]."'].getListDone('".$reqParams["req_field"]."', '$radio');\n");
	}
	return $radio;
}

function buildCheckList($db, $reqParams, $SQL, $out=true) {
	// Build List
	$rs = $db->execute($SQL);
	if (!$rs) {
		$db->debug = true;
		$db->execute($SQL);
		return false;
	}
	$i = 0;
	$check = "<table cellpadding=\"0\" cellspacing=\"0\"><tr>";
	while ($rs && !$rs->EOF) {
		$check .= "<td class=\"rText\" style=\"width: 10px\">".
				  "	<input type=\"checkbox\" id=\"".$reqParams["req_name"]."_field".$reqParams["req_index"]."_radio$i\" ".
				  "		name=\"".$reqParams["req_field"]."_radio\" value=\"".$rs->fields[0]."\"".
				  "		onclick=\"document.getElementById('".$reqParams["req_name"]."_field".$reqParams["req_index"]."').value = '".$rs->fields[0]."';\" ".$rs->fields[2].">".
				  "</td><td class=\"rText\">".
				  "	<label for=\"".$reqParams["req_name"]."_field".$reqParams["req_index"]."_radio$i\" class=\"rText\" ".$rs->fields[3].">".$rs->fields[1]."</label>".
				  "</td><td style=\"paddin-left: 5px; padding-right: 5px\"></td>";
		$rs->moveNext(); $i++;
	}
	$check .= "</tr></table>";
	if ($out) {
		$check = str_replace("'", "\\'", $check);
		$check = str_replace("\n", "\\n", $check);
		$check = str_replace("\r", "", $check);
		print("top.elements['".$reqParams["req_name"]."'].getListDone('".$reqParams["req_field"]."', '$check');\n");
	}
	return $check;
}

// =============------------------------------------
// ---------- LIST CLASS FUNCTIONS

function list_process($db, $reqParams, $SQL, $CQL="", $retArray=false, $colorColumn=null) {
	$retValues = Array();
	// prepare sql
	if ($reqParams['req_search'] == '') {
		$SQL = str_replace("~SEARCH~", "1=1", $SQL);
		$CQL = str_replace("~SEARCH~", "1=1", $CQL);
	} else {
		$searchStatement = "";
		foreach( $reqParams['req_search'] as $column=>$value) {
			$value = trim($value);
			if ($value == '') continue;
			if (strpos('*'.strtoupper($column), "~VALUE") > 0) {
				$tmp = split("::", $value);
				$column = str_ireplace('~AMP~', '&', $column);
				$column = str_ireplace("~VALUE~",  $tmp[0], $column);
				$column = str_ireplace("~VALUE1~", $tmp[0], $column);
				$column = str_ireplace("~VALUE2~", $tmp[1], $column);
				$searchStatement .= " AND (".$column.") ".chr(13).chr(10);
			} else {
				$searchStatement .= " AND ".$column." ILIKE '".str_replace("'","''", $value)."%'".chr(13).chr(10);
			}
		}
		$searchStatement = substr($searchStatement, 5);
		if ($searchStatement == "") $searchStatement = "1=1";
		$SQL = str_replace("~SEARCH~", $searchStatement, $SQL);
		$CQL = str_replace("~SEARCH~", $searchStatement, $CQL);
	}
	if ($CQL == "") $CQL = "SELECT count(*) FROM ($SQL) as listforcount";

	if (is_array($reqParams['req_sort'])) {
		// remove old sorting
		$pos = strripos($SQL, 'ORDER BY');
		if ($pos > 0) $SQL = substr($SQL, 0, $pos);
		// apply new
		$orderStatement = "";
		foreach ($reqParams['req_sort'] as $column=>$value) {
			$orderStatement .= ", $value";
		}
		if (trim(substr($orderStatement, 1)) != '') $orderStatement = " ORDER BY ".substr($orderStatement, 1);
		$SQL = $SQL.$orderStatement;
	}
	// apply limit and offset
	if ($reqParams['req_limit'] > '') {
		$SQL = $SQL." LIMIT ".$reqParams['req_limit'];
	}
	if ($reqParams['req_offset'] > '') {
		$SQL = $SQL." OFFSET ".$reqParams['req_offset'];
	}

	// build list
	$rs = $db->execute($SQL);
	if (!$rs) {
		$db->debug = true;
		$db->execute($SQL);
		return false;
	}
	$keyField = 0;
	if ($colorColumn != null) { print("top.elements['".$reqParams['req_name']."'].colors = []; \n"); }
	if (!$retArray) { print("top.elements['".$reqParams['req_name']."'].items = []; \n"); }
	while (!$rs->EOF) {
		$str = ""; $str2 = "";
		$keyField = $rs->fields[0];
		foreach ($rs->fields as $key=>$val){
			if (is_numeric($key) && $key != 0 ) {
				$val = str_replace("'", "\\'", $val);
				//$val = str_replace("'", '\'', $val);
				$val = str_replace("\n", "\\n", $val);
				$val = str_replace("\r", "", $val);
				$str  .= ",'$val'";
				$str2 .= "%%".$val;
			}
		}
		$str = substr($str,1);
		if ($retArray) {
			$retValues[$keyField] = $str2;
		} else {
			print("top.elements['".$reqParams['req_name']."'].addItem('$keyField', [$str]); \n");
			if ($colorColumn != null && $rs->fields[$colorColumn] != '') {
				print("top.elements['".$reqParams['req_name']."'].colors['$keyField'] = '".$rs->fields[$colorColumn]."'; \n");
			}
		}
		$cntr++;
		$rs->MoveNext();
	}
	if (intval($reqParams['req_count']) < 0 && $CQL != "") {
		$rs = $db->execute($CQL);
		if (!$rs) {
			$db->debug = true;
			$db->execute($CQL);
			return false;
		}
		if ($retArray) {
			$retValues['sql_count'] = $rs->fields[0];
		} else {
			print("top.elements['".$reqParams['req_name']."'].count = ".$rs->fields[0].";\n");
		}
	}
	if (!$retArray) print("top.elements['".$reqParams['req_name']."'].dataReceived();\n");
	return $retArray ? $retValues : true;
}

function list_delete($db, $reqParams, $deleteTableName, $deleteKeyField){
	if( $reqParams['req_ids'] > '' ) {
		$reqParams['req_ids'] = "'".str_replace(",", "','", $reqParams['req_ids'])."'";
		$tmp = split(",", $reqParams['req_ids']);
		$error = false;
		foreach ($tmp as $key => $value) {
			$cmd = "DELETE FROM $deleteTableName WHERE $deleteKeyField = $value";
			$ok = $db->execute($cmd);
			if( !$ok ) { $error = true; }
		}
		if ($error) print("alert('Some records cannot be deleted because they have dependencies.'); ");
		print("top.elements['".$reqParams['req_name']."'].getData(); ");
	}
	return false;
}

// ============-----------------------------------------
// ------------ EDIT CLASS FUNCTIONS

function edit_process($db, $reqParams, $SQL) {
	// Execute SQL
	$rs = $db->execute($SQL);
	if (!$rs) {
		$db->debug = true;
		$db->execute($SQL);
		return false;
	}
	while ($rs && !$rs->EOF) {
		$fld = $rs->fields;
		$i   = 0;
		foreach ($fld as $key => $value) {
			if (!is_int($key)) continue;
			$tmpout = str_replace("\\'", "'", $fld[$i]);
			$tmpout = str_replace("'", "\\'", $tmpout);
			$tmpout = str_replace("\n", "\\n", $tmpout);
			$tmpout = str_replace("\r", "", $tmpout);
			print("top.elements['".$reqParams["req_name"]."'].items[$i] = '".$tmpout."';\n");
			$i++;
		}
		$rs->moveNext();
	}
	$rs->moveFirst();
	print("top.elements['".$reqParams["req_name"]."'].dataReceived();\n");
	return $rs;
}

function edit_lookup_process($db, $reqParams, $SQL) {
	$rs = $db->execute("SELECT * FROM ($SQL) list1 LIMIT 10");
	if (!$rs) {
		$db->debug = true;
		$db->execute($SQL);
		return false;
	}
	$i   = 0;
	print("top.elements['".$reqParams["req_name"]."'].lookup_items = [];\n");
	while ($rs && !$rs->EOF) {
		$fld = $rs->fields;
		$tmpout = str_replace("\\'", "'", $fld[1]);
		$tmpout = str_replace("'", "\\'", $tmpout);
		$tmpout = str_replace("\n", "\\n", $tmpout);
		$tmpout = str_replace("\r", "", $tmpout);
		if ($i == 9) {
			print("top.elements['".$reqParams["req_name"]."'].lookup_items[''] = '...';\n");
		} else {
			print("top.elements['".$reqParams["req_name"]."'].lookup_items['".$fld[0]."'] = '".$tmpout."';\n");
		}
		$rs->moveNext(); $i++;
	}
	$rs->moveFirst();
	print("top.elements['".$reqParams["req_name"]."'].lookup_show('".$reqParams["lookup_name"]."');\n");
	return $rs;
}

function edit_save($db, $reqParams, $post, $table, $kfield, $stayHere=false, $seqName=null) {
	if ($reqParams["req_recid"] == "" || $reqParams["req_recid"] == "null") {
		$fields = "";
		$values = "";
		foreach($post as $key => $val) {
			// ignore radio and check boxes
			if (substr($key, strlen($key)-6) == '_radio') continue;
			if (substr($key, strlen($key)-6) == '_check') continue;
			$fields .= $key.",";
			if ($val != '' OR $val == '0') {
				if (substr($val, 0, 2) != '__') $values .= "'".addslashes($val)."',";
										  else $values .= "".substr($val, 2).",";
			} else {
				$values .= "DEFAULT,";
			}
		}
		if (substr($fields, strlen($fields)-1, 1) == ',') $fields = substr($fields, 0, strlen($fields)-1);
		if (substr($values, strlen($values)-1, 1) == ',') $values = substr($values, 0, strlen($values)-1);
		$SQL = "INSERT INTO $table($fields) VALUES ($values);";
		if ($seqName != null) $SQL .= "SELECT currval('$seqName');";
	} else {
		$fields = "";
		foreach($post as $key => $val) {
			// ignore radio and check boxes
			if (substr($key, strlen($key)-6) == '_radio') continue;
			if (substr($key, strlen($key)-6) == '_check') continue;
			if ($val != '' OR $val == '0') {
				if (substr($val, 0, 2) != '__') $fields .= "$key = '".addslashes($val)."',";
										  else $fields .= "$key = ".substr($val, 2).",";
			} else {
				$fields .= "$key = null,";
			}
		}
		if (substr($fields, strlen($fields)-1, 1) == ',') $fields = substr($fields, 0, strlen($fields)-1);
		$SQL = "UPDATE $table SET $fields WHERE $kfield='".$reqParams["req_recid"]."'";
	}
	$rs = $db->execute($SQL);
	if (!$rs) {
		$db->debug = true;
		$db->execute($SQL);
		edit_showFrame($reqParams);
		die();
	}
	if ($seqName != null && ($reqParams["req_recid"] == "" || $reqParams["req_recid"] == "null")) $reqParams["req_recid"] = $rs->fields[0];
	if (!$stayHere) edit_finish($reqParams);
	return $reqParams;
}

function edit_finish($reqParams) {
	print("<script>
			  top.elements['".$reqParams["req_name"]."'].saveDone(".$reqParams["req_recid"].");
		   </script>
		   ");
}

function edit_showFrame($reqParams) {
	print("=== POST ===<pre>");
	print_r($_POST);
	print("</pre>");
	print("<script>
				frm = parent.document.getElementById('".$reqParams["req_frame"]."');
				frm.style.border  = '1px solid gray';
				frm.style.width   = '99%';
				frm.style.height  = '300px';
				alert('Error while saving. Please scroll down to see error details.');
		   </script>");
}

?>