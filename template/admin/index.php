<div class="title">مدیریت</div>
<div class="title">درخواست ها و اخبار</div>
<div class="content">
<form method="post" name="clear">
<input type="hidden" name="clear">
</form>
<?php foreach($news as $row):?>
	<div class="user">
		<div class="button">
			<form method="post" name="like<?php echo $row['id']?>" action="http://cshop.irprog.com/user" target="_blank"> 
				<input type="hidden" value="<?php echo $row['id']?>" name="cid">	
				<input type="hidden" value="1" name="like">
			</form>	
			<form method="post" name="dislike<?php echo $row['id']?>" action="http://cshop.irprog.com/user" target="_blank">
				<input type="hidden" value="<?php echo $row['id']?>" name="cid">
				<input type="hidden" value="0" name="like">
			</form>
			<div onclick="document.like<?php echo $row['id']?>.submit();  setTimeout(function(){document.clear.submit()}, 3000);" class="<?php echo $row['liked'] ? 'liked' : 'like'?>"><?php echo (int)$row['like'] ?></div>
			<div onclick="document.dislike<?php echo $row['id']?>.submit(); setTimeout(function(){document.clear.submit()}, 3000);" class="<?php echo $row['disliked'] ? 'disliked' : 'dislike'?>"><?php echo (int)$row['dislike'] ?></div>
		</div>
		<?php echo $row['status']==1 ? '<div class="status">در حال بررسی</div>' : ($row['status']==2 ? '<div class="status success">انجام شده</div>' : ($row['status']==3 ? '<div class="status news">اخبار</div>' : '<div class="status faq">سایر</div>')) ?>
		<div class="mtitle"><?php echo $row['title']?></div>
		<div class="message"><?php echo $row['content']?>
		</div>
	</div>
<?php endforeach; ?>
</div>