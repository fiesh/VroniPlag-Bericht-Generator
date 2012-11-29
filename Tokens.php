<?php

class Tokens implements ArrayAccess, Countable {

	private $tokens;

	public function Tokens(array $tokens) {
		$this->tokens = $tokens;
	}

	public function asString($offset, $length) {
		if ($offset + $length > count($this->tokens)) {
			// TODO meaningful Exception
			throw new Exception();
		}
		$tokensAsString = '';
		for ($i = $offset; $i < $offset + $length; $i++) {
			$tokensAsString .= $this->tokens[$i]->asString();
		}
		return $tokensAsString;
	}

	public function asStringToCompare($offset, $length) {
		if ($offset + $length > count($this->tokens)) {
			// TODO meaningful Exception
			throw new Exception();
		}
		$tokensAsStringToCompare = '';
		for ($i = $offset; $i < $offset + $length; $i++) {
			$tokensAsStringToCompare .= $this->tokens[$i]->asStringToCompare();
		}
		return $tokensAsStringToCompare;
	}

	public function count() {
		return count($this->tokens);
	}

    public function offsetExists($offset) {
        return isset($this->tokens[$offset]);
    }

    public function offsetGet($offset) {
        return isset($this->tokens[$offset]) ? $this->tokens[$offset] : null;
    }

	public function offsetSet($offset, $value) {
        if (is_null($offset)) {
            $this->tokens[] = $value;
        } else {
            $this->tokens[$offset] = $value;
        }
    }

    public function offsetUnset($offset) {
        unset($this->tokens[$offset]);
    }

}