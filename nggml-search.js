
altGallery={
    gallery:function(){
        this.images=[];
    },
    init:function(){
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
            var divControls=document.createElement("div");
            divControls.className="nggml-alt-gallery-controls";
            var button=document.createElement("button");
            button.id="nggml-alt-gallery-show";
            button.appendChild(document.createTextNode("Show High Density Gallery View"));
            divControls.appendChild(button);
            var buttonColor=document.createElement("button");
            buttonColor.id="nggml-alt-gallery-unfocused";
            divControls.appendChild(buttonColor);
            var buttonColor=document.createElement("button");
            buttonColor.id="nggml-alt-gallery-focused";
            divControls.appendChild(buttonColor);
            divGallery.before(divControls);
            self1.extract(divGallery);
            var divAltGallery=self1.recreate();
            divAltGallery.style.zIndex=1000;
            divAltGallery.style.display="none";
            divGallery.append(divAltGallery);
            jQuery(button).click(function(){
                if(this.textContent==="Show High Density Gallery View"){
                    var windowHeight=jQuery(window).height();
                    var divAdminBar=jQuery("div#wpadminbar");
                    if(divAdminBar.length){windowHeight-=divAdminBar.outerHeight(true);}
                    jQuery("div.nggml-alt-gallery-titles",divAltGallery).height(windowHeight);
                    jQuery("div.nggml-alt-gallery-icons",divAltGallery).height(windowHeight);
                    var divGalleryHeight=divGallery.height();
                    var divAltGalleryHeight=jQuery(divAltGallery).height();
                    if(divAltGalleryHeight<divGalleryHeight){jQuery(divAltGallery).height(divGalleryHeight);}
                    divAltGallery.style.display="block";
                    this.textContent="Show Standard WordPress Gallery View";
                }else{
                    divAltGallery.style.display="none";
                    this.textContent="Show High Density Gallery View";
                }
            });
        });
    }
};
    
altGallery.gallery.prototype.image=function(src,href,text,dl){
    this.src=src;
    this.href=href;
    this.text=text;
    this.dl=dl;
    this.width=nggmlAltGalleryImageWidth;
};

altGallery.gallery.prototype.extract=function(gallery){
    var self=this;
    jQuery("dl.gallery-item",gallery).each(function(){
        var a=jQuery("dt.gallery-icon a",this).get(0);
        var img=jQuery("img",a).get(0);
        var dd=jQuery("dd.gallery-caption",this).get(0);
        self.images.push(new self.image(img.src,a.href,dd?dd.textContent:img.alt,this));
    });
    jQuery("figure.gallery-item",gallery).each(function(){
        var a=jQuery("div.gallery-icon a",this).get(0);
        var img=jQuery("img",a).get(0);
        var dd=jQuery("figcaption.gallery-caption",this).get(0);
        self.images.push(new self.image(img.src,a.href,dd?dd.textContent:img.alt,this));
    });
},

altGallery.gallery.prototype.recreate=function(){
    var self=this;
    var divTitles=document.createElement("div");
    divTitles.className="nggml-alt-gallery-titles";
    var divIcons=document.createElement("div");
    divIcons.className="nggml-alt-gallery-icons";
    for(var i=0;i<this.images.length;i++){
        var image=this.images[i];
        var img=document.createElement("img");
        img.className="nggml-alt-gallery";
        img.id="nggml-img-"+i;
        img.src=image.src;
        img.width=image.width;
        img.height=image.width;
        img.style.width=image.width+"px";
        img.style.height=image.width+"px";
        divIcons.appendChild(img);
        image.img=img;
        var li=document.createElement("li");
        li.className="nggml-alt-gallery";
        li.id="nggml-li-"+i;
        li.appendChild(document.createTextNode(image.text));
        divTitles.appendChild(li);
        image.li=li;
        jQuery(img)
            .click(function(e){
                jQuery("dt.gallery-icon a img",self.images[this.id.substr(10)].dl).click();
                e.stopImmediatePropagation();
                e.stopPropagation();
                e.preventDefault();
                e.returnValue=false;
                return false;
            })
            .hover(
                function(e){
                    var color=jQuery("button#nggml-alt-gallery-focused").css("background-color");
                    this.style.borderColor=color;
                    var id=this.id.substr(10);
                    self.images[id].li.style.backgroundColor=color;
                    id-=4;
                    jQuery(divTitles).scrollTop(id>0?jQuery(self.images[id].li).position().top
                        +jQuery(divTitles).scrollTop():0);
                    var top=jQuery(this).parents("div.nggml-alt-gallery-container").offset().top;
                    var divAdminBar=jQuery("div#wpadminbar");
                    if(divAdminBar.length){top-=divAdminBar.outerHeight(true);}
                    jQuery("body").scrollTop(top);
                    if(jQuery("body").scrollTop()!==top){jQuery("html").scrollTop(top);}
                },
                function(e){
                    var color=jQuery("button#nggml-alt-gallery-unfocused").css("background-color");
                    this.style.borderColor=color;
                    self.images[this.id.substr(10)].li.style.backgroundColor=color;
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
                    var color=jQuery("button#nggml-alt-gallery-focused").css("background-color");
                    this.style.backgroundColor=color;
                    var id=this.id.substr(9);
                    self.images[id].img.style.borderColor=color;
                    id-=4;
                    jQuery(divIcons).scrollTop(id>0?jQuery(self.images[id].img).position().top
                        +jQuery(divIcons).scrollTop():0);
                    var top=jQuery(this).parents("div.nggml-alt-gallery-container").offset().top;
                    var divAdminBar=jQuery("div#wpadminbar");
                    if(divAdminBar.length){top-=divAdminBar.outerHeight(true);}
                    jQuery("body").scrollTop(top);
                    if(jQuery("body").scrollTop()!==top){jQuery("html").scrollTop(top);}
                },
                function(e){
                    var color=jQuery("button#nggml-alt-gallery-unfocused").css("background-color");
                    this.style.backgroundColor=color;
                    self.images[this.id.substr(9)].img.style.borderColor=color;
                }
            );
    }
    var divContainer=document.createElement("div");
    divContainer.className="nggml-alt-gallery-container";
    divContainer.appendChild(divTitles);
    divContainer.appendChild(divIcons);
    return divContainer;
};

jQuery(document).ready(function(){altGallery.init();});
