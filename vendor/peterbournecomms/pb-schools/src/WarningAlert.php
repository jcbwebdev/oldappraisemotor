<?php

    namespace PeterBourneComms\Schools;
    use PeterBourneComms\CMS\Database;
    use PDO;
    use PDOException;
    use Exception;


    /**
     * Deals with Warning Alerts
     *
     * It will allow you to
     *  - retrieve an individual alert item
     *  - retrieve an array of all alerts
     *  - delete alert
     *
     * Relies on the WarningAlert table in this structure:
     *  ID
     *  Title
     *  Content
     *  DateExpires
     *  MessageType
     *  Display
     *  AuthorID
     *  AuthorName
     *
     *
     * @author Peter Bourne
     * @version 1.1
     *
     */
    class WarningAlert
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
        protected $_content;
        /**
         * @var
         */
        protected $_date_expires;
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
        protected $_message_type;


        protected $_display;


        /**
         * @var
         */
        protected $_allitems;


        /**
         * WarningAlert constructor.
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
                    throw new Exception('Class WarningAlert requires id to be specified as an integer');
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
         * Retrieves specified alert record ID
         * Populates object member elements
         *
         * @param $id
         */
        public function getItemById($id)
        {
            try
            {
                $stmt = $this->_dbconn->prepare("SELECT * FROM WarningAlert WHERE ID =  :id LIMIT 1");
                $stmt->execute(['id' => $id]);
                $alert = $stmt->fetch();
            } catch (Exception $e)
            {
                error_log("Failed to retrieve Alert details when searching by ID" . $e);
            }

            //Store details in relevant members
            $this->_id = $alert['ID'];
            $this->_title = $alert['Title'];
            $this->_content= $alert['Content'];
            $this->_date_expires = $alert['DateExpires'];
            $this->_author_id = $alert['AuthorID'];
            $this->_author_name = $alert['AuthorName'];
            $this->_message_type = $alert['MessageType'];
            $this->_display = $alert['Display'];

            return $alert;
        }


        /**
         * Create new empty Alert item
         *
         * Sets the _id property accordingly
         */
        public function createItem()
        {
            //Only execute if the _id property isn't already set
            if (is_numeric($this->_id) || $this->_id > 0)
            {
                throw new Exception('You cannot create a new Alert at this stage - the id property is already set as '.$this->_id);
            }

            //Create DB item
            try
            {
                $result = $this->_dbconn->query("INSERT INTO WarningAlert SET Title = ''");
                $lastID = $this->_dbconn->lastInsertId();

                if ($lastID > 0)
                {
                    $this->_id = $lastID;
                }
            }
            catch (Exception $e) {
                error_log("Failed to create new Alert record: " . $e);
            }
        }


        /**
         * Saves the current object to the WarningAlert table in the database
         *
         * @throws Exception
         */
        public function saveItem()
        {
            //First need to determine if this is a new Test item
            if ($this->_id <= 0)
            {
                $this->createItem(); //_id should now be set
            }

            try {
                $stmt = $this->_dbconn->prepare("UPDATE WarningAlert SET Title = :title, Content = :content, DateExpires = :dateexpires, MessageType = :messagetype, Display = :display, AuthorID = :authorid, AuthorName = :authorname WHERE ID = :id LIMIT 1");
                $result = $stmt->execute([
                    'title' => $this->_title,
                    'content' => $this->_content,
                    'dateexpires' => $this->_date_expires,
                    'messagetype' => $this->_message_type,
                    'authorid' => $this->_author_id,
                    'authorname' => $this->_author_name,
                    'display' => $this->_display,
                    'id' => $this->_id
                               ]);
                if ($result == true) { return true; } else { return $stmt->errorInfo(); }
            } catch (Exception $e) {
                error_log("Failed to save Alert record: " . $e);
            }
        }


        /**
         * Returns all Alert records and fields in Assoc array
         *
         * @return mixed
         */
        public function listAllItems($activeonly = true)
        {
            $basesql = "SELECT * FROM WarningAlert ";
            if ($activeonly === true) {
                $query = " WHERE Display = 'Y' AND DateExpires >= CURDATE() ";
            } else { $query = ""; }
            $order = " ORDER BY DateExpires ASC";

            $sql = $basesql.$query.$order;
            //echo $sql;

            try
            {
                $stmt = $this->_dbconn->query($sql);
                $alerts = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $e)
            {
                error_log("Failed to retrieve Alert items" . $e);
            }

            //Store details in relevant member
            $this->_allitems = $alerts;

            //return the array
            return $alerts;
        }

        /**
         * Delete the complete alert item
         *
         * @return mixed
         */
        public function deleteItem()
        {
            if (!is_numeric($this->_id))
            {
                throw new Exception('Class WarningAlert requires the item ID to be set if you are trying to delete the item');
            }

            //Now delete the item from the DB
            try {
                $stmt = $this->_dbconn->prepare("DELETE FROM WarningAlert WHERE ID = :id LIMIT 1");
                $result = $stmt->execute([
                                   'id' => $this->_id
                               ]);
            } catch (Exception $e) {
                error_log("Failed to delete Warning Alert record: " . $e);
            }

            if ($result == true)
            {
                //Unset the properties
                $this->_id = null;
                $this->_title = null;
                $this->_content = null;
                $this->_date_expires = null;
                $this->_message_type = null;
                $this->_author_id = null;
                $this->_author_name = null;
                $this->_display = null;

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
        public function getDateExpires()
        {
            return $this->_date_expires;
        }

        /**
         * @param mixed $date_expires
         */
        public function setDateExpires($date_expires)
        {
            if ($date_expires == '') { $date_expires = null; }
            $this->_date_expires = $date_expires;
        }

        /**
         * @return mixed
         */
        public function getAuthorID()
        {
            return $this->_author_id;
        }

        /**
         * @param mixed $author_id
         */
        public function setAuthorID($author_id)
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
        public function getMessageType()
        {
            return $this->_message_type;
        }

        /**
         * @param mixed $message_type
         */
        public function setMessageType($message_type)
        {
            $this->_message_type = $message_type;
        }

        /**
         * @return mixed
         */
        public function getDisplay()
        {
            return $this->_display;
        }

        /**
         * @param mixed $display
         */
        public function setDisplay($display)
        {
            $this->_display = $display;
        }



        /**
         * @return mixed
         */
        public function getAllItems()
        {
            return $this->_allitems;
        }

    }