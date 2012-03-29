=== Plugin Name ===
Contributors: goldenapples, frankservais
Donate link: http://goldenapplesdesign.com/projects/upcoming-events-calendar-plugin/
Tags: calendar, links, widget, events calendar, upcoming events
Requires at least: 2.9.1
Tested up to: 3.1RC3
Stable tag: 0.4.1

This plugin adds a basic "upcoming events" calendar of links to Wordpress.

== Description ==

This plugin is useful for listing speaking engagements, upcoming social events, or other calendar listings which link outside of your site. It adds a special link category for "Events Calendar" with meta fields for location and date. Any links in this category can be displayed through a widget withoption to display all events or only future events, show in ascending/descending order, and options to show or hide link images and descriptions.

The Start Date and End Date fields are stored as text fields and can be entered as any standard php-readable date format (i.e. "Aug 1, 2010 5:00pm", "10/4/11", and "November 2012" are all acceptable, and will be ordered properly and displayed just as they are entered). The End Date field is optional; if included, it will display the dates as a range; if not, only the first date will be displayed.

There is an option to use dates exactly as they are entered (may look better in some cases, but doesn't work very well for non-US English dates) or to use WordPress's localized date formatting.

Styling is customizable through css: each field is given its own <span> class. A basic stylesheet is included in the plugin, but feel free to modify that or delete it and use your own stylesheet to override. You can also copy the default stylesheet to your theme directory or a new directory called `/wp-content/plugins/gad-events-custom/` to preserve any changes to make against future plugin/theme upgrades.


== Installation ==

This section describes how to install the plugin and get it working.

1. Upload the entire folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Modify the default settings in the plugin options page under the 'Settings' manu in WordPress
1. Add links to the "Events Calendar" category, and give them a "Location" (optional) and Start and End "Date"
1. View and manage all your events in the "Events Calendar" page under the 'Links' menu 
1. Use the "External Events Calendar" widget in any widgetized area in your site, or
1. Use the shortcode `[eventslisting]` in a post or page (see the FAQ for parameters that can be passed).

== Frequently Asked Questions ==

= Can the widget also show link descriptions and images? =

Short answer, yes. Choose those selections from the widget options form.

= What date formats are acceptable? =

This plugin uses the php `strtotime()` function to process the dates entered, which can handle a lot of date formats. "Next Monday", for example, will be correctly computed on most servers (but will always refer to the next Monday in the future, not the next Monday from the time someone added the link). '10/5/10' will be interpreted as October 5, not May 10. But "10 May" will work as expected. PHP does not, to the best of my knowledge, recognize non-English dates in text format very well. "1 enero 11" will probably fail.

If you are having problems having your dates be ordered properly, it is most likely because the `strtotime()` function is not able to recognize or parse your input. Try entering your dates in a simple format like MM/DD/YYYY and using the WordPress date format settings to display them.

= Can I have more than one category of Events? Can I use the widget twice, pulling from a different listing of events in each case? =

Yes, you can add an additional link category and use it to store events. If the category you create has an id of `11`, for example, you can pull events from your own category using the shortcode `[eventslisting link_category=11]`. At the moment, the widget will only pull from the default "Events Calendar" link category. If you create your own category, you will have to manage it yourself - the "Events Calendar" submenu page under "Links" will only show events in the default category.

= Can I override the default CSS? =

As of version 0.3, you have the option to include or not include the default CSS styling. Take a look at the css file provided to see the class names that need to be styled. If you define those in the `style.css` of your main theme, you can safely disable the default CSS, while will make your page loads a wee bit quicker.

As of version 0.4, you can also copy the existing `gad-events-calendar.css` file to your theme directory and edit it there, OR create a new directory under `/wp-content/plugins` called `gad-events-custom` and copy the stylesheet there. This can help protect your style changes in case of updating the plugin in the future.

= What options does the shortcode `[eventslisting]` take? =

You can pass any of the same options to the shortcode that you can select in the widget. Here is the syntax for each of the options:

* `display_title` (false|string)  The title, if any, to show before the list of events. Will be wrapped in an `<h2>` tag if present.
* `show_past_events` (true|false) Present for backwards compatability. Use `show_events` instead.
* `show_events` ("upcomingonly"|"allevents"|"pastonly") Pretty much what it implies. Defaults to "upcomingonly".
* `show_descriptions` (true|false) Whether or not to show descriptions in the list of events. Defaults to true.
* `show_images` (true|false) Whether or not to show link images, if present, in the list of events. Defaults to false.
* `orderby` (ASC|DESC) ASC: Order events current to future, or past to future. DESC: Order events future to current (or past). Defaults to ASC.
* `limit` (int) If present, the maximum number of events to show in the listing.
* `link_category` (int) A different category to pull events from, other than the default "Events Calendar" category.

So for example, if you want to include the five events farthest in the future in a post, this would be the shortcode to use: 

`[eventslisting display_title="Planning Ahead" orderby=DESC limit=5]`


== Screenshots ==

1. Basic widget options


== Changelog ==

= 0.4 =

New features and bugfixes 2/4/11

* Added "past only" option, to show only past events.
* New options for behavior of current date (can choose to have upcoming events drop off the calendar when the start date is past, when the end date is past, or to include all events from the current day, or week). This addresses a bug which was reported where events which weren't given a time would drop off of the calendar on the day they were scheduled.
* Fixed bug within shortcode that would always display at the top of post content.
* Added custom date formatting option using any PHP date format string.
* Removed deprecated functions

= 0.3.1 =

New features 8/19/10

* Added shortcode to display listings on posts or pages
* Added 'limit' feature to limit the number of events which appear in widget or shortcode.

= 0.3 =

New features and bugfixes 8/8/10.

* Added settings page and submenu page under "Links" menu to manage events.
* Added the option to use WordPress date format setting
* Fixed bug where if two events with the same starting date were added, only one would show
* Fixed markup bug which left an unclosed div if no events were displayed 
* Improved internationalization - now everything should be translatable except the category name "Events Calendar"
* Beautified the code, so you won't be ashamed to take it home to your mama


= 0.2.2 =

Minor bugfix patch 6/14/10 - fixed issue where plugin would fail if it wasn't in a directory named `wp-content/plugins`. 

= 0.2.1 =

Minor bugfix 6/10/10. Replaced php shorttags from plugin file with full tags so that plugin will not give syntax errors on php installations with shorttags disabled. 


= 0.2 =

New features and bugfix 5/19/10.

* Fixed bug where custom meta did not save properly on adding new link, only when editing existing link.
* Previously, the custom fields metabox was only available once links had been added to the "Events Calendar" category - so you had to create a link & assign it to that category, then go back and edit it to access the custom fields. The metabox is now available to all links, regardless of category.
* Improved regular expression processing of date ranges (i.e. ranges of dates will now show as "May 27-30, 2010" or "June 26-July 1, 2010"; not "May 27, 2010 - May 30, 2010" or "June 26, 2010 - July 1, 2010").
* Added option to display link images.


= 0.1 =

Initial release 5/11/10.


