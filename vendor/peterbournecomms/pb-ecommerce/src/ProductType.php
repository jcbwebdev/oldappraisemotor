<?php

    namespace PeterBourneComms\Ecommerce;

    use PeterBourneComms\CMS\Database;
    use PDO;
    use PDOException;
    use Exception;


    /**
     * Deals with ProductTypes
     *
     *
     * @author Peter Bourne
     * @version 1.0
     *
     *          1.0     11/11/21    Original version
     *
     *
     */
    class ProductType
    {
        /**
         * @var mixed
         */
        protected $_dbconn;
        /**
         * @var int
         */
        protected $_id;
        /**
         * @var
         */
        protected $_title;
        /**
         * @var
         */
        protected $_url_text;
        protected $_display_order;
        
        /**
         * @var
         */
        protected $_allitems;


        /**
         * ProductType constructor.
         *
         * @param null   $id
         * @param int    $width
         * @param int    $height
         * @param string $path
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
                    throw new Exception('Class ProductType requires id to be specified as an integer');
                }

                //Retrieve current ProductTypes information
                if (isset($id))
                {
                    $this->_id = $id;
                    $this->getItemById($id);
                }
            }
        }


        /**
         * Retrieves specified ProductTypes record ID from Test table
         * Populates object member elements
         *
         * @param $id
         */
        public function getItemById($id)
        {
            try
            {
                $stmt = $this->_dbconn->prepare("SELECT ID, AES_DECRYPT(Title, :key) AS Title, AES_DECRYPT(URLText, :key) AS URLText, DisplayOrder FROM ProductTypes WHERE ID =  :id LIMIT 1");
                $stmt->execute([
                    'key' => AES_ENCRYPTION_KEY,
                    'id' => $id
                ]);
                $item = $stmt->fetch();
            } catch (Exception $e)
            {
                error_log("Failed to retrieve ProductTypes item details when searching by ID" . $e);
            }

            //Store details in relevant members
            $this->_id = $item['ID'];
            $this->_title = $item['Title'];
            $this->_url_text = $item['URLText'];
            $this->_display_order = $item['DisplayOrder'];
            
            return $item;
        }

        public function getItemByUrl($urltext)
        {
            try
            {
                $stmt = $this->_dbconn->prepare("SELECT ID FROM ProductTypes WHERE CONVERT(AES_DECRYPT(URLText, :key) USING utf8) LIKE :urltext LIMIT 1");
                $stmt->execute([
                    'key' => AES_ENCRYPTION_KEY,
                    'urltext' => $urltext
                ]);
                $item = $stmt->fetch();
            } catch (Exception $e)
            {
                error_log("Failed to retrieve ProductTypes item details when searching by URLText" . $e);
            }
            //Use other function to populate rest of properties (no sense writing it twice!)_
            return $this->getItemById($item['ID']);
        }
        

        /**
         * Saves the current object to the ProductTypes table in the database
         *
         * @throws Exception
         */
        public function saveItem()
        {
            //First need to determine if this is a new ProductTypes item
            if ($this->_id <= 0)
            {
                $this->createNewItem(); //_id should now be set
            }

            try
            {
                $stmt = $this->_dbconn->prepare("UPDATE ProductTypes SET Title = AES_ENCRYPT(:title, :key), URLText = AES_ENCRYPT(:url_text, :key), DisplayOrder = :display_order WHERE ID = :id LIMIT 1");
                $result = $stmt->execute([
                                            'key' => AES_ENCRYPTION_KEY,
                                             'title' => $this->_title,
                                             'url_text' => $this->_url_text,
                                             'display_order' => $this->_display_order,
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
                error_log("Failed to save ProductTypes record: " . $e);
            }
        }

        /**
         * Create new empty ProductTypes item
         *
         * Sets the _id property accordingly
         */
        public function createNewItem()
        {
            //Only execute if the _id property isn't already set
            if (is_numeric($this->_id) || $this->_id > 0)
            {
                throw new Exception('You cannot create a new ProductTypes item at this stage - the id property is already set as ' . $this->_id);
            }

            //Create DB item
            try
            {
                $result = $this->_dbconn->query("INSERT INTO ProductTypes SET Title = NULL");
                $lastID = $this->_dbconn->lastInsertId();

                if ($lastID > 0)
                {
                    $this->_id = $lastID;
                }
            } catch (Exception $e)
            {
                error_log("Failed to create new ProductTypes record: " . $e);
            }
        }

        /**
         * Returns all ProductTypes records and fields in Assoc array
         * Flags for retrieving just Panel items OR All future items OR All items OR Just past items
         *
         * @param string    Panel | Current | All | Past
         * @param int       Cut off in months for items
         *
         * @return mixed
         */
        public function listAllItems()
        {
            $sql = "SELECT ID FROM ProductTypes ORDER BY DisplayOrder ASC, AES_DECRYPT(Title, :key) ASC";

            try
            {
                $stmt = $this->_dbconn->prepare($sql);
                $stmt->execute([
                    'key' => AES_ENCRYPTION_KEY
                ]);
                $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $e)
            {
                error_log("Failed to retrieve ProductTypes items" . $e);
            }

            if (is_array($items)) {
                unset($this->_allitems);
                foreach($items as $item) {
                    //Store details in relevant member
                    $this->_allitems[] = $this->getItemById($item['ID']);
                }
            }

            //return the array
            return $this->_allitems;
        }

        /**
         * Delete the complete ProductTypes item - including any images
         *
         * @return mixed
         */
        public function deleteItem()
        {
            if (!is_numeric($this->_id))
            {
                throw new Exception('Class ProductType requires the ProductTypes item ID to be set if you are trying to delete the item');
            }
    
    
            try
            {
                //First delete all ProductByTypeEntries
                $stmt = $this->_dbconn->prepare("DELETE FROM ProductsByType WHERE TypeID = :id LIMIT 1");
                $stmt->execute([
                    'id' => $this->_id
                ]);
                
                //Now delete the item from the DB
                $stmt = $this->_dbconn->prepare("DELETE FROM ProductTypes WHERE ID = :id LIMIT 1");
                $result = $stmt->execute([
                     'id' => $this->_id
                ]);
            } catch (Exception $e)
            {
                error_log("Failed to delete ProductTypes record: " . $e);
            }

            if ($result === true)
            {
                //Unset the properties
                $this->_id = null;
                $this->_title = null;
                $this->_url_text = null;
                $this->_display_order = null;

                return true;
            }
            else
            {
                return false;
            }

        }


        /**
         * Function to check if a similar URL already exists in the ProductTypes table
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
                throw new Exception('ProductType needs the new URL specifying as a string');
            }


            if (clean_int($ID) > 0)
            {
                $sql = "SELECT ID FROM ProductTypes WHERE CONVERT(AES_DECRYPT(URLText, :key) USING utf8) LIKE :urltext AND ID != :id";
                $vars = array('urltext' => $ContentURL, 'id' => $ID, 'key' => AES_ENCRYPTION_KEY);
            }
            else
            {
                $sql = "SELECT ID FROM ProductTypes WHERE CONVERT(AES_DECRYPT(URLText, :key) USING utf8) = :urltext AND (ID IS NULL OR ID <= 0)";
                $vars = array('urltext' => $ContentURL, 'key' => AES_ENCRYPTION_KEY);
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

        public function getURLText()
        {
            return $this->_url_text;
        }

        public function setURLText($urltext)
        {
            $this->_url_text = $urltext;
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