<?php

class Token {

	private $token;

	public function Token($token) {
		$this->token = $token;
	}

	public function asString() {
		return $this->token;
	}

	public function asStringToCompare() {
		$tokenToCompare = $this->token;
		$tokenToCompare = preg_replace(array(
			'/\$\[\$\.\.\.\$\]\$/', // [...] looks like this $[$...$]$
			'/\$\[\$FN 1\$\]\$/',
			'/\$\[\$/',
			'/\$\]\$/',
			'/\\\[a-z]+\{([^}]*)}/', // LaTeX commands
			'/\\\[a-z]+\{/',
			'/}/',
			'/â€ž/', // „
			'/â€œ/', // “
			'/„/',
			'/“/',
			'/»/',
			'/«/',
			'/,/',
			'/‘/',
			'/\'\'/',
			'/^\s$/',
			'/\./'
		), array(
			'',
			'',
			'',
			'',
			'$1',
			'',
			'',
			'',
			'',
			'',
			'',
			'',
			'',
			'',
			'',
			'',
			'',
			''
		), $tokenToCompare);
		$tokenToCompare = strtolower($tokenToCompare);
		return $tokenToCompare;
	}

}