<?php

    namespace PeterBourneComms\CMS;

    use PDO;
    use PDOException;
    use Exception;


    /**
     * Deals with Blog - in conjunction with Bloggers
     *
     *
     * @author Peter Bourne
     * @version 1.0
     *
     *          1.0     13/03/23    Original version
     *
     */
    class Blog
    {
        /**
         * @var mixed
         */
        protected $_dbconn;
        /**
         * @var int|string
         */
        protected $_img_width;
        /**
         * @var int|string
         */
        protected $_img_height;
        /**
         * @var string
         */
        protected $_img_path;
        /**
         * @var int|string
         */
        protected $_id;
        /**
         * @var
         */
        protected $_blogger_id;
        protected $_title;
        /**
         * @var
         */
        protected $_sub_title;
        protected $_summary;
        protected $_img_filename;
        /**
         * @var
         */
        protected $_content;
        /**
         * @var
         */
        protected $_date_display;
        protected $_date_edited;
        /**
         * @var
         */
        protected $_author_id;
        /**
         * @var
         */
        protected $_author_name;

        protected $_url_text;
        protected $_meta_desc;
        protected $_meta_key;
        protected $_meta_title;


        /**
         * @var
         */
        protected $_all_items;


        /**
         * Constructor.
         *
         * @param null   $id
         * @param int    $width
         * @param int    $height
         * @param string $path
         *
         * @throws Exception
         */
        public function __construct($id = null, $width = 1200, $height = 450, $path = USER_UPLOADS.'/images/blogs/')
        {
            //Connect to database
            if (!$this->_dbconn)
            {
                try {
                    $conn = new Database();
                    $this->_dbconn = $conn->getConnection();
                } catch (Exception $e) {
                    //handle the exception
                    die;
                }
                //Assess passed carousel id
                if (isset($id) && !is_numeric($id)) {
                    throw new Exception('CMS\Blog->__construct() requires id to be specified as an integer');
                }

                //Assess passed width & height
                if (!is_numeric($width) || !is_numeric($height)) {
                    throw new Exception('CMS\Blog->__construct() requires width and height to be specified as integers');
                }

                //Assess passed path
                if (isset($path) && !is_string($path)) {
                    throw new Exception('CMS\Blog->__construct() requires path to be specified as a string, eg: /user_uploads/images/blogs/');
                }

                //See if provided path exists - if not - create it
                if (!file_exists(DOCUMENT_ROOT . $path)) {
                    //Create it
                    $success = mkdir(DOCUMENT_ROOT . $path, 0777, true);
                    if (!$success) {
                        throw new Exception('CMS\Blog->__construct() Directory specified (' . $path . ') does not exist - and cannot be created');
                    }
                }

                //See if subdirectories for 'large' and 'small' images exist in the path specified - if not create them. If fail - Throw Exception
                $large = $path . "large/";
                $small = $path . "small/";
                if (!file_exists(DOCUMENT_ROOT . $large)) {
                    //Create it
                    $success = mkdir(DOCUMENT_ROOT . $large, 0777, true);
                    if (!$success) {
                        throw new Exception('CMS\Blog->__construct() Directory specified (' . $large . ') does not exist - and cannot be created');
                    }
                }
                if (!file_exists(DOCUMENT_ROOT . $small)) {
                    //Create it
                    $success = mkdir(DOCUMENT_ROOT . $small, 0777, true);
                    if (!$success) {
                        throw new Exception('CMS\Blog->__construct() Directory specified (' . $small . ') does not exist - and cannot be created');
                    }
                }

                //Default path
                $this->_img_path = $path;

                //Retrieve current sinformation
                if (isset($id)) {
                    $this->_id = $id;
                    $this->getItemById($id);
                }

                //Store the width/height/path etc
                $this->_img_width = $width;
                $this->_img_height = $height;

            }
        }


        /**
         * Retrieves specified srecord ID from table
         * Populates object member elements
         *
         * @param $id
         */
        public function getItemById($id)
        {
            try {
                $stmt = $this->_dbconn->prepare("SELECT Blogs.ID, BloggerID, AES_DECRYPT(Blogs.Title, :key) AS Title, AES_DECRYPT(Blogs.SubTitle, :key) AS SubTitle, AES_DECRYPT(Blogs.Summary, :key) AS Summary, AES_DECRYPT(Blogs.ImgFilename, :key) AS ImgFilename, AES_DECRYPT(Blogs.ImgPath, :key) AS ImgPath, AES_DECRYPT(Blogs.Content, :key) AS Content, Blogs.DateDisplay, Blogs.DateEdited, Blogs.AuthorID, AES_DECRYPT(Blogs.AuthorName, :key) AS AuthorName, AES_DECRYPT(Blogs.URLText, :key) AS URLText, AES_DECRYPT(Blogs.MetaTitle, :key) AS MetaTitle, AES_DECRYPT(Blogs.MetaDesc, :key) AS MetaDesc, AES_DECRYPT(Blogs.MetaKey, :key) AS MetaKey, AES_DECRYPT(Bloggers.Firstname, :key) AS BloggerFirstname, AES_DECRYPT(Bloggers.Surname, :key) AS BloggerSurname, AES_DECRYPT(Bloggers.ImgPath, :key) AS BloggerImgPath, AES_DECRYPT(Bloggers.ImgFilename, :key) AS BloggerImgFilename FROM Blogs LEFT JOIN Bloggers ON Bloggers.ID = Blogs.BloggerID WHERE Blogs.ID =  :id LIMIT 1");
                $stmt->execute([
                    'key' => AES_ENCRYPTION_KEY,
                    'id' => $id
                ]);
                $item = $stmt->fetch();
            } catch (Exception $e) {
                error_log("CMS\Blog->getItemById() Failed to retrieve item details when searching by ID" . $e);
            }

            //Store details in relevant members
            $this->_id = $item['ID'];
            $this->_blogger_id = $item['BloggerID'];
            $this->_title = $item['Title'];
            $this->_sub_title = $item['SubTitle'];
            $this->_summary = $item['Summary'];
            $this->_img_filename = $item['ImgFilename'];
            $this->_img_path = $item['ImgPath'];
            $this->_content = $item['Content'];
            $this->_date_display = $item['DateDisplay'];
            $this->_date_edited = $item['DateEdited'];
            $this->_author_id = $item['AuthorID'];
            $this->_author_name = $item['AuthorName'];
            $this->_url_text = $item['URLText'];
            $this->_meta_desc = $item['MetaDesc'];
            $this->_meta_key = $item['MetaKey'];
            $this->_meta_title = $item['MetaTitle'];

            return $item;
        }

        public function getItemByUrl($urltext)
        {
            try {
                $stmt = $this->_dbconn->prepare("SELECT ID FROM Blogs WHERE CONVERT(AES_DECRYPT(URLText, :key) USING utf8) LIKE :needle LIMIT 1");
                $stmt->execute([
                    'key'=> AES_ENCRYPTION_KEY,
                    'needle' => $urltext
                ]);
                $item = $stmt->fetch();
            } catch (Exception $e) {
                error_log("CMS\Blog->getItemByUrl() Failed to retrieve item details when searching by URLText" . $e);
            }
            //Use other function to populate rest of properties (no sense writing it twice!)_
            return $this->getItemById($item['ID']);
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
        public function uploadImage($ImageStream)
        {
            //Check we have some useful data passed
            if ($ImageStream == '') {
                throw new Exception("CMS\Blog->uploadImage() You must supply a file stream to this function.");
            }

            //Create ImageHandler
            $ImgObj = new ImageHandler($this->_img_path, true);
            //Set up some defaults
            $ImgObj->setSourceFileType('png');
            $ImgObj->setDestFileType('jpg');
            $ImgObj->setImageWidth($this->_img_width);
            $ImgObj->setImageHeight($this->_img_height);
            $ImgObj->setThumbWidth(floor($this->_img_width / 3));
            $ImgObj->setThumbHeight(floor($this->_img_height / 3));
            $ImgObj->createFilename($this->_title);


            $result = $ImgObj->processImage($ImageStream);
            $newFilename = $ImgObj->getImgFilename();

            if ($result === true) {
                //Delete the old image
                $this->deleteImage();

                //Store the new filename
                $this->_img_filename = $newFilename;

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
            if (!is_string($this->_img_filename) || $this->_img_filename == '') {
                error_log("CMS\Blog->deleteImage() Sorry - there was no image to delete");

                return;
            }

            $OldImg = new ImageHandler($this->_img_path, true);
            $OldImg->setImgFilename($this->_img_filename);
            $OldImg->deleteImage();

            $this->_img_filename = '';
            $this->saveItem();
        }

        /**
         * Saves the current object to the table in the database
         *
         * @throws Exception
         */
        public function saveItem()
        {
            //First need to determine if this is a new sitem
            if ($this->_id <= 0) {
                $this->createNewItem(); //_id should now be set
            }

            try {
                $stmt = $this->_dbconn->prepare("UPDATE Blogs SET BloggerID = :blogger_id, Title = AES_ENCRYPT(:title, :key), SubTitle = AES_ENCRYPT(:sub_title, :key), Summary = AES_ENCRYPT(:summary, :key), Content = AES_ENCRYPT(:content, :key), DateDisplay = :date_display, DateEdited = NOW(), ImgFilename = AES_ENCRYPT(:img_filename, :key), ImgPath = AES_ENCRYPT(:img_path, :key), URLText = AES_ENCRYPT(:url_text, :key), MetaTitle = AES_ENCRYPT(:meta_title, :key), MetaDesc = AES_ENCRYPT(:meta_desc, :key), MetaKey = AES_ENCRYPT(:meta_key, :key), AuthorID = :author_id, AuthorName = AES_ENCRYPT(:author_name, :key) WHERE ID = :id LIMIT 1");
                $result = $stmt->execute([
                    'key' => AES_ENCRYPTION_KEY,
                    'blogger_id' => $this->_blogger_id,
                    'title' => $this->_title,
                    'sub_title' => $this->_sub_title,
                    'summary' => $this->_summary,
                    'content' => $this->_content,
                    'date_display' => $this->_date_display,
                    'img_filename' => $this->_img_filename,
                    'img_path' => $this->_img_path,
                    'url_text' => $this->_url_text,
                    'meta_desc' => $this->_meta_desc,
                    'meta_key' => $this->_meta_key,
                    'meta_title' => $this->_meta_title,
                    'author_id' => $this->_author_id,
                    'author_name' => $this->_author_name,
                    'id' => $this->_id
                                         ]);
                if ($result === true) {
                    return true;
                } else {
                    error_log($stmt->errorInfo()[2]);
                    return false;
                }
            } catch (Exception $e) {
                error_log("CMS\Blog->saveItem() Failed to save record: " . $e);
            }
        }

        /**
         * Create new empty item
         *
         * Sets the _id property accordingly
         */
        public function createNewItem()
        {
            //Only execute if the _id property isn't already set
            if (is_numeric($this->_id) || $this->_id > 0)
            {
                throw new Exception('CMS\Blog->createNewItem() You cannot create a new item at this stage - the id property is already set as ' . $this->_id);
            }

            //Create DB item
            try {
                $result = $this->_dbconn->query("INSERT INTO Blogs SET Title = ''");
                $lastID = $this->_dbconn->lastInsertId();

                if ($lastID > 0) {
                    $this->_id = $lastID;
                }
            } catch (Exception $e) {
                error_log("CMS\Blog->createNewItem() Failed to create new record: " . $e);
            }
        }
    
        /**
         * Function to return array of records (as assoc array)
         *
         * @param string $passedNeedle
         * @param string $passedMode
         *
         * @return array
         */
        public function listAllItems($passedNeedle = '', $passedMode = 'title')
        {
            $basesql = "SELECT ID FROM Blogs WHERE ";
            $params = array();
        
            //Build SQL depending on passedMode and passedNeedle
            switch ($passedMode) {
                case 'blogger-id':
                    $query = "BloggerID = :needle ";
                    $order = "ORDER BY DateDisplay DESC, AES_DECRYPT(Title, :key) ASC";
                    $params['key'] = AES_ENCRYPTION_KEY;
                    $params['needle'] = $passedNeedle;
                    break;
    
                case 'blogger':
                    $basesql = "SELECT Blogs.ID FROM Blogs LEFT JOIN Bloggers ON Bloggers.ID = Blogs.BloggerID WHERE ";
                    $query = "(CONVERT(AES_DECRYPT(Bloggers.Firstname, :key) USING utf8) LIKE :needle OR CONVERT(AES_DECRYPT(Bloggers.Surname, :key) USING utf8) LIKE :needle) AND Blogs.DateDisplay <= NOW()";
                    $order = "ORDER BY DateDisplay DESC, AES_DECRYPT(Bloggers.Surname, :key), AES_DECRYPT(Blogs.Title, :key) ASC";
                    $passedNeedle = "%".$passedNeedle . "%";
                    $params['key'] = AES_ENCRYPTION_KEY;
                    $params['needle'] = $passedNeedle;
                    break;
                    
                case 'title':
                    $query = "(CONVERT(AES_DECRYPT(Title, :key) USING utf8) LIKE :needle) ";
                    $order = "ORDER BY AES_DECRYPT(Title, :key) ASC, DateDisplay DESC";
                    $passedNeedle = $passedNeedle . "%";
                    $params['key'] = AES_ENCRYPTION_KEY;
                    $params['needle'] = $passedNeedle;
                    break;
                    
                case 'all':
                    $basesql = "SELECT Blogs.ID FROM Blogs LEFT JOIN Bloggers ON Bloggers.ID = Blogs.BloggerID WHERE ";
                    $query = "(CONVERT(AES_DECRYPT(Blogs.Title, :key) USING utf8) LIKE :needle OR CONVERT(AES_DECRYPT(Blogs.SubTitle, :key) USING utf8) LIKE :needle OR CONVERT(AES_DECRYPT(Blogs.Summary, :key) USING utf8) LIKE :needle OR CONVERT(AES_DECRYPT(Bloggers.Firstname, :key) USING utf8) LIKE :needle OR CONVERT(AES_DECRYPT(Bloggers.Surname, :key) USING utf8) LIKE :needle) AND Blogs.DateDisplay <= NOW()";
                    $order = "ORDER BY DateDisplay DESC, AES_DECRYPT(Bloggers.Surname, :key), AES_DECRYPT(Blogs.Title, :key) ASC";
                    $passedNeedle = "%".$passedNeedle . "%";
                    $params['key'] = AES_ENCRYPTION_KEY;
                    $params['needle'] = $passedNeedle;
                    break;
            
                default:
                    $query = "(CONVERT(AES_DECRYPT(Title, :key) USING utf8) LIKE :needle) ";
                    $order = "ORDER BY DisplayOrder ASC, AES_DECRYPT(Title, :key) ASC, DateDisplay DESC";
                    $passedNeedle = $passedNeedle . "%";
                    $params['key'] = AES_ENCRYPTION_KEY;
                    $params['needle'] = $passedNeedle;
                    break;
            }
        
            //echo $basesql . $query . $order;
            //echo "<br/>".$passedNeedle;
        
            //Carry out the query
            $stmt = $this->_dbconn->prepare($basesql . $query . $order);
            $stmt->execute($params);
        
            //Prepare results array
            $results = array();
        
            //Work through results from query
            while ($this_res = $stmt->fetch()) {
                $mem = $this->getItemByID($this_res['ID']);
                $results[] = $mem;
            }
        
            return $results;
        }

        /**
         * Delete the complete item - including any images
         *
         * @return mixed
         */
        public function deleteItem()
        {
            if (!is_numeric($this->_id)) {
                throw new Exception('CMS\Blog->deleteItem() requires the item ID to be set if you are trying to delete the item');
            }

            //First delete the image files
            $this->deleteImage();

            // Step through all images for this content item AND DELETE
            $ImgDel = new ImageLibrary('Blogs', $this->_id, null, null, null, USER_UPLOADS.'/images/blogs/');
            $ImgDel->deleteAllImagesForContent('Blogs',$this->_id);

            //Now delete the item from the DB
            try {
                $stmt = $this->_dbconn->prepare("DELETE FROM Blogs WHERE ID = :id LIMIT 1");
                $result = $stmt->execute([
                                             'id' => $this->_id
                                         ]);
            } catch (Exception $e) {
                error_log("CMS\Blog->deleteItem() Failed to delete record: " . $e);
            }

            if ($result === true) {
                //Unset the properties
                $this->_id = null;
                $this->_title = null;
                $this->_img_filename = null;
                $this->_content = null;
                $this->_date_display = null;
                $this->_author_id = null;
                $this->_author_name = null;
                $this->_url_text = null;
                $this->_meta_desc = null;
                $this->_meta_key = null;
                $this->_meta_title = null;

                return true;
            } else {
                return false;
            }

        }


        /**
         * Function to check if a similar URL already exists in the blogs table
         * Returns TRUE if VALID, ie: not present in database
         *
         *
         *
         * @param     $ContentURL
         * @param int $ID
         *
         * @return bool
         * @throws Exception
         */

        public function URLTextValid($ContentURL, $ID = 0)
        {
            if ($ID <= 0) {
                $ID = $this->_id;
            }
            if (!is_string($ContentURL)) {
                throw new Exception('CMS\Blog->URLTextValid() needs the new URL specifying as a string');
            }
    
    
            if (clean_int($ID) > 0)
            {
                $sql = "SELECT ID FROM Blogs WHERE CONVERT(AES_DECRYPT(URLText, :key) USING utf8) = :urltext AND ID != :id";
                $vars = array('urltext' => $ContentURL, 'id' => $ID, 'key' => AES_ENCRYPTION_KEY);
            }
            else
            {
                $sql = "SELECT ID FROM Blogs WHERE CONVERT(AES_DECRYPT(URLText, :key) USING utf8) = :urltext AND (ID IS NULL OR ID <= 0)";
                $vars = array('urltext' => $ContentURL, 'key' => AES_ENCRYPTION_KEY);
            }


            // Execute query
            $stmt = $this->_dbconn->prepare($sql);
            $result = $stmt->execute($vars);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($rows) > 0) {
                return false;
            } else {
                return true;
            }
        }



        /**
         * Function to search all content for passed string. We will search the following fields:
         *  - Title         (weight: 20 - level 1)
         *  - Content       (weight: 10 - level 3)
         *
         * Will return array of arrays:
         * array('ID','Title,'SubTitle','FullURLText','Weight');  The Full URL will be provided - to cover lower level content items - this will need to be derived.
         *
         * The search will only be carried out where the parent item is present in a menu (ie there is an entry in the ContentByType table for the parent/Toplevel ContentID).
         *
         * @param   mixed   $needle
         * @return  mixed   array
         */
        function searchContent($needle = '')
        {
            if ($needle == '') {
                return array();
            }

            //$results = array('Level1'=>array(),'Level2'=>array(),'Level3'=>array());
            $search_field = strtolower(filter_var($needle, FILTER_SANITIZE_STRING));
            $search_criteria = "%" . $needle . "%";

            $search_results = array();

            //Search content as outlined above
            //Return IDs
            $sql = "SELECT Blogs.ID FROM Blogs LEFT JOIN Bloggers ON Bloggers.ID = Blogs.BloggerID WHERE (CONVERT(AES_DECRYPT(Blogs.Title, :key) USING utf8) LIKE :needle OR CONVERT(AES_DECRYPT(Blogs.SubTitle, :key) USING utf8) LIKE :needle OR CONVERT(AES_DECRYPT(Blogs.Summary, :key) USING utf8) LIKE :needle OR CONVERT(AES_DECRYPT(Bloggers.Firstname, :key) USING utf8) LIKE :needle OR CONVERT(AES_DECRYPT(Bloggers.Surname, :key) USING utf8) LIKE :needle) AND Blogs.DateDisplay <= NOW() ";
            $stmt = $this->_dbconn->prepare($sql);
            $stmt->execute([
                'key' => AES_ENCRYPTION_KEY,
                'needle' => $search_criteria
                           ]);
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                //Retrieve full data
                $content = $this->getItemById($row['ID']);

                //Prepare link
                unset($link);
                if ($content['URLText'] != '') {
                    $link = "//" . SITEFQDN . "/blog/" . $content['URLText'];
                } else {
                    $link = "//" . SITEFQDN . "/blogs/blog-detail.php?id=" . $content['ID'];
                }

                //Prepare content
                $Content = FixOutput(substr(strip_tags($content['Content']), 0, 160));

                //Check weighting
                if (strtolower($content['Title']) == $search_field || strtolower($content['BloggerSurname'] == $search_field)) {
                    $Weighting = 0;
                } elseif ($search_field == substr(strtolower($content['Title']), 0, strlen($search_field)) || $search_field == $content['BloggerFirstname']) {
                    $Weighting = 10;
                } else {
                    $Weighting = 20;
                }
                
                $content['Weighting'] = $Weighting;

                //Add to search results
                $search_results[] = array('Title' => $content['Title'], 'Content' => $Content, 'Link' => $link, 'DateDisplay' => $content['DateDisplay'], 'Weighting' => $Weighting);
            }

            //Return results
            return $search_results;

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
        public function getBloggerId()
        {
            return $this->_blogger_id;
        }
    
        /**
         * @param mixed $blogger_id
         */
        public function setBloggerId($blogger_id): void
        {
            $this->_blogger_id = $blogger_id;
        }
    
        /**
         * @return mixed
         */
        public function getSubTitle()
        {
            return $this->_sub_title;
        }
    
        /**
         * @param mixed $sub_title
         */
        public function setSubTitle($sub_title): void
        {
            $this->_sub_title = $sub_title;
        }
    
        /**
         * @return mixed
         */
        public function getDateEdited()
        {
            return $this->_date_edited;
        }
        

        /**
         * @return mixed
         */
        public function getImgFilename()
        {
            return $this->_img_filename;
        }

        /**
         * @param $imgfilename
         */
        public function setImgFilename($imgfilename)
        {
            $this->_img_filename = $imgfilename;
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
        public function getDateDisplay()
        {
            return $this->_date_display;
        }

        /**
         * @param $datedisplay
         */
        public function setDateDisplay($datedisplay)
        {
            if ($datedisplay == '' || $datedisplay == '0000-00-00') { $datedisplay = null; }
            $this->_date_display = $datedisplay;
        }

        /**
         * @return mixed
         */
        public function getAuthorID()
        {
            return $this->_author_id;
        }

        /**
         * @param $authorid
         */
        public function setAuthorID($authorid)
        {
            $this->_author_id = $authorid;
        }

        /**
         * @return mixed
         */
        public function getAuthorName()
        {
            return $this->_author_name;
        }

        /**
         * @param $authorname
         */
        public function setAuthorName($authorname)
        {
            $this->_author_name = $authorname;
        }

        public function getURLText()
        {
            return $this->_url_text;
        }

        public function setURLText($urltext)
        {
            $this->_url_text = $urltext;
        }

        public function getMetaDesc()
        {
            return $this->_meta_desc;
        }

        public function setMetaDesc($metadesc)
        {
            $this->_meta_desc = $metadesc;
        }

        public function getMetaKey()
        {
            return $this->_meta_key;
        }

        public function setMetaKey($metakey)
        {
            $this->_meta_key = $metakey;
        }

        public function getMetaTitle()
        {
            return $this->_meta_title;
        }

        public function setMetaTitle($metatitle)
        {
            $this->_meta_title = $metatitle;
        }

        public function getImgPath()
        {
            return $this->_img_path;
        }

        public function setImgPath($image_path)
        {
            $this->_img_path = $image_path;
        }
    
        /**
         * @return mixed
         */
        public function getSummary()
        {
            return $this->_summary;
        }
    
        /**
         * @param mixed $summary
         */
        public function setSummary($summary): void
        {
            $this->_summary = $summary;
        }
        
        
    }