<?php
/**
 * @package   DocImport
 * @copyright Copyright (c)2011-2019 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\DocImport\Admin\Model;

use FOF30\Container\Container;
use FOF30\Model\DataModel;

defined('_JEXEC') or die();

/**
 * Model class for the Articles
 *
 * Fields:
 *
 * @property int    $docimport_article_id
 * @property int    $docimport_category_id
 * @property string $title
 * @property string $slug
 * @property string $fulltext
 * @property string $meta_description
 * @property string $meta_tags
 * @property int    $last_timestamp
 * @property int    $enabled
 * @property string $created_on
 * @property int    $created_by
 * @property string $modified_on
 * @property int    $modified_by
 * @property string $locked_on
 * @property int    $locked_by
 * @property int    $ordering
 *
 * Filters / state:
 *
 * @method  $this  docimport_article_id()   docimport_article_id(int $v)
 * @method  $this  docimport_category_id()  docimport_category_id(int $v)
 * @method  $this  category()               category(int $v)
 * @method  $this  slug()            		slug(int $v)
 * @method  $this  title()                  title(string $v)
 * @method  $this  search()                 search(string $v)
 * @method  $this  language()               language(array $v)
 *
 * Relations:
 *
 * @property-read  Categories	 $category  The DocImport Category for this article
 */
class Articles extends DataModel
{
	/**
	 * Public constructor. Adds behaviours and sets up the behaviours and the relations
	 *
	 * @param   Container  $container
	 * @param   array      $config
	 */
	public function __construct(Container $container, array $config = array())
	{
		parent::__construct($container, $config);

		// Add the filtering behaviour
		$this->addBehaviour('Filters');
		$this->addBehaviour('RelationFilters');
		$this->blacklistFilters([
			'docimport_category_id',
			'title',
		    'fulltext',
		    'meta_description',
		    'meta_tags',
		    'last_timestamp',
		]);

		// Other behaviours
		$this->addBehaviour('Created');
		$this->addBehaviour('Modified');

		// Set up relations
		$this->belongsTo('category', 'Categories', 'docimport_category_id', 'docimport_category_id');
	}

	/**
	 * @param   \JDatabaseQuery  $query
	 */
	protected function onBeforeBuildQuery(\JDatabaseQuery &$query)
	{
		$db = $this->getDbo();

		// Search filter, use either "title" or "search" model state.
		$fltSearch = $this->getState('title', null, 'string');
		$fltSearch = $this->getState('search', $fltSearch, 'string');

		if (!empty($fltSearch))
		{
			$query->where(
				$db->quoteName('title') . ' LIKE ' . $db->quote('%' . $fltSearch . '%')
			);
		}

		// Slug filter
		$fltSlug = $this->getState('slug', null, 'string');

		if (!empty($fltSlug))
		{
			$query->where(
				$db->quoteName('slug') . ' = ' . $db->quote($fltSlug)
			);
		}

		// Category filter, use "docimport_category_id" or "category" model state
		$fltCategory = $this->getState('docimport_category_id', null, 'cmd');
		$fltCategory = $this->getState('category', $fltCategory, 'cmd');

		if (is_numeric($fltCategory) && ($fltCategory > 0))
		{
			$query->where(
				$db->qn('docimport_category_id') . ' = ' . $db->q($fltCategory)
			);
		}

		// Category language filter. We use a relation filter to implement it.
		$fltLanguage = $this->getState('language', null, 'array');

		if (!empty($fltLanguage) && (is_array($fltLanguage) ? (!empty($fltLanguage[0])) : true))
		{
			$this->whereHas('category', function (\JDatabaseQuery $q) use($fltLanguage)
			{
				if (is_array($fltLanguage))
				{
					$langs = array();

					foreach ($fltLanguage as $l)
					{
						$langs[] = $q->q($l);
					}

					$q->where($q->qn('language') . ' IN (' . implode(',', $langs) . ')');
				}
				else
				{
					$q->where($q->qn('language') . ' = ' . $q->q($fltLanguage));
				}
			});
		}

		// Set the default ordering by ID, descending
		if (is_null($this->getState('filter_order', null, 'cmd')) && is_null($this->getState('filter_order_Dir', null, 'cmd')))
		{
			$this->setState('filter_order', $this->getIdFieldName());
			$this->setState('filter_order_Dir', 'DESC');
		}
	}

	public function check()
	{
		// Create a new or sanitise an existing slug
		if (empty($this->slug))
		{
			// Auto-fetch a slug
			$this->slug = \JApplicationHelper::stringURLSafe($this->title);
		}
		else
		{
			// Make sure nobody adds crap characters to the slug
			$this->slug = \JApplicationHelper::stringURLSafe($this->slug);
		}

		/**
		 * WARNING! DO NOT SEARCH FOR SIMILAR SLUGS, DO NOT CHANGE THE SLUG IN ANY OTHER WAY.
		 *
		 * The slugs come from the DocBook XML. They are the XML IDs of other sections, paragraphs etc. They are used
		 * as link targets in internal link references. When you change the article slug DocImport cannot translate the
		 * internal references to  DocImport article IDs. As a result it produces broken non-SEF links inside the
		 * article source. When these are parsed in the front-end you get all sorts of weird behavior.
		 */

	}
}
