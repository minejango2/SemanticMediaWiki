<?php

namespace SMW\Tests;

/**
 * @group semantic-mediawiki
 *
 * @license GPL-2.0-or-later
 * @since 1.9
 *
 * @author mwjames
 */
class GlobalFunctionsTest extends \PHPUnit\Framework\TestCase {

	/**
	 * @covers ::smwfGetLinker
	 * @test smwfGetLinker
	 *
	 * @since 1.9
	 */
	public function testSmwfGetLinker() {
		$instance = smwfGetLinker();

		$this->assertInstanceOf( 'Linker', $instance );
	}

	/**
	 * @covers ::smwfNormalTitleDBKey
	 * @test smwfNormalTitleDBKey
	 *
	 * @since 1.9
	 */
	public function testSmwfNormalTitleDBKey() {
		$result = smwfNormalTitleDBKey( ' foo bar ' );

		// Globals are ... but it can't be invoke ... well make my day
		$expected = $GLOBALS['wgCapitalLinks'] ? 'Foo_bar' : 'foo_bar';
		$this->assertEquals( $expected, $result );
	}

	/**
	 * @covers ::smwfHTMLtoUTF8
	 * @test smwfHTMLtoUTF8
	 *
	 * @since 1.9
	 */
	public function testSmwfHTMLtoUTF8() {
		$result = smwfHTMLtoUTF8( "\xc4\x88io bonas dans l'\xc3\xa9cole, &#x108;io bonas dans l'&eacute;cole!" );

		$expected = "Ĉio bonas dans l'école, Ĉio bonas dans l'école!";
		$this->assertEquals( $expected, $result );
	}

	/**
	 * @test Test if global functions are accessible
	 * @dataProvider getGlobalFunctions
	 *
	 * @param $function
	 */
	public function testGlobalFunctionsAccessibility( $function ) {
		$this->assertTrue( function_exists( $function ) );
	}

	/**
	 * @covers ::smwfEncodeMessages
	 * @test smwfEncodeMessages
	 * @dataProvider getEncodeMessagesDataProvider
	 *
	 * @param $message
	 * @param $type
	 * @param $separator
	 * @param $escape
	 */
	public function testSmwfEncodeMessages( $message, $type, $separator, $escape ) {
		$results = smwfEncodeMessages( $message );
		$this->assertFalse( $results === null );
		$this->assertTrue( is_string( $results ) );

		$results = smwfEncodeMessages( $message, $type );
		$this->assertFalse( $results === null );
		$this->assertTrue( is_string( $results ) );

		$results = smwfEncodeMessages( $message, $type, $separator );
		$this->assertFalse( $results === null );
		$this->assertTrue( is_string( $results ) );

		$results = smwfEncodeMessages( $message, $type, $separator, $escape );
		$this->assertFalse( $results === null );
		$this->assertTrue( is_string( $results ) );
	}

	public function testSmwfCacheKeyOnPrefixedNamespace() {
		$this->assertEquals(
			smwfCacheKey( 'foo', 'bar' ),
			smwfCacheKey( ':foo', 'bar' )
		);
	}

	public function testSmwfContLang() {
		$this->assertInstanceOf(
			'\SMW\Localizer\LocalLanguage\LocalLanguage',
			smwfContLang()
		);
	}

	/**
	 * Provides available global functions
	 *
	 * @return array
	 */
	public function getGlobalFunctions() {
		return [
			[ 'smwfNormalTitleDBKey' ],
			[ 'smwfXMLContentEncode' ],
			[ 'smwfHTMLtoUTF8' ],
			[ 'smwfEncodeMessages' ],
			[ 'smwfGetStore' ],
			[ 'smwfGetLinker' ],
		];
	}

	/**
	 * Provides messages
	 *
	 * @return array
	 */
	public function getEncodeMessagesDataProvider() {
		return [
			[ [ '', '', '' ], '', '', true ],
			[ [ 'abc', 'ABC', '<span>Test</span>' ], '', '', true ],
			[ [ 'abc', 'ABC', '<span>Test</span>' ], 'warning', '', true ],
			[ [ 'abc', 'ABC', '<span>Test</span>' ], 'info', ',', false ],
			[ [ 'abc', 'ABC', '<span>Test</span>' ], null, ',', false ],
			[ [ 'abc', 'ABC', '<span>Test</span>' ], '<span>Test</span>', ',', true ],
		];
	}
}
