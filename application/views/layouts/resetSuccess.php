<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8" />
	<meta name="renderer" content="webkit"> 
      <title>修改成功</title>
	<script type="text/javascript" src="<?php echo $this->scriptUrl; ?>jquery-1.8.2.min.js"></script>
	<script type="text/javascript">
	$(document).ready(function() {
		alert("修改成功");
		window.location.href="<?php echo $this->app->getSiteBaseUrl();?>";
	});
	</script>
</head>
<body>
</body>
</html>