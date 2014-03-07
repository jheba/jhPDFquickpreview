<script type="text/javascript" src="http://coffeescripter.com/code/ad-gallery/jquery.ad-gallery.js">
var galleries = $('.ad-gallery').adGallery();
</script>
<link rel="stylesheet" type="text/css" href="http://www.frescojs.com/css/fresco/fresco.css"/>

<div class="ad-gallery">
  <div class="ad-image-wrapper">
  </div>
  <div class="ad-controls">
  </div>
  <div class="ad-nav">
    <div class="ad-thumbs">
      <ul class="ad-thumb-list">

        {def $n = 0}
        {foreach $image_list as $image}
            {set $n = $n|inc()}
            <li>
              <a href="{concat( '/', $image )}">
                <img src="{concat( '/', $image )}" title="Image {$n}" width="50">
              </a>
            </li>
        {/foreach}
      </ul>
    </div>
  </div>
</div>






