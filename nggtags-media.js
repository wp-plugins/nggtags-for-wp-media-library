// start of extract from .../wp-includes/js/plupload/wp-plupload.js
/* global pluploadL10n, plupload, _wpPluploadSettings */

window.wp = window.wp || {};

( function( exports, $ ) {
	var Uploader;

	if ( typeof _wpPluploadSettings === 'undefined' ) {
		return;
	}

	/**
	 * An object that helps create a WordPress uploader using plupload.
	 *
	 * @param options - object - The options passed to the new plupload instance.
	 *    Accepts the following parameters:
	 *    - container - The id of uploader container.
	 *    - browser   - The id of button to trigger the file select.
	 *    - dropzone  - The id of file drop target.
	 *    - plupload  - An object of parameters to pass to the plupload instance.
	 *    - params    - An object of parameters to pass to $_POST when uploading the file.
	 *                  Extends this.plupload.multipart_params under the hood.
	 *
	 * @param attributes - object - Attributes and methods for this specific instance.
	 */
	Uploader = function( options ) {
		var self = this,
			isIE = navigator.userAgent.indexOf('Trident/') != -1 || navigator.userAgent.indexOf('MSIE ') != -1,
			elements = {
				container: 'container',
				browser:   'browse_button',
				dropzone:  'drop_element'
			},
			key, error;

		this.supports = {
			upload: Uploader.browser.supported
		};

		this.supported = this.supports.upload;

		if ( ! this.supported ) {
			return;
		}

		// Use deep extend to ensure that multipart_params and other objects are cloned.
		this.plupload = $.extend( true, { multipart_params: {} }, Uploader.defaults );
		this.container = document.body; // Set default container.

		// Extend the instance with options
		//
		// Use deep extend to allow options.plupload to override individual
		// default plupload keys.
		$.extend( true, this, options );

		// Proxy all methods so this always refers to the current instance.
		for ( key in this ) {
			if ( $.isFunction( this[ key ] ) ) {
				this[ key ] = $.proxy( this[ key ], this );
			}
		}

		// Ensure all elements are jQuery elements and have id attributes
		// Then set the proper plupload arguments to the ids.
		for ( key in elements ) {
			if ( ! this[ key ] ) {
				continue;
			}

			this[ key ] = $( this[ key ] ).first();

			if ( ! this[ key ].length ) {
				delete this[ key ];
				continue;
			}

			if ( ! this[ key ].prop('id') ) {
				this[ key ].prop( 'id', '__wp-uploader-id-' + Uploader.uuid++ );
			}

			this.plupload[ elements[ key ] ] = this[ key ].prop('id');
		}

		// If the uploader has neither a browse button nor a dropzone, bail.
		if ( ! ( this.browser && this.browser.length ) && ! ( this.dropzone && this.dropzone.length ) ) {
			return;
		}

		// Make sure flash sends cookies (seems in IE it does without switching to urlstream mode)
		if ( ! isIE && 'flash' === plupload.predictRuntime( this.plupload ) &&
			( ! this.plupload.required_features || ! this.plupload.required_features.hasOwnProperty( 'send_binary_string' ) ) ) {

			this.plupload.required_features = this.plupload.required_features || {};
			this.plupload.required_features.send_binary_string = true;
		}

		this.uploader = new plupload.Uploader( this.plupload );
		delete this.plupload;

		// Set default params and remove this.params alias.
		this.param( this.params || {} );
		delete this.params;

		error = function( message, data, file ) {
			if ( file.attachment ) {
				file.attachment.destroy();
			}

			Uploader.errors.unshift({
				message: message || pluploadL10n.default_error,
				data:    data,
				file:    file
			});

			self.error( message, data, file );
		};

		this.uploader.bind( 'init', function( uploader ) {
			var timer, active, dragdrop,
				dropzone = self.dropzone;

            dragdrop = self.supports.dragdrop = uploader.features.dragdrop && ! Uploader.browser.mobile;

			// Generate drag/drop helper classes.
			if ( ! dropzone ) {
				return;
			}

			dropzone.toggleClass( 'supports-drag-drop', !! dragdrop );

			if ( ! dragdrop ) {
				return dropzone.unbind('.wp-uploader');
			}

			// 'dragenter' doesn't fire correctly,
			// simulate it with a limited 'dragover'
			dropzone.bind( 'dragover.wp-uploader', function() {
				if ( timer ) {
					clearTimeout( timer );
				}

				if ( active ) {
					return;
				}

				dropzone.trigger('dropzone:enter').addClass('drag-over');
				active = true;
			});

			dropzone.bind('dragleave.wp-uploader, drop.wp-uploader', function() {
				// Using an instant timer prevents the drag-over class from
				// being quickly removed and re-added when elements inside the
				// dropzone are repositioned.
				//
				// See http://core.trac.wordpress.org/ticket/21705
				timer = setTimeout( function() {
					active = false;
					dropzone.trigger('dropzone:leave').removeClass('drag-over');
				}, 0 );
			});

			$(self).trigger( 'uploader:ready' );
		});

		this.uploader.init();

		if ( this.browser ) {
			this.browser.on( 'mouseenter', this.refresh );
		} else {
			this.uploader.disableBrowse( true );
			// If HTML5 mode, hide the auto-created file container.
			$('#' + this.uploader.id + '_html5_container').hide();
		}

		this.uploader.bind( 'FilesAdded', function( up, files ) {
			_.each( files, function( file ) {
				var attributes, image;

				// Ignore failed uploads.
				if ( plupload.FAILED === file.status ) {
					return;
				}

				// Generate attributes for a new `Attachment` model.
				attributes = _.extend({
					file:      file,
					uploading: true,
					date:      new Date(),
					filename:  file.name,
					menuOrder: 0
				}, _.pick( file, 'loaded', 'size', 'percent' ) );

				// Handle early mime type scanning for images.
				image = /(?:jpe?g|png|gif)$/i.exec( file.name );

				// Did we find an image?
				if ( image ) {
					attributes.type = 'image';

					// `jpeg`, `png` and `gif` are valid subtypes.
					// `jpg` is not, so map it to `jpeg`.
					attributes.subtype = ( 'jpg' === image[0] ) ? 'jpeg' : image[0];
				}

				// Create the `Attachment`.
				file.attachment = wp.media.model.Attachment.create( attributes );

				Uploader.queue.add( file.attachment );

				self.added( file.attachment );
			});

			up.refresh();
			up.start();
		});

		this.uploader.bind( 'UploadProgress', function( up, file ) {
			file.attachment.set( _.pick( file, 'loaded', 'percent' ) );
			self.progress( file.attachment );
		});

		this.uploader.bind( 'FileUploaded', function( up, file, response ) {
			var complete;

			try {
				response = JSON.parse( response.response );
			} catch ( e ) {
				return error( pluploadL10n.default_error, e, file );
			}

			if ( ! _.isObject( response ) || _.isUndefined( response.success ) )
				return error( pluploadL10n.default_error, null, file );
			else if ( ! response.success )
				return error( response.data && response.data.message, response.data, file );

			_.each(['file','loaded','size','percent'], function( key ) {
				file.attachment.unset( key );
			});

			file.attachment.set( _.extend( response.data, { uploading: false }) );
			wp.media.model.Attachment.get( response.data.id, file.attachment );
      wp.media.nggml.collection.add(file.attachment);

			complete = Uploader.queue.all( function( attachment ) {
				return ! attachment.get('uploading');
			});

			if(complete){
                Uploader.queue.each(function(file){console.log("'FileUploaded'():file=",file);});
                var ids=Uploader.queue.pluck("id");
                r={action:"nggml_get_media_table_rows",data:ids};
                jQuery.post(ajaxurl,r,function(r){
                    var newTrs=r.match(/<tr\s(.|\n)*?<\/tr>/gi);
                    newTrs.forEach(function(v,i,a){
                        var id=/<tr\s+id="post-(\d+)"\s/i.exec(v);
                        id=parseInt(id[1]);
                        var trs=jQuery("tbody#the-list tr");
                        for(var i=trs.length-1;i>=0;i--){
                            var tr=trs[i];
                            var j=parseInt(tr.id.substr(5));
                            var p=parseInt(jQuery(tr).find("td.taxonomy-priority").text());
                            if(p!==NaN||id>j){
                                var $v=jQuery(v).insertAfter(tr);
                                v=null;
                                break;
                            }
                        }
                        if(v){var $v=jQuery("tbody#the-list").prepend(v);}
                        $v.find("td.title div.row-actions span.edit a").click(function(e){
                            var id=jQuery(this).parents("tr")[0].id.substr(5);
                            e.stopImmediatePropagation();
                            e.stopPropagation();
                            e.preventDefault();
                            e.returnValue=false;
                            media.nggml.showEditAttachmentOverlay(id);
                            return false;
                        });
                        var thead=jQuery("table.wp-list-table thead tr");
                        $v.find('td').each(function(){
                            var td=this;
                            this.className.split(" ").forEach(function(v){
                                if(v.indexOf("column-")===0){
                                    if(thead.find("th."+v)[0].style.display==='none'){td.style.display='none';}
                                }
                            });
                        });
                    });
                    if(wp.media.nggml.nggmlScreenOptions.use_alt_media_list_pane){
                        wp.media.nggml.hideAltMediaListPane();
                        wp.media.nggml.showAltMediaListPane();
                    }
                    Uploader.queue.reset();
                });
                jQuery("div.tablenav span.displaying-num")
                    .text(jQuery("table.wp-list-table tbody#the-list tr[id^='post-']").length+" items");
            }

			self.success( file.attachment );
		});

		this.uploader.bind( 'Error', function( up, pluploadError ) {
			var message = pluploadL10n.default_error,
				key;

			// Check for plupload errors.
			for ( key in Uploader.errorMap ) {
				if ( pluploadError.code === plupload[ key ] ) {
					message = Uploader.errorMap[ key ];

					if ( _.isFunction( message ) ) {
						message = message( pluploadError.file, pluploadError );
					}

					break;
				}
			}

			error( message, pluploadError, pluploadError.file );
			up.refresh();
		});

		this.uploader.bind( 'PostInit', function() {
			self.init();
		});
	};

	// Adds the 'defaults' and 'browser' properties.
	$.extend( Uploader, _wpPluploadSettings );

	Uploader.uuid = 0;

	Uploader.errorMap = {
		'FAILED':                 pluploadL10n.upload_failed,
		'FILE_EXTENSION_ERROR':   pluploadL10n.invalid_filetype,
		'IMAGE_FORMAT_ERROR':     pluploadL10n.not_an_image,
		'IMAGE_MEMORY_ERROR':     pluploadL10n.image_memory_exceeded,
		'IMAGE_DIMENSIONS_ERROR': pluploadL10n.image_dimensions_exceeded,
		'GENERIC_ERROR':          pluploadL10n.upload_failed,
		'IO_ERROR':               pluploadL10n.io_error,
		'HTTP_ERROR':             pluploadL10n.http_error,
		'SECURITY_ERROR':         pluploadL10n.security_error,

		'FILE_SIZE_ERROR': function( file ) {
			return pluploadL10n.file_exceeds_size_limit.replace('%s', file.name);
		}
	};

	$.extend( Uploader.prototype, {
		/**
		 * Acts as a shortcut to extending the uploader's multipart_params object.
		 *
		 * param( key )
		 *    Returns the value of the key.
		 *
		 * param( key, value )
		 *    Sets the value of a key.
		 *
		 * param( map )
		 *    Sets values for a map of data.
		 */
		param: function( key, value ) {
			if ( arguments.length === 1 && typeof key === 'string' ) {
				return this.uploader.settings.multipart_params[ key ];
			}

			if ( arguments.length > 1 ) {
				this.uploader.settings.multipart_params[ key ] = value;
			} else {
				$.extend( this.uploader.settings.multipart_params, key );
			}
		},

		init:     function() {},
		error:    function() {},
		success:  function() {},
		added:    function() {},
		progress: function() {},
		complete: function() {},
		refresh:  function() {
			var node, attached, container, id;

			if ( this.browser ) {
				node = this.browser[0];

				// Check if the browser node is in the DOM.
				while ( node ) {
					if ( node === document.body ) {
						attached = true;
						break;
					}
					node = node.parentNode;
				}

				// If the browser node is not attached to the DOM, use a
				// temporary container to house it, as the browser button
				// shims require the button to exist in the DOM at all times.
				if ( ! attached ) {
					id = 'wp-uploader-browser-' + this.uploader.id;

					container = $( '#' + id );
					if ( ! container.length ) {
						container = $('<div class="wp-uploader-browser" />').css({
							position: 'fixed',
							top: '-1000px',
							left: '-1000px',
							height: 0,
							width: 0
						}).attr( 'id', 'wp-uploader-browser-' + this.uploader.id ).appendTo('body');
					}

					container.append( this.browser );
				}
			}

			this.uploader.refresh();
		}
	});

	Uploader.queue = new wp.media.model.Attachments( [], { query: false });
	Uploader.errors = new Backbone.Collection();

	exports.Uploader = Uploader;
})( wp, jQuery );

// end of extract from .../wp-includes/js/plupload/wp-plupload.js

// start of extract from .../wp-includes/js/media-views.js

jQuery(document).ready(function(){
	var l10n;
  var media = wp.media;
	l10n = media.view.l10n = typeof _wpMediaViewsL10n === 'undefined' ? {} : _wpMediaViewsL10n;
  
	/**
	 * Makes it easier to bind events using transitions.
	 *
	 * @param {string} selector
	 * @param {Number} sensitivity
	 * @returns {Promise}
	 */
	media.transition = function( selector, sensitivity ) {
		var deferred = jQuery.Deferred();

		sensitivity = sensitivity || 2000;

		if ( jQuery.support.transition ) {
			if ( ! (selector instanceof jQuery) ) {
				selector = jQuery( selector );
			}

			// Resolve the deferred when the first element finishes animating.
			selector.first().one( jQuery.support.transition.end, deferred.resolve );

			// Just in case the event doesn't trigger, fire a callback.
			_.delay( deferred.resolve, sensitivity );

		// Otherwise, execute on the spot.
		} else {
			deferred.resolve();
		}

		return deferred.promise();
	};

    media.nggml.DetailsView=Backbone.View.extend({
        tagName:   'div',
        className: 'attachment-details',
        template:media.template('attachment-details-two-column'),
        events: {
            'change [data-setting]':          'updateSetting',
            'change [data-setting] input':    'updateSetting',
            'change [data-setting] select':   'updateSetting',
            'change [data-setting] textarea': 'updateSetting',
            'click .delete-attachment':       'deleteAttachment',
            'click .trash-attachment':        'trashAttachment',
            'click .untrash-attachment':      'untrashAttachment',
            'click .edit-attachment':         'editAttachment',
            'click .refresh-attachment':      'refreshAttachment',
            'keydown':                        'toggleSelectionHandler'
        },    
        render:function(){   // excerpted from media.view.Attachment.render() from media-views.js
			var options = _.defaults( this.model.toJSON(), {
					orientation:   'landscape',
					uploading:     false,
					type:          '',
					subtype:       '',
					icon:          '',
					filename:      '',
					caption:       '',
					title:         '',
					dateFormatted: '',
					width:         '',
					height:        '',
					compat:        false,
					alt:           '',
					description:   ''
				}, this.options );

			options.buttons  = {};
            options.describe = false;
			if ( 'image' === options.type ) {
				options.size = this.imageSize();
			}

			options.can = {};
			if ( options.nonces ) {
				options.can.remove = !! options.nonces['delete'];
				options.can.save = !! options.nonces.update;
			}

			if ( options.uploading && ! options.percent ) {
				options.percent = 0;
			}

            var html=this.template( options );
            this.$el.html(html);
            this.$el.find(".attachment-compat").append((new media.nggml.AttachmentCompatView({model:this.model})).render().el);  
            return this;
        },
    
		imageSize: function( size ) {   // excerpted from media.view.Attachment.imageSize() from media-views.js
			var sizes = this.model.get('sizes');

			size = size || 'medium';

			// Use the provided image size if possible.
			if ( sizes && sizes[ size ] ) {
				return _.clone( sizes[ size ] );
			} else {
				return {
					url:         this.model.get('url'),
					width:       this.model.get('width'),
					height:      this.model.get('height'),
					orientation: this.model.get('orientation')
				};
			}
		},
		updateSetting: function( event ) {
			var $setting = jQuery( event.target ).closest('[data-setting]'),
				setting, value;

			if ( ! $setting.length ) {
				return;
			}

			setting = $setting.data('setting');
			value   = event.target.value;

			if ( this.model.get( setting ) !== value ) {
				this.save( setting, value );
			}
		},
		save: function() {
			var view = this,
				save = this._save = this._save || { status: 'ready' },
				request = this.model.save.apply( this.model, arguments ),
				requests = save.requests ? jQuery.when( request, save.requests ) : request,
            model=this.model;
            if(wp.media.nggml.nggmlScreenOptions.use_alt_media_list_pane){
                jQuery("div#updating-overlay-for-table-media-row").css("display","block");
            }else{
                var tr=jQuery("tbody#the-list tr#post-"+model.id);
                createUpdatingOverlayForTableMediaRow(tr,true);
            }
            request.done(function(){
                model.refreshMediaTableRow();
            });
			// If we're waiting to remove 'Saved.', stop.
			if ( save.savedTimer ) {
				clearTimeout( save.savedTimer );
			}

			this.updateSave('waiting');
			save.requests = requests;
			requests.always( function() {
				// If we've performed another request since this one, bail.
				if ( save.requests !== requests ) {
					return;
				}

				view.updateSave( requests.state() === 'resolved' ? 'complete' : 'error' );
				save.savedTimer = setTimeout( function() {
					view.updateSave('ready');
					delete save.savedTimer;
				}, 2000 );
			});
		},
		updateSave: function( status ) {
			var save = this._save = this._save || { status: 'ready' };

			if ( status && status !== save.status ) {
				this.$el.removeClass( 'save-' + save.status );
				save.status = status;
			}

			this.$el.addClass( 'save-' + save.status );
			return this;
		},
		deleteAttachment: function( event ) {
			event.preventDefault();

			if ( confirm( l10n.warnDelete ) ) {
				this.model.destroy();
			}
		},
		editAttachment: function( event ) {
            var editImage=new media.nggml.EditImage({model:this.model});
            editImage.render();
            jQuery("div.media-frame-content").empty().append(editImage.el);
            editImage.loadEditor();
            event.preventDefault();
		}
    });
  
	media.nggml.AttachmentCompatView=Backbone.View.extend({
		tagName:   'form',
		className: 'compat-item',

		events: {
			'submit':          'preventDefault',
			'change input':    'save',
			'change select':   'save',
			'change textarea': 'save'
		},

		initialize: function() {
			this.model.on( 'change:compat', this.render, this );
		},
		/**
		 * @returns {wp.media.view.AttachmentCompat} Returns itself to allow chaining
		 */
		render: function() {
			var compat = this.model.get('compat');
			if ( ! compat || ! compat.item ) {
				return;
			}
			this.$el.html( compat.item );
			return this;
		},
		/**
		 * @param {Object} event
		 */
		preventDefault: function( event ) {
			event.preventDefault();
		},
		/**
		 * @param {Object} event
		 */
		save: function( event ) {
			var data = {};

			if ( event ) {
				event.preventDefault();
			}

			_.each( this.$el.serializeArray(), function( pair ) {
				data[ pair.name ] = pair.value;
			});

			this.model.saveCompat( data );
		}
	});

	media.nggml.EditImage=Backbone.View.extend({
		className:'image-editor',
		template:media.template('image-editor'),
		initialize:function(options){
            this.model=options.model;
            this.editor = window.imageEdit;
        },
		prepare:function(){
			return this.model.toJSON();
		},
		render:function(){
			var options;
			if(this.prepare){
				options=this.prepare();
            }
			if(this.template){
				options=options||{};
                this.$el.html(this.template(options));
			}
			return this;
		},

		loadEditor: function() {
			var dfd = this.editor.open( this.model.get('id'), this.model.get('nonces').edit, this );
			dfd.done( _.bind( this.focus, this ) );
		},

		focus: function() {
			this.$( '.imgedit-submit .button' ).eq( 0 ).focus();
		},

		back:function(){
            jQuery("div.media-frame-content").empty();
            jQuery("div.media-modal .media-modal-close").click();
            media.nggml.showEditAttachmentOverlay(this.model.id);
		},

		refresh: function() {
			this.model.fetch();
		},

		save:function(){
			var self=this;
            jQuery("div#updating-overlay-for-table-media-row").css("display","block");
			this.model.fetch().done(function(){
                self.model.refreshMediaTableRow();
                self.back();
			});
		}

	});

	/**
	 * wp.media.view.UploaderWindow
	 *
	 * @constructor
	 * @augments wp.media.View
	 * @augments wp.Backbone.View
	 * @augments Backbone.View
	 */
	media.nggml.UploaderWindow = Backbone.View.extend({
		tagName:   'div',
		className: 'uploader-window',
		template:  media.template('uploader-window'),

		initialize: function(options) {
			var uploader;

			this.$browser = jQuery('<a href="#" class="browser" />').hide().appendTo('body');
            this.options = options || {};
            uploader = this.options.uploader = _.defaults( this.options.uploader || {}, {
				dropzone:  this.$el,
				browser:   this.$browser,
				params:    {}
			});

			// Ensure the dropzone is a jQuery collection.
			if ( uploader.dropzone && ! (uploader.dropzone instanceof jQuery) ) {
				uploader.dropzone = jQuery( uploader.dropzone );
			}

			//this.controller.on( 'activate', this.refresh, this );

			//this.controller.on( 'detach', function() {
			//	this.$browser.remove();
			//}, this );
		},
    
		render: function() {
			if (this.template){this.$el.html(this.template({}));}
			return this;
    },
    
		refresh: function() {
			if ( this.uploader ) {
				this.uploader.refresh();
			}
		},

		ready: function() {
			var dropzone;

			// If the uploader already exists, bail.
			if ( this.uploader ) {
				return;
			}

			this.uploader = new wp.Uploader( this.options.uploader );

			dropzone = this.uploader.dropzone;
			dropzone.on( 'dropzone:enter', _.bind( this.show, this ) );
			dropzone.on( 'dropzone:leave', _.bind( this.hide, this ) );

			//jQuery( this.uploader ).on( 'uploader:ready', _.bind( this._ready, this ) );
		},

		//_ready: function() {
		//	this.controller.trigger( 'uploader:ready' );
		//},

		show: function() {
			var $el = this.$el.show();

			// Ensure that the animation is triggered by waiting until
			// the transparent element is painted into the DOM.
			_.defer( function() {
				$el.css({ opacity: 1 });
			});
		},

		hide: function() {
			var $el = this.$el.css({ opacity: 0 });

			media.transition( $el ).done( function() {
				// Transition end events are subject to race conditions.
				// Make sure that the value is set as intended.
				if ( '0' === $el.css('opacity') ) {
					$el.hide();
				}
			});

			// https://core.trac.wordpress.org/ticket/27341
			_.delay( function() {
				if ( '0' === $el.css('opacity') && $el.is(':visible') ) {
					$el.hide();
				}
			}, 500 );
		}
	});
  
  
	/**
	 * wp.media.view.UploaderInline
	 *
	 * @constructor
	 * @augments wp.media.View
	 * @augments wp.Backbone.View
	 * @augments Backbone.View
	 */
	media.nggml.UploaderInline = Backbone.View.extend({
		tagName:   'div',
		className: 'uploader-inline',
		template:  media.template('uploader-inline'),

		events: {
			'click .close': 'hide'
		},

		initialize: function(options) {
			this.options = options || {};
			_.defaults( this.options, {
				message: '',
				status:  true,
				canClose: false
			});
		},

		render: function() {
			var options;

			if ( this.prepare )
				options = this.prepare();

			if ( this.template ) {
				options = options || {};
				//this.trigger( 'prepare', options );
                var html=this.template( options );
				this.$el.html( html );
			}
			return this;
		},

		prepare: function() {
/*
			var suggestedWidth = this.controller.state().get('suggestedWidth'),
				suggestedHeight = this.controller.state().get('suggestedHeight'),
*/
				data = {};

			data.message = this.options.message;
			data.canClose = this.options.canClose;
/*
			if ( suggestedWidth && suggestedHeight ) {
				data.suggestedWidth = suggestedWidth;
				data.suggestedHeight = suggestedHeight;
			}
*/
			return data;
		},
		/**
		 * @returns {wp.media.view.UploaderInline} Returns itself to allow chaining
		 */
		dispose: function() {
			if ( this.disposing ) {
				/**
				 * call 'dispose' directly on the parent class
				 */
				return media.View.prototype.dispose.apply( this, arguments );
			}

			// Run remove on `dispose`, so we can be sure to refresh the
			// uploader with a view-less DOM. Track whether we're disposing
			// so we don't trigger an infinite loop.
			this.disposing = true;
			return this.remove();
		},
		/**
		 * @returns {wp.media.view.UploaderInline} Returns itself to allow chaining
		 */
		remove: function() {
			/**
			 * call 'remove' directly on the parent class
			 */
			var result = media.View.prototype.remove.apply( this, arguments );

			_.defer( _.bind( this.refresh, this ) );
			return result;
		},

		refresh: function() {
			//var uploader = this.controller.uploader;

			//if ( uploader ) {
				//uploader.refresh();
			//}
		},
		/**
		 * @returns {wp.media.view.UploaderInline}
		 */
		ready: function() {
			var $browser = this.options.$browser;
      var $placeholder = this.$('.browser');

      // Check if we've already replaced the placeholder.
      if ( $placeholder[0] === $browser[0] ) {
        return;
      }

      $browser.detach().text( $placeholder.text() );
      $browser[0].className = $placeholder[0].className;
      $placeholder.replaceWith( $browser.show() );

			//this.refresh();
			return this;
		},
		show: function() {
			this.$el.removeClass( 'hidden' );
		},
		hide: function() {
			this.$el.addClass( 'hidden' );
		}

	});

	/**
	 * wp.media.view.UploaderStatus
	 *
	 * @constructor
	 * @augments wp.media.View
	 * @augments wp.Backbone.View
	 * @augments Backbone.View
	 */
	media.nggml.UploaderStatus = Backbone.View.extend({
		className: 'media-uploader-status',
		template:  media.template('uploader-status'),

		events: {
			'click .upload-dismiss-errors': 'dismiss'
		},

		initialize: function() {
			this.queue = wp.Uploader.queue;
			this.queue.on( 'add remove reset', this.visibility, this );
			this.queue.on( 'add remove reset change:percent', this.progress, this );
			this.queue.on( 'add remove reset change:uploading', this.info, this );

			this.errors = wp.Uploader.errors;
			this.errors.reset();
			this.errors.on( 'add remove reset', this.visibility, this );
			this.errors.on( 'add', this.error, this );
		},
		render: function() {
			if (this.template){this.$el.html(this.template({}));}
			return this;
    },
		/**
		 * @global wp.Uploader
		 * @returns {wp.media.view.UploaderStatus}
		 */
		dispose: function() {
			wp.Uploader.queue.off( null, null, this );
			/**
			 * call 'dispose' directly on the parent class
			 */
			media.View.prototype.dispose.apply( this, arguments );
			return this;
		},

		visibility: function() {
			this.$el.toggleClass( 'uploading', !! this.queue.length );
			this.$el.toggleClass( 'errors', !! this.errors.length );
			this.$el.toggle( !! this.queue.length || !! this.errors.length );
		},

		ready: function() {
			_.each({
				'$bar':      '.media-progress-bar div',
				'$index':    '.upload-index',
				'$total':    '.upload-total',
				'$filename': '.upload-filename'
			}, function( selector, key ) {
				this[ key ] = this.$( selector );
			}, this );

			this.visibility();
			this.progress();
			this.info();
		},

		progress: function() {
			var queue = this.queue,
				$bar = this.$bar;

			if ( ! $bar || ! queue.length ) {
				return;
			}

			$bar.width( ( queue.reduce( function( memo, attachment ) {
				if ( ! attachment.get('uploading') ) {
					return memo + 100;
				}

				var percent = attachment.get('percent');
				return memo + ( _.isNumber( percent ) ? percent : 100 );
			}, 0 ) / queue.length ) + '%' );
		},

		info: function() {
			var queue = this.queue,
				index = 0, active;

			if ( ! queue.length ) {
				return;
			}

			active = this.queue.find( function( attachment, i ) {
				index = i;
				return attachment.get('uploading');
			});

			this.$index.text( index + 1 );
			this.$total.text( queue.length );
			this.$filename.html( active ? this.filename( active.get('filename') ) : 'updating page ...' );
		},
		/**
		 * @param {string} filename
		 * @returns {string}
		 */
		filename: function( filename ) {
			return media.truncate( _.escape( filename ), 24 );
		},
		/**
		 * @param {Backbone.Model} error
		 */
		error: function( error ) {
			this.views.add( '.upload-errors', new media.nggml.UploaderStatusError({
				filename: this.filename( error.get('file').name ),
				message:  error.get('message')
			}), { at: 0 });
		},

		/**
		 * @global wp.Uploader
		 *
		 * @param {Object} event
		 */
		dismiss: function( event ) {
			var errors = this.views.get('.upload-errors');

			event.preventDefault();

			if ( errors ) {
				_.invoke( errors, 'remove' );
			}
			wp.Uploader.errors.reset();
		}
	});

	/**
	 * wp.media.view.UploaderStatusError
	 *
	 * @constructor
	 * @augments wp.media.View
	 * @augments wp.Backbone.View
	 * @augments Backbone.View
	 */
	media.nggml.UploaderStatusError = Backbone.View.extend({
		className: 'upload-error',
		template:  media.template('uploader-status-error')
	});
  
// end of extract from .../wp-includes/js/media-views.js

    jQuery("div.contextual-help-tabs").append('<li id="tab-link-nggtags"><a href="#tab-panel-nggtags" \
        aria-controls="tab-panel-nggtags">Tags for Media Library</a></li>');
    jQuery("div.contextual-help-tabs-wrap").append('<div id="tab-panel-nggtags" class="help-tab-content" \
        style="display:none;"><p>Ngg Tags Features</p><p>The filters allow multiple values to be selected. \
        Enabling "Use Checkbox Overlay for Filters" from the "Screen Options" replaces the select box with a overlay of \
        checkboxes. (Note that this feature may not work on all browsers.)</p><p>Enabling \
        "Use Alternate High Density Media List View" from the "Screen Options" replaces the WordPress table view with a \
        "high density" gallery view.</p><p>The "Bulk Actions" include actions for bulk editing taxonomy tags and order \
        priorities.</p></div>');
    jQuery("div.contextual-help-sidebar").append('<a href="http://nggtagsforwpml.wordpress.com/#media-library-for-nggtags" \
        target="_blank">Documentation on Tags for Media Library</a>');
    var select=jQuery("select.nggml-filter");
    var height0=select.parent().height();
    jQuery("form#adv-settings div.screen-options").prepend(
        '<input type="checkbox" id="nggtags-for-ml-use-checkboxes-overlay">'+
        '<label for="nggtags-for-ml-use-checkboxes-overlay">Use Checkbox Overlay for Filters</label>'+
        '<input type="checkbox" id="nggtags-for-ml-use-alt-media-list-pane">'+
        '<label for="nggtags-for-ml-use-alt-media-list-pane">Use Alternate High Density Media List View</label>'
    );
    var nggmlScreenOptions=media.nggml.nggmlScreenOptions=JSON.parse(jQuery("input#nggml-screen-options").val());
    jQuery("input#nggtags-for-ml-use-checkboxes-overlay").prop("checked",nggmlScreenOptions.use_checkbox_overlay_for_select);
    jQuery("input#nggtags-for-ml-use-checkboxes-overlay").change(function(){
        var select=jQuery("select.nggml-filter");
        select.prop("multiple",!this.checked);
        select.prop("size",!this.checked?"4":"1");
        select.parent().height(!this.checked?select.height()+10:height0);
        jQuery("select.nggml-filter option").prop("selected",false);
        jQuery("select.nggml-filter option[value='0-nggml-all']").prop("selected",true);
        jQuery("div.nggml-filter-checkbox-overlay input[type='checkbox']").prop("checked",false);
        jQuery("div.nggml-filter-checkbox-overlay input[type='checkbox'][value='0-nggml-all']").prop("checked",true);
        nggmlScreenOptions.use_checkbox_overlay_for_select=this.checked?1:0;
        jQuery.post(ajaxurl,{action:'nggml_update_screen_options',nggml_screen_options:nggmlScreenOptions});
    });
    select
        .prop("multiple",!nggmlScreenOptions.use_checkbox_overlay_for_select)
        .prop("size",!nggmlScreenOptions.use_checkbox_overlay_for_select?"4":"1");
    var height=0;
    var count=0;
    jQuery("select.nggml-filter")
        .mousedown(function(e){
            if(jQuery("input#nggtags-for-ml-use-checkboxes-overlay").prop("checked")){
                var overlay=jQuery("div.nggml-filter-checkbox-overlay",jQuery(this).prev());
                if(overlay.css("display")==="none"){
                    var offset=jQuery(this).outerHeight();
                    overlay.width(jQuery(this).width()).css("top",offset).show();
                    var parent=overlay.parent().parent();
                    if(!height){height=parent.height();}
                    var newHeight=offset+overlay.outerHeight()+20;
                    if(newHeight>parent.height()){parent.height(newHeight);}
                    ++count;
                }
                e.stopImmediatePropagation();
                e.stopPropagation();
                e.preventDefault();
                e.returnValue=false;
                return false;
            }
        });
    jQuery("div.nggml-filter-checkbox-overlay button.nggml-button-close").click(function(e){
        var overlay=jQuery(this).parents("div.nggml-filter-checkbox-overlay").hide();
        if(!--count){overlay.parent().parent().height(height);}
        e.stopImmediatePropagation();
        e.stopPropagation();
        e.preventDefault();
        e.returnValue=false;
        return false;
    });
    jQuery("div.nggml-filter-checkbox-overlay input[type='checkbox']").change(function(e){
        if(this.checked){
            if(this.value!=="0-nggml-all"){
                jQuery("input[type='checkbox'][value='0-nggml-all']",this.parentNode).prop("checked",false);
            }else{
                jQuery("input[type='checkbox'][value!='0-nggml-all']",this.parentNode).prop("checked",false);
            }
        }
        var checked=jQuery("input:checked",this.parentNode);
        if(checked.size()){
            checked.each(function(){
                jQuery("option[value='"+this.value+"']",jQuery(this.parentNode).next().get(0)).prop("selected",this.checked);
            });
        }else{
            jQuery("input[type='checkbox'][value='0-nggml-all']",this.parentNode).prop("checked",true);
            jQuery("option[value='0-nggml-all']",jQuery(this.parentNode).next().get(0)).prop("selected",true);
        }	
    });
    var clicked;
    jQuery("select.nggml-filter option").click(function(e){
        clicked=this;
    });
    jQuery("select.nggml-filter").change(function(e){
        if(!clicked||clicked.parentNode!==this){return;}
        if(clicked.selected){
            if(clicked.value!=="0-nggml-all"){
                jQuery("option[value='0-nggml-all']",this).prop("selected",false);
            }else{
                jQuery("option[value!='0-nggml-all']",this).prop("selected",false);
            }
        }
        var selected=jQuery("option:selected",this);
        if(!selected.size()){
            jQuery("option[value='0-nggml-all']",this).prop("selected",true);
        }
        clicked=null;
    });
    jQuery("input#nggtags-for-ml-use-alt-media-list-pane").prop("checked",nggmlScreenOptions.use_alt_media_list_pane);
    if(nggmlScreenOptions.use_alt_media_list_pane){window.setTimeout(showAltMediaListPane,1000);}
    jQuery("input#nggtags-for-ml-use-alt-media-list-pane").change(function(){
        nggmlScreenOptions.use_alt_media_list_pane=this.checked?1:0;
        jQuery.post(ajaxurl,{action:'nggml_update_screen_options',nggml_screen_options:nggmlScreenOptions});
        if(this.checked){
            jQuery("div.view-switch a.view-list").removeClass("current");
            jQuery("div.view-switch a.view-grid").addClass("current");
            showAltMediaListPane();
        }else{
            jQuery("div.view-switch a.view-grid").removeClass("current");
            jQuery("div.view-switch a.view-list").addClass("current");
            hideAltMediaListPane();
        }
    });
    jQuery("div.view-switch a.view-grid").click(function(e){
        if(!nggmlScreenOptions.use_alt_media_list_pane){
            jQuery("div.view-switch a.view-list").removeClass("current");
            jQuery("div.view-switch a.view-grid").addClass("current");
            nggmlScreenOptions.use_alt_media_list_pane=1;
            jQuery.post(ajaxurl,{action:'nggml_update_screen_options',nggml_screen_options:nggmlScreenOptions});
            jQuery("input#nggtags-for-ml-use-alt-media-list-pane").prop("checked",nggmlScreenOptions.use_alt_media_list_pane);
            showAltMediaListPane();
            e.stopImmediatePropagation();
            e.stopPropagation();
            e.preventDefault();
            e.returnValue=false;
            return false;
        }
    });
    jQuery("div.view-switch a.view-list").click(function(e){
        if(nggmlScreenOptions.use_alt_media_list_pane){
            jQuery("div.view-switch a.view-grid").removeClass("current");
            jQuery("div.view-switch a.view-list").addClass("current");
            nggmlScreenOptions.use_alt_media_list_pane=0;
            jQuery.post(ajaxurl,{action:'nggml_update_screen_options',nggml_screen_options:nggmlScreenOptions});
            jQuery("input#nggtags-for-ml-use-alt-media-list-pane").prop("checked",nggmlScreenOptions.use_alt_media_list_pane);
            hideAltMediaListPane();
            e.stopImmediatePropagation();
            e.stopPropagation();
            e.preventDefault();
            e.returnValue=false;
            return false;
        }
    });
    function showEditAttachmentOverlay(id){
        var model;
        if(!media.nggml.collection||!(model=media.nggml.collection.get(id))){return false;}
        var detailsView=new media.nggml.DetailsView({model:model});
        detailsView.render();
        var modal=jQuery(media.template("media-modal")({}));
        modal.find(".media-modal-close").click(function(e){
            detailsView.remove();
            jQuery(modal).remove();
            modal=null;
            e.stopImmediatePropagation;
            e.stopPropagation;
            e.preventDefault;
            return false;
        });
        modal.find(".media-modal-content").append('<div class="edit-attachment-frame"></div>');
        var frame=modal.find(".edit-attachment-frame");
        frame.html(media.template("edit-attachment-frame")({hasPrevious:true,hasNext:true}));
        frame.find(".left,.right").click(function(e){
            var index=-1;
            media.nggml.collection.each(function(e,i,l){if(e.id==id){index=i;}});
            jQuery(this).hasClass('left')?--index:++index;
            if(index<0){index=media.nggml.collection.length-1;}
            else if(index===media.nggml.collection.length){index=0;}
            var next=media.nggml.collection.at(index);
            detailsView.remove();
            jQuery(modal).remove();
            modal=null;
            showEditAttachmentOverlay(next.id);
            e.stopImmediatePropagation;
            e.stopPropagation;
            e.preventDefault;
            return false;
        });
        modal.find(".media-frame-content").append(detailsView.el);
        jQuery("body").append(modal);
        // Start of adapted from .../wp-includes//mediaelement/wp-mediaelement.js
        (function(){
            var settings = {};
            if ( typeof _wpmejsSettings !== 'undefined' ) {
                settings.pluginPath = _wpmejsSettings.pluginPath;
            }
            settings.success = function (mejs) {
                var autoplay = mejs.attributes.autoplay && 'false' !== mejs.attributes.autoplay;
                if ( 'flash' === mejs.pluginType && autoplay ) {
                mejs.addEventListener( 'canplay', function () {
                    mejs.play();
                }, false );
                }
            };
            modal.find('.wp-audio-shortcode,.wp-video-shortcode').mediaelementplayer(settings);
        }());
        // End of adapted from .../wp-includes//mediaelement/wp-mediaelement.js
        return true;
    }
    media.nggml.showEditAttachmentOverlay=showEditAttachmentOverlay;
    jQuery("table.wp-list-table tbody#the-list tr td.title div.row-actions span.edit a").each(function(){
      jQuery(this).click(function(e){
        var id=jQuery(this).parents("tr")[0].id.substr(5);
        e.stopImmediatePropagation();
        e.stopPropagation();
        e.preventDefault();
        e.returnValue=false;
        showEditAttachmentOverlay(id);
        return false;
      });
    });
    function createUpdatingOverlayForTableMediaRow(tr,display){
      var updatingOverlayDiv=document.createElement("div");
      updatingOverlayDiv.id="updating-overlay-for-table-media-row";
      updatingOverlayDiv.style.position="absolute";
      updatingOverlayDiv.style.left=tr.position().left+"px";
      updatingOverlayDiv.style.top=tr.position().top+"px";    
      updatingOverlayDiv.style.zIndex=10000;
      updatingOverlayDiv.style.opacity=0.5;
      updatingOverlayDiv.style.fontSize=Math.floor(tr.height()/2)+"px";
      updatingOverlayDiv.style.textAlign="center";
      var p=Math.floor(tr.height()/5);
      updatingOverlayDiv.style.padding=p+"px";
      updatingOverlayDiv.style.width=(tr.width()-p-p-6)+"px";
      updatingOverlayDiv.style.height=(tr.height()-p-p-6)+"px";
      updatingOverlayDiv.style.color="red";
      updatingOverlayDiv.textContent="Refreshing...";
      updatingOverlayDiv.style.border="3px dashed red";
      updatingOverlayDiv.style.display=display?"block":"none";
      //jQuery("table.wp-list-table tbody#the-list").append(updatingOverlayDiv);
      jQuery("table.wp-list-table").parent().append(updatingOverlayDiv);
    }
    media.nggml.createUpdatingOverlayForTableMediaRow=createUpdatingOverlayForTableMediaRow;
    function showAltMediaListPane(){
        jQuery("table.wp-list-table tr td.title div.row-actions").css("display","none");
        var maxRowOuterHeight=0;
        jQuery("table.wp-list-table.widefat tbody#the-list tr[id|='post']").each(function(){
            var h=jQuery(this).outerHeight(true);
            if(h>maxRowOuterHeight){maxRowOuterHeight=h;}
        }); 
        jQuery("table.wp-list-table.widefat tbody#the-list tr[id|='post']").each(function(){
            var d=maxRowOuterHeight-jQuery(this).outerHeight(true);
            jQuery(this).height(jQuery(this).height()+d);
        });
        var height=jQuery(window).height()
            -jQuery("div#wpadminbar").outerHeight(true)
            -jQuery("table.wp-list-table.widefat thead").outerHeight(true)
            -maxRowOuterHeight;
        var row=jQuery("tr#nggml-alt-media-list").css({padding:"0px",border:"0px",margin:"0px"}).height(height);
        jQuery("td",row).css({padding:"0px",border:"0px",margin:"0px"});
        var fieldset=jQuery("fieldset#nggml-alt-media-list-pane",row).css({padding:"0px",border:"0px",margin:"0px"})
            .height(height);
        var titles=jQuery("div#nggml-alt-media-list-titles",fieldset).height(height).get(0);
        var images=jQuery("div#nggml-alt-media-list-images",fieldset).height(height).get(0);
        var posts=new Array();
        jQuery("table.wp-list-table tbody#the-list tr[id|='post']").each(function(){
            var checkbox=jQuery("th.check-column input[type='checkbox']",this).prop("disabled",true).get(0);
            var id=this.id.substr(5);
            var title=jQuery("td.title a",this).first().text();
            var image=jQuery("td.media-icon a img",this).get(0);
            var liCheckbox=document.createElement("input");
            liCheckbox.id="nggml-li-checkbox-"+id;
            liCheckbox.type="checkbox";
            liCheckbox.name="media[]";
            liCheckbox.value=checkbox.value;
            liCheckbox.checked=checkbox.checked;
            var li=document.createElement("li");
            li.id="nggml-li-"+id;
            li.className="nggml-image-title-box";
            li.appendChild(liCheckbox);
            var span=document.createElement("span");
            span.className="nggml-image-title-text";
            span.appendChild(document.createTextNode(title));
            li.appendChild(span);
            titles.appendChild(li);
            var width=80;
            var div=document.createElement("div");
            div.id="nggml-div-"+id;
            div.className="nggml-image-icon-box";
            div.style.width=width+"px";
            div.style.height=width+"px";
            var img=document.createElement("img");
            img.id="nggml-img-"+id;
            img.className="nggml-image-icon-image";
            img.src=image.src;
            var imgStyle=img.style;
            imgStyle.maxWidth=width+"px";
            imgStyle.maxHeight=width+"px";
            div.appendChild(img);
            var imgCheckbox=document.createElement("input");
            imgCheckbox.id="nggml-img-checkbox-"+id;
            imgCheckbox.type="checkbox";
            imgCheckbox.value=checkbox.value;
            imgCheckbox.checked=checkbox.checked;
            imgCheckbox.style.position="absolute";
            imgCheckbox.style.left="4px";
            imgCheckbox.style.top="4px";
            imgCheckbox.style.zIndex=100;
            div.appendChild(imgCheckbox);
            var imgLock=document.createElement("div");
            imgLock.className="nggml-img-lock";
            imgLock.style.display="inline-block";
            imgLock.style.position="absolute";
            imgLock.style.top="0";
            imgLock.style.right="0";
            imgLock.style.zIndex=100;
            div.appendChild(imgLock);
            images.appendChild(div);
            jQuery(this).hide();
            posts.push(id);
        });
        var tr=jQuery("table.wp-list-table tbody#the-list tr[id|='post']").first().show();
        createUpdatingOverlayForTableMediaRow(tr);
        var row=jQuery("tr#nggml-alt-media-list");
        jQuery("table.widefat tbody").append(row);
        row.show();
        jQuery("fieldset#nggml-alt-media-list-pane div#nggml-alt-media-list-images div.nggml-image-icon-box").hover(
            function(){
                if(nggmlImageIconLocked){return;}
                jQuery(this).addClass("nggml-focused");
                var id=this.id.substr(10);
                var li=jQuery("fieldset#nggml-alt-media-list-pane div#nggml-alt-media-list-titles li#nggml-li-"+id)
                    .addClass("nggml-focused").get(0);
                jQuery("table.wp-list-table tbody#the-list tr[id|='post']").hide();
                jQuery("th.check-column input[type='checkbox'][id|='cb-select']",
                    jQuery("table.wp-list-table tbody#the-list tr[id='post-"+id+"']").show())
                    .prop("checked",jQuery("input[type='checkbox']",this).prop("checked"));
                jQuery("div#updating-overlay-for-table-media-row")[0].style.top=
                    jQuery("table.wp-list-table tbody#the-list tr[id='post-"+id+"']").position().top+"px";    
                for(var i=0;i<4;i++){if(!(li=li.previousSibling)){break;}}
                var titles=jQuery("fieldset#nggml-alt-media-list-pane div#nggml-alt-media-list-titles");
                titles.scrollTop(li?jQuery(li).position().top+titles.scrollTop():0);
                var top=jQuery("table.wp-list-table").offset().top;
                var divAdminBar=jQuery("div#wpadminbar");
                if(divAdminBar.length){top-=divAdminBar.outerHeight(true);}
                jQuery("body").scrollTop(top);
                if(jQuery("body").scrollTop()!==top){jQuery("html").scrollTop(top);}
                if(jQuery(this).position().top<0){
                    var images=jQuery("fieldset#nggml-alt-media-list-pane div#nggml-alt-media-list-images");
                    images.scrollTop(jQuery(this).position().top+images.scrollTop());
                }
            },
            function(){
                if(nggmlImageIconLocked){return;}
                jQuery(this).removeClass("nggml-focused");
                var id=this.id.substr(10);
                jQuery("fieldset#nggml-alt-media-list-pane div#nggml-alt-media-list-titles li#nggml-li-"+id)
                    .removeClass("nggml-focused");
            }
        );
        jQuery("fieldset#nggml-alt-media-list-pane div#nggml-alt-media-list-titles li.nggml-image-title-box").hover(
            function(){
                if(nggmlImageIconLocked){return;}
                jQuery(this).addClass("nggml-focused");
                var id=this.id.substr(9);
                var div=jQuery("fieldset#nggml-alt-media-list-pane div#nggml-alt-media-list-images div#nggml-div-"+id)
                    .addClass("nggml-focused").get(0);
                jQuery("table.wp-list-table tbody#the-list tr[id|='post']").hide();
                jQuery("th.check-column input[type='checkbox'][id|='cb-select']",
                    jQuery("table.wp-list-table tbody#the-list tr#post-"+id).show())
                    .prop("checked",jQuery("input[type='checkbox']",this).prop("checked"));
                jQuery("div#updating-overlay-for-table-media-row")[0].style.top=
                    jQuery("table.wp-list-table tbody#the-list tr#post-"+id).position().top+"px";    
                for(var i=0;i<4;i++){if(!(div=div.previousSibling)){break;}}
                var images=jQuery("fieldset#nggml-alt-media-list-pane div#nggml-alt-media-list-images");
                images.scrollTop(div?jQuery(div).position().top+images.scrollTop():0);
                var top=jQuery("table.wp-list-table").offset().top;
                var divAdminBar=jQuery("div#wpadminbar");
                if(divAdminBar.length){top-=divAdminBar.outerHeight(true);}
                jQuery("body").scrollTop(top);
                if(jQuery("body").scrollTop()!==top){jQuery("html").scrollTop(top);}
            },
            function(){
                if(nggmlImageIconLocked){return;}
                jQuery(this).removeClass("nggml-focused");
                var id=this.id.substr(9);
                jQuery("fieldset#nggml-alt-media-list-pane div#nggml-alt-media-list-images div#nggml-div-"+id)
                    .removeClass("nggml-focused");
            }
        );
        var nggmlImageIconLocked=false;
        jQuery("fieldset#nggml-alt-media-list-pane div#nggml-alt-media-list-images div.nggml-image-icon-box \
            div.nggml-img-lock").click(function(){
            if(!nggmlImageIconLocked){
                this.style.color="red";
                nggmlImageIconLocked=true;
            }else if(this.style.color==="red"){
                this.style.color="black";
                nggmlImageIconLocked=false;
            }
        });
        jQuery("fieldset#nggml-alt-media-list-pane div#nggml-alt-media-list-images div.nggml-image-icon-box \
            input[type='checkbox']").change(function(){
            var id=this.id.substr(19);
            jQuery("fieldset#nggml-alt-media-list-pane div#nggml-alt-media-list-titles input#nggml-li-checkbox-"+id)
                .prop("checked",this.checked);
            jQuery("table.wp-list-table tbody#the-list tr#post-"+id+" input[type='checkbox']#cb-select-"+id)
                .prop("checked",this.checked);
        });
        jQuery("fieldset#nggml-alt-media-list-pane div#nggml-alt-media-list-titles li.nggml-image-title-box \
            input[type='checkbox']").change(function(){
            var id=this.id.substr(18);
            jQuery("fieldset#nggml-alt-media-list-pane div#nggml-alt-media-list-images input#nggml-img-checkbox-"+id)
                .prop("checked",this.checked);
            jQuery("table.wp-list-table tbody#the-list tr#post-"+id+" input[type='checkbox']#cb-select-"+id)
                .prop("checked",this.checked);
        });
        jQuery("fieldset#nggml-alt-media-list-pane div#nggml-alt-media-list-images div.nggml-image-icon-box img")
            .click(function(e){
            if(e.target!==this){return;}
            showOverlay(this.id.substr(10),this.src,"");
        });
        jQuery("fieldset#nggml-alt-media-list-pane div#nggml-alt-media-list-titles li.nggml-image-title-box \
            input[type='checkbox']").click(function(e){
            e.stopPropagation();
        });
        jQuery("fieldset#nggml-alt-media-list-pane div#nggml-alt-media-list-titles li.nggml-image-title-box span.nggml-image-title-text")
            .click(function(e){
            if(e.target!==this){return;}
            var id=this.parentNode.id.substr(9);
            var img=jQuery("fieldset#nggml-alt-media-list-pane div#nggml-alt-media-list-images div.nggml-image-icon-box \
                img#nggml-img-"+id);
            showOverlay(id,img.get(0).src,"");
        });
        function showOverlay(id,src,text){
            if(showEditAttachmentOverlay(id)){return;}
            function append(div){
                var container=document.createElement("div");
                container.className="nggml-table-data";
                var ths=jQuery("table.wp-list-table thead tr th");
                var tds=jQuery("th,td",jQuery("table.wp-list-table tbody#the-list tr#post-"+id));
                for(var i=0;i<tds.length;i++){
                    var td=jQuery(tds.get(i));
                    if(td.hasClass("check-column")||td.hasClass("column-icon")||td.hasClass("column-title")){continue;}
                    var th=jQuery(ths.get(i));
                    var span=document.createElement("span");
                    span.className="nggml-column-name";
                    span.textContent=(th.get(0).id!=="comments"?th.text():"Comments")+": ";
                    var text=document.createTextNode(td.text());
                    var p=document.createElement("p");
                    p.appendChild(span);
                    p.appendChild(text);
                    container.appendChild(p);
                    jQuery("div.nggml-overlay-left",div).append(container);
                }
            }
            var controls=jQuery("div#nggml-overlay div.nggml-overlay-controls");
            jQuery("p.nggml_actions",controls).remove();
            var actions=jQuery("table.wp-list-table tbody#the-list tr#post-"+id+" td.column-title div.row-actions");
            var p=document.createElement("p");
            p.className="nggml_actions";
            var a=document.createElement("a");
            var a0=jQuery("span.edit a",actions).get(0);
            a.href=a0.href;
            a.textContent=a0.textContent;
            p.appendChild(a);
            p.appendChild(document.createTextNode("|"));
            var a=document.createElement("a");
            var a0=jQuery("span.delete a",actions).get(0);
            a.className=a0.className;
            a.onclick=function(){return showNotice.warn();};
            a.href=a0.href;
            a.textContent=a0.textContent;
            p.appendChild(a);
            p.appendChild(document.createTextNode("|"));
            var a=document.createElement("a");
            var a0=jQuery("span.view a",actions).get(0);
            a.href=a0.href;
            a.title=a0.title;
            a.rel=a0.rel;
            a.textContent=a0.textContent;
            p.appendChild(a);
            controls.prepend(p);
            var div=jQuery("div#nggml-overlay div.nggml-overlay-container");
            div.empty();
            var img=document.createElement("img");
            img.src=src;
            div.append(img);
            var p=document.createElement("p");
            p.textContent="loading...";
            div.append(p);
            jQuery.get(ajaxurl,{action:'nggml_get_image',nggml_image_id:id},function(r){
                var overlay=div.parents("div#nggml-overlay");
                var overlayChromeHeight=overlay.outerHeight(true)-overlay.height();
                var chromeHeight=div.outerHeight(true)-div.height();
                div.empty();
                div.html(r);
                append(div);
                var controlsHeight=controls.outerHeight(true);
                div.height(Math.floor(0.9*jQuery(window).height())-overlayChromeHeight-controlsHeight-chromeHeight);
            },"html");
            div.parents("div#nggml-overlay").toggle();
        }
        jQuery("div#nggml-overlay div.nggml-overlay-controls button.nggml-button-close").click(function(){
            jQuery(this).parents("div#nggml-overlay").hide();
        });
        // ### ajax for models
    }
    media.nggml.showAltMediaListPane=showAltMediaListPane;
    function hideAltMediaListPane(){
        var tr=jQuery("tr#nggml-alt-media-list").hide();
        var fieldset=jQuery("fieldset#nggml-alt-media-list-pane",tr);
        var titles=jQuery("div#nggml-alt-media-list-titles",fieldset);
        jQuery("table.wp-list-table tbody#the-list tr[id|='post']").each(function(){
            jQuery(this).css("height","auto");
            jQuery(this).show();
            jQuery("th.check-column input[type='checkbox']",this).prop("disabled",false)
                .prop("checked",jQuery("input[type='checkbox']#nggml-li-checkbox-"+this.id.substr(5),titles).prop("checked"));
        });
        jQuery("table.wp-list-table tr td.title div.row-actions").css("display","block");
        titles.empty();
        jQuery("div#nggml-alt-media-list-images",fieldset).empty();
        jQuery("div#updating-overlay-for-table-media-row").remove();
    }
    media.nggml.hideAltMediaListPane=hideAltMediaListPane;
    jQuery("input[type='checkbox'][id|='cb-select-all']").change(function(){
        var fieldset=jQuery("tr#nggml-alt-media-list fieldset#nggml-alt-media-list-pane");
        jQuery("div#nggml-alt-media-list-titles input[type='checkbox']",fieldset).prop("checked",this.checked);
        jQuery("div#nggml-alt-media-list-images input[type='checkbox']",fieldset).prop("checked",this.checked);
        if(window.navigator.userAgent.toLowerCase().indexOf("chrome")>=0){
            var checked=this.checked;
            var checkboxes=jQuery("tbody#the-list tr th.check-column input[type='checkbox'][name^='media']").each(function(){
                jQuery(this).parents("tr[id^='post-']")[0].style.display="table-row";
                this.checked=checked;
            });
            window.setTimeout(function(){
                checkboxes.parents("tr[id^='post-']").css("display","none")[0].style.display="table-row";
            },1);
        }else{
            jQuery("tbody#the-list tr th.check-column input[type='checkbox'][name^='media']").prop("checked",this.checked);
        }
    });
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
        if(wp.media.nggml.nggmlScreenOptions.use_alt_media_list_pane){
            wp.media.nggml.hideAltMediaListPane();
            window.setTimeout(wp.media.nggml.showAltMediaListPane,1000);
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
    //this.cleanTags();
		return false;
	},

  /*
    cleanTags : function(){
        var posts=new Array();
        jQuery('tr#bulk-edit div#bulk-titles div').each(function(){
            posts.push(this.id.substr(4));
        });
        jQuery('tr#bulk-edit div.tagsdiv').each(function(){
            var map=new Object();
            jQuery('input.nggtags-for-ml-post_id-to-tags-to-edit',this.parentNode).val().split(';').forEach(function(p){
                if(p){
                    var m=p.split(':');
                    map[m[0]]=m[1]?m[1].split(','):null;
                }
            });
            valid=new Array();
            for(var p in map){
                if(!map[p]||posts.indexOf(p)===-1){continue;}
                valid=valid.concat(map[p]);
            }
            var added=jQuery('input#nggtags-for-ml-added-'+this.id).val();
            if(added){valid=valid.concat(added.substr(1).split(','))};
            jQuery('div.tagchecklist span',this).each(function(){
                var t=this.textContent.substr(this.textContent.indexOf('X')+1).trim();
                if(valid.indexOf(t)!==-1){
                    this.style.display="inline";
                }else{
                    this.style.display="none";
                }
            });
        });
    },
*/
    
  cleanTagsInitial:function(){
    var posts=new Array();
    jQuery('tr#bulk-edit div#bulk-titles div').each(function(){
      posts.push(this.id.substr(4));
    });
    var table=jQuery("table.wp-list-table tbody#the-list");
    table.find("tr#bulk-edit td div.tagsdiv div.tagchecklist").each(function(){
      jQuery(this).empty();
      var tax=this.parentNode.id;
      var tagchecklist=this;
      var id=0;
      var tags=[];
      posts.forEach(function(v,i,a){
        table.find("tr#post-"+v+" td.taxonomy-"+tax+" a").each(function(){
          if(tags.indexOf(this.textContent)===-1){
            tags.push(this.textContent);
            var span=jQuery("<span />").text(this.textContent);
            var xbutton=jQuery('<a id="'+tax+'-check-num-'+(id++)+'" class="ntdelbutton">X</a>');
            xbutton.click(function(){tagBox.parseTags(this);});
            jQuery(tagchecklist).append(span.prepend('&nbsp;').prepend(xbutton));
          }
        });
      });
      tags=tags.join(",");
      jQuery("textarea[name='tax_input["+tax+"]']").val(tags);
      jQuery("input[name='nggtags-for-ml-initial["+tax+"]']").val(tags);
      jQuery("input[name='nggtags-for-ml-added["+tax+"]']").val("");
    });
  },
  
    cleanImages:function(){
        function revertOrder(){
            var map=new Object();
            jQuery("input#nggtags-for-ml-post_id-to-tags-to-edit-priority").val().split(";").forEach(function(p){
                if(p){
                    var m=p.split(":");
                    map[m[0]]=m[1]?m[1].split(","):[""];
                }
            });
            var images=jQuery("div#nggml-bulk-priority-edit-images img");
            images.each(function(){
                var key=this.dataset.postId.substr(5);
                jQuery("span.nggml-bulk-priority-edit-item-priority",this.parentNode).text(
                    (map.hasOwnProperty(key)?map[key][0]:"")+" ");
            });
            var start=jQuery("span.nggml-bulk-priority-edit-item-priority",images.get(0).parentNode).text();
            var increment=100;
            if(jQuery.isNumeric(start)&&start>0){
                start=Math.ceil(start);
                if(images.length>1){
                    var next=jQuery("span.nggml-bulk-priority-edit-item-priority",images.get(1).parentNode).text();
                    if(jQuery.isNumeric(next)&&next>start){
                        increment=Math.ceil(next-start);
                    }
                }
            }else{
                start=100;
            }
            jQuery("input#nggml-bulk-priority-edit-start").val(start);
            jQuery("input#nggml-bulk-priority-edit-increment").val(increment);
            jQuery("input#nggml-bulk-priority-edit-order").val("reverted");
        }
        function saveOrder(){
            var start=Number(jQuery('input#nggml-bulk-priority-edit-start').val());
            var increment=Number(jQuery('input#nggml-bulk-priority-edit-increment').val());
            var order="";
            var priority=start;
            jQuery("div#nggml-bulk-priority-edit-images img").each(function(){
                jQuery("span.nggml-bulk-priority-edit-item-priority",this.parentNode).text(priority+" ");
                order+=this.dataset.postId+";";
                priority+=increment;
            });
            jQuery("input#nggml-bulk-priority-edit-order").val(order);
        }
        var posts=new Array();
        jQuery('tr#bulk-edit div#bulk-titles div').each(function(){
            posts.push(this.id.substr(4));
        });
        var container=jQuery('div#nggml-bulk-priority-edit-images').get(0);
        jQuery(container).empty();
        var height=0;
        jQuery('table.wp-list-table tbody#the-list td.media-icon img').each(function(){
            var post = jQuery(this).parents('tr').get(0).id.substr(5);
            if(posts.indexOf(post)!==-1){
                var slot=document.createElement("div");
                slot.className="nggml-bulk-priority-edit-slot";
                slot.style.textAlign="center";
                slot.style.width=(this.width+30)+"px";
                var insert=document.createElement("div");
                insert.className="nggml-bulk-priority-edit-insert";
                insert.style.height=this.height+"px";
                var item=document.createElement("div");
                item.className="nggml-bulk-priority-edit-item";
                item.style.width=(this.width+10)+"px";
                var image=document.createElement("img");
                image.src=this.src;
                image.width=this.width;
                image.height=this.height;
                image.dataset.postId=jQuery(this).parents("tr").get(0).id;
                image.title=jQuery(jQuery("td.title a",jQuery(this).parents("tr").get(0)).get(0)).text().trim();
                var br0=document.createElement("br");
                var spanPriority=document.createElement("span");
                var br1=document.createElement("br");
                spanPriority.className="nggml-bulk-priority-edit-item-priority";
                var spanTitle=document.createElement("span");
                var title=document.createTextNode(image.title);
                spanTitle.appendChild(title);
                var xbutton=jQuery('<a id="ntdelbutton-' + image.dataset.postId + '" class="ntdelbutton">X</a>');
                xbutton.click(function(){
                    jQuery(this.parentNode.parentNode).remove();
                    saveOrder();
                });
                item.appendChild(xbutton.get(0));
                item.appendChild(image);
                item.appendChild(br0);
                item.appendChild(spanPriority);
                item.appendChild(br1);
                item.appendChild(spanTitle);
                slot.appendChild(insert);
                slot.appendChild(item);
                container.appendChild(slot);
                height=this.height;
            }
        });
        var slot=document.createElement("div");
        slot.className="nggml-bulk-priority-edit-slot";
        var insert=document.createElement("div");
        insert.className="nggml-bulk-priority-edit-insert";
        insert.style.height=height+"px";
        slot.appendChild(insert);
        container.appendChild(slot);       
        jQuery("div.nggml-bulk-priority-edit-slot").draggable({cursor:"crosshair",revert:true});
        jQuery("div.nggml-bulk-priority-edit-insert").droppable({accept:"div.nggml-bulk-priority-edit-slot",tolerance:"touch",
            hoverClass:"hover",drop:function(e,u){
            jQuery(this.parentNode).before(u.draggable);
            saveOrder();
        }});
        jQuery('input#nggml-bulk-priority-edit-start').change(saveOrder);
        jQuery('input#nggml-bulk-priority-edit-increment').change(saveOrder);
        revertOrder();
        return saveOrder;
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
    //this.cleanTags();
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
