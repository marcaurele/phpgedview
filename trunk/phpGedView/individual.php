<?php
/**
 * Individual Page
 *
 * Display all of the information about an individual
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
 * @subpackage Charts
 * @version $Id$
 */

require './config.php';

require_once 'includes/controllers/individual_ctrl.php';
$controller = new IndividualController();
$controller->init();

loadLangFile("lightbox:lang, googlemap:lang");

global $USE_THUMBS_MAIN, $mediacnt, $tabno;
global $linkToID;
global $SEARCH_SPIDER, $GOOGLEMAP_PH_CONTROLS;

print_header($controller->getPageTitle());

if (!$controller->indi->canDisplayName()) {
	print_privacy_error($CONTACT_EMAIL);
	print_footer();
	exit;
}
$linkToID = $controller->pid;	// -- Tell addmedia.php what to link to
?>

<table border="0" cellspacing="0" cellpadding="0" class="facts_table">
	<tr>
	<?php if ($controller->canShowHighlightedObject()) { ?>
		<td <?php if (!$USE_THUMBS_MAIN) print "rowspan=\"2\" ";?> valign="top">
			<?php print $controller->getHighlightedObject(); ?>
		</td>
	<?php } ?>
	<td valign="top">
		<?php if ((empty($SEARCH_SPIDER))&&($controller->accept_success)) print "<b>".$pgv_lang["accept_successful"]."</b><br />"; ?>
		<span class="name_head">
		<?php
		if ($TEXT_DIRECTION=="rtl") print "&nbsp;";
			print PrintReady($controller->indi->getFullName());
			print "&nbsp;&nbsp;";
 			print PrintReady("(".$controller->pid.")");
			if (PGV_USER_IS_ADMIN) {
				$pgvuser=get_user_from_gedcom_xref($GEDCOM, $controller->pid);
				if ($pgvuser) {
					print "&nbsp;";
					print printReady("(<a href=\"useradmin.php?action=edituser&amp;username={$pgvuser}\">{$pgvuser}</a>)");
				}
			}
		?>
		</span><br />
		<?php if (strlen($controller->indi->getAddName()) > 0) print "<span class=\"name_head\">".PrintReady($controller->indi->getAddName())."</span><br />"; ?>
		<?php if ($controller->indi->canDisplayDetails()) { ?>
		<table><tr>
		<?php
			$col=0; $maxcols=7;	// 4 with data and 3 spacers
			$globalfacts=$controller->getGlobalFacts();
			$nameSex = array('NAME','SEX');
			foreach ($globalfacts as $key=>$value) {
				$fact = $value->getTag();
				if (in_array($fact, $nameSex)) {
						if ($col>0) {
							print "<td width=\"10\"><br /></td>\n";
							++$col;
						}
					if ($fact=="SEX") $controller->print_sex_record($value);
					if ($fact=="NAME") $controller->print_name_record($value);
						++$col;
						$FACT_COUNT++;
						if ($col==$maxcols) {
							print "</tr><tr>";
							$col=0;
						}
					}
			}
			// Display summary birth/death info.  Note this info can come from various BIRT/CHR/BAPM/etc. records
			$summary=$controller->indi->format_first_major_fact(PGV_EVENTS_BIRT, 2);
			if (!($controller->indi->isDead())) {
				// If alive display age
				$bdate=$controller->indi->getBirthDate();
				$age = GedcomDate::GetAgeGedcom($bdate);
				if ($age!="")
					$summary.= "<span class=\"label\">".$pgv_lang["age"].":</span><span class=\"field\"> ".get_age_at_event($age, true)."</span>";
			}
			$summary.=$controller->indi->format_first_major_fact(PGV_EVENTS_DEAT, 2);
			if ($SHOW_LDS_AT_GLANCE) {
				$summary.='<b>'.get_lds_glance($controller->indi->getGedcomRecord()).'</b>';
			}
			if ($summary) {
				++$col;
				echo '<td width="10"><br /></td><td valign="top" colspan="', $maxcols-$col, '">', $summary, '</td>';
			}
		?>
		</tr>
		</table>
		<?php
		if($SHOW_COUNTER && (empty($SEARCH_SPIDER))) {
			//print indi counter only if displaying a non-private person
			require("hitcount.php");
			print "\n<br />".$pgv_lang["hit_count"]."	".$hits."\n";
		}
		// if individual is a remote individual
		// if information for this information is based on a remote site
		if ($controller->indi->isRemote())
		{
			?><br />
			<?php print $pgv_lang["indi_is_remote"]; ?><!--<br />--><!--take this out if you want break the remote site and the fact that it was remote into two separate lines-->
			<a href="<?php print encode_url($controller->indi->getLinkUrl()); ?>"><?php print $controller->indi->getLinkTitle(); ?></a>
			<?php
		}
		// if indivual is not a remote individual
		// if information for this individual is based on this local site
		// this is not need to be printed, but may be uncommented if desired
		/*else
			echo("This is a local individual.");*/
	}
	if ((!$controller->isPrintPreview())&&(empty($SEARCH_SPIDER))) {
		$showFull = ($PEDIGREE_FULL_DETAILS) ? 1 : 0;
	?>
	</td><td class="<?php echo $TEXT_DIRECTION; ?> noprint" valign="top">
		<div class="accesskeys">
			<a class="accesskeys" href="<?php print "pedigree.php?rootid=$pid&amp;show_full=$showFull";?>" title="<?php print $pgv_lang["pedigree_chart"] ?>" tabindex="-1" accesskey="<?php print $pgv_lang["accesskey_individual_pedigree"]; ?>"><?php print $pgv_lang["pedigree_chart"] ?></a>
			<a class="accesskeys" href="<?php print "descendancy.php?pid=$pid&amp;show_full=$showFull";?>" title="<?php print $pgv_lang["descend_chart"] ?>" tabindex="-1" accesskey="<?php print $pgv_lang["accesskey_individual_descendancy"]; ?>"><?php print $pgv_lang["descend_chart"] ?></a>
			<a class="accesskeys" href="<?php print "timeline.php?pids[]=$pid";?>" title="<?php print $pgv_lang["timeline_chart"] ?>" tabindex="-1" accesskey="<?php print $pgv_lang["accesskey_individual_timeline"]; ?>"><?php print $pgv_lang["timeline_chart"] ?></a>
			<?php
				if (PGV_USER_GEDCOM_ID) {
			?>
			<a class="accesskeys" href="<?php print "relationship.php?show_full=$showFull&amp;pid1=".PGV_USER_GEDCOM_ID."&amp;pid2=".$controller->pid;?>" title="<?php print $pgv_lang["relationship_to_me"] ?>" tabindex="-1" accesskey="<?php print $pgv_lang["accesskey_individual_relation_to_me"]; ?>"><?php print $pgv_lang["relationship_to_me"] ?></a>
			<?php 	}
			if ($controller->canShowGedcomRecord()) {
			?>
			<a class="accesskeys" href="javascript:show_gedcom_record();" title="<?php print $pgv_lang["view_gedcom"] ?>" tabindex="-1" accesskey="<?php print $pgv_lang["accesskey_individual_gedcom"]; ?>"><?php print $pgv_lang["view_gedcom"] ?></a>
			<?php
			}
		?>
		</div>
		<table class="sublinks_table" cellspacing="4" cellpadding="0">
			<tr>
				<td class="list_label <?php echo $TEXT_DIRECTION; ?>" colspan="5"><?php echo $pgv_lang["indis_charts"]; ?></td>
			</tr>
			<tr>
				<td class="sublinks_cell <?php echo $TEXT_DIRECTION; ?>">
				<?php
				//-- get charts menu from menubar
				$menubar = new MenuBar();
				$menu = $menubar->getChartsMenu($controller->pid); $menu->printMenu();
				?>
				</td>
				<td class="sublinks_cell <?php echo $TEXT_DIRECTION; ?>">
				<?php
				list($surname)=explode(',', $controller->indi->getSortName());
				if (!$surname) {
					$surname='@N.N.'; // TODO empty surname is not the same as @N.N.
				}
				$menu = $menubar->getListsMenu($surname); $menu->printMenu();
				if (file_exists("reports/individual.xml")) {?>
					</td><td class="sublinks_cell <?php echo $TEXT_DIRECTION; ?>">
					<?php
					//-- get reports menu from menubar
					$menu = $menubar->getReportsMenu($controller->pid); $menu->printMenu();
				}
				if ($controller->userCanEdit()) {
				?>
				</td>
				<td class="sublinks_cell <?php echo $TEXT_DIRECTION;?>">
				<?php $menu = $controller->getEditMenu(); $menu->printMenu();
				}
				if ($controller->canShowOtherMenu()) {
				?>
				</td>
				<td class="sublinks_cell <?php echo $TEXT_DIRECTION; ?>">
				<?php $menu = $controller->getOtherMenu(); $menu->printMenu();
				}
				?>
				</td>
			</tr>
		</table><br />
	<?php } ?>
	</td>
	<td width="10"><br /></td>
	</tr>
	<tr>
	<td valign="bottom" colspan="5">
	<?php if ($controller->indi->isMarkedDeleted()) print "<span class=\"error\">".$pgv_lang["record_marked_deleted"]."</span>"; ?>
<script language="JavaScript" type="text/javascript">
// <![CDATA[
// javascript function to open a window with the raw gedcom in it
function show_gedcom_record(shownew) {
	fromfile="";
	if (shownew=="yes") fromfile='&fromfile=1';
	var recwin = window.open("gedrecord.php?pid=<?php print $controller->pid; ?>"+fromfile, "_blank", "top=50,left=50,width=600,height=400,scrollbars=1,scrollable=1,resizable=1");
}
<?php if (PGV_USER_CAN_EDIT) { ?>
function open_link_remote(pid){
	window.open("addremotelink.php?pid="+pid, "_blank", "top=50,left=50,width=600,height=500,scrollbars=1,scrollable=1,resizable=1");
	return false;
}

function showchanges() {
	window.location = 'individual.php?pid=<?php print $controller->pid; ?>&show_changes=yes';
}
<?php } ?>
<!-- ====================== Added for Lightbox Module ===================== -->
<?php
if (file_exists("modules/lightbox/album.php")) {
	include('modules/lightbox/lb_defaultconfig.php');
	if (file_exists('modules/lightbox/lb_config.php')) include('modules/lightbox/lb_config.php');
	if ($theme_name=="Minimal") {
		// Force icon options to "text" when we're dealing with the Minimal theme
		if ($LB_AL_HEAD_LINKS!="none") { $LB_AL_HEAD_LINKS = "text"; }
		if ($LB_AL_THUMB_LINKS!="none") { $LB_AL_THUMB_LINKS = "text"; }
		if ($LB_ML_THUMB_LINKS!="none") { $LB_ML_THUMB_LINKS = "text"; }
	}
	include_once ("modules/lightbox/functions/lb_indi_tabs_" . $mediatab . ".php");
}else{
?>
<!-- ================== End Additions for Lightbox Module ================== -->

var tabid = new Array('0', 'facts','notes','sources','media','relatives','tree','researchlog');
<?php if (file_exists("modules/googlemap/defaultconfig.php")) {?>
var tabid = new Array('0', 'facts','notes','sources','media','relatives','tree','researchlog','googlemap');
<?php }?>
var loadedTabs = new Array(false,false,false,false,false,false,false,false,false);

<!-- ====================== Added for Lightbox Module ===================== -->
 <?php } ?>
<!-- ================== End Additions for Lightbox Module ================== -->

loadedTabs[<?php print ($controller->default_tab+1); ?>] = true;

function tempObj(tab, oXmlHttp) {
	this.processFunc = function()
	{
		if (oXmlHttp.readyState==4)
		{
			target = document.getElementById(tabid[tab]+'_content');
			evalAjaxJavascript(oXmlHttp.responseText, target);
			target.style.height = 'auto';
			if (tabid[tab]=='googlemap') {
				if (!loadedTabs[tab]) {
					loadMap();
					<?php if ($GOOGLEMAP_PH_CONTROLS != "false") {?>
					// hide controls
					GEvent.addListener(map,'mouseout',function()
					{
						map.hideControls();
					});
					// show controls
					GEvent.addListener(map,'mouseover',function()
					{
						map.showControls();
					});
					GEvent.trigger(map,'mouseout');
					<?php
					}
					?>
					map.setMapType(GOOGLEMAP_MAP_TYPE);
				}
				SetMarkersAndBounds();
				ResizeMap();
				ResizeMap();
			}
			//-- initialize lightbox tabs if lightbox installed
			<?php if (file_exists("modules/lightbox/album.php")) { ?>
				// if (tabid[tab]=='lightbox2' || tabid[tab]=='facts' || tabid[tab]=='media' || tabid[tab]=='relatives') {
				CB_Init();
				// }
			<?php } ?>
			loadedTabs[tab] = true;
		}
	};
}

function tabswitch(n) {
	if (!tabid[n]) n = 1;
	for (var i=1; i<tabid.length; i++) {
		var disp='none'; // reset inactive tabs
		if (i==n || n==0) disp='block'; // active tab(s) : one or all
		document.getElementById(tabid[i]).style.display=disp;
		if (disp=='block' && !loadedTabs[i]) {
			// -- load ajax
			if (tabid[i]=='tree') {
				treetab.newRoot('<?php print $controller->pid; ?>', treetab.innerPort, '<?php print $GEDCOM; ?>');
				loadedTabs[i] = true;
			}
			else if (document.getElementById(tabid[i]+'_content')) {
				var oXmlHttp = createXMLHttp();
				var link = "individual.php?action=ajax&pid=<?php echo $controller->pid; ?>&tab="+i+"&show_changes=<?php if ($controller->show_changes) echo 'yes'?>";
				oXmlHttp.open("get", link, true);
				temp = new tempObj(i, oXmlHttp);
				oXmlHttp.onreadystatechange=temp.processFunc;
					oXmlHttp.send(null);
				}
			}
	}
	// empty tabs
	for (i=0; i<tabid.length; i++) {
		var elt = document.getElementById('door'+i);
		if (elt) {
			if (document.getElementById('no_tab'+i)) { // empty ?
				if (<?php if (PGV_USER_CAN_EDIT) echo 'true'; else echo 'false';?>) {
					elt.style.display='block';
					elt.style.opacity='0.4';
					elt.style.filter='alpha(opacity=40)';
				}
				else elt.style.display='none'; // empty and not editable ==> hide
				//if (i==3 && <?php if ($SHOW_SOURCES>=PGV_USER_ACCESS_LEVEL) echo 'true'; else echo 'false';?>) elt.style.display='none'; // no sources
				if (i==4 && <?php if (!$MULTI_MEDIA) echo 'true'; else echo 'false';?>) elt.style.display='none'; // no multimedia
				if (i==7) elt.style.display='none'; // hide researchlog
				if (i==9 && <?php if (!$MULTI_MEDIA) echo 'true'; else echo 'false';?>) elt.style.display='none'; // no multimedia (for Album tab)
				// ALL : hide empty contents
				if (n==0) document.getElementById(tabid[i]).style.display='none';
			}
			else elt.style.display='block';
		}
	}
	// current door
	for (i=0; i<tabid.length; i++) {
		var elt = document.getElementById('door'+i);
		if (elt) elt.className='door optionbox rela';
	}
	document.getElementById('door'+n).className='door optionbox';
	// set a cookie which stores the last tab they clicked on
	document.cookie = "lasttabs=<?php print $controller->getCookieTabString().$controller->pid; ?>="+n;
	return false;
}

// function is required by cloudy theme
function resize_content_div(i) {
	// check for container ..
	var cont = document.getElementById("content");
	if (!cont) cont = document.getElementById("container");
	if (cont) {
		if (document.getElementById("marker"+i)) {
			var y = getAbsoluteTop("marker"+i);
			if (y<300) y=600;
			cont.style.height =y.toString()+'px';
		}
	}
}
 //]]>
</script>
<script src="phpgedview.js" language="JavaScript" type="text/javascript"></script>
<?php
function loading_message() {
	global $pgv_lang;
	echo "<p style=\"margin: 20px 20px 20px 20px\">";
	echo "<img src=\"images/loading.gif\" alt=\"".$pgv_lang["loading"]."\" title=\"".$pgv_lang["loading"]."\" />";
	echo "</p>";
}
if ((!$controller->isPrintPreview())&&(empty($SEARCH_SPIDER))) {
?>
<div class="door">
<dl>
<dd id="door1"><a href="javascript:;" onclick="tabswitch(1); return false;" ><?php print $pgv_lang["personal_facts"]?></a></dd>
<dd id="door2"><a href="javascript:;" onclick="tabswitch(2); return false;" ><?php print $pgv_lang["notes"]?></a></dd>
<dd id="door3"><a href="javascript:;" onclick="tabswitch(3); return false;" ><?php print $pgv_lang["ssourcess"]?></a></dd>

<!-- ====================== Added for Lightbox Module ===================== -->
<?php
if (file_exists("modules/lightbox/album.php")) {
	include_once ("modules/lightbox/functions/lb_indi_doors_" . $mediatab . ".php");
}else{
?>
<!-- ================== End Additions for Lightbox Module ================== -->

<dd id="door4"><a href="javascript:;" onclick="tabswitch(4); return false;" ><?php print $pgv_lang["media"]?></a></dd>
<dd id="door5"><a href="javascript:;" onclick="tabswitch(5); return false;" ><?php print $pgv_lang["relatives"]?></a></dd>
<dd id="door6"><a href="javascript:;" onclick="tabswitch(6); return false;" ><?php print $pgv_lang["tree"]?></a></dd>
<dd id="door7"><a href="javascript:;" onclick="tabswitch(7); return false;" ><?php print $pgv_lang["research_assistant"]?></a></dd>
<?php if (file_exists("modules/googlemap/defaultconfig.php")) {?>
<dd id="door8"><a href="javascript:;" onclick="tabswitch(8); if (loadedTabs[8]) {ResizeMap(); ResizeMap();} return false;" ><?php print $pgv_lang["googlemap"]?></a></dd>
<?php }?>
<dd id="door0"><a href="javascript:;" onclick="tabswitch(0); if (loadedTabs[8]) {ResizeMap(); ResizeMap();} return false;" ><?php print $pgv_lang["all"]?></a></dd>

<!-- ====================== Added for Lightbox Module ===================== -->
<?php } ?>
<!-- ================== End Additions for Lightbox Module ================== -->

</dl>
</div>
<br />
<?php
}
?>
	</td>
	</tr>
</table>

<!-- ======================== Start 1st tab individual page ============ Personal Facts and Details -->
<?php
if(empty($SEARCH_SPIDER))
	print "<div id=\"facts\" class=\"tab_page\" style=\"display:none;\" >\n";
else
	print "<div id=\"facts\" class=\"tab_page\" style=\"display:block;\" >\n";
print "<span class=\"subheaders\">".$pgv_lang["personal_facts"]."</span><div id=\"facts_content\">";
if (($controller->default_tab==0)||(!empty($SEARCH_SPIDER))) $controller->getTab(0);
else loading_message();
?>
</div>
</div>
<!-- ======================== Start 2nd tab individual page ==== Notes ======= -->
<?php
if(empty($SEARCH_SPIDER))
	print "<div id=\"notes\" class=\"tab_page\" style=\"display:none;\" >\n";
else
	print "<div id=\"notes\" class=\"tab_page\" style=\"display:block;\" >\n";
print "<span class=\"subheaders\">".$pgv_lang["notes"]."</span><div id=\"notes_content\">";
if (($controller->default_tab==1)||(!empty($SEARCH_SPIDER))) $controller->getTab(1);
else {
	if ($controller->get_note_count()>0) loading_message();
	else print "<span id=\"no_tab2\">".$pgv_lang["no_tab2"]."</span>";
	}
?>
</div>
</div>
<!-- =========================== Start 3rd tab individual page === Sources -->
<?php
if(empty($SEARCH_SPIDER))
	print "<div id=\"sources\" class=\"tab_page\" style=\"display:none;\" >\n";
else
	print "<div id=\"sources\" class=\"tab_page\" style=\"display:block;\" >\n";
if ($SHOW_SOURCES>=PGV_USER_ACCESS_LEVEL) {
	print "<span class=\"subheaders\">".$pgv_lang["ssourcess"]."</span><div id=\"sources_content\">";
	if (($controller->default_tab==2)||(!empty($SEARCH_SPIDER))) $controller->getTab(2);
	else {
		if ($controller->get_source_count()>0) loading_message();
		else print "<span id=\"no_tab3\">".$pgv_lang["no_tab3"]."</span>";
	}
	print "</div>\n";
}
?>
</div>
<!-- ==================== Start 4th tab individual page ==== Media -->
<?php
if(empty($SEARCH_SPIDER))
	print "<div id=\"media\" class=\"tab_page\" style=\"display:none;\" >\n";
else
	print "<div id=\"media\" class=\"tab_page\" style=\"display:block;\" >\n";
if ($MULTI_MEDIA) {
	print "<span class=\"subheaders\">".$pgv_lang["media"]."</span>";
	// For Reorder media ------------------------------------
	if (PGV_USER_CAN_EDIT) {
		print "<center>";
		include_once('includes/media_tab_head.php');
		print "</center>";
	}
	// -----------------------------------------------------------
	print "<div id=\"media_content\">";
	if (($controller->default_tab==3)||(!empty($SEARCH_SPIDER))) $controller->getTab(3);
	else {
		if ($controller->get_media_count()>0) loading_message();
		else print "<span id=\"no_tab4\">".$pgv_lang["no_tab4"]."</span>";
	}
	print "</div>\n";
}
?>
</div>
<!-- ============================= Start 5th tab individual page ==== Close relatives -->
<?php
if(empty($SEARCH_SPIDER))
	print "<div id=\"relatives\" class=\"tab_page\" style=\"display:none;\" >\n";
else
	print "<div id=\"relatives\" class=\"tab_page\" style=\"display:block;\" >\n";
?>
<div id="relatives_content">
<?php
	if (($controller->default_tab==4)||(!empty($SEARCH_SPIDER))) $controller->getTab(4);
	else loading_message();
?>
</div>
</div>

<!-- ============================= Start 6th tab individual page ==== Tree -->
<?php
if(empty($SEARCH_SPIDER)) {
	print "<div id=\"tree\" class=\"tab_page\" style=\"display:none; border: solid blue 1px;\" >\n";
?>
<div id="tree_content">
<?php
	require_once('includes/classes/class_treenav.php');
	if ($controller->default_tab==5) {
		$inav = new TreeNav($controller->pid,'treetab');
	}
	else {
		$inav = new TreeNav('none','treetab');
	}
	$inav->generations = 7;
	$inav->zoomLevel = -1;
	$inav->drawViewport('treetab', "100%", "600px");
?>
</div>
</div>
<?php } ?>
<!-- ============================ Start 7th tab individual page === Research Assistant -->
<?php
// Only show this section if we are not talking to a search engine.
if(empty($SEARCH_SPIDER)) {
	print "<div id=\"researchlog\" class=\"tab_page\" style=\"display:none;\" >\n";
	print "<span class=\"subheaders\">".$pgv_lang["research_assistant"]."</span>\n";
	print "<div id=\"researchlog_content\">\n";

	if (file_exists("modules/research_assistant/research_assistant.php") && ($SHOW_RESEARCH_ASSISTANT>=PGV_USER_ACCESS_LEVEL)) {
		if (version_compare(PGV_VERSION, '4.1')<0) print "<script src=\"compat.js\" language\"JavaScript\" type=\"text/javascript\"></script>\n";
		print "<script type=\"text/javascript\" src=\"modules/research_assistant/research_assistant.js\"></script>";
		if ($controller->default_tab==6) $controller->getTab(6);
		else loading_message();
	}
	else {
		print "<table class=\"facts_table\"><tr><td id=\"no_tab7\" colspan=\"2\" class=\"facts_value\">".$pgv_lang["no_tab6"]."</td></tr></table>\n";
	}
	print "</div>\n";
	print "</div>\n";
}
?>

<!-- =========================== Start 8th tab individual page ==== GoogleMaps -->
<?php
// Only show this section if we are not talking to a search engine.
if(empty($SEARCH_SPIDER)) {
	if (file_exists("modules/googlemap/defaultconfig.php")) {
		print "<div id=\"googlemap\" class=\"tab_page\" style=\"display:none;\" >\n";
		print "<span class=\"subheaders\">".$pgv_lang["googlemap"]."</span>\n";
		include_once('modules/googlemap/googlemap.php');
		if ($GOOGLEMAP_ENABLED == "false") {
			print "<table class=\"facts_table\">\n";
			print "<tr><td id=\"no_tab8\" colspan=\"2\" class=\"facts_value\">".$pgv_lang["gm_disabled"]."</td></tr>\n";
			if (PGV_USER_IS_ADMIN) {
				print "<tr><td align=\"center\" colspan=\"2\">\n";
				print "<a href=\"module.php?mod=googlemap&amp;pgvaction=editconfig\">".$pgv_lang["gm_manage"]."</a>";
				print "</td></tr>\n";
			}
			print "\n\t</table>\n<br />";
			?>
			<script language="JavaScript" type="text/javascript">
			<!--
				function ResizeMap () {}
				function SetMarkersAndBounds () {}
			//-->
			</script>
			<?php
		}else{
			if(empty($SEARCH_SPIDER)) {
				$tNew = preg_replace("/&HIDE_GOOGLEMAP=true/", "", $_SERVER["REQUEST_URI"]);
				$tNew = preg_replace("/&HIDE_GOOGLEMAP=false/", "", $tNew);
				$tNew = preg_replace("/&/", "&amp;", $tNew);
				if($SESSION_HIDE_GOOGLEMAP == "true") {
					print "&nbsp;&nbsp;&nbsp;<span class=\"font9\"><a href=\"".$tNew."&amp;HIDE_GOOGLEMAP=false\">";
					print "<img src=\"".$PGV_IMAGE_DIR."/".$PGV_IMAGES["plus"]["other"]."\" border=\"0\" width=\"11\" height=\"11\" alt=\"".$pgv_lang["activate"]."\" title=\"".$pgv_lang["activate"]."\" />";
					print " ".$pgv_lang["activate"]."</a></span>\n";
					} else {
						print "&nbsp;&nbsp;&nbsp;<span class=\"font9\"><a href=\"" .$tNew."&amp;HIDE_GOOGLEMAP=true\">";
						print "<img src=\"".$PGV_IMAGE_DIR."/".$PGV_IMAGES["minus"]["other"]."\" border=\"0\" width=\"11\" height=\"11\" alt=\"".$pgv_lang["deactivate"]."\" title=\"".$pgv_lang["deactivate"]."\" />";
						print " ".$pgv_lang["deactivate"]."</a></span>\n";
					}
			}
			if (!$controller->indi->canDisplayName()) {
				print "\n\t<table class=\"facts_table\">";
				print "<tr><td class=\"facts_value\">";
				print_privacy_error($CONTACT_EMAIL);
				print "</td></tr>";
				print "\n\t</table>\n<br />";
				print "<script type=\"text/javascript\">\n";
				print "function ResizeMap ()\n{\n}\n</script>\n";
			}else{
				if(empty($SEARCH_SPIDER)) {
					if($SESSION_HIDE_GOOGLEMAP == "false") {
						include_once('modules/googlemap/googlemap.php');
						print "<table class=\"facts_table\">\n";
						print "<tr><td valign=\"top\">\n";
						print "<div id=\"googlemap_left\">\n";
						print "<img src=\"images/hline.gif\" width=\"".$GOOGLEMAP_XSIZE."\" height=\"0\" alt=\"\" /><br/>";
						print "<div id=\"map_pane\" style=\"border: 1px solid gray; color:black; width: 100%; height: ".$GOOGLEMAP_YSIZE."px\"></div>\n";
						if (PGV_USER_IS_ADMIN) {
							print "<table width=\"100%\"><tr>\n";
							print "<td width=\"33%\" align=\"left\">\n";
							print "<a href=\"module.php?mod=googlemap&amp;pgvaction=editconfig\">".$pgv_lang["gm_manage"]."</a>";
							print "</td>\n";
							print "<td width=\"33%\" align=\"center\">\n";
							print "<a href=\"module.php?mod=googlemap&amp;pgvaction=places\">".$pgv_lang["edit_place_locations"]."</a>";
							print "</td>\n";
							print "<td width=\"33%\" align=\"right\">\n";
							print "<a href=\"module.php?mod=googlemap&amp;pgvaction=placecheck\">".$pgv_lang["placecheck"]."</a>";
							print "</td>\n";
							print "</tr></table>\n";
						}
						print "</div>\n";
						print "</td>\n";
						print "<td valign=\"top\" width=\"33%\">\n";
						print "<div id=\"googlemap_content\">\n";
						setup_map();
						if ($controller->default_tab==7) {
							$controller->getTab(7);
						}
						print "</div>\n";
						print "</td></tr></table>\n";
					}
				}
			}
		}
		// start
		print "<img src=\"".$PGV_IMAGE_DIR."/".$PGV_IMAGES["spacer"]["other"]."\" id=\"marker6\" width=\"1\" height=\"1\" alt=\"\" />";
		// end
		print "</div>\n";
	}
}

?>
<!-- ========================== End 8th tab individual page ==== GoogleMaps ===== -->


<!-- ========================== Start 9th tab individual page ==== Album ======== -->
<?php
if(empty($SEARCH_SPIDER) && file_exists("modules/lightbox/album.php")) {
	print "<div id=\"lightbox2\" class=\"tab_page\" style=\"display:none;\" >\n";

	if ($MULTI_MEDIA && file_exists("modules/lightbox/album.php")) {

		print "<span class=\"subheaders\">" . $pgv_lang["lightbox"] . "</span>\n";
		print "&nbsp;&nbsp;";

		// ---------- Help link --------------------
		print_help_link("lb_general_help", "qm", "lb_help", true);


		// Header info ---------------------------------------------------
		$mediacnt = $controller->get_media_count();
		if ($mediacnt!=0) {
			include_once('modules/lightbox/functions/lb_head.php');
		}else{
			include_once('modules/lightbox/functions/lb_head.php');
			print "<table class=\"facts_table\"><tr><td id=\"no_tab9\" colspan=\"2\" class=\"facts_value\">".$pgv_lang["no_tab4"]."</td></tr></table>\n";
		}
	}else{
		print "<div id=\"lightbox2\" class=\"tab_page\" style=\"display:block; \" >\n";
	}
	// Content info ---------------------------------------------------
	print "<div id=\"lightbox2_content\"> \n";
	if ($mediacnt!=0) {
		if ($MULTI_MEDIA && file_exists("modules/lightbox/album.php")) {

			// LB Fix if no googlemaps ========================================================
			if (file_exists("modules/googlemap/googlemap.php")) {
				if (($controller->default_tab==8)||(!empty($SEARCH_SPIDER))) {
					$controller->getTab(8) ;
				}else{
					loading_message();
				}
			}else{
				if (($controller->default_tab==7)||(!empty($SEARCH_SPIDER))) {
					$controller->getTab(7) ;
				}else{
					loading_message();
				}
			}
			// LB Fix if no googlemaps ========================================================
		}
	}

	print "</div>\n";
	print "</div>\n";
}
?>
<!-- ============================= End 9th tab individual page ==== Album -->

<script language="JavaScript" type="text/javascript">
<!--
	var catch_and_ignore;
	function paste_id(value) {
		catch_and_ignore = value;
	}
<?php
//-- make sure googlemap is reloaded
if ($controller->default_tab==7) print "loadedTabs[".($controller->default_tab+1)."] = false;\n";
if ($controller->isPrintPreview()) print "tabswitch(0);\n";
else print "tabswitch(". ($controller->default_tab+1) .");\n";
if ($controller->default_tab==5) print "treetab.sizeLines();\n";
?>
if (typeof toggleByClassName == "undefined") alert('phpgedview.js\na javascript function is missing\n\nPlease clear your Web browser cache');

//-->



</script>
<?php
if(empty($SEARCH_SPIDER))
	print_footer();
else {
	if($SHOW_SPIDER_TAGLINE)
		print $pgv_lang["label_search_engine_detected"].": ".$SEARCH_SPIDER;
	print "\n</div>\n\t</body>\n</html>";
}
?>
