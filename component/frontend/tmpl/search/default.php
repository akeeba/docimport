<?php
/*
 * @package   DocImport
 * @copyright Copyright (c)2011-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

/** @var \Akeeba\Component\DocImport\Site\View\Search\HtmlView $this */

// Get the submission URL
$returnUrl = base64_encode(Uri::current());
HTMLHelper::_('formbehavior.chosen', 'select.fancySelect')
?>

<?= $this->loadPosition('docimport-search-top'); ?>
<?php if (!empty($this->search)): ?>
<?= $this->loadPosition('docimport-search-results-top'); ?>
<?php else: ?>
<?= $this->loadPosition('docimport-search-form-top'); ?>
<?php endif; ?>

<?php if ($this->headerText): ?>
	<h3><?= $this->headerText ?></h3>
<?php endif; ?>

<form action="<?= Route::_('index.php?option=com_docimport&view=search') ?>" id="dius-form"
	  method="post">
	<div id="dius-searchform">
		<label class="visually-hidden" for="dius-search">
			<?= Text::_('COM_DOCIMPORT_SEARCH_LBL_SEARCHSUPPORT') ?>
		</label>
		<div class="input-group mb-3">
			<input type="text" class="form-control" id="dius-search" name="search"
				   placeholder="<?= Text::_('COM_DOCIMPORT_SEARCH_LBL_SEARCHSUPPORT') ?>"
				   value="<?= htmlentities($this->search); ?>"
			>
			<button class="btn btn-primary" type="submit">
				<span class="icon icon-search"></span>
				<span class="visually-hidden">
					<?= Text::_('JSEARCH_FILTER') ?>
				</span>
			</button>
		</div>
	</div>

	<div id="dius-searchutils">
		<div id="dius-searching-container" class="d-flex">
			<div class="flex-grow-1">
				<label id="dius-searching-label" for="dius-searching-areas">
					<?= Text::_('COM_DOCIMPORT_SEARCH_LBL_SEARCHINGSECTIONS') ?>
				</label>
				<span id="dius-searching-areas"></span>
			</div>
			<span id="dius-searching-toggle" class="float-end">
				<a aria-controls="dius-searchutils-collapsible" aria-expanded="false"
				   data-bs-toggle="collapse" href="#dius-searchutils-collapsible">
					<?= Text::_('COM_DOCIMPORT_SEARCH_LBL_SEARCHTOOLS'); ?>
				</a>
			</span>
		</div>

		<div id="dius-searchutils-collapsible">
			<div class="card" id="dius-searchutils-groupcontainer">
				<div class="card-body">
					<div class="form-group row">
						<label class="col-sm-3" for="dius-searchutils-areas">
							<?= Text::_('COM_DOCIMPORT_SEARCH_LBL_SECTIONS'); ?>
						</label>
						<div class="col-sm-9">
							<?= HTMLHelper::_('select.genericlist', $this->areaOptions, 'areas[]', [
								'multiple' => 'multiple',
								'class' => 'fancySelect form-control',
								'onchange' => 'akeeba.DocImport.Search.sectionsChange(this)'
							], 'value', 'text', $this->areas, 'dius-searchutils-areas') ?>
						</div>
					</div>
				</div>

			</div>
		</div>

		<?php if ($this->troubleshooterLinks):?>
		<div class="clearfix"></div>

		<div id="dius-troubleshoot-links">
			<div class="col-lg-6 col-md-12">
				<?php
				for ($i = 0; $i < count($this->troubleshooterLinks); $i += 2):
					[$text, $link] = explode('|', $this->troubleshooterLinks[$i]);
					?>
					<a href="<?= $link ?>"><?= $text?></a>
				<?php endfor;?>
			</div>
			<div class="col-lg-6 col-md-12">
				<?php
				for ($i = 1; $i < count($this->troubleshooterLinks); $i += 2):
					[$text, $link] = explode('|', $this->troubleshooterLinks[$i]);
					?>
					<a href="<?= $link ?>"><?= $text?></a>
				<?php endfor;?>
			</div>
		</div>

		<div class="clearfix"></div>
		<?php endif;?>
	</div>

	<?= HTMLHelper::_('form.token') ?>
</form>

<?php if (empty($this->search)): ?>
<?= $this->loadPosition('docimport-search-bottom'); ?>
<?= $this->loadPosition('docimport-search-form-bottom'); ?>
<?php
    return;
    endif;
?>

<?php if (!$this->pagination->pagesTotal): ?>

<div class="row col-xs-12">
	<div class="alert alert-danger">
		<?= Text::_('COM_DOCIMPORT_SEARCH_ERR_NOTHINGFOUND'); ?>
	</div>
</div>

<?php else:
	// Get a smart active slider
	$active = 'docimport';

	if ($this->items['ats']['count'] && ($this->items['ats']['count'] >= $this->limitStart))
	{
		$active = 'ats';
	}

	if ($this->items['video']['count'] && ($this->items['video']['count'] >= $this->limitStart))
	{
		$active = 'video';
	}
?>
<div class="accordion my-3" id="dius-results-accordion">
	<?php foreach ($this->items as $section => $data):
		// Skip over sections with no results
		if (!$data['count'])
		{
			continue;
		}

		// Skip over sections with less result pages than the current page
		if ($this->limitStart > $data['count'])
		{
			continue;
		}

		$accordionButtonClass = ($section == $active) ? '' : 'collapsed';
		$accordionShow = ($section == $active) ? 'show' : '';
		$ariaExpanded = ($section == $active) ? 'true' : 'false';
		?>
		<div class="accordion-item">
			<h3 class="accordion-header" id="dius-results-slide-<?= $section ?>-head">
				<button class="accordion-button <?= $accordionButtonClass ?>" type="button"
						data-bs-toggle="collapse" data-bs-target="#dius-results-slide-<?= $section ?>"
						aria-expanded="<?= $ariaExpanded ?>" aria-controls="dius-results-slide-<?= $section ?>">
					<?= Text::_('COM_DOCIMPORT_SEARCH_SECTION_' . $section) ?>
				</button>
			</h3>
			<div id="dius-results-slide-<?= $section ?>"
				 class="accordion-collapse collapse <?= $accordionShow ?>"
				 role="tabpanel"
				 aria-labelledby="dius-results-slide-<?= $section ?>-head"
				 data-bs-parent="#dius-results-accordion"
			>
				<div class="accordion-body">
					<?php
					// Render the section using template sectionName, e.g. joomla
					try
					{
						$this->sectionData = $data;

						echo $this->loadTemplate($section);
					}
					catch (Throwable $e)
					{
						echo $e->getMessage(); die;
					}
					?>
				</div>
			</div>
		</div>
	<?php endforeach; ?>
</div>

<div class="row col-xs-12 form">
	<div class="pagination">
		<p class="counter pull-right"> <?= $this->pagination->getPagesCounter(); ?> </p>
		<?= $this->pagination->getPagesLinks(); ?>
	</div>
</div>

<?php endif; ?>

<?= $this->loadPosition('docimport-search-results-bottom'); ?>
<?= $this->loadPosition('docimport-search-bottom'); ?>
