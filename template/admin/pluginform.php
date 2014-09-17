<div class="title">مدیریت پلاگین ها</div>
<div class="content">
<form action="" method="post">
<?php
foreach ($items as $item)
{
	if(! $out=$item['class']::proccessInput($item['key'],"meta[{$item['key']}]",$item['value'],array('id'=>$item['key']))) continue;
?>
	<div class="formrow">
		<div class="label"><label for="<?php echo $item['key']?>"><?php echo $item['class']::getParameterByName($item['key'])?></label></div>
		<div class="input"><?php echo $out?></div>
	</div>
<?php }?>
	<div class="formrow">
		<input type="submit" value="ذخیره" name="save">
	</div>
</form>
</div>