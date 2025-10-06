<?php
    
    namespace PeterBourneComms\CMS;
    
    use PDO;
    use PDOException;
    use Exception;
    
    
    /**
     * Class to resize images provided as a filesystem link
     *
     * It will have options to resize and change the format (maintaining transparency etc)
     *
     * @author Peter Bourne
     * @version 1.2
     *
     *
     * @history
     * 1.0      --------        Original
     * 1.1      17.11.2023      Added GetimageSize method - to return array of image size
     * 1.2      12.06.2024      Added various fixes for PHP 8.x
     *
     */
    class ImageResizer
    {
        protected $_originalFilePath;
        protected $_newFilePath;
        protected $_originalImgType;
        
        /**
         * ImageResizer constructor.
         *
         * @param $originalFilePath
         * @param $originalFileType
         *
         * @throws Exception
         */
        public function __construct($originalFilePath, $originalFileType)
        {
            if ($originalFilePath != '') {
                //determine if the file exists - if it does set its property
                if (file_exists($originalFilePath)) {
                    $this->_originalFilePath = $originalFilePath;
                } else {
                    throw new Exception('Sorry - you passed a file to ImageResizer that doesn\'t exist');
                }
            } else {
                throw new Exception('Sorry - ImageResizer needs one parameter: originalfilepath');
            }
            
            if ($originalFileType == '' || ($originalFileType != 'jpg' && $originalFileType != 'png')) {
                throw new Exception('You need to set a FileType of \'jpg\' or \'png\'with ImageResizer');
            } else {
                $this->_originalImgType = $originalFileType;
            }
        }
        
        
        /**
         * Function to resize the current image to the specified format. Needs params:
         *
         * @param string $newFilePath The new complete filepath (complete path for the host filesystem)
         * @param string $newType png | jpg
         * @param int $newW Width of new image in pixels
         * @param int $newH Height of new image in pixels
         * @param int $qual Quality setting (1-10 for PNG, 1-100 for JPG)
         * @param bool $flagMaintainTrans Maintain transparency flag (only relevant for PNGs)
         * @param bool $flagGallery If this is true we only use the $newW value as a new Maximum size for the image (whether vertical or portrait)
         *
         * @return mixed
         * @throws Exception
         */
        public function resizeImage($newFilePath, $newType, $newW, $newH, $qual = 100, $flagMaintainTrans = true, $flagGallery = false)
        {
            //June 2024 addition
            if ($newType == '') { $newType = 'jpg'; }
            
            //Check we have all the required information
            if ($newFilePath == '' && ($newType != 'png' || $newType != 'jpg') && (!is_numeric($newW) && $newW <= 0) && ($flagGallery != true && !is_numeric($newH) && $newH <= 0) && (!is_numeric($qual) && $qual < 0)) {
                //Missing data
                throw new Exception("Please ensure that you provide all required parameters: newFilePath, newType, newW, newH (if not a gallery resize) and qual");
            }
            
            //Gallery rotate image as required
            if ($flagGallery === true) {
                $this->adjustPicOrientation($this->_originalFilePath);
            }
            
            
            switch ($this->_originalImgType) {
                case 'png':
                    $im_source = imagecreatefrompng($this->_originalFilePath);
                    break;
                case 'jpg':
                    $im_source = imagecreatefromjpeg($this->_originalFilePath);
                    break;
                default:
                    throw new Exception("You must specify png or jpg as the new file type.");
            }
            
            //Get Source size
            $im_details = getimagesize($this->_originalFilePath);
            $source_w = $im_details[0];
            $source_h = $im_details[1];
            $source_aspect_ratio = $source_w / $source_h;
            
            // Gallery resize - or normal image resize - they are treated differently:
            // If its a Gallery item - we look at the image - and then we resize its LONGEST edge to the value specified in newW (ignoring newH)
            if ($flagGallery === true) {
                if ($source_aspect_ratio > 1) {
                    $temp_width = $newW;
                    $temp_height = $newW / $source_aspect_ratio;
                } else {
                    $temp_width = $newW * $source_aspect_ratio;
                    $temp_height = $newW;
                }
                //Need to set width and height - as they won't have ben passed into the function
                $newW = $temp_width;
                $newH = $temp_height;
            } else {
                //Set Desired size
                $desired_aspect_ratio = $newW / $newH;
                
                if ($source_aspect_ratio > $desired_aspect_ratio) {
                    $temp_height = $newH;
                    $temp_width = ( int )($newH * $source_aspect_ratio);
                } else {
                    $temp_width = $newW;
                    $temp_height = ( int )($newW / $source_aspect_ratio);
                }
            }
            
            //June 2024 addition for PHP 8 compatibility
            $temp_height = (int)$temp_height;
            $temp_width = (int)$temp_width;
            $newW = (int)$newW;
            $newH = (int)$newH;
            
            //Copy to temp image
            $temp_gdim = imagecreatetruecolor($temp_width, $temp_height);
            
            //Now look at transparency for PNG
            if ($this->_originalImgType == 'png' && $flagMaintainTrans === true) {
                imagealphablending($temp_gdim, false);
                imagesavealpha($temp_gdim, true);
                $transparent = imagecolorallocatealpha($temp_gdim, 255, 255, 255, 127);
                imagefilledrectangle($temp_gdim, 0, 0, $temp_width, $temp_height, $transparent);
            } else {
                imagesavealpha($temp_gdim, false);
                $bg = imagecolorallocate($temp_gdim, 255, 255, 255);
                imagefilledrectangle($temp_gdim, 0, 0, $temp_width, $temp_height, $bg);
            }
            //Create temp image at max dimensions - may still need cropping
            imagecopyresampled(
                $temp_gdim,
                $im_source,
                0, 0,
                0, 0,
                $temp_width, $temp_height,
                $source_w, $source_h
            );
            
            //Copy cropped region from temp image to new image (created at top - could be JPEG could be PNG)
            $x0 = ($temp_width - $newW) / 2;
            $y0 = ($temp_height - $newH) / 2;
            $im = imagecreatetruecolor($newW, $newH);
            
            //Now look at transparency for PNG
            if ($newType == 'png' && $flagMaintainTrans === true) {
                imagealphablending($im, false);
                imagesavealpha($im, true);
                $transparent = imagecolorallocatealpha($im, 255, 255, 255, 127);
                imagefilledrectangle($im, 0, 0, $newW, $newH, $transparent);
            } else {
                imagesavealpha($im, false);
                $bg = imagecolorallocate($im, 255, 255, 255);
                imagefilledrectangle($im, 0, 0, $newW, $newH, $bg);
            }
            //Create the cropped version
            imagecopy(
                $im,
                $temp_gdim,
                0, 0,
                $x0, $y0,
                $newW, $newH
            );
            
            
            //Prepare quality setting
            if ($newType == 'png') {
                $qual = floor($qual);
                if ($qual > 9) {
                    $qual = 9;
                }
            } elseif ($newType == 'jpg') {
                $qual = floor($qual);
                if ($qual > 100) {
                    $qual = 100;
                }
            }
            
            //Output the file
            if ($newType == 'png') {
                //PNG
                $result = imagepng($im, $newFilePath, $qual);
            } elseif ($newType == 'jpg') {
                //JPG
                $result = imagejpeg($im, $newFilePath, $qual);
            }
            
            
            //Destroy all our images
            imagedestroy($im);
            imagedestroy($temp_gdim);
            imagedestroy($im_source);
            
            return $result;
        }
        
        private function adjustPicOrientation($full_filename)
        {
            
            $exif = exif_read_data($full_filename);
            //print_r($exif);
            if ($exif && isset($exif['Orientation'])) {
                $orientation = $exif['Orientation'];
                if ($orientation != 1) {
                    $img = imagecreatefromjpeg($full_filename);
                    
                    $mirror = false;
                    $deg = 0;
                    
                    switch ($orientation) {
                        case 2:
                            $mirror = true;
                            break;
                        case 3:
                            $deg = 180;
                            break;
                        case 4:
                            $deg = 180;
                            $mirror = true;
                            break;
                        case 5:
                            $deg = -90;
                            $mirror = true;
                            break;
                        case 6:
                            $deg = -90;
                            break;
                        case 7:
                            $deg = 90;
                            $mirror = true;
                            break;
                        case 8:
                            $deg = 90;
                            break;
                    }
                    if ($deg) $img = imagerotate($img, $deg, 0);
                    if ($mirror) $img = $this->_mirrorImage($img);
                    $full_filename = str_replace('.jpg', "-O$orientation.jpg", $full_filename);
                    imagejpeg($img, $full_filename, 100);
                }
            }
            return $full_filename;
        }
        
        
        public function getImageSize(): array
        {
            $im_details = getimagesize($this->_originalFilePath);
            $source_w = $im_details[0];
            $source_h = $im_details[1];
            $source_aspect_ratio = $source_w / $source_h;
            
            $ret_arr =  array('Width' => $source_w, 'Height' => $source_h, 'AspectRatio' => $source_aspect_ratio);
            return $ret_arr;
        }
        
        
        
        
        //GETTERS AND SETTERS

        private function _mirrorImage($imgsrc)
        {
            $width = imagesx($imgsrc);
            $height = imagesy($imgsrc);
            
            $src_x = $width - 1;
            $src_y = 0;
            $src_width = -$width;
            $src_height = $height;
            
            $imgdest = imagecreatetruecolor($width, $height);
            
            if (imagecopyresampled($imgdest, $imgsrc, 0, 0, $src_x, $src_y, $width, $height, $src_width, $src_height)) {
                return $imgdest;
            }
            
            return $imgsrc;
        }
        
        public function getOriginalFilePath()
        {
            return $this->_originalFilePath;
        }
        
        public function setOriginalFilePath($originalFilePath)
        {
            $this->_originalFilePath = $originalFilePath;
        }
        
        public function getNewFilePath()
        {
            return $this->_newFilePath;
        }
        
        public function setNewFilePath($newFilePath)
        {
            $this->_newFilePath = $newFilePath;
        }
        
        private function checkImageType()
        {
            $this->getImageType();
            
            if ($this->_originalImgType != 'IMAGETYPE_JPEG' || $this->_originalImgType != 'IMAGETYPE_PNG') {
                throw new Exception('Sorry - the ImageResizer class needs to work with PNG or JPEG files.');
            } else {
                return true;
            }
        }
        
        private function getImageType()
        {
            $type = exif_imagetype($this->_originalFilePath);
            
            if ($type == '' || $type === false) {
                throw new Exception('Sorry - the filetype cannot be determined.');
            } else {
                $this->_originalImgType = $type;
            }
        }
        
        
    }