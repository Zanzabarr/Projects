<?php
class String 
{
	// limits the number of characters output by text... attempts to account for paragraph spacing in html
	public static function truncate(&$text, $length, $lineLength = 50, $paraSpace = 50,  $suffix = '...')
	{ 
		$i = 0; 
		$tags = array(); 
		 
			//will be parms
			$lineLength = 50;   // this is an estimate of how many chars fills an average line
			$paraSpace	= 50;	// estimate of number of chars the trailing space of a paragraph uses
								//	normally this is the same as line length(single space, but could be double or half or whatever)
		
			$parasWithTags 	= array();
			$parasNoTags 	= array();
			$charCount 		= 0;
			$tooLong 		= false;
			$lastParaOffset	= 0;		// this is the starting character position of the paragraph that needs to be trimmed
			
			// get rid of tables
			$text = preg_replace(array('@(<table.*</table>)@'), array(''),$text);
			
			//$text = preg_replace(array('@(<p>)|(<p\s[^>]*>)@', '@</p>@'), array('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;', '<br />'),$text);
			// strip all most tags
			$text = strip_tags($text, '<a><p><br>');
			
			// explode on the paragraph closing tags to get array of paragraphs
			$parasWithTags = explode('</p>', $text);

			// find the last one!
			$curIndex = 0;
			foreach ($parasWithTags as $para)
			{	
				
				$tmpPara = strip_tags($para); 
				$parasNoTags[] = $tmpPara;
				$paraCount = ceil(strlen($tmpPara)/$lineLength);
				
				$lastParaOffset = $charCount;
				$charCount += $paraCount * $lineLength + $paraSpace;
				
				// round to a full line
				if ($charCount > $length || ($charCount == $length && count($parasWithTags) > $curIndex ) ) 
				{
					$tooLong = true;
					break;
				}

				
				$curIndex +=1;
			}
			//echo $charCount;
			// if it isn't too long...we are done here!
			if (! $tooLong ) return false; // false indicates that the text wasn't shortened.
			
			// if the charCount exceeds our limit, the last paragraph in parasNoTags needs to be trimmed!
			// but we need to trim parasWithTags (counting the non-tag elements therein.
			$lastPara = $parasWithTags[count($parasNoTags)-1];
			// find the portion of the guilty paragraph that crosses the length threshold
			$arLastPara = preg_split('/<(.*?)>/s', $lastPara, -1, PREG_SPLIT_OFFSET_CAPTURE);
			//echo display_array($arLastPara);

		
			// grab the offending line
			$lastParaOutput = '';
			for ($i = 0; $i < count($arLastPara); $i++)
			{
				// if this is the last snip...this is it, (could be ending after text because of spacing after paragraph)
				// otherwise, check to see if this snip's length + offset plus para's offset is greater than length limit
				if ( $i == count($arLastPara) - 1 || strlen($arLastPara[$i][0]) + $arLastPara[$i][1] + $lastParaOffset >= $length )
				{
					
					// this is the guiltiest snip! Find the last valid whole word's ending position in the snip
					//echo strlen($arLastPara[$i][0]) .':'. $arLastPara[$i][1].':'. $lastParaOffset ."<br />".$arLastPara[$i][0]."<br />";
					
					// cut off the excess
					// lastletter = desired length - 
					//		(the length of the previous paragraph 
					//		+ length of this paragraph up to this point 
					//		+ the length of all previous paragraphs combined)
					$lastLetter = $length - ($lastParaOffset + $arLastPara[$i][1]);
					
					// if the last letter position is greater than the length of this snip, 
					//	then this is the end of a complete paragraph: cut position equals snip length
					// otherwise, cutposition is lastLetter
					// in either event, go back to the last space before this point (last whole word)
					
					$cutPointSnipPosn =  $arLastPara[$i][1];
					$cutPoint = strrpos(substr($arLastPara[$i][0], 0, (min($lastLetter, strlen($arLastPara[$i][0]) )) ), ' '  ); 
					//echo 'cutpoint = ' . $cutPoint . $arLastPara[$i][0][$cutPoint] . "<br />";
					//echo min($lastLetter, strlen($arLastPara[$i][0]) )."<br />";
					//echo $lastLetter . "<br />";
					
					$lastSnip = substr($arLastPara[$i][0], 0, $cutPoint) . $suffix;
					break;
				}
				
			}
			
			// output for all paras up to the last para (has tags)
			$i = 0;
			$output = '';
			while($i < $curIndex)
			{
				
				$output .= $parasWithTags[$i++];
			}
			
			// output for last para
			// split along the same delimiters but save the delimiters this time
			$arLastParaOutput = preg_split('/(<.*?>)/s', $lastPara, -1, PREG_SPLIT_OFFSET_CAPTURE|PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
			
			// output the last para
			for($i = 0; $i < count($arLastParaOutput) ; $i++)
			{
				$curSnipStart = $arLastParaOutput[$i][1];
				// output all tags/text before final snip
				if ( $curSnipStart < $cutPointSnipPosn )
					$output .= $arLastParaOutput[$i][0];
				// output the last snip
				elseif ($curSnipStart == $cutPointSnipPosn)
					$output .= $lastSnip;
				// output only the tags (no text) after the last snip
				elseif ($curSnipStart > $cutPointSnipPosn && $arLastParaOutput[$i][0][0] == "<")
					$output .= $arLastParaOutput[$i][0];
			}
			
			$text = $output;
			
			/*
			preg_match_all('/<[^>]+>([^<]*)/', $text, $m, PREG_OFFSET_CAPTURE | PREG_SET_ORDER); 
			foreach($m as $o)
			{ 
				if($o[0][1] - $i >= $length) break; 
				$t = substr(strtok($o[0][0], " \t\n\r\0\x0B>"), 1); 
				if($t[0] != '/') $tags[] = $t; 
				elseif(end($tags) == substr($t, 1)) array_pop($tags); 
				
				$i += $o[1][1] - $o[0][1]; 
			} 
			*/
		
		return $tooLong;
	} 
} 