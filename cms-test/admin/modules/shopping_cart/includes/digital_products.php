<div id="digital_product">
		
		<h2 class="tiptip toggle" id="digital_product-toggle" title="Upload Digital Products Here" >Digital Products</h2>
		
		<div id="digital_product-toggle-wrap">
			<div class="digital_product_left">
				<label class="tipRight" title="Upload images, the following are valid type jpeg, gif, png." >Upload Image</label>
				<div class='input_inner'>
			<?php  if (using_ie()) : ?>
					<input id="digital_product_upload" type='file' name='files[]' style='width:180px;' data-url="../../js/jquery.fileupload/upload_handler.php" multiple>
			<?php else : ?>
					<div id="digital_product-fake-file-wrap" style='position:relative;'>
						<div id='digital_product-fake-file'>Click to Select a File</div>
						<input type='file' name='files[]' id="digital_product_upload" style='position: relative;opacity:0; z-index:2;' data-url="../../js/jquery.fileupload/upload_handler.php" multiple />
					</div><br />
			<?php endif; ?>
				</div>	
				<div id="digital_product-progress">
					<div class="bar" style="width: 0%;"></div>
				</div>
				<p id="digital_product-no-progress">(Click above or drag and drop files into box below)</p>
			</div>	
			<div class='message_wrap'>
				<span id='digital_product_msg' <?php echo isset($imageErr) ? "class='errorMsg'" : (isset($imageSuccess) ? "class='successMsg'" :''); ?>><?php echo isset($imageErr) ? $imageErr : (isset($imageSuccess) ? $imageSuccess : '' ); ?></span>
			</div>
				
			<div class='clearFix'></div>

			<input type="hidden" id="digital_product_id" value="<?php echo $page_get_id_val; ?>">
			<input type="hidden" id="digital_product_admin_url" value="<?php echo $_config['admin_url'] ?>" >				
		
			<div class='clearFix' ></div> 

			<div class="windowHeading tipTop" title="These Digital Products will be distributed with this purchase."><h2>Digital Products</h2></div>
			<div id='digital_product_pen_group' class = 'scroll'>	
				<ul id="digital_product_pen" class='connected'>
				<!-- build result set -->
<?php
$result = logged_query("
	SELECT * FROM `upload_file` 
	WHERE `page_type` = 'digital_product' 
	  AND `page_id` = :page_get_id_val
	ORDER BY filename
",0,array(":page_get_id_val" => $page_get_id_val));
if(is_array($result) && count($result)) : foreach($result as $digi_prod)
{ ?>
					<li><span><?php echo $digi_prod['filename']; ?></span><a class='digital_product-del' href='#' rel='<?php echo $digi_prod['filename']; ?>'><img src='../../images/delete.png' ></a></span></li>
<?php
} endif;
?>				
				</ul> <!-- end image pen -->
				<div class='clearFix' ></div>
			</div>	
			
		</div><!-- end "digital_product-toggle-wrap" -->
	


</div>