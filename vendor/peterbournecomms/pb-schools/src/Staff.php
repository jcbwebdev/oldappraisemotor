<?php

    namespace PeterBourneComms\Schools;
    use PeterBourneComms\CMS\Database;
    use PeterBourneComms\CMS\ImageHandler;
    use PDO;
    use PDOException;
    use Exception;

    /**
     * Deals with Staff items
     *
     * It will allow you to
     *  - specify the size of the Staff image
     *  - specify the location of stored images
     *  - pass an image in and have it resized to that size
     *  - stored in the filesystem and database - along with caption, title and link
     *  - retrieve an individual Staff item
     *  - retrieve an array of all Staff items
     *  - delete Staff item (including images)
     *
     *
     * @author Peter Bourne
     * @version 1.1
     * @history
     *
     * 1.0      ----        Original version
     * 1.1      24/06/2022  Added stafftype filter to listAllItems
     *
     *
     */
    class Staff
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
        protected $_specialism;
        protected $_area_of_work;
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
        protected $_staff_type;
        protected $_display_order;

        /**
         * @var
         */
        protected $_allitems;


        /**
         * Staff constructor.
         *
         * @param null   $id
         * @param int    $width
         * @param int    $height
         * @param string $path
         *
         * @throws Exception
         */
        public function __construct($id = null, $width = 600, $height = 800, $path = USER_UPLOADS.'/images/staff/')
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
                    throw new Exception('Class Staff requires id to be specified as an integer');
                }

                //Assess passed width & height
                if (!is_numeric($width) || !is_numeric($height))
                {
                    throw new Exception('Class Staff requires width and height to be specified as integers');
                }

                //Assess passed path
                if (isset($path) && !is_string($path))
                {
                    throw new Exception('Class Staff requires path to be specified as a string, eg: /user_uploads/images/staff/');
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


                //Retrieve current Staff information
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
         * Retrieves specified Staff record ID from Test table
         * Populates object member elements
         *
         * @param $id
         *
         * @return
         */
        public function getItemById($id)
        {
            try
            {
                $stmt = $this->_dbconn->prepare("SELECT * FROM Staff WHERE ID =  :id LIMIT 1");
                $stmt->execute(['id' => $id]);
                $story = $stmt->fetch();
            } catch (Exception $e)
            {
                error_log("Failed to retrieve Staff item details when searching by ID" . $e);
            }

            //Store details in relevant members
            $this->_id = $story['ID'];
            $this->_staff_type = $story['StaffType'];
            $this->_first_name = $story['Firstname'];
            $this->_surname = $story['Surname'];
            $this->_specialism = $story['Specialism'];
            $this->_area_of_work = $story['AreaOfWork'];
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
                $stmt = $this->_dbconn->prepare("SELECT ID FROM Staff WHERE URLText LIKE :urltext LIMIT 1");
                $stmt->execute(['urltext' => $urltext]);
                $story = $stmt->fetch();
            } catch (Exception $e)
            {
                error_log("Failed to retrieve Staff item details when searching by URLText" . $e);
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
         * Delete the image for this Staff item - assuming _img_filename is set
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
         * Saves the current object to the Staff table in the database
         *
         * @throws Exception
         */
        public function saveItem()
        {
            //First need to determine if this is a new Staff item
            if ($this->_id <= 0)
            {
                $this->createNewItem(); //_id should now be set
            }

            try
            {
                $stmt = $this->_dbconn->prepare("UPDATE Staff SET StaffType = :stafftype, Firstname = :firstname, Surname = :surname, Specialism = :specialism, AreaOfWork = :areaofwork, Content = :content, DateDisplay = :datedisplay, ImgFilename = :imgfilename, ImgPath = :imgpath, AuthorID = :authorid, AuthorName = :authorname, DisplayOrder = :displayorder WHERE ID = :id LIMIT 1");
                $result = $stmt->execute([
                    'stafftype' => $this->_staff_type,
                    'firstname' => $this->_first_name,
                    'surname' => $this->_surname,
                    'specialism' => $this->_specialism,
                    'areaofwork' => $this->_area_of_work,
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
                error_log("Failed to save Staff record: " . $e);
            }
        }

        /**
         * Create new empty Staff item
         *
         * Sets the _id property accordingly
         */
        public function createNewItem()
        {
            //Only execute if the _id property isn't already set
            if (is_numeric($this->_id) || $this->_id > 0)
            {
                throw new Exception('You cannot create a new Staff item at this stage - the id property is already set as ' . $this->_id);
            }

            //Create DB item
            try
            {
                $result = $this->_dbconn->query("INSERT INTO Staff SET Firstname = ''");
                $lastID = $this->_dbconn->lastInsertId();

                if ($lastID > 0)
                {
                    $this->_id = $lastID;
                }
            } catch (Exception $e)
            {
                error_log("Failed to create new Staff record: " . $e);
            }
        }

        /**
         * Returns all Staff records and fields in Assoc array
         * Flags for retrieving just Panel items OR All future items OR All items OR Just past items
         *
         * @param string    Panel | Current | All | Past
         * @param int       Cut off in months for items
         *
         * @return mixed
         */
        public function listAllItems($stafftype = null)
        {
            $basesql = "SELECT ID FROM Staff ";
            $order = " ORDER BY DisplayOrder ASC, Surname ASC";

            if ($stafftype != '') {}
            try
            {
                $stmt = $this->_dbconn->query($sql);
                $stories = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $e)
            {
                error_log("Failed to retrieve Staff items" . $e);
            }

            //Store details in relevant member
            $this->_allitems = $stories;

            //return the array
            return $stories;
        }

        /**
         * Delete the complete Staff item - including any images
         *
         * @return mixed
         */
        public function deleteItem()
        {
            if (!is_numeric($this->_id))
            {
                throw new Exception('Class Staff requires the Staff item ID to be set if you are trying to delete the item');
            }

            //First delete the image files
            $this->deleteImage();

            //Now delete the item from the DB
            try
            {
                $stmt = $this->_dbconn->prepare("DELETE FROM Staff WHERE ID = :id LIMIT 1");
                $result = $stmt->execute([
                    'id' => $this->_id
                ]);
            } catch (Exception $e)
            {
                error_log("Failed to delete Staff record: " . $e);
            }

            if ($result == true)
            {
                //Unset the properties
                $this->_id = null;
                $this->_first_name = null;
                $this->_surname = null;
                $this->_specialism = null;
                $this->_area_of_work = null;
                $this->_imgfilename = null;
                $this->_content = null;
                $this->_datedisplay = null;
                $this->_authorid = null;
                $this->_authorname = null;
                $this->_display_order = null;

                return true;
            }
            else
            {
                return false;
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
            $sql = "SELECT ID FROM Staff WHERE (Firstname LIKE :needle OR Surname LIKE :needle OR Content LIKE :needle) AND DateDisplay <= NOW()";
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
                    $link = "//" . SITEFQDN . "/staff/" . $content['URLText'];
                }
                else
                {
                    $link = "//" . SITEFQDN . "/content/people-detail.php?id=" . $content['ID'];
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
                $search_results[] = array('Title' => $content['Firstname']." ".$content['Surname'], 'Content' => $Content, 'Link' => $link, 'DateDisplay' => $content['DateDisplay'], 'Weighting' => $Weighting);
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
        public function getStaffType()
        {
            return $this->_staff_type;
        }

        /**
         * @param mixed $staff_type
         */
        public function setStaffType($staff_type)
        {
            $this->_staff_type = $staff_type;
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
        public function getAreaOfWork()
        {
            return $this->_area_of_work;
        }

        /**
         * @param mixed $area_of_work
         */
        public function setAreaOfWork($area_of_work)
        {
            $this->_area_of_work = $area_of_work;
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
        public function setDisplayOrder($display_order)
        {
            $this->_display_order = $display_order;
        }



    }