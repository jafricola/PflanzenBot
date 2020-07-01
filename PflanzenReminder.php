<?php
include(__DIR__ . '/vendor/autoload.php');
require_once "Stopwatch.php";
require_once "credentials.php";
date_default_timezone_set("Europe/Berlin");

$mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
if (!empty($mysqli->connect_errno)) {
	throw new Exception($mysqli->connect_error, $mysqli->connect_errno);
}

$bot = new Telegram($bot_token);

/**
 * @param $mysqli
 * @param $bot
 * @param $chat_id
 *
 * Run this script with a cron job as often as you want to remind people of watering their plants.
 *
 */

function sendReminder ($mysqli, $bot, $chat_id) {
	$stopwatch = new Stopwatch($mysqli, $chat_id);

	if ($stopwatch->needsWatering()) {
		$content = [
			'chat_id' => $chat_id,
			'text'    =>
				"Zeit Blumen zu gießen! Zuletzt gegossen am " . date("d.m.", $stopwatch->lastWatered()) . " um " . date("H:i", $stopwatch->lastWatered()) . " Uhr!",
		];
		$bot->sendMessage($content);
	} else {
		exit();
	}
}

//function testReminder ($mysqli, $bot, $chat_id) {
//	$stopwatch = new Stopwatch($mysqli, $chat_id);
//
//	$content = [
//		'chat_id' => $chat_id,
//		'text'    =>
//			"Zeit Blumen zu gießen! Zuletzt gegossen am " . date("d.m.", $stopwatch->lastWatered()) . " um " . date("H:i", $stopwatch->lastWatered()) . " Uhr!",
//	];
//	$bot->sendMessage($content);
//}

$stopwatch = new Stopwatch($mysqli, $chat_id);
$chatIDs = $stopwatch->getAllUsers();

foreach ($chatIDs as $id) {
	sendReminder($mysqli, $bot, $id);
}






