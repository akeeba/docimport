<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */


class DocimportHelperFormat
{
	public static function language($lang = '*')
	{
		jimport('joomla.language.helper');
		$languages = JLanguageHelper::getLanguages('lang_code');
		
		if($lang == '*') {
			return JText::_('JALL_LANGUAGE');
		} elseif(array_key_exists($lang, $languages)) {
			return $languages[$lang]->title;
		} else {
			return $lang;
		}
	}
}