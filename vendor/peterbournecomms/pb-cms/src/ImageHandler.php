<?php

    namespace PeterBourneComms\CMS;
    use PDO;
    use PDOException;
    use Exception;


    /**
     * Class to handle images - works with other classes to store filename in database etc. This just stores file in filesystem
     * Will create ImageResizer objects or library objects etc.
     *
     * @author Peter Bourne
     * @version 1.0
     *
     */
    class ImageHandler
    {
        protected $_source_filetype;
        protected $_dest_filetype;
        protected $_image_width;
        protected $_image_height;
        protected $_image_path;
        protected $_flag_thumbnails;
        protected $_thumb_width;
        protected $_thumb_height;
        protected $_image_filename;
        protected $_flag_maintain_transparency;


        public function __construct($path = USER_UPLOADS.'/images/', $flag_thumbnails = true)
        {
            //Assess passed path
            if (isset($path) && !is_string($path))
            {
                throw new Exception('Class ImageHandler requires path to be specified as a string, eg: /user_uploads/images/carousel/');
            }

            //See if provided path exists - if not - create it
            if (!file_exists(DOCUMENT_ROOT . $path))
            {
                //Create it
                $success = mkdir(DOCUMENT_ROOT . $path, 0777, true);
                if (!$success)
                {
                    throw new Exception('Directory specified ('.$path.') does not exist - and cannot be created');
                }
            }

            //Next check if thumbnail directories are needed (always: 'small' and 'large')
            if ($flag_thumbnails === true)
            {
                //Check paths exist - or create them
                $large = $path . "large/";
                $small = $path . "small/";

                if (!file_exists(DOCUMENT_ROOT . $large))
                {
                    //Create it
                    $success = mkdir(DOCUMENT_ROOT . $large, 0777, true);
                    if (!$success)
                    {
                        throw new Exception('Directory specified ('.$large.') does not exist - and cannot be created');
                    }
                }
                if (!file_exists(DOCUMENT_ROOT . $small))
                {
                    //Create it
                    $success = mkdir(DOCUMENT_ROOT . $small, 0777, true);
                    if (!$success)
                    {
                        throw new Exception('Directory specified ('.$small.') does not exist - and cannot be created');
                    }
                }
            }

            //Now store the properties
            $this->_image_path = $path;
            $this->_flag_thumbnails = $flag_thumbnails;

            //Store some defaults
            $this->_source_filetype = 'png';
            $this->_dest_filetype = 'jpg';
            $this->_flag_maintain_transparency = false;
            $this->_thumb_height = 180;
            $this->_thumb_width = 240;
            $this->_image_width = 1200;
            $this->_image_height = 440;
        }


        public function createFilename($root = '')
        {
            if ($this->_dest_filetype == '')
            {
                throw new Exception ('ImageHandler:; Can not create new filename as no dest_filetype set');
            }

            if ($root == '')
            {
                $filename = uniqid();
            }
            else
            {
                $filename = $root;
            }

            $NewFilename = new FilenameSanitiser($filename);
            $NewFilename->sanitiseFilename();
            $now = date('YmdHis');
            $new_filename = $NewFilename->getFilename();

            $filename = $now.$new_filename.".".$this->_dest_filetype;

            //Set the property
            $this->_image_filename = $filename;

            return $filename;
        }


        /**
         * Receive an image data stream
         *  - process the image based on path and size settings - (the dir gets created the first time the object is called)
         *  - store it in the file system
         *  - store it in the new object ahead of saving
         *  - save the object
         *
         * @param     $ImageStream
         *
         * @return mixed
         * @throws Exception
         */
        public function processImage($ImageStream)
        {
            //Check we have some useful data passed
            if ($ImageStream == '')
            {
                throw new Exception("You must supply a file stream to this function.");
            }

            switch ($this->_source_filetype)
            {
                case 'png':
                    $search_val = "data:image/png;base64,";
                    break;
                case 'jpg':
                    $search_val = "data:image/jpeg;base64,";
                    break;
                default:
                    die();
            }

            //Process image data
            $ImageStream = str_replace($search_val, '', $ImageStream);
            $ImageStream = str_replace(' ', '+', $ImageStream);
            $data = base64_decode($ImageStream);
            $unid = uniqid();
            $file_source = $unid;
            file_put_contents($file_source, $data); //Stores in the temp or current working directory

            //Sort out naming and path
            $MainPath = DOCUMENT_ROOT.$this->_image_path;

            //Do we have a filename already?
            if ($this->_image_filename == '')
            {
                $Filename = $this->createFilename();
            }
            $Filename = $this->getImgFilename();


            //Create the object
            $img = new ImageResizer($file_source, $this->_source_filetype);

            //Do we need thumbnails?
            if ($this->_flag_thumbnails === true)
            {
                //Yes we do
                $large = $MainPath."large/".$Filename;
                $small = $MainPath."small/".$Filename;
                $result = $img->resizeImage($large,$this->_dest_filetype, $this->_image_width, $this->_image_height, 100, $this->_flag_maintain_transparency,false);
                $img->resizeImage($small,$this->_dest_filetype, $this->_thumb_width, $this->_thumb_height, 100, $this->_flag_maintain_transparency,false);
            }
            else
            {
                $imgfile = $MainPath.$Filename;
                $result = $img->resizeImage($imgfile,$this->_dest_filetype, $this->_image_width, $this->_image_height, 100, $this->_flag_maintain_transparency,false);
            }

            //Now tidy up the tmp files
            unlink($file_source);

            return $result;

        }


        /**
         * Delete the image for this carousel item - assuming _image_filename is set
         *
         * @return mixed
         */
        public function deleteImage($filename = '')
        {
            if (!is_string($filename) || $filename == '')
            {
                $filename = $this->_image_filename;
            }
            if ($filename == '')
            {
                //throw new Exception ('ImageHandler requires a filename to be set if you are deleting an image');
                error_log("ImageHandler requires a filename to be set if you are deleting an image");
                return false;
            }

            //Does the filename exist?
            $delete_success = false;

            //Are we doing thumbnails?
            if ($this->_flag_thumbnails == true)
            {
                $large = DOCUMENT_ROOT.$this->_image_path."large/".$filename;
                $small = DOCUMENT_ROOT.$this->_image_path."small/".$filename;

                //echo "large file = ".$large."<br/>";

                if (file_exists($large))
                {
                    //remove the file
                    $delete_success = unlink($large);
                }
                if (file_exists($small))
                {
                    //remove the file
                    $delete_success = unlink($small);
                }
            }
            else
            {
                $img = DOCUMENT_ROOT.$this->_image_path.$filename;
                if (file_exists($img))
                {
                    //remove the file
                    $delete_success = unlink($img);
                }
            }

            if ($delete_success === true)
            {
                $this->_imgfilename = "";
                return true;
            }

            return false;
        }


        public function checkFileType($filetype)
        {
            if ($filetype != 'jpg' && $filetype != 'png')
            {
                return false;
            }
            else
            {
                return true;
            }
        }



        public function getSourceFileType()
        {
            return $this->_source_filetype;
        }

        public function setSourceFileType($source_filetype)
        {
            if ($this->checkFileType($source_filetype))
            {
                $this->_source_filetype = $source_filetype;
            };
        }

        public function getDestFileType()
        {
            return $this->_dest_filetype;
        }

        public function setDestFileType($dest_filetype)
        {
            if ($this->checkFileType($dest_filetype))
            {
                $this->_dest_filetype = $dest_filetype;
            }
        }

        public function getImageWidth()
        {
            return $this->_image_width;
        }

        public function setImageWidth($image_width)
        {
            $this->_image_width = $image_width;
        }

        public function getImageHeight()
        {
            return $this->_image_height;
        }

        public function setImageHeight($image_height)
        {
            $this->_image_height = $image_height;
        }

        public function getImagePath()
        {
            return $this->_image_path;
        }

        public function setImagePath($image_path)
        {
            $this->_image_path = $image_path;
        }

        public function getFlagThumbnails()
        {
            return $this->_flag_thumbnails;
        }

        public function setFlagThumbnails($flag_thumbnails)
        {
            $this->_flag_thumbnails = $flag_thumbnails;
        }

        public function getThumbWidth()
        {
            return $this->_thumb_width;
        }

        public function setThumbWidth($thumb_width)
        {
            $this->_thumb_width = $thumb_width;
        }

        public function getThumbHeight()
        {
            return $this->_thumb_height;
        }

        public function setThumbHeight($thumb_height)
        {
            $this->_thumb_height = $thumb_height;
        }

        public function getImgFilename()
        {
            return $this->_image_filename;
        }

        public function setImgFilename($image_filename)
        {
            $this->_image_filename = $image_filename;
        }

        public function getFlagMaintainTransparency()
        {
            return $this->_flag_maintain_transparency;
        }

        public function setFlagMaintainTransparency($flag_maintain_transparency)
        {
            $this->_flag_maintain_transparency = $flag_maintain_transparency;
        }


    }