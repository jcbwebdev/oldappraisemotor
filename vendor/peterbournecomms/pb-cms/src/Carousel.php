<?php

    namespace PeterBourneComms\CMS;
    use PDO;
    use PDOException;
    use Exception;


    /**
     * Deals with image Carousels
     *
     * It will allow you to
     *  - specify the size of the carousel
     *  - specify the location of stored images
     *  - pass an image in and have it resized to that size
     *  - stored in the filesystem and database - along with caption, title and link
     *  - retrieve an individual carousel item
     *  - retrieve an array of all carousel items
     *  - delete a carousel item
     *
     * Relies on the Carousel table in this structure:
     *  ID
     *  ImgFilename
     *  Title
     *  Content
     *  CTALabel (if button used)
     *  CTALink (if button used)
     *  DisplayOrder (standard ascending order)
     *  AuthorID
     *  AuthorName
     *  BGColour
     *
     *
     * @author Peter Bourne
     * @version 1.0
     *
     */
    class Carousel
    {
        /**
         * @var mixed
         */
        protected $_dbconn;
        /**
         * @var int|string
         */
        protected $_image_width;
        /**
         * @var int|string
         */
        protected $_image_height;
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
        protected $_title;
        /**
         * @var
         */
        protected $_imgfilename;
        /**
         * @var
         */
        protected $_content;
        /**
         * @var
         */
        protected $_ctalabel;
        /**
         * @var
         */
        protected $_ctalink;
        /**
         * @var
         */
        protected $_displayorder;
        /**
         * @var
         */
        protected $_authorid;
        /**
         * @var
         */
        protected $_authorname;
        /**
         * @var
         */
        protected $_allitems;


        /**
         * Carousel constructor.
         *
         * @param null   $id
         * @param int    $width
         * @param int    $height
         * @param string $path
         *
         * @throws Exception
         */
        public function __construct($id = null, $width = 1200, $height = 440, $path = USER_UPLOADS.'/images/carousel/')
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
                //Assess passed carousel id
                if (isset($id) && !is_numeric($id))
                {
                    throw new Exception('Class Carousel requires id to be specified as an integer');
                }

                //Assess passed width & height
                if (!is_numeric($width) || !is_numeric($height))
                {
                    throw new Exception('Class Carousel requires width and height to be specified as integers');
                }

                //Assess passed path
                if (isset($path) && !is_string($path))
                {
                    throw new Exception('Class Carousel requires path to be specified as a string, eg: /user_uploads/images/carousel/');
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

                //Retrieve current carousel slide information
                if (isset($id))
                {
                    $this->_id = $id;
                    $this->getItemById($id);
                }

                //Store the width/height/path etc
                $this->_image_width = $width;
                $this->_image_height = $height;
                $this->_image_path = $path;

            }
        }


        /**
         * Retrieves specified slide record ID from Carousel table
         * Populates object member elements
         *
         * @param $id
         */
        public function getItemById($id)
        {
            try
            {
                $stmt = $this->_dbconn->prepare("SELECT * FROM Carousel WHERE ID =  :id LIMIT 1");
                $stmt->execute(['id' => $id]);
                $slide = $stmt->fetch();
            } catch (Exception $e)
            {
                error_log("Failed to retrieve Carousel slide details" . $e);
            }

            //Store details in relevant members
            $this->_id = $slide['ID'];
            $this->_title = $slide['Title'];
            $this->_imgfilename = $slide['ImgFilename'];
            $this->_content = $slide['Content'];
            $this->_ctalabel = $slide['CTALabel'];
            $this->_ctalink = $slide['CTALink'];
            $this->_displayorder = $slide['DisplayOrder'];
            $this->_authorid = $slide['AuthorID'];
            $this->_authorname = $slide['AuthorName'];

            return $slide;
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
         * @throws Exception
         */
        public function uploadImage($ImageStream)
        {
            //Check we have some useful data passed
            if ($ImageStream == '')
            {
                throw new Exception("You must supply a file stream to this function.");
            }

            //Create ImageHandler
            $ImgObj = new ImageHandler($this->_image_path, false);
            //Set up some defaults
            $ImgObj->setSourceFileType('png');
            $ImgObj->setDestFileType('jpg');
            $ImgObj->setImageWidth($this->_image_width);
            $ImgObj->setImageHeight($this->_image_height);
            $ImgObj->createFilename($this->_title);
            $ImgObj->setFlagMaintainTransparency(false);


            $result = $ImgObj->processImage($ImageStream);
            $newFilename = $ImgObj->getImgFilename();


            if ($result === true)
            {
                //Delete the old image
                $this->deleteImage();

                //Store the new filename
                $this->_imgfilename = $newFilename;

                //Save the object in its current state
                $this->saveItem();
            }
        }




        /**
         * Create new empty carousel item
         *
         * Sets the _id property accordingly
         */
        public function createNewItem()
        {
            //Only execute if the _id property isn't already set
            if (is_numeric($this->_id) || $this->_id > 0)
            {
                throw new Exception('You cannot create a new Carousel slide at this stage - the id property is already set as '.$this->_id);
            }

            //Create DB item
            try
            {
                $result = $this->_dbconn->query("INSERT INTO Carousel SET Title = ''");
                $lastID = $this->_dbconn->lastInsertId();

                if ($lastID > 0)
                {
                    $this->_id = $lastID;
                }
            }
            catch (Exception $e) {
                error_log("Failed to create new Carousel record: " . $e);
            }
        }


        /**
         * Saves the current object to the Carousel table in the database
         *
         * @throws Exception
         */
        public function saveItem()
        {
            //First need to determine if this is a new Carousel item
            if ($this->_id <= 0)
            {
                $this->createNewItem(); //_id should now be set
            }

            try {
                $stmt = $this->_dbconn->prepare("UPDATE Carousel SET ImgFilename = :imgfilename, ImgPath = :imgpath, Title = :title, Content = :content, CTALabel = :ctalabel, CTALink = :ctalink, DisplayOrder = :displayorder, AuthorID = :authorid, AuthorName = :authorname WHERE ID = :id LIMIT 1");
                $result = $stmt->execute([
                    'imgfilename' => $this->_imgfilename,
                    'imgpath' => $this->_image_path,
                    'title' => $this->_title,
                    'content' => $this->_content,
                    'ctalabel' => $this->_ctalabel,
                    'ctalink' => $this->_ctalink,
                    'displayorder' => $this->_displayorder,
                    'authorid' => $this->_authorid,
                    'authorname' => $this->_authorname,
                    'id' => $this->_id
                               ]);
                if ($result === true) { return true; } else { return $stmt->errorInfo(); }
            } catch (Exception $e) {
                error_log("Failed to save Carousel record: " . $e);
            }
        }


        /**
         * Returns all carousel records and fields in Assiociative array (possibly XML also?)
         *
         * @return mixed
         */
        public function listAllItems()
        {
            try
            {
                $stmt = $this->_dbconn->query("SELECT * FROM Carousel ORDER BY DisplayOrder ASC");
                $slides = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $e)
            {
                error_log("Failed to retrieve Carousel slides" . $e);
            }

            //Store details in relevant member
            $this->_allitems = $slides;

            //return the array
            return $slides;
        }


        /**
         * Delete the image for this carousel item - assuming _img_filename is set
         *
         * @return mixed
         */
        public function deleteImage()
        {
            if (!is_string($this->_imgfilename) || $this->_imgfilename == '')
            {
                return "Sorry - there was no image to delete";
            }

            $OldImg = new ImageHandler($this->_image_path,false);
            $OldImg->setImgFilename($this->_imgfilename);
            $OldImg->deleteImage();

            $this->_imgfilename = '';
            $this->saveItem();
        }

        /**
         * Delete the complete carousel slide item - including any images
         *
         * @return mixed
         */
        public function deleteItem()
        {
            if (!is_numeric($this->_id))
            {
                throw new Exception('Class Carousel requires the panel item ID to be set if you are trying to delete the item');
            }

            //First delete the image files
            $this->deleteImage();

            //Now delete the item from the DB
            try {
                $stmt = $this->_dbconn->prepare("DELETE FROM Carousel WHERE ID = :id LIMIT 1");
                $result = $stmt->execute([
                                             'id' => $this->_id
                                         ]);
            } catch (Exception $e) {
                error_log("Failed to delete Carousel record: " . $e);
            }

            if ($result === true)
            {
                //Unset the properties
                $this->_id = null;
                $this->_title = null;
                $this->_imgfilename = null;
                $this->_content = null;
                $this->_ctalabel= null;
                $this->_ctalink = null;
                $this->_displayorder = null;
                $this->_authorid = null;
                $this->_authorname = null;

                return true;
            }
            else
            {
                return false;
            }

        }






        ###########################################################
        # Getters and Setters
        ###########################################################

        /**
         * @return int|string
         */
        public function getID()
        {
            return $this->_id;
        }

        /**
         * @param $id
         */
        public function setID($id)
        {
            $this->_id = $id;
        }

        /**
         * @return mixed
         */
        public function getImgFilename()
        {
            return $this->_imgfilename;
        }

        /**
         * @param $imgfilename
         */
        public function setImgFilename($imgfilename)
        {
            $this->_imgfilename = $imgfilename;
        }

        /**
         * @return mixed
         */
        public function getTitle()
        {
            return $this->_title;
        }

        /**
         * @param $title
         */
        public function setTitle($title)
        {
            $this->_title = $title;
        }

        /**
         * @return mixed
         */
        public function getContent()
        {
            return $this->_content;
        }

        /**
         * @param $content
         */
        public function setContent($content)
        {
            $this->_content = $content;
        }

        /**
         * @return mixed
         */
        public function getCTALabel()
        {
            return $this->_ctalabel;
        }

        /**
         * @param $ctalabel
         */
        public function setCTALabel($ctalabel)
        {
            $this->_ctalabel = $ctalabel;
        }

        /**
         * @return mixed
         */
        public function getCTALink()
        {
            return $this->_ctalink;
        }

        /**
         * @param $ctalink
         */
        public function setCTALink($ctalink)
        {
            $this->_ctalink = $ctalink;
        }

        /**
         * @return mixed
         */
        public function getDisplayOrder()
        {
            return $this->_displayorder;
        }

        /**
         * @param $displayorder
         */
        public function setDisplayOrder($displayorder)
        {
            $this->_displayorder = $displayorder;
        }

        /**
         * @return mixed
         */
        public function getAuthorID()
        {
            return $this->_authorid;
        }

        /**
         * @param $authorid
         */
        public function setAuthorID($authorid)
        {
            $this->_authorid = $authorid;
        }

        /**
         * @return mixed
         */
        public function getAuthorName()
        {
            return $this->_authorname;
        }

        /**
         * @param $authorname
         */
        public function setAuthorName($authorname)
        {
            $this->_authorname = $authorname;
        }

        public function getImagePath()
        {
            return $this->_image_path;
        }

        public function setImagePath($image_path)
        {
            $this->_image_path = $image_path;
        }

    }