<?php

require_once('config.php');
require_once('WikiLoader.php');

if(!file_exists('cache')) {
	print "Fehler: Cache existiert nicht! 'make cache' ausgefuehrt?\n";
	exit(1);
}
$cache = unserialize(file_get_contents('cache'));

$content = $cache['static'];

$bericht = WikiLoader::parseSource($content, 'Bericht');
if(!isset($bericht['Titel1']) || !isset($bericht['Titel2'])) {
	print "Fehler: Titel1 / Titel2 nicht gesetzt!\n";
	exit(1);
}

$TITEL1 = $bericht['Titel1'];
$TITEL2 = $bericht['Titel2'];
