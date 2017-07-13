**********************************************
README for Stikitab
**********************************************

*Instructions for positioning the stickitab

1) 	Place the stikitab folder in the webroot 

2)  In /includes/inc-bottom.php file include the following code right before the </body> tag. (fixed position, doesn't care where it goes)
	<?php
	// ** INCLUDE STIKITAB ** //
	include_once( $_config['rootpath'] . 'stikitab/stikitab.php');
	?> 

3) 	To style the stikitab, edit the css rules in /stikitab/stikitab.css


4)	To add a new link, ensure images are placed in /stikitab/icons/ and write the styles using the established pattern