<?php
/**
 * Class file for a Repository (REPO) object
 *
 * phpGedView: Genealogy Viewer
 * Copyright (C) 2002 to 2007	John Finlay and Others
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
 * @package PhpGedView
 * @subpackage DataModel
 * @version $Id: repository_class.php 3509 2008-07-08 23:27:34Z yalnifj $
 */

if (stristr($_SERVER["SCRIPT_NAME"], basename(__FILE__))!==false) {
	print "You cannot access an include file directly.";
	exit;
}

require_once('includes/gedcomrecord.php');

class Repository extends GedcomRecord {
	var $disp = true;
	var $sourcelist = null;
	var $repositoryfacts = null;

	/**
	 * Constructor for repository object
	 * @param string $gedrec	the raw repository gedcom record
	 */
	function Repository($gedrec) {
		parent::GedcomRecord($gedrec);
		$this->disp = displayDetailsByID($this->xref, "REPO");
	}

	/**
	 * Static function used to get an instance of a repository object
	 * @param string $pid	the ID of the repository to retrieve
	 */
	function &getInstance($pid, $simple=true) {
		global $gedcom_record_cache, $GEDCOM, $pgv_changes;

		$ged_id=get_id_from_gedcom($GEDCOM);
		// Check the cache first
		if (isset($gedcom_record_cache[$pid][$ged_id])) {
			return $gedcom_record_cache[$pid][$ged_id];
		}

		$repositoryrec = find_repo_record($pid);
		if (empty($repositoryrec)) {
			$ct = preg_match("/(\w+):(.+)/", $pid, $match);
			if ($ct>0) {
				$servid = trim($match[1]);
				$remoteid = trim($match[2]);
				require_once 'includes/serviceclient_class.php';
				$service = ServiceClient::getInstance($servid);
				$newrec= $service->mergeGedcomRecord($remoteid, "0 @".$pid."@ REPO\r\n1 RFN ".$pid, false);
				$repositoryrec = $newrec;
			}
		}
		if (empty($repositoryrec)) {
			if (PGV_USER_CAN_EDIT && isset($pgv_changes[$pid."_".$GEDCOM])) {
				$repositoryrec = find_updated_record($pid);
				$fromfile = true;
			}
		}
		if (empty($repositoryrec)) return null;
		$repository = new Repository($repositoryrec, $simple);
		if (!empty($fromfile)) $repository->setChanged(true);
		// Store the object in the cache
		$repository->ged_id=$ged_id;
		$gedcom_record_cache[$pid][$ged_id]=&$repository;
		return $repository;
	}

	/**
	 * Check if privacy options allow this record to be displayed
	 * @return boolean
	 */
	function canDisplayDetails() {
		return $this->disp;
	}

	/**
	 * get the repository name
	 * @return string
	 */
	function getName() {
		return $this->name;
	}

	/**
	 * get repository facts array
	 * @return array
	 */
	function getRepositoryFacts() {
		$this->parseFacts();
		return $this->repositoryfacts;
	}

	/**
	 * Parse the facts from the repository record
	 */
	function parseFacts() {
		if (!is_null($this->repositoryfacts)) return;
		$this->repositoryfacts = array();
		$gedlines = preg_split("/\n/", $this->gedrec);
		$lct = count($gedlines);
		$factrec = "";	// -- complete fact record
		$line = "";	// -- temporary line buffer
		$linenum = 1;
		for($i=1; $i<=$lct; $i++) {
			if ($i<$lct) $line = $gedlines[$i];
			else $line=" ";
			if (empty($line)) $line=" ";
			if (($i==$lct)||($line{0}==1)) {
				if (!empty($factrec) ) {
					$this->repositoryfacts[] = array($factrec, $linenum);
				}
				$factrec = $line;
				$linenum = $i;
			}
			else $factrec .= "\n".$line;
		}
	}

	/**
	 * Merge the facts from another Repository object into this object
	 * for generating a diff view
	 * @param Repository $diff	the repository to compare facts with
	 */
	function diffMerge(&$diff) {
		if (is_null($diff)) return;
		$this->parseFacts();
		$diff->parseFacts();

		//-- update old facts
		foreach($this->repositoryfacts as $key=>$fact) {
			$found = false;
			foreach($diff->repositoryfacts as $indexval => $newfact) {
				$newfact=preg_replace("/\\\/", "/", $newfact);
				if (trim($newfact[0])==trim($fact[0])) {
					$found = true;
					break;
				}
			}
			if (!$found) {
				$this->repositoryfacts[$key][0].="\nPGV_OLD\n";
			}
		}
		//-- look for new facts
		foreach($diff->repositoryfacts as $key=>$newfact) {
			$found = false;
			foreach($this->repositoryfacts as $indexval => $fact) {
				$newfact=preg_replace("/\\\/", "/", $newfact);
				if (trim($newfact[0])==trim($fact[0])) {
					$found = true;
					break;
				}
			}
			if (!$found) {
				$newfact[0].="\nPGV_NEW\n";
				$this->repositoryfacts[]=$newfact;
			}
		}
	}

	/**
	 * get the list of sources connected to this repository
	 * @return array
	 */
	function getRepositorySours() {
		global $REGEXP_DB;
		if (!is_null($this->sourcelist)) return $this->sourcelist;
		$query = "REPO @".$this->xref."@";
		if (!$REGEXP_DB) $query = "%".$query."%";

		$this->sourcelist = search_sources($query);
		uasort($this->sourcelist, "itemsort");
		return $this->sourcelist;
	}

	/**
	 * get the count of sources connected to this repository
	 * @return array
	 */
	function countLinkedSources() {
		global $DBCONN, $TBLPREFIX, $TOTAL_QUERIES;

		$query=preg_replace('/([_%@])/', '@$1', $DBCONN->escapeSimple($this->getXref()));

		++$TOTAL_QUERIES;
		return $DBCONN->getOne(
			"SELECT COUNT(s_id) FROM {$TBLPREFIX}sources ".
			"WHERE s_gedcom LIKE '%\n1 REPO @@{$query}@@%' ESCAPE '@' ".
			"AND s_file=".$this->ged_id
		);
	}
	/**
	 * get the URL to link to this repository
	 * @string a url that can be used to link to this repository
	 */
	function getLinkUrl() {
		return parent::getLinkUrl('repo.php?rid=');
	}

	// Get an array of structures containing all the names in the record
	function getAllNames() {
		return parent::getAllNames('NAME');
	}
}
?>