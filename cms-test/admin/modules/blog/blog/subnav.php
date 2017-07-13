<div id="blognav">
    <ul>
        <li><a href="blog.php" class='<?php echo $selectedPosts; ?>' >Posts</a></li>
        <li><a href="blog_cats.php" class='<?php echo $selectedCats; ?>' >Categories</a></li>
		<li><a href="blog_options.php" class='<?php echo $selectedOpts; ?>' >Blog Options</a></li>
    </ul>
	<div id='blognavbtn'>
		<a class="blue button tipTop" title='Write a new Post for your blog.' href="blog_new.php?option=create">New Blog Post</a>
		<a class="blue button tipTop" title='Create a new category to post your blogs under.' href="blog_category.php?option=create">New Category</a>
		<a class="blue button tipTop" title="Write a description about the blog." href="blog_home.php">Blog Homepage</a>
		<div class='clearFix'></div>
	</div>	
</div>
