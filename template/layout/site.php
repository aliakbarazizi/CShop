<?php $this->beginRender()?>
<?php if (isset($message['message'])):?>
<div id="message"><?php echo $message['message']?></div>
<?php endif;?>
<div class="wrp">
	<div class="content">
		<?php echo $content;?>
	</div>
	<div class="copyright">
		تمامی حقوق برای فروشگاه سی شاپ محفوظ میباشد . قدرت گرفته از : <a href="#">فروشگاه ساز سی شاپ</a> ، طراحی قالب توسط : <a href="http://shahkarweb.com/" title="طراحی سایت">طراحان شاهکار</a>
	</div>
</div>
<script type="text/javascript">
<?php if (isset($message['content']) && $message['content']):?>
$(function() {
	generate('<?php echo $message['content']?>','<?php echo $message['type'] ? $message['type'] : 'error'?>');
}
);
<?php endif;?>
</script>
<?php $this->endRender('layout/main')?>


