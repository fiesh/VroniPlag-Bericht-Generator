<?php

require_once('config.php');

// Seiten korrigieren
function korrBereich($s)
{
	$i = 0;
	$ret = '';
	if(preg_match_all('/(\d+)[-,](\d+)/', $s, $matches)) {
		while(isset($matches[1][$i])) {
			if($matches[1][$i] == $matches[2][$i])
				$ret .= ((int)$matches[1][$i]).',~';
			else
				$ret .= ((int)$matches[1][$i]).'--'.((int)$matches[2][$i]).',~';
			$i++;
		}
	} else if(preg_match_all('/(\d+)/', $s, $matches)) {
		while(isset($matches[1][$i])) {
			$ret .= ((int)$matches[1][$i]).',~';
			$i++;
		}
	} else {
		return false;
	}

	return trim(substr($ret, 0, strlen($ret)-2));
}

function korrStringWiki($s, $doTrim=true)
{
	//$s = preg_replace('/"([^"]+)"/', '"`$1"\'', $s); // Anfuehrungszeichen lassen sich nicht korrekt reparieren.
	$s = str_replace(array(
			'----',
			'"',
			'&',
			'#',
			'%',
			'
',
			'_',
			'^',
			'´',
			'ﬁ',
			'¬',
			'ﬂ',
			'→',
			'°',
			'−',
			'­',
			'$',
			'[',
			']',
			'~',
			'‑',
			'\.\.\.',
		), array(
			'',
			'\textquotedbl{}',
			'\&',
			'\#',
			'\%',
			' ', // double whitespaces are ignored by LaTeX
			'\_',
			'\^',
			'\'',
			'fi',
			' ',
			'fl',
			'\textrightarrow{}',
			'o',
			'--',
			'-',
			'\$',
			'$[$',
			'$]$',
			'\~{}',
			'--',
			'\ldots',
		), $s);

	$s = korrDash($s);
	$s = strip_tags($s);
	if ($doTrim)
		$s = trim($s);

	return $s;
}

function korrString($s, $doTrim=true)
{
	$result = '';
	foreach (preg_split('/(\\\newline\s\\\begin{tabular}{[lr|]*}.*?\\\end{tabular})/s', $s, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE) as $part) {
		if (preg_match('/(\\\newline\s\\\begin{tabular}{[lr|]*})(.*?)\\\end{tabular}/s', $part, $match)) {

			$rows = preg_split('/\\\[^a-z]/', $match[2]);

			/*	require_once('Logger.php');
			Logger::dump($match[1]);
			Logger::dump($rows);	*/

			for ($i = 0; $i < count($rows); $i++) {

				$columns = preg_split('/&/', $rows[$i]);
				for ($j = 0; $j < count($columns); $j++) {			
					$columns[$j] = preg_replace(
						'/\$\[\$\$\[\$Quelle:(.*?)\|(.*?)\$\]\$\$\]\$/es',
        				'"\href{http://de.vroniplag.wikia.com/wiki/" . urlToTex("$1") . "}{" . korrString("$1") . "}"',
        				$columns[$j]
					);
				}

				$rows[$i] = "\n" . '\hline' . "\n" . implode('&', $columns);
			}
			
			$result .= '\newline' . $match[1] . implode('\\\\', $rows) . "\n" . '\hline' . "\n" . '\end{tabular}';
		}
		else {
			foreach (preg_split('/(<math>.*?<\/math>)/s', $part, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE) as $part) {
				if (preg_match('/<math>(.*?)<\/math>/s', $part, $match)) {
					$result .= '$'.korrMath($match[1]).'$';
				}
				else {
					$r = str_replace(array(
						'\\',
						'{',
						'}',
					), array(
						'\backslash ',
						'\{',
						'\}',
					), $part);
					$result .= korrStringWiki($r, false);
				}
			}
		}
	}
	
	if($doTrim)
		$result = trim($result);
	return $result;
}

// wie korrString, aber externe Links in Anmerkung mit @url umfassen
function korrStringWithLinks($s, $doTrim=true, $stuffIntoFootnotes=false, $enableRef = false)
{
	$result = '';
	$prots = 'http|https|ftp';
	$schemeRegex = '(?:(?:'.$prots.'):\/\/)';
	$fragmentRegex = NAME_PREFIX.'\/Fragment[_ ]\d\d\d[_ ]\d\d';
	$refRegex = $enableRef ? '|<ref>.*?<\/ref>' : '';
	//if ($enableRef) print "enableRef\n";
	foreach(preg_split('/(\[\[.+?\]\]|\['.$schemeRegex.'[^][{}<>"\\x00-\\x08\\x0a-\\x1F]+\]|'.$schemeRegex.'[^][{}<>"\\x00-\\x20\\x7F]+'.$refRegex.')/s', $s, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE) as $part) {
		if(preg_match('/^\[\[([^|]+)\]\]$/', $part, $match)) {
			// interne Links ohne Linktext
			if(preg_match('/'.$fragmentRegex.'/', $match[1])) {
				// Link auf Fragment intern handhaben
				$result .= '\hyperlink{'.titleToKey($match[1]).'}{'.korrString($match[1]).'}';
			} else {
				$result .= '\url{http://de.vroniplag.wikia.com/wiki/'.urlToTex($match[1]).'}';
			}
		} else if(preg_match('/^\[\[(.+?)\|(.+)\]\]$/', $part, $match)) {
			// interne Links mit Linktext
			if(preg_match('/'.$fragmentRegex.'/', $match[1])) {
				$result .= '\hyperlink{'.titleToKey($match[1]).'}{'.korrString($match[2]).'}';
			} else {
				if($stuffIntoFootnotes) {
					$result .= korrString($match[2]).'\footnote{\url{http://de.vroniplag.wikia.com/wiki/'.urlToTex($match[1]).'}}';
				} else {
					$result .= '\href{http://de.vroniplag.wikia.com/wiki/'.urlToTex($match[1]).'}{'.korrString($match[2]).'}';
				}
			}
		} else if(preg_match('/^'.$schemeRegex.'/s', $part, $match)) {
			// externe Links ohne Linktext
			$result .= '\url{'.urlToTex($part).'}';
		} else if(preg_match('/^\[('.$schemeRegex.'[^][{}<>"\\x00-\x20\\x7F]+) *([^\]\\x00-\\x08\\x0A-\\x1F]*)?\]$/s', $part, $match)) {
			if(isset($match[2]) && trim($match[2])) {
				// externe Links mit Linktext
				if($stuffIntoFootnotes) {
					$result .= korrString($match[2]).'\footnote{\url{'.urlToTex($match[1]).'}}';
				} else {
					$result .= '\href{'.urlToTex($match[1]).'}{'.korrString($match[2]).'}';
				}
			} else {
				$result .= '\url{'.urlToTex($match[1]).'}';
			}

		} else if($enableRef && preg_match('/^<ref>(.*)<\/ref>$/s', $part, $match)) {
			// <ref>...</ref>
			$result .= '\footnote{' . korrStringWithLinks($match[1], false, false, false) . '}';
		} else {
			$result .= korrString($part, false);
		}
	}

	if ($doTrim)
		$result = trim($result);

	return $result;
}

// konvertiert Wiki-Formatierung ('''...''', ''...'', <u>...</u>, <i>...</i>, <b>...</b>) nach LaTeX
function korrWikiFontStyles($s)
{
	$s = preg_replace('/\'\'\'(.*?)\'\'\'/s', '\textbf{$1}', $s);
	$s = preg_replace('/\'\'(.*?)\'\'/s', '\textsl{$1}', $s);
	$s = preg_replace(';<u>(.*?)</u>;s', '\underline{$1}', $s);
	$s = preg_replace(';<i>(.*?)</i>;s', '\textsl{$1}', $s);
	$s = preg_replace(';<b>(.*?)</b>;s', '\textbf{$1}', $s);
	return $s;
}

// convert to LaTeX math
function korrMath($s)
{
	$s = preg_replace(';<math>(.*?)</math>;s', '$$1$', $s);
	return $s;
}

// {{highlight|...|...}} entfernen
function removeHighlights($s)
{
	$s = preg_replace('/{{highlight\|[^\|]*\|([^}]*)}}/', '$1', $s);
	return $s;
}

// Dissertation und Original eines Fragments korrigieren
function korrFragmentText($s)
{
	$s = trim($s);
	$s = removeHighlights($s);
	$s = korrString($s);
	$s = korrWikiFontStyles($s);
	return ($s != '') ? $s : '--';
}

// Anmerkung eines Fragments korrigieren
function korrFragmentAnmerkung($s)
{
	$s = trim($s);
	$s = preg_replace('/\{\{Fragmentsichter[^}]*\}\}/i', '', $s);
	$s = korrStringWithLinks($s);
	return $s;
}

// Grossbuchstaben in Titel und Sammlung vor bibtex schuetzen
function korrVersalien($s)
{
	return preg_replace('/([A-Z])/', '{$1}', $s);
}

// - durch -- ersetzen, wenn es passt
function korrDash($s)
{
	return str_replace(' - ', ' -- ', $s);
}

// & durch \& ersetzen
function korrAmpersand($s)
{
	return str_replace('&', '\&', $s);
}

// , durch and ersetzen in den Autoren (aber nicht in geschweiften Klammern)
function korrAnd($s)
{
	$depth = 0;
	for($i = 0; $i < strlen($s); ++$i) {
		if($s[$i] == ',' && $depth <= 0)
			$s = substr($s, 0, $i) . ' and ' . substr($s, $i+1);
		else if($s[$i] == '{')
			$depth++;
		else if($s[$i] == '}')
			$depth--;
	}
	return $s;
}

// u.a. durch and others ersetzen in den Autoren
function korrEtAl($s)
{
	return preg_replace('/\[\s*u\.\s*a\.\s*\]\s*$|u\.\s*a\.\s*$/', ' and others', $s);
}

// aeussere eckige Klammern entfernen
function korrBracket($s)
{
	return preg_replace('/^\s*\[(.*)\]\s*$/', '$1', $s);
}

// Hack fuer [http:// Linktext]-Links im URL-Feld
// wird wegen umbruch von zu breiten floats im Wiki (IE/Chrome) benoetigt
function korrUrlForBibliography($s)
{
	$prots = 'http|https|ftp';
	$schemeRegex = '(?:(?:'.$prots.'):\/\/)';
	return preg_replace('/^\[('.$schemeRegex.'[^][{}<>"\\x00-\x20\\x7F]+) *([^\]\\x00-\\x08\\x0A-\\x1F]*)?\]$/s', '$1', $s);
}

function titleToKey($title)
{
	$title = str_replace('Kategorie:', '', $title);
	$title = str_replace(' ', '-', $title);
	$title = preg_replace('/[^a-zA-Z0-9]/', '-', $title);
	return $title;
}

// % und # in URLs mit Backslash escapen
function urlToTex($s)
{
	return str_replace(array('%', '#'), array('\\%', '\\#'), $s);
}

