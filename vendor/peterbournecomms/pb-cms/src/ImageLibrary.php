<?php

    namespace PeterBourneComms\CMS;
    use PDO;
    use PDOException;
    use Exception;


    /**
     * Class that deals with ImageLibraries for Content and Test Items etc. But could be extended to any parent content
     *
     * The Images table will be independent of parent - but will contain a field that indicates what type of content.
     * It will take the max size for both thumbnail and main image (image will be scaled down to this - whether width or height).
     *
     * The structure of the table shall be:
     *  - ID
     *  - ContentID
     *  - ContentParentTable (Test | Content | etc)
     *  - ImgFilename
     *  - ImgPath ?????
     *  - DisplayOrder
     *  - Caption
     *
     * Class needs to be able to:
     *  - Create individual image - based on sizes provided
     *  - Return image record
     *  - Specify the size of the image
     *  - specify the location of stored images
     *  - pass an image in and have it resized to that size
     *  - stored in the filesystem and database
     *  - retrieve an individual image item
     *  - delete image item (from db and filesystem)
     *  - Return array of all images for this content Type (Test | Content | etc)
     *  - also needs to be able to just update various elements - Caption and DisplayOrder
     *
     *  - DELETE ALL IMAGES (file and db record) for a particular contentID
     *
     *
     * @author Peter Bourne
     * @version 1.1		13.09.2021		TABLE_NAME modification in checkParent for mySQL 8 issue
     *
     */
    class ImageLibrary
    {
        /**
         * @var mixed
         */
        protected $_dbconn;
        /**
         * @var int|string
         */
        protected $_image_max;
        /**
         * @var int|string
         */
        protected $_thumb_max;
        /**
         * @var string
         */
        protected $_image_path;
        /**
         * @var int|string
         */
        protected $_id;
        /**
         * @var
         */
        protected $_content_id;
        protected $_content_parent_table;
        protected $_display_order;
        protected $_caption;
        protected $_title;
        /**
         * @var
         */
        protected $_imgfilename;


        public function __construct($contenttype, $contentid, $id = null, $imagemax = 1000, $thumbmax= 200, $path = USER_UPLOADS.'/images/gallery/')
        {
            //Connect to database
            if (!$this->_dbconn)
            {
                try
                {
                    $conn = new Database();
                    $this->_dbconn = $conn->getConnection();
                } catch (Exception $e)
                {
                    //handle the exception
                    die;
                }

                //Assess Contenttype and ID - they NEED to be provided
                if (!is_numeric($contentid) || $contentid <= 0 || $contentid == '')
                {
                    throw new Exception('Class ImageLibrary requires you to supply the ID of the Content item');
                }
                if ($contenttype == '' || !is_string($contenttype))
                {
                    throw new Exception(('Class ImageLibrary requires you to specify the table that the ContentID and this image relates to, eg: Test, Content etc.'));
                }

                //Assess passed id
                if ($id != null && !is_numeric($id))
                {
                    throw new Exception('Class ImageLibrary requires id to be specified as an integer');
                }

                //Assess passed maximum sizes
                if (($imagemax != null && !is_numeric($imagemax)) || ($thumbmax != null && !is_numeric($thumbmax)))
                {
                    throw new Exception('Class ImageLibrary requires image and thumbnail maximum sizes to be specified as integers');
                }

                //Assess passed path
                if (isset($path) && !is_string($path))
                {
                    throw new Exception('Class ImageLibrary requires path to be specified as a string, eg: /user_uploads/images/gallery/');
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
                //See if subdirectories for 'large' and 'small' images exist in the path specified - if not create them. If fail - Throw Exception
                //error_log('path = '.$path);
                $large = $path . "/large";
                $small = $path . "/small";
                if (!file_exists(DOCUMENT_ROOT . $large))
                {
                    //Create it
                    $success = mkdir(DOCUMENT_ROOT . $large, 0777, true);
                    if (!$success)
                    {
                        throw new Exception('Directory specified ('.DOCUMENT_ROOT.$large.') does not exist - and cannot be created');
                    }
                }
                if (!file_exists(DOCUMENT_ROOT . $small))
                {
                    //Create it
                    $success = mkdir(DOCUMENT_ROOT . $small, 0777, true);
                    if (!$success)
                    {
                        throw new Exception('Directory specified ('.DOCUMENT_ROOT.$small.') does not exist - and cannot be created');
                    }
                }

                //Store the properties
                $this->_id = $id;
                $this->_image_max = $imagemax;
                $this->_thumb_max = $thumbmax;
                $this->_image_path = $path;
                $this->_content_parent_table = $contenttype;
                $this->_content_id  = $contentid;

                //Check the table (and record) exists for the ParentContent
                if (!$this->checkParent())
                {
                    throw new Exception('Class ImageLibrary has found that the MemberDetails Content table specified, or the parent content ID specified - does not exist in the database. Table: '.$contenttype.' and ID: '.$contentid);
                }

                //Retrieve current Image information
                if (isset($id))
                {
                    $this->getImageById($id);
                }
            }

        }



        public function getImageById($id)
        {
            try
            {
                $stmt = $this->_dbconn->prepare("SELECT * FROM Images WHERE ID =  :id AND ContentID = :contentid AND ContentParentTable = :contenttable LIMIT 1");
                $stmt->execute([
                    'id' => $id,
                    'contentid' => $this->_content_id,
                    'contenttable' => $this->_content_parent_table
                               ]);
                $image = $stmt->fetch();
            } catch (Exception $e)
            {
                error_log("Failed to retrieve Content item details when searching by ID" . $e);
            }

            //Store details in relevant members
            $this->_id = $image['ID'];
            $this->_caption = $image['Caption'];
            $this->_imgfilename = $image['ImgFilename'];
            $this->_display_order = $image['DisplayOrder'];
            if ($this->_display_order <= 0)
            {
                $this->_display_order = 1000;
            }
        }



        /**
         * Receive an image data stream
         *  - process the image based on path and size settings - using large and small as subdirs for file storage (these get created the first time the object is called)
         *  - store it in the file system
         *  - store it in the new object ahead of saving
         *
         * @param     $ImageStream
         *
         * @throws Exception
         */
        public function processImage($ImageStream)
        {
            //Check we have some useful data passed
            if ($ImageStream == '')
            {
                throw new Exception("You must supply a file stream (data:image/jpeg;base64) to this function.");
            }

            $ImageStream = str_replace('data:image/jpeg;base64,', '', $ImageStream);
            //$img = str_replace('data:image/jpeg;base64,', '', $img);
            $ImageStream = str_replace(' ', '+', $ImageStream);
            $data = base64_decode($ImageStream);
            $unid = uniqid();
            $file_jpg = $unid;  //At this stage its actually a PNG file from the Darkroom Canvas object
            file_put_contents($file_jpg, $data);

            //Sort out naming
            $NewFilename = new FilenameSanitiser($unid."_".$this->_caption);
            $NewFilename->sanitiseFilename();
            $now = date('YmdHis');
            $filename = $NewFilename->getFilename();
            $filename = $now."_".$filename.".jpg";

            $NewPath = DOCUMENT_ROOT.$this->_image_path;

            $large = $NewPath."large/".$filename;
            $small = $NewPath."small/".$filename;

            $img = new ImageResizer($file_jpg, 'jpg');
            $img->resizeImage($large,'jpg', $this->_image_max, null, 100, false,true);
            $img->resizeImage($small,'jpg', $this->_thumb_max, null, 100, false,true);

            //Delete the old image
            $this->deleteImage();

            //Store the new filename
            $this->_imgfilename = $filename;

            //Save the object in its current state
            $this->saveImageItem();

            //Now tidy up the tmp file
            unlink($file_jpg);
        }


        /**
         * Create new empty image item
         *
         * Sets the _id property accordingly
         */
        public function createImageItem()
        {
            //Only execute if the _id property isn't already set
            if (is_numeric($this->_id) || $this->_id > 0)
            {
                throw new Exception('You cannot create a new Image item at this stage - the id property is already set as '.$this->_id);
            }

            //Create DB item
            try
            {
                $result = $this->_dbconn->prepare("INSERT INTO Images SET ContentID = :contentid, ContentParentTable = :contentparenttable");
                $result->execute([
                    'contentid' => $this->_content_id,
                    'contentparenttable' => $this->_content_parent_table
                                 ]);
                $lastID = $this->_dbconn->lastInsertId();

                if ($lastID > 0)
                {
                    $this->_id = $lastID;
                }
                else
                {
                    throw new Exception('Unable to create new Image item');
                }
            }
            catch (Exception $e) {
                error_log("Failed to create new Image record: " . $e);
            }
        }


        /**
         * Saves the current object to the Images table in the database
         *
         * @throws Exception
         */
        public function saveImageItem()
        {
            //First need to determine if this is a new Image item
            if ($this->_id <= 0)
            {
                $this->createImageItem(); //_id should now be set
            }

            try {
                $stmt = $this->_dbconn->prepare("UPDATE Images SET ContentID = :contentid, ContentParentTable = :contentparenttable, Caption = :caption, ImgFilename = :imgfilename, ImgPath = :imgpath, DisplayOrder = :displayorder WHERE ID = :id LIMIT 1");
                $stmt->execute([
                                   'contentid' => $this->_content_id,
                                   'contentparenttable' => $this->_content_parent_table,
                                   'caption' => $this->_caption,
                                   'imgfilename' => $this->_imgfilename,
                                   'imgpath' => $this->_image_path,
                                   'displayorder' => $this->_display_order,
                                   'id' => $this->_id
                               ]);
                if ($stmt->rowCount() == 1) {
                    return true;
                } else {
                    //print_r($stmt->errorInfo());
                    return false;
                }
            } catch (Exception $e) {
                error_log("Failed to save Image record: " . $e);
            }
        }


        /**
         * Delete the image file for this Image - assuming _img_filename is set
         *
         * @return mixed
         */
        public function deleteImage()
        {
            if (!is_string($this->_imgfilename))
            {
                return "Sorry - there was no image to delete";
            }

            //Does the filename exist?
            $delete_complete = false;
            if (file_exists(DOCUMENT_ROOT.$this->_image_path."large/".$this->_imgfilename))
            {
                //remove the file
                $delete_large = unlink(DOCUMENT_ROOT.$this->_image_path."large/".$this->_imgfilename);
            }
            if (file_exists(DOCUMENT_ROOT.$this->_image_path."small/".$this->_imgfilename))
            {
                //remove the file
                $delete_small = unlink(DOCUMENT_ROOT.$this->_image_path."small/".$this->_imgfilename);
            }
            if ($delete_large || $delete_small)
            {
                $delete_complete = true;
                $this->_imgfilename = "";
                $this->saveImageItem();
                return true;
            }
        }


        /**
         * Delete the complete Image item - including any images
         *
         * @return mixed
         */
        public function deleteItem()
        {
            if (!is_numeric($this->_id))
            {
                throw new Exception('Class ImageLibrary requires the Image item ID to be set if you are trying to delete the item');
            }

            //First delete the image files
            $this->deleteImage();

            //Now delete the item from the DB
            try {
                $stmt = $this->_dbconn->prepare("DELETE FROM Images WHERE ID = :id LIMIT 1");
                $result = $stmt->execute([
                                             'id' => $this->_id
                                         ]);
            } catch (Exception $e) {
                error_log("Failed to delete Image record: " . $e);
            }

            if ($result === true)
            {
                //Unset the properties
                $this->_id = null;
                $this->_caption = null;
                $this->_imgfilename = null;
                $this->_displayorder = null;
                $this->_content_parent_table = null;
                $this->_content_id = null;

                return true;
            }
            else
            {
                return false;
            }

        }


        /**
         * Returns an array of all Images in the table specified in the function call - with the same ContentParentID
         * Used by pages to output the image library typically
         *
         * @param $contentid
         * @param $contenttable
         *
         * @return mixed
         */
        public function retrieveAllImages($contenttable = null,$contentid = null)
        {
            if ($contenttable == '')
            {
                $contenttable = $this->_content_parent_table;
            }
            if ($contentid <= 0)
            {
                $contentid = $this->_content_id;
            }

            //Table and parent check first
            if ($this->checkParent($contenttable,$contentid))
            {
                //echo "Found table and id";
                //Proceed - the table and parent content do exist!
                $images = $this->_dbconn->prepare("SELECT * FROM Images WHERE ContentID = :contentid AND ContentParentTable = :contenttable ORDER BY DisplayOrder ASC");
                $images->execute([
                    'contentid' => $contentid,
                    'contenttable' => $contenttable
                                 ]);
                $ret_arr = array();
                while($thisimage = $images->fetch())
                {
                    $thisimage['FullImage'] = $this->_image_path."large/".$thisimage['ImgFilename'];
                    $thisimage['ThumbImage'] = $this->_image_path."small/".$thisimage['ImgFilename'];
                    $ret_arr[] = $thisimage;
                }
                return $ret_arr;
            }

            return false;
        }


        /**
         * Function that checks the Object's $_property_id and $_media_type_id to see if they exist in the DB
         */
        private function checkParent($contenttable = null, $parentid = null)
        {
            //Populate with object properties if nothing supplied - then check there is soemthing present!
            if ($contenttable == '')
            {
                $contenttable = $this->_content_parent_table;
            }
            if ($parentid <= 0)
            {
                $parentid = $this->_content_id;
            }

            //Now check for valid values
            if ($contenttable == '')
            {
                throw new Exception('Sorry - ImageLibrary requires the checkParent function to be passed a table reference - or for it to be set in the object already');
            }
            if ($parentid <= 0)
            {
                throw new Exception('Sorry - ImageLibrary requires the checkParent function to be passed a content parent ID - or for it to be present in the object already');
            }

            //Get complete list of tables
            $tables = $this->_dbconn->query("SELECT TABLE_NAME FROM information_schema.tables WHERE table_schema = '" . DB_NAME ."'");
            $tables_rs = $tables->fetchAll(PDO::FETCH_ASSOC);
            $tables_present = array();
            foreach($tables_rs as $row)
            {
                $tables_present[] = $row['TABLE_NAME'];
            }

            if (in_array($contenttable,$tables_present))
            {
                //$tablename = $tablesRS[$this->_media_type_id]
                //Now check for the actual record

                $stmt = $this->_dbconn->prepare("SELECT ID FROM ".$contenttable." WHERE ID = :id LIMIT 1");
                if ($ID = $parentid)
                {
                    return true;
                }
            }
            return false;
        }




        public function deleteAllImagesForContent($contenttable = null,$contentid = null)
        {
            $arr_all_images = $this->retrieveAllImages($contenttable,$contentid);
            if (is_array($arr_all_images) && count($arr_all_images) > 0)
            {
                foreach($arr_all_images as $image)
                {
                    $IO = new ImageLibrary($image['ContentParentTable'],$image['ContentID'],$image['ID']);
                    $IO->deleteItem();
                }
            }
        }



        public function getID()
        {
            return $this->_id;
        }

        public function setID($id)
        {
            $this->_id = $id;
        }

        public function getImgFilename()
        {
            return $this->_imgfilename;
        }

        public function setImgFilename($imgfilename)
        {
            $this->_imgfilename = $imgfilename;
        }

        public function getCaption()
        {
            return $this->_caption;
        }

        public function setCaption($caption)
        {
            $this->_caption = $caption;
        }

        public function getDisplayOrder()
        {
            return $this->_display_order;
        }

        public function setDisplayOrder($display_order)
        {
            if ($display_order <= 0) { $display_order = 1000; }
            $this->_display_order = $display_order;
        }

        public function getContentID()
        {
            return $this->_content_id;
        }

        public function setContentID($content_id)
        {
            $this->_content_id = $content_id;
        }

        public function getContentType()
        {
            return $this->_content_parent_table;
        }

        public function setContentType($content_parent_table)
        {
            $this->_content_parent_table = $content_parent_table;
        }
    }