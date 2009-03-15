<?php
/**
 * Acts as interface into gedview. Is passsed a cookie from pgvindex.php
 * (that module should be the one called from postnuke - see the readme.txt)
 * 
 * This cookie contains the userid, fullname and email from postnuke.
 * The module checks to see if the user is defined to phpGedView. If so
 * it logs thm on, if not it adds them as a user to the system.
 * A number or parameters can be defined in post-config.php to define
 * defaults for the new users. These are
 * language			eg english - must be one of the supported languages
 * user verified		set to yes
 * verified by admin	do you want a sep admin verification - set to no if you
 * 						want to verify all new users
 * password			default password to assign as initial password. This
 * 					allows the user to login directly in gedview without
 * 					going through PostNuke. The yser can subsequently
 * 					change their password if desired
 * canedit				can user use the eedit feature
 * canadmin			is user an admin
 * rootid				default rootid for the user
 * language			default language
 * theme				default theme
 * 
 * Just put this in your phpGedView folder. It is called by pgvindex.php which 
 * should be (with post-config.php and postwrap.js in modules/phpGedView)
 *
 * phpGedView: Genealogy Viewer
 * Copyright (C) 2002 to 2005  PGV Development Team
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
 * @author Jim Carrey
 * @package PhpGedView
 * @subpackage PostNuke
 */

	$ModName = $GLOBALS['name'];
// get the phpGedView config - our config comes through via cookies
		require ("config.php");
		$def_urlcmd = "";
		$post_firstname = " ";
		$post_lastname = " ";
		$post_email = " ";
		$post_user = "";
		
		// create some variables in case they dont get in from the cookie
		$def_canedit = "none";		
		$def_canadmin = "";		
		$def_theme = " ";
		$def_rootid = "I1";
		$def_canview = "";
		$def_verified = "yes";
		$def_verified_by_admin = "no";
		$def_contact_method = "messaging2";
		$def_gedcom = "";
		$def_language = "english";
		$def_upass = "";
		$def_create_user = "no";

		// pick up cookie values
		if (isset($_COOKIE['def_canedit'])) $def_canedit = $_COOKIE['def_canedit'];
		if (isset($_COOKIE['def_canadmin'])) $def_canadmin = $_COOKIE['def_canadmin'];
		if (isset($_COOKIE['def_theme'])) $def_theme = $_COOKIE['def_theme'];
		if (isset($_COOKIE['def_rootid'])) $def_rootid = $_COOKIE['def_rootid'];
		if (isset($_COOKIE['def_canview'])) $def_canview = $_COOKIE['def_canview'];
		if (isset($_COOKIE['def_verified'])) $def_verified = $_COOKIE['def_verified'];
		if (isset($_COOKIE['def_verified_by_admin'])) $def_verified_by_admin = $_COOKIE['def_verified_by_admin'];
		if (isset($_COOKIE['def_contact_method'])) $def_contact_method = $_COOKIE['def_contact_method'];
		if (isset($_COOKIE['def_gedcom'])) $def_gedcom = $_COOKIE['def_gedcom'];
		if (isset($_COOKIE['def_language'])) $def_language = $_COOKIE['def_language'];
		if (isset($_COOKIE['def_upass'])) $def_upass = $_COOKIE['def_upass'];
		if (isset($_COOKIE['def_create_user'])) $def_create_user = $_COOKIE['def_create_user'];
		
		if (isset($_COOKIE['post_user'])) $post_user = $_COOKIE['post_user'];
		if (isset($_COOKIE['post_firstname'])) $post_fullname = $_COOKIE['post_firstname'];
		if (isset($_COOKIE['post_lastname'])) $post_fullname = $_COOKIE['post_lastname'];
		if (isset($_COOKIE['post_email'])) $post_email = $_COOKIE['post_email'];
		if (isset($_COOKIE['post_canedit'])) $post_canedit = $_COOKIE['post_canedit'];


		// need to add the user into gedview - but only if def_create_user says its ok
		if ($post_user && !get_user_id($post_user) && $def_create_user=='yes')) {
			if ($user_id=create_user($user_id, crypt($def_upass))) {
				set_user_setting($user_id, 'firstname', $post_firstname);
				set_user_setting($user_id, 'lastname', $post_lastname);
				set_user_setting($user_id, 'relationship_privacy', 'N');
				set_user_setting($user_id, 'max_relation_path', '0');
				set_user_setting($user_id, 'auto_accept', 'N');
				if (($def_canedit == 'edit') || ($post_canedit == 'yes')) {
					set_user_gedcom_setting($user_id, $def_gedcom, 'canedit',  'edit');
				} else {
					set_user_gedcom_setting($user_id, $def_gedcom, 'canedit',  'access');
				}
				set_user_gedcom_setting($user_id, $def_gedcom, 'rootid', $def_rootid);
				set_user_setting($user_id, 'canadmin', $def_canadmin ? 'Y' : 'N');
				set_user_setting($user_id, 'email', $post_email);
				set_user_setting($user_id, 'verified', 'yes');
				set_user_setting($user_id, 'verified_by_admin', $def_verified_by_admin);
				set_user_setting($user_id, 'theme', $def_theme);
				set_user_setting($user_id, 'language', $def_language);
				set_user_setting($user_id, 'reg_timestamp', date('U'));
				set_user_setting($user_id, 'contactmethod', $def_contact_method);
				set_user_setting($user_id, 'visibleonline', $def_canview);
				AddToLog("Added user ->{$post_user}<- in postgedview.php");
			}
		}
		
		// is the user there and verified ?
		if ($user_id=get_user_id($post_user) && get_user_setting($user_id, 'verified_by_admin')=="yes") {
			set_user_setting($user_id, 'loggedin', 'Y');
			set_user_setting($user_id, 'sessiontime', time());
			AddToLog("Login Successful ->{$post_user}<-");
				
			if (isset($_GET["id"])){
				$def_urlcmd = "individual.php?pid=". $_GET["id"]."&ged=". $_GET["ged"]."&";
			} else {
				$def_urlcmd = "index.php";
			}
			$_SESSION['pgv_user'] = $user_id;
			$url = $def_urlcmd;
			$url.="?".session_name()."=".session_id();
			if ($def_urlcmd == 'index.php'){
				$url.="&command=user";
			}
		} else {
			$url = "index.php?logout=1";
		}

	header("Location: $url");
	exit;
?>