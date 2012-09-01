=== Message Flow ===
Contributors: Joe Anzalone
Donate link: http://JoeAnzalone.com
Tags: cover-flow, podcast, rss, syndication
Requires at least: 2.0.2
Tested up to: 3.4.1
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Message Flow generates a CoverFlow-like interface for your blog posts.

== Description ==

Message Flow provides you with a shortcode you can use on your posts and pages to display your posts in a JavaScript widget powered by [ContentFlow](http://www.jacksasylum.eu/ContentFlow/).

It even supports podcast integration with [PowerPress](http://wordpress.org/extend/plugins/powerpress/) and external podcast feeds.


= Usage =
Show the ten latest posts:
`[message-flow]`

Show the ten latest podcast episodes:
`[message-flow podcasts_only="true"]`

Show the five latest posts from category number 11:
`[message-flow numberposts="5" category="11"]`

Show the three latest posts from an external feed:
`[message-flow numberposts="3" feed="http://example.com/feed.rss"]`

Disable excerpts:
`[message-flow show_excerpt="false"]`

= Defining the thumbnails (Album Art) =
The thumbnails used in the widget are taken from the posts' featured images. In the event that a podcast does not have a featured image available, Message Flow will check the following locations for a suitable fallback image:

1. A URL to the fallback image in the shortcode: `[message-flow fallback_image="http://example.com/default_image.png"]`
2. If you're using the [PowerPress](http://wordpress.org/extend/plugins/powerpress/) podcasting plugin, the default image will be taken from the iTunes image. PowerPress users can configure this in the "iTunes Feed Settings" section of PowerPress' configuration. (/wp-admin/admin.php?page=powerpress/powerpressadmin_basic.php)
3. In your theme's directory. Just make sure it's 165 pixels square and named "unknown-album_165.png"

== Installation ==

Extract the zip file and drop the contents in the wp-content/plugins/ directory of your WordPress installation and then activate the plugin from the "Plugins" page.

== Screenshots ==
1. Simply add the shortcode to your post or page
2. The Message Flow widget will be placed wherever you put the shortcode

== Changelog ==

= 1.1.6 =
* Fixed undefined variable warning when WP_DEBUG is enabled

= 1.1.5 =
* Automatically truncate long posts so widget doesn't become too large
* Added "show_excerpt" parameter
* Fixed "Invalid argument supplied" warning

= 1.1.4 =
* Added support for regular posts that lack an associated podcast episode
* Added "podcasts_only" parameter: `[message-flow podcasts_only="true"]`

= 1.1.3 =
* Fixed bug where post content would be inaccurate for some episodes within widget

= 1.1.2 =
* Fixed WP_DEBUG errors that appear when site has no podcast episodes

= 1.1.1 =
* Fixed WP_DEBUG errors
* Added screenshots
* Added more usage examples

= 1.1 =
* First public release
