<?php
/**
 * PEAR:DB specific functions file
 *
 * This file implements the datastore functions necessary for PhpGedView to use an SQL database as its
 * datastore. This file also implements array caches for the database tables.  Whenever data is
 * retrieved from the database it is stored in a cache.  When a database access is requested the
 * cache arrays are checked first before querying the database.
 *
 * phpGedView: Genealogy Viewer
 * Copyright (C) 2002 to 2007  PGV Development Team
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @version $Id$
 * @package PhpGedView
 * @subpackage DB
 */

if (stristr($_SERVER["SCRIPT_NAME"], basename(__FILE__))!==false) {
	print "You cannot access an include file directly.";
	exit;
}

//-- load the PEAR:DB files
require_once 'DB.php';

//-- set the REGEXP status of databases
$REGEXP_DB = (stristr($DBTYPE,'mysql') !== false || $DBTYPE=='pgsql');

/**
 * Field and function definition variances between sql databases
 */
//-- mysql
if (stristr($DBTYPE,'mysql') !== false) {
	define('DB_RANDOM', 'RAND');
	define('DB_LONGTEXT_TYPE', 'LONGTEXT');
	define('DB_BEGIN_TRANS', 'BEGIN');
	define('DB_COMMIT_TRANS', 'COMMIT');
}
else if ($DBTYPE=='pgsql') {
	define('DB_RANDOM', 'RANDOM');
	define('DB_LONGTEXT_TYPE', 'TEXT');
	define('DB_BEGIN_TRANS', 'BEGIN');
	define('DB_COMMIT_TRANS', 'COMMIT');
}
else if ($DBTYPE=='sqlite') {
	define('DB_RANDOM', 'RANDOM');
	define('DB_LONGTEXT_TYPE', 'TEXT');
	define('DB_BEGIN_TRANS', 'BEGIN');
	define('DB_COMMIT_TRANS', 'COMMIT');
}
else if ($DBTYPE=='mssql') {
	define('DB_RANDOM', 'NEWID');
	define('DB_LONGTEXT_TYPE', 'TEXT');
	define('DB_BEGIN_TRANS', 'BEGIN TRANSACTION');
	define('DB_COMMIT_TRANS', 'COMMIT TRANSACTION');
}

//-- uncomment the following line to turn on sql query logging
//$SQL_LOG = true;

/**
 * query the database
 *
 * this function will perform the given SQL query on the database
 * @param string $sql		the sql query to execture
 * @param boolean $show_error	whether or not to show any error messages
 * @param int $count	the number of records to return, 0 returns all
 * @return Object the connection result
 */
function &dbquery($sql, $show_error=true, $count=0) {
	global $DBCONN, $TOTAL_QUERIES, $INDEX_DIRECTORY, $SQL_LOG, $LAST_QUERY, $CONFIGURED;

	if (!$CONFIGURED) return false;
	if (!isset($DBCONN)) {
		//print "No Connection";
		return false;
	}
	//-- make sure a database connection has been established
	if (DB::isError($DBCONN)) {
		if ($DBCONN->getCode()!=-24) print $DBCONN->getCode()." ".$DBCONN->getMessage();
		return $DBCONN;
	}
	
	/**
	 * Debugging code for multi-database support
	 */
/* -- commenting out for final release
	if (preg_match('/[^\\\]"/', $sql)>0) {
		pgv_error_handler(2, "<span class=\"error\">Incompatible SQL syntax. Double quote query: $sql</span><br>","","");
	}
	if (preg_match('/LIMIT \d/', $sql)>0) {
		pgv_error_handler(2,"<span class=\"error\">Incompatible SQL syntax. Limit query error, use dbquery \$count parameter instead: $sql</span><br>","","");
	}
	if (preg_match('/(&&)|(\|\|)/', $sql)>0) {
		pgv_error_handler(2,"<span class=\"error\">Incompatible SQL syntax.  Use 'AND' instead of '&&'.  Use 'OR' instead of '||'.: $sql</span><br>","","");
	}
	*/
	if (!empty($SQL_LOG)) $start_time2 = getmicrotime();
	if ($count == 0)
		$res =& $DBCONN->query($sql);
	else
		$res =& $DBCONN->limitQuery($sql, 0, $count);

	$LAST_QUERY = $sql;
	$TOTAL_QUERIES++;
	if (!empty($SQL_LOG)) {
		global $start_time;
		$end_time = getmicrotime();
		$exectime = $end_time - $start_time;
		$exectime2 = $end_time - $start_time2;
		
		if ($count>0) $sql = $DBCONN->modifyLimitQuery($sql, 0, $count);
		
		$fp = fopen($INDEX_DIRECTORY."/sql_log.txt", "a");
		$backtrace = debug_backtrace();
		$temp = "";
		if (!DB::isError($res) && is_object($res)) $temp .= "\t".$res->numRows()."\t";
		if (isset($backtrace[3])) $temp .= basename($backtrace[3]["file"])." (".$backtrace[3]["line"].")";
		if (isset($backtrace[2])) $temp .= basename($backtrace[2]["file"])." (".$backtrace[2]["line"].")";
		if (isset($backtrace[1])) $temp .= basename($backtrace[1]["file"])." (".$backtrace[1]["line"].")";
		$temp .= basename($backtrace[0]["file"])." (".$backtrace[0]["line"].")";
		fwrite($fp, date("Y-m-d H:i:s")."\t".sprintf(" %.4f %.4f sec", $exectime, $exectime2).$_SERVER["SCRIPT_NAME"]."\t".$temp."\t".$TOTAL_QUERIES."-".$sql."\r\n");
		fclose($fp);
	}
	if (DB::isError($res)) {
		if ($show_error) print "<span class=\"error\"><b>ERROR:".$res->getCode()." ".$res->getMessage()." <br />SQL:</b>".$res->getUserInfo()."</span><br /><br />\n";
	}
	return $res;
}

/**
 * prepare an item to be updated in the database
 *
 * add slashes and convert special chars so that it can be added to db
 * @param mixed $item		an array or string to be prepared for the database
 */
function db_prep($item) {
	global $DBCONN;

	if (is_array($item)) {
		foreach($item as $key=>$value) {
			$item[$key]=db_prep($value);
		}
		return $item;
	}
	else {
		if (DB::isError($DBCONN) || empty($DBCONN)) return $item;
		if (is_object($DBCONN)) return $DBCONN->escapeSimple($item);
		//-- use the following commented line to convert between character sets
		//return $DBCONN->escapeSimple(iconv("iso-8859-1", "UTF-8", $item));
	}
}

/**
 * Clean up an item retrieved from the database
 *
 * clean the slashes and convert special
 * html characters to their entities for
 * display and entry into form elements
 * @param mixed $item	the item to cleanup
 * @return mixed the cleaned up item
 */
function db_cleanup($item) {
//	return $item;
	if (is_array($item)) {
		foreach($item as $key=>$value) {
			if ($key!="gedcom") $item[$key]=stripslashes($value);
			else $key=$value;
		}
		return $item;
	}
	else {
		return stripslashes($item);
	}
}

/**
 * check if a gedcom has been imported into the database
 *
 * this function checks the database to see if the given gedcom has been imported yet.
 * @param string $ged the filename of the gedcom to check for import
 * @return bool return true if the gedcom has been imported otherwise returns false
 */
function check_for_import($ged) {
	global $TBLPREFIX, $DBCONN, $GEDCOMS;

	if (DB::isError($DBCONN)) return false;
	if (count($GEDCOMS)==0) return false;
	
	if (!isset($GEDCOMS[$ged]["imported"])) {
		$GEDCOMS[$ged]["imported"] = false;
			$sql = "SELECT count(i_id) FROM ".$TBLPREFIX."individuals WHERE i_file='".$DBCONN->escapeSimple($GEDCOMS[$ged]["id"])."'";
			$res = dbquery($sql, false);
	
			if (!empty($res) && !DB::isError($res) && is_object($res)) {
				$row =& $res->fetchRow();
				$res->free();
				if ($row[0]>0) {
					$GEDCOMS[$ged]["imported"] = true;
				}
			}
		store_gedcoms();
	}
	
	return $GEDCOMS[$ged]["imported"];
}

/**
 * find the gedcom record for a family
 *
 * This function first checks the <var>$famlist</var> cache to see if the family has already
 * been retrieved from the database.  If it hasn't been retrieved, then query the database and
 * add it to the cache.
 * also lookup the husb and wife so that they are in the cache
 * @link http://phpgedview.sourceforge.net/devdocs/arrays.php#family
 * @param string $famid the unique gedcom xref id of the family record to retrieve
 * @return string the raw gedcom record is returned
 */
function find_family_record($famid, $gedfile="") {
	global $TBLPREFIX;
	global $GEDCOMS, $GEDCOM, $famlist, $DBCONN;

	if (empty($famid)) return false;
	if (empty($gedfile)) $gedfile = $GEDCOM;

	if (isset($famlist[$famid]["gedcom"])&&($famlist[$famid]["gedfile"]==$GEDCOMS[$gedfile]["id"])) return $famlist[$famid]["gedcom"];

	$sql = "SELECT f_gedcom, f_file, f_husb, f_wife, f_numchil FROM ".$TBLPREFIX."families WHERE f_id LIKE '".$DBCONN->escapeSimple($famid)."' AND f_file='".$DBCONN->escapeSimple($GEDCOMS[$gedfile]["id"])."'";
	$res = dbquery($sql);
	if ($res->numRows()==0) {
		//debug_print_backtrace();
		return false;
	}
	$row =& $res->fetchRow();

	$famlist[$famid]["gedcom"] = $row[0];
	$famlist[$famid]["gedfile"] = $row[1];
	$famlist[$famid]["husb"] = $row[2];
	$famlist[$famid]["wife"] = $row[3];
	$famlist[$famid]["numchil"] = $row[4];
	find_person_record($row[2]);
	find_person_record($row[3]);
	$res->free();
	return $row[0];
}

/**
 * Load up a group of families into the cache by their ids from an array
 * This function is useful for optimizing pages that need to reference large
 * sets of families without loading them up individually 
 * @param array $ids	an array of ids to load up
 */
function load_families($ids, $gedfile='') {
	global $TBLPREFIX;
	global $GEDCOM, $GEDCOMS;
	global $famlist, $DBCONN;
	
	if (empty($gedfile)) $gedfile = $GEDCOM;
	if (!is_int($gedfile)) $gedfile = get_gedcom_from_id($gedfile);
	
	$sql = "SELECT f_gedcom, f_file, f_husb, f_wife, f_id, f_numchil FROM ".$TBLPREFIX."families WHERE f_id IN (";
	//-- don't load up families who are already loaded
	$idsadded = false;
	foreach($ids as $k=>$id) {
		if ((!isset($famlist[$id]["gedcom"])) || ($famlist[$id]["gedfile"]!=$GEDCOMS[$gedfile]["id"])) {
			$sql .= "'".$DBCONN->escapeSimple($id)."',";
			$idsadded = true;
		}
	}
	if (!$idsadded) return;
	$sql = rtrim($sql,',');
	$sql .= ") AND f_file='".$DBCONN->escapeSimple($GEDCOMS[$gedfile]["id"])."'";
	
	$res = dbquery($sql);

	if (!DB::isError($res)) {
		if ($res->numRows()==0) {
			return false;
		}
		$parents = array();
		while($row =& $res->fetchRow()) {
			if (!isset($famlist[$row[4]])) {
			$famlist[$row[4]]["gedcom"] = $row[0];
			$famlist[$row[4]]["gedfile"] = $row[1];
			$famlist[$row[4]]["husb"] = $row[2];
			$famlist[$row[4]]["wife"] = $row[3];
				$famlist[$row[4]]["numchil"] = $row[5];
			$parents[] = $row[2];
			$parents[] = $row[3];
			}
//			find_person_record($row[2]);
//			find_person_record($row[3]);
		}
		$res->free();
		load_people($parents);
	}
}

/**
 * find the gedcom record for an individual
 *
 * This function first checks the <var>$indilist</var> cache to see if the individual has already
 * been retrieved from the database.  If it hasn't been retrieved, then query the database and
 * add it to the cache.
 * @link http://phpgedview.sourceforge.net/devdocs/arrays.php#indi
 * @param string $pid the unique gedcom xref id of the individual record to retrieve
 * @return string the raw gedcom record is returned
 */
function find_person_record($pid, $gedfile="") {
	global $TBLPREFIX;
	global $GEDCOM, $GEDCOMS;
	global $indilist, $DBCONN;

	if (empty($pid)) return false;
	if (empty($gedfile)) $gedfile = $GEDCOM;

	if (!is_int($gedfile)) $gedfile = get_gedcom_from_id($gedfile);
	//-- first check the indilist cache
	// cache is unreliable for use with different gedcoms in user favorites (sjouke)
	if ((isset($indilist[$pid]["gedcom"]))&&($indilist[$pid]["gedfile"]==$GEDCOMS[$gedfile]["id"])) return $indilist[$pid]["gedcom"];

	$sql = "SELECT i_gedcom, i_name, i_isdead, i_file FROM ".$TBLPREFIX."individuals WHERE i_id LIKE '".$DBCONN->escapeSimple($pid)."' AND i_file='".$DBCONN->escapeSimple($GEDCOMS[$gedfile]["id"])."'";
	$res = dbquery($sql);

	if (!DB::isError($res)) {
		if ($res->numRows()==0) {
			return false;
		}
		$row =& $res->fetchRow();
		//-- don't cache records from other gedcoms
		if (!isset($indilist[$pid]) || $indilist[$pid]['gedfile']==$gedfile) {
			$indilist[$pid]["gedcom"] = $row[0];
			$indilist[$pid]["names"] = get_indi_names($row[0]);
			$indilist[$pid]["isdead"] = $row[2];
			$indilist[$pid]["gedfile"] = $row[3];
			if (isset($indilist[$pid]['privacy'])) unset($indilist[$pid]['privacy']);
		}
		$res->free();
		return $row[0];
	}
}

/**
 * Load up a group of people into the cache by their ids from an array
 * This function is useful for optimizing pages that need to reference large
 * sets of people without loading them up individually 
 * @param array $ids	an array of ids to load up
 */
function load_people($ids, $gedfile='') {
	global $TBLPREFIX;
	global $GEDCOM, $GEDCOMS;
	global $indilist, $DBCONN;
	
	if (count($ids)==0) return false;
	
	if (empty($gedfile)) $gedfile = $GEDCOM;
	if (!is_int($gedfile)) $gedfile = get_gedcom_from_id($gedfile);
	
	$sql = "SELECT i_gedcom, i_name, i_isdead, i_file, i_id FROM ".$TBLPREFIX."individuals WHERE i_id IN (";
	//-- don't load up people who are already loaded
	$idsadded = false;
	foreach($ids as $k=>$id) {
		if ((!isset($indilist[$id]["gedcom"])) || ($indilist[$id]["gedfile"]!=$GEDCOMS[$gedfile]["id"])) {
			$sql .= "'".$DBCONN->escapeSimple($id)."',";
			$idsadded = true;
		}
	}
	if (!$idsadded) return;
	$sql = rtrim($sql,',');
	$sql .= ") AND i_file='".$DBCONN->escapeSimple($GEDCOMS[$gedfile]["id"])."'";
	
	$res = dbquery($sql);

	if (!DB::isError($res)) {
		if ($res->numRows()==0) {
			return false;
		}
		while($row =& $res->fetchRow()) {
			if (!isset($indilist[$row[4]])) {
			$indilist[$row[4]]["gedcom"] = $row[0];
			$indilist[$row[4]]["names"] = get_indi_names($row[0]);
			$indilist[$row[4]]["isdead"] = $row[2];
			$indilist[$row[4]]["gedfile"] = $row[3];
			}
		}
		$res->free();
	}
}

/**
 * find the gedcom record
 *
 * This function first checks the caches to see if the record has already
 * been retrieved from the database.  If it hasn't been retrieved, then query the database and
 * add it to the cache.
 * @link http://phpgedview.sourceforge.net/devdocs/arrays.php#other
 * @param string $pid the unique gedcom xref id of the record to retrieve
 * @return string the raw gedcom record is returned
 */
function find_gedcom_record($pid, $gedfile = "") {
	global $TBLPREFIX, $GEDCOMS;
	global $GEDCOM, $indilist, $famlist, $sourcelist, $objectlist, $otherlist, $DBCONN;

	if (empty($pid)) return false;
	if (empty($gedfile)) $gedfile = $GEDCOM;

	if ((isset($indilist[$pid]["gedcom"]))&&($indilist[$pid]["gedfile"]==$GEDCOMS[$gedfile]["id"])) return $indilist[$pid]["gedcom"];
	if ((isset($famlist[$pid]["gedcom"]))&&($famlist[$pid]["gedfile"]==$GEDCOMS[$gedfile]["id"])) return $famlist[$pid]["gedcom"];
	if ((isset($objectlist[$pid]["gedcom"]))&&($objectlist[$pid]["gedfile"]==$GEDCOMS[$gedfile]["id"])) return $objectlist[$pid]["gedcom"];
	if ((isset($sourcelist[$pid]["gedcom"]))&&($sourcelist[$pid]["gedfile"]==$GEDCOMS[$gedfile]["id"])) return $sourcelist[$pid]["gedcom"];
	if ((isset($repolist[$pid]["gedcom"])) && ($repolist[$pid]["gedfile"]==$GEDCOMS[$gedfile]["id"])) return $repolist[$pid]["gedcom"];
	if ((isset($otherlist[$pid]["gedcom"]))&&($otherlist[$pid]["gedfile"]==$GEDCOMS[$gedfile]["id"])) return $otherlist[$pid]["gedcom"];

	//-- try to look ahead and guess the best type of record to look for
	//-- NOTE: not foolproof so leave the other section in place
	$type = id_type($pid);
	switch($type) {
		case 'INDI':
			$gedrec = find_person_record($pid, $gedfile);
			break;
		case 'FAM':
			$gedrec = find_family_record($pid, $gedfile);
			break;
		case 'OBJE':
			$gedrec = find_media_record($pid, $gedfile);
			break;
		case 'SOUR':
			$gedrec = find_source_record($pid, $gedfile);
			break;
		case 'REPO':
			$gedrec = find_repo_record($pid, $gedfile);
			break;
	}

	//-- unable to guess the type so look in all the tables
	if (empty($gedrec)) {
		$sql = "SELECT o_gedcom, o_file FROM ".$TBLPREFIX."other WHERE o_id LIKE '".$DBCONN->escapeSimple($pid)."' AND o_file='".$DBCONN->escapeSimple($GEDCOMS[$gedfile]["id"])."'";
		$res =& dbquery($sql);
		if (DB::isError($res)) return "";
		if ($res->numRows()!=0) {
			$row =& $res->fetchRow();
			$res->free();
			$otherlist[$pid]["gedcom"] = $row[0];
			$otherlist[$pid]["gedfile"] = $row[1];
			return $row[0];
		}
		$res->free();
		$gedrec = find_person_record($pid, $gedfile);
		if (empty($gedrec)) $gedrec = find_family_record($pid, $gedfile);
		if (empty($gedrec)) $gedrec = find_source_record($pid, $gedfile);
		if (empty($gedrec)) $gedrec = find_media_record($pid, $gedfile);
			//-- why are we looking in the media_mapping table here?
			if (empty($gedrec)) {
				$sql1 = "select mm_gedrec, mm_gedfile from ".$TBLPREFIX."media_mapping where mm_gid='".$DBCONN->escapeSimple($pid)."' AND mm_gedfile=".$DBCONN->escapeSimple($GEDCOMS[$GEDCOM]['id']);
				$res1 = dbquery($sql1);
				if (!DB::isError($res1) && $res1!==false) {
					if ($res1->numRows() != 0){
						$row1 =& $res1->fetchRow();
						$res1->free();
						$otherlist[$pid]["gedcom"] = $row1[0];
						$otherlist[$pid]["gedfile"] = $row1[1];
						return $row1[0];
					}
				}
			}
		}
	return $gedrec;
}

/**
 * find the gedcom record for a source
 *
 * This function first checks the <var>$sourcelist</var> cache to see if the source has already
 * been retrieved from the database.  If it hasn't been retrieved, then query the database and
 * add it to the cache.
 * @link http://phpgedview.sourceforge.net/devdocs/arrays.php#source
 * @param string $sid the unique gedcom xref id of the source record to retrieve
 * @return string the raw gedcom record is returned
 */
function find_source_record($sid, $gedfile="") {
	global $TBLPREFIX, $GEDCOMS;
	global $GEDCOM, $sourcelist, $DBCONN;

	if ($sid=="") return false;
	if (empty($gedfile)) $gedfile = $GEDCOM;

	if (isset($sourcelist[$sid]["gedcom"]) && ($sourcelist[$sid]["gedfile"]==$GEDCOMS[$gedfile]["id"])) return $sourcelist[$sid]["gedcom"];

	$sql = "SELECT s_gedcom, s_name, s_file FROM ".$TBLPREFIX."sources WHERE s_id LIKE '".$DBCONN->escapeSimple($sid)."' AND s_file='".$DBCONN->escapeSimple($GEDCOMS[$gedfile]["id"])."'";
	$res = dbquery($sql);

	if ($res->numRows()!=0) {
		$row =& $res->fetchRow();
		$sourcelist[$sid]["name"] = stripslashes($row[1]);
		$sourcelist[$sid]["gedcom"] = $row[0];
		$sourcelist[$sid]["gedfile"] = $row[2];
		$res->free();
		return $row[0];
	}
	else {
		return false;

	}
}


/**
 * Find a repository record by its ID
 * @param string $rid	the record id
 * @param string $gedfile	the gedcom file id
 */
function find_repo_record($rid, $gedfile="") {
	global $TBLPREFIX, $GEDCOMS;
	global $GEDCOM, $repolist, $DBCONN;

	if ($rid=="") return false;
	if (empty($gedfile)) $gedfile = $GEDCOM;

	if (isset($repolist[$rid]["gedcom"]) && ($repolist[$rid]["gedfile"]==$GEDCOMS[$gedfile]["id"])) return $repolist[$rid]["gedcom"];

	$sql = "SELECT o_id, o_gedcom, o_file FROM ".$TBLPREFIX."other WHERE o_type='REPO' AND o_id LIKE '".$DBCONN->escapeSimple($rid)."' AND o_file='".$DBCONN->escapeSimple($GEDCOMS[$gedfile]["id"])."'";
	$res = dbquery($sql);

	if ($res->numRows()!=0) {
		$row =& $res->fetchRow();
		$tt = preg_match("/1 NAME (.*)/", $row[1], $match);
		if ($tt == "0") $name = $row[0]; else $name = $match[1];
		$repolist[$rid]["name"] = stripslashes($name);
		$repolist[$rid]["gedcom"] = $row[1];
		$repolist[$rid]["gedfile"] = $row[2];
		$res->free();
		return $row[1];
	}
	else {
		return false;
	}
}

/**
 * Find a media record by its ID
 * @param string $rid	the record id
 */
function find_media_record($rid, $gedfile='') {
	global $TBLPREFIX, $GEDCOMS;
	global $GEDCOM, $objectlist, $DBCONN, $MULTI_MEDIA;

	//-- don't look for a media record if not using media
	if (!$MULTI_MEDIA) return false;
	if ($rid=="") return false;
	if (empty($gedfile)) $gedfile = $GEDCOM;

	//-- first check for the record in the cache
	if (empty($objectlist)) $objectlist = array();
	if (isset($objectlist[$rid]["gedcom"]) && ($objectlist[$rid]["gedfile"]==$GEDCOMS[$gedfile]["id"])) return $objectlist[$rid]["gedcom"];

	$sql = "SELECT * FROM ".$TBLPREFIX."media WHERE m_media LIKE '".$DBCONN->escapeSimple($rid)."' AND m_gedfile='".$DBCONN->escapeSimple($GEDCOMS[$gedfile]["id"])."'";
	$res = dbquery($sql);
	if (DB::isError($res)) return false;
	if ($res->numRows()!=0) {
		$row = $res->fetchRow(DB_FETCHMODE_ASSOC);
		$row = db_cleanup($row);
		$objectlist[$rid]["ext"] = $row["m_ext"];
		$row["m_titl"] = trim($row["m_titl"]);
		if (empty($row["m_titl"])) $row["m_titl"] = $row["m_file"];
		$objectlist[$rid]["title"] = $row["m_titl"];
		$objectlist[$rid]["file"] = $row["m_file"];
		$objectlist[$rid]["gedcom"] = $row["m_gedrec"];
		$objectlist[$rid]["gedfile"] = $row["m_gedfile"];
		$res->free();
		return $row["m_gedrec"];
	}
	else {
		return false;
	}
}

/**
 * find and return the id of the first person in the gedcom
 * @return string the gedcom xref id of the first person in the gedcom
 */
function find_first_person() {
	global $GEDCOM, $TBLPREFIX, $GEDCOMS, $DBCONN;
	$sql = "SELECT i_id FROM ".$TBLPREFIX."individuals WHERE i_file='".$DBCONN->escapeSimple($GEDCOMS[$GEDCOM]["id"])."' ORDER BY i_id";
	$res = dbquery($sql,false,1);
	$row = $res->fetchRow();
	$res->free();
	if (!DB::isError($row)) return $row[0];
	else return "I1";
}

/**
 * update the is_dead status in the database
 *
 * this function will update the is_dead field in the individuals table with the correct value
 * calculated by the is_dead() function.  To improve import performance, the is_dead status is first
 * set to -1 during import.  The first time the is_dead status is retrieved this function is called to update
 * the database.  This makes the first request for a person slower, but will speed up all future requests.
 * @param string $gid	gedcom xref id of individual to update
 * @param array $indi	the $indi array struction for the individal as used in the <var>$indilist</var>
 * @return int	1 if the person is dead, 0 if living
 */
function update_isdead($gid, $indi) {
	global $TBLPREFIX, $indilist, $DBCONN;
	$isdead = 0;
	if (isset($indi["gedcom"])) {
		$isdead = is_dead($indi["gedcom"]);
		if (empty($isdead)) $isdead = 0;
		$sql = "UPDATE ".$TBLPREFIX."individuals SET i_isdead=$isdead WHERE i_id LIKE '".$DBCONN->escapeSimple($gid)."' AND i_file='".$DBCONN->escapeSimple($indi["gedfile"])."'";
		$res = dbquery($sql);
	}
	if (isset($indilist[$gid])) $indilist[$gid]["isdead"] = $isdead;
	return $isdead;
}

/**
 * reset the i_isdead column
 *
 * This function will reset the i_isdead column with the default -1 so that all is dead status
 * items will be recalculated.
 */
function reset_isdead() {
	global $TBLPREFIX, $GEDCOMS, $GEDCOM, $DBCONN;

	$sql = "UPDATE ".$TBLPREFIX."individuals SET i_isdead=-1 WHERE i_file='".$DBCONN->escapeSimple($GEDCOMS[$GEDCOM]["id"])."'";
	dbquery($sql);
}

/**
 * get a list of all the source titles
 *
 * returns an array of all of the sourcetitles in the database.
 * @link http://phpgedview.sourceforge.net/devdocs/arrays.php#sources
 * @return array the array of source-titles
 */
function get_source_add_title_list() {
	global $sourcelist, $GEDCOM, $GEDCOMS;
	global $TBLPREFIX, $DBCONN;

	$sourcelist = array();

 	$sql = "SELECT s_id, s_file, s_file as s_name, s_gedcom FROM ".$TBLPREFIX."sources WHERE s_file='".$DBCONN->escapeSimple($GEDCOMS[$GEDCOM]["id"])."' AND ((s_gedcom LIKE '% _HEB %') OR (s_gedcom LIKE '% ROMN %'));";

	$res = dbquery($sql);

	$ct = $res->numRows();
	while($row =& $res->fetchRow(DB_FETCHMODE_ASSOC)){
		$source = array();
		$row = db_cleanup($row);
		$ct = preg_match("/\d ROMN (.*)/", $row["s_gedcom"], $match);
 		if ($ct==0) $ct = preg_match("/\d _HEB (.*)/", $row["s_gedcom"], $match);
		$source["name"] = $match[1];
		$source["gedcom"] = $row["s_gedcom"];
		$source["gedfile"] = $row["s_file"];
		$sourcelist[$row["s_id"]] = $source;
	}
	$res->free();

	return $sourcelist;
}

/**
 * get a list of all the sources
 *
 * returns an array of all of the sources in the database.
 * @link http://phpgedview.sourceforge.net/devdocs/arrays.php#sources
 * @return array the array of sources
 */
function get_source_list() {
	global $sourcelist, $GEDCOM, $GEDCOMS;
	global $TBLPREFIX, $DBCONN;

	$sourcelist = array();

	$sql = "SELECT * FROM ".$TBLPREFIX."sources WHERE s_file='".$DBCONN->escapeSimple($GEDCOMS[$GEDCOM]["id"])."' ORDER BY s_name";
	$res = dbquery($sql);

	$ct = $res->numRows();
	while($row =& $res->fetchRow(DB_FETCHMODE_ASSOC)){
		$source = array();
		$source["name"] = $row["s_name"];
		$source["gedcom"] = $row["s_gedcom"];
		$row = db_cleanup($row);
		$source["gedfile"] = $row["s_file"];
//		$source["nr"] = 0;
		$sourcelist[$row["s_id"]] = $source;
	}
	$res->free();

	return $sourcelist;
}

//-- get the repositorylist from the datastore
function get_repo_list() {
	global $repolist, $GEDCOM, $GEDCOMS;
	global $TBLPREFIX, $DBCONN;

	$repolist = array();

	$sql = "SELECT * FROM ".$TBLPREFIX."other WHERE o_file='".$DBCONN->escapeSimple($GEDCOMS[$GEDCOM]["id"])."' AND o_type='REPO'";
	$res = dbquery($sql);

	$ct = $res->numRows();
	while($row =& $res->fetchRow(DB_FETCHMODE_ASSOC)){
		$repo = array();
		$tt = preg_match("/1 NAME (.*)/", $row["o_gedcom"], $match);
		if ($tt == "0") $name = $row["o_id"]; else $name = $match[1];
		$repo["name"] = $name;
		$repo["id"] = $row["o_id"];
		$repo["gedfile"] = $row["o_file"];
		$repo["type"] = $row["o_type"];
		$repo["gedcom"] = $row["o_gedcom"];
		$row = db_cleanup($row);
		$repolist[$row["o_id"]]= $repo;
	}
	$res->free();
	asort($repolist); // sort by repo name
	return $repolist;
}

//-- get the repositorylist from the datastore
function get_repo_id_list() {
	global $GEDCOM, $GEDCOMS;
	global $TBLPREFIX, $DBCONN;

	$repo_id_list = array();

	$sql = "SELECT * FROM ".$TBLPREFIX."other WHERE o_file='".$DBCONN->escapeSimple($GEDCOMS[$GEDCOM]["id"])."' AND o_type='REPO' ORDER BY o_id";
	$res = dbquery($sql);

	$ct = $res->numRows();
	while($row =& $res->fetchRow(DB_FETCHMODE_ASSOC)){
		$repo = array();
		$tt = preg_match("/1 NAME (.*)/", $row["o_gedcom"], $match);
		if ($tt>0) $repo["name"] = $match[1];
		else $repo["name"] = "";
		$repo["gedfile"] = $row["o_file"];
		$repo["type"] = $row["o_type"];
		$repo["gedcom"] = $row["o_gedcom"];
		$row = db_cleanup($row);
		$repo_id_list[$row["o_id"]] = $repo;
	}
	$res->free();
	return $repo_id_list;
}

/**
 * get a list of all the repository titles
 *
 * returns an array of all of the repositorytitles in the database.
 * @link http://phpgedview.sourceforge.net/devdocs/arrays.php#repositories
 * @return array the array of repository-titles
 */
function get_repo_add_title_list() {
	global $GEDCOM, $GEDCOMS;
	global $TBLPREFIX, $DBCONN;

	$repolist = array();

 	$sql = "SELECT o_id, o_file, o_file as o_name, o_type, o_gedcom FROM ".$TBLPREFIX."other WHERE o_type='REPO' AND o_file='".$DBCONN->escapeSimple($GEDCOMS[$GEDCOM]["id"])."' AND ((o_gedcom LIKE '% _HEB %') OR (o_gedcom LIKE '% ROMN %'));";

	$res = dbquery($sql);

	$ct = $res->numRows();
	while($row =& $res->fetchRow(DB_FETCHMODE_ASSOC)){
		$repo = array();
		$repo["gedcom"] = $row["o_gedcom"];
		$ct = preg_match("/\d ROMN (.*)/", $row["o_gedcom"], $match);
 		if ($ct==0) $ct = preg_match("/\d _HEB (.*)/", $row["o_gedcom"], $match);
		$repo["name"] = $match[1];
		$repo["id"] = $row["o_id"];
		$repo["gedfile"] = $row["o_file"];
		$repo["type"] = $row["o_type"];
		$row = db_cleanup($row);
		$repolist[$row["o_id"]] = $repo;

	}
	$res->free();
	return $repolist;
}

//-- get the indilist from the datastore
function get_indi_list() {
	global $indilist, $GEDCOM, $DBCONN, $GEDCOMS;
	global $TBLPREFIX, $INDILIST_RETRIEVED;

	if ($INDILIST_RETRIEVED) return $indilist;
	$indilist = array();
	$sql = "SELECT * FROM ".$TBLPREFIX."individuals WHERE i_file='".$DBCONN->escapeSimple($GEDCOMS[$GEDCOM]["id"])."' ORDER BY i_surname";
	$res = dbquery($sql);

	$ct = $res->numRows();
	while($row =& $res->fetchRow(DB_FETCHMODE_ASSOC)){
		$indi = array();
		$indi["gedcom"] = $row["i_gedcom"];
		$row = db_cleanup($row);
		$indi["names"] = array(array($row["i_name"], $row["i_letter"], $row["i_surname"], "A"));
		$indi["isdead"] = $row["i_isdead"];
		$indi["gedfile"] = $row["i_file"];
		$indilist[$row["i_id"]] = $indi;
	}
	$res->free();

	$sql = "SELECT * FROM ".$TBLPREFIX."names WHERE n_file='".$DBCONN->escapeSimple($GEDCOMS[$GEDCOM]["id"])."' ORDER BY n_surname";
	$res = dbquery($sql);

	$ct = $res->numRows();
	while($row =& $res->fetchRow(DB_FETCHMODE_ASSOC)){
		$row = db_cleanup($row);
		if (isset($indilist[$row["n_gid"]]) && ($indilist[$row["n_gid"]]["gedfile"]==$GEDCOMS[$GEDCOM]["id"])) {
			$indilist[$row["n_gid"]]["names"][] = array($row["n_name"], $row["n_letter"], $row["n_surname"], $row["n_type"]);
		}
	}
	$res->free();
	$INDILIST_RETRIEVED = true;
	return $indilist;
}


//-- get the assolist from the datastore
function get_asso_list($type = "all", $ipid='') {
	global $assolist, $GEDCOM;
	global $TBLPREFIX, $ASSOLIST_RETRIEVED;

	if ($ASSOLIST_RETRIEVED) return $assolist;
	$assolist = array();

	$oldged = $GEDCOM;
	if (($type == "all") || ($type == "fam")) {
		$sql = "SELECT f_id, f_file, f_gedcom, f_husb, f_wife FROM ".$TBLPREFIX."families WHERE f_gedcom ";
		if (!empty($pid)) $sql .= "LIKE '% ASSO @$ipid@%'";
		else $sql .= "LIKE '% ASSO %'";
		$res = dbquery($sql);

		$ct = $res->numRows();
		while($row =& $res->fetchRow(DB_FETCHMODE_ASSOC)){
			$asso = array();
			$asso["type"] = "fam";
			$pid2 = $row["f_id"]."[".$row["f_file"]."]";
			$asso["gedcom"] = $row["f_gedcom"];
			$asso["gedfile"] = $row["f_file"];
			// Get the family names
			$GEDCOM = get_gedcom_from_id($row["f_file"]);
			$hname = get_sortable_name($row["f_husb"], "", "", true);
			$wname = get_sortable_name($row["f_wife"], "", "", true);
			if (empty($hname)) $hname = "@N.N.";
			if (empty($wname)) $wname = "@N.N.";
			$name = array();
			foreach ($hname as $hkey => $hn) {
				foreach ($wname as $wkey => $wn) {
					$name[] = $hn." + ".$wn;
					$name[] = $wn." + ".$hn;
				}
			}
			$asso["name"] = $name;
			$ca = preg_match_all("/\d ASSO @(.*)@/", $row["f_gedcom"], $match, PREG_SET_ORDER);
			for ($i=0; $i<$ca; $i++) {
				$pid = $match[$i][1]."[".$row["f_file"]."]";
				$assolist[$pid][$pid2] = $asso;
			}
			$row = db_cleanup($row);
		}
		$res->free();
	}

	if (($type == "all") || ($type == "indi")) {
		$sql = "SELECT i_id, i_file, i_gedcom FROM ".$TBLPREFIX."individuals WHERE i_gedcom ";
		if (!empty($pid)) $sql .= "LIKE '% ASSO @$ipid@%'";
		else $sql .= "LIKE '% ASSO %'";
		$res = dbquery($sql);

		$ct = $res->numRows();
		while($row =& $res->fetchRow(DB_FETCHMODE_ASSOC)){
			$asso = array();
			$asso["type"] = "indi";
			$pid2 = $row["i_id"]."[".$row["i_file"]."]";
			$asso["gedcom"] = $row["i_gedcom"];
			$asso["gedfile"] = $row["i_file"];
			$asso["name"] = get_indi_names($row["i_gedcom"]);
			$ca = preg_match_all("/\d ASSO @(.*)@/", $row["i_gedcom"], $match, PREG_SET_ORDER);
			for ($i=0; $i<$ca; $i++) {
				$pid = $match[$i][1]."[".$row["i_file"]."]";
				$assolist[$pid][$pid2] = $asso;
			}
			$row = db_cleanup($row);
		}
		$res->free();
	}

	$GEDCOM = $oldged;

	$ASSOLIST_RETRIEVED = true;
	return $assolist;
}

//-- get the famlist from the datastore
function get_fam_list() {
	global $famlist, $GEDCOM, $DBCONN, $GEDCOMS;
	global $TBLPREFIX, $FAMLIST_RETRIEVED;

	if ($FAMLIST_RETRIEVED) return $famlist;
	$famlist = array();
	$sql = "SELECT * FROM ".$TBLPREFIX."families WHERE f_file='".$DBCONN->escapeSimple($GEDCOMS[$GEDCOM]["id"])."'";
	$res = dbquery($sql);

	$ct = $res->numRows();
	while($row =& $res->fetchRow(DB_FETCHMODE_ASSOC)){
		$fam = array();
		$fam["gedcom"] = $row["f_gedcom"];
		$row = db_cleanup($row);
		$hname = get_sortable_name($row["f_husb"]);
		$wname = get_sortable_name($row["f_wife"]);
		$name = "";
		if (!empty($hname)) $name = $hname;
		else $name = "@N.N., @P.N.";

		if (!empty($wname)) $name .= " + ".$wname;
		else $name .= " + @N.N., @P.N.";

		$fam["name"] = $name;
		$fam["HUSB"] = $row["f_husb"];
		$fam["WIFE"] = $row["f_wife"];
		$fam["CHIL"] = $row["f_chil"];
		$fam["gedfile"] = $row["f_file"];
		$fam["numchil"] = $row["f_numchil"];
		$famlist[$row["f_id"]] = $fam;
	}
	$res->free();
	$FAMLIST_RETRIEVED = true;
	return $famlist;
}

//-- get the otherlist from the datastore
function get_other_list() {
	global $otherlist, $GEDCOM, $DBCONN, $GEDCOMS;
	global $TBLPREFIX;

	$otherlist = array();

	$sql = "SELECT * FROM ".$TBLPREFIX."other WHERE o_file='".$DBCONN->escapeSimple($GEDCOMS[$GEDCOM]["id"])."'";
	$res = dbquery($sql);

	$ct = $res->numRows();
	while($row =& $res->fetchRow(DB_FETCHMODE_ASSOC)){
		$source = array();
		$source["gedcom"] = $row["o_gedcom"];
		$row = db_cleanup($row);
		$source["type"] = $row["o_type"];
		$source["gedfile"] = $row["o_file"];
		$otherlist[$row["o_id"]]= $source;
	}
	$res->free();
	return $otherlist;
}

//-- search through the gedcom records for individuals
/**
 * Search the database for individuals that match the query
 *
 * uses a regular expression to search the gedcom records of all individuals and returns an
 * array list of the matching individuals
 *
 * @author	yalnifj
 * @param	string $query a regular expression query to search for
 * @param	boolean $allgeds setting if all gedcoms should be searched, default is false
 * @return	array $myindilist array with all individuals that matched the query
 */
function search_indis($query, $allgeds=false, $ANDOR="AND") {
	global $TBLPREFIX, $GEDCOM, $indilist, $DBCONN, $DBTYPE, $GEDCOMS;
	$myindilist = array();
	if (stristr($DBTYPE, "mysql")!==false) $term = "REGEXP";
	else if (stristr($DBTYPE, "pgsql")!==false) $term = "~*";
	else $term = "LIKE";
	//-- if the query is a string
	if (!is_array($query)) {
		$sql = "SELECT i_id, i_name, i_file, i_gedcom, i_isdead, i_letter, i_surname FROM ".$TBLPREFIX."individuals WHERE (";
		//-- make sure that MySQL matches the upper and lower case utf8 characters
		if (has_utf8($query)) $sql .= "i_gedcom $term '".$DBCONN->escapeSimple(str2upper($query))."' OR i_gedcom $term '".$DBCONN->escapeSimple(str2lower($query))."')";
		else $sql .= "i_gedcom $term '".$DBCONN->escapeSimple($query)."')";
	}
	//-- create a more complicated query if it is an array
	else {
		$sql = "SELECT i_id, i_name, i_file, i_gedcom, i_isdead, i_letter, i_surname FROM ".$TBLPREFIX."individuals WHERE (";
		$i=0;
		foreach($query as $indexval => $q) {
			if ($i>0) $sql .= " $ANDOR ";
			if (has_utf8($q)) $sql .= "(i_gedcom $term '".$DBCONN->escapeSimple(str2upper($q))."' OR i_gedcom $term '".$DBCONN->escapeSimple(str2lower($q))."')";
			else $sql .= "(i_gedcom $term '".$DBCONN->escapeSimple($q)."')";
			$i++;
		}
		$sql .= ")";
	}
	if (!$allgeds) $sql .= " AND i_file='".$DBCONN->escapeSimple($GEDCOMS[$GEDCOM]["id"])."'";

	if ((is_array($allgeds)) && (count($allgeds) != 0)) {
		$sql .= " AND (";
		for ($i=0; $i<count($allgeds); $i++) {
			$sql .= "i_file='".$DBCONN->escapeSimple($GEDCOMS[$allgeds[$i]]["id"])."'";
			if ($i < count($allgeds)-1) $sql .= " OR ";
		}
		$sql .= ")";
	}
	//print $sql;
	$res = dbquery($sql, false);

	$gedold = $GEDCOM;
	if (!DB::isError($res)) {
		while($row =& $res->fetchRow()){
			$row = db_cleanup($row);
			if (count($allgeds) > 1) {
				$myindilist[$row[0]."[".$row[2]."]"]["names"] = get_indi_names($row[3]);
				$myindilist[$row[0]."[".$row[2]."]"]["gedfile"] = $row[2];
				$myindilist[$row[0]."[".$row[2]."]"]["gedcom"] = $row[3];
				$myindilist[$row[0]."[".$row[2]."]"]["isdead"] = $row[4];
				if (!isset($indilist[$row[0]]) && $row[2]==$GEDCOMS[$gedold]['id']) $indilist[$row[0]] = $myindilist[$row[0]."[".$row[2]."]"];
			}
			else {
				$myindilist[$row[0]]["names"] = get_indi_names($row[3]);
				$myindilist[$row[0]]["gedfile"] = $row[2];
				$myindilist[$row[0]]["gedcom"] = $row[3];
				$myindilist[$row[0]]["isdead"] = $row[4];
				if (!isset($indilist[$row[0]]) && $row[2]==$GEDCOMS[$gedold]['id']) $indilist[$row[0]] = $myindilist[$row[0]];
			}
		}
		$res->free();
	}
	return $myindilist;
}

//-- search through the gedcom records for individuals in families
function search_indis_fam($add2myindilist) {
	global $TBLPREFIX, $indilist, $myindilist;

	$sql = "SELECT i_id, i_name, i_file, i_gedcom, i_isdead, i_letter, i_surname FROM ".$TBLPREFIX."individuals";
	$res = dbquery($sql);

	while($row =& $res->fetchRow()){
		if (isset($add2myindilist[$row[0]])){
			$add2my_fam=$add2myindilist[$row[0]];
			$row = db_cleanup($row);
			$myindilist[$row[0]]["names"] = get_indi_names($row[3]);
			$myindilist[$row[0]]["gedfile"] = $row[2];
			$myindilist[$row[0]]["gedcom"] = $row[3].$add2my_fam;
			$myindilist[$row[0]]["isdead"] = $row[4];
			$indilist[$row[0]] = $myindilist[$row[0]];
			if (isset($indilist[$row[0]]['privacy'])) unset($indilist[$row[0]]['privacy']);
		}
	}
	$res->free();
	return $myindilist;
}

/**
 * Search for individuals who had dates within the given year ranges
 * @param int $startyear	the starting year
 * @param int $endyear		The ending year
 * @return array
 */
function search_indis_year_range($startyear, $endyear, $allgeds=false) {
	global $TBLPREFIX, $GEDCOM, $indilist, $DBCONN, $GEDCOMS;

	$myindilist = array();
	$sql = "SELECT i_id, i_name, i_file, i_gedcom, i_isdead, i_letter, i_surname FROM ".$TBLPREFIX."individuals, ".$TBLPREFIX."dates WHERE i_id=d_gid AND i_file=d_file AND d_fact NOT IN('CHAN', 'BAPL', 'SLGC', 'SLGS', 'ENDL') AND ";
	$sql .= "d_year >= ".$startyear." AND d_year<=".$endyear;

	if (!$allgeds) $sql .= " AND i_file='".$DBCONN->escapeSimple($GEDCOMS[$GEDCOM]["id"])."'";
	$res = dbquery($sql);

	if (!DB::isError($res)) {
		while($row =& $res->fetchRow()){
			$row = db_cleanup($row);
			$myindilist[$row[0]]["names"] = get_indi_names($row[3]);
			$myindilist[$row[0]]["gedfile"] = $row[2];
			$myindilist[$row[0]]["gedcom"] = $row[3];
			$myindilist[$row[0]]["isdead"] = $row[4];
			$indilist[$row[0]] = $myindilist[$row[0]];
		}
		$res->free();
	}
	return $myindilist;
}


//-- search through the gedcom records for individuals
function search_indis_names($query, $allgeds=false) {
	global $TBLPREFIX, $GEDCOM, $indilist, $DBCONN, $REGEXP_DB, $DBTYPE, $GEDCOMS;

	if (stristr($DBTYPE, "mysql")!==false) $term = "REGEXP";
	else if (stristr($DBTYPE, "pgsql")!==false) $term = "~*";
	else $term='LIKE';

	//-- split up words and find them anywhere in the record... important for searching names
	//-- like "givenname surname"
	if (!is_array($query)) {
		$query = preg_split("/[\s,]+/", $query);
		if (!$REGEXP_DB) {
			for($i=0; $i<count($query); $i++){
				$query[$i] = "%".$query[$i]."%";
			}
		}
	}

	$myindilist = array();
	if (empty($query)) $sql = "SELECT i_id, i_name, i_file, i_gedcom, i_isdead, i_letter, i_surname FROM ".$TBLPREFIX."individuals";
	else if (!is_array($query)) $sql = "SELECT i_id, i_name, i_file, i_gedcom, i_isdead, i_letter, i_surname FROM ".$TBLPREFIX."individuals WHERE i_name $term '".$DBCONN->escapeSimple($query)."'";
	else {
		$sql = "SELECT i_id, i_name, i_file, i_gedcom, i_isdead, i_letter, i_surname FROM ".$TBLPREFIX."individuals WHERE (";
		$i=0;
		foreach($query as $indexval => $q) {
			if (!empty($q)) {
				if ($i>0) $sql .= " AND ";
				$sql .= "i_name $term '".$DBCONN->escapeSimple($q)."'";
				$i++;
			}
		}
		$sql .= ")";
	}
	if (!$allgeds) $sql .= " AND i_file='".$DBCONN->escapeSimple($GEDCOMS[$GEDCOM]["id"])."'";
	$res = dbquery($sql, false);
	if (!DB::isError($res)) {
		while($row = $res->fetchRow()){
			$row = db_cleanup($row);
			if ($allgeds) $key = $row[0]."[".$row[2]."]";
			else $key = $row[0];
			if (isset($indilist[$key])) $myindilist[$key] = $indilist[$key];
			else {
				$myindilist[$key]["names"] = get_indi_names($row[3]);
				$myindilist[$key]["gedfile"] = $row[2];
				$myindilist[$key]["gedcom"] = $row[3];
				$myindilist[$key]["isdead"] = $row[4];
				$indilist[$key] = $myindilist[$key];
			}
		}
		$res->free();
	}

	//-- search the names table too
	if (!is_array($query)) $sql = "SELECT i_id, i_name, i_file, i_gedcom, i_isdead, i_letter, i_surname FROM ".$TBLPREFIX."individuals, ".$TBLPREFIX."names WHERE i_id=n_gid AND i_file=n_file AND n_name $term '".$DBCONN->escapeSimple($query)."'";
	else {
		$sql = "SELECT i_id, i_name, i_file, i_gedcom, i_isdead, i_letter, i_surname FROM ".$TBLPREFIX."individuals, ".$TBLPREFIX."names WHERE i_id=n_gid AND i_file=n_file AND (";
		$i=0;
		foreach($query as $indexval => $q) {
			if (!empty($q)) {
				if ($i>0) $sql .= " AND ";
				$sql .= "n_name $term '".$DBCONN->escapeSimple($q)."'";
				$i++;
			}
		}
		$sql .= ")";
	}
	if (!$allgeds) $sql .= " AND i_file='".$DBCONN->escapeSimple($GEDCOMS[$GEDCOM]["id"])."'";
	$res = dbquery($sql, false);

	if (!DB::isError($res)) {
		while($row = $res->fetchRow()){
			$row = db_cleanup($row);
			if ($allgeds) $key = $row[0]."[".$row[2]."]";
			else $key = $row[0];
			if (!isset($myindilist[$key])) {
				if (isset($indilist[$key])) $myindilist[$key] = $indilist[$key];
				else {
					$myindilist[$key]["names"] = get_indi_names($row[3]);
					$myindilist[$key]["gedfile"] = $row[2];
					$myindilist[$key]["gedcom"] = $row[3];
					$myindilist[$key]["isdead"] = $row[4];
					$indilist[$key] = $myindilist[$key];
				}
			}
		}
		$res->free();
	}
	return $myindilist;
}

/**
 * get recent changes since the given date inclusive
 * @author	yalnifj
 * @param	int $day the day of the month to search for, leave empty to include all
 * @param	int $mon the integer value for the month to search for, leave empty to include all
 * @param	int $year the year to search for, leave empty to include all
 */
function get_recent_changes($day="", $mon="", $year="", $allgeds=false) {
	global $TBLPREFIX, $GEDCOM, $DBCONN, $GEDCOMS;

	$changes = array();
	while(strlen($year)<4) $year ='0'.$year;
	while(strlen($mon)<2) $mon ='0'.$mon;
	while(strlen($day)<2) $day ='0'.$day;
	$datestamp = $year.$mon.$day;
	$sql = "SELECT * FROM ".$TBLPREFIX."dates WHERE d_fact='CHAN' AND d_datestamp>=".$datestamp;
	if (!$allgeds) $sql .= " AND d_file='".$DBCONN->escapeSimple($GEDCOMS[$GEDCOM]["id"])."' ";
	$sql .= " ORDER BY d_datestamp DESC";
	//print $sql;
	$res = dbquery($sql);

	if (!DB::isError($res)) {
		while($row =& $res->fetchRow(DB_FETCHMODE_ASSOC)){
			if (preg_match("/\w+:\w+/", $row['d_gid'])==0) {
				$changes[] = $row;
			}
		}
	}
	return $changes;
}

/**
 * Search the dates table for individuals that had events on the given day
 *
 * @author	yalnifj
 * @param	int $day the day of the month to search for, leave empty to include all
 * @param	string $month the 3 letter abbr. of the month to search for, leave empty to include all
 * @param	int $year the year to search for, leave empty to include all
 * @param	string $fact the facts to include (use a comma seperated list to include multiple facts)
 * 				prepend the fact with a ! to not include that fact
 * @param	boolean $allgeds setting if all gedcoms should be searched, default is false
 * @return	array $myindilist array with all individuals that matched the query
 */
function search_indis_dates($day="", $month="", $year="", $fact="", $allgeds=false, $ANDOR="AND") {
	global $TBLPREFIX, $GEDCOM, $indilist, $DBCONN, $GEDCOMS;
	$myindilist = array();
	
	$sql = "SELECT i_id, i_name, i_file, i_gedcom, i_isdead, i_letter, i_surname, d_gid, d_fact FROM ".$TBLPREFIX."dates, ".$TBLPREFIX."individuals WHERE i_id=d_gid AND i_file=d_file ";
	if (!empty($day)) $sql .= "AND d_day='".$DBCONN->escapeSimple($day)."' ";
	if (!empty($month)) $sql .= "AND d_month='".$DBCONN->escapeSimple(str2upper($month))."' ";
	if (!empty($year)) $sql .= "AND d_year='".$DBCONN->escapeSimple($year)."' ";
	if (!empty($fact)) {
		$sql .= "AND (";
		$facts = preg_split("/[,:; ]/", $fact);
		$i=0;
		foreach($facts as $fact) {
			if ($i!=0) $sql .= " OR ";
			$ct = preg_match("/!(\w+)/", $fact, $match);
			if ($ct > 0) {
				$fact = $match[1];
				$sql .= "d_fact!='".$DBCONN->escapeSimple(str2upper($fact))."'";
			}
			else {
				$sql .= "d_fact='".$DBCONN->escapeSimple(str2upper($fact))."'";
			}
			$i++;
		}
		$sql .= ") ";
	}
	if (!$allgeds) $sql .= "AND d_file='".$DBCONN->escapeSimple($GEDCOMS[$GEDCOM]["id"])."' ";
	$sql .= "ORDER BY d_year DESC, d_mon DESC, d_day DESC";
//  print $sql; 
	$res = dbquery($sql);

	if (!DB::isError($res)) {
		while($row =& $res->fetchRow()){
			$row = db_cleanup($row);
			if ($allgeds) {
				$myindilist[$row[0]."[".$row[2]."]"]["names"] = get_indi_names($row[3]);
				$myindilist[$row[0]."[".$row[2]."]"]["gedfile"] = $row[2];
				$myindilist[$row[0]."[".$row[2]."]"]["gedcom"] = $row[3];
				$myindilist[$row[0]."[".$row[2]."]"]["isdead"] = $row[4];
				if ($myindilist[$row[0]."[".$row[2]."]"]["gedfile"] == $GEDCOMS[$GEDCOM]['id']) $indilist[$row[0]] = $myindilist[$row[0]."[".$row[2]."]"];
			}
			else {
				$myindilist[$row[0]]["names"] = get_indi_names($row[3]);
				$myindilist[$row[0]]["gedfile"] = $row[2];
				$myindilist[$row[0]]["gedcom"] = $row[3];
				$myindilist[$row[0]]["isdead"] = $row[4];
				if ($myindilist[$row[0]]["gedfile"] == $GEDCOMS[$GEDCOM]['id']) $indilist[$row[0]] = $myindilist[$row[0]];
			}
		}
		$res->free();
	}
	return $myindilist;
}

/**
 * Search the dates table for individuals that had events in the given range
 *
 * @author	yalnifj
 * @param	int $day the day of the month to search for, leave empty to include all
 * @param	string $month the 3 letter abbr. of the month to search for, leave empty to include all
 * @param	int $year the year to search for, leave empty to include all
 * @param	string $fact the facts to include (use a comma seperated list to include multiple facts)
 * 				prepend the fact with a ! to not include that fact
 * @param	boolean $allgeds setting if all gedcoms should be searched, default is false
 * @return	array $myindilist array with all individuals that matched the query
 */
function search_indis_daterange($start, $end, $fact='', $allgeds=false, $ANDOR="AND") {
	global $TBLPREFIX, $GEDCOM, $indilist, $DBCONN, $GEDCOMS;
	$myindilist = array();
	
	$sql = "SELECT i_id, i_name, i_file, i_gedcom, i_isdead, i_letter, i_surname, d_gid, d_fact FROM ".$TBLPREFIX."dates, ".$TBLPREFIX."individuals WHERE i_id=d_gid AND i_file=d_file AND d_type is null ";
//	if (!empty($day)) $sql .= "AND d_day='".$DBCONN->escapeSimple($day)."' ";
//	if (!empty($month)) $sql .= "AND d_month='".$DBCONN->escapeSimple(str2upper($month))."' ";
//	if (!empty($year)) $sql .= "AND d_year='".$DBCONN->escapeSimple($year)."' ";
	$mod = 1;
	for($i = strlen($start); $i>0; $i--) $mod=$mod*10;
	if ($mod==100000000) $sql .= "AND d_datestamp>=".$start." AND d_datestamp<=".$end." ";
	else $sql .= "AND (d_datestamp%$mod)>=".$start." AND (d_datestamp%$mod)<=".$end." ";
	if (!empty($fact)) {
		$sql .= "AND (";
		$facts = preg_split("/[,:; ]/", $fact);
		$i=0;
		foreach($facts as $fact) {
			if ($i!=0) $sql .= " OR ";
			$ct = preg_match("/!(\w+)/", $fact, $match);
			if ($ct > 0) {
				$fact = $match[1];
				$sql .= "d_fact!='".$DBCONN->escapeSimple(str2upper($fact))."'";
			}
			else {
				$sql .= "d_fact='".$DBCONN->escapeSimple(str2upper($fact))."'";
			}
			$i++;
		}
		$sql .= ") ";
	}
	if (!$allgeds) $sql .= "AND d_file='".$DBCONN->escapeSimple($GEDCOMS[$GEDCOM]["id"])."' ";
	$sql .= "ORDER BY d_year DESC, d_mon DESC, d_day DESC";
//	print $sql;
	$res = dbquery($sql);

	if (!DB::isError($res)) {
		while($row =& $res->fetchRow()){
			$row = db_cleanup($row);
			if ($allgeds) {
				if (!isset($myindilist[$row[0]."[".$row[2]."]"])) {
					$myindilist[$row[0]."[".$row[2]."]"]["names"] = get_indi_names($row[3]);
					$myindilist[$row[0]."[".$row[2]."]"]["gedfile"] = $row[2];
					$myindilist[$row[0]."[".$row[2]."]"]["gedcom"] = $row[3];
					$myindilist[$row[0]."[".$row[2]."]"]["isdead"] = $row[4];
					if ($myindilist[$row[0]."[".$row[2]."]"]["gedfile"] == $GEDCOMS[$GEDCOM]['id']) $indilist[$row[0]] = $myindilist[$row[0]."[".$row[2]."]"];
				}
			}
			else {
				if (!isset($myindilist[$row[0]])) {
					$myindilist[$row[0]]["names"] = get_indi_names($row[3]);
					$myindilist[$row[0]]["gedfile"] = $row[2];
					$myindilist[$row[0]]["gedcom"] = $row[3];
					$myindilist[$row[0]]["isdead"] = $row[4];
					if ($myindilist[$row[0]]["gedfile"] == $GEDCOMS[$GEDCOM]['id']) $indilist[$row[0]] = $myindilist[$row[0]];
				}
			}
		}
		$res->free();
	}
	return $myindilist;
}

//-- search through the gedcom records for families
function search_fams($query, $allgeds=false, $ANDOR="AND", $allnames=false) {
	global $TBLPREFIX, $GEDCOM, $famlist, $DBCONN, $DBTYPE, $GEDCOMS;
	if (stristr($DBTYPE, "mysql")!==false) $term = "REGEXP";
	else if (stristr($DBTYPE, "pgsql")!==false) $term = "~*";
	else $term='LIKE';
	$myfamlist = array();
	if (!is_array($query)) $sql = "SELECT f_id, f_husb, f_wife, f_file, f_gedcom, f_numchil FROM ".$TBLPREFIX."families WHERE (f_gedcom $term '".$DBCONN->escapeSimple($query)."')";
	else {
		$sql = "SELECT f_id, f_husb, f_wife, f_file, f_gedcom, f_numchil FROM ".$TBLPREFIX."families WHERE (";
		$i=0;
		foreach($query as $indexval => $q) {
			if ($i>0) $sql .= " $ANDOR ";
			$sql .= "(f_gedcom $term '".$DBCONN->escapeSimple($q)."')";
			$i++;
		}
		$sql .= ")";
	}

	if (!$allgeds) $sql .= " AND f_file='".$DBCONN->escapeSimple($GEDCOMS[$GEDCOM]["id"])."'";

	if ((is_array($allgeds)) && (count($allgeds) != 0)) {
		$sql .= " AND (";
		for ($i=0, $max=count($allgeds); $i<$max; $i++) {
			$sql .= "f_file='".$DBCONN->escapeSimple($GEDCOMS[$allgeds[$i]]["id"])."'";
			if ($i < $max-1) $sql .= " OR ";
		}
		$sql .= ")";
	}

	$res = dbquery($sql, false);

	$gedold = $GEDCOM;
	if (!DB::isError($res)) {
		while($row =& $res->fetchRow()){
			$row = db_cleanup($row);
			$GEDCOM = get_gedcom_from_id($row[3]);
			if ($allnames == true) {
				$hname = get_sortable_name($row[1], "", "", true);
				$wname = get_sortable_name($row[2], "", "", true);
				if (empty($hname)) $hname = "@N.N.";
				if (empty($wname)) $wname = "@N.N.";
				$name = array();
				foreach ($hname as $hkey => $hn) {
					foreach ($wname as $wkey => $wn) {
						$name[] = $hn." + ".$wn;
						$name[] = $wn." + ".$hn;
					}
				}
			}
			else {
				$hname = get_sortable_name($row[1]);
				$wname = get_sortable_name($row[2]);
				if (empty($hname)) $hname = "@N.N.";
				if (empty($wname)) $wname = "@N.N.";
				$name = $hname." + ".$wname;
			}
			if (count($allgeds) > 1) {
				$myfamlist[$row[0]."[".$row[3]."]"]["name"] = $name;
				$myfamlist[$row[0]."[".$row[3]."]"]["gedfile"] = $row[3];
				$myfamlist[$row[0]."[".$row[3]."]"]["gedcom"] = $row[4];
				$myfamlist[$row[0]."[".$row[3]."]"]["numchil"] = $row[5];
				if (!isset($famlist[$row[0]]) && $row[3]==$GEDCOMS[$gedold]['id']) $famlist[$row[0]] = $myfamlist[$row[0]."[".$row[3]."]"];
			}
			else {
				$myfamlist[$row[0]]["name"] = $name;
				$myfamlist[$row[0]]["gedfile"] = $row[3];
				$myfamlist[$row[0]]["gedcom"] = $row[4];
	//			$myfamlist[$row[0]]["gedcom"] = $row[5];
				$myfamlist[$row[0]]["numchil"] = $row[5];
				if (!isset($famlist[$row[0]]) && $row[3]==$GEDCOMS[$gedold]['id']) $famlist[$row[0]] = $myfamlist[$row[0]];
			}
		}
		$GEDCOM = $gedold;
		$res->free();
	}
	return $myfamlist;
}

//-- search through the gedcom records for families
function search_fams_names($query, $ANDOR="AND", $allnames=false, $gedcnt=1) {
	global $TBLPREFIX, $GEDCOM, $famlist, $DBCONN;
	$myfamlist = array();
	$sql = "SELECT f_id, f_husb, f_wife, f_file, f_gedcom, f_numchil FROM ".$TBLPREFIX."families WHERE (";
	$i=0;
	foreach($query as $indexval => $q) {
		if ($i>0) $sql .= " $ANDOR ";
		$sql .= "((f_husb='".$DBCONN->escapeSimple($q[0])."' OR f_wife='".$DBCONN->escapeSimple($q[0])."') AND f_file='".$DBCONN->escapeSimple($q[1])."')";
		$i++;
	}
	$sql .= ")";

	$res = dbquery($sql);

	if (!DB::isError($res)) {
		$gedold = $GEDCOM;
		while($row =& $res->fetchRow()){
			$row = db_cleanup($row);
			$GEDCOM = get_gedcom_from_id($row[3]);
			if ($allnames == true) {
				$hname = get_sortable_name($row[1], "", "", true);
				$wname = get_sortable_name($row[2], "", "", true);
				if (empty($hname)) $hname = "@N.N.";
				if (empty($wname)) $wname = "@N.N.";
				$name = array();
				foreach ($hname as $hkey => $hn) {
					foreach ($wname as $wkey => $wn) {
						$name[] = $hn." + ".$wn;
						$name[] = $wn." + ".$hn;
					}
				}
			}
			else {
				$hname = get_sortable_name($row[1]);
				$wname = get_sortable_name($row[2]);
				if (empty($hname)) $hname = "@N.N.";
				if (empty($wname)) $wname = "@N.N.";
				$name = $hname." + ".$wname;
			}
			if ($gedcnt > 1) {
				$myfamlist[$row[0]."[".$row[3]."]"]["name"] = $name;
				$myfamlist[$row[0]."[".$row[3]."]"]["gedfile"] = $row[3];
				$myfamlist[$row[0]."[".$row[3]."]"]["gedcom"] = $row[4];
				$myfamlist[$row[0]."[".$row[3]."]"]["numchil"] = $row[5];
				$famlist[$row[0]] = $myfamlist[$row[0]."[".$row[3]."]"];
			}
			else {
				$myfamlist[$row[0]]["name"] = $name;
				$myfamlist[$row[0]]["gedfile"] = $row[3];
				$myfamlist[$row[0]]["gedcom"] = $row[4];
				$myfamlist[$row[0]]["numchil"] = $row[5];
				$famlist[$row[0]] = $myfamlist[$row[0]];
			}
		}
		$GEDCOM = $gedold;
		$res->free();
	}
	return $myfamlist;
}

/**
 * Search the families table for individuals are part of that family
 * either as a husband, wife or child.
 *
 * @author	roland-d
 * @param	string $query the query to search for as a single string
 * @param	array $query the query to search for as an array
 * @param	boolean $allgeds setting if all gedcoms should be searched, default is false
 * @param	string $ANDOR setting if the sql query should be constructed with AND or OR
 * @param	boolean $allnames true returns all names in an array
 * @return	array $myfamlist array with all families that matched the query
 */
function search_fams_members($query, $allgeds=false, $ANDOR="AND", $allnames=false) {
	global $TBLPREFIX, $GEDCOM, $famlist, $DBCONN, $GEDCOMS;
	$myfamlist = array();
	if (!is_array($query)) $sql = "SELECT f_id, f_husb, f_wife, f_file FROM ".$TBLPREFIX."families WHERE (f_husb='$query' OR f_wife='$query' OR f_chil LIKE '%$query;%')";
	else {
		$sql = "SELECT f_id, f_husb, f_wife, f_file FROM ".$TBLPREFIX."families WHERE (";
		$i=0;
		foreach($query as $indexval => $q) {
			if ($i>0) $sql .= " $ANDOR ";
			$sql .= "(f_husb='$query' OR f_wife='$query' OR f_chil LIKE '%$query;%')";
			$i++;
		}
		$sql .= ")";
	}

	if (!$allgeds) $sql .= " AND f_file='".$DBCONN->escapeSimple($GEDCOMS[$GEDCOM]["id"])."'";

	if ((is_array($allgeds)) && (count($allgeds) != 0)) {
		$sql .= " AND (";
		for ($i=0, $max=count($allgeds); $i<$max; $i++) {
			$sql .= "f_file='".$DBCONN->escapeSimple($GEDCOMS[$allgeds[$i]]["id"])."'";
			if ($i < $max-1) $sql .= " OR ";
		}
		$sql .= ")";
	}
	$res = dbquery($sql);

	$i=0;
	while($row =& $res->fetchRow()){
		$row = db_cleanup($row);
		if ($allnames == true) {
			$hname = get_sortable_name($row[1], "", "", true);
			$wname = get_sortable_name($row[2], "", "", true);
			if (empty($hname)) $hname = "@N.N.";
			if (empty($wname)) $wname = "@N.N.";
			$name = array();
			foreach ($hname as $hkey => $hn) {
				foreach ($wname as $wkey => $wn) {
					$name[] = $hn." + ".$wn;
					$name[] = $wn." + ".$hn;
				}
			}
		}
		else {
			$hname = get_sortable_name($row[1]);
			$wname = get_sortable_name($row[2]);
			if (empty($hname)) $hname = "@N.N.";
			if (empty($wname)) $wname = "@N.N.";
			$name = $hname." + ".$wname;
		}
		if (count($allgeds) > 1) {
			$myfamlist[$i]["name"] = $name;
			$myfamlist[$i]["gedfile"] = $row[0];
			$myfamlist[$i]["gedcom"] = $row[1];
			$famlist[] = $myfamlist;
		}
		else {
			$myfamlist[$i][] = $name;
			$myfamlist[$i][] = $row[0];
			$myfamlist[$i][] = $row[3];
			$i++;
			$famlist[] = $myfamlist;
		}
	}
	$res->free();
	return $myfamlist;
}

//-- search through the gedcom records for families with daterange
function search_fams_year_range($startyear, $endyear, $allgeds=false) {
	global $TBLPREFIX, $GEDCOM, $famlist, $DBCONN, $GEDCOMS;

	$myfamlist = array();
	$sql = "SELECT f_id, f_husb, f_wife, f_file, f_gedcom FROM ".$TBLPREFIX."families, ".$TBLPREFIX."dates WHERE f_id=d_gid AND f_file=d_file AND d_fact NOT IN('CHAN', 'BAPL', 'SLGC', 'SLGS', 'ENDL') AND ";
	$sql .= "d_year >= ".$startyear." AND d_year<=".$endyear;
	if (!$allgeds) $sql .= " AND f_file='".$DBCONN->escapeSimple($GEDCOMS[$GEDCOM]["id"])."'";
	$res = dbquery($sql);

	if (!DB::isError($res)) {
		while($row =& $res->fetchRow()){
			$row = db_cleanup($row);
			$hname = get_sortable_name($row[1]);
			$wname = get_sortable_name($row[2]);
			if (empty($hname)) $hname = "@N.N.";
			if (empty($wname)) $wname = "@N.N.";
			$name = $hname." + ".$wname;
			$myfamlist[$row[0]]["name"] = $name;
			$myfamlist[$row[0]]["gedfile"] = $row[3];
			$myfamlist[$row[0]]["gedcom"] = $row[4];
			$famlist[$row[0]] = $myfamlist[$row[0]];
		}
		$res->free();
	}
	return $myfamlist;
}

/**
 * Search the dates table for families that had events on the given day
 *
 * @author	yalnifj
 * @param	int $day the day of the month to search for, leave empty to include all
 * @param	string $month the 3 letter abbr. of the month to search for, leave empty to include all
 * @param	int $year the year to search for, leave empty to include all
 * @param	string $fact the facts to include (use a comma seperated list to include multiple facts)
 * 				prepend the fact with a ! to not include that fact
 * @param	boolean $allgeds setting if all gedcoms should be searched, default is false
 * @return	array $myfamlist array with all individuals that matched the query
 */
function search_fams_dates($day="", $month="", $year="", $fact="", $allgeds=false) {
	global $TBLPREFIX, $GEDCOM, $famlist, $DBCONN, $GEDCOM, $GEDCOMS;
	$myfamlist = array();

	$sql = "SELECT f_id, f_husb, f_wife, f_file, f_gedcom, d_gid, d_fact FROM ".$TBLPREFIX."dates, ".$TBLPREFIX."families WHERE f_id=d_gid AND f_file=d_file ";
	if (!empty($day)) $sql .= "AND d_day='".$DBCONN->escapeSimple($day)."' ";
	if (!empty($month)) $sql .= "AND d_month='".$DBCONN->escapeSimple(str2upper($month))."' ";
	if (!empty($year)) $sql .= "AND d_year='".$DBCONN->escapeSimple($year)."' ";
	if (!empty($fact)) {
		$sql .= "AND (";
		$facts = preg_split("/[,:; ]/", $fact);
		$i=0;
		foreach($facts as $fact) {
			if ($i!=0) $sql .= " OR ";
			$ct = preg_match("/!(\w+)/", $fact, $match);
			if ($ct > 0) {
				$fact = $match[1];
				$sql .= "d_fact!='".$DBCONN->escapeSimple(str2upper($fact))."'";
			}
			else {
				$sql .= "d_fact='".$DBCONN->escapeSimple(str2upper($fact))."'";
			}
			$i++;
		}
		$sql .= ") ";
	}
	if (!$allgeds) $sql .= "AND d_file='".$DBCONN->escapeSimple($GEDCOMS[$GEDCOM]["id"])."' ";
	$sql .= "ORDER BY d_year, d_month, d_day DESC";

	$res = dbquery($sql);

	if (!DB::isError($res)) {
		$gedold = $GEDCOM;
		while($row =& $res->fetchRow()){
			$row = db_cleanup($row);
			$GEDCOM = get_gedcom_from_id($row[3]);
			$hname = get_sortable_name($row[1]);
			$wname = get_sortable_name($row[2]);
			if (empty($hname)) $hname = "@N.N.";
			if (empty($wname)) $wname = "@N.N.";
			$name = $hname." + ".$wname;
			if ($allgeds) {
				$myfamlist[$row[0]."[".$row[3]."]"]["name"] = $name;
				$myfamlist[$row[0]."[".$row[3]."]"]["gedfile"] = $row[3];
				$myfamlist[$row[0]."[".$row[3]."]"]["gedcom"] = $row[4];
				$famlist[$row[0]] = $myfamlist[$row[0]."[".$row[3]."]"];
			}
			else {
				$myfamlist[$row[0]]["name"] = $name;
				$myfamlist[$row[0]]["gedfile"] = $row[3];
				$myfamlist[$row[0]]["gedcom"] = $row[4];
				$famlist[$row[0]] = $myfamlist[$row[0]];
			}
		}
		$GEDCOM = $gedold;
		$res->free();
	}
	return $myfamlist;
}

/**
 * Search the dates table for families that had events in the date range
 *
 * @author	yalnifj
 * @param	int $day the day of the month to search for, leave empty to include all
 * @param	string $month the 3 letter abbr. of the month to search for, leave empty to include all
 * @param	int $year the year to search for, leave empty to include all
 * @param	string $fact the facts to include (use a comma seperated list to include multiple facts)
 * 				prepend the fact with a ! to not include that fact
 * @param	boolean $allgeds setting if all gedcoms should be searched, default is false
 * @return	array $myfamlist array with all individuals that matched the query
 */
function search_fams_daterange($start, $end, $fact="", $allgeds=false) {
	global $TBLPREFIX, $GEDCOM, $famlist, $DBCONN, $GEDCOM, $GEDCOMS;
	$myfamlist = array();

	$sql = "SELECT f_id, f_husb, f_wife, f_file, f_gedcom, d_gid, d_fact FROM ".$TBLPREFIX."dates, ".$TBLPREFIX."families WHERE f_id=d_gid AND f_file=d_file ";
//	if (!empty($day)) $sql .= "AND d_day='".$DBCONN->escapeSimple($day)."' ";
//	if (!empty($month)) $sql .= "AND d_month='".$DBCONN->escapeSimple(str2upper($month))."' ";
//	if (!empty($year)) $sql .= "AND d_year='".$DBCONN->escapeSimple($year)."' ";
	$mod = 1;
	for($i = strlen($start); $i>0; $i--) $mod=$mod*10;
	if ($mod==100000000) $sql .= "AND d_datestamp>=".$start." AND d_datestamp<=".$end." ";
	else $sql .= "AND (d_datestamp%$mod)>=".$start." AND (d_datestamp%$mod)<=".$end." ";
	if (!empty($fact)) {
		$sql .= "AND (";
		$facts = preg_split("/[,:; ]/", $fact);
		$i=0;
		foreach($facts as $fact) {
			if ($i!=0) $sql .= " OR ";
			$ct = preg_match("/!(\w+)/", $fact, $match);
			if ($ct > 0) {
				$fact = $match[1];
				$sql .= "d_fact!='".$DBCONN->escapeSimple(str2upper($fact))."'";
			}
			else {
				$sql .= "d_fact='".$DBCONN->escapeSimple(str2upper($fact))."'";
			}
			$i++;
		}
		$sql .= ") ";
	}
	if (!$allgeds) $sql .= "AND d_file='".$DBCONN->escapeSimple($GEDCOMS[$GEDCOM]["id"])."' ";
	$sql .= "ORDER BY d_year, d_month, d_day DESC";

	$res = dbquery($sql);

	if (!DB::isError($res)) {
		$people = array();
		$gedold = $GEDCOM;
		while($row =& $res->fetchRow()){
			$row = db_cleanup($row);
			$GEDCOM = get_gedcom_from_id($row[3]);
	//		$hname = get_sortable_name($row[1]);
	//		$wname = get_sortable_name($row[2]);
	//		if (empty($hname)) $hname = "@N.N.";
	//		if (empty($wname)) $wname = "@N.N.";
	//		$name = $hname." + ".$wname;
			if ($allgeds) {
	//			$myfamlist[$row[0]."[".$row[3]."]"]["name"] = $name;
				$myfamlist[$row[0]."[".$row[3]."]"]["gedfile"] = $row[3];
				$myfamlist[$row[0]."[".$row[3]."]"]["gedcom"] = $row[4];
				$famlist[$row[0]] = $myfamlist[$row[0]."[".$row[3]."]"];
			}
			else {
	//			$myfamlist[$row[0]]["name"] = $name;
				$people[] = $row[1];
				$people[] = $row[2];
				$myfamlist[$row[0]]["gedfile"] = $row[3];
				$myfamlist[$row[0]]["gedcom"] = $row[4];
				$famlist[$row[0]] = $myfamlist[$row[0]];
			}
		}
		$GEDCOM = $gedold;
		$res->free();
		load_people($people);
	}
	return $myfamlist;
}

//-- search through the gedcom records for sources
function search_sources($query, $allgeds=false, $ANDOR="AND") {
	global $TBLPREFIX, $GEDCOM, $DBCONN, $DBTYPE, $GEDCOMS;
	$mysourcelist = array();
	if (stristr($DBTYPE, "mysql")!==false) $term = "REGEXP";
	else if (stristr($DBTYPE, "pgsql")!==false) $term = "~*";
	else $term='LIKE';
	if (!is_array($query)) {
		$sql = "SELECT s_id, s_name, s_file, s_gedcom FROM ".$TBLPREFIX."sources WHERE ";
		//-- make sure that MySQL matches the upper and lower case utf8 characters
		if (has_utf8($query)) $sql .= "(s_gedcom $term '".$DBCONN->escapeSimple(str2upper($query))."' OR s_gedcom $term '".$DBCONN->escapeSimple(str2lower($query))."')";
		else $sql .= "s_gedcom $term '".$DBCONN->escapeSimple($query)."'";
	}
	else {
		$sql = "SELECT s_id, s_name, s_file, s_gedcom FROM ".$TBLPREFIX."sources WHERE (";
		$i=0;
		foreach($query as $indexval => $q) {
			if ($i>0) $sql .= " $ANDOR ";
			if (has_utf8($q)) $sql .= "(s_gedcom $term '".$DBCONN->escapeSimple(str2upper($q))."' OR s_gedcom $term '".$DBCONN->escapeSimple(str2lower($q))."')";
			else $sql .= "(s_gedcom $term '".$DBCONN->escapeSimple($q)."')";
			$i++;
		}
		$sql .= ")";
	}
	if (!$allgeds) $sql .= " AND s_file='".$DBCONN->escapeSimple($GEDCOMS[$GEDCOM]["id"])."'";

	if ((is_array($allgeds)) && (count($allgeds) != 0)) {
		$sql .= " AND (";
		for ($i=0; $i<count($allgeds); $i++) {
			$sql .= "s_file='".$DBCONN->escapeSimple($GEDCOMS[$allgeds[$i]]["id"])."'";
			if ($i < count($allgeds)-1) $sql .= " OR ";
		}
		$sql .= ")";
	}

	$res = dbquery($sql, false);

	if (!DB::isError($res)) {
		while($row =& $res->fetchRow()){
			$row = db_cleanup($row);
			if (count($allgeds) > 1) {
				$mysourcelist[$row[0]."[".$row[2]."]"]["name"] = $row[1];
				$mysourcelist[$row[0]."[".$row[2]."]"]["gedfile"] = $row[2];
				$mysourcelist[$row[0]."[".$row[2]."]"]["gedcom"] = $row[3];
			}
			else {
				$mysourcelist[$row[0]]["name"] = $row[1];
				$mysourcelist[$row[0]]["gedfile"] = $row[2];
				$mysourcelist[$row[0]]["gedcom"] = $row[3];
			}
		}
		$res->free();
	}
	return $mysourcelist;
}

/**
 * Search the dates table for sources that had events on the given day
 *
 * @author	yalnifj
 * @param	boolean $allgeds setting if all gedcoms should be searched, default is false
 * @return	array $myfamlist array with all individuals that matched the query
 */
function search_sources_dates($day="", $month="", $year="", $fact="", $allgeds=false) {
	global $TBLPREFIX, $GEDCOM, $DBCONN, $GEDCOMS;
	$mysourcelist = array();

	$sql = "SELECT s_id, s_name, s_file, s_gedcom, d_gid FROM ".$TBLPREFIX."dates, ".$TBLPREFIX."sources WHERE s_id=d_gid AND s_file=d_file ";
	if (!empty($day)) $sql .= "AND d_day='".$DBCONN->escapeSimple($day)."' ";
	if (!empty($month)) $sql .= "AND d_month='".$DBCONN->escapeSimple(str2upper($month))."' ";
	if (!empty($year)) $sql .= "AND d_year='".$DBCONN->escapeSimple($year)."' ";
	if (!empty($fact)) $sql .= "AND d_fact='".$DBCONN->escapeSimple(str2upper($fact))."' ";
	if (!$allgeds) $sql .= "AND d_file='".$DBCONN->escapeSimple($GEDCOMS[$GEDCOM]["id"])."' ";
	$sql .= "GROUP BY s_id ORDER BY d_year, d_month, d_day DESC";

	$res = dbquery($sql);

	if (!DB::isError($res)) {
		$gedold = $GEDCOM;
		while($row =& $res->fetchRow()){
			$row = db_cleanup($row);
			if ($allgeds) {
				$mysourcelist[$row[0]."[".$row[2]."]"]["name"] = $row[1];
				$mysourcelist[$row[0]."[".$row[2]."]"]["gedfile"] = $row[2];
				$mysourcelist[$row[0]."[".$row[2]."]"]["gedcom"] = $row[3];
			}
			else {
				$mysourcelist[$row[0]]["name"] = $row[1];
				$mysourcelist[$row[0]]["gedfile"] = $row[2];
				$mysourcelist[$row[0]]["gedcom"] = $row[3];
			}
		}
		$GEDCOM = $gedold;
	}
	$res->free();
	return $mysourcelist;
}

//-- search through the gedcom records for sources
function search_other($query, $allgeds=false, $type="", $ANDOR="AND") {
	global $TBLPREFIX, $GEDCOM, $DBCONN, $DBTYPE, $GEDCOMS;
	$mysourcelist = array();
	if (stristr($DBTYPE, "mysql")!==false) $term = "REGEXP";
	else if (stristr($DBTYPE, "pgsql")!==false) $term = "~*";
	else $term='LIKE';
	if (!is_array($query)) {
		$sql = "SELECT o_id, o_type, o_file, o_gedcom FROM ".$TBLPREFIX."other WHERE ";
		//-- make sure that MySQL matches the upper and lower case utf8 characters
		if (has_utf8($query)) $sql .= "(o_gedcom $term '".$DBCONN->escapeSimple(str2upper($query))."' OR o_gedcom $term '".$DBCONN->escapeSimple(str2lower($query))."')";
		else $sql .= "o_gedcom $term '".$DBCONN->escapeSimple($query)."'";
	}
	else {
		$sql = "SELECT o_id, o_type, o_file, o_gedcom FROM ".$TBLPREFIX."other WHERE (";
		$i=0;
		foreach($query as $indexval => $q) {
			if ($i>0) $sql .= " $ANDOR ";
			if (has_utf8($q)) $sql .= "(o_gedcom $term '".$DBCONN->escapeSimple(str2upper($q))."' OR o_gedcom $term '".$DBCONN->escapeSimple(str2lower($q))."')";
			else $sql .= "(o_gedcom $term '".$DBCONN->escapeSimple($q)."')";
			$i++;
		}
		$sql .= ")";
	}
	if (!$allgeds) $sql .= " AND o_file='".$DBCONN->escapeSimple($GEDCOMS[$GEDCOM]["id"])."'";

	if ((is_array($allgeds)) && (count($allgeds) != 0)) {
		$sql .= " AND (";
		for ($i=0; $i<count($allgeds); $i++) {
			$sql .= "o_file='".$DBCONN->escapeSimple($GEDCOMS[$allgeds[$i]]["id"])."'";
			if ($i < count($allgeds)-1) $sql .= " OR ";
		}
		$sql .= ")";
	}

	$res = dbquery($sql, false);

	if (!DB::isError($res)) {
		while($row =& $res->fetchRow()){
			$row = db_cleanup($row);
			if (count($allgeds) > 1) {
				$mysourcelist[$row[0]."[".$row[2]."]"]["type"] = $row[1];
				$mysourcelist[$row[0]."[".$row[2]."]"]["gedfile"] = $row[2];
				$mysourcelist[$row[0]."[".$row[2]."]"]["gedcom"] = $row[3];
			}
			else {
				$mysourcelist[$row[0]]["type"] = $row[1];
				$mysourcelist[$row[0]]["gedfile"] = $row[2];
				$mysourcelist[$row[0]]["gedcom"] = $row[3];
			}
		}
		$res->free();
	}
	return $mysourcelist;
}

/**
 * Search the dates table for other records that had events on the given day
 *
 * @author	yalnifj
 * @param	boolean $allgeds setting if all gedcoms should be searched, default is false
 * @return	array $myfamlist array with all individuals that matched the query
 */
function search_other_dates($day="", $month="", $year="", $fact="", $allgeds=false) {
	global $TBLPREFIX, $GEDCOM, $DBCONN, $GEDCOMS;
	$myrepolist = array();

	$sql = "SELECT o_id, o_file, o_type, o_gedcom, d_gid FROM ".$TBLPREFIX."dates, ".$TBLPREFIX."other WHERE o_id=d_gid AND o_file=d_file ";
	if (!empty($day)) $sql .= "AND d_day='".$DBCONN->escapeSimple($day)."' ";
	if (!empty($month)) $sql .= "AND d_month='".$DBCONN->escapeSimple(str2upper($month))."' ";
	if (!empty($year)) $sql .= "AND d_year='".$DBCONN->escapeSimple($year)."' ";
	if (!empty($fact)) $sql .= "AND d_fact='".$DBCONN->escapeSimple(str2upper($fact))."' ";
	if (!$allgeds) $sql .= "AND d_file='".$DBCONN->escapeSimple($GEDCOMS[$GEDCOM]["id"])."' ";
	$sql .= "GROUP BY o_id ORDER BY d_year, d_month, d_day DESC";

	$res = dbquery($sql);

	if (!DB::isError($res)) {
		$gedold = $GEDCOM;
		while($row =& $res->fetchRow()){
			$row = db_cleanup($row);
			$tt = preg_match("/1 NAME (.*)/", $row[2], $match);
			if ($tt == "0") $name = $row[0]; else $name = $match[1];
			if ($allgeds) {
				$myrepolist[$row[0]."[".$row[1]."]"]["name"] = $name;
				$myrepolist[$row[0]."[".$row[1]."]"]["gedfile"] = $row[1];
				$myrepolist[$row[0]."[".$row[1]."]"]["type"] = $row[2];
				$myrepolist[$row[0]."[".$row[1]."]"]["gedcom"] = $row[3];
			}
			else {
				$myrepolist[$row[0]]["name"] = $name;
				$myrepolist[$row[0]]["gedfile"] = $row[1];
				$myrepolist[$row[0]]["type"] = $row[2];
				$myrepolist[$row[0]]["gedcom"] = $row[3];
			}
		}
		$GEDCOM = $gedold;
		$res->free();
	}
	return $myrepolist;
}

/**
 * get place parent ID
 * @param array $parent
 * @param int $level
 * @return int
 */
function get_place_parent_id($parent, $level) {
	global $DBCONN, $TBLPREFIX, $GEDCOM, $GEDCOMS;

	$parent_id=0;
	for($i=0; $i<$level; $i++) {
		$escparent=preg_replace("/\?/","\\\\\\?", $DBCONN->escapeSimple($parent[$i]));
		$psql = "SELECT p_id FROM ".$TBLPREFIX."places WHERE p_level=".$i." AND p_parent_id=$parent_id AND p_place LIKE '".$escparent."' AND p_file='".$DBCONN->escapeSimple($GEDCOMS[$GEDCOM]["id"])."' ORDER BY p_place";
		$res = dbquery($psql);
		$row =& $res->fetchRow();
		$res->free();
		if (empty($row[0])) break;
		$parent_id = $row[0];
	}
	return $parent_id;
}

/**
 * find all of the places in the hierarchy
 * The $parent array holds the parent hierarchy of the places
 * we want to get.  The level holds the level in the hierarchy that
 * we are at.
 */
function get_place_list() {
	global $numfound, $level, $parent;
	global $GEDCOM, $TBLPREFIX, $placelist, $DBCONN, $GEDCOMS;

	// --- find all of the place in the file
	if ($level==0) $sql = "SELECT p_place FROM ".$TBLPREFIX."places WHERE p_level=0 AND p_file='".$DBCONN->escapeSimple($GEDCOMS[$GEDCOM]["id"])."' ORDER BY p_place";
	else {
		$parent_id = get_place_parent_id($parent, $level);
		$sql = "SELECT p_place FROM ".$TBLPREFIX."places WHERE p_level=$level AND p_parent_id=$parent_id AND p_file='".$DBCONN->escapeSimple($GEDCOMS[$GEDCOM]["id"])."' ORDER BY p_place";
	}
	$res = dbquery($sql);

	while ($row =& $res->fetchRow()) {
		$placelist[] = $row[0];
		$numfound++;
	}
	$res->free();
}

/**
 * get all of the place connections
 * @param array $parent
 * @param int $level
 * @return array
 */
function get_place_positions($parent, $level='') {
	global $positions, $TBLPREFIX, $GEDCOM, $DBCONN, $GEDCOMS;

	if ($level!='') $p_id = get_place_parent_id($parent, $level);
	else {
		//-- we don't know the level so get the any matching place
		$sql = "SELECT DISTINCT pl_gid FROM ".$TBLPREFIX."placelinks, ".$TBLPREFIX."places WHERE p_place LIKE '".$DBCONN->escapeSimple($parent)."' AND p_file=pl_file AND p_id=pl_p_id AND p_file='".$DBCONN->escapeSimple($GEDCOMS[$GEDCOM]["id"])."'";
		//print $sql;
		$res = dbquery($sql);
		while ($row =& $res->fetchRow()) {
			$positions[] = $row[0];
		}
		$res->free();
		return $positions;
	}
	$sql = "SELECT DISTINCT pl_gid FROM ".$TBLPREFIX."placelinks WHERE pl_p_id=$p_id AND pl_file='".$DBCONN->escapeSimple($GEDCOMS[$GEDCOM]["id"])."'";
	$res = dbquery($sql);

	while ($row =& $res->fetchRow()) {
		$positions[] = $row[0];
	}
	$res->free();
	return $positions;
}

//-- find all of the places
function find_place_list($place) {
	global $GEDCOM, $TBLPREFIX, $placelist, $DBCONN, $GEDCOMS;

	$sql = "SELECT p_id, p_place, p_parent_id  FROM ".$TBLPREFIX."places WHERE p_file='".$DBCONN->escapeSimple($GEDCOMS[$GEDCOM]["id"])."' ORDER BY p_parent_id, p_id";
	$res = dbquery($sql);

	while($row =& $res->fetchRow()) {
		if ($row[2]==0) $placelist[$row[0]] = $row[1];
		else {
			$placelist[$row[0]] = $placelist[$row[2]].", ".$row[1];
		}
	}
	if (!empty($place)) {
		$found = array();
		foreach($placelist as $indexval => $pplace) {
			if (preg_match("/$place/i", $pplace)>0) {
				$upperplace = str2upper($pplace);
				if (!isset($found[$upperplace])) {
					$found[$upperplace] = $pplace;
				}
			}
		}
		$placelist = array_values($found);
	}
}

//-- find all of the media
function get_media_list() {
	global $GEDCOM, $TBLPREFIX, $medialist, $ct, $GEDCOMS, $MEDIA_DIRECTORY;
	global $GEDCOM_ID_PREFIX, $FAM_ID_PREFIX, $SOURCE_ID_PREFIX;
	$ct = 0;
	if (!isset($medialinks)) $medialinks = array();
	$sqlmm = "SELECT mm_gid, mm_media FROM ".$TBLPREFIX."media_mapping WHERE mm_gedfile = '".$GEDCOMS[$GEDCOM]["id"]."' ORDER BY mm_id ASC";
	$resmm =@ dbquery($sqlmm);
	while($rowmm =& $resmm->fetchRow(DB_FETCHMODE_ASSOC)){
		$sqlm = "SELECT * FROM ".$TBLPREFIX."media WHERE m_media = '".$rowmm["mm_media"]."' AND m_gedfile = '".$GEDCOMS[$GEDCOM]["id"]."'";
		$resm =@ dbquery($sqlm);
		while($rowm =& $resm->fetchRow(DB_FETCHMODE_ASSOC)){
			$filename = check_media_depth($rowm["m_file"], "NOTRUNC");
			$thumbnail = str_replace($MEDIA_DIRECTORY, $MEDIA_DIRECTORY."thumbs/", $filename);
			$title = $rowm["m_titl"];
			$mediarec = $rowm["m_gedrec"];
			$level = $mediarec{0};
			$isprim="N";
			$isthumb="N";
			$pt = preg_match("/\d _PRIM (.*)/", $mediarec, $match);
			if ($pt>0) $isprim = trim($match[1]);
			$pt = preg_match("/\d _THUM (.*)/", $mediarec, $match);
			if ($pt>0) $isthumb = trim($match[1]);
			$linkid = trim($rowmm["mm_gid"]);
			switch ($linkid{0}) {
				case $GEDCOM_ID_PREFIX:
					$type = "INDI";
					break;
				case $FAM_ID_PREFIX:
					$type = "FAM";
					break;
				case $SOURCE_ID_PREFIX:
					$type = "SOUR";
					break;
			}
			$medialinks[$ct][$linkid] = $type;
			$links = $medialinks[$ct];
			if (!isset($foundlist[$filename])) {
				$media = array();
				$media["file"] = $filename;
				$media["thumb"] = $thumbnail;
				$media["title"] = $title;
				$media["gedcom"] = $mediarec;
				$media["level"] = $level;
				$media["THUM"] = $isthumb;
				$media["PRIM"] = $isprim;
				$medialist[$ct]=$media;
				$foundlist[$filename] = $ct;
				$ct++;
			}
			$medialist[$foundlist[$filename]]["link"]=$links;
		}
	}
}

/**
 * get all first letters of individual's last names
 * @see indilist.php
 * @return array	an array of all letters
 */
function get_indi_alpha() {
	global $TBLPREFIX, $GEDCOM, $LANGUAGE, $SHOW_MARRIED_NAMES, $DBCONN, $GEDCOMS;
	global $MULTI_LETTER_ALPHABET;
	global $DICTIONARY_SORT, $UCDiacritWhole, $UCDiacritStrip, $LCDiacritWhole, $LCDiacritStrip;

	$indialpha = array();

	$danishex = array("OE", "AE", "AA");
	$danishFrom = array("AA", "AE", "OE");
	$danishTo = array("Å", "Æ", "Ø");
	// Force danish letters in the top list [ 1579889 ]
	if ($LANGUAGE=="danish" || $LANGUAGE=="norwegian") foreach ($danishTo as $k=>$v) $indialpha[$v] = $v;

	$sql = "SELECT DISTINCT i_letter AS alpha FROM ".$TBLPREFIX."individuals WHERE i_file='".$DBCONN->escapeSimple($GEDCOMS[$GEDCOM]["id"])."' ORDER BY alpha";
	$res = dbquery($sql);

	while($row =& $res->fetchRow(DB_FETCHMODE_ASSOC)){
		$letter = str2upper($row["alpha"]);
		if ($LANGUAGE=="danish" || $LANGUAGE=="norwegian") $letter = str_replace($danishFrom, $danishTo, $letter);
		$inArray = strpos($MULTI_LETTER_ALPHABET[$LANGUAGE], " ".$letter." ");
		if ($inArray===false) {
			if ((ord(substr($letter, 0, 1)) & 0x80)==0x00) $letter = substr($letter, 0, 1);
		}
		if ($DICTIONARY_SORT[$LANGUAGE]) {
			$position = strpos($UCDiacritWhole, $letter);
			if ($position!==false) {
				$position = $position >> 1;
				$letter = substr($UCDiacritStrip, $position, 1);
			} else {
				$position = strpos($LCDiacritWhole, $letter);
				if ($position!==false) {
					$position = $position >> 1;
					$letter = substr($LCDiacritStrip, $position, 1);
				}
			}
		}
		$indialpha[$letter] = $letter;
	}
	$res->free();

	$sql = "SELECT DISTINCT n_letter AS alpha FROM ".$TBLPREFIX."names WHERE n_file='".$DBCONN->escapeSimple($GEDCOMS[$GEDCOM]["id"])."'";
	if (!$SHOW_MARRIED_NAMES) $sql .= " AND n_type!='C'";
	$sql .= " ORDER BY alpha";
	$res = dbquery($sql);

	while($row =& $res->fetchRow(DB_FETCHMODE_ASSOC)){
		$letter = str2upper($row["alpha"]);
		if ($LANGUAGE=="danish" || $LANGUAGE=="norwegian") $letter = str_replace($danishFrom, $danishTo, $letter);
		$inArray = strpos($MULTI_LETTER_ALPHABET[$LANGUAGE], " ".$letter." ");
		if ($inArray===false) {
			if ((ord(substr($letter, 0, 1)) & 0x80)==0x00) $letter = substr($letter, 0, 1);
		}
		if ($DICTIONARY_SORT[$LANGUAGE]) {
			$position = strpos($UCDiacritWhole, $letter);
			if ($position!==false) {
				$position = $position >> 1;
				$letter = substr($UCDiacritStrip, $position, 1);
			} else {
				$position = strpos($LCDiacritWhole, $letter);
				if ($position!==false) {
					$position = $position >> 1;
					$letter = substr($LCDiacritStrip, $position, 1);
				}
			}
		}
		$indialpha[$letter] = $letter;
	}
	$res->free();

	return $indialpha;
}

//-- get the first character in the list
function get_fam_alpha() {
	global $TBLPREFIX, $GEDCOM, $LANGUAGE, $famalpha, $DBCONN, $GEDCOMS;
	global $MULTI_LETTER_ALPHABET;
	global $DICTIONARY_SORT, $UCDiacritWhole, $UCDiacritStrip, $LCDiacritWhole, $LCDiacritStrip;

	$famalpha = array();

	$danishex = array("OE", "AE", "AA");
	$danishFrom = array("AA", "AE", "OE");
	$danishTo = array("Å", "Æ", "Ø");
	// Force danish letters in the top list [ 1579889 ]
	if ($LANGUAGE=="danish" || $LANGUAGE=="norwegian") foreach ($danishTo as $k=>$v) $famalpha[$v] = $v;

	$sql = "SELECT DISTINCT i_letter AS alpha FROM ".$TBLPREFIX."individuals WHERE i_file='".$DBCONN->escapeSimple($GEDCOMS[$GEDCOM]["id"])."' AND i_gedcom LIKE '%1 FAMS%' ORDER BY alpha";
	$res = dbquery($sql);

	while($row =& $res->fetchRow(DB_FETCHMODE_ASSOC)){
		$letter = str2upper($row["alpha"]);
		if ($LANGUAGE=="danish" || $LANGUAGE=="norwegian") $letter = str_replace($danishFrom, $danishTo, $letter);
		$inArray = strpos($MULTI_LETTER_ALPHABET[$LANGUAGE], " ".$letter." ");
		if ($inArray===false) {
			if ((ord(substr($letter, 0, 1)) & 0x80)==0x00) $letter = substr($letter, 0, 1);
		}
		if ($DICTIONARY_SORT[$LANGUAGE]) {
			$position = strpos($UCDiacritWhole, $letter);
			if ($position!==false) {
				$position = $position >> 1;
				$letter = substr($UCDiacritStrip, $position, 1);
			} else {
				$position = strpos($LCDiacritWhole, $letter);
				if ($position!==false) {
					$position = $position >> 1;
					$letter = substr($LCDiacritStrip, $position, 1);
				}
			}
		}
		$famalpha[$letter] = $letter;
	}
	$res->free();

	$sql = "SELECT DISTINCT n_letter AS alpha FROM ".$TBLPREFIX."names, ".$TBLPREFIX."individuals WHERE i_file=n_file AND i_id=n_gid AND n_file='".$DBCONN->escapeSimple($GEDCOMS[$GEDCOM]["id"])."' AND i_gedcom LIKE '%1 FAMS%' ORDER BY alpha";
	$res = dbquery($sql);

	while($row =& $res->fetchRow(DB_FETCHMODE_ASSOC)){
		$letter = str2upper($row["alpha"]);
		if ($LANGUAGE=="danish" || $LANGUAGE=="norwegian") $letter = str_replace($danishFrom, $danishTo, $letter);
		$inArray = strpos($MULTI_LETTER_ALPHABET[$LANGUAGE], " ".$letter." ");
		if ($inArray===false) {
			if ((ord(substr($letter, 0, 1)) & 0x80)==0x00) $letter = substr($letter, 0, 1);
		}
		if ($DICTIONARY_SORT[$LANGUAGE]) {
			$position = strpos($UCDiacritWhole, $letter);
			if ($position!==false) {
				$position = $position >> 1;
				$letter = substr($UCDiacritStrip, $position, 1);
			} else {
				$position = strpos($LCDiacritWhole, $letter);
				if ($position!==false) {
					$position = $position >> 1;
					$letter = substr($LCDiacritStrip, $position, 1);
				}
			}
		}
		$famalpha[$letter] = $letter;
	}
	$res->free();

	$sql = "SELECT f_id FROM ".$TBLPREFIX."families WHERE f_file='".$DBCONN->escapeSimple($GEDCOMS[$GEDCOM]["id"])."' AND (f_husb='' OR f_wife='')";
	$res = dbquery($sql);

	if ($res->numRows()>0) {
		$famalpha["@"] = "@";
	}
	$res->free();

	return $famalpha;
}

/**
 * Get Individuals Starting with a letter
 *
 * This function finds all of the individuals who start with the given letter
 * @param string $letter	The letter to search on
 * @return array	$indilist array
 * @see http://www.phpgedview.net/devdocs/arrays.php#indilist
 */
function get_alpha_indis($letter) {
	global $TBLPREFIX, $GEDCOM, $LANGUAGE, $indilist, $surname, $SHOW_MARRIED_NAMES, $DBCONN, $GEDCOMS;
	global $MULTI_LETTER_ALPHABET;
	global $DICTIONARY_SORT, $UCDiacritWhole, $UCDiacritStrip, $LCDiacritWhole, $LCDiacritStrip;

	$tindilist = array();

	if ($letter=='_') $letter='\_';
	if ($letter=='%') $letter='\%';
	if ($letter=='') $letter='@';

	$danishex = array("OE", "AE", "AA");
	$danishFrom = array("AA", "AE", "OE");
	$danishTo = array("Å", "Æ", "Ø");

	$checkDictSort = true;

	$sql = "SELECT * FROM ".$TBLPREFIX."individuals WHERE ";
	if ($LANGUAGE == "danish" || $LANGUAGE == "norwegian") {
		if ($letter == "Ø") $text = "OE";
		else if ($letter == "Æ") $text = "AE";
		else if ($letter == "Å") $text = "AA";
//	[ 1579889 ]
//	if (isset($text)) $sql .= "(i_letter = '".$DBCONN->escapeSimple($letter)."' OR i_letter = '".$DBCONN->escapeSimple($text)."') ";
		if (isset($text)) $sql .= "(i_letter = '".$DBCONN->escapeSimple($letter)."' OR i_name LIKE '%/".$DBCONN->escapeSimple($text)."%') ";
		else if ($letter=="A") $sql .= "i_letter LIKE '".$DBCONN->escapeSimple($letter)."' ";
		else $sql .= "i_letter LIKE '".$DBCONN->escapeSimple($letter)."%' ";
		$checkDictSort = false;
	} else if ($MULTI_LETTER_ALPHABET[$LANGUAGE]!="") {
		$isMultiLetter = strpos($MULTI_LETTER_ALPHABET[$LANGUAGE], " ".$letter." ");
		if ($isMultiLetter!==false) {
			$sql .= "i_letter = '".$DBCONN->escapeSimple($letter)."' ";
			$checkDictSort = false;
		}
	}
	if ($checkDictSort) {
		$text = "";
		if ($DICTIONARY_SORT[$LANGUAGE]) {
			$inArray = strpos($UCDiacritStrip, $letter);
			if ($inArray!==false) {
				while (true) {
					$text .= " OR i_letter = '".$DBCONN->escapeSimple(substr($UCDiacritWhole, ($inArray+$inArray), 2))."'";
					$inArray ++;
					if ($inArray > strlen($UCDiacritStrip)) break;
					if (substr($UCDiacritStrip, $inArray, 1)!=$letter) break;
				}
				if ($MULTI_LETTER_ALPHABET[$LANGUAGE]=="") $sql .= "(i_letter LIKE '".$DBCONN->escapeSimple($letter)."%'".$text.") ";
				else $sql .= "(i_letter = '".$DBCONN->escapeSimple($letter)."'".$text.") ";
			} else {
				$inArray = strpos($LCDiacritStrip, $letter);
				if ($inArray!==false) {
					while (true) {
						$text .= " OR i_letter = '".$DBCONN->escapeSimple(substr($LCDiacritWhole, ($inArray+$inArray), 2))."'";
						$inArray ++;
						if ($inArray > strlen($LCDiacritStrip)) break;
						if (substr($LCDiacritStrip, $inArray, 1)!=$letter) break;
					}
					if ($MULTI_LETTER_ALPHABET[$LANGUAGE]=="") $sql .= "(i_letter LIKE '".$DBCONN->escapeSimple($letter)."%'".$text.") ";
					else $sql .= "(i_letter = '".$DBCONN->escapeSimple($letter)."'".$text.") ";
				}
			}
		}
		if ($text=="") {
			if ($MULTI_LETTER_ALPHABET[$LANGUAGE]=="") $sql .= "i_letter LIKE '".$DBCONN->escapeSimple($letter)."%'";
			else $sql .= "i_letter = '".$DBCONN->escapeSimple($letter)."'";
		}
	}

	//-- add some optimization if the surname is set to speed up the lists
	if (!empty($surname)) $sql .= "AND i_surname LIKE '%".$DBCONN->escapeSimple($surname)."%' ";
	$sql .= "AND i_file='".$DBCONN->escapeSimple($GEDCOMS[$GEDCOM]["id"])."' ORDER BY i_name";
	$res = dbquery($sql);
	if (!DB::isError($res)) {
		while($row =& $res->fetchRow(DB_FETCHMODE_ASSOC)){
			$row = db_cleanup($row);
			//if (substr($row["i_letter"], 0, 1)==substr($letter, 0, 1)||(isset($text)?substr($row["i_letter"], 0, 1)==substr($text, 0, 1):FALSE)){
				$indi = array();
				$indi["names"] = array(array($row["i_name"], $row["i_letter"], $row["i_surname"], 'P'));
				$indi["isdead"] = $row["i_isdead"];
				$indi["gedcom"] = $row["i_gedcom"];
				$indi["gedfile"] = $row["i_file"];
				$tindilist[$row["i_id"]] = $indi;
				//-- cache the item in the $indilist for improved speed
				$indilist[$row["i_id"]] = $indi;
			//}
		}
		$res->free();
	}

	$checkDictSort = true;

	$sql = "SELECT i_id, i_name, i_file, i_isdead, i_gedcom, i_letter, i_surname, n_letter, n_name, n_surname, n_letter, n_type FROM ".$TBLPREFIX."individuals, ".$TBLPREFIX."names WHERE i_id=n_gid AND i_file=n_file AND ";
	if ($LANGUAGE == "danish" || $LANGUAGE == "norwegian") {
		if ($letter == "Ø") $text = "OE";
		else if ($letter == "Æ") $text = "AE";
		else if ($letter == "Å") $text = "AA";
		if (isset($text)) $sql .= "(n_letter = '".$DBCONN->escapeSimple($letter)."' OR n_letter = '".$DBCONN->escapeSimple($text)."') ";
		else if ($letter=="A") $sql .= "n_letter LIKE '".$DBCONN->escapeSimple($letter)."' ";
		else $sql .= "n_letter LIKE '".$DBCONN->escapeSimple($letter)."%' ";
		$checkDictSort = false;
	} else if ($MULTI_LETTER_ALPHABET[$LANGUAGE]!="") {
		$isMultiLetter = strpos($MULTI_LETTER_ALPHABET[$LANGUAGE], " ".$letter." ");
		if ($isMultiLetter!==false) {
			$sql .= "n_letter = '".$DBCONN->escapeSimple($letter)."' ";
			$checkDictSort = false;
		}
	}
	if ($checkDictSort) {
		$text = "";
		if ($DICTIONARY_SORT[$LANGUAGE]) {
			$inArray = strpos($UCDiacritStrip, $letter);
			if ($inArray!==false) {
				while (true) {
					$text .= " OR n_letter = '".$DBCONN->escapeSimple(substr($UCDiacritWhole, ($inArray+$inArray), 2))."'";
					$inArray ++;
					if ($inArray > strlen($UCDiacritStrip)) break;
					if (substr($UCDiacritStrip, $inArray, 1)!=$letter) break;
				}
				if ($MULTI_LETTER_ALPHABET[$LANGUAGE]=="") $sql .= "(n_letter LIKE '".$DBCONN->escapeSimple($letter)."%'".$text.")";
				else $sql .= "(n_letter = '".$DBCONN->escapeSimple($letter)."'".$text.")";
			} else {
				$inArray = strpos($LCDiacritStrip, $letter);
				if ($inArray!==false) {
					while (true) {
						$text .= " OR n_letter = '".$DBCONN->escapeSimple(substr($LCDiacritWhole, ($inArray+$inArray), 2))."'";
						$inArray ++;
						if ($inArray > strlen($LCDiacritStrip)) break;
						if (substr($LCDiacritStrip, $inArray, 1)!=$letter) break;
					}
					if ($MULTI_LETTER_ALPHABET[$LANGUAGE]=="") $sql .= "(n_letter LIKE '".$DBCONN->escapeSimple($letter)."%'".$text.")";
					else $sql .= "(n_letter = '".$DBCONN->escapeSimple($letter)."'".$text.")";
				}
			}
		}
		if ($text=="") {
			if ($MULTI_LETTER_ALPHABET[$LANGUAGE]=="") $sql .= "n_letter LIKE '".$DBCONN->escapeSimple($letter)."%'";
			else $sql .= "n_letter = '".$DBCONN->escapeSimple($letter)."'";
		}
	}
	//-- add some optimization if the surname is set to speed up the lists
	if (!empty($surname)) $sql .= "AND n_surname LIKE '%".$DBCONN->escapeSimple($surname)."%' ";
	if (!$SHOW_MARRIED_NAMES) $sql .= "AND n_type!='C' ";
	$sql .= "AND i_file='".$DBCONN->escapeSimple($GEDCOMS[$GEDCOM]["id"])."' ORDER BY i_name";
	$res = dbquery($sql);
	if (!DB::isError($res)) {
	while($row =& $res->fetchRow(DB_FETCHMODE_ASSOC)){
		$row = db_cleanup($row);
		//if (substr($row["n_letter"], 0, strlen($letter))==$letter||(isset($text)?substr($row["n_letter"], 0, strlen($text))==$text:FALSE)){
			if (!isset($indilist[$row["i_id"]])) {
				$indi = array();
				$indi["names"] = array(array($row["i_name"], $row["i_letter"], $row["i_surname"], "P"), array($row["n_name"], $row["n_letter"], $row["n_surname"], $row["n_type"]));
				$indi["isdead"] = $row["i_isdead"];
				$indi["gedcom"] = $row["i_gedcom"];
				$indi["gedfile"] = $row["i_file"];
				//-- cache the item in the $indilist for improved speed
				$indilist[$row["i_id"]] = $indi;
				$tindilist[$row["i_id"]] = $indilist[$row["i_id"]];
			}
			else {
				// do not add to the array an indi name that already exists in it
				if (!in_array(array($row["n_name"], $row["n_letter"], $row["n_surname"], $row["n_type"]), $indilist[$row["i_id"]]["names"])) {
				    $indilist[$row["i_id"]]["names"][] = array($row["n_name"], $row["n_letter"], $row["n_surname"], $row["n_type"]);
			    }
				$tindilist[$row["i_id"]] = $indilist[$row["i_id"]];
			}
		//}
	}
	$res->free();
	}

	return $tindilist;
}

/**
 * Get Individuals with a given surname
 *
 * This function finds all of the individuals who have the given surname
 * @param string $surname	The surname to search on
 * @return array	$indilist array
 * @see http://www.phpgedview.net/devdocs/arrays.php#indilist
 */
function get_surname_indis($surname) {
	global $TBLPREFIX, $GEDCOM, $indilist, $SHOW_MARRIED_NAMES, $DBCONN, $GEDCOMS;
	$tindilist = array();
	$sql = "SELECT i_id, i_isdead, i_file, i_gedcom, i_name, i_letter, i_surname FROM ".$TBLPREFIX."individuals WHERE i_surname LIKE '".$DBCONN->escapeSimple($surname)."' ";
	$sql .= "AND i_file='".$DBCONN->escapeSimple($GEDCOMS[$GEDCOM]["id"])."'";
	$sql .= " ORDER BY i_surname";
	$res = dbquery($sql);

	while($row =& $res->fetchRow(DB_FETCHMODE_ASSOC)) {
		$row = db_cleanup($row);
		$indi = array();
		$indi["names"] = array(array($row["i_name"], $row["i_letter"], $row["i_surname"], "P"));
		$indi["isdead"] = $row["i_isdead"];
		$indi["gedcom"] = $row["i_gedcom"];
		$indi["gedfile"] = $row["i_file"];
		$indi["numchil"] = "";
		$indilist[$row["i_id"]] = $indi;
		$tindilist[$row["i_id"]] = $indilist[$row["i_id"]];
	}
	$res->free();
	
	// Get the number of children for each individual
	$sqlHusb = "";
	$sqlWife = "";
	foreach($tindilist as $gid => $indi) {
		$sqlHusb .= "f_husb = '".$gid."' OR ";
		$sqlWife .= "f_wife = '".$gid."' OR ";
	}
	// Look for all individuals recorded as partner #1 in a family.  
	// Because of same-sex partnerships, we can't depend on male persons being recorded 
	// as the "father" in the family.  
	// We'll do separate "father" and "mother" searches to allow better use of indexes.
	if ($sqlHusb) {
		$sql = "SELECT f_husb, f_wife, f_numchil FROM ".$TBLPREFIX."families WHERE (";
		$sql .= substr($sqlHusb, 0, -4);		// get rid of final " OR "
		$sql .= ") AND f_file='".$DBCONN->escapeSimple($GEDCOMS[$GEDCOM]["id"])."'";
		$res = dbquery($sql);
		while($row =& $res->fetchRow(DB_FETCHMODE_ASSOC)) {
			$gid = $row["f_husb"];
			$indilist[$gid]["numchil"] += $row["f_numchil"];
			$tindilist[$gid]["numchil"] += $row["f_numchil"];
		}
		$res->free();
	}
	// And now the same thing for partner #2 in a family.
	if ($sqlWife) {
		$sql = "SELECT f_husb, f_wife, f_numchil FROM ".$TBLPREFIX."families WHERE (";
		$sql .= substr($sqlWife, 0, -4);		// get rid of final " OR "
		$sql .= ") AND f_file='".$DBCONN->escapeSimple($GEDCOMS[$GEDCOM]["id"])."'";
		$res = dbquery($sql);
		while($row =& $res->fetchRow(DB_FETCHMODE_ASSOC)) {
			$gid = $row["f_wife"];
			$indilist[$gid]["numchil"] += $row["f_numchil"];
			$tindilist[$gid]["numchil"] += $row["f_numchil"];
		}
		$res->free();
	}

	$sql = "SELECT i_id, i_name, i_file, i_isdead, i_gedcom, i_letter, i_surname, n_letter, n_name, n_surname, n_letter, n_type FROM ".$TBLPREFIX."individuals, ".$TBLPREFIX."names WHERE i_id=n_gid AND i_file=n_file AND n_surname LIKE '".$DBCONN->escapeSimple($surname)."' ";
	if (!$SHOW_MARRIED_NAMES) $sql .= "AND n_type!='C' ";
	$sql .= "AND i_file='".$DBCONN->escapeSimple($GEDCOMS[$GEDCOM]["id"])."' ORDER BY n_surname";
	$res = dbquery($sql);

	while($row =& $res->fetchRow(DB_FETCHMODE_ASSOC)){
		$row = db_cleanup($row);
		if (isset($indilist[$row["i_id"]])) {
			$namearray = array($row["n_name"], $row["n_letter"], $row["n_surname"], $row["n_type"]);
			// do not add to the array an indi name that already exists in it
			if (!in_array($namearray, $indilist[$row["i_id"]]["names"])) {
				$indilist[$row["i_id"]]["names"][] = $namearray;
		    }
			$tindilist[$row["i_id"]] = $indilist[$row["i_id"]];
		}
		else {
			$indi = array();
			//do not add main name first name beginning letter for alternate names
			$indi["names"] = array(array($row["n_name"], $row["n_letter"], $row["n_surname"], $row["n_type"]),array($row["i_name"], $row["i_letter"], $row["i_surname"], "P"));
			$indi["isdead"] = $row["i_isdead"];
			$indi["gedcom"] = $row["i_gedcom"];
			$indi["gedfile"] = $row["i_file"];
			$indilist[$row["i_id"]] = $indi;
			$tindilist[$row["i_id"]] = $indilist[$row["i_id"]];
		}
	}
	$res->free();
	return $tindilist;
}

/**
 * Get Families Starting with a letter
 *
 * This function finds all of the families who start with the given letter
 * @param string $letter	The letter to search on
 * @return array	$indilist array
 * @see get_alpha_indis()
 * @see http://www.phpgedview.net/devdocs/arrays.php#famlist
 */
function get_alpha_fams($letter) {
	global $TBLPREFIX, $GEDCOM, $famlist, $indilist, $LANGUAGE, $SHOW_MARRIED_NAMES, $DBCONN, $GEDCOMS;
	global $DICTIONARY_SORT, $UCDiacritWhole, $UCDiacritStrip, $LCDiacritWhole, $LCDiacritStrip;

	$danishex = array("OE", "AE", "AA");
	$danishFrom = array("AA", "AE", "OE");
	$danishTo = array("Å", "Æ", "Ø");

	$tfamlist = array();
	$temp = $SHOW_MARRIED_NAMES;
	$SHOW_MARRIED_NAMES = false;
	$myindilist = get_alpha_indis($letter);
	$SHOW_MARRIED_NAMES = $temp;
	if ($letter=="(" || $letter=="[" || $letter=="?" || $letter=="/" || $letter=="*" || $letter=="+" || $letter==')') $letter = "\\".$letter;
	foreach($myindilist as $gid=>$indi) {
		$ct = preg_match_all("/1 FAMS @(.*)@/", $indi["gedcom"], $match, PREG_SET_ORDER);
		$surnames = array();
		for($i=0; $i<$ct; $i++) {
			$famid = $match[$i][1];
			$famrec = find_family_record($famid);
			if ($famlist[$famid]["husb"]==$gid) {
				$HUSB = $famlist[$famid]["husb"];
				$WIFE = $famlist[$famid]["wife"];
			}
			else {
				$HUSB = $famlist[$famid]["wife"];
				$WIFE = $famlist[$famid]["husb"];
			}
			$hname="";
			$surnames = array();
			foreach($indi["names"] as $indexval => $namearray) {
				//-- don't use married names in the family list
				if ($namearray[3]!='C') {
					$text = "";
					if ($LANGUAGE == "danish" || $LANGUAGE == "norwegian") {
						if ($letter == "Ø") $text = "OE";
						else if ($letter == "Æ") $text = "AE";
						else if ($letter == "Å") $text = "AA";
					}
					if ($DICTIONARY_SORT[$LANGUAGE]) {
						if (strlen($namearray[1])>1) {
							$aPos = strpos($UCDiacritWhole, $namearray[1]);
							if ($aPos!==false) {
								if ($letter==substr($UCDiacritStrip, ($aPos>>1), 1)) $text = $namearray[1];
							} else {
								$aPos = strpos($LCDiacritWhole, $namearray[1]);
								if ($aPos!==false) {
									if ($letter==substr($LCDiacritStrip, ($aPos>>1), 1)) $text = $namearray[1];
								}
							}
						}
					}
//				[ 1579889 ]
//				if ((preg_match("/^$letter/", $namearray[1])>0)||(!empty($text)&&preg_match("/^$text/", $namearray[1])>0)) {
					if ((preg_match("/^$letter/", $namearray[1])>0)||(!empty($text)&&preg_match("/^$text/i", $namearray[2])>0)) {
						$surnames[str2upper($namearray[2])] = $namearray[2];
						$hname = sortable_name_from_name($namearray[0]);
					}
				}
			}
			if (!empty($hname)) {
				$wname = get_sortable_name($WIFE);
				if (hasRTLText($hname)) {
					$indirec = find_person_record($WIFE);
					if (isset($indilist[$WIFE])) {
						foreach($indilist[$WIFE]["names"] as $n=>$namearray) {
							if (hasRTLText($namearray[0])) {
								$surnames[str2upper($namearray[2])] = $namearray[2];
								$wname = sortable_name_from_name($namearray[0]);
								break;
							}
						}
					}
				}
				$name = $hname ." + ". $wname;
				if ($famlist[$famid]["wife"]==$gid) $name = $wname ." + ". $hname; // force husb first
				$famlist[$famid]["name"] = $name;
				if (!isset($famlist[$famid]["surnames"])||count($famlist[$famid]["surnames"])==0) $famlist[$famid]["surnames"] = $surnames;
//				else pgv_array_merge($famlist[$famid]["surnames"], $surnames);
				else $famlist[$famid]["surnames"] += $surnames;
				$tfamlist[$famid] = $famlist[$famid];
			}
		}
	}

	//-- handle the special case for @N.N. when families don't have any husb or wife
	//-- SHOULD WE SHOW THE UNDEFINED? MA
	if ($letter=="@") {
		$sql = "SELECT * FROM ".$TBLPREFIX."families WHERE (f_husb='' OR f_wife='') AND f_file='".$DBCONN->escapeSimple($GEDCOMS[$GEDCOM]["id"])."'";
		$res = dbquery($sql);

		if ($res->numRows()>0) {
			while($row =& $res->fetchRow(DB_FETCHMODE_ASSOC)){
				$fam = array();
				$hname = get_sortable_name($row["f_husb"]);
				$wname = get_sortable_name($row["f_wife"]);
				if (!empty($hname)) $name = $hname;
				else $name = "@N.N., @P.N.";
				if (!empty($wname)) $name .= " + ".$wname;
				else $name .= " + @N.N., @P.N.";
				$fam["name"] = $name;
				$fam["HUSB"] = $row["f_husb"];
				$fam["WIFE"] = $row["f_wife"];
				$fam["CHIL"] = $row["f_chil"];
				$fam["gedcom"] = $row["f_gedcom"];
				$fam["gedfile"] = $row["f_file"];
				$fam["surnames"] = array("@N.N.");
				$tfamlist[$row["f_id"]] = $fam;
				//-- cache the items in the lists for improved speed
				$famlist[$row["f_id"]] = $fam;
			}
		}
		$res->free();
	}
	return $tfamlist;
}

/**
 * Get Families with a given surname
 *
 * This function finds all of the individuals who have the given surname
 * @param string $surname	The surname to search on
 * @return array	$indilist array
 * @see http://www.phpgedview.net/devdocs/arrays.php#indilist
 */
function get_surname_fams($surname) {
	global $TBLPREFIX, $GEDCOM, $famlist, $indilist, $DBCONN, $SHOW_MARRIED_NAMES, $GEDCOMS;
	$tfamlist = array();
	$temp = $SHOW_MARRIED_NAMES;
	$SHOW_MARRIED_NAMES = false;
	$myindilist = get_surname_indis($surname);
	$SHOW_MARRIED_NAMES = $temp;
	$famids = array();
	//-- load up the families with 1 query
	foreach($myindilist as $gid=>$indi) {
		$ct = preg_match_all("/1 FAMS @(.*)@/", $indi["gedcom"], $match, PREG_SET_ORDER);
		for($i=0; $i<$ct; $i++) {
			$famid = $match[$i][1];
			$famids[] = $famid;
		}
	}
	load_families($famids);
	
	foreach($myindilist as $gid=>$indi) {
		$ct = preg_match_all("/1 FAMS @(.*)@/", $indi["gedcom"], $match, PREG_SET_ORDER);
		for($i=0; $i<$ct; $i++) {
			$famid = $match[$i][1];
			//$famrec = find_family_record($famid);
			if ($famlist[$famid]["husb"]==$gid) {
				$HUSB = $famlist[$famid]["husb"];
				$WIFE = $famlist[$famid]["wife"];
			}
			else {
				$HUSB = $famlist[$famid]["wife"];
				$WIFE = $famlist[$famid]["husb"];
			}
			$hname = "";
			foreach($indi["names"] as $indexval => $namearray) {
				if (stristr($namearray[2], $surname)!==false) {
					$hname = sortable_name_from_name($namearray[0]);
					break; 
					// we should show also at least the _HEB and ROMN first names of our family parent surname in the list
					// currently only one name is processed - without the break it is the last name
					// now we stop at the first name
				}
			}
			if (!empty($hname)) {
				$wname = get_sortable_name($WIFE);
				if (hasRTLText($hname)) {
					$indirec = find_person_record($WIFE);
					if (isset($indilist[$WIFE])) {
						foreach($indilist[$WIFE]["names"] as $n=>$namearray) {
							if (hasRTLText($namearray[0])) {
								$wname = sortable_name_from_name($namearray[0]);
								break;
							}
						}
					}
				}
				$name = $hname ." + ". $wname;
				if ($famlist[$famid]["wife"]==$gid) $name = $wname ." + ". $hname; // force husb first
				$famlist[$famid]["name"] = $name;
				$tfamlist[$famid] = $famlist[$famid];
			}
		}
	}

	//-- handle the special case for @N.N. when families don't have any husb or wife
	//-- SHOULD WE SHOW THE UNDEFINED? 
	if ($surname=="@N.N.") {
		$sql = "SELECT * FROM ".$TBLPREFIX."families WHERE (f_husb='' OR f_wife='') AND f_file='".$DBCONN->escapeSimple($GEDCOMS[$GEDCOM]["id"])."'";
		$res = dbquery($sql);

		if ($res->numRows()>0) {
			while($row =& $res->fetchRow(DB_FETCHMODE_ASSOC)){
				$fam = array();
				$hname = get_sortable_name($row["f_husb"]);
				$wname = get_sortable_name($row["f_wife"]);
				if (empty($hname)) $hname = "@N.N., @P.N.";
				if (empty($wname)) $wname = "@N.N., @P.N.";
				$fam["name"] = $hname." + ".$wname;
				$fam["HUSB"] = $row["f_husb"];
				$fam["WIFE"] = $row["f_wife"];
				$fam["CHIL"] = $row["f_chil"];
				$fam["gedcom"] = $row["f_gedcom"];
				$fam["gedfile"] = $row["f_file"];
				$tfamlist[$row["f_id"]] = $fam;
				//-- cache the items in the lists for improved speed
				$famlist[$row["f_id"]] = $fam;
			}
		}
		$res->free();
	}
	return $tfamlist;
}

//-- function to find the gedcom id for the given rin
function find_rin_id($rin) {
	global $TBLPREFIX, $GEDCOM, $DBCONN, $GEDCOMS;

	$sql = "SELECT i_id FROM ".$TBLPREFIX."individuals WHERE i_rin='$rin' AND i_file='".$DBCONN->escapeSimple($GEDCOMS[$GEDCOM]["id"])."'";
	$res = dbquery($sql);

	while($row =& $res->fetchRow(DB_FETCHMODE_ASSOC)){
		return $row["i_id"];
	}
	return $rin;
}

/**
 * Delete a gedcom from the database and the system
 * Does not delete the file from the file system
 * @param string $ged 	the filename of the gedcom to delete
 */
function delete_gedcom($ged) {
	global $TBLPREFIX, $pgv_changes, $DBCONN, $GEDCOMS;

	if (!isset($GEDCOMS[$ged])) return;
	$dbged = $GEDCOMS[$ged]["id"];

	$sql = "DELETE FROM ".$TBLPREFIX."blocks WHERE b_username='".$DBCONN->escapeSimple($ged)."'";
	$res = dbquery($sql);

	$sql = "DELETE FROM ".$TBLPREFIX."dates WHERE d_file='".$DBCONN->escapeSimple($dbged)."'";
	$res = dbquery($sql);

	$sql = "DELETE FROM ".$TBLPREFIX."families WHERE f_file='".$DBCONN->escapeSimple($dbged)."'";
	$res = dbquery($sql);

	$sql = "DELETE FROM ".$TBLPREFIX."favorites WHERE fv_file='".$DBCONN->escapeSimple($ged)."'";
	$res = dbquery($sql);

	$sql = "DELETE FROM ".$TBLPREFIX."individuals WHERE i_file='".$DBCONN->escapeSimple($dbged)."'";
	$res = dbquery($sql);

	$sql = "DELETE FROM ".$TBLPREFIX."media WHERE m_gedfile='".$DBCONN->escapeSimple($dbged)."'";
	$res = dbquery($sql);

	$sql = "DELETE FROM ".$TBLPREFIX."media_mapping WHERE mm_gedfile='".$DBCONN->escapeSimple($dbged)."'";
	$res = dbquery($sql);

	$sql = "DELETE FROM ".$TBLPREFIX."names WHERE n_file='".$DBCONN->escapeSimple($dbged)."'";
	$res = dbquery($sql);

	$sql = "DELETE FROM ".$TBLPREFIX."news WHERE n_username='".$DBCONN->escapeSimple($ged)."'";
	$res = dbquery($sql);

	$sql = "DELETE FROM ".$TBLPREFIX."nextid WHERE ni_gedfile='".$DBCONN->escapeSimple($dbged)."'";
	$res = dbquery($sql);

	$sql = "DELETE FROM ".$TBLPREFIX."other WHERE o_file='".$DBCONN->escapeSimple($dbged)."'";
	$res = dbquery($sql);

	$sql = "DELETE FROM ".$TBLPREFIX."placelinks WHERE pl_file='".$DBCONN->escapeSimple($dbged)."'";
	$res = dbquery($sql);

	$sql = "DELETE FROM ".$TBLPREFIX."places WHERE p_file='".$DBCONN->escapeSimple($dbged)."'";
	$res = dbquery($sql);

	$sql = "DELETE FROM ".$TBLPREFIX."sources WHERE s_file='".$DBCONN->escapeSimple($dbged)."'";
	$res = dbquery($sql);

	if (isset($pgv_changes)) {
		//-- erase any of the changes
		foreach($pgv_changes as $cid=>$changes) {
			if ($changes[0]["gedcom"]==$ged) unset($pgv_changes[$cid]);
		}
		write_changes();
	}
}

//-- return the current size of the given list
//- list options are indilist famlist sourcelist and otherlist
function get_list_size($list, $filter="") {
	global $TBLPREFIX, $GEDCOM, $DBCONN, $GEDCOMS, $DBTYPE;

	if ($filter) {
		if (stristr($DBTYPE, "mysql")!==false) $term = "REGEXP";
		else if (stristr($DBTYPE, "pgsql")!==false) $term = "~*";
		else $term = "LIKE";
	}

	switch($list) {
		case "indilist":
			$sql = "SELECT count(i_file) FROM ".$TBLPREFIX."individuals WHERE i_file='".$DBCONN->escapeSimple($GEDCOMS[$GEDCOM]["id"])."'";
			if ($filter) $sql .= " AND i_gedcom $term '$filter'";
			$res = dbquery($sql);

			while($row =& $res->fetchRow()) return $row[0];
		break;
		case "famlist":
			$sql = "SELECT count(f_file) FROM ".$TBLPREFIX."families WHERE f_file='".$DBCONN->escapeSimple($GEDCOMS[$GEDCOM]["id"])."'";
			if ($filter) $sql .= " AND f_gedcom $term '$filter'";
			$res = dbquery($sql);

			while($row =& $res->fetchRow()) return $row[0];
		break;
		case "sourcelist":
			$sql = "SELECT count(s_file) FROM ".$TBLPREFIX."sources WHERE s_file='".$DBCONN->escapeSimple($GEDCOMS[$GEDCOM]["id"])."'";
			if ($filter) $sql .= " AND s_gedcom $term '$filter'";
			$res = dbquery($sql);

			while($row =& $res->fetchRow()) return $row[0];
		break;
		case "objectlist": // media object
			$sql = "SELECT count(m_id) FROM ".$TBLPREFIX."media WHERE m_gedfile='".$DBCONN->escapeSimple($GEDCOMS[$GEDCOM]["id"])."'";
			if ($filter) $sql .= " AND m_gedrec $term '$filter'";
			$res = dbquery($sql);

			while($row =& $res->fetchRow()) return $row[0];
		break;
		case "otherlist": // REPO
			$sql = "SELECT count(o_file) FROM ".$TBLPREFIX."other WHERE o_file='".$DBCONN->escapeSimple($GEDCOMS[$GEDCOM]["id"])."'";
			if ($filter) $sql .= " AND o_gedcom $term '$filter'";
			$res = dbquery($sql);

			while($row =& $res->fetchRow()) return $row[0];
		break;
	}
	return 0;
}

/**
 * get the top surnames
 * @param int $num	how many surnames to return
 * @return array
 */
function get_top_surnames($num) {
	global $TBLPREFIX, $GEDCOM, $DBCONN, $GEDCOMS;

	$surnames = array();
	$sql = "SELECT COUNT(i_surname) AS count, i_surname FROM ".$TBLPREFIX."individuals WHERE i_file='".$DBCONN->escapeSimple($GEDCOMS[$GEDCOM]["id"])."' GROUP BY i_surname ORDER BY count DESC";
	$res = dbquery($sql, true, $num+1);

	if (!DB::isError($res)) {
		while($row =& $res->fetchRow()) {
			if (isset($surnames[str2upper($row[1])]["match"])) $surnames[str2upper($row[1])]["match"] += $row[0];
			else {
				$surnames[str2upper($row[1])]["name"] = $row[1];
				$surnames[str2upper($row[1])]["match"] = $row[0];
			}
		}
		$res->free();
	}
	$sql = "SELECT COUNT(n_surname) AS count, n_surname FROM ".$TBLPREFIX."names WHERE n_file='".$DBCONN->escapeSimple($GEDCOMS[$GEDCOM]["id"])."' AND n_type!='C' GROUP BY n_surname ORDER BY count DESC";
	$res = dbquery($sql, true, $num+1);

	if (!DB::isError($res)) {
		while($row =& $res->fetchRow()) {
			if (isset($surnames[str2upper($row[1])]["match"])) $surnames[str2upper($row[1])]["match"] += $row[0];
			else {
				$surnames[str2upper($row[1])]["name"] = $row[1];
				$surnames[str2upper($row[1])]["match"] = $row[0];
			}
		}
		$res->free();
	}
	return $surnames;
}

/**
 * get next unique id for the given table
 * @param string $table 	the name of the table
 * @param string $field		the field to get the next number for
 * @return int the new id
 */
function get_next_id($table, $field) {
	global $TBLPREFIX, $TABLE_IDS;

	if (!isset($TABLE_IDS)) $TABLE_IDS = array();
	if (isset($TABLE_IDS[$table][$field])) {
		$TABLE_IDS[$table][$field]++;
		return $TABLE_IDS[$table][$field];
	}
	$newid = 0;
	$sql = "SELECT MAX($field) FROM ".$TBLPREFIX.$table;
	$res = dbquery($sql);

	if (!DB::isError($res)) {
		$row = $res->fetchRow();
		$res->free();
		$newid = $row[0];
	}
	$newid++;
	$TABLE_IDS[$table][$field] = $newid;
	return $newid;
}

/**
 * get a list of remote servers
 */
function get_server_list(){
 	global $GEDCOM, $GEDCOMS;
	global $TBLPREFIX, $DBCONN, $sitelist, $sourcelist;

	//if (isset($sitelist)) return $sitelist;
	$sitelist = array();

	if (isset($GEDCOMS[$GEDCOM]) && check_for_import($GEDCOM)) {
		$sql = "SELECT * FROM ".$TBLPREFIX."sources WHERE s_file='".$DBCONN->escapeSimple($GEDCOMS[$GEDCOM]["id"])."' AND s_gedcom LIKE '%1 _DBID%' ORDER BY s_name";
		$res = dbquery($sql, false);
		if (DB::isError($res)) return $sitelist;

		$ct = $res->numRows();
		while($row =& $res->fetchRow(DB_FETCHMODE_ASSOC)){
			$source = array();
			$source["name"] = $row["s_name"];
			$source["gedcom"] = $row["s_gedcom"];
			$row = db_cleanup($row);
			$source["gedfile"] = $row["s_file"];
			$sitelist[$row["s_id"]] = $source;
			$sourcelist[$row["s_id"]] = $source;
		}
		$res->free();
	}

	return $sitelist;
}

/**
 * Retrieve the array of faqs from the DB table blocks
 * @param int $id		The FAQ ID to retrieven
 * @return array $faqs	The array containing the FAQ items
 */
function get_faq_data($id='') {
	global $TBLPREFIX, $GEDCOM;

	$faqs = array();
	// Read the faq data from the DB
	$sql = "SELECT b_id, b_location, b_order, b_config, b_username FROM ".$TBLPREFIX."blocks WHERE (b_username='$GEDCOM' OR b_username='*all*') AND b_name='faq'";
	if ($id != '') $sql .= " AND b_order='".$id."'";
	$res = dbquery($sql);

	while($row =& $res->fetchRow(DB_FETCHMODE_ASSOC)){
		$faqs[$row["b_order"]][$row["b_location"]]["text"] = unserialize($row["b_config"]);
		$faqs[$row["b_order"]][$row["b_location"]]["pid"] = $row["b_id"];
		$faqs[$row["b_order"]][$row["b_location"]]["gedcom"] = $row["b_username"];
	}
	ksort($faqs);
	return $faqs;
}

function delete_fact($linenum, $pid, $gedrec) {
	global $linefix, $pgv_lang;
	if (!empty($linenum)) {
		if ($linenum==0) {
			if (delete_gedrec($pid)) print $pgv_lang["gedrec_deleted"];
		}
		else {
			$gedlines = preg_split("/[\r\n]+/", $gedrec);
			// NOTE: The array_pop is used to kick off the last empty element on the array
			// NOTE: To prevent empty lines in the GEDCOM
			// DEBUG: Records without line breaks are imported as 1 big string
			if ($linefix > 0) array_pop($gedlines);
			$newged = "";
			// NOTE: Add all lines that are before the fact to be deleted
			for($i=0; $i<$linenum; $i++) {
				$newged .= trim($gedlines[$i])."\r\n";
			}
			if (isset($gedlines[$linenum])) {
				$fields = preg_split("/\s/", $gedlines[$linenum]);
				$glevel = $fields[0];
				$ctlines = count($gedlines);
				$i++;
				if ($i<$ctlines) {
					// Remove the fact
					while((isset($gedlines[$i]))&&($gedlines[$i]{0}>$glevel)) $i++;
					// Add the remaining lines
					while($i<$ctlines) {
						$newged .= trim($gedlines[$i])."\r\n";
						$i++;
					}
				}
			}
			if ($newged != "")  return $newged;
		}
	}
}

/**
 * get_remote_id Recieves a RFN key and returns a Stub ID if the RFN exists
 *
 * @param mixed $rfn RFN number to see if it exists
 * @access public
 * @return gid Stub ID that contains the RFN number. Returns false if it didn't find anything
 */
function get_remote_id($rfn) {
global $TBLPREFIX, $DBCONN, $GEDCOMS, $GEDCOM;
	$sql = "SELECT r_gid FROM ".$TBLPREFIX."remotelinks WHERE r_linkid='".$DBCONN->escapeSimple($rfn)."' AND r_file='".$GEDCOMS[$GEDCOM]['id']."'";
	$res = dbquery($sql);

	if ($res->numRows()>0) {
		$row = $res->fetchRow();
		$res->free();
		return $row[0];
	}
	else {
		return false;
	}
}

/**
 * Get the list of current and upcoming events, sorted by anniversary date
 *
 * This function is used by the Todays and Upcoming blocks on the Index and Portal
 * pages.  It is also used by the RSS feed.
 *
 * Special note on unknown day-of-month:
 * When the anniversary date is imprecise, the sort will pretend that the day-of-month
 * is either tomorrow or the first day of next month.  These imprecise anniversaries
 * will sort to the head of the chosen day.
 *
 * Special note on Privacy:
 * This routine does not check the Privacy of the events in the list.  That check has
 * to be done by the routine that makes use of the event list.
 */
function get_event_list() {
	global $USE_RTL_FUNCTIONS;
	global $INDEX_DIRECTORY, $GEDCOM, $DEBUG;
	global $DAYS_TO_SHOW_LIMIT;

	if (!isset($DAYS_TO_SHOW_LIMIT)) $DAYS_TO_SHOW_LIMIT = 30;

	$skipfacts = "CHAN,BAPL,SLGC,SLGS,ENDL";	// These are always excluded
	$skipfacts .= ",CENS,RESI,NOTE,ADDR,OBJE,SOUR,PAGE,DATA,TEXT";

	// Look for cached Facts data
	$found_facts = array();
	$cache_load = false;
	if ((file_exists($INDEX_DIRECTORY.$GEDCOM."_upcoming.php"))&&(!isset($DEBUG)||($DEBUG==false))) {
		$modtime = filemtime($INDEX_DIRECTORY.$GEDCOM."_upcoming.php");
		$mday = date("d", $modtime);
		if ($mday==date('j')) {
			$fp = fopen($INDEX_DIRECTORY.$GEDCOM."_upcoming.php", "rb");
			$fcache = fread($fp, filesize($INDEX_DIRECTORY.$GEDCOM."_upcoming.php"));
			fclose($fp);
			$found_facts = unserialize($fcache);
			$cache_load = true;
		}
	}

	if (!$cache_load) {
		$nmonth = date('n');
		$dateRangeStart = mktime(0,0,0,date('n'),date('j'),date('Y'));
		$dateRangeEnd = $dateRangeStart+(60*60*24*$DAYS_TO_SHOW_LIMIT)-1;
		$startstamp = date("md", $dateRangeStart);
		$endstamp = date("md", $dateRangeEnd);

		// Search database for raw Indi data if no cache was found
		$dayindilist = search_indis_daterange($startstamp, $endstamp, "!CHAN");

		// Search database for raw Family data if no cache was found
		$dayfamlist = search_fams_daterange($startstamp, $endstamp, "!CHAN");

		// Apply filter criteria and perform other transformations on the raw data
		$found_facts = array();
		foreach($dayindilist as $gid=>$indi) {
			$facts = get_all_subrecords($indi["gedcom"], $skipfacts, false, false, false);
			foreach($facts as $key=>$factrec) {
				$date = 0; 
				if ($USE_RTL_FUNCTIONS) {
					$hct = preg_match("/2 DATE.*(@#DHEBREW@)/", $factrec, $match);
					if ($hct>0) {
						$dct = preg_match("/2 DATE (.+)/", $factrec, $match);
						$hebrew_date = parse_date(trim($match[1]));
						$date = jewishGedcomDateToCurrentGregorian($hebrew_date);
					}
				} 
				
				if ($date===0) {
				  	$ct = preg_match("/2 DATE (.+)/", $factrec, $match);
				  	if ($ct>0) $date = parse_date(trim($match[1]));
				}
				
				if ($date !== 0) {
					$startSecond = 1;
					if ($date[0]["day"]=="") {
						$startSecond = 0;
						$date[0]["day"] = ($date[0]["month"]==$nmonth) ? date('j')+1 : 1;
					}
					$anniversaryDate = mktime(0,0,$startSecond,(int)$date[0]["mon"],(int)$date[0]["day"],date('Y'));
					if ($anniversaryDate<$dateRangeStart) $anniversaryDate = mktime(0,0,$startSecond,(int)$date[0]["mon"],(int)$date[0]["day"],date('Y')+1);
					if ($anniversaryDate>=$dateRangeStart && $anniversaryDate<=$dateRangeEnd) {
						// Strip useless information:
						//   NOTE, ADDR, OBJE, SOUR, PAGE, DATA, TEXT, CONC, CONT
						$factrec = preg_replace("/\d\s+(NOTE|ADDR|OBJE|SOUR|PAGE|DATA|TEXT|CONC|CONT)\s+(.+)\n/", "", $factrec);
						$found_facts[] = array($gid, $factrec, "INDI", $anniversaryDate);
					}
				}
			}
		}
		foreach($dayfamlist as $gid=>$fam) {
			$facts = get_all_subrecords($fam["gedcom"], $skipfacts, false, false, false);
			foreach($facts as $key=>$factrec) {
				$date = 0;
				if ($USE_RTL_FUNCTIONS) {
					$hct = preg_match("/2 DATE.*(@#DHEBREW@)/", $factrec, $match);
					if ($hct>0) {
						$dct = preg_match("/2 DATE (.+)/", $factrec, $match);
						$hebrew_date = parse_date(trim($match[1]));
						$date = jewishGedcomDateToCurrentGregorian($hebrew_date);
					}
				} 
				if ($date===0) {
					$ct = preg_match("/2 DATE (.+)/", $factrec, $match);
					if ($ct>0) $date = parse_date(trim($match[1]));
				}
				
				if ($date !== 0) {
					$startSecond = 1;
					if ($date[0]["day"]=="") {
						$startSecond = 0;
						$date[0]["day"] = ($date[0]["month"]==$nmonth) ? date('j')+1 : 1;
					}
					$anniversaryDate = mktime(0,0,$startSecond,(int)$date[0]["mon"],(int)$date[0]["day"],date('Y'));
					if ($anniversaryDate<$dateRangeStart) $anniversaryDate = mktime(0,0,$startSecond,(int)$date[0]["mon"],(int)$date[0]["day"],date('Y')+1);
					if ($anniversaryDate>=$dateRangeStart && $anniversaryDate<=$dateRangeEnd) {
						// Strip useless information:
						//   NOTE, ADDR, OBJE, SOUR, PAGE, DATA, TEXT, CONC, CONT
						$factrec = preg_replace("/\d\s+(NOTE|ADDR|OBJE|SOUR|PAGE|DATA|TEXT|CONC|CONT)\s+(.+)\n/", "", $factrec);
						$found_facts[] = array($gid, $factrec, "FAM", $anniversaryDate);
					}
				}
			}
		}

		uasort($found_facts, "compare_found_facts");
		reset($found_facts);

// Cache the Facts data just found
		if (is_writable($INDEX_DIRECTORY)) {
			$fp = fopen($INDEX_DIRECTORY."/".$GEDCOM."_upcoming.php", "wb");
			fwrite($fp, serialize($found_facts));
			fclose($fp);
			$logline = AddToLog($GEDCOM."_upcoming.php updated by >".getUserName()."<");
 			if (!empty($COMMIT_COMMAND)) check_in($logline, $GEDCOM."_upcoming.php", $INDEX_DIRECTORY);
		}
	}

	return $found_facts;
}

function compare_found_facts($a, $b) {
	// Sort by date
	if ($a[3]!=$b[3])
		return $a[3]-$b[3];
	// :TODO: Sort by name
	// Sort by fact type
	return compare_facts_type($a[1], $b[1]);
}

?>