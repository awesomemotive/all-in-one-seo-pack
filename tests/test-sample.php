<?php
/**
 * Class SampleTest
 *
 * @package 
 */

/**
 * Sample test case.
 */
class SampleTest extends WP_UnitTestCase {

	/**
	 * A single example test.
	 */
	function test_sample() {
		// Replace this with some actual testing code.
		$this->assertTrue( true );
	}

function test_sample_string() {
 
	$string = 'Unit tests are sweet';
 
	$this-&gt;assertEquals( 'Unit tests are sweet', $string );
}

}


