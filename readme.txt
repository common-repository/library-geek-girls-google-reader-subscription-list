=== LibraryGeekGirl's Google Reader Subscription List ===
Contributors: druthb
Tags: google reader
Requires at least: 2.7
Tested up to: 3.4.2
Stable tag: 1.0
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl-3.0.html

This plugin enables you to place links on a page or post, using a shortcode, from your Google Reader subscriptions.

== Description ==

This plugin lets you choose to include or exclude links from your Google Reader subscriptions by adding parameters 
to the shortcode.

`[lgg_reader]`

will include all subscriptions, from all labels.

`[lgg_reader include="label1,label 2"]`

will only list subscriptions in label1 and label 2. These are case-insensitive, but must be spaced and punctuated
just like they are in Google Reader.  

`[lgg_reader exclude="label1"]`

will list everything *but* label1 items.

Some notes on this:

* an "include" directive will moot an "exclude" directive, in every case, and only include what is listed.
* if you forget and use include="all" or include="*", it will DWYM, which is include="".
* links that have no label in Google Reader are placed in the group "unfiled".

`[lgg_reader include="friends" hide_header=1]`

The hide_header directive is useful if you're only listing one label; it hides the header to the bulleted list.

== Installation ==

1. Upload the lgg-google-reader-list directory to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Enter your Google Reader username and password on the options page
1. Place shortcodes in your pages and posts

== Frequently Asked Questions ==

== Screenshots ==

== Changelog ==

= 1.0 =
* Initial release

== Upgrade Notice ==
