<?php

require_once( ABSPATH . 'wp-admin/includes/meta-boxes.php' );

$nggtags_for_wp_media_library_in_inline_edit_tags_to_edit = null;

# class WP_Media_List_Table_for_Ngg_Tags is a specialization of WP_Media_List_Table that supports Ngg_Tags

class WP_Media_List_Table_for_Ngg_Tags extends WP_Media_List_Table {

    function __construct( $args = array() ) {
        parent::__construct( $args );
    }
    
    # function get_bulk_actions() modified to add bulk add/remove tag actions
    
    function get_bulk_actions() {
        $actions = parent::get_bulk_actions();
        # add support for bulk addition and removal of tags
        $actions['add-tags'] = __( 'Add/Remove Tags' );
        # add support for bulk setting of priorities
        $actions['edit-priority'] = __( 'Set Priorities' );
        return $actions;
    }
    
    # function taxonomy_filter() is a helper function for use by extra_tablenav()
    
    function taxonomy_filter( $taxonomy, $name, $hidden ) {
        global $wpdb;
        #$terms = get_terms( $taxonomy, array( 'orderby' => 'count', 'order' => 'DESC', 'fields' => 'all' ) );
        $terms = $wpdb->get_results( <<<EOD
            SELECT t.slug, t.name, COUNT(*) count
                FROM $wpdb->terms t, $wpdb->term_taxonomy x, $wpdb->term_relationships r, $wpdb->posts p
                WHERE t.term_id = x.term_id AND x.term_taxonomy_id = r.term_taxonomy_id AND r.object_id = p.ID
                    AND x.taxonomy = "$taxonomy" AND p.post_type = "attachment"
                GROUP BY t.slug ORDER BY count DESC
EOD
            , OBJECT );
?>
		<div style="position:relative;top:0px;left:0px;width:0px;height:0px;margin:0px;display:block;float:left;z-index:1000;">
			<div class="nggml-filter-checkbox-overlay"
				style="position:absolute;top:0px;left:0px;width:100px;padding:10px;border:1px solid black;background-color:#e0e0e0;display:none;">
				<input type="checkbox" value="0-nggml-all" checked>
					<?php echo "All $name"; ?><br>
<?php
        foreach ( $terms as $term ) {
?>
				<input type="checkbox" name="filter-<?php echo $taxonomy; ?>[]" value="<?php echo $term->slug; ?>">
					<?php echo $term->name . ' (' . $term->count . ')'; ?><br>
<?php
        }
?>
			</div>
		</div>
        <select name="filter-<?php echo $taxonomy; ?>[]" class="nggml-filter filter-<?php echo $taxonomy; ?>"
			<?php if ( $hidden ) { echo 'style="display:none;"'; } ?>>
            <option value="0-nggml-all" selected><?php echo "All $name"; ?></option>
<?php
        foreach ( $terms as $term ) {
?>
            <option value="<?php echo $term->slug; ?>">
              <?php echo $term->name . ' (' . $term->count . ')'; ?></option>
<?php
        }
?>
        </select>
<?php
    }

    # function extra_tablenav() modified to support tag taxonomy filters
    
    function extra_tablenav( $which ) {  
?>
        <div class="alignleft actions">
<?php
        if ( 'top' == $which && !is_singular() && !$this->detached && !$this->is_trash ) {
            # the single value select month filter seems out of place here so omit for now
            #$this->months_dropdown( 'attachment' );
          
            # nggtags version supports tag filters
            $hidden = get_hidden_columns( $this->screen );           
            foreach ( $this->get_columns() as $column_key => $column_name) {
                if ( strpos( $column_key, 'taxonomy-' ) === 0 ) {
                    # Is everything with prefix 'taxonomy-' really a taxonomy?
                    $this->taxonomy_filter( substr( $column_key, 9 ), $column_name, in_array( $column_key, $hidden ) );
                }
            }
      
            /** This action is documented in wp-admin/includes/class-wp-posts-list-table.php */
            do_action( 'restrict_manage_posts' );
            submit_button( __( 'Filter' ), 'button', false, false, array( 'id' => 'post-query-submit' ) );
        }

        if ( $this->detached ) {
            submit_button( __( 'Scan for lost attachments' ), 'secondary', 'find_detached', false );
        } elseif ( $this->is_trash && current_user_can( 'edit_others_posts' ) ) {
            submit_button( __( 'Empty Trash' ), 'apply', 'delete_all', false );
        }
?>
        </div>
<?php
    }
    
    # bulk inline editor needs inline data so hack function row_actions() to append the inline data
    
    function row_actions( $actions, $always_visible = false ) {
        global $post;
        $output = parent::row_actions( $actions, $always_visible );
        # now append the inline data
        ob_start();
        get_inline_data( $post );
        $output .= ob_get_contents();
        ob_end_clean();
        return $output;
    }
    
    # nggtags version supports bulk inline editing
    # code adapted from WP_Posts_List_Table::inline_edit() from admin\includes\class-wp-posts-list-table.php
    
	/**
	 * Outputs the hidden row displayed when inline editing
	 *
	 * @since 3.1.0
	 */
   
	function inline_edit() {
        global $wp_query;
        global $nggtags_for_wp_media_library_in_inline_edit_tags_to_edit;
		$screen = $this->screen;
        $post_ids = array_map( function( $post ) { return (int) $post->ID; }, $wp_query->posts );
		$taxonomy_names = get_object_taxonomies( 'attachment' );
		$hierarchical_taxonomies = array();
		$flat_taxonomies = array();
		foreach ( $taxonomy_names as $taxonomy_name ) {
			$taxonomy = get_taxonomy( $taxonomy_name );

			if ( !$taxonomy->show_ui )
				continue;

			if ( $taxonomy->hierarchical )
				$hierarchical_taxonomies[] = $taxonomy;
			else
				$flat_taxonomies[] = $taxonomy;
		}
		$m = 'list';
        $hclass = 'post';
        ?>
        <form method="get" action=""><table style="display: none"><tbody id="inlineedit">
            <tr id="bulk-edit" class="inline-edit-row <?php echo "inline-edit-row-$hclass inline-edit-attachment";
                echo " bulk-edit-row bulk-edit-row-$hclass bulk-edit-attachment"; ?>"
                style="display: none">
                <td colspan="<?php echo $this->get_column_count(); ?>" class="colspanchange">
                    <fieldset class="bulk-edit-taxonomy inline-edit-col-left" style="display:none"><div class="inline-edit-col">
                        <div style="background-color:#c0c0c0;width:25%;float:right;border:2px solid black;border-radius:7px;text-align:center;margin:5px;">
                            <a href="http://nggtagsforwpml.wordpress.com/#media-library-for-nggtags" target="_blank">help</a>
                        </div>
                        <h4><?php echo __( 'Bulk Edit' ); ?></h4>
                        <p style="clear:both;margin:0px;">
                        <div id="bulk-title-div">
                            <div id="bulk-titles"></div>
                        </div>
                    </div></fieldset>
                    <fieldset class="bulk-edit-taxonomy inline-edit-col-right" style="display:none"><div class="inline-edit-col"><br>
                        <?php if ( count( $flat_taxonomies ) ) : ?>
                        <?php foreach ( $flat_taxonomies as $taxonomy ) : ?>
                        <?php if ( current_user_can( $taxonomy->cap->assign_terms ) ) : ?>
                        <label class="inline-edit-tags">
                            <span class="title"><?php echo esc_html( $taxonomy->labels->name ) ?></span>
                            <?php
                            $tags_to_edit = array();
                            $post_valid = null;
                            $post_id_to_tags_to_edit = '';
                            foreach( $wp_query->posts as &$post ) {
                                $count = count( $tags_to_edit );
                                $tags_to_edit =  array_filter( array_merge( $tags_to_edit,
                                    explode( ',', $terms_to_edit = get_terms_to_edit( $post->ID, $taxonomy->name ) ) ) );
                                if ( !$post_valid && count( $tags_to_edit) > $count ) { $post_valid =& $post; }
                                $post_id_to_tags_to_edit .= "$post->ID:$terms_to_edit;";
                            }
                            $nggtags_for_wp_media_library_in_inline_edit_tags_to_edit = implode( ',', array_unique( $tags_to_edit ) );
                            unset( $post );
                            reset( $wp_query->posts );
                            $box = array(
                                'id'       => 'tagsdiv-' . $taxonomy->name,
                                'title'    => $taxonomy->label,
                                'callback' => 'post_tags_meta_box',
                                'args'     => array(
                                                  'taxonomy' => $taxonomy->name,
                                              )
                            );
                            echo '<div class="inside">';
                            # use first valid post as a dummy
                            post_tags_meta_box( $post_valid, $box );
                            ?>
                            <input type="hidden" name="nggtags-for-ml-initial[<?php echo $taxonomy->name; ?>]"
                                value="<?php echo implode( ',', array_unique( $tags_to_edit ) ); ?>" />
                            <input type="hidden" name="nggtags-for-ml-added[<?php echo $taxonomy->name; ?>]"
                                id="nggtags-for-ml-added-<?php echo $taxonomy->name; ?>" />
                            <input type="hidden" class="nggtags-for-ml-post_id-to-tags-to-edit"
                                name="nggtags-for-ml-post_id-to-tags-to-edit[<?php echo $taxonomy->name; ?>]"
                                id="nggtags-for-ml-post_id-to-tags-to-edit-<?php echo $taxonomy->name; ?>"
                                value="<?php echo $post_id_to_tags_to_edit; ?>" />
                            <?php
                            echo '</div>';
                            unset( $post_valid );
                            $nggtags_for_wp_media_library_in_inline_edit_tags_to_edit = false;
                            ?>
                        </label>
                        <br class="clear" />
                        <?php endif; ?>
                        <?php endforeach; //$flat_taxonomies as $taxonomy ?>
                        <?php endif; // count( $flat_taxonomies ) && !$bulk  ?>
                    </div></fieldset>
                    <fieldset class="bulk-edit-priority inline-edit-col-left" style="display:none"><div class="inline-edit-col">
                        <!-- help button -->
                        <div id="nggml-bulk-priority-edit-button-help"
                            style="background-color:#c0c0c0;width:100px;float:right;border:2px solid black;border-radius:7px;text-align:center;margin:5px;">
                            <a href="http://nggtagsforwpml.wordpress.com/#bulk-priority-edit" target="_blank">help</a>
                        </div>
                        <!-- revert button -->
                        <div id="nggml-bulk-priority-edit-button-revert"
                            style="background-color:#c0c0c0;width:100px;float:right;border:2px solid black;border-radius:7px;text-align:center;margin:5px;">
                            revert
                        </div>
                        <!-- reorder button -->
                        <div id="nggml-bulk-priority-edit-button-reorder"
                            style="background-color:#c0c0c0;width:100px;float:right;border:2px solid black;border-radius:7px;text-align:center;margin:5px;">
                            reorder
                        </div>
                        <h4><?php echo __( 'Bulk Priority Editor' ); ?></h4>
                        <p style="clear:both;margin:0px;">
                        Start: <input id="nggml-bulk-priority-edit-start" name="nggml-bulk-priority-edit-start"
                            type="number" size="12" min="1" value="100" required style="text-align:right;">
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        Increment: <input id="nggml-bulk-priority-edit-increment" name="nggml-bulk-priority-edit-increment"
                            type="number" size="12" min="1" value="100" required style="text-align:right;">
                        <input type="hidden" name="nggml-bulk-priority-edit-order" id="nggml-bulk-priority-edit-order" />
                        <div id="nggml-bulk-priority-edit-images"></div>
                        <?php
                        #$thumb = wp_get_attachment_image( $post->ID, array( 80, 60 ), true );
                        ?>
                    </div></fieldset>
                    <p class="submit inline-edit-save">
                    <a accesskey="c" href="#inline-edit" class="button-secondary cancel alignleft"><?php _e( 'Cancel' ); ?></a>
                        <?php submit_button( __( 'Update' ), 'button-primary alignright', 'bulk_edit', false,
                            array( 'accesskey' => 's' ) ); ?>
                    <input type="hidden" name="post_view" value="<?php echo esc_attr( $m ); ?>" />
                    <input type="hidden" name="screen" value="<?php echo esc_attr( $screen->id ); ?>" />
                    <span class="error" style="display:none"></span>
                    <br class="clear" />
                    </p>
                </td>
            </tr>
		</tbody></table></form>
		<?php
    }  
}

# Since we are using a single dummy post we need substitute the tags for the bulk posts

add_filter( 'terms_to_edit', function( $tags_to_edit, $taxonomy ) {
    global $nggtags_for_wp_media_library_in_inline_edit_tags_to_edit;
    if ( !$nggtags_for_wp_media_library_in_inline_edit_tags_to_edit ) { return $tags_to_edit; }
    return $nggtags_for_wp_media_library_in_inline_edit_tags_to_edit; 
}, 10, 2 );
