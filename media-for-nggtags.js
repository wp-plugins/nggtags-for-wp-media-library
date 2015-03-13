// extracted from .../wp-admin/js/media.js

/* global ajaxurl, attachMediaBoxL10n */

var findPosts;
var nggmlFindPosts;
( function( $ ){

  var media = wp.media;
  
	findPosts = {
		open: function( af_name, af_val ) {
			var overlay = $( '.ui-find-overlay' );

			if ( overlay.length === 0 ) {
				$( 'body' ).append( '<div class="ui-find-overlay"></div>' );
				findPosts.overlay();
			}

			overlay.show();

			if ( af_name && af_val ) {
				$( '#affected' ).attr( 'name', af_name ).val( af_val );
			}

			$( '#find-posts' ).show();

			$('#find-posts-input').focus().keyup( function( event ){
				if ( event.which == 27 ) {
					findPosts.close();
				} // close on Escape
			});

			// Pull some results up by default
			findPosts.send();

			return false;
		},

		close: function() {
			$('#find-posts-response').html('');
			$('#find-posts').hide();
			$( '.ui-find-overlay' ).hide();
		},

		overlay: function() {
			$( '.ui-find-overlay' ).on( 'click', function () {
				findPosts.close();
			});
		},

		send: function() {
			var post = {
					ps: $( '#find-posts-input' ).val(),
					action: 'find_posts',
					_ajax_nonce: $('#_ajax_nonce').val()
				},
				spinner = $( '.find-box-search .spinner' );

			spinner.show();

			$.ajax( ajaxurl, {
				type: 'POST',
				data: post,
				dataType: 'json'
			}).always( function() {
				spinner.hide();
			}).done( function( x ) {
				if ( ! x.success ) {
					$( '#find-posts-response' ).text( attachMediaBoxL10n.error );
				}

				$( '#find-posts-response' ).html( x.data );
			}).fail( function() {
				$( '#find-posts-response' ).text( attachMediaBoxL10n.error );
			});
		}
	};

	nggmlFindPosts = {
		open: function( ) {
			$('#nggml-find-posts-input').focus().keyup( function( event ){
				if ( event.which == 27 ) {
					nggmlFindPosts.close();
				} // close on Escape
			});
			// Pull some results up by default
			nggmlFindPosts.send();
			return false;
		},

		close: function() {
			$('#nggml-find-posts-response').html('');
		},

		send: function() {
			var post = {
					ps: $( '#nggml-find-posts-input' ).val(),
					action: 'find_posts',
					_ajax_nonce: $('#_nggml_ajax_nonce').val()
				},
				spinner = $( '.nggml-find-box-search .spinner' );

			spinner.show();

			$.ajax( ajaxurl, {
				type: 'POST',
				data: post,
				dataType: 'json'
			}).always( function() {
				spinner.hide();
			}).done( function( x ) {
				if ( ! x.success ) {
					$( '#nggml-find-posts-response' ).text( attachMediaBoxL10n.error );
				}
				$( '#nggml-find-posts-response' ).html( x.data );
			}).fail( function() {
				$( '#nggml-find-posts-response' ).text( attachMediaBoxL10n.error );
			});
		}
	};

	$( document ).ready( function() {
    var uploader=null;
    jQuery("a.add-new-h2").click(function(e){
        if(!uploader){
          var uploaderWindow=new media.nggml.UploaderWindow({
            controller: this,
            uploader: {
              dropzone:  document.body,
              container: document.body
            }
          }).render();
          jQuery("body").append(uploaderWindow.el);
          uploaderWindow.ready();
          uploader=new media.nggml.UploaderInline({status:false,message:"",canClose:true,$browser:uploaderWindow.$browser});
          uploader.render();
          var uploaderStatus=new media.nggml.UploaderStatus();
          uploaderStatus.render();
          uploader.$(".upload-inline-status").append(uploaderStatus.el);
          jQuery("div#uploader-inline-container").append(uploader.el);
          uploader.ready();
          uploaderStatus.ready();
        }
        uploader.show();
        e.stopImmediatePropagation;
        e.stopPropagation;
        e.preventDefault;
        return false;
    });
    var ids=[];
    jQuery("table.wp-list-table tbody#the-list tr[id|='post']").each(function(){
        ids.push(parseInt(this.id.substr(5)));
    });
    media.model.Query.defaultArgs.posts_per_page=jQuery("input.screen-per-page#upload_per_page").val();
    media.nggml={};
    media.nggml.collection=media.query({
      post__in:ids,
      orderby:"post__in",
      query:true
    });
    media.nggml.collection.comparator=function(m,n){
        [m,n].forEach(function(v,i,a){
            if(!v.priority){
                var r=v.attributes.compat.item.match(/name='attachments\[\d+\]\[priority\]'\s+value='(\d+)'/m);
                v.priority=r?parseInt(r[1]):Number.MAX_VALUE;
            }
        });
        if(m.priority===n.priority){return m.id===n.id?0:(parseInt(m.id)<parseInt(n.id)?-1:1);}
        return m.priority<n.priority?-1:1;
    };
    media.nggml.collection.on('remove',function(attachment){
        jQuery("div#nggml-alt-media-list-images div#nggml-div-"+attachment.attributes.id).remove();
        jQuery("div#nggml-alt-media-list-titles li#nggml-li-"+attachment.attributes.id).remove();
        jQuery("table.wp-list-table tr#post-"+attachment.attributes.id).remove();
        jQuery("div.tablenav span.displaying-num")
            .text(jQuery("table.wp-list-table tbody#the-list tr[id^='post-']").length+media.nggml.otherPagesCount+" items");        
    });
    media.nggml.collection.more().done(function(){
    });
		$( '#find-posts-submit' ).click( function( event ) {
			if ( ! $( '#find-posts-response input[type="radio"]:checked' ).length )
				event.preventDefault();
		});
		$( '#find-posts .find-box-search :input' ).keypress( function( event ) {
			if ( 13 == event.which ) {
				findPosts.send();
				return false;
			}
		});
		$( '#find-posts-search' ).click( findPosts.send );
		$( '#find-posts-close' ).click( findPosts.close );
		$( '#nggml-find-posts-submit' ).click( function( event ) {
			if ( ! $( '#find-posts-response input[type="radio"]:checked' ).length ) {
                var media=$(".check-column [name='media[]']:checked");
                media=media.map(function(){return this.value;}).get();
                var query={
                    _wpnonce:$("[name='_wpnonce']").val(),
                    _wp_http_referer:$("[name='_wp_http_referer']").val(),
                    action:$("[name='action']").val(),
                    media:media,
                    action2:$("[name='action2']").val(),
                    found_post_id:$("[name='found_post_id']:checked").val(),
                    "find-posts-submit":$("[name='nggml-find-posts-submit']").val()
                };
                location.assign(location.origin+location.pathname+"?"+$.param(query));
            }
			event.preventDefault();
		});
		$( '#nggml-find-posts-search' ).click( nggmlFindPosts.send );
		$( '#doaction, #doaction2' ).click( function( event ) {
			$( 'select[name^="action"]' ).each( function() {
				if ( $(this).val() === 'attach' ) {
					event.preventDefault();
					findPosts.open();
				}
			});
		});

		// Enable whole row to be clicked
		$( '.find-box-inside' ).on( 'click', 'tr', function() {
			$( this ).find( '.found-radio input' ).prop( 'checked', true );
		});
	});
})( jQuery );
