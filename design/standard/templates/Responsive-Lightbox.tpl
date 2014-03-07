

{def $n = 0}
<div class="gallery">
{foreach $image_list as $image}
    {set $n = $n|inc()}
    <a href="{concat( '/', $image )}" data-caption="Use arrows to navigate between pages">
    {if $n|eq(1)} 
        <img src="{concat( '/', $image )}" width="150"/>
    {/if}
    </a>
{/foreach}
</div>

<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
<script src={'javascript/jquery.lightbox.js'|ezdesign()}></script>
<script>
{literal}
 $(function()
 {
    $('.gallery a').lightbox(); 
 });
{/literal}
</script>
