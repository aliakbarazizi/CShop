<ul id="category">
	<?php foreach ($category as $id=>$c):?>
	<li data-type="<?php echo $id?>"><?php echo $c['name']?></li>
	<?php endforeach;?>
</ul>
<div class="clear">
</div>
<div id="cards" class="cards">
	<?php foreach ($product as $id=>$p):?>
	<div class="item" data-id="<?php echo $id?>" data-type="<?php echo $p['categoryid']?>">
		<span><?php echo $p['name']?></span>
		<div class="price"><span><?php echo $p['price']?></span> ریال</div>
	</div>
	<?php endforeach;?>
</div>
<div class="clear">
</div>
<script type="text/javascript">
<?php if (isset($_POST['product'])):?>
$(function() {
	<?php foreach ($_POST['product'] as $id=>$v){
		echo "addItem($id,"; echo $v?$v:1; echo ");";}
	?>
}
);
<?php endif;?>
</script>