<?php

require_once('Logger.php');
require_once('Tokenizer.php');

class TextMarker {

	private static $colour_index;

	private static $shuffle_colours;

	public static function getTextColours() {
		return array(
			'fragmentcolor0' => array(0,	0,		255), // blue
			'fragmentcolor1' => array(255,	0,		0), // red
			'fragmentcolor2' => array(226,	0,		122), // violet
			'fragmentcolor3' => array(0,	144,	54), // darkgreen
			'fragmentcolor4' => array(0,	255,	0), // lightgreen
			'fragmentcolor5' => array(255,	136,	0), // orange
			'fragmentcolor6' => array(239,	22,		155) // pink
		);
	}

	public static function shuffleColours() {
		self::$colour_index = 0;

        $colours = self::getTextColours();
        $colour_names = array_keys($colours);
        shuffle($colour_names);
        self::$shuffle_colours = array_merge(array_flip($colour_names) , $colours);
	}

	public static function getNextColourName() {
		$colour_names = array_keys(self::$shuffle_colours);
		$colour_name = $colour_names[self::$colour_index];
		self::$colour_index++;
		if (self::$colour_index >= count(self::$shuffle_colours)) {
			self::$colour_index = 0;
		}
		return $colour_name;
	}

	public static function markDuplicates(&$original, &$plagiarism) {

        self::shuffleColours();

        $hits = array();

        $original_tokens = Tokenizer::tokenize($original);
		$plagiarism_tokens = Tokenizer::tokenize($plagiarism);

		$minimum_number_of_similar_tokens = 4;

		for ($original_index = 0; $original_index < count($original_tokens) - $minimum_number_of_similar_tokens; $original_index++) {
            $original_length = 0;
			$new_original_text = '';
			$new_original_text_length = 0;

	        $plagiarism_index = 0;
    	    $plagiarism_length = 0;
			$new_plagiarism_text = '';
            $new_plagiarism_text_length = 0;

            while ($original_index + $original_length < count($original_tokens)
            	&& $plagiarism_index + $plagiarism_length < count($plagiarism_tokens)) {

            	//$original_text = $new_original_text;
                $original_text = '';
                //$original_text_length = $new_original_text_length;
                $original_text_length = 0;
                //$new_original_text = $original_tokens->asStringToCompare($original_index, $original_length);
                $new_original_text = $original_tokens[$original_index + $original_length]->asStringToCompare();
                $new_original_text_length = strlen($new_original_text);

                //$plagiarism_text = $new_plagiarism_text;
                $plagiarism_text = '';
                //$plagiarism_text_length = $new_plagiarism_text_length;
                $plagiarism_text_length = 0;
                //$new_plagiarism_text = $plagiarism_tokens->asStringToCompare($plagiarism_index, $plagiarism_length);
                $new_plagiarism_text = $plagiarism_tokens[$plagiarism_index + $plagiarism_length]->asStringToCompare();
                $new_plagiarism_text_length = strlen($new_plagiarism_text);

                if ($new_original_text_length === $new_plagiarism_text_length
                    && $new_original_text_length === similar_text($new_original_text, $new_plagiarism_text)) {

                    $original_length++;
                    $plagiarism_length++;
                } else if ($new_original_text_length === 0) {

                    $original_length++;
                } else if ($new_plagiarism_text_length === 0) {

                    $plagiarism_length++;
                } else {

                	if ($original_length > $minimum_number_of_similar_tokens
                    	&& $plagiarism_length > $minimum_number_of_similar_tokens) {

	                	// remove ignored characters from the beginning
	                	$tmp_original_index = $original_index;
	                	$tmp_original_length = $original_length;
	                	while ($tmp_original_index < $original_index + $original_length) {
	                        if ($original_tokens[$tmp_original_index]->asStringToCompare() === '') {
	                            $tmp_original_index++;
	                            $tmp_original_length--;
	                        }
	                        else {
	                            break;
	                        }
	                    }
	                    $original_length = $tmp_original_length;

	                    // remove ignored characters from the beginning
	                	$tmp_plagiarism_index = $plagiarism_index;
	                	$tmp_plagiarism_length = $plagiarism_length;
	                	while ($tmp_plagiarism_index < $plagiarism_index + $plagiarism_length) {
	                        if ($plagiarism_tokens[$tmp_plagiarism_index]->asStringToCompare() === '') {
	                            $tmp_plagiarism_index++;
	                            $tmp_plagiarism_length--;
	                        }
	                        else {
	                            break;
	                        }
	                    }
	                    $plagiarism_length = $tmp_plagiarism_length;

	                    // remove ignored characters from the end
						while ($original_length > 2) {
							if ($original_tokens[$tmp_original_index + $original_length - 2]->asStringToCompare() === '') {
	                            $original_length--;
	                      	}
	                      	else {
	                      		break;
	                        }
	                    }

	                    // remove ignored characters from the end
	                    while ($plagiarism_length > 2) {
							if ($plagiarism_tokens[$tmp_plagiarism_index + $plagiarism_length - 2]->asStringToCompare() === '') {
	                            $plagiarism_length--;
	                      	}
	                      	else {
	                      		break;
	                        }
	                    }

	                }

                    if ($original_length > $minimum_number_of_similar_tokens
                    	&& $plagiarism_length > $minimum_number_of_similar_tokens) {

	                    $original_index = $tmp_original_index;
    	                $plagiarism_index = $tmp_plagiarism_index;

    	                $colour_name = self::getNextColourName();

                    	$original_tokens[$original_index] = new Token('\textcolor{' . $colour_name . '}{' . $original_tokens[$original_index]->asString());
                        $original_tokens[$original_index + $original_length - 2] = new Token($original_tokens[$original_index + $original_length - 2]->asString() . '}');
                        
                        $plagiarism_tokens[$plagiarism_index] = new Token('\textcolor{' . $colour_name . '}{' . $plagiarism_tokens[$plagiarism_index]->asString());
                        $plagiarism_tokens[$plagiarism_index + $plagiarism_length - 2] = new Token($plagiarism_tokens[$plagiarism_index + $plagiarism_length - 2]->asString() . '}');
                       	
                        $hits[$plagiarism_index] = $plagiarism_index + $plagiarism_length - 2;

                        $original_index += $original_length - 2;
	                    $original_length = 0;
				        $new_original_text = '';
				        $new_original_text_length = 0;

		                $plagiarism_index++;
	    	            $plagiarism_length = 0;
					    $new_plagiarism_text = '';
	            		$new_plagiarism_text_length = 0;

                        break;

                    }
                    $original_length = 0;
			        $new_original_text = '';
			        $new_original_text_length = 0;

	                $plagiarism_index++;
                    if (array_key_exists($plagiarism_index, $hits)) {
                        $plagiarism_index = $hits[$plagiarism_index] + 1;
                    }
    	            $plagiarism_length = 0;
				    $new_plagiarism_text = '';
            		$new_plagiarism_text_length = 0;
               	}
            }

			if ($original_length > $minimum_number_of_similar_tokens
        		&& $plagiarism_length > $minimum_number_of_similar_tokens) {

	            // remove ignored characters from the beginning
	        	$tmp_original_index = $original_index;
	            $tmp_original_length = $original_length;
	            while ($tmp_original_index < $original_index + $original_length) {
	                if ($original_tokens[$tmp_original_index]->asStringToCompare() === '') {
	                    $tmp_original_index++;
	                    $tmp_original_length--;
	                }
	                else {
	                    break;
	                }
	            }
	            $original_length = $tmp_original_length;

	            // remove ignored characters from the beginning
	        	$tmp_plagiarism_index = $plagiarism_index;
	        	$tmp_plagiarism_length = $plagiarism_length;
	        	while ($tmp_plagiarism_index < $plagiarism_index + $plagiarism_length) {
	                if ($plagiarism_tokens[$tmp_plagiarism_index]->asStringToCompare() === '') {
	                    $tmp_plagiarism_index++;
	                    $tmp_plagiarism_length--;
	                } 
                    else {
	                    break;
	                }
	            }
	            $plagiarism_length = $tmp_plagiarism_length;

	            // remove ignored characters from the end
	           	while ($original_length > 2) {
					if ($original_tokens[$tmp_original_index + $original_length - 2]->asStringToCompare() === '') {
	                     $original_length--;
	                }
	                else {
	                	break;
	                }
	            }

	            // remove ignored characters from the end
	            while ($plagiarism_length > 2) {
					if ($plagiarism_tokens[$tmp_plagiarism_index + $plagiarism_length - 2]->asStringToCompare() === '') {
	                    $plagiarism_length--;
	              	}
	              	else {
	              		break;
	                }
	            }
	        }

			if ($original_length > $minimum_number_of_similar_tokens
	        	&& $plagiarism_length > $minimum_number_of_similar_tokens) {

	            $original_index = $tmp_original_index;
	            $plagiarism_index = $tmp_plagiarism_index;

                $colour_name = self::getNextColourName();

	        	$original_tokens[$original_index] = new Token('\textcolor{' . $colour_name . '}{' . $original_tokens[$original_index]->asString());
	            $original_tokens[$original_index + $original_length - 2] = new Token($original_tokens[$original_index + $original_length - 2]->asString() . '}');
	            
	            $plagiarism_tokens[$plagiarism_index] = new Token('\textcolor{' . $colour_name . '}{' . $plagiarism_tokens[$plagiarism_index]->asString());
	            $plagiarism_tokens[$plagiarism_index + $plagiarism_length - 2] = new Token($plagiarism_tokens[$plagiarism_index + $plagiarism_length - 2]->asString() . '}');

                $original_index += $original_length - 2;
	        }
		}

		$original = $original_tokens->asString(0, count($original_tokens));
		$plagiarism = $plagiarism_tokens->asString(0, count($plagiarism_tokens));

    }

}