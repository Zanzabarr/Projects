<?php
if (uri::get(1) == 'ftp' ) require_once('includes/display_ftp.php');
else require_once('includes/display_members.php');