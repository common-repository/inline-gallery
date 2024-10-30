<?php 
	include "browser_functions.php";
?>
<div class="wrap">
	<h2>Inline Gallery Manager</h2>
	<h3 class="ig_menu">
		<a href="<?php echo $base; ?>&amp;load=browser" id="ig_menu_browse" title="Browse galleries">Browse</a>&nbsp;
		<?php echo $_GET["do"]; ?>
	</h3>
	<div id="ig_galleries">
		<?php ig_list_galleries(); ?>
		<br clear="both" />
	</div>
	<div id="ig_browser">
		<h4 id="ig_browser_title">Gallery</h4>
		<div id="ig_images">
		</div>
		<div id="ig_meta">
			<div id="ig_preview">
			</div>
			<form action="" method="post" id="ig_form">
			<fieldset>
				<input type="text" size="80" id="ig_caption" name="ig_caption" />
			</fieldset>
			</form>
		</div>
		<br clear="both" />
	</div>
</div> 

