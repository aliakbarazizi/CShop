<div class="title">مدیریت پرداخت ها</div>
<div class="content">
	<table>
	<tr>
		<th>ردیف</th>
		<th>نام</th>
		<th>مقدار</th>
	</tr>
		<?php 
		$td = $th = '';
		$i=1;
		foreach($payment['input'] as $input)
		{
			echo '<tr>';
			echo '<td>'.$i++.'</td>';
			echo '<td>'.$input['name'].'</td>';
			echo '<td>'.$input['value'].'</td>';
			echo '</tr>';
		}

		?>
	</table>
</div>
<div id="result">
	<?php foreach ($items as $item):?>
	<div class="item">
		<div class="title"><?php echo $item[0]['name']?></div>
		<div class="content">
			<table>
				<?php 
				$td = $th = '';
				foreach($item as $i)
				{
					if (!isset($i['value']))
					{
						continue;
					}
					$th .= '<th>'.$i['fieldname'].'</th>';
					$td .= '<td>'.Item::proccess($i['type'], $i['value']).'</td>';
				}
				echo '<tr>'.$th.'</tr>';
				echo '<tr>'.$td.'</tr>';
				?>

			</table>	
		</div>
		<div class="bottom"><?php echo $item[0]['description']?></div>
	</div>
	<?php endforeach;?>
</div>