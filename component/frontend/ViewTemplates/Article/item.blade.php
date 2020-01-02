<?php
/**
 * @package   DocImport
 * @copyright Copyright (c)2011-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

// Protect from unauthorized access
defined('_JEXEC') or die();

?>
<div class="docimport docimport-page-article">
	@if($this->showPageHeading)
		<div class="page-header">
			<h2>
				@lang($this->pageHeading)
			</h2>
		</div>
	@endif

	@if ($this->contentPrepare)
		@jhtml('content.prepare', $this->item->fulltext)
	@else
		{{ $this->item->fulltext }}
	@endif
</div>

<script type="text/javascript">
	(function ($) {
		$(document).ready(function () {
			$('pre.programlisting').each(function (i, e) {
				language = $(e).attr('data-language');
				content = $(e).text();

				if (!language) {
					return;
				}

				result = hljs.highlight(language, content);
				$(e).html(result.value);
			});
		});
	})(window.jQuery);

</script>
