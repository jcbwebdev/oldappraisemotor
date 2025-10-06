<?php

    namespace PeterBourneComms\CMS;
    use PDO;
    use PDOException;
    use Exception;


    /**
     * Deals with Content items
     *
     * It will allow you to
     *  - specify if its a lowerlevel item or not (default = not)
     *  - specify the size of the top-of-page image
     *  - specify the location of stored images
     *  - pass an image in and have it resized to that size
     *  - stored in the filesystem and database
     *  - retrieve an individual content item
     *  - delete content item (including images - top level and library image)
     *  - retrieve the top level content ID (From ContentTypes- derived via ContentByType table)
     *
     *  - understands whether this is LowerLevel content - same table used for storage
     *
     *
     *
     *
     * @author Peter Bourne
     * @version 1.4
     *
     * 1.0      ---         Original version
     * 1.1      15.07.20    Added removing ParentID from sub-pages when deleting the parent - so content gets orphaned - but still visible - rather than disappearing OR deleting.
     * 1.2      06.04.21    Added lookup (getContentWithSpecialContent) based on SpecialContent type - so we could match SpecialContent - ad retrieve full Content record (for use in Project and Committee look ups for menu state etc.
     * 1.3      04.05.2023  Return SpecialContent in AllParentContent lookup
     * 1.4      23.10.2023  Added getParentTitle method to retrieve the immediate parent content page title
     *
     */
    class Content
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
        protected $_col2_content;
        protected $_col3_content;
        /**
         * @var
         */
        protected $_datedisplay;
        /**
         * @var
         */
        protected $_authorid;
        /**
         * @var
         */
        protected $_authorname;

        protected $_urltext;
        protected $_metadesc;
        protected $_metakey;
        protected $_metatitle;

        protected $_subtitle;
        protected $_menutitle;
        protected $_link;
        protected $_displayorder;

        protected $_flaglowerlevel;
        protected $_parentid;

        protected $_contenttypeid;
        protected $_contenttypetitle;

        protected $_special_content;

        protected $_categories;


        /**
         * Content constructor.
         *
         * @param null   $id
         * @param null   $flagLowerLevel    true if this is a Lower Level item
         * @param int    $width
         * @param int    $height
         * @param string $path
         *
         * @throws Exception
         */
        public function __construct($id = null, $flagLowerLevel = false, $width = 1200, $height = 360, $path = USER_UPLOADS.'/images/content-headers/')
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
                    throw new Exception('Class Content requires id to be specified as an integer');
                }

                //Assess passed width & height
                if (!is_numeric($width) || !is_numeric($height))
                {
                    throw new Exception('Class Content requires width and height to be specified as integers');
                }

                //Assess passed path
                if (isset($path) && !is_string($path))
                {
                    throw new Exception('Class Content requires path to be specified as a string, eg: /user_uploads/images/content-headers/');
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
                $this->_flaglowerlevel = $flagLowerLevel;
            }
        }


        /**
         * Retrieves specified content record ID from Content table
         * Populates object member elements
         *
         * @param int   $id
         */
        public function getItemById($id)
        {
	        if (!is_numeric($id) || $id <= 0) {
                error_log('Content->getItemById failed because no ID passed');
                return false;
            }
            
            try
            {
                $stmt = $this->_dbconn->prepare("SELECT * FROM Content WHERE ID =  :id LIMIT 1");
                $stmt->execute(['id' => $id]);
                $story = $stmt->fetch();
            } catch (Exception $e)
            {
                error_log("Failed to retrieve Content item details when searching by ID" . $e);
            }

            //Store details in relevant members
                $this->_id = $story['ID'];
                $this->_parentid = $story['ParentID'];
                $this->_title = $story['Title'];
                $this->_subtitle = $story['SubTitle'];
                $this->_menutitle = $story['MenuTitle'];
                $this->_content = $story['Content'];
                $this->_col2_content = $story['Col2Content'];
                $this->_col3_content = $story['Col3Content'];
                $this->_link = $story['Link'];
                $this->_imgfilename = $story['ImgFilename'];
                $this->_image_path = $story['ImgPath'];
                $this->_datedisplay = $story['DateDisplay'];
                $this->_displayorder = $story['DisplayOrder'];
                $this->_authorid = $story['AuthorID'];
                $this->_authorname = $story['AuthorName'];
                $this->_urltext = $story['URLText'];
                $this->_metadesc = $story['MetaDesc'];
                $this->_metakey = $story['MetaKey'];
                $this->_metatitle = $story['MetaTitle'];
                $this->_special_content = $story['SpecialContent'];

            //Now also retrieve the info for the main Content Type
            $content_types = $this->getContentTypeInfo();
            
            //Update the categories for this item
            $categories = $this->populateCategories();

            $story['ContentTypes'] = $content_types;
            $story['Categories'] = $categories;
            
            $story['ParentTitle'] = $this->getParentTitle();

            return $story;
        }

        /**
         * Retrieves ID of Content item based on passed URLText
         * Then passes ID into the getContentItemById function to populate full item
         *
         * @param $urltext
         */
        public function getItemByUrl($urltext, $parenturl = '')
        {
            if ($parenturl != '')
            {
                //Retrieve the Parent ID
                $ParentRec = $this->getItemByUrl($parenturl);
                $ParentID = $ParentRec['ID'];
                $sql = "SELECT ID FROM Content WHERE URLText LIKE :urltext AND ParentID = :parentid LIMIT 1";
                $params = array('urltext' => $urltext, 'parentid' => $ParentID);
            }
            else
            {
                $sql = "SELECT ID FROM Content WHERE URLText LIKE :urltext LIMIT 1";
                $params = array('urltext' => $urltext);
            }

            try {
                $stmt = $this->_dbconn->prepare($sql);
                $stmt->execute($params);
                $story = $stmt->fetch();
            } catch (Exception $e)
            {
                error_log("Failed to retrieve Content item details when searching by URLText" . $e);
            }
            //Use other function to populate rest of properties (no sense writing it twice!)_
            return $this->getItemById($story['ID']);
        }


        /**
         * Retrieves an array of ID, Title, MenuTitle for all Content records that are Parents - ie: Don't have a parentID set.
         *
         * @return mixed
         */
        public function getAllParentContent()
        {
            try {
                $stmt = $this->_dbconn->query("SELECT ID, Title, MenuTitle, SpecialContent FROM Content WHERE ParentID = 0 OR ParentID IS NULL ORDER BY Title ASC");
                $parents = $stmt->fetchAll();
            } catch (Exception $e)
            {
                error_log("Failed to retrieve Content Parents " . $e);
            }

            return $parents;
        }

        /**
         * Retrieves all ContentTypes table data from the database
         *
         * @return mixed
         */
        public function getAllContentTypes()
        {
            try {
                $stmt = $this->_dbconn->query("SELECT * FROM ContentTypes ORDER BY DisplayOrder ASC, Title ASC");
                $contenttypes = $stmt->fetchAll();
            } catch (Exception $e)
            {
                error_log("Failed to retrieve Content Types" . $e);
            }

            return $contenttypes;
        }

        /**
         * Retrieves all content records for a specified ContentTypeID
         * Doesn't use the getContent functions - as we don't want to populate this object - just return an array of data.
         * If the passed typeid field is null - it will return all content NOT assigned to a section.
         *
         * Ignore any records where ParentID is populated as these aren't orphans
         *
         * All table fields are returned.
         *
         * @param int   $typeid     Supply the recordID for the Type of content you wish to return
         *
         * @return array
         */
        public function getAllContentByType($typeid = null)
        {
            if (is_numeric($typeid) && $typeid > 0)
            {
                $sql = "SELECT Content.* FROM ContentByType LEFT JOIN Content ON Content.ID = ContentByType.ContentID WHERE ContentTypeID = :typeid AND (ParentID = 0 OR ParentID IS NULL) AND Content.Title != '' ORDER BY Content.DisplayOrder ASC, Content.Title ASC";
            }
            else
            {
                $sql = "SELECT Content.* FROM ContentByType RIGHT JOIN Content ON Content.ID = ContentByType.ContentID WHERE ContentByType.ContentTypeID IS :typeid AND (ParentID = 0 OR ParentID IS NULL) AND Content.Title != '' ORDER BY Content.Title ASC";
                $typeid = null;
            }

            try {
                $stmt = $this->_dbconn->prepare($sql);
                $stmt->execute(['typeid' => $typeid]);
                $ret_arr = array();
                while ($story = $stmt->fetch())
                {
                    $l_pages = $this->getLowerLevelPages($story['ID']);
                    $story['LowerLevelPageCount'] = count($l_pages);
                    $story['LowerLevelPages'] = $l_pages;
                    $ret_arr[] = $story;
                }
                $stories = $ret_arr;
                //$stories = $stmt->fetchAll();
            } catch (Exception $e)
            {
                error_log("Failed to retrieve Content records" . $e);
            }

            return $stories;
        }


        /**
         * Checks the ContentByType table - with the two provided IDs to see if a record exists.
         * Return true if it does
         *
         * @param $contentid
         * @param $typeid
         *
         * @return bool
         * @throws Exception
         */
        public function checkSectionMatch($contentid, $typeid)
        {
            if (is_numeric($contentid) && is_numeric($typeid) && $contentid > 0 && $typeid > 0)
            {
                try {
                    $stmt = $this->_dbconn->prepare("SELECT ID FROM ContentByType WHERE ContentID = :contentid AND ContentTypeID = :typeid LIMIT 1");
                    $stmt->execute([
                        'contentid' => $contentid,
                        'typeid' => $typeid
                                   ]);
                    $match = $stmt->fetch();
                    if (is_array($match) && $match['ID'] >= 1) { return true; } else { return false; }
                } catch (Exception $e)
                {
                    error_log("Failed to retrieve Content records" . $e);
                }
            }
            else
            {
                //throw new Exception("Class Content, Function checkSectionMatch requires contentid and typeid to be integers.");
                return false;
            }
        }


        public function getLowerLevelPages($contentid = null)
        {
            if (isset($contentid))
            {
                if (!is_numeric($contentid) || $contentid <= 0)
                {
                    throw new Exception('Cannot return lower level pages as ID provided is not an integer.');
                }
            }
            else
            {
                if (!is_numeric($this->_id) || $this->_id <= 0)
                {
                    throw new Exception('Cannot return lower level pages as no ID set for this object');
                }
                else
                {
                    $contentid = $this->_id;
                }
            }

            try {
                $stmt = $this->_dbconn->prepare("SELECT ID, Title, MenuTitle, URLText FROM Content WHERE ParentID = :contentid ORDER BY DisplayOrder ASC, Title ASC");
                $stmt->execute([
                                   'contentid' => $contentid
                               ]);
                $lowerpages = $stmt->fetchAll();
                return $lowerpages;
            } catch (Exception $e)
            {
                error_log("Failed to retrieve Lower Level Content records" . $e);
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
            $ImgObj = new ImageHandler($this->_image_path, false);
            //Set up some defaults
            $ImgObj->setSourceFileType('png');
            $ImgObj->setDestFileType('jpg');
            $ImgObj->setImageWidth($this->_image_width);
            $ImgObj->setImageHeight($this->_image_height);
            $ImgObj->createFilename($this->_title);


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
         * Create new empty content item
         *
         * Sets the _id property accordingly
         */
        public function createNewItem()
        {
            //Only execute if the _id property isn't already set
            if (is_numeric($this->_id) || $this->_id > 0)
            {
                throw new Exception('You cannot create a new Content item at this stage - the id property is already set as '.$this->_id);
            }

            //Create DB item
            try
            {
                $result = $this->_dbconn->query("INSERT INTO Content SET Title = ''");
                $lastID = $this->_dbconn->lastInsertId();

                if ($lastID > 0)
                {
                    $this->_id = $lastID;
                }
            }
            catch (Exception $e) {
                error_log("Failed to create new Content record: " . $e);
            }
        }

        /**
         * Populates the _categories property of the object with an array of the ContentTypeIDs
         */
        public function populateCategories()
        {
            //Sublevel item or not?
            if ($this->_parentid > 0)
            {
                $id = $this->_parentid;
            }
            else
            {
                $id = $this->_id;
            }

            try {
                $stmt = $this->_dbconn->prepare("SELECT ContentTypeID FROM ContentByType WHERE ContentID = :contentid");
                $stmt->execute([
                                   'contentid' => $id
                               ]);
                $sections = $stmt->fetchAll();
            } catch (Exception $e) {
                error_log("Failed to save Content record: " . $e);
            }

            $this->_categories = $sections;

            return $sections;
        }

        /**
         * Takes the content of the _categories property and updates the ContentByType table
         *
         * Should only run if not a lowerlevel item
         */
        public function updateCategories()
        {
            if (is_array($this->_categories) && $this->_flaglowerlevel === false && $this->_id > 0)
            {
                //First we need to delete all entries for this content
                try {
                    $stmt = $this->_dbconn->prepare("DELETE FROM ContentByType WHERE ContentID = :contentid");
                    $stmt->execute([
                        'contentid' => $this->_id
                                   ]);
                } catch (Exception $e) {
                    error_log("Failed to delete ContentByType records.");
                }

                //Then we need to post the new records
                //reset($this_>_categories);
                $stmt = $this->_dbconn->prepare("INSERT INTO ContentByType SET ContentID = :contentid, ContentTypeID = :contenttypeid");
                for ($i=0; $i < count($this->_categories); $i++)
                {
                    $stmt->execute([
                        'contentid' => $this->_id,
                        'contenttypeid' => $this->_categories[$i]
                                   ]);
                }
            }
        }


        /**
         * Saves the current object to the Content table in the database
         *
         * @throws Exception
         */
        public function saveItem()
        {
            //First need to determine if this is a new Content item
            if ($this->_id <= 0)
            {
                $this->createNewItem(); //_id should now be set
            }

            try {
                $stmt = $this->_dbconn->prepare("UPDATE Content SET ParentID = :parentid, Title = :title, SubTitle = :subtitle, MenuTitle = :menutitle, Content = :content, Col2Content = :col2content, Col3Content = :col3content, Link = :link, DateDisplay = :datedisplay, ImgFilename = :imgfilename, ImgPath = :imgpath, DisplayOrder = :displayorder, URLText = :urltext, MetaDesc = :metadesc, MetaKey = :metakey, MetaTitle = :metatitle, AuthorID = :authorid, AuthorName = :authorname, SpecialContent = :specialcontent WHERE ID = :id LIMIT 1");
                $result = $stmt->execute([
                    'parentid' => $this->_parentid,
                    'title' => $this->_title,
                    'subtitle' => $this->_subtitle,
                    'menutitle' => $this->_menutitle,
                    'content' => $this->_content,
                    'col2content' => $this->_col2_content,
                    'col3content' => $this->_col3_content,
                    'link' => $this->_link,
                    'datedisplay' => $this->_datedisplay,
                    'imgfilename' => $this->_imgfilename,
                    'imgpath' => $this->_image_path,
                    'displayorder' => $this->_displayorder,
                    'urltext' => $this->_urltext,
                    'metadesc' => $this->_metadesc,
                    'metakey' => $this->_metakey,
                    'metatitle' => $this->_metatitle,
                    'authorid' => $this->_authorid,
                    'authorname' => $this->_authorname,
                    'specialcontent' => $this->_special_content,
                    'id' => $this->_id
                               ]);
                if ($result === true && $this->_flaglowerlevel === false) {
                    //Update the categories
                    $this->updateCategories();
                    return true;
                } elseif ($result === true) {
                    return true;
                } else {
                    error_log($stmt->errorInfo()[2]);
                    return false;
                }
            } catch (Exception $e) {
                error_log("Failed to save Content record: " . $e);
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
            $this->saveItem();
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
                throw new Exception('Class Content requires the content item ID to be set if you are trying to delete the item');
            }

            //First delete the image files
            $this->deleteImage();

            //Delete from ContentByType
            try {
                $stmt = $this->_dbconn->prepare("DELETE FROM ContentByType WHERE ContentID = :id");
                $result = $stmt->execute([
                                             'id' => $this->_id
                                         ]);
            } catch (Exception $e) {
                error_log("Failed to delete ContentByType records: " . $e);
            }


            // Step through all images for this content item AND DELETE
            $ImgDel = new ImageLibrary('Content', $this->_id, null, null, null, USER_UPLOADS.'/images/gallery/');
            $ImgDel->deleteAllImagesForContent('Content',$this->_id);

            //Now unset the ParentID for any pieces of Content where the ParentID = this id (so the pages get orphaned rather than deleted)
            try {
                $stmt = $this->_dbconn->prepare("UPDATE Content SET ParentID = null WHERE ParentID = :id");
                $stmt->execute([
                    'id' => $this->_id
                ]);
            } catch (Exception $e) {
                error_log("Failed to delete orphaned sub-pages: " . $e);
            }

            //Now delete the item from the DB
            try {
                $stmt = $this->_dbconn->prepare("DELETE FROM Content WHERE ID = :id LIMIT 1");
                $result = $stmt->execute([
                                   'id' => $this->_id
                               ]);
            } catch (Exception $e) {
                error_log("Failed to delete Content record: " . $e);
            }

            if ($result === true)
            {
                //Unset the properties
                $this->_id = null;
                $this->_parentid = null;
                $this->_title = null;
                $this->_subtitle = null;
                $this->_menutitle = null;
                $this->_content = null;
                $this->_col2_content = null;
                $this->_col3_content = null;
                $this->_link = null;
                $this->_imgfilename = null;
                $this->_datedisplay = null;
                $this->_displayorder = null;
                $this->_authorid = null;
                $this->_authorname = null;
                $this->_urltext = null;
                $this->_metadesc = null;
                $this->_metakey = null;
                $this->_metatitle = null;

                return true;
            }
            else
            {
                return false;
            }

        }


        public function getContentTypeInfo()
        {
            if ($this->_parentid > 0)
            {
                $id = $this->_parentid;
            }
            else
            {
                $id = $this->_id;
            }

            try
            {
                $stmt = $this->_dbconn->prepare("SELECT ContentTypes.ID, ContentTypes.Title FROM ContentByType LEFT JOIN ContentTypes ON ContentTypes.ID = ContentByType.ContentTypeID WHERE ContentByType.ContentID = :id ORDER BY ContentTypes.DisplayOrder ASC LIMIT 1");
                $stmt->execute(['id' => $id]);
                $story = $stmt->fetch();
            } catch (Exception $e)
            {
                error_log("Failed to retrieve ContentType details" . $e);
            }

            //Store details in relevant members
            $this->_contenttypeid = $story['ID'];
            $this->_contenttypetitle = $story['Title'];

            return $story;
        }

        
        public function getParentTitle()
        {
            if ($this->_parentid > 0) {
                $id = $this->_parentid;
            } else {
                return false;
            }
            try {
                $stmt = $this->_dbconn->prepare("SELECT Title FROM Content WHERE ID = :id LIMIT 1");
                $stmt->execute(['id' => $id]);
                $story = $stmt->fetch();
            } catch (Exception $e) {
                error_log("Failed to retrieve ContentType details" . $e);
            }
            
            if ($story['Title'] != '') {
                return $story['Title'];
            } else {
                return false;
            }
            
        }



        /**
         * Function to check if a similar URL already exists in the Content table
         * Returns TRUE if VALID, ie: not present in database
         * Takes into account lower level pages - as they will use their parent page URLText to build the URL
         * eg: There may be two pages with "about" as URLText - but one has ParentID populated so url is actually "parenturl/about"
         *
         *
         * @param     $ContentURL
         * @param int $ID
         * @param int $ParentID
         *
         * @return bool
         * @throws Exception
         */

        public function URLTextValid($ContentURL, $ID = 0, $ParentID = 0)
        {
            if ($ID <= 0)
            {
                $ID = $this->_id;
            }
            if (!is_string($ContentURL))
            {
                throw new Exception('Content needs the new URL specifying as a string');
            }


            // Two possible routes to take:
            //  1. If ParentID is passed - we need to check records where URL exists and row['ParentID'] == $passedParentID.
            //  2. If no ParentID is passed,
            //      a) We need to just check records where URL exists and ID != passedID
            //      b) If No passedID - we just need to check URL doesn't exist where ParentID == NULL


            if (clean_int($ParentID) > 0)
            {
                // Option 1 - return any rows = PROBLEM (return false)
                if (clean_int($ID) > 0)
                {
                    $sql = "SELECT ID, ParentID FROM Content WHERE URLText = :urltext AND ParentID = :parentid AND ID != :id";
                    $vars = array('urltext' => $ContentURL, 'parentid' => $ParentID, 'id' => $ID);
                }
                else
                {
                    $sql = "SELECT ID, ParentID FROM Content WHERE URLText = :urltext AND ParentID = :parentid";
                    $vars = array('urltext' => $ContentURL, 'parentid' => $ParentID);
                }
            }
            else
            {
                // Option 2 - return any rows = PROBLEM (return false)
                if (clean_int($ID) > 0)
                {
                    $sql = "SELECT ID, ParentID FROM Content WHERE URLText = :urltext AND ID != :id";
                    $vars = array('urltext' => $ContentURL, 'id' => $ID);
                }
                else
                {
                    $sql = "SELECT ID, ParentID FROM Content WHERE URLText = :urltext AND (ParentID IS NULL OR ParentID <= 0)";
                    $vars = array('urltext' => $ContentURL);
                }
            }


            // Execute query
            $stmt = $this->_dbconn->prepare($sql);
            $result = $stmt->execute($vars);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            /*echo "result = ".$result."<br/>";
            echo "number of returned rows = ".count($rows)."<br/>";
            exit;*/

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
         *  - SubTitle      (weight: 15 - level 2)
         *  - MenuTitle     (weight: 20 - level 1)
         *  - Content       (weight: 10 - level 3)
         *  - COl2Content   (weight: 10 - level 3)
         *
         * Will return array of arrays:
         * array('ID','Title,'SubTitle','FullURLText','Weight');  The Full URL will be provided - to cover lower level content items - this will need to be derived.
         *
         * The search will only be carried out where the parent item is present in a menu (ie there is an entry in the ContentByType table for the parent/Toplevel ContentID).
         *
         * @param   mixed   $needle
         * @return  mixed   array
         */
        public function searchContent($needle = '')
        {
            if ($needle == '') { return array(); }

            //$results = array('Level1'=>array(),'Level2'=>array(),'Level3'=>array());
            $search_field = strtolower(filter_var($needle, FILTER_SANITIZE_STRING));
            $search_criteria = "%".$needle."%";

            $search_results = array();

            //Search content as outlined above
            //Return IDs
            $sql = "SELECT ID, ParentID FROM Content WHERE Title LIKE :needle OR MenuTitle LIKE :needle OR SubTitle LIKE :needle OR Content LIKE :needle OR Col2Content LIKE :needle OR Col3Content LIKE :needle";
            $stmt = $this->_dbconn->prepare($sql);
            $stmt->execute([
                'needle' => $search_criteria
                                     ]);
            while($row = $stmt->fetch(PDO::FETCH_ASSOC))
            {
                //Check if the item is available in a menu (ie:  Can be show in search results)
                if ($this->isContentInMenu($row['ID']))
                {
                    //Retrieve full data
                    $content = $this->getItemById($row['ID'], false);

                    //Prepare link
                    unset($link);
                    if ($content['Link'] != '')
                    {
                        $link = $content['Link'];
                    }
                    elseif ($content['URLText'] != '')
                    {
                        if($content['ParentID'] > 0)
                        {
                            //Need to build up link from Parent url
                            $PRO = new Content($content['ParentID']);
                            $ParentURL = $PRO->getURLText();
                            $link = "//".SITEFQDN."/".$ParentURL."/".$content['URLText'];
                        }
                        else
                        {
                            $link = "//".SITEFQDN."/".$content['URLText'];
                        }
                    }
                    else
                    {
                        $link = "//".SITEFQDN."/content/index.php?id=".$content['ID'];
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
            }

            //Return results
            return $search_results;

        }


        /**
         * Function to determine if a piece of content is present in a menu.
         *  - For top level content - this means they have a record in the ContentByType table
         *  - For lower level content it means that the top level parent has an entry in the ContentByType table
         *
         * @int     int     @contentid
         *
         * @param int $contentid
         *
         * @return bool
         * @throws Exception
         */
        function isContentInMenu($contentid = 0)
        {
            if (clean_int($contentid <= 0))
            {
                $contentid = $this->_id;
            }
            if ($contentid <= 0)
            {
                throw new Exception('Content: Can not determine whether content is in the menu as no ID passed');
            }

            $sql = "SELECT ID, ParentID FROM Content WHERE ID = :contentid LIMIT 1";
            $stmt = $this->_dbconn->prepare($sql);
            $stmt->execute([
                'contentid' => $contentid
                           ]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row['ParentID'] != '' && $row['ParentID'] > 0)
            {
                //Need to do it again to retrieve the contentid we are searching for
                //ASSUMES NO ORPHANED CONTENT - otherwise infinite loop!
                $sql = "SELECT ID, ParentID FROM Content WHERE ID = :contentid LIMIT 1";
                $stmt = $this->_dbconn->prepare($sql);
                do
                {
                    $stmt->execute([
                                       'contentid' => $row['ParentID']
                    ]);
                    $row2 = $stmt->fetch(PDO::FETCH_ASSOC);
                    $contentid = $row2['ID'];
                } while($row2 = $stmt->fetch(PDO::FETCH_ASSOC));
            }

            //We can now check for an entry
            $sql = "SELECT ID FROM ContentByType WHERE ContentID = :contentid";
            $stmt = $this->_dbconn->prepare($sql);
            $stmt->execute([
                'contentid' => $contentid
                           ]);
            $row2 = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row2['ID'] > 0)
            {
                return true; // its in the menu
            }
            else
            {
                return false;
            }
        }

        /**
         * @param $needle
         * @return array|false|mixed
         */
        public function getContentWithSpecialContent($needle) {
            if (!is_string($needle)) {
                return false;
            }
            $sql = "SELECT ID FROM Content WHERE SpecialContent = :needle ORDER BY DisplayOrder ASC, Title ASC LIMIT 1";
            $stmt = $this->_dbconn->prepare($sql);
            $stmt->execute([
                'needle' => $needle
            ]);
            $item = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($item['ID'] > 0) {
                return $this->getItemById($item['ID']);
            }

            return false;

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

        public function getContentTypeID()
        {
            return $this->_contenttypeid;
        }
        public function getContentTypeTitle()
        {
            $this->_contenttypetitle;
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

        public function getSubTitle()
        {
            return $this->_subtitle;
        }

        public function setSubTitle($subtitle)
        {
            $this->_subtitle = $subtitle;
        }

        public function getMenuTitle()
        {
            return $this->_menutitle;
        }

        public function setMenuTitle($menutitle)
        {
            $this->_menutitle = $menutitle;
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
        public function getCol2Content()
        {
            return $this->_col2_content;
        }

        /**
         * @param $col2content
         */
        public function setCol2Content($col2content)
        {
            $this->_col2_content = $col2content;
        }

        /**
         * @return mixed
         */
        public function getDateDisplay()
        {
            return $this->_datedisplay;
        }

        /**
         * @param $datedisplay
         */
        public function setDateDisplay($datedisplay)
        {
            $this->_datedisplay = $datedisplay;
        }

        public function getDisplayOrder()
        {
            return $this->_displayorder;
        }

        public function setDisplayOrder($displayorder)
        {
            $this->_displayorder = $displayorder;
        }

        public function getLink()
        {
            return $this->_link;
        }

        public function setLink($link)
        {
            $this->_link = $link;
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

        public function getURLText()
        {
            return $this->_urltext;
        }

        public function setURLText($urltext)
        {
            $this->_urltext = $urltext;
        }

        public function getMetaDesc()
        {
            return $this->_metadesc;
        }

        public function setMetaDesc($metadesc)
        {
            $this->_metadesc = $metadesc;
        }

        public function getMetaKey()
        {
            return $this->_metakey;
        }

        public function setMetaKey($metakey)
        {
            $this->_metakey = $metakey;
        }

        public function getMetaTitle()
        {
            return $this->_metatitle;
        }

        public function setMetaTitle($metatitle)
        {
            $this->_metatitle = $metatitle;
        }

        public function getCategories()
        {
            return $this->_categories;
        }

        public function setCategories($categories)
        {
            $this->_categories = $categories;
            $this->updateCategories();
        }

        public function getParentID()
        {
            return $this->_parentid;
        }

        public function setParentID($parentid)
        {
            $this->_parentid = $parentid;
        }

        public function getImagePath()
        {
            return $this->_image_path;
        }

        public function setImagePath($image_path)
        {
            $this->_image_path = $image_path;
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
        public function setCol3Content($col3_content)
        {
            $this->_col3_content = $col3_content;
        }

        /**
         * @return mixed
         */
        public function getSpecialContent()
        {
            return $this->_special_content;
        }

        /**
         * @param mixed $special_content
         */
        public function setSpecialContent($special_content): void
        {
            $this->_special_content = $special_content;
        }



    }