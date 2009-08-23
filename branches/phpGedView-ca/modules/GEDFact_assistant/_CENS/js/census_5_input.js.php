<?php
/**
 * Census Assistant Control module for phpGedView
 *
 * Census information about an individual
 *
 * phpGedView: Genealogy Viewer
 * Copyright (C) 2002 to 2008  PGV Development Team.  All rights reserved.
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
 * @subpackage Census Assistant
 * @version $Id$
 */

// modified from tabledeleterow.js version 1.2 2006-02-21
// mredkj.com

// CONFIG notes. Below are some comments that point to where this script can be customized.
// Note: Make sure to include a <tbody></tbody> in your table's HTML

//	To load the file XXX for module YYY, call
	loadLangFile("GEDFact_assistant:lang");

?>

<script>

//Load Language variables for Edit header and tooltip ============================
var HeaderName   = "<?php echo $pgv_lang["header_Name"];?>";
var TTEditName   = "<?php echo $pgv_lang["tt_edit_Name"];?>";
var HeaderRela   = "<?php echo $pgv_lang["header_Rela"];?>";
var TTEditRela   = "<?php echo $pgv_lang["tt_edit_Rela"];?>";
var HeaderMCond  = "<?php echo $pgv_lang["header_MCond"];?>";
var TTEditMCond  = "<?php echo $pgv_lang["tt_edit_MCond"];?>";
var HeaderAsset  = "<?php echo $pgv_lang["header_Asset"];?>";
var TTEditAsset  = "<?php echo $pgv_lang["tt_edit_Asset"];?>";
var HeaderAge    = "<?php echo $pgv_lang["header_Age"];?>";
var TTEditAge    = "<?php echo $pgv_lang["tt_edit_Age"];?>";
var HeaderRace   = "<?php echo $pgv_lang["header_Race"];?>";
var TTEditRace   = "<?php echo $pgv_lang["tt_edit_Race"];?>";
var HeaderSex    = "<?php echo $pgv_lang["header_Sex"];?>";
var TTEditSex    = "<?php echo $pgv_lang["tt_edit_Sex"];?>";
var HeaderYOB    = "<?php echo $pgv_lang["header_YOB"];?>";
var TTEditYOB    = "<?php echo $pgv_lang["tt_edit_YOB"];?>";
var HeaderBmth   = "<?php echo $pgv_lang["header_Bmth"];?>";
var TTEditBmth   = "<?php echo $pgv_lang["tt_edit_Bmth"];?>";
var HeaderYrsM   = "<?php echo $pgv_lang["header_YrsM"];?>";
var TTEditYrsM   = "<?php echo $pgv_lang["tt_edit_YrsM"];?>";
var HeaderChilB  = "<?php echo $pgv_lang["header_ChilB"];?>";
var TTEditChilB  = "<?php echo $pgv_lang["tt_edit_ChilB"];?>";
var HeaderChilL  = "<?php echo $pgv_lang["header_ChilL"];?>";
var TTEditChilL  = "<?php echo $pgv_lang["tt_edit_ChilL"];?>";
var HeaderChilD  = "<?php echo $pgv_lang["header_ChilD"];?>";
var TTEditChilD  = "<?php echo $pgv_lang["tt_edit_ChilD"];?>";
var HeaderAgeM   = "<?php echo $pgv_lang["header_AgM"];?>";
var TTEditAgeM   = "<?php echo $pgv_lang["tt_edit_AgM"];?>";
var HeaderOccu   = "<?php echo $pgv_lang["header_Occu"];?>";
var TTEditOccu   = "<?php echo $pgv_lang["tt_edit_Occu"];?>";
var HeaderBplace = "<?php echo $pgv_lang["header_Bplace"];?>";    // Full format
var TTEditBplace = "<?php echo $pgv_lang["tt_edit_Bplace"];?>";   // Full format
var HeaderBP     = "<?php echo $pgv_lang["header_BP"];?>";        // Chapman format
var TTEditBP     = "<?php echo $pgv_lang["tt_edit_BP"];?>";       // Chapman format
var HeaderFBP    = "<?php echo $pgv_lang["header_FBP"];?>";       // Chapman format
var TTEditFBP    = "<?php echo $pgv_lang["tt_edit_FBP"];?>";      // Chapman format
var HeaderMBP    = "<?php echo $pgv_lang["header_MBP"];?>";       // Chapman format
var TTEditMBP    = "<?php echo $pgv_lang["tt_edit_MBP"];?>";      // Chapman format
var HeaderNL     = "<?php echo $pgv_lang["header_NL"];?>";        
var TTEditNL     = "<?php echo $pgv_lang["tt_edit_NL"];?>";       
var HeaderHealth = "<?php echo $pgv_lang["header_Health"];?>";
var TTEditHealth = "<?php echo $pgv_lang["tt_edit_Health"];?>";
var HeaderYrsUS  = "<?php echo $pgv_lang["header_YrsUS"];?>";
var TTEditYrsUS  = "<?php echo $pgv_lang["tt_edit_YrsUS"];?>";
var HeaderYOI    = "<?php echo $pgv_lang["header_YOI"];?>";
var TTEditYOI    = "<?php echo $pgv_lang["tt_edit_YOI"];?>";
var HeaderNA     = "<?php echo $pgv_lang["header_NA"];?>";
var TTEditNA     = "<?php echo $pgv_lang["tt_edit_NA"];?>";
var HeaderYON    = "<?php echo $pgv_lang["header_YON"];?>";
var TTEditYON    = "<?php echo $pgv_lang["tt_edit_YON"];?>";
var HeaderEngL   = "<?php echo $pgv_lang["header_EngL"];?>";
var TTEditEngL   = "<?php echo $pgv_lang["tt_edit_EngL"];?>";
var HeaderEng    = "<?php echo $pgv_lang["header_Eng"];?>";
var TTEditEng    = "<?php echo $pgv_lang["tt_edit_Eng"];?>";
var HeaderInd    = "<?php echo $pgv_lang["header_Ind"];?>";
var TTEditInd    = "<?php echo $pgv_lang["tt_edit_Ind"];?>";
var HeaderEmp    = "<?php echo $pgv_lang["header_Emp"];?>";
var TTEditEmp    = "<?php echo $pgv_lang["tt_edit_Emp"];?>";
var HeaderEmR    = "<?php echo $pgv_lang["header_EmR"];?>";
var TTEditEmR    = "<?php echo $pgv_lang["tt_edit_EmR"];?>";
var HeaderEmD    = "<?php echo $pgv_lang["header_EmD"];?>";
var TTEditEmD    = "<?php echo $pgv_lang["tt_edit_EmD"];?>";
var HeaderEmH    = "<?php echo $pgv_lang["header_EmH"];?>";
var TTEditEmH    = "<?php echo $pgv_lang["tt_edit_EmH"];?>";
var HeaderEmN    = "<?php echo $pgv_lang["header_EmN"];?>";
var TTEditEmN    = "<?php echo $pgv_lang["tt_edit_EmN"];?>";
var HeaderEduc    = "<?php echo $pgv_lang["header_Educ"];?>";
var TTEditEduc    = "<?php echo $pgv_lang["tt_edit_Educ"];?>";
var HeaderBIC    = "<?php echo $pgv_lang["header_BIC"];?>";
var TTEditBIC    = "<?php echo $pgv_lang["tt_edit_BIC"];?>";
var HeaderBOE    = "<?php echo $pgv_lang["header_BOE"];?>";
var TTEditBOE    = "<?php echo $pgv_lang["tt_edit_BOE"];?>";
var HeaderInfirm = "<?php echo $pgv_lang["header_Infirm"];?>";
var TTEditInfirm = "<?php echo $pgv_lang["tt_edit_Infirm"];?>";
var HeaderVet    = "<?php echo $pgv_lang["header_Vet"];?>";
var TTEditVet    = "<?php echo $pgv_lang["tt_edit_Vet"];?>";

var HeaderLang   = "<?php echo $pgv_lang["header_Lang"];?>";
var TTEditLang   = "<?php echo $pgv_lang["tt_edit_Lang"];?>";



// Load Edit Table variables =====================================================
var INPUT_NAME_PREFIX = 'InputCell_'; // this is being set via script
var RADIO_NAME = "totallyrad"; // this is being set via script
var TABLE_NAME = 'tblSample'; // this should be named in the HTML
var ROW_BASE = 0; // first number (for display)
var hasLoaded = false;


// Load Other variables ==========================================================
var NoteCtry = document.getElementById('censCtry');
var NoteYear = document.getElementById('censYear');
var NoteTitl = document.getElementById('Titl');


function preview() {
	NoteCtry = document.getElementById('censCtry');
	NoteYear = document.getElementById('censYear');
	Citation = document.getElementById('citation');
	Locality = document.getElementById('locality');
	Notes    = document.getElementById('notes');

	str = NoteYear.value + " " + NoteCtry.value + " " + NoteTitl.value;
	str += "\n";
	if (Citation.value!="" && Citation.value!=null) {
		str += Citation.value + "\n";
	}
	if (Locality.value!="" && Locality.value!=null) {
		str += Locality.value + "\n";
	}
	str += "\n";
	str += ".start_formatted_area.";
	
	var tbl = document.getElementById('tblSample');
	for(var i=0; i<tbl.rows.length; i++){
		var tr = tbl.rows[i];
		var strRow = '';
		
		if (NoteCtry.value=="UK") {
			// UK 1921 or 1911 ===============
			if (NoteYear.value=="1921" || NoteYear.value=="1911") {
				for(var j=2; j<tr.cells.length-3; j++) {  
					if (j==5 || j==7 || (j>=9 && j<=14) || (j>=19 && j<=30) || j==32 || j==35 || j==36 || (j>=38 && j<=41) || (j>=43 && j<=54) || j==56 ) {
							continue;
					}else{
						if (i==0) {
							strRow += (strRow==''?'':'|') + tr.cells[j].childNodes[0].id;
						}else{
							strRow += (strRow==''?'':'|') + tr.cells[j].childNodes[0].value;
						}
					}
				}
			// UK 1901 ===============
			} else if (NoteYear.value=="1901") {
				for(var j=2; j<tr.cells.length-3; j++) {	// == j=2 means miss out cols 0 and 1 (# and pid), cells.length-3 means miss out del, ins and item # 
					if ( j==5 || j==7 || (j>=9 && j<=30) || j==32 || j==33 || j==35 || j==36 || (j>=38 && j<=41) || (j>=43 && j<=54) || j==56 ) { 
							continue;
					}else{
						if (i==0) {
							strRow += (strRow==''?'':'|') + tr.cells[j].childNodes[0].id;
						}else{
							strRow += (strRow==''?'':'|') + tr.cells[j].childNodes[0].value;
						}
					}
				}
			// UK 1891 ===============
			} else if (NoteYear.value=="1891") {
				for(var j=2; j<tr.cells.length-3; j++) {	// == j=2 means miss out cols 0 and 1 (# and pid), cells.length-3 means miss out del, ins and item # 
					if ( j==5 || j==7 || (j>=9 && j<=30) || (j>=32 && j<=34) || j==37 || (j>=39 && j<=41) || (j>=43 && j<=54) || j==56 ) { 
							continue;
					}else{
						if (i==0) {
							strRow += (strRow==''?'':'|') + tr.cells[j].childNodes[0].id;
						}else{
							strRow += (strRow==''?'':'|') + tr.cells[j].childNodes[0].value;
						}
					}
				}
			// UK 1951-1881 ============
			} else if (NoteYear.value=="1851" || NoteYear.value=="1861" || NoteYear.value=="1871" || NoteYear.value=="1881") {
				for(var j=2; j<tr.cells.length-3; j++) {	// == j=2 means miss out cols 0 and 1 (# and pid), cells.length-3 means miss out del, ins and item # 
					if ( j==5 || j==7 || (j>=9 && j<=30) || (j>=32 && j<=41) || (j>=43 && j<=54) || j==56 ) { 
							continue;
					}else{
						if (i==0) {
							strRow += (strRow==''?'':'|') + tr.cells[j].childNodes[0].id;
						}else{
							strRow += (strRow==''?'':'|') + tr.cells[j].childNodes[0].value;
						}
					}
				}
			// UK 1841 ===============
			} else if (NoteYear.value=="1841") {
				for(var j=2; j<tr.cells.length-3; j++) {	// == j=2 means miss out cols 0 and 1 (# and pid), cells.length-3 means miss out del, ins and item # 
					if ( j==3 || j==4 || j==5 || j==7 || (j>=9 && j<=30) || (j>=32 && j<=43) || (j>=46 && j<=56) ) { 
							continue;
					}else{
						if (i==0) {
							strRow += (strRow==''?'':'|') + tr.cells[j].childNodes[0].id;
						}else{
							strRow += (strRow==''?'':'|') + tr.cells[j].childNodes[0].value;
						}
					}
				}
			}
			
		} else if (NoteCtry.value=="USA") {
			// USA 1930 ===============
			if (NoteYear.value=="1930") {
				for(var j=2; j<tr.cells.length-3; j++) {	// == j=2 means miss out cols 0 and 1 (# and pid), cells.length-3 means miss out del, ins and item # 
					if ( j==4 || j==6 || j==7 || j==10 || j==12 || j==13 || (j>=15 && j<=18) || (j>=20 && j<=38) || j==40 || j==41 || j==42 || j==44 || j==45 || j==55 ) { 
							continue;
					}else{
						if (i==0) {
							strRow += (strRow==''?'':'|') + tr.cells[j].childNodes[0].id;
						}else{
							strRow += (strRow==''?'':'|') + tr.cells[j].childNodes[0].value;
						}
					}
				}
			} 
			// USA 1920 ===============
			else if (NoteYear.value=="1920") {
				for(var j=2; j<tr.cells.length-3; j++) {	// == j=2 means miss out cols 0 and 1 (# and pid), cells.length-3 means miss out del, ins and item # 
					if ( j==4 || j==6 || j==7 || j==10 || j==12 || j==13 || (j>=15 && j<=26) || (j>=30 && j<=38) || j==40 || j==41 || j==42 || j==44 || j==45 || j==49 || j==50 || j==55 || j==56 ) { 
							continue;
					}else{
						if (i==0) {
							strRow += (strRow==''?'':'|') + tr.cells[j].childNodes[0].id;
						}else{
							strRow += (strRow==''?'':'|') + tr.cells[j].childNodes[0].value;
						}
					}
				}
			}
			// USA 1910 ===============
			else if (NoteYear.value=="1910") {
				for(var j=2; j<tr.cells.length-3; j++) {	// == j=2 means miss out cols 0 and 1 (# and pid), cells.length-3 means miss out del, ins and item # 
					if ( (j>=4 && j<=7) || j==10 || j==12 || j==13 || (j>=18 && j<=22) || j==26 || j==29 || j==32 || (j>=35 && j<=38) || j==40 || (j>=42 && j<=54) ) { 
							continue;
					}else{
						if (i==0) {
							strRow += (strRow==''?'':'|') + tr.cells[j].childNodes[0].id;
						}else{
							strRow += (strRow==''?'':'|') + tr.cells[j].childNodes[0].value;
						}
					}
				}
			}
			// USA 1900 ===============
			else if (NoteYear.value=="1900") {
				for(var j=2; j<tr.cells.length-3; j++) {	// == j=2 means miss out cols 0 and 1 (# and pid), cells.length-3 means miss out del, ins and item # 
					if ( (j>=4 && j<=7) || j==12 || j==13 || (j>=18 && j<=22) || j==26 || j==29 || j==30 || j==32 || j==33 || (j>=35 && j<=38) || (j>=42 && j<=56) ) { 
							continue;
					}else{
						if (i==0) {
							strRow += (strRow==''?'':'|') + tr.cells[j].childNodes[0].id;
						}else{
							strRow += (strRow==''?'':'|') + tr.cells[j].childNodes[0].value;
						}
					}
				}
			}
			// USA 1890 ===============
			else if (NoteYear.value=="1890") {
				for(var j=2; j<tr.cells.length-3; j++) {	// == j=2 means miss out cols 0 and 1 (# and pid), cells.length-3 means miss out del, ins and item # 
					if ( (j>=4 && j<=7) || j==10 || j==12 || j==13 || (j>=18 && j<=22) || j==27 || j==29 || j==30 || j==32 || j==33 || (j>=35 && j<=38) || (j>=41 && j<=54) || j==56 ) { 
							continue;
					}else{
						if (i==0) {
							strRow += (strRow==''?'':'|') + tr.cells[j].childNodes[0].id;
						}else{
							strRow += (strRow==''?'':'|') + tr.cells[j].childNodes[0].value;
						}
					}
				}
			}
			// USA 1880 ===============
			else if (NoteYear.value=="1880") {
				for(var j=2; j<tr.cells.length-3; j++) {	// == j=2 means miss out cols 0 and 1 (# and pid), cells.length-3 means miss out del, ins and item # 
					if ( (j>=3 && j<=6) || j==9 || j==10 || (j>=15 && j<=32) || (j>=33 && j<=38) || (j>=40 && j<=42) || (j>=44 && j<=45) || (j>=48 && j<=56) ) { 
							continue;
					}else{
						if (i==0) {
							strRow += (strRow==''?'':'|') + tr.cells[j].childNodes[0].id;
						}else{
							strRow += (strRow==''?'':'|') + tr.cells[j].childNodes[0].value;
						}
					}
				}
			}
			// USA 1870, 1860 or 1850 ===============
			else if (NoteYear.value=="1870" || NoteYear.value=="1860" || NoteYear.value=="1850") {
				for(var j=2; j<tr.cells.length-3; j++) {	// == j=2 means miss out cols 0 and 1 (# and pid), cells.length-3 means miss out del, ins and item # 
					if ( (j>=3 && j<=5) || j==7 || (j>=10 && j<=19) || (j>=23 && j<=38) || (j>=40 && j<=54) || j==56 ) { 
							continue;
					}else{
						if (i==0) {
							strRow += (strRow==''?'':'|') + tr.cells[j].childNodes[0].id;
						}else{
							strRow += (strRow==''?'':'|') + tr.cells[j].childNodes[0].value;
						}
					}
				}
			}
			
		}else{
			// Other country stuff
		}
		str += (str==''?'':'\n') + strRow;
	}
	var mem = document.getElementById('NOTE');
	if (Notes.value!="" && Notes.value!=null) {
		mem.value = str + "\n.end_formatted_area.\n\nNotes:\n"+Notes.value;
	} else {
		mem.value = str + "\n.end_formatted_area.\n";
	}
}

window.onload=fillInRows;

// fillInRows - can be used to pre-load a table with a header, row, or rows
function fillInRows() {
	hasLoaded = true;
	// create_header();
	// insertRowToTable();
	// addRowToTable();
}

// myRowObject - an object for storing information about the table rows
function myRowObject(	zero, one, two, three, four, five, six, seven, eight, nine, 
						ten, eleven, twelve, thirteen, fourteen, fifteen, sixteen, seventeen, eighteen, nineteen, 
						twenty, twentyone, twentytwo, twentythree, twentyfour, twentyfive, twentysix, twentyseven, twentyeight, twentynine, 
						thirty, thirtyone, thirtytwo, thirtythree, thirtyfour, thirtyfive, thirtysix, thirtyseven, thirtyeight, thirtynine, 
						forty, fortyone, fortytwo, fortythree, fortyfour, fortyfive, fortysix, fortyseven, fortyeight, fortynine, 
						fifty, fiftyone, fiftytwo, fiftythree, fiftyfour, fiftyfive, fiftysix, 
						cb, ra, fiftynine 
					) 
{
						
	this.zero		 = zero;		 // text object
	this.one		 = one;			 // input text object
	this.two		 = two;			 // input text object
	this.three		 = three;		 // input text object
	this.four		 = four;		 // input text object
	this.five		 = five;		 // input text object
	this.six		 = six;			 // input text object
	this.seven		 = seven;		 // input text object
	this.eight		 = eight;		 // input text object
	this.nine		 = nine;		 // input text object
	this.ten		 = ten;			 // input text object
	this.eleven		 = eleven;		 // input text object
	this.twelve		 = twelve;		 // input text object
	this.thirteen	 = thirteen;	 // input text object
	this.fourteen	 = fourteen;	 // input text object
	this.fifteen	 = fifteen;		 // input text object
	this.sixteen	 = sixteen;		 // input text object
	this.seventeen	 = seventeen;	 // input text object
	this.eighteen	 = eighteen;	 // input text object
	this.nineteen	 = nineteen;	 // input text object
	this.twenty		 = twenty;		 // input text object
	this.twentyone	 = twentyone;	 // input text object
	this.twentytwo	 = twentytwo;	 // input text object
	this.twentythree = twentythree;	 // input text object
	this.twentyfour	 = twentyfour;	 // input text object
	this.twentyfive	 = twentyfive;	 // input text object
	this.twentysix	 = twentysix;	 // input text object
	this.twentyseven = twentyseven;	 // input text object
	this.twentyeight = twentyeight;	 // input text object
	this.twentynine	 = twentynine;	 // input text object
	this.thirty		 = thirty;		 // input text object
	this.thirtyone	 = thirtyone;	 // input text object
	this.thirtytwo	 = thirtytwo;	 // input text object
	this.thirtythree = thirtythree;	 // input text object
	this.thirtyfour	 = thirtyfour;	 // input text object
	this.thirtyfive	 = thirtyfive;	 // input text object
	this.thirtysix	 = thirtysix;	 // input text object
	this.thirtyseven = thirtyseven;	 // input text object
	this.thirtyeight = thirtyeight;	 // input text object
	this.thirtynine	 = thirtynine;	 // input text object
	this.forty		 = forty;		 // input text object
	this.fortyone	 = fortyone;	 // input text object
	this.fortytwo	 = fortytwo;	 // input text object
	this.fortythree	 = fortythree;	 // input text object
	this.fortyfour	 = fortyfour;	 // input text object
	this.fortyfive	 = fortyfive;	 // input text object
	this.fortysix	 = fortysix;	 // input text object
	this.fortyseven	 = fortyseven;	 // input text object
	this.fortyeight	 = fortyeight;	 // input text object
	this.fortynine	 = fortynine;	 // input text object
	this.fifty		 = fifty;		 // input text object
	this.fiftyone	 = fiftyone;	 // input text object
	this.fiftytwo	 = fiftytwo;	 // input text object
	this.fiftythree	 = fiftythree;	 // input text object
	this.fiftyfour	 = fiftyfour;	 // input text object
	this.fiftyfive	 = fiftyfive;	 // input text object
	this.fiftysix	 = fiftysix;	 // input text object
	this.cb			 = cb;			 // input checkbox object
	this.ra			 = ra;			 // input radio object
	this.fiftynine	 = fiftynine;	 // text object
}

function create_header() {
		addRowToTable();
}

// insertRowToTable - inserts a row into the table (and reorders)
function insertRowToTable(pid, nam, label, gend, cond, yob, age, YMD, occu, birthpl, fbirthpl, mbirthpl) {
	if (hasLoaded) {
		// calculate marriage status -----------------------
		var cenyr = document.getElementById('censYear').value;
		// alert(cond);
		
		var tbl = document.getElementById(TABLE_NAME);
		var rowToInsertAt = tbl.tBodies[0].rows.length;
		for (var i=1; i<tbl.tBodies[0].rows.length; i++) {  // i set to 1 to avoid header row of number 0
			if (tbl.tBodies[0].rows[i].myRow && tbl.tBodies[0].rows[i].myRow.ra.getAttribute('type') == 'radio' && tbl.tBodies[0].rows[i].myRow.ra.checked) {
				rowToInsertAt = i;
				break;
			}
		}
		addRowToTable(rowToInsertAt, pid, nam, label, gend, cond, yob, age, YMD, occu, birthpl, fbirthpl, mbirthpl);
		reorderRows(tbl, rowToInsertAt);
		// var currcenyear = document.getElementById('censYear').value; 
		// changeCols(currcenyear);
		preview();
	}
}

// addRowToTable - Inserts at row 'num', or appends to the end if no arguments are passed in. Don't pass in empty strings.
function addRowToTable(num, pid, nam, label, gend, cond, yob, age2, YMD, occu, birthpl, fbirthpl, mbirthpl, cb, ra) {

// -- Temporary until insert variable are corrected
	var ibirthpl = '';
	var fbirthpl = '';
	var mbirthpl = '';
// ------------------------------------------------


	var cyear_a=document.getElementById('censYear'); 
	var cyear=cyear_a.value;
	var cctry_a=document.getElementById('censCtry'); 
	var cctry=cctry_a.value;

	var ctry = document.getElementById('censCtry').value;
	if (hasLoaded) {
		
		var tbl = document.getElementById(TABLE_NAME);
		var nextRow = tbl.tBodies[0].rows.length;
		var iteration = nextRow + ROW_BASE;
		if (num == null) { 
			num = nextRow;
			num2 = num;
		} else {
			iteration = num + ROW_BASE;
		}
		
		// Calculate age based on Census Year input ====================
		if (age2!="Age") {
			// Check if Census year filled in -------------
			if (cyear!="choose") {
				cyear=cyear;
			}else{
				alert ("You must choose a Census year first")
				return;
			}
			var diffage=(1901-cyear);
			age=(age2-diffage);
		}else{
			age="";
		}
		// add the row ==================================================
		var row = tbl.tBodies[0].insertRow(num);
		// a. Define Cells ==============================================
		var cell_0 = row.insertCell(0);				// Item Number
			cell_0.setAttribute('id', 'col_0');
			cell_0.setAttribute('name', 'col_0');
		var cell_1 = row.insertCell(1);				// Indi ID
			cell_1.setAttribute('id', 'col_1');
			cell_1.setAttribute('name', 'col_1');
		var cell_2 = row.insertCell(2);				// Name
			cell_2.setAttribute('id', 'col_2');	
			cell_2.setAttribute('name', 'col_2');
		var cell_3 = row.insertCell(3);				// Relation_1
			cell_3.setAttribute('id', 'col_3');
			cell_3.setAttribute('name', 'col_3');
		var cell_4 = row.insertCell(4);				// Conditition_1
			cell_4.setAttribute('id', 'col_4');
			cell_4.setAttribute('name', 'col_4');
		var cell_5 = row.insertCell(5);				// Assets_1
			cell_5.setAttribute('id', 'col_5');
			cell_5.setAttribute('name', 'col_5');
		var cell_6 = row.insertCell(6);				// Age_1
			cell_6.setAttribute('id', 'col_6');
			cell_6.setAttribute('name', 'col_6');
		var cell_7 = row.insertCell(7);				// Race_1
			cell_7.setAttribute('id', 'col_7');
			cell_7.setAttribute('name', 'col_7');
		var cell_8 = row.insertCell(8);				// Sex
			cell_8.setAttribute('id', 'col_8');
			cell_8.setAttribute('name', 'col_8');
		var cell_9 = row.insertCell(9);				// Race_2
			cell_9.setAttribute('id', 'col_9');
			cell_9.setAttribute('name', 'col_9');
		var cell_10 = row.insertCell(10);			// YOB (Month Year for USA)
			cell_10.setAttribute('id', 'col_10');
			cell_10.setAttribute('name', 'col_10');
		var cell_11 = row.insertCell(11);			// Age_2
			cell_11.setAttribute('id', 'col_11');
			cell_11.setAttribute('name', 'col_11');
		var cell_12 = row.insertCell(12);			// Mnth
			cell_12.setAttribute('id', 'col_12');
			cell_12.setAttribute('name', 'col_12');
		var cell_13 = row.insertCell(13);			// Relation_2
			cell_13.setAttribute('id', 'col_13');
			cell_13.setAttribute('name', 'col_13');
		var cell_14 = row.insertCell(14);			// Conditition_2
			cell_14.setAttribute('id', 'col_14');
			cell_14.setAttribute('name', 'col_14');
		var cell_15 = row.insertCell(15);			// YrsM
			cell_15.setAttribute('id', 'col_15');
			cell_15.setAttribute('name', 'col_15');
		var cell_16 = row.insertCell(16);			// ChilB
			cell_16.setAttribute('id', 'col_16');
			cell_16.setAttribute('name', 'col_16');
		var cell_17 = row.insertCell(17);			// ChilL
			cell_17.setAttribute('id', 'col_17');
			cell_17.setAttribute('name', 'col_17');
		var cell_18 = row.insertCell(18);			// ChilD
			cell_18.setAttribute('id', 'col_18');
			cell_18.setAttribute('name', 'col_18');
		var cell_19 = row.insertCell(19);			// AgeM
			cell_19.setAttribute('id', 'col_19');
			cell_19.setAttribute('name', 'col_19');
		var cell_20 = row.insertCell(20);			// Occupation_1
			cell_20.setAttribute('id', 'col_20');
			cell_20.setAttribute('name', 'col_20');
		var cell_21 = row.insertCell(21);			// Assets_2
			cell_21.setAttribute('id', 'col_21');
			cell_21.setAttribute('name', 'col_21');
		var cell_22 = row.insertCell(22);			// Birthpl_1
			cell_22.setAttribute('id', 'col_22');
			cell_22.setAttribute('name', 'col_22');
		var cell_23 = row.insertCell(23);			// POB_1
			cell_23.setAttribute('id', 'col_23');
			cell_23.setAttribute('name', 'col_23');
		var cell_24 = row.insertCell(24);			// FPOB_1
			cell_24.setAttribute('id', 'col_24');
			cell_24.setAttribute('name', 'col_24');
		var cell_25 = row.insertCell(25);			// MPOB_1
			cell_25.setAttribute('id', 'col_25');
			cell_25.setAttribute('name', 'col_25');
		var cell_26 = row.insertCell(26);			// Years in USA YrsUS
			cell_26.setAttribute('id', 'col_26');
			cell_26.setAttribute('name', 'col_26');
		var cell_27 = row.insertCell(27);			// Year of Immigration YOI_1
			cell_27.setAttribute('id', 'col_27');
			cell_27.setAttribute('name', 'col_27');
		var cell_28 = row.insertCell(28);			// Naturalized or Alien Nat/Aln_1
			cell_28.setAttribute('id', 'col_28');
			cell_28.setAttribute('name', 'col_28');
		var cell_29 = row.insertCell(29);			// Year of Naturalization YON
			cell_29.setAttribute('id', 'col_29');
			cell_29.setAttribute('name', 'col_29');
		var cell_30 = row.insertCell(30);			// If English spoken, if Not, language spoken Eng/Lang
			cell_30.setAttribute('id', 'col_30');
			cell_30.setAttribute('name', 'col_30');
		var cell_31 = row.insertCell(31);			// Occupation_2
			cell_31.setAttribute('id', 'col_31');
			cell_31.setAttribute('name', 'col_31');
		var cell_32 = row.insertCell(32);			// Health
			cell_32.setAttribute('id', 'col_32');
			cell_32.setAttribute('name', 'col_32');
		var cell_33 = row.insertCell(33);			// Industry_1
			cell_33.setAttribute('id', 'col_33');
			cell_33.setAttribute('name', 'col_33');
		var cell_34 = row.insertCell(34);			// Employment_1
			cell_34.setAttribute('id', 'col_34');
			cell_34.setAttribute('name', 'col_34');
		var cell_35 = row.insertCell(35);			// Employer EmR
			cell_35.setAttribute('id', 'col_35');
			cell_35.setAttribute('name', 'col_35');
		var cell_36 = row.insertCell(36);			// Employed EmD
			cell_36.setAttribute('id', 'col_36');
			cell_36.setAttribute('name', 'col_36');
		var cell_37 = row.insertCell(37);			// Working at Home  EmH
			cell_37.setAttribute('id', 'col_37');
			cell_37.setAttribute('name', 'col_37');
		var cell_38 = row.insertCell(38);			// Employment Neither EmN
			cell_38.setAttribute('id', 'col_38');
			cell_38.setAttribute('name', 'col_38');
		var cell_39 = row.insertCell(39);			// Education = "---" (School/Read/Write)
			cell_39.setAttribute('id', 'col_39');
			cell_39.setAttribute('name', 'col_39');
		var cell_40 = row.insertCell(40);			// Can speak English_1 (Eng_1)
			cell_40.setAttribute('id', 'col_40');
			cell_40.setAttribute('name', 'col_40');
		var cell_41 = row.insertCell(41);			// Assets_3
			cell_41.setAttribute('id', 'col_41');
			cell_41.setAttribute('name', 'col_41');
		var cell_42 = row.insertCell(42);			// Birtkpl_2
			cell_42.setAttribute('id', 'col_42');
			cell_42.setAttribute('name', 'col_42');
		var cell_43 = row.insertCell(43);			// POB_2
			cell_43.setAttribute('id', 'col_43');
			cell_43.setAttribute('name', 'col_43');
		var cell_44 = row.insertCell(44);			// BICounty (UK only)
			cell_44.setAttribute('id', 'col_44');
			cell_44.setAttribute('name', 'col_44');
		var cell_45 = row.insertCell(45);			// BOEng (UK only)
			cell_45.setAttribute('id', 'col_45');
			cell_45.setAttribute('name', 'col_45');
		var cell_46 = row.insertCell(46);			// FPOB_2
			cell_46.setAttribute('id', 'col_46');
			cell_46.setAttribute('name', 'col_46');
		var cell_47 = row.insertCell(47);			// MPOB_2
			cell_47.setAttribute('id', 'col_47');
			cell_47.setAttribute('name', 'col_47');
		var cell_48 = row.insertCell(48);			// Lang (Mother Tongue)
			cell_48.setAttribute('id', 'col_48');
			cell_48.setAttribute('name', 'col_48');
		var cell_49 = row.insertCell(49);			// YOI_2
			cell_49.setAttribute('id', 'col_49');
			cell_49.setAttribute('name', 'col_49');
		var cell_50 = row.insertCell(50);			// Nat/Aln_2
			cell_50.setAttribute('id', 'col_50');
			cell_50.setAttribute('name', 'col_50');
		var cell_51 = row.insertCell(51);			// Can speak English_2 (Eng_2)
			cell_51.setAttribute('id', 'col_51');
			cell_51.setAttribute('name', 'col_51');
		var cell_52 = row.insertCell(52);			// Occupation_3
			cell_52.setAttribute('id', 'col_52');
			cell_52.setAttribute('name', 'col_52');
		var cell_53 = row.insertCell(53);			// Industry_2
			cell_53.setAttribute('id', 'col_53');
			cell_53.setAttribute('name', 'col_53');
		var cell_54 = row.insertCell(54);			// Employment_2
			cell_54.setAttribute('id', 'col_54');
			cell_54.setAttribute('name', 'col_54');
		var cell_55 = row.insertCell(55);			// Infirmaties
			cell_55.setAttribute('id', 'col_55');
			cell_55.setAttribute('name', 'col_55');
		var cell_56 = row.insertCell(56);			// Veteran
			cell_56.setAttribute('id', 'col_56');
			cell_56.setAttribute('name', 'col_56');

		if (iteration == 0) {
			var cell_tdel = row.insertCell(57);		// text Del
			var cell_tra  = row.insertCell(58);		// text Radio
		}else{
			var cell_del = row.insertCell(57);		// Onclick = Delete Row
				cell_del.setAttribute('align', 'center');
			var cell_ra = row.insertCell(58);		// Radio button used for inserting a row, rather than adding at end of table)
		}
		
		var cell_59 = row.insertCell(59);			// Item Number
			cell_59.setAttribute('id', 'col_59');
			cell_59.setAttribute('name', 'col_59');
			cell_59.setAttribute('align', 'center');
			
		//Basic Hidden Columns
			cell_3.style.display = "none"; 
			cell_4.style.display = "none"; 
			cell_5.style.display = "none";
			cell_6.style.display = "none";
			cell_7.style.display = "none";
			cell_8.style.display = "none";
			cell_9.style.display = "none";
			cell_10.style.display = "none";
			cell_11.style.display = "none";
			cell_12.style.display = "none";
			cell_13.style.display = "none";
			cell_14.style.display = "none";
			cell_15.style.display = "none";
			cell_16.style.display = "none";
			cell_17.style.display = "none";
			cell_18.style.display = "none";
			cell_19.style.display = "none";
			cell_20.style.display = "none";
			cell_21.style.display = "none";
			cell_22.style.display = "none";
			cell_23.style.display = "none";
			cell_24.style.display = "none";
			cell_25.style.display = "none";
			cell_26.style.display = "none";
			cell_27.style.display = "none";
			cell_28.style.display = "none";
			cell_29.style.display = "none";
			cell_30.style.display = "none";
			cell_31.style.display = "none";
			cell_32.style.display = "none";
			cell_33.style.display = "none";
			cell_34.style.display = "none";
			cell_35.style.display = "none";
			cell_36.style.display = "none";
			cell_37.style.display = "none";
			cell_38.style.display = "none";
			cell_39.style.display = "none";
			cell_40.style.display = "none";
			cell_41.style.display = "none";
			cell_42.style.display = "none";
			cell_43.style.display = "none";
			cell_44.style.display = "none";
			cell_45.style.display = "none";
			cell_46.style.display = "none";
			cell_47.style.display = "none";
			cell_48.style.display = "none";
			cell_49.style.display = "none";
			cell_50.style.display = "none";
			cell_51.style.display = "none";
			cell_52.style.display = "none";
			cell_53.style.display = "none";
			cell_54.style.display = "none";
			cell_55.style.display = "none";
			cell_56.style.display = "none";
			
		// Show Cell Columns ====================================================
		
		// UK =================
		if (cctry=="UK") {
			// If 1921 or 1911========================
			if (cyear=="1911" || cyear=="1921" )	{
				cell_3.style.display = ""; 
				cell_4.style.display = ""; 
				cell_6.style.display = ""; 
				cell_8.style.display = ""; 
				cell_15.style.display = ""; 
				cell_16.style.display = ""; 
				cell_17.style.display = ""; 
				cell_18.style.display = "";
				cell_31.style.display = ""; 
				cell_33.style.display = ""; 
				cell_34.style.display = ""; 
				cell_37.style.display = ""; 
				cell_42.style.display = ""; 
				cell_55.style.display = "";
			} else 
			if (cyear=="1901")	{
				cell_3.style.display = ""; 
				cell_4.style.display = ""; 
				cell_6.style.display = ""; 
				cell_8.style.display = ""; 
				cell_31.style.display = ""; 
				cell_34.style.display = ""; 
				cell_37.style.display = ""; 
				cell_42.style.display = ""; 
				cell_55.style.display = "";
			} else
			if (cyear=="1891")	{
				cell_3.style.display = ""; 
				cell_4.style.display = ""; 
				cell_6.style.display = ""; 
				cell_8.style.display = ""; 
				cell_31.style.display = "";
				cell_35.style.display = ""; 
				cell_36.style.display = "";
				cell_38.style.display = "";
				cell_42.style.display = ""; 
				cell_55.style.display = "";
			} else
			if (cyear=="1881" || cyear=="1871" || cyear=="1861" || cyear=="1851")	{
				cell_3.style.display = ""; 
				cell_4.style.display = ""; 
				cell_6.style.display = ""; 
				cell_8.style.display = ""; 
				cell_31.style.display = ""; 
				cell_42.style.display = ""; 
				cell_55.style.display = "";
			} else
			if (cyear=="1841")	{
				cell_6.style.display = ""; 
				cell_8.style.display = ""; 
				cell_31.style.display = ""; 
				cell_44.style.display = ""; 
				cell_45.style.display = ""; 
			} 
			
		// USA =================
		}else if (cctry=="USA")	{
			// If 1930 ========================
			if (cyear=="1930" )	{
				cell_3.style.display = ""; 
				cell_5.style.display = ""; 
				cell_8.style.display = ""; 
				cell_9.style.display = ""; 
				cell_11.style.display = ""; 
				cell_14.style.display = ""; 
				cell_19.style.display = ""; 
				cell_39.style.display = "";
			//	cell_42.style.display = ""; 
				cell_43.style.display = ""; 
				cell_46.style.display = ""; 
				cell_47.style.display = ""; 
				cell_48.style.display = ""; 
				cell_49.style.display = ""; 
				cell_50.style.display = ""; 
				cell_51.style.display = ""; 
				cell_52.style.display = "";
				cell_53.style.display = "";
				cell_54.style.display = "";
				cell_56.style.display = "";
			} else
			// If 1920 ========================
			if (cyear=="1920" )	{
				cell_3.style.display = ""; 
				cell_5.style.display = ""; 
				cell_8.style.display = ""; 
				cell_9.style.display = ""; 
				cell_11.style.display = ""; 
				cell_14.style.display = ""; 
				cell_27.style.display = ""; 
				cell_28.style.display = ""; 
				cell_29.style.display = ""; 
				cell_39.style.display = "";
			//	cell_42.style.display = ""; 
				cell_43.style.display = ""; 
				cell_46.style.display = ""; 
				cell_47.style.display = ""; 
				cell_48.style.display = ""; 
				cell_51.style.display = ""; 
				cell_52.style.display = ""; 
				cell_53.style.display = ""; 
				cell_54.style.display = "";
			} else
			if (cyear=="1910" )	{
				cell_3.style.display = "";
				cell_8.style.display = "";
				cell_9.style.display = "";
				cell_11.style.display = "";
				cell_14.style.display = "";
				cell_15.style.display = "";
				cell_16.style.display = "";
				cell_17.style.display = "";
			//	cell_22.style.display = "";
				cell_23.style.display = "";
				cell_24.style.display = "";
				cell_25.style.display = "";
				cell_27.style.display = "";
				cell_28.style.display = "";
				cell_30.style.display = "";
				cell_31.style.display = "";
				cell_33.style.display = "";
				cell_34.style.display = "";
				cell_39.style.display = "";
				cell_41.style.display = ""; 
				cell_55.style.display = ""; 
				cell_56.style.display = "";
			} else
			if (cyear=="1900" )	{
				cell_3.style.display = "";
				cell_8.style.display = "";
				cell_9.style.display = "";
				cell_10.style.display = "";
				cell_11.style.display = "";
				cell_14.style.display = "";
				cell_15.style.display = "";
				cell_16.style.display = "";
				cell_17.style.display = "";
			//	cell_22.style.display = "";
				cell_23.style.display = "";
				cell_24.style.display = "";
				cell_25.style.display = "";
				cell_27.style.display = "";
				cell_28.style.display = "";
				cell_31.style.display = "";
				cell_34.style.display = "";
				cell_39.style.display = "";
				cell_40.style.display = "";
				cell_41.style.display = ""; 
			} else
			if (cyear=="1890" )	{
				cell_3.style.display = "";
				cell_8.style.display = "";
				cell_9.style.display = "";
				cell_11.style.display = "";
				cell_14.style.display = "";
				cell_15.style.display = "";
				cell_16.style.display = "";
				cell_17.style.display = "";
			//	cell_22.style.display = "";
				cell_23.style.display = "";
				cell_24.style.display = "";
				cell_25.style.display = "";
				cell_26.style.display = "";
				cell_28.style.display = "";
				cell_31.style.display = "";
				cell_34.style.display = "";
				cell_39.style.display = "";
				cell_40.style.display = "";
				cell_55.style.display = ""; 
			} else
			if (cyear=="1880" )	{
				cell_7.style.display = "";
				cell_8.style.display = "";
				cell_11.style.display = "";
				cell_12.style.display = "";
				cell_13.style.display = "";
				cell_14.style.display = "";
				cell_31.style.display = "";
				cell_32.style.display = "";
				cell_39.style.display = "";
			//	cell_42.style.display = "";
				cell_43.style.display = "";
				cell_46.style.display = "";
				cell_47.style.display = "";
			} else
			if (cyear=="1870" || cyear=="1860" || cyear=="1850" )	{
				cell_6.style.display = "";
				cell_8.style.display = "";
				cell_9.style.display = "";
			//	cell_12.style.display = "";
				cell_20.style.display = "";
				cell_21.style.display = "";
				cell_22.style.display = "";
			//	cell_23.style.display = "";
				cell_39.style.display = "";
				cell_55.style.display = "";
			}
			
		}

		
		if (iteration == 0) {
		// b. Define Header Cell elements =====================================
		
		// 0. Item Number -----------------------------------------------------
			var txt_itemNo = document.createElement('div');
				txt_itemNo.setAttribute('class', 'descriptionbox');
				txt_itemNo.className= 'descriptionbox'; //Required for IE
				txt_itemNo.style.border='0px';
				txt_itemNo.innerHTML = '#';
				txt_itemNo.setAttribute('id', '.b.Item');
				txt_itemNo.setAttribute('type', 'text');
				txt_itemNo.style.fontSize="10px";
		// 1. Indi ID ---------------------------------------------------------
			var txtInp_pid = document.createElement('div');
				txtInp_pid.setAttribute('type', 'text');
				txtInp_pid.setAttribute('class', 'descriptionbox');
				txtInp_pid.className= 'descriptionbox'; //Required for IE
				txtInp_pid.style.fontSize="10px";
				txtInp_pid.style.border='0px';
				txtInp_pid.innerHTML = ' ID ';
				txtInp_pid.setAttribute('id', '.b.Indi ID');
		// 2. Name ------------------------------------------------------------
			var txtInp_nam = document.createElement('div');
				txtInp_nam.setAttribute('class', 'descriptionbox');
				txtInp_nam.className= 'descriptionbox'; //Required for IE
				txtInp_nam.setAttribute('type', 'text');
				txtInp_nam.style.fontSize="10px";
				txtInp_nam.style.border='0px';
				txtInp_nam.innerHTML = '<a href="#" alt="'+TTEditName+'" title="'+TTEditName+'">'+HeaderName+'</a>'; 
				txtInp_nam.setAttribute('id', '.b.'+HeaderName);
		// 3. Relationship_1 --------------------------------------------------
			var txtInp_label = document.createElement('div');
				txtInp_label.setAttribute('type', 'text');
				txtInp_label.setAttribute('class', 'descriptionbox');
				txtInp_label.className= 'descriptionbox'; //Required for IE
				txtInp_label.style.fontSize="10px";
				txtInp_label.style.border='0px';
				txtInp_label.innerHTML = '<a href="#" alt="'+TTEditRela+'" title="'+TTEditRela+'">'+HeaderRela+'</a>'; 
				txtInp_label.setAttribute('id', '.b.'+HeaderRela);
		// 4. Conditition_1 ---------------------------------------------------
			var txtInp_cond = document.createElement('div');
				txtInp_cond.setAttribute('type', 'text');
				txtInp_cond.setAttribute('class', 'descriptionbox');
				txtInp_cond.className= 'descriptionbox'; //Required for IE
				txtInp_cond.style.fontSize="10px";
				txtInp_cond.style.border='0px';
				txtInp_cond.innerHTML = '<a href="#" alt="'+TTEditMCond+'" title="'+TTEditMCond+'">'+HeaderMCond+'</a>'; 
				txtInp_cond.setAttribute('id', '.b.'+HeaderMCond);
		// 5. Assets ----------------------------------------------------------
			var txtInp_assets = document.createElement('div');
				txtInp_assets.setAttribute('type', 'text');
				txtInp_assets.setAttribute('class', 'descriptionbox');
				txtInp_assets.className= 'descriptionbox'; //Required for IE
				txtInp_assets.style.fontSize="10px";
				txtInp_assets.style.border='0px';
				txtInp_assets.innerHTML = '<a href="#" alt="'+TTEditAsset+'" title="'+TTEditAsset+'">'+HeaderAsset+'</a>'; 
				txtInp_assets.setAttribute('id', '.b.'+HeaderAsset);
		// 6. Age_1 -----------------------------------------------------------
			var txtInp_age = document.createElement('div');
				txtInp_age.setAttribute('type', 'text');
				txtInp_age.setAttribute('class', 'descriptionbox');
				txtInp_age.className= 'descriptionbox'; //Required for IE
				txtInp_age.style.fontSize="10px";
				txtInp_age.style.border='0px';
				txtInp_age.innerHTML = '<a href="#" alt="'+TTEditAge+'" title="'+TTEditAge+'">'+HeaderAge+'</a>'; 
				txtInp_age.setAttribute('id', '.b.'+HeaderAge);
		// 7. Race_1 ----------------------------------------------------------
			var txtInp_race = document.createElement('div');
				txtInp_race.setAttribute('type', 'text');
				txtInp_race.setAttribute('class', 'descriptionbox');
				txtInp_race.className= 'descriptionbox'; //Required for IE
				txtInp_race.style.fontSize="10px";
				txtInp_race.style.border='0px';
				txtInp_race.innerHTML = '<a href="#" alt="'+TTEditRace+'" title="'+TTEditRace+'">'+HeaderRace+'</a>'; 
				txtInp_race.setAttribute('id', '.b.'+HeaderRace);
		// 8. Sex -------------------------------------------------------------
			var txtInp_gend = document.createElement('div');
				txtInp_gend.setAttribute('type', 'text');
				txtInp_gend.setAttribute('class', 'descriptionbox');
				txtInp_gend.className= 'descriptionbox'; //Required for IE
				txtInp_gend.style.fontSize="10px";
				txtInp_gend.style.border='0px';
				txtInp_gend.innerHTML = '<a href="#" alt="'+TTEditSex+'" title="'+TTEditSex+'">'+HeaderSex+'</a>'; 
				txtInp_gend.setAttribute('id', '.b.'+HeaderSex);
		// 9. Race_2 ----------------------------------------------------------
			var txtInp_race2 = document.createElement('div');
				txtInp_race2.setAttribute('type', 'text');
				txtInp_race2.setAttribute('class', 'descriptionbox');
				txtInp_race2.className= 'descriptionbox'; //Required for IE
				txtInp_race2.style.fontSize="10px";
				txtInp_race2.style.border='0px';
				txtInp_race2.innerHTML = '<a href="#" alt="'+TTEditRace+'" title="'+TTEditRace+'">'+HeaderRace+'</a>'; 
				txtInp_race2.setAttribute('id', '.b.'+HeaderRace);
		// 10. DOB/YOB ---------------------------------------------------------
			var txtInp_yob = document.createElement('div');
				txtInp_yob.setAttribute('type', 'text');
				txtInp_yob.setAttribute('class', 'descriptionbox');
				txtInp_yob.className= 'descriptionbox'; //Required for IE
				txtInp_yob.style.fontSize="10px";
				txtInp_yob.style.border='0px';
				txtInp_yob.innerHTML = '<a href="#" alt="'+TTEditYOB+'" title="'+TTEditYOB+'">'+HeaderYOB+'</a>'; 
				txtInp_yob.setAttribute('id', '.b.'+HeaderYOB);
		// 11. Age_2 -----------------------------------------------------------
			var txtInp_age2 = document.createElement('div');
				txtInp_age2.setAttribute('type', 'text');
				txtInp_age2.setAttribute('class', 'descriptionbox');
				txtInp_age2.className= 'descriptionbox'; //Required for IE
				txtInp_age2.style.fontSize="10px";
				txtInp_age2.style.border='0px';
				txtInp_age2.innerHTML = '<a href="#" alt="'+TTEditAge+'" title="'+TTEditAge+'">'+HeaderAge+'</a>'; 
				txtInp_age2.setAttribute('id', '.b.'+HeaderAge);
		// 12. Bmth (if born within census year) -------------------------------
			var txtInp_bmth = document.createElement('div');
				txtInp_bmth.setAttribute('type', 'text');
				txtInp_bmth.setAttribute('class', 'descriptionbox');
				txtInp_bmth.className= 'descriptionbox'; //Required for IE
				txtInp_bmth.style.fontSize="10px";
				txtInp_bmth.style.border='0px';
				txtInp_bmth.innerHTML = '<a href="#" alt="'+TTEditBmth+'" title="'+TTEditBmth+'">'+HeaderBmth+'</a>'; 
				txtInp_bmth.setAttribute('id', '.b.'+HeaderBmth);
		// 13. Relationship_2 --------------------------------------------------
			var txtInp_label2 = document.createElement('div');
				txtInp_label2.setAttribute('type', 'text');
				txtInp_label2.setAttribute('class', 'descriptionbox');
				txtInp_label2.className= 'descriptionbox'; //Required for IE
				txtInp_label2.style.fontSize="10px";
				txtInp_label2.style.border='0px';
				txtInp_label2.innerHTML = '<a href="#" alt="'+TTEditRela+'" title="'+TTEditRela+'">'+HeaderRela+'</a>'; 
				txtInp_label2.setAttribute('id', '.b.'+HeaderRela);
		// 14. Conditition_2 ---------------------------------------------------
			var txtInp_cond2 = document.createElement('div');
				txtInp_cond2.setAttribute('type', 'text');
				txtInp_cond2.setAttribute('class', 'descriptionbox');
				txtInp_cond2.className= 'descriptionbox'; //Required for IE
				txtInp_cond2.style.fontSize="10px";
				txtInp_cond2.style.border='0px';
				txtInp_cond2.innerHTML = '<a href="#" alt="'+TTEditMCond+'" title="'+TTEditMCond+'">'+HeaderMCond+'</a>'; 
				txtInp_cond2.setAttribute('id', '.b.'+HeaderMCond);
		// 15. Years Married ---------------------------------------------------
			var txtInp_yrsm = document.createElement('div');
				txtInp_yrsm.setAttribute('type', 'text');
				txtInp_yrsm.setAttribute('class', 'descriptionbox');
				txtInp_yrsm.className= 'descriptionbox'; //Required for IE
				txtInp_yrsm.style.fontSize="10px";
				txtInp_yrsm.style.border='0px';
				txtInp_yrsm.innerHTML = '<a href="#" alt="'+TTEditYrsM+'" title="'+TTEditYrsM+'">'+HeaderYrsM+'</a>'; 
				txtInp_yrsm.setAttribute('id', '.b.'+HeaderYrsM);
		// 16. Children Born Alive ---------------------------------------------
			var txtInp_chilB = document.createElement('div');
				txtInp_chilB.setAttribute('type', 'text');
				txtInp_chilB.setAttribute('class', 'descriptionbox');
				txtInp_chilB.className= 'descriptionbox'; //Required for IE
				txtInp_chilB.style.fontSize="10px";
				txtInp_chilB.style.border='0px';
				txtInp_chilB.innerHTML = '<a href="#" alt="'+TTEditChilB+'" title="'+TTEditChilB+'">'+HeaderChilB+'</a>'; 
				txtInp_chilB.setAttribute('id', '.b.'+HeaderChilB);
		// 17. Children Still Living -------------------------------------------
			var txtInp_chilL = document.createElement('div');
				txtInp_chilL.setAttribute('type', 'text');
				txtInp_chilL.setAttribute('class', 'descriptionbox');
				txtInp_chilL.className= 'descriptionbox'; //Required for IE
				txtInp_chilL.style.fontSize="10px";
				txtInp_chilL.style.border='0px';
				txtInp_chilL.innerHTML = '<a href="#" alt="'+TTEditChilL+'" title="'+TTEditChilL+'">'+HeaderChilL+'</a>'; 
				txtInp_chilL.setAttribute('id', '.b.'+HeaderChilL);
		// 18. Children who have Died ------------------------------------------
			var txtInp_chilD = document.createElement('div');
				txtInp_chilD.setAttribute('type', 'text');
				txtInp_chilD.setAttribute('class', 'descriptionbox');
				txtInp_chilD.className= 'descriptionbox'; //Required for IE
				txtInp_chilD.style.fontSize="10px";
				txtInp_chilD.style.border='0px';
				txtInp_chilD.innerHTML = '<a href="#" alt="'+TTEditChilD+'" title="'+TTEditChilD+'">'+HeaderChilD+'</a>'; 
				txtInp_chilD.setAttribute('id', '.b.'+HeaderChilD);
		// 19. Age at first Marriage -------------------------------------------
			var txtInp_ageM = document.createElement('div');
				txtInp_ageM.setAttribute('type', 'text');
				txtInp_ageM.setAttribute('class', 'descriptionbox');
				txtInp_ageM.className= 'descriptionbox'; //Required for IE
				txtInp_ageM.style.fontSize="10px";
				txtInp_ageM.style.border='0px';
				txtInp_ageM.innerHTML = '<a href="#" alt="'+TTEditAgeM+'" title="'+TTEditAgeM+'">'+HeaderAgeM+'</a>'; 
				txtInp_ageM.setAttribute('id', '.b.'+HeaderAgeM);
		// 20. Occupation_1 ----------------------------------------------------
			var txtInp_occu = document.createElement('div');
				txtInp_occu.setAttribute('type', 'text');
				txtInp_occu.setAttribute('class', 'descriptionbox');
				txtInp_occu.className= 'descriptionbox'; //Required for IE
				txtInp_occu.style.fontSize="10px";
				txtInp_occu.style.border='0px';
				txtInp_occu.innerHTML = '<a href="#" alt="'+TTEditOccu+'" title="'+TTEditOccu+'">'+HeaderOccu+'</a>'; 
				txtInp_occu.setAttribute('id', '.b.'+HeaderOccu);
		// 21. Assets_2 --------------------------------------------------------
			var txtInp_assets2 = document.createElement('div');
				txtInp_assets2.setAttribute('type', 'text');
				txtInp_assets2.setAttribute('class', 'descriptionbox');
				txtInp_assets2.className= 'descriptionbox'; //Required for IE
				txtInp_assets2.style.fontSize="10px";
				txtInp_assets2.style.border='0px';
				txtInp_assets2.innerHTML = '<a href="#" alt="'+TTEditAsset+'" title="'+TTEditAsset+'">'+HeaderAsset+'</a>'; 
				txtInp_assets2.setAttribute('id', '.b.'+HeaderAsset);
		// 22. Birth Place_1 -----------------------------------------------
			var txtInp_birthpl = document.createElement('div');
				txtInp_birthpl.setAttribute('type', 'text');
				txtInp_birthpl.setAttribute('class', 'descriptionbox');
				txtInp_birthpl.className= 'descriptionbox'; //Required for IE
				txtInp_birthpl.style.fontSize="10px";
				txtInp_birthpl.style.border='0px';
				txtInp_birthpl.innerHTML = '<a href="#" alt="'+TTEditBplace+'" title="'+TTEditBplace+'">'+HeaderBplace+'</a>';
				txtInp_birthpl.setAttribute('id', '.b.'+HeaderBplace);
		// 23. Indi Birth Place_1 -----------------------------------------------
			var txtInp_ibirthpl = document.createElement('div');
				txtInp_ibirthpl.setAttribute('type', 'text');
				txtInp_ibirthpl.setAttribute('class', 'descriptionbox');
				txtInp_ibirthpl.className= 'descriptionbox'; //Required for IE
				txtInp_ibirthpl.style.fontSize="10px";
				txtInp_ibirthpl.style.border='0px';
				txtInp_ibirthpl.innerHTML = '<a href="#" alt="'+TTEditBP+'" title="'+TTEditBP+'">'+HeaderBP+'</a>';
				txtInp_ibirthpl.setAttribute('id', '.b.'+HeaderBP);
		// 24. Fathers Birth Place_1 ---------------------------------------------
			var txtInp_fbirthpl = document.createElement('div');
				txtInp_fbirthpl.setAttribute('type', 'text');
				txtInp_fbirthpl.setAttribute('class', 'descriptionbox');
				txtInp_fbirthpl.className= 'descriptionbox'; //Required for IE
				txtInp_fbirthpl.style.fontSize="10px";
				txtInp_fbirthpl.style.border='0px';
				txtInp_fbirthpl.innerHTML = '<a href="#" alt="'+TTEditFBP+'" title="'+TTEditFBP+'">'+HeaderFBP+'</a>';
				txtInp_fbirthpl.setAttribute('id', '.b.'+HeaderFBP);
		// 24. Mothers Birth Place_1 ---------------------------------------------
			var txtInp_mbirthpl = document.createElement('div');
				txtInp_mbirthpl.setAttribute('type', 'text');
				txtInp_mbirthpl.setAttribute('class', 'descriptionbox');
				txtInp_mbirthpl.className= 'descriptionbox'; //Required for IE
				txtInp_mbirthpl.style.fontSize="10px";
				txtInp_mbirthpl.style.border='0px';
				txtInp_mbirthpl.innerHTML = '<a href="#" alt="'+TTEditMBP+'" title="'+TTEditMBP+'">'+HeaderMBP+'</a>';
				txtInp_mbirthpl.setAttribute('id', '.b.'+HeaderMBP);
		// 26. Years in USA ----------------------------------------------------
			var txtInp_yrsUS = document.createElement('div');
				txtInp_yrsUS.setAttribute('type', 'text');
				txtInp_yrsUS.setAttribute('class', 'descriptionbox');
				txtInp_yrsUS.className= 'descriptionbox'; //Required for IE
				txtInp_yrsUS.style.fontSize="10px";
				txtInp_yrsUS.style.border='0px';
				txtInp_yrsUS.innerHTML = '<a href="#" alt="'+TTEditYrsUS+'" title="'+TTEditYrsUS+'">'+HeaderYrsUS+'</a>';
				txtInp_yrsUS.setAttribute('id', '.b.'+HeaderYrsUS);
		// 27. Year of Immigration YOI_1 ----------------------------------------
			var txtInp_yoi1 = document.createElement('div');
				txtInp_yoi1.setAttribute('type', 'text');
				txtInp_yoi1.setAttribute('class', 'descriptionbox');
				txtInp_yoi1.className= 'descriptionbox'; //Required for IE
				txtInp_yoi1.style.fontSize="10px";
				txtInp_yoi1.style.border='0px';
				txtInp_yoi1.innerHTML = '<a href="#" alt="'+TTEditYOI+'" title="'+TTEditYOI+'">'+HeaderYOI+'</a>';
				txtInp_yoi1.setAttribute('id', '.b.'+HeaderYOI);
		// 28. Natualized or Alien_1 ----------------------------------------
			var txtInp_na1 = document.createElement('div');
				txtInp_na1.setAttribute('type', 'text');
				txtInp_na1.setAttribute('class', 'descriptionbox');
				txtInp_na1.className= 'descriptionbox'; //Required for IE
				txtInp_na1.style.fontSize="10px";
				txtInp_na1.style.border='0px';
				txtInp_na1.innerHTML = '<a href="#" alt="'+TTEditNA+'" title="'+TTEditNA+'">'+HeaderNA+'</a>';
				txtInp_na1.setAttribute('id', '.b.'+HeaderNA);
		// 29. Year of Naturalization YON_1 ----------------------------------------
			var txtInp_yon = document.createElement('div');
				txtInp_yon.setAttribute('type', 'text');
				txtInp_yon.setAttribute('class', 'descriptionbox');
				txtInp_yon.className= 'descriptionbox'; //Required for IE
				txtInp_yon.style.fontSize="10px";
				txtInp_yon.style.border='0px';
				txtInp_yon.innerHTML = '<a href="#" alt="'+TTEditYON+'" title="'+TTEditYON+'">'+HeaderYON+'</a>';
				txtInp_yon.setAttribute('id', '.b.'+HeaderYON);
		// 30. English if spoken, or if not, Language spoken Eng/Lang ------------------------
			var txtInp_englang = document.createElement('div');
				txtInp_englang.setAttribute('type', 'text');
				txtInp_englang.setAttribute('class', 'descriptionbox');
				txtInp_englang.className= 'descriptionbox'; //Required for IE
				txtInp_englang.style.fontSize="10px";
				txtInp_englang.style.border='0px';
				txtInp_englang.innerHTML = '<a href="#" alt="'+TTEditEngL+'" title="'+TTEditEngL+'">'+HeaderEngL+'</a>';
				txtInp_englang.setAttribute('id', '.b.'+HeaderEngL);
		// 31. Occupation_2 -----------------------------------------------------
			var txtInp_occu2 = document.createElement('div');
				txtInp_occu2.setAttribute('type', 'text');
				txtInp_occu2.setAttribute('class', 'descriptionbox');
				txtInp_occu2.className= 'descriptionbox'; //Required for IE
				txtInp_occu2.style.fontSize="10px";
				txtInp_occu2.style.border='0px';
				txtInp_occu2.innerHTML = '<a href="#" alt="'+TTEditOccu+'" title="'+TTEditOccu+'">'+HeaderOccu+'</a>'; 
				txtInp_occu2.setAttribute('id', '.b.'+HeaderOccu);
		// 32. Health health -------------------------------------------------------
			var txtInp_health = document.createElement('div');
				txtInp_health.setAttribute('type', 'text');
				txtInp_health.setAttribute('class', 'descriptionbox');
				txtInp_health.className= 'descriptionbox'; //Required for IE
				txtInp_health.style.fontSize="10px";
				txtInp_health.style.border='0px';
				txtInp_health.innerHTML = '<a href="#" alt="'+TTEditHealth+'" title="'+TTEditHealth+'">'+HeaderHealth+'</a>'; 
				txtInp_health.setAttribute('id', '.b.'+HeaderHealth);
		// 33. Industry ind_1 ------------------------------------------------------
			var txtInp_ind1 = document.createElement('div');
				txtInp_ind1.setAttribute('type', 'text');
				txtInp_ind1.setAttribute('class', 'descriptionbox');
				txtInp_ind1.className= 'descriptionbox'; //Required for IE
				txtInp_ind1.style.fontSize="10px";
				txtInp_ind1.style.border='0px';
				txtInp_ind1.innerHTML = '<a href="#" alt="'+TTEditInd+'" title="'+TTEditInd+'">'+HeaderInd+'</a>'; 
				txtInp_ind1.setAttribute('id', '.b.'+HeaderInd);
		// 34. Employ_1 ------------------------------------------------------------
			var txtInp_emp1 = document.createElement('div');
				txtInp_emp1.setAttribute('type', 'text');
				txtInp_emp1.setAttribute('class', 'descriptionbox');
				txtInp_emp1.className= 'descriptionbox'; //Required for IE
				txtInp_emp1.style.fontSize="10px";
				txtInp_emp1.style.border='0px';
				txtInp_emp1.innerHTML = '<a href="#" alt="'+TTEditEmp+'" title="'+TTEditEmp+'">'+HeaderEmp+'</a>';
				txtInp_emp1.setAttribute('id', '.b.'+HeaderEmp);
		// 35. Employer - EmR-----------------------------------------------------------
			var txtInp_emR = document.createElement('div');
				txtInp_emR.setAttribute('type', 'text');
				txtInp_emR.setAttribute('class', 'descriptionbox');
				txtInp_emR.className= 'descriptionbox'; //Required for IE
				txtInp_emR.style.fontSize="10px";
				txtInp_emR.style.border='0px';
				txtInp_emR.innerHTML = '<a href="#" alt="'+TTEditEmR+'" title="'+TTEditEmR+'">'+HeaderEmR+'</a>';
				txtInp_emR.setAttribute('id', '.b.'+HeaderEmR);
		// 36. Employed EmD ------------------------------------------------------------
			var txtInp_emD = document.createElement('div');
				txtInp_emD.setAttribute('type', 'text');
				txtInp_emD.setAttribute('class', 'descriptionbox');
				txtInp_emD.className= 'descriptionbox'; //Required for IE
				txtInp_emD.style.fontSize="10px";
				txtInp_emD.style.border='0px';
				txtInp_emD.innerHTML = '<a href="#" alt="'+TTEditEmD+'" title="'+TTEditEmD+'">'+HeaderEmD+'</a>';
				txtInp_emD.setAttribute('id', '.b.'+HeaderEmD);
		// 37. Employed at Home EmH ----------------------------------------------------
			var txtInp_emH = document.createElement('div');
				txtInp_emH.setAttribute('type', 'text');
				txtInp_emH.setAttribute('class', 'descriptionbox');
				txtInp_emH.className= 'descriptionbox'; //Required for IE
				txtInp_emH.style.fontSize="10px";
				txtInp_emH.style.border='0px';
				txtInp_emH.innerHTML = '<a href="#" alt="'+TTEditEmH+'" title="'+TTEditEmH+'">'+HeaderEmH+'</a>';
				txtInp_emH.setAttribute('id', '.b.'+HeaderEmH);
		// 38. Not Employed EmN --------------------------------------------------------
			var txtInp_emN = document.createElement('div');
				txtInp_emN.setAttribute('type', 'text');
				txtInp_emN.setAttribute('class', 'descriptionbox');
				txtInp_emN.className= 'descriptionbox'; //Required for IE
				txtInp_emN.style.fontSize="10px";
				txtInp_emN.style.border='0px';
				txtInp_emN.innerHTML = '<a href="#" alt="'+TTEditEmN+'" title="'+TTEditEmN+'">'+HeaderEmN+'</a>';
				txtInp_emN.setAttribute('id', '.b.'+HeaderEmN);
		// 39. Education -----------------------------------------------------------
			var txtInp_educ = document.createElement('div');
				txtInp_educ.setAttribute('type', 'text');
				txtInp_educ.setAttribute('class', 'descriptionbox');
				txtInp_educ.className= 'descriptionbox'; //Required for IE
				txtInp_educ.style.fontSize="10px";
				txtInp_educ.style.border='0px';
				txtInp_educ.innerHTML = '<a href="#" alt="'+TTEditEduc+'" title="'+TTEditEduc+'">'+HeaderEduc+'</a>';
				txtInp_educ.setAttribute('id', '.b.'+HeaderEduc);
		// 40. English Spoken y/n eng_1 ----------------------------------------
			var txtInp_eng1 = document.createElement('div');
				txtInp_eng1.setAttribute('type', 'text');
				txtInp_eng1.setAttribute('class', 'descriptionbox');
				txtInp_eng1.className= 'descriptionbox'; //Required for IE
				txtInp_eng1.style.fontSize="10px";
				txtInp_eng1.style.border='0px';
				txtInp_eng1.innerHTML = '<a href="#" alt="'+TTEditEng+'" title="'+TTEditEng+'">'+HeaderEng+'</a>';
				txtInp_eng1.setAttribute('id', '.b.'+HeaderEng);
		// 41. Assets_3 --------------------------------------------------------
			var txtInp_assets3 = document.createElement('div');
				txtInp_assets3.setAttribute('type', 'text');
				txtInp_assets3.setAttribute('class', 'descriptionbox');
				txtInp_assets3.className= 'descriptionbox'; //Required for IE
				txtInp_assets3.style.fontSize="10px";
				txtInp_assets3.style.border='0px';
				txtInp_assets3.innerHTML = '<a href="#" alt="'+TTEditAsset+'" title="'+TTEditAsset+'">'+HeaderAsset+'</a>'; 
				txtInp_assets3.setAttribute('id', '.b.'+HeaderAsset);
		// 42. Birth Place_2 -----------------------------------------------
			var txtInp_birthpl2 = document.createElement('div');
				txtInp_birthpl2.setAttribute('type', 'text');
				txtInp_birthpl2.setAttribute('class', 'descriptionbox');
				txtInp_birthpl2.className= 'descriptionbox'; //Required for IE
				txtInp_birthpl2.style.fontSize="10px";
				txtInp_birthpl2.style.border='0px';
				txtInp_birthpl2.innerHTML = '<a href="#" alt="'+TTEditBplace+'" title="'+TTEditBplace+'">'+HeaderBplace+'</a>';
				txtInp_birthpl2.setAttribute('id', '.b.'+HeaderBplace);
		// 43. Indi Birth Place_2 ---------------------------------------------
			var txtInp_ibirthpl2 = document.createElement('div');
				txtInp_ibirthpl2.setAttribute('type', 'text');
				txtInp_ibirthpl2.setAttribute('class', 'descriptionbox');
				txtInp_ibirthpl2.className= 'descriptionbox'; //Required for IE
				txtInp_ibirthpl2.style.fontSize="10px";
				txtInp_ibirthpl2.style.border='0px';
				txtInp_ibirthpl2.innerHTML = '<a href="#" alt="'+TTEditBP+'" title="'+TTEditBP+'">'+HeaderBP+'</a>';
				txtInp_ibirthpl2.setAttribute('id', '.b.'+HeaderBP);
		// 44. Born in Same Country (ENG) -----------------------------------------------
			var txtInp_bic = document.createElement('div');
				txtInp_bic.setAttribute('type', 'text');
				txtInp_bic.setAttribute('class', 'descriptionbox');
				txtInp_bic.className= 'descriptionbox'; //Required for IE
				txtInp_bic.style.fontSize="10px";
				txtInp_bic.style.border='0px';
				txtInp_bic.innerHTML = '<a href="#" alt="'+TTEditBIC+'" title="'+TTEditBIC+'">'+HeaderBIC+'</a>';
				txtInp_bic.setAttribute('id', '.b.'+HeaderBIC)
		// 45. Born outside England (SCO, IRE, WAL, FOReign ----------------------------
			var txtInp_boe = document.createElement('div');
				txtInp_boe.setAttribute('type', 'text');
				txtInp_boe.setAttribute('class', 'descriptionbox');
				txtInp_boe.className= 'descriptionbox'; //Required for IE
				txtInp_boe.style.fontSize="10px";
				txtInp_boe.style.border='0px';
				txtInp_boe.innerHTML = '<a href="#" alt="'+TTEditBOE+'" title="'+TTEditBOE+'">'+HeaderBOE+'</a>';
				txtInp_boe.setAttribute('id', '.b.'+HeaderBOE)
		// 46. Fathers Birth Place_2 ---------------------------------------------
			var txtInp_fbirthpl2 = document.createElement('div');
				txtInp_fbirthpl2.setAttribute('type', 'text');
				txtInp_fbirthpl2.setAttribute('class', 'descriptionbox');
				txtInp_fbirthpl2.className= 'descriptionbox'; //Required for IE
				txtInp_fbirthpl2.style.fontSize="10px";
				txtInp_fbirthpl2.style.border='0px';
				txtInp_fbirthpl2.innerHTML = '<a href="#" alt="'+TTEditFBP+'" title="'+TTEditFBP+'">'+HeaderFBP+'</a>';
				txtInp_fbirthpl2.setAttribute('id', '.b.'+HeaderFBP);
		// 47. Mothers Birth Place_2 ---------------------------------------------
			var txtInp_mbirthpl2 = document.createElement('div');
				txtInp_mbirthpl2.setAttribute('type', 'text');
				txtInp_mbirthpl2.setAttribute('class', 'descriptionbox');
				txtInp_mbirthpl2.className= 'descriptionbox'; //Required for IE
				txtInp_mbirthpl2.style.fontSize="10px";
				txtInp_mbirthpl2.style.border='0px';
				txtInp_mbirthpl2.innerHTML = '<a href="#" alt="'+TTEditMBP+'" title="'+TTEditMBP+'">'+HeaderMBP+'</a>';
				txtInp_mbirthpl2.setAttribute('id', '.b.'+HeaderMBP);
		// 48. Native Language ----------------------------------------------------
			var txtInp_lang = document.createElement('div');
				txtInp_lang.setAttribute('type', 'text');
				txtInp_lang.setAttribute('class', 'descriptionbox');
				txtInp_lang.className= 'descriptionbox'; //Required for IE
				txtInp_lang.style.fontSize="10px";
				txtInp_lang.style.border='0px';
				txtInp_lang.innerHTML = '<a href="#" alt="'+TTEditNL+'" title="'+TTEditNL+'">'+HeaderNL+'</a>';
				txtInp_lang.setAttribute('id', '.b.'+HeaderNL);
		// 49. Year of Immigration YOI_1 ----------------------------------------
			var txtInp_yoi2 = document.createElement('div');
				txtInp_yoi2.setAttribute('type', 'text');
				txtInp_yoi2.setAttribute('class', 'descriptionbox');
				txtInp_yoi2.className= 'descriptionbox'; //Required for IE
				txtInp_yoi2.style.fontSize="10px";
				txtInp_yoi2.style.border='0px';
				txtInp_yoi2.innerHTML = '<a href="#" alt="'+TTEditYOI+'" title="'+TTEditYOI+'">'+HeaderYOI+'</a>';
				txtInp_yoi2.setAttribute('id', '.b.'+HeaderYOI);
		// 50. Natualized or Alien_1 ----------------------------------------
			var txtInp_na2 = document.createElement('div');
				txtInp_na2.setAttribute('type', 'text');
				txtInp_na2.setAttribute('class', 'descriptionbox');
				txtInp_na2.className= 'descriptionbox'; //Required for IE
				txtInp_na2.style.fontSize="10px";
				txtInp_na2.style.border='0px';
				txtInp_na2.innerHTML = '<a href="#" alt="'+TTEditNA+'" title="'+TTEditNA+'">'+HeaderNA+'</a>';
				txtInp_na2.setAttribute('id', '.b.'+HeaderNA);
		// 51. English Spoken y/n eng_2 ----------------------------------------
			var txtInp_eng2 = document.createElement('div');
				txtInp_eng2.setAttribute('type', 'text');
				txtInp_eng2.setAttribute('class', 'descriptionbox');
				txtInp_eng2.className= 'descriptionbox'; //Required for IE
				txtInp_eng2.style.fontSize="10px";
				txtInp_eng2.style.border='0px';
				txtInp_eng2.innerHTML = '<a href="#" alt="'+TTEditEng+'" title="'+TTEditEng+'">'+HeaderEng+'</a>';
				txtInp_eng2.setAttribute('id', '.b.'+HeaderEng);
		// 52. Occupation_3 -----------------------------------------------------
			var txtInp_occu3 = document.createElement('div');
				txtInp_occu3.setAttribute('type', 'text');
				txtInp_occu3.setAttribute('class', 'descriptionbox');
				txtInp_occu3.className= 'descriptionbox'; //Required for IE
				txtInp_occu3.style.fontSize="10px";
				txtInp_occu3.style.border='0px';
				txtInp_occu3.innerHTML = '<a href="#" alt="'+TTEditOccu+'" title="'+TTEditOccu+'">'+HeaderOccu+'</a>'; 
				txtInp_occu3.setAttribute('id', '.b.'+HeaderOccu);
		// 53. Industry ind_2 ------------------------------------------------------
			var txtInp_ind2 = document.createElement('div');
				txtInp_ind2.setAttribute('type', 'text');
				txtInp_ind2.setAttribute('class', 'descriptionbox');
				txtInp_ind2.className= 'descriptionbox'; //Required for IE
				txtInp_ind2.style.fontSize="10px";
				txtInp_ind2.style.border='0px';
				txtInp_ind2.innerHTML = '<a href="#" alt="'+TTEditInd+'" title="'+TTEditInd+'">'+HeaderInd+'</a>'; 
				txtInp_ind2.setAttribute('id', '.b.'+HeaderInd);
		// 54. Employ_2 ------------------------------------------------------------
			var txtInp_emp2 = document.createElement('div');
				txtInp_emp2.setAttribute('type', 'text');
				txtInp_emp2.setAttribute('class', 'descriptionbox');
				txtInp_emp2.className= 'descriptionbox'; //Required for IE
				txtInp_emp2.style.fontSize="10px";
				txtInp_emp2.style.border='0px';
				txtInp_emp2.innerHTML = '<a href="#" alt="'+TTEditEmp+'" title="'+TTEditEmp+'">'+HeaderEmp+'</a>';
				txtInp_emp2.setAttribute('id', '.b.'+HeaderEmp);
		// 55. Infirmaties Infirm -------------------------------------------------------
			var txtInp_infirm = document.createElement('div');
				txtInp_infirm.setAttribute('type', 'text');
				txtInp_infirm.setAttribute('class', 'descriptionbox');
				txtInp_infirm.className= 'descriptionbox'; //Required for IE
				txtInp_infirm.style.fontSize="10px";
				txtInp_infirm.style.border='0px';
				txtInp_infirm.innerHTML = '<a href="#" alt="'+TTEditInfirm+'" title="'+TTEditInfirm+'">'+HeaderInfirm+'</a>';
				txtInp_infirm.setAttribute('id', '.b.'+HeaderInfirm);
		// 56. Veteran ? ------------------------------------------------------
			var txtInp_vet = document.createElement('div');
				txtInp_vet.setAttribute('type', 'text');
				txtInp_vet.setAttribute('class', 'descriptionbox');
				txtInp_vet.className= 'descriptionbox'; //Required for IE
				txtInp_vet.style.fontSize="10px";
				txtInp_vet.style.border='0px';
				txtInp_vet.innerHTML = '<a href="#" alt="'+TTEditVet+'" title="'+TTEditVet+'">'+HeaderVet+'</a>';
				txtInp_vet.setAttribute('id', '.b.'+HeaderVet);
				
		// 57. Text Del Button ------------------------------------------------- 
			var txtInp_tdel = document.createElement('div');
				txtInp_tdel.setAttribute('type', 'text');
				txtInp_tdel.setAttribute('class', 'descriptionbox');
				txtInp_tdel.className= 'descriptionbox'; //Required for IE
				txtInp_tdel.style.fontSize="10px";
				txtInp_tdel.style.border='0px';
				txtInp_tdel.innerHTML = 'Del';
				txtInp_tdel.setAttribute('id', this);
		// 58. Text Radio Button ----------------------------------------------- 
			var txtInp_tra = document.createElement('div');
				txtInp_tra.setAttribute('type', 'text');
				txtInp_tra.setAttribute('class', 'descriptionbox');
				txtInp_tra.className= 'descriptionbox'; //Required for IE
				txtInp_tra.style.fontSize="10px";
				txtInp_tra.style.border='0px';
				txtInp_tra.innerHTML = 'Ins';
		// 59. Item Number 2 -------------------------------------------------
			var txt_itemNo2 = document.createElement('div');
				txt_itemNo2.setAttribute('class', 'descriptionbox');
				txt_itemNo2.className= 'descriptionbox'; //Required for IE
				txt_itemNo2.style.border='0px';
				txt_itemNo2.innerHTML = '#';
				txt_itemNo2.setAttribute('id', '.b.Item');
				txt_itemNo2.setAttribute('type', 'text');
				txt_itemNo2.style.fontSize="10px";

		}else{

		// c. Define Cell Elements ======================================
			var txtcolor = "#0000FF";
			
		// 0. Item Number ---------------------------------------------------
			var txt_itemNo = document.createTextNode(iteration);
		// 1. Indi ID -------------------------------------------------------
				if ( pid == ''){
					var txtInp_pid = document.createElement('input');
					var txtcolor = "#000000";
					txtInp_pid.setAttribute('type', 'checkbox');
					//txtInp_pid.checked='';
					if (txtInp_pid.checked!=''){
						txtInp_pid.setAttribute('value', 'no');
					}else{
						txtInp_pid.setAttribute('value', 'add');
					}
					txtInp_pid.setAttribute('id', INPUT_NAME_PREFIX + iteration + '_1');
					txtInp_pid.setAttribute('size', '4');
					txtInp_pid.style.color=txtcolor;
					txtInp_pid.style.fontSize="10px";
				}else{
					var txtInp_pid = document.createElement('div');
						txtInp_pid.style.border='0px';
						txtInp_pid.innerHTML = pid;
						// txtInp_pid.setAttribute('id', '.b.Item');
						txtInp_pid.setAttribute('type', 'text');
						txtInp_pid.style.fontSize="11px";
						txtInp_pid.style.color=txtcolor;
				}
		// 2. Full Name -----------------------------------------------------
			var txtInp_nam = document.createElement('input');
				txtInp_nam.setAttribute('type', 'text');
				txtInp_nam.setAttribute('id', INPUT_NAME_PREFIX + iteration + '_2');
				txtInp_nam.setAttribute('size', '30');
				txtInp_nam.setAttribute('value', nam);
				txtInp_nam.style.color=txtcolor;
				txtInp_nam.style.fontSize="10px";
		// 3. Relationship_1 --------------------------------------------------
			var txtInp_label = document.createElement('input');
				txtInp_label.setAttribute('type', 'text');
				txtInp_label.setAttribute('id', INPUT_NAME_PREFIX + iteration + '_3');
				txtInp_label.setAttribute('size', '15');
				txtInp_label.setAttribute('value', label);
				txtInp_label.style.color=txtcolor;
				txtInp_label.style.fontSize="10px";
		// 4. Conditition_1 -------------------------------------------
			var txtInp_cond = document.createElement('input');
				txtInp_cond.setAttribute('type', 'text');
				txtInp_cond.setAttribute('id', INPUT_NAME_PREFIX + iteration + '_4');
				txtInp_cond.setAttribute('size', '1');
				txtInp_cond.setAttribute('value', cond);
				txtInp_cond.style.color=txtcolor;
				txtInp_cond.style.fontSize="10px";
				txtInp_cond.style.width="1em";
		// 5. Assets_1 -------------------------------------------
			var txtInp_assets = document.createElement('input');
				txtInp_assets.setAttribute('type', 'text');
				txtInp_assets.setAttribute('id', INPUT_NAME_PREFIX + iteration + '_5');
				txtInp_assets.setAttribute('size', '7');
				txtInp_assets.setAttribute('maxlength', '9');
				txtInp_assets.setAttribute('value', '');
				txtInp_assets.style.color=txtcolor;
				txtInp_assets.style.fontSize="10px";
		// 6. Age_1 -----------------------------------------------------------
			var txtInp_age = document.createElement('input');
				txtInp_age.setAttribute('type', 'text');
				txtInp_age.setAttribute('id', INPUT_NAME_PREFIX + iteration + '_6');
				txtInp_age.setAttribute('size', '2');
				txtInp_age.setAttribute('maxlength', '4');
				txtInp_age.setAttribute('value', age);
				if (txtInp_age.value>=0) {
					txtInp_age.style.color=txtcolor;
				} else {
					txtInp_age.style.color="red";
				}
				txtInp_age.style.fontSize="10px";
				txtInp_age.style.width="1.8em";
		// 7. Race_1 -----------------------------------------------------------
			var txtInp_race = document.createElement('input');
				txtInp_race.setAttribute('type', 'text');
				txtInp_race.setAttribute('id', INPUT_NAME_PREFIX + iteration + '_7');
				txtInp_race.setAttribute('size', '1');
				txtInp_race.setAttribute('maxlength', '1');
				txtInp_race.setAttribute('value', ''); 
				txtInp_race.style.color=txtcolor;
				txtInp_race.style.fontSize="10px";
				txtInp_race.style.width="1em";
		// 8. Sex -----------------------------------------------------------
			var txtInp_gend = document.createElement('input');
				txtInp_gend.setAttribute('type', 'text');
				txtInp_gend.setAttribute('id', INPUT_NAME_PREFIX + iteration + '_8');
				txtInp_gend.setAttribute('size', '1');
				txtInp_gend.setAttribute('maxlength', '1');
				txtInp_gend.setAttribute('value', gend); 
				txtInp_gend.style.color=txtcolor;
				txtInp_gend.style.fontSize="10px";
				txtInp_gend.style.width="1em";
		// 9. Race_2 -----------------------------------------------------------
			var txtInp_race2 = document.createElement('input');
				txtInp_race2.setAttribute('type', 'text');
				txtInp_race2.setAttribute('id', INPUT_NAME_PREFIX + iteration + '_9');
				txtInp_race2.setAttribute('size', '1');
				txtInp_race2.setAttribute('maxlength', '1');
				txtInp_race2.setAttribute('value', ''); 
				txtInp_race2.style.color=txtcolor;
				txtInp_race2.style.fontSize="10px";
				txtInp_race2.style.width="1em";
		// 10. DOB/YOB ---------------------------------------------------------
			var txtInp_yob = document.createElement('input');
				txtInp_yob.setAttribute('type', 'text');
				txtInp_yob.setAttribute('id', INPUT_NAME_PREFIX + iteration + '_10');
				txtInp_yob.setAttribute('size', '4');
				txtInp_yob.setAttribute('maxlength', '8');
				txtInp_yob.setAttribute('value', yob);
				txtInp_yob.style.color=txtcolor;
				txtInp_yob.style.fontSize="10px";
		// 11. Age_2 -----------------------------------------------------------
			var txtInp_age2 = document.createElement('input');
				txtInp_age2.setAttribute('type', 'text');
				txtInp_age2.setAttribute('id', INPUT_NAME_PREFIX + iteration + '_11');
				txtInp_age2.setAttribute('size', '2');
				txtInp_age2.setAttribute('maxlength', '4');
				txtInp_age2.setAttribute('value', age); 
				if (txtInp_age2.value>=0) {
					txtInp_age2.style.color=txtcolor;
				} else {
					txtInp_age2.style.color="red";
				}
				txtInp_age2.style.fontSize="10px";
				txtInp_age2.style.width="1.8em";
		// 12. Bmth -----------------------------------------------------------
			var txtInp_bmth = document.createElement('input');
				txtInp_bmth.setAttribute('type', 'text');
				txtInp_bmth.setAttribute('id', INPUT_NAME_PREFIX + iteration + '_12');
				txtInp_bmth.setAttribute('size', '1');
				txtInp_bmth.setAttribute('maxlength', '3');
				txtInp_bmth.setAttribute('value', ''); 
				txtInp_bmth.style.color=txtcolor;
				txtInp_bmth.style.fontSize="10px";
				txtInp_bmth.style.width="1.8em";
		// 13. Relationship_2 --------------------------------------------------
			var txtInp_label2 = document.createElement('input');
				txtInp_label2.setAttribute('type', 'text');
				txtInp_label2.setAttribute('id', INPUT_NAME_PREFIX + iteration + '_13');
				txtInp_label2.setAttribute('size', '15');
				txtInp_label2.setAttribute('value', label);
				txtInp_label2.style.color=txtcolor;
				txtInp_label2.style.fontSize="10px";
		// 14. Conditition_2 ---------------------------------------------------
			var txtInp_cond2 = document.createElement('input');
				txtInp_cond2.setAttribute('type', 'text');
				txtInp_cond2.setAttribute('id', INPUT_NAME_PREFIX + iteration + '_14');
				txtInp_cond2.setAttribute('size', '1');
				txtInp_cond2.setAttribute('maxlength', '1');
				txtInp_cond2.setAttribute('value', cond);
				txtInp_cond2.style.color=txtcolor;
				txtInp_cond2.style.fontSize="10px";
				txtInp_cond2.style.width="1em";
		// 15. Years Married ---------------------------------------------------
			var txtInp_yrsm = document.createElement('input');
				txtInp_yrsm.setAttribute('type', 'text');
				txtInp_yrsm.setAttribute('id', INPUT_NAME_PREFIX + iteration + '_15');
				txtInp_yrsm.setAttribute('size', '1');
				txtInp_yrsm.setAttribute('maxlength', '2');
				txtInp_yrsm.setAttribute('value', '');
				txtInp_yrsm.style.color=txtcolor;
				txtInp_yrsm.style.fontSize="10px";
				txtInp_yrsm.style.width="1.4em";
		// 16. Children Born Alive --------------------------------------------
			var txtInp_chilB = document.createElement('input');
				txtInp_chilB.setAttribute('type', 'text');
				txtInp_chilB.setAttribute('id', INPUT_NAME_PREFIX + iteration + '_16');
				txtInp_chilB.setAttribute('size', '1');
				txtInp_chilB.setAttribute('maxlength', '2');
				txtInp_chilB.setAttribute('value', '');
				txtInp_chilB.style.color=txtcolor;
				txtInp_chilB.style.fontSize="10px";
				txtInp_chilB.style.width="1.4em";
		// 17. Children Still Living ------------------------------------------
			var txtInp_chilL = document.createElement('input');
				txtInp_chilL.setAttribute('type', 'text');
				txtInp_chilL.setAttribute('id', INPUT_NAME_PREFIX + iteration + '_17');
				txtInp_chilL.setAttribute('size', '1');
				txtInp_chilL.setAttribute('maxlength', '2');
				txtInp_chilL.setAttribute('value', '');
				txtInp_chilL.style.color=txtcolor;
				txtInp_chilL.style.fontSize="10px";
				txtInp_chilL.style.width="1.4em";
		// 18. Children who have Died ==---------------------------------------
			var txtInp_chilD = document.createElement('input');
				txtInp_chilD.setAttribute('type', 'text');
				txtInp_chilD.setAttribute('id', INPUT_NAME_PREFIX + iteration + '_18');
				txtInp_chilD.setAttribute('size', '1');
				txtInp_chilD.setAttribute('maxlength', '2');
				txtInp_chilD.setAttribute('value', '');
				txtInp_chilD.style.color=txtcolor;
				txtInp_chilD.style.fontSize="10px";
				txtInp_chilD.style.width="1.4em";
		// 19. Age at first marriage -------------------------------------------
			var txtInp_ageM = document.createElement('input');
				txtInp_ageM.setAttribute('type', 'text');
				txtInp_ageM.setAttribute('id', INPUT_NAME_PREFIX + iteration + '_19');
				txtInp_ageM.setAttribute('size', '1');
				txtInp_ageM.setAttribute('maxlength', '2');
				txtInp_ageM.setAttribute('value', ''); 
				txtInp_ageM.style.color=txtcolor;
				txtInp_ageM.style.fontSize="10px";
				txtInp_ageM.style.width="1.4em";
		// 20. Occupation_1 ----------------------------------------------------
			var txtInp_occu = document.createElement('input');
				txtInp_occu.setAttribute('type', 'text');
				txtInp_occu.setAttribute('id', INPUT_NAME_PREFIX + iteration + '_20');
				txtInp_occu.setAttribute('size', '22');
				txtInp_occu.setAttribute('value', ''); 
				txtInp_occu.style.color=txtcolor;
				txtInp_occu.style.fontSize="10px";
		// 21. Assets_2 -------------------------------------------
			var txtInp_assets2 = document.createElement('input');
				txtInp_assets2.setAttribute('type', 'text');
				txtInp_assets2.setAttribute('id', INPUT_NAME_PREFIX + iteration + '_21');
				txtInp_assets2.setAttribute('size', '7');
				txtInp_assets2.setAttribute('maxlength', '9');
				txtInp_assets2.setAttribute('value', '');
				txtInp_assets2.style.color=txtcolor;
				txtInp_assets2.style.fontSize="10px";
		// 22. Birth Place_1 (Full format) ---------------------------------------
			var txtInp_birthpl = document.createElement('input');
				txtInp_birthpl.setAttribute('type', 'text');
				txtInp_birthpl.setAttribute('id', INPUT_NAME_PREFIX + iteration + '_22');
				txtInp_birthpl.setAttribute('size', '25');
				txtInp_birthpl.setAttribute('value', birthpl); 
				txtInp_birthpl.style.color=txtcolor;
				txtInp_birthpl.style.fontSize="10px";
		// 23. POB_1 Indi Birth Place_1 (Chapman format) ------------------------
			var txtInp_ibirthpl = document.createElement('input');
				txtInp_ibirthpl.setAttribute('type', 'text');
				txtInp_ibirthpl.setAttribute('id', INPUT_NAME_PREFIX + iteration + '_23');
				txtInp_ibirthpl.setAttribute('size', '1');
				txtInp_ibirthpl.setAttribute('maxlength', '3');
				txtInp_ibirthpl.setAttribute('value', ibirthpl); 
				txtInp_ibirthpl.style.color=txtcolor;
				txtInp_ibirthpl.style.fontSize="10px";
				txtInp_ibirthpl.style.width="1.8em";
		// 24. FPOB_1 Fathers Birth Place_1 (Chapman format) ---------------------
			var txtInp_fbirthpl = document.createElement('input');
				txtInp_fbirthpl.setAttribute('type', 'text');
				txtInp_fbirthpl.setAttribute('id', INPUT_NAME_PREFIX + iteration + '_24');
				txtInp_fbirthpl.setAttribute('size', '1');
				txtInp_fbirthpl.setAttribute('maxlength', '3');
				txtInp_fbirthpl.setAttribute('value', fbirthpl); 
				txtInp_fbirthpl.style.color=txtcolor;
				txtInp_fbirthpl.style.fontSize="10px";
				txtInp_fbirthpl.style.width="1.8em";
		// 25. FPOB_1 Mothers Birth Place_1 (Chapman format )---------------------
			var txtInp_mbirthpl = document.createElement('input');
				txtInp_mbirthpl.setAttribute('type', 'text');
				txtInp_mbirthpl.setAttribute('id', INPUT_NAME_PREFIX + iteration + '_25');
				txtInp_mbirthpl.setAttribute('size', '1');
				txtInp_mbirthpl.setAttribute('maxlength', '3');
				txtInp_mbirthpl.setAttribute('value', mbirthpl); 
				txtInp_mbirthpl.style.color=txtcolor;
				txtInp_mbirthpl.style.fontSize="10px";
				txtInp_mbirthpl.style.width="1.8em";
		// 26. Years in USA ----------------------------------------------------
			var txtInp_yrsUS = document.createElement('input');
				txtInp_yrsUS.setAttribute('type', 'text');
				txtInp_yrsUS.setAttribute('id', INPUT_NAME_PREFIX + iteration + '_26');
				txtInp_yrsUS.setAttribute('size', '1');
				txtInp_yrsUS.setAttribute('maxlength', '2');
				txtInp_yrsUS.setAttribute('value', ''); 
				txtInp_yrsUS.style.color=txtcolor;
				txtInp_yrsUS.style.fontSize="10px";
				txtInp_yrsUS.style.width="1.4em";
		// 27. Year of Immigration YOI_1 ----------------------------------------
			var txtInp_yoi1 = document.createElement('input');
				txtInp_yoi1.setAttribute('type', 'text');
				txtInp_yoi1.setAttribute('id', INPUT_NAME_PREFIX + iteration + '_27');
				txtInp_yoi1.setAttribute('size', '2');
				txtInp_yoi1.setAttribute('maxlength', '4');
				txtInp_yoi1.setAttribute('value', ''); 
				txtInp_yoi1.style.color=txtcolor;
				txtInp_yoi1.style.fontSize="10px";
		// 28. Naturalized or Alien N-A_1 ---------------------------------------
			var txtInp_na1 = document.createElement('input');
				txtInp_na1.setAttribute('type', 'text');
				txtInp_na1.setAttribute('id', INPUT_NAME_PREFIX + iteration + '_28');
				txtInp_na1.setAttribute('size', '1');
				txtInp_na1.setAttribute('maxlength', '1');
				txtInp_na1.setAttribute('value', ''); 
				txtInp_na1.style.color=txtcolor;
				txtInp_na1.style.fontSize="10px";
				txtInp_na1.style.width="1em";
		// 29. Year of naturalization YON ---------------------------------------
			var txtInp_yon = document.createElement('input');
				txtInp_yon.setAttribute('type', 'text');
				txtInp_yon.setAttribute('id', INPUT_NAME_PREFIX + iteration + '_29');
				txtInp_yon.setAttribute('size', '2');
				txtInp_yon.setAttribute('maxlength', '4');
				txtInp_yon.setAttribute('value', ''); 
				txtInp_yon.style.color=txtcolor;
				txtInp_yon.style.fontSize="10px";
		// 30. English spoken, or if not, other Language spoken Eng/Lang --------
			var txtInp_englang = document.createElement('input');
				txtInp_englang.setAttribute('type', 'text');
				txtInp_englang.setAttribute('id', INPUT_NAME_PREFIX + iteration + '_30');
				txtInp_englang.setAttribute('size', '8');
				txtInp_englang.setAttribute('maxlength', '10');
				txtInp_englang.setAttribute('value', ''); 
				txtInp_englang.style.color=txtcolor;
				txtInp_englang.style.fontSize="10px";
		// 31. Occupation_2 ----------------------------------------------------
			var txtInp_occu2 = document.createElement('input');
				txtInp_occu2.setAttribute('type', 'text');
				txtInp_occu2.setAttribute('id', INPUT_NAME_PREFIX + iteration + '_31');
				txtInp_occu2.setAttribute('size', '22');
				txtInp_occu2.setAttribute('value', ''); 
				txtInp_occu2.style.color=txtcolor;
				txtInp_occu2.style.fontSize="10px";
		// 32. Health ----------------------------------------------------------
			var txtInp_health = document.createElement('input');
				txtInp_health.setAttribute('type', 'text');
				txtInp_health.setAttribute('id', INPUT_NAME_PREFIX + iteration + '_32');
				txtInp_health.setAttribute('size', '3');
				txtInp_health.setAttribute('maxlength', '5');
				txtInp_health.setAttribute('value', ''); 
				txtInp_health.style.color=txtcolor;
				txtInp_health.style.fontSize="10px";
		// 33. Industry_1 ------------------------------------------------------
			var txtInp_ind1 = document.createElement('input');
				txtInp_ind1.setAttribute('type', 'text');
				txtInp_ind1.setAttribute('id', INPUT_NAME_PREFIX + iteration + '_33');
				txtInp_ind1.setAttribute('size', '22');
				txtInp_ind1.setAttribute('value', ''); 
				txtInp_ind1.style.color=txtcolor;
				txtInp_ind1.style.fontSize="10px";
		// 34. Employ_1 --------------------------------------------------------
			var txtInp_emp1 = document.createElement('input');
				txtInp_emp1.setAttribute('type', 'text');
				txtInp_emp1.setAttribute('id', INPUT_NAME_PREFIX + iteration + '_34');
				txtInp_emp1.setAttribute('size', '12');
				txtInp_emp1.setAttribute('value', ''); 
				txtInp_emp1.style.color=txtcolor;
				txtInp_emp1.style.fontSize="10px";
		// 35. Employer EmR ----------------------------------------------------
			var txtInp_emR = document.createElement('input');
				txtInp_emR.setAttribute('type', 'text');
				txtInp_emR.setAttribute('id', INPUT_NAME_PREFIX + iteration + '_35');
				txtInp_emR.setAttribute('size', '1');
				txtInp_emR.setAttribute('maxlength', '1');
				txtInp_emR.setAttribute('value', ''); 
				txtInp_emR.style.color=txtcolor;
				txtInp_emR.style.fontSize="10px";
				txtInp_emR.style.width="1em";
		// 36. Employed EmD ----------------------------------------------------
			var txtInp_emD = document.createElement('input');
				txtInp_emD.setAttribute('type', 'text');
				txtInp_emD.setAttribute('id', INPUT_NAME_PREFIX + iteration + '_36');
				txtInp_emD.setAttribute('size', '1');
				txtInp_emD.setAttribute('maxlength', '1');
				txtInp_emD.setAttribute('value', ''); 
				txtInp_emD.style.color=txtcolor;
				txtInp_emD.style.fontSize="10px";
				txtInp_emD.style.width="1em";
		// 37. Employed at Home EmH --------------------------------------------
			var txtInp_emH = document.createElement('input');
				txtInp_emH.setAttribute('type', 'text');
				txtInp_emH.setAttribute('id', INPUT_NAME_PREFIX + iteration + '_37');
				txtInp_emH.setAttribute('size', '1');
				txtInp_emH.setAttribute('maxlength', '1');
				txtInp_emH.setAttribute('value', ''); 
				txtInp_emH.style.color=txtcolor;
				txtInp_emH.style.fontSize="10px";
				txtInp_emH.style.width="1em";
		// 38. Not Employed EmN ------------------------------------------------
			var txtInp_emN = document.createElement('input');
				txtInp_emN.setAttribute('type', 'text');
				txtInp_emN.setAttribute('id', INPUT_NAME_PREFIX + iteration + '_38');
				txtInp_emN.setAttribute('size', '1');
				txtInp_emN.setAttribute('maxlength', '1');
				txtInp_emN.setAttribute('value', ''); 
				txtInp_emN.style.color=txtcolor;
				txtInp_emN.style.fontSize="10px";
				txtInp_emN.style.width="1em";
		// 39. Education -------------------------------------------------------
			var txtInp_educ = document.createElement('input');
				txtInp_educ.setAttribute('type', 'text');
				txtInp_educ.setAttribute('id', INPUT_NAME_PREFIX + iteration + '_39');
				txtInp_educ.setAttribute('size', '1');
				txtInp_educ.setAttribute('maxlength', '3');
				txtInp_educ.setAttribute('value', ''); 
				txtInp_educ.style.color=txtcolor;
				txtInp_educ.style.fontSize="10px";
				txtInp_educ.style.width="1.8em";
		// 40. English Spoken?_1 eng_1 -----------------------------------------
			var txtInp_eng1 = document.createElement('input');
				txtInp_eng1.setAttribute('type', 'text');
				txtInp_eng1.setAttribute('id', INPUT_NAME_PREFIX + iteration + '_40');
				txtInp_eng1.setAttribute('size', '1');
				txtInp_eng1.setAttribute('maxlength', '1');
				txtInp_eng1.setAttribute('value', ''); 
				txtInp_eng1.style.color=txtcolor;
				txtInp_eng1.style.fontSize="10px";
				txtInp_eng1.style.width="1em";
		// 41. Assets_3 --------------------------------------------------------
			var txtInp_assets3 = document.createElement('input');
				txtInp_assets3.setAttribute('type', 'text');
				txtInp_assets3.setAttribute('id', INPUT_NAME_PREFIX + iteration + '_41');
				txtInp_assets3.setAttribute('size', '7');
				txtInp_assets3.setAttribute('maxlength', '9');
				txtInp_assets3.setAttribute('value', '');
				txtInp_assets3.style.color=txtcolor;
				txtInp_assets3.style.fontSize="10px";
		// 42. Birth Place_2 (full format) -------------------------------------
			var txtInp_birthpl2 = document.createElement('input');
				txtInp_birthpl2.setAttribute('type', 'text');
				txtInp_birthpl2.setAttribute('id', INPUT_NAME_PREFIX + iteration + '_42');
				txtInp_birthpl2.setAttribute('size', '25');
				txtInp_birthpl2.setAttribute('value', birthpl); 
				txtInp_birthpl2.style.color=txtcolor;
				txtInp_birthpl2.style.fontSize="10px";
		// 43. POB_2 Indi Birth Place_2 ----------------------------------------
			var txtInp_ibirthpl2 = document.createElement('input');
				txtInp_ibirthpl2.setAttribute('type', 'text');
				txtInp_ibirthpl2.setAttribute('id', INPUT_NAME_PREFIX + iteration + '_43');
				txtInp_ibirthpl2.setAttribute('size', '1');
				txtInp_ibirthpl2.setAttribute('maxlength', '3');
				txtInp_ibirthpl2.setAttribute('value', ibirthpl); 
				txtInp_ibirthpl2.style.color=txtcolor;
				txtInp_ibirthpl2.style.fontSize="10px";
				txtInp_ibirthpl2.style.width="1.8em";
		// 44. Born in Same Country BIC ----------------------------------------
			var txtInp_bic = document.createElement('input');
				txtInp_bic.setAttribute('type', 'text');
				txtInp_bic.setAttribute('id', INPUT_NAME_PREFIX + iteration + '_44');
				txtInp_bic.setAttribute('size', '1');
				txtInp_bic.setAttribute('maxlength', '1');
				txtInp_bic.setAttribute('value', fbirthpl); 
				txtInp_bic.style.color=txtcolor;
				txtInp_bic.style.fontSize="10px";
				txtInp_bic.style.width="1em";
		// 45. Born outside England BOE ----------------------------------------
			var txtInp_boe = document.createElement('input');
				txtInp_boe.setAttribute('type', 'text');
				txtInp_boe.setAttribute('id', INPUT_NAME_PREFIX + iteration + '_45');
				txtInp_boe.setAttribute('size', '1');
				txtInp_boe.setAttribute('maxlength', '3');
				txtInp_boe.setAttribute('value', fbirthpl); 
				txtInp_boe.style.color=txtcolor;
				txtInp_boe.style.fontSize="10px";
				txtInp_boe.style.width="1.8em";
		// 46. FPOB_2 Birth Place_2 --------------------------------------------
			var txtInp_fbirthpl2 = document.createElement('input');
				txtInp_fbirthpl2.setAttribute('type', 'text');
				txtInp_fbirthpl2.setAttribute('id', INPUT_NAME_PREFIX + iteration + '_46');
				txtInp_fbirthpl2.setAttribute('size', '1');
				txtInp_fbirthpl2.setAttribute('maxlength', '3');
				txtInp_fbirthpl2.setAttribute('value', fbirthpl); 
				txtInp_fbirthpl2.style.color=txtcolor;
				txtInp_fbirthpl2.style.fontSize="10px";
				txtInp_fbirthpl2.style.width="1.8em";
		// 47. MPOB_2 Birth Place_2 --------------------------------------------
			var txtInp_mbirthpl2 = document.createElement('input');
				txtInp_mbirthpl2.setAttribute('type', 'text');
				txtInp_mbirthpl2.setAttribute('id', INPUT_NAME_PREFIX + iteration + '_47');
				txtInp_mbirthpl2.setAttribute('size', '1');
				txtInp_mbirthpl2.setAttribute('maxlength', '3');
				txtInp_mbirthpl2.setAttribute('value', mbirthpl); 
				txtInp_mbirthpl2.style.color=txtcolor;
				txtInp_mbirthpl2.style.fontSize="10px";
				txtInp_mbirthpl2.style.width="1.8em";
		// 48. Mother Tongue ---------------------------------------------------
			var txtInp_lang = document.createElement('input');
				txtInp_lang.setAttribute('type', 'text');
				txtInp_lang.setAttribute('id', INPUT_NAME_PREFIX + iteration + '_48');
				txtInp_lang.setAttribute('size', '8');
				txtInp_lang.setAttribute('maxlength', '10');
				txtInp_lang.setAttribute('value', ''); 
				txtInp_lang.style.color=txtcolor;
				txtInp_lang.style.fontSize="10px";
		// 49. Year of Immigration YOI_2 ---------------------------------------
			var txtInp_yoi2 = document.createElement('input');
				txtInp_yoi2.setAttribute('type', 'text');
				txtInp_yoi2.setAttribute('id', INPUT_NAME_PREFIX + iteration + '_49');
				txtInp_yoi2.setAttribute('size', '2');
				txtInp_yoi2.setAttribute('maxlength', '4');
				txtInp_yoi2.setAttribute('value', ''); 
				txtInp_yoi2.style.color=txtcolor;
				txtInp_yoi2.style.fontSize="10px";
		// 50. Naturalized or Alien N-A_2 --------------------------------------
			var txtInp_na2 = document.createElement('input');
				txtInp_na2.setAttribute('type', 'text');
				txtInp_na2.setAttribute('id', INPUT_NAME_PREFIX + iteration + '_50');
				txtInp_na2.setAttribute('size', '1');
				txtInp_na2.setAttribute('maxlength', '1');
				txtInp_na2.setAttribute('value', ''); 
				txtInp_na2.style.color=txtcolor;
				txtInp_na2.style.fontSize="10px";
				txtInp_na2.style.width="1em";
		// 51. English Spoken?_2 eng_2 -----------------------------------------
			var txtInp_eng2 = document.createElement('input');
				txtInp_eng2.setAttribute('type', 'text');
				txtInp_eng2.setAttribute('id', INPUT_NAME_PREFIX + iteration + '_51');
				txtInp_eng2.setAttribute('size', '1');
				txtInp_eng2.setAttribute('maxlength', '1');
				txtInp_eng2.setAttribute('value', ''); 
				txtInp_eng2.style.color=txtcolor;
				txtInp_eng2.style.fontSize="10px";
				txtInp_eng2.style.width="1em";
		// 52. Occupation_3 ----------------------------------------------------
			var txtInp_occu3 = document.createElement('input');
				txtInp_occu3.setAttribute('type', 'text');
				txtInp_occu3.setAttribute('id', INPUT_NAME_PREFIX + iteration + '_52');
				txtInp_occu3.setAttribute('size', '22');
				txtInp_occu3.setAttribute('value', ''); 
				txtInp_occu3.style.color=txtcolor;
				txtInp_occu3.style.fontSize="10px";
		// 53. Industry_2 -----------------------------------------------------
			var txtInp_ind2 = document.createElement('input');
				txtInp_ind2.setAttribute('type', 'text');
				txtInp_ind2.setAttribute('id', INPUT_NAME_PREFIX + iteration + '_53');
				txtInp_ind2.setAttribute('size', '22');
				txtInp_ind2.setAttribute('value', ''); 
				txtInp_ind2.style.color=txtcolor;
				txtInp_ind2.style.fontSize="10px";
		// 54. Employ_2 -------------------------------------------------------
			var txtInp_emp2 = document.createElement('input');
				txtInp_emp2.setAttribute('type', 'text');
				txtInp_emp2.setAttribute('id', INPUT_NAME_PREFIX + iteration + '_54');
				txtInp_emp2.setAttribute('size', '12');
				txtInp_emp2.setAttribute('value', ''); 
				txtInp_emp2.style.color=txtcolor;
				txtInp_emp2.style.fontSize="10px";
		// 55. Infirmaties ----------------------------------------------------
			var txtInp_infirm = document.createElement('input');
				txtInp_infirm.setAttribute('type', 'text');
				txtInp_infirm.setAttribute('id', INPUT_NAME_PREFIX + iteration + '_55');
				txtInp_infirm.setAttribute('size', '3');
				txtInp_infirm.setAttribute('maxlength', '4');
				txtInp_infirm.setAttribute('value', ''); 
				txtInp_infirm.style.color=txtcolor;
				txtInp_infirm.style.fontSize="10px";
				txtInp_infirm.style.width="2.3em";
		// 56. Veteran ? ------------------------------------------------------
			var txtInp_vet = document.createElement('input');
				txtInp_vet.setAttribute('type', 'text');
				txtInp_vet.setAttribute('id', INPUT_NAME_PREFIX + iteration + '_56');
				txtInp_vet.setAttribute('size', '1');
				txtInp_vet.setAttribute('maxlength', '1');
				txtInp_vet.setAttribute('value', ''); 
				txtInp_vet.style.color=txtcolor;
				txtInp_vet.style.fontSize="10px";
				txtInp_vet.style.width="1em";
		// 57. Delete Row Button ----------------------------------------------
			var btnEl = document.createElement('input');
				btnEl.setAttribute('type', 'button');
				btnEl.setAttribute('value', 'x');
				btnEl.onclick = function () {deleteCurrentRow(this)};
		// 58. Insert row Radio button ----------------------------------------
			var raEl;
				try {
					raEl = document.createElement('<input type="radio" name="' + RADIO_NAME + '" value="' + iteration + '">');
					var failIfNotIE = raEl.name.length;
				} catch(ex) {
					raEl = document.createElement('input');
					raEl.setAttribute('type', 'radio');
					raEl.setAttribute('name', RADIO_NAME );
					raEl.setAttribute('value', iteration);
				}
		// 59. Item Number ----------------------------------------------------
			var txt_itemNo2 = document.createTextNode(iteration);
		}
		// Not visible but used for row re-order process ----------------------
			var cbEl = document.createElement('input');
				cbEl.type = "hidden";

		// d. Append appropriate Cell elements to each cell ===================
		cell_0.appendChild(txt_itemNo);			// Item Number
		cell_1.appendChild(txtInp_pid);			// Indi ID
		cell_2.appendChild(txtInp_nam);			// Name
		cell_3.appendChild(txtInp_label);		// Relationship_1
		cell_4.appendChild(txtInp_cond);		// Condition_1
		cell_5.appendChild(txtInp_assets);		// Assets_1
		cell_6.appendChild(txtInp_age);			// Age_1
		cell_7.appendChild(txtInp_race);		// Race_1
		cell_8.appendChild(txtInp_gend);		// Sex
		cell_9.appendChild(txtInp_race2);		// Race_2
		
		cell_10.appendChild(txtInp_yob);		// DOB/YOB
		cell_11.appendChild(txtInp_age2);		// Age_2
		cell_12.appendChild(txtInp_bmth);		// Birth Month
		cell_13.appendChild(txtInp_label2);		// Relationship_2
		cell_14.appendChild(txtInp_cond2);		// Condition_2
		cell_15.appendChild(txtInp_yrsm);		// Years Married
		cell_16.appendChild(txtInp_chilB);		// Children Born Alive
		cell_17.appendChild(txtInp_chilL);		// Children Still Living
		cell_18.appendChild(txtInp_chilD);		// Children who have Died
		cell_19.appendChild(txtInp_ageM);		// Age st first marriage
		
		cell_20.appendChild(txtInp_occu);		// Occupation_1
		cell_21.appendChild(txtInp_assets2);	// Assets_2
		cell_22.appendChild(txtInp_birthpl);	// Place of Birth_1
		cell_23.appendChild(txtInp_ibirthpl);	// Indis POB_1
		cell_24.appendChild(txtInp_fbirthpl);	// Father FPOB_1
		cell_25.appendChild(txtInp_mbirthpl);	// Mother MPOB_1
		cell_26.appendChild(txtInp_yrsUS);		// Years in USA
		cell_27.appendChild(txtInp_yoi1);		// Year of Immigration YOI_1
		cell_28.appendChild(txtInp_na1);		// Naturalized or Alien N-A_1
		cell_29.appendChild(txtInp_yon);		// Year of Naturalization YON
		
		cell_30.appendChild(txtInp_englang);	// English spoken, if not, Other Language spoken Eng/Lang
		cell_31.appendChild(txtInp_occu2);		// Occupation_2
		cell_32.appendChild(txtInp_health);		// Health - 5 parameters x--xx etc
		cell_33.appendChild(txtInp_ind1);		// Industry ind_1
		cell_34.appendChild(txtInp_emp1);		// Employ_1
		cell_35.appendChild(txtInp_emR);		// Employer EmR
		cell_36.appendChild(txtInp_emD);		// Employed EmD
		cell_37.appendChild(txtInp_emH);		// Employed At Home EmH
		cell_38.appendChild(txtInp_emN);		// Not Employed EmN
		cell_39.appendChild(txtInp_educ);		// Education 3 parameters Sch-Read-Write  -xx
		
		cell_40.appendChild(txtInp_eng1);		// English spoken Y/N  eng_1
		cell_41.appendChild(txtInp_assets3);	// Assets_3
		cell_42.appendChild(txtInp_birthpl2);	// Birth Place_2
		cell_43.appendChild(txtInp_ibirthpl2);	// Indis POB_2
		cell_44.appendChild(txtInp_bic);		// Born in County (UK)
		cell_45.appendChild(txtInp_boe);		// Born outside England (UK)
		cell_46.appendChild(txtInp_fbirthpl2);	// Fathers FPOB_2
		cell_47.appendChild(txtInp_mbirthpl2);	// Mothers MPOB_2
		cell_48.appendChild(txtInp_lang);		// Mother Tongue lang
		cell_49.appendChild(txtInp_yoi2);		// Year of Immigration YOI_2
		
		cell_50.appendChild(txtInp_na2);		// Naturalized or Alien N-A_2
		cell_51.appendChild(txtInp_eng2);		// English spoken Y/N  eng_2
		cell_52.appendChild(txtInp_occu3);		// Occupation_3
		cell_53.appendChild(txtInp_ind2);		// Industry ind_2
		cell_54.appendChild(txtInp_emp2);		// Employ_2
		cell_55.appendChild(txtInp_infirm);		// Infirmaties - up to 5 parameters x--xx etc
		cell_56.appendChild(txtInp_vet);		// Veteran ?

		if (iteration == 0) {
			cell_tdel.appendChild(txtInp_tdel);	// Text Del
			cell_tra.appendChild(txtInp_tra);	// Text Ins
		}else{
			cell_del.appendChild(btnEl);		// Onclick = Delete Row
			cell_ra.appendChild(raEl);			// Radio button used for inserting a row, rather than adding at end of table)
		}
		cell_59.appendChild(txt_itemNo2);		// Text Item Number
		
		
		// Pass in the elements to be referenced later ===================
		// Store the myRow object in each row
		row.myRow = new myRowObject(	txt_itemNo, txtInp_pid, txtInp_nam, txtInp_label, txtInp_cond, txtInp_assets, txtInp_age, txtInp_race, txtInp_gend, txtInp_race2, 
										txtInp_yob, txtInp_age2, txtInp_bmth, txtInp_label2, txtInp_cond2, txtInp_yrsm, txtInp_chilB, txtInp_chilL, txtInp_chilD, txtInp_ageM,
										txtInp_occu, txtInp_assets2, txtInp_birthpl, txtInp_ibirthpl, txtInp_fbirthpl, txtInp_mbirthpl, txtInp_yrsUS, txtInp_yoi1, txtInp_na1, txtInp_yon, 
										txtInp_englang, txtInp_occu2, txtInp_health, txtInp_ind1, txtInp_emp1, txtInp_emR, txtInp_emD, txtInp_emH, txtInp_emN, txtInp_educ, 
										txtInp_eng1, txtInp_assets3, txtInp_birthpl2, txtInp_ibirthpl2, txtInp_bic, txtInp_boe, txtInp_fbirthpl2, txtInp_mbirthpl2, txtInp_lang, txtInp_yoi2, 
										txtInp_na2, txtInp_eng2, txtInp_occu3, txtInp_ind2, txtInp_emp2, txtInp_infirm, txtInp_vet, 
										cbEl, raEl, txt_itemNo2
									);

	}
}

// deleteCurrentRow - function to delete a row
function deleteCurrentRow(obj) {
	if (hasLoaded) {
		var delRow = obj.parentNode.parentNode;
		var tbl = delRow.parentNode.parentNode;
		var rIndex = delRow.sectionRowIndex;
		var rowArray = new Array(delRow);
		deleteRows(rowArray);
		reorderRows(tbl, rIndex);
		preview();
	}
}

function deleteHeaderRow(obj) {
	if (hasLoaded) {
		var delRow = obj.parentNode.parentNode;
		var tbl = delRow.parentNode.parentNode;
		var rIndex = delRow.sectionRowIndex;
		var rowArray = new Array(delRow);
		deleteRows(rowArray);
	}
}

function deleteRows(rowObjArray) {
	if (hasLoaded) {
		for (var i=0; i<rowObjArray.length; i++) {  // i set to 1 to avoid table header row of number 0
			var rIndex = rowObjArray[i].sectionRowIndex;
			rowObjArray[i].parentNode.deleteRow(rIndex);
		}
	}
}

// reorderRows - used to reorder rows after an insert or delete
function reorderRows(tbl, startingIndex) {
	if (hasLoaded) {
		if (tbl.tBodies[0].rows[startingIndex]) {
			var count = startingIndex + ROW_BASE;
			for (var i=startingIndex; i<tbl.tBodies[0].rows.length; i++) {
				
				// CONFIG: next 2 lines are affected by myRowObject settings
				tbl.tBodies[0].rows[i].myRow.zero.data		 = count; // text - (left column item number)
				tbl.tBodies[0].rows[i].myRow.fiftynine.data	 = count; // text - (right column item number)
				
				// ------------------------------------
				tbl.tBodies[0].rows[i].myRow.one.id				 = INPUT_NAME_PREFIX + count + '_1';  // input text
				tbl.tBodies[0].rows[i].myRow.two.id 			 = INPUT_NAME_PREFIX + count + '_2';  // input text
				tbl.tBodies[0].rows[i].myRow.three.id			 = INPUT_NAME_PREFIX + count + '_3';  // input text
				tbl.tBodies[0].rows[i].myRow.four.id			 = INPUT_NAME_PREFIX + count + '_4';  // input text
				tbl.tBodies[0].rows[i].myRow.five.id			 = INPUT_NAME_PREFIX + count + '_5';  // input text
				tbl.tBodies[0].rows[i].myRow.six.id				 = INPUT_NAME_PREFIX + count + '_6';  // input text
				tbl.tBodies[0].rows[i].myRow.seven.id			 = INPUT_NAME_PREFIX + count + '_7';  // input text
				tbl.tBodies[0].rows[i].myRow.eight.id			 = INPUT_NAME_PREFIX + count + '_8';  // input text
				tbl.tBodies[0].rows[i].myRow.nine.id			 = INPUT_NAME_PREFIX + count + '_9';  // input text
				
				tbl.tBodies[0].rows[i].myRow.ten.id				 = INPUT_NAME_PREFIX + count + '_10'; // input text
				tbl.tBodies[0].rows[i].myRow.eleven.id			 = INPUT_NAME_PREFIX + count + '_11'; // input text
				tbl.tBodies[0].rows[i].myRow.twelve.id			 = INPUT_NAME_PREFIX + count + '_12'; // input text
				tbl.tBodies[0].rows[i].myRow.thirteen.id		 = INPUT_NAME_PREFIX + count + '_13';  // input text
				tbl.tBodies[0].rows[i].myRow.fourteen.id		 = INPUT_NAME_PREFIX + count + '_14';  // input text
				tbl.tBodies[0].rows[i].myRow.fifteen.id			 = INPUT_NAME_PREFIX + count + '_15';  // input text
				tbl.tBodies[0].rows[i].myRow.sixteen.id			 = INPUT_NAME_PREFIX + count + '_16';  // input text
				tbl.tBodies[0].rows[i].myRow.seventeen.id		 = INPUT_NAME_PREFIX + count + '_17';  // input text
				tbl.tBodies[0].rows[i].myRow.eighteen.id		 = INPUT_NAME_PREFIX + count + '_18';  // input text
				tbl.tBodies[0].rows[i].myRow.nineteen.id		 = INPUT_NAME_PREFIX + count + '_19';  // input text
				
				tbl.tBodies[0].rows[i].myRow.twenty.id			 = INPUT_NAME_PREFIX + count + '_20'; // input text
				tbl.tBodies[0].rows[i].myRow.twentyone.id		 = INPUT_NAME_PREFIX + count + '_21';  // input text
				tbl.tBodies[0].rows[i].myRow.twentytwo.id 		 = INPUT_NAME_PREFIX + count + '_22';  // input text
				tbl.tBodies[0].rows[i].myRow.twentythree.id		 = INPUT_NAME_PREFIX + count + '_23';  // input text
				tbl.tBodies[0].rows[i].myRow.twentyfour.id		 = INPUT_NAME_PREFIX + count + '_24';  // input text
				tbl.tBodies[0].rows[i].myRow.twentyfive.id		 = INPUT_NAME_PREFIX + count + '_25';  // input text
				tbl.tBodies[0].rows[i].myRow.twentysix.id		 = INPUT_NAME_PREFIX + count + '_26';  // input text
				tbl.tBodies[0].rows[i].myRow.twentyseven.id		 = INPUT_NAME_PREFIX + count + '_27';  // input text
				tbl.tBodies[0].rows[i].myRow.twentyeight.id		 = INPUT_NAME_PREFIX + count + '_28';  // input text
				tbl.tBodies[0].rows[i].myRow.twentynine.id		 = INPUT_NAME_PREFIX + count + '_29';  // input text
				
				tbl.tBodies[0].rows[i].myRow.thirty.id			 = INPUT_NAME_PREFIX + count + '_30'; // input text
				tbl.tBodies[0].rows[i].myRow.thirtyone.id		 = INPUT_NAME_PREFIX + count + '_31';  // input text
				tbl.tBodies[0].rows[i].myRow.thirtytwo.id		 = INPUT_NAME_PREFIX + count + '_32';  // input text
				tbl.tBodies[0].rows[i].myRow.thirtythree.id		 = INPUT_NAME_PREFIX + count + '_33';  // input text
				tbl.tBodies[0].rows[i].myRow.thirtyfour.id		 = INPUT_NAME_PREFIX + count + '_34';  // input text
				tbl.tBodies[0].rows[i].myRow.thirtyfive.id		 = INPUT_NAME_PREFIX + count + '_35';  // input text
				tbl.tBodies[0].rows[i].myRow.thirtysix.id		 = INPUT_NAME_PREFIX + count + '_36';  // input text
				tbl.tBodies[0].rows[i].myRow.thirtyseven.id		 = INPUT_NAME_PREFIX + count + '_37';  // input text
				tbl.tBodies[0].rows[i].myRow.thirtyeight.id		 = INPUT_NAME_PREFIX + count + '_38';  // input text
				tbl.tBodies[0].rows[i].myRow.thirtynine.id		 = INPUT_NAME_PREFIX + count + '_39';  // input text
				
				tbl.tBodies[0].rows[i].myRow.forty.id			 = INPUT_NAME_PREFIX + count + '_40'; // input text
				tbl.tBodies[0].rows[i].myRow.fortyone.id		 = INPUT_NAME_PREFIX + count + '_41';  // input text
				tbl.tBodies[0].rows[i].myRow.fortytwo.id		 = INPUT_NAME_PREFIX + count + '_42';  // input text
				tbl.tBodies[0].rows[i].myRow.fortythree.id		 = INPUT_NAME_PREFIX + count + '_43';  // input text
				tbl.tBodies[0].rows[i].myRow.fortyfour.id		 = INPUT_NAME_PREFIX + count + '_44';  // input text
				tbl.tBodies[0].rows[i].myRow.fortyfive.id		 = INPUT_NAME_PREFIX + count + '_45';  // input text
				tbl.tBodies[0].rows[i].myRow.fortysix.id		 = INPUT_NAME_PREFIX + count + '_46';  // input text
				tbl.tBodies[0].rows[i].myRow.fortyseven.id		 = INPUT_NAME_PREFIX + count + '_47';  // input text
				tbl.tBodies[0].rows[i].myRow.fortyeight.id		 = INPUT_NAME_PREFIX + count + '_48';  // input text
				tbl.tBodies[0].rows[i].myRow.fortynine.id		 = INPUT_NAME_PREFIX + count + '_49';  // input text
				
				tbl.tBodies[0].rows[i].myRow.fifty.id			 = INPUT_NAME_PREFIX + count + '_50';  // input text
				tbl.tBodies[0].rows[i].myRow.fiftyone.id		 = INPUT_NAME_PREFIX + count + '_51';  // input text
				tbl.tBodies[0].rows[i].myRow.fiftytwo.id		 = INPUT_NAME_PREFIX + count + '_52';  // input text
				tbl.tBodies[0].rows[i].myRow.fiftythree.id		 = INPUT_NAME_PREFIX + count + '_53';  // input text
				tbl.tBodies[0].rows[i].myRow.fiftyfour.id		 = INPUT_NAME_PREFIX + count + '_54';  // input text
				tbl.tBodies[0].rows[i].myRow.fiftyfive.id		 = INPUT_NAME_PREFIX + count + '_55';  // input text
				tbl.tBodies[0].rows[i].myRow.fiftysix.id		 = INPUT_NAME_PREFIX + count + '_56';  // input text
				// ------------------------------------
				
				// ------------------------------------
				tbl.tBodies[0].rows[i].myRow.one.name			 = INPUT_NAME_PREFIX + count + '_1';  // input text
				tbl.tBodies[0].rows[i].myRow.two.name 			 = INPUT_NAME_PREFIX + count + '_2';  // input text
				tbl.tBodies[0].rows[i].myRow.three.name			 = INPUT_NAME_PREFIX + count + '_3';  // input text
				tbl.tBodies[0].rows[i].myRow.four.name			 = INPUT_NAME_PREFIX + count + '_4';  // input text
				tbl.tBodies[0].rows[i].myRow.five.name			 = INPUT_NAME_PREFIX + count + '_5';  // input text
				tbl.tBodies[0].rows[i].myRow.six.name			 = INPUT_NAME_PREFIX + count + '_6';  // input text
				tbl.tBodies[0].rows[i].myRow.seven.name			 = INPUT_NAME_PREFIX + count + '_7';  // input text
				tbl.tBodies[0].rows[i].myRow.eight.name			 = INPUT_NAME_PREFIX + count + '_8';  // input text
				tbl.tBodies[0].rows[i].myRow.nine.name			 = INPUT_NAME_PREFIX + count + '_9';  // input text
				
				tbl.tBodies[0].rows[i].myRow.ten.name			 = INPUT_NAME_PREFIX + count + '_10'; // input text
				tbl.tBodies[0].rows[i].myRow.eleven.name		 = INPUT_NAME_PREFIX + count + '_11'; // input text
				tbl.tBodies[0].rows[i].myRow.twelve.name		 = INPUT_NAME_PREFIX + count + '_12'; // input text
				tbl.tBodies[0].rows[i].myRow.thirteen.name		 = INPUT_NAME_PREFIX + count + '_13';  // input text
				tbl.tBodies[0].rows[i].myRow.fourteen.name		 = INPUT_NAME_PREFIX + count + '_14';  // input text
				tbl.tBodies[0].rows[i].myRow.fifteen.name		 = INPUT_NAME_PREFIX + count + '_15';  // input text
				tbl.tBodies[0].rows[i].myRow.sixteen.name		 = INPUT_NAME_PREFIX + count + '_16';  // input text
				tbl.tBodies[0].rows[i].myRow.seventeen.name		 = INPUT_NAME_PREFIX + count + '_17';  // input text
				tbl.tBodies[0].rows[i].myRow.eighteen.name		 = INPUT_NAME_PREFIX + count + '_18';  // input text
				tbl.tBodies[0].rows[i].myRow.nineteen.name		 = INPUT_NAME_PREFIX + count + '_19';  // input text
				
				tbl.tBodies[0].rows[i].myRow.twenty.name		 = INPUT_NAME_PREFIX + count + '_20'; // input text
				tbl.tBodies[0].rows[i].myRow.twentyone.name		 = INPUT_NAME_PREFIX + count + '_21';  // input text
				tbl.tBodies[0].rows[i].myRow.twentytwo.name		 = INPUT_NAME_PREFIX + count + '_22';  // input text
				tbl.tBodies[0].rows[i].myRow.twentythree.name	 = INPUT_NAME_PREFIX + count + '_23';  // input text
				tbl.tBodies[0].rows[i].myRow.twentyfour.name	 = INPUT_NAME_PREFIX + count + '_24';  // input text
				tbl.tBodies[0].rows[i].myRow.twentyfive.name	 = INPUT_NAME_PREFIX + count + '_25';  // input text
				tbl.tBodies[0].rows[i].myRow.twentysix.name		 = INPUT_NAME_PREFIX + count + '_26';  // input text
				tbl.tBodies[0].rows[i].myRow.twentyseven.name	 = INPUT_NAME_PREFIX + count + '_27';  // input text
				tbl.tBodies[0].rows[i].myRow.twentyeight.name	 = INPUT_NAME_PREFIX + count + '_28';  // input text
				tbl.tBodies[0].rows[i].myRow.twentynine.name	 = INPUT_NAME_PREFIX + count + '_29';  // input text
				
				tbl.tBodies[0].rows[i].myRow.thirty.name		 = INPUT_NAME_PREFIX + count + '_30'; // input text
				tbl.tBodies[0].rows[i].myRow.thirtyone.name		 = INPUT_NAME_PREFIX + count + '_31';  // input text
				tbl.tBodies[0].rows[i].myRow.thirtytwo.name 	 = INPUT_NAME_PREFIX + count + '_32';  // input text
				tbl.tBodies[0].rows[i].myRow.thirtythree.name	 = INPUT_NAME_PREFIX + count + '_33';  // input text
				tbl.tBodies[0].rows[i].myRow.thirtyfour.name	 = INPUT_NAME_PREFIX + count + '_34';  // input text
				tbl.tBodies[0].rows[i].myRow.thirtyfive.name	 = INPUT_NAME_PREFIX + count + '_35';  // input text
				tbl.tBodies[0].rows[i].myRow.thirtysix.name		 = INPUT_NAME_PREFIX + count + '_36';  // input text
				tbl.tBodies[0].rows[i].myRow.thirtyseven.name	 = INPUT_NAME_PREFIX + count + '_37';  // input text
				tbl.tBodies[0].rows[i].myRow.thirtyeight.name	 = INPUT_NAME_PREFIX + count + '_38';  // input text
				tbl.tBodies[0].rows[i].myRow.thirtynine.name	 = INPUT_NAME_PREFIX + count + '_39';  // input text
				
				tbl.tBodies[0].rows[i].myRow.forty.name			 = INPUT_NAME_PREFIX + count + '_40'; // input text
				tbl.tBodies[0].rows[i].myRow.fortyone.name		 = INPUT_NAME_PREFIX + count + '_41';  // input text
				tbl.tBodies[0].rows[i].myRow.fortytwo.name 		 = INPUT_NAME_PREFIX + count + '_42';  // input text
				tbl.tBodies[0].rows[i].myRow.fortythree.name	 = INPUT_NAME_PREFIX + count + '_43';  // input text
				tbl.tBodies[0].rows[i].myRow.fortyfour.name		 = INPUT_NAME_PREFIX + count + '_44';  // input text
				tbl.tBodies[0].rows[i].myRow.fortyfive.name		 = INPUT_NAME_PREFIX + count + '_45';  // input text
				tbl.tBodies[0].rows[i].myRow.fortysix.name		 = INPUT_NAME_PREFIX + count + '_46';  // input text
				tbl.tBodies[0].rows[i].myRow.fortyseven.name	 = INPUT_NAME_PREFIX + count + '_47';  // input text
				tbl.tBodies[0].rows[i].myRow.fortyeight.name	 = INPUT_NAME_PREFIX + count + '_48';  // input text
				tbl.tBodies[0].rows[i].myRow.fortynine.name		 = INPUT_NAME_PREFIX + count + '_49';  // input text
				
				tbl.tBodies[0].rows[i].myRow.fifty.name			 = INPUT_NAME_PREFIX + count + '_50';  // input text
				tbl.tBodies[0].rows[i].myRow.fiftyone.name		 = INPUT_NAME_PREFIX + count + '_51';  // input text
				tbl.tBodies[0].rows[i].myRow.fiftytwo.name		 = INPUT_NAME_PREFIX + count + '_52';  // input text
				tbl.tBodies[0].rows[i].myRow.fiftythree.name	 = INPUT_NAME_PREFIX + count + '_53';  // input text
				tbl.tBodies[0].rows[i].myRow.fiftyfour.name		 = INPUT_NAME_PREFIX + count + '_54';  // input text
				tbl.tBodies[0].rows[i].myRow.fiftyfive.name		 = INPUT_NAME_PREFIX + count + '_55';  // input text
				tbl.tBodies[0].rows[i].myRow.fiftysix.name		 = INPUT_NAME_PREFIX + count + '_56';  // input text
				
				// ------------------------------------
				
				tbl.tBodies[0].rows[i].myRow.ra.value = count; // input radio
				count++;
			}
		}
	}
}

</script>


