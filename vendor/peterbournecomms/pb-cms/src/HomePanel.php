<?php

    namespace PeterBourneComms\CMS;
    use PDO;
    use PDOException;
    use Exception;


    /**
     * Deals with Home panel items
     *
     * It will allow you to
     *  - specify the size of the image
     *  - specify the location of stored images
     *  - pass an image in and have it resized to that size
     *  - stored in the filesystem and database
     *  - retrieve an individual home panel item
     *  - retrieve an array of all homepanels
     *  - delete panel (including images)
     *
     * Relies on the HomePanels table in this structure:
     *  ID
     *  Title
     *  Content
     *  LinkText
     *  LinkURL
     *  BGCol
     *  ImgFilename
     *  DisplayOrder
     *  AuthorID
     *  AuthorName
     *
     *
     * @author Peter Bourne
     * @version 1.0
     *
     */
    class HomePanel
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
        protected $_linktext;
        /**
         * @var
         */
        protected $_linkurl;
        /**
         * @var
         */
        protected $_bgcol;
        /**
         * @var
         */
        protected $_authorid;
        /**
         * @var
         */
        protected $_authorname;

        protected $_displayorder;


        /**
         * @var
         */
        protected $_allitems;


        /**
         * HomePanel constructor.
         *
         * @param null   $id
         * @param int    $width
         * @param int    $height
         * @param string $path
         *
         * @throws Exception
         */
        public function __construct($id = null, $width = 180, $height = 180, $path = USER_UPLOADS.'/images/home-panels/')
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
                    throw new Exception('Class HomePanel requires id to be specified as an integer');
                }

                //Assess passed width & height
                if (!is_numeric($width) || !is_numeric($height))
                {
                    throw new Exception('Class HomePanel requires width and height to be specified as integers');
                }

                //Assess passed path
                if (isset($path) && !is_string($path))
                {
                    throw new Exception('Class HomePanel requires path to be specified as a string, eg: /user_uploads/images/home-panels/');
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


                //Retrieve current panel information
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
         * Retrieves specified home panel record ID from HomePanels table
         * Populates object member elements
         *
         * @param $id
         */
        public function getItemById($id)
        {
            try
            {
                $stmt = $this->_dbconn->prepare("SELECT * FROM HomePanels WHERE ID =  :id LIMIT 1");
                $stmt->execute(['id' => $id]);
                $panel = $stmt->fetch();
            } catch (Exception $e)
            {
                error_log("Failed to retrieve Home Panel details when searching by ID" . $e);
            }

            //Store details in relevant members
            $this->_id = $panel['ID'];
            $this->_title = $panel['Title'];
            $this->_imgfilename = $panel['ImgFilename'];
            $this->_image_path = $panel['ImgPath'];
            $this->_content = $panel['Content'];
            $this->_linktext = $panel['LinkText'];
            $this->_linkurl = $panel['LinkURL'];
            $this->_displayorder = $panel['DisplayOrder'];
            $this->_authorid = $panel['AuthorID'];
            $this->_authorname = $panel['AuthorName'];
            $this->_bgcol = $panel['BGCol'];

            return $panel;
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
                throw new Exception("You must supply a file stream (data:image/png;base64) to this function.");
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
         * Create new empty homepanel item
         *
         * Sets the _id property accordingly
         */
        public function createNewItem()
        {
            //Only execute if the _id property isn't already set
            if (is_numeric($this->_id) || $this->_id > 0)
            {
                throw new Exception('You cannot create a new Home Panel at this stage - the id property is already set as '.$this->_id);
            }

            //Create DB item
            try
            {
                $result = $this->_dbconn->query("INSERT INTO HomePanels SET Title = ''");
                $lastID = $this->_dbconn->lastInsertId();

                if ($lastID > 0)
                {
                    $this->_id = $lastID;
                }
            }
            catch (Exception $e) {
                error_log("Failed to create new Home Panel record: " . $e);
            }
        }


        /**
         * Saves the current object to the HomePanels table in the database
         *
         * @throws Exception
         */
        public function saveItem()
        {
            //First need to determine if this is a new Test item
            if ($this->_id <= 0)
            {
                $this->createNewItem(); //_id should now be set
            }

            try {
                $stmt = $this->_dbconn->prepare("UPDATE HomePanels SET Title = :title, Content = :content, LinkText = :linktext, LinkURL = :linkurl, BGCol = :bgcol, ImgFilename = :imgfilename, ImgPath = :imgpath, DisplayOrder = :displayorder, AuthorID = :authorid, AuthorName = :authorname WHERE ID = :id LIMIT 1");
                $result = $stmt->execute([
                    'title' => $this->_title,
                    'content' => $this->_content,
                    'linktext' => $this->_linktext,
                    'linkurl' => $this->_linkurl,
                    'bgcol' => $this->_bgcol,
                    'imgfilename' => $this->_imgfilename,
                    'imgpath' => $this->_image_path,
                    'displayorder' => $this->_displayorder,
                    'authorid' => $this->_authorid,
                    'authorname' => $this->_authorname,
                    'id' => $this->_id
                               ]);
                if ($result == true) { return true; } else { return $stmt->errorInfo(); }
            } catch (Exception $e) {
                error_log("Failed to save HomePanel record: " . $e);
            }
        }


        /**
         * Returns all HomePanel records and fields in Assoc array
         *
         * @return mixed
         */
        public function getAllHomePanels()
        {
            $sql = "SELECT * FROM HomePanels ORDER BY DisplayOrder ASC";

            try
            {
                $stmt = $this->_dbconn->query($sql);
                $panels = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $e)
            {
                error_log("Failed to retrieve Home Panel items" . $e);
            }

            //Store details in relevant member
            $this->_allitems = $panels;

            //return the array
            return $panels;
        }

        /**
         * Delete the image for this panel item - assuming _img_filename is set
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
         * Delete the complete homep panel item - including any images
         *
         * @return mixed
         */
        public function deleteItem()
        {
            if (!is_numeric($this->_id))
            {
                throw new Exception('Class HomePanel requires the panel item ID to be set if you are trying to delete the item');
            }

            //First delete the image files
            $this->deleteImage();

            //Now delete the item from the DB
            try {
                $stmt = $this->_dbconn->prepare("DELETE FROM HomePanels WHERE ID = :id LIMIT 1");
                $result = $stmt->execute([
                                   'id' => $this->_id
                               ]);
            } catch (Exception $e) {
                error_log("Failed to delete Home Panel record: " . $e);
            }

            if ($result === true)
            {
                //Unset the properties
                $this->_id = null;
                $this->_title = null;
                $this->_imgfilename = null;
                $this->_content = null;
                $this->_linktext = null;
                $this->_linkurl = null;
                $this->_bgcol = null;
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
        public function getLinkText()
        {
            return $this->_linktext;
        }

        /**
         * @param $linktext
         */
        public function setLinkText($linktext)
        {
            $this->_linktext = $linktext;
        }

        /**
         * @return mixed
         */
        public function getLinkURL()
        {
            return $this->_linkurl;
        }

        /**
         * @param $linkurl
         */
        public function setLinkURL($linkurl)
        {
            $this->_linkurl = $linkurl;
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

        public function getBGCol()
        {
            return $this->_bgcol;
        }

        public function setBGCol($bgcol)
        {
            $this->_bgcol = $bgcol;
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