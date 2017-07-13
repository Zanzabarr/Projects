1. if you pulled this folder and want to include it in a site not based on the latest cms-test: a)put this in index.php, just after <div id="top"> before <div id="header">

<?php include_once ( $_config['rootpath'] . 'iconbar/iconbar.php' ); ?>

b)put this chunk of code inside the <head> in includes/inc-head.php

<!-- sharethis -->
<script type="text/javascript">var switchTo5x=true;</script>
<script type="text/javascript" src="http://w.sharethis.com/button/buttons.js"></script>
<script type="text/javascript">stLight.options({publisher: "3e5b23a3-0c6d-4d14-94d3-58261499cf0a", doNotHash: false, doNotCopy: false, hashAddressBar: false});</script>

------------------------------------------------------------------------------------


a) go to http://www.sharethis.com/ and login
b) get code: 1-website, 2-buttons, 3-customize
c) change sharethis buttons in iconbar.php

------------------------------------------------------------------------------------

3. in iconbar.php - add social media links to icons and comment out those icons not needed:
,array( "Facebook", "facebook", "https://www.facebook.com/customeraccountpage" ) // valid
//,array( "Facebook", "facebook", "https://www.facebook.com/" ) // commented out

------------------------------------------------------------------------------------

4. css is made with a css fade as background. icons change to small at 549px. icons are white 32px png's. if you need new icons (or other colors) go to https://icomoon.io/app/#/select
