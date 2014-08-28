// extracted from wp-admin\js\media.js

jQuery(document).ready(function(){
    jQuery("div.contextual-help-tabs").append('<li id="tab-link-nggtags"><a href="#tab-panel-nggtags" \
        aria-controls="tab-panel-nggtags">Ngg Tags Features</a></li>');
    jQuery("div.contextual-help-tabs-wrap").append('<div id="tab-panel-nggtags" class="help-tab-content" \
        style="display:none;"><p>Ngg Tags Features</p><p>The filters allow multiple values to be selected. \
        Enabling "Use Checkbox Overlay for Filters" from the "Screen Options" replaces the select box with a overlay of \
        checkboxes. (Note that this feature may not work on all browsers.)</p><p>Enabling \
        "Use Alternate High Density Media List View" from the "Screen Options" replaces the WordPress table view with a \
        "high density" gallery view.</p><p>The "Bulk Actions" include actions for bulk editing taxonomy tags and order \
        priorities.</p></div>');
    jQuery("div.contextual-help-sidebar").append('<a href="http://nggtagsforwpml.wordpress.com/#media-library-for-nggtags" \
        target="_blank">Documentation on Media Library for NGG Tags</a>');
    var select=jQuery("select.nggml-filter");
    var height0=select.parent().height();
    jQuery("form#adv-settings div.screen-options").prepend(
        '<input type="checkbox" id="nggtags-for-ml-use-checkboxes-overlay">'+
        '<label for="nggtags-for-ml-use-checkboxes-overlay">Use Checkbox Overlay for Filters</label>'+
        '<input type="checkbox" id="nggtags-for-ml-use-alt-media-list-pane">'+
        '<label for="nggtags-for-ml-use-alt-media-list-pane">Use Alternate High Density Media List View</label>'
    );
    var nggmlScreenOptions=JSON.parse(jQuery("input#nggml-screen-options").val());
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
        var nggmlScreenOptions={use_checkbox_overlay_for_select:this.checked?1:0};
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
    if(nggmlScreenOptions.use_alt_media_list_pane){showAltMediaListPane();}
    jQuery("input#nggtags-for-ml-use-alt-media-list-pane").change(function(){
        nggmlScreenOptions.use_alt_media_list_pane=this.checked?1:0;
        jQuery.post(ajaxurl,{action:'nggml_update_screen_options',nggml_screen_options:nggmlScreenOptions});
        this.checked?showAltMediaListPane():hideAltMediaListPane();
    });
    function showAltMediaListPane(){
        var height=jQuery(window).height()
            -jQuery("div#wpadminbar").outerHeight(true)
            -jQuery("table.wp-list-table.widefat thead").outerHeight(true)
            -jQuery("table.wp-list-table.widefat tbody#the-list tr[id|='post']").first().outerHeight(true);
        var row=jQuery("tr#nggml-alt-media-list").css({padding:"0px",border:"0px",margin:"0px"}).height(height);
        jQuery("td",row).css({padding:"0px",border:"0px",margin:"0px"});
        var fieldset=jQuery("fieldset#nggml-alt-media-list-pane",row).css({padding:"0px",border:"0px",margin:"0px"})
            .height(height);
        var titles=jQuery("div#nggml-alt-media-list-titles",fieldset).height(height).get(0);
        var images=jQuery("div#nggml-alt-media-list-images",fieldset).height(height).get(0);
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
            li.appendChild(document.createTextNode(title));
            titles.appendChild(li);
            var div=document.createElement("div");
            div.id="nggml-div-"+id;
            div.className="nggml-image-icon-box";
            div.style.width=image.width+"px";
            div.style.height=image.height+"px";
            div.style.position="relative";
            var img=document.createElement("img");
            img.id="nggml-img-"+id;
            img.src=image.src;
            img.width=image.width;
            img.height=image.height;
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
            images.appendChild(div);
            jQuery(this).hide();
        });
        jQuery("table.wp-list-table tbody#the-list tr[id|='post']").first().show();
        var row=jQuery("tr#nggml-alt-media-list");
        jQuery("table.widefat tbody").append(row);
        row.show();
        jQuery("fieldset#nggml-alt-media-list-pane div#nggml-alt-media-list-images div.nggml-image-icon-box").hover(
            function(){
                jQuery(this).addClass("nggml-focused");
                var id=this.id.substr(10);
                var li=jQuery("fieldset#nggml-alt-media-list-pane div#nggml-alt-media-list-titles li#nggml-li-"+id)
                    .addClass("nggml-focused").get(0);
                jQuery("table.wp-list-table tbody#the-list tr[id|='post']").hide();
                jQuery("th.check-column input[type='checkbox'][id|='cb-select']",
                    jQuery("table.wp-list-table tbody#the-list tr[id='post-"+id+"']").show())
                    .prop("checked",jQuery("input[type='checkbox']",this).prop("checked"));
                for(var i=0;i<4;i++){if(!(li=li.previousSibling)){break;}}
                var titles=jQuery("fieldset#nggml-alt-media-list-pane div#nggml-alt-media-list-titles");
                titles.scrollTop(li?jQuery(li).position().top+titles.scrollTop():0);
            },
            function(){
                jQuery(this).removeClass("nggml-focused");
                var id=this.id.substr(10);
                jQuery("fieldset#nggml-alt-media-list-pane div#nggml-alt-media-list-titles li#nggml-li-"+id)
                    .removeClass("nggml-focused");
            }
        );
        jQuery("fieldset#nggml-alt-media-list-pane div#nggml-alt-media-list-titles li.nggml-image-title-box").hover(
            function(){
                jQuery(this).addClass("nggml-focused");
                var id=this.id.substr(9);
                var div=jQuery("fieldset#nggml-alt-media-list-pane div#nggml-alt-media-list-images div#nggml-div-"+id)
                    .addClass("nggml-focused").get(0);
                jQuery("table.wp-list-table tbody#the-list tr[id|='post']").hide();
                jQuery("th.check-column input[type='checkbox'][id|='cb-select']",
                    jQuery("table.wp-list-table tbody#the-list tr#post-"+id).show())
                    .prop("checked",jQuery("input[type='checkbox']",this).prop("checked"));
                for(var i=0;i<4;i++){if(!(div=div.previousSibling)){break;}}
                var images=jQuery("fieldset#nggml-alt-media-list-pane div#nggml-alt-media-list-images");
                images.scrollTop(div?jQuery(div).position().top+images.scrollTop():0);
            },
            function(){
                jQuery(this).removeClass("nggml-focused");
                var id=this.id.substr(9);
                jQuery("fieldset#nggml-alt-media-list-pane div#nggml-alt-media-list-images div#nggml-div-"+id)
                    .removeClass("nggml-focused");
            }
        );
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
        jQuery("fieldset#nggml-alt-media-list-pane div#nggml-alt-media-list-titles li.nggml-image-title-box")
            .click(function(e){
            if(e.target!==this){return;}
            var id=this.id.substr(9);
            var img=jQuery("fieldset#nggml-alt-media-list-pane div#nggml-alt-media-list-images div.nggml-image-icon-box \
                img#nggml-img-"+id);
            showOverlay(id,img.get(0).src,"");
        });
        function showOverlay(id,src,text){
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
    }
    function hideAltMediaListPane(){
        var tr=jQuery("tr#nggml-alt-media-list").hide();
        var fieldset=jQuery("fieldset#nggml-alt-media-list-pane",tr);
        var titles=jQuery("div#nggml-alt-media-list-titles",fieldset);
        jQuery("table.wp-list-table tbody#the-list tr[id|='post']").each(function(){
            jQuery(this).show();
            jQuery("th.check-column input[type='checkbox']",this).prop("disabled",false)
                .prop("checked",jQuery("input[type='checkbox']#nggml-li-checkbox-"+this.id.substr(5),titles).prop("checked"));
        });
        titles.empty();
        jQuery("div#nggml-alt-media-list-images",fieldset).empty();
    }
    jQuery("input[type='checkbox'][id|='cb-select-all']").change(function(){
        var fieldset=jQuery("tr#nggml-alt-media-list fieldset#nggml-alt-media-list-pane");
        jQuery("div#nggml-alt-media-list-titles input[type='checkbox']",fieldset).prop("checked",this.checked);
        jQuery("div#nggml-alt-media-list-images input[type='checkbox']",fieldset).prop("checked",this.checked);
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
        this.cleanTags();
		return false;
	},

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
                jQuery("span.nggml-bulk-priority-edit-item-priority",this.parentNode).text(
                    map[this.dataset.postId.substr(5)][0]+" ");
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
        this.cleanTags();
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
