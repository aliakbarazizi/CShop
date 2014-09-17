<div class="title">مدیریت فیلد ها</div>
<div class="content">
<form action="" method="post">
	<div class="formrow">
		<div class="label"><label for="name">نام</label></div>
		<div class="input"><input type="text" name="input[name]" id="name" value="<?php echo $item['name']?>"></div>
	</div>
	<div class="formrow">
		<div class="label"><label for="type">نوع</label></div>
		<div class="input">
		<select name="input[type]" id="type">
		<?php
		foreach (Input::types() as $key=> $value)
		{
			echo Html::optionList($value['description'], $key,$key==$item['type']);
		}
		?>
		</select>
		</div>
	</div>
	<div class="formrow">
		<div class="label"><label for="placeholder">توضیحات</label></div>
		<div class="input"><input type="text" name="input[data][placeholder]" id="placeholder" value="<?php echo $item['data']['placeholder']?>"></div>
	</div>
	<div class="formrow">
		<div class="label"><label for="require">آیا فیلد اجباری است؟</label></div>
		<div class="input"><?php echo Html::selectList('input[data][require]', Html::optionsList(array('خیر','بله'),$item['data']['require']),array('id'=>'require'))?></div>
	</div>
	<div class="formrow">
		<div class="label"><label for="min">حداقل طول فیلد</label></div>
		<div class="input"><input type="text" name="input[data][minLenght]" id="min" value="<?php echo $item['data']['minLenght']?>"></div>
	</div>
	<div class="formrow">
		<div class="label"><label for="max">حداکثر طول فیلد</label></div>
		<div class="input"><input type="text" name="input[data][maxLenght]" id="max" value="<?php echo $item['data']['maxLenght']?>"></div>
	</div>
	<div class="formrow">
		<input type="submit" value="ذخیره" name="save">
	</div>
</form>
</div>