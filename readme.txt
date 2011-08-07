=== Upload Media by Zip ===
Contributors: trepmal 
Donate link: http://kaileylampert.com/donate/
Tags: upload, media library, zip
Requires at least: 2.8
Tested up to: 3.2.1
Stable tag: trunk

Upload a zip archive and let WP unzip it and attach everything to a page/post (or not).

== Description ==

Upload a zip archive and let WP unzip it and attach everything to a page/post (or not).

New plugin. Please report bugs to trepmal (at) gmail (dot) com. Thanks!

* [I'm on twitter](http://twitter.com/trepmal)

== Installation ==

This is a new plugin. Only those comfortable using and providing feedback for new plugins should use this.
If you don't know how to install a plugin, this plugin isn't for you (yet).

== Frequently Asked Questions ==

= The tabs in the media pop-up are all crazy =
Sounds like you're using 2.8. Update.

= The zip file uploads, but the contents aren't extracted =
Try this: Open up the upload-media-by-zip.php file and locate <code>WP_Filesystem()</code> (line 257). Surrounding it are three lines labeled 1, 2, and three. Uncomment those.

== Screenshots ==

1. Original uploader (good if you don't want to attach images to another post)
2. Zip uploader media button
3. Second uploader

== Changelog ==

= 0.9 =
* Getting ready for translation, POT file may already be available
* Bugfix: can now delete temporary upload despite hidden files

= 0.8.1 =
* Bugfix: now shows correct message on failed extraction

= 0.8 =
* Experimental "Insert all into post" feature (feedback appreciated)
* List attachment IDs with 'success' message

= 0.7 =
* fun with recursion - funky stuff should be fixed - sorry for the double update

= 0.6 =
* zipped folders (any depth) now good-to-go
* file extensions removed from title, like core uploader

= 0.5 =
* allows contents of a zipped folder to be added successfully to the media library

= 0.4 =
* linked Upload page to better capability (upload_files)
* works with 2.8! (media upload tabs are wacky in 2.8, but I'm not going to fix it... because it's 2.8)
* minor wording changes (sticking with "upload zip archive")

= 0.3 =
* fixed compatibility with Quick Press

= 0.2 =
* added zip uploader to media pop-up
* first WP.org release

= 0.1 =
* just getting started

== Upgrade Notice ==

= 0.8 =
New experimental "Insert all into post" feature. Feedback appreciated.

