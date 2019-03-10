<?php error_reporting(E_ALL); 

require('lib.upload.php');

?>
<!doctype html>
<html>
	<head>
		<meta charset="utf-8" />
		<title>Example</title>
		<style>
*, *:after, *:before {
	-moz-box-sizing: border-box;
	-webkit-box-sizing: border-box;
	box-sizing: border-box;
}

body {
	background: #fff;
	font: 0.9em/1 'Arial', 'Helvetica', sans-serif;
	cursor: default;
}

#more {
	cursor: pointer;
}

.clear:before, .clear:after { content: ""; display: table; }
.clear:after { clear: both; }
		</style>
		<link rel="stylesheet" href="jquery.fileinput.css" />
		<script src="../jquery.min.js"></script>
		<script src="jquery.fileinput.min.js"></script>
		<script>
$(document).ready(function()
{
	$('.uploader').fileInput({
		labelBrowseButton: 'Choose&#8230;',
		labelBrowseButtonDisabled: 'Choose&#8230;'
	});
	
	$('#more').click(function(e){
		$(this).before('<span class="uploader-new uploader"><input type="file" name="uploadedfile[]" /></span>');
		$('.uploader-new').fileInput({
			labelBrowseButton: 'Select&#8230;'
		});
	});
	
});
		</script>
	</head>
	<body>

<?php

if (isset($_POST['action'])) {
	if ($_POST['action'] == 'upload') {

		$targetPath = "uploads/";
		$fileArray = $_FILES['uploadedfile']['name'];
		
		/* bulk upload files */
		$i = 0;
		foreach ($fileArray as $key) {

			if(move_uploaded_file($_FILES['uploadedfile']['tmp_name'][$i], $targetPath.basename($_FILES['uploadedfile']['name'][$i]))) {
				echo $_FILES['uploadedfile']['name'][$i].' uploaded!<br/>';
			} else {
				echo 'File not uploaded!<br/>';
			}
			
			$i++;
		}
		echo '<br />';
	}
}

?>
		<form enctype="multipart/form-data" method="post" action="">
		
			<input type="hidden" name="action" value="upload" />
			
			<span class="uploader clear">
				<input type="file" name="uploadedfile[]"  />
			</span>
			
			<!-- multiple -->
			<span class="uploader clear">
				<input type="file" name="uploadedfile[]" multiple="multiple" />
			</span>
			
			<span class="uploader clear">
				<input type="file" name="uploadedfile[]" />
			</span>
			
			<!-- disabled -->
			<span class="uploader clear">
				<input type="file" name="uploadedfile[]" disabled="disabled" />
			</span>
			
			<a id="more">Add more...</a>
			
			<input type="submit" value="Upload" />
		</form>
		
	</body>
</html>