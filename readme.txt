=== nggtags for WordPress Media Library ===
Contributors: Magenta Cuda
Tags: NextGEN Gallery,nggtags,convertor,alternative,replacement
Requires at least: 3.6
Tested up to: 3.9.1
Stable tag: 0.3.0.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Converts NextGEN Gallery images to WordPress Media Library images. Has an alternative implementation of NextGEN Gallery's nggtags shortcode.

== Description ==
This plugin first converts NextGEN Gallery's image database to a WordPress Media Library image database. After the conversion NextGEN Gallery images and galleries have become WordPress Media Library images and galleries. NextGEN albums are ignored. A WordPress taxonomy 'ngg_tag' is created and the NextGEN Gallery image tags are saved in this taxonomy. This plugin also directly implements the 'nggtags' shortcode so post content with the 'nggtags' shortcode will still work without editing the post content. NextGEN Gallery's sortorder is implemented using a tag taxonomy to hold priorities. The website for this plugin is [here](http://nggtagsforwpml.wordpress.com/). **This plugin works with NextGEN Gallery 1.9.13 and requires at least PHP 5.4.**

== Installation ==
1. Upload 'nggtags-for-wp-media-library to the '/wp-content/plugins' directory.
2. Backup your MySQL WordPress tables.
3. Deactivate NextGEN Gallery.
4. Activate this plugin through the 'Plugins' menu in WordPress.
5. Run the conversion utility using the Dashboard menu item 'nggtags for Media Library'.

== Frequently Asked Questions ==
= What if you do not have NextGEN Gallery installed? =
No conversion is necessary and the plugin can still be used as implementation of the shortcode 'nggtags' which allows you to have dynamically generated galleries based on tags.

== Screenshots ==
1. Media Library with NGG Tags
2. Edit Media with NGG Tags
3. Search Media using NGG Tags
4. The Conversion Log

== Changelog ==
= 0.3.0.1 =
fix sort order bug on Media Library screen
= 0.3 =
* Added support for NextGEN Gallery's sort order and shortcodes nggallery and singlepic
= 0.2 =
* Modified Media Library's 'Search Media' to search by NGG Tags or gallery
= 0.1 =
* Initial release.

== Upgrade Notice ==
= 0.3.0.1 =
fix sort order bug on Media Library screen
= 0.3 =
* Added support for NextGEN Gallery's sort order and shortcodes nggallery and singlepic
= 0.2 =
* Modified Media Library's 'Search Media' to search by NGG Tags or gallery
= 0.1 =
* Initial release.
