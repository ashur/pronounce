<?php

/**
 * This file is part of Pronounce
 */
namespace Pronounce;

class Dictionary
{
	/**
	 * @var	string
	 */
	protected $dictionaryContents;

	/**
	 * @var	string
	 */
	protected $dictionaryFile;

	/**
	 * @param	string	$dictionaryFile
	 *
	 * @return	void
	 */
	public function __construct( string $dictionaryFile=null )
	{
		$this->dictionaryFile = $dictionaryFile != null ? $dictionaryFile : dirname( __DIR__ ) . '/assets/cmu-dict.txt';

		if( !file_exists( $this->dictionaryFile ) )
		{
			throw new \InvalidArgumentException( "No such file '{$dictionaryFile}'" );
		}
	}

	/**
	 * Returns array of matching pronunciations
	 *
	 * @param	string	$word
	 *
	 * @throws	OutOfBoundsException	If word not found in dictionary
	 *
	 * @return	array
	 */
	public function getPronunciations( string $word ) : array
	{
		$pronunciations = [];

		if( $this->dictionaryContents === null )
		{
			$this->loadDictionary();
		}

		$word = strtoupper( $word );
		$pattern = "/^{$word}(\ *|\(\d\))\ +([\ A-Z0-9]+)$/m";

		preg_match_all( $pattern, $this->dictionaryContents, $matchingLines );

		if( count( $matchingLines[2] ) == 0 )
		{
			throw new \OutOfBoundsException( "'{$word}' not found in dictionary" );
		}

		foreach( $matchingLines[2] as $pronunciation )
		{
			$pronunciations[] = trim( $pronunciation );
		}

		return $pronunciations;
	}

	/**
	 * Return number of syllables in a pronunciation
	 *
	 * @param	string	$pronunciation
	 *
	 * @return	int
	 */
	static public function getSyllableCount( string $pronunciation ) : int
	{
		/*
		 * CMU lexical stress markers:
		 *
		 * 0 — No stress
		 * 1 — Primary stress
		 * 2 — Secondary stress
		 */
		$pattern = '/[0-2]/';
		return preg_match_all( $pattern, $pronunciation );
	}

	/**
	 * Finds whether dictionary contains a word
	 *
	 * @param	string	$word
	 *
	 * @return	boolean
	 */
	public function hasWord( string $word ) : bool
	{
		try
		{
			$this->getPronunciations( $word );
			return true;
		}
		catch( \OutOfBoundsException $e )
		{
			return false;
		}
	}

	/**
	 * Loads contents of dictionary file into string
	 */
	protected function loadDictionary()
	{
		$this->dictionaryContents = file_get_contents( $this->dictionaryFile );
	}

	/**
	 * Finds whether two words rhyme
	 *
	 * @param	string	$word1
	 *
	 * @param	string	$word2
	 *
	 * @return	boolean
	 */
	public function wordsDoRhyme( string $word1, string $word2 ) : bool
	{
		$vowelPhonemes = ['AA','AE','AH','AO','AW','AY','EH','ER','EY','IH','IY','OW','OY','UH','UW'];

		$wordsDoRhyme = false;

		$pronunciations1 = $this->getPronunciations( $word1 );
		$pronunciations2 = $this->getPronunciations( $word2 );

		foreach( $pronunciations1 as $pronunciation1 )
		{
			$phonemes1 = explode( ' ', $pronunciation1 );
			foreach( $phonemes1 as $phoneme1 )
			{
				$rootPhoneme1 = substr( $phoneme1, 0, 2 );
				if( !in_array( $rootPhoneme1, $vowelPhonemes ) )
				{
					array_shift( $phonemes1 );
					continue;
				}
				break;
			}

			foreach( $pronunciations2 as $pronunciation2 )
			{
				$phonemes2 = explode( ' ', $pronunciation2 );
				foreach( $phonemes2 as $phoneme2 )
				{
					$rootPhoneme2 = substr( $phoneme2, 0, 2 );
					if( !in_array( $rootPhoneme2, $vowelPhonemes ) )
					{
						array_shift( $phonemes2 );
						continue;
					}

					$countPhonemes1 = count( $phonemes1 );
					$countPhonemes2 = count( $phonemes2 );

					$phonemes1 = array_slice( $phonemes1, -1 * $countPhonemes2 );
					$phonemes2 = array_slice( $phonemes2, -1 * $countPhonemes1 );

					$wordsDoRhyme = $wordsDoRhyme || ($phonemes1 == $phonemes2);
					break;
				}
			}
		}

		return $wordsDoRhyme;
	}
}
