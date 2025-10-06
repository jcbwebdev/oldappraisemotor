<?php

    namespace PeterBourneComms\Schools;
    use PeterBourneComms\CMS\Database;
    use PeterBourneComms\CMS\FileLibrary;
    use PDO;
    use PDOException;
    use Exception;

    /**
     * Deals with Policy Documents
     *
     * Relies on the Policies table in this structure:
     *
     *  ID
     *  CatID
     *  Title
     *  Version
     *  DateApproved
     *  DateToBeReviewed
     *  Content
     *  AuthorID
     *  AuthorName
     *  MetaDesc
     *  MetaKey
     *  URLText
     *
     * @author Peter Bourne
     * @version 1.1 - added getItemByUrl
     *
     */
    class Policy
    {
        /**
         * @var mixed
         */
        protected $_dbconn;
        /**
         * @var int|string
         */
        protected $_id;
        /**
         * @var
         */
        protected $_cat_id;
        protected $_title;
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

        protected $_version;
        protected $_date_approved;
        protected $_date_to_be_reviewed;
        protected $_meta_desc;
        protected $_meta_key;
        protected $_url_text;

        protected $_mime_type;
        protected $_file_type;
        protected $_file_size;
        protected $_file_name;


        /**
         * @var
         */
        protected $_allitems;


        /**
         * PB_Policy constructor.
         *
         * @param null $id
         *
         * @throws Exception
         */
        public function __construct($id = null)
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
                if (isset($id) && !is_numeric($id))
                {
                    throw new Exception('Class PB_Policy requires id to be specified as an integer');
                }

                //Retrieve current information
                if (isset($id))
                {
                    $this->_id = $id;
                    $this->getItemById($id);
                }

            }
        }


        /**
         * Retrieves specified record ID from table
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
                $stmt = $this->_dbconn->prepare("SELECT * FROM Policies WHERE ID =  :id LIMIT 1");
                $stmt->execute(['id' => $id]);
                $story = $stmt->fetch();
            } catch (Exception $e)
            {
                error_log("Failed to retrieve Policies item details when searching by ID" . $e);
            }

            //Store details in relevant members
            $this->_id = $story['ID'];
            $this->_cat_id = $story['CatID'];
            $this->_title = $story['Title'];
            $this->_content = $story['Content'];
            $this->_date_approved = $story['DateApproved'];
            $this->_date_to_be_reviewed = $story['DateToBeReviewed'];
            $this->_version = $story['Version'];
            $this->_authorid = $story['AuthorID'];
            $this->_authorname = $story['AuthorName'];
            $this->_meta_desc = $story['MetaDesc'];
            $this->_meta_key = $story['MetaKey'];
            $this->_url_text = $story['URLText'];

            return $story;
        }
        
        public function getItemByUrl($urltext)
        {
            try {
                $stmt = $this->_dbconn->prepare("SELECT ID FROM Policies WHERE URLText LIKE :needle LIMIT 1");
                $stmt->execute([
                    'needle' => $urltext
                ]);
                $item = $stmt->fetch();
            } catch (Exception $e) {
                error_log("Schools\Policy->getItemByUrl() Failed to retrieve item details when searching by URLText" . $e);
            }
            //Use other function to populate rest of properties (no sense writing it twice!)_
            return $this->getItemById($item['ID']);
        }


        /**
         * Receive an file data stream
         *
         * @param     $FileStream
         *
         * @throws Exception
         */
        public function uploadFile($FileStream)
        {
            //Check we have some useful data passed
            if ($FileStream == '' || $this->_id <= 0)
            {
                throw new Exception("You must supply a file stream to this function. - and the record should already have an ID");
            }

            //Create FileLibrary object
            $File = new FileLibrary('Policies',$this->_id);
            $File->createFileItem();
            $File->setTitle($this->_title);

            //Author stuff
            $File->setAuthorId($_SESSION['UserDetails']['ID']);
            $File->setAuthorName($_SESSION['UserDetails']['FullName']);


            //Send and save file
            $File->processFile($FileStream,$this->_mime_type,$this->_file_type,$this->_file_size,$this->_file_name);
            $result = $File->saveFileItem();

            if ($result == true)
            {
                //Save the object in its current state
                $this->saveItem();
            }
        }


        /**
         * Saves the current object to the table in the database
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
                $stmt = $this->_dbconn->prepare("UPDATE Policies SET Title = :title, CatID = :catid, Content = :content, AuthorID = :authorid, AuthorName = :authorname, DateApproved = :dateapproved, DateToBeReviewed = :datetobereviewed, Version = :version, MetaDesc = :metadesc, MetaKey = :metakey, URLText = :urltext WHERE ID = :id LIMIT 1");
                $result = $stmt->execute([
                                             'title' => $this->_title,
                                             'catid' => $this->_cat_id,
                                             'content' => $this->_content,
                                             'dateapproved' => $this->_date_approved,
                                             'datetobereviewed' => $this->_date_to_be_reviewed,
                                             'version' => $this->_version,
                                             'metadesc' => $this->_meta_desc,
                                             'metakey' => $this->_meta_key,
                                             'urltext' => $this->_url_text,
                                             'authorid' => $this->_authorid,
                                             'authorname' => $this->_authorname,
                                             'id' => $this->_id
                                         ]);
                if ($result == true)
                {
                    return true;
                }
                else
                {
                    //return $stmt->errorInfo();
                    error_log($stmt->errorInfo()[2]);
                    exit;
                }
            } catch (Exception $e)
            {
                error_log("Failed to save Policies record: " . $e);
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
                throw new Exception('You cannot create a new Policies item at this stage - the id property is already set as ' . $this->_id);
            }

            //Create DB item
            try
            {
                $result = $this->_dbconn->query("INSERT INTO Policies SET Title = ''");
                $lastID = $this->_dbconn->lastInsertId();

                if ($lastID > 0)
                {
                    $this->_id = $lastID;
                }
            } catch (Exception $e)
            {
                error_log("Failed to create new Policies record: " . $e);
            }
        }

        /**
         * Returns all records and fields in Assoc array
         * Flags for retrieving just Panel items OR All future items OR All items OR Just past items
         *
         *
         * @return mixed
         */
        public function listAllItems()
        {
            $sql = "SELECT * FROM Policies ORDER BY DateDisplay DESC, Title ASC";

            try
            {
                $stmt = $this->_dbconn->query($sql);
                $stories = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $e)
            {
                error_log("Failed to retrieve Policies items" . $e);
            }

            //Store details in relevant member
            $this->_allitems = $stories;

            //return the array
            return $stories;
        }

        /**
         * Delete the complete item
         *
         * @return mixed
         * @throws Exception
         */
        public function deleteItem()
        {
            if (!is_numeric($this->_id))
            {
                throw new Exception('Class PB_Policys requires the item ID to be set if you are trying to delete the item');
            }

            //Delete associated files
            $FO = new FileLibrary('Policies',$this->_id);
            $FO->deleteAllFilesForContent('Policies',$this->_id);

            //Delete from Cats table
            /*$stmt = $this->_dbconn->prepare("DELETE FROM PoliciesByType WHERE PolicyID = :id LIMIT 1");
            $result = $stmt->execute([
                                         'id' => $this->_id
                                     ]);
            */

            //Now delete the item from the DB
            try
            {
                $stmt = $this->_dbconn->prepare("DELETE FROM Policies WHERE ID = :id LIMIT 1");
                $result = $stmt->execute([
                                             'id' => $this->_id
                                         ]);
            } catch (Exception $e)
            {
                error_log("Failed to delete Policies record: " . $e);
            }

            if ($result == true)
            {
                //Unset the properties
                $this->_id = null;
                $this->_cat_id = null;
                $this->_title = null;
                $this->_content = null;
                $this->_date_approved = null;
                $this->_date_to_be_reviewed = null;
                $this->_authorid = null;
                $this->_authorname = null;
                $this->_version = null;
                $this->_meta_desc = null;
                $this->_meta_key = null;

                return true;
            }
            else
            {
                return false;
            }

        }

        /**
         * Function to count documents in specified category - will look at Policies table
         *
         * @param $id
         *
         * @return
         * @throws Exception
         */
        public function countDocsInCategory($id = 0)
        {
            if ($id <= 0)
            {
                //Select all records with no category
                $sql = "SELECT COUNT(ID) FROM Policies WHERE CatID = :catid OR CatID IS NULL ORDER BY DateUploaded DESC, Title ASC";
            }
            else
            {
                $sql = "SELECT COUNT(ID) FROM Policies WHERE CatID = :catid ORDER BY DateUploaded DESC, Title ASC";
            }

            try
            {
                $stmt = $this->_dbconn->prepare($sql);
                $result = $stmt->execute([
                                             'catid' => $id
                                         ]);
                $doccount = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $e)
            {
                error_log("Failed to count documents in category" . $e);
            }

            return $doccount[0];
        }


        /**
         * Function to return all documents in specified category - will look at Policies table
         *
         * @param $id
         *
         * @return
         * @throws Exception
         */
        public function returnDocsInCategory($id = 0)
        {
            if ($id <= 0)
            {
                //Select all records with no category
                $sql = "SELECT * FROM Policies WHERE CatID = :catid OR CatID IS NULL ORDER BY Title ASC";
            }
            else
            {
                $sql = "SELECT * FROM Policies WHERE CatID = :catid ORDER BY Title ASC";
            }

            try
            {
                $stmt = $this->_dbconn->prepare($sql);
                $stmt->execute([
                                   'catid' => $id
                               ]);
                $docs = array();
                while($doc = $stmt->fetch(PDO::FETCH_ASSOC))
                {
                    $files = $this->getFileDetails($doc['ID']);
                    $thisdoc = array('DocInfo'=>$doc,'FileInfo'=>$files);
                    $docs[] = $thisdoc;
                }
            } catch (Exception $e)
            {
                error_log("Failed to return documents in category" . $e);
            }

            return $docs;
        }


        /**
         * Function to retrieve file details for specified document
         *
         */
        public function getFileDetails($id = 0)
        {
            if (!is_numeric($id) || $id <= 0)
            {
                $id = $this->_id;
            }
            if ($id <= 0)
            {
                throw new Exception('PB_Policy->getFileDetails requires a ID to be specified when counting or retrieving records');
            }

            //Use FileLibrary to see
            $FO = new FileLibrary('Policies',$id);
            $Files = $FO->retrieveAllFiles('Policies',$id);

            if (is_array($Files) && count($Files) > 0)
            {
                return $Files;
            }
            else
            {
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
         * @param int $ID
         * @param int $ParentID
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
                throw new Exception('PB_Policy needs the new URL specifying as a string');
            }


            if (clean_int($ID) > 0)
            {
                $sql = "SELECT ID FROM Policies WHERE URLText = :urltext AND ID != :id";
                $vars = array('urltext' => $ContentURL, 'id' => $ID);
            }
            else
            {
                $sql = "SELECT ID FROM Policies WHERE URLText = :urltext";
                $vars = array('urltext' => $ContentURL);
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



        ###########################################################
        # Getters and Setters
        ###########################################################


        /**
         * @return int|string
         */
        public function getId()
        {
            return $this->_id;
        }

        /**
         * @param int|string $id
         */
        public function setId($id)
        {
            $this->_id = $id;
        }

        /**
         * @return mixed
         */
        public function getCatId()
        {
            return $this->_cat_id;
        }

        /**
         * @param mixed $cat_id
         */
        public function setCatId($cat_id)
        {
            $this->_cat_id = $cat_id;
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
        public function getDateDisplay()
        {
            return $this->_date_display;
        }

        /**
         * @param mixed $date_display
         */
        public function setDateDisplay($date_display)
        {
            if ($date_display == '' || $date_display == '0000-00-00' || $date_display == '0000-00-00 00:00:00') { $date_display = null; }
            $this->_date_display = $date_display;
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
        public function getMimeType()
        {
            return $this->_mime_type;
        }

        /**
         * @param mixed $mime_type
         */
        public function setMimeType($mime_type)
        {
            $this->_mime_type = $mime_type;
        }

        /**
         * @return mixed
         */
        public function getFileType()
        {
            return $this->_file_type;
        }

        /**
         * @param mixed $file_type
         */
        public function setFileType($file_type)
        {
            $this->_file_type = $file_type;
        }

        /**
         * @return mixed
         */
        public function getFileSize()
        {
            return $this->_file_size;
        }

        /**
         * @param mixed $file_size
         */
        public function setFileSize($file_size)
        {
            $this->_file_size = $file_size;
        }

        /**
         * @return mixed
         */
        public function getFileName()
        {
            return $this->_file_name;
        }

        /**
         * @param mixed $file_name
         */
        public function setFileName($file_name)
        {
            $this->_file_name = $file_name;
        }

        /**
         * @return mixed
         */
        public function getVersion()
        {
            return $this->_version;
        }

        /**
         * @param mixed $version
         */
        public function setVersion($version)
        {
            if (!is_numeric($version) || $version <= 0 ) { $version = null; }
            $this->_version = $version;
        }

        /**
         * @return mixed
         */
        public function getDateApproved()
        {
            return $this->_date_approved;
        }

        /**
         * @param mixed $date_approved
         */
        public function setDateApproved($date_approved)
        {
            $this->_date_approved = $date_approved;
        }

        /**
         * @return mixed
         */
        public function getDateToBeReviewed()
        {
            return $this->_date_to_be_reviewed;
        }

        /**
         * @param mixed $date_to_be_reviewed
         */
        public function setDateToBeReviewed($date_to_be_reviewed)
        {
            $this->_date_to_be_reviewed = $date_to_be_reviewed;
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
        public function setMetaDesc($meta_desc)
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
        public function setMetaKey($meta_key)
        {
            $this->_meta_key = $meta_key;
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
        public function setUrlText($url_text)
        {
            $this->_url_text = $url_text;
        }








    }