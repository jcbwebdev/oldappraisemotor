<?php

    namespace PeterBourneComms\CMS;

    use PDO;
    use PDOException;
    use Exception;


    /**
     * Deals with Bloggers - in conjunction with Blog
     *
     *
     * @author Peter Bourne
     * @version 1.0
     *
     *          1.0     13/03/23    Original version
     *
     */
    class Blogger
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
        protected $_title;
        protected $_first_name;
        protected $_surname;
        /**
         * @var
         */
        protected $_img_filename;
        /**
         * @var
         */
        protected $_content;
        /**
         * @var
         */
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
        public function __construct($id = null, $width = 800, $height = 800, $path = USER_UPLOADS.'/images/bloggers/')
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
                    throw new Exception('CMS\Blogger->__construct() requires id to be specified as an integer');
                }

                //Assess passed width & height
                if (!is_numeric($width) || !is_numeric($height)) {
                    throw new Exception('CMS\Blogger->__construct() requires width and height to be specified as integers');
                }

                //Assess passed path
                if (isset($path) && !is_string($path)) {
                    throw new Exception('CMS\Blogger->__construct() requires path to be specified as a string, eg: /user_uploads/images/bloggers/');
                }

                //See if provided path exists - if not - create it
                if (!file_exists(DOCUMENT_ROOT . $path)) {
                    //Create it
                    $success = mkdir(DOCUMENT_ROOT . $path, 0777, true);
                    if (!$success) {
                        throw new Exception('CMS\Blogger->__construct() Directory specified (' . $path . ') does not exist - and cannot be created');
                    }
                }

                //See if subdirectories for 'large' and 'small' images exist in the path specified - if not create them. If fail - Throw Exception
                $large = $path . "large/";
                $small = $path . "small/";
                if (!file_exists(DOCUMENT_ROOT . $large)) {
                    //Create it
                    $success = mkdir(DOCUMENT_ROOT . $large, 0777, true);
                    if (!$success) {
                        throw new Exception('CMS\Blogger->__construct() Directory specified (' . $large . ') does not exist - and cannot be created');
                    }
                }
                if (!file_exists(DOCUMENT_ROOT . $small)) {
                    //Create it
                    $success = mkdir(DOCUMENT_ROOT . $small, 0777, true);
                    if (!$success) {
                        throw new Exception('CMS\Blogger->__construct() Directory specified (' . $small . ') does not exist - and cannot be created');
                    }
                }

                //Default path
                $this->_img_path = $path;

                //Retrieve current news information
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
         * Retrieves specified news record ID from table
         * Populates object member elements
         *
         * @param $id
         */
        public function getItemById($id)
        {
            try {
                $stmt = $this->_dbconn->prepare("SELECT ID, AES_DECRYPT(Title, :key) AS Title, AES_DECRYPT(Firstname, :key) AS Firstname, AES_DECRYPT(Surname, :key) AS Surname, AES_DECRYPT(ImgFilename, :key) AS ImgFilename, AES_DECRYPT(ImgPath, :key) AS ImgPath, AES_DECRYPT(Content, :key) AS Content, DateEdited, AuthorID, AES_DECRYPT(AuthorName, :key) AS AuthorName, AES_DECRYPT(URLText, :key) AS URLText, AES_DECRYPT(MetaTitle, :key) AS MetaTitle, AES_DECRYPT(MetaDesc, :key) AS MetaDesc, AES_DECRYPT(MetaKey, :key) AS MetaKey FROM Bloggers WHERE ID =  :id LIMIT 1");
                $stmt->execute([
                    'key' => AES_ENCRYPTION_KEY,
                    'id' => $id
                ]);
                $item = $stmt->fetch();
            } catch (Exception $e) {
                error_log("CMS\Blogger->getItemById() Failed to retrieve item details when searching by ID" . $e);
            }

            //Store details in relevant members
            $this->_id = $item['ID'];
            $this->_title = $item['Title'];
            $this->_first_name = $item['Firstname'];
            $this->_surname = $item['Surname'];
            $this->_img_filename = $item['ImgFilename'];
            $this->_img_path = $item['ImgPath'];
            $this->_content = $item['Content'];
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
                $stmt = $this->_dbconn->prepare("SELECT ID FROM Bloggers WHERE CONVERT(AES_DECRYPT(URLText, :key) USING utf8) LIKE :needle LIMIT 1");
                $stmt->execute([
                    'key'=> AES_ENCRYPTION_KEY,
                    'needle' => $urltext
                ]);
                $item = $stmt->fetch();
            } catch (Exception $e) {
                error_log("CMS\Blogger->getItemByUrl() Failed to retrieve item details when searching by URLText" . $e);
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
                throw new Exception("CMS\Blogger->uploadImage() You must supply a file stream to this function.");
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
            $ImgObj->createFilename(FixOutput($this->_title."_".$this->_first_name."_".$this->_surname));


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
                error_log("CMS\Blogger->deleteImage() Sorry - there was no image to delete");

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
            //First need to determine if this is a new News item
            if ($this->_id <= 0) {
                $this->createNewItem(); //_id should now be set
            }

            try {
                $stmt = $this->_dbconn->prepare("UPDATE Bloggers SET Title = AES_ENCRYPT(:title, :key), Firstname = AES_ENCRYPT(:first_name, :key), Surname = AES_ENCRYPT(:surname, :key), URLText = AES_ENCRYPT(:url_text, :key), MetaTitle = AES_ENCRYPT(:meta_title, :key), MetaDesc = AES_ENCRYPT(:meta_desc, :key), MetaKey = AES_ENCRYPT(:meta_key, :key), ImgFilename = AES_ENCRYPT(:img_filename, :key), ImgPath = AES_ENCRYPT(:img_path, :key), Content = AES_ENCRYPT(:content, :key), DateEdited = NOW(), AuthorID = :author_id, AuthorName = AES_ENCRYPT(:author_name, :key) WHERE ID = :id LIMIT 1");
                $result = $stmt->execute([
                    'key' => AES_ENCRYPTION_KEY,
                    'title' => $this->_title,
                    'first_name' => $this->_first_name,
                    'surname' => $this->_surname,
                    'url_text' => $this->_url_text,
                    'meta_title' => $this->_meta_title,
                    'meta_desc' => $this->_meta_desc,
                    'meta_key' => $this->_meta_key,
                    'img_filename' => $this->_img_filename,
                    'img_path' => $this->_img_path,
                    'content' => $this->_content,
                    'author_id' => $this->_author_id,
                    'author_name' => $this->_author_name,
                    'id' => $this->_id
                ]);
                if ($result === true) {
                    return true;
                } else {
                    //print_r($stmt->errorInfo()[2]);
                    //die();
                    error_log($stmt->errorInfo()[2]);
                    return false;
                }
            } catch (Exception $e) {
                error_log("CMS\Blogger->saveItem() Failed to save record: " . $e);
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
                throw new Exception('CMS\Blogger->createNewItem() You cannot create a new item at this stage - the id property is already set as ' . $this->_id);
            }

            //Create DB item
            try {
                $result = $this->_dbconn->query("INSERT INTO Bloggers SET Title = ''");
                $lastID = $this->_dbconn->lastInsertId();

                if ($lastID > 0) {
                    $this->_id = $lastID;
                }
            } catch (Exception $e) {
                error_log("CMS\Blogger->createNewItem() Failed to create new record: " . $e);
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
        public function listAllItems($passedNeedle = '', $passedMode = 'surname')
        {
            $basesql = "SELECT Bloggers.ID, COUNT(Blogs.ID) AS NumBlogs FROM Bloggers LEFT JOIN Blogs ON Blogs.BloggerID = Bloggers.ID WHERE ";
            $params = array();
        
            //Build SQL depending on passedMode and passedNeedle
            switch ($passedMode) {
                case 'id':
                    $query = "Bloggers.ID = :needle ";
                    $order = "ORDER BY AES_DECRYPT(Bloggers.Surname, :key) ASC";
                    $params['key'] = AES_ENCRYPTION_KEY;
                    $params['needle'] = $passedNeedle;
                    break;
                    
                case 'surname':
                    $query = "(CONVERT(AES_DECRYPT(Bloggers.Surname, :key) USING utf8) LIKE :needle) ";
                    $order = "ORDER BY AES_DECRYPT(Bloggers.Surname, :key) ASC";
                    $passedNeedle = $passedNeedle . "%";
                    $params['key'] = AES_ENCRYPTION_KEY;
                    $params['needle'] = $passedNeedle;
                    break;
            
                default:
                    $query = "(CONVERT(AES_DECRYPT(Bloggers.Surname, :key) USING utf8) LIKE :needle) ";
                    $order = "ORDER BY AES_DECRYPT(SBloggers.Surname, :key) ASC";
                    $passedNeedle = $passedNeedle . "%";
                    $params['key'] = AES_ENCRYPTION_KEY;
                    $params['needle'] = $passedNeedle;
                    break;
            }
        
            //echo $basesql . $query . $order;
            //echo $passedNeedle;
        
            //Carry out the query
            $stmt = $this->_dbconn->prepare($basesql . $query . " GROUP BY Bloggers.ID " . $order);
            $stmt->execute($params);
        
            //Prepare results array
            $results = array();
        
            //Work through results from query
            while ($this_res = $stmt->fetch()) {
                $mem = $this->getItemByID($this_res['ID']);
                $mem['NumBlogs'] = $this_res['NumBlogs'];
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
                throw new Exception('CMS\Blogger->deleteItem() requires the item ID to be set if you are trying to delete the item');
            }

            //First delete the image files
            $this->deleteImage();

            // Step through all images for this content item AND DELETE
            $ImgDel = new ImageLibrary('Bloggers', $this->_id, null, null, null, USER_UPLOADS.'/images/bloggers/');
            $ImgDel->deleteAllImagesForContent('Bloggers',$this->_id);
            
            //Now reset any blogs for this blogger
            $stmt = $this->_dbconn->prepare("UPDATE Blogs SET BloggerID = null WHERE BloggerID = :id");
            $result = $stmt->execute([
                'id' => $this->_id
            ]);

            //Now delete the item from the DB
            try {
                $stmt = $this->_dbconn->prepare("DELETE FROM Bloggers WHERE ID = :id LIMIT 1");
                $result = $stmt->execute([
                                             'id' => $this->_id
                                         ]);
            } catch (Exception $e) {
                error_log("CMS\Blogger->deleteItem() Failed to delete record: " . $e);
            }

            if ($result === true) {
                //Unset the properties
                $this->_id = null;
                $this->_title = null;
                $this->_img_filename = null;
                $this->_content = null;
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
         * Function to check if a similar URL already exists in the News table
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
                throw new Exception('CMS\Blogger->URLTextValid() needs the new URL specifying as a string');
            }


            if (clean_int($ID) > 0) {
                $sql = "SELECT ID FROM Bloggers WHERE URLText = :urltext AND ID != :id";
                $vars = array('urltext' => $ContentURL, 'id' => $ID);
            } else {
                $sql = "SELECT ID FROM Bloggers WHERE URLText = :urltext AND (ID IS NULL OR ID <= 0)";
                $vars = array('urltext' => $ContentURL);
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
            $sql = "SELECT ID FROM Bloggers WHERE (CONVERT(AES_DECRYPT(Firstname, :key) USING utf8) LIKE :needle OR CONVERT(AES_DECRYPT(Surname, :key) USING utf8) LIKE :needle OR CONVERT(AES_DECRYPT(Content, :key) USING utf8) LIKE :needle) ";
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
                    $link = "//" . SITEFQDN . "/blogger/" . $content['URLText'];
                } else {
                    $link = "//" . SITEFQDN . "/blogs/blogger-detail.php?id=" . $content['ID'];
                }

                //Prepare content
                $Content = FixOutput(substr(strip_tags($content['Content']), 0, 160));

                //Check weighting
                if (strtolower($content['Surname']) == $search_field || strtolower($content['Firstname']) == $search_field) {
                    $Weighting = 0;
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
        public function getFirstName()
        {
            return $this->_first_name;
        }
    
        /**
         * @param mixed $first_name
         */
        public function setFirstName($first_name): void
        {
            $this->_first_name = $first_name;
        }
    
        /**
         * @return mixed
         */
        public function getSurname()
        {
            return $this->_surname;
        }
    
        /**
         * @param mixed $surname
         */
        public function setSurname($surname): void
        {
            $this->_surname = $surname;
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
    }