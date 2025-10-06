<?php

    namespace PeterBourneComms\Schools;
    use PeterBourneComms\CMS\Database;
    use PeterBourneComms\CMS\ImageHandler;
    use PDO;
    use PDOException;
    use Exception;


    /**
     * Deals with Governor items
     *
     * It will allow you to
     *  - specify the size of the Governor image
     *  - specify the location of stored images
     *  - pass an image in and have it resized to that size
     *  - stored in the filesystem and database - along with caption, title and link
     *  - retrieve an individual Governor item
     *  - retrieve an array of all Governor items
     *  - delete Governor item (including images)
     *
     * @author Peter Bourne
     * @version 1.0
     *
     */
    class Governor
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
        protected $_datedisplay;
        /**
         * @var
         */
        protected $_authorid;
        /**
         * @var
         */
        protected $_authorname;

        protected $_first_name;
        protected $_surname;
        protected $_governor_type;
        protected $_display_order;

        /**
         * @var
         */
        protected $_allitems;


        /**
         * Governor constructor.
         *
         * @param null   $id
         * @param int    $width
         * @param int    $height
         * @param string $path
         *
         * @throws Exception
         */
        public function __construct($id = null, $width = 600, $height = 800, $path = USER_UPLOADS.'/images/governors/')
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
                    throw new Exception('Class Governor requires id to be specified as an integer');
                }

                //Assess passed width & height
                if (!is_numeric($width) || !is_numeric($height))
                {
                    throw new Exception('Class Governor requires width and height to be specified as integers');
                }

                //Assess passed path
                if (isset($path) && !is_string($path))
                {
                    throw new Exception('Class Governor requires path to be specified as a string, eg: /user_uploads/images/Governor/');
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


                //Retrieve current Governor information
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
         * Retrieves specified Governor record ID from Test table
         * Populates object member elements
         *
         * @param $id
         */
        public function getItemById($id)
        {
            try
            {
                $stmt = $this->_dbconn->prepare("SELECT * FROM Governors WHERE ID =  :id LIMIT 1");
                $stmt->execute(['id' => $id]);
                $story = $stmt->fetch();
            } catch (Exception $e)
            {
                error_log("Failed to retrieve Governor item details when searching by ID" . $e);
            }

            //Store details in relevant members
            $this->_id = $story['ID'];
            $this->_governor_type = $story['GovernorType'];
            $this->_first_name = $story['Firstname'];
            $this->_surname = $story['Surname'];
            $this->_title = $story['Title'];
            $this->_imgfilename = $story['ImgFilename'];
            $this->_image_path = $story['ImgPath'];
            $this->_content = $story['Content'];
            $this->_datedisplay = $story['DateDisplay'];
            $this->_authorid = $story['AuthorID'];
            $this->_authorname = $story['AuthorName'];
            $this->_display_order = $story['DisplayOrder'];


            return $story;
        }

        /*public function getItemByUrl($urltext)
        {
            try
            {
                $stmt = $this->_dbconn->prepare("SELECT ID FROM Governors WHERE URLText LIKE :urltext LIMIT 1");
                $stmt->execute(['urltext' => $urltext]);
                $story = $stmt->fetch();
            } catch (Exception $e)
            {
                error_log("Failed to retrieve Governor item details when searching by URLText" . $e);
            }
            //Use other function to populate rest of properties (no sense writing it twice!)_
            return $this->getItemById($story['ID']);
        }*/


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
         * Delete the image for this Governor item - assuming _img_filename is set
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
         * Saves the current object to the Governor table in the database
         *
         * @throws Exception
         */
        public function saveItem()
        {
            //First need to determine if this is a new Governor item
            if ($this->_id <= 0)
            {
                $this->createNewItem(); //_id should now be set
            }

            try
            {
                $stmt = $this->_dbconn->prepare("UPDATE Governors SET GovernorType = :governortype, Firstname = :firstname, Surname = :surname, Title = :title, Content = :content, DateDisplay = :datedisplay, ImgFilename = :imgfilename, ImgPath = :imgpath, AuthorID = :authorid, AuthorName = :authorname, DisplayOrder = :displayorder WHERE ID = :id LIMIT 1");
                $result = $stmt->execute([
                                             'governortype' => $this->_governor_type,
                                             'firstname' => $this->_first_name,
                                             'surname' => $this->_surname,
                                             'title' => $this->_title,
                                             'content' => $this->_content,
                                             'datedisplay' => $this->_datedisplay,
                                             'imgfilename' => $this->_imgfilename,
                                             'imgpath' => $this->_image_path,
                                             'authorid' => $this->_authorid,
                                             'authorname' => $this->_authorname,
                                             'displayorder' => $this->_display_order,
                                             'id' => $this->_id
                                         ]);
                if ($result == true)
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
                error_log("Failed to save Governor record: " . $e);
            }
        }

        /**
         * Create new empty Governor item
         *
         * Sets the _id property accordingly
         */
        public function createNewItem()
        {
            //Only execute if the _id property isn't already set
            if (is_numeric($this->_id) || $this->_id > 0)
            {
                throw new Exception('You cannot create a new Governor item at this stage - the id property is already set as ' . $this->_id);
            }

            //Create DB item
            try
            {
                $result = $this->_dbconn->query("INSERT INTO Governors SET Title = ''");
                $lastID = $this->_dbconn->lastInsertId();

                if ($lastID > 0)
                {
                    $this->_id = $lastID;
                }
            } catch (Exception $e)
            {
                error_log("Failed to create new Governor record: " . $e);
            }
        }

        /**
         * Returns all Governor records and fields in Assoc array
         * Flags for retrieving just Panel items OR All future items OR All items OR Just past items
         *
         * @param string    Panel | Current | All | Past
         * @param int       Cut off in months for items
         *
         * @return mixed
         */
        public function listAllItems($sortorder = 'display')
        {
            switch ($sortorder) {
                case 'surname':
                    $sort = " ORDER BY Surname ASC, DisplayOrder ASC";
                    break;
                default:
                    $sort = " ORDER BY DisplayOrder ASC, Surname ASC";
                    break;
            }
            $sql = "SELECT * FROM Governors ".$sort;

            try
            {
                $stmt = $this->_dbconn->query($sql);
                $stories = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $e)
            {
                error_log("Failed to retrieve Governor items" . $e);
            }

            //Store details in relevant member
            $this->_allitems = $stories;

            //return the array
            return $stories;
        }

        /**
         * Delete the complete Governor item - including any images
         *
         * @return mixed
         */
        public function deleteItem()
        {
            if (!is_numeric($this->_id))
            {
                throw new Exception('Class Governor requires the Governor item ID to be set if you are trying to delete the item');
            }

            //First delete the image files
            $this->deleteImage();

            //Now delete the item from the DB
            try
            {
                $stmt = $this->_dbconn->prepare("DELETE FROM Governors WHERE ID = :id LIMIT 1");
                $result = $stmt->execute([
                                             'id' => $this->_id
                                         ]);
            } catch (Exception $e)
            {
                error_log("Failed to delete Governor record: " . $e);
            }

            if ($result == true)
            {
                //Unset the properties
                $this->_id = null;
                $this->_first_name = null;
                $this->_surname = null;
                $this->_title = null;
                $this->_imgfilename = null;
                $this->_content = null;
                $this->_datedisplay = null;
                $this->_authorid = null;
                $this->_authorname = null;

                return true;
            }
            else
            {
                return false;
            }

        }


        /**
         * Function to check if a similar URL already exists in the Governor table
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
                throw new Exception('Governor needs the new URL specifying as a string');
            }


            if (clean_int($ID) > 0)
            {
                $sql = "SELECT ID FROM Governors WHERE URLText = :urltext AND ID != :id";
                $vars = array('urltext' => $ContentURL, 'id' => $ID);
            }
            else
            {
                $sql = "SELECT ID FROM Governors WHERE URLText = :urltext AND (ID IS NULL OR ID <= 0)";
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
            $sql = "SELECT ID FROM Governor WHERE (Title LIKE :needle OR Content LIKE :needle) AND DateDisplay <= NOW()";
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
                    $link = "//" . SITEFQDN . "/governor/" . $content['URLText'];
                }
                else
                {
                    $link = "//" . SITEFQDN . "/content/governorview.php?id=" . $content['ID'];
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
        public function getFirstName()
        {
            return $this->_first_name;
        }

        /**
         * @param mixed $first_name
         */
        public function setFirstName($first_name)
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
        public function setSurname($surname)
        {
            $this->_surname = $surname;
        }

        /**
         * @return mixed
         */
        public function getGovernorType()
        {
            return $this->_governor_type;
        }

        /**
         * @param mixed $governor_type
         */
        public function setGovernorType($governor_type)
        {
            $this->_governor_type = $governor_type;
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
        public function getDisplayOrder()
        {
            return $this->_display_order;
        }

        /**
         * @param mixed $display_order
         */
        public function setDisplayOrder($display_order): void
        {
            $this->_display_order = $display_order;
        }


    }