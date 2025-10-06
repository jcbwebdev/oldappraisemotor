<?php

    namespace PeterBourneComms\CMS;
    use PDO;
    use PDOException;
    use Exception;


    /**
     * Deals with Home page buttons
     *
     * It will allow you to
     *  - retrieve an individual home button item
     *  - retrieve an array of all homebuttons
     *  - delete button
     *
     * Relies on the HomePanels table in this structure:
     *  ID
     *  Title
     *  LinkURL
     *  BGCol
     *  DisplayOrder
     *  AuthorID
     *  AuthorName
     *  NewWindow
     *
     *
     * @author Peter Bourne
     * @version 1.0
     *
     */
    class HomeButton
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
        protected $_link_url;
        /**
         * @var
         */
        protected $_bg_col;
        /**
         * @var
         */
        protected $_author_id;
        /**
         * @var
         */
        protected $_author_name;
        /**
         * @var
         */
        protected $_new_window;


        protected $_display_order;


        /**
         * @var
         */
        protected $_allitems;


        /**
         * HomeButton constructor.
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
                //Assess passed carousel id
                if (isset($id) && !is_numeric($id))
                {
                    throw new Exception('Class HomeButton requires id to be specified as an integer');
                }

                //Retrieve current panel information
                if (isset($id))
                {
                    $this->_id = $id;
                    $this->getItemById($id);
                }

            }
        }


        /**
         * Retrieves specified home button record ID from HomeButtons table
         * Populates object member elements
         *
         * @param $id
         */
        public function getItemById($id)
        {
            try
            {
                $stmt = $this->_dbconn->prepare("SELECT * FROM HomeButtons WHERE ID =  :id LIMIT 1");
                $stmt->execute(['id' => $id]);
                $button = $stmt->fetch();
            } catch (Exception $e)
            {
                error_log("Failed to retrieve Home Button details when searching by ID" . $e);
            }

            //Store details in relevant members
            $this->_id = $button['ID'];
            $this->_title = $button['Title'];
            $this->_link_url = $button['LinkURL'];
            $this->_display_order = $button['DisplayOrder'];
            $this->_author_id = $button['AuthorID'];
            $this->_author_name = $button['AuthorName'];
            $this->_bg_col = $button['BGCol'];
            $this->_new_window = $button['NewWindow'];

            return $button;
        }


        /**
         * Create new empty home button item
         *
         * Sets the _id property accordingly
         */
        public function createNewItem()
        {
            //Only execute if the _id property isn't already set
            if (is_numeric($this->_id) || $this->_id > 0)
            {
                throw new Exception('You cannot create a new Home Button at this stage - the id property is already set as '.$this->_id);
            }

            //Create DB item
            try
            {
                $result = $this->_dbconn->query("INSERT INTO HomeButtons SET Title = ''");
                $lastID = $this->_dbconn->lastInsertId();

                if ($lastID > 0)
                {
                    $this->_id = $lastID;
                }
            }
            catch (Exception $e) {
                error_log("Failed to create new Home Button record: " . $e);
            }
        }


        /**
         * Saves the current object to the HomeButtons table in the database
         *
         * @throws Exception
         */
        public function saveItem()
        {
            //First need to determine if this is a new Test item
            if ($this->_id <= 0)
            {
                $this->createNewItem(); //_id should now be set
            }

            try {
                $stmt = $this->_dbconn->prepare("UPDATE HomeButtons SET Title = :title, LinkURL = :linkurl, BGCol = :bgcol, DisplayOrder = :displayorder, AuthorID = :authorid, AuthorName = :authorname, NewWindow = :newwindow WHERE ID = :id LIMIT 1");
                $result = $stmt->execute([
                    'title' => $this->_title,
                    'linkurl' => $this->_link_url,
                    'bgcol' => $this->_bg_col,
                    'displayorder' => $this->_display_order,
                    'authorid' => $this->_author_id,
                    'authorname' => $this->_author_name,
                    'newwindow' => $this->_new_window,
                    'id' => $this->_id
                               ]);
                if ($result == true) { return true; } else { return $stmt->errorInfo(); }
            } catch (Exception $e) {
                error_log("Failed to save HomeButton record: " . $e);
            }
        }


        /**
         * Returns all HomeButton records and fields in Assoc array
         *
         * @return mixed
         */
        public function getAllHomeButtons()
        {
            $sql = "SELECT * FROM HomeButtons ORDER BY DisplayOrder ASC";

            try
            {
                $stmt = $this->_dbconn->query($sql);
                $buttons = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $e)
            {
                error_log("Failed to retrieve Home Button items" . $e);
            }

            //Store details in relevant member
            $this->_allitems = $buttons;

            //return the array
            return $buttons;
        }

        /**
         * Delete the complete home button item
         *
         * @return mixed
         */
        public function deleteItem()
        {
            if (!is_numeric($this->_id))
            {
                throw new Exception('Class HomeButton requires the button item ID to be set if you are trying to delete the item');
            }

            //Now delete the item from the DB
            try {
                $stmt = $this->_dbconn->prepare("DELETE FROM HomeButtons WHERE ID = :id LIMIT 1");
                $result = $stmt->execute([
                                   'id' => $this->_id
                               ]);
            } catch (Exception $e) {
                error_log("Failed to delete Home Button record: " . $e);
            }

            if ($result == true)
            {
                //Unset the properties
                $this->_id = null;
                $this->_title = null;
                $this->_link_url = null;
                $this->_bg_col = null;
                $this->_display_order = null;
                $this->_author_id = null;
                $this->_author_name = null;
                $this->_new_window = null;

                return true;
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
        public function getLinkURL()
        {
            return $this->_link_url;
        }

        /**
         * @param $linkurl
         */
        public function setLinkURL($linkurl)
        {
            $this->_link_url = $linkurl;
        }

        /**
         * @return mixed
         */
        public function getDisplayOrder()
        {
            return $this->_display_order;
        }

        /**
         * @param $displayorder
         */
        public function setDisplayOrder($displayorder)
        {
            $this->_display_order = $displayorder;
        }

        public function getBGCol()
        {
            return $this->_bg_col;
        }

        public function setBGCol($bgcol)
        {
            $this->_bg_col = $bgcol;
        }
        /**
         * @return mixed
         */
        public function getAuthorID()
        {
            return $this->_author_id;
        }

        /**
         * @param $authorid
         */
        public function setAuthorID($authorid)
        {
            $this->_author_id = $authorid;
        }

        /**
         * @return mixed
         */
        public function getAuthorName()
        {
            return $this->_author_name;
        }

        /**
         * @param $authorname
         */
        public function setAuthorName($authorname)
        {
            $this->_author_name = $authorname;
        }

        /**
         * @return mixed
         */
        public function getNewWindow()
        {
            return $this->_new_window;
        }

        /**
         * @param mixed $new_window
         */
        public function setNewWindow($new_window)
        {
            $this->_new_window = $new_window;
        }

        /**
         * @return mixed
         */
        public function getAllitems()
        {
            return $this->_allitems;
        }

    }