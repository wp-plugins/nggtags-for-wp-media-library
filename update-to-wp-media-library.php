<?php

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

namespace NggTags_for_Media_Library;

global $wpdb;
 
// Parameters

// These limits should be set small enough so that the server can do the corresponding tasks without timing out
// Depending on the speed of the server this may need adjustment

$update_limit                          = 25;
$update_galleries_limit                = $update_limit;
$update_pictures_limit                 = $update_limit;
$update_albums_limit                   = $update_limit;
$update_thumbnails_limit               = $update_limit;
$update_term_relationships_setup_limit = $update_limit;
$update_term_relationships_limit       = $update_limit;
$update_post_content_limit             = $update_limit;
$update_attachment_metadata_limit      = $update_limit;

update_to_wp_media_library();

function update_to_wp_media_library() {
    
    /*
     * Update not started or not completed so start or continue the update.
     *
     * If the update is interrupted the update is re-startable since there is enough state in the database.
     */
     
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
        // exclude taxonomy will be used to save NextGEN Gallery's exclude flag
        $labels = array(
            'name'              => _x( 'Exclude', 'taxonomy general name' ),
            'singular_name'     => _x( 'Exclude', 'taxonomy singular name' ),
        );  
        register_taxonomy( 'exclude', 'attachment', array(
            'label'             => __( 'Exclude' ),
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'rewrite'           => array( 'slug' => 'exclude' )
        ) );
        register_taxonomy_for_object_type( 'exclude', 'attachment' );
    } );

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
This feature lets you use the shortcode 'nggtags', 'nggallery', 'slideshow', 'album' and 'singlepic' on Wordpress's Media Library images.
If you have an existing NextGEN Gallery database you must update your database.
This update will copy NextGEN Gallery's pictures and galleries to WordPress's Media Library.
After this update NextGEN Gallery will not work.
NextGEN pictures and galleries will now exists as WordPress Media Library images and galleries
and the shortcodes will work with the Media Library images.
Except for 'gallery' and 'album' the parameters to the shortcodes are parameters to
WordPress's 'gallery' shortcode in particular you may need to specify
'<strong>link=&quot;file&quot;</strong>' to make some lightboxes to work.
You may want to <a href="<?php echo admin_url( 'options-media.php' ); ?>">set the image sizes</a> before starting the update.
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
            jQuery.post(
                ajaxurl,
                data,
                function(response) {
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
            global $ntfwml_ngg_album;
            global $ntfwml_options;
            global $update_galleries_limit;
            global $update_pictures_limit;
            global $update_albums_limit;
            global $update_thumbnails_limit;
            global $update_term_relationships_setup_limit;
            global $update_term_relationships_limit;
            global $update_post_content_limit;
            global $update_attachment_metadata_limit;
            
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

             function update_galleries( $ngg_galleries, $limit ) {
                global $wpdb;
                $done_gids = $wpdb->get_col( "SELECT meta_value from $wpdb->postmeta WHERE meta_key = 'pre_update_ngg_gid'" );
                $results = $wpdb->get_results( "SELECT gid, name, title, galdesc, previewpic, author FROM $ngg_galleries ORDER BY gid" );
                $count = 0;
                foreach ( $results as $result ) {
                    // skip stuff previously done.
                    if ( !in_array( $result->gid, $done_gids ) ) {
                        if ( empty( $first_id ) ) { $first_id = $result->gid; }
                        // create a page to be used as an attachment host
                        $post = array(
                            'post_author'  => $result->author,
                            'post_content' => <<<EOD
<!--
    This post is used to hold the pictures in a NextGEN Gallery gallery.
    This gallery is intended to be displayed using NextGen Gallery's nggallery or slideshow shortcode.
    I.e., this post is not intended to be displayed directly but functions as a container for images.
    However, you can display this post to verify the container contains the desired images.
-->
[gallery]
EOD
                            ,
                            'post_excerpt' => $result->galdesc,
                            'post_name'    => $result->name,
                            'post_status'  => 'publish',
                            'post_title'   => "Gallery: $result->title",
                            'post_type'    => 'page'
                        );
                        if ( $id = wp_insert_post( $post ) ) {
                            // previewpic cannot be correctly done now since new pid of previewpic is not known so mark it
                            update_post_meta( $id, '_thumbnail_id', 'TODO:' . $result->previewpic );
                            // also save the original NextGEN Gallery gallery id
                            update_post_meta( $id, 'pre_update_ngg_gid', $result->gid );
                            if ( ++$count === $limit ) {
                                return "Galleries for ids $first_id to $result->gid done.";
                            }
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
                $uploads['path'] = str_replace( '\\', '/', $uploads['path'] );
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
     
            function update_pictures( $ngg_pictures, $ngg_galleries, &$options, $limit ) {
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
                $done_pids = $wpdb->get_col( "SELECT meta_value from $wpdb->postmeta WHERE meta_key = 'pre_update_ngg_pid'" );
                $gallery_paths = $wpdb->get_results( "SELECT gid, path FROM $ngg_galleries", OBJECT_K );
                $results = $wpdb->get_results( <<<EOD
SELECT pid, image_slug, galleryid, filename, description, alttext, imagedate, exclude, sortorder
    FROM $ngg_pictures ORDER BY sortorder ASC
EOD
                );
                $count = 0;
                foreach ( $results as $result ) {
                    // simulate a timeout if the debug option for this is set
                    if ( !empty( $die_count ) && $count >= $die_count ) {
                        die( 'simulated timeout for testing recovery' );
                    }
                    // skip stuff previously done.
                    if ( !in_array( $result->pid, $done_pids ) ) {
                        if ( empty( $first_id ) ) { $first_id = $result->pid; }
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
                                    update_post_meta( $id, 'TODO:attachment_metadata', $move_results['file'] );
                                    // add a taxonomy priority tag for NextGEN Gallery's sortorder
                                    wp_set_post_terms( $id, 100 * $result->sortorder, 'priority' );
                                    // save exclude flag in taxonomy exclude
                                    if ( $result->exclude ) { wp_set_post_terms( $id, 'yes', 'exclude' ); }
                                    // also save the original NextGEN Gallery picture id
                                    update_post_meta( $id, 'pre_update_ngg_pid', $result->pid );
                                    if ( ++$count === $limit ) {
                                        return "Pictures for ids $first_id, ..., $result->pid done.";
                                    }
                                }
                            }
                        }
                    }
                }
                return true;
            }
            
            function update_albums( $ngg_album, $limit ) {
                global $wpdb;
                $new_gids = $wpdb->get_results(
                    "SELECT meta_value, post_id from $wpdb->postmeta WHERE meta_key = 'pre_update_ngg_gid'", OBJECT_K );
                $done_ids = $wpdb->get_col( "SELECT meta_value from $wpdb->postmeta WHERE meta_key = 'pre_update_ngg_id'" );
                $results = $wpdb->get_results(
                    "SELECT id, name, slug, previewpic, albumdesc, sortorder FROM $ngg_album ORDER BY id" );
                $count = 0;
                foreach ( $results as $result ) {
                    // skip stuff previously done.
                    if ( !in_array( $result->id, $done_ids ) ) {
                        if ( empty( $first_id ) ) { $first_id = $result->id; }
                        $post_content = <<<EOD
<!--
    This post is used to hold the galleries in a NextGEN Gallery album.
    This album is intended to be displayed using NextGen Gallery's album shortcode.
    I.e., this post is not intended to be displayed directly but functions as a container for galleries.
    However, you can display this post to verify the container contains the desired galleries.
-->
EOD;
                        // save galleries in album as
                        foreach ( unserialize( $result->sortorder ) as $gallery ) {
                            // translate the old gallery ids to the new gallery ids
                            $new_gallery = array_key_exists( $gallery, $new_gids ) ? $new_gids[$gallery]->post_id : '';
                            if ( $post_content ) { $post_content .= "\n<hr>\n"; }
                            $post_content .= "[nggallery id=\"$new_gallery\" nggid=\"$gallery\"]";
                        }
                        // create a page to be used as a gallery host
                        $post = array(
                            'post_content' => $post_content,
                            'post_excerpt' => $result->albumdesc,
                            'post_name'    => $result->slug,
                            'post_status'  => 'publish',
                            'post_title'   => "Album: $result->name",
                            'post_type'    => 'page'
                        );
                        if ( $id = wp_insert_post( $post ) ) {
                            // also save the original NextGEN Gallery gallery id
                            update_post_meta( $id, 'pre_update_ngg_id', $result->id );
                            if ( ++$count === $limit ) {
                                return "Albums for ids $first_id to $result->id done.";
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
             
            function update_thumbnails( $limit ) {
                global $wpdb;
                $new_pids = $wpdb->get_results(
                    "SELECT meta_value, post_id from $wpdb->postmeta WHERE meta_key = 'pre_update_ngg_pid'", OBJECT_K );
                $results = $wpdb->get_results( <<<EOD
SELECT post_id, meta_value FROM $wpdb->postmeta WHERE meta_key = '_thumbnail_id' AND meta_value LIKE 'TODO:%' ORDER BY post_id
EOD
                );
                $first_id = current( $results )->post_id;
                $count = 0;
                foreach ( $results as $result ) {
                    // get new pid
                    $new_pid = $new_pids[substr( $result->meta_value, 5) ]->post_id;
                    update_post_meta( $result->post_id, '_thumbnail_id', $new_pid );
                    if ( ++$count === $limit ) {
                        return "Thumbnails for ids $first_id to $result->post_id done.";
                    }
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
     
            function update_term_relationships_setup( $ngg_pictures, &$options, $limit ) {
                global $wpdb;
                // This is the first phase where the obsolete ngg_tag relationship data is saved.
                // save ngg_tag relationship data in options
                $pids = $wpdb->get_col( "SELECT pid FROM $ngg_pictures ORDER BY pid" );
                if ( !array_key_exists( 'term_relationships', $options ) ) { $options['term_relationships'] = array(); }
                $count = 0;
                $done_pids = array();
                foreach ( $pids as $pid ) {
                    if ( !array_key_exists( $pid, $options['term_relationships'] ) ) {
                        if ( empty( $first_id ) ) { $first_id = $pid; }
                        $options['term_relationships'][$pid]
                            = wp_get_object_terms( $pid, 'ngg_tag', array( 'fields' => 'ids' ) );
                        $done_pids[] = $pid;
                        if ( ++$count === $limit ) {
                            update_option( 'nggtags_for_wp_media_library', $options );
                            // now we can delete the obsolete ngg tag relationship data
                            foreach ( $done_pids as $pid ) {
                                wp_delete_object_term_relationships( $pid, array( 'ngg_tag' ) );
                            }
                            return "Term relationships setup for ids $first_id to $pid done.";
                        }
                    }
                }
                update_option( 'nggtags_for_wp_media_library', $options );
                // now we can delete the obsolete ngg tag relationship data
                foreach ( $done_pids as $pid ) {
                    wp_delete_object_term_relationships( $pid, array( 'ngg_tag' ) );
                }
                return true;
            }

            function update_term_relationships( $ngg_pictures, &$options, $limit ) {
                global $wpdb;
                if ( isset( $options['term_relationships'] ) ) {
                    // This is the second phase - the obsolete ngg_tag relationship data was previously saved in options in the first phase.
                    // Now construct the new ngg_tag relationship data from the previously saved obsolete ngg_tag relationship data.
                    $new_pids = $wpdb->get_results(
                        "SELECT meta_value, post_id from $wpdb->postmeta WHERE meta_key = 'pre_update_ngg_pid'", OBJECT_K );
                    $first_id = key( $options['term_relationships'] );
                    $count = 0;
                    foreach ( $options['term_relationships'] as $pid => &$term_ids ) {
                        if ( array_key_exists( $pid, $new_pids) and $new_pid = $new_pids[$pid]->post_id ) {
                            $int_term_ids = array_map( function ( $value ) { return (integer) $value; }, $term_ids );
                            wp_set_post_terms( $new_pid, $int_term_ids, 'ngg_tag' );
                            $term_ids = null;
                            if ( ++$count === $limit ) {
                                $options['term_relationships'] = array_filter( $options['term_relationships'] );
                                update_option( 'nggtags_for_wp_media_library', $options );
                                return "Term relationships for ids $first_id, ..., $pid done.";
                            }
                        }
                    }
                }
                return true;
            }
            
            // update_post_content() fixes the NextGEN Gallery shortcodes nggallery and singlepic in post content
           
            function update_post_content( $limit ) {
                global $wpdb;
                $new_gids = $wpdb->get_results(
                    "SELECT meta_value, post_id FROM $wpdb->postmeta WHERE meta_key = 'pre_update_ngg_gid'", OBJECT_K );
                $new_pids = $wpdb->get_results(
                    "SELECT meta_value, post_id FROM $wpdb->postmeta WHERE meta_key = 'pre_update_ngg_pid'", OBJECT_K );
                $new_ids = $wpdb->get_results(
                    "SELECT meta_value, post_id FROM $wpdb->postmeta WHERE meta_key = 'pre_update_ngg_id'", OBJECT_K );
                $fix_shortcode = function( $matches ) use ( $new_gids, $new_pids, $new_ids ) {
                    if ( strpos( $matches[0], 'nggid' ) !== false ) { return $matches[0]; }
                    $new_xids = ( $matches[1] === 'nggallery' || $matches[1] === 'slideshow' ) ? $new_gids
                        : ( $matches[1] === 'singlepic' ? $new_pids : $new_ids );
                    $fix_id = function( $matches ) use ( $new_xids ) {
                        $mlid = $new_xids[$matches[3]]->post_id;
                        return " id=\"$mlid\" nggid=\"$matches[3]\"";
                    };
                    return preg_replace_callback( '#\sid=(("|\')?)(\d+)\1#', $fix_id, $matches[0] );
                };
                $fix_old_singlepic = function( $matches ) use ( $new_pids ) {
                    $mlid = $new_pids[$matches[1]]->post_id;
                    return "[singlepic id=\"$mlid\" nggorig=\"{$matches[1]}{$matches[2]}{$matches[3]}\"]";
                };
                $results = $wpdb->get_results( <<<EOT
SELECT ID, post_content FROM $wpdb->posts
    WHERE ( post_content LIKE '%[nggallery %' OR post_content LIKE '%[singlepic %' OR post_content LIKE '%[singlepic=%'
            OR post_content LIKE '%[album %' OR post_content LIKE '%[slideshow %' )
        AND post_content NOT LIKE '% nggid=%' AND post_status = 'publish' ORDER BY ID
EOT
                    , OBJECT_K );
                $first_id = key( $results );
                $count = 0;
                foreach ( $results as $post_id => $result ) {
                    $post_content = preg_replace_callback( '#\[(nggallery|singlepic|album|slideshow)\s[^\]]*\]#',
                        $fix_shortcode, $result->post_content );
                    $post_content = preg_replace_callback( '#\[singlepic=(\d+)(,?)([^\]]*)\]#', $fix_old_singlepic,
                        $post_content );
                    $post = array(
                        'ID' => $post_id,
                        'post_content' => $post_content
                    );
                    wp_update_post( $post );
                    if ( ++$count === $limit ) {
                        return "Post content for post ids $first_id to $post_id done.";
                    }
                }
                return true;
            }
            
            // update_attachment_metadata() does the attachment metadata; done last because it is cpu expensive
            
            function update_attachment_metadata( $limit ) {
                global $wpdb;
                $results = $wpdb->get_results(
                    "SELECT post_id, meta_value file from $wpdb->postmeta WHERE meta_key = 'TODO:attachment_metadata' ORDER BY post_id",
                    OBJECT_K );
                $first_id = key( $results );
                $count = 0;
                foreach ( $results as $id => $result ) {
                    $metadata = wp_generate_attachment_metadata( $id, $result->file );
                    wp_update_attachment_metadata( $id, $metadata );
                    delete_post_meta( $id, 'TODO:attachment_metadata' );
                    if ( ++$count === $limit ) {
                        return "Attachment metadata for ids $first_id to $id done.";
                    }
                }
                return true;
            }
            
            function send_all_messages_to_browser( $options, $die, $status = null ) {
                if ( !empty( $options['messages'] ) ) {
                    foreach ( $options['messages'] as $message ) {
                        echo "<li>$message</li>";
                    }
                }
                if ( $die ) {
                    // return working status to browser
                    if ( $status ) { echo "<li>$status</li>"; }
                    echo '<li>working...</li>';
                    die();
                }
            }
            
            // Get last completed phase and continue from there.
            
            if ( !$ntfwml_options || !isset( $ntfwml_options['status'] ) ) {
                // Galleries not started or not completed so start or continue the galleries.
                if ( ( $status = update_galleries( $ntfwml_ngg_galleries, $update_galleries_limit ) ) === true ) {
                    if ( !$ntfwml_options ) { $ntfwml_options = array(); }
                    $ntfwml_options['status'] = 'galleries done';
                    $ntfwml_options['messages'][] = 'Galleries done.';
                    update_option( 'nggtags_for_wp_media_library', $ntfwml_options );
                    send_all_messages_to_browser( $ntfwml_options, true );
                } else {
                    send_all_messages_to_browser( $ntfwml_options, true, $status );
                }
            }
    
            if ( $ntfwml_options['status'] === 'galleries done' ) {
                // Galleries done but pictures not started or not completed so start or continue the pictures.
                if ( ( $status = update_pictures( $ntfwml_ngg_pictures, $ntfwml_ngg_galleries, $ntfwml_options,
                    $update_pictures_limit ) ) === true ) {
                    $ntfwml_options['status'] = 'pictures done';
                    $ntfwml_options['messages'][] = 'Pictures done.';
                    update_option( 'nggtags_for_wp_media_library', $ntfwml_options );
                    send_all_messages_to_browser( $ntfwml_options, true );
                } else {
                    send_all_messages_to_browser( $ntfwml_options, true, $status );
                }
            }
    
            if ( $ntfwml_options['status'] === 'pictures done' ) {
                // Pictures done but albums not started or not completed so start or continue the albums.
                if ( ( $status = update_albums( $ntfwml_ngg_album, $update_albums_limit ) ) === true ) {
                    // Albums done.
                    $ntfwml_options['status'] = 'albums done';
                    $ntfwml_options['messages'][] = 'Albums done.';
                    update_option( 'nggtags_for_wp_media_library', $ntfwml_options );
                    send_all_messages_to_browser( $ntfwml_options, true );
                } else {
                    send_all_messages_to_browser( $ntfwml_options, true, $status );
                }
            }
            
            if ( $ntfwml_options['status'] === 'albums done' ) {
                // Albums done but thumbnails not started or not completed so start or continue the thumbnails.
                if ( ( $status = update_thumbnails( $update_thumbnails_limit ) ) === true ) {
                    // Thumbnails done.
                    $ntfwml_options['status'] = 'thumbnails done';
                    $ntfwml_options['messages'][] = 'Thumbnails done.';
                    update_option( 'nggtags_for_wp_media_library', $ntfwml_options );
                    send_all_messages_to_browser( $ntfwml_options, true );
                } else {
                    send_all_messages_to_browser( $ntfwml_options, true, $status );
                }
            }
            
            if ( $ntfwml_options['status'] === 'thumbnails done' ) {
                // Thumbnails done but term relationships not started or not completed so start or continue doing
                // the term relationships.   
                // First register NextGEN Gallery's ngg_tag taxonomy so we can use wp_get_object_terms() and 
                // wp_delete_object_term_relationships() which both require a registered taxonomy.
                register_nextgen_gallery_taxonomy();
                if ( ( $status = update_term_relationships_setup( $ntfwml_ngg_pictures, $ntfwml_options,
                    $update_term_relationships_setup_limit ) ) === true ) {
                    // Term relationships setup done.
                    $ntfwml_options['status'] = 'term relationships setup done';
                    $ntfwml_options['messages'][] = 'Term relationships setup done.';
                    update_option( 'nggtags_for_wp_media_library', $ntfwml_options );
                    send_all_messages_to_browser( $ntfwml_options, true );
                } else {
                    send_all_messages_to_browser( $ntfwml_options, true, $status );
                }
            }
            
            // register_new_ngg_tag_taxonomy_for_wp_attachments() creates the ngg_tag taxonomy for WordPress attachments.

            function register_new_ngg_tag_taxonomy_for_wp_attachments() {
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
            
            if ( $ntfwml_options['status'] === 'term relationships setup done' ) {
                // Term relationships setup done but term relationships not started or not completed
                // so start or continue doing the term relationships.
                // First register the new ngg_tag taxonomy for WordPress attachments.
                register_new_ngg_tag_taxonomy_for_wp_attachments();
                if ( ( $status = update_term_relationships( $ntfwml_ngg_pictures, $ntfwml_options,
                    $update_term_relationships_limit ) ) === true ) {
                    // Term relationships done.
                    $ntfwml_options['status'] = 'term relationships done';
                    $ntfwml_options['messages'][] = 'Term relationships done.';
                    update_option( 'nggtags_for_wp_media_library', $ntfwml_options );
                    send_all_messages_to_browser( $ntfwml_options, true );
                } else {
                    send_all_messages_to_browser( $ntfwml_options, true, $status );
                }
            }
            
            if ( $ntfwml_options['status'] === 'term relationships done' ) {
                // Term relationships done but post content not started or not completed so start or continue doing
                // the post content.
                if ( ( $status = update_post_content( $update_post_content_limit ) ) === true ) {
                    // Post content done.
                    $ntfwml_options['status'] = 'post content done';
                    $ntfwml_options['messages'][] = 'Post content done.';
                    update_option( 'nggtags_for_wp_media_library', $ntfwml_options );
                    send_all_messages_to_browser( $ntfwml_options, true );
                } else {
                    send_all_messages_to_browser( $ntfwml_options, true, $status );
                }
            }
                
            if ( $ntfwml_options['status'] === 'post content done' ) {
                // Post content done but attachment metadata not started or not completed so start or continue doing
                // the attachment metadata.
                if ( ( $status = update_attachment_metadata( $update_attachment_metadata_limit ) ) === true ) {
                    // Update completed.
                    $ntfwml_options['status'] = 'attachment metadata done';
                    $ntfwml_options['messages'][] = 'Attachment metadata done.';
                    update_option( 'nggtags_for_wp_media_library', $ntfwml_options );
                    //send_all_messages_to_browser( $ntfwml_options, true );
                } else {
                    send_all_messages_to_browser( $ntfwml_options, true, $status );
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
}


