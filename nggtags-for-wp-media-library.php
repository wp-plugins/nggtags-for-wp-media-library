<?php
namespace NggTags_for_Media_Library;

/*
 * Plugin Name:   NextGEN Gallery's nggtags for WordPress's Media Library
 * Plugin URI:    http://nggtagsforwpml.wordpress.com/
 * Description:   An implementation of NextGEN Gallery's shortcode nggtags for WordPress' Media Library.
 * Documentation: http://nggtagsforwpml.wordpress.com/
 * Version:       0.3.0.1
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

add_action( 'init', function () {
    // priority taxonomy will be used to replace NextGEN Gallery's sortorder
    $labels = array(
        'name'              => _x( 'Priority', 'taxonomy general name' ),
        'singular_name'     => _x( 'Priority', 'taxonomy singular name' ),
    );  
    register_taxonomy( 'priority', 'attachment', array(
        'label'             => __( 'Priority' ),
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'rewrite'           => array( 'slug' => 'priority' )
    ) );
    register_taxonomy_for_object_type( 'priority', 'attachment' );
} );

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
    add_action( 'admin_menu', function () {
        add_menu_page( 'Update for Using nggtags on WordPress\'s Media Library', 'nggtags for Media Library',
            'manage_options', 'nggtags_for_media_library', function () {
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
<button id="ntfwml_update_button" type="button"<?php if ( $nextgen_gallery_activated ) { echo ' disabled'; } ?>>
    <?php echo $continue ?>Update for nggtags on Media Library</button>
<div id="ntfwml_status" style="border:2px solid black;margin:20px;padding:20px;display:none;"><p><small>
<h2>Status Messages</h2><ul>
<li>Update started. This may take some time ...</li>
</ul></small></p></div>
</div>
<script type="text/javascript">
jQuery("#ntfwml_update_button").click( function() {
    var confirmed = confirm(
        "Did you really backup your database? "
        + "Pressing OK will make permanent changes to your database. "
        + "After these changes are made NextGEN Gallery will no longer work. "
    );
    if (confirmed) {
        this.disabled = true;
        jQuery( "#ntfwml_status" ).css( "display", "block" );
        var data = {
            'action': 'update_for_nggtags_on_media_library'
        };
        function update() {
            console.log("post:action:"+data.action);
            jQuery.post(
                ajaxurl,
                data,
                function(response) {
                    console.log("post:response"+response);
                    jQuery("#ntfwml_status ul").html(response);
                    if (response.indexOf("The conversion of NextGEN Gallery to WordPress Media Library is done.") === -1) {
                        update();
                    }
                }
            );
        }
        update();
    }
} );
</script>
<?php
        } );
    } );
    
    if ( is_admin() ) {
        add_action( 'wp_ajax_update_for_nggtags_on_media_library', function () {
        
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
                    $tag = '-' . (string) $i++;
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
                $results = $wpdb->get_results( <<<EOD
SELECT pid, image_slug, galleryid, filename, description, alttext, imagedate, sortorder
    FROM $ngg_pictures ORDER BY sortorder ASC
EOD
                );
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
                                    update_post_meta( $id, '_wp_attached_file', $move_results['subdirpath'] );
                                    update_post_meta( $id, '_wp_attachment_image_alt', $result->alttext, true );
                                    // save reminder to do attachment metadata
                                    //update_post_meta( $id, 'TODO:attachment_metadata', $move_results['file'] );
                                    $metadata = wp_generate_attachment_metadata( $id, $move_results['file'] );
                                    wp_update_attachment_metadata( $id, $metadata );
                                    // add a taxonomy priority tag for NextGEN Gallery's sortorder
                                    wp_set_post_terms( $id, 100 * $result->sortorder, 'priority' );
                                    // also save the original NextGEN Gallery picture id
                                    update_post_meta( $id, 'pre_update_ngg_pid', $result->pid );
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
                            $term_ids = array_map( function ( $value ) { return (integer) $value; }, $term_ids );
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

            // update_post_content() fixes the NextGEN Gallery shortcodes nggallery and singlepic in post content
           
            function update_post_content() {
                global $wpdb;
                $new_gids = $wpdb->get_results(
                    "SELECT meta_value, post_id FROM $wpdb->postmeta WHERE meta_key = 'pre_update_ngg_gid'", OBJECT_K );
                $new_pids = $wpdb->get_results(
                    "SELECT meta_value, post_id FROM $wpdb->postmeta WHERE meta_key = 'pre_update_ngg_pid'", OBJECT_K );
                $fix_shortcode = function( $matches ) use ( $new_gids, $new_pids ) {
                    $new_ids = $matches[1] == 'nggallery' ? $new_gids : $new_pids;
                    $fix_id = function( $matches ) use ( $new_ids ) {
                        $mlid = $new_ids[$matches[3]]->post_id;
                        return " id=\"$mlid\" nggid=\"$matches[3]\"";
                    };
                    return preg_replace_callback( '#\sid=(("|\')?)(\d+)\1#', $fix_id, $matches[0] );
                };
                $results = $wpdb->get_results( <<<EOT
SELECT ID, post_content FROM $wpdb->posts
    WHERE ( post_content LIKE '%[nggallery %' OR post_content LIKE '%[singlepic %' )
        AND post_content NOT LIKE '% nggid=%'
EOT
                    , OBJECT_K );
                foreach ( $results as $post_id => $result ) {
                    $post_content = preg_replace_callback( '#\[(nggallery|singlepic)\s[^\]]*\]#', $fix_shortcode,
                        $result->post_content );
                    $post = array(
                        'ID' => $post_id,
                        'post_content' => $post_content
                    );
                    wp_update_post( $post );
                }
                return true;
            }
            
            // update_attachment_metadata() does the attachment metadata; done last because it is cpu expensive
            
            function update_attachment_metadata() {
                global $wpdb;
                $results = $wpdb->get_results(
                    "SELECT post_id, meta_value file from $wpdb->postmeta WHERE meta_key = 'TODO:attachment_metadata'",
                    OBJECT_K );
                foreach ( $results as $id => $result ) {
                    $metadata = wp_generate_attachment_metadata( $id, $result->file );
                    wp_update_attachment_metadata( $id, $metadata );
                }
                return true;
            }
            
            function send_all_messages_to_browser( $options, $die ) {
                if ( !empty( $options['messages'] ) ) {
                    foreach ( $options['messages'] as $message ) {
                        echo "<li>$message</li>";
                    }
                }
                if ( $die ) {
                    echo "<li>working...</li>";
                    die();
                }
            }
            
            // Get last completed phase and continue from there.
            
            if ( !$ntfwml_options || !isset( $ntfwml_options['status'] ) ) {
                // Galleries not started or not completed so start or continue the galleries.
                if ( update_galleries( $ntfwml_ngg_galleries, $ntfwml_options ) ) {
                    if ( !$ntfwml_options ) { $ntfwml_options = array(); }
                    $ntfwml_options['status'] = 'galleries done';
                    $ntfwml_options['messages'][] = 'Galleries done.';
                    update_option( 'nggtags_for_wp_media_library', $ntfwml_options );
                    send_all_messages_to_browser( $ntfwml_options, true );
                }
            }
    
            if ( $ntfwml_options['status'] === 'galleries done' ) {
                // Galleries done but pictures not started or not completed so start or continue the pictures.
                if ( update_pictures( $ntfwml_ngg_pictures, $ntfwml_ngg_galleries, $ntfwml_options ) ) {
                    $ntfwml_options['status'] = 'pictures done';
                    $ntfwml_options['messages'][] = 'Pictures done.';
                    update_option( 'nggtags_for_wp_media_library', $ntfwml_options );
                    send_all_messages_to_browser( $ntfwml_options, true );
                }
            }
    
            if ( $ntfwml_options['status'] === 'pictures done' ) {
                // Pictures done but thumbnails not started or not completed so start or continue the thumbnails.
                if ( update_thumbnails( $ntfwml_options ) ) {
                    // Thumbnails done.
                    $ntfwml_options['status'] = 'thumbnails done';
                    $ntfwml_options['messages'][] = 'Thumbnails done.';
                    update_option( 'nggtags_for_wp_media_library', $ntfwml_options );
                    send_all_messages_to_browser( $ntfwml_options, true );
                }
            }
            
            if ( $ntfwml_options['status'] === 'thumbnails done' ) {
                // Thumbnails done but term relationships not started or not completed so start or continue doing
                // the term relationships.   
                // First register NextGEN Gallery's ngg_tag taxonomy so we can use wp_get_object_terms() and 
                // wp_delete_object_term_relationships() which both require a registered taxonomy.
                register_nextgen_gallery_taxonomy();
                if ( update_term_relationships( $ntfwml_ngg_pictures, $ntfwml_options ) ) {
                    // Term relationships done.
                    $ntfwml_options['status'] = 'term relationships done';
                    $ntfwml_options['messages'][] = 'Term relationships done.';
                    update_option( 'nggtags_for_wp_media_library', $ntfwml_options );
                    send_all_messages_to_browser( $ntfwml_options, true );
                }
            }
            
            if ( $ntfwml_options['status'] === 'term relationships done' ) {
                // Term relationships done but post content not started or not completed so start or continue doing
                // the post content.
                if ( update_post_content( $ntfwml_options ) ) {
                    // Post content done.
                    $ntfwml_options['status'] = 'post content done';
                    $ntfwml_options['messages'][] = 'Post content done.';
                    update_option( 'nggtags_for_wp_media_library', $ntfwml_options );
                    send_all_messages_to_browser( $ntfwml_options, true );
                }
            }
                
            if ( $ntfwml_options['status'] === 'post content done' ) {
                // Post content done but attachment metadata not started or not completed so start or continue doing
                // the attachment metadata.
                if ( update_attachment_metadata( ) ) {
                    // Update completed.
                    $ntfwml_options['status'] = 'attachment metadata done';
                    $ntfwml_options['messages'][] = 'Attachment metadata done.';
                    update_option( 'nggtags_for_wp_media_library', $ntfwml_options );
                    //send_all_messages_to_browser( $ntfwml_options, true );
                }
            }
            
            // If we get here then everything is done.
            $ntfwml_options['messages'][] = 'The conversion of NextGEN Gallery to WordPress Media Library is done.';
            // Send all messages to browser.
            send_all_messages_to_browser( $ntfwml_options, false );
            // clean up transient data from wp_options.
            $ntfwml_options = array( 'status' => 'update done' );
            update_option( 'nggtags_for_wp_media_library', $ntfwml_options );
            die();
        } );
    }  # if ( is_admin() ) {
    return;
}   # if ( $ntfwml_ngg_pictures_count && ( !$ntfwml_options || !isset( $ntfwml_options['status'] )

// Update completed or no update needed so enable nggtags for Media Library.
// Allow the user to set WordPress Gallery options for the shortcode 'nggtags'

add_action( 'admin_init', function () {
    add_settings_section( 'nggtags_for_media_library_settings_section', 'Settings for nggtags for Media Library',
        function () {
?>
<div style="margin:10px 20px;padding:5px 10px;font-size:smaller;">
Since the NextGEN Gallery shortcodes - nggtags, nggallery and singlepic - are implemented
using WordPress's gallery shortcode the options are the same as
<a href="http://codex.wordpress.org/Gallery_Shortcode" target="_blank">the options of WordPress's gallery shortcode</a>.
These options will automatically be added to the corresponding shortcodes in your post content.
</div>
<?php
        }, 'nggtags_for_media_library_settings_page' );
    add_settings_field( 'nggtags_for_media_library_gallery_options', 'nggtags options',
        function () {
?>
<input id="nggtags_for_media_library_gallery_options" name="nggtags_for_media_library_gallery_options" type="text"
    size="40" value='<?php echo get_option( 'nggtags_for_media_library_gallery_options' )?>'
    placeholder='e.g. size="thumbnail" link="file"'/>
<?php
        }, 'nggtags_for_media_library_settings_page', 'nggtags_for_media_library_settings_section' );
    add_settings_field( 'nggallery_for_media_library_gallery_options', 'nggallery options',
        function () {
?>
<input id="nggallery_for_media_library_gallery_options" name="nggallery_for_media_library_gallery_options" type="text"
    size="40" value='<?php echo get_option( 'nggallery_for_media_library_gallery_options' )?>'
    placeholder='e.g. size="thumbnail" link="file"'/>
<?php
        }, 'nggtags_for_media_library_settings_page', 'nggtags_for_media_library_settings_section' );
    add_settings_field( 'singlepic_for_media_library_gallery_options', 'singlepic options',
        function () {
?>
<input id="singlepic_for_media_library_gallery_options" name="singlepic_for_media_library_gallery_options" type="text"
    size="40" value='<?php echo get_option( 'singlepic_for_media_library_gallery_options' )?>'
    placeholder='e.g. size="thumbnail" link="file"'/>
<?php
        }, 'nggtags_for_media_library_settings_page', 'nggtags_for_media_library_settings_section' );
    register_setting( 'nggtags_for_media_library_settings', 'nggtags_for_media_library_gallery_options' );
    register_setting( 'nggtags_for_media_library_settings', 'nggallery_for_media_library_gallery_options' );
    register_setting( 'nggtags_for_media_library_settings', 'singlepic_for_media_library_gallery_options' );
} ) ;

add_action( 'admin_menu', function () {
    add_options_page( 'Settings for nggtags for Media Library', 'nggtags for Media Library',
        'manage_options', 'nggtags_for_media_library_settings_page', function () {
        echo( '<form method="post" action="options.php">' );
        settings_fields( 'nggtags_for_media_library_settings' ); 
        do_settings_sections( 'nggtags_for_media_library_settings_page' );
        submit_button();
        echo( '</form>' );
    } );       
}, 11 );
    
add_filter( 'plugin_action_links', function ( $actions, $plugin_file, $plugin_data, $context ) {
    if ( strpos( $plugin_file, basename( __FILE__ ) ) !== false ) {
        array_unshift( $actions, '<a href="' . admin_url( 'options-general.php?page=nggtags_for_media_library_settings_page' ) . '">'
            . __( 'Settings' ) . '</a>' );
    }
    return $actions;
}, 10, 4 );
        
add_action( 'init', function () {
    // First create the ngg_tag taxonomy.
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
}, 0 );

add_action( 'wp_enqueue_scripts', function () { wp_enqueue_script( 'jquery' ); } );

// Now register the NextGEN Gallery shortcodes.

include_once( dirname( __FILE__ ) . '/magic-fields-2-post-filter.php' );

/*
 * class Nggtags_for_Media_Library is just a wrapper so we can use the trait Magic_Fields_2_Toolkit_Post_Filters
 * which is defined in the above include.
 */
 
class Nggtags_for_Media_Library {
    use \Magic_Fields_2_Toolkit_Post_Filters;
}

function sort_ids_by_priority( $ids ) {
    global $wpdb;
    # get the priorities which are tags in the priority taxonomy 
    $list_ids = implode( ',', $ids );
    $sort_order = $wpdb->get_results( <<<EOD
SELECT r.object_id, t.name priority FROM $wpdb->terms t, $wpdb->term_taxonomy x, $wpdb->term_relationships r
    WHERE t.term_id = x.term_id AND x.term_taxonomy_id = r.term_taxonomy_id AND x.taxonomy = 'priority'
        AND r.object_id IN ( $list_ids )       
EOD
        , OBJECT_K );
    uasort( $sort_order, function( $a, $b ) {
        return $a->priority == $b->priority ? 0 : $a->priority < $b->priority ? -1 : 1;
    } );
    $sorted_ids = array_keys( $sort_order );
    // now append those ids that don't have a priority
    $ids = array_merge( $sorted_ids, array_diff( $ids, $sorted_ids ) );
    return $ids;
}

/*
 * nggtags_func() uses Nggtags_for_Media_Library::get_posts_with_spec() to find the images that matches the nggtags tags
 * then constructs a shortcode for WordPress's 'gallery' shortcode with an 'ids' parameter and calls do_shortcode on the 
 * construct and returns this result.
 */

add_shortcode( 'nggtags', function ( $atts, $content, $tag ) {
    static $count = 0;
    if ( !$tag ) { $tag = $atts[0]; }
    extract( $atts );
    // pass all parameters except 'gallery' and 'album' to the WordPress builtin 'gallery' shortcode
    unset( $atts['gallery'], $atts['album'] );
    $args = '';
    foreach ( $atts as $att => $att_value ) {
        $args .= " $att=\"$att_value\"";
    }
    $gallery_options = get_option( 'nggtags_for_media_library_gallery_options', '' );
    if ( !empty( $gallery_options ) ) {
        $gallery_options = ' ' . trim( $gallery_options );
    }
    if ( !empty( $gallery ) ) {
        // this is a gallery
        $ids = Nggtags_for_Media_Library::get_posts_with_spec( 'attachment:ngg_tag:' . $gallery );
        // reorder $ids using priorities saved in taxonomy priority
        $ids = sort_ids_by_priority( $ids );
        // use WordPress's built in gallery to do NextGEN Gallery's nggtags shortcode
        return do_shortcode( '[gallery ids="' . implode( ',', $ids ) . "\"{$gallery_options}{$args}]" );
    }
    if ( !empty( $album ) ) {
        // this is an album
        $count++;
        $tags = explode( ',', $album );
        $image_ids = array();
        $gallery_image_ids = array();
        $gallery_image_tags = array();
        foreach( $tags as $tag ) {
            $ids = Nggtags_for_Media_Library::get_posts_with_spec( 'attachment:ngg_tag:' . $tag );
            if ( empty( $ids ) ) {
                continue;
            }
            // reorder $ids using priorities saved in taxonomy priority
            $ids = sort_ids_by_priority( $ids );
            $image_ids[] = $ids;
            $gallery_image_ids[] = $ids[0];
            // get the term name for $tag
            $term_name = $tag;
            if ( is_array( $terms = get_the_terms( $ids[0], 'ngg_tag' ) ) ) {
                foreach ( $terms as $term ) {
                    if ( $term->slug === $tag ) {
                        $term_name = $term->name;
                        break;
                    }
                }
            }
            // save the term name to be used as caption for gallery
            $gallery_image_tags[] = $term_name;
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
        // replace the image captions with the tag name
        $callback_count = 0;
        $album = preg_replace_callback( '#(<(\w+)\s+class=(\'|")wp-caption-text[^\'"]*\3\s*>)([^<]*)(</\2>)#',
            function ( $m ) use ( $gallery_image_tags, &$callback_count ) {
                return $m[1] . $gallery_image_tags[$callback_count++] . $m[5];
            }, $album );      
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
<script type="text/javascript">
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
} );

/*
 * nggallery_func() constructs a WordPress 'gallery' shortcode from the 'nggallery' shortcode and calls do_shortcode on the 
 * construct and returns this result.
 */
 
add_shortcode( 'nggallery', function ( $atts, $content, $tag ) {
    global $wpdb;
    if ( !$tag ) { $tag = $atts[0]; }
    extract( $atts );
    // get the global nggallery options
    $gallery_options = get_option( 'nggallery_for_media_library_gallery_options', '' );
    if ( !empty( $gallery_options ) ) {
        $gallery_options = ' ' . trim( $gallery_options );
    }
    if ( empty( $id ) ) { return ''; }
    $ids = $wpdb->get_col( <<<EOD
SELECT ID FROM $wpdb->posts WHERE post_type = 'attachment' AND post_mime_type LIKE 'image/%' AND post_parent = $id
EOD
    );
    if ( empty( $ids ) ) { return ''; }
    $ids = sort_ids_by_priority( $ids );
    $ids = ' ids="' . implode( ',', $ids ) . '"';
    unset( $atts['id'] );
    $args = '';
    foreach ( $atts as $att => $att_value ) {
        $args .= " $att=\"$att_value\"";
    }
    // use WordPress's built in gallery to do NextGEN Gallery's nggallery shortcode
    return do_shortcode( "[gallery{$ids}{$gallery_options}{$args}]" );
} );

/*
 * singlepic_func() constructs a WordPress 'gallery' shortcode from the 'singlepic' shortcode and calls do_shortcode on the 
 * construct and returns this result.
 */
 
add_shortcode( 'singlepic', function ( $atts, $content, $tag ) {
    if ( !$tag ) { $tag = $atts[0]; }
    // get the global singlepic options
	extract( $atts );
    $gallery_options = get_option( 'singlepic_for_media_library_gallery_options', '' );
    if ( !empty( $gallery_options ) ) {
        $gallery_options = ' ' . trim( $gallery_options );
    }
    if ( empty( $id ) ) { return ''; }
    $ids = " ids=\"$id\"";
    unset( $atts['id'] );
    $args = '';
    foreach ( $atts as $att => $att_value ) {
        $args .= " $att=\"$att_value\"";
    }
    // use WordPress's built in gallery shortcode to do NextGEN Gallery's singlepic shortcode
    return do_shortcode( "[gallery{$ids}{$gallery_options}{$args}]" );
} );

function is_nggtags_media_library_request() {
    if ( strpos( $_SERVER['SCRIPT_NAME'], '/upload.php' ) === false ) { return false; }
    if ( $_REQUEST['action'] != -1 || $_REQUEST['action2'] != -1 ) { return false; }
    if ( !( strpos( $_REQUEST['s'], 'tags:' ) === 0 ) && !( strpos( $_REQUEST['s'], 'gallery:' ) === 0 ) ) {
        return false;
    }    
    return true;
}

/*
 * posts_where_ngg_tags_filter() modifies the search when either 'tags:' or 'gallery:' prefix is present in the search criteria
 */
 
add_filter( 'posts_where', function ( $where ) {
    global $wpdb;
    if ( !is_nggtags_media_library_request() ) { return $where; }
    if ( strpos( $_REQUEST['s'], 'tags:' ) === 0 ) {
        // ngg_tag search
        list( , $tags ) = explode( ':', $_REQUEST['s'], 2 );
        $tags = '"' . implode( '","', array_map( function ( $tag ) { return strtolower( str_replace( ' ', '-', $tag ) ); },
            explode( ',', $tags ) ) ) . '"';
        if ( strpbrk( $tags, " \n\r\t\f()" ) !== false ) { die; } 
        $where = <<<EOT
 AND {$wpdb->posts}.post_type = 'attachment' AND ( {$wpdb->posts}.post_status = 'inherit' OR {$wpdb->posts}.post_status = 'private' ) 
 AND EXISTS ( SELECT * FROM $wpdb->term_relationships r, $wpdb->term_taxonomy x, $wpdb->terms t
    WHERE r.object_id = {$wpdb->posts}.ID AND r.term_taxonomy_id = x.term_taxonomy_id AND x.term_id = t.term_id
        AND t.slug IN ( $tags ) )
EOT;
    } else if ( strpos( $_REQUEST['s'], 'gallery:' ) === 0 ) {
        // gallery search
        list( , $galleries ) = explode( ':', $_REQUEST['s'], 2 );
        $galleries = '"' . implode( '","', array_map( function ( $tag ) { return strtolower( str_replace( ' ', '-', $tag ) ); },
            explode( ',', $galleries ) ) ) . '"';
        if ( strpbrk( $galleries, " \n\r\t\f()" ) !== false ) { die; } 
        $galleries = $wpdb->get_col( "SELECT ID from $wpdb->posts where post_name IN ( $galleries )" );
        $galleries = ' "' . implode( '",', $galleries ) . '" ';
        $where = <<<EOT
 AND {$wpdb->posts}.post_type = 'attachment' AND ( {$wpdb->posts}.post_status = 'inherit' OR {$wpdb->posts}.post_status = 'private' ) 
 AND {$wpdb->posts}.post_parent IN ( $galleries )
EOT;
    } else {
        return $where;
    }
    return $where;
} );

add_filter( 'post_limits_request', function ( $limits ) {
    global $wpdb;
    if ( is_nggtags_media_library_request() ) {
        $limits = 'LIMIT 0, 256';
    }
    return $limits;
} );

?>