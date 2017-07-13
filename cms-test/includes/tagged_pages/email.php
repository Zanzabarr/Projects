<?php 
// CUSTOM EMAIL {{email/my text here/customemail@example.com}} OR CONFIG EMAIL
$emailaddress = (isset($arSpecial[2]) && $arSpecial[2]!="") ? $arSpecial[2] : $_config['forms_email'];

// SCRAMBLE MAILTO:EMAIL
$mailto = $emailaddress;
$mailto = str_replace("@","#",$mailto);
$mailto = str_replace(".ca","&#x2e;'+'c'+'a",$mailto);
$mailto = str_replace(".com","&#x2e;c'+'o'+'m",$mailto);
$mailto = "'mai'+'lto:".$mailto;

// WHAT YOU SEE - DEFAULT - TEXT AS EMAIL
$etxt = $emailaddress;
$etxt = str_replace("@","<span style='display:none;'> #?a=#$-@@@.a</span>&#x40;<span style='font-size:0;'> a#</span>",$etxt);
$etxt = str_replace(".",".<span style='display:none;'>...! ... </span>",$etxt);
$etxt = str_replace("com","<span style='unicode-bidi:bidi-override;direction:rtl;'>moc</span>",$etxt);

// WHAT YOU SEE - OVERRIDE IF GIVEN TEXT - {{email/my text here}} - "my text here" to "ereh txet ym"
$etxt = (isset($arSpecial[1]) && $arSpecial[1]!="") ? "<span style='unicode-bidi:bidi-override;direction:rtl;'>".strrev($arSpecial[1])."</span>" : $etxt;

echo "<a href='Send me a message!' style='white-space:nowrap;' onclick=\"str={$mailto}?subjec'+'t=Not Spam';this.href=str.replace('#','@')\">{$etxt}</a>";

// USE LIKE THIS:
//{{email}} >> <a href="mailto:{$_config['forms_email']}">{$_config['forms_email']}</a>
//{{email/my text here}} >> <a href="mailto:{$_config['forms_email']}">my text here</a>
//{{email/my text here/customemail@example.com}} >> <a href="mailto:customemail@example.com">my text here</a>
//{{email//customemail@example.com}} >> <a href="mailto:customemail@example.com">customemail@example.com</a>
?>

