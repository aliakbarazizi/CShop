<div class="title">تنظیمات</div>
<div class="content">
<form action="" method="post">
	<?php foreach ($item as $key=>$row):?>
	<div class="formrow wide">
		<div class="label"><label for="<?php echo $key?>"><?php echo $row['description']?></label></div>
		<div class="input"><input type="text" name="setting[<?php echo $key?>]" id="<?php echo $key?>" value="<?php echo $row['value']?>"></div>
	</div>
	<?php endforeach;?>
	<div class="formrow">
		<input type="submit" value="ذخیره" name="save">
	</div>
</form>
</div>