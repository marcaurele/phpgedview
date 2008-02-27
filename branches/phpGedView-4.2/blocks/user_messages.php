<?php
/**
 * User Messages Block
 *
 * This block will print a users messages
 *
 * phpGedView: Genealogy Viewer
 * Copyright (C) 2002 to 2005  John Finlay and Others
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
 * @subpackage Blocks
 */

$PGV_BLOCKS["print_user_messages"]["name"]		= $pgv_lang["user_messages_block"];
$PGV_BLOCKS["print_user_messages"]["descr"]		= "user_messages_descr";
$PGV_BLOCKS["print_user_messages"]["type"]		= "user";
$PGV_BLOCKS["print_user_messages"]["canconfig"]	= false;
$PGV_BLOCKS["print_user_messages"]["config"]	= array("cache"=>0);

//-- print user messages
function print_user_messages($block=true, $config="", $side, $index) {
	global $pgv_lang, $PGV_IMAGE_DIR, $TEXT_DIRECTION, $PGV_STORE_MESSAGES, $PGV_IMAGES;

	$usermessages = getUserMessages(getUserName());

	$id="user_messages";
	$title = print_help_link("mygedview_message_help", "qm", "", false, true);
	$title .= $pgv_lang["my_messages"]."&nbsp;&nbsp;";
	if ($TEXT_DIRECTION=="rtl") $title .= getRLM();
	$title .= "(".count($usermessages).")";
	if ($TEXT_DIRECTION=="rtl") $title .= getRLM();

	$content = "";
	$content .= "<form name=\"messageform\" action=\"\" onsubmit=\"return confirm('".$pgv_lang["confirm_message_delete"]."');\">\n";
	if (count($usermessages)==0) {
		$content .= $pgv_lang["no_messages"]."<br />";
	}
	else {
		$content .= '
			<script language="JavaScript" type="text/javascript">
			<!--
				function select_all() {
					';
		foreach($usermessages as $key=>$message) {
			if (isset($message["id"])) $key = $message["id"];
			$content .= '
						var cb = document.getElementById("cb_message'.$key.'");
						if (cb) {
							if (!cb.checked) cb.checked = true;
							else cb.checked = false;
						}
						';
		}
		$content .= '
					return false;
				}
			//-->
			</script>
			';
		$content .= "<input type=\"hidden\" name=\"action\" value=\"deletemessage\" />\n";
		$content .= "<table class=\"list_table\"><tr>\n";
		$content .= "<td class=\"list_label\">".$pgv_lang["delete"]."<br /><a href=\"javascript:;\" onclick=\"return select_all();\">".$pgv_lang["all"]."</a></td>\n";
		$content .= "<td class=\"list_label\">".$pgv_lang["message_subject"]."</td>\n";
		$content .= "<td class=\"list_label\">".$pgv_lang["date_created"]."</td>\n";
		$content .= "<td class=\"list_label\">".$pgv_lang["message_from"]."</td>\n";
		$content .= "</tr>\n";
		foreach($usermessages as $key=>$message) {
			if (isset($message["id"])) $key = $message["id"];
			$content .= "<tr>";
			$content .= "<td class=\"list_value_wrap\"><input type=\"checkbox\" id=\"cb_message$key\" name=\"message_id[]\" value=\"$key\" /></td>\n";
			$showmsg=preg_replace("/(\w)\/(\w)/","\$1/<span style=\"font-size:1px;\"> </span>\$2",PrintReady($message["subject"]));
			$showmsg=preg_replace("/@/","@<span style=\"font-size:1px;\"> </span>",$showmsg);
			$content .= "<td class=\"list_value_wrap\"><a href=\"javascript:;\" onclick=\"expand_layer('message$key'); return false;\"><b>".$showmsg."</b> <img id=\"message${key}_img\" src=\"".$PGV_IMAGE_DIR."/".$PGV_IMAGES["plus"]["other"]."\" border=\"0\" alt=\"\" title=\"\" /></a></td>\n";
				if (!empty($message["created"])) {
					$time = strtotime($message["created"]);
				} else {
					$time = time();
				}
			$content .= "<td class=\"list_value_wrap\">".format_timestamp($time)."</td>\n";
			$content .= "<td class=\"list_value_wrap\">";
			$user_id=get_user_id($message["from"]);
			if ($user_id) {
				$content .= PrintReady(getUserFullName($user_id));
				if ($TEXT_DIRECTION=="ltr") {
					$content .= " " . getLRM() . " - ".htmlspecialchars($user_id) . getLRM();
					} else {
					$content .= " " . getRLM() . " - ".htmlspecialchars($user_id) . getRLM();
					}
				$content .= "<a href=\"mailto:".$user_id."\">".preg_replace("/@/","@<span style=\"font-size:1px;\"> </span>",$user_id)."</a>";
			}
			$content .= "</td>\n";
			$content .= "</tr>\n";
			$content .= "<tr><td class=\"list_value_wrap\" colspan=\"5\"><div id=\"message$key\" style=\"display: none;\">\n";
			$message["body"] = nl2br(htmlspecialchars($message["body"]));
			$message["body"] = expand_urls($message["body"]);

			$content .= PrintReady($message["body"])."<br /><br />\n";
			if (preg_match("/RE:/", $message["subject"])==0) $message["subject"]="RE:".$message["subject"];
			if ($tempuser) $content .= "<a href=\"javascript:;\" onclick=\"reply('".$message["from"]."', '".$message["subject"]."'); return false;\">".$pgv_lang["reply"]."</a> | ";
			$content .= "<a href=\"index.php?action=deletemessage&amp;message_id=$key\" onclick=\"return confirm('".$pgv_lang["confirm_message_delete"]."');\">".$pgv_lang["delete"]."</a></div></td></tr>\n";
		}
		$content .= "</table>\n";
		$content .= "<input type=\"submit\" value=\"".$pgv_lang["delete_selected_messages"]."\" /><br /><br />\n";
	}
		if (get_user_count()>1) {
			$content .= $pgv_lang["message"]." <select name=\"touser\">\n";
			$my_user_name = getUserName();
			if (userIsAdmin()) {
				$content .= "<option value=\"all\">".$pgv_lang["broadcast_all"]."</option>\n";
				$content .= "<option value=\"never_logged\">".$pgv_lang["broadcast_never_logged_in"]."</option>\n";
				$content .= "<option value=\"last_6mo\">".$pgv_lang["broadcast_not_logged_6mo"]."</option>\n";
			}
			foreach(get_all_users() as $user_id=>$user_name) {
				if ($user_name!=$my_user_name && get_user_setting($user_id, 'verified_by_admin')=='yes') {
					print "<option value=\"".$user_id."\">".PrintReady(getUserFullName($user_id))." ";
					if ($TEXT_DIRECTION=="ltr") {
						print getLRM()." - ".$user_id.getLRM();
					} else {
						print getRLM()." - ".$user_id.getRLM();
					}
					print "</option>";
				}
			}
			$content .= "</select><input type=\"button\" value=\"".$pgv_lang["send"]."\" onclick=\"message(document.messageform.touser.options[document.messageform.touser.selectedIndex].value, 'messaging2', ''); return false;\" />\n";
		}
	$content .= "</form>\n";
	
	global $THEME_DIR;
	if ($block) {
		include($THEME_DIR."/templates/block_small_temp.php");
	} else {
		include($THEME_DIR."/templates/block_main_temp.php");
	}
}
?>
