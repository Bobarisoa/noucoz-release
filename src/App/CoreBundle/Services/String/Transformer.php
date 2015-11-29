<?php

namespace App\CoreBundle\Services\String;

/**
 * Transformer.
 * Toolbox of string formatter
 */
class Transformer
{	
	const UPPERCASE_NO_TRIM 	= true;
	
	private $htmlspecialchars 	= array('äâàáåãéèëêòóôõöøìíîïùúûüýñçþÿæœðø', 'ÄÂÀÁÅÃÉÈËÊÒÓÔÕÖØÌÍÎÏÙÚÛÜÝÑÇÞÝÆŒÐØ');
	private $utf8chars			= array('àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ',
'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');

	public function split($string = null)
	{
		$string = preg_split('~~u', $string, null, PREG_SPLIT_NO_EMPTY);
		
		return $string;
	}
	
	public function preprocess($string = null)
	{
		$string = trim($string);
		
		return $string;
	}
	
	public function lowercase($string = null, $const = null)
	{
		$string = strtolower($string);
		if (!$const) $string = self::preprocess($string);
		$string = strtr($string, $this->htmlspecialchars[1], $this->htmlspecialchars[0]);
		
		return $string;
	}
	
	public function uppercase($string = null, $const = null)
	{
		$string = strtoupper($string);
		if (!$const) $string = self::preprocess($string);
		$string = strtr($string, $this->htmlspecialchars[0], $this->htmlspecialchars[1]);
		
		return $string;
	}
	
	public function pure($string = null)
	{
		$string = self::preprocess($string);
		$string = str_replace(self::split($this->utf8chars[0]), self::split($this->utf8chars[1]), $string);
		$string = preg_replace('/\s+/', '', $string);
		$string = preg_replace('/[^A-Za-z0-9\-]/', '', $string);
		
		return $string;
	}
	
	public function stlupper($string = null)
	{
		$string = self::preprocess($string);
		$string = self::upfirst($string);
		
		return $string;
	}
	
	public function upfirst($string)
	{
		$string = preg_split("/( |-)/", $string, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
	
		if(count($string) > 1) {
			foreach($string as $word) {
				$string[array_search($word, $string)] = self::upfirst($word);
			}
			
			$string = implode('', $string);
			
			return $string;
		} else {
			$string = array(self::uppercase(mb_substr($string[0], 0, 1, 'UTF8'), self::UPPERCASE_NO_TRIM), self::lowercase(mb_substr($string[0], 1, mb_strlen($string[0]), 'UTF8'), self::UPPERCASE_NO_TRIM));
			$string = implode('', $string);

			return $string;
		}
	}
}