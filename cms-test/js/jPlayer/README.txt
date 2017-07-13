********************
Date: July 4th, 2012
Author: Ryan Osler
********************


******************************
jPlayer with Playlist Tutorial
******************************

To set things up, you will need to:
1) Put the "jPlayer" inner folder into the root directory's "js" folder
2) Put the "jPlayerPlaylistInstance.php" file into the root directory's "/includes/tagged_pages" folder.
3) Add these lines to your "/includes/inc-head.php" file:

	<link rel="stylesheet" href="<?php echo $_config['site_path']; ?>js/jPlayer/skin/jplayer.blue.monday.css" type="text/css">
	<script type="text/javascript" src="<?php echo $_config['site_path']; ?>js/jPlayer/jquery.jplayer.min.js"></script>
	<script type="text/javascript" src="<?php echo $_config['site_path']; ?>js/jPlayer/jplayer.playlist.min.js"></script>

*NOTE*
the two javascript includes MUST go after the inclusion of the base jquery file (i.e. <script type="text/javascript" src="js/jquery-1.7.2.min.js"></script>)

4) Make sure the .htaccess file in the "uploads/files" folder is not forcing all filetypes to be octet-stream, otherwise the player will not work in IE

5) Once these steps are all done, to include the player on a page (with a playlist), upload the songs for the page with the pages file uploader, and then place
   code in this format in the HTML edit of the tinymce page content box where you want the player to go (will explain after):

	{{jPlayerPlaylistInstance/jplayer_instance_1/Test Name 1/test1.mp3/Test Name 2/test2.mp3/Test Name 3/test3.mp3}}

NOTE:
"jPlayerPlaylistInstance" is the name of the script used, and will always stay the same

"jplayer_instance_1" is a unique id for the particular jplayer. Needs to be unique on a per page basis. For convention and to avoid conflicts, 
I'd stick with naming the id's jplayer_instance_1, jplayer_instance_2, etc.

"Test Name 1", "Test Name 2", and "Test Name 3" are the titles of the songs as they will appear in the playlist, and "test1.mp3", "test2.mp3", and "test3.mp3" are 
the filenames of the uploaded mp3 files that are played on the instance of the jplayer. In this case, the uploaded files are test1.mp3, test2.mp3 and test3.mp3, 
respectively. There needs to be at least one music file here for the player to work (obviously), but you can have as many as you want for the playlist.
Just to clarify, the format is SongTitle1/Filename1/SongTitle2/Filename2 etc. etc.

The player itself is fully customizable through CSS, though the CSS is quite obtuse as there are rules for the video player portion, as well as a lot of buttons
that are unused on this player.



the admin user/pass is "admin/admin", so you can look at how it is set up. Note, I did not use the file uploader to upload the mp3's, 
just put them in the uploads folder myself, and although they have different names, are both the same song.


****************************
jPlayer single song Tutorial
****************************

To set things up, you will need to:
1) Put the "jPlayer" inner folder into the root directory's "js" folder
2) Put the "jPlayerInstance.php" file into the root directory's "/includes/tagged_pages" folder.
3) Add these lines to your "/includes/inc-head.php" file:

	<link rel="stylesheet" href="<?php echo $_config['site_path']; ?>js/jPlayer/skin/jplayer.blue.monday.css" type="text/css">
	<script type="text/javascript" src="<?php echo $_config['site_path']; ?>js/jPlayer/jquery.jplayer.min.js"></script>


*NOTE*
the two javascript includes MUST go after the inclusion of the base jquery file (i.e. <script type="text/javascript" src="js/jquery-1.7.2.min.js"></script>)

4) Make sure the .htaccess file in the "uploads/files" folder is not forcing all filetypes to be octet-stream, otherwise the player will not work in IE

5) Once these steps are all done, to include the player on a page, upload the song for the page with the pages file uploader, and then place
   code in this format in the HTML edit of the tinymce page content box where you want the player to go (will explain after):

	{{jPlayerInstance/jplayer_instance_1/test1.mp3}}

NOTE:
"jPlayerInstance" is the name of the script used, and will always stay the same

"jplayer_instance_1" is a unique id for the particular jplayer. Needs to be unique on a per page basis. For convention and to avoid conflicts, 
I'd stick with naming the id's jplayer_instance_1, jplayer_instance_2, etc.

"test1.mp3" is the filename of the uploaded mp3 file that is played on the instance of the jplayer. In this case, the uploaded file is test1.mp3, 
respectively.

The player itself is fully customizable through CSS, though the CSS is quite obtuse as there are rules for the video player portion, as well as a lot of buttons
that are unused on this player.


the admin user/pass is "admin/admin", so you can look at how it is set up. Note, I did not use the file uploader to upload the mp3, 
just put them in the uploads folder myself.


