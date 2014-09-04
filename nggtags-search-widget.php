<?php

/*
Module Name: Search Media Library Widget
Module URI: http://wordpress.org/plugins/nggtags-for-wp-media-library/search.html
Description: Widget for searching Media Library taxonomies.
Version: 0.6
Author: Magenta Cuda (PHP), Black Charger (JavaScript)
Author URI: http://magentacuda.wordpress.com
License: GPL2
Documentation: http://nggtagsforwpml.wordpress.com/
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

if ( is_admin() ) {
    add_action( 'admin_enqueue_scripts', function() {
        wp_enqueue_script( 'jquery-ui-draggable' );
        wp_enqueue_script( 'jquery-ui-droppable' );
    } );
}

class Search_Media_Library_by_Taxonomy_Widget extends WP_Widget {

    const OPTIONAL_TEXT_VALUE_SUFFIX = '-nggml-optional-text-value'; # suffix for additional text input for a custom field
    
	public function __construct() {
		parent::__construct(
            'search_media_library_by_taxonomy_widget',
            __( 'Search Media Library by Taxonomy' ),
            array(
                'classname' => 'search_media_library_by_taxonomy_widget',
                'description' => __( "Search Media Library by Taxonomy" )
            )
        );
	}

    # widget() emits a form to specify the search criteria
    
	public function widget( $args, $instance ) {
        global $wpdb;
        extract( $args );
?>
<form id="search-types-custom-fields-widget-<?php echo $this->number; ?>" class="nggml-search-fields-form" method="get"
    action="<?php echo esc_url( home_url( '/' ) ); ?>">
<input id="search_media_library_for_nggtags_form" name="search_media_library_for_nggtags_form" type="hidden"
    value="search_media_library_for_nggtags_form">
<input id="search_media_library_for_nggtags_widget_option" name="search_media_library_for_nggtags_widget_option" type="hidden"
    value="<?php echo $this->option_name; ?>">
<input id="search_media_library_for_nggtags_widget_number" name="search_media_library_for_nggtags_widget_number" type="hidden"
    value="<?php echo $this->number; ?>">
<div id="nggml-search-fields-parameters">
<div id="nggml-search-fields-help">
<a href="http://nggtagsforwpml.wordpress.com/a-widget-for-searching-the-media-library-by-taxonomy/#user"
    target="_blank">help</a>
</div>
<h2 class="nggml-search-fields-title">Search Images</h2>
<p style="clear:both;margin:0px;">
<h4>Specify search conditions:</h4>
<?php
        $selected = $instance['attachment'];
        $SQL_LIMIT = (integer) $instance['maximum_number_of_items'];
        # get all terms for all taxonomies for attachments
        $sql = <<<EOD
            SELECT x.taxonomy, r.term_taxonomy_id, t.name, COUNT(*) count
                FROM $wpdb->term_relationships r, $wpdb->term_taxonomy x, $wpdb->terms t, $wpdb->posts p
                WHERE r.term_taxonomy_id = x.term_taxonomy_id AND x.term_id = t.term_id AND r.object_id = p.ID
                    AND p.post_type = "attachment" AND p.post_mime_type LIKE "image/%"
                GROUP BY x.taxonomy, r.term_taxonomy_id ORDER BY x.taxonomy ASC, count DESC
EOD;
        $results = $wpdb->get_results( $sql, OBJECT );
        $taxonomies = get_taxonomies( '', 'objects' );
        # restructure the results for displaying by taxonomy
        $terms = array();
        foreach ( $results as $result ) {
            $taxonomy = $taxonomies[$result->taxonomy];
            $tax_type = ( $taxonomy->hierarchical ) ? 'tax-cat-' : 'tax-tag-';
            if ( !in_array( $tax_type . $taxonomy->name, $selected ) ) { continue; }
            $terms[$result->taxonomy]['values'][$result->term_taxonomy_id]['name' ] = $result->name;
            $terms[$result->taxonomy]['values'][$result->term_taxonomy_id]['count'] = $result->count;
        }
        # add post content, post author and post parent
        if ( in_array( 'pst-std-post_content', $selected ) ) {
            $fields['pst-std-post_content'] = array( 'label' => 'Description' );
        }
        if ( in_array( 'pst-std-post_author', $selected ) ) {
            $fields['pst-std-post_author']  = array( 'label' => 'Author'   );
        }
        if ( in_array( 'pst-std-post_parent', $selected ) ) {
            $fields['pst-std-post_parent']  = array( 'label' => 'Gallery'   );
        }
        foreach ( $selected as $key ) {
            if ( substr_compare( $key, 'tax-tag-', 0, 8 ) === 0 ) {
                # display the taxonomy results
                $tax_name = substr( $key, 8 );
                $values =& $terms[$tax_name];
                $taxonomy = $taxonomies[$tax_name];
?>
<div class="nggml-search-fields">
<span class="nggml-search-fields-field-label"><?php echo $taxonomy->label ?>:</span>
<div class="nggml-search-fields-show-button">Open</div>
<div style="clear:both;"></div>
<div class="nggml-search-fields-values" style="display:none;">
<?php
                $count = -1;
                foreach ( $values['values'] as $term_id => &$result ) {
                    if ( ++$count === $SQL_LIMIT ) { break; }
?>
<input type="checkbox" id="<?php echo $tax_type . $taxonomy->name ?>" name="<?php echo $tax_type . $taxonomy->name ?>[]"
    value="<?php echo $term_id; ?>"><?php echo "$result[name]($result[count])"; ?><br>
<?php
                }   # foreach ( $values['values'] as $term_id => $result ) {
                unset( $result );
                if ( $count === $SQL_LIMIT ) {
?>
<input type="text"
    id="<?php echo $tax_type . $taxonomy->name . self::OPTIONAL_TEXT_VALUE_SUFFIX; ?>"
    name="<?php echo $tax_type . $taxonomy->name . self::OPTIONAL_TEXT_VALUE_SUFFIX; ?>"
    class="nggml-search-fields-for-input" placeholder="--Enter Search Value--">
<?php
                }
?>
</div>
</div>
<?php
            } else {
                # do post content, post author and post parent
                $field =& $fields[$key];
?>
<div class="nggml-search-fields">
<span class="nggml-search-fields-field-label"><?php echo $field['label'] ?>:</span>
<div class="nggml-search-fields-show-button">Open</div>
<div style="clear:both;"></div>
<div class="nggml-search-fields-values" style="display:none;">
<?php
                if ( $key === 'pst-std-post_content' ) {
?>
<input id="pst-std-post_content" name="pst-std-post_content" class="nggml-search-fields-for-input" type="text"
    placeholder="--Enter Search Value--">
<?php
                } else if ( $key === 'pst-std-post_author' ) {
                    # use author display name in place of author id
                    $results = $wpdb->get_results( <<<EOD
                        SELECT p.post_author, u.display_name, COUNT(*) count FROM $wpdb->posts p, $wpdb->users u
                            WHERE p.post_author = u.ID AND p.post_type = "attachment" AND p.post_mime_type LIKE "image/%"
                                AND p.post_author IS NOT NULL
                            GROUP BY p.post_author
EOD
                        , OBJECT );
                    $count = -1;
                    foreach ( $results as $result ) {
                        if ( ++$count === $SQL_LIMIT ) { break; }
?>
<input type="checkbox" id="pst-std-post_author" name="pst-std-post_author[]"
    value="<?php echo $result->post_author; ?>"> <?php echo $result->display_name . " ($result->count)"; ?><br>
<?php
                    }
                    if ( $count === $SQL_LIMIT ) {
?>
<input type="text" id="pst-std-post_author<?php echo self::OPTIONAL_TEXT_VALUE_SUFFIX; ?>"
    name="pst-std-post_author<?php echo self::OPTIONAL_TEXT_VALUE_SUFFIX; ?>"
    class="nggml-search-fields-for-input" placeholder="--Enter Search Value--">
<?php
                    }
                } else if ( $key === 'pst-std-post_parent' ) {
                    # post parent in this case is the gallery
                    # use post title which is the gallery name in place of post id
                    $results = $wpdb->get_results( <<<EOD
                        SELECT g.ID, g.post_title, COUNT(*) count FROM $wpdb->posts a, $wpdb->posts g
                            WHERE a.post_parent = g.ID AND a.post_type = "attachment" AND a.post_mime_type LIKE "image/%"
                                AND a.post_parent IS NOT NULL
                            GROUP BY g.ID ORDER BY count DESC
EOD
                        , OBJECT );
                    $count = -1;
                    foreach ( $results as $result ) {
                        if ( ++$count === $SQL_LIMIT ) { break; }
?>
<input type="checkbox" id="pst-std-post_parent" name="pst-std-post_parent[]"
    value="<?php echo $result->ID; ?>"> <?php echo $result->post_title . " ($result->count)"; ?><br>
<?php
                    }
                    if ( $count === $SQL_LIMIT ) {
?>
<input type="text" id="pst-std-post_parent<?php echo self::OPTIONAL_TEXT_VALUE_SUFFIX; ?>"
    name="pst-std-post_parent<?php echo self::OPTIONAL_TEXT_VALUE_SUFFIX; ?>"
    class="nggml-search-fields-for-input" placeholder="--Enter Search Value--">
<?php
                    }
                }
?>
</div>
</div>
<?php
            }
        }   # foreach ( $selected as $key ) {
        unset( $values );
        unset( $field );
?>
</div>
<div class="nggml-search-fields-and-or-box">
<div style="text-align:center;margin:10px;">
Results should satisfy<br> 
<input type="radio" name="search_media_library_for_nggtags_and_or" value="and" checked><strong>All</strong>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input type="radio" name="search_media_library_for_nggtags_and_or" value="or"><strong>Any</strong></br>
of the search conditions.
</div>
</div>
<div style="text-align:right;">
<input id="nggml-search-fields-reset" type="button" value="Reset">
<input id="nggml-search-fields-submit" name="nggml-search-fields-submit" type="submit" value="Start Search">
&nbsp;&nbsp;
</div>
</form>
<script type="text/javascript">
jQuery("div.nggml-search-fields-show-button").click(function(event){
    if(jQuery(this).text()=="Open"){
        jQuery(this).text("Close");
        jQuery("div.nggml-search-fields-values",this.parentNode).css("display","block");
    }else{
        jQuery(this).text("Open");
        jQuery("div.nggml-search-fields-values",this.parentNode).css("display","none");
    }
    return false;
});
jQuery("div.nggml-search-fields-values input[type='checkbox']").change(function(){
	this.parentNode.parentNode.style.backgroundColor=(jQuery("input[type='checkbox']:checked",this.parentNode).size()
      +jQuery("input[type='text']",this.parentNode).filter(function(){return jQuery(this).val();}).size())
    ?"white":this.parentNode.parentNode.parentNode.style.backgroundColor;
});
jQuery("div.nggml-search-fields-values input[type='text']").change(function(){
	this.parentNode.parentNode.style.backgroundColor=(jQuery("input[type='checkbox']:checked",this.parentNode).size()
      +jQuery("input[type='text']",this.parentNode).filter(function(){return jQuery(this).val();}).size())
    ?"white":this.parentNode.parentNode.parentNode.style.backgroundColor;
});
jQuery("input[type='button']#nggml-search-fields-reset").click(function(){
	jQuery("div.nggml-search-fields-values input[type='checkbox']").prop("checked",false);
	jQuery("div.nggml-search-fields-values input[type='text']").val("");
	jQuery("div.nggml-search-fields").css("background-color",
		jQuery("div#nggml-search-fields-parameters").css("background-color"));
});
</script>
<?php
	}
    
    public function update( $new, $old ) {
        return array_map( function( $values ) {
            return is_array( $values) ? array_map( strip_tags, $values ) : strip_tags( $values );
        }, $new );
    }
    
    # form() is for the administrator to specify the post types and custom fields that will be searched
    
    public function form( $instance ) {
        global $wpdb;
?>
<div style="background-color:#c0c0c0;width:25%;float:right;border:2px solid black;border-radius:7px;text-align:center;margin:5px;">
<a href="http://nggtagsforwpml.wordpress.com/a-widget-for-searching-the-media-library-by-taxonomy/#administrator"
    target="_blank">help</a>
</div>
<h4 style="float:left;margin:5px;">Select Search Fields for Media Library:</h4>
<p style="clear:both;margin:0px;">
<?php
        # the sql below gives the number of posts tagged, since a single post may be tagged with multiple tags
        # the sql is somewhat complicated
        $sql = <<<EOD
            SELECT taxonomy, count(*) count
                FROM (SELECT x.taxonomy, r.object_id
                    FROM wp_term_relationships r, wp_term_taxonomy x, wp_terms t, wp_posts p
                    WHERE r.term_taxonomy_id = x.term_taxonomy_id AND x.term_id = t.term_id AND r.object_id = p.ID
                        AND p.post_type = 'attachment' AND p.post_mime_type LIKE 'image/%'
                    GROUP BY x.taxonomy, r.object_id) d 
                GROUP BY taxonomy
EOD;
        $db_taxonomies = $wpdb->get_results( $sql, OBJECT );
        $wp_taxonomies = get_taxonomies( '', 'objects' );
        $selected = $instance['attachment'];
?>
<div class="scpbcfw-search-fields">
<div class="scpbcfw-search-field-values" style="display:block;">
<style scoped>
div.mf2tk-selectable-field-after{height:2px;background-color:white;}
div.mf2tk-selectable-field-after.mf2tk-hover{background-color:black;}
</style>
<!-- before drop point -->
<div><div class="mf2tk-selectable-field-after"></div></div>
<?php
        # do taxonomies first
        $the_taxonomies = array();
        foreach ( $db_taxonomies as &$db_taxonomy ) {
            $wp_taxonomy =& $wp_taxonomies[$db_taxonomy->taxonomy];
            $prefix = $wp_taxonomy->hierarchical ? 'tax-cat-:' : 'tax-tag-';
            $the_taxonomies[$prefix . $wp_taxonomy->name] =& $db_taxonomy;
        }
        unset( $db_taxonomy, $wp_taxonomy );
        
        # setup post content
        $sql = <<<EOD
            SELECT COUNT(*) FROM $wpdb->posts
                WHERE post_type = 'attachment' AND post_mime_type LIKE 'image/%' AND post_mime_type LIKE "image/%"
                    AND ( post_title IS NOT NULL OR post_content IS NOT NULL OR post_excerpt IS NOT NULL )
EOD;
        $fields['pst-std-post_content'] = (object) array( 'label' => 'Description', 'count' => $wpdb->get_var( $sql ) );
        
        # setup post_author
        $sql = <<<EOD
            SELECT COUNT(*) FROM $wpdb->posts
                WHERE post_type = 'attachment' AND post_mime_type LIKE 'image/%' AND post_author IS NOT NULL
EOD;
        $fields['pst-std-post_author']  = (object) array( 'label' => 'Author', 'count' => $wpdb->get_var( $sql ) );
        
        # setup post_parent which for attachments is the gallery
        $sql = <<<EOD
            SELECT COUNT(*) FROM $wpdb->posts
                WHERE post_type = 'attachment' AND post_mime_type LIKE 'image/%' AND post_parent IS NOT NULL
EOD;
        $fields['pst-std-post_parent']  = (object) array( 'label' => 'Gallery', 'count' => $wpdb->get_var( $sql ) );
        
        # it is possible that user has added new fields or deleted fields so ...
        $previous = !empty( $instance['attachment'] ) ? $instance['attachment'] : array();
        $current = array_merge( array_keys( $the_taxonomies ), array_keys( $fields ) );
        $previous = array_intersect( $previous, $current );
        $new = array_diff( $current, $previous );
        $current = array_merge( $previous, $new );
        
        # do previously selected fields first then do all other valid fields
        foreach ( $current as $key ) {
            if ( substr_compare( $key, 'tax-tag-', 0, 8 ) === 0 ) {
                # do taxonomies
                $db_taxonomy =& $the_taxonomies[$key];
                $wp_taxonomy = $wp_taxonomies[$db_taxonomy->taxonomy];
                $tax_type = ( $wp_taxonomy->hierarchical ) ? 'tax-cat-' : 'tax-tag-';
                $tax_label = ( $wp_taxonomy->hierarchical ) ? ' (category)' : ' (tag)';
?>
<div class="mf2tk-selectable-field">
    <input type="checkbox"
        class="mf2tk-selectable-field" 
        id="<?php echo $this->get_field_id( 'attachment' ); ?>"
        name="<?php echo $this->get_field_name( 'attachment' ); ?>[]"
        value="<?php echo $tax_type . $wp_taxonomy->name; ?>"
        <?php if ( $selected && in_array( $tax_type . $wp_taxonomy->name, $selected ) ) { echo ' checked'; } ?>>
        <?php echo "{$wp_taxonomy->label}{$tax_label} ($db_taxonomy->count)"; ?>
    <!-- a drop point -->
    <div class="mf2tk-selectable-field-after"></div>
</div>
<?php
            } else {
                # do post author, post content, post parent
                $field =& $fields[$key];
?>
<div class="mf2tk-selectable-field">
    <input type="checkbox"
        class="mf2tk-selectable-field"
        id="<?php echo $this->get_field_id( 'attachment' ); ?>"
        name="<?php echo $this->get_field_name( 'attachment' ); ?>[]"
        value="<?php echo $key; ?>"
        <?php if ( $selected && in_array( $key, $selected ) ) { echo ' checked'; } ?>>
    <?php echo "$field->label ($field->count)"; ?>
    <!-- a drop point -->
    <div class="mf2tk-selectable-field-after"></div>
</div>
<?php
            }
        }   # foreach ( $current as $key ) {
?>
</div>
</div>
<div style="border:2px solid gray;padding:5px;margin:5px;border-radius:7px;">
<div style="padding:10px;border:1px solid gray;margin:5px;">
<input type="number" min="0" max="1024" 
    id="<?php echo $this->get_field_id( 'maximum_number_of_items' ); ?>"
    name="<?php echo $this->get_field_name( 'maximum_number_of_items' ); ?>"
    value="<?php echo isset( $instance['maximum_number_of_items'] ) ? $instance['maximum_number_of_items'] : 16; ?>"
    size="4" style="float:right;text-align:right;">
Maximum number of items to display per custom field:
<div style="clear:both;"></div>
</div>
</div>
<script type="text/javascript">
jQuery(document).ready(function(){
    jQuery("div.mf2tk-selectable-field").draggable({cursor:"crosshair",revert:true});
    jQuery("div.mf2tk-selectable-field-after").droppable({accept:"div.mf2tk-selectable-field",tolerance:"touch",
        hoverClass:"mf2tk-hover",drop:function(e,u){
            jQuery(this.parentNode).after(u.draggable);
    }});
});
</script>
<?php
    }   # public function form( $instance ) {

    # helper functions
    
    public static function &join_arrays( $op, &$arr0, &$arr1 ) {
        $is_arr0 = is_array( $arr0 );
        $is_arr1 = is_array( $arr1 );
        if ( $is_arr0 || $is_arr1 ) {
            if ( $op == 'AND' ) {
                if ( $is_arr0 && $is_arr1 ) { $arr = array_intersect( $arr0, $arr1 ); }
                else if ( $is_arr0 ) { $arr = $arr0; } else { $arr = $arr1; }
            } else {
                if ( $is_arr0 && $is_arr1 ) { $arr = array_unique( array_merge( $arr0, $arr1 ) ); }
                else if ( $is_arr0 ) { $arr = $arr0; } else { $arr = $arr1; }
            }
            return $arr;
        }
        return FALSE;
    }
}

add_action( 'widgets_init', function() {
    register_widget( 'Search_Media_Library_by_Taxonomy_Widget' );
} );


if ( !is_admin() ) {
    add_action( 'wp_enqueue_scripts', function() {
        wp_enqueue_style( 'nggml_search', plugins_url( 'nggml_search.css', __FILE__ ) );
        wp_enqueue_script( 'jquery' );
    } );
    add_action( 'parse_query', function( &$query ) {
        if ( !$query->is_main_query() || !array_key_exists( 'search_media_library_for_nggtags_form', $_REQUEST ) ) { return; }
        $option = get_option( $_REQUEST['search_media_library_for_nggtags_widget_option'] );
        $number = $_REQUEST['search_media_library_for_nggtags_widget_number'];
        if ( isset( $option[$number]['set_is_search'] ) ) { $query->is_search = true; }
    } );
    add_filter( 'posts_where', function( $where, $query ) {
        global $wpdb;
        if ( !$query->is_main_query() || !array_key_exists( 'search_media_library_for_nggtags_form', $_REQUEST ) ) {
            return $where;
        }
        # this is a Media Library search by taxonomy request so modify the SQL where clause
        $and_or = $_REQUEST['search_media_library_for_nggtags_and_or'] == 'and' ? 'AND' : 'OR';
        # first get taxonomy name to term_taxonomy_id translation table in case we need the translations
        $results = $wpdb->get_results( <<<EOD
            SELECT x.taxonomy, t.name, x.term_taxonomy_id
                FROM $wpdb->term_taxonomy x, $wpdb->terms t
                WHERE x.term_id = t.term_id
                GROUP BY x.term_taxonomy_id
EOD
            , OBJECT );
        $term_taxonomy_ids = array();
        foreach ( $results as $result ) {
            $term_taxonomy_ids[$result->taxonomy][strtolower( $result->name)] = $result->term_taxonomy_id;
        }
        # first get gallery name to ID translation table in case we need the translations
        $results = $wpdb->get_results( <<<EOD
            SELECT g.post_title, g.ID
                FROM $wpdb->posts g, $wpdb->posts a
                WHERE g.ID = a.post_parent AND a.post_type = 'attachment' AND a.post_mime_type LIKE "image/%"
                GROUP BY g.ID
EOD
            , OBJECT );
        $gallery_ids = array();
        foreach ( $results as $result ) {
            $gallery_ids[strtolower( $result->post_title)] = $result->ID;
        }
        # first get author name to ID translation table in case we need the translations
        $results = $wpdb->get_results( <<<EOD
            SELECT u.display_name, u.ID
                FROM $wpdb->users u, $wpdb->posts a
                WHERE u.ID = a.post_author AND a.post_type = 'attachment' AND a.post_mime_type LIKE "image/%"
                GROUP BY u.ID
EOD
            , OBJECT );
        $author_ids = array();
        foreach ( $results as $result ) {
            $author_ids[strtolower( $result->display_name)] = $result->ID;
        }
        # merge optional text values into the checkboxes array
        $suffix_len = strlen( Search_Media_Library_by_Taxonomy_Widget::OPTIONAL_TEXT_VALUE_SUFFIX );
        foreach ( $_REQUEST as $index => &$request ) {
            if ( $request && substr_compare( $index, Search_Media_Library_by_Taxonomy_Widget::OPTIONAL_TEXT_VALUE_SUFFIX,
                -$suffix_len ) === 0 && $request ) {
                $index = substr( $index, 0, strlen( $index ) - $suffix_len );
                if ( is_array( $_REQUEST[$index] ) || !array_key_exists( $index, $_REQUEST ) ) {
                    $lowercase_request = strtolower( $request );
                    if ( substr_compare( $index, 'tax-', 0, 4 ) === 0 ) {
                        # for taxonomy values must replace the value with the corresponding term_taxonomy_id
                        $tax_name = substr( $index, 8 );
                        if ( !array_key_exists( $tax_name, $term_taxonomy_ids )
                            || !array_key_exists( $lowercase_request, $term_taxonomy_ids[$tax_name] ) ) {
                            # kill the original request
                            $request = NULL;
                            continue;
                        }
                        $request = $term_taxonomy_ids[$tax_name][$lowercase_request];
                    } else if ( $index === 'pst-std-post_parent' ) {
                        # for gallery names must replace the value with the corresponding post ID
                        if ( !array_key_exists( $lowercase_request, $gallery_ids ) ) {
                            # kill the original request
                            $request = NULL;
                            continue;
                        }
                        $request = $gallery_ids[$lowercase_request];
                    } else if ( $index === 'pst-std-post_author' ) {
                        # for author names must replace the value with the corresponding author ID
                        if ( !array_key_exists( $lowercase_request, $author_ids ) ) {
                            # kill the original request
                            $request = NULL;
                            continue;
                        }
                        $request = $author_ids[$lowercase_request];
                    }
                    # merge optional text values into the checkboxes array
                    $_REQUEST[$index][] = $request;
                }    
                # kill the original request
                $request = NULL;
            }
        }
        unset( $request );
        $non_field_keys = array( 'search_media_library_for_nggtags_form', 'search_media_library_for_nggtags_widget_option',
            'search_media_library_for_nggtags_widget_number', 'search_media_library_for_nggtags_and_or', 'post_type', 'paged' );
        $sql = '';
        foreach ( $_REQUEST as $key => $values ) {
            if ( !$values ) { continue; }
            # here only taxonomies are processed
            if ( in_array( $key, $non_field_keys ) ) { continue; }
            $prefix = substr( $key, 0, 8 );
            if ( $prefix !== 'tax-cat-' && $prefix !== 'tax-tag-' ) {
                continue;
            }
            if ( !is_array( $values) ) {
                if ( $values ) { $values = array( $values ); }
                else { continue; }
            }
            $values = array_filter( $values ); 
            if ( !$values ) { continue; }
            $taxonomy = substr( $key, 8 );
            if ( $sql ) { $sql .= " $and_or "; }
            $sql .= " EXISTS ( SELECT * FROM $wpdb->term_relationships WHERE ( ";
            foreach ( $values as $value ) {
                if ( $value !== $values[0] ) { $sql .= ' OR '; }
                $sql .= $wpdb->prepare( 'term_taxonomy_id = %s', $value ); 
            }
            $sql .= ') AND object_id = p.ID )';
        }   # foreach ( $_REQUEST as $key => $values ) {
        if ( $sql ) {
            $sql = <<<EOD
                SELECT ID FROM $wpdb->posts p
                    WHERE p.post_type = 'attachment' AND p.post_mime_type LIKE 'image/%' AND ( $sql )
EOD;
            $ids1 = $wpdb->get_col( $sql );
            if ( $and_or == 'AND' && !$ids1 ) { return ' AND 1 = 2 '; }
       } else {
            $ids1 = FALSE;
        }
        $ids = $ids1;
        if ( $and_or == 'AND' && $ids !== FALSE && !$ids ) { return ' AND 1 = 2 '; }
        $ids2 = FALSE;
        $ids = Search_Media_Library_by_Taxonomy_Widget::join_arrays( $and_or, $ids, $ids2 );
        if ( $and_or == 'AND' && $ids !== FALSE && !$ids ) { return ' AND 1 = 2 '; }
        # finally handle post_content - post_title and post_excerpt are included in the search of post_content
        if ( array_key_exists( 'pst-std-post_content', $_REQUEST ) && $_REQUEST['pst-std-post_content'] ) {
            $sql = $wpdb->prepare( <<<EOD
                SELECT ID FROM $wpdb->posts
                    WHERE post_type = 'attachment' AND post_mime_type LIKE 'image/%%'
                        AND ( post_content LIKE %s OR post_title LIKE %s OR post_excerpt LIKE %s )
EOD
                , "%{$_REQUEST['pst-std-post_content']}%", "%{$_REQUEST['pst-std-post_content']}%",
                "%{$_REQUEST['pst-std-post_content']}%" );
            $ids3 = $wpdb->get_col( $sql );
            if ( $and_or == 'AND' && !$ids3 ) { return ' AND 1 = 2 '; }
        } else {
            $ids3 = FALSE;
        }
        $ids = Search_Media_Library_by_Taxonomy_Widget::join_arrays( $and_or, $ids, $ids3 );
        if ( $and_or == 'AND' && $ids !== FALSE && !$ids ) { return ' AND 1 = 2 '; }
        # filter on post_author
        if ( array_key_exists( 'pst-std-post_author', $_REQUEST ) && $_REQUEST['pst-std-post_author'] ) {
            $sql = "SELECT ID FROM $wpdb->posts WHERE post_type = 'attachment' AND post_mime_type LIKE 'image/%'"
                . ' AND post_author IN ( ' . implode( ',', $_REQUEST['pst-std-post_author'] ) . ' )';
EOD;
            $ids4 = $wpdb->get_col( $sql );
            if ( $and_or == 'AND' && !$ids4 ) { return ' AND 1 = 2 '; }
        } else {
            $ids4 = FALSE;
        }
        $ids = Search_Media_Library_by_Taxonomy_Widget::join_arrays( $and_or, $ids, $ids4 );
        if ( $and_or == 'AND' && $ids !== FALSE && !$ids ) { return ' AND 1 = 2 '; }        
        # filter on post_parent
        if ( array_key_exists( 'pst-std-post_parent', $_REQUEST ) && $_REQUEST['pst-std-post_parent'] ) {
            $sql = "SELECT ID FROM $wpdb->posts WHERE post_type = 'attachment' AND post_mime_type LIKE 'image/%'"
                . ' AND post_parent IN ( ' . implode( ',', $_REQUEST['pst-std-post_parent'] ) . ' )';
EOD;
            $ids5 = $wpdb->get_col( $sql );
            if ( $and_or == 'AND' && !$ids5 ) { return ' AND 1 = 2 '; }
        } else {
            $ids5 = FALSE;
        }
        $ids = Search_Media_Library_by_Taxonomy_Widget::join_arrays( $and_or, $ids, $ids5 );
        if ( $and_or == 'AND' && $ids !== FALSE && !$ids ) { return ' AND 1 = 2 '; }        
        if ( $ids ) {
            $ids = implode( ', ', $ids );
            $where = " AND ID IN ( $ids ) ";
        } else {
            #$where = " AND post_type = "$_REQUEST[post_type]" AND post_status = 'publish' ";
            $where = ' AND 1 = 2 ';
        }
        return $where;
    }, 10, 2 );
    if ( isset( $_REQUEST['search_media_library_for_nggtags_form'] )
        && $_REQUEST['search_media_library_for_nggtags_form'] === 'search_media_library_for_nggtags_form' ) {
        # for alternate output format do not page output
        add_filter( 'post_limits', function( $limit, &$query ) {
            if ( !$query->is_main_query() ) { return $limit; }
            return ' ';
        }, 10, 2 );
        add_action( 'template_redirect', function() {
            global $wp_query;
            global $wpdb;
            # in this case a template is dynamically constructed and returned
            # get the list of posts
            $posts = array_map( function( $post ) { return $post->ID; }, $wp_query->posts );
            # reorder $posts using priorities saved in taxonomy priority
            $posts = \NggTags_for_Media_Library\sort_ids_by_priority( $posts );
            $posts_imploded = implode( ', ', $posts );
            # finally output all the HTML - header, content and footer
            get_header();
            # emit the appropriate gallery shortcode for content
            echo '<h1 style="text-align:center;margin-bottom:50px;">Media Library Search Results</h1>';
            $gallery_options = get_option( 'search_results_for_media_library_gallery_options', '' );
            if ( !empty( $gallery_options ) ) { $gallery_options = ' ' . trim( $gallery_options );}
            echo do_shortcode( "[gallery ids=\"$posts_imploded\"{$gallery_options}]" );
            get_footer();
            exit();
        } );
    }
}

?>