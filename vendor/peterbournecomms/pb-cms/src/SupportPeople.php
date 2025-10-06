<?php

    namespace PeterBourneComms\CMS;
    use PeterBourneComms\CMS\Database;
    use PeterBourneComms\CMS\ImageHandler;
    use PDO;
    use PDOException;
    use Exception;

    /**
     * Deals with SupportPeople items
     *
     * It will allow you to
     *  - specify the size of the top-of-page image
     *  - specify the location of stored images
     *  - pass an image in and have it resized to that size
     *  - stored in the filesystem and database
     *  - retrieve an individual content item
     *  - delete content item (including images - top level and library image)
     *  - retrieve the top level SupportTypeID (From SupportTypes - derived via SupportPeopleByType table)
     *
     *
     *
     */
    class SupportPeople
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
        protected $_imgfilename;
        /**
         * @var
         */
        protected $_content;
        /**
         * @var
         */
        protected $_authorid;
        /**
         * @var
         */
        protected $_authorname;

        protected $_displayorder;
        protected $_firstname;
        protected $_surname;
        protected $_specialism;
        protected $_email;


        protected $_supporttypeid;
        protected $_supporttypetitle;

        protected $_support_types;


        /**
         * PB_SupportPeople constructor.
         *
         * @param null   $id
         * @param int    $width
         * @param int    $height
         * @param string $path
         *
         * @throws Exception
         */
        public function __construct($id = null, $width = 400, $height = 600, $path = USER_UPLOADS.'/images/staff/')
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
                    throw new Exception('Class PB_SupportPeople requires id to be specified as an integer');
                }

                //Assess passed width & height
                if (!is_numeric($width) || !is_numeric($height))
                {
                    throw new Exception('Class PB_SupportPeople requires width and height to be specified as integers');
                }

                //Assess passed path
                if (isset($path) && !is_string($path))
                {
                    throw new Exception('Class PB_SupportPeople requires path to be specified as a string, eg: /user_uploads/images/staff/');
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
                $large = $path . "large/";
                $small = $path . "small/";
                if (!file_exists(DOCUMENT_ROOT . $large))
                {
                    //Create it
                    $success = mkdir(DOCUMENT_ROOT . $large, 0777, true);
                    if (!$success)
                    {
                        throw new Exception('Directory specified (' . $large . ') does not exist - and cannot be created');
                    }
                }
                if (!file_exists(DOCUMENT_ROOT . $small))
                {
                    //Create it
                    $success = mkdir(DOCUMENT_ROOT . $small, 0777, true);
                    if (!$success)
                    {
                        throw new Exception('Directory specified (' . $small . ') does not exist - and cannot be created');
                    }
                }

                //Retrieve current news information
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
         * Retrieves specified content record ID from SupportPeople table
         * Populates object member elements
         *
         * @param int   $id
         */
        public function getItemById($id)
        {
            try
            {
                $stmt = $this->_dbconn->prepare("SELECT * FROM SupportPeople WHERE ID =  :id LIMIT 1");
                $stmt->execute(['id' => $id]);
                $story = $stmt->fetch();
            } catch (Exception $e)
            {
                error_log("Failed to retrieve SupportPeople item details when searching by ID" . $e);
            }

            //Store details in relevant members
            $this->_id = $story['ID'];
            $this->_firstname = $story['Firstname'];
            $this->_surname = $story['Surname'];
            $this->_specialism = $story['Specialism'];
            $this->_content = $story['Content'];
            $this->_email = $story['Email'];
            $this->_imgfilename = $story['ImgFilename'];
            $this->_image_path = $story['ImgPath'];
            $this->_displayorder = $story['DisplayOrder'];
            $this->_authorid = $story['AuthorID'];
            $this->_authorname = $story['AuthorName'];

            //Now also retrieve the info for the main Content Type
            $SupportTypes = $this->getSupportTypeInfo();

            //Update the support_types for this item
            $support_types = $this->populateSupportTypes();

            $story['SupportTypes'] = $SupportTypes;
            $story['support_types'] = $support_types;

            return $story;
        }

        /**
         * Retrieves all SupportTypes table data from the database
         *
         * @return mixed
         */
        public function getAllSupportTypes()
        {
            try {
                $stmt = $this->_dbconn->query("SELECT * FROM SupportTypes ORDER BY DisplayOrder ASC, Title ASC");
                $contenttypes = $stmt->fetchAll();
            } catch (Exception $e)
            {
                error_log("Failed to retrieve Support Types" . $e);
            }

            return $contenttypes;
        }

        /**
         * Retrieves all supportpeople records for a specified supporttypeid
         * Doesn't use the getContent functions - as we don't want to populate this object - just return an array of data.
         * If the passed typeid field is null - it will return all content NOT assigned to a section.
         *
         *
         * All table fields are returned.
         *
         * @param int   $typeid     Supply the recordID for the Type of content you wish to return
         *
         * @return array
         */
        public function getAllSupportPeopleByType($typeid = null)
        {
            if (is_numeric($typeid) && $typeid > 0)
            {
                $sql = "SELECT SupportPeople.* FROM SupportPeopleByType LEFT JOIN SupportPeople ON SupportPeople.ID = SupportPeopleByType.SupportPeopleID WHERE SupportPeopleByType.SupportTypeID = :typeid AND SupportPeople.Surname != '' ORDER BY SupportPeople.DisplayOrder ASC, SupportPeople.Surname ASC";
            }
            else
            {
                $sql = "SELECT SupportPeople.* FROM SupportPeopleByType RIGHT JOIN SupportPeople ON SupportPeople.ID = SupportPeopleByType.SupportPeopleID WHERE SupportPeopleByType.SupportTypeID IS :typeid AND SupportPeople.Surname != '' ORDER BY SupportPeople.DisplayOrder ASC, SupportPeople.Surname ASC";
                $typeid = null;
            }

            try {
                $stmt = $this->_dbconn->prepare($sql);
                $stmt->execute(['typeid' => $typeid]);
                $ret_arr = array();
                while ($story = $stmt->fetch())
                {
                    $ret_arr[] = $story;
                }
                $stories = $ret_arr;
                //$stories = $stmt->fetchAll();
            } catch (Exception $e)
            {
                error_log("Failed to retrieve SupportPeople records" . $e);
            }

            return $stories;
        }


        /**
         * Checks the SupportPeopleByType table - with the two provided IDs to see if a record exists.
         * Return true if it does
         *
         * @param $supportpeopleid
         * @param $supporttypeid
         *
         * @return bool
         * @throws Exception
         */
        public function checkSectionMatch($supportpeopleid, $supporttypeid)
        {
            if (is_numeric($supportpeopleid) && is_numeric($supporttypeid) && $supportpeopleid > 0 && $supporttypeid > 0)
            {
                try {
                    $stmt = $this->_dbconn->prepare("SELECT ID FROM SupportPeopleByType WHERE SupportPeopleID = :supportpeopleid AND SupportTypeID = :supporttypeid LIMIT 1");
                    $stmt->execute([
                        'supportpeopleid' => $supportpeopleid,
                        'supporttypeid' => $supporttypeid
                                   ]);
                    $match = $stmt->fetch();
                    if ($match['ID'] >= 1) { return true; } else { return false; }
                } catch (Exception $e)
                {
                    error_log("Failed to retrieve SupprotPeople records" . $e);
                }
            }
            else
            {
                //throw new Exception("Class PB_SupportPeople, Function checkSectionMatch requires contentid and typeid to be integers.");
                return false;
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
        public function uploadImage($ImageStream)
        {
            //Check we have some useful data passed
            if ($ImageStream == '')
            {
                throw new Exception("You must supply a file stream (data:image/png;base64) to this function.");
            }


            //Create ImageHandler
            $ImgObj = new ImageHandler($this->_image_path, true);
            //Set up some defaults
            $ImgObj->setSourceFileType('png');
            $ImgObj->setDestFileType('jpg');
            $ImgObj->setImageWidth($this->_image_width);
            $ImgObj->setImageHeight($this->_image_height);
            $ImgObj->setThumbWidth(floor($this->_image_width / 2));
            $ImgObj->setThumbHeight(floor($this->_image_height / 2));
            $ImgObj->createFilename($this->_firstname." ".$this->_surname);


            $result = $ImgObj->processImage($ImageStream);
            $newFilename = $ImgObj->getImgFilename();

            if ($result == true)
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
         * Create new empty content item
         *
         * Sets the _id property accordingly
         */
        public function createItem()
        {
            //Only execute if the _id property isn't already set
            if (is_numeric($this->_id) || $this->_id > 0)
            {
                throw new Exception('You cannot create a new SupportPeople item at this stage - the id property is already set as '.$this->_id);
            }

            //Create DB item
            try
            {
                $result = $this->_dbconn->query("INSERT INTO SupportPeople SET Surname = ''");
                $lastID = $this->_dbconn->lastInsertId();

                if ($lastID > 0)
                {
                    $this->_id = $lastID;
                }
            }
            catch (Exception $e) {
                error_log("Failed to create new SupportPeople record: " . $e);
            }
        }

        /**
         * Populates the _support_types property of the object with an array of the supporttypeids
         */
        public function populateSupportTypes()
        {

            try {
                $stmt = $this->_dbconn->prepare("SELECT SupportTypeID FROM SupportPeopleByType WHERE SupportPeopleID = :supportpeopleid");
                $stmt->execute([
                                   'supportpeopleid' => $this->_id
                               ]);
                $sections = $stmt->fetchAll();
            } catch (Exception $e) {
                error_log("Failed to save SupportByPeople record: " . $e);
            }

            $this->_support_types = $sections;

            return $sections;
        }

        /**
         * Takes the content of the _support_types property and updates the ContentByType table
         *
         */
        public function updateSupportTypes()
        {
            if (is_array($this->_support_types))
            {
                //First we need to delete all entries for this content
                try {
                    $stmt = $this->_dbconn->prepare("DELETE FROM SupportPeopleByType WHERE SupportPeopleID = :supportpeopleid");
                    $stmt->execute([
                        'supportpeopleid' => $this->_id
                                   ]);
                } catch (Exception $e) {
                    error_log("Failed to delete SupportPeopleByType records.");
                }

                //Then we need to post the new records
                //reset($this_>_support_types);
                $stmt = $this->_dbconn->prepare("INSERT INTO SupportPeopleByType SET SupportPeopleID = :supportpeopleid, SupportTypeID= :supporttypeid");
                for ($i=0; $i < count($this->_support_types); $i++)
                {
                    $stmt->execute([
                        'supportpeopleid' => $this->_id,
                        'supporttypeid' => $this->_support_types[$i]
                                   ]);
                }
            }
        }


        /**
         * Saves the current object to the SupportPeople table in the database
         *
         * @throws Exception
         */
        public function saveItem()
        {
            //First need to determine if this is a new item
            if ($this->_id <= 0)
            {
                $this->createItem(); //_id should now be set
            }

            try {
                $stmt = $this->_dbconn->prepare("UPDATE SupportPeople SET Firstname = :firstname, Surname = :surname, Specialism = :specialism, Content = :content, Email = :email, ImgFilename = :imgfilename, ImgPath = :imgpath, DisplayOrder = :displayorder, AuthorID = :authorid, AuthorName = :authorname WHERE ID = :id LIMIT 1");
                $result = $stmt->execute([
                    'firstname' => $this->_firstname,
                    'surname' => $this->_surname,
                    'specialism' => $this->_specialism,
                    'content' => $this->_content,
                    'email' => $this->_email,
                    'imgfilename' => $this->_imgfilename,
                    'imgpath' => $this->_image_path,
                    'displayorder' => $this->_displayorder,
                    'authorid' => $this->_authorid,
                    'authorname' => $this->_authorname,
                    'id' => $this->_id
                               ]);
                if ($result == true) {
                    //Update the support_types
                    $this->updateSupportTypes();
                    return true;
                } else {
                    error_log($stmt->errorInfo()[2]);
                    return false;
                }
            } catch (Exception $e) {
                error_log("Failed to save SupportPeople record: " . $e);
            }
        }

        /**
         * Delete the image for this content item - assuming _img_filename is set
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
            try {
                $this->saveItem();
            } catch (Exception $e) {
                error_log('Failed to delete image for Spescialist Support People table');
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
                throw new Exception('Class PB_SupportPeople requires the content item ID to be set if you are trying to delete the item');
            }

            //First delete the image files
            $this->deleteImage();

            //Delete from ContentByType
            try {
                $stmt = $this->_dbconn->prepare("DELETE FROM SupportPeopleByType WHERE SupportPeopleID = :id");
                $result = $stmt->execute([
                                             'id' => $this->_id
                                         ]);
            } catch (Exception $e) {
                error_log("Failed to delete SupportPeopleByType records: " . $e);
            }


//            // Step through all images for this content item AND DELETE
//            $ImgDel = new PB_ImageLibrary('Content', $this->_id, null, null, null, USER_UPLOADS.'/images/gallery/');
//            $ImgDel->deleteAllImagesForContent('Content',$this->_id);


            //Now delete the item from the DB
            try {
                $stmt = $this->_dbconn->prepare("DELETE FROM SupportPeople WHERE ID = :id LIMIT 1");
                $result = $stmt->execute([
                                   'id' => $this->_id
                               ]);
            } catch (Exception $e) {
                error_log("Failed to delete SupportPeople record: " . $e);
            }

            if ($result == true)
            {
                //Unset the properties
                $this->_id = null;
                $this->_firstname = null;
                $this->_surname = null;
                $this->_specialism = null;
                $this->_email = null;
                $this->_content = null;
                $this->_imgfilename = null;
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


        public function getSupportTypeInfo()
        {

            try
            {
                $stmt = $this->_dbconn->prepare("SELECT SupportTypes.* FROM SupportPeopleByType LEFT JOIN SupportTypes ON SupportTypes.ID = SupportPeopleByType.SupportTypeID WHERE SupportPeopleByType.SupportPeopleID = :id ORDER BY SupportTypes.DisplayOrder ASC LIMIT 1");
                $stmt->execute(['id' => $this->_id]);
                $story = $stmt->fetch();
            } catch (Exception $e)
            {
                error_log("Failed to retrieve SupprotType details" . $e);
            }

            //Store details in relevant members
            $this->_supporttypeid = $story['ID'];
            $this->_supporttypetitle = $story['Title'];

            return $story;
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
            $sql = "SELECT ID FROM SupportPeople WHERE Firstname LIKE :needle OR Surname LIKE :needle OR Specialism LIKE :needle OR Content LIKE :needle";
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
                $link = "//".SITEFQDN."/content/specialist.php?id=".$content['ID'];

                //Prepare content
                $Content = FixOutput(substr(strip_tags($content['Content']), 0, 160));

                //Check weighting
                if (strtolower($content['Firstname']) == $search_field || strtolower($content['Surname']) == $search_field || strtolower($content['Specialism']) == $search_field)
                {
                    $Weighting = 0;
                }
                else
                {
                    $Weighting = 20;
                }
                $content['Weighting'] = $Weighting;

                //Add to search results
                $search_results[] = array('Title' => $content['Firstname']." ".$content['Surname']." (".$content['Specialism'].")", 'Content' => $Content, 'Link' => $link, 'DateDisplay' => "", 'Weighting' => $Weighting);
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
        public function getImgfilename()
        {
            return $this->_imgfilename;
        }

        /**
         * @param mixed $imgfilename
         */
        public function setImgfilename($imgfilename)
        {
            $this->_imgfilename = $imgfilename;
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
            return $this->_authorid;
        }

        /**
         * @param mixed $authorid
         */
        public function setAuthorid($authorid)
        {
            $this->_authorid = $authorid;
        }

        /**
         * @return mixed
         */
        public function getAuthorname()
        {
            return $this->_authorname;
        }

        /**
         * @param mixed $authorname
         */
        public function setAuthorname($authorname)
        {
            $this->_authorname = $authorname;
        }

        /**
         * @return mixed
         */
        public function getDisplayorder()
        {
            return $this->_displayorder;
        }

        /**
         * @param mixed $displayorder
         */
        public function setDisplayorder($displayorder)
        {
            $this->_displayorder = $displayorder;
        }

        /**
         * @return mixed
         */
        public function getFirstname()
        {
            return $this->_firstname;
        }

        /**
         * @param mixed $firstname
         */
        public function setFirstname($firstname)
        {
            $this->_firstname = $firstname;
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
        public function getSpecialism()
        {
            return $this->_specialism;
        }

        /**
         * @param mixed $specialism
         */
        public function setSpecialism($specialism)
        {
            $this->_specialism = $specialism;
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
        public function getSupporttypeid()
        {
            return $this->_supporttypeid;
        }

        /**
         * @param mixed $supporttypeid
         */
        public function setSupporttypeid($supporttypeid)
        {
            $this->_supporttypeid = $supporttypeid;
        }

        /**
         * @return mixed
         */
        public function getSupporttypetitle()
        {
            return $this->_supporttypetitle;
        }

        /**
         * @param mixed $supporttypetitle
         */
        public function setSupporttypetitle($supporttypetitle)
        {
            $this->_supporttypetitle = $supporttypetitle;
        }



        public function setSupportTypes($types)
        {
            $this->_support_types = $types;
            $this->updateSupportTypes();
        }

    }