<?php

require_once('config.php');
require_once('korrekturen.php');

# Cache laden
if(!file_exists('cache')) {
	print "Fehler: Cache existiert nicht! 'make cache' ausgefuehrt?\n";
	exit(1);
}
$cache = unserialize(file_get_contents('cache'));

# Liste ignorierter Fragmente/Plagiatskategorien/Quellen anzeigen
foreach($cache['ignored']['fragments'] as $title => $reason) {
	print "%XXX: Ignoriere Fragment: $title: $reason\n";
}
foreach($cache['ignored']['sources'] as $title) {
	print "%XXX: Ignoriere Quelle: $title\n";
}

# Liste der Quellen erzeugen
$sources = array();
foreach($cache['sources'] as $source) {
	if(!isset($source['InLit']))
		$source['InLit'] = 'nein';
	if(!isset($source['InFN']))
		$source['InFN'] = 'nein';
	$sources['Kategorie:'.$source['title']] = $source;
}

$list = array();
$fragmentTypeUsed = array();
$i = 0;
foreach($cache['fragments'] as $f) {
	$currentSources = array_values(array_intersect($f['categories'], array_keys($sources)));
	$currentTypes = array_values(array_intersect($f['categories'], $categoryWhitelist));

	if(empty($currentTypes)) {
		print "%XXX: Ignoriere {$f['wikiTitle']}: Kategorie nicht in Whitelist\n";
		continue; // Silently ignore everything that does not match whitelist
	}

	$z = array_values(array_intersect($f['categories'], $categoryBlacklist));
	if(!empty($z)) {
		print "%Ignoriere {$f['wikiTitle']}: Kategorie in Blacklist: $z[0]\n";
		continue;
	}

	foreach($categoryRequired as $req) {
		if(!in_array($req, $f['categories'])) {
			print "%Ignoriere {$f['wikiTitle']}: Kategorie $req nicht gesetzt\n";
			continue 2; // Silently ignore everything that does not have everythign from $categoryRequired set
		}
	}

	if(empty($currentSources)) {
		print "%XXX: {$f['wikiTitle']}: Ignoriere, keine Quelle gefunden! (".implode(", ", $f['categories']).")\n";
	} else if(count($currentSources) >= 2) {
		print "%XXX: {$f['wikiTitle']}: Warnung, mehrere Quellen gefunden! (".implode(", ", $f['categories']).")\n";
	}

	if(empty($currentTypes) && empty($f[7])) {
		print "%XXX: {$f['wikiTitle']}: Ignoriere, keinen Plagiatstyp gefunden! (".implode(", ", $f['categories']).")\n";
	} else if(empty($currentTypes)) {
		print "%XXX: {$f['wikiTitle']}: Warnung, keinen Plagiatstyp gefunden! (".implode(", ", $f['categories']).")\n";
		$currentTypes[] = 'Kategorie:'.$f[7];
	} else if(count($currentTypes) >= 2) {
		print "%XXX: {$f['wikiTitle']}: Ignoriere, mehrere Plagiatstypen gefunden! (".implode(", ", $f['categories']).")\n";
	}

	if(empty($currentSources) || count($currentTypes) != 1)
		continue;

	$currentSourceTitle = $currentSources[0];
	$currentTypeTitle = $currentTypes[0];
	$currentTypeCleaned = preg_replace('/^Kategorie:/', '', $currentTypeTitle);
	$currentTypeCleaned = str_replace($typesFrom, $typesTo, $currentTypeCleaned);

	if($f['typus'] != $currentTypeCleaned) {
		print "%XXX: {$f['wikiTitle']}: Warnung, Diskrepanz zwischen Fragment und Kategorisierung! (".$f[7]." != ".$currentTypeCleaned.")\n";
	}
	$list[$i]['inFN'] = $sources[$currentSourceTitle]['InFN'];

	$list[$i]['quelle'] = titleToKey($currentSourceTitle);
	$list[$i]['seite'] = $f['seite'];
	$list[$i]['zeilen'] = $f['zeilen'];
	$list[$i]['plagiat'] = $f['plagiat'];
	$list[$i]['seitefund'] = $f['seitefund'];
	$list[$i]['zeilenfund'] = isset($f['zeilenfund']) ? $f['zeilenfund'] : '---';
	$list[$i]['orig'] = $f['orig'];
	$list[$i]['anmerkung'] = isset($f['anmerkung']) ? $f['anmerkung'] : '';
	$list[$i]['kategorie'] = $currentTypeTitle;
	$list[$i]['inLit'] = $sources[$currentSourceTitle]['InLit'];
	$list[$i]['inFN'] = $sources[$currentSourceTitle]['InFN'];
	$list[$i]['wikiTitle'] = titleToKey($f['wikiTitle']);
	preg_match('/\d+/', $list[$i]['seite'], $m1);
	preg_match('/\d+/', $list[$i]['zeilen'], $m2);
	$sort[$i] = (int)($m1[0]) *1000 + (int)$m2[0];
	$fragmentTypeUsed[$currentTypeTitle] = true;
	$i++;
}

array_multisort($sort, $list);

if(SORT_BY_CATEGORY) {
	foreach($categoryWhitelist as $fragtypeTitle => $fragtype) {
		$found = false;
		foreach($list as $l) {
			if($l['kategorie'] === $fragtypeTitle) {
				$found = true;
				break;
			}
		}
		if(!$found)
			continue;

		print '\subsection{'.$fragtypeTitle."}\n";

		print_fragments($list, $fragtypeTitle);
	}
} else {
	print_fragments($list, FALSE);
}

function print_fragments($list, $fragtypeTitle)
{
	foreach($list as $l) {
		if($fragtypeTitle !== FALSE && $l['kategorie'] !== $fragtypeTitle)
			continue;
		$l['seite'] = korrBereich($l['seite']);
		$l['seitefund'] = korrBereich($l['seitefund']);
		$l['zeilen'] = korrBereich($l['zeilen']);
		$l['zeilenfund'] = korrBereich($l['zeilenfund']);
		$l['plagiat'] = korrFragmentText($l['plagiat']);
		$l['orig'] = korrFragmentText($l['orig']);
		$l['anmerkung'] = korrFragmentAnmerkung($l['anmerkung']);

		if($l['seitefund']) {
			if($l['zeilenfund'])
				$cite = '\cite[S.~'.$l['seitefund'].' Z.~'.$l['zeilenfund'].']';
			else
				$cite = '\cite[S.~'.$l['seitefund'].']';
		} else {
			$cite = '\cite';
		}

		if($l['inLit'] === 'ja') {
			$citedInDiss = '';
		} else if($l['inFN'] === 'ja') {
			$citedInDiss = ' (Nur in Fu\ss{}note, aber \emph{nicht} im Literaturverzeichnis angef\"uhrt!)';
		} else {
			$citedInDiss = ' (\emph{Weder} in Fu\ss{}note noch im Literaturverzeichnis angef\"uhrt!)';
		}

		print '\phantomsection{}'."\n";
		print '\belowpdfbookmark{Fragment '.$l['seite'].' '.$l['zeilen'].'}{'.$l['wikiTitle'].'}'."\n";
		print '\hypertarget{'.titleToKey($l['wikiTitle']).'}{}'."\n";

		print '\begin{fragment}'."\n";
		$k = str_replace('Kategorie:', '', $l['kategorie']);
		print '\begin{fragmentpart}{Dissertation S.~'.$l['seite'].' Z.~'.$l['zeilen'].(SORT_BY_CATEGORY ? '}' : ' ('.$k.')}')."\n";
		print '\enquote{'.$l['plagiat'].'}'."\n";
		print '\end{fragmentpart}'."\n";
		print '\begin{fragmentpart}{Original '.$cite.'{'.$l['quelle'].'}'.$citedInDiss.'}'."\n";
		print '\enquote{'.$l['orig'].'}'."\n";
		print '\end{fragmentpart}'."\n";
		if(!empty($l['anmerkung'])) {
			print '\begin{fragmentpart}{Anmerkung}'."\n";
			print $l['anmerkung']."\n";
			print '\end{fragmentpart}'."\n";
		}
		print '\end{fragment}'."\n";
	}
}

