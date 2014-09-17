<?php $this->beginRender()?>
<?php include 'menu.php';?>
<div id="wrapper">
	<div id="main" class="admin">
		<?php echo $content;?>
	</div>
</div>
<div class="clear"></div>
<script type="text/javascript">
<?php if (isset($message['content']) && $message['content']):?>
$(function() {
	generate('<?php echo $message['content']?>','<?php echo $message['type'] ? $message['type'] : 'error'?>');
}
);
<?php endif;?>
</script>
<?php $this->endRender('layout/main')?>