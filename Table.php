<?php

require_once('Row.php');

class Table {

	private $wikiaSyntax;

	private $wikiaPosition;

	public function Table($wikiaSyntax, $wikiaPosition) {
		$this->wikiaSyntax = $wikiaSyntax;
		$this->wikiaPosition = $wikiaPosition;
	}

	public function getWikiaPosition() {
		return $this->wikiaPosition;
	}

	public function getWikiaLength() {
		return strlen($this->wikiaSyntax);
	}

	public function asLatexSyntax() {
		preg_match('/({\|)([^}]*)(\|})/', $this->wikiaSyntax, $parts);
		$wikiaSyntaxToParse = $parts[2];

		$lastRows = substr($wikiaSyntaxToParse, strrpos($wikiaSyntaxToParse, '|-') + 2);
		$lastRows = preg_split('/!/', substr($lastRows, strpos($lastRows, '!') + 1));

		$latexSyntax = '\newline \begin{tabular}{';

		$columns = array();
		for ($i = 0; $i < count($lastRows); $i++) {
			if (preg_match('/text-align:right/', $lastRows[$i])) {
				$columns[] = 'r';
			}
			else {
				$columns[] = 'l';
			}
		}
		$latexSyntax .= '|' . implode('|', $columns) . '|';

		$latexSyntax .= '} ' . "\n";

		$rows = preg_split('/\|-/', $parts[2]);
		for ($i = 0; $i < count($rows); $i++) {
			$rows[$i] = new Row($rows[$i]);
		}

		foreach ($rows as $row) {
			$latexSyntax .= $row->asLatexSyntax();
		}

		$latexSyntax .= '\end{tabular}' . "\n";

		return $latexSyntax;
	}

}