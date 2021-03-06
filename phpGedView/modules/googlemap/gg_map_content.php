<?php
/**
 * Google map module for phpGedView
 *
 * phpGedView: Genealogy Viewer
 * Copyright (C) 2002 to 2010  PGV Development Team.  All rights reserved.
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
 * @subpackage Module
 * $Id$
 * @author windmillway
 */
if (file_exists(PGV_ROOT.'modules/googlemap/defaultconfig.php')) {
	require_once PGV_ROOT.'modules/googlemap/googlemap.php';

//	echo "<div id=\"googlemap\" class=\"tab_page\" style=\"display:none;\" >\n";
	echo '<span class="subheaders">', $pgv_lang["googlemap"], "</span>\n";

	if (!$GOOGLEMAP_ENABLED) {
		echo "<table class=\"facts_table\">\n";
		echo '<tr><td id="no_tab8" colspan="2" class="facts_value">', $pgv_lang["gm_disabled"], "</td></tr>\n";
		if (PGV_USER_IS_ADMIN) {
			echo "<tr><td align=\"center\" colspan=\"2\">\n";
			echo '<a href="module.php?mod=googlemap&amp;pgvaction=editconfig">', $pgv_lang["gm_manage"], '</a>';
			echo '</td>';
			echo "</tr>\n";
		}
		echo "\n\t</table>\n<br />";
		?>
		<script language="JavaScript" type="text/javascript">
		<!--
			function ResizeMap () {}
			function SetMarkersAndBounds () {}
		//-->
		</script>
		<?php
	} else {
		if (empty($SEARCH_SPIDER)) {
			$tNew = str_replace("&HIDE_GOOGLEMAP=true", "", $_SERVER["REQUEST_URI"]);
			$tNew = str_replace("&HIDE_GOOGLEMAP=false", "", $tNew);
			$tNew = str_replace("&", "&amp;", $tNew);
			if ($SESSION_HIDE_GOOGLEMAP == "true") {
				echo '&nbsp;&nbsp;&nbsp;<span class="font9"><a href="', $tNew, '&amp;HIDE_GOOGLEMAP=false">';
				echo '<img src="', $PGV_IMAGE_DIR, '/', $PGV_IMAGES["plus"]["other"], '" border="0" width="11" height="11" alt="', $pgv_lang["activate"], '" title="', $pgv_lang["activate"], '" />';
				echo ' ', $pgv_lang["activate"], "</a></span>\n";
				} else {
					echo '&nbsp;&nbsp;&nbsp;<span class="font9"><a href="', $tNew, '&amp;HIDE_GOOGLEMAP=true">';
					echo '<img src="', $PGV_IMAGE_DIR, '/', $PGV_IMAGES["minus"]["other"], '" border="0" width="11" height="11" alt="', $pgv_lang["deactivate"], '" title="', $pgv_lang["deactivate"], '" />';
					echo ' ', $pgv_lang["deactivate"], "</a></span>\n";
				}
		}
		if (!$controller->indi->canDisplayName()) {
			echo "\n\t<table class=\"facts_table\">";
			echo '<tr><td class="facts_value">';
			print_privacy_error($CONTACT_EMAIL);
			echo '</td></tr>';
			echo "\n\t</table>\n<br />";
			echo "<script type=\"text/javascript\">\n";
			echo "function ResizeMap ()\n{\n}\n</script>\n";
		} else {
			if (empty($SEARCH_SPIDER)) {
				if ($SESSION_HIDE_GOOGLEMAP == "false") {
					require_once PGV_ROOT.'modules/googlemap/googlemap.php';
					echo "<table width=\"100%\" border=\"0\" class=\"facts_table\">\n";
					echo "<tr><td valign=\"top\">\n";
					echo "<div id=\"googlemap_left\">\n";
					echo '<img src="images/hline.gif" width="', $GOOGLEMAP_XSIZE, '" height="0" alt="" /><br />';
					echo '<div id="map_pane" style="border: 1px solid gray; color:black; width: 100%; height: ', $GOOGLEMAP_YSIZE, "px\"></div>\n";
					if (PGV_USER_IS_ADMIN) {
						echo "<table width=\"100%\"><tr>\n";
						echo "<td width=\"33%\" align=\"left\">\n";
						echo '<a href="module.php?mod=googlemap&amp;pgvaction=editconfig">', $pgv_lang["gm_manage"], '</a>';
						echo "</td>\n";
						echo "<td width=\"33%\" align=\"center\">\n";
						echo '<a href="module.php?mod=googlemap&amp;pgvaction=places">', $pgv_lang["edit_place_locations"], '</a>';
						echo "</td>\n";
						echo "<td width=\"33%\" align=\"right\">\n";
						echo '<a href="module.php?mod=googlemap&amp;pgvaction=placecheck">', $pgv_lang["placecheck"], '</a>';
						echo "</td>\n";
						echo "</tr></table>\n";
					}
					echo "</div>\n";
					echo "</td>\n";
					echo "<td valign=\"top\" width=\"30%\">\n";
					echo "<div id=\"googlemap_content\">\n";
					setup_map();
					if ($controller->default_tab==7) {
//						$controller->getTab(7);
					} else {
						loading_message();
					}
					echo "</div>\n";
					echo '</td>';

					// Dummy <td> for Navigator =============================================================
					// Show or Hide Navigator -----------
					if (isset($_COOKIE['famnav'])) {
							$Fam_Navigator=$_COOKIE['famnav'];
					}else{
						$Fam_Navigator="YES";
					}
					if ($Fam_Navigator == "HIDE") {
						echo '<td width="220px" align="center" valign="top">';
						//
						echo '</td>';
					}
					// =====================================================================================
					echo "</tr></table>\n";
				}
			}
		}
	}
	// start
	echo '<img src="', $PGV_IMAGE_DIR, '/', $PGV_IMAGES["spacer"]["other"], '" id="marker6" width="1" height="1" alt="" />';
	// end
//	echo "</div>\n";
}
?>
