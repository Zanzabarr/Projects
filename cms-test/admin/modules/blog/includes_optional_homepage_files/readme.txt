Purpose: 
  Use these files to display recent blog posts on the homepage or other pages unrelated to the blog module

Implementation: 
  place the css and includes files in /css and /includes respectively
  include the file where you wish to display it. 
  eg) in /includes/custom/home.php: 
      include "{$_config['rootpath']}includes/inc-blog-box.php";