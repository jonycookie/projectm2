<?php
//******************************************************//
//           /////////                 ////   /////
//              //                    // //  //  //
//             //      /////  ////// ////   ////
//            //      //     ////   //  // //  //
//        /////////  /////  ////// /////  /////
//******************************************************//
// icebb.net // 1.0
//******************************************************//
// CAPTCHA image generation module
// $Id: make_image.php 339 2006-07-10 18:02:15Z mutantmonkey $
//******************************************************//

class captcha
{
	// main configuration
	var $width					= 200;
	var $height					= 50;
	
	var $captcha_id;
	var $correct_word;
	var $fonts;

	/**
	 * Constructor
	 */
	function captcha()
	{
		global $icebb,$db,$std;
		
		// load code from URL
		$this->captcha_id		= wash_key($_GET['s']);

		$result					= $db->query("SELECT * FROM icebb_captcha WHERE id='{$this->captcha_id}'");// AND ip='{$icebb->user['ip']}'");
		
		$r						= $db->fetch_row($result);
		$wordNum				= $r['word_num'];
		
		// open up dictionary and choose a word
		$words					= @file("langs/{$icebb->lang_id}/captcha.dict");
		
		$this->correct_word		= $words[$wordNum];
		$this->correct_word		= str_replace("\n",'',$this->correct_word);
		
		if($db->get_num_rows($result)<=0)
		{
			$this->correct_word	= "ERROR";
		}
		
		if($icebb->settings['img_engine']=='imagemagick')
		{
			$this->load_fonts();
			$im_image			= new captcha_imagemagick(&$this);
			return $im_image->imagemagick_make();
		}
		else {
			$this->load_fonts();
			$gd_image			= new captcha_gd(&$this);
			return $gd_image->gd_make();
		}
	}
	
	function load_fonts()
	{
		global $icebb;
	
		$dir						= "skins/{$icebb->skin->skin_id}/images/captcha/fonts/";
		if($dh = @opendir($dir))
		{
			while(false !== ($file = @readdir($dh)))
			{
				if(stristr($file,'.ttf') !== false)
				{
					$this->fonts[]	= "{$dir}{$file}";
				}
			}
			@closedir($dh);
		}
	}
}

/**
 * Word verification using ImageMagick
 */
class captcha_imagemagick extends captcha
{
	var $im_convert_path	= '/usr/bin/convert';

	var $rand_bgs			= array('plasma:grey-pink','plasma:grey-grey','plasma:pink-red','plasma:steelblue-white','plasma:pink-white');
	var $fonts				= array('Courier','Helvetica','Helvetica-Narrow','Palatino-Roman','Times-Roman');

	/**
	 * Constructor
	 * @param 		object		Calling class
	 */
	function captcha_imagemagick($parent)
	{
		global $icebb;
	
		$this->captcha		= &$parent;
		
		$this->im_convert_path= $icebb->settings['imagemagick_convert_path'];
	
		$which_to_use		= rand(0,count($this->rand_bgs)-1);
		$this->bgcolor		= $this->rand_bgs[$which_to_use];
		$this->fillcolor	= "\"rgb(".mt_rand(0,150).",".mt_rand(0,150).",".mt_rand(0,150).")\"";
		
		$this->font			= $this->captcha->fonts[mt_rand(0,count($this->captcha->fonts)-1)];
		
		return $this->imagemagick_make();
	}
	
	/**
	 * Nice and simple. Generates the image using the width, background color, fill color, font,
	 * and correct word set above.
	 */
	function imagemagick_make()
	{
		// adjust for border
		$w					= $this->captcha->width-2;
		$h					= $this->captcha->height-2;
		
		$convert[]			= "-size {$w}x{$h} null: -matte";
		$convert[]			= "-font {$this->font} -fill {$this->fillcolor} -pointsize 32";
		$convert[]			= "-annotate +".mt_rand(5,45)."+".mt_rand(25,45)." '{$this->captcha->correct_word}'";

		$randy				= mt_rand(0,3);
		switch($randy)
		{
			case 0:
				$convert[]	= '-swirl '.mt_rand(30,55);
				break;
			case 1:
				$convert[]	= '-swirl '.mt_rand(20,40);
				$convert[]	= '-background none -wave '.mt_rand(1,3).'x'.mt_rand(60,70);
				break;
			case 2:
				$convert[]	= '-background none -wave '.mt_rand(3,5).'x'.mt_rand(40,60);
				break;
			case 3:
				$convert[]	= '-swirl '.mt_rand(20,40).' -spread .'.rand(3,7);
				break;
		}
		
		$cmds				= implode(' ',$convert);
		@exec("{$this->im_convert_path} {$cmds} uploads/{$this->captcha->captcha_id}.png");
		$convert			= array();
		
		$convert[]			= "-size {$w}x{$h} {$this->bgcolor}";// -quality 75";
		$convert[]			= "uploads/{$this->captcha->captcha_id}.png";
		$convert[]			= "-gravity center -composite";
		$convert[]			= "-fill {$this->fillcolor} -draw \"path 'M ".rand(0,25).",".rand(0,25)." L ".rand(150,200).",".rand(40,45)." M ".rand(0,25).",".rand(10,50)." L ".rand(150,200).",".rand(10,50)."'\"";
		$convert[]			= '-bordercolor black -border 1';

		$cmds				= implode(' ',$convert);
		@exec("{$this->im_convert_path} {$cmds} uploads/{$this->captcha->captcha_id}.png");
		//echo "convert {$cmds} {$this->captcha->captcha_id}.png";exit();
		
		@header('Content-type: image/png');
		echo @file_get_contents("uploads/{$this->captcha->captcha_id}.png");
		
		// remove temporary image
		@unlink("uploads/{$this->captcha->captcha_id}.png");
	}
}

/**
 * Word verification using GD
 */
class captcha_gd extends captcha
{
	// configuration
	var $strikeTexth			= false;
	var $strikeTextv			= false;
	var $showGrid				= false;
	var $ultraDistort			= false;
	
	var $noisyImg				= true;
	var $noise_type				= 2;
	var $noise_amount			= 400;
	var $noise_color			= '130,130,130';
	
	var $rotateImg				= false;
	
	var $add_color				= false;
	var $add_vertical_lines		= false;
	var $add_blur				= true;
	
	var $bg_color				= '255,255,255';
	var $text_color				= '100,100,100';
	
	var $format					= 'jpeg';
	
	// when using a TTF, you may want to use a darker font color
	//var $ttfFile				= 'modules/login/palr46w.ttf';

	/**
	 * Constructor
	 * @param 		object		Calling class
	 */
	function captcha_gd($parent)
	{
		global $icebb,$std;
		
		$this->captcha		= &$parent;
		
		if(!function_exists('jpeg'))
		{
			$this->format		= 'png';
		}
	
		$this->width			= $this->captcha->width;
		$this->height			= $this->captcha->height;
	
		$this->noise_color		= rand(100,160).','.rand(100,160).',255';//'.rand(100,160);
		$this->noise_color		= explode(',',$this->noise_color);
		//$this->bg_color			= rand(250,255).','.rand(250,255).','.rand(250,255);
		$this->bg_color			= explode(',',$this->bg_color);	
		$this->text_color		= explode(',',$this->text_color);
		
		// can we use a TTF?
		if(function_exists('imagettftext'))
		{
			$this->ttfFile		= $this->captcha->fonts[mt_rand(0,count($this->captcha->fonts)-1)];
		}
	}
	
	/**
	 * This one's a little big longer. It's some really old code I wrote a long time ago, but
	 * it's a bit more flexible than the ImageMagick code above because it's been changed so much.
	 */
	function gd_make()
	{
		global $icebb,$std;
		
		// create image
		$im						= @imagecreate($this->width,$this->height);
			
		// allocate colors
		$background_color		= imagecolorallocate($im,$this->bg_color[0],$this->bg_color[1],$this->bg_color[2]);
		$lgray					= imagecolorallocate($im,220,220,220);
		$gray					= imagecolorallocate($im,200,200,200);
		$dgray					= imagecolorallocate($im,180,180,180);
		$black					= imagecolorallocate($im,0,0,0);
		$white					= imagecolorallocate($im,255,255, 255);
		$text					= imagecolorallocate($im,$this->text_color[0],$this->text_color[1],$this->text_color[2]);

		imagefill($im,0,0,$background_color);

		imageline($im, 0, 0, 200, 0, $lgray);
		imageline($im, 0, 49, 200, 49, $lgray);
		imageline($im, 0, 0, 0, 50, $lgray);
		imageline($im, 199, 0, 199, 50, $lgray);
		
		for($i=0;$i<=10;$i++)
		{
			$main_color[$i]	= imagecolorallocate($im,mt_rand(100,255),mt_rand(100,255),mt_rand(100,255));
		}
	
		for($x=1;$x<=$this->captcha->width;$x++)
		{
			for($y=1;$y<=$this->captcha->height;$y++)
			{
				imagesetpixel($im,$x,$y,$main_color[mt_rand(0,10)]);
			}
		}
		
		if($this->add_color==true && function_exists('imagefilledellipse'))
		{
			$rcolor_start			= 200;
			$rcolor_end				= 255;
		
			$rand_color				= imagecolorallocate($im,rand($rcolor_start,$rcolor_end),rand($rcolor_start,$rcolor_end),rand($rcolor_start,$rcolor_end));
			imagefilledellipse($im,50,50,80,120,$rand_color);
			imagefilledellipse($im,150,50,80,120,$rand_color);
			
			$rand_color2			= imagecolorallocate($im,rand($rcolor_start,$rcolor_end),rand($rcolor_start,$rcolor_end),rand($rcolor_start,$rcolor_end));
			imagefilledellipse($im,100,50,80,120,$rand_color2);		
		}
		
		if($this->ultraDistort	== true)
		{
			// create text in background to fool ocr
			$captchaBgNum		= mt_rand(1000000,9999999);
			imagestring($im, 3, 20, 5,  $captchaBgNum.' '.$captchaBgNum.' '.$captchaBgNum, $lgray);
			imagestring($im, 3, 20, 10,  $captchaBgNum.' '.$captchaBgNum.' '.$captchaBgNum, $gray);
			imagestring($im, 3, 20, 15,  $captchaBgNum.' '.$captchaBgNum.' '.$captchaBgNum, $dgray);
			imagestring($im, 3, 20, 20,  $captchaBgNum.' '.$captchaBgNum.' '.$captchaBgNum, $edgray);
			imagestring($im, 3, 20, 25,  $captchaBgNum.' '.$captchaBgNum.' '.$captchaBgNum, $dgray);
			imagestring($im, 3, 20, 30,  $captchaBgNum.' '.$captchaBgNum.' '.$captchaBgNum, $gray);
			imagestring($im, 3, 20, 35,  $captchaBgNum.' '.$captchaBgNum.' '.$captchaBgNum, $lgray);
		}	
		
		if($this->noisyImg==true)
		{
			$i					= 0;
			$noisy_color		= imagecolorallocate($im,$this->noise_color[0],$this->noise_color[1],$this->noise_color[2]);
		
			if($this->noise_type== 1)
			{
				// create some noise
				while($i<$this->noise_amount)
				{
					$x=rand(0,200);		$x2=rand(0,200);
					$y=rand(0,30);		$y2=rand(0,50);
		
					imageline($im,$x2,$y2,$x,$y,$noisy_color);
					imageline($im,$x,$y,$x2,$y2,$noisy_color);
					$i++;
				}
			}
			else if($this->noise_type== 2)
			{
				// create some noise - granier than above
				while($i<$this->noise_amount)
				{
					$x=rand(0,200);		$x2=rand(0,200);
					$y=rand(0,50);		$y2=rand(0,50);
		
					imageline($im,$x+1,$y+1,$x,$y,$noisy_color);
					imageline($im,$x2+1,$y2+1,$x2,$y2,$noisy_color);
					$i++;
				}
			}
		}
		
		if($this->ttfFile!='')
		{
			imagettftext($im,20,rand(-10,10),30,30,$text,$this->ttfFile,$this->captcha->correct_word);
		}
		else {
			$tempim				= @imagecreatetruecolor($this->width,$this->height*2);
			$tblack				= imagecolorallocate($tempim,0,0,0);
			$tweiss				= imagecolorallocate($tempim,255,255,255);
			$ttext				= imagecolorallocate($tempim,$this->text_color[0],$this->text_color[1],$this->text_color[2]);
			$tnone				= imagecolorallocate($tempim,200,200,200);
			
			imagecolortransparent($tempim,$tnone);
			imagefill($tempim,0,0,$tnone);
			imagestring($tempim,5,rand(2,6),rand(0,2),$this->captcha->correct_word,$ttext);
			
			//$tempim				= imagerotate($tempim,rand(-3,3),$tnone);
			imagecolortransparent($tempim,$tnone);
			imagecopyresized($im,$tempim,5,0,0,0,$this->width,$this->height,$this->width/2,($this->height/2)-($this->height/8));
			//imagecopymerge($im,$tempim,0,0,0,0,$this->width,$this->height,90);
			
			imagedestroy($tempim);
			
			// remove blur
			//$this->add_blur		= 0;
		}

		// adds horizontal lines through text
		if($this->strikeTexth==true)
		{
			imageline($im, 0, 6, 200, 6, $white);
			imageline($im, 0, 8, 200, 8, $white);
			imageline($im, 0, 10, 200, 10, $white);
			imageline($im, 0, 12, 200, 12, $white);
			imageline($im, 0, 14, 200, 14, $white);
			imageline($im, 0, 16, 200, 16, $white);
			imageline($im, 0, 18, 200, 18, $white);
			imageline($im, 0, 20, 200, 20, $white);
			imageline($im, 0, 22, 200, 22, $white);
			imageline($im, 0, 24, 200, 24, $white);
			imageline($im, 0, 26, 200, 26, $white);
			imageline($im, 0, 28, 200, 28, $white);
			imageline($im, 0, 30, 200, 30, $white);
			imageline($im, 0, 32, 200, 32, $white);
			imageline($im, 0, 34, 200, 34, $white);
			imageline($im, 0, 36, 200, 36, $white);
			imageline($im, 0, 38, 200, 38, $white);
			imageline($im, 0, 40, 200, 40, $white);
			imageline($im, 0, 42, 200, 42, $white);
			imageline($im, 0, 44, 200, 44, $white);
			imageline($im, 0, 46, 200, 46, $white);
			imageline($im, 0, 48, 200, 48, $white);
		}
		
		// adds vertical lines through text
		if($this->strikeTextv==true)
		{
			imageline($im, 6, 0, 6, 6, $white);
			imageline($im, 8, 0, 8, 8, $white);
			imageline($im, 10, 0, 10, 50, $white);
			imageline($im, 12, 0, 12, 50, $white);
			imageline($im, 14, 0, 14, 50, $white);
			imageline($im, 16, 0, 16, 50, $white);
			imageline($im, 18, 0, 18, 50, $white);
			imageline($im, 20, 0, 20, 50, $white);
			imageline($im, 22, 0, 22, 50, $white);
			imageline($im, 24, 0, 24, 50, $white);
			imageline($im, 26, 0, 26, 50, $white);
			imageline($im, 28, 0, 28, 50, $white);
			imageline($im, 30, 0, 30, 50, $white);
			imageline($im, 32, 0, 32, 50, $white);
			imageline($im, 34, 0, 34, 50, $white);
			imageline($im, 36, 0, 36, 50, $white);
			imageline($im, 38, 0, 38, 50, $white);
			imageline($im, 40, 0, 40, 50, $white);
			imageline($im, 42, 0, 42, 50, $white);
			imageline($im, 44, 0, 44, 50, $white);
			imageline($im, 46, 0, 46, 50, $white);
			imageline($im, 48, 0, 48, 50, $white);
			imageline($im, 50, 0, 50, 50, $white);
			imageline($im, 52, 0, 52, 50, $white);
			imageline($im, 54, 0, 54, 50, $white);
			imageline($im, 56, 0, 56, 50, $white);
			imageline($im, 58, 0, 58, 50, $white);
			imageline($im, 60, 0, 60, 50, $white);
			imageline($im, 62, 0, 62, 50, $white);
			imageline($im, 64, 0, 64, 50, $white);
			imageline($im, 66, 0, 66, 50, $white);
			imageline($im, 68, 0, 68, 50, $white);
			imageline($im, 70, 0, 70, 50, $white);
			imageline($im, 72, 0, 72, 50, $white);
			imageline($im, 74, 0, 74, 50, $white);
			imageline($im, 76, 0, 76, 50, $white);
			imageline($im, 78, 0, 78, 50, $white);
			imageline($im, 80, 0, 80, 50, $white);
			imageline($im, 82, 0, 82, 50, $white);
			imageline($im, 84, 0, 84, 50, $white);
			imageline($im, 86, 0, 86, 50, $white);
			imageline($im, 88, 0, 88, 50, $white);
			imageline($im, 90, 0, 90, 50, $white);
			imageline($im, 92, 0, 92, 50, $white);
			imageline($im, 94, 0, 94, 50, $white);
			imageline($im, 96, 0, 96, 50, $white);
			imageline($im, 98, 0, 98, 50, $white);
			imageline($im, 106, 0, 106, 6, $white);
			imageline($im, 108, 0, 108, 8, $white);
			imageline($im, 110, 0, 110, 50, $white);
			imageline($im, 112, 0, 112, 50, $white);
			imageline($im, 114, 0, 114, 50, $white);
			imageline($im, 116, 0, 116, 50, $white);
			imageline($im, 118, 0, 118, 50, $white);
			imageline($im, 120, 0, 120, 50, $white);
			imageline($im, 122, 0, 122, 50, $white);
			imageline($im, 124, 0, 124, 50, $white);
			imageline($im, 126, 0, 126, 50, $white);
			imageline($im, 128, 0, 128, 50, $white);
			imageline($im, 130, 0, 130, 50, $white);
			imageline($im, 132, 0, 132, 50, $white);
			imageline($im, 134, 0, 134, 50, $white);
			imageline($im, 136, 0, 136, 50, $white);
			imageline($im, 138, 0, 138, 50, $white);
			imageline($im, 140, 0, 140, 50, $white);
			imageline($im, 142, 0, 142, 50, $white);
			imageline($im, 144, 0, 144, 50, $white);
			imageline($im, 146, 0, 146, 50, $white);
			imageline($im, 148, 0, 148, 50, $white);
			imageline($im, 150, 0, 150, 50, $white);
			imageline($im, 152, 0, 152, 50, $white);
			imageline($im, 154, 0, 154, 50, $white);
			imageline($im, 156, 0, 156, 50, $white);
			imageline($im, 158, 0, 158, 50, $white);
			imageline($im, 160, 0, 160, 50, $white);
			imageline($im, 162, 0, 162, 50, $white);
			imageline($im, 164, 0, 164, 50, $white);
			imageline($im, 166, 0, 166, 50, $white);
			imageline($im, 168, 0, 168, 50, $white);
			imageline($im, 170, 0, 170, 50, $white);
			imageline($im, 172, 0, 172, 50, $white);
			imageline($im, 174, 0, 174, 50, $white);
			imageline($im, 176, 0, 176, 50, $white);
			imageline($im, 178, 0, 178, 50, $white);
			imageline($im, 180, 0, 180, 50, $white);
			imageline($im, 182, 0, 182, 50, $white);
			imageline($im, 184, 0, 184, 50, $white);
			imageline($im, 186, 0, 186, 50, $white);
			imageline($im, 188, 0, 188, 50, $white);
			imageline($im, 190, 0, 190, 50, $white);
			imageline($im, 192, 0, 192, 50, $white);
			imageline($im, 194, 0, 194, 50, $white);
			imageline($im, 196, 0, 196, 50, $white);
			imageline($im, 198, 0, 198, 50, $white);
		}
		
		// create grid (helps fool ocr)
		if($this->showGrid==true)
		{
			imageline($im, 10, 0, 10, 50, $lgray);
			imageline($im, 20, 0, 20, 50, $lgray);
			imageline($im, 30, 0, 30, 50, $lgray);
			imageline($im, 40, 0, 40, 50, $lgray);
			imageline($im, 50, 0, 50, 50, $lgray);
			imageline($im, 60, 0, 60, 50, $lgray);
			imageline($im, 70, 0, 70, 50, $lgray);
			imageline($im, 80, 0, 80, 50, $lgray);
			imageline($im, 90, 0, 90, 50, $lgray);
			imageline($im, 100, 0, 100, 50, $lgray);
			imageline($im, 110, 0, 110, 50, $lgray);
			imageline($im, 120, 0, 120, 50, $lgray);
			imageline($im, 130, 0, 130, 50, $lgray);
			imageline($im, 140, 0, 140, 50, $lgray);
			imageline($im, 150, 0, 150, 50, $lgray);
			imageline($im, 160, 0, 160, 50, $lgray);
			imageline($im, 170, 0, 170, 50, $lgray);
			imageline($im, 180, 0, 180, 50, $lgray);
			imageline($im, 190, 0, 190, 50, $lgray);
			imageline($im, 0, 10, 200, 10, $lgray);
			imageline($im, 0, 20, 200, 20, $lgray);
			imageline($im, 0, 30, 200, 30, $lgray);
			imageline($im, 0, 40, 200, 40, $lgray);
			imageline($im, 0, 50, 200, 50, $lgray);
		}
		
		if($this->rotateImg==true && function_exists('imagerotate'))
		{
			// degrees to rotate?
			$deg		= rand(-5,5);
			
			// fix 0 degree rotation
			if($deg	       == 0)
			{
				$deg	= rand(1,2);
			}
			
			// rotate the image
			$img2		= imagerotate($im,$deg,0);
			
			// resize to it's normal square size
			imagecopyresized($im,$img2,0,0,0,0,imagesx($im),imagesy($im),imagesx($img2),imagesy($img2));
			imagedestroy($img2);
		}

		if($this->add_vertical_lines==true)
		{
			for($i=0;$i<$this->width;$i=$i+5)
			{
				imageline($im,$i,0,$i,$this->width,$lgray);
			}
		}
		
		if($this->add_blur==true)
		{
			if(function_exists('imagefilter'))
			{
				@imagefilter($this->im,IMG_FILTER_GAUSSIAN_BLUR);
			}
			else {
				// Old fashioned blur... (PHP 4 sucks!)
				$img3			= @imagecreate($this->width,$this->height);
				imagefill($img3,0,0,imagecolorallocate($img3,$this->bg_color[0],$this->bg_color[1],$this->bg_color[2]));
				imagecopymerge($img3,$im,0,1,0,0,$this->width,$this->height,70);
				imagecopymerge($im,$img3,0,0,1,0,$this->width,$this->height,70);
				imagecopymerge($img3,$im,1,0,0,0,$this->width,$this->height,70);
				imagecopymerge($im,$img3,0,0,0,1,$this->width,$this->height,70);
				imagedestroy($img3);
			}
		}
		
		if($this->ultraDistort==true)
		{
			// just some added distortion, thought it looked nice
			for($i=0;$i<$this->height;$i=$i+5)
			{
				imageline($im,0,$i,$this->width,$this->height,$dgray);
			}
		}
		
		// border
		imageline($im,0,0,$this->captcha->width,0,$black);
		imageline($im,0,($this->captcha->height)-1,$this->captcha->width,($this->captcha->height)-1,$black);
		imageline($im,0,0,0,$this->captcha->height,$black);
		imageline($im,($this->captcha->width)-1,0,($this->captcha->width)-1,$this->captcha->height,$black);

		//imagejpeg($im,'',25);
		@header("Content-type: image/{$this->format}");
		($this->format=='jpeg') ? imagejpeg($im) : imagepng($im);
		imagedestroy($im);
	}
}

$captcha_da = new captcha();
?>
