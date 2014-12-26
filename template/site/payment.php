<div class="wrp" style="width:450px">
	<div class="content" style="width:450px">
		<div class="show">
			<div class="title message">
				<?php echo $message['content']?>
			</div>
		</div>
		<?php foreach ($items as $item):?>
		<div class="show">
			<div class="title">
				<?php echo $item[0]['name']?>
			</div>
			<?php 
				
				foreach($item as $i)
				{
					if (!isset($i['value']) || Item::checkHidden($i['type']))
					{
						continue;
					}
					echo '<span><li>'.$i['fieldname'].'</li>';
					echo Item::proccess($i['type'], $i['value']).'</span>';
				}
			?>
			<div class="bottom"><?php echo $item[0]['description']?></div>
		</div>
		<?php endforeach;?>
	</div>
	<div class="copyright">
		طراحی قالب توسط : <a href="http://shahkarweb.com/">طراحان شاهکار</a>
	</div>
</div>
