<?php $this->beginRender()?>
<?php if (isset($message['message'])):?>
<div id="message"><?php echo $message['message']?></div>
<?php endif;?>
<?php include 'sidebar.php';?>
<div id="wrapper">
	<div id="main">
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