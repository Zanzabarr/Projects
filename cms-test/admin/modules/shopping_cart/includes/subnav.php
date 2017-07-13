<div id="cartnav">
<?php if ($_SESSION['user']['admin'] == 'yes') : // only have access to products with admin status ?>
    <ul>
		<?php //only show the orders page if it is enabled
		if ( ecom_functions::valid_shopping_admin('orders.php') ) : ?>
        <li <?php if($pg == 'orders'){echo " class=\"active\"";} ?>><a href="orders.php">Orders</a></li>
		<?php endif; ?>
        <li <?php if($pg == 'product'){echo " class=\"active\"";} ?>><a href="product.php">Products</a></li>
		<li <?php if($pg == 'category'){echo " class=\"active\"";} ?>><a href="category.php">Categories</a></li>
		<li <?php if($pg == 'collection'){echo " class=\"active\"";} ?>><a href="collections.php">Collections</a></li>
		<li <?php if($pg == 'preferences'){echo " class=\"active\"";} ?>><a href="edit_preferences.php">Preferences</a></li>
<?php
		/* show Shipping and Taxes tabs only if cart, pricing, shipping, purchasing are active */
		if($_config['shopping_cart']['preferences']['hasCart']==1 && $_config['shopping_cart']['preferences']['includePricing']==1) { 
			if($_config['coupons']) {
		?>
			<li <?php if($pg == 'coupons') {echo " class=\"active\"";} ?>><a href="edit_coupons.php">Coupons</a></li>
	<?php	}
			if($_config['shopping_cart']['preferences']['shipping_on']==1) { ?>
				<li <?php if($pg == 'shipping'){echo " class=\"active\"";} ?>><a href="edit_shipping.php">Shipping</a></li>
	<?php	} 
			if($_config['shopping_cart']['preferences']['purchasing_on']==1) { ?>
				<li <?php if($pg == 'taxes'){echo " class=\"active\"";} ?>><a href="edit_taxes.php">Taxes</a></li>
			<?php /* REPORTING NOT YET COMPLETE 
				<li <?php if($pg == 'reports') {echo " class=\"active\"";} ?>><a href="reports.php">Reports</a></li> */
			?>
<?php		}
		}?>
	</ul>
<?php endif;?>
	<div id='navbtn'>
	<?php 
	switch($pg)
	{
		case "collection" : 
			echo "<a class='blue button tipTop' title=Return to Collections Home Page.' href='collections.php'>Collection Home</a>";
			echo "<a class='blue button tipTop' title='Create a new Product Collection.' href='edit_collection.php?option=create'>New Collection</a>";
			break;
		case "product" :	
			echo "<a class='blue button tipTop' title='Return to Products Home Page' href='product.php'>Products Home</a>";
			echo "<a class='blue button tipTop' title='Create a new Product.' href='edit_product.php?option=create'>New Product</a>";
			break;
		case "category" :	
			echo "<a class='blue button tipTop' title='Return to Category Home Page' href='category.php'>Category Home</a>";
			echo "<a class='blue button tipTop' title='Create a new Category.' href='edit_category.php?option=new_cat'>New Category</a>";
			break;
	}
	?>
		<div class='clearFix'></div>
	</div>	
</div>
