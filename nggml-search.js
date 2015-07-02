var nggmlAltGallery=function(){
  
var self1MetaLocked;

altGallery={
    gallery:function(){
        this.images=[];
        this.transitioning=false;
        this.meta={};
        this.metaLocked=false;
    },
    loading:"data:image/png;base64,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",
    transitionInterval:1.5,
    transition:"",
    preFlipLargeImage:function(divLarge,self){
        var imgs=jQuery(divLarge).find("img.nggml-large-image");
        var image=imgs[0].style.zIndex==200?imgs[0]:imgs[1];
        var other=imgs[0].style.zIndex==200?imgs[1]:imgs[0];
        if(nggmlTransition==="explode"||nggmlTransition==="rotation"||nggmlTransition==="reveal-left"){
            var image0=image;
            image=other;
            other=image0;
        }
        if(self.transitioning==="loading"){
            return other;
        }  
        var style=image.style;
        var otherStyle=other.style;
        self.imageTransition=style.transition;
        style.transition="";
        if(nggmlTransition==="slide-left"){
            style.zIndex=100;
            style.left="100%";
            otherStyle.zIndex=200;
        }else if(nggmlTransition==="fade"){
            style.zIndex=100;
            otherStyle.zIndex=200;
        }else if(nggmlTransition==="explode"||nggmlTransition==="rotation"){
            style.zIndex=200;
            style.opacity=1;
            style.height=style.width=0;
            if(nggmlTransition==="rotation"){style.transform="translate(-50%,-50%) rotate(90deg)";}
            otherStyle.zIndex=100;
        }else if(nggmlTransition==="reveal-left"){
            var parentParentStyle=image.parentNode.parentNode.style;
            self.containerTransition=parentParentStyle.transition;
            parentParentStyle.transition="";
            parentParentStyle.width="0%";
            parentParentStyle.opacity=1;
            parentParentStyle.zIndex=style.zIndex=200;
            image.parentNode.style.width=jQuery(divLarge).width()+"px";
            image.parentNode.style.height=jQuery(divLarge).height()+"px";
            otherStyle.zIndex=other.parentNode.parentNode.style.zIndex=100;
        }
        return image;
    },
    flipLargeImage:function(divLarge,image,self){
        var largeImgs=jQuery(divLarge).find("img.nggml-large-image");
        var other=image===largeImgs[0]?largeImgs[1]:largeImgs[0];
        var style=image.style;
        var otherStyle=other.style;
        style.transition=self.imageTransition;
        if(nggmlTransition==="slide-left"){
            style.left="50%";
            style.transform="translate(-50%,-50%)";
            otherStyle.left="-100%";
            otherStyle.transform="translate(0%,-50%)";
        }else if(nggmlTransition==="fade"){
            style.opacity=1;
            otherStyle.opacity=0;
        }else if(nggmlTransition==="explode"||nggmlTransition==="rotation"){
            style.width=jQuery(image).data("width")+"px";
            style.height=jQuery(image).data("height")+"px";
            style.opacity=1;
            otherStyle.opacity=0;
            if(nggmlTransition==="rotation"){style.transform="translate(-50%,-50%) rotate(360deg)";}
        }else if(nggmlTransition==="reveal-left"){
            var parentParentStyle=image.parentNode.parentNode.style;
            parentParentStyle.transition=self.containerTransition;
            parentParentStyle.width="100%";
            parentParentStyle.opacity=1;
            other.parentNode.parentNode.style.opacity=0;
        }
        self.transitioning="transitioning";
        if(typeof self.slideShowId!=="undefined"){
            window.setTimeout(function(){self.transitioning=false;},altGallery.transitionInterval*1000+100);
        }else{
            self.transitioning=false;
        }
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
        var windowWidth=jQuery(window).width()+"px";
        var windowHeight=jQuery(window).height()+"px";
        var fixed=document.createElement("div");
        var fixedStyle=fixed.style;
        fixedStyle.position="fixed";
        fixedStyle.left="0px";
        fixedStyle.top="0px";
        fixedStyle.width=windowWidth;
        fixedStyle.height=windowHeight;
        fixedStyle.zIndex=100000;
        fixedStyle.display="none";
        var background=document.createElement("div");
        var backgroundStyle=background.style;
        backgroundStyle.position="absolute";
        backgroundStyle.left="0px";
        backgroundStyle.top="0px";
        backgroundStyle.width=windowWidth;
        backgroundStyle.height=windowHeight;
        backgroundStyle.zIndex=1;
        backgroundStyle.backgroundColor="black";
        backgroundStyle.opacity=0.9;
        fixed.appendChild(background);
        var fixedButton=document.createElement("button");
        fixedButtonStyle=fixedButton.style;
        fixedButtonStyle.position="absolute";
        fixedButtonStyle.left="0px";
        fixedButtonStyle.top="0px";
        fixedButtonStyle.zIndex=11;
        fixedButtonStyle.margin="10px";
        fixedButton.textContent="X";
        fixed.appendChild(fixedButton);
        var self1Fixed;
        var containerFixed;
        var selectFixed;
        jQuery(fixedButton).click(function(){
            if(self1Fixed.metaLocked){
                jQuery(fixed).find("div.nggml-alt-gallery-large div.nggml-slide-controls button#nggml-info-button").click();
                jQuery("div#nggml-meta-overlay").css("display","none");
            }
            jQuery(fixed).hide();
            jQuery(containerFixed).prepend(jQuery(this.parentNode).find("div.nggml-alt-gallery-large")[0]);
            if(selectFixed.length>1){
                if(selectFixed[0].value!=="slideshow"){selectFixed.selectedIndex=0;}
                else{selectFixed.selectedIndex=1;}
                jQuery(selectFixed).change();
            }
        });
        jQuery("body")[0].appendChild(fixed);
        
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
            var view;
            var views;
            this.className.split(" ").forEach(function(v){
                if(v.indexOf("tml_view-")===0){
                    view=v.substr(9);
                }
                if(v.indexOf("tml_views-")===0){
                    views=v.substr(10).split("_");
                }
            });
            var select=document.createElement("select");
            select.className="nggml-gallery-select-view";
            [{v:"standard",l:"Standard View"},{v:"titles",l:"Titles View"},{v:"large",l:"Large Image View"},
                {v:"slideshow",l:"Slide Show"}].forEach(function(o){
                if(typeof views==='undefined'||views.indexOf(o.v)!==-1){
                    var option=document.createElement("option");
                    option.value=o.v;
                    option.textContent=o.l;
                    select.appendChild(option);
                }
            });
            if(typeof view!=='undefined'){
                for(var i=0;i<select.options.length;i++){
                    if(select.options[i].value===view){
                        select.selectedIndex=i;
                        //jQuery(select).change();
                    }
                }
            }
            divControls.appendChild(select);
            var buttonColor=document.createElement("button");
            buttonColor.id="nggml-alt-gallery-unfocused";
            divControls.appendChild(buttonColor);
            var buttonColor=document.createElement("button");
            buttonColor.id="nggml-alt-gallery-focused";
            buttonColor.style.backgroundColor=nggmlAltGalleryFocusColor;
            divControls.appendChild(buttonColor);
            divGallery.before(divControls);
            self1.select=select;
            self1.getMeta();
            var divAltGallery=self1.divAltGallery=self1.recreate();
            for(var parent=divGallery[0];parent.tagName!=="HTML";parent=parent.parentNode){
                if(parent.style.backgroundColor&&parent.style.backgroundColor!=="transparent"){
                    divAltGallery.style.backgroundColor=parent.style.backgroundColor;
                }
            }
            divAltGallery.style.zIndex=1000;
            divAltGallery.style.display="none";
            divGallery.append(divAltGallery);
            jQuery(select).change(function(){
                if(select.length===1){select.parentNode.style.display="none";}
                if(this.options[this.selectedIndex].value!=="slideshow"){
                    self1.slideShow=false;
                    self1.imageTransition=window.undefined;
                    if(typeof self1.slideShowId!=="undefined"){
                        window.clearInterval(self1.slideShowId);
                        self1.slideShowId=window.undefined;
                    }
                }else if(typeof self1.slideShowId!=="undefined"){
                    return;
                }
                switch(this.options[this.selectedIndex].value){
                case "standard":
                    divAltGallery.style.display="none";
                    break;
                case "titles":
                    jQuery(divAltGallery).find("div.nggml-alt-gallery-large").css("display","none");
                    jQuery(divAltGallery).find("div.nggml-alt-gallery-bottom").css("display","none");
                    var iconsDiv=jQuery(divAltGallery).find("div.nggml-alt-gallery-icons");
                    jQuery(divAltGallery).prepend(iconsDiv);
                    var windowHeight=jQuery(window).height();
                    var divAdminBar=jQuery("div#wpadminbar");
                    if(divAdminBar.length){windowHeight-=divAdminBar.outerHeight(true);}
                    jQuery("div.nggml-alt-gallery-titles",divAltGallery).height(windowHeight).css("display","block");
                    iconsDiv.height(windowHeight).css({width:"74%",display:"block"});   // TODO: better W - w
                    divAltGallery.style.display="block";
                    var divGalleryHeight=divGallery.height();
                    var divAltGalleryHeight=jQuery(divAltGallery).height();
                    if(divAltGalleryHeight<divGalleryHeight){jQuery(divAltGallery).height(divGalleryHeight);}
                    else if(divGalleryHeight<divAltGalleryHeight){jQuery(divGallery).height(divAltGalleryHeight);}
                    break;
                case "large":
                    divAltGallery.style.display="block";
                    var iconsDiv=jQuery(divAltGallery).find("div.nggml-alt-gallery-icons");
                    var largeDiv=jQuery(divAltGallery).find("div.nggml-alt-gallery-large");
                    var titlesDiv=jQuery(divAltGallery).find("div.nggml-alt-gallery-titles");
                    var bottomDiv=jQuery(divAltGallery).find("div.nggml-alt-gallery-bottom");
                    var imgDiv=iconsDiv.find("div.nggml-alt-gallery");
                    var width=Math.ceil(imgDiv.length/2)*imgDiv.outerWidth()+24;
                    var bottomWidth=bottomDiv.css("display","block").width();
                    if(width<bottomWidth){width=bottomWidth;}
                    iconsDiv.height(2*imgDiv.outerHeight()).width(width).css("display","block");
                    bottomDiv.append(iconsDiv);
                    var windowHeight=jQuery(window).height();
                    var divAdminBar=jQuery("div#wpadminbar");
                    if(divAdminBar.length){windowHeight-=divAdminBar.outerHeight(true);}
                    var topHeight=windowHeight-bottomDiv.outerHeight();
                    largeDiv.height(topHeight).css({width:"74%",display:"block"});
                    titlesDiv.height(topHeight).css("display","block");
                    var divGalleryHeight=divGallery.height();
                    var divAltGalleryHeight=jQuery(divAltGallery).height();
                    if(divAltGalleryHeight<divGalleryHeight){jQuery(divAltGallery).height(divGalleryHeight);}
                    else if(divGalleryHeight<divAltGalleryHeight){jQuery(divGallery).height(divAltGalleryHeight);}
                    var largeImg=jQuery(largeDiv).find("img.nggml-large-image").css("transition","");
                    if(nggmlTransition==="reveal-left"){largeImg.parent().parent().css("transition","");}
                    break;
                case "slideshow":
                    divAltGallery.style.display="block";
                    var divLarge=jQuery(divAltGallery).find("div.nggml-alt-gallery-large");
                    if(!nggmlUseFullScreenSlideShow){
                        jQuery(divAltGallery).find("div.nggml-alt-gallery-icons").css("display","none");
                        jQuery(divAltGallery).find("div.nggml-alt-gallery-titles").css("display","none");
                        jQuery(divAltGallery).find("div.nggml-alt-gallery-bottom").css("display","none");
                        var windowHeight=jQuery(window).height();
                        var divAdminBar=jQuery("div#wpadminbar");
                        if(divAdminBar.length){windowHeight-=divAdminBar.outerHeight(true);}
                        divLarge.height(windowHeight).css({width:"100%",display:"block"});
                        var divGalleryHeight=divGallery.height();
                        var divAltGalleryHeight=jQuery(divAltGallery).height();
                        if(divAltGalleryHeight<divGalleryHeight){jQuery(divAltGallery).height(divGalleryHeight);}
                        else if(divGalleryHeight<divAltGalleryHeight){jQuery(divGallery).height(divAltGalleryHeight);}
                    }else{
                        self1Fixed=self1;
                        containerFixed=divAltGallery;
                        selectFixed=this;
                        divLarge.css({width:"100%",height:"100%",display:"block"});
                        fixed.appendChild(divLarge[0]);
                        jQuery(fixed).show();
                        fixedButtonStyle.display=this.length===1?"none":"inline";
                    }
                    var imgDiv=jQuery(divAltGallery).find("div.nggml-alt-gallery-icons div.nggml-alt-gallery");
                    var imgs=divLarge.find("img.nggml-large-image").css("transition",altGallery.transition);
                    if(nggmlTransition==="reveal-left"){imgs.parent().parent().css("transition",altGallery.revealTransition);}
                    var playButton=divLarge.find("div.nggml-slide-controls button#nggml-play-stop-button")[0];
                    self1.slideShow=true;
                    self1.slideShowIndex=0;
                    self1.imagesCount=imgDiv.length;
                    function showSlideShow(){
                        if(jQuery(divAltGallery).is(":hidden")){
                            if(typeof self1.slideShowId!=="undefined"){window.clearInterval(self1.slideShowId);}
                            playButton.textContent="Play";
                            return;
                        }
                        var image=altGallery.preFlipLargeImage(divLarge,self1);
                        try{
                            var metaId=imgDiv.slice(self1.slideShowIndex,self1.slideShowIndex+1)
                                .find("div.nggml-alt-gallery-meta")[0].id.substr(11);
                            var meta=self1.meta[metaId];
                            if(image.src!==meta.guid){
                                image.src=meta.guid;
                                self.transitioning="loading";
                            }else{
                                window.setTimeout(function(){altGallery.flipLargeImage(divLarge,image,self1);},100);
                            }
                            divLarge.find("div.nggml-slide-title")[0].textContent=meta.post_title;
                            divLarge.find("div.nggml-slide-controls button#nggml-info-button").data("meta-id",metaId);
                            self1.slideShowIndex=(self1.slideShowIndex+1)%imgDiv.length;
                        }catch(e){
                            image.src=altGallery.loading;
                            self.transitioning="loading";
                        }
                        if(!nggmlUseFullScreenSlideShow){
                            var top=divLarge.parents("div.nggml-alt-gallery-container").offset().top;
                            var divAdminBar=jQuery("div#wpadminbar");
                            if(divAdminBar.length){top-=divAdminBar.outerHeight(true);}
                            jQuery("body").scrollTop(top);
                            if(jQuery("body").scrollTop()!==top){jQuery("html").scrollTop(top);}
                        }
                    }
                    self1.showSlideShow=showSlideShow;
                    divLarge.find("div.nggml-slide-controls button#nggml-play-stop-button")[0].textContent="Stop";
                    divLarge.find("div.nggml-span").remove();
                    showSlideShow();
                    if(!jQuery(divAltGallery).is(":hidden")){
                        if(typeof self1.slideShowId!=="undefined"){window.clearInterval(self1.slideShowId);}
                        self1.slideShowId=window.setInterval(showSlideShow,nggmlSlideShowInterval);
                        playButton.textContent="Stop";
                    }else{
                        playButton.textContent="Play";
                    }
                    break;
                }
            });
            self1.slideShow=false;
        });   // divGallery.each(function(){

        jQuery("div#nggml-meta-overlay").click(function(e){
            e.stopPropagation();
        });
        jQuery("button.nggml-meta-overlay-close-button").click(function(e){
            var s=this.parentNode.style;
            s.borderColor="black";
            s.display="none";
            if(self1MetaLocked.slideShow){
                var button=jQuery(self1MetaLocked.divAltGallery)
                    .find("div.nggml-alt-gallery-large div.nggml-slide-controls button#nggml-info-button");
                if(!button.length){
                    button=jQuery(fixed).find("div.nggml-alt-gallery-large div.nggml-slide-controls button#nggml-info-button");
                }
                button.click();
            }else{
                jQuery(self1MetaLocked.divAltGallery).find("div.nggml-alt-gallery-meta").css("color","black");
            }  
            self1MetaLocked.metaLocked=false;
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
    }   // init:function(){
};  // altGallery={

switch(nggmlTransition){
case "slide-left":
    altGallery.transitionInterval=0.5;
    break;
case "fade":
    altGallery.transitionInterval=2.0;
    break;
case "explode":
case "rotation":
    altGallery.transitionInterval=0.5;
    break;
case "reveal-left":
    altGallery.transitionInterval=1.0;
    break;
default:
    altGallery.transitionInterval=1.0;
    break;
}

altGallery.transition="opacity "+altGallery.transitionInterval+"s,left "+altGallery.transitionInterval+"s linear,top "
    +altGallery.transitionInterval+"s linear,transform "+altGallery.transitionInterval+"s linear";
altGallery.revealTransition="width "+altGallery.transitionInterval+"s linear,opacity "+altGallery.transitionInterval
    +"s step-end";
    
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
        var aLink=typeof a.dataset.attachmentLink!=="undefined"?a.dataset.attachmentLink:"";
        self.images.push(new self.image(img.src,a.href,dd?dd.textContent:img.alt,this,aLink));
    });
    jQuery("figure.gallery-item",gallery).each(function(){
        var a=jQuery("div.gallery-icon a",this).get(0);
        var img=jQuery("img",a).get(0);
        var dd=jQuery("figcaption.gallery-caption",this).get(0);
        var aLink=typeof a.dataset.attachmentLink!=="undefined"?a.dataset.attachmentLink:"";
        self.images.push(new self.image(img.src,a.href,dd?dd.textContent:img.alt,this,aLink));
    });
};

altGallery.gallery.prototype.getMeta=function(){
    var self=this;
    var ids=[];
    for(var i=0;i<this.images.length;i++){
        var image=this.images[i];
        var match=image.href.match(/\?attachment_id=(\d+)/);
        if(match===null){match=image.aLink.match(/\?attachment_id=(\d+)/);}
        if(match!==null){ids.push(match[1]);}
    }
    ids=ids.join(",");
    var select=this.select;
    jQuery.post(ajaxurl,{action:"nggml_get_attachment_meta",ids:ids},function(r){
        jQuery.extend(self.meta,JSON.parse(r));
        jQuery(select).change();
    });
};

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
    var spanLarge=document.createElement("div");
    spanLarge.className="nggml-span";
    spanLarge.textContent="Please mouse over an image or a title to select large image to display.";
    divLarge.appendChild(spanLarge);
    divLarge.style.zIndex=10;
    divLarge.style.display="none";
    for(var i=0;i<2;i++){
        var largeImg=document.createElement("img");
        largeImg.id="nggml-large-image-"+i;
        largeImg.className="nggml-large-image";
        var style=largeImg.style;
        style.position="absolute";
        style.left="50%";
        style.top="50%";
        style.transform="translate(-50%,-50%)";
        style.zIndex=100+i*100;
        style.borderWidth="0";
        if(nggmlTransition==="slide-left"){
            if(i){
                style.left="100%";
                style.transform="translate(0%,-50%)";
            }
        }else if(nggmlTransition==="fade"){
            style.opacity=1-i;
        }else if(nggmlTransition==="explode"||nggmlTransition==="rotation"){
            altGallery.transition+=",width "+altGallery.transitionInterval+"s linear,height "
              +altGallery.transitionInterval+"s linear";
        }else if(nggmlTransition==="reveal-left"){
            jQuery(largeImg).wrap("<div style='position:absolute;right:0px;height:100%;overflow:hidden;'>"
                +"<div style='position:absolute;right:0px;background-color:white;'></div></div>");
            var parentParent=largeImg.parentNode.parentNode;
            var parentParentStyle=parentParent.style;  
            parentParentStyle.zIndex=style.zIndex;
            parentParentStyle.transition=altGallery.revealTransition;
            divLarge.appendChild(parentParent);
            continue;
        }
        divLarge.appendChild(largeImg);
    }
    var divSlideTitle=document.createElement("div");
    divSlideTitle.className="nggml-slide-title";
    divLarge.appendChild(divSlideTitle);
    var divSlideControls=document.createElement("div");
    divSlideControls.className="nggml-slide-controls";
    var button=document.createElement("button");
    button.className="nggml-slide-button";
    button.textContent="<<";
    divSlideControls.appendChild(button);
    divLarge.appendChild(divSlideControls);
    var button=document.createElement("button");
    button.className="nggml-slide-button";
    button.textContent="<";
    divSlideControls.appendChild(button);
    divLarge.appendChild(divSlideControls);
    var button=document.createElement("button");
    button.id="nggml-play-stop-button";
    button.className="nggml-slide-button";
    button.textContent="Stop";
    divSlideControls.appendChild(button);
    divLarge.appendChild(divSlideControls);
    var button=document.createElement("button");
    button.id="nggml-info-button";
    button.className="nggml-slide-button";
    button.textContent="";
    divSlideControls.appendChild(button);
    divLarge.appendChild(divSlideControls);
    var button=document.createElement("button");
    button.className="nggml-slide-button";
    button.textContent=">";
    divSlideControls.appendChild(button);
    divLarge.appendChild(divSlideControls);
    var button=document.createElement("button");
    button.className="nggml-slide-button";
    button.textContent=">>";
    divSlideControls.appendChild(button);
    divLarge.appendChild(divSlideControls);
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
                    if(self.metaLocked){return;}
                    var hasLarge=jQuery(this.parentNode.parentNode.parentNode).hasClass("nggml-alt-gallery-bottom");
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
                    if(hasLarge){
                        var image=altGallery.preFlipLargeImage(divLarge,self);
                        try{
                            var guid=self.meta[jQuery(this.parentNode)
                                .find("div.nggml-alt-gallery-meta")[0].id.substr(11)].guid;
                            if(image.src!==guid){
                                image.src=guid;
                                self.transitioning="loading";
                            }else{
                                altGallery.flipLargeImage(divLarge,image,self);
                            }
                        }catch(e){
                            image.src=altGallery.loading;
                            self.transitioning="loading";
                        }
                        spanLarge.textContent="";
                    }
                },
                function(e){
                    if(self.metaLocked){return;}
                    var color=jQuery("button#nggml-alt-gallery-unfocused").css("background-color");
                    this.parentNode.style.borderColor=color;
                    self.images[this.id.substr(10)].li.style.backgroundColor=color;
                }
            );
        jQuery(meta)
            .click(function(e){
                if(!self.metaLocked){
                    self.metaLocked=true;
                    self1MetaLocked=self;
                    this.style.color="red";
                    jQuery("div#nggml-meta-overlay").css("border-color","red");
                }else if(this.style.color==="red"){
                    self.metaLocked=false;
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
                    if(self.metaLocked){return;}
                    try{
                        var meta=self.meta[this.id.substr(11)];
                    }catch(e){
                        return;
                    }
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
                    if(!self.metaLocked){jQuery("div#nggml-meta-overlay").css("display","none");}
                }
            );
        jQuery(li)
            .click(function(e){
                if(e.timeStamp-t0<250){jQuery(".gallery-icon a img",self.images[this.id.substr(9)].dl).click();}
                e.stopImmediatePropagation();
                e.stopPropagation();
                e.preventDefault();
                e.returnValue=false;
                return false;
            })
            .hover(
                function(e){
                    if(self.metaLocked){return;}
                    var id=this.id.substr(9);
                    var img=self.images[id].img;
                    var hasLarge=jQuery(img.parentNode.parentNode.parentNode).hasClass("nggml-alt-gallery-bottom");
                    var color=jQuery("button#nggml-alt-gallery-focused").css("background-color");
                    this.style.backgroundColor=color;
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
                    if(hasLarge){
                        var image=altGallery.preFlipLargeImage(divLarge,self);
                        try{
                            var guid=self.meta[jQuery(img.parentNode)
                                .find("div.nggml-alt-gallery-meta")[0].id.substr(11)].guid;
                            if(image.src!==guid){
                                image.src=guid;
                                self.transitioning="loading";
                            }else{
                                altGallery.flipLargeImage(divLarge,image,self);
                            }
                        }catch(e){
                            image.src=altGallery.loading;
                            self.transitioning="loading";
                        }
                        spanLarge.textContent="";
                    }
                },
                function(e){
                    if(self.metaLocked){return;}
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
        
    var titlesY0=null;
    var titlesScrollable=jQuery(divTitles)
        .mousedown(function(e){
            titlesY0=e.clientY;
            t0=e.timeStamp;
            e.preventDefault();
        })
        .mousemove(function(e){
            if(titlesY0!==null){
                titlesScrollable.scrollTop(titlesScrollable.scrollTop()-(e.clientY-titlesY0));
                titlesY0=e.clientY;
            }
            e.preventDefault();
        })
        .mouseup(function(e){
            titlesY0=null;
            e.preventDefault();
        })
        .mouseleave(function(e){
            if(e.currentTarget!==titlesScrollable[0]){
                return;
            }
            titlesY0=null;
        });
        
    var largeImgs=jQuery(divLarge).find("img.nggml-large-image");
    largeImgs.load(function(){
        var container=jQuery(this.parentNode);
        var W=container.width()-10;
        var H=container.height()-10;
        var w=this.naturalWidth;
        var h=this.naturalHeight;
        var width;
        var height;
        if(!nggmlStretchToFit){
            var rw=1;
            var rh=1;
            if(w>W){rw=W/w;}
            if(h>H){rh=H/h;}
            if(rw<rh){r=rw;}else{r=rh;}
            this.width=width=Math.floor(r*w);
            this.height=height=Math.floor(r*h);
        }else{
            if(nggmlPreserveAspectRatio){
              var rw=W/w;
              var rh=H/h;
              var r=rw<=rh?rw:rh;
              this.width=width=Math.floor(r*w);
              this.height=height=Math.floor(r*h);
            }else{
              this.width=width=W;
              this.height=height=H;
            }
        }
        jQuery(this).data({width:width,height:height});
        altGallery.flipLargeImage(divLarge,this,self);
    });
    jQuery(divLarge).hover(
        function(e){
            if(self.slideShow){
                jQuery(this).find("div.nggml-slide-title").show();
                jQuery(this).find("div.nggml-slide-controls").show();
            }
        },
        function(e){
            jQuery(this).find("div.nggml-slide-title").hide();
            jQuery(this).find("div.nggml-slide-controls").hide();
        }        
    ).click(function(e){
        e.stopPropagation();
    });
    jQuery(divLarge).find("div.nggml-slide-controls button.nggml-slide-button").click(function(e){
        var button=this;
        function showSlideAt(i){
            if(typeof self.slideShowId!=="undefined"){
                window.clearInterval(self.slideShowId);
                self.slideShowId=window.undefined;
            }
            var largeImg=jQuery(divLarge).find("img.nggml-large-image").css("transition","");
            if(nggmlTransition==="reveal-left"){largeImg.parent().parent().css("transition","");}
            self.slideShowIndex=i;
            self.showSlideShow();
            jQuery(button.parentNode).find("button#nggml-play-stop-button")[0].textContent="Play";
        }
        if(this.textContent==="Stop"){
            window.clearInterval(self.slideShowId);
            self.slideShowId=window.undefined;
            var largeImg=jQuery(divLarge).find("img.nggml-large-image").css("transition","");
            if(nggmlTransition==="reveal-left"){largeImg.parent().parent().css("transition","");}
            this.textContent="Play";
        }else if(this.textContent==="Play"){
            var largeImg=jQuery(divLarge).find("img.nggml-large-image").css("transition",altGallery.transition);
            if(nggmlTransition==="reveal-left"){largeImg.parent().parent().css("transition",altGallery.revealTransition);}
            self.showSlideShow();
            self.slideShowId=window.setInterval(self.showSlideShow,nggmlSlideShowInterval);
            this.textContent="Stop";
        }else if(this.textContent===">"){
            showSlideAt(self.slideShowIndex);
        }else if(this.textContent==="<"){
            showSlideAt((self.imagesCount+self.slideShowIndex-2)%self.imagesCount);
        }else if(this.textContent==="<<"){
            showSlideAt(0);
        }else if(this.textContent===">>"){
            showSlideAt(self.imagesCount-1);
        }
        e.stopPropagation();
    });
    jQuery(divLarge).find("div.nggml-slide-controls button#nggml-info-button").hover(
        function(e){
            if(self.metaLocked){return;}
            try{
                var meta=self.meta[jQuery(this).data("meta-id")];
                if(typeof meta==="undefined"){return;}
            }catch(e){
                return;
            }
            if(typeof self.slideShowId!=="undefined"){
                window.clearInterval(self.slideShowId);
                self.slideShowId=window.undefined;
            }
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
            overlay.css("position","absolute");
            var jqDivLarge=jQuery(divLarge).append(overlay);
            overlay.css({display:"block",left:Math.floor((jqDivLarge.width()-overlay.outerWidth())/2),
                top:Math.floor((jqDivLarge.height()-overlay.outerHeight())/2)});
        },
        function(e){
            if(self.metaLocked){return;}
            jQuery("div#nggml-meta-overlay").css("display","none");
            if(jQuery("button#nggml-play-stop-button")[0].textContent==="Stop"){
                //self.showSlideShow();
                self.slideShowId=window.setInterval(self.showSlideShow,nggmlSlideShowInterval);
            }
        }
    ).click(function(e){
        if(!self.metaLocked){
            self.metaLocked=true;
            self1MetaLocked=self;
            this.style.color="red";
            jQuery("div#nggml-meta-overlay").css("border-color","red");
            jQuery(divLarge).find("div.nggml-slide-controls button.nggml-slide-button").prop("disabled",true);
            jQuery(this).prop("disabled",false);
            var playButton=jQuery(divLarge).find("div.nggml-slide-controls button#nggml-play-stop-button");
            if(playButton[0].textContent==="Stop"){
                if(typeof self.slideShowId!=="undefined"){
                    window.clearInterval(self.slideShowId);
                    self.slideShowId=window.undefined;
                }
                jQuery(divLarge).find("img.nggml-large-image").css("transition","");
                playButton[0].textContent="Play";
            }
        }else{
            self.metaLocked=false;
            this.style.color="black";
            jQuery("div#nggml-meta-overlay").css("border-color","black");
            jQuery(divLarge).find("div.nggml-slide-controls button.nggml-slide-button").prop("disabled",false);
        }
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
};  // altGallery.gallery.prototype.recreate=function(){

return altGallery;
}();// var nggmlAltGallery=function(){

jQuery(document).ready(function(){nggmlAltGallery.init();});
