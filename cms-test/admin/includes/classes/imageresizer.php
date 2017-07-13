<?php
/* Image Re-Sizer */
class imageResizer {
	public $image,$image_type,$image_info;
	public $maxLimit = 1073741824; // 10MiB this is the maximum amount of RAM that the resizer is allowed to consume

	public function load ($filename) {
		$image_info = getimagesize($filename, $info);
		$this->image_type = $image_info[2];
		$this->image_info = $image_info;
$changeIn = memory_get_usage();		
		if($this->image_type == IMAGETYPE_JPEG) { 
			if ($this->exceedsMemoryLimit()) return false;
			$this->image = imagecreatefromjpeg($filename); 
		}
		elseif($this->image_type == IMAGETYPE_GIF) { 
			if ($this->exceedsMemoryLimit()) return false;
			$this->image = imagecreatefromgif($filename); 
		}
		elseif($this->image_type == IMAGETYPE_PNG) { 
			if ($this->exceedsMemoryLimit()) return false;
			$this->image = imagecreatefrompng($filename); 
		}
		else return false;
		//	my_log(memory_get_usage() - $changeIn);
		//	my_log('real change above');
		return true;
	}

	public function save($filename,$image_type = IMAGETYPE_JPEG,$compression = 75,$permissions = null) {
		if($image_type == IMAGETYPE_JPEG) { imagejpeg($this->image,$filename,$compression); }
		elseif($image_type == IMAGETYPE_GIF) { imagegif($this->image,$filename); }
		elseif($image_type == IMAGETYPE_PNG) { imagepng($this->image,$filename); }
		if($permissions != null) { chmod($filename,$permissions); }
	}

	public function output($image_type = IMAGETYPE_JPEG) {
		if($image_type == IMAGETYPE_JPEG) { imagejpeg($this->image); }
		elseif($image_type == IMAGETYPE_GIF) { imagegif($this->image); }
		elseif($image_type == IMAGETYPE_PNG) { imagepng($this->image); }
	}
	
	public function exceedsMemoryLimit($toWidth = 0, $toHeight = 0)
	{
		if(! $this->image_info[0] || ! $this->image_info[1]) return true;
		
		
		if(isset($this->image_info['channels']))
		{
			// this is a jpg: do jpg calculations
			$memoryNeeded = (int) round( (
				($this->image_info[0] * $this->image_info[1] + $toWidth * $toHeight ) 
				* $this->image_info['bits']  
				* $this->image_info['channels'] / 8
				+ 65536 //overhead
				) * 1.8 // tweak factor
			); 
		}
		else 
		{
			
			// png calculations
			$memoryNeeded = round (
				($this->image_info[0] * $this->image_info[1] + $toWidth * $toHeight ) 
				* $this->image_info['bits'] 
				*1.8 //tweak factor
			);
		}
	
		
		
		$curLimit = ((int) ini_get('memory_limit'))*(1024*1024);
		
		
	
		$totNeeded = (int) memory_get_usage() + (int) $memoryNeeded;
	
		if($totNeeded > $this->maxLimit )
		{
			return true;
			
		}
		elseif($totNeeded > $curLimit)
		{
			ini_set('memory_limit',$totNeeded);
		}
		
		
		
		
// my_log("estimated usage: " . $memoryNeeded);
		return false;
	}
	
	public function getWidth() {
		return imagesx($this->image);
	}

	public function getHeight() {
		return imagesy($this->image);
	}

	   public function resizeToHeight($height) {
		  $ratio = $height / $this->getHeight();
		  $width = $this->getWidth() * $ratio;
		  return $this->resize($width,$height);
	   }

	   public function resizeToWidth($width) {
		  $ratio = $width / $this->getWidth();
		  $height = $this->getheight() * $ratio;
		  return $this->resize($width,$height);
	   }
		
		// assumes image has width and height values > 0
		public function findDimensionToChange($maxWidth, $maxHeight){
		
			$width = $this->getWidth();
			$height = $this->getHeight();
			if ($width > $maxWidth){
				if ($height > $maxHeight){
					// figure out ratios and use the most appropriate one
					$whratio = $width / $height;
					$heightConstraint = $height * $whratio - $maxHeight * $maxWidth / $maxHeight;
					if ($maxWidth < $maxHeight * $whratio)
					{
						return "width";
					}
					else return "height";
				}
				else {
					// $width is the limiting factor
					return "width";
				}
			}
			else if ($height > $maxHeight) {
				// height is the limiting factor
				return "height";
			}
			return false;
		}


	   public function scale($scale) {
		  $width = $this->getWidth() * $scale/100;
		  $height = $this->getheight() * $scale/100;
		  $this->resize($width,$height);
	   }

	   public function resize($final_width,$final_height) {
		
		$image_resized = imagecreatetruecolor( $final_width, $final_height );
		$image = $this->image;  
	   
		if ( ($this->image_type == IMAGETYPE_GIF) || ($this->image_type == IMAGETYPE_PNG) ) {
		  
		  $trnprt_indx = imagecolortransparent($image);
	   
		  // If we have a specific transparent color
		  if ($trnprt_indx >= 0) {
	   
			// Get the original image's transparent color's RGB values
			$trnprt_color    = imagecolorsforindex($image, $trnprt_indx);
	   
			// Allocate the same color in the new image resource
			$trnprt_indx    = imagecolorallocate($image_resized, $trnprt_color['red'], $trnprt_color['green'], $trnprt_color['blue']);
	   
			// Completely fill the background of the new image with allocated color.
			imagefill($image_resized, 0, 0, $trnprt_indx);
	   
			// Set the background color for new image to transparent
			imagecolortransparent($image_resized, $trnprt_indx);
	   
		 
		  }
		  // Always make a transparent background color for PNGs that don't have one allocated already
		  elseif ($this->image_type == IMAGETYPE_PNG) {
	   
			// Turn off transparency blending (temporarily)
			imagealphablending($image_resized, false);
	   
			// Create a new transparent color for image
			$color = imagecolorallocatealpha($image_resized, 0, 0, 0, 127);
			
			// Completely fill the background of the new image with allocated color.
			imagefill($image_resized, 0, 0, $color);
	   
			// Restore transparency blending
			imagesavealpha($image_resized, true);
		  }
		}
if($this->exceedsMemoryLimit($final_width, $final_height)) return false;

		$result = imagecopyresampled($image_resized, $image, 0, 0, 0, 0, $final_width, $final_height, $this->getWidth(), $this->getHeight());
		$this->image = $image_resized;
		return $result;
	   }
		
		// assumes final_height is less than original height (can't crop something to be larger than original)
		public function cropHeight($final_height) {
			$final_width = $this->getWidth();
			
			$image_resized = imagecreatetruecolor( $final_width, $final_height );
			$image = $this->image;  
		   
			if ( ($this->image_type == IMAGETYPE_GIF) || ($this->image_type == IMAGETYPE_PNG) ) {
			  
			  $trnprt_indx = imagecolortransparent($image);
		   
			  // If we have a specific transparent color
			  if ($trnprt_indx >= 0) {
		   
				// Get the original image's transparent color's RGB values
				$trnprt_color    = imagecolorsforindex($image, $trnprt_indx);
		   
				// Allocate the same color in the new image resource
				$trnprt_indx    = imagecolorallocate($image_resized, $trnprt_color['red'], $trnprt_color['green'], $trnprt_color['blue']);
		   
				// Completely fill the background of the new image with allocated color.
				imagefill($image_resized, 0, 0, $trnprt_indx);
		   
				// Set the background color for new image to transparent
				imagecolortransparent($image_resized, $trnprt_indx);
		   
			 
			  }
			  // Always make a transparent background color for PNGs that don't have one allocated already
			  elseif ($this->image_type == IMAGETYPE_PNG) {
		   
				// Turn off transparency blending (temporarily)
				imagealphablending($image_resized, false);
		   
				// Create a new transparent color for image
				$color = imagecolorallocatealpha($image_resized, 0, 0, 0, 127);
				
				// Completely fill the background of the new image with allocated color.
				imagefill($image_resized, 0, 0, $color);
		   
				// Restore transparency blending
				imagesavealpha($image_resized, true);
			  }
			}
if($this->exceedsMemoryLimit($final_width, $final_height)) return false;
			$result = imagecopyresampled($image_resized, $image, 0, 0, 0, ($this->getHeight() - $final_height) / 2, $final_width, $final_height, $final_width, $final_height);
			$this->image = $image_resized;
			return $result;

		}
	
	
		// assumes final_height is less than original height (can't crop something to be larger than original)
		public function cropWidth($final_width) {
			$final_height = $this->getHeight();
			
			$image_resized = imagecreatetruecolor( $final_width, $final_height );
			$image = $this->image;  
		   
			if ( ($this->image_type == IMAGETYPE_GIF) || ($this->image_type == IMAGETYPE_PNG) ) {
			  
			  $trnprt_indx = imagecolortransparent($image);
		   
			  // If we have a specific transparent color
			  if ($trnprt_indx >= 0) {
		   
				// Get the original image's transparent color's RGB values
				$trnprt_color    = imagecolorsforindex($image, $trnprt_indx);
		   
				// Allocate the same color in the new image resource
				$trnprt_indx    = imagecolorallocate($image_resized, $trnprt_color['red'], $trnprt_color['green'], $trnprt_color['blue']);
		   
				// Completely fill the background of the new image with allocated color.
				imagefill($image_resized, 0, 0, $trnprt_indx);
		   
				// Set the background color for new image to transparent
				imagecolortransparent($image_resized, $trnprt_indx);
		   
			 
			  }
			  // Always make a transparent background color for PNGs that don't have one allocated already
			  elseif ($this->image_type == IMAGETYPE_PNG) {
		   
				// Turn off transparency blending (temporarily)
				imagealphablending($image_resized, false);
		   
				// Create a new transparent color for image
				$color = imagecolorallocatealpha($image_resized, 0, 0, 0, 127);
				
				// Completely fill the background of the new image with allocated color.
				imagefill($image_resized, 0, 0, $color);
		   
				// Restore transparency blending
				imagesavealpha($image_resized, true);
			  }
			}
if($this->exceedsMemoryLimit($final_width, $final_height)) return false;
			$result = imagecopyresampled($image_resized, $image, 0, 0, ($this->getWidth() - $final_width) / 2, 0, $final_width, $final_height, $final_width, $final_height);
			$this->image = $image_resized;
			return $result;

		}
}