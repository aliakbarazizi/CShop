<div class="title">مدیریت محصولات</div>
<div class="content">
<form action="" method="post">
	<div class="formrow">
		<div class="label"><label for="name">نام</label></div>
		<div class="input"><input type="text" name="product[name]" id="name" value="<?php echo $item['name']?>"></div>
	</div>
	<div class="formrow">
		<div class="label"><label for="price">مبلغ</label></div>
		<div class="input"><input  type="text" name="product[price]" id="price" value="<?php echo $item['price']?>"></div>
	</div>
	<div class="formrow">
		<div class="label"><label for="skipitem">بدون کارت</label></div>
		<div class="input">
			<select name="product[skipitem]" id="skipitem">
			<?php echo Html::optionsList(array(0=>'خیر',1=>'بله'),$item['skipitem'])?>
			</select>
		</div>
	</div>
	<div class="formrow">
		<div class="label"><label for="description">توضیحات</label></div>
		<div class="input"><textarea name="product[description]" id="description"><?php echo $item['description']?></textarea></div>
	</div>
	
	
	<div class="formrow">
		<div class="label"><label for="categoryid">دسته</label></div>
		<div class="input"><select name="product[categoryid]" id="categoryid">
		<?php foreach ($category as $c)
		{
			if ($item['categoryid'] == $c['id']) {
				$selected = 'selected';
			}
			else
				$selected = '';
			
			echo "<option value='$c[id]' $selected>$c[name]</option>";
		}
		?>
		</select></div>
	</div>
	
	<div id="fields">
		
	</div>
	
	<div class="formrow">
		<select id="type">
		<?php CShop::app()->raise(Application::EVENT_ITEM_TYPE);
		foreach (Item::types() as $type=>$value):?>
			<option value="<?php echo $type?>"><?php echo $value['description']?></option>
		<?php endforeach;?>
		</select>
		<input type="button" value="اضافه کردن" id="add">
	</div>
	
	<div class="formrow">
		<input type="submit" value="ذخیره" name="save">
	</div>
</form>
</div>

<script>
$(function() {
	$("#add").click(function() {
		var text = $("#type option:selected").text();
		var type = $("#type").val();

		add(type,text);
	});
});
var field_id = 0;

function add(type,text,value,id)
{
	value = typeof value !== 'undefined' ? value : '';
	
	var add = '<div class="label"><label for="field_'+field_id+'">'+text+'</label></div>'+
	'<div class="input"><input type="text" name="field['+field_id+'][fieldname]" id="field_'+field_id+'" value="'+value+'">'+
	'<input type="hidden" name="field['+field_id+'][type]" id="name" value="'+type+'"></div>';

	if(typeof id !== 'undefined' && id != '')
		add += '<input type="hidden" name="field['+field_id+'][fieldid]" value="'+id+'">';
	
	field_id++;
	$("#fields").append(add);
}
<?php
		if (is_array($fields))
		{
			foreach ($fields as $product)
			{
				if (isset($product[type]) && isset($product[fieldname]))
				{
					echo "add('$product[type]','".Item::description($product[type])."','$product[fieldname]',";
					echo $product[fieldid] ? $product[fieldid] : "''" ;
					echo ");";
				}
			}
		}
?>
</script>