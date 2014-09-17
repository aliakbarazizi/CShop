<div class="title">مدیریت پلاگین ها</div>
<div class="content">
<form action="" method="post">
	<table>
	<tr>
		<th>ردیف</th>
		<th>نام</th>
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
			echo '<td><input type="text" name="order['.$item['id'].']" value="'.$item['order'].'"></td>';
			echo '<td><a href="plugindata.php?id='.$item['id'].'">ویرایش</a></td>';
			echo '<td><input type="checkbox" name="delete[]" value="'.$item['id'].'"></td>';
			echo '</tr>';
		}

		?>
	</table>
	<?php if(!empty($items)):?>
	<div style="text-align: left">
	<input type="submit" value="ذخیره" name="update">
	<input type="submit" value="حذف" name="remove">
	</div>
	<?php endif;?>
</form>
</div>
<div class="title">مدیریت دسته ها</div>
<div class="content">
<form action="" method="post">
	<table>
	<tr>
		<th>ردیف</th>
		<th>نام</th>
		<th>مدیریت</th>
	</tr>
		<?php 
		$td = $th = '';
		$i=1;
		foreach($newplugins as $item)
		{
			echo '<tr>';
			echo '<td>'.$i++.'</td>';
			echo '<td>'.$item['name'].'</td>';
			echo '<td><a href="plugin.php?install='.$item['filename'].'">نصب</a></td>';
			echo '</tr>';
		}

		?>
	</table>
	<?php if(!empty($newplugins)):?>
	<div style="text-align: left">
	<input type="submit" value="ذخیره" name="update">
	<input type="submit" value="حذف" name="remove">
	</div>
	<?php endif;?>
</form>
</div>