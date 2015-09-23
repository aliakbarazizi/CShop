<!DOCTYPE html>
<html lang="en">
    <head>
		<meta charset="UTF-8" />
        <title><?php echo CShop::app()->systemOption()->sitetitle . ' - ' . $this->pageTitle?></title>
        <link rel="stylesheet" type="text/css" href="<?php echo CShop::$baseurl?>/static/css/loginstyle.css" />
    </head>
    <body>
			<div class="main">
	
				<form class="form-1" method="post">
					<p class="field">
						<input type="text" name="username" placeholder="Username" class="<?php if($message) echo 'error'?>">
						<i class="icon-user icon-large"></i>
					</p>
					<p class="field">
							<input type="password" name="password" placeholder="Password" class="<?php if($message) echo 'error'?>">
							<i class="icon-lock icon-large"></i>
					</p>
					<p class="field error"><?php echo $message?></p>
					<p class="submit">
						<button type="submit" name="submit"><i class="icon-arrow-right icon-large"></i></button>
					</p>
				</form>
			</div>
    </body>
</html>