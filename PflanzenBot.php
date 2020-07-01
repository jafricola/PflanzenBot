<?php
include(__DIR__ . '/vendor/autoload.php');
require_once "Stopwatch.php";
require_once "credentials.php";
require_once "Antworten.php";

// establish DB connection
$mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName);

if (!empty($mysqli->connect_errno)) {
	throw new Exception($mysqli->connect_error, $mysqli->connect_errno);
}

$botAge = round(((date('U') - 1593306539) / 3600), 1);

$bot = new Telegram($bot_token);

$stopwatch = new Stopwatch($mysqli, $bot->ChatID());

if ($stopwatch->isNotSubscribed()) {
	$stopwatch->start();
}

if ($stopwatch->getState() == "main") {
	switch ($bot->Text()) {
		case "/start":

			$content = [
				'chat_id' => $bot->ChatID(),
				'text'    => 'Herzlich willkommen! Ich bin der PflanzenBot und helfe euch dabei, dass eure Pflanzen nicht verdursten. 🤖',
			];
			$bot->sendMessage($content);
			sleep(0.5);

			$content = ['chat_id' => $bot->ChatID(), 'text' => 'Es gibt folgende Befehle:'];
			$bot->sendMessage($content);
			sleep(0.5);

			$content = [
				'chat_id' => $bot->ChatID(),
				'text'    => '
	Mit /haushalt könnt ihr einen geheimen Zugangscode zu eurem Haushalt eingeben, um euch das Gießen zu teilen.
Macht diesen Code nicht zu leicht, sonst könnten andere ihn erraten und Teil eures Haushaltes werden.',
			];
			$bot->sendMessage($content);
			sleep(0.5);

			$content = [
				'chat_id' => $bot->ChatID(),
				'text'    => '
	Mit /intervall stellt ihr ein, nach wie vielen Tagen ohne Bewässerung ich euch an eure grünen Mitbewohner erinnern soll.
Solltet ihr Teil eines Haushaltes sein, wird das Intervall für den gesamten Haushalt eingestellt.',
			];
			$bot->sendMessage($content);
			sleep(0.5);

			$content = [
				'chat_id' => $bot->ChatID(),
				'text'    => '
	Mit /gegossen sagt ihr mir, dass ihr vorbildliche Pflanzenfreunde seit, die sich soeben adäquat um Ihre Pflänzchen gekümmert haben.',
			];
			$bot->sendMessage($content);
			sleep(0.5);

			$content = [
				'chat_id' => $bot->ChatID(),
				'text'    => '
	Mit /zuletzt erfahrt ihr, wann zuletzt gegossen worden ist.',
			];
			$bot->sendMessage($content);
			sleep(0.5);

			$content = [
				'chat_id' => $bot->ChatID(),
				'text'    => '
	Mit /fakten lernt ihr etwas neues über die Pflanzenwelt!',
			];
			$bot->sendMessage($content);
			sleep(0.5);

			$content = [
				'chat_id' => $bot->ChatID(),
				'text'    => '
	Mit /abmelden beendet ihr meinen Service. Danach seid ihr auf euch gestellt!',
			];
			$bot->sendMessage($content);
			sleep(0.5);

			$content = [
				'chat_id' => $bot->ChatID(),
				'text'    => '
	Mit /start ruft ihr erneut diese Anleitung auf.',
			];
			$bot->sendMessage($content);
			sleep(0.5);

			$content = [
				'chat_id' => $bot->ChatID(),
				'text'    => '
	Hinweis: 
Solltet ihr kein eigenes Intervall einstellen, werde ich euch standardmäßig alle 5 Tage ans Gießen erinnern.',
			];
			$bot->sendMessage($content);
			sleep(0.5);

			$content = [
				'chat_id' => $bot->ChatID(),
				'text'    => '
	Ganz einfach, oder? Wenn ihr Fragen habt, wendet euch an meinen Erschaffer, Jago.',
			];
			$bot->sendMessage($content);
			sleep(0.5);

			$content = [
				'chat_id' => $bot->ChatID(),
				'text'    => '
	Email: pflanzenbot@jagofriedrichs.de',
			];
			$bot->sendMessage($content);
			break;

		case "/gegossen":
			$stopwatch->watered();

			$content = [
				'chat_id' => $bot->ChatID(),
				'text'    =>

					randomReplyFromArray($wateringPraises) . " 🤖
Ich sage dir bescheid, wenn das nächste Mal gegossen werden muss.",
			];
			$bot->sendMessage($content);
			break;

		case "/zuletzt":
			$content = [
				'chat_id' => $bot->ChatID(),
				'text'    =>
					"Zuletzt gegossen am "
					. date("d.m.", $stopwatch->lastWatered()) . " um " . date("H:i", $stopwatch->lastWatered()) . " Uhr",
			];
			$bot->sendMessage($content);
			break;

		case "/intervall":
			$status = $stopwatch->setState("setting_interval");

			$content = [
				'chat_id' => $bot->ChatID(),
				'text'    =>

					"Wie oft soll ich ans Gießen erinnern? (Angabe in Tagen)",
			];
			$bot->sendMessage($content);
			break;

		case "/abmelden":
			$stopwatch->stop();

			$content = [
				'chat_id' => $bot->ChatID(),
				'text'    =>

					"Abmeldung erfolgreich, du erhältst jetzt keine Benachrichtigungen mehr.",
			];
			$bot->sendMessage($content);
			sleep(1);
			$content = [
				'chat_id' => $bot->ChatID(),
				'text'    =>

					"...",
			];
			$bot->sendMessage($content);
			sleep(2);
			$content = [
				'chat_id' => $bot->ChatID(),
				'text'    =>

					":(",
			];
			$bot->sendMessage($content);
			break;

		case "/haushalt":
			$status = $stopwatch->setState("setting_household");

			$content = [
				'chat_id' => $bot->ChatID(),
				'text'    =>

					"Bitte gib den geheimen Zugangscode zu eurem Haushalt ein. Falls er noch nicht existiert, wird er neu gesetzt.
Mach diesen Code nicht zu leicht, sonst könnten andere ihn erraten und Teil deines Haushaltes werden.",
			];
			$bot->sendMessage($content);
			break;

		case "/showhousehold":
			$household = $stopwatch->getHousehold();
			if ($household != "") {
				$content = [
					'chat_id' => $bot->ChatID(),
					'text'    =>

						"Dein Haushalt lautet: " . $household,
				];
			} else {
				$content = [
					'chat_id' => $bot->ChatID(),
					'text'    =>

						"Du bist nicht Teil eines Haushaltes.",
				];
			}
			$bot->sendMessage($content);
			break;

		case "/fakten":
			$content = [
				'chat_id' => $bot->ChatID(),
				'text'    =>
					"" . randomReplyFromArray($plantFacts),
			];
			$bot->sendMessage($content);
			break;

		default:
			$content = [
				'chat_id' => $bot->ChatID(),
				'text'    =>
					"Ich verstehe deine Anfrage leider (noch) nicht, sei bitte nicht böse. :( 
Ich bin immerhin erst " . $botAge . " Stunden alt...",
			];
			$bot->sendMessage($content);
	}
} else {
	if ($stopwatch->getState() == "setting_interval") {

		$stopwatch->setWateringInterval($bot->Text());
		$stopwatch->setState("main");

		$content = [
			'chat_id' => $bot->ChatID(),
			'text'    =>
				"Erledigt! Ich werde dich alle " . $stopwatch->getWateringInterval() . " Tage ans Gießen erinnern. :)",
		];
		$bot->sendMessage($content);
	} else {
		if ($stopwatch->getState() == "setting_household") {
			$stopwatch->setHousehold($bot->Text());
			$stopwatch->setState("main");

			$content = [
				'chat_id' => $bot->ChatID(),
				'text'    =>
					"Bleep Blerp Blop! 
Dein Haushalt wurde gesetzt. 
Teile den geheimen Code, damit du dir das Gießen mit mehreren Leuten teilen kannst! :)",
			];
			$bot->sendMessage($content);
		}
	}
}













