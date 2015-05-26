<?php
/**
 * @package   AkeebaReleaseSystem
 * @copyright Copyright (c)2010-2015 Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\ReleaseSystem\Site\View\Releases;

defined('_JEXEC') or die;

use Akeeba\ReleaseSystem\Site\Helper\Filter;
use Akeeba\ReleaseSystem\Site\Helper\Title;
use Akeeba\ReleaseSystem\Site\Model\Categories;
use Akeeba\ReleaseSystem\Site\Model\Releases;
use FOF30\Model\DataModel\Collection;
use FOF30\View\View as BaseView;

class Html extends BaseView
{
	/** @var  Collection  The items to display */
	public $items;

	/** @var  Categories  The category of the releases */
	public $category;

	/** @var  \JRegistry  Page parameters */
	public $params;

	/** @var  string  The order column */
	public $order;

	/** @var  string  The order direction */
	public $order_Dir;

	/** @var  \JPagination  Pagination object */
	public $pagination;

	/** @var  array Visual groups */
	public $vgroups;

	/** @var  int  Active menu item ID */
	public $Itemid;

	/** @var  \JMenuNode  The active menu item */
	public $menu;

	public function onBeforeBrowse($tpl = null)
	{
		// Prevent phpStorm's whining...
		if ($tpl) {}

		// Load the model
		/** @var Releases $model */
		$model = $this->getModel();

		// Assign data to the view, part 1 (we need this later on)
		$this->items = $model->get()->filter(function ($item)
		{
			return Filter::filterItem($item, true);
		});

		// Add RSS links
		/** @var \JApplicationSite $app */
		$app = \JFactory::getApplication();
		/** @var \JRegistry $params */
		$params = $app->getParams('com_ars');

		// Set page title and meta
		$title = Title::setTitleAndMeta($params, 'ARS_VIEW_BROWSE_TITLE');

		$show_feed = $params->get('show_feed_link');

		if ($show_feed)
		{
			$feed = 'index.php?option=com_ars&view=Releases&category_id=' . $this->getModel('Categories')->id . '&format=feed';

			$rss = array(
				'type'  => 'application/rss+xml',
				'title' => $title . ' (RSS)'
			);

			$atom = array(
				'type'  => 'application/atom+xml',
				'title' => $title . ' (Atom)'
			);

			// Add the links
			/** @var \JDocumentHTML $document */
			$document = \JFactory::getDocument();
			$document->addHeadLink(\AKRouter::_($feed . '&type=rss'), 'alternate',
				'rel', $rss);
			$document->addHeadLink(\AKRouter::_($feed . '&type=atom'), 'alternate',
				'rel', $atom);
		}

		// Get the ordering
		$this->order = $model->getState('filter_order', 'id', 'cmd');
		$this->order_Dir = $model->getState('filter_order_Dir', 'DESC', 'cmd');

		// Assign data to the view
		$this->pagination = new \JPagination($model->count(), $model->limitstart, $model->limit);
		$this->category = $this->getModel('Categories');

		// Pass page params
		$this->params = $app->getParams();
		$this->Itemid = $this->input->getInt('Itemid', 0);
		$this->menu = $app->getMenu()->getActive();

		return true;
	}
}