<?php

    namespace PeterBourneComms\CMS;

    use PDO;
    use PDOException;
    use Exception;


    /**
     * Deals with News items
     *
     * It will allow you to
     *  - specify the size of the news image
     *  - specify the location of stored images
     *  - pass an image in and have it resized to that size
     *  - stored in the filesystem and database - along with caption, title and link
     *  - retrieve an individual news item
     *  - retrieve an array of all news items
     *  - delete news item (including images)
     *
     * Relies on the Test table in this structure:
     *  ID
     *  Title
     *  Content
     *  PanelShow
     *  PanelExpire
     *  DateDisplay
     *  AuthorID
     *  AuthorName
     *  URLText
     *  MetaDesc
     *  MetaKey
     *  ImgFilename
     *  MetaTitle
     *
     *
     * @author Peter Bourne
     * @version 1.3
     *
     *          1.0     --------    Original version
     *          1.1     --------    Better handling of failed update (error logs) and dates
     *          1.2     17.05.2022  Changed listAll 'past' functionality to use the cutoff in months param
     *          1.3     13.11.2023  Swapped order of parameters for URLTextValid function
     *
     *
     */
    class News
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
        protected $_panelshow;
        /**
         * @var
         */
        protected $_panelexpire;
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


        /**
         * @var
         */
        protected $_allitems;


        /**
         * News constructor.
         *
         * @param null   $id
         * @param int    $width
         * @param int    $height
         * @param string $path
         *
         * @throws Exception
         */
        public function __construct($id = null, $width = 1200, $height = 360, $path = USER_UPLOADS.'/images/news/')
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
                    throw new Exception('Class News requires id to be specified as an integer');
                }

                //Assess passed width & height
                if (!is_numeric($width) || !is_numeric($height))
                {
                    throw new Exception('Class News requires width and height to be specified as integers');
                }

                //Assess passed path
                if (isset($path) && !is_string($path))
                {
                    throw new Exception('Class News requires path to be specified as a string, eg: /user_uploads/images/news/');
                }

                //See if provided path exists - if not - create it
                if (!file_exists(DOCUMENT_ROOT . $path))
                {
                    //Create it
                    $success = mkdir(DOCUMENT_ROOT . $path, 0777, true);
                    if (!$success)
                    {
                        throw new Exception('Directory specified (' . $path . ') does not exist - and cannot be created');
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

                //Default path
                $this->_image_path = $path;

                //Retrieve current news information
                if (isset($id))
                {
                    $this->_id = $id;
                    $this->getItemById($id);
                }

                //Store the width/height/path etc
                $this->_image_width = $width;
                $this->_image_height = $height;

            }
        }


        /**
         * Retrieves specified news record ID from Test table
         * Populates object member elements
         *
         * @param $id
         */
        public function getItemById($id)
        {
            try
            {
                $stmt = $this->_dbconn->prepare("SELECT * FROM News WHERE ID =  :id LIMIT 1");
                $stmt->execute(['id' => $id]);
                $story = $stmt->fetch();
            } catch (Exception $e)
            {
                error_log("Failed to retrieve News item details when searching by ID" . $e);
            }

            //Store details in relevant users
            $this->_id = $story['ID'];
            $this->_title = $story['Title'];
            $this->_imgfilename = $story['ImgFilename'];
            $this->_image_path = $story['ImgPath'];
            $this->_content = $story['Content'];
            $this->_panelshow = $story['PanelShow'];
            $this->_panelexpire = $story['PanelExpire'];
            $this->_datedisplay = $story['DateDisplay'];
            $this->_authorid = $story['AuthorID'];
            $this->_authorname = $story['AuthorName'];
            $this->_urltext = $story['URLText'];
            $this->_metadesc = $story['MetaDesc'];
            $this->_metakey = $story['MetaKey'];
            $this->_metatitle = $story['MetaTitle'];

            return $story;
        }

        public function getItemByUrl($urltext)
        {
            try
            {
                $stmt = $this->_dbconn->prepare("SELECT ID FROM News WHERE URLText LIKE :urltext LIMIT 1");
                $stmt->execute(['urltext' => $urltext]);
                $story = $stmt->fetch();
            } catch (Exception $e)
            {
                error_log("Failed to retrieve News item details when searching by URLText" . $e);
            }
            //Use other function to populate rest of properties (no sense writing it twice!)_
            return $this->getItemById($story['ID']);
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
            if ($ImageStream == '')
            {
                throw new Exception("You must supply a file stream to this function.");
            }

            //Create ImageHandler
            $ImgObj = new ImageHandler($this->_image_path, true);
            //Set up some defaults
            $ImgObj->setSourceFileType('png');
            $ImgObj->setDestFileType('jpg');
            $ImgObj->setImageWidth($this->_image_width);
            $ImgObj->setImageHeight($this->_image_height);
            $ImgObj->setThumbWidth(floor($this->_image_width / 3));
            $ImgObj->setThumbHeight(floor($this->_image_height / 3));
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
         * Delete the image for this news item - assuming _img_filename is set
         *
         * @return mixed
         */
        public function deleteImage()
        {
            if (!is_string($this->_imgfilename) || $this->_imgfilename == '')
            {
                error_log("Sorry - there was no image to delete");

                return;
            }

            $OldImg = new ImageHandler($this->_image_path, true);
            $OldImg->setImgFilename($this->_imgfilename);
            $OldImg->deleteImage();

            $this->_imgfilename = '';
            $this->saveItem();
        }

        /**
         * Saves the current object to the News table in the database
         *
         * @throws Exception
         */
        public function saveItem()
        {
            //First need to determine if this is a new News item
            if ($this->_id <= 0)
            {
                $this->createNewItem(); //_id should now be set
            }

            try
            {
                $stmt = $this->_dbconn->prepare("UPDATE News SET Title = :title, Content = :content, PanelShow = :panelshow, PanelExpire = :panelexpire, DateDisplay = :datedisplay, ImgFilename = :imgfilename, ImgPath = :imgpath, URLText = :urltext, MetaDesc = :metadesc, MetaKey = :metakey, MetaTitle = :metatitle, AuthorID = :authorid, AuthorName = :authorname WHERE ID = :id LIMIT 1");
                $result = $stmt->execute([
                                             'title' => $this->_title,
                                             'content' => $this->_content,
                                             'panelshow' => $this->_panelshow,
                                             'panelexpire' => $this->_panelexpire,
                                             'datedisplay' => $this->_datedisplay,
                                             'imgfilename' => $this->_imgfilename,
                                             'imgpath' => $this->_image_path,
                                             'urltext' => $this->_urltext,
                                             'metadesc' => $this->_metadesc,
                                             'metakey' => $this->_metakey,
                                             'metatitle' => $this->_metatitle,
                                             'authorid' => $this->_authorid,
                                             'authorname' => $this->_authorname,
                                             'id' => $this->_id
                                         ]);
                if ($result === true)
                {
                    return true;
                }
                else
                {
                    error_log($stmt->errorInfo()[2]);
                    return false;
                }
            } catch (Exception $e)
            {
                error_log("Failed to save News record: " . $e);
            }
        }

        /**
         * Create new empty news item
         *
         * Sets the _id property accordingly
         */
        public function createNewItem()
        {
            //Only execute if the _id property isn't already set
            if (is_numeric($this->_id) || $this->_id > 0)
            {
                throw new Exception('You cannot create a new News item at this stage - the id property is already set as ' . $this->_id);
            }

            //Create DB item
            try
            {
                $result = $this->_dbconn->query("INSERT INTO News SET Title = ''");
                $lastID = $this->_dbconn->lastInsertId();

                if ($lastID > 0)
                {
                    $this->_id = $lastID;
                }
            } catch (Exception $e)
            {
                error_log("Failed to create new News record: " . $e);
            }
        }

        /**
         * Returns all news records and fields in Assoc array
         * Flags for retrieving just Panel items OR All future items OR All items OR Just past items
         *
         * @param string    Panel | Current | All | Past
         * @param int       Cut off in months for items
         *
         * @return mixed
         */
        public function listAllItems($flagWhichItems = 'Panel', $CutOffInMonths = null)
        {
            switch ($flagWhichItems)
            {
                case 'Panel':
                    $sql = "SELECT * FROM News WHERE DateDisplay <= CURDATE() AND PanelShow = 'Y' AND PanelExpire >= NOW()";
                    break;
                case 'Current':
                    $sql = "SELECT * FROM News WHERE DateDisplay <= CURDATE()";
                    break;
                case 'All':
                    $sql = "SELECT * FROM News";
                    break;
                case 'Past':
                    $sql = "SELECT * FROM News WHERE ID = ID ";
                    break;
                default:
                    $sql = "SELECT * FROM News WHERE DateDisplay <= NOW()";
                    break;
            }


            //Limit the results returned? Only relevant for Current and Past items (commonly used on news index display page)
            if (is_numeric($CutOffInMonths) && $CutOffInMonths > 0)
            {
                //Convert to int
                $CutOffInMonths = floor($CutOffInMonths);
                switch ($flagWhichItems)
                {
                    case 'Current':
                        $sql .= " AND DateDisplay >= DATE_SUB(CURDATE(), INTERVAL ".$CutOffInMonths." MONTH) ";
                        break;
                    case 'Past':
                        $sql .= " AND DateDisplay <= DATE_SUB(CURDATE(), INTERVAL ".$CutOffInMonths." MONTH) ";
                        break;
                }
            }
            $sql .= " ORDER BY DateDisplay DESC";
            
            try
            {
                $stmt = $this->_dbconn->query($sql);
                $stories = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $e)
            {
                error_log("Failed to retrieve News items" . $e);
            }

            //Store details in relevant member
            $this->_allitems = $stories;

            //return the array
            return $stories;
        }

        /**
         * Delete the complete news item - including any images
         *
         * @return mixed
         */
        public function deleteItem()
        {
            if (!is_numeric($this->_id))
            {
                throw new Exception('Class News requires the news item ID to be set if you are trying to delete the item');
            }

            //First delete the image files
            $this->deleteImage();

            // Step through all images for this content item AND DELETE
            $ImgDel = new ImageLibrary('News', $this->_id, null, null, null, USER_UPLOADS.'/images/gallery/');
            $ImgDel->deleteAllImagesForContent('News',$this->_id);

            //Now delete the item from the DB
            try
            {
                $stmt = $this->_dbconn->prepare("DELETE FROM News WHERE ID = :id LIMIT 1");
                $result = $stmt->execute([
                                             'id' => $this->_id
                                         ]);
            } catch (Exception $e)
            {
                error_log("Failed to delete News record: " . $e);
            }

            if ($result === true)
            {
                //Unset the properties
                $this->_id = null;
                $this->_title = null;
                $this->_imgfilename = null;
                $this->_content = null;
                $this->_panelshow = null;
                $this->_panelexpires = null;
                $this->_datedisplay = null;
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


        /**
         * Function to check if a similar URL already exists in the News table
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

        public function URLTextValid($ContentURL, $ID = 0)
        {
            if (!is_string($ContentURL)) {
                throw new Exception('News needs the new URL specifying as a string');
            }
            if ($ID <= 0) {
                $ID = $this->_id;
            }


            if (clean_int($ID) > 0) {
                $sql = "SELECT ID FROM News WHERE URLText = :urltext AND ID != :id";
                $vars = array('urltext' => $ContentURL, 'id' => $ID);
            } else {
                $sql = "SELECT ID FROM News WHERE URLText = :urltext AND (ID IS NULL OR ID <= 0)";
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
            $sql = "SELECT ID FROM News WHERE (Title LIKE :needle OR Content LIKE :needle) AND DateDisplay <= NOW() AND DATE_ADD(DateDisplay, INTERVAL 6 MONTH) > NOW()";
            $stmt = $this->_dbconn->prepare($sql);
            $stmt->execute([
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
                    $link = "//" . SITEFQDN . "/news/" . $content['URLText'];
                }
                else
                {
                    $link = "//" . SITEFQDN . "/content/_newsview.php?id=" . $content['ID'];
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
        public function getPanelShow()
        {
            return $this->_panelshow;
        }

        /**
         * @param $panelshow
         */
        public function setPanelShow($panelshow)
        {
            $this->_panelshow = $panelshow;
        }

        /**
         * @return mixed
         */
        public function getPanelExpire()
        {
            return $this->_panelexpire;
        }

        /**
         * @param $panelexpire
         */
        public function setPanelExpire($panelexpire)
        {
            if ($panelexpire == '' || $panelexpire == '0000-00-00') { $panelexpire = null; }
            $this->_panelexpire = $panelexpire;
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
            if ($datedisplay == '' || $datedisplay == '0000-00-00') { $datedisplay = null; }
            $this->_datedisplay = $datedisplay;
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

        public function getImagePath()
        {
            return $this->_image_path;
        }

        public function setImagePath($image_path)
        {
            $this->_image_path = $image_path;
        }
    }