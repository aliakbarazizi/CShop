<div class="title">مدیریت کارت ها</div>
<div class="content">
<form action="" method="post">
	<div class="formrow">
		<div class="label"><label for="status">وضعیت</label></div>
		<div class="input">
			<select name="item[status]" id="status">
				<option value="0" <?php if($item['status']==0) echo "selected"?> >فروخته نشده</option>
				<option value="-1" <?php if($item['status']==-1) echo "selected"?> >غیر فعال</option>
				<option value="1" <?php if($item['status']==1) echo "selected"?> >فروخته شده</option>
			</select>
		</div>
	</div>

	
	<div class="formrow">
		<div class="label"><label for="product">محصول</label></div>
		<div class="input">
		<select name="item[productid]" id="product">
		<?php echo Html::optionsList($products,$item['productid']);	?>
		</select></div>
	</div>
	
	<div id="fields">
		
	</div>
	
	<div class="formrow">
		<select id="type">

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

		if(text)
		{
			add(type,text);
		}
	});

	$("#product").change(function() {
		$.ajax({
			url: "createitem.php?product="+this.value,
			dataType: "json",
			success: function(data) {
				var options, index, select, option;
				select = document.getElementById('type');

				select.options.length = 0;

				options = data; // Or whatever source information you're working with


				for (index = 0; index < options.length; ++index) {
					option = options[index];
					if($("input[name$='[fieldid]'][value='"+option.id+"']").length == 0)
					{
						select.options.add(new Option(option.name, option.id));
					}
				}
			}
		});
	});
	$("#product").trigger('change');
});
var field_id = 0;

function add(type,text,value)
{
	value = typeof value !== 'undefined' ? value : '';
	
	var add = '<div class="label"><label for="field_'+field_id+'">'+text+'</label></div>'+
	'<div class="input"><input type="text" name="value['+field_id+'][value]" id="field_'+field_id+'" value="'+value+'">'+
	'<input type="hidden" name="value['+field_id+'][fieldid]" id="name" value="'+type+'"><input type="hidden" name="value['+field_id+'][fieldname]" id="name" value="'+text+'"></div>';
	field_id++;
	$("#fields").append(add);
	$("#type option[value='"+type+"']").remove();
}
<?php
		if (is_array($values))
		{
			foreach ($values as $value)
			{
				if (isset($value[value]) && isset($value[fieldid]))
					echo "add($value[fieldid],'$value[fieldname]','$value[value]');";
			}
		}
?>
</script>