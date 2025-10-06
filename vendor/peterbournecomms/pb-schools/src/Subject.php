<?php

    namespace PeterBourneComms\Schools;
    use PeterBourneComms\CMS\Database;
    use PeterBourneComms\CMS\ImageHandler;
    use PDO;
    use PDOException;
    use Exception;


    /**
     * Deals with Subject items
     *
     * It will allow you to
     *  - specify if its a lowerlevel item or not (default = not)
     *  - specify the size of the top-of-page image
     *  - specify the location of stored images
     *  - pass an image in and have it resized to that size
     *  - stored in the filesystem and database
     *  - retrieve an individual Subject item
     *  - delete Subject item (including images - top level and library image)
     *  - retrieve the top level Subject ID (From SubjectTypes)
     *
     *  - understands whether this is LowerLevel content - same table used for storage
     *
     * Relies on the Subject table in this structure:
     *  ID
     *  ParentID
     *  Title
     *  SubTitle
     *  MenuTitle
     *  Content
     *  Link
     *  ImgFilename
     *  DateDisplay
     *  AuthorID
     *  AuthorName
     *  DisplayOrder
     *  URLText
     *  MetaDesc
     *  MetaKey
     *  MetaTitle
     *
     *
     *
     * @author Peter Bourne
     * @version 1.0
     *
     */
    class Subject
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

        protected $_subjecttypeid;
        protected $_subjecttypetitle;


        /**
         * Subject constructor.
         *
         * @param null   $id
         * @param null   $flagLowerLevel    true if this is a Lower Level item
         * @param int    $width
         * @param int    $height
         * @param string $path
         *
         * @throws Exception
         */
        public function __construct($id = null, $flagLowerLevel = false, $width = 1200, $height = 360, $path = USER_UPLOADS.'/images/subjects/')
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
                    throw new Exception('Class Subject requires id to be specified as an integer');
                }

                //Assess passed width & height
                if (!is_numeric($width) || !is_numeric($height))
                {
                    throw new Exception('Class Subject requires width and height to be specified as integers');
                }

                //Assess passed path
                if (isset($path) && !is_string($path))
                {
                    throw new Exception('Class Subject requires path to be specified as a string, eg: /user_uploads/images/subjects/');
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
         * Retrieves specified Subject record ID from Subjects table
         * Populates object member elements
         *
         * @param int   $id
         */
        public function getItemById($id)
        {
            try
            {
                $stmt = $this->_dbconn->prepare("SELECT * FROM Subjects WHERE ID =  :id LIMIT 1");
                $stmt->execute(['id' => $id]);
                $story = $stmt->fetch();
            } catch (Exception $e)
            {
                error_log("Failed to retrieve Subject item details when searching by ID" . $e);
            }

            //Store details in relevant members
            $this->_id = $story['ID'];
            $this->_parentid = $story['ParentID'];
            $this->_title = $story['Title'];
            $this->_subtitle = $story['SubTitle'];
            $this->_menutitle = $story['MenuTitle'];
            $this->_content = $story['Content'];
            $this->_col2_content = $story['Col2Content'];
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


            if ($story['ID'] > 0)
            {
                $l_pages = $this->getLowerLevelPages($story['ID']);
                $story['LowerLevelPageCount'] = count($l_pages);
                $story['LowerLevelPages'] = $l_pages;
            }

            return $story;
        }

        /**
         * Retrieves ID of Subject item based on passed URLText
         * Then passes ID into the getItemById function to populate full item
         *
         * @param $urltext
         */
        public function getItemByUrl($urltext)
        {
            try {
                $stmt = $this->_dbconn->prepare("SELECT ID FROM Subjects WHERE URLText LIKE :urltext LIMIT 1");
                $stmt->execute(['urltext' => $urltext]);
                $story = $stmt->fetch();
            } catch (Exception $e)
            {
                error_log("Failed to retrieve Subject item details when searching by URLText" . $e);
            }
            //Use other function to populate rest of properties (no sense writing it twice!)_
            $this->getItemById($story['ID']);
        }


        /**
         * Retrieves an array of ID, Title, MenuTitle for all Subject records that are Parents - ie: Don't have a parentID set.
         *
         * @return mixed
         */
        public function getAllParentSubjects()
        {
            try {
                $stmt = $this->_dbconn->query("SELECT ID FROM Subjects WHERE ParentID = 0 OR ParentID IS NULL ORDER BY Title ASC");
                $parents = array();
                while($rec = $stmt->fetch())
                {
                    $parents[] = $this->getItemById($rec['ID']);
                }
            } catch (Exception $e)
            {
                error_log("Failed to retrieve Subject Parents " . $e);
            }

            return $parents;
        }


        /**
         * Retrieves all Subject records for a specified SubjectTypeID
         * Doesn't use the getSubject functions - as we don't want to populate this object - just return an array of data.
         * If the passed typeid field is null - it will return all content NOT assigned to a section.
         *
         * Ignore any records where ParentID is populated as these aren't orphans
         *
         * All table fields are returned.
         *
         * @param int   $typeid     Supply the recordID for the Type of Subject you wish to return
         *
         * @return array
         */
        public function getAllSubjectsByType($typeid)
        {
            if (is_numeric($typeid) && $typeid > 0)
            {
                $sql = "SELECT Subjects.* FROM Subjects WHERE SubjectTypeID = :typeid AND (ParentID = 0 OR ParentID IS NULL) AND Subjects.Title != '' ORDER BY Subjects.DisplayOrder ASC, Subjects.Title ASC";
            }
            else
            {
                throw new Exception('Subject getAllSubjectsByType requires an integer passed for the type');
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
                error_log("Failed to retrieve Subject records" . $e);
            }

            return $stories;
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
                $stmt = $this->_dbconn->prepare("SELECT ID, Title, MenuTitle, URLText FROM Subjects WHERE ParentID = :contentid ORDER BY DisplayOrder ASC, Title ASC");
                $stmt->execute([
                                   'contentid' => $contentid
                               ]);
                $lowerpages = $stmt->fetchAll();
                return $lowerpages;
            } catch (Exception $e)
            {
                error_log("Failed to retrieve Lower Level Subject records" . $e);
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
         * Create new empty Subject item
         *
         * Sets the _id property accordingly
         */
        public function createItem()
        {
            //Only execute if the _id property isn't already set
            if (is_numeric($this->_id) || $this->_id > 0)
            {
                throw new Exception('You cannot create a new Subject item at this stage - the id property is already set as '.$this->_id);
            }

            //Create DB item
            try
            {
                $result = $this->_dbconn->query("INSERT INTO Subjects SET Title = ''");
                $lastID = $this->_dbconn->lastInsertId();

                if ($lastID > 0)
                {
                    $this->_id = $lastID;
                }
            }
            catch (Exception $e) {
                error_log("Failed to create new Subject record: " . $e);
            }
        }


        /**
         * Saves the current object to the Subject table in the database
         *
         * @throws Exception
         */
        public function saveItem()
        {
            //First need to determine if this is a new Subject item
            if ($this->_id <= 0)
            {
                $this->createItem(); //_id should now be set
            }

            try {
                $stmt = $this->_dbconn->prepare("UPDATE Subjects SET ParentID = :parentid, Title = :title, SubTitle = :subtitle, MenuTitle = :menutitle, Content = :content, Col2Content = :col2content, Link = :link, DateDisplay = :datedisplay, ImgFilename = :imgfilename, ImgPath = :imgpath, DisplayOrder = :displayorder, URLText = :urltext, MetaDesc = :metadesc, MetaKey = :metakey, MetaTitle = :metatitle, AuthorID = :authorid, AuthorName = :authorname WHERE ID = :id LIMIT 1");
                $result = $stmt->execute([
                    'parentid' => $this->_parentid,
                    'title' => $this->_title,
                    'subtitle' => $this->_subtitle,
                    'menutitle' => $this->_menutitle,
                    'content' => $this->_content,
                    'col2content' => $this->_col2_content,
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
                    'id' => $this->_id
                               ]);
                if ($result == true && $this->_flaglowerlevel === false) {
                    return true;
                } else {
                    return $stmt->errorInfo();
                }
            } catch (Exception $e) {
                error_log("Failed to save Subject record: " . $e);
            }
        }

        /**
         * Delete the image for this Subject item - assuming _img_filename is set
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
         * Delete the complete Subject item - including any images
         *
         * @return mixed
         * @throws Exception
         */
        public function deleteItem()
        {
            if (!is_numeric($this->_id))
            {
                throw new Exception('Class Subject requires the Subject item ID to be set if you are trying to delete the item');
            }

            //First delete the image files
            $this->deleteImage();


            // Step through all images for this content item AND DELETE
            $ImgDel = new ImageLibrary('Subject', $this->_id, null, null, null, USER_UPLOADS.'/images/gallery/');
            $ImgDel->deleteAllImagesForContent('Subject',$this->_id);


            //Now delete the item from the DB
            try {
                $stmt = $this->_dbconn->prepare("DELETE FROM Subjects WHERE ID = :id LIMIT 1");
                $result = $stmt->execute([
                                   'id' => $this->_id
                               ]);
            } catch (Exception $e) {
                error_log("Failed to delete Subject record: " . $e);
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


        public function getSubjectParent($itemid = 0)
        {
            if ($this->_parentid > 0)
            {
                $id = $this->_parentid;
            }
            elseif ($this->_id > 0)
            {
                $id = $this->_id;
            }
            else
            {
                $id = $itemid;
            }

            try
            {
                $stmt = $this->_dbconn->prepare("SELECT ID, Title FROM Subjects WHERE ID = :id LIMIT 1");
                $stmt->execute(['id' => $id]);
                $story = $stmt->fetch();
            } catch (Exception $e)
            {
                error_log("Failed to retrieve Parent details" . $e);
            }

            return $story;
        }




        /**
         * Function to check if a similar URL already exists in the Subjects table
         * Returns TRUE if VALID, ie: not present in database
         * Takes into account lower level pages - as they will use their parent page URLText to build the URL
         * eg: There may be two pages with "about" as URLText - but one has ParentID populated so url is actually "parenturl/about"
         *
         *
         * @param int $ID
         * @param int $ParentID
         * @param     $ContentURL
         *
         * @return bool
         * @throws Exception
         */

        public function URLTextValid($ID = 0, $ParentID = 0, $ContentURL)
        {
            if ($ID <= 0)
            {
                $ID = $this->_id;
            }
            if (!is_string($ContentURL))
            {
                throw new Exception('Subject needs the new URL specifying as a string');
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
                    $sql = "SELECT ID, ParentID FROM Subjects WHERE URLText = :urltext AND ParentID = :parentid AND ID != :id";
                    $vars = array('urltext' => $ContentURL, 'parentid' => $ParentID, 'id' => $ID);
                }
                else
                {
                    $sql = "SELECT ID, ParentID FROM Subjects WHERE URLText = :urltext AND ParentID = :parentid";
                    $vars = array('urltext' => $ContentURL, 'parentid' => $ParentID);
                }
            }
            else
            {
                // Option 2 - return any rows = PROBLEM (return false)
                if (clean_int($ID) > 0)
                {
                    $sql = "SELECT ID, ParentID FROM Subjects WHERE URLText = :urltext AND ID != :id";
                    $vars = array('urltext' => $ContentURL, 'id' => $ID);
                }
                else
                {
                    $sql = "SELECT ID, ParentID FROM Subjects WHERE URLText = :urltext AND (ParentID IS NULL OR ParentID <= 0)";
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
            $sql = "SELECT ID, ParentID FROM Subjects WHERE Title LIKE :needle OR MenuTitle LIKE :needle OR SubTitle LIKE :needle OR Content LIKE :needle OR Col2Content LIKE :needle";
            $stmt = $this->_dbconn->prepare($sql);
            $stmt->execute([
                'needle' => $search_criteria
                                     ]);
            while($row = $stmt->fetch(PDO::FETCH_ASSOC))
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
                        $PRO = new Subject($content['ParentID']);
                        $ParentURL = $PRO->getURLText();
                        $link = "//".SITEFQDN."/subject/".$ParentURL."/".$content['URLText'];
                    }
                    else
                    {
                        $link = "//".SITEFQDN."/subject/".$content['URLText'];
                    }
                }
                else
                {
                    $link = "//".SITEFQDN."/content/subjectview.php?id=".$content['ID'];
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

        public function getSubjectTypeID()
        {
            return $this->_subjecttypeid;
        }
        public function getSubjectTypeTitle()
        {
            $this->_subjecttypetitle;
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
    }