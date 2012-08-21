=== Message Flow ===
Contributors: Captin Shmit
Donate link: http://JoeAnzalone.com
Tags: cover-flow, podcast, rss, syndication
Requires at least: 2.0.2
Tested up to: 3.4.1
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Provides a shortcode that generates a cover flow-like interface for posts in a given category: [message-flow category="11"]

== Description ==

Message Flow provides you with a shortcode you can use on your posts and pages that can be used for displaying your posts or external RSS feeds in a JavaScript widget powered by [ContentFlow](http://www.jacksasylum.eu/ContentFlow/)

The thumbnails used int he widget are taken from the posts' featured images.
If you're using the [PowerPress](http://wordpress.org/extend/plugins/powerpress/) podcasting plugin, the default image will be taken from the iTunes image. PowerPress users can configure this in the "iTunes Feed Settings" section of PowerPress' configuration.
(/wp-admin/admin.php?page=powerpress/powerpressadmin_basic.php)

= Usage =
Show the five latest posts from category number 11:
`[message-flow numberposts="5" category="11"]`

Show the three latest posts from an external feed:
`[message-flow numberposts="3" feed="http://example.com/feed.rss"]`

== Installation ==

1. Upload message-flow.zip via the "Install Plugins" panel (/wp-admin/plugin-install.php)
2. Insert the shortcode into a post or page: `[message-flow numberposts="5" category="11"]`

== Changelog ==

= 1.1 =
* First public release