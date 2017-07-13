<?php
// TODO enable attachments, source is from the newsletter module, need to look closer to do attachments
class email {
	protected $from;		// who emails are from; 
							//	array('name' => [name of person it's from], 
							//		  'email' => [email address (used in return email too)])
							
	public function __construct($from)
    {
		$this->from = array('name' => $this->email_safe($from['name']), 'email' => $this->email_safe($from['email']));
			
	}
	

	// returns an email mime safe(for TO: FROM: fields) version (ASCII:No ")
	public function email_safe($string)
	{
		$string = $this->ascii_only($string);
		return str_replace('"', '', $string);
	}
	
	public function send($to, $subject, $message, $attachments = array())
	{	
		// TODO NEED TO MODIFY TO ENABLE ATTACHMENTS
		$attachments = array();
		$to['name'] = $this->email_safe($to['name']);
		$to['email'] = $this->email_safe($to['email']);
		if ( $attachments && count($attachments) > 0 ) return $this->send_email_attachments($to, $subject, $message, $attachments);
		else return $this->send_html_plain_email($to, $subject, $message);
	}

	protected function send_html_plain_email($to, $subject, $message)
	{
		global $_config;
		$from = $this->from;
		$boundary="BOUNDARY_".md5(mt_rand());
		$plain =  wordwrap(convert_html_to_text(htmlspecialchars_decode($message)), 70);
		$html = $this->wrap_content($subject, $message);
			
		$headers = "MIME-Version: 1.0\n".
		"From: \"{$from['name']}\" <{$from['email']}>" ."\n".
		"Reply-To: {$from['email']}" ."\n".
		"Content-Type: multipart/alternative;" ."\n".
		" boundary=\"{$boundary}\"\n";

		$msg = "--{$boundary}\nContent-Type: text/plain; charset=iso-8859-1\nContent-Transfer-Encoding: 8bit\n\n{$plain}\n--{$boundary}\nContent-Type: text/html; charset=iso-8859-1\nContent-Transfer-Encoding: 8bit\n\n{$html}\n--{$boundary}--";

		return mail($to['name'] . " <" .$to['email'].">", $subject, $msg, $headers);
	}


	protected function wrap_content($subject, $content)
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

	protected function ascii_only($string, $replacement = '')
	{
		return preg_replace('/[^(\x20-\x7F)]*/',$replacement, $string);
	}
	

	############# TODO  reinstate attachments ############
	protected function send_email_attachments($to, $subject, $message, $attachment_files)
	{
		$from = $this->from;
		$alt_boundary="ALT_".md5(mt_rand());
		$mix_boundary="MIX_".md5(mt_rand());
		
		// chunk the attachments and set the attachment array
		foreach($attachment_files as $att_file) $attachments[] = build_attachment_chunks($att_file['filename']);

		$plain =  wordwrap(convert_html_to_text(htmlspecialchars_decode($message)), 70);
		$html = $this->wrap_content($subject, $message);
			
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

	protected function build_attachment_chunks($filename)
	{
		global $_config;
		
$filepath = $_config['upload_path'].'attachments/original/'.$filename;
		
		$mime_type = $this->get_mime ( $filepath  );
		return array(
			'name'		=> $filename,
			'chunks'	=> chunk_split(base64_encode(file_get_contents($filepath))),
			'mime'		=> $mime_type
		);
	}
	
	protected function get_mime($filename)
	{
		if(!function_exists('mime_content_type')) return $this->mime_content_type($filename);
		return mime_content_type($filename);
	}
	
	protected function mime_content_type($filename) {

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

// include the required html_to_text function
include_once($_config['admin_includes'].'html2text.php');