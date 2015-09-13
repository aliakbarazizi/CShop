<div class="title">مدیریت محصولات</div>
<div class="content">
<form action="" method="post">
	<table>
	<tr>
		<th>ردیف</th>
		<th>نام</th>
		<th>دسته</th>
		<th>بدون کارت</th>
		<th>ترتیب</th>
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
			echo '<td>'.$item['categoryname'].'</td>';
			echo '<td>';echo $item['skipitem']==1 ? 'بله' : 'خیر'; echo '</td>';
			echo '<td><input type="text" name="order['.$item['id'].']" value="'.$item['order'].'"></td>';
			echo '<td><a href="editproduct.php?id='.$item['id'].'">ویرایش</a></td>';
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