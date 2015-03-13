
altGallery={
    meta:{},
    metaLocked:false,
    gallery:function(){
        this.images=[];
    },
    init:function(){
        if(!nggmlAltGalleryEnabled){return;}
        var self=this;
        var divGallery=jQuery("div.gallery").css("position","relative");
        if(!divGallery.length){return;}
        divGallery=divGallery.filter(function(){
            if(jQuery("dl.gallery-item dt.gallery-icon a",this).length){
                return true;
            }else if(jQuery("figure.gallery-item div.gallery-icon a",this).length){
                return true;
            }else{
                jQuery("dl.gallery-item dt.gallery-icon img",this).removeAttr("data-orig-file")
                    .removeAttr("data-medium-file").removeAttr("data-large-file");
                return false;
            }
        });
        if(!divGallery.length){return;}
        divGallery.each(function(){
            var divGallery=jQuery(this);
            var self1=new self.gallery();
            self1.extract(divGallery);
            if(self1.images.length<2){return;}
            var divControls=document.createElement("div");
            divControls.className="nggml-alt-gallery-controls";
            //var button=document.createElement("button");
            //button.id="nggml-alt-gallery-show";
            //button.appendChild(document.createTextNode("Alternate View"));
            //divControls.appendChild(button);
            var select=document.createElement("select");
            select.className="nggml-gallery-select-view";
            [{v:"standard",l:"Standard View"},{v:"titles",l:"Titles View"},{v:"large",l:"Large Image View"}]
                .forEach(function(o){
                var option=document.createElement("option");
                option.value=o.v;
                option.textContent=o.l;
                select.appendChild(option);
            });
            divControls.appendChild(select);
            var buttonColor=document.createElement("button");
            buttonColor.id="nggml-alt-gallery-unfocused";
            divControls.appendChild(buttonColor);
            var buttonColor=document.createElement("button");
            buttonColor.id="nggml-alt-gallery-focused";
            buttonColor.style.backgroundColor=nggmlAltGalleryFocusColor;
            divControls.appendChild(buttonColor);
            divGallery.before(divControls);
            self1.getMeta();
            var divAltGallery=self1.recreate();
            divAltGallery.style.zIndex=1000;
            divAltGallery.style.display="none";
            divGallery.append(divAltGallery);
            jQuery(select).change(function(){
                switch(this.options[this.selectedIndex].value){
                case "standard":
                    divAltGallery.style.display="none";
                    break;
                case "titles":
                    jQuery(divAltGallery).find("div.nggml-alt-gallery-large").css("display","none");
                    var iconsDiv=jQuery(divAltGallery).find("div.nggml-alt-gallery-icons");
                    jQuery(divAltGallery).prepend(iconsDiv);
                    var windowHeight=jQuery(window).height();
                    var divAdminBar=jQuery("div#wpadminbar");
                    if(divAdminBar.length){windowHeight-=divAdminBar.outerHeight(true);}
                    jQuery("div.nggml-alt-gallery-titles",divAltGallery).height(windowHeight);
                    iconsDiv.height(windowHeight).css("width","74%");   // TODO: better W - w
                    divAltGallery.style.display="block";
                    var divGalleryHeight=divGallery.height();
                    var divAltGalleryHeight=jQuery(divAltGallery).height();
                    if(divAltGalleryHeight<divGalleryHeight){jQuery(divAltGallery).height(divGalleryHeight);}
                    else if(divGalleryHeight<divAltGalleryHeight){jQuery(divGallery).height(divAltGalleryHeight);}
                    break;
                case "large":
                    divAltGallery.style.display="block";
                    var bottomDiv=jQuery(divAltGallery).find("div.nggml-alt-gallery-bottom");
                    var iconsDiv=jQuery(divAltGallery).find("div.nggml-alt-gallery-icons");
                    var imgDiv=iconsDiv.find("div.nggml-alt-gallery");
                    var width=Math.ceil(imgDiv.length/2)*imgDiv.outerWidth()+24;
                    var bottomWidth=bottomDiv.width();
                    if(width<bottomWidth){width=bottomWidth;}
                    iconsDiv.height(2*imgDiv.outerHeight()).width(width);
                    bottomDiv.append(iconsDiv);
                    var windowHeight=jQuery(window).height();
                    var divAdminBar=jQuery("div#wpadminbar");
                    if(divAdminBar.length){windowHeight-=divAdminBar.outerHeight(true);}
                    var topHeight=windowHeight-bottomDiv.outerHeight();
                    jQuery(divAltGallery).find("div.nggml-alt-gallery-large").height(topHeight).css("display","block");
                    jQuery(divAltGallery).find("div.nggml-alt-gallery-titles").height(topHeight);
                    var divGalleryHeight=divGallery.height();
                    var divAltGalleryHeight=jQuery(divAltGallery).height();
                    if(divAltGalleryHeight<divGalleryHeight){jQuery(divAltGallery).height(divGalleryHeight);}
                    else if(divGalleryHeight<divAltGalleryHeight){jQuery(divGallery).height(divAltGalleryHeight);}
                    break;
                }
            });
        });
        jQuery("div#nggml-meta-overlay").click(function(e){
        });
        jQuery("button.nggml-meta-overlay-close-button").click(function(e){
            altGallery.metaLocked=false;
            jQuery("div.nggml-alt-gallery-meta").css("color","black");
            jQuery("div#nggml-meta-overlay").css("border-color","black");
            this.parentNode.style.display="none";
            e.stopImmediatePropagation();
            e.stopPropagation();
            e.preventDefault();
            return false;
        });
        jQuery("button.nggml-meta-overlay-fullsize-button").click(function(e){
            var img=jQuery("div#nggml-meta-overlay img.nggml-meta-overlay-img")[0];
            var w=img.naturalWidth;
            var h=img.naturalHeight;
            var W=screen.width;
            var H=screen.height;
            var wW=w/W;
            var hH=h/(H-128);
            var r=Math.max(wW,hH);
            if(r>1){
                w=Math.floor(w/r);
                h=Math.floor(h/r);
            }
            window.open(img.src,"nngml_fullsize","width="+w+",height="+h+",left="+(W-w)/2+",top="+(H-(h+128))/2);
            e.stopImmediatePropagation();
            e.stopPropagation();
            e.preventDefault();
            return false;
        });
    }
};
    
altGallery.gallery.prototype.image=function(src,href,text,dl,aLink){
    this.src=src;
    this.href=href;
    this.text=text;
    this.dl=dl;
    this.aLink=aLink;
    this.width=nggmlAltGalleryImageWidth;
};

altGallery.gallery.prototype.extract=function(gallery){
    var self=this;
    jQuery("dl.gallery-item",gallery).each(function(){
        var a=jQuery("dt.gallery-icon a",this).get(0);
        var img=jQuery("img",a).get(0);
        var dd=jQuery("dd.gallery-caption",this).get(0);
        var aLink=(a.hasOwnProperty("dataset")&&a.dataset.hasOwnProperty("attachmentLink"))
            ?a.dataset.attachmentLink:"";
        self.images.push(new self.image(img.src,a.href,dd?dd.textContent:img.alt,this,aLink));
    });
    jQuery("figure.gallery-item",gallery).each(function(){
        var a=jQuery("div.gallery-icon a",this).get(0);
        var img=jQuery("img",a).get(0);
        var dd=jQuery("figcaption.gallery-caption",this).get(0);
        var aLink=(a.hasOwnProperty("dataset")&&a.dataset.hasOwnProperty("attachmentLink"))
            ?a.dataset.attachmentLink:"";
        self.images.push(new self.image(img.src,a.href,dd?dd.textContent:img.alt,this,aLink));
    });
},

altGallery.gallery.prototype.getMeta=function(){
    var ids=[];
    for(var i=0;i<this.images.length;i++){
        var image=this.images[i];
        var match=image.href.match(/\?attachment_id=(\d+)/);
        if(match===null){match=image.aLink.match(/\?attachment_id=(\d+)/);}
        if(match!==null){ids.push(match[1]);}
    }
    ids=ids.join(",");
    jQuery.post(ajaxurl,{action:"nggml_get_attachment_meta",ids:ids},function(r){
        jQuery.extend(altGallery.meta,JSON.parse(r));
    });
},

altGallery.gallery.prototype.recreate=function(){
    var self=this;
    var divBottom=document.createElement("div");
    divBottom.className="nggml-alt-gallery-bottom";
    var divTitles=document.createElement("div");
    divTitles.className="nggml-alt-gallery-titles";
    var divIcons=document.createElement("div");
    divIcons.className="nggml-alt-gallery-icons";
    divIcons.style.overflowX="visible";
    var divLarge=document.createElement("div");
    divLarge.className="nggml-alt-gallery-large";
    divLarge.style.display="none";
    var largeImg=document.createElement("img");
    largeImg.className="nggml-large-image";
    divLarge.appendChild(largeImg);
    for(var i=0;i<this.images.length;i++){
        var image=this.images[i];
        var div=document.createElement("div");
        div.className="nggml-alt-gallery";
        div.style.width=(image.width)+"px";
        div.style.height=(image.width)+"px";
        div.style.display="inline-block";
        div.style.position="relative";
        div.style.float="left";
        var img=document.createElement("img");
        img.className="nggml-alt-gallery";
        img.id="nggml-img-"+i;
        img.src=image.src;
        img.width=image.width;
        img.height=image.width;
        img.style.width=image.width+"px";
        img.style.height=image.width+"px";
        img.style.borderWidth="0";
        div.appendChild(img);
        var id=image.href.match(/\?attachment_id=(\d+)/);
        if(id===null){id=image.aLink.match(/\?attachment_id=(\d+)/);}
        if(id!==null){
            var meta=document.createElement("div");
            meta.id="nggml-meta-"+id[1];
            meta.className="nggml-alt-gallery-meta nggml-alt-gallery-meta-image";
            meta.style.display="inline-block";
            meta.style.position="absolute";
            meta.style.top="0";
            meta.style.right="0";
            meta.style.zIndex=10000;
            div.appendChild(meta);
        }
        divIcons.appendChild(div);
        image.img=img;
        var li=document.createElement("li");
        li.className="nggml-alt-gallery";
        li.id="nggml-li-"+i;
        li.appendChild(document.createTextNode(image.text));
        divTitles.appendChild(li);
        image.li=li;
        var t0=null;
        jQuery(img)
            .click(function(e){
                if(jQuery(img.parentNode.parentNode.parentNode).hasClass("nggml-alt-gallery-container")){
                    jQuery(".gallery-icon a img",self.images[this.id.substr(10)].dl).click();
                }else if(jQuery(img.parentNode.parentNode.parentNode).hasClass("nggml-alt-gallery-bottom")){
                    if(e.timeStamp-t0<250){jQuery(".gallery-icon a img",self.images[this.id.substr(10)].dl).click();}
                }
                e.stopImmediatePropagation();
                e.stopPropagation();
                e.preventDefault();
                e.returnValue=false;
                return false;
            })
            .hover(
                function(e){
                    if(altGallery.metaLocked){return;}
                    var jqThis=jQuery(this);
                    var color=jQuery("button#nggml-alt-gallery-focused").css("background-color");
                    this.parentNode.style.borderColor=color;
                    var id=this.id.substr(10);
                    self.images[id].li.style.backgroundColor=color;
                    id-=3;
                    jQuery(divTitles).scrollTop(id>0?jQuery(self.images[id].li).position().top
                        +jQuery(divTitles).scrollTop():0);
                    var top=jqThis.parents("div.nggml-alt-gallery-container").offset().top;
                    var divAdminBar=jQuery("div#wpadminbar");
                    if(divAdminBar.length){top-=divAdminBar.outerHeight(true);}
                    jQuery("body").scrollTop(top);
                    if(jQuery("body").scrollTop()!==top){jQuery("html").scrollTop(top);}
                    if(jQuery(this.parentNode.parentNode.parentNode).hasClass("nggml-alt-gallery-bottom")){
                        var meta=altGallery.meta[jQuery(this.parentNode).find("div.nggml-alt-gallery-meta")[0].id.substr(11)];
                        jQuery(divLarge).find("img")[0].src=meta.guid;
                    }
                },
                function(e){
                    if(altGallery.metaLocked){return;}
                    var color=jQuery("button#nggml-alt-gallery-unfocused").css("background-color");
                    this.parentNode.style.borderColor=color;
                    self.images[this.id.substr(10)].li.style.backgroundColor=color;
                }
            );
        jQuery(meta)
            .click(function(e){
                if(!altGallery.metaLocked){
                    altGallery.metaLocked=true;
                    this.style.color="red";
                    jQuery("div#nggml-meta-overlay").css("border-color","red");
                }else if(this.style.color==="red"){
                    altGallery.metaLocked=false;
                    this.style.color="black";
                    jQuery("div#nggml-meta-overlay").css("border-color","black");
                }
                e.stopImmediatePropagation();
                e.stopPropagation();
                e.preventDefault();
                return false;
            })
            .hover(
                function(e){
                    if(altGallery.metaLocked){return;}
                    var meta=altGallery.meta[this.id.substr(11)];
                    var overlay=jQuery("div#nggml-meta-overlay");
                    meta.img_width=overlay.width()-10;
                    //var img=jQuery(this.parentNode).find("img")[0];
                    //meta.img_size=img.naturalWidth+" x "+img.naturalHeight;
                    meta.img_size=meta._wp_attachment_metadata.width+" x "+meta._wp_attachment_metadata.height;
                    var html=overlay.find("script#nggml-meta-template").text();
                    html=html.replace(/\{\{(\w+)\}\}/g,function(m0,m1){
                        return meta.hasOwnProperty(m1)?meta[m1]:"null";
                    });
                    var metaContent=overlay.find("div.nggml-meta-content");
                    metaContent.html(html);
                    if(jQuery(this.parentNode.parentNode.parentNode).hasClass("nggml-alt-gallery-container")){
                        var thisParent=jQuery(this.parentNode);
                        overlay.css("position","fixed");
                        thisParent.offsetParent().append(overlay);
                        var divIcons=thisParent.parents("div.nggml-alt-gallery-icons");
                        var thisLeft=thisParent.offset().left;
                        var left=thisParent.position().left>divIcons.width()/2
                            ?thisLeft-overlay.outerWidth():thisLeft+thisParent.outerWidth();
                        var top=thisParent.position().top;
                        var divAdminBar=jQuery("div#wpadminbar");
                        if(divAdminBar.length){top+=divAdminBar.outerHeight(true);}
                        var bottomOffset=jQuery(window).height()-(top+overlay.outerHeight());
                        if(bottomOffset<0){top+=bottomOffset;}
                        overlay.css({display:"block",left:left,top:top});
                    }else{
                        overlay.css("position","absolute");
                        var jqDivLarge=jQuery(divLarge).append(overlay);
                        overlay.css({display:"block",left:Math.floor((jqDivLarge.width()-overlay.outerWidth())/2),
                            top:Math.floor((jqDivLarge.height()-overlay.outerHeight())/2)});
                    }
                },
                function(e){
                    if(!altGallery.metaLocked){jQuery("div#nggml-meta-overlay").css("display","none");}
                }
            );
        jQuery(li)
            .click(function(e){
                jQuery("dt.gallery-icon a img",self.images[this.id.substr(9)].dl).click();
                e.stopImmediatePropagation();
                e.stopPropagation();
                e.preventDefault();
                e.returnValue=false;
                return false;
            })
            .hover(
                function(e){
                    if(altGallery.metaLocked){return;}
                    var color=jQuery("button#nggml-alt-gallery-focused").css("background-color");
                    this.style.backgroundColor=color;
                    var id=this.id.substr(9);
                    var img=self.images[id].img;
                    img.parentNode.style.borderColor=color;
                    if(jQuery(img.parentNode.parentNode.parentNode).hasClass("nggml-alt-gallery-container")){
                        id-=4;
                        jQuery(divIcons).scrollTop(id>0?jQuery(self.images[id].img.parentNode).position().top
                            +jQuery(divIcons).scrollTop():0);
                    }else if(jQuery(img.parentNode.parentNode.parentNode).hasClass("nggml-alt-gallery-bottom")){
                        var imgLeft=jQuery(img.parentNode).position().left;
                        id-=2;
                        if(id>0&&jQuery(self.images[id].img.parentNode).position().left<imgLeft){
                            jQuery(divBottom).scrollLeft(jQuery(self.images[id].img.parentNode).position().left);
                        }else{
                            jQuery(divBottom).scrollLeft(0);
                        }
                    }
                    var top=jQuery(this).parents("div.nggml-alt-gallery-container").offset().top;
                    var divAdminBar=jQuery("div#wpadminbar");
                    if(divAdminBar.length){top-=divAdminBar.outerHeight(true);}
                    jQuery("body").scrollTop(top);
                    if(jQuery("body").scrollTop()!==top){jQuery("html").scrollTop(top);}
                    if(jQuery(img.parentNode.parentNode.parentNode).hasClass("nggml-alt-gallery-bottom")){
                        var meta=altGallery.meta[jQuery(img.parentNode).find("div.nggml-alt-gallery-meta")[0].id.substr(11)];
                        jQuery(divLarge).find("img")[0].src=meta.guid;
                    }
                },
                function(e){
                    if(altGallery.metaLocked){return;}
                    var color=jQuery("button#nggml-alt-gallery-unfocused").css("background-color");
                    this.style.backgroundColor=color;
                    self.images[this.id.substr(9)].img.parentNode.style.borderColor=color;
                }
            );
    }    
    var x0=null;
    var scrollable=jQuery(divBottom)
        .mousedown(function(e){
            x0=e.clientX;
            t0=e.timeStamp;
            e.preventDefault();
        })
        .mousemove(function(e){
            if(x0!==null){
                scrollable.scrollLeft(scrollable.scrollLeft()-(e.clientX-x0));
                x0=e.clientX;
            }
            e.preventDefault();
        })
        .mouseup(function(e){
            x0=null;
            e.preventDefault();
        })
        .mouseleave(function(e){
            if(e.currentTarget!==scrollable[0]){
                return;
            }
            x0=null;
        });
    jQuery(largeImg).load(function(){
        var container=jQuery(largeImg.parentNode);
        var W=container.width()-10;
        var H=container.height()-10;
        var w=largeImg.naturalWidth;
        var h=largeImg.naturalHeight;
        var rw=1;
        var rh=1;
        if(w>W){rw=W/w;}
        if(h>H){rh=H/h;}
        if(rw<rh){r=rw;}else{r=rh;}
        w=Math.floor(r*w);
        h=Math.floor(r*h);
        largeImg.width=w;
        largeImg.height=h;
    });
    var divContainer=document.createElement("div");
    divContainer.className="nggml-alt-gallery-container";
    divContainer.style.overflow="visible";
    divContainer.style.boxSizing="content-box";
    divContainer.appendChild(divTitles);
    divContainer.appendChild(divIcons);
    divContainer.appendChild(divLarge);
    divContainer.appendChild(divBottom);
    jQuery(divContainer).find("*").css("boxSizing","content-box");
    return divContainer;
};

jQuery(document).ready(function(){altGallery.init();});
