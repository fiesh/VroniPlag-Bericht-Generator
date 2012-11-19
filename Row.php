<?php

class Row {

	private static $remove = 0;
	private $wikiaSyntax;

	public function Row($wikiaSyntax) {
		$this->wikiaSyntax = $wikiaSyntax;
	}

	public function asLatexSyntax() {
		$wikiaSyntaxToParse = $this->wikiaSyntax;

		if (preg_match('/\|\+((.*?)!)+/s', $wikiaSyntaxToParse)) {
			$start = strpos($wikiaSyntaxToParse, '|+') + 2;
			$length = $tart + strpos($wikiaSyntaxToParse, '!') + 1;
			$caption = substr($wikiaSyntaxToParse, $start, $length);
			// TODO handle caption
			require_once('Logger.php');
			Logger::dump($caption);
		}
		if (preg_match('/!(([^+]*?)!)+/s', $wikiaSyntaxToParse)) {
			$columns = preg_split('/!/s', substr($wikiaSyntaxToParse, strpos($wikiaSyntaxToParse, '!') + 1));
			
			for ($i = 0; $i < count($columns); $i++) {
				// TODO add real symbol
				$columns[$i] = preg_replace('/&sum;/', 'Summe', $columns[$i]);
				if (preg_match('/\|/', $columns[$i])) {
					// e.g. "style="text-align:right;"|19"
					$columns[$i] = substr($columns[$i], strpos($columns[$i], '|') + 1);
				} 
				$columns[$i] = korrString($columns[$i]);
			}

			$latexSyntax = implode(' & ', $columns) . ' \\\\ ';
		}
		else if (preg_match('/\|(([^+]*?)\|\|)+/s', $wikiaSyntaxToParse)) {
			$columns = preg_split('/\|\|/s', substr($wikiaSyntaxToParse, strpos($wikiaSyntaxToParse, '|') + 1));

			for ($i = 0; $i < count($columns); $i++) {
				$columns[$i] = korrString($columns[$i]);
			}

			$latexSyntax = implode(' & ', $columns) . ' \\\\ ';
		}

		/*	$latexSyntax = '';
		foreach ($columns as $i => $column) {

			require_once('Logger.php');

			if ($i > 1) {
				$column = korrString($column);
				Logger::dump(korrString($column));
			}
			else {
				$column = korrString($column);
				Logger::dump(korrStringWithLinks($column));
			}

			$latexSyntax .= ' ' . $column . ' ';
			if ($i + 1 < count($columns)) {
				$latexSyntax .= '&';
			}
			else {
				$latexSyntax .= '\\\\';
			}
		}	*/

		return $latexSyntax;
	}

}