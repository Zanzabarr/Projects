<?php
function send_newsletter($to, $subject, $message, $attachments = array())
{	
	if ( $attachments && count($attachments) > 0 ) return send_email_attachments($to, $subject, $message, $attachments);
	else return send_html_plain_email($to, $subject, $message);
}

function send_html_plain_email($to, $subject, $message)
{
	global $_config;
	$from = $_config['newsletter'];
	$boundary="BOUNDARY_".md5(mt_rand());
	$plain =  wordwrap(convert_html_to_text(htmlspecialchars_decode($message)), 70);
	$html = wrap_content($subject, $message);
		
	$headers = "MIME-Version: 1.0\n".
	"From: \"{$from['name']}\" <{$from['email']}>" ."\n".
	"Reply-To: {$from['email']}" ."\n".
	"Content-Type: multipart/alternative;" ."\n".
	" boundary=\"{$boundary}\"\n";

	$msg = "--{$boundary}\nContent-Type: text/plain; charset=iso-8859-1\nContent-Transfer-Encoding: 8bit\n\n{$plain}\n--{$boundary}\nContent-Type: text/html; charset=iso-8859-1\nContent-Transfer-Encoding: 8bit\n\n{$html}\n--{$boundary}--";

	return mail($to['name'] . " <" .$to['email'].">", $subject, $msg, $headers);
}

if(!function_exists('mime_content_type')) {

    function mime_content_type($filename) {

        $mime_types = array(

            'txt' => 'text/plain',
            'htm' => 'text/html',
            'html' => 'text/html',
            'php' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'swf' => 'application/x-shockwave-flash',
            'flv' => 'video/x-flv',

            // images
            'png' => 'image/png',
            'jpe' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'tiff' => 'image/tiff',
            'tif' => 'image/tiff',
            'svg' => 'image/svg+xml',
            'svgz' => 'image/svg+xml',

            // archives
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'exe' => 'application/x-msdownload',
            'msi' => 'application/x-msdownload',
            'cab' => 'application/vnd.ms-cab-compressed',

            // audio/video
            'mp3' => 'audio/mpeg',
            'qt' => 'video/quicktime',
            'mov' => 'video/quicktime',

            // adobe
            'pdf' => 'application/pdf',
            'psd' => 'image/vnd.adobe.photoshop',
            'ai' => 'application/postscript',
            'eps' => 'application/postscript',
            'ps' => 'application/postscript',

            // ms office
            'doc' => 'application/msword',
            'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',

            // open office
            'odt' => 'application/vnd.oasis.opendocument.text',
            'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
        );

        $ext = strtolower(array_pop(explode('.',$filename)));
        if (array_key_exists($ext, $mime_types)) {
            return $mime_types[$ext];
        }
        elseif (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME);
            $mimetype = finfo_file($finfo, $filename);
            finfo_close($finfo);
            return $mimetype;
        }
        else {
            return 'application/octet-stream';
        }
    }
}

function buildNewsletters_Menu($newsletters)
{
	global $_config;
	$menu="";
	foreach($newsletters as $newsletter)
	{
	switch($newsletter['status']) {
		case 0:
			$status_word = "Draft";
			break;
		case 1:
			$status_word = "Ready";
			break;
		case 2:
			$status_word = "Sent";
			break;
	}
	$menu .="<tr>";
	$menu .="<td style='text-align:left'>{$newsletter['subject']}</td>";
	$menu .= "<td style='text-align:center'>{$status_word}</td>";
	$menu .="<td style='text-align:center'>{$newsletter['date_created']}</td>";
	$menu .="<td style='text-align:center'>{$newsletter['date_updated']}</td>";
	
	
	$menu .="<td style='text-align:center'>
		<a href='newsletter_edit.php?newsid={$newsletter['id']}'><img class='tipTop' title='Edit' src='../../images/edit.png' alt='Edit'></a>
		
		<a  href='#' class='deleteNewsletter' rel='{$newsletter['id']}'><img class='tipTop' title='Permanently delete this Newsletter' src='../../images/delete.png' alt='Delete'></a>
		</td></tr>";
	}
	return $menu;
}

function send_email_attachments($to, $subject, $message, $attachment_files)
{
	global $_config;
	$from = $_config['newsletter'];
	$alt_boundary="ALT_".md5(mt_rand());
	$mix_boundary="MIX_".md5(mt_rand());
	
	// chunk the attachments and set the attachment array
	foreach($attachment_files as $att_file) $attachments[] = build_attachment_chunks($att_file['filename']);

	$plain =  wordwrap(convert_html_to_text(htmlspecialchars_decode($message)), 70);
	$html = wrap_content($subject, $message);
		
	$headers = "MIME-Version: 1.0\n".
	"From: \"{$from['name']}\" <{$from['email']}>" ."\n".
	"Reply-To: {$from['email']}" ."\n".
	"Content-Type: multipart/mixed;" ."\n".
	" boundary=\"{$mix_boundary}\"\n";

	$msg = "--{$mix_boundary}\nContent-Type: multipart/alternative; boundary=\"{$alt_boundary}\"\n\n--{$alt_boundary}\nContent-Type: text/plain; charset=iso-8859-1\nContent-Transfer-Encoding: 8bit\n\n{$plain}\n\n--{$alt_boundary}\nContent-Type: text/html; charset=iso-8859-1\nContent-Transfer-Encoding: 8bit\n\n{$html}\n--{$alt_boundary}--\n\n";
	
	foreach($attachments as $attachment)
	{
		$msg .= "--{$mix_boundary}\nContent-Type: {$attachment['mime']}; name=\"{$attachment['name']}\"\nContent-Transfer-Encoding: base64\nContent-Disposition: attachment\n\n{$attachment['chunks']}\n";
	}
	$msg .= "--{$mix_boundary}--";
	
	return mail($to['name'] . " <" .$to['email'].">", $subject, $msg, $headers); 
}

function build_attachment_chunks($filename)
{
	global $_config;
	
	$filepath = $_config['upload_path'].'attachments/original/'.$filename;
	
	$mime_type = mime_content_type ( $filepath  );
	return array(
		'name'		=> $filename,
		'chunks'	=> chunk_split(base64_encode(file_get_contents($filepath))),
		'mime'		=> $mime_type
	);
}

function wrap_content($subject, $content)
{
	return htmlspecialchars_decode("
	<html>
	<head>
		<title>{$subject}</title>
		<style type='text/css'>
			th, td {padding: 0 5px;}
			h1, h2, h3{    
				color: #378B36;
				font-size: 16px;
				font-weight: normal;
				line-height: 24px;
				margin: 0 0 8px;
				text-transform: uppercase;
			}
			img,
			embed,
			object,
			video {
			  max-width: 100%;
			  height: auto;
			  border:0 none;
			}
			body {width:100%;}
			.bordered { 
				border:solid 1px #b9b9b9;
				padding: .1em;
				background-color:#eee;
				margin-left:2.5%;
				margin-bottom: 2.5%;
				text-align: center;
			}
		</style>
	</head>
	<body>
		{$content}
	</body>
	</html>
	");
}

function get_attachments ()
{
	return logged_query_assoc_array("SELECT * FROM `newsletter_attachments`");
}

// returns an email mime safe(for TO: FROM: fields) version (ASCII:No ")
function email_safe($string)
{
	$string = ascii_only($string);
	return str_replace('"', '', $string);
}


function ascii_only($string, $replacement = '')
{
	return preg_replace('/[^(\x20-\x7F)]*/',$replacement, $string);
}
function delete_attachement($id)
{
	global $_config;
	$query = "SELECT `filename` FROM `newsletter_attachments` WHERE id = '{$id}'";
	$result = logged_query_assoc_array($query); 
	if (! $result) return 'Error: file not found.';

	$filename = $result[0]['filename'];
	$type = strtolower(end(explode(".", $filename)));
	
	$query = "DELETE FROM newsletter_attachments WHERE id = '{$id}'";
	$result = logged_query($query); 
	if (! $result ) return 'Error: could not delete file.';
	
	
	if(mysql_affected_rows() > 0) 
	{
		//$pathType = $type == 'pdf' ? 'pdf/' : 'files/';
		@unlink($_config['upload_path'].'attachments/original/'.$filename);
		return 'success';
	}
	else return 'No such file.';
}

function delete_all_attachments()
{
	$tmp_attachments = get_attachments();

	if($tmp_attachments && count($tmp_attachments) > 0)
	{
		foreach ($tmp_attachments as $tmp_attachment)
		{
			delete_attachement($tmp_attachment['id']);
		}
	}
}

function get_temp_recip()
{
	return logged_query_assoc_array("SELECT * FROM `newsletter_tmp_recip`");
}

function build_recip_row($recip)
{
	echo "<div class='recip-row'><span class='list-email' title='{$recip['email']}'>{$recip['email']}</span><span class='list-name' title='{$recip['name']}'>{$recip['name']}</span><span class='recip-remove' rel='{$recip['id']}'></span></div>";
}

function remove_recipient($id)
{
	$id = mysql_real_escape_string($id);
	$query_success = logged_query("DELETE FROM `newsletter_tmp_recip` WHERE id='{$id}'");
	if ($query_success) return "success";
	else return "Could not remove address from Additional Recipients list";
}

function remove_all_recipients()
{
	$query_success = logged_query("DELETE FROM `newsletter_tmp_recip`");
	if ($query_success) return "success";
	else return "Error clearing list";
}

function save_blank()
{
	$stuff = logged_query_assoc_array("SELECT @@session.time_zone");
	var_dump($stuff);
	logged_query("INSERT INTO `newsletter` () VALUES()");
		$stuff = logged_query_assoc_array("SELECT * from newsletter");
	var_dump($stuff);
	return mysql_insert_id();
}