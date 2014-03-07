<script type="text/javascript" src="http://code.jquery.com/jquery-1.10.1.min.js"></script>
<!-- Make IE8 and below responsive by adding CSS3 MediaQuery support -->
<!--[if lt IE 9]>
  <script type="text/javascript" src="http://css3-mediaqueries-js.googlecode.com/svn/trunk/css3-mediaqueries.js"></script>
<![endif]-->
<!-- Fresco -->
<script type="text/javascript" src="http://www.frescojs.com/js/fresco/fresco.js"></script>
<link rel="stylesheet" type="text/css" href="http://www.frescojs.com/css/fresco/fresco.css"/>

<script type="text/javascript">
Fresco.show('{$image.0}');
</script>



{def $n = 0}
{foreach $image_list as $image}
    {set $n = $n|inc()}
    <a href="{concat( '/', $image )}" class="fresco" data-fresco-group="unique_name"><img src="{concat( '/', $image )}" width="150"/></a>
    {*<a href="{concat( '/', $image )}" class="fresco" data-fresco-group="unique_name">Image {$n}</a>*}
{/foreach}
