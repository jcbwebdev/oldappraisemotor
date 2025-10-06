<?php

    namespace PeterBourneComms\Ecommerce;

    use PeterBourneComms\CMS\Database;
    use PDO;
    use PDOException;
    use Exception;

    /**
     * Deals with Ecomerce settings items
     *
     *
     *
     * @author Peter Bourne
     * @version 1.0
     *
     *          1.0     23/06/2021    Original version
     *
     *
     */
    class Settings
    {
        /**
         * @var mixed
         */
        protected $_dbconn;
        protected $_id;
        protected $_delivery_uk_free_basket_value;
        protected $_delivery_uk_amount;
        protected $_delivery_row_amount;
        

        /**
         * Constructor.
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
                    throw new Exception('Class Settings requires id to be specified as an integer');
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
         * Retrieves specified Products record ID
         * Populates object member elements
         *
         * @param $id
         */
        public function getItemById($id)
        {
            try
            {
                $stmt = $this->_dbconn->prepare("SELECT ID, AES_DECRYPT(Delivery_UK_Free_Basket_Value, :key) AS Delivery_UK_Free_Basket_Value, AES_DECRYPT(Delivery_UK_Amount, :key) AS Delivery_UK_Amount, AES_DECRYPT(Delivery_RoW_Amount, :key) AS Delivery_RoW_Amount FROM EcommerceSettings WHERE ID =  :id LIMIT 1");
                $stmt->execute(['id' => $id, 'key'=>AES_ENCRYPTION_KEY]);
                $item = $stmt->fetch();
            } catch (Exception $e)
            {
                error_log("Failed to retrieve Settings item details when searching by ID" . $e);
            }

            //Store details in relevant members
            $this->_id = $item['ID'];
            $this->_delivery_uk_free_basket_value = $item['Delivery_UK_Free_Basket_Value'];
            $this->_delivery_uk_amount = $item['Delivery_UK_Amount'];
            $this->_delivery_row_amount = $item['Delivery_RoW_Amount'];

            return $item;
        }

        

        /**
         * Saves the current object to the table in the database
         *
         * @throws Exception
         */
        public function saveItem()
        {
            //First need to determine if this is a new Products item
            if ($this->_id <= 0)
            {
                $this->createNewItem(); //_id should now be set
            }

            try
            {
                $stmt = $this->_dbconn->prepare("UPDATE EcommerceSettings SET Delivery_UK_Free_Basket_Value = AES_ENCRYPT(:delivery_uk_free_basket_value, :key), Delivery_UK_Amount = AES_ENCRYPT(:delivery_uk_amount, :key), Delivery_RoW_Amount = AES_ENCRYPT(:delivery_row_amount, :key) WHERE ID = :id LIMIT 1");
                $result = $stmt->execute([
                                             'key'=>AES_ENCRYPTION_KEY,
                                             'delivery_uk_free_basket_value' => $this->_delivery_uk_free_basket_value,
                                             'delivery_uk_amount' => $this->_delivery_uk_amount,
                                             'delivery_row_amount' => $this->_delivery_row_amount,
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
                error_log("Failed to save Settings record: " . $e);
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
                throw new Exception('You cannot create a new item at this stage - the id property is already set as ' . $this->_id);
            }

            //Create DB item
            try
            {
                $result = $this->_dbconn->query("INSERT INTO EcommerceSettings SET Delivery_UK_Amount = null");
                $lastID = $this->_dbconn->lastInsertId();

                if ($lastID > 0)
                {
                    $this->_id = $lastID;
                }
            } catch (Exception $e)
            {
                error_log("Failed to create new Settings record: " . $e);
            }
        }

//        /**
//         * Returns all records and fields in Assoc array
//         * Flags for retrieving just Panel items OR All future items OR All items OR Just past items
//         *
//         * @param string    Panel | Current | All | Past
//         * @param int       Cut off in months for items
//         *
//         * @return mixed
//         */
//        public function listAllItems($passedNeedle = null, $passedMode = null)
//        {
//            $basesql = "SELECT Products.ID FROM Products LEFT JOIN ProductCategories ON ProductCategories.ID = Products.CategoryID ";
//            $sort = " ORDER BY AES_DECRYPT(ProductCategories.Title, :key) ASC, Products.DisplayOrder ASC, AES_DECRYPT(Products.Title, :key) ASC";
//            $params = array('key' => AES_ENCRYPTION_KEY);
//
//            //Build SQL depending on passedMode and passedNeedle
//            switch($passedMode) {
//                case 'id':
//                    $query = " WHERE Products.ID = :needle ";
//                    $params['needle'] = $passedNeedle;
//                    break;
//
//                case 'category-id':
//                    $query = " WHERE Products.CategoryID = :needle ";
//                    $params['needle'] = $passedNeedle;
//                    break;
//
//                case 'category-title':
//                    $basesql = "SELECT Products.ID FROM Products LEFT JOIN ProductCategories ON Category.ID = Products.CategoryID ";
//                    $query = " WHERE CONVERT(AES_DECRYPT(Categories.Title, :key) USING utf8) LIKE :needle";
//                    $params['needle'] = $passedNeedle . "%";
//                    break;
//
//                case 'title':
//                    $query = " WHERE CONVERT(AES_DECRYPT(Products.Title, :key) USING utf8) LIKE :needle";
//                    $params['needle'] = "%" . $passedNeedle . "%";
//                    break;
//
//                case 'latest':
//                    $query = " WHERE CONVERT(AES_DECRYPT(Products.NewProduct, :key) USING utf8) = 'Y'";
//                    break;
//
//                default:
//                    $query = "";
//                    break;
//            }
//
//            $sql = $basesql.$query.$sort;
//
//            try
//            {
//                $stmt = $this->_dbconn->prepare($sql);
//                $stmt->execute($params);
//                $items = array();
//                while ($item = $stmt->fetch(PDO::FETCH_ASSOC)) {
//                    $items[] = $this->getItemById($item['ID']);
//                }
//            } catch (Exception $e)
//            {
//                error_log("Failed to retrieve Products items" . $e);
//            }
//
//            //Store details in relevant member
//            $this->_allitems = $items;
//
//            //return the array
//            return $items;
//        }



        /**
         * Delete the complete item - including any images
         *
         * @return mixed
         */
        public function deleteItem()
        {
            if (!is_numeric($this->_id))
            {
                throw new Exception('Class Settings requires the item ID to be set if you are trying to delete the item');
            }

            //Now delete the item from the DB
            try
            {
                $stmt = $this->_dbconn->prepare("DELETE FROM EcommerceSettings WHERE ID = :id LIMIT 1");
                $result = $stmt->execute([
                                             'id' => $this->_id
                                         ]);
            } catch (Exception $e)
            {
                error_log("Failed to delete record: " . $e);
            }

            if ($result === true)
            {
                //Unset the properties
                $this->_id = null;

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
        public function getDeliveryUkFreeBasketValue()
        {
            return $this->_delivery_uk_free_basket_value;
        }

        /**
         * @param mixed $delivery_uk_free_basket_value
         */
        public function setDeliveryUkFreeBasketValue($delivery_uk_free_basket_value): void
        {
            $this->_delivery_uk_free_basket_value = $delivery_uk_free_basket_value;
        }

        /**
         * @return mixed
         */
        public function getDeliveryUkAmount()
        {
            return $this->_delivery_uk_amount;
        }

        /**
         * @param mixed $delivery_uk_amount
         */
        public function setDeliveryUkAmount($delivery_uk_amount): void
        {
            $this->_delivery_uk_amount = $delivery_uk_amount;
        }

        /**
         * @return mixed
         */
        public function getDeliveryRowAmount()
        {
            return $this->_delivery_row_amount;
        }

        /**
         * @param mixed $delivery_row_amount
         */
        public function setDeliveryRowAmount($delivery_row_amount): void
        {
            $this->_delivery_row_amount = $delivery_row_amount;
        }





        

    }