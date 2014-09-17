<div class="title">مدیریت پرداخت ها</div>

<div class="content">
<form action="" method="post">
	<table>
	<tr>
		<th>ردیف</th>
		<th>تاریخ</th>
		<th>مبلغ</th>
		<th>وضعیت</th>
		<th>مدیریت</th>
		<th><a href="#" onclick="check(this)">انتخاب</a></th>
	</tr>
		<?php 
		$td = $th = '';
		$i=1+$this->pagination->offset();
		
		foreach($items as $item)
		{
			echo '<tr>';
			echo '<td>'.$i++.'</td>';
			echo '<td>'.jDateTime::date(CShop::app()->systemConfig()->timeformat,$item['paymenttime'] ? $item['paymenttime'] : $item['requesttime']).'</td>';
			echo '<td>'.$item['amount'].'</td>';
			echo '<td>'; echo $item['status']==Application::STATUS_COMPLETE ? 'پرداخت شده' : 'پرداخت نشده'; echo '</td>';
			echo '<td><a href="viewpayment.php?id='.$item['id'].'">مشاهده</a></td>';
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