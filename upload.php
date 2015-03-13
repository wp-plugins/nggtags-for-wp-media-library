<?php

# This is a specialization of ...\wp-admin\upload.php for NGG tags
# All injected/replacement code are suitable commented - search for "NGG Tags"

/**
 * Media Library administration panel.
 *
 * @package WordPress
 * @subpackage Administration
 */

 # TODO: is this neccessary
$_SERVER['PHP_SELF'] = preg_replace( '#wp-content(\\\\|/)plugins(\\\\|/)nggtags-for-wp-media-library#', 'wp-admin',
    $_SERVER['PHP_SELF'] );
    
/** WordPress Administration Bootstrap */
#require_once( dirname( __FILE__ ) . '/admin.php' ); - - replace this line with the NGG Tags version below
require_once( dirname(dirname(dirname(dirname(__FILE__)))) . '/wp-admin/admin.php' );

if ( !current_user_can('upload_files') )
	wp_die( __( 'You do not have permission to upload files.' ) );

$parent_file = 'upload.php';
$submenu_file = 'nggtags-for-wp-media-library/upload.php';

#$wp_list_table = _get_list_table('WP_Media_List_Table'); - replace this line with the following NGG Tags version
# Start of NGG Tags replacement code
require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
require_once( ABSPATH . 'wp-admin/includes/class-wp-media-list-table.php' );
require_once( dirname( __FILE__ ) . '/class-wp-media-list-table-for-nggtags.php' );

$nggml_explode = function( $delimiter, $string ) {
    if ( !$string ) { return []; }
    return array_map( function( $v ) { return( trim( $v ) ); }, explode( $delimiter, $string ) );
};

$wp_list_table = new WP_Media_List_Table_for_Ngg_Tags( null );
# End of NGG Tags replacement code

$pagenum = $wp_list_table->get_pagenum();

// Handle bulk actions
$doaction = $wp_list_table->current_action();

if ( $doaction ) {
    check_admin_referer('bulk-media');

    # Start of injected NGG Tags code that supports bulk addition/removal of taxonomy tags
  
    if ( 'add-tags' === $doaction ) {
        $ids = $_REQUEST['media'];
        foreach ( get_object_taxonomies( 'attachment' ) as $taxonomy_name ) {
            if ( $taxonomy_name === 'priority' ) { continue; }
            $taxonomy = get_taxonomy( $taxonomy_name );
            if ( !$taxonomy->show_ui || $taxonomy->hierarchical ) { continue; }
            $current = $nggml_explode( ',', $_REQUEST['tax_input'][$taxonomy->name] );
            $current = array_map( function( $v ) { return trim( $v ); }, $current );
            $initial = $nggml_explode( ',', $_REQUEST['nggtags-for-ml-initial'][$taxonomy->name] );
            $added = array_intersect( array_unique(
                $nggml_explode( ',', ltrim( $_REQUEST['nggtags-for-ml-added'][$taxonomy->name], ',' ) ) ), $current );
            $removed = array_map( function( $name ) use ( &$taxonomy ) {
                $term = get_term_by( 'name', $name, $taxonomy->name );
                return (string)$term->slug;
            }, array_diff( $initial, $current ) );
            foreach ( $ids as $id ) {
                wp_set_post_terms( $id, $added, $taxonomy->name, true );
                ##### TODO: replace with wp_set_object_terms()?
                wp_remove_object_terms( $id, $removed, $taxonomy->name );
            }
        }
    } else if ( 'edit-priority' === $doaction ) {
        $start = $_REQUEST['nggml-bulk-priority-edit-start'];
        $increment =  $_REQUEST['nggml-bulk-priority-edit-increment'];
        if ( !is_numeric( $start ) || !is_numeric( $increment ) ) {
            wp_die( __( 'Invalid start or increment value.' ) );
        }
        $start = (integer) $start;
        $increment = (integer) $increment;
        if ( $start <= 0 || $increment <= 0 ) {
            wp_die( __( 'Invalid start or increment value.' ) );
        }        
        $order = $_REQUEST['nggml-bulk-priority-edit-order'];
        if ( $order !== 'reverted' ) {
            $order = $nggml_explode( ';', rtrim( $order, ';' ) );
            if ( !is_array( $order ) || !$order ) {
                wp_die( __( 'Invalid list of image ids.' ) );
            }
            $order = array_map( function( $id ) {
                return substr( $id, 5);
            }, $order );    
            for ($priority = $start; ; $priority += $increment ) {
                $id = array_shift( $order );
                wp_set_post_terms( $id, (string) $priority, 'priority', false );
                if ( !$order ) {
                    break;
                }
            }
        }
	} else
    
    # End of injected NGG Tags code that supports bulk addition/removal of taxonomy tags
    
    if ( 'delete_all' == $doaction ) {
		$post_ids = $wpdb->get_col( "SELECT ID FROM $wpdb->posts WHERE post_type='attachment' AND post_status = 'trash'" );
		$doaction = 'delete';
	} elseif ( isset( $_REQUEST['media'] ) ) {
		$post_ids = $_REQUEST['media'];
	} elseif ( isset( $_REQUEST['ids'] ) ) {
		$post_ids = $nggml_explode( ',', $_REQUEST['ids'] );
	}

	$location = 'upload.php';
	if ( $referer = wp_get_referer() ) {
		if ( false !== strpos( $referer, 'upload.php' ) )
			$location = remove_query_arg( array( 'trashed', 'untrashed', 'deleted', 'message', 'ids', 'posted' ), $referer );
	}

	switch ( $doaction ) {
		case 'find_detached':
			if ( !current_user_can('edit_posts') )
				wp_die( __('You are not allowed to scan for lost attachments.') );

			$lost = $wpdb->get_col( "
				SELECT ID FROM $wpdb->posts
				WHERE post_type = 'attachment' AND post_parent > '0'
				AND post_parent NOT IN (
					SELECT ID FROM $wpdb->posts
					WHERE post_type NOT IN ( 'attachment', '" . join( "', '", get_post_types( array( 'public' => false ) ) ) . "' )
				)
			" );

			$_REQUEST['detached'] = 1;
			break;
		case 'attach':
			$parent_id = (int) $_REQUEST['found_post_id'];
			if ( !$parent_id )
				return;

			$parent = get_post( $parent_id );
			if ( !current_user_can( 'edit_post', $parent_id ) )
				wp_die( __( 'You are not allowed to edit this post.' ) );

			$attach = array();
			foreach ( (array) $_REQUEST['media'] as $att_id ) {
				$att_id = (int) $att_id;

				if ( !current_user_can( 'edit_post', $att_id ) )
					continue;

				$attach[] = $att_id;
			}

			if ( ! empty( $attach ) ) {
				$attach_string = implode( ',', $attach );
				$attached = $wpdb->query( $wpdb->prepare( "UPDATE $wpdb->posts SET post_parent = %d WHERE post_type = 'attachment' AND ID IN ( $attach_string )", $parent_id ) );
				foreach ( $attach as $att_id ) {
					clean_attachment_cache( $att_id );
				}
			}

			if ( isset( $attached ) ) {
				$location = 'upload.php';
				if ( $referer = wp_get_referer() ) {
					if ( false !== strpos( $referer, 'upload.php' ) )
						$location = $referer;
				}

				$location = add_query_arg( array( 'attached' => $attached ) , $location );
				wp_redirect( $location );
				exit;
			}
			break;
		case 'trash':
			if ( !isset( $post_ids ) )
				break;
			foreach ( (array) $post_ids as $post_id ) {
				if ( !current_user_can( 'delete_post', $post_id ) )
					wp_die( __( 'You are not allowed to move this post to the trash.' ) );

				if ( !wp_trash_post( $post_id ) )
					wp_die( __( 'Error in moving to trash.' ) );
			}
			$location = add_query_arg( array( 'trashed' => count( $post_ids ), 'ids' => join( ',', $post_ids ) ), $location );
			break;
		case 'untrash':
			if ( !isset( $post_ids ) )
				break;
			foreach ( (array) $post_ids as $post_id ) {
				if ( !current_user_can( 'delete_post', $post_id ) )
					wp_die( __( 'You are not allowed to move this post out of the trash.' ) );

				if ( !wp_untrash_post( $post_id ) )
					wp_die( __( 'Error in restoring from trash.' ) );
			}
			$location = add_query_arg( 'untrashed', count( $post_ids ), $location );
			break;
		case 'delete':
			if ( !isset( $post_ids ) )
				break;
			foreach ( (array) $post_ids as $post_id_del ) {
				if ( !current_user_can( 'delete_post', $post_id_del ) )
					wp_die( __( 'You are not allowed to delete this post.' ) );

				if ( !wp_delete_attachment( $post_id_del ) )
					wp_die( __( 'Error in deleting.' ) );
			}
			$location = add_query_arg( 'deleted', count( $post_ids ), $location );
			break;
	}

	wp_redirect( $location );
	exit;
} elseif ( ! empty( $_GET['_wp_http_referer'] ) ) {
	 wp_redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce' ), wp_unslash( $_SERVER['REQUEST_URI'] ) ) );
	 exit;
}

# Start of injected NGG Tags code that does the additional filtering on tag taxonomies

# since we want to order by priority we need to join with the taxonomy tables

add_filter( 'posts_join', function( $join ) {
    global $wpdb;
    if ( strpos( $_SERVER['REQUEST_URI'], 'nggtags-for-wp-media-library/upload.php' ) === false ) { return $join; }
    return $join . <<< EOD
        LEFT JOIN ( SELECT $wpdb->term_relationships.object_id, $wpdb->terms.name
            FROM $wpdb->term_relationships
            INNER JOIN $wpdb->term_taxonomy ON $wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id
            INNER JOIN $wpdb->terms ON $wpdb->term_taxonomy.term_id = $wpdb->terms.term_id
            WHERE $wpdb->term_taxonomy.taxonomy = 'priority'
        ) priority ON $wpdb->posts.ID = priority.object_id
EOD;
} );

add_filter( 'posts_where', function( $where ) {
    global $wpdb;
    if ( strpos( $_SERVER['REQUEST_URI'], 'nggtags-for-wp-media-library/upload.php' ) === false ) { return $where; }
    if ( array_key_exists( 'filter-post-mime-type', $_REQUEST )
        && array_search( '0-nggml-all', $_REQUEST['filter-post-mime-type'] ) === FALSE ) {
        $where .= " AND $wpdb->posts.post_mime_type IN ( " . implode( ', ', array_map( function( $slug ) {
            global $wpdb;
            return $wpdb->prepare( '%s', $slug );
        }, $_REQUEST['filter-post-mime-type'] ) ) . ' ) ';
    }
    if ( array_key_exists( 'filter-attached-to', $_REQUEST )
        && array_search( '0-nggml-all', $_REQUEST['filter-attached-to'] ) === FALSE ) {
        $where .= " AND $wpdb->posts.post_parent IN ( " . implode( ', ', array_map( function( $id ) {
            global $wpdb;
            return $wpdb->prepare( '%d', $id );
        }, $_REQUEST['filter-attached-to'] ) ) . ' ) ';
    }
    $ids = false;
    foreach ( $_REQUEST as $taxonomy => $slugs ) {
        if ( substr_compare( $taxonomy, 'filter-', 0, 7 ) !== 0 ) { continue; }
        if ( $taxonomy === 'filter-post-mime-type' || $taxonomy === 'filter-attached-to' ) { continue; }
        if ( $slugs[0] === '0-nggml-all' ) { continue; }
        # select is now multiple value so $slugs is an array of taxonomy slugs
        $slugs = implode( ', ', array_map( function( $slug ) {
            global $wpdb;
            return $wpdb->prepare( '%s', $slug );
        }, $slugs ) );
        $taxonomy = $wpdb->prepare( '%s', substr( $taxonomy, 7 ) );
        # get all objects with $taxonomy slug in $slugs
        $sql = <<<EOD
          SELECT r.object_id FROM $wpdb->term_relationships r, $wpdb->term_taxonomy x, $wpdb->terms t
            WHERE r.term_taxonomy_id = x.term_taxonomy_id AND x.term_id = t.term_id
              AND x.taxonomy = $taxonomy and t.slug IN ( $slugs )
EOD;
        $objects = $wpdb->get_col( $sql );
        if ( !$objects ) { return $where . ' AND  1 = 2 '; }
        if ( $ids ) {
          $ids = array_intersect( $ids, $objects );
          if ( !$ids ) { return $where . ' AND  1 = 2 '; }
        } else {
          $ids = $objects;
        }
    }
    if ( $ids === false ) { return $where; }
    $where .= " AND $wpdb->posts.ID IN ( " . implode( ', ', $ids ) . ' ) ';
    return $where;
} );

# order by priority using the join with the 'priority' taxonomy table

add_filter( 'posts_orderby', function( $order_by ) {
    global $wpdb;
    if ( strpos( $_SERVER['REQUEST_URI'], 'nggtags-for-wp-media-library/upload.php' ) === false ) { return $order_by; }
    return 'CAST( IF ( priority.name IS NOT NULL, priority.name, ~0 ) AS UNSIGNED ) ASC, '
        . "CAST( IF ( $wpdb->posts.ID IS NOT NULL, $wpdb->posts.ID, ~0 ) AS UNSIGNED ) ASC";
} );

#add_filter( 'posts_request', function( $request ) {
#    if ( strpos( $_SERVER['REQUEST_URI'], 'nggtags-for-wp-media-library/upload.php' ) === false ) { return $request; }
#    return $request;
#} );

# End of injected NGG Tags code that does the additional filtering on tag taxonomies

$wp_list_table->prepare_items();

#$title = __('Media Library'); - replace this line with the NGG Tags version below
$title = __('Tags for Media Library');
$parent_file = 'upload.php';

#wp_enqueue_script( 'media' );

# Start of injected NGG Tags code that loads my client side javascripts

# extracted from .../wp-includes/media.php

function ngg_wp_plupload_default_settings() {
	global $wp_scripts;

	$data = $wp_scripts->get_data( 'nggtags-media', 'data' );
	if ( $data && false !== strpos( $data, '_wpPluploadSettings' ) )
		return;

	$max_upload_size = wp_max_upload_size();

	$defaults = array(
		'runtimes'            => 'html5,flash,silverlight,html4',
		'file_data_name'      => 'async-upload', // key passed to $_FILE.
		'url'                 => admin_url( 'async-upload.php', 'relative' ),
		'flash_swf_url'       => includes_url( 'js/plupload/plupload.flash.swf' ),
		'silverlight_xap_url' => includes_url( 'js/plupload/plupload.silverlight.xap' ),
		'filters' => array(
			'max_file_size'   => $max_upload_size . 'b',
		),
	);

	/**
	 * Filter the Plupload default settings.
	 *
	 * @since 3.4.0
	 *
	 * @param array $defaults Default Plupload settings array.
	 */
	$defaults = apply_filters( 'plupload_default_settings', $defaults );

	$params = array(
		'action' => 'upload-attachment',
	);

	/**
	 * Filter the Plupload default parameters.
	 *
	 * @since 3.4.0
	 *
	 * @param array $params Default Plupload parameters array.
	 */
	$params = apply_filters( 'plupload_default_params', $params );
	$params['_wpnonce'] = wp_create_nonce( 'media-form' );
	$defaults['multipart_params'] = $params;

	$settings = array(
		'defaults' => $defaults,
		'browser'  => array(
			'mobile'    => wp_is_mobile(),
			'supported' => _device_can_upload(),
		),
		'limitExceeded' => is_multisite() && ! is_upload_space_available()
	);

	$script = 'var _wpPluploadSettings = ' . json_encode( $settings ) . ';';

	if ( $data )
		$script = "$data\n$script";

	$wp_scripts->add_data( 'nggtags-media', 'data', $script );
}

function ngg_upload( ) {
	$strings = array(
		// Generic
		'url'         => __( 'URL' ),
		'addMedia'    => __( 'Add Media' ),
		'search'      => __( 'Search' ),
		'select'      => __( 'Select' ),
		'cancel'      => __( 'Cancel' ),
		'update'      => __( 'Update' ),
		'replace'     => __( 'Replace' ),
		'remove'      => __( 'Remove' ),
		'back'        => __( 'Back' ),
		/* translators: This is a would-be plural string used in the media manager.
		   If there is not a word you can use in your language to avoid issues with the
		   lack of plural support here, turn it into "selected: %d" then translate it.
		 */
		'selected'    => __( '%d selected' ),
		'dragInfo'    => __( 'Drag and drop to reorder images.' ),

		// Upload
		'uploadFilesTitle'  => __( 'Upload Files' ),
		'uploadImagesTitle' => __( 'Upload Images' ),

		// Library
		'mediaLibraryTitle'      => __( 'Media Library' ),
		'insertMediaTitle'       => __( 'Insert Media' ),
		'createNewGallery'       => __( 'Create a new gallery' ),
		'createNewPlaylist'      => __( 'Create a new playlist' ),
		'createNewVideoPlaylist' => __( 'Create a new video playlist' ),
		'returnToLibrary'        => __( '&#8592; Return to library' ),
		'allMediaItems'          => __( 'All media items' ),
		'allMediaTypes'          => __( 'All media types' ),
		'allDates'               => __( 'All dates' ),
		'noItemsFound'           => __( 'No items found.' ),
		'insertIntoPost'         => __( 'Insert into post' ),
		'unattached'             => __( 'Unattached' ),
		'trash'                  => __( 'Trash' ),
		'uploadedToThisPost'     => __( 'Uploaded to this post' ),
		'warnDelete'             => __( "You are about to permanently delete this item.\n  'Cancel' to stop, 'OK' to delete." ),
		'warnBulkDelete'         => __( "You are about to permanently delete these items.\n  'Cancel' to stop, 'OK' to delete." ),
		'warnBulkTrash'          => __( "You are about to trash these items.\n  'Cancel' to stop, 'OK' to delete." ),
		'bulkSelect'             => __( 'Bulk Select' ),
		'cancelSelection'        => __( 'Cancel Selection' ),
		'trashSelected'          => __( 'Trash Selected' ),
		'untrashSelected'        => __( 'Untrash Selected' ),
		'deleteSelected'         => __( 'Delete Selected' ),
		'deletePermanently'      => __( 'Delete Permanently' ),
		'apply'                  => __( 'Apply' ),
		'filterByDate'           => __( 'Filter by date' ),
		'filterByType'           => __( 'Filter by type' ),
		'searchMediaLabel'       => __( 'Search Media' ),

		// Library Details
		'attachmentDetails'  => __( 'Attachment Details' ),

		// From URL
		'insertFromUrlTitle' => __( 'Insert from URL' ),

		// Featured Images
		'setFeaturedImageTitle' => __( 'Set Featured Image' ),
		'setFeaturedImage'    => __( 'Set featured image' ),

		// Gallery
		'createGalleryTitle' => __( 'Create Gallery' ),
		'editGalleryTitle'   => __( 'Edit Gallery' ),
		'cancelGalleryTitle' => __( '&#8592; Cancel Gallery' ),
		'insertGallery'      => __( 'Insert gallery' ),
		'updateGallery'      => __( 'Update gallery' ),
		'addToGallery'       => __( 'Add to gallery' ),
		'addToGalleryTitle'  => __( 'Add to Gallery' ),
		'reverseOrder'       => __( 'Reverse order' ),

		// Edit Image
		'imageDetailsTitle'     => __( 'Image Details' ),
		'imageReplaceTitle'     => __( 'Replace Image' ),
		'imageDetailsCancel'    => __( 'Cancel Edit' ),
		'editImage'             => __( 'Edit Image' ),

		// Crop Image
		'chooseImage' => __( 'Choose Image' ),
		'selectAndCrop' => __( 'Select and Crop' ),
		'skipCropping' => __( 'Skip Cropping' ),
		'cropImage' => __( 'Crop Image' ),
		'cropYourImage' => __( 'Crop your image' ),
		'cropping' => __( 'Cropping&hellip;' ),
		'suggestedDimensions' => __( 'Suggested image dimensions:' ),
		'cropError' => __( 'There has been an error cropping your image.' ),

		// Edit Audio
		'audioDetailsTitle'     => __( 'Audio Details' ),
		'audioReplaceTitle'     => __( 'Replace Audio' ),
		'audioAddSourceTitle'   => __( 'Add Audio Source' ),
		'audioDetailsCancel'    => __( 'Cancel Edit' ),

		// Edit Video
		'videoDetailsTitle'     => __( 'Video Details' ),
		'videoReplaceTitle'     => __( 'Replace Video' ),
		'videoAddSourceTitle'   => __( 'Add Video Source' ),
		'videoDetailsCancel'    => __( 'Cancel Edit' ),
		'videoSelectPosterImageTitle' => __( 'Select Poster Image' ),
		'videoAddTrackTitle'	=> __( 'Add Subtitles' ),

 		// Playlist
 		'playlistDragInfo'    => __( 'Drag and drop to reorder tracks.' ),
 		'createPlaylistTitle' => __( 'Create Audio Playlist' ),
 		'editPlaylistTitle'   => __( 'Edit Audio Playlist' ),
 		'cancelPlaylistTitle' => __( '&#8592; Cancel Audio Playlist' ),
 		'insertPlaylist'      => __( 'Insert audio playlist' ),
 		'updatePlaylist'      => __( 'Update audio playlist' ),
 		'addToPlaylist'       => __( 'Add to audio playlist' ),
 		'addToPlaylistTitle'  => __( 'Add to Audio Playlist' ),

 		// Video Playlist
 		'videoPlaylistDragInfo'    => __( 'Drag and drop to reorder videos.' ),
 		'createVideoPlaylistTitle' => __( 'Create Video Playlist' ),
 		'editVideoPlaylistTitle'   => __( 'Edit Video Playlist' ),
 		'cancelVideoPlaylistTitle' => __( '&#8592; Cancel Video Playlist' ),
 		'insertVideoPlaylist'      => __( 'Insert video playlist' ),
 		'updateVideoPlaylist'      => __( 'Update video playlist' ),
 		'addToVideoPlaylist'       => __( 'Add to video playlist' ),
 		'addToVideoPlaylistTitle'  => __( 'Add to Video Playlist' ),

 		// Media Library
 		'editMetadata' => __( 'Edit Metadata' ),
 		'noMedia'      => __( 'No media attachments found.' ),
	);
  
  $uploader_l10n = array(
		'queue_limit_exceeded' => __('You have attempted to queue too many files.'),
		'file_exceeds_size_limit' => __('%s exceeds the maximum upload size for this site.'),
		'zero_byte_file' => __('This file is empty. Please try another.'),
		'invalid_filetype' => __('This file type is not allowed. Please try another.'),
		'not_an_image' => __('This file is not an image. Please try another.'),
		'image_memory_exceeded' => __('Memory exceeded. Please try another smaller file.'),
		'image_dimensions_exceeded' => __('This is larger than the maximum size. Please try another.'),
		'default_error' => __('An error occurred in the upload. Please try again later.'),
		'missing_upload_url' => __('There was a configuration error. Please contact the server administrator.'),
		'upload_limit_exceeded' => __('You may only upload 1 file.'),
		'http_error' => __('HTTP error.'),
		'upload_failed' => __('Upload failed.'),
		'big_upload_failed' => __('Please try uploading this file with the %1$sbrowser uploader%2$s.'),
		'big_upload_queued' => __('%s exceeds the maximum upload size for the multi-file uploader when used in your browser.'),
		'io_error' => __('IO error.'),
		'security_error' => __('Security error.'),
		'file_cancelled' => __('File canceled.'),
		'upload_stopped' => __('Upload stopped.'),
		'dismiss' => __('Dismiss'),
		'crunching' => __('Crunching&hellip;'),
		'deleted' => __('moved to the trash.'),
		'error_uploading' => __('&#8220;%s&#8221; has failed to upload.')
	);
  
    wp_register_script( 'media-models-for-nggtags', plugins_url( 'media-models-for-nggtags.js', __FILE__ ),
        array( 'wp-backbone' ) );
    wp_localize_script( 'media-models-for-nggtags', 'nggmlServerParametersForNggTags', [ 'screenPerPage' => 'TODO:' ] );
    wp_register_script( 'media-for-nggtags', plugins_url( 'media-for-nggtags.js', __FILE__ ),
        array( 'media-models-for-nggtags' ) );
    wp_register_script( 'nggtags-media', plugins_url( 'nggtags-media.js', __FILE__ ),
        array( 'plupload', 'jquery', 'json2', 'media-models-for-nggtags', 'media-for-nggtags' ) );
    wp_localize_script( 'nggtags-media', '_wpMediaViewsL10n', $strings );
    wp_localize_script( 'nggtags-media', 'pluploadL10n', $uploader_l10n );  
    ngg_wp_plupload_default_settings();
    
    wp_enqueue_style( 'nggtags-media', plugins_url( 'nggtags-media.css', __FILE__ ) );
    wp_enqueue_style( 'media-views' );
    wp_enqueue_script( 'media-models-for-nggtags' );
    wp_enqueue_script( 'media-for-nggtags' );
    wp_enqueue_script( 'inline-edit-nggtags-media', plugins_url( 'inline-edit-nggtags-media.js', __FILE__ ),
        array( 'jquery', 'suggest', 'heartbeat' ) );
    wp_enqueue_script( 'nggtags-media' );
    wp_enqueue_script( 'wp-mediaelement' );
    wp_enqueue_script( 'image-edit' );
    wp_enqueue_style( 'imgareaselect' );

    require_once ABSPATH . WPINC . '/media-template.php';
    add_action( 'admin_footer', 'wp_print_media_templates' );
};
ngg_upload();
# End of injected NGG Tags code that loads my client side javascripts

add_screen_option( 'per_page', array('label' => _x( 'Media items', 'items per page (screen options)' )) );

get_current_screen()->add_help_tab( array(
'id'		=> 'overview',
'title'		=> __('Overview'),
'content'	=>
	'<p>' . __( 'All the files you&#8217;ve uploaded are listed in the Media Library, with the most recent uploads listed first. You can use the Screen Options tab to customize the display of this screen.' ) . '</p>' .
	'<p>' . __( 'You can narrow the list by file type/status using the text link filters at the top of the screen. You also can refine the list by date using the dropdown menu above the media table.' ) . '</p>'
) );
get_current_screen()->add_help_tab( array(
'id'		=> 'actions-links',
'title'		=> __('Available Actions'),
'content'	=>
	'<p>' . __( 'Hovering over a row reveals action links: Edit, Delete Permanently, and View. Clicking Edit or on the media file&#8217;s name displays a simple screen to edit that individual file&#8217;s metadata. Clicking Delete Permanently will delete the file from the media library (as well as from any posts to which it is currently attached). View will take you to the display page for that file.' ) . '</p>'
) );
get_current_screen()->add_help_tab( array(
'id'		=> 'attaching-files',
'title'		=> __('Attaching Files'),
'content'	=>
	'<p>' . __( 'If a media file has not been attached to any post, you will see that in the Attached To column, and can click on Attach File to launch a small popup that will allow you to search for a post and attach the file.' ) . '</p>'
) );

get_current_screen()->set_help_sidebar(
	'<p><strong>' . __( 'For more information:' ) . '</strong></p>' .
	'<p>' . __( '<a href="http://codex.wordpress.org/Media_Library_Screen" target="_blank">Documentation on Media Library</a>' ) . '</p>' .
	'<p>' . __( '<a href="https://wordpress.org/support/" target="_blank">Support Forums</a>' ) . '</p>'
);

require_once( ABSPATH . 'wp-admin/admin-header.php' );
?>

<div class="wrap">
<h2>
<?php
echo esc_html( $title );
if ( current_user_can( 'upload_files' ) ) { ?>
	<a href="media-new.php" class="add-new-h2"><?php echo esc_html_x('Add New', 'file'); ?></a><?php
}
if ( ! empty( $_REQUEST['s'] ) )
	printf( '<span class="subtitle">' . __('Search results for &#8220;%s&#8221;') . '</span>', get_search_query() ); ?>
</h2>

<?php
$message = '';
if ( ! empty( $_GET['posted'] ) ) {
	$message = __('Media attachment updated.');
	$_SERVER['REQUEST_URI'] = remove_query_arg(array('posted'), $_SERVER['REQUEST_URI']);
}

if ( ! empty( $_GET['attached'] ) && $attached = absint( $_GET['attached'] ) ) {
	$message = sprintf( _n('Reattached %d attachment.', 'Reattached %d attachments.', $attached), $attached );
	$_SERVER['REQUEST_URI'] = remove_query_arg(array('attached'), $_SERVER['REQUEST_URI']);
}

if ( ! empty( $_GET['deleted'] ) && $deleted = absint( $_GET['deleted'] ) ) {
	$message = sprintf( _n( 'Media attachment permanently deleted.', '%d media attachments permanently deleted.', $deleted ), number_format_i18n( $_GET['deleted'] ) );
	$_SERVER['REQUEST_URI'] = remove_query_arg(array('deleted'), $_SERVER['REQUEST_URI']);
}

if ( ! empty( $_GET['trashed'] ) && $trashed = absint( $_GET['trashed'] ) ) {
	$message = sprintf( _n( 'Media attachment moved to the trash.', '%d media attachments moved to the trash.', $trashed ), number_format_i18n( $_GET['trashed'] ) );
	$message .= ' <a href="' . esc_url( wp_nonce_url( 'upload.php?doaction=undo&action=untrash&ids='.(isset($_GET['ids']) ? $_GET['ids'] : ''), "bulk-media" ) ) . '">' . __('Undo') . '</a>';
	$_SERVER['REQUEST_URI'] = remove_query_arg(array('trashed'), $_SERVER['REQUEST_URI']);
}

if ( ! empty( $_GET['untrashed'] ) && $untrashed = absint( $_GET['untrashed'] ) ) {
	$message = sprintf( _n( 'Media attachment restored from the trash.', '%d media attachments restored from the trash.', $untrashed ), number_format_i18n( $_GET['untrashed'] ) );
	$_SERVER['REQUEST_URI'] = remove_query_arg(array('untrashed'), $_SERVER['REQUEST_URI']);
}

$messages[1] = __('Media attachment updated.');
$messages[2] = __('Media permanently deleted.');
$messages[3] = __('Error saving media attachment.');
$messages[4] = __('Media moved to the trash.') . ' <a href="' . esc_url( wp_nonce_url( 'upload.php?doaction=undo&action=untrash&ids='.(isset($_GET['ids']) ? $_GET['ids'] : ''), "bulk-media" ) ) . '">' . __('Undo') . '</a>';
$messages[5] = __('Media restored from the trash.');

if ( ! empty( $_GET['message'] ) && isset( $messages[ $_GET['message'] ] ) ) {
	$message = $messages[ $_GET['message'] ];
	$_SERVER['REQUEST_URI'] = remove_query_arg(array('message'), $_SERVER['REQUEST_URI']);
}

if ( !empty($message) ) { ?>
<div id="message" class="updated"><p><?php echo $message; ?></p></div>
<?php } ?>

<?php $wp_list_table->views(); ?>
<div id="uploader-inline-container"></div>

<?php
$nggml_screen_options = get_option( 'nggml_screen_options',
    [ 'use_checkbox_overlay_for_select' => 1, 'use_alt_media_list_pane' => 1 ] );
    
function view_switcher( &$nggml_screen_options ) {
    $modes = [ 'list' => __( 'List View' ), 'grid' => __( 'Grid View' ) ];
?>
<div class="view-switch" style="display:inline-block;">
<?php
    foreach ( $modes as $mode => $title ) {
        $classes = [ 'view-' . $mode ];
        if ( ( $mode === 'grid' && $nggml_screen_options['use_alt_media_list_pane'] )
            || ( $mode === 'list' && !$nggml_screen_options['use_alt_media_list_pane'] ) ) {
            $classes[] = 'current';
        }
        printf("<a href='#' class='%s' id='view-switch-$mode'><span class='screen-reader-text'>%s</span></a>\n",
            implode( ' ', $classes ), $title );
    }
?>
</div>
<?php
}
?>
<form id="posts-filter" action="" method="get">

<?php view_switcher( $nggml_screen_options ); ?>

<?php $wp_list_table->search_box( __( 'Search Media' ), 'media' ); ?>

<?php $wp_list_table->display(); ?>

<div id="ajax-response"></div>
<?php find_posts_div(); ?> 
</form>

<!-- Start of injected NGG Tags code to support screen option state -->

<form>
<input type="hidden" id="nggml-screen-options" value='<?php echo json_encode( (object) $nggml_screen_options ); ?>'>
</form>

<!-- End of injected NGG Tags code to support screen option state -->

<!-- Start of injected NGG Tags code to support inline bulk editing -->

<?php
if ( $wp_list_table->has_items() ) {
	$wp_list_table->inline_edit();
}
?>

<!-- End of injected NGG Tags code to support inline bulk editing -->

</div>

<?php
include( ABSPATH . 'wp-admin/admin-footer.php' );
