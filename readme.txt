=== Upload Media by Zip ===
Contributors: trepmal 
Donate link: http://kaileylampert.com/donate/
Tags: upload, media library, zip
Requires at least: 2.8
Tested up to: 3.2-beta2
Stable tag: trunk

Upload a zip archive and let WP unzip it and attach everything to a page/post

== Description ==

Upload a zip archive and let WP unzip it and attach everything to a page/post.

New plugin. Please report bugs to trepmal (at) gmail (dot) com. Thanks!

* [I'm on twitter](http://twitter.com/trepmal)

== Installation ==

This is a new plugin. Only those comfortable using and providing feedback for new plugins should use this.
If you don't know how to install a plugin, this plugin isn't for you (yet).

== Frequently Asked Questions ==

= The archive was uploaded, but none of my files! =
Up until v0.5, the uploader expected the zip file to contain files only - no folders. Meaning, if you put your files in a folder, then zipped the folder, it wouldn't work correctly.
You can however, find those unzipped files inside the `temp` folder in the plugin's directory.

= I'm using 0.5 and the archive was uploaded, but none of my files! =
Your zip file shouldn't have any files deeper than 1 directory. (Zip some files, or zip a folder that has some files in it, but not a folder in a folder...)

= The tabs in the media pop-up are all crazy =
Sounds like you're using 2.8. Update.

== Screenshots ==

1. Original uploader (good if you don't want to attach images to another post)
2. Zip uploader media button
3. Second uploader

== Changelog ==

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