<?php

    namespace PeterBourneComms\CMS;
    use PDO;
    use Exception;
    use PeterBourneComms\CMS\Database;
    use PeterBourneComms\CMS\ImageHandler;

    /**
     * Deals with Supporting Partner logos
     *
     *
     *
     * @author Peter Bourne
     * @version 1.0
     *
     *          1.0     --------    Original version
     *
     */
    class SupportingPartner
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
        protected $_link;
        protected $_display_order;
        protected $_last_edited;
        protected $_author_id;
        protected $_author_name;
        protected $_imgfilename;


        protected $_allitems;


        /**
         * SupportingPartner constructor.
         *
         * @param null $id
         * @param int $width
         * @param int $height
         * @param string $path
         *
         * @throws Exception
         */
        public function __construct($id = null, $width = 680, $height = 440, $path = USER_UPLOADS . '/images/supporting-partners/')
        {
            //Connect to database
            if (!$this->_dbconn) {
                try {
                    $conn = new Database();
                    $this->_dbconn = $conn->getConnection();
                } catch (Exception $e) {
                    //handle the exception
                    die;
                }
                //Assess passed id
                if (isset($id) && !is_numeric($id)) {
                    throw new Exception('CMS\SupportingPartner->__construct() requires id to be specified as an integer');
                }

                //Assess passed width & height
                if (!is_numeric($width) || !is_numeric($height)) {
                    throw new Exception('CMS\SupportingPartner->__construct() requires width and height to be specified as integers');
                }

                //Assess passed path
                if (isset($path) && !is_string($path)) {
                    throw new Exception('CMS\SupportingPartner->__construct() requires path to be specified as a string, eg: /user_uploads/images/news/');
                }

                //See if provided path exists - if not - create it
                if (!file_exists(DOCUMENT_ROOT . $path)) {
                    //Create it
                    $success = mkdir(DOCUMENT_ROOT . $path, 0777, true);
                    if (!$success) {
                        throw new Exception('CMS\SupportingPartner->__construct() Directory specified (' . $path . ') does not exist - and cannot be created');
                    }
                }

                //Retrieve current information
                if (isset($id)) {
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
         * Retrieves specified Meeting record ID from table
         * Populates object member elements
         *
         * @param $id
         */
        public function getItemById($id)
        {
            try {
                $stmt = $this->_dbconn->prepare("SELECT * FROM SupportingPartners WHERE ID =  :id LIMIT 1");
                $stmt->execute(['id' => $id]);
                $item = $stmt->fetch();
            } catch (Exception $e) {
                error_log("CMS\SupportingPartner->getItemById() Failed to retrieve Logo item details when searching by ID" . $e);
            }

            //Store details in relevant members
            $this->_id = $item['ID'];
            $this->_title = $item['Title'];
            $this->_link = $item['Link'];
            $this->_display_order = $item['DisplayOrder'];
            $this->_author_id = $item['AuthorID'];
            $this->_author_name = $item['AuthorName'];
            $this->_last_edited = $item['LastEdited'];

            $this->_imgfilename = $item['ImgFilename'];
            $this->_image_path = $item['ImgPath'];

            return $item;
        }



        /**
         * Receive an image data stream
         *  - process the image based on path and size settings - using large and small as subdirs for file storage (these get created the first time the object is called)
         *  - store it in the file system
         *  - store it in the new object ahead of saving
         *  - save the object based on the current state of the data - DESIRABLE?
         *
         * @param     $ImageStream
         * @param int $thumbnailWidth
         * @param int $thumbnailHeight
         *
         * @throws Exception
         */
        public
        function uploadImage($ImageStream)
        {
            //Check we have some useful data passed
            if ($ImageStream == '') {
                throw new Exception("CMS\SupportingPartner->uploadImage() You must supply a file stream to this function.");
            }

            //Create ImageHandler
            $ImgObj = new ImageHandler($this->_image_path, false);
            //Set up some defaults
            $ImgObj->setSourceFileType('png');
            $ImgObj->setDestFileType('png');
            $ImgObj->setImageWidth($this->_image_width);
            $ImgObj->setImageHeight($this->_image_height);
            $ImgObj->setFlagMaintainTransparency(true);
            $ImgObj->createFilename($this->_title);


            $result = $ImgObj->processImage($ImageStream);
            $newFilename = $ImgObj->getImgFilename();


            if ($result === true) {
                //Delete the old image
                $this->deleteImage();

                //Store the new filename
                $this->_imgfilename = $newFilename;

                //Save the object in its current state
                $this->saveItem();
            }
        }

        /**
         * Delete the image for this item - assuming _img_filename is set
         *
         * @return mixed
         */
        public function deleteImage()
        {
            if (!is_string($this->_imgfilename) || $this->_imgfilename == '') {
                error_log("CMS\SupportingPartner->deleteImage() Sorry - there was no image to delete");

                return;
            }

            $OldImg = new ImageHandler($this->_image_path, false);
            $OldImg->setImgFilename($this->_imgfilename);
            $OldImg->deleteImage();

            $this->_imgfilename = '';
            $this->saveItem();
        }

        /**
         * Saves the current object to the Meeting table in the database
         *
         * @throws Exception
         */
        public function saveItem()
        {
            //First need to determine if this is a new Meeting item
            if ($this->_id <= 0) {
                $this->createNewItem(); //_id should now be set
            }

            try {
                $stmt = $this->_dbconn->prepare("UPDATE SupportingPartners SET Title = :title, Link = :link, ImgPath = :imgpath, ImgFilename = :imgfilename, DisplayOrder = :displayorder, AuthorID = :authorid, AuthorName = :authorname, LastEdited = NOW() WHERE ID = :id LIMIT 1");
                $result = $stmt->execute([
                    'title' => $this->_title,
                    'link' => $this->_link,
                    'imgpath' => $this->_image_path,
                    'imgfilename' => $this->_imgfilename,
                    'displayorder' => $this->_display_order,
                    'authorid' => $this->_author_id,
                    'authorname' => $this->_author_name,
                    'id' => $this->_id
                ]);
                //print_r($stmt->errorInfo()[2]);
                if ($result === true) {
                    return true;
                } else {
                    //print_r($stmt->errorInfo()[2]);
                    error_log($stmt->errorInfo()[2]);
                    return false;
                }
            } catch (Exception $e) {
                error_log("CMS\SupportingPartner->saveItem() Failed to save Logo record: " . $e);
            }
        }

        /**
         * Create new empty Meeting item
         *
         * Sets the _id property accordingly
         */
        public function createNewItem()
        {
            //Only execute if the _id property isn't already set
            if (is_numeric($this->_id) || $this->_id > 0) {
                throw new Exception('CMS\SupportingPartner->createNewItem() You cannot create a new Meeting item at this stage - the id property is already set as ' . $this->_id);
            }

            //Create DB item
            try {
                $result = $this->_dbconn->query("INSERT INTO SupportingPartners SET LastEdited = NOW()");
                $lastID = $this->_dbconn->lastInsertId();

                if ($lastID > 0) {
                    $this->_id = $lastID;
                }
            } catch (Exception $e) {
                error_log("CMS\SupportingPartner->createNewItem()Failed to create new Logo record: " . $e);
            }
        }


        /**
         * Returns array of complete database records for all items - as searched for
         *
         * @param string $needle record id or string - default = empty
         * @param string $searchtype 'id','title','type' - default = title
         *
         * @return array
         */
        public
        function listAllItems($needle = '', $searchtype = '')
        {
            $basesql = "SELECT ID FROM SupportingPartners";

            $params = array();

            switch ($searchtype) {
                case 'id':
                    $search = " WHERE ID = :needle";
                    $order = " LIMIT 1";
                    $params['needle'] = $needle;
                    break;

                case 'title':
                    $search = " WHERE Title LIKE :needle";
                    $order = " ORDER BY Title ASC";
                    $params['needle'] = $needle . "%";
                    break;

                default:
                    $search = " WHERE ID = ID";
                    $order = " ORDER BY DisplayOrder ASC, Title ASC";
                    //$params['needle'] = $needle . "%";
                    break;
            }


            //Create sql
            $sql = $basesql . $search . $order;
            //echo $sql;

            $items = array();
            try {
                $stmt = $this->_dbconn->prepare($sql);
                $stmt->execute($params);
                while ($item = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $items[] = $this->getItemById($item['ID']);
                }
            } catch (Exception $e) {
                error_log("CMS\SupportingPartner->listAllItems() Failed to retrieve Logo items" . $e);
            }

            //Store details in relevant member
            $this->_all_items = $items;

            //return the array
            return $items;
        }

        /**
         * Delete the complete Meeting item - including any images
         *
         * @return mixed
         */
        public
        function deleteItem()
        {
            if (!is_numeric($this->_id)) {
                throw new Exception('CMS\SupportingPartner->deleteItem() requires the item ID to be set if you are trying to delete the item');
            }

            //First delete the image files
            $this->deleteImage();

            //Now delete the item from the DB
            try {
                $stmt = $this->_dbconn->prepare("DELETE FROM SupportingPartners WHERE ID = :id LIMIT 1");
                $result = $stmt->execute([
                    'id' => $this->_id
                ]);
            } catch (Exception $e) {
                error_log("CMS\SupportingPartner->deleteItem() Failed to delete Logo record: " . $e);
            }

            if ($result === true) {
                //Unset the properties
                $this->_id = null;
                $this->_title = null;
                $this->_imgfilename = null;

                return true;
            } else {
                return false;
            }

        }



        ###########################################################
        # Getters and Setters
        ###########################################################

        /**
         * @return int|string
         */
        public
        function getID()
        {
            return $this->_id;
        }

        /**
         * @param $id
         */
        public
        function setID($id)
        {
            $this->_id = $id;
        }

        /**
         * @return mixed
         */
        public
        function getImgFilename()
        {
            return $this->_imgfilename;
        }

        /**
         * @param $imgfilename
         */
        public
        function setImgFilename($imgfilename)
        {
            $this->_imgfilename = $imgfilename;
        }

        /**
         * @return mixed
         */
        public
        function getTitle()
        {
            return $this->_title;
        }

        /**
         * @param $title
         */
        public
        function setTitle($title)
        {
            $this->_title = $title;
        }

        /**
         * @return string
         */
        public function getImagePath()
        {
            return $this->_image_path;
        }

        /**
         * @param string $image_path
         */
        public function setImagePath($image_path)
        {
            $this->_image_path = $image_path;
        }

        /**
         * @return mixed
         */
        public function getLink()
        {
            return $this->_link;
        }

        /**
         * @param mixed $link
         */
        public function setLink($link)
        {
            $this->_link = $link;
        }

        /**
         * @return mixed
         */
        public function getDisplayOrder()
        {
            return $this->_display_order;
        }

        /**
         * @param mixed $display_order
         */
        public function setDisplayOrder($display_order)
        {
            $this->_display_order = $display_order;
        }

        /**
         * @return mixed
         */
        public function getLastEdited()
        {
            return $this->_last_edited;
        }

        /**
         * @param mixed $last_edited
         */
        public function setLastEdited($last_edited)
        {
            $this->_last_edited = $last_edited;
        }

        /**
         * @return mixed
         */
        public function getAuthorId()
        {
            return $this->_author_id;
        }

        /**
         * @param mixed $author_id
         */
        public function setAuthorId($author_id)
        {
            $this->_author_id = $author_id;
        }

        /**
         * @return mixed
         */
        public function getAuthorName()
        {
            return $this->_author_name;
        }

        /**
         * @param mixed $author_name
         */
        public function setAuthorName($author_name)
        {
            $this->_author_name = $author_name;
        }

        /**
         * @return mixed
         */
        public function getAllitems()
        {
            return $this->_allitems;
        }

        /**
         * @param mixed $allitems
         */
        public function setAllitems($allitems)
        {
            $this->_allitems = $allitems;
        }



    }