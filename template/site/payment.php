<div class="title"><?php echo $message['content']?></div>
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
					if (!isset($i['value']) || Item::checkHidden($i['type']))
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