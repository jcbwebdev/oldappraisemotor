<?php
    namespace PeterBourneComms\CMS;
    use PDO;
    use PDOException;
    use Exception;
    use PeterBourneComms\CARS\Content;
    
    /**
     * Deals with FAQ items
     *
     *
     * @author Peter Bourne
     * @version 1.1
     *
     * 1.0      22.06.2020          Original version
     * 1.1      30.11.2022          PSR-4 conversion
     * 
     */
    
    class FAQ
    {
        /**
         * @var mixed
         */
        protected $_dbconn;
        /**
         * @var int|string
         */
        protected $_id;
        protected $_content_id;
        /**
         * @var
         */
        protected $_title;
        /**
         * @var
         */
        protected $_content;
        protected $_author_id;
        protected $_author_name;
        protected $_display_order;
        protected $_date_edited;
        

        /**
         * FAQ constructor.
         *
         * @param null   $id
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
                if ($id != null && !is_numeric($id))
                {
                    throw new Exception('CMS\FAQ->__construct() requires id to be specified as an integer');
                }

                //Retrieve current news information
                if (isset($id))
                {
                    $this->_id = $id;
                    $this->getItemById($id);
                }
            }
        }


        /**
         * Retrieves specified content record ID from FAQs table
         * Populates object member elements
         *
         * @param int   $id
         */
        public function getItemById($id)
        {
            try
            {
                $stmt = $this->_dbconn->prepare("SELECT * FROM FAQs WHERE ID =  :id LIMIT 1");
                $stmt->execute(['id' => $id]);
                $item = $stmt->fetch();
            } catch (Exception $e)
            {
                error_log("CMS\FAQ->getItemById() Failed to retrieve FAQs item details when searching by ID" . $e);
            }

            //Store details in relevant members
            $this->_id = $item['ID'];
            $this->_content_id = $item['ContentID'];
            $this->_title = $item['Title'];
            $this->_content = $item['Content'];
            $this->_date_edited = $item['DateEdited'];
            $this->_display_order = $item['DisplayOrder'];
            $this->_author_id = $item['AuthorID'];
            $this->_author_name = $item['AuthorName'];

            //Retrieve content title
            $CO = new Content();
            if (is_object($CO)) {
                $Content = $CO->getContentItemById($this->_content_id);
                if (is_array($Content) || count($Content) > 0) {
                    //Parent info needed?
                    if ($Content['ParentID'] != '') {
                        //Get parent info
                        $Parent = $CO->getContentItemById($Content['ParentID']);
                        if (is_array($Parent) && count($Parent) > 0) {
                            $item['ContentTitle'] = $Parent['Title']." -&gt; ".$item['ContentTitle'] = $Content['Title'];
                        }
                    } else {
                        $item['ContentTitle'] = $Content['Title'];
                    }
                }
            }

            return $item;
        }


        /**
         * Returns array of complete database records for all items - as searched for
         *
         * @param string $needle     record id or string - default = empty
         * @param string $searchtype 'id','title','type' - default = title
         *
         * @return array
         */
        public function listAllItems($needle = '', $searchtype = '')
        {
            $basesql = "SELECT ID FROM FAQs ";

            $params = array();

            switch ($searchtype)
            {
                case 'id':
                    $search = " WHERE ID = :needle";
                    $order = " LIMIT 1";
                    $params['needle'] = $needle;
                    break;

                case 'contentid':
                    $search = " WHERE ContentID = :needle";
                    $order = " ORDER BY DisplayOrder ASC, Title ASC";
                    $params['needle'] = $needle;
                    break;

                case 'title':
                    $search = " WHERE Title LIKE :needle";
                    $order = " ORDER BY DisplayOrder ASC, Title ASC";
                    $params['needle'] = $needle."%";
                    break;

                default:
                    $search = "";
                    $order = " ORDER BY DisplayOrder ASC, Title ASC";
                    //$params['needle'] = $needle;
                    break;
            }


            //Create sql
            $sql = $basesql . $search . $order;
            //echo $sql;
            //print_r($params);

            $items = array();
            try
            {
                $stmt = $this->_dbconn->prepare($sql);
                $stmt->execute($params);
                while ($item = $stmt->fetch(PDO::FETCH_ASSOC))
                {
                    $items[] = $this->getItemById($item['ID']);
                }
            } catch (Exception $e)
            {
                error_log("CMS\FAQ->listAllItems() Failed to retrieve FAQ items" . $e);
            }

            //return the array
            return $items;
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
                throw new Exception('CMS\FAQ->createNewItem() You cannot create a new FAQs item at this stage - the id property is already set as '.$this->_id);
            }

            //Create DB item
            try
            {
                $result = $this->_dbconn->query("INSERT INTO FAQs SET Title = ''");
                $lastID = $this->_dbconn->lastInsertId();

                if ($lastID > 0)
                {
                    $this->_id = $lastID;
                }
            }
            catch (Exception $e) {
                error_log("CMS\FAQ->createNewItem() Failed to create new FAQs record: " . $e);
            }
        }


        /**
         * Saves the current object to the FAQs table in the database
         *
         * @throws Exception
         */
        public function saveItem()
        {
            //First need to determine if this is a new FAQs item
            if ($this->_id <= 0)
            {
                $this->createNewItem(); //_id should now be set
            }

            try {
                $stmt = $this->_dbconn->prepare("UPDATE FAQs SET ContentID = :ContentID, Title = :title, Content = :content,  DateEdited = NOW(), DisplayOrder = :displayorder, AuthorID = :authorid, AuthorName = :authorname WHERE ID = :id LIMIT 1");
                $result = $stmt->execute([
                    'ContentID' => $this->_content_id,
                    'title' => $this->_title,
                    'content' => $this->_content,
                    'displayorder' => $this->_display_order,
                    'authorid' => $this->_author_id,
                    'authorname' => $this->_author_name,
                    'id' => $this->_id
                ]);

                if ($result === true) {
                    return true;
                } else {
                    error_log($stmt->errorInfo()[2]);
                    return false;
                }
            } catch (Exception $e) {
                error_log("CMS\FAQ->saveItem() Failed to save FAQs record: " . $e);
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
                throw new Exception('CMS\FAQ->deleteItem() requires the content item ID to be set if you are trying to delete the item');
            }


            //Now delete the item from the DB
            try {
                $stmt = $this->_dbconn->prepare("DELETE FROM FAQs WHERE ID = :id LIMIT 1");
                $result = $stmt->execute([
                    'id' => $this->_id
                ]);
            } catch (Exception $e) {
                error_log("CMS\FAQ->deleteItem() Failed to delete FAQs record: " . $e);
            }

            if ($result === true)
            {
                //Unset the properties
                $this->_id = null;
                $this->_content_id = null;
                $this->_title = null;

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
            $sql = "SELECT ID FROM FAQs WHERE (Title LIKE :needle OR Content LIKE :needle) ";
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
                $CO = new PB_Content();
                if (is_object($CO)) {
                    $CI = $CO->getContentItemById($content['ContentID']);
                    if (is_array($CI) && count($CI) > 0) {
                        if ($CI['URLText'] != '') {
                            $link = "//" . SITEFQDN . "/";
                            if ($CI['ParentID'] > 0) {
                                $Parent = $CO->getContentItemById($CI['ParentID']);
                                $link .= $Parent['URLText']."/";
                            }
                            $link .= $CI['URLText'];
                        } else {
                            $link = "//" . SITEFQDN . "/content/index.php?id=" . $CI['ID'];
                        }
                    }
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
        public function getContentId()
        {
            return $this->_content_id;
        }

        /**
         * @param mixed $meeting_id
         */
        public function setContentId($meeting_id)
        {
            $this->_content_id = $meeting_id;
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
        public function getAuthorId()
        {
            return $this->_author_id;
        }

        /**
         * @param mixed $author_id
         */
        public function setAuthorId($author_id)
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
        public function setAuthorName($author_name)
        {
            $this->_author_name = $author_name;
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
        public function getDateEdited()
        {
            return $this->_date_edited;
        }





    }