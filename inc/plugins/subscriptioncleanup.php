<?php
/**
 * Subscription cleanup task
 * Copyright 2010 Starpaul20
 */

// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

// Tell MyBB when to run the hooks

// The information that shows up on the plugin manager
function subscriptioncleanup_info()
{
	return array(
		"name"				=> "Subscription Cleanup task",
		"description"		=> "Adds a task to your board to delete old thread and forum subscriptions from inactive threads/forums.",
		"website"			=> "http://galaxiesrealm.com/index.php",
		"author"			=> "Starpaul20",
		"authorsite"		=> "http://galaxiesrealm.com/index.php",
		"version"			=> "2.0.1",
		"guid"				=> "c0f6a5b3059d6abbe8788ce8985a7e8c",
		"compatibility"		=> "16*"
	);
}

// This function runs when the plugin is activated.
function subscriptioncleanup_activate()
{
	global $db;
	$query = $db->simple_select("settinggroups", "gid", "name='member'");
	$gid = intval($db->fetch_field($query, "gid"));

	$insertarray = array(
		'name' => 'threadsubscriptioncut',
		'title' => 'Delete old Thread Subscriptions',
		'description' => 'The number of days that a thread must be inactive before its thread subscriptions are deleted. Set to 0 to disable.',
		'optionscode' => 'text',
		'value' => 120,
		'disporder' => 38,
		'gid' => $gid
	);
	$db->insert_query("settings", $insertarray);

	$insertarray = array(
		'name' => 'forumsubscriptioncut',
		'title' => 'Delete old Forum Subscriptions',
		'description' => 'The number of days that a forum must be inactive before its forum subscriptions are deleted. Set to 0 to disable.',
		'optionscode' => 'text',
		'value' => 240,
		'disporder' => 39,
		'gid' => $gid
	);
	$db->insert_query("settings", $insertarray);

	rebuild_settings();

	require_once MYBB_ROOT."inc/functions_task.php";
	$subscription_insert = array(
		"title"			=> "Subscription Cleanup",
		"description"	=> "Removes thread and forum subscriptions from inactive threads/forums.",
		"file"			=> "subscriptioncleanup",
		"minute"		=> "0",
		"hour"			=> "0",
		"day"			=> "*",
		"month"			=> "*",
		"weekday"		=> "*",
		"enabled"		=> 1,
		"logging"		=> 1,
		"locked"		=> 0
	);

	$subscription_insert['nextrun'] = fetch_next_run($subscription_insert);
	$db->insert_query("tasks", $subscription_insert);
}

// This function runs when the plugin is deactivated.
function subscriptioncleanup_deactivate()
{
	global $db;
	$db->delete_query("settings", "name IN('threadsubscriptioncut','forumsubscriptioncut')");
	$db->delete_query("tasks", "file='subscriptioncleanup'");
	rebuild_settings();
}
?>