<?php

# This is a specialization of ...\wp-admin\upload.php for NGG tags
# All injected/replacement code are suitable commented - search for "NGG Tags"

/**
 * Media Library administration panel.
 *
 * @package WordPress
 * @subpackage Administration
 */

/** WordPress Administration Bootstrap */
#require_once( dirname( __FILE__ ) . '/admin.php' ); - - replace this line with the NGG Tags version below
require_once( dirname(dirname(dirname(dirname(__FILE__)))) . '/wp-admin/admin.php' );

if ( !current_user_can('upload_files') )
	wp_die( __( 'You do not have permission to upload files.' ) );

#$wp_list_table = _get_list_table('WP_Media_List_Table'); - replace this line with the following NGG Tags version
# Start of NGG Tags replacement code
require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
require_once( ABSPATH . 'wp-admin/includes/class-wp-media-list-table.php' );
require_once( dirname( __FILE__ ) . '/class-wp-media-list-table-for-nggtags.php' );
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
            $taxonomy = get_taxonomy( $taxonomy_name );
            if ( !$taxonomy->show_ui || $taxonomy->hierarchical ) { continue; }
            $current = explode( ',', $_REQUEST['tax_input'][$taxonomy->name] );
            $current = array_map( function( $v ) { return trim( $v ); }, $current );
            $initial = explode( ',', $_REQUEST['nggtags-for-ml-initial'][$taxonomy->name] );
            $added = array_intersect( array_unique(
                explode( ',', ltrim( $_REQUEST['nggtags-for-ml-added'][$taxonomy->name], ',' ) ) ), $current );
            $removed = array_map( function( $name ) use ( &$taxonomy ) {
                $term = get_term_by( 'name', $name, $taxonomy->name );
                return (string)$term->slug;
            }, array_diff( $initial, $current ) );    
            foreach ( $ids as $id ) {
                wp_set_post_terms( $id, $added, $taxonomy->name, true );
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
            $order = explode( ';', rtrim( $order, ';' ) );
            if ( !is_array( $order )|| !order ) {
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
		$post_ids = explode( ',', $_REQUEST['ids'] );
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
    $ids = false;
    foreach ( $_REQUEST as $taxonomy => $slugs ) {
        if ( substr_compare( $taxonomy, 'filter-', 0, 7 ) !== 0 ) { continue; }
        if ( $slugs[0] === '0-nggml-all' ) { continue; }
        # select is now multiple value so $slugs is an array of taxonomy slugs
        $slugs = implode( ', ', array_map( function( $slug ) {
            global $wpdb;
            return $wpdb->prepare( '%s', $slug );
        }, $slugs ) );
        $taxonomy = $wpdb->prepare( '%s', substr( $taxonomy, 7 ) );
        # get all objects with $taxonomy slug in $slugs
        $sql = <<<EOD
          SELECT r.object_id FROM wp_term_relationships r, wp_term_taxonomy x, wp_terms t
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
    $where .= ' AND wp_posts.ID IN ( ' . implode( ', ', $ids ) . ' ) ';
    return $where;
} );

# order by priority using the join with the 'priority' taxonomy table

add_filter( 'posts_orderby', function( $order_by ) {
    global $wpdb;
    if ( strpos( $_SERVER['REQUEST_URI'], 'nggtags-for-wp-media-library/upload.php' ) === false ) { return $order_by; }
    return "CAST( IF ( priority.name IS NOT NULL, priority.name, ~0 ) AS UNSIGNED ) ASC";
} );

#add_filter( 'posts_request', function( $request ) {
#    if ( strpos( $_SERVER['REQUEST_URI'], 'nggtags-for-wp-media-library/upload.php' ) === false ) { return $request; }
#    return $request;
#} );

# End of injected NGG Tags code that does the additional filtering on tag taxonomies

$wp_list_table->prepare_items();

#$title = __('Media Library'); - replace this line with the NGG Tags version below
$title = __('Media Library for Ngg Tags');
$parent_file = 'upload.php';

wp_enqueue_script( 'media' );

# Start of injected NGG Tags code that loads my client side javascripts
wp_enqueue_style( 'nggtags-media', plugins_url( 'nggtags-media.css', __FILE__ ) );
wp_enqueue_script( 'nggtags-media', plugins_url( 'nggtags-media.js', __FILE__ ), array( 'jquery' ) );
wp_enqueue_script( 'inline-edit-nggtags-media', plugins_url( 'inline-edit-nggtags-media.js', __FILE__ ),
    array( 'jquery', 'suggest', 'heartbeat' ) );
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

<form id="posts-filter" action="" method="get">

<?php $wp_list_table->search_box( __( 'Search Media' ), 'media' ); ?>

<?php $wp_list_table->display(); ?>

<div id="ajax-response"></div>
<?php find_posts_div(); ?> 
</form>

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
