<form action="" method="post">
	<div class="formbuy">
		<?php foreach ($input as $key=>$value):$value['data'] = unserialize($value['data'])?>
			<label><?php echo $value['name']?></label>
			<?php echo Input::proccess("input[$key]",$value, htmlspecialchars($_POST['input'][$key]))?>
		<?php endforeach;?>
		<label>درگاه پرداخت</label>
		<select name="gatewayid">
			<?php foreach ($gateway as $key=>$row):?>
			<option value="<?php echo $key?>"><?php echo $row['name']?></option>
			<?php endforeach;?>
		</select>
		<input type="submit" value="پرداخت" name="submit" onclick="">
	</div>
	<div class="buybox">
		<ul class="select" id="category">
			<?php foreach ($category as $id=>$c):?>
				<li data-type="<?php echo $id?>"><?php echo $c['name']?></li>
			<?php endforeach;?>
		
		</ul>
		
		<div class="cont" id="cards">
			<?php foreach ($product as $id=>$p):?>
				<div style="display: none;" class="item" data-id="<?php echo $id?>" data-type="<?php echo $p['categoryid']?>">
					<span class="name"><?php echo $p['name']?></span><span class="f"><?php echo $p['price']?> ریال </span>
				</div>
			<?php endforeach;?>
	
		</div>
	</div>
	<div class="boxb">
		<div class="endtitle">
			 سبد خرید
		</div>
		<div class="endbuy">
			
		</div>
		<div id="total" style="padding-right:5px">جمع کل : <span>0</span> ریال</div>
	</div>
</form>
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