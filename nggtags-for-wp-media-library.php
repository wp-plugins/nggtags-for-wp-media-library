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

$max_taxonomies = 8;

// NextGEN Gallery MySQL Table names defined here.

$ntfwml_ngg_pictures  = $wpdb->prefix . 'ngg_pictures';
$ntfwml_ngg_galleries = $wpdb->prefix . 'ngg_gallery';

// Are there any NextGEN Gallery pictures?

$tables = $wpdb->get_col( "SHOW TABLES LIKE '$ntfwml_ngg_pictures'" );
$ntfwml_ngg_pictures_count = !$tables ? 0 : $wpdb->get_var( "SELECT COUNT(*) FROM $ntfwml_ngg_pictures" );

// What is the current state of the update?

$ntfwml_options = get_option( 'nggtags_for_wp_media_library', array() );

if ( $ntfwml_ngg_pictures_count && ( !$ntfwml_options || !isset( $ntfwml_options['status'] )
    || $ntfwml_options['status'] != 'update done' ) ) {
    # start or continue the upgrade to media library
    include( dirname( __FILE__ ) . '/update-to-wp-media-library.php' );
    return;
}   # if ( $ntfwml_ngg_pictures_count && ( !$ntfwml_options || !isset( $ntfwml_options['status'] )

// Update completed or no update needed so enable nggtags for Media Library.
// Allow the user to set WordPress Gallery options for the shortcode 'nggtags'

add_action( 'admin_init', function () {
    global $max_taxonomies;
    add_settings_section( 'nggtags_for_media_library_settings_section', 'Tags for Media Library Settings',
        function () {
?>
<div style="margin:10px 20px;padding:5px 10px;font-size:smaller;">
Since the NextGEN Gallery shortcodes -
<a href="http://nggtagsforwpml.wordpress.com/#nggtags" target="_blank">nggtags</a>,
<a href="http://nggtagsforwpml.wordpress.com/#nggallery-singlepic" target="_blank">nggallery</a> and
<a href="http://nggtagsforwpml.wordpress.com/#nggallery-singlepic" target="_blank">singlepic</a> - are implemented
using WordPress's gallery shortcode the options are the same as
<a href="http://codex.wordpress.org/Gallery_Shortcode" target="_blank">the options of WordPress's gallery shortcode</a>.
These options will automatically be added to the corresponding shortcodes in your post content.
</div>
<?php
        }, 'nggtags_for_media_library_settings_page' );
        
    # settings field for gallery options for nggtags shortcode
    add_settings_field( 'nggtags_for_media_library_gallery_options', 'nggtags options',
        function () {
?>
<input id="nggtags_for_media_library_gallery_options" name="nggtags_for_media_library_gallery_options" type="text"
    size="40" value='<?php echo get_option( 'nggtags_for_media_library_gallery_options' ); ?>'
    placeholder='e.g. size="thumbnail" link="file" columns="4"'/>
<?php
        }, 'nggtags_for_media_library_settings_page', 'nggtags_for_media_library_settings_section' );
        
    # settings field for gallery options for nggallery shortcode
    add_settings_field( 'nggallery_for_media_library_gallery_options', 'nggallery options',
        function () {
?>
<input id="nggallery_for_media_library_gallery_options" name="nggallery_for_media_library_gallery_options" type="text"
    size="40" value='<?php echo get_option( 'nggallery_for_media_library_gallery_options' ); ?>'
    placeholder='e.g. size="thumbnail" link="file" columns="4"'/>
<?php
        }, 'nggtags_for_media_library_settings_page', 'nggtags_for_media_library_settings_section' );
        
    # settings field for gallery options for singlepic shortcode
    add_settings_field( 'singlepic_for_media_library_gallery_options', 'singlepic options',
        function () {
?>
<input id="singlepic_for_media_library_gallery_options" name="singlepic_for_media_library_gallery_options" type="text"
    size="40" value='<?php echo get_option( 'singlepic_for_media_library_gallery_options' ); ?>'
    placeholder='e.g. size="full"'/>
<?php
        }, 'nggtags_for_media_library_settings_page', 'nggtags_for_media_library_settings_section' );
        
    # settings field for gallery options for search widget
    add_settings_field( 'search_results_for_media_library_gallery_options', 'search results options',
        function () {
?>
<input id="search_results_for_media_library_gallery_options" name="search_results_for_media_library_gallery_options"
    type="text" size="40" value='<?php echo get_option( 'search_results_for_media_library_gallery_options' ); ?>'
    placeholder='e.g. size="e.g. size="thumbnail" link="file" columns="4"'/>
<?php
        }, 'nggtags_for_media_library_settings_page', 'nggtags_for_media_library_settings_section' );
        
    # settings field for alt high density gallery view
    add_settings_field( 'nggml_alt_high_density_gallery_image_width', 'high density gallery view image width',
        function () {
?>
<input id="nggml_alt_high_density_gallery_image_width" name="nggml_alt_high_density_gallery_image_width"
    type="number" min="16" max="1024" size="40"
    value='<?php echo get_option( 'nggml_alt_high_density_gallery_image_width', '64' ); ?>' />
<?php
            echo '&nbsp;&nbsp;for the user (not admin) high density gallery view';
        }, 'nggtags_for_media_library_settings_page', 'nggtags_for_media_library_settings_section' );
        
    register_setting( 'nggtags_for_media_library_settings', 'nggtags_for_media_library_gallery_options' );
    register_setting( 'nggtags_for_media_library_settings', 'nggallery_for_media_library_gallery_options' );
    register_setting( 'nggtags_for_media_library_settings', 'singlepic_for_media_library_gallery_options' );
    register_setting( 'nggtags_for_media_library_settings', 'search_results_for_media_library_gallery_options' );
    register_setting( 'nggtags_for_media_library_settings', 'nggml_alt_high_density_gallery_image_width' );
    
    add_settings_section( 'nggtags_for_media_library_taxonomy_section', 'Taxonomies for Media Library',
        function () {
?>
<div style="margin:10px 20px;padding:5px 10px;font-size:smaller;">
In addition to Ngg Tags you can <a href="http://nggtagsforwpml.wordpress.com/#additional-taxonomies" target="_blank">create
your own tag taxonomies</a> for Media Library images.
</div>
<?php
        }, 'nggtags_for_media_library_settings_page' );
    $taxonomy_count = $max_taxonomies + 1; 
    for ( $i = 1; $i <= $taxonomy_count; $i++ ) {
        if ( $i === $taxonomy_count ) {
            if ( !empty( $first_empty_i ) ) {
                $use_i = $first_empty_i;
                $taxonomy_slug = "nggtags_for_media_library_taxonomy_slug_$use_i";
                $taxonomy_name = "nggtags_for_media_library_taxonomy_name_$use_i";
            } else {
                break;
            }
        } else {
            $use_i = $i;
            $taxonomy_slug = "nggtags_for_media_library_taxonomy_slug_$i";
            $taxonomy_name = "nggtags_for_media_library_taxonomy_name_$i";
        }
        $taxonomy_name_value = get_option( $taxonomy_name );
        if ( $i === 1 ) {
            $taxonomy_slug_value = 'ngg_tag';
            if ( empty( $taxonomy_name_value ) ) { $taxonomy_name_value = 'NGG Tags'; }
        } else if ( $i === 2 ) {
            $taxonomy_slug_value = 'priority';
            if ( empty( $taxonomy_name_value ) ) { $taxonomy_name_value = 'Priority'; }
        } else {  
            $taxonomy_slug_value = get_option( $taxonomy_slug );
        }
        if ( empty( $taxonomy_slug_value ) ) {
            if ( $i !== $taxonomy_count ) {
                if ( empty( $first_empty_i) ) { $first_empty_i = $i; }
                continue;
            }
            $taxonomy_name_value = '';
        }
        add_settings_field( $taxonomy_name, "Taxonomy Name $use_i",
            function () use ( $taxonomy_name, $taxonomy_name_value ) {
?>
<input id="<?php echo $taxonomy_name; ?>" name="<?php echo $taxonomy_name; ?>" type="text"
    size="40" value="<?php echo $taxonomy_name_value; ?>" placeholder="enter new taxonomy name" />
<?php
            }, 'nggtags_for_media_library_settings_page', 'nggtags_for_media_library_taxonomy_section' );
        add_settings_field( $taxonomy_slug, "Taxonomy Slug $use_i",
            function () use ( $taxonomy_slug, $taxonomy_slug_value, $use_i ) {
?>
<input id="<?php echo $taxonomy_slug; ?>" name="<?php echo $taxonomy_slug; ?>" type="text"
    size="40" value="<?php echo $taxonomy_slug_value; ?>" placeholder="enter new taxonomy slug"
    <?php if ( $use_i <= 2 ) { echo 'disabled'; } ?> />
    <?php if ( !empty( $taxonomy_slug_value ) && $use_i > 2 ) {
        echo '&nbsp;&nbsp;an empty slug value will delete this taxonomy';
    } ?>
<?php
            }, 'nggtags_for_media_library_settings_page', 'nggtags_for_media_library_taxonomy_section' );
        register_setting( 'nggtags_for_media_library_settings', $taxonomy_slug );
        register_setting( 'nggtags_for_media_library_settings', $taxonomy_name );
    }
    add_filter( 'pre_update_option', function ( $new_value, $option, $old_value ) {
        if ( strpos( $option, 'nggtags_for_media_library_taxonomy_slug_' ) !== 0 ) { return $new_value; }
        // nggtags_for_media_library_taxonomy_slugs cannot be changed
        if ( $old_value && $new_value ) { return $old_value; }
        return $new_value;
    }, 10, 3 );
    add_action( 'updated_option', function ( $option, $old_value, $new_value ) {
        global $wpdb;
        if ( sscanf( $option, 'nggtags_for_media_library_taxonomy_slug_%d', $index ) !== 1 ) { return; }
        if ( $new_value ) { return; }
        // !$new_value means delete the taxonomy
        $ids = $wpdb->get_col( <<< EOD
SELECT r.object_id FROM $wpdb->term_relationships r, $wpdb->term_taxonomy x
    WHERE r.term_taxonomy_id = x.term_taxonomy_id AND x.taxonomy = "$old_value"
EOD
        );
        foreach ( $ids as $id ) { wp_delete_object_term_relationships( $id, $old_value ); }
        $ids = $wpdb->get_col( "SELECT term_id FROM $wpdb->term_taxonomy WHERE taxonomy = '$old_value'" );
        foreach ( $ids as $id ) { wp_delete_term( $id, $old_value ); }
    }, 10, 3 );
} );

if ( strpos( $_SERVER['REQUEST_URI'], 'nggtags-for-wp-media-library/upload.php' ) !== false ) {
    add_action( 'wp_loaded', function() {
        global $pagenow;
        $pagenow = 'upload.php';
    } );
    add_action('admin_head', function() {
    ?>
      <base href="<?php echo admin_url(); ?>">
    <?php
    } );
}

add_action( 'admin_init', function () {
    if ( isset( $_GET['page'] ) && $_GET['page'] === 'nggtags-for-wp-media-library/upload.php' ) {
        wp_redirect( plugins_url( 'upload.php', __FILE__ ) );
    }
}, 1 );

add_action( 'admin_menu', function () {
    add_options_page( 'Tags for Media Library Settings', 'Tags for Media Library',
        'manage_options', 'nggtags_for_media_library_settings_page', function () {
        echo( '<form method="post" action="options.php">' );
        settings_fields( 'nggtags_for_media_library_settings' ); 
        do_settings_sections( 'nggtags_for_media_library_settings_page' );
        submit_button();
        echo( '</form>' );
    } );
    add_media_page( 'Tags for Media Library', 'Tags for Media Library', 'edit_posts',
        'nggtags-for-wp-media-library/upload.php' );
}, 11 );
        
add_filter( 'plugin_action_links', function ( $actions, $plugin_file, $plugin_data, $context ) {
    if ( strpos( $plugin_file, basename( __FILE__ ) ) !== false ) {
        array_unshift( $actions, '<a href="' . admin_url( 'options-general.php?page=nggtags_for_media_library_settings_page' ) . '">'
            . __( 'Settings' ) . '</a>' );
    }
    return $actions;
}, 10, 4 );
        

add_action( 'init', function () {
    global $max_taxonomies;
    $taxonomy_count = $max_taxonomies; 
    for ( $i = 1; $i <= $taxonomy_count; $i++ ) {
        $taxonomy_slug = "nggtags_for_media_library_taxonomy_slug_$i";
        $taxonomy_name = "nggtags_for_media_library_taxonomy_name_$i";
        $taxonomy_name_value = get_option( $taxonomy_name );
        if ( $i === 1 ) {
            // create ngg_tag taxonomy for WordPress attachments.
            $taxonomy_slug_value = 'ngg_tag';
            if ( empty( $taxonomy_name_value ) ) { $taxonomy_name_value = 'NGG Tags'; }
        } else if ( $i === 2 ) {
            // priority taxonomy will be used to replace NextGEN Gallery's sortorder
            $taxonomy_slug_value = 'priority';
            if ( empty( $taxonomy_name_value ) ) { $taxonomy_name_value = 'Priority'; }
        } else {  
            $taxonomy_slug_value = get_option( $taxonomy_slug );
        }
        if ( empty( $taxonomy_slug_value ) ) { continue; }
        register_taxonomy( $taxonomy_slug_value, 'attachment', array(
            'label'              => __( $taxonomy_name_value ),
            'labels'             => array(
                                        'name'          => _x( $taxonomy_name_value, 'taxonomy general name' ),
                                        'singular_name' => _x( $taxonomy_name_value, 'taxonomy singular name' ),
                                    ),
            'show_ui'            => true,
            'show_admin_column'  => true,
            'rewrite'            => array( 'slug' => $taxonomy_slug_value )
        ) );
        register_taxonomy_for_object_type( $taxonomy_slug_value, 'attachment' );
    }
} );

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
    if ( !$ids ) { return $ids; }
    # get the priorities which are tags in the priority taxonomy 
    $list_ids = implode( ',', $ids );
    $sort_order = $wpdb->get_results( <<<EOD
SELECT r.object_id id, t.name priority FROM $wpdb->terms t, $wpdb->term_taxonomy x, $wpdb->term_relationships r
    WHERE t.term_id = x.term_id AND x.term_taxonomy_id = r.term_taxonomy_id AND x.taxonomy = 'priority'
        AND r.object_id IN ( $list_ids )       
EOD
        , OBJECT_K );
    uasort( $sort_order, function( $a, $b ) {
        if ( $a->priority == $b->priority ) {
            return $a->id == $b->id ? 0 : $a->id < $b->id ? -1 : 1;
        }
        return $a->priority < $b->priority ? -1 : 1;
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
        // rewrite the default taxonomy 'ngg_tag' entry to include the implied taxonomy specifier 'ngg_tag:'
        // the duplicate below is not a mistake - two passes are sometimes necessary
        $gallery = preg_replace( '#(^|;)(([a-z_-]+,)*[a-z_-]+)(;|$)#', '$1ngg_tag:$2$4', $gallery );
        $gallery = preg_replace( '#(^|;)(([a-z_-]+,)*[a-z_-]+)(;|$)#', '$1ngg_tag:$2$4', $gallery );
        $ids = Nggtags_for_Media_Library::get_posts_with_spec( 'attachment:' . $gallery );
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
    jQuery( "div#div-album-$count span.album-gallery-icon" ).click( function(e) {
        jQuery( "div#div-album-$count" ).css( "display", "none" );        
        jQuery( "div#div-galleries-$count div.hidden-gallery" ).css( "display", "none" );
        jQuery( "div#div-galleries-$count div#hidden-gallery-" + this.id.substr( 14 ) ).css( "display", "block" );
        e.stopImmediatePropagation();
        e.stopPropagation();
        e.preventDefault();
        e.returnValue=false;
        return false;
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
    $ids = $wpdb->get_col(
        "SELECT ID FROM $wpdb->posts WHERE post_type = 'attachment' AND post_mime_type LIKE 'image/%' AND post_parent = $id" );
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
    if ( !$_REQUEST || ( array_key_exists( 'action', $_REQUEST ) && $_REQUEST['action'] != -1 )
        || ( array_key_exists( 'action2', $_REQUEST ) && $_REQUEST['action2'] != -1 ) ) { return false; }
    if ( !array_key_exists( 's', $_REQUEST ) ) { return false; }
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

add_action( 'wp_ajax_nggml_update_screen_options', function( ) {
    #$nggml_screen_options = json_decode( $_POST['nggml_screen_options'] );
    # make sure numbers are stored as numbers not strings
    $nggml_screen_options = array_map( function( $v ) { return is_numeric($v) ? intval($v) : $v; },
        $_POST['nggml_screen_options'] );
    update_option( 'nggml_screen_options', $nggml_screen_options );
    die();
});

add_action( 'wp_ajax_nggml_get_image', function( ) {
    $id = (integer) $_GET['nggml_image_id'];
    $image = get_post( $id, OBJECT );
?>
<div class="nggml-overlay-right">
    <?php echo wp_get_attachment_image( $id, 'full' ); ?>
</div>
<div class="nggml-overlay-left">
    <h1><?php echo get_the_title( $id ); ?></h1>
    <div class="nggml-overlay-caption"><p><?php echo $image->post_excerpt; ?></p></div>
    <div class="nggml-overlay-description"><p><?php echo $image->post_content; ?></p></div>
</div>
<?php
    die();
});

add_action( 'wp_ajax_nggml_get_media_table_rows', function( ) {
    global $wp_query;
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
    require_once( ABSPATH . 'wp-admin/includes/class-wp-media-list-table.php' );
    require_once( dirname( __FILE__ ) . '/class-wp-media-list-table-for-nggtags.php' );
    $GLOBALS['hook_suffix']="nggtags-for-wp-media-library/upload.php";
    $wp_list_table = new \WP_Media_List_Table_for_Ngg_Tags( null );
    $wp_list_table->is_trash = false;
    query_posts( [ 'post_type' => 'attachment', 'post__in' => $_REQUEST['data'], 'post_status' => 'inherit',
        'posts_per_page' => -1 ] );
    $wp_list_table->display_rows();
    die;
} );

include_once( dirname( __FILE__ ) . '/nggtags-search-widget.php' );

if ( !is_admin() ) {
    add_action( 'wp_enqueue_scripts', function() {
        global $wp_scripts;
        wp_enqueue_style( 'nggml_search', plugins_url( 'nggml_search.css', __FILE__ ) );
        wp_enqueue_script( 'nggml-search', plugins_url( 'nggml-search.js', __FILE__ ), array( 'jquery' ) );
        $wp_scripts->add_data( 'nggml-search', 'data', 'var nggmlAltGalleryImageWidth='
            . get_option( 'nggml_alt_high_density_gallery_image_width', '64' ) . ';' );
    } );
}

?>
