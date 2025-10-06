<?php

    namespace PeterBourneComms\CMS;

    use PeterBourneComms\CMS\Database;
    use PeterBourneComms\CMS\ImageHandler;
    use PDO;
    use PDOException;
    use Exception;

    /**
     * Deals with Project items
     *
     *
     *
     * @author Peter Bourne
     * @version 1.1
     *
     *          1.0     --------    Original version
     *          1.1     26.04.21    New version for encrypted Project DB
     *
     */
    class Project
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
        protected $_sub_title;
        protected $_menu_title;
        /**
         * @var
         */
        protected $_img_filename;
        /**
         * @var
         */
        protected $_content;
        protected $_col2_content;
        protected $_col3_content;
        /**
         * @var
         */
        protected $_date_display;
        /**
         * @var
         */
        protected $_author_id;
        /**
         * @var
         */
        protected $_author_name;

        protected $_urltext;
        protected $_metadesc;
        protected $_metakey;
        protected $_metatitle;
        protected $_display_order;


        /**
         * @var
         */
        protected $_allitems;


        /**
         * Project constructor.
         *
         * @param null $id
         * @param int $width
         * @param int $height
         * @param string $path
         *
         * @throws Exception
         */
        public function __construct($id = null, $width = 1400, $height = 546, $path = USER_UPLOADS . '/images/projects/')
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
                //Assess passed carousel id
                if (isset($id) && !is_numeric($id)) {
                    throw new Exception('Class Project requires id to be specified as an integer');
                }

                //Assess passed width & height
                if (!is_numeric($width) || !is_numeric($height)) {
                    throw new Exception('Class Project requires width and height to be specified as integers');
                }

                //Assess passed path
                if (isset($path) && !is_string($path)) {
                    throw new Exception('Class Project requires path to be specified as a string, eg: /user_uploads/images/projects/');
                }

                //See if provided path exists - if not - create it
                if (!file_exists(DOCUMENT_ROOT . $path)) {
                    //Create it
                    $success = mkdir(DOCUMENT_ROOT . $path, 0777, true);
                    if (!$success) {
                        throw new Exception('Directory specified (' . $path . ') does not exist - and cannot be created');
                    }
                }

                //See if subdirectories for 'large' and 'small' images exist in the path specified - if not create them. If fail - Throw Exception
                $large = $path . "large/";
                $small = $path . "small/";
                if (!file_exists(DOCUMENT_ROOT . $large)) {
                    //Create it
                    $success = mkdir(DOCUMENT_ROOT . $large, 0777, true);
                    if (!$success) {
                        throw new Exception('Directory specified (' . $large . ') does not exist - and cannot be created');
                    }
                }
                if (!file_exists(DOCUMENT_ROOT . $small)) {
                    //Create it
                    $success = mkdir(DOCUMENT_ROOT . $small, 0777, true);
                    if (!$success) {
                        throw new Exception('Directory specified (' . $small . ') does not exist - and cannot be created');
                    }
                }


                //Retrieve current Projects information
                if (isset($id)) {
                    $this->_id = $id;
                    $this->getItemById($id);
                }

                //Store the width/height/path etc
                $this->_img_width = $width;
                $this->_img_height = $height;
                $this->_img_path = $path;

            }
        }


        /**
         * Retrieves specified Projects record ID from Test table
         * Populates object member elements
         *
         * @param $id
         */
        public function getItemById($id)
        {
            try {
                $stmt = $this->_dbconn->prepare("SELECT ID, AES_DECRYPT(Title, :key) AS Title, AES_DECRYPT(SubTitle, :key) AS SubTitle, AES_DECRYPT(MenuTitle, :key) AS MenuTitle, AES_DECRYPT(Content, :key) AS Content, AES_DECRYPT(Col2Content, :key) AS Col2Content, AES_DECRYPT(Col3Content, :key) AS Col3Content, AES_DECRYPT(ImgFilename, :key) AS ImgFilename, AES_DECRYPT(ImgPath, :key) AS ImgPath, DateDisplay, AuthorID, AES_DECRYPT(AuthorName, :key) AS AuthorName, DisplayOrder, AES_DECRYPT(URLText, :key) AS URLText, AES_DECRYPT(MetaDesc, :key) AS MetaDesc, AES_DECRYPT(MetaKey, :key) AS MetaKey, AES_DECRYPT(MetaTitle, :key) AS MetaTitle FROM Projects WHERE ID =  :id LIMIT 1");
                $stmt->execute([
                    'key' => AES_ENCRYPTION_KEY,
                    'id' => $id
                ]);
                $item = $stmt->fetch();
            } catch (Exception $e) {
                error_log("Failed to retrieve Projects item details when searching by ID" . $e);
            }

            //Store details in relevant members
            $this->_id = $item['ID'];
            $this->_title = $item['Title'];
            $this->_sub_title = $item['SubTitle'];
            $this->_menu_title = $item['MenuTitle'];
            $this->_content = $item['Content'];
            $this->_col2_content = $item['Col2Content'];
            $this->_col3_content = $item['Col3Content'];
            $this->_img_filename = $item['ImgFilename'];
            $this->_img_path = $item['ImgPath'];
            $this->_date_display = $item['DateDisplay'];
            $this->_author_id = $item['AuthorID'];
            $this->_author_name = $item['AuthorName'];
            $this->_display_order = $item['DisplayOrder'];
            $this->_urltext = $item['URLText'];
            $this->_metadesc = $item['MetaDesc'];
            $this->_metakey = $item['MetaKey'];
            $this->_metatitle = $item['MetaTitle'];

            return $item;
        }

        public function getItemByUrl($urltext)
        {
            try {
                $stmt = $this->_dbconn->prepare("SELECT ID FROM Projects WHERE (CONVERT(AES_DECRYPT(URLText, :key) USING utf8) LIKE :urltext) LIMIT 1");
                $stmt->execute([
                    'key' => AES_ENCRYPTION_KEY,
                    'urltext' => $urltext
                ]);
                $item = $stmt->fetch();
            } catch (Exception $e) {
                error_log("Failed to retrieve Projects item details when searching by URLText" . $e);
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
                throw new Exception("You must supply a file stream to this function.");
            }

            //Create ImageHandler
            $ImgObj = new ImageHandler($this->_img_path, true);
            //Set up some defaults
            //Thumb width of 600.
            $aspect = $this->_img_height / $this->_img_width;
            $thumb_width = 600;
            $thumb_height = floor(600 * $aspect);

            $ImgObj->setSourceFileType('png');
            $ImgObj->setDestFileType('jpg');
            $ImgObj->setImageWidth($this->_img_width);
            $ImgObj->setImageHeight($this->_img_height);
            $ImgObj->setThumbWidth($thumb_width);
            $ImgObj->setThumbHeight($thumb_height);
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
         * Delete the image for this Projects item - assuming _img_filename is set
         *
         * @return mixed
         */
        public function deleteImage()
        {
            if (!is_string($this->_img_filename) || $this->_img_filename == '') {
                error_log("Sorry - there was no image to delete");

                return;
            }

            $OldImg = new ImageHandler($this->_img_path, true);
            $OldImg->setImgFilename($this->_img_filename);
            $OldImg->deleteImage();

            $this->_img_filename = '';
            $this->saveItem();
        }

        /**
         * Saves the current object to the Projects table in the database
         *
         * @throws Exception
         */
        public function saveItem()
        {
            //First need to determine if this is a new Projects item
            if ($this->_id <= 0) {
                $this->createNewItem(); //_id should now be set
            }

            try {
                $stmt = $this->_dbconn->prepare("UPDATE Projects SET Title = AES_ENCRYPT(:title, :key), SubTitle = AES_ENCRYPT(:subtitle, :key), MenuTitle = AES_ENCRYPT(:menutitle, :key), Content = AES_ENCRYPT(:content, :key), Col2Content = AES_ENCRYPT(:col2content, :key), Col3Content = AES_ENCRYPT(:col3content, :key), ImgFilename = AES_ENCRYPT(:imgfilename, :key), ImgPath = AES_ENCRYPT(:imgpath, :key), DateDisplay = :datedisplay, AuthorID = :authorid, AuthorName = AES_ENCRYPT(:authorname, :key), DisplayOrder = :displayorder, URLText = AES_ENCRYPT(:urltext, :key), MetaDesc = AES_ENCRYPT(:metadesc, :key), MetaKey = AES_ENCRYPT(:metakey, :key), MetaTitle = AES_ENCRYPT(:metatitle, :key) WHERE ID = :id LIMIT 1");
                $result = $stmt->execute([
                    'key' => AES_ENCRYPTION_KEY,
                    'title' => $this->_title,
                    'subtitle' => $this->_sub_title,
                    'menutitle' => $this->_menu_title,
                    'content' => $this->_content,
                    'col2content' => $this->_col2_content,
                    'col3content' => $this->_col3_content,
                    'imgfilename' => $this->_img_filename,
                    'imgpath' => $this->_img_path,
                    'datedisplay' => $this->_date_display,
                    'authorid' => $this->_author_id,
                    'authorname' => $this->_author_name,
                    'displayorder' => $this->_display_order,
                    'urltext' => $this->_urltext,
                    'metadesc' => $this->_metadesc,
                    'metakey' => $this->_metakey,
                    'metatitle' => $this->_metatitle,
                    'id' => $this->_id
                ]);
                if ($result === true) {
                    return true;
                } else {
                    error_log($stmt->errorInfo()[2]);
                    return false;
                }
            } catch (Exception $e) {
                error_log("Failed to save Projects record: " . $e);
            }
        }

        /**
         * Create new empty Projects item
         *
         * Sets the _id property accordingly
         */
        public function createNewItem()
        {
            //Only execute if the _id property isn't already set
            if (is_numeric($this->_id) || $this->_id > 0) {
                throw new Exception('You cannot create a new Projects item at this stage - the id property is already set as ' . $this->_id);
            }

            //Create DB item
            try {
                $result = $this->_dbconn->query("INSERT INTO Projects SET Title = ''");
                $lastID = $this->_dbconn->lastInsertId();

                if ($lastID > 0) {
                    $this->_id = $lastID;
                }
            } catch (Exception $e) {
                error_log("Failed to create new Projects record: " . $e);
            }
        }

        /**
         * Function to return array of project records (as assoc array)
         *
         * @param string $passedNeedle
         * @param string $passedMode
         *
         * @return array
         */
        public function listAllItems($passedNeedle = '', $passedMode = 'title')
        {
            $basesql = "SELECT ID FROM Projects WHERE ";
            $params = array();

            //Build SQL depending on passedMode and passedNeedle
            switch ($passedMode) {
                case 'title':
                    $query = "(CONVERT(AES_DECRYPT(Title, :key) USING utf8) LIKE :needle) ";
                    $order = "ORDER BY DisplayOrder ASC, AES_DECRYPT(Title, :key) ASC, DateDisplay DESC";
                    $passedNeedle = $passedNeedle . "%";
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
            //echo $passedNeedle;

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
         * Delete the complete Projects item - including any images
         *
         * @return mixed
         */
        public function deleteItem()
        {
            if (!is_numeric($this->_id)) {
                throw new Exception('Class Project requires the Projects item ID to be set if you are trying to delete the item');
            }

            //First delete the image files
            $this->deleteImage();

            // Step through all images for this content item AND DELETE
            $ImgDel = new ImageLibrary('Projects', $this->_id, null, null, null, USER_UPLOADS . '/images/gallery/');
            $ImgDel->deleteAllImagesForContent('Projects', $this->_id);

            //Now delete the item from the DB
            try {
                $stmt = $this->_dbconn->prepare("DELETE FROM Projects WHERE ID = :id LIMIT 1");
                $result = $stmt->execute([
                    'id' => $this->_id
                ]);
            } catch (Exception $e) {
                error_log("Failed to delete Projects record: " . $e);
            }

            if ($result === true) {
                //Unset the properties
                $this->_id = null;
                $this->_title = null;
                $this->_img_filename = null;
                $this->_content = null;
                $this->_display_order = null;
                $this->_date_display = null;
                $this->_author_id = null;
                $this->_author_name = null;
                $this->_urltext = null;
                $this->_metadesc = null;
                $this->_metakey = null;
                $this->_metatitle = null;

                return true;
            } else {
                return false;
            }

        }


        /**
         * Function to check if a similar URL already exists in the table
         * Returns TRUE if VALID, ie: not present in database
         *
         *
         *
         * @param int $ID
         * @param     $ContentURL
         *
         * @return bool
         * @throws Exception
         */

        public function URLTextValid($ID = 0, $ContentURL)
        {
            if ($ID <= 0)
            {
                $ID = $this->_id;
            }
            if (!is_string($ContentURL))
            {
                throw new Exception('Project needs the new URL specifying as a string');
            }


            if (clean_int($ID) > 0)
            {
                $sql = "SELECT ID FROM Projects WHERE CONVERT(AES_DECRYPT(URLText, :key) USING utf8) = :urltext AND ID != :id";
                $vars = array('urltext' => $ContentURL, 'id' => $ID, 'key' => AES_ENCRYPTION_KEY);
            }
            else
            {
                $sql = "SELECT ID FROM Projects WHERE CONVERT(AES_DECRYPT(URLText, :key) USING utf8) = :urltext AND (ID IS NULL OR ID <= 0)";
                $vars = array('urltext' => $ContentURL, 'key' => AES_ENCRYPTION_KEY);
            }

            // Execute query
            $stmt = $this->_dbconn->prepare($sql);
            $result = $stmt->execute($vars);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($rows) > 0)
            {
                return false;
            }
            else
            {
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
            if ($needle == '')
            {
                return array();
            }

            //$results = array('Level1'=>array(),'Level2'=>array(),'Level3'=>array());
            $search_field = strtolower(filter_var($needle, FILTER_SANITIZE_STRING));
            $search_criteria = "%" . $needle . "%";

            $search_results = array();

            //Search content as outlined above
            //Return IDs
            $sql = "SELECT ID FROM Projects WHERE (CONVERT(AES_DECRYPT(Title, :key) USING utf8) LIKE :needle OR CONVERT(AES_DECRYPT(MenuTitle, :key) USING utf8) LIKE :needle OR CONVERT(AES_DECRYPT(Content, :key) USING utf8) LIKE :needle OR CONVERT(AES_DECRYPT(Col2Content, :key) USING utf8) LIKE :needle OR CONVERT(AES_DECRYPT(Col3Content, :key) USING utf8) LIKE :needle) ";
            $stmt = $this->_dbconn->prepare($sql);
            $stmt->execute([
                'key' => AES_ENCRYPTION_KEY,
                'needle' => $search_criteria
            ]);
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
            {
                //Retrieve full data
                $content = $this->getItemById($row['ID']);

                //Prepare link
                unset($link);
                if ($content['URLText'] != '')
                {
                    $link = "//" . SITEFQDN . "/project/" . $content['URLText'];
                }
                else
                {
                    $link = "//" . SITEFQDN . "/content/project-detail.php?id=" . $content['ID'];
                }

                //Prepare content
                $Content = FixOutput(substr(strip_tags($content['Content']), 0, 160));

                //Check weighting
                if (strtolower($content['Title']) == $search_field)
                {
                    $Weighting = 0;
                }
                elseif ($search_field == substr(strtolower($content['Title']), 0, strlen($search_field)))
                {
                    $Weighting = 10;
                }
                else
                {
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
         * @return string
         */
        public function getImgPath(): string
        {
            return $this->_img_path;
        }

        /**
         * @param string $img_path
         */
        public function setImgPath(string $img_path): void
        {
            $this->_img_path = $img_path;
        }

        /**
         * @return mixed
         */
        public function getTitle()
        {
            return $this->_title;
        }

        /**
         * @param mixed $title
         */
        public function setTitle($title): void
        {
            $this->_title = $title;
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
        public function getMenuTitle()
        {
            return $this->_menu_title;
        }

        /**
         * @param mixed $menu_title
         */
        public function setMenuTitle($menu_title): void
        {
            $this->_menu_title = $menu_title;
        }

        /**
         * @return mixed
         */
        public function getImgFilename()
        {
            return $this->_img_filename;
        }

        /**
         * @param mixed $img_filename
         */
        public function setImgFilename($img_filename): void
        {
            $this->_img_filename = $img_filename;
        }

        /**
         * @return mixed
         */
        public function getContent()
        {
            return $this->_content;
        }

        /**
         * @param mixed $content
         */
        public function setContent($content): void
        {
            $this->_content = $content;
        }

        /**
         * @return mixed
         */
        public function getCol2Content()
        {
            return $this->_col2_content;
        }

        /**
         * @param mixed $col2_content
         */
        public function setCol2Content($col2_content): void
        {
            $this->_col2_content = $col2_content;
        }

        /**
         * @return mixed
         */
        public function getCol3Content()
        {
            return $this->_col3_content;
        }

        /**
         * @param mixed $col3_content
         */
        public function setCol3Content($col3_content): void
        {
            $this->_col3_content = $col3_content;
        }

        /**
         * @return mixed
         */
        public function getDateDisplay()
        {
            return $this->_date_display;
        }

        /**
         * @param mixed $date_display
         */
        public function setDateDisplay($date_display): void
        {
            if ($date_display == '' || $date_display == '0000-00-00') { $date_display = null; }
            $this->_date_display = $date_display;
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
        public function setAuthorId($author_id): void
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
        public function setAuthorName($author_name): void
        {
            $this->_author_name = $author_name;
        }

        /**
         * @return mixed
         */
        public function getUrltext()
        {
            return $this->_urltext;
        }

        /**
         * @param mixed $urltext
         */
        public function setUrltext($urltext): void
        {
            $this->_urltext = $urltext;
        }

        /**
         * @return mixed
         */
        public function getMetadesc()
        {
            return $this->_metadesc;
        }

        /**
         * @param mixed $metadesc
         */
        public function setMetadesc($metadesc): void
        {
            $this->_metadesc = $metadesc;
        }

        /**
         * @return mixed
         */
        public function getMetakey()
        {
            return $this->_metakey;
        }

        /**
         * @param mixed $metakey
         */
        public function setMetakey($metakey): void
        {
            $this->_metakey = $metakey;
        }

        /**
         * @return mixed
         */
        public function getMetatitle()
        {
            return $this->_metatitle;
        }

        /**
         * @param mixed $metatitle
         */
        public function setMetatitle($metatitle): void
        {
            $this->_metatitle = $metatitle;
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
        public function setDisplayOrder($display_order): void
        {
            if (!is_numeric($display_order) || $display_order <= 0) { $display_order = null; }
            $this->_display_order = $display_order;
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
        public function setAllitems($allitems): void
        {
            $this->_allitems = $allitems;
        }

        /**
         * @return int|string
         */
        public function getImgWidth()
        {
            return $this->_img_width;
        }

        /**
         * @param int|string $img_width
         */
        public function setImgWidth($img_width): void
        {
            $this->_img_width = $img_width;
        }

        /**
         * @return int|string
         */
        public function getImgHeight()
        {
            return $this->_img_height;
        }

        /**
         * @param int|string $img_height
         */
        public function setImgHeight($img_height): void
        {
            $this->_img_height = $img_height;
        }

        

    }