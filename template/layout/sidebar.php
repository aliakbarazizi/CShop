<div id="sidebar">
	<div id="slots" class="ui-droppable">
		<form action="" method="post">
			<div id="dropholder">
				<div class="title">سبد خرید</div><div class="drophere"></div>
				<div id="cardslots">
				
				</div>
			</div>
			<div id="total">جمع کل : <span>0</span> ریال</div>
			<div class="bottom">
			<?php foreach ($input as $key=>$value):$value['data'] = unserialize($value['data'])?>
				<label><?php echo $value['name']?></label>
				<?php echo Input::proccess("input[$key]",$value, htmlspecialchars($_POST['input'][$key]))?>
			<?php endforeach;?>
				<label>درگاه بانکی</label>
				<select name="gatewayid">
					<?php foreach ($gateway as $key=>$row):?>
					<option value="<?php echo $key?>"><?php echo $row['name']?></option>
					<?php endforeach;?>
				</select>
				<div class="submit">
					<input type="submit" value="پرداخت" name="submit" onclick="">
					<img alt="" src="<?php echo Cshop::$baseurl?>/static/images/loader.gif">
				</div>
			</div>
		</form>
	</div>
</div>