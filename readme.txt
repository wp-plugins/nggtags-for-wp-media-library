=== Tags for Media Library ===
Contributors: Black 68 Charger,Magenta Cuda
Tags: taxonomy tags,media library,NextGEN Gallery,nggtags,convertor,alternative,replacement
Requires at least: 4.0
Tested up to: 4.2
Stable tag: 1.1.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Features for using taxonomy tags with Media Library. Also converts NextGEN Gallery images to WordPress Media Library images.

== Description ==
TML - [Tags for Media Library](http://nggtagsforwpml.wordpress.com/) - was originally developed for porting NextGEN Gallery 1.9 to WordPress Media Library but has evolved into something that may be useful to anyone trying to use taxonomy tags on WordPress Media Library items. TML supports [dynamically generating galleries](http://nggtagsforwpml.wordpress.com/#nggtags) using a criteria based on taxonomy tags, [multiple tag taxonomies](http://nggtagsforwpml.wordpress.com/#additional-taxonomies) for media items, [bulk assignment/removal](http://nggtagsforwpml.wordpress.com/#bulk-tag-edit) of taxonomy tags to/from media items, filtering the Media Library [by taxonomy tags](http://nggtagsforwpml.wordpress.com/#media-library-for-nggtags), sorting the media items based on a [priority tag](http://nggtagsforwpml.wordpress.com/#bulk-priority-edit), a [search widget](http://nggtagsforwpml.wordpress.com/a-widget-for-searching-the-media-library-by-taxonomy) for media items using a criteria based on taxonomy tags and an [alternate high density view](http://nggtagsforwpml.wordpress.com/#hi-density-media-library) of the Media Library. The website for this plugin is [here](http://nggtagsforwpml.wordpress.com/).  **This version requires WordPress 4.0 and PHP 5.4.**

For NextGEN Gallery 1.9 users: This plugin first converts NextGEN Gallery's image database to a WordPress Media Library image database. After the conversion NextGEN Gallery images and galleries have become WordPress Media Library images and galleries. A WordPress taxonomy 'ngg_tag' is created and the NextGEN Gallery image tags are saved in this taxonomy. This plugin implements the NextGEN Gallery shortcodes [nggtags], [nggallery], [slideshow], [album] and [singlepic] by dynamically translating them into an equivalent standard WordPress [gallery] shortcode so post content with these shortcodes will still work without editing the post content. NextGEN Gallery's sortorder is implemented using a tag taxonomy to hold priorities. **This plugin works with NextGEN Gallery 1.9.13.**

== Installation ==
1. Upload 'nggtags-for-wp-media-library to the '/wp-content/plugins' directory.
2. For NextGEN Gallery 1.9 users: Backup your MySQL WordPress tables.
3. For NextGEN Gallery 1.9 users: Deactivate NextGEN Gallery.
4. Activate this plugin through the 'Plugins' menu in WordPress.
5. For NextGEN Gallery 1.9 users: Run the conversion utility using the Dashboard menu item 'nggtags for Media Library'.

== Frequently Asked Questions ==
= What if you do not have NextGEN Gallery installed? =
No conversion is necessary and the plugin can still be used as implementation of the shortcode 'nggtags' which allows you to have dynamically generated galleries based on tags.

== Screenshots ==
1. Media Library with NGG Tags
2. Edit Media with NGG Tags
3. Search Media using NGG Tags
4. The Conversion Log
5. The Enhanced WordPress Media Library Page
6. The Bulk Tag Taxonomy Editor
7. Search Widget Administrator's Interface
8. Search Widget User's Interface
9. The Bulk Priority Editor
10. High Density Media Library View

== Changelog ==
= 1.1.1 =
* added fade in, explode, rotate and reveal transitions to slideshow
= 1.1 =
* added full browser window mode for slideshow
* added image details popup to slideshow view
* added preserve aspect ratio option
* added shortcode option tml_views to configure gallery mode selection
* conversion from NextGEN Gallery now stores NextGEN galleries and albums in its own post type - "tml_galleries_albums"
= 1.0 =
* implements a slideshow view
* so now also converts the NextGEN Gallery slideshow shortcode
* fix some bugs
= 0.10 =
* fix captions for [nggtags album] shortcode
* fix alternate gallery view to work with themes using non default css box-sizing
* fix various other bugs
* conversion from NextGEN Gallery now also does albums
* conversion from NextGEN Gallery now also does the exclude flag
* use NextGEN Gallery shortcode parameters as HTML class attributes to the converted WordPress gallery
* bulk operations now support attach to post
* admin media view now supports filtering by gallery (i.e. uploaded to)
* another alternate gallery view
* edit/view/delete links now accessible in row view
= 0.8.1.2 =
* show meta data for images in overlay
= 0.8.1.1 =
* enhanced to work better with WordPress 4.0
= 0.8.1 =
* added optional high density view to WordPress standard gallery
= 0.8 =
* added optional high density media list view
= 0.7.1.1 =
* add optional checkboxes overlay for multiple select
* added help for Media Library for NGG Tags page
* reset button for search widget
= 0.7.1 =
* sort images in Media Library for nggtags by priority
* change taxonomy select boxes in Media Library for nggtags to multiple selection
* show existing priorities in Bulk Priority Editor
* make images in Bulk Priority Editor removable
= 0.7 =
* added bulk priority (sort order) editor
* fix problem with backslashes (\) in file paths on Windows servers causing wp_generate_attachment_metadata() to fail in conversion process
= 0.6 =
* added search widget for Media Library
= 0.5 =
* modified WordPress media library page to filter by tags and bulk add/remove tags
= 0.4 =
* support for multiple taxonomies and boolean expressions 
= 0.3.1 =
* change conversion logic to prevent timeout on larger databases 
= 0.3.0.1 =
* fix sort order bug on Media Library screen
= 0.3 =
* Added support for NextGEN Gallery's sort order and shortcodes nggallery and singlepic
= 0.2 =
* Modified Media Library's 'Search Media' to search by NGG Tags or gallery
= 0.1 =
* Initial release.

== Upgrade Notice ==
= 1.1.1 =
* added fade in, explode, rotate and reveal transitions to slideshow
= 1.1 =
* added full browser window mode for slideshow
* added image details popup to slideshow view
* added preserve aspect ratio option
* added shortcode option tml_views to configure gallery mode selection
* conversion from NextGEN Gallery now stores NextGEN galleries and albums in its own post type - "tml_galleries_albums"
= 1.0 =
* implements a slideshow view
* so now also converts the NextGEN Gallery slideshow shortcode
* fix some bugs
= 0.10 =
* fix captions for [nggtags album] shortcode
* fix alternate gallery view to work with themes using non default css box-sizing
* fix various other bugs
* conversion from NextGEN Gallery now also does albums
* conversion from NextGEN Gallery now also does the exclude flag
* use NextGEN Gallery shortcode parameters as HTML class attributes to the converted WordPress gallery
* bulk operations now support attach to post
* admin media view now supports filtering by gallery (i.e. uploaded to)
* another alternate gallery view
* edit/view/delete links now accessible in row view
= 0.8.1.2 =
* show meta data for images in overlay
= 0.8.1.1 =
* enhanced to work better with WordPress 4.0
= 0.8.1 =
* added optional high density view to WordPress standard gallery
= 0.8 =
* added optional high density media list view
= 0.7.1.1 =
* add optional checkboxes overlay for multiple select
* added help for Media Library for NGG Tags page
* reset button for search widget
= 0.7.1 =
* sort images in Media Library for nggtags by priority
* change taxonomy select boxes in Media Library for nggtags to multiple selection
* show existing priorities in Bulk Priority Editor
* make images in Bulk Priority Editor removable
= 0.7 =
* added bulk priority (sort order) editor
* fix problem with backslashes (\) in file paths on Windows servers causing wp_generate_attachment_metadata() to fail in conversion process
= 0.6 =
* added search widget for Media Library
= 0.5 =
* modified WordPress media library page to filter by tags and bulk add/remove tags
= 0.4 =
* support for multiple taxonomies and boolean expressions 
= 0.3.1 =
* change conversion tasks to prevent timeout on larger databases
= 0.3.0.1 =
* fix sort order bug on Media Library screen
= 0.3 =
* Added support for NextGEN Gallery's sort order and shortcodes nggallery and singlepic
= 0.2 =
* Modified Media Library's 'Search Media' to search by NGG Tags or gallery
= 0.1 =
* Initial release.

