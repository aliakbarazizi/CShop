<div class="title">مدیریت کارت ها</div>
<div class="content">
<form action="" method="post">
	<table>
	<tr>
		<th>ردیف</th>
		<th>نام</th>
		<th>وضعیت</th>
		<th>مدیریت</th>
		<th><a href="#" onclick="check(this)">انتخاب</a></th>
	</tr>
		<?php 
		$td = $th = '';
		$i=1;
		foreach($items as $item)
		{
			echo '<tr>';
			echo '<td>'.$i++.'</td>';
			echo '<td>'.$item['name'].'</td>';
			echo '<td>'; echo $item['status']==0?'فروخته نشده':($item['status'] == 1 ? 'فروخته شده' : 'غیر فعال'); echo '</td>';
			echo '<td><a href="edititem.php?id='.$item['id'].'">ویرایش</a></td>';
			echo '<td><input type="checkbox" name="delete[]" value="'.$item['id'].'"></td>';
			echo '</tr>';
		}

		?>
	</table>
	<?php if($this->pagination->total):?>
	<div class="pagination">
	<?php echo $this->pagination->getPagination();?>
	</div>
	<div style="text-align: left">
	<input type="submit" value="ذخیره" name="update">
	<input type="submit" value="حذف" name="remove">
	</div>
	<?php endif;?>
</form>
</div>