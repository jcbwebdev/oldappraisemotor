<?php

    namespace PeterBourneComms\Schools;
    use PeterBourneComms\CMS\Database;
    use PeterBourneComms\CMS\FileLibrary;
    use PDO;
    use PDOException;
    use Exception;

    /**
     * Deals with Policy Document Categories
     *
     * Relies on the PolicyCats table in this structure:
     *
     *  ID
     *  Title
     *  DisplayOrder
     *  AuthorID
     *  AuthorName*
     *
     * @author Peter Bourne
     * @version 1.0
     *
     */
    class PolicyCat
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
        protected $_title;
        /**
         * @var
         */
        protected $_display_order;
        /**
         * @var
         */
        protected $_authorid;
        /**
         * @var
         */
        protected $_authorname;

        /**
         * @var
         */
        protected $_allitems;


        /**
         * PB_PolicyCat constructor.
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
                //Assess passed carousel id
                if (isset($id) && !is_numeric($id))
                {
                    throw new Exception('Class PB_PolicyCat requires id to be specified as an integer');
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
                $stmt = $this->_dbconn->prepare("SELECT * FROM PolicyCats WHERE ID =  :id LIMIT 1");
                $stmt->execute(['id' => $id]);
                $story = $stmt->fetch();
            } catch (Exception $e)
            {
                error_log("Failed to retrieve PolicyCat item details when searching by ID" . $e);
            }

            //Store details in relevant members
            $this->_id = $story['ID'];
            $this->_title = $story['Title'];
            $this->_display_order = $story['DisplayOrder'];
            $this->_authorid = $story['AuthorID'];
            $this->_authorname = $story['AuthorName'];

            return $story;
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
                $stmt = $this->_dbconn->prepare("UPDATE PolicyCats SET Title = :title, DisplayOrder = :displayorder, AuthorID = :authorid, AuthorName = :authorname WHERE ID = :id LIMIT 1");
                $result = $stmt->execute([
                                             'title' => $this->_title,
                                             'displayorder' => $this->_display_order,
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
                    return $stmt->errorInfo();
                }
            } catch (Exception $e)
            {
                error_log("Failed to save PolicyCat record: " . $e);
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
                throw new Exception('You cannot create a new PolicyCat item at this stage - the id property is already set as ' . $this->_id);
            }

            //Create DB item
            try
            {
                $result = $this->_dbconn->query("INSERT INTO PolicyCats SET Title = ''");
                $lastID = $this->_dbconn->lastInsertId();

                if ($lastID > 0)
                {
                    $this->_id = $lastID;
                }
            } catch (Exception $e)
            {
                error_log("Failed to create new PolicyCat record: " . $e);
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
            $sql = "SELECT * FROM PolicyCats ORDER BY DisplayOrder ASC, Title ASC";

            try
            {
                $stmt = $this->_dbconn->query($sql);
                $stories = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $e)
            {
                error_log("Failed to retrieve PolicyCat items" . $e);
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
                throw new Exception('Class PB_PolicyCat requires the item ID to be set if you are trying to delete the item');
            }


            //Now delete the item from the DB
            try
            {
                $stmt = $this->_dbconn->prepare("DELETE FROM PolicyCats WHERE ID = :id LIMIT 1");
                $result = $stmt->execute([
                                             'id' => $this->_id
                                         ]);
            } catch (Exception $e)
            {
                error_log("Failed to delete PolicyCat record: " . $e);
            }

            if ($result == true)
            {
                //Unset the properties
                $this->_id = null;
                $this->_title = null;
                $this->_display_order = null;
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
         * Function to count documents in specified category - will look at GovDocs table
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
         * Function to return all documents in specified category - will look at GovDocs table
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
                throw new Exception('PB_GovDoc->getFileDetails requires a ID to be specified when counting or retrieving records');
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


    }