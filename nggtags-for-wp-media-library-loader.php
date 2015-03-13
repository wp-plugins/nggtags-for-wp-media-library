<?php

/*
 * Plugin Name:   Tags for Media Library
 * Plugin URI:    http://nggtagsforwpml.wordpress.com/
 * Description:   Some features for using taxonomy tags with WordPress Media Library.
 * Documentation: http://nggtagsforwpml.wordpress.com/
 * Version:       0.10
 * Author:        Black 68 Charger, Magenta Cuda  
 * Author URI:    http://nggtagsforwpml.wordpress.com/
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

# The check for version is in its own file since if the file contains PHP 5.4 code an ugly fatal error will be triggered instead

list( $major, $minor ) = sscanf( phpversion(), '%D.%D' );
$tested_major = 5;
$tested_minor = 4;
if ( !( $major > $tested_major || ( $major == $tested_major && $minor >= $tested_minor ) ) ) {
    add_action( 'admin_notices', function () use ( $major, $minor, $tested_major, $tested_minor ) {
        echo <<<EOD
<div style="padding:10px 20px;border:2px solid red;margin:50px 20px;font-weight:bold;">
    NextGEN Gallery's nggtags for WordPress's Media Library will not work with PHP version $major.$minor;
    Please uninstall it or upgrade your PHP version to $tested_major.$tested_minor or later.
</div>
EOD;
    } );
    return;
}

if ( isset( $wp_version ) ) {
    list( $major, $minor ) = sscanf( $wp_version, '%D.%D' );
    $tested_major = 4;
    $tested_minor = 0;
    if ( !( $major > $tested_major || ( $major == $tested_major && $minor >= $tested_minor ) ) ) {
        add_action( 'admin_notices', function () use ( $major, $minor, $tested_major, $tested_minor ) {
            echo <<<EOD
<div style="padding:10px 20px;border:2px solid red;margin:50px 20px;font-weight:bold;">
    NextGEN Gallery's nggtags for WordPress's Media Library will not work with WordPress version $major.$minor;
    Please uninstall it or upgrade your WordPress version to $tested_major.$tested_minor or later.
</div>
EOD;
        } );
        return;
    }
}

# ok to start loading PHP 5.4 code

require( dirname( __FILE__ ) . '/nggtags-for-wp-media-library.php' );
