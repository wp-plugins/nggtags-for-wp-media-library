<?php

/*
 * Plugin Name:   NextGEN Gallery's nggtags for WordPress's Media Library
 * Plugin URI:    http://nggtagsforwpml.wordpress.com/
 * Description:   An implementation of NextGEN Gallery's shortcode nggtags for WordPress' Media Library.
 * Documentation: http://nggtagsforwpml.wordpress.com/
 * Version:       0.2
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

global $wpdb;
 
// NextGEN Gallery MySQL Table names defined here.

$ntfwml_ngg_pictures  = $wpdb->prefix . 'ngg_pictures';
$ntfwml_ngg_galleries = $wpdb->prefix . 'ngg_gallery';

// Are there any NextGEN Gallery pictures?

$ntfwml_ngg_pictures_count = $wpdb->get_var( "SELECT COUNT(*) FROM $ntfwml_ngg_pictures" );

// What is the current state of the update?

$ntfwml_options = get_option( 'nggtags_for_wp_media_library', array() );

if ( $ntfwml_ngg_pictures_count && ( !$ntfwml_options || !isset( $ntfwml_options['status'] )
    || $ntfwml_options['status'] != 'update done' ) ) {
    /*
     * Update not started or not completed so start or continue the update.
     *
     * If the update is interrupted the update is re-startable since there is enough state in the database.
     */
    add_action( 'admin_menu', 'register_nggtags_for_media_library_menu_page' );
    
    function register_nggtags_for_media_library_menu_page() {
        add_menu_page( 'Update for Using nggtags on WordPress\'s Media Library', 'nggtags for Media Library', 'manage_options',
        'nggtags_for_media_library', 'nggtags_for_media_library_menu_page' );
    }
    
    function nggtags_for_media_library_menu_page() {
        $nextgen_gallery_activated = class_exists( 'nggLoader' );
        $options = get_option( 'nggtags_for_wp_media_library', array() );
        if ( !$options || !isset( $options['status'] ) ) {
            $continue = 'Start ';
        } else {
            $continue = 'Restart ';
        }
?>
<div style="padding:10px 20px;">
<h1>NextGEN Gallery's nggtags for WordPress's Media Library</h1>
This feature lets you use the shortcode 'nggtags' on Wordpress's Media Library images.
If you have an existing NextGEN Gallery database you must update your database.
This update will copy NextGEN Gallery's pictures and galleries to WordPress's Media Library.
(NextGEN Gallery's albums are ignored.)
After this update NextGEN Gallery will not work.
NextGEN pictures and galleries will now exists as WordPress Media Library images and galleries
and the shortcode 'nggtags' will work with the Media Library images.
Except for 'gallery' and 'album' the parameters to the shortcode 'nggtags' are parameters to
WordPress's 'gallery' shortcode in particular you may need to specify
'<strong>link=&quot;file&quot;</strong>' to make some lightboxes to work.
<strong>It is imperative that you backup your database before proceeding!</strong>
<div style="border:2px solid black;margin:20px;padding:10px 20px;"><small>
<h4>The Fine Print</h4>
The update actually only copies the NextGEN Gallery picture files
and leaves the NextGEN picture directory - usually 'wp_content/gallery' -
and the NextGEN MySQL tables - wp_ngg_pictures, wp_ngg_gallery and wp_ngg_album - intact.
However, new rows are inserted into the WordPress MySQL tables - wp_posts and wp_postmeta -
and rows are deleted from and inserted into the WordPress MySQL table - wp_term_relationships.
So most of NextGEN Gallery will still work only the tags will stop working as their data in
wp_term_relationships are changed to work with the copied pictures in WordPress's Media Library.
If you are satisfied with 'NextGEN Gallery's nggtags for WordPress's Media Library' you
can delete NextGEN Gallery's picture directory - usually 'wp-content/gallery' -
and the NextGEN Gallery's MySQL tables - wp_ngg_pictures, wp_ngg_gallery and wp_ngg_album - to
save space on your server.
</small></div>
<?php
        if ( $nextgen_gallery_activated ) {
?>
<div style="border:2px solid red;margin:20px;padding:10px 20px;">
NextGEN Gallery is activated. You must deactivate NextGEN Gallery before you can run the update.
</div>
<?php
        }
        // If there is any history display it.
        if ( !empty( $options['messages'] ) ) {
?>
<div style="border:2px solid black;margin=20px;padding=20px;"><p><small><ul>
<h2>Previous Status Messages</h2>
<?php
            foreach ( $options['messages'] as $message ) {
                echo "<li>$message</li>";
            }
?>
</ul></small></p></div>
<?php
        }
?>
<button id="ntfwml_update_button" type="button"<?php if ( $nextgen_gallery_activated ) { echo 'disabled'; } ?>>
    <?php echo $continue ?>Update for nggtags on Media Library</button>
<div id="ntfwml_status" style="border:2px solid black;margin:20px;padding:20px;display:none;"><p><small>
<h2>Status Messages</h2><ul>
<li>Update started. This may take some time ...</li>
</ul></small></p></div>
</div>
<script type="text/javascript" >
jQuery( "#ntfwml_update_button" ).click( function() {
    var confirmed = confirm(
        "Did you really backup your database? "
        + "Pressing OK will make permanent changes to your database. "
        + "After these changes are made NextGEN Gallery will no longer work. "
    );
    if ( confirmed ) {
        this.disabled = true;
        jQuery( "#ntfwml_status" ).css( "display", "block" );
        var data = {
            'action': 'update_for_nggtags_on_media_library'
        };
        jQuery.post(
            ajaxurl,
            data,
            function ( response ) {
                jQuery( "#ntfwml_status ul" ).html( response );
            }
        );
    }
} );
</script>
<?php
    }
    
    if ( is_admin() ) {
        add_action( 'wp_ajax_update_for_nggtags_on_media_library', 'update_for_nggtags_on_media_library' );
        
        function update_for_nggtags_on_media_library() {
        
            global $ntfwml_ngg_pictures;
            global $ntfwml_ngg_galleries;
            global $ntfwml_options;
            
            /*
             * register_nextgen_gallery_taxonomy() is copied from NextGEN Gallery's nggallery.php file. It is neccessary
             * to register the NextGEN Gallery taxonomy so we can use the WordPress functions: wp_get_object_terms() and 
             * wp_delete_object_term_relationships() which both require a registered taxonomy.
             */
            
            function register_nextgen_gallery_taxonomy() {
                global $wp_rewrite;

                // Register the NextGEN taxonomy
                $args = array(
                        'label' => __('Picture tag', 'nggallery'),
                        'template' => __('Picture tag: %2$l.', 'nggallery'),
                        'helps' => __('Separate picture tags with commas.', 'nggallery'),
                        'sort' => true,
                        'args' => array('orderby' => 'term_order')
                        );

                register_taxonomy( 'ngg_tag', 'nggallery', $args );
            }
            
            /*
             * update_galleries creates a page post for every NextGEN Gallery gallery that will be used to attach the corresponding
             * pictures. This will be done later by update_pictures(). The original gallery id is saved as post meta data with
             * meta_key 'pre_update_ngg_gid'.
             *
             * If update_galleries() is interrupted the function is re-startable since there is enough state in the database.
             */

             function update_galleries( $ngg_galleries, &$options ) {
                global $wpdb;
                $done_gids = $wpdb->get_col( "SELECT meta_value from $wpdb->postmeta WHERE meta_key = 'pre_update_ngg_gid'",
                    OBJECT_K );
                $results = $wpdb->get_results( "SELECT gid, name, title, galdesc, previewpic, author FROM $ngg_galleries" );
                foreach ( $results as $result ) {
                    // skip stuff previously done.
                    if ( !in_array( $result->gid, $done_gids ) ) {
                        // create a page to be used as an attachment host
                        $post = array(
                            'post_author'  => $result->author,
                            'post_content' => '[gallery]',
                            'post_excerpt' => $result->galdesc,
                            'post_name'    => $result->name,
                            'post_status'  => 'publish',
                            'post_title'   => $result->title,
                            'post_type'    => 'page'
                        );
                        if ( $id = wp_insert_post( $post ) ) {
                            // previewpic cannot be correctly done now since new pid of previewpic is not known so mark it
                            update_post_meta( $id, '_thumbnail_id', 'TODO:' . $result->previewpic );
                            // also save the original NextGEN Gallery gallery id
                            update_post_meta( $id, 'pre_update_ngg_gid', $result->gid );
                        }
                    }
                }
                return true;
            }
    
            /*
             * move_picture_to_wordpress_upload_directory() is a helper function for update_pictures(). 
             * The main difficulty is if the file name already exists we need a new name.
             */
             
            function move_picture_to_wordpress_upload_directory( $file, &$options ) {
                $uploads = wp_upload_dir( null );
                if ( !$uploads || $uploads['error'] !== false ) {
                    $options['messages'][] = $uploads['error'];
                    return false;
                }
                $file_atts = wp_check_filetype_and_ext( $file, basename( $file ) );
                if ( !empty( $file_atts['proper_filename'] ) ) {
                    $new_file_name = $file_atts['proper_filename'];
                } else {
                    $new_file_name = basename( $file );
                }
                $i = strrpos( $new_file_name, "." );
                if ( $i === false ) {
                    $options['messages'][] = "$file is an invalid picture file.";
                    return false;
                }                
                $basename = substr( $new_file_name, 0, $i );
                $extension = substr( $new_file_name, $i );
                $upload_file = "$uploads[path]/$new_file_name";
                $i = 0;
                while ( true ) {
                    if ( !file_exists( $upload_file ) ) {
                        break;
                    }
                    $tag = '-' . (string) $i;
                    $new_file_name = "$basename$tag$extension";
                    $upload_file = "$uploads[path]/$new_file_name";
                }
                if ( @ copy( $file, $upload_file ) === false ) {
                    $options['messages'][] = "copy of \"$file\" to \"$upload_file\" failed.";
                    return false;
                }
                return array(
                    'file'       => $upload_file,                                      // path of file
                    'type'       => $file_atts['type'],                                // mime type
                    'subdirpath' => ltrim( "$uploads[subdir]/$new_file_name", '/' ),   // relative path of file from upload directory
                    'url'        => $uploads['url']    . "/$new_file_name"             // uri of file
                );
            }
            
            /*
             * update_pictures() copies the image file from NextGEN Gallery's image directory to WordPress's upload directory
             * and creates the corresponding attachment post with post parent the corresponding new page post created in 
             * update_galleries(). The original picture id is saved as post meta data with meta_key 'pre_update_ngg_pid'. 
             *
             * If update_pictures() is interrupted the function is re-startable since there is enough state in the database.
             */
     
            function update_pictures( $ngg_pictures, $ngg_galleries, &$options ) {
                global $wpdb;
                // get debug option and check if it is for simulating a timeout
                $debug_option = get_option( 'nggtags_for_wp_media_library_debug_option', false );
                if ( $debug_option ) {
                    list( $debug_option_name, $debug_option_value ) = explode( ':', $debug_option );
                    if ( $debug_option_name == 'update_pictures_abort' ) {
                        $die_count = $debug_option_value;
                    }
                }
                $new_gids = $wpdb->get_results(
                    "SELECT meta_value, post_id from $wpdb->postmeta WHERE meta_key = 'pre_update_ngg_gid'", OBJECT_K );
                $done_pids = $wpdb->get_col(
                    "SELECT meta_value from $wpdb->postmeta WHERE meta_key = 'pre_update_ngg_pid'", OBJECT_K );
                $gallery_paths = $wpdb->get_results(
                    "SELECT gid, path FROM $ngg_galleries", OBJECT_K );
                $results = $wpdb->get_results(
                    "SELECT pid, image_slug, galleryid, filename, description, alttext, imagedate FROM $ngg_pictures" );
                $count = 0;
                foreach ( $results as $result ) {
                    $count++;
                    // simulate a timeout if the debug option for this is set
                    if ( !empty( $die_count ) && $count >= $die_count ) {
                        die( 'simulated timeout for testing recovery' );
                    }
                    // skip stuff previously done.
                    if ( !in_array( $result->pid, $done_pids ) ) {
                        // get the new gallery id
                        if ( array_key_exists( $result->galleryid, $new_gids ) ) {
                            $post_id = $new_gids[$result->galleryid]->post_id;
                            // copy picture file from NextGEN Gallery directory to WordPress's upload directory.
                            $path = ABSPATH . $gallery_paths[$result->galleryid]->path;
                            $file = "$path/$result->filename";
                            $move_results = move_picture_to_wordpress_upload_directory( $file, $options );
                            if ( $move_results ) {
                                // now create the attachment post.
                                $post = array(
                                    'guid'           => $move_results['url'],
                                    'post_content'   => $result->description,
                                    'post_date'      => $result->imagedate,
                                    'post_excerpt'   => $result->description,
                                    'post_mime_type' => $move_results['type'],
                                    'post_name'      => $result->image_slug,
                                    'post_parent'    => $post_id,
                                    'post_status'    => 'inherit',
                                    'post_title'     => $result->alttext,
                                    'post_type'      => 'attachment'
                                );
                                if ( $id = wp_insert_post( $post ) ) {
                                    // do all the related meta data
                                    update_post_meta( $id, '_wp_attached_file',        $move_results['subdirpath'] );
                                    update_post_meta( $id, '_wp_attachment_image_alt', $result->alttext,   TRUE );
                                    $metadata = wp_generate_attachment_metadata( $id, $move_results['file'] );
                                    wp_update_attachment_metadata( $id, $metadata );
                                    // also save the original NextGEN Gallery picture id
                                    update_post_meta( $id, 'pre_update_ngg_pid',       $result->pid );
                                }
                            }
                        }
                    }
                }
                return true;
            }
    
            /*
             * update_thumbnails() updates the wp_postmeta rows with meta_key = '_thumbnail_id' with the new pid
             * which is now known. This could not be done in update_galleries() because the new pid was not known then.
             *
             * If update_thumbnails() is interrupted the function is re-startable since there is enough state in the database.
             */
             
            function update_thumbnails( &$options ) {
                global $wpdb;
                $new_pids = $wpdb->get_results(
                    "SELECT meta_value, post_id from $wpdb->postmeta WHERE meta_key = 'pre_update_ngg_pid'", OBJECT_K );
                $results = $wpdb->get_results(
                    "SELECT post_id, meta_value FROM $wpdb->postmeta WHERE meta_key = '_thumbnail_id' AND meta_value LIKE 'TODO:%'" );
                foreach ( $results as $result ) {
                    // get new pid
                    $new_pid = $new_pids[substr( $result->meta_value, 5) ];
                    update_post_meta( $result->post_id, '_thumbnail_id', $new_pid );
                }
                return true;
            }
            
            function cast_to_integer( $value ) {
                return (integer) $value;
            }
            
            /*
             * update_term_relationships() updates the wp_term_relationships table in two passes:
             * In the first pass the table is scanned for all ngg_tag entries and the picture id and corresponding ngg_tags saved in the
             * wp_options table. Then all ngg_tag entries are deleted from the wp_term_relationships table.
             * In the second pass the saved data is read back from the wp_options table and new ngg_tag entries are made using the new
             * picture ids.
             *
             * If update_term_relationships() is interrupted the function is re-startable since there is enough state in the database.
             */
     
            function update_term_relationships( $ngg_pictures, &$options ) {
                global $wpdb;
                if ( isset( $options['term_relationships'] ) ) {
                    // This is the second phase - the obsolete ngg_tag relationship data was previously saved in options in the first phase.
                    // Now construct the new ngg_tag relationship data from the previously saved obsolete ngg_tag relationship data.
                    $new_pids = $wpdb->get_results(
                        "SELECT meta_value, post_id from $wpdb->postmeta WHERE meta_key = 'pre_update_ngg_pid'", OBJECT_K );
                    foreach ( $options['term_relationships'] as $pid => $term_ids ) {
                        if ( $new_pid = $new_pids[$pid]->post_id ) {
                            $term_ids = array_map( 'cast_to_integer', $term_ids );
                            wp_set_object_terms( $new_pid, $term_ids, 'ngg_tag' );
                        }
                    }
                    return true;
                }
                
                // This is the first phase where the obsolete ngg_tag relationship data is saved.
                
                // save ngg_tag relationship data in options
                $pids = $wpdb->get_col( "SELECT pid FROM $ngg_pictures" );
                $options['term_relationships'] = array();
                foreach ( $pids as $pid ) {
                    $options['term_relationships'][$pid] = wp_get_object_terms( $pid, 'ngg_tag', array( 'fields' => 'ids' ) );
                }
                update_option( 'nggtags_for_wp_media_library', $options );
                
                // now we can delete the obsolete ngg tag relationship data
                foreach ( $pids as $pid ) {
                    wp_delete_object_term_relationships( $pid, array( 'ngg_tag' ) );
                }
                
                // go to phase 2
                return update_term_relationships( $ngg_pictures, $options );
            }
    
            // Get last completed phase and continue from there.
            
            if ( !$ntfwml_options || !isset( $ntfwml_options['status'] ) ) {
                // Galleries not started or not completed so start or continue the galleries.
                if ( update_galleries( $ntfwml_ngg_galleries, $ntfwml_options ) ) {
                    if ( !$ntfwml_options ) { $ntfwml_options = array(); }
                    $ntfwml_options['status'] = 'galleries done';
                    $ntfwml_options['messages'][] = 'Galleries done.';
                    update_option( 'nggtags_for_wp_media_library', $ntfwml_options );
                }
            }
    
            if ( $ntfwml_options['status'] == 'galleries done' ) {
                // Galleries done but pictures not started or not completed so start or continue the pictures.
                if ( update_pictures( $ntfwml_ngg_pictures, $ntfwml_ngg_galleries, $ntfwml_options ) ) {
                    $ntfwml_options['status'] = 'pictures done';
                    $ntfwml_options['messages'][] = 'Pictures done.';
                    update_option( 'nggtags_for_wp_media_library', $ntfwml_options );
                }
            }
    
            if ( $ntfwml_options['status'] == 'pictures done' ) {
                // Pictures done but thumbnails not started or not completed so start or continue the thumbnails.
                if ( update_thumbnails( $ntfwml_options ) ) {
                    $ntfwml_options['status'] = 'thumbnails done';
                    $ntfwml_options['messages'][] = 'Thumbnails done.';
                    update_option( 'nggtags_for_wp_media_library', $ntfwml_options );
                }
            }
            
            if ( $ntfwml_options['status'] == 'thumbnails done' ) {
                // Thumbnails done but term relationships not started or not completed so start or continue the term relationships.
                
                // First register NextGEN Gallery's ngg_tag taxonomy so we can use wp_get_object_terms() and 
                // wp_delete_object_term_relationships() which both require a registered taxonomy.
                register_nextgen_gallery_taxonomy();
                
                if ( update_term_relationships( $ntfwml_ngg_pictures, $ntfwml_options ) ) {
                    // Update completed.
                    $ntfwml_options['status'] = 'update done';
                    $ntfwml_options['messages'][] = 'Term relationships done.';
                    update_option( 'nggtags_for_wp_media_library', $ntfwml_options );
                }
            }
            
            // If we get here then everything is done.
            
            // Send all messages to browser.
            if ( !empty( $ntfwml_options['messages'] ) ) {
                foreach ( $ntfwml_options['messages'] as $message ) {
                    echo "<li>$message</li>";
                }
            }
            echo '<li>Update completed.</li>';
            
            // clean up transient data from wp_options.
            $ntfwml_options = array( 'status' => 'update done' );
            update_option( 'nggtags_for_wp_media_library', $ntfwml_options );
            
            die();
        }
    }
    
    return;
}

// Update completed or no update needed so enable nggtags for Media Library.

// Allow the user to set WordPress Gallery options for the shortcode 'nggtags'

function nggtags_for_media_library_settings_init() {
    add_settings_section( 'nggtags_for_media_library_settings_section', 'Settings for nggtags for Media Library',
        'do_nggtags_for_media_library_settings_section', 'nggtags_for_media_library_settings_page' );
    add_settings_field( 'nggtags_for_media_library_gallery_options', 'Gallery Options',
        'do_nggtags_for_media_library_gallery_options', 'nggtags_for_media_library_settings_page',
        'nggtags_for_media_library_settings_section' );
    register_setting( 'nggtags_for_media_library_settings', 'nggtags_for_media_library_gallery_options' );
}

add_action( 'admin_init', 'nggtags_for_media_library_settings_init' );

function do_nggtags_for_media_library_settings_section() {
}

function do_nggtags_for_media_library_gallery_options() {
?>
<input id="nggtags_for_media_library_gallery_options" name="nggtags_for_media_library_gallery_options" type="text" size="40"
    value='<?php echo get_option( 'nggtags_for_media_library_gallery_options' )?>' placeholder='e.g. size="thumbnail" link="file"'/>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="http://codex.wordpress.org/Gallery_Shortcode">WordPress Gallery Options Documentation</a>
<?php
}

add_action( 'admin_menu', 'nggtags_for_media_library_settings_menu', 11 );

function nggtags_for_media_library_settings_menu() {
    add_options_page( 'Settings for nggtags for Media Library', 'nggtags for Media Library',
        'manage_options', 'nggtags_for_media_library_settings_page', 'do_nggtags_for_media_library_settings_page' );
}

function do_nggtags_for_media_library_settings_page() {
    echo( '<form method="post" action="options.php">' );
    settings_fields( 'nggtags_for_media_library_settings' ); 
    do_settings_sections( 'nggtags_for_media_library_settings_page' );
    submit_button();
    echo( '</form>' );
}
    
add_filter( 'plugin_action_links', 'plugin_action_links_for_nggtags_for_media_library', 10, 4 );

function plugin_action_links_for_nggtags_for_media_library( $actions, $plugin_file, $plugin_data, $context ) {
    if ( strpos( $plugin_file, basename( __FILE__ ) ) !== false ) {
        array_unshift( $actions, '<a href="' . admin_url( 'options-general.php?page=nggtags_for_media_library_settings_page' ) . '">'
            . __( 'Settings' ) . '</a>' );
    }
    return $actions;
}
        
// First create the ngg_tag taxonomy.

add_action( 'init', 'create_ngg_tag_taxonomy', 0 );

function create_ngg_tag_taxonomy() {

    $labels = array(
        'name'              => _x( 'NGG Tags', 'taxonomy general name' ),
        'singular_name'     => _x( 'NGG Tag', 'taxonomy singular name' ),
    );
    
    $args = array(
        'label'             => __( 'NGG Tags' ),
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'rewrite'           => array( 'slug' => 'ngg_tag' )
    );
    
    register_taxonomy( 'ngg_tag', 'attachment', $args );
    register_taxonomy_for_object_type( 'ngg_tag', 'attachment' );    
}

function enqueue_nggtags_scripts() {
    wp_enqueue_script( 'jquery' );
}

add_action( 'wp_enqueue_scripts', 'enqueue_nggtags_scripts' );

// Now register the nggtags shortcode.

add_shortcode( 'nggtags', 'nggtags_func' );

include_once( dirname( __FILE__ ) . '/magic-fields-2-post-filter.php' );

/*
 * class Nggtags_for_Media_Library is just a wrapper so we can use the trait Magic_Fields_2_Toolkit_Post_Filters
 * which is defined in the above include.
 */
 
class Nggtags_for_Media_Library {
    use Magic_Fields_2_Toolkit_Post_Filters;
}

/*
 * nggtags_func() uses Nggtags_for_Media_Library::get_posts_with_spec() to find the images that matches the nggtags tags
 * then constructs a shortcode for WordPress's 'gallery' shortcode with an 'ids' parameter and calls do_shortcode on the 
 * construct and returns this result.
 */
 
function nggtags_func( $atts, $content, $tag ) {
    static $count = 0;
    if ( !$tag ) { $tag = $atts[0]; }
	extract( shortcode_atts( array( 'gallery' => '', 'album' => ''), $atts ) );
    $gallery_options = get_option( 'nggtags_for_media_library_gallery_options', '' );
    if ( !empty( $gallery_options ) ) {
        $gallery_options = ' ' . trim( $gallery_options );
    }
    if ( !empty( $gallery ) ) {
        // this is a gallery
        $ids = Nggtags_for_Media_Library::get_posts_with_spec( 'attachment:ngg_tag:' . $gallery );
        // pass all parameters except 'gallery' and 'album' to the WordPress builtin 'gallery' shortcode
        unset( $atts['gallery'], $atts['album'] );
        $args = '';
        foreach ( $atts as $att => $att_value ) {
            $args = " $att=\"$att_value\"";
        }
        // use WordPress's built in gallery to do NextGEN Gallery's nggtags shortcode
        return do_shortcode( '[gallery ids="' . implode( ',', $ids ) . "\"{$gallery_options}{$args}]" );
    }
    if ( !empty( $album ) ) {
        // this is an album
        $count++;
        $tags = explode( ',', $album );
        $image_ids = array();
        $gallery_image_ids = array();
        foreach( $tags as $tag ) {
            $ids = Nggtags_for_Media_Library::get_posts_with_spec( 'attachment:ngg_tag:' . $tag );
            if ( empty( $ids ) ) {
                continue;
            }
            $image_ids[] = $ids;
            $gallery_image_ids[] = $ids[0];
        }
        // pass all parameters except 'gallery' and 'album' to the WordPress builtin 'gallery' shortcode
        unset( $atts['gallery'], $atts['album'] );
        $args = '';
        foreach ( $atts as $att => $att_value ) {
            $args = " $att=\"$att_value\"";
        }
        // for albums make sure link is not set since we want the permalink for preg_replace
        unset( $atts['link'] );
        $album_args = '';
        foreach ( $atts as $att => $att_value ) {
            $album_args = " $att=\"$att_value\"";
        }
        // create an album of galleries
        // for albums make sure link is not set since we want the permalink for preg_replace
        $album_gallery_options = preg_replace( '#\slink="\w+\"#', '', $gallery_options );
        $album = do_shortcode( '[gallery ids="' . implode( ',', $gallery_image_ids ) . "\"{$album_gallery_options}{$album_args}]" );
        // replace the <a> element with a <span> element since we do not want that link
        $album = preg_replace( array( '#<a\s.+?attachment_id=(\d+).+?>#', '#</a>#' ),
            array( '<span id="album-gallery-$1" class="album-gallery-icon">', '</span>' ), $album );
        // Since there may be multiple albums use $count to give everything a unique identity
        $content .= "<div id='div-album-$count'>$album</div>";
        // now do the corresponding galleries
        $galleries = '';
        foreach ( $image_ids as $ids ) {
            $galleries .= "<div id='hidden-gallery-$ids[0]' class='hidden-gallery' style='display:none;'>";
            $galleries .= '<div><button class="button-back" style="float:right">Go Back to Album View</button></div>';
            $galleries .= '<br style="clear:both">';
            $galleries .= do_shortcode( '[gallery ids="' . implode( ',', $ids ) . "\"{$gallery_options}{$args}]" );
            $galleries .= '<div><button class="button-back" style="float:right" >Go Back to Album View</button></div></div>';
            $galleries .= '<br style="clear:both">';
        }
        $content .= "<div id='div-galleries-$count'>$galleries</div>";
        $script = <<<EOT
<script>
    // install a click handler to show the corresponding gallery
    jQuery( "div#div-album-$count span.album-gallery-icon" ).click( function() {
        jQuery( "div#div-album-$count" ).css( "display", "none" );        
        jQuery( "div#div-galleries-$count div.hidden-gallery" ).css( "display", "none" );
        jQuery( "div#div-galleries-$count div#hidden-gallery-" + this.id.substr( 14 ) ).css( "display", "block" );
    } );
    // install a buttun click handler to go back to album view
    jQuery( "div#div-galleries-$count button.button-back" ).click( function() {
        jQuery( "div#div-galleries-$count div.hidden-gallery" ).css( "display", "none" );
        jQuery( "div#div-album-$count" ).css( "display", "block" );        
    } );    
</script>
EOT;
        $content .= $script;
        return $content;
    }
}

function make_slug( $tag ) {
    return strtolower( str_replace( ' ', '-', $tag ) );
}

/*
 * posts_where_ngg_tags_filter() modifies the search when either 'tags:' or 'gallery:' prefix is present in the search criteria
 */
 
function posts_where_ngg_tags_filter( $where ) {
    global $wpdb;
    if ( strpos( $_SERVER['SCRIPT_NAME'], '/upload.php' ) === FALSE ) { return $where; }
    if ( $_REQUEST['action'] != -1 ) { return $where; }
    if ( $_REQUEST['action2'] != -1 ) { return $where; }
    if ( !( $is_tags = strpos( $_REQUEST['s'], 'tags:' ) === 0 ) && !( strpos( $_REQUEST['s'], 'gallery:' ) === 0 ) ) {
        return $where;
    }    
    if ( $is_tags ) {
        // ngg_tag search
        list( , $tags ) = explode( ':', $_REQUEST['s'], 2 );
        $tags = '"' . implode( '","', array_map( 'make_slug', explode( ',', $tags ) ) ) . '"';
        if ( strpbrk( $tags, " \n\r\t\f()" ) !== false ) { die; } 
        $where = <<<EOT
 AND {$wpdb->posts}.post_type = 'attachment' AND ( {$wpdb->posts}.post_status = 'inherit' OR {$wpdb->posts}.post_status = 'private' ) 
 AND EXISTS ( SELECT * FROM $wpdb->term_relationships r, $wpdb->term_taxonomy x, $wpdb->terms t
    WHERE r.object_id = {$wpdb->posts}.ID AND r.term_taxonomy_id = x.term_taxonomy_id AND x.term_id = t.term_id
        AND t.slug IN ( $tags ) )
EOT;
    } else {
        // gallery search
        list( , $galleries ) = explode( ':', $_REQUEST['s'], 2 );
        $galleries = '"' . implode( '","', array_map( 'make_slug', explode( ',', $galleries ) ) ) . '"';
        if ( strpbrk( $galleries, " \n\r\t\f()" ) !== false ) { die; } 
        $galleries = $wpdb->get_col( "SELECT ID from $wpdb->posts where post_name IN ( $galleries )" );
        $galleries = ' "' . implode( '",', $galleries ) . '" ';
        $where = <<<EOT
 AND {$wpdb->posts}.post_type = 'attachment' AND ( {$wpdb->posts}.post_status = 'inherit' OR {$wpdb->posts}.post_status = 'private' ) 
 AND {$wpdb->posts}.post_parent IN ( $galleries )
EOT;
    }
    return $where;
}

add_filter( 'posts_where', posts_where_ngg_tags_filter );

?>