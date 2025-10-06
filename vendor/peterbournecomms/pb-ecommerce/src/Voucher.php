<?php
    
    namespace PeterBourneComms\Ecommerce;

    use PeterBourneComms\CMS\Database;
    use PDO;
    use PDOException;
    use Exception;
    
    /**
     * Deals with Voucher items
     *
     *
     *
     * @author Peter Bourne
     * @version 1.2
     *
     *          1.0     20/07/20    Original version
     *          1.1     11/11/21    Composer version
     *          1.2     17.02.23    Added ValueFlag and ValueAmount to Vouchers
     *
     *
     */
    class Voucher
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
        protected $_code;
        /**
         * @var
         */
        protected $_percent_off;
        /**
         * @var
         */
        protected $_voucher_expires;
        
        protected $_value_flag;
        protected $_value_amount;
        
        protected $_allitems;
        
        /**
         * Voucher constructor.
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
                    throw new Exception('Class Voucher requires id to be specified as an integer');
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
         * Retrieves specified record ID
         * Populates object member elements
         *
         * @param $id
         */
        public function getItemById($id)
        {
            try {
                $stmt = $this->_dbconn->prepare("SELECT ID, AES_DECRYPT(Title, :key) AS Title, AES_DECRYPT(Code, :key) AS Code, AES_DECRYPT(PercentOff, :key) AS PercentOff, AES_DECRYPT(VoucherExpires, :key) AS VoucherExpires, AES_DECRYPT(ValueFlag, :key) AS ValueFlag, AES_DECRYPT(ValueAmount, :key) AS ValueAmount FROM Vouchers WHERE ID =  :id LIMIT 1");
                $stmt->execute(['id' => $id, 'key'=>AES_ENCRYPTION_KEY]);
                $item = $stmt->fetch();
            } catch (Exception $e)
            {
                error_log("Failed to retrieve Voucher details when searching by ID" . $e);
            }
            
            //Store details in relevant members
            $this->_id = $item['ID'];
            $this->_title = $item['Title'];
            $this->_code = $item['Code'];
            $this->_percent_off = $item['PercentOff'];
            $this->_voucher_expires = $item['VoucherExpires'];
            $this->_value_flag = $item['ValueFlag'];
            $this->_value_amount = $item['ValueAmount'];
            
            return $item;
        }
        
        
        public function getItemByVoucherCode($code) {
            try {
                $stmt = $this->_dbconn->prepare("SELECT ID FROM Vouchers WHERE CONVERT(AES_DECRYPT(Code, :key) USING utf8) LIKE :code AND CONVERT(AES_DECRYPT(VoucherExpires, :key) USING utf8) >= CURDATE() LIMIT 1");
                $stmt->execute([
                    'code' => $code,
                    'key'=>AES_ENCRYPTION_KEY
                ]);
                $item = $stmt->fetch();
                return $this->getItemById($item['ID']);
            } catch (Exception $e)
            {
                error_log("Failed to retrieve Voucher details when searching by ID" . $e);
                return false;
            }
        }
        
        
        /**
         * Saves the current object to the Products table in the database
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
                $stmt = $this->_dbconn->prepare("UPDATE Vouchers SET Title = AES_ENCRYPT(:title, :key), Code = AES_ENCRYPT(:code, :key), PercentOff = AES_ENCRYPT(:percentoff, :key), VoucherExpires = AES_ENCRYPT(:voucherexpires, :key), ValueFlag = AES_ENCRYPT(:value_flag, :key), ValueAmount = AES_ENCRYPT(:value_amount, :key) WHERE ID = :id LIMIT 1");
                $result = $stmt->execute([
                    'key'=>AES_ENCRYPTION_KEY,
                    'title' => $this->_title,
                    'code' => $this->_code,
                    'percentoff' => $this->_percent_off,
                    'voucherexpires' => $this->_voucher_expires,
                    'value_flag' => $this->_value_flag,
                    'value_amount' => $this->_value_amount,
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
                error_log("Failed to save Voucher record: " . $e);
            }
        }
        
        /**
         * Create new empty Voucher item
         *
         * Sets the _id property accordingly
         */
        public function createNewItem()
        {
            //Only execute if the _id property isn't already set
            if (is_numeric($this->_id) || $this->_id > 0)
            {
                throw new Exception('You cannot create a new Voucher item at this stage - the id property is already set as ' . $this->_id);
            }
            
            //Create DB item
            try
            {
                $result = $this->_dbconn->query("INSERT INTO Vouchers SET Title = null");
                $lastID = $this->_dbconn->lastInsertId();
                
                if ($lastID > 0)
                {
                    $this->_id = $lastID;
                }
            } catch (Exception $e)
            {
                error_log("Failed to create new Voucher record: " . $e);
            }
        }
        
        /**
         * Returns all Products records and fields in Assoc array
         * Flags for retrieving just Panel items OR All future items OR All items OR Just past items
         *
         * @param string    Panel | Current | All | Past
         * @param int       Cut off in months for items
         *
         * @return mixed
         */
        public function listAllItems($passedNeedle = null, $passedMode = null)
        {
            $basesql = "SELECT Vouchers.ID FROM Vouchers ";
            $sort = " ORDER BY AES_DECRYPT(Vouchers.VoucherExpires, :key) DESC";
            $params = array('key' => AES_ENCRYPTION_KEY);
            
            //Build SQL depending on passedMode and passedNeedle
            switch($passedMode) {
                /*case 'id':
                    $query = " WHERE Products.ID = :needle ";
                    $params['needle'] = $passedNeedle;
                    break;

                case 'categoryid':
                    $query = " WHERE Products.CategoryID = :needle ";
                    $params['needle'] = $passedNeedle;
                    break;

                case 'category-title':
                    $basesql = "SELECT Products.ID FROM Products LEFT JOIN Categories ON Category.ID = Products.CategoryID ";
                    $query = " WHERE CONVERT(AES_DECRYPT(Categories.Title, :key) USING utf8) LIKE :needle";
                    $params['needle'] = $passedNeedle . "%";
                    break;

                case 'title':
                    $query = " WHERE CONVERT(AES_DECRYPT(Products.Title, :key) USING utf8) LIKE :needle";
                    $params['needle'] = $passedNeedle . "%";
                    break;
*/
                default:
                    $query = "";
                    break;
            }
            
            $sql = $basesql.$query.$sort;
            
            try
            {
                $stmt = $this->_dbconn->prepare($sql);
                $stmt->execute($params);
                $items = array();
                while ($item = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $items[] = $this->getItemById($item['ID']);
                }
            } catch (Exception $e)
            {
                error_log("Failed to retrieve Voucher items" . $e);
            }
            
            //Store details in relevant member
            $this->_allitems = $items;
            
            //return the array
            return $items;
        }
        
        
        
        /**
         * Delete the complete Products item - including any images
         *
         * @return mixed
         */
        public function deleteItem()
        {
            if (!is_numeric($this->_id))
            {
                throw new Exception('Class Voucher requires the item ID to be set if you are trying to delete the item');
            }
            
            //Now delete the item from the DB
            try
            {
                $stmt = $this->_dbconn->prepare("DELETE FROM Vouchers WHERE ID = :id LIMIT 1");
                $result = $stmt->execute([
                    'id' => $this->_id
                ]);
            } catch (Exception $e)
            {
                error_log("Failed to delete Vouchers record: " . $e);
            }
            
            if ($result === true)
            {
                //Unset the properties
                $this->_id = null;
                $this->_title = null;
                $this->_code = null;
                $this->_percent_off = null;
                $this->_voucher_expires = null;
                
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
         * @param mixed $title
         */
        public function setTitle($title)
        {
            $this->_title = $title;
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
        
        /**
         * @return mixed
         */
        public function getPercentOff()
        {
            return $this->_percent_off;
        }
        
        /**
         * @param mixed $percent_off
         */
        public function setPercentOff($percent_off)
        {
            $this->_percent_off = $percent_off;
        }
        
        /**
         * @return mixed
         */
        public function getVoucherExpires()
        {
            return $this->_voucher_expires;
        }
        
        /**
         * @param mixed $voucher_expires
         */
        public function setVoucherExpires($voucher_expires)
        {
            if ($voucher_expires === '' || $voucher_expires === '0000-00-00' || $voucher_expires === '0000-00-00 00:00:00') {
                $voucher_expires = null;
            }
            $this->_voucher_expires = $voucher_expires;
        }
        
        /**
         * @return mixed
         */
        public function getAllitems()
        {
            return $this->_allitems;
        }
        
        /**
         * @param mixed $allitems
         */
        public function setAllitems($allitems)
        {
            $this->_allitems = $allitems;
        }
    
        /**
         * @return mixed
         */
        public function getValueFlag()
        {
            return $this->_value_flag;
        }
    
        /**
         * @param mixed $value_flag
         */
        public function setValueFlag($value_flag): void
        {
            $this->_value_flag = $value_flag;
        }
    
        /**
         * @return mixed
         */
        public function getValueAmount()
        {
            return $this->_value_amount;
        }
    
        /**
         * @param mixed $value_amount
         */
        public function setValueAmount($value_amount): void
        {
            $this->_value_amount = $value_amount;
        }
        
        
        
    }