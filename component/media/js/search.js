/*
 * @package   DocImport
 * @copyright Copyright (c)2011-2021 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

/**
 * Setup (required for Joomla! 3)
 */
if (typeof (akeeba) == "undefined")
{
    var akeeba = {};
}

if (typeof (akeeba.jQuery) == "undefined")
{
    akeeba.jQuery = jQuery.noConflict();
}

if (typeof akeeba.DocImport == "undefined")
{
    akeeba.DocImport = {}
}

if (typeof akeeba.DocImport.Search == "undefined")
{
    akeeba.DocImport.Search = {
        "labelAllSections": ""
    }
}

akeeba.DocImport.Search.sectionsChange = function ()
{
    (function ($)
    {
        var forDisplay = [];
        var element    = $("#dius-searchutils-areas");
        var selections = element.val();

        element.children().each(function (name, val)
        {
            if (selections != null)
            {
                if (selections.indexOf(val.value) >= 0)
                {
                    forDisplay.push(val.text);
                }
            }
        });

        if (selections != null)
        {
            if (selections.indexOf("*") != -1)
            {
                forDisplay = [akeeba.DocImport.Search.labelAllSections];
            }
        }

        if (!forDisplay.length)
        {
            forDisplay = [akeeba.DocImport.Search.labelAllSections];
        }

        $("#dius-searching-areas").html(forDisplay.join(", "));

    }(akeeba.jQuery));
};

akeeba.DocImport.Search.rememberTabs = function ()
{
    (function ($)
    {
        $(document).find("h4.panel-title a[data-toggle=\"collapse\"]").on("click", function (e)
        {
            // Store the selected tab href in localstorage
            window.localStorage.setItem("accordion-href", $(e.target).attr("href"));
        });

        var activateSlide = function (href)
        {
            var $target = $(href);

            // Is it already active?
            if ($target.hasClass("in"))
            {
                return;
            }

            var $el = $("h4.panel-title a[data-toggle=\"collapse\"]a[href*=" + href + "]");
            $el.click();
        };

        var hasSlide = function (href)
        {
            return $("h4.panel-title a[data-toggle=\"collapse\"]a[href*=" + href + "]").length;
        };

        if (localStorage.getItem("accordion-href"))
        {
            // When moving from accordion area to a different view
            if (!hasSlide(localStorage.getItem("accordion-href")))
            {
                localStorage.removeItem("accordion-href");
                return true;
            }

            // Clean default slides
            $("h4.panel-title a[data-toggle=\"collapse\"]").parent().removeClass("active");
            var slideHref = localStorage.getItem("accordion-href");

            // Add active attribute for selected tab indicated by url
            activateSlide(slideHref);

            // Check whether internal tab is selected (in format <slideName>-<id>)
            var seperatorIndex = slideHref.indexOf("-");

            if (seperatorIndex !== -1)
            {
                var singular = slideHref.substring(0, seperatorIndex);
                var plural   = singular + "s";

                activateSlide(plural);
            }
        }
    }(akeeba.jQuery));
};

window.addEventListener("DOMContentLoaded", function ()
{
    akeeba.DocImport.Search.labelAllSections = Joomla.Text._("COM_DOCIMPORT_SEARCH_LBL_ALLAREAS");
    akeeba.DocImport.Search.sectionsChange();
    setTimeout(function ()
    {
        var elCollapsible = document.getElementById("dius-searchutils-collapsible");
        elCollapsible.classList.add("collapse");
    }, 10);
    setTimeout(akeeba.DocImport.Search.rememberTabs, 100);
});
