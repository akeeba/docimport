<?php
/**
 *  @package DocImport
 *  @copyright Copyright (c)2011-2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 *  @license GNU General Public License version 3, or later
 */

namespace Akeeba\DocImport\Site\Model\Search\Result;

// Protect from unauthorized access
defined('_JEXEC') or die();

/**
 * Search result interface
 *
 * @property       string  title            The result's title
 * @property       string  slug             The result's slug (alias)
 * @property       int     id               The result's primary key in its table
 * @property-read  string  link             The URL to the search result item
 * @property-read  string  catlink          The URL to the category where the article belongs
 * @property-read  string  youtubeId        Find an embedded YouTube video and return its ID
 * @property-read  string  youtubeLink      Find an embedded YouTube video and return a link for embed
 * @property-read  string  youtubeIframe    Find an embedded YouTube video and return the embed IFRAME
 * @property-read  string  youtubeExternal  Find an embedded YouTube video and return a direct link to YouTube's site
 * @property-read  string  synopsis         A synopsis of the article's text
 */
interface ResultInterface
{
	/**
	 * Get the URL to access the item
	 *
	 * @return  string
	 */
	public function getLink();

	/**
	 * Get the URL to access the item's category
	 *
	 * @return  string
	 */
	public function getCategoryLink();

	/**
	 * Get the URL to access a preview of the item. Defaults to item URL + tmpl=component
	 *
	 * @return  string
	 */
	public function getPreviewLink();

	/**
	 * Look for an embedded YouTube video and return its ID
	 *
	 * @return  string
	 */
	public function getYouTubeId();

	/**
	 * Look for an embedded YouTube video and return its ID
	 *
	 * @return  string
	 */
	public function getYouTubeExternal();

	/**
	 * Look for an embedded YouTube video and return an embed URL you can then use in an IFRAME, see getYouTubeIframe
	 *
	 * @return  string
	 */
	public function getYouTubeLink();

	/**
	 * Look for an embedded YouTube video and returns an embed IFRAME
	 *
	 * @param   string  $class            The class to append to the IFRAME
	 * @param   string  $attributeString  Any attributes to append to the IFRAME
	 *
	 * @return  string
	 */
	public function getYouTubeIframe($class = 'flex-video', $attributeString = '');

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
	public function getYouTubeThumbnail($type = 'mqdefault');

	/**
	 * Generates a synopsis from the article's full text
	 *
	 * @return string
	 */
	public function getSynopsis();
}
