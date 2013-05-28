<?php

namespace Egzakt\SystemBundle\Lib\GregwarImage;

ini_set('memory_limit', '512M');

use Gregwar\ImageBundle\ImageHandler as BaseImageHandler;

/**
 * Image manipulation class
 */
class ImageHandler extends BaseImageHandler
{
    /**
     * Resize the image and crops it in the middle
     *
     * @param int $w the width of the crop box
     * @param int $h the height of the crop box
     */
    public function _centeredCrop($w, $h)
    {
        $actualWidth = $this->width();
        $actualHeight = $this->height();
        $actualRatio = $actualWidth / $actualHeight;
        $destRatio = $w / $h;

        if ($actualRatio == $destRatio) {
            $destWidth = $actualWidth;
            $destHeight = $actualHeight;
            $x = 0;
            $y = 0;

        } elseif ($actualRatio > $destRatio) {
            $destWidth = $destRatio / $actualRatio * $actualWidth;
            $destHeight = $actualHeight;
            $x = ($actualWidth - $destWidth) / 2;
            $y = 0;

        } else {
            $destWidth = $actualWidth;
            $destHeight = $actualRatio / $destRatio * $actualHeight;
            $x = 0;
            $y = ($actualHeight - $destHeight) / 2;
        }

        $this->_crop($x, $y, $w, $h, $destWidth, $destHeight);
    }

    /**
     * Crops the image
     *
     * @param int $x         the top-left x position of the crop box
     * @param int $y         the top-left y position of the crop box
     * @param int $w         the width of the crop box
     * @param int $h         the height of the crop box
     * @param int $srcWidth  the width of the source
     * @param int $srcHeight the height of the source
     */
    public function _crop($x, $y, $w, $h, $srcWidth = null, $srcHeight = null)
    {
        $destCanvas = imagecreatetruecolor($w, $h);

        // For transparency
        imagealphablending($destCanvas, false);
        imagesavealpha($destCanvas, true);
        $transparent = imagecolorallocatealpha($destCanvas, 255, 255, 255, 127);
        imagefilledrectangle($destCanvas, 0, 0, $w, $h, $transparent);

        // Resize and crop
        imagecopyresampled($destCanvas, $this->gd, 0, 0, $x, $y, $w, $h, $srcWidth, $srcHeight);

        imagedestroy($this->gd);
        $this->gd = $destCanvas;
    }

    /**
     * Resize the image to a maximum width or height depending on whether the image is horizontal or vertical
     *
     * @param int $max
     */
    public function _max($max)
    {
        if ($this->width() >= $this->height()) {
            $this->_cropResize($max, null, 'transparent');
        } else {
            $this->_cropResize(null, $max, 'transparent');
        }
    }
}

