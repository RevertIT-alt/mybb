<?php
/**
 * MyBB 1.2
 * Copyright � 2006 MyBB Group, All Rights Reserved
 *
 * Website: http://www.mybboard.com
 * License: http://www.mybboard.com/eula.html
 *
 * $Id$
 */

// Lets pretend we're a level higher
define("IN_ADMINCP", 1);
$config = array();

if(!isset($config['admindir']))
{
	$config['admindir'] = "admin";
}

require "../inc/init.php";

define('MYBB_ADMIN_DIR', MYBB_ROOT."admin/");

require MYBB_ADMIN_DIR."adminfunctions.php";
require MYBB_ROOT."inc/functions_user.php";

$style = "styles/".$mybb->settings['cpstyle']."/stylesheet.css";
if(!file_exists(MYBB_ADMIN_DIR.$style))
{
	$style = "./styles/Axiom/stylesheet.css";
}
$lang->setPath(MYBB_ROOT."inc/languages");
$lang->setLanguage($settings['cplanguage'], "admin");

// Load global language phrases
$lang->load("global");

// Remove slashes from bbname
$mybb->settings['bbname'] = stripslashes($mybb->settings['bbname']);

$time = time();

if(is_dir(MYBB_ROOT."install") && !file_exists(MYBB_ROOT."install/lock"))
{
	$mybb->trigger_generic_error("install_directory");
}

if($mybb->input['action'] == "logout")
{
	$expires = $time-60*60*24;
	@setcookie("mybbadmin", "", $expires);
	$lang->invalid_admin = $lang->logged_out_admin;
}

$showlogin = 1;
$ipaddress = get_ip();

unset($user);
if($mybb->input['do'] == "login")
{
	$user = validate_password_from_username($mybb->input['username'], $mybb->input['password']);
	if($user['uid'])
	{
		$query = $db->simple_select(TABLE_PREFIX."users", "*", "uid='".$user['uid']."'");
		$user = $db->fetch_array($query);
	}
	$failcheck = 1;
}
elseif($mybb->input['action'] != "logout")
{
	$logon = explode("_", $_COOKIE['mybbadmin'], 2);
	$query = $db->simple_select(TABLE_PREFIX."users", "*", "uid='".$db->escape_string($logon[0])."'");
	$user = $db->fetch_array($query);
	if($user['loginkey'] != $logon[1])
	{
		unset($user);
	}
}
$mybbgroups = $user['usergroup'].",".$user['additionalgroups'];

if(!$user['usergroup'])
{
	$mybbgroups = 1;
}

$groupscache = $cache->read("usergroups");
$admingroup = usergroup_permissions($mybbgroups);

if($admingroup['cancp'] != "yes" || !$user['uid'])
{
	unset($user);
}

if($user['uid'])
{
	$expires = $time+60*60*24;
	setcookie("mybbadmin", $user['uid']."_".$user['loginkey'], $expires);
	$mybbadmin = $mybb->user = $user;
	$query = $db->simple_select(TABLE_PREFIX."usergroups", "*", "gid='$user[usergroup]'");
	$mybb->usergroup = $db->fetch_array($query);
	$query = $db->simple_select(TABLE_PREFIX."adminoptions", "*", "uid='$user[uid]'");
	$adminoptions = $db->fetch_array($query);
	if($adminoptions['cpstyle'] && file_exists(MYBB_ADMIN_DIR."styles/$adminoptions[cpstyle]/stylesheet.css"))
	{
		$style = "./styles/$adminoptions[cpstyle]/stylesheet.css";
	}
}
else
{
	if($failcheck)
	{
		$md5pw = md5($mybb->input['password']);
		$ipaddress = get_ip();
		$iphost = @gethostbyaddr($ipaddress);
		$lang->invalidlogin_message = sprintf($lang->invalidlogin_message, $mybb->settings['bbname'], $mybb->input['username'], $mybb->input['password'], $md5pw, $ipaddress, $iphost);
		$lang->invalidlogin_subject = sprintf($lang->invalidlogin_subject, $mybb->settings['bbname']);
		$lang->invalidlogin_headers = sprintf($lang->invalidlogin_headers, $mybb->settings['bbname'], $mybb->settings['adminemail']);
		mail($settings['adminemail'], $lang->invalidlogin_subject, $message, $lang->invalidlogin_headers);
	}

	if(!empty($mybb->input['goto']))
	{
		$goto = htmlspecialchars_uni($_GET['goto']);
	}
	else
	{
		$goto = '';
	}
	cpheader("", 0, "javascript:document.loginform.username.focus();");
	echo "<br />\n<br />\n<br />";
	echo "<form action=\"$_SERVER[PHP_SELF]\" method=\"post\" name=\"loginform\">\n";
	echo "<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" width=\"450\" align=\"center\">\n";
	echo "<tr><td class=\"bordercolor\">\n";
	echo "<table border=\"0\" cellspacing=\"1\" cellpadding=\"4\" width=\"100%\">\n";
	echo "<tr>\n";
	echo "<td id=\"logo\"><h1><span class=\"hidden\">MyBB</span></h1></td>\n";
	echo "</tr>\n";
	echo "<tr>\n";
	echo "<td class=\"header\" align=\"center\">".$lang->administration_login."</td>\n";
	echo "</tr>";
	echo "<tr>\n";
	echo "<td class=\"altbg1\" align=\"center\">".$lang->invalid_admin."</td>\n";
	echo "</tr>";
	echo "<tr>\n";
	echo "<td class=\"altbg2\">\n";
	echo "<table width=\"100%\">\n";
	echo "<tr>\n";
	echo "<td><b>".$lang->login_username."</b></td>\n";
	echo "<td><input type=\"text\" name=\"username\"></td>\n";
	echo "</tr>\n";
	echo "<tr>\n";
	echo "<td><b>".$lang->login_password."</b></td>\n";
	echo "<td><input type=\"password\" name=\"password\" /></td>\n";
	echo "</tr>\n";
	echo "</table>\n";
	echo "</td>";
	echo "</tr>\n";
	echo "<tr>\n";
	echo "<td class=\"altbg2\" align=\"center\"><input type=\"submit\" value=\"".$lang->login."\">&nbsp;&nbsp;&nbsp;<input type=\"reset\" value=\"".$lang->reset."\"></td>\n";
	echo "</td>\n";
	echo "</table>\n";
	echo "</td></tr></table>\n";
	echo "</td></tr></table>\n";
	echo "<input type=\"hidden\" name=\"do\" value=\"login\">\n";
	echo "<input type=\"hidden\" name=\"goto\" value=\"".$goto."\">\n";
	echo "</form>\n";
	echo "<p style=\"text-align: center\"><a href=\"../\">".$lang->back_to_forum."</a></p>\n";
	cpfooter(0);
	exit;
}
$navbits[0]['name'] = $mybb->settings['bbname']." ".$lang->control_panel;
$navbits[0]['url'] = "index.php?action=home";

?>