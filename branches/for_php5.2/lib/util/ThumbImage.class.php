<?php
class ThumbImage {
	/**
	 * 生成缩略图
	 * @param 源图片路径 $srcFile
	 * @param 缩略图路径 $dstFile
	 * @param 缩略图宽度 $dstW
	 * @param 缩略图高度 $dstH
	 */
	static function create($srcFile, $dstFile, $dstW, $dstH, $fill = false)
	{
		$data = GetImageSize($srcFile);
		switch ($data[2]) {
			case 1:
				$srcImg = @ImageCreateFromGIF($srcFile);
				break;
			case 2:
				$srcImg = @ImageCreateFromJPEG($srcFile);
				break;
			case 3:
				$srcImg = @ImageCreateFromPNG($srcFile);
				break;
			default:
				return;
				break;
		}
		if (!$srcImg) {
			return;
		}
		
		$srcW = ImageSX($srcImg);
		$srcH = ImageSY($srcImg);
		$dstX = 0;
		$dstY = 0;
		
		if ($srcW * $dstH > $srcH * $dstW) {// srcW/srcH > dstW/dstH ，源比目标扁
			$dstRealW = max($dstW, 1);
			$dstRealH = max(round($srcH * $dstRealW / $srcW), 1);
		} else {
			$dstRealH = max($dstH, 1);
			$dstRealW = max(round($srcW * $dstRealH / $srcH), 1);
		}
		
		if ($fill) {
			$dstX = floor(($dstW - $dstRealW) / 2);
			$dstY = floor(($dstH - $dstRealH) / 2);
			$dstImg = ImageCreateTrueColor($dstW, $dstH);
			$backColor = ImageColorAllocate($dstImg, 255, 255, 255);//缩图空出部分的背景色
			ImageFilledRectangle($dstImg, 0, 0, $dstW, $dstH, $backColor);
		} else {
			$dstImg = ImageCreateTrueColor($dstRealW, $dstRealH);	
		}
		
		
		//ImageCopyResized($dstImg, $srcImg, 0, 0, 0, 0, $dstRealW, $dstRealH, $srcW, $srcH);
		imagecopyresampled($dstImg, $srcImg, $dstX, $dstY, 0, 0, $dstRealW, $dstRealH, $srcW, $srcH);
		ImageJPEG($dstImg, $dstFile, 85);
		chmod($dstFile, 0644);
		ImageDestroy($srcImg);
		ImageDestroy($dstImg);
	}
}