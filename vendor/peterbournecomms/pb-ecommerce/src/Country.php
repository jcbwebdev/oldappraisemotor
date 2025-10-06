<?php

    namespace PeterBourneComms\Ecommerce;

    use PeterBourneComms\CMS\Database;
    use PDO;
    use PDOException;
    use Exception;

    class Country
    {
        /**
         * @var int|string
         */
        protected $_id;
        /**
         * @var
         */
        protected $_country;
        /**
         * @var
         */
        protected $_code;

        /**
         * @var
         */
        protected $_all_items;
        

        /**
         * Country constructor.
         *
         * @param null $id
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
                if (isset($id) && !is_numeric($id) && $id != 0)
                {
                    throw new Exception('Class Country requires id to be specified as an integer');
                }

                //Retrieve current information
                if (isset($id) && $id > 0)
                {
                    $this->_id = $id;
                    $this->getItemById($id);
                }
            }
        }


        /**
         * Retrieve fields by ID - populate member properties and return array of database record
         *
         * @param $id
         *
         * @return mixed
         */
        public function getItemById($id)
        {
            try
            {
                $stmt = $this->_dbconn->prepare("SELECT * FROM Countries WHERE ID =  :id LIMIT 1");
                $stmt->execute(['id' => $id]);
                $item = $stmt->fetch();
            } catch (Exception $e)
            {
                error_log("Failed to retrieve Country item details when searching by ID" . $e);

                return false;
            }

            $this->_id = $item['ID'];
            $this->_country = $item['Country'];
            $this->_code = $item['Code'];
            
            return $item;
        }
        

        /**
         * Returns array of complete database records for all items - as searched for
         *
         *
         * @return array
         */
        public function getAllItems()
        {
            $params = array();
            $basesql = "SELECT ID FROM Countries";

            $ordersql = " ORDER BY `Country` ASC";

            //Create sql
            $sql = $basesql . $ordersql;

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
                error_log("Failed to retrieve Country items" . $e);
            }

            //Store details in relevant member
            $this->_all_items = $items;

            //return the array
            return $items;
        }

        /**
         * Function to look up country based on code or vice versa
         */
        public function listAllItems($needle = '', $searchtype = '')
        {
            $basesql = "SELECT ID FROM Countries";

            $params = array();

            switch ($searchtype)
            {
                case 'id':
                    $search = " WHERE ID = :needle";
                    $order = " LIMIT 1";
                    $params['needle'] = $needle;
                    break;

                case 'country':
                    $search = " WHERE Country LIKE :needle";
                    $order = " ORDER BY Country ASC";
                    $params['needle'] = $needle . "%";
                    break;

                case 'country-exact':
                    $search = " WHERE Country = :needle";
                    $order = " ORDER BY Country ASC LIMIT 1";
                    $params['needle'] = $needle;
                    break;

                case 'code':
                    $search = " WHERE Code = :needle";
                    $order = " ORDER BY Country ASC";
                    $params['needle'] = $needle;
                    break;

                default:
                    $search = " WHERE Country LIKE :needle";
                    $order = " ORDER BY Country ASC";
                    $params['needle'] = $needle . "%";
                    break;
            }


            //Create sql
            $sql = $basesql . $search . $order;
            //echo $sql;

            $items = array();
            try
            {
                $stmt = $this->_dbconn->prepare($sql);
                $stmt->execute($params);
                while ($item = $stmt->fetch(PDO::FETCH_ASSOC))
                {
                    $thisrow = $this->getItemById($item['ID']);
                    $items[] = $thisrow;
                }
            } catch (Exception $e)
            {
                error_log("Failed to retrieve Countries" . $e);
            }

            //Store details in relevant member
            $this->_all_items = $items;

            //return the array
            return $items;
        }

        /**
         * Delete item
         *
         * @return bool
         */
        public function deleteItem()
        {
            if (!is_numeric($this->_id))
            {
                throw new Exception('Class Country requires the item ID to be set if you are trying to delete the item');
            }

            //Now delete the item from the DB
            try
            {
                //Now the actual summary total
                $stmt = $this->_dbconn->prepare("DELETE FROM Countries WHERE ID = :id LIMIT 1");
                $result = $stmt->execute([
                                             'id' => $this->_id
                                         ]);
            } catch (Exception $e)
            {
                error_log("Failed to delete Country record: " . $e);
            }

            if ($result === true)
            {
                //Unset the properties
                $this->_id = null;
                $this->_country = null;
                $this->_code = null;

                return true;
            }
            else
            {
                return false;
            }

        }


        /**
         * Save item - based on properties set
         *
         * @return bool
         */
        public function saveItem()
        {
            try
            {
                //First need to determine if this is a new item
                if ($this->_id <= 0)
                {
                    $this->createNewItem(); //_id should now be set
                    //echo "created item ".$this->_id;
                }

                $stmt = $this->_dbconn->prepare("UPDATE Countries SET `Country` = :country, Code = :code WHERE ID = :id LIMIT 1");
                $result = $stmt->execute([
                                             'country' => $this->_country,
                                             'code' => $this->_code,
                                             'id' => $this->_id
                                         ]);
                //echo $this->_id;
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
                error_log("Failed to save record: " . $e);
            }
        }

        /**
         * Create new item
         */
        public function createNewItem()
        {
            //Only execute if the _id property isn't already set
            if (is_numeric($this->_id) || $this->_id > 0)
            {
                throw new Exception('You cannot create a new item at this stage - the id property is already set as ' . $this->_id);
            }

            //Create DB item
            try
            {
                $result = $this->_dbconn->query("INSERT INTO Countries SET `Country` = ''");
                $lastID = $this->_dbconn->lastInsertId();

                if ($lastID > 0)
                {
                    $this->_id = $lastID;
                }
            } catch (Exception $e)
            {
                error_log("Failed to create new record: " . $e);
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
        public function getCountry()
        {
            return $this->_country;
        }

        /**
         * @param mixed $country
         */
        public function setCountry($country)
        {
            $this->_country = $country;
        }

        /**
         * @return mixed
         */
        public function getCode()
        {
            return $this->_code;
        }

        /**
         * @param mixed $code
         */
        public function setCode($code)
        {
            $this->_code = $code;
        }




    }