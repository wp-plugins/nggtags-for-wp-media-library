<?php
?>
<div id="nggml-meta-overlay" style="position:absolute;z-index:100000;display:none;">
    Click the <div class="nggml-alt-gallery-meta" style="display:inline-block;"></div> icon to lock.
    <button class="nggml-meta-overlay-close-button" style="float:right;">X</button>
    <div class="nggml-meta-content">
Lorem ipsum dolor sit amet, utinam nusquam alienum cum ei, no maluisset constituam nec. Eam omnium conclusionemque ea, alia partem consequuntur per ut. Ea has viris mandamus patrioque, vim vidit dolore accommodare ne. Purto doctus constituam qui eu, scripta qualisque has ei, id mea solum verear invidunt. Nec vidit bonorum ea, te minimum fierent sadipscing vix.    
    </div>
    <button class="nggml-meta-overlay-fullsize-button" style="float:right;">Show Full Size</button>
    <script type="text/html" id="nggml-meta-template">
Title: {{post_title}}<br>
Alt: {{_wp_attachment_image_alt}}<br>
Caption: {{post_excerpt}}<br>
Description: {{post_content}}<br>
Size: {{img_size}}<br>
Mime Type: {{post_mime_type}}<br>
File: {{_wp_attached_file}}<br> 
Author: {{post_author}}<br>
Image: <a href="{{guid}}" target="blank">{{guid}}</a><br>
<img class="nggml-meta-overlay-img" src="{{guid}}" width="{{img_width}}">
    </script>
</div>
<?php
?>