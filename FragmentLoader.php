<?php

require_once('WikiLoader.php');
require_once('config.php');

class FragmentLoader {
	static private function processString($s)
	{
		$needle = 'val_\d+="([^"]*)"';
		if (preg_match_all("/$needle/", $s, $match) >= 10) {
			for($i = 0; $i < 10; $i++) {
				$a[$i+1] = trim(html_entity_decode(html_entity_decode($match[1][$i], ENT_QUOTES, 'UTF-8'), ENT_QUOTES, 'UTF-8')); // Do this once and it doesn't work... hurray php?!
			}
			return $a;
		} else
			return false;
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
		$titleBlacklist = array('Fragment 99999 11-22');
		$fragments = array();
		foreach($entries as $e) {
			$a = self::processString($e['revisions'][0]['*']);
			if($a !== false && $a[1] && !in_array($e['title'], $titleBlacklist)) {
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
