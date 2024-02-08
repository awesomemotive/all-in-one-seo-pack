<?php
namespace PHPInsight;

/*
  phpInsight is a Naive Bayes classifier to calculate sentiment. The program
  uses a database of words categorised as positive, negative or neutral

  Copyright (C) 2012  James Hennessey
  Class modifications and improvements by Ismayil Khayredinov (ismayil.khayredinov@gmail.com)

  This program is free software: you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation, either version 3 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program.  If not, see <http://www.gnu.org/licenses/>

 */

class Sentiment {

	/**
	 * Location of the dictionary files
	 * @var str 
	 */
	private $dataFolder = '';

	/**
	 * List of tokens to ignore
	 * @var array 
	 */
	private $ignoreList = array();

	/**
	 * List of words with negative prefixes, e.g. isn't, arent't
	 * @var array
	 */
	private $negPrefixList = array();

	/**
	 * Storage of cached dictionaries
	 * @var array 
	 */
	private $dictionary = array();

	/**
	 * Min length of a token for it to be taken into consideration
	 * @var int
	 */
	private $minTokenLength = 1;

	/**
	 * Max length of a taken for it be taken into consideration
	 * @var int
	 */
	private $maxTokenLength = 15;

	/**
	 * Classification of opinions
	 * @var array
	 */
	private $classes = array('pos', 'neg', 'neu');

	/**
	 * Token score per class
	 * @var array 
	 */
	private $classTokCounts = array(
		'pos' => 0,
		'neg' => 0,
		'neu' => 0
	);

	/**
	 * Analyzed text score per class
	 * @var array
	 */
	private $classDocCounts = array(
		'pos' => 0,
		'neg' => 0,
		'neu' => 0
	);

	/**
	 * Number of tokens in a text
	 * @var int 
	 */
	private $tokCount = 0;

	/**
	 * Number of analyzed texts
	 * @var int
	 */
	private $docCount = 0;

	/**
	 * Implication that the analyzed text has 1/3 chance of being in either of the 3 categories
	 * @var array
	 */
	private $prior = array(
		'pos' => 0.333,
		'neg' => 0.333,
		'neu' => 0.334,
	);

	/**
	 * Class constructor
	 * @param str $dataFolder base folder
	 * Sets defaults and loads/caches dictionaries
	 */
	public function __construct($dataFolder = false) {

		//set the base folder for the data models
		$this->setDataFolder($dataFolder);

		//load and cache directories, get ignore and prefix lists
		$this->loadDefaults();
	}

	/**
	 * Get scores for each class
	 *
	 * @param str $sentence Text to analyze
	 * @return int Score
	 */
	public function score($sentence) {

		//For each negative prefix in the list
		foreach ($this->negPrefixList as $negPrefix) {

			//Search if that prefix is in the document
			if (strpos($sentence, $negPrefix) !== false) {
				//Reove the white space after the negative prefix
				$sentence = str_replace($negPrefix . ' ', $negPrefix, $sentence);
			}
		}

		//Tokenise Document
		$tokens = $this->_getTokens($sentence);
		// calculate the score in each category

		$total_score = 0;

		//Empty array for the scores for each of the possible categories
		$scores = array();

		//Loop through all of the different classes set in the $classes variable
		foreach ($this->classes as $class) {

			//In the scores array add another dimention for the class and set it's value to 1. EG $scores->neg->1
			$scores[$class] = 1;

			//For each of the individual words used loop through to see if they match anything in the $dictionary
			foreach ($tokens as $token) {

				//If statement so to ignore tokens which are either too long or too short or in the $ignoreList
				if (strlen($token) > $this->minTokenLength && strlen($token) < $this->maxTokenLength && !in_array($token, $this->ignoreList)) {
					//If dictionary[token][class] is set
					if (isset($this->dictionary[$token][$class])) {
						//Set count equal to it
						$count = $this->dictionary[$token][$class];
					} else {
						$count = 0;
					}

					//Score[class] is calcumeted by $scores[class] x $count +1 divided by the $classTokCounts[class] + $tokCount
					$scores[$class] *= ($count + 1);
				}
			}

			//Score for this class is the prior probability multiplyied by the score for this class
			$scores[$class] = $this->prior[$class] * $scores[$class];
		}

		//Makes the scores relative percents
		foreach ($this->classes as $class) {
			$total_score += $scores[$class];
		}

		foreach ($this->classes as $class) {
			$scores[$class] = round($scores[$class] / $total_score, 3);
		}

		//Sort array in reverse order
		arsort($scores);

		return $scores;
	}

	/**
	 * Get the class of the text based on it's score
	 * 
	 * @param str $sentence
	 * @return str pos|neu|neg
	 */
	public function categorise($sentence) {

		$scores = $this->score($sentence);

		//Classification is the key to the scores array
		$classification = key($scores);

		return $classification;
	}

	/**
	 * Load and cache dictionary
	 *
	 * @param str $class
	 * @return boolean
	 */
	public function setDictionary($class) {
		/**
		 *  For some people this file extention causes some problems!
		 */
		$fn = "{$this->dataFolder}data.{$class}.php";

		if (file_exists($fn)) {
			$temp  = trim((string) file_get_contents($fn));
			$words = unserialize($temp);
		} else {
			echo 'File does not exist: ' . $fn;
		}

		//Loop through all of the entries
		foreach ($words as $word) {

			$this->docCount++;
			$this->classDocCounts[$class]++;

			//Trim word
			$word = trim($word);

			//If this word isn't already in the dictionary with this class
			if (!isset($this->dictionary[$word][$class])) {

				//Add to this word to the dictionary and set counter value as one. This function ensures that if a word is in the text file more than once it still is only accounted for one in the array
				$this->dictionary[$word][$class] = 1;
			}//Close If statement

			$this->classTokCounts[$class]++;
			$this->tokCount++;
		}//Close while loop going through everyline in the text file

		return true;
	}

	/**
	 * Set the base folder for loading data models
	 * @param str  $dataFolder base folder
	 * @param bool $loadDefaults true - load everything by default | false - just change the directory
	 */
	public function setDataFolder($dataFolder = false, $loadDefaults = false){
		//if $dataFolder not provided, load default, else set the provided one
		if($dataFolder == false){
			$this->dataFolder = __DIR__ . '/data/';
		}
		else{
			if(file_exists($dataFolder)){
				$this->dataFolder = $dataFolder;
			}
			else{
				echo 'Error: could not find the directory - '.$dataFolder;
			}
		}

		//load default directories, ignore and prefixe lists
		if($loadDefaults !== false){
			$this->loadDefaults();
		}
	}

	/**
	 * Load and cache directories, get ignore and prefix lists
	 */
	private function loadDefaults(){
		// Load and cache dictionaries
		foreach ($this->classes as $class) {
			if (!$this->setDictionary($class)) {
				echo "Error: Dictionary for class '$class' could not be loaded";
			}
		}

		if (!isset($this->dictionary) || empty($this->dictionary))
			echo 'Error: Dictionaries not set';

		//Run function to get ignore list
		$this->ignoreList = $this->getList('ign');

		//If ingnoreList not get give error message
		if (!isset($this->ignoreList))
			echo 'Error: Ignore List not set';

		//Get the list of negative prefixes
		$this->negPrefixList = $this->getList('prefix');

		//If neg prefix list not set give error
		if (!isset($this->negPrefixList))
			echo 'Error: Ignore List not set';
	}

	/**
	 * Break text into tokens
	 *
	 * @param str $string	String being broken up
	 * @return array An array of tokens
	 */
	private function _getTokens($string) {

		// Replace line endings with spaces
		$string = str_replace("\r\n", " ", $string);

		//Clean the string so is free from accents
		$string = $this->_cleanString($string);

		//Make all texts lowercase as the database of words in in lowercase
		$string = strtolower($string);
		$string = preg_replace('/[[:punct:]]+/', '', $string);

		//Break string into individual words using explode putting them into an array
		$matches = explode(' ', $string);

		//Return array with each individual token
		return $matches;
	}

	/**
	 * Load and cache additional word lists
	 *
	 * @param str $type
	 * @return array
	 */
	public function getList($type) {
		//Set up empty word list array
		$wordList = array();

		$fn = "{$this->dataFolder}data.{$type}.php";
		;
		if (file_exists($fn)) {
			$temp  = trim((string) file_get_contents($fn));
			$words = unserialize($temp);
		} else {
			return 'File does not exist: ' . $fn;
		}

		//Loop through results
		foreach ($words as $word) {
			//remove any slashes
			$word = stripcslashes($word);
			//Trim word
			$trimmed = trim($word);

			//Push results into $wordList array
			array_push($wordList, $trimmed);
		}
		//Return $wordList
		return $wordList;
	}

	/**
	 * Function to clean a string so all characters with accents are turned into ASCII characters. EG: ‡ = a
	 * 
	 * @param str $string
	 * @return str
	 */
	private function _cleanString($string) {

		$diac =
				/* A */ chr(192) . chr(193) . chr(194) . chr(195) . chr(196) . chr(197) .
				/* a */ chr(224) . chr(225) . chr(226) . chr(227) . chr(228) . chr(229) .
				/* O */ chr(210) . chr(211) . chr(212) . chr(213) . chr(214) . chr(216) .
				/* o */ chr(242) . chr(243) . chr(244) . chr(245) . chr(246) . chr(248) .
				/* E */ chr(200) . chr(201) . chr(202) . chr(203) .
				/* e */ chr(232) . chr(233) . chr(234) . chr(235) .
				/* Cc */ chr(199) . chr(231) .
				/* I */ chr(204) . chr(205) . chr(206) . chr(207) .
				/* i */ chr(236) . chr(237) . chr(238) . chr(239) .
				/* U */ chr(217) . chr(218) . chr(219) . chr(220) .
				/* u */ chr(249) . chr(250) . chr(251) . chr(252) .
				/* yNn */ chr(255) . chr(209) . chr(241);

		return strtolower(strtr($string, $diac, 'AAAAAAaaaaaaOOOOOOooooooEEEEeeeeCcIIIIiiiiUUUUuuuuyNn'));
	}

	/**
	 * Deletes old data/data.* files
	 * Creates new files from updated source fi
	 */
	public function reloadDictionaries(){

		foreach($this->classes as $class){
			$fn = "{$this->dataFolder}data.{$class}.php";
			if (file_exists($fn)) {
				unlink($fn);
			} 
		}

		$dictionaries = __DIR__ . '/dictionaries/';

		foreach($this->classes as $class){
			$dict = "{$dictionaries}source.{$class}.php";

			require_once($dict);

			$data = $class;

			$fn = "{$this->dataFolder}data.{$class}.php";
			file_put_contents($fn, serialize($$data));
		}

		

	}

}

?>
