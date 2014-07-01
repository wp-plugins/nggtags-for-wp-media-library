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
        <select name="filter-<?php echo $taxonomy; ?>" class="filter-<?php echo $taxonomy; ?>"
            <?php if ( $hidden ) { echo 'style="display:none;"'; } ?>>
            <option value="0"><?php echo "All $name"; ?></option>
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
             $this->months_dropdown( 'attachment' );
          
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
                    <fieldset class="inline-edit-col-left"><div class="inline-edit-col">
                        <h4><?php echo __( 'Bulk Edit' ); ?></h4>
                        <div id="bulk-title-div">
                            <div id="bulk-titles"></div>
                        </div>
                    </div></fieldset>
                    <fieldset class="inline-edit-col-right"><div class="inline-edit-col"><br>
                        <?php if ( count( $flat_taxonomies ) ) : ?>
                        <?php foreach ( $flat_taxonomies as $taxonomy ) : ?>
                        <?php if ( current_user_can( $taxonomy->cap->assign_terms ) ) : ?>
                        <label class="inline-edit-tags">
                            <span class="title"><?php echo esc_html( $taxonomy->labels->name ) ?></span>
                            <?php
                            $tags_to_edit = array();
                            $post_valid = null;
                            foreach( $wp_query->posts as &$post ) {
                                $count = count( $tags_to_edit );
                                $tags_to_edit = array_merge( $tags_to_edit, explode( ',', get_terms_to_edit( $post->ID, $taxonomy->name ) ) );
                                if ( !$post_valid && count( $tags_to_edit) > $count ) { $post_valid =& $post; }
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
