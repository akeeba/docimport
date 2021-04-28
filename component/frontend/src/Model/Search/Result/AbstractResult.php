<?php
/*
 * @package   DocImport
 * @copyright Copyright (c)2011-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\Component\DocImport\Site\Model\Search\Result;

// Protect from unauthorized access
defined('_JEXEC') or die();

/**
 * Abstract search result class
 *
 * @property-read  string  link             The URL to the search result item
 * @property-read  string  catlink          The URL to the category where the article belongs
 * @property-read  string  previewLink      The URL to preview the search result item
 * @property-read  string  youtubeId        Find an embedded YouTube video and return its ID
 * @property-read  string  youtubeLink      Find an embedded YouTube video and return a link for embed
 * @property-read  string  youtubeIframe    Find an embedded YouTube video and return the embed IFRAME
 * @property-read  string  youtubeExternal  Find an embedded YouTube video and return a direct link to YouTube's site
 * @property-read  string  youtubeThumbnail Find an embedded YouTube video and return the URL to its JPG thumbnail
 * @property-read  string  synopsis         A synopsis of the article's text
 */
abstract class AbstractResult implements ResultInterface
{
	/** @var  int  Result item's ID */
	public $id;

	/** @var  string  Result item's title */
	public $title;

	/** @var  string  The name of the field which holds the HTML text */
	public $textFieldName = 'fulltext';

	/** @var  int  Maximum length (in characters) for the synopsis displayed in search results */
	public $synopsisMaxLength = 300;

	/**
	 * Get the URL to access the item
	 *
	 * @return  string
	 */
	abstract public function getLink();

	/**
	 * Get the URL to access the item's category
	 *
	 * @return  string
	 */
	abstract public function getCategoryLink();

	/**
	 * Get the URL to access a preview of the item. Defaults to item URL + tmpl=component
	 *
	 * @return  string
	 */
	public function getPreviewLink()
	{
		$jLink = new \JUri($this->getLink());
		$jLink->setVar('tmpl', 'component');
		return $jLink->toString();
	}

	/**
	 * Look for an embedded YouTube video and return its ID
	 *
	 * @return  string
	 */
	public function getYouTubeId()
	{
		$pattern = '#www\.(youtube|youtu\.be)(-nocookie)?\.com/embed/([a-zA-Z0-9_\-]{1,})(["?])#i';
		$hasMatch = preg_match($pattern, $this->fulltext, $matches);

		if (!$hasMatch)
		{
			return '';
		}

		if ($matches[2] != '-nocookie')
		{
			return $matches[2];
		}

		return $matches[3];
	}

	/**
	 * Look for an embedded YouTube video and return its ID
	 *
	 * @return  string
	 */
	public function getYouTubeExternal()
	{
		$id = $this->getYouTubeId();

		if (!$id)
		{
			return '';
		}

		return "//www.youtube.com/watch?v=$id&playnext=0&list=UL&rel=0&cc_load_policy=1";
	}

	/**
	 * Look for an embedded YouTube video and return an embed URL you can then use in an IFRAME, see getYouTubeIframe
	 *
	 * @return  string
	 */
	public function getYouTubeLink()
	{
		$id = $this->getYouTubeId();

		if (!$id)
		{
			return '';
		}

		return '//www.youtube-nocookie.com/embed/' . $id . '?rel=0&cc_load_policy=1&modestbranding=1';
	}

	/**
	 * Look for an embedded YouTube video and returns an embed IFRAME
	 *
	 * @param   string  $class            The class to append to the IFRAME
	 * @param   string  $attributeString  Any attributes to append to the IFRAME
	 *
	 * @return  string
	 */
	public function getYouTubeIframe($class = 'flex-video', $attributeString = '')
	{
		$link = $this->getYouTubeLink();

		if (!$link)
		{
			return '';
		}

		return '<iframe src="' . $link . '" frameborder="0" allowfullscreen="allowfullscreen" class="' . $class . '" ' .
		$attributeString . '></iframe>';
	}

	/**
	 * Look for an embedded YouTube video and returns the URL for its thumbnail in JPG format. The supported formats
	 * which are available are:
	 * - 0  			First auto-generated thumbnail frame
	 * - 1  			Second auto-generated thumbnail frame
	 * - 2  			Third auto-generated thumbnail frame
	 * - 3  			Fourth auto-generated thumbnail frame
	 * - default  		Default thumbnail (0.jpg or whatever you have uploaded, 120x90 px)
	 * - hqdefault  	High quality version of default thumbnail (480x360 px)
	 * - mqdefault  	Medium quality version of default thumbnail (320x180 px)
	 * - sddefault  	Standard definition version of default thumbnail (640x480 px)
	 * - maxresdefault  Maximum resolution version of default thumbnail (maximum video resolution, depends on your video)
	 *
	 * We use medium quality (320x180) by default which is more than enough to display previews in carousels.
	 *
	 * (per http://stackoverflow.com/questions/2068344/how-do-i-get-a-youtube-video-thumbnail-from-the-youtube-api)
	 *
	 * @param   string  $type  The thumbnail type. See above. Default: mqdefault
	 *
	 * @return  string
	 */
	public function getYouTubeThumbnail($type = 'mqdefault')
	{
		$id = $this->getYouTubeId();

		if (!$id)
		{
			return '';
		}

		return '//img.youtube.com/vi/' . $id . '/' . $type . '.jpg';
	}

	/**
	 * Generates a synopsis from the article's full text
	 *
	 * @return string
	 */
	public function getSynopsis()
	{
		$stripedText = strip_tags($this->{$this->textFieldName});

		if (strlen($stripedText) < $this->synopsisMaxLength)
		{
			return $stripedText;
		}

		return substr($stripedText, 0, $this->synopsisMaxLength) . '&hellip;';
	}

	/**
	 * Magic getter, turns the get* methods into virtual properties
	 *
	 * @param   string  $name  The name of the virtual property being accessed
	 *
	 * @return null|string
	 */
	public function __get($name)
	{
		// Map known properties
		switch ($name)
		{
			case 'link':
				return $this->getLink();
				break;

			case 'previewLink':
				return $this->getPreviewLink();
				break;

			case 'catlink':
				return $this->getCategoryLink();
				break;

			case 'synopsis':
				return $this->getSynopsis();
				break;

			case 'youtubeId':
				return $this->getYouTubeId();
				break;

			case 'youtubeLink':
				return $this->getYouTubeLink();
				break;

			case 'youtubeIframe':
				return $this->getYouTubeIframe();
				break;

			case 'youtubeExternal':
				return $this->getYouTubeExternal();
				break;

			case 'youtubeThumbnail':
				return $this->getYouTubeThumbnail();
				break;
		}

		// Still here? You screwed up. Oops.
		$trace = debug_backtrace();
		trigger_error(
			'Undefined property via __get(): ' . $name .
			' in ' . $trace[0]['file'] .
			' on line ' . $trace[0]['line'],
			E_USER_NOTICE);

		return null;
	}
}
