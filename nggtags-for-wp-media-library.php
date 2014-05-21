<?php
namespace NggTags_for_Media_Library;

/*
 * Plugin Name:   NextGEN Gallery's nggtags for WordPress's Media Library
 * Plugin URI:    http://nggtagsforwpml.wordpress.com/
 * Description:   An implementation of NextGEN Gallery's shortcode nggtags for WordPress' Media Library.
 * Documentation: http://nggtagsforwpml.wordpress.com/
 * Version:       0.3.0.2
 * Author:        Magenta Cuda
 * Author URI:    http://magentacuda.wordpress.com
 * License:       GPL2
 */
 
/*  
    Copyright 2013  Magenta Cuda

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

add_action( 'admin_notices', function () {
        echo <<<EOD
<div style="padding:10px 20px;border:2px solid red;margin:50px 20px;font-weight:bold;">
NextGEN Gallery's nggtags for WordPress's Media Library: I have discovered a major bug in the restart logic - the plugin was designed to be able to restart after a server timeout and continue - however this is not working correctly and you should wait until I release a corrected version.
</div>
EOD;
    return;
} );

?>

