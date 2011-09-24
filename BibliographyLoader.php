<?php

require_once('config.php');
require_once('WikiLoader.php');

class BibliographyLoader {
	static public function getSources(&$ignored = array())
	{
		$pageids = WikiLoader::getCategoryMembers('Kategorie:'.NAME_PREFIX.'/Quelle');
		$entries = WikiLoader::getEntries($pageids, true, true);

		$sources = array();
		foreach($entries as $entry) {
			$source = WikiLoader::parseSource($entry['revisions'][0]['*'], 'Quelle');
			if ($source !== false) {
				$source['title'] = $entry['title'];
				$sources[] = $source;
			} else {
				$ignored[] = $entry['title'];
			}
		}
		return $sources;
	}

}
