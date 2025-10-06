<?php

    namespace PeterBourneComms\CMS;
    use PDO;
    use Exception;
    use PeterBourneComms\CMS\Database;
    use PeterBourneComms\CMS\ImageHandler;

    /**
     * Deals with People items - for General PB CMS site
     *
     * @version 1.1
     * @history
     *
     * 1.0      27/05/2022  Original - adapted from EACR - with added meta stuff
     * 1.1      04/06/2022  Moved from TheARR into general CMS code
     *
     *
     *
     */
    class People
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
        protected $_img_filename;
        /**
         * @var
         */
        protected $_content;
        /**
         * @var
         */
        protected $_author_id;
        /**
         * @var
         */
        protected $_author_name;

        protected $_display_order;
        protected $_first_name;
        protected $_surname;
        protected $_title;
        protected $_email;
        protected $_telephone;

        protected $_section;
        protected $_contact_type_order;
        protected $_contact_type;

        protected $_link;
        
        protected $_url_text;
        protected $_meta_title;
        protected $_meta_desc;
        protected $_meta_key;


        /**
         * People constructor.
         *
         * @param null   $id
         * @param int    $width
         * @param int    $height
         * @param string $path
         *
         * @throws Exception
         */
        public function __construct($id = null, $width = 400, $height = 400, $path = USER_UPLOADS.'/images/people/')
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
                //Assess passed id
                if ($id != null && !is_numeric($id))
                {
                    throw new Exception('CMS\People->__construct() requires id to be specified as an integer');
                }

                //Assess passed width & height
                if (!is_numeric($width) || !is_numeric($height))
                {
                    throw new Exception('CMS\People->__construct() requires width and height to be specified as integers');
                }

                //Assess passed path
                if (isset($path) && !is_string($path))
                {
                    throw new Exception('CMS\People->__construct()requires path to be specified as a string, eg: /user_uploads/images/people/');
                }

                //See if provided path exists - if not - create it
                if (!file_exists(DOCUMENT_ROOT . $path))
                {
                    //Create it
                    $success = mkdir(DOCUMENT_ROOT . $path, 0777, true);
                    if (!$success)
                    {
                        throw new Exception('CMS\People->__construct() Directory specified ('.$path.') does not exist - and cannot be created');
                    }
                }

                //See if subdirectories for 'large' and 'small' images exist in the path specified - if not create them. If fail - Throw Exception
                $large = $path . "large/";
                $small = $path . "small/";
                if (!file_exists(DOCUMENT_ROOT . $large))
                {
                    //Create it
                    $success = mkdir(DOCUMENT_ROOT . $large, 0777, true);
                    if (!$success)
                    {
                        throw new Exception('CMS\People->__construct() Directory specified (' . $large . ') does not exist - and cannot be created');
                    }
                }
                if (!file_exists(DOCUMENT_ROOT . $small))
                {
                    //Create it
                    $success = mkdir(DOCUMENT_ROOT . $small, 0777, true);
                    if (!$success)
                    {
                        throw new Exception('CMS\People->__construct() Directory specified (' . $small . ') does not exist - and cannot be created');
                    }
                }

                //Retrieve current news information
                if (isset($id))
                {
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
         * Retrieves specified content record ID from People table
         * Populates object member elements
         *
         * @param int   $id
         */
        public function getItemById($id)
        {
            try
            {
                $stmt = $this->_dbconn->prepare("SELECT * FROM People WHERE ID =  :id LIMIT 1");
                $stmt->execute(['id' => $id]);
                $story = $stmt->fetch();
            } catch (Exception $e)
            {
                error_log("CMS\People->__construct()Failed to retrieve People item details when searching by ID" . $e);
            }

            //Store details in relevant members
            $this->_id = $story['ID'];
            $this->_section = $story['Section'];
            $this->_first_name = $story['Firstname'];
            $this->_surname = $story['Surname'];
            $this->_title = $story['Title'];
            $this->_content = $story['Content'];
            $this->_email = $story['Email'];
            $this->_telephone = $story['Telephone'];
            $this->_img_filename = $story['ImgFilename'];
            $this->_img_path = $story['ImgPath'];
            $this->_display_order = $story['DisplayOrder'];
            $this->_author_id = $story['AuthorID'];
            $this->_author_name = $story['AuthorName'];
            $this->_contact_type = $story['ContactType'];
            $this->_contact_type_order = $story['ContactTypeOrder'];
            $this->_link = $story['Link'];
            $this->_url_text = $story['URLText'];
            $this->_meta_title = $story['MetaTitle'];
            $this->_meta_desc = $story['MetaDesc'];
            $this->_meta_key = $story['MetaKey'];

            return $story;
        }
    
    
        public function getItemByUrl($urltext)
        {
            try
            {
                $stmt = $this->_dbconn->prepare("SELECT ID FROM People WHERE URLText LIKE :urltext LIMIT 1");
                $stmt->execute(['urltext' => $urltext]);
                $item = $stmt->fetch();
            } catch (Exception $e)
            {
                error_log("CMS\People->getItemByUrl() Failed to retrieve People record details when searching by URLText" . $e);
            }
            //Use other function to populate rest of properties (no sense writing it twice!)_
            return $this->getItemById($item['ID']);
        }
        

        /**
         * List Contact types in DB
         *
         * @return mixed
         */
        public function listPeopleTypes()
        {
            $sql = "SELECT DISTINCT(ContactType) AS ContactType, ContactTypeOrder FROM People ORDER BY ContactTypeOrder ASC";
            $stmt = $this->_dbconn->query($sql);
            $types = $stmt->fetchAll();
            
            //Step through $types to make sure no null values
            $newtypes = array();
            foreach($types as $type) {
                if ($type['ContactType'] != '' && !in_array_r($type['ContactType'], $newtypes)) {
                    $newtypes[] = $type;
                }
            }
            //Add one dummy record in
            if (count($newtypes) == 0) {
                $newtypes[] = array('ContactType'=>'','ContactTypeOrder'=>1);
            }
            return $newtypes;
        }

        /**
         * Retrieves all People records for a specified section
         * Doesn't use the getContent functions - as we don't want to populate this object - just return an array of data.
         *
         *
         * All table fields are returned.
         *
         * @param string   $section     Supply the section for the Person
         * @param string   $type        Supply the ContactType - if 'null' -> return only 'empty' ContactType; if 'all' -> ignore and return all people
         *
         * @return mixed
         */
        public function listPeopleBySection($section = null, $type = null)
        {
            if ($section == '')
            {
                $sql = "SELECT People.* FROM People ";
                $params = array();
            }
            else
            {
                $sql = "SELECT People.* FROM People WHERE People.Section = :section ";
                $params['section'] = $section;
            }

            //Default order (may be over-ridden)
            $order = "ORDER BY People.DisplayOrder ASC, People.Surname ASC";

            if ($type != '')
            {
                if ($type == 'all')
                {
                    //$sql .= " AND (People.ContactType = :type AND People.ContactType IS NOT NULL) ";
                    //$params['type'] = $type;
                    $order = "ORDER BY People.Surname ASC";
                }
                else
                {
                    $sql .= " AND (People.ContactType = :type AND People.ContactType IS NOT NULL) ";
                    $params['type'] = $type;
                }
            }
            else {
                $sql .= " AND ((People.ContactType IS NULL OR People.ContactType = '')) ";
            }

            /*echo $sql.$order."<br/>";
            echo $section."<br/>";
            echo $type."<br/>";
*/
            try {
                $stmt = $this->_dbconn->prepare($sql.$order);
                $stmt->execute($params);
                $ret_arr = array();
                while ($story = $stmt->fetch())
                {
                    $ret_arr[] = $story;
                }
                $stories = $ret_arr;
                //$stories = $stmt->fetchAll();
            } catch (Exception $e)
            {
                error_log("CMS\People->listPeopleBySection() Failed to retrieve People records" . $e);
            }

            return $stories;
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
        public function uploadImage($ImageStream)
        {
            //Check we have some useful data passed
            if ($ImageStream == '')
            {
                throw new Exception("CMS\People->uploadImage() You must supply a file stream (data:image/png;base64) to this function.");
            }


            //Create ImageHandler
            $ImgObj = new ImageHandler($this->_img_path, true);
            //Set up some defaults
            $ImgObj->setSourceFileType('png');
            $ImgObj->setDestFileType('jpg');
            $ImgObj->setImageWidth($this->_img_width);
            $ImgObj->setImageHeight($this->_img_height);
            $ImgObj->setThumbWidth(floor($this->_img_width / 2));
            $ImgObj->setThumbHeight(floor($this->_img_height / 2));
            $ImgObj->createFilename($this->_first_name." ".$this->_surname);


            $result = $ImgObj->processImage($ImageStream);
            $newFilename = $ImgObj->getImgFilename();

            if ($result == true)
            {
                //Delete the old image
                $this->deleteImage();

                //Store the new filename
                $this->_img_filename = $newFilename;

                //Save the object in its current state
                $this->saveItem();
            }
        }


        /**
         * Create new empty content item
         *
         * Sets the _id property accordingly
         */
        public function createNewItem()
        {
            //Only execute if the _id property isn't already set
            if (is_numeric($this->_id) || $this->_id > 0)
            {
                throw new Exception('CMS\People->createNewItem() You cannot create a new People item at this stage - the id property is already set as '.$this->_id);
            }

            //Create DB item
            try
            {
                $result = $this->_dbconn->query("INSERT INTO People SET Surname = ''");
                $lastID = $this->_dbconn->lastInsertId();

                if ($lastID > 0)
                {
                    $this->_id = $lastID;
                }
            }
            catch (Exception $e) {
                error_log("CMS\People->createNewItem() Failed to create new People record: " . $e);
            }
        }



        /**
         * Saves the current object to the People table in the database
         *
         * @throws Exception
         */
        public function saveItem()
        {
            //First need to determine if this is a new item
            if ($this->_id <= 0)
            {
                $this->createNewItem(); //_id should now be set
            }

            try {
                $stmt = $this->_dbconn->prepare("UPDATE People SET Section = :section, Firstname = :firstname, Surname = :surname, Title = :title, Content = :content, Email = :email, Telephone = :telephone, ImgFilename = :imgfilename, ImgPath = :imgpath, DisplayOrder = :displayorder, AuthorID = :authorid, AuthorName = :authorname, ContactTypeOrder = :contacttypeorder, ContactType = :contacttype, Link = :link, URLText = :url_text, MetaTitle = :meta_title, MetaDesc = :meta_desc, MetaKey = :meta_key WHERE ID = :id LIMIT 1");

                $result = $stmt->execute([
                    'section' => $this->_section,
                    'firstname' => $this->_first_name,
                    'surname' => $this->_surname,
                    'title' => $this->_title,
                    'content' => $this->_content,
                    'email' => $this->_email,
                    'telephone' => $this->_telephone,
                    'imgfilename' => $this->_img_filename,
                    'imgpath' => $this->_img_path,
                    'displayorder' => $this->_display_order,
                    'authorid' => $this->_author_id,
                    'authorname' => $this->_author_name,
                    'contacttypeorder' => $this->_contact_type_order,
                    'contacttype' => $this->_contact_type,
                    'link' => $this->_link,
                    'url_text' => $this->_url_text,
                    'meta_title' => $this->_meta_title,
                    'meta_desc' => $this->_meta_desc,
                    'meta_key' => $this->_meta_key,
                    'id' => $this->_id
                               ]);
                if ($result == true) {
                    return true;
                } else {
                    error_log($stmt->errorInfo()[2]);
                    return false;
                }
            } catch (Exception $e) {
                error_log("CMS\People->saveItem() Failed to save People record: " . $e);
            }
        }

        /**
         * Delete the image for this content item - assuming _img_filename is set
         *
         * @return mixed
         */
        public function deleteImage()
        {
            if (!is_string($this->_img_filename) || $this->_img_filename == '')
            {
                return false;
            }


            $OldImg = new ImageHandler($this->_img_path,false);
            $OldImg->setImgFilename($this->_img_filename);
            $OldImg->deleteImage();

            $this->_img_filename = '';
            try {
                $this->saveItem();
            } catch (Exception $e) {
                error_log('CMS\People->deleteImage() Failed to delete image for People table');
            }
        }



        /**
         * Delete the complete content item - including any images
         *
         * @return mixed
         * @throws Exception
         */
        public function deleteItem()
        {
            if (!is_numeric($this->_id))
            {
                throw new Exception('CMS\People->deleteItem() requires the content item ID to be set if you are trying to delete the item');
            }

            //First delete the image files
            $this->deleteImage();

            //Now delete the item from the DB
            try {
                $stmt = $this->_dbconn->prepare("DELETE FROM People WHERE ID = :id LIMIT 1");
                $result = $stmt->execute([
                                   'id' => $this->_id
                               ]);
            } catch (Exception $e) {
                error_log("CMS\People->deleteItem() Failed to delete People record: " . $e);
            }

            if ($result == true)
            {
                //Unset the properties
                $this->_id = null;
                $this->_section = null;
                $this->_title = null;
                $this->_first_name = null;
                $this->_surname = null;
                $this->_telephone = null;
                $this->_email = null;
                $this->_content = null;
                $this->_img_filename = null;
                $this->_display_order = null;
                $this->_author_id = null;
                $this->_author_name = null;
                $this->_link = null;
                $this->_contact_type = null;
                $this->_contact_type_order = null;

                return true;
            }
            else
            {
                return false;
            }

        }
    
    
        /**
         * Function to check if a similar URL already exists in the Job table
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
                throw new Exception('CMS\People->URLTextValid() Person needs the new URL specifying as a string');
            }
        
        
            if (clean_int($ID) > 0)
            {
                $sql = "SELECT ID FROM People WHERE URLText = :urltext AND ID != :id";
                $vars = array('urltext' => $ContentURL, 'id' => $ID);
            }
            else
            {
                $sql = "SELECT ID FROM People WHERE URLText = :urltext AND (ID IS NULL OR ID <= 0)";
                $vars = array('urltext' => $ContentURL);
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
         *  - Firstname
         *  - Surname
         *  - Specialism
         *  - Content
         *
         * Will return array of arrays:
         * array('ID','Title (full name),'SubTitle (Specialism)','FullURLText','Weight');
         *
         * @param   mixed   $needle
         * @return  mixed   array
         */
        function searchContent($needle = '')
        {
            if ($needle == '') { return array(); }

            //$results = array('Level1'=>array(),'Level2'=>array(),'Level3'=>array());
            $search_field = strtolower(filter_var($needle, FILTER_SANITIZE_STRING));
            $search_criteria = "%".$needle."%";

            $search_results = array();

            //Search content as outlined above
            //Return IDs
            $sql = "SELECT ID FROM People WHERE Firstname LIKE :needle OR Surname LIKE :needle OR Content LIKE :needle";
            $stmt = $this->_dbconn->prepare($sql);
            $stmt->execute([
                'needle' => $search_criteria
                                     ]);
            while($row = $stmt->fetch(PDO::FETCH_ASSOC))
            {
                //Check if the item is available in a menu (ie:  Can be show in search results)
                //Retrieve full data
                $content = $this->getItemById($row['ID'], false);

                //Prepare link
                $link = "//".SITEFQDN."/content/people-detail.php?id=".$content['ID'];

                //Prepare content
                $Content = FixOutput(substr(strip_tags($content['Content']), 0, 160));

                //Check weighting
                if (strtolower($content['Firstname']) == $search_field || strtolower($content['Surname']) == $search_field)
                {
                    $Weighting = 0;
                }
                else
                {
                    $Weighting = 20;
                }
                $content['Weighting'] = $Weighting;

                //Add to search results
                $search_results[] = array('Title' => $content['Firstname']." ".$content['Surname'], 'Content' => $Content, 'Link' => $link, 'DateDisplay' => "", 'Weighting' => $Weighting);
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
        public function getImgPath()
        {
            return $this->_img_path;
        }

        /**
         * @param string $image_path
         */
        public function setImgPath($image_path)
        {
            $this->_img_path = $image_path;
        }

        /**
         * @return mixed
         */
        public function getImgfilename()
        {
            return $this->_img_filename;
        }

        /**
         * @param mixed $imgfilename
         */
        public function setImgfilename($imgfilename)
        {
            $this->_img_filename = $imgfilename;
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
        public function setContent($content)
        {
            $this->_content = $content;
        }

        /**
         * @return mixed
         */
        public function getAuthorid()
        {
            return $this->_author_id;
        }

        /**
         * @param mixed $authorid
         */
        public function setAuthorid($authorid)
        {
            $this->_author_id = $authorid;
        }

        /**
         * @return mixed
         */
        public function getAuthorname()
        {
            return $this->_author_name;
        }

        /**
         * @param mixed $authorname
         */
        public function setAuthorname($authorname)
        {
            $this->_author_name = $authorname;
        }

        /**
         * @return mixed
         */
        public function getDisplayorder()
        {
            return $this->_display_order;
        }

        /**
         * @param mixed $displayorder
         */
        public function setDisplayorder($displayorder)
        {
            $this->_display_order = $displayorder;
        }

        /**
         * @return mixed
         */
        public function getFirstname()
        {
            return $this->_first_name;
        }

        /**
         * @param mixed $firstname
         */
        public function setFirstname($firstname)
        {
            $this->_first_name = $firstname;
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
        public function setSurname($surname)
        {
            $this->_surname = $surname;
        }

        /**
         * @return mixed
         */
        public function getEmail()
        {
            return $this->_email;
        }

        /**
         * @param mixed $email
         */
        public function setEmail($email)
        {
            $this->_email = $email;
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
        public function setTitle($title)
        {
            $this->_title = $title;
        }

        /**
         * @return mixed
         */
        public function getTelephone()
        {
            return $this->_telephone;
        }

        /**
         * @param mixed $telephone
         */
        public function setTelephone($telephone)
        {
            $this->_telephone = $telephone;
        }

        /**
         * @return mixed
         */
        public function getSection()
        {
            return $this->_section;
        }

        /**
         * @param mixed $section
         */
        public function setSection($section)
        {
            $this->_section = $section;
        }

        /**
         * @return mixed
         */
        public function getContactTypeOrder()
        {
            return $this->_contact_type_order;
        }

        /**
         * @param mixed $contact_type_order
         */
        public function setContactTypeOrder($contact_type_order)
        {
            if (!is_numeric($contact_type_order) || $contact_type_order <= 0) { $contact_type_order = null; }
            $this->_contact_type_order = $contact_type_order;
        }

        /**
         * @return mixed
         */
        public function getContactType()
        {
            return $this->_contact_type;
        }

        /**
         * @param mixed $contact_type
         */
        public function setContactType($contact_type)
        {
            $this->_contact_type = $contact_type;
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
        public function getUrlText()
        {
            return $this->_url_text;
        }
    
        /**
         * @param mixed $url_text
         */
        public function setUrlText($url_text): void
        {
            $this->_url_text = $url_text;
        }
    
        /**
         * @return mixed
         */
        public function getMetaTitle()
        {
            return $this->_meta_title;
        }
    
        /**
         * @param mixed $meta_title
         */
        public function setMetaTitle($meta_title): void
        {
            $this->_meta_title = $meta_title;
        }
    
        /**
         * @return mixed
         */
        public function getMetaDesc()
        {
            return $this->_meta_desc;
        }
    
        /**
         * @param mixed $meta_desc
         */
        public function setMetaDesc($meta_desc): void
        {
            $this->_meta_desc = $meta_desc;
        }
    
        /**
         * @return mixed
         */
        public function getMetaKey()
        {
            return $this->_meta_key;
        }
    
        /**
         * @param mixed $meta_key
         */
        public function setMetaKey($meta_key): void
        {
            $this->_meta_key = $meta_key;
        }



    }