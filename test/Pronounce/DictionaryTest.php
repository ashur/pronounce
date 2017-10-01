<?php

/*
 * This file is part of Pronounce
 */
namespace Pronounce;

use PHPUnit\Framework\TestCase;

class DictionaryTest extends TestCase
{
	public function testConstructorUsesBundledDictionaryByDefault()
	{
		$defaultDictionary = new Dictionary();

		$testDictFile = dirname( __DIR__ ) . '/fixtures/test-dict.txt';
		$customDictionary = new Dictionary( $testDictFile );

		$this->assertFalse( $defaultDictionary->hasWord( 'zoobar' ) );
		$this->assertTrue( $customDictionary->hasWord( 'zoobar' ) );
	}

	public function testGetPronunciationsIsCaseInsensitive()
	{
		$dictionary = new Dictionary();
		$lowercasePronunciations = $dictionary->getPronunciations( 'foobar' );

		$dictionary = new Dictionary();
		$uppercasePronunciations = $dictionary->getPronunciations( 'FOOBAR' );

		$this->assertEquals( $lowercasePronunciations, $uppercasePronunciations );
	}

	/**
	 * @expectedException	OutOfBoundsException
	 */
	public function testGetPronunciationsOfUnknownWordThrowsException()
	{
		$dictionary = new Dictionary();
		$pronunciations = $dictionary->getPronunciations( 'zoobar' );
	}

	public function wordMatchesProvider() : array
	{
		return [
			['foobar', ['F UW1 B AA1 R']],
			['read', ['R EH1 D', 'R IY1 D']],
		];
	}

	/**
	 * @dataProvider	wordMatchesProvider
	 */
	public function testGetPronunciationsReturnsArray( $word, $expectedMatches )
	{
		$dictionary = new Dictionary();
		$actualMatches = $dictionary->getPronunciations( $word );

		$this->assertTrue( is_array( $actualMatches ) );
		$this->assertEquals( $expectedMatches, $actualMatches );
	}

	public function pronunciationSyllablesProvider() : array
	{
		return [
			['F UW1 B AA1 R', 2],
			['HH IH2 P AH0 P AA1 T AH0 M AH0 S', 5],
			['R IY1 D', 1],
		];
	}

	/**
	 * @dataProvider	pronunciationSyllablesProvider
	 */
	public function testGetSyllableCount( $pronunciation, $expectedCount )
	{
		$actualCount = Dictionary::getSyllableCount( $pronunciation );
		$this->assertEquals( $expectedCount, $actualCount );
	}

	public function testHasWordReturnsBoolean()
	{
		$testDictFile = dirname( __DIR__ ) . '/fixtures/test-dict.txt';
		$dictionary = new Dictionary( $testDictFile );

		$this->assertFalse( $dictionary->hasWord( 'hello' ) );
		$this->assertTrue( $dictionary->hasWord( 'zoobar' ) );
	}

	/**
	 * @expectedException	InvalidArgumentException
	 */
	public function testNonExistentDictionaryFileThrowsException()
	{
		$testDictFile = dirname( __DIR__ ) . '/fixtures/' . microtime( true );
		$dictionary = new Dictionary( $testDictFile );
	}

	public function wordRhymeProvider() : array
	{
		return [
			['fair', 'wear', true],
			['strict', 'nicked', true],

			['weepy', 'sleepy', true],

			['spoon', 'cartoon', true],
			['cartoon', 'spoon', true],

			['boot', 'foot', false],
			['fun', 'funny', false],
			['naked', 'baked', false],
		];
	}

	/**
	 * @dataProvider	wordRhymeProvider
	 */
	public function testWordsDoRhyme( $word1, $word2, $shouldRhyme )
	{
		$dictionary = new Dictionary();

		$doesRhyme = $dictionary->wordsDoRhyme( $word1, $word2 );

		$this->assertEquals( $shouldRhyme, $doesRhyme );
	}
}
