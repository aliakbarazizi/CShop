<div class="title">مدیریت دسته ها</div>
<div class="content">
<form action="" method="post">
	<div class="formrow">
		<div class="label"><label for="name">نام</label></div>
		<div class="input"><input type="text" name="category[name]" id="name" value="<?php echo $item['name']?>"></div>
	</div>
	<div class="formrow">
		<div class="label"><label for="description">توضیحات</label></div>
		<div class="input"><textarea name="category[description]" id="description"><?php echo $item['description']?></textarea></div>
	</div>
	<div class="formrow">
		<input type="submit" value="ذخیره" name="save">
	</div>
</form>
</div>