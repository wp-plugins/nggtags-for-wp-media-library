// extracted from wp-admin\js\media.js

jQuery(document).ready(function(){
    jQuery(".hide-column-tog").click(function(){
        var t=jQuery(this),column=t.val();
        var select_filter=null;
        if(column.indexOf("taxonomy-")===0){
            select_filter="select.filter-"+column.substr(9);
        }else if(column==="date"){
            select_filter="select[name='m']";
        }
        if(select_filter){
            if(t.prop('checked')){
                jQuery(select_filter).show();
            }else{
                jQuery(select_filter).hide();
            }
        }
    });
    if(!jQuery(".hide-column-tog[value='date']").prop("checked")){
        jQuery("select[name='m']").hide();
    }
});

// extracted from wp-admin\js\post.js

// return an array with any duplicate, whitespace or values removed
function array_unique_noempty(a) {
	var out = [];
	jQuery.each( a, function(key, val) {
		val = jQuery.trim(val);
		if ( val && jQuery.inArray(val, out) == -1 )
			out.push(val);
		} );
	return out;
}

tagBox = {

	clean : function(tags) {
		//var comma = postL10n.comma;
    var comma = ",";
		if ( ',' !== comma )
			tags = tags.replace(new RegExp(comma, 'g'), ',');
		tags = tags.replace(/\s*,\s*/g, ',').replace(/,+/g, ',').replace(/[,\s]+$/, '').replace(/^[,\s]+/, '');
		if ( ',' !== comma )
			tags = tags.replace(/,/g, comma);
		return tags;
	},
  
	parseTags : function(el) {
		var id = el.id, num = id.split('-check-num-')[1], taxbox = jQuery(el).closest('.tagsdiv'),
			//thetags = taxbox.find('.the-tags'), comma = postL10n.comma,
			thetags = taxbox.find('.the-tags'), comma = ",",
			current_tags = thetags.val().split(comma), new_tags = [];
		delete current_tags[num];

		jQuery.each( current_tags, function(key, val) {
			val = jQuery.trim(val);
			if ( val ) {
				new_tags.push(val);
			}
		});

		thetags.val( this.clean( new_tags.join(comma) ) );

		this.quickClicks(taxbox);
		return false;
	},

	quickClicks : function(el) {
		var thetags = jQuery('.the-tags', el),
			tagchecklist = jQuery('.tagchecklist', el),
			id = jQuery(el).attr('id'),
			current_tags, disabled;

		if ( !thetags.length )
			return;

		disabled = thetags.prop('disabled');

		//current_tags = thetags.val().split(postL10n.comma);
		current_tags = thetags.val().split(",");
		tagchecklist.empty();

		jQuery.each( current_tags, function( key, val ) {
			var span, xbutton;

			val = jQuery.trim( val );

			if ( ! val )
				return;

			// Create a new span, and ensure the text is properly escaped.
			span = jQuery('<span />').text( val );

			// If tags editing isn't disabled, create the X button.
			if ( ! disabled ) {
				xbutton = jQuery( '<a id="' + id + '-check-num-' + key + '" class="ntdelbutton">X</a>' );
				xbutton.click( function(){ tagBox.parseTags(this); });
				span.prepend('&nbsp;').prepend( xbutton );
			}

			// Append the span to the tag list.
			tagchecklist.append( span );
		});
	},

    flushTags : function(el, a, f) {
		var tagsval, newtags, text,
			tags = jQuery('.the-tags', el),
			newtag = jQuery('input.newtag', el),
			//comma = postL10n.comma;
			comma = ',';
		a = a || false;
		text = a ? jQuery(a).text() : newtag.val();
        added=jQuery("input#nggtags-for-ml-added-"+el.attr("id"));
        added.val(added.val()+","+text);
		tagsval = tags.val();
		newtags = tagsval ? tagsval + comma + text : text;
		newtags = this.clean( newtags );
		newtags = array_unique_noempty( newtags.split(comma) ).join(comma);
		tags.val(newtags);
		this.quickClicks(el);
		if ( !a )
			newtag.val('');
		if ( 'undefined' == typeof(f) )
			newtag.focus();

		return false;
	},

  get : function(id) {
		var tax = id.substr(id.indexOf('-')+1);

		jQuery.post(ajaxurl, {'action':'get-tagcloud', 'tax':tax}, function(r, stat) {
			if ( 0 === r || 'success' != stat )
				r = wpAjax.broken;

			r = jQuery('<p id="tagcloud-'+tax+'" class="the-tagcloud">'+r+'</p>');
			jQuery('a', r).click(function(){
				tagBox.flushTags( jQuery(this).closest('.inside').children('.tagsdiv'), this);
				return false;
			});

			jQuery('#'+id).after(r);
		});
	},

 init : function() {
		var t = this, ajaxtag = jQuery('div.ajaxtag');
    
		jQuery('.tagsdiv').each( function() {
			tagBox.quickClicks(this);
		});
    
		jQuery('input.tagadd', ajaxtag).click(function(){
			t.flushTags( jQuery(this).closest('.tagsdiv') );
		});

		jQuery('div.taghint', ajaxtag).click(function(){
			jQuery(this).css('visibility', 'hidden').parent().siblings('.newtag').focus();
		});

		jQuery('input.newtag', ajaxtag).blur(function() {
			if ( '' === this.value )
				jQuery(this).parent().siblings('.taghint').css('visibility', '');
		}).focus(function(){
			jQuery(this).parent().siblings('.taghint').css('visibility', 'hidden');
		}).keyup(function(e){
			if ( 13 == e.which ) {
				tagBox.flushTags( jQuery(this).closest('.tagsdiv') );
				return false;
			}
		}).keypress(function(e){
			if ( 13 == e.which ) {
				e.preventDefault();
				return false;
			}
		}).each(function(){
			var tax = jQuery(this).closest('div.tagsdiv').attr('id');
			//jQuery(this).suggest( ajaxurl + '?action=ajax-tag-search&tax=' + tax, { delay: 500, minchars: 2, multiple: true, multipleSep: postL10n.comma + ' ' } );
			jQuery(this).suggest( ajaxurl + '?action=ajax-tag-search&tax=' + tax, { delay: 500, minchars: 2, multiple: true, multipleSep: ',' + ' ' } );
		});

		// save tags on post save/publish
		jQuery('#post').submit(function(){
			jQuery('div.tagsdiv').each( function() {
				tagBox.flushTags(this, false, 1);
			});
		});

		// tag cloud
		jQuery('a.tagcloud-link').click(function(){
			tagBox.get( jQuery(this).attr('id') );
			jQuery(this).unbind().click(function(){
				jQuery(this).siblings('.the-tagcloud').toggle();
				return false;
			});
			return false;
		});
	}

};

jQuery(document).ready( function() {
	// multi-taxonomies
	if ( jQuery('.tagchecklist').length ) {
		tagBox.init();
	}
});
