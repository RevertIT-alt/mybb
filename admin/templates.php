<?php
/**
 * MyBB 1.0
 * Copyright � 2005 MyBulletinBoard Group, All Rights Reserved
 *
 * Website: http://www.mybboard.com
 * License: http://www.mybboard.com/eula.html
 *
 * $Id$
 */

require "./global.php";

// Just a little fix here
$db->query("DELETE FROM ".TABLE_PREFIX."templates WHERE title=''");

// Load language packs for this section
global $lang;
$lang->load("templates");

addacpnav($lang->nav_templates, "templates.php");
switch($action)
{
	case "add":
		addacpnav($lang->nav_add_template);
		break;
	case "edit":
		addacpnav($lang->nav_edit_template);
		break;
	case "delete":
		addacpnav($lang->nav_delete_template);
		break;
	case "addset":
		addacpnav($lang->nav_add_set);
		break;
	case "editset":
		addacpnav($lang->nav_edit_set);
		break;
	case "deleteset":
		addacpnav($lang->nav_delete_set);
		break;
	default:
		if($expand)
		{
			if($expand == "-1")
			{
				addacpnav($lang->global_templates);
			}
			else
			{
				$query = $db->query("SELECT * FROM ".TABLE_PREFIX."templatesets WHERE sid='$expand'");
				$set = $db->fetch_array($query);
				addacpnav($set['title']);
			}
		}
		break;
}

checkadminpermissions("canedittemps");
logadmin();

$templategroups['calendar'] = $lang->group_calendar;
$templategroups['editpost'] = $lang->group_editpost;
$templategroups['email'] = $lang->group_email;
$templategroups['emailsubject'] = $lang->group_emailsubject;
$templategroups['forumbit'] = $lang->group_forumbit;
$templategroups['forumjump'] = $lang->group_forumjump;
$templategroups['forumdisplay'] = $lang->group_forumdisplay;
$templategroups['index'] = $lang->group_index;
$templategroups['error'] = $lang->group_error;
$templategroups['memberlist'] = $lang->group_memberlist;
$templategroups['multipage'] = $lang->group_multipage;
$templategroups['private'] = $lang->group_private;
$templategroups['portal'] = $lang->group_portal;
$templategroups['postbit'] = $lang->group_postbit;
$templategroups['redirect'] = $lang->group_redirect;
$templategroups['showthread'] = $lang->group_showthread;
$templategroups['usercp'] = $lang->group_usercp;
$templategroups['online'] = $lang->group_online;
$templategroups['moderation'] = $lang->group_moderation;
$templategroups['nav'] = $lang->group_nav;
$templategroups['search'] = $lang->group_search;
$templategroups['showteam'] = $lang->group_showteam;
$templategroups['reputation'] = $lang->group_reputation;
$templategroups['newthread'] = $lang->group_newthread;
$templategroups['newreply'] = $lang->group_newreply;
$templategroups['member'] = $lang->group_member;

if($action == "do_add") {
	$template = addslashes($template);
	$query = $db->query("SELECT * FROM ".TABLE_PREFIX."templates WHERE sid='$setid' AND title='$title'");
	$temp = $db->fetch_array($query);
	if($temp[tid]) {
		cperror($lang->name_exists);
	}
	$db->query("INSERT INTO ".TABLE_PREFIX."templates VALUES (NULL,'$title','$template', '$setid')");
	cpredirect("templates.php?expand=$setid", $lang->template_added);
}
if($action == "do_addset") {
	$db->query("INSERT INTO ".TABLE_PREFIX."templatesets VALUES (NULL, '$title')");
	$setid = $db->insert_id();
	cpredirect("templates.php?expand=$setid", $lang->set_added);
}
	
if($action == "do_delete") {
	if($deletesubmit) {	
		$db->query("DELETE FROM ".TABLE_PREFIX."templates WHERE tid='$tid'");
		cpredirect("templates.php?expand=$template[sid]", $lang->template_deleted);
	} else {
		$action = "modify";
		$expand = $template[sid];
	}
}
if($action == "do_deleteset") {
	if($deletesubmit) {	
		$db->query("DELETE FROM ".TABLE_PREFIX."templatesets WHERE sid='$setid'");
		$db->query("DELETE FROM ".TABLE_PREFIX."templates WHERE sid='$setid'");
		cpredirect("templates.php", $lang->set_deleted);
	}
}
if($action == "do_editset") {
	$db->query("UPDATE ".TABLE_PREFIX."templatesets SET title='$title' WHERE sid='$setid'");
	cpredirect("templates.php", $lang->set_edited);
}

if($action == "do_edit") {
	$query = $db->query("SELECT * FROM ".TABLE_PREFIX."templates WHERE tid='$tid'");
	$templateinfo = $db->fetch_array($query);

	if($title == "") {
		$title = $templateinfo[title];
	}
	$template = addslashes($template);
	$db->query("UPDATE ".TABLE_PREFIX."templates SET template='$template', title='$title', sid='$setid' WHERE tid='$tid'");
	cpredirect("templates.php?expand=$setid", $lang->template_edited);
}
if($action == "do_replace") {
	$noheader = 1;
	if(!$find) {
		cpmessage("You did not enter a search string.");
	} else {
		cpheader();
		starttable();
		tableheader("Template Search Results");
		tablesubheader("Searching For: $find in Custom Templates");
		echo "<tr>\n";
		echo "<td class=\"altbg1\">\n";
		$query = $db->query("SELECT * FROM ".TABLE_PREFIX."templates WHERE sid>'1'");
		while($template = $db->fetch_array($query)) {
			$newtemplate = str_replace($find, $replace, $template[template]);
			if($newtemplate != $template[template]) {
				if($replace != "") {
					$newtemplate = addslashes($newtemplate);
					$db->query("UPDATE ".TABLE_PREFIX."templates SET template='$newtemplate' WHERE tid='$template[tid]'");
					echo "Updated $template[title]".
						makelinkcode("edit", "templates.php?action=edit&tid=$template[tid]").
						"<br>";
				} else {
					echo "Found in $template[title]".
						makelinkcode("edit", "templates.php?action=edit&tid=$template[tid]").
						"<br>";
				}
			}
		}
		echo "</td>\n</tr>";
		endtable();
		cpfooter();
	}
}
if($action == "edit") {
	$query = $db->query("SELECT * FROM ".TABLE_PREFIX."templates WHERE tid='$tid'");
	$template = $db->fetch_array($query);
	$template[template] = stripslashes($template[template]);
	$template[template] = stripslashes($template[template]);

	cpheader();
	if($template[sid] != "-2") {
		startform("templates.php", "" , "do_edit");
		makehiddencode("tid", $tid);
		starttable();
		tableheader($lang->modify_template);
		makeinputcode($lang->title, "title", $template[title]);
	} elseif(md5($debugmode) == "0100e895f975e14f4193538dac4d0dc7" && $template[sid] == -2) {
		startform("templates.php", "" , "do_edit");
		makehiddencode("tid", $tid);
		starttable();
		tableheader($lang->modify_master_template);
		makeinputcode($lang->title, "title", $template[title]);
	} else {
		starttable();
		tableheader($lang->view_template);
		makelabelcode($lang->title, $template[title]);
	}
	maketextareacode($lang->template, "template", "$template[template]", "25", "80");
	if($template[sid] != "-2") {
		makeselectcode($lang->template_set, "setid", "templatesets", "sid", "title", "$template[sid]", "Global - All Template Sets");
	} else {
		makehiddencode("setid", $template[sid]);
	}
	endtable();
	if(($template[sid] != -2) || (md5($debugmode) == "0100e895f975e14f4193538dac4d0dc7" && $template[sid] == -2)) {
		endform($lang->update_template, $lang->reset_button);
	}
	cpfooter();
}
if($action == "editset") {
	$query = $db->query("SELECT * FROM ".TABLE_PREFIX."templatesets WHERE sid='$setid'");
	$set = $db->fetch_array($query);
	cpheader();
	startform("templates.php", "" , "do_editset");
	makehiddencode("setid", $setid);
	starttable();
	tableheader($lang->modify_set);
	makeinputcode($lang->title, "title", $set[title]);
	endtable();
	endform($lang->update_set, $lang->reset_button);
	cpfooter();
}

if($action == "delete" || $action == "revert") {
	$query = $db->query("SELECT * FROM ".TABLE_PREFIX."templates WHERE tid='$tid'");
	$template = $db->fetch_array($query);

	cpheader();
	startform("templates.php", "", "do_delete");
	makehiddencode("tid", $tid);
	starttable();
	tableheader($lang->delete_template, "", 1);
	$yes = makebuttoncode("deletesubmit", "Yes");
	$no = makebuttoncode("no", "No");
	if($action == "revert")
	{
		tableheader($lang->revert_template, "", 1);
		makelabelcode("<center>$lang->revert_template_notice<br><br>$yes$no</center>", "");
	}
	else
	{
		tableheader($lang->delete_template, "", 1);
		makelabelcode("<center>$lang->delete_template_notice<br><br>$yes$no</center>", "");
	}
	endtable();
	endform();
	cpfooter();
}

if($action == "deleteset") {
	$query = $db->query("SELECT * FROM ".TABLE_PREFIX."templatesets WHERE sid='$setid'");
	$templateset = $db->fetch_array($query);
	cpheader();
	startform("templates.php", "", "do_deleteset");
	makehiddencode("setid", $setid);
	starttable();
	tableheader($lang->delete_template_set, "", 1);
	$yes = makebuttoncode("deletesubmit", "Yes");
	$no = makebuttoncode("no", "No");
	makelabelcode("<center>$lang->delete_set_notice $templateset[title]?<br><br>$yes$no</center>", "");
	endtable();
	endform();
	cpfooter();
}
if($action == "makeoriginals") {
	$query = $db->query("SELECT t1.*, t2.title AS origtitle FROM ".TABLE_PREFIX."templates t1 LEFT JOIN ".TABLE_PREFIX."templates t2 ON (t1.title=t2.title AND t2.sid='-2') WHERE t1.sid='$setid'");
	$query2 = $db->query("SELECT t1.* FROM ".TABLE_PREFIX."templates t1 LEFT JOIN ".TABLE_PREFIX."templates t2 ON (t1.title=t2.title AND t2.sid='-2') WHERE t1.sid='$set[sid]' AND ISNULL(t2.template) ORDER BY t1.title ASC");

	$query = $db->query("SELECT * FROM templates WHERE sid='$setid'");
	while($template = $db->fetch_array($query)) {
		$template[template] = addslashes($template[template]);
		if($template[origtitle]) {
			$db->query("UPDATE ".TABLE_PREFIX."templates SET template='$template[template]' WHERE title='$template[title]' AND sid='-2'");
		} else {
			$db->query("INSERT INTO ".TABLE_PREFIX."templates (tid,sid,title,template) VALUES (NULL,'-2','$template[title]','$template[template]')");
		}
	}
	$db->query("DELETE FROM ".TABLE_PREFIX."templates WHERE sid='$setid'");
	cpredirect("templates.php?expand=$setid", $lang->originals_made);
}

if($action == "add") {
	if($title) {
		$query = $db->query("SELECT * FROM ".TABLE_PREFIX."templates WHERE title='$title' AND sid='-2'");
		$template = $db->fetch_array($query);
		$template[template] = stripslashes($template[template]);
		$template[template] = stripslashes($template[template]);
	}
	cpheader();
	startform("templates.php", "" , "do_add");
	starttable();
	if(md5($debugmode) == "0100e895f975e14f4193538dac4d0dc7") {
		tableheader($lang->add_master_template);
	} else {
		tableheader($lang->add_template);
	}
	makeinputcode($lang->title, "title", "$template[title]");
	maketextareacode($lang->template, "template", "$template[template]", "25", "80");
	if(md5($debugmode) == "0100e895f975e14f4193538dac4d0dc7") {
		makehiddencode("setid", -2);
	} else {
		makeselectcode($lang->template_set, "setid", "templatesets", "sid", "title", $sid, $lang->global_sel);
	}
	endtable();
	endform($lang->add_template, $lang->reset_button);
	cpfooter();
}
if($action == "addset") {
	cpheader();
	startform("templates.php", "" , "do_addset");
	starttable();
	tableheader($lang->add_set);
	makeinputcode($lang->title, "title", "");
	endtable();
	endform($lang->add_set, $lang->reset_button);
	cpfooter();
}
if($action == "search") {
	if(!$noheader) {
		cpheader();
	}
	startform("templates.php", "", "do_replace");
	starttable();
	tableheader($lang->search_replace);
	makeinputcode($lang->search_for, "find");
	makeinputcode($lang->replace_with, "replace");
	endtable();
	endform($lang->find_replace, $lang->reset_button);
	cpfooter();
}
if($action == "modify" || $action == "") {

	if(!$noheader) {
		cpheader();
	}
	// Fetch the listing of themes so we can see which template sets are associated to themes
	$query = $db->query("SELECT name,tid,themebits FROM ".TABLE_PREFIX."themes WHERE tid!='1'");
	while($theme = $db->fetch_array($query))
	{
		$tbits = unserialize($theme['themebits']);
		$themes[$tbits['templateset']][$theme['tid']] = $theme;
	}

	if(!$expand) // Build a listing of all of the template sets
	{
		if(md5($debugmode) == "0100e895f975e14f4193538dac4d0dc7")
		{
			$templatesets[-20]['title'] = $lang->master_templates;
			$templatesets[-20]['sid'] = -2;
		}
		$templatesets[-10]['title'] = $lang->global_templates;
		$templatesets[-10]['sid'] = -1;

		$query = $db->query("SELECT* FROM ".TABLE_PREFIX."templatesets ORDER BY title ASC");
		while($templateset = $db->fetch_array($query))
		{
			$templatesets[$templateset['sid']] = $templateset;
		}
	
		starttable();
		tableheader($lang->template_management, "", 1);
		foreach($templatesets as $templateset)
		{
			echo "<tr>\n";
			echo "<td class=\"subheader\">";
			echo "<div style=\"float: right;\">";
			echo "<input type=\"button\" value=\"$lang->add_template\" onclick=\"hopto('templates.php?action=add&sid=".$templateset['sid']."');\" class=\"submitbutton\">";
			if($templateset['sid'] != "-2" && $templateset['sid'] != "-1")
			{
				echo "<input type=\"button\" value=\"$lang->edit_set\" onclick=\"hopto('templates.php?action=editset&setid=".$templateset['sid']."');\" class=\"submitbutton\">";
				if(!$themes[$templateset['sid']])
				{
					echo "<input type=\"button\" value=\"$lang->delete_set\" onclick=\"hopto('templates.php?action=deleteset&setid=".$templateset['sid']."');\" class=\"submitbutton\">";
				}
			}
			echo "<input type=\"button\" value=\"$lang->expand\" onclick=\"hopto('templates.php?expand=".$templateset['sid']."');\" class=\"submitbutton\">";
			echo "</div><div>".$templateset['title']."</div></td>\n";
			echo "</tr>\n";
			if($themes[$templateset['sid']])
			{
				$note = $lang->template_set_associated_themes;
				$note .= "<ul>";
				foreach($themes[$templateset['sid']] as $theme)
				{
					$note .= "<li>".$theme['name']."</li>";
				}
				$note .= "</ul>";
				$note .= $lang->template_set_associated_themes2;
			}
			elseif($templateset['sid'] == -2)
			{
				$note = $lang->template_set_master_templates;
			}
			elseif($templateset['sid'] == -1)
			{
				$note = $lang->template_set_global_templates;
			}
			else
			{
				$note = $lang->template_set_no_associated_themes;
			}
			makelabelcode($note);
		}
		endtable();
	}
	else // We're showing a specific template set
	{
		if($expand == -2)
		{
			$templateset['title'] = $lang->master_templates;
			$templateset['sid'] = -2;
		}
		elseif($expand == -1)
		{
			$templateset['title'] = $lang->global_templates;
			$templateset['sid'] = -1;
		}
		else
		{
			$query = $db->query("SELECT * FROM ".TABLE_PREFIX."templatesets WHERE sid='$expand'");
			$templateset = $db->fetch_array($query);
			starttable();
			makelabelcode("$lang->template_color1_note<br /><span class=\"highlight3\">$lang->template_color2_note</span><br /><span class=\"highlight2\">$lang->template_color3_note</span>");
			endtable();
		}

		starttable();
		tableheader($lang->template_management." (".$templateset['title'].")", "", 3);
		echo "<tr>\n";
		echo "<td class=\"subheader\" colspan=\"3\">";
		echo "<div style=\"float: right;\">";
		echo "<input type=\"button\" value=\"$lang->add_template\" onclick=\"hopto('templates.php?action=add&sid=".$templateset['sid']."');\" class=\"submitbutton\">";
		if($templateset['sid'] != "-2" && $templateset['sid'] != "-1")
		{
			echo "<input type=\"button\" value=\"$lang->edit_set\" onclick=\"hopto('templates.php?action=editset&setid=".$templateset['sid']."');\" class=\"submitbutton\">";
			if(!$themes[$expand])
			{
				echo "<input type=\"button\" value=\"$lang->delete_set\" onclick=\"hopto('templates.php?action=deleteset&setid=".$templateset['sid']."');\" class=\"submitbutton\">";
			}
		}
		echo "<input type=\"button\" value=\"$lang->collapse\" onclick=\"hopto('templates.php?');\" class=\"submitbutton\">";
		echo "</div><div>".$templateset['title']."</div></td>\n";
		echo "</tr>\n";
		if($expand == -2 && md5($debugmode) == "0100e895f975e14f4193538dac4d0dc7")
		{
			// Master templates
			$query = $db->query("SELECT tid,title FROM ".TABLE_PREFIX."templates WHERE sid='-2' ORDER BY title ASC");
			while($template = $db->fetch_array($query))
			{
				$altbg = getaltbg();
				echo "<td class=\"$altbg\" width=\"10\">&nbsp;</td>\n";
				echo "<td class=\"$altbg\"><a href=\"templates.php?action=edit&tid=".$template['tid']."\">".$template['title']."</a></td>";
				echo "<td class=\"$altbg\" align=\"right\">";
				echo "<input type=\"button\" value=\"$lang->edit\" onclick=\"hopto('templates.php?action=edit&tid=".$template['tid']."');\" class=\"submitbutton\">";
				echo "<input type=\"button\" value=\"$lang->delete\" onclick=\"hopto('templates.php?action=delete&tid=".$template['tid']."');\" class=\"submitbutton\">";
				echo "</tr>";
			}
		}
		elseif($expand == -1)
		{
			// Global Templates
			$query = $db->query("SELECT tid,title FROM ".TABLE_PREFIX."templates WHERE sid='-1' ORDER BY title ASC");
			while($template = $db->fetch_array($query))
			{
				$altbg = getaltbg();
				echo "<td class=\"$altbg\" width=\"10\">&nbsp;</td>\n";
				echo "<td class=\"$altbg\"><a href=\"templates.php?action=edit&tid=".$template['tid']."\">".$template['title']."</a></td>";
				echo "<td class=\"$altbg\" align=\"right\">";
				echo "<input type=\"button\" value=\"$lang->edit\" onclick=\"hopto('templates.php?action=edit&tid=".$template['tid']."');\" class=\"submitbutton\">";
				echo "<input type=\"button\" value=\"$lang->delete\" onclick=\"hopto('templates.php?action=delete&tid=".$template['tid']."');\" class=\"submitbutton\">";
				echo "</tr>";
			}
		}
		else
		{
			// Query for custom templates
			$query2 = $db->query("SELECT t1.* FROM ".TABLE_PREFIX."templates t1 LEFT JOIN ".TABLE_PREFIX."templates t2 ON (t1.title=t2.title AND t2.sid='-2') WHERE t1.sid='$set[sid]' AND ISNULL(t2.template) ORDER BY t1.title ASC");
			while($template = $db->fetch_array($query2))
			{
				$template['customtemplate'] = 1;
				$templatelist[$template['title']] = $template;
			}

			// Query for original templates
			$query3 = $db->query("SELECT t1.title AS originaltitle, t1.tid AS originaltid, t2.tid FROM ".TABLE_PREFIX."templates t1 LEFT JOIN ".TABLE_PREFIX."templates t2 ON (t2.title=t1.title AND t2.sid='$set[sid]') WHERE t1.sid='-2' ORDER BY t1.title ASC");
			while($template = $db->fetch_array($query3)) {
				$templatelist[$template['originaltitle']] = $template;
			}
			reset($templatelist);
			ksort($templatelist);
			foreach($templatelist as $template)
			{
				if($template['customtemplate'])
				{
					$checkname = $template['title'];
				}
				else
				{
					$checkname = $template['originaltitle'];
				}
				$exploded = explode("_", $checkname, 2);
				reset($templategroups);
				$grouptype = "";
				if($templategroups[$exploded[0]])
				{
					$grouptype = $exploded[0];
					if(!$donegroup[$exploded[0]])
					{
						$groupname = $templategroups[$grouptype];
						$altbg = getaltbg();
						echo "<tr>\n";
						echo "<td class=\"$altbg\" colspan=\"2\"><b><a href=\"templates.php?expand=$expand&group=$grouptype#$grouptype\" name=\"$grouptype\">$groupname $lang->templates</a></b></td>\n";
						echo "<td class=\"$altbg\" align=\"right\">\n";
						echo "<input type=\"button\" value=\"$lang->expand\" onclick=\"hopto('templates.php?expand=$expand&group=$grouptype#$grouptype');\" class=\"submitbutton\">\n";
						echo "</tr>\n";
						$donegroup[$grouptype] = 1;
					}
						if($group != $grouptype && $group != "all")
						{
							continue;
						}

				}
				$altbg = getaltbg();
				if($grouptype)
				{
					echo "<tr>\n";
					echo "<td class=\"$altbg\" width=\"10\">&nbsp;</td>\n";
					echo "<td class=\"$altbg\">";
				}
				else
				{
					echo "<tr>\n";
					echo "<td class=\"$altbg\" colspan=\"2\">\n";
				}
				if(!$template['tid'])
				{
					echo "<a href=\"templates.php?action=add&title=".$template['originaltitle']."&sid=".$set['sid']."\">".$template['originaltitle']."</a></td>\n";
					echo "<td class=\"$altbg\" align=\"right\">";
					echo "<input type=\"button\" value=\"$lang->change_original\" onclick=\"hopto('templates.php?action=add&title=".$template['originaltitle']."&sid=".$set['sid']."');\" class=\"submitbutton\">";
					echo "</td>\n";
					echo "</tr>\n";
				}
				elseif($template['customtemplate'])
				{
						echo "<a href=\"templates.php?action=edit&tid=".$template['tid']."\"><span class=\"highlight2\">".$template['title']."</span></a></td>";
						echo "<td class=\"$altbg\" align=\"right\">";
						echo "<input type=\"button\" value=\"$lang->edit\" onclick=\"hopto('templates.php?action=edit&tid=".$template['tid']."');\" class=\"submitbutton\">";
						echo "<input type=\"button\" value=\"$lang->delete\" onclick=\"hopto('templates.php?action=delete&tid=".$template['tid']."');\" class=\"submitbutton\">";
						echo "</td>\n";
						echo "</tr>\n";
				}
				else
				{
					echo "<a href=\"templates.php?action=edit&tid=".$template['tid']."\"><span class=\"highlight3\">".$template['originaltitle']."</span></a></td>";
					echo "<td class=\"$altbg\" align=\"right\">";
					echo "<input type=\"button\" value=\"$lang->edit\" onclick=\"hopto('templates.php?action=edit&tid=".$template['tid']."');\" class=\"submitbutton\">";
					echo "<input type=\"button\" value=\"$lang->revert_original\" onclick=\"hopto('templates.php?action=revert&tid=".$template['tid']."');\" class=\"submitbutton\">";
					echo "</td>\n";
					echo "</tr>\n";
				}
				$grouptype = "";
			}
		}
		endtable();
	}

/*





		$query = $
	}
	else // Expand a specific template set
	{
	}

	starttable();
	tableheader($lang->template_management, "", 3);
	if(md5($debugmode) == "0100e895f975e14f4193538dac4d0dc7")
	{
		echo "<tr>\n";
		echo "<td class=\"subheader\" colspan=\"3\">";
		echo "<div style=\"float: right;\">";
		echo "<input type=\"button\" value=\"$lang->add_template\" onclick=\"hopto('templates.php?action=add&sid=-2');\" class=\"submitbutton\">";
		if($expand == -2)
		{
			echo "<input type=\"button\" value=\"$lang->collapse\" onclick=\"hopto('templates.php?action=modify');\" class=\"submitbutton\">";
		}
		else
		{
			echo "<input type=\"button\" value=\"$lang->expand\" onclick=\"hopto('templates.php?expand=-2');\" class=\"submitbutton\">";
		}
		echo "</div><div>".$lang->master_templates."</div></td>\n";
		echo "</tr>\n";
		if($expand == "-2")
		{
			$query = $db->query("SELECT tid,title FROM ".TABLE_PREFIX."templates WHERE sid='-2' ORDER BY title ASC");
			while($template = $db->fetch_array($query))
			{
				$altbg = getaltbg();
				echo "<td class=\"$altbg\" width=\"10\">&nbsp;</td>\n";
				echo "<td class=\"$altbg\"><a href=\"templates.php?action=edit&tid=".$template['tid']."\">".$template['title']."</a></td>";
				echo "<td class=\"$altbg\" align=\"right\">";
				echo "<input type=\"button\" value=\"$lang->edit\" onclick=\"hopto('templates.php?action=edit&tid=".$template['tid']."');\" class=\"submitbutton\">";
				echo "<input type=\"button\" value=\"$lang->delete\" onclick=\"hopto('templates.php?action=delete&tid=".$template['tid']."');\" class=\"submitbutton\">";
				echo "</tr>";
			}
		}
	}
	echo "<tr>\n";
	echo "<td class=\"subheader\" colspan=\"3\">";
	echo "<div style=\"float: right;\">";
	echo "<input type=\"button\" value=\"$lang->add_template\" onclick=\"hopto('templates.php?action=add&sid=-1');\" class=\"submitbutton\">";
	if($expand == -1)
	{
		echo "<input type=\"button\" value=\"$lang->collapse\" onclick=\"hopto('templates.php?action=modify');\" class=\"submitbutton\">";
	}
	else
	{
		echo "<input type=\"button\" value=\"$lang->expand\" onclick=\"hopto('templates.php?expand=-1');\" class=\"submitbutton\">";
	}
	echo "</div><div>".$lang->global_templates."</div></td>\n";
	echo "</tr>\n";
	if($expand == -1)
	{
		$query = $db->query("SELECT tid,title FROM ".TABLE_PREFIX."templates WHERE sid='-1' ORDER BY title ASC");
		while($template = $db->fetch_array($query))
		{
			$altbg = getaltbg();
			echo "<tr>\n";
			echo "<td class=\"$altbg\" width=\"10\">&nbsp;</td>\n";
			echo "<td class=\"$altbg\"><a href=\"templates.php?action=edit&tid=".$template['tid']."\">".$template['title']."</a></td>";
			echo "<td class=\"$altbg\" align=\"right\">";
			echo "<input type=\"button\" value=\"$lang->edit\" onclick=\"hopto('templates.php?action=edit&tid=".$template['tid']."');\" class=\"submitbutton\">";
			echo "<input type=\"button\" value=\"$lang->delete\" onclick=\"hopto('templates.php?action=delete&tid=".$template['tid']."');\" class=\"submitbutton\">";
			echo "</tr>";
		}
	}

	$query = $db->
	$query = $db->query("SELECT * FROM ".TABLE_PREFIX."templatesets ORDER BY title ASC");
	while($set = $db->fetch_array($query)) {
		echo "<tr>\n";
		echo "<td class=\"subheader\" colspan=\"3\">";
		echo "<div style=\"float: right;\">";
		echo "<input type=\"button\" value=\"$lang->add_template\" onclick=\"hopto('templates.php?action=add&sid=".$set['sid']."');\" class=\"submitbutton\">";
		echo "<input type=\"button\" value=\"$lang->edit_set\" onclick=\"hopto('templates.php?action=editset&setid=".$set['sid']."');\" class=\"submitbutton\">";
		echo "<input type=\"button\" value=\"$lang->delete_set\" onclick=\"hopto('templates.php?action=deleteset&setid=".$set['sid']."');\" class=\"submitbutton\">";
		if($expand == $set['sid'])
		{
			echo "<input type=\"button\" value=\"$lang->collapse\" onclick=\"hopto('templates.php?action=modify');\" class=\"submitbutton\">";
		}
		else
		{
			echo "<input type=\"button\" value=\"$lang->expand\" onclick=\"hopto('templates.php?expand=".$set['sid']."');\" class=\"submitbutton\">";
		}
		echo "</div><div><a href=\"templates.php?action=editset&setid=".$set['sid']."\">".$set['title']."</a></div></td>\n";
		echo "</tr>\n";
		if($expand == $set['sid'])
		{
			$query2 = $db->query("SELECT t1.* FROM ".TABLE_PREFIX."templates t1 LEFT JOIN ".TABLE_PREFIX."templates t2 ON (t1.title=t2.title AND t2.sid='-2') WHERE t1.sid='$set[sid]' AND ISNULL(t2.template) ORDER BY t1.title ASC");
			while($template = $db->fetch_array($query2))
			{
				if(!$donecustom)
				{
					$altbg = getaltbg();
					echo "<tr>\n";
					echo "<td class=\"$altbg\" colspan=\"2\"><b>$lang->custom_templates</b></td>";
					echo "<td class=\"$altbg\" align=\"right\">";
					echo "<input type=\"button\" value=\"$lang->expand\" onclick=\"hopto('templates.php?expand=$expand&group=custom');\" class=\"submitbutton\">\n";
					echo "</td>\n";
					echo "</tr>\n";
					$donecustom = 1;
				}
				if($group == "custom")
				{
					$altbg = getaltbg();
					echo "<tr>\n";
					echo "<td class=\"$altbg\" width=\"10\">&nbsp;</td>\n";
					echo "<td class=\"$altbg\"><a href=\"templates.php?action=edit&tid=".$template['tid']."\">".$template['title']."</a></td>";
					echo "<td class=\"$altbg\" align=\"right\">";
					echo "<input type=\"button\" value=\"$lang->edit\" onclick=\"hopto('templates.php?action=edit&tid=".$template['tid']."');\" class=\"submitbutton\">";
					echo "<input type=\"button\" value=\"$lang->delete\" onclick=\"hopto('templates.php?action=deletet&tid=".$template['tid']."');\" class=\"submitbutton\">";
					echo "</tr>";
				}
				else
				{
					break;
				}
			}
			$query3 = $db->query("SELECT t1.title AS originaltitle, t1.tid AS originaltid, t2.tid FROM ".TABLE_PREFIX."templates t1 LEFT JOIN ".TABLE_PREFIX."templates t2 ON (t2.title=t1.title AND t2.sid='$set[sid]') WHERE t1.sid='-2' ORDER BY t1.title ASC");
			while($template = $db->fetch_array($query3)) {
				$exploded = explode("_", $template['originaltitle'], 2);
				reset($templategroups);
				$grouptype = "";
				if($templategroups[$exploded[0]])
				{
					$grouptype = $exploded[0];
					if(!$donegroup[$exploded[0]])
					{
						$groupname = $templategroups[$grouptype];
						$altbg = getaltbg();
						echo "<tr>\n";
						echo "<td class=\"$altbg\" colspan=\"2\"><b><a href=\"templates.php?expand=$expand&group=$grouptype#$grouptype\" name=\"$grouptype\">$groupname $lang->templates</a></b></td>\n";
						echo "<td class=\"$altbg\" align=\"right\">\n";
						echo "<input type=\"button\" value=\"$lang->expand\" onclick=\"hopto('templates.php?expand=$expand&group=$grouptype#$grouptype');\" class=\"submitbutton\">\n";
						echo "</tr>\n";
						$donegroup[$grouptype] = 1;
					}
						if($group != $grouptype && $group != "all")
						{
							continue;
						}

				}
				$altbg = getaltbg();
				if($grouptype)
				{
					echo "<tr>\n";
					echo "<td class=\"$altbg\" width=\"10\">&nbsp;</td>\n";
					echo "<td class=\"$altbg\">";
				}
				else
				{
					echo "<tr>\n";
					echo "<td class=\"$altbg\" colspan=\"2\">\n";
				}
				if(!$template['tid'])
				{
					echo "<a href=\"templates.php?action=add&title=".$template['originaltitle']."&sid=".$set['sid']."\">".$template['originaltitle']."</a></td>\n";
					echo "<td class=\"$altbg\" align=\"right\">";
					echo "<input type=\"button\" value=\"$lang->change_original\" onclick=\"hopto('templates.php?action=add&title=".$template['originaltitle']."&sid=".$set['sid']."');\" class=\"submitbutton\">";
					echo "</td>\n";
					echo "</tr>\n";
				}
				else
				{
					echo "<a href=\"templates.php?action=edit&tid=".$template['tid']."\"><span class=\"highlight3\">".$template['originaltitle']."</span></a></td>";
					echo "<td class=\"$altbg\" align=\"right\">";
					echo "<input type=\"button\" value=\"$lang->edit\" onclick=\"hopto('templates.php?action=edit&tid=".$template['tid']."');\" class=\"submitbutton\">";
					echo "<input type=\"button\" value=\"$lang->revert_original\" onclick=\"hopto('templates.php?action=delete&tid=".$template['tid']."');\" class=\"submitbutton\">";
					echo "</td>\n";
					echo "</tr>\n";
				}
				$grouptype = "";
			}
		}
	}
	endtable();
	starttable();
	makelabelcode("$lang->template_color1_note<br /><span class=\"highlight3\">$lang->template_color2_note</span><br /><span class=\"highlight2\">$lang->template_color3_note</span>");
	endtable();
*/
	cpfooter();
}

?>