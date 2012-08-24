<?php

require_once('WikiLoader.php');
require_once('config.php');

class FragmentLoader {
	static private function processString($s, $title)
	{
		$source = WikiLoader::parseSource($s, 'SMWFragment');

		if($source === false)
			return false;

		$renames = array(
			'SeiteArbeit' => 'seite',
			'ZeileArbeit' => 'zeilen',
			'TextArbeit' => 'plagiat',
			'SeiteQuelle' => 'seitefund',
			'ZeileQuelle' => 'zeilenfund',
			'TextQuelle' => 'orig',
			'Anmerkungen' => 'anmerkung',
			'Typus' => 'typus',
			'Bearbeiter' => false,
			'Sichter' => false,
			'Kuerzel' => false,
			'Quelle' => false,
			'FragmentStatus' => false,
			'Markierungslaufweite' => false,
			'QuelleAlt' => false,
		);

		foreach($source as $key => $val) {
			if(in_array($key, array_keys($renames))) {
				if($val && $renames[$key])
					$ret[$renames[$key]] = $val;
			} else {
				print "Fehler: Unbekannter Wert: $key.  Fragment: $title\n";
			}
		}
		
		return $ret;
	}

	static private function collectCategories($entry)
	{
		$cats = array();
		if(isset($entry['categories']))
			foreach($entry['categories'] as $c)
				$cats[] = $c['title'];
		$cats = array_unique($cats);
		sort($cats);
		return $cats;
	}

	static private function processFrags($entries, &$ignored = array())
	{
		$fragments = array();
		foreach($entries as $e) {
			$a = self::processString($e['revisions'][0]['*'], $e['title']);
			if($a !== false) {
				$a['wikiTitle'] = $e['title'];
				$a['categories'] = self::collectCategories($e);
				$fragments[] = $a;
			} else {
				$ignored[$e['title']] = $e['revisions'][0]['*'];
			}
		}
		return $fragments;
	}

	static public function getFragments(&$ignored = array())
	{
		$entries = WikiLoader::getEntriesWithPrefix(NAME_PREFIX.'/Fragment ', true, true);
		return self::processFrags($entries, $ignored);
	}

	static private function parseFragmentType($rawText)
	{
		$fragtype = array();
		if(preg_match('/<!--\s*prioritaet\s*=\s*(-?\s*\d+)/si', $rawText, $match)) {
			$fragtype['priority'] = (int) preg_replace('/\s/', '', $match[1]);
		} else {
			$fragtype['priority'] = 0;
		}
		return $fragtype;
	}

	static public function getFragmentTypes(&$ignored = array())
	{
		$pageids = WikiLoader::getCategoryMembers('Kategorie:Plagiatsarten');
		$entries = WikiLoader::getEntries($pageids, true, true);

		$fragtypes = array();
		foreach($entries as $entry) {
			if (substr($entry['title'], 0, 10) == 'Kategorie:') {
				$fragtype = self::parseFragmentType($entry['revisions'][0]['*']);
				if ($fragtype !== false) {
					$fragtype['title'] = $entry['title'];
					$fragtypes[] = $fragtype;
				} else {
					$ignored[] = $entry['title'];
				}
			}
		}
		usort($fragtypes, 'fragmentLoaderTypePriorityCmp');
		return $fragtypes;
	}

}

// these functions have to be defined outside of the class --
// they are used as callbacks
function fragmentLoaderTypePriorityCmp($fragtype1, $fragtype2) {
	if($fragtype1['priority'] < $fragtype2['priority']) {
		return -1;
	} else if($fragtype1['priority'] > $fragtype2['priority']) {
		return 1;
	} else {
		return strcmp($fragtype1['title'], $fragtype2['title']);
	}
}
