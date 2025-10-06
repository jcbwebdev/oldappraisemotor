<?php
    
    namespace PeterBourneComms\CCA;
    
    use PDO;
    use Exception;
    use PeterBourneComms\CMS\Database;
    use PeterBourneComms\CCA\Auction;
    
    /**
     * @package PeterBourneComms\CCA
     * @author  Peter Bourne
     * @version 1.1
     *
     *  Deals with CCA Vehicles in Auctions
     *  Relies on VehicleAuctions in database
     *
     *  History
     *  23/04/2024   1.0     Initial version
     *  03/07/2024   1.1     Added AuctionStatus property and method AND StatusOptions
     *
     *
     */
    
    class AuctionVehicle
    {
        protected $_dbconn;
        /**
         * @var int|string
         */
        protected $_id;
        /**
         * @var
         */
        protected $_auction_id;
        protected $_vehicle_id;
        protected $_display_order;
        protected $_buy_it_now;
        protected $_buy_it_now_price;
        protected $_buy_it_now_paid;
        protected $_old_buy_it_now;
        protected $_old_buy_it_now_price;
        protected $_current_bid_amount;
        protected $_current_bid_id;
        protected $_current_bid_customer_id;
        protected $_customer_notified;
        protected $_final_bid_amount;
        protected $_final_bid_customer_id;
        protected $_final_bid_customer_details;
        protected $_auction_status;
        
        private $_status_options = array(array('Label' => 'Waiting', 'Value' => 'Waiting'),array('Label' => 'Active', 'Value' => 'Active'), array('Label' => 'Sold', 'Value' => 'Sold'), array('Label' => 'Not sold', 'Value' => 'Not sold'));
        
        
        public function __construct($id = null)
        {
            // Make connection to database
            if (!$this->_dbconn) {
                try {
                    $conn = new Database();
                    $this->_dbconn = $conn->getConnection();
                } catch (Exception $e) {
                    //handle the exception
                    die;
                }
            }
            if (isset($id) && !is_numeric($id)) {
                throw new Exception('CCA\AuctionVehicle->__construct() requires id to be specified as an integer - if it is specified at all');
            }

            //Retrieve current customer information
            if (isset($id)) {
                $this->_id = $id;
                $this->getItemById($id);
            }
        }


        /**
         * @param int $id
         *
         * @return array
         */
        public function getItemById($id = 0){
            if ($id == 0) {
                $id = $this->_id;
            }
            if (!is_numeric($id) || $id <= 0) {
                error_log('CCA\AuctionVehicle->getItemById() Unable to retrieve details as no ID set');
                return false;
            }

            //Now retrieve the extra fields
            try {
                $stmt = $this->_dbconn->prepare("SELECT ID, AuctionID, VehicleID, DisplayOrder, AES_DECRYPT(BuyItNow, :key) AS BuyItNow, AES_DECRYPT(BuyItNowPrice, :key) AS BuyItNowPrice, AES_DECRYPT(BuyItNowPaid, :key) AS BuyItNowPaid, AES_DECRYPT(OldBuyItNow, :key) AS OldBuyItNow, AES_DECRYPT(OldBuyItNowPrice, :key) AS OldBuyItNowPrice, AES_DECRYPT(CurrentBidAmount, :key) AS CurrentBidAmount, CurrentBidID, CurrentBidCustomerID, AES_DECRYPT(CustomerNotified, :key) AS CustomerNotified, AES_DECRYPT(FinalBidAmount, :key) AS FinalBidAmount, FinalBidCustomerID, AES_DECRYPT(FinalBidCustomerDetails, :key) AS FinalBidCustomerDetails, AES_DECRYPT(AuctionStatus, :key) AS AuctionStatus FROM AuctionVehicles WHERE ID =  :id LIMIT 1");
                $stmt->execute([
                                   'key' => AES_ENCRYPTION_KEY,
                                   'id' => $id
                               ]);
                $item = $stmt->fetch(PDO::FETCH_ASSOC);

            } catch (Exception $e)
            {
                error_log("CCA\AuctionVehicle->getItemById() Failed to retrieve details" . $e);
            }

            //Store details in relevant customers
            $this->_id = $item['ID'];
            $this->_auction_id = $item['AuctionID'];
            $this->_vehicle_id = $item['VehicleID'];
            $this->_display_order = $item['DisplayOrder'];
            $this->_buy_it_now = $item['BuyItNow'];
            $this->_buy_it_now_price = $item['BuyItNowPrice'];
            $this->_buy_it_now_paid = $item['BuyItNowPaid'];
            $this->_old_buy_it_now = $item['OldBuyItNow'];
            $this->_old_buy_it_now_price = $item['OldBuyItNowPrice'];
            $this->_current_bid_amount = $item['CurrentBidAmount'];
            $this->_current_bid_id = $item['CurrentBidID'];
            $this->_current_bid_customer_id = $item['CurrentBidCustomerID'];
            $this->_customer_notified = $item['CustomerNotified'];
            $this->_final_bid_amount = $item['FinalBidAmount'];
            $this->_final_bid_customer_id = $item['FinalBidCustomerID'];
            $this->_final_bid_customer_details = $item['FinalBidCustomerDetails'];
            $this->_auction_status = $item['AuctionStatus'];
            
            //DO NOT RETURN VEHICLE DETAILS HERE - AS The vehicle class will return list of these records.
            
            //Return Auction details (which will include Auction Room details
            $AO = new Auction();
            if (is_object($AO)) {
                $Auction = $AO->getItemById($item['AuctionID']);
                if (is_array($Auction) && count($Auction) > 0 ) {
                    $item['Auction'] = $Auction;
                }
            }
            
            return $item;
        }


        /**
         * Function to return array of records (as assoc array)
         *
         * @param string $passedNeedle
         * @param string $passedMode
         *
         * @return array
         */
        public function listAllItems($passedNeedle = '', $passedMode = '', $statusfilter = null)
        {
            $basesql = "SELECT AuctionVehicles.ID FROM AuctionVehicles WHERE ";
            $order = " ORDER BY AuctionVehicles.ID ASC";
            $params = array();

            //Build SQL depending on passedMode and passedNeedle
            switch($passedMode) {
                    
                case 'vehicle-id':
                    $query = "AuctionVehicles.VehicleID = :needle ";
                    $params['needle'] = $passedNeedle;
                    break;
                
                case 'auction-id':
                    $query = "AuctionVehicles.AuctionID = :needle ";
                    $order = " ORDER BY AuctionVehicles.DisplayOrder ASC";
                    $params['needle'] = $passedNeedle;
                    break;
                
                default:
                    $query = "ID = ID ";
                    break;
            }
            
            
            //OPTIONS
            if (isset($statusfilter) && is_string($statusfilter) && $statusfilter != '') {
                switch($statusfilter) {
                    case 'remaining':
                        $query .= " AND (AuctionStatus IS NULL OR CONVERT(AES_DECRYPT(AuctionStatus, :key) USING utf8) = '' OR CONVERT(AES_DECRYPT(AuctionStatus, :key) USING utf8) = 'Active') ";
                        $params['key'] = AES_ENCRYPTION_KEY;
                        break;
                        
                    default:
                        break;
                }
            }
            
            //Carry out the query
            //echo $basesql.$query.$order;
            $stmt = $this->_dbconn->prepare($basesql.$query.$order);
            $stmt->execute($params);

            //Prepare results array
            $results = array();

            //Work through results from query
            while($this_res = $stmt->fetch())
            {
                //Now retrieve the full customer record
                $mem = $this->getItemById($this_res['ID']);
                $results[] = $mem;
            }

            return $results;
        }


        /**
         * @param int $id
         *
         * @return bool
         */
        public function deleteItem($id = 0)
        {
            if ($id == 0) {
                $id = $this->_id;
            }
            
            if (!is_numeric($id) || $id <= 0) {
                error_log('CCA\AuctionVehicle->deleteItem() Unable to delete as no id set');

                return false;
            }
            
            //Look up the vehicle and reset its status to Waiting
            $VO = new Vehicle();
            $TempAVO = new AuctionVehicle();
            $TempAuctionItem = $TempAVO->getItemById($id);
            if (is_array($TempAuctionItem) && count($TempAuctionItem) > 0) {
                if (is_object($VO)) {
                    $Vehicle = $VO->getItemById($TempAuctionItem['VehicleID']);
                    if (is_array($Vehicle) && count($Vehicle) > 0) {
                        //Update Status of vehicle to waiting
                        $VO->setVehicleStatus('Waiting');
                        $VO->saveItem();
                    } else {
                        error_log('CCA\AuctionVehicle->deleteItem() tried to update Vehicle - but failed');
                    }
                } else {
                    error_log('CCA\AuctionVehicle->deleteItem() tried to create Vehicle object - but failed');
                }
            } else {
                error_log('CCA\AuctionVehicle->deleteItem() tried to create temporary Auction object - but failed - Aborting deletion');
                return false;
            }
            
            //Now the actual record
            try {
                $stmt = $this->_dbconn->prepare("DELETE FROM AuctionVehicles WHERE ID = :id LIMIT 1");
                $result = $stmt->execute([
                                             'id' => $id
                                         ]);
                if ($result === true) {
                    return true;
                }

            } catch (Exception $e) {
                error_log("CCA\AuctionVehicle->deleteItem() Failed to delete record" . $e);
            }

            return false;
        }


        /**
         * Create new record stub
         */
        public function createNewItem()
        {
            try {
                $stmt = $this->_dbconn->prepare("INSERT INTO AuctionVehicles SET DisplayOrder = null");
                $stmt->execute();
                $id = $this->_dbconn->lastInsertId();
                
                //Store in the object
                $this->_id = $id;
                
            } catch (Exception $e) {
                error_log("CCA\AuctionVehicle->createNewItem() Failed to create new stub".$e);
            }
        }



        /**
         * Save item
         *
         * @return bool
         */
        public function saveItem()
        {
            //First need to determine if this is a new customer item
            if ($this->_id <= 0) {
                $this->createNewItem(); //_id should now be set
            }

            try {
                $stmt = $this->_dbconn->prepare("UPDATE AuctionVehicles SET AuctionID = :auction_id, VehicleID = :vehicle_id, DisplayOrder = :display_order, BuyItNow = AES_ENCRYPT(:buy_it_now, :key), BuyItNowPrice = AES_ENCRYPT(:buy_it_now_price, :key), BuyItNowPaid = AES_ENCRYPT(:buy_it_now_paid, :key), OldBuyItNow = AES_ENCRYPT(:old_buy_it_now, :key), OldBuyItNowPrice = AES_ENCRYPT(:old_buy_it_now_price, :key), CurrentBidAmount = AES_ENCRYPT(:current_bid_amount, :key), CurrentBidID = :current_bid_id, CurrentBidCustomerID = :current_bid_customer_id, CustomerNotified = AES_ENCRYPT(:customer_notified, :key), FinalBidAmount = AES_ENCRYPT(:final_bid_amount, :key), FinalBidCustomerID = :final_bid_customer_id, FinalBidCustomerDetails = AES_ENCRYPT(:final_bid_customer_details, :key), AuctionStatus = AES_ENCRYPT(:auction_status, :key) WHERE ID = :id LIMIT 1");
                $result = $stmt->execute([
                    'key' => AES_ENCRYPTION_KEY,
                    'auction_id' => $this->_auction_id,
                    'vehicle_id' => $this->_vehicle_id,
                    'display_order' => $this->_display_order,
                    'buy_it_now' => $this->_buy_it_now,
                    'buy_it_now_price' => $this->_buy_it_now_price,
                    'buy_it_now_paid' => $this->_buy_it_now_paid,
                    'old_buy_it_now' => $this->_old_buy_it_now,
                    'old_buy_it_now_price' => $this->_old_buy_it_now_price,
                    'current_bid_amount' => $this->_current_bid_amount,
                    'current_bid_id' => $this->_current_bid_id,
                    'current_bid_customer_id' => $this->_current_bid_customer_id,
                    'customer_notified' => $this->_customer_notified,
                    'final_bid_amount' => $this->_final_bid_amount,
                    'final_bid_customer_id' => $this->_final_bid_customer_id,
                    'final_bid_customer_details' => $this->_final_bid_customer_details,
                    'auction_status' => $this->_auction_status,
                    'id' => $this->_id,
                ]);
                if ($result == true) {
                    return true;
                }
                else {
                    error_log($stmt->errorInfo()[2]);
                    return false;
                }
            } catch (Exception $e) {
                error_log("CCA\AuctionVehicle->saveItem() Failed to save record: " . $e);
            }

            return false;
        }

        
        
        /**
         * Update the specified field
         *
         * Need to check in the schema if the field exists. If not, fail.
         *
         */
        public function updateField($field, $value, $recid) {
            if (!is_numeric($recid)) {
                error_log('CCA\AuctionVehicle->updateField() - requires record id to be passed');
                return false;
            }
            if (!is_string($field) || $field == '' ) {
                error_log('CCA\AuctionVehicle->updateField() - requires field to be passed');
                return false;
            }
            
            //Check its not one of our special columns
            if ($field == 'ID') {
                error_log('CCA\AuctionVehicle->updateField() - Attempted to update prohibited field');
                return false;
            }
            
            
            //Check if the field and record exists
            $stmt = $this->_dbconn->prepare("SELECT ID FROM AuctionVehicles WHERE ID = :id LIMIT 1");
            $stmt->execute([
                'id' => $recid
            ]);
            $item = $stmt->fetch();
            
            if ($item['ID'] <= 0 ) {
                error_log('CCA\AuctionVehicle->updateField() - Record does not exist');
                return false;
            }
            
            $stmt = $this->_dbconn->prepare("SHOW COLUMNS FROM AuctionVehicles LIKE :field");
            $stmt->execute([
                'field' => $field
            ]);
            $items = $stmt->fetchAll();
            
            if (!is_array($items) || count($items) <= 0) {
                error_log('CCA\AuctionVehicle->updateField() - Field does not exist');
                return false;
            }
            
            
            //Now carry out the update
            //Check if the field and record exists
            if ($field == 'VehicleID' || $field == 'AuctionID' || $field == 'DisplayOrder' || $field == 'CurrentBidID' || $field == 'CurrentBidCustomerID' || $field == 'FinalCustomerBidID') {
                //No encryption
                $sql = "UPDATE AuctionVehicles SET ".$field." = :value WHERE ID = :id LIMIT 1";
                $params = array('id' => $recid, 'value' => $value);
            } else {
                $sql = "UPDATE AuctionVehicles SET ".$field." = AES_ENCRYPT(:value, :key) WHERE ID = :id LIMIT 1";
                $params = array('id' => $recid, 'key' => AES_ENCRYPTION_KEY, 'value' => $value);
            }
            $stmt = $this->_dbconn->prepare($sql);
            $result = $stmt->execute($params);
            
            if ($result == true) { return true; } else {
                error_log($stmt->errorInfo()[2]);
                return false;
            }
        }
        
        

        ###########################################################
        # Getters and Setters
        ###########################################################



        /**
         * @return null
         */
        public function getId()
        {
            return $this->_id;
        }

        /**
         * @param null $id
         */
        public function setId($id)
        {
            if ($id <= 0 || !is_numeric($id)) { $id = null; }
            $this->_id = $id;

            return $this;
        }
        
        /**
         * @return mixed
         */
        public function getVehicleId()
        {
            return $this->_vehicle_id;
        }
        
        /**
         * @param mixed $vehicle_id
         */
        public function setVehicleId($vehicle_id): void
        {
            if (!is_numeric($vehicle_id) || $vehicle_id < 0) { $vehicle_id = null; }
            $this->_vehicle_id = $vehicle_id;
        }
        
        /**
         * @return mixed
         */
        public function getAuctionId()
        {
            return $this->_auction_id;
        }
        
        /**
         * @param mixed $auction_id
         */
        public function setAuctionId($auction_id): void
        {
            $this->_auction_id = $auction_id;
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
        public function setDisplayOrder($display_order): void
        {
            if (!is_numeric($display_order) || $display_order < 0) { $display_order = null; }
            $this->_display_order = $display_order;
        }
        
        /**
         * @return mixed
         */
        public function getBuyItNow()
        {
            return $this->_buy_it_now;
        }
        
        /**
         * @param mixed $buy_it_now
         */
        public function setBuyItNow($buy_it_now): void
        {
            $this->_buy_it_now = $buy_it_now;
        }
        
        /**
         * @return mixed
         */
        public function getBuyItNowPrice()
        {
            return $this->_buy_it_now_price;
        }
        
        /**
         * @param mixed $buy_it_now_price
         */
        public function setBuyItNowPrice($buy_it_now_price): void
        {
            $this->_buy_it_now_price = $buy_it_now_price;
        }
        
        /**
         * @return mixed
         */
        public function getBuyItNowPaid()
        {
            return $this->_buy_it_now_paid;
        }
        
        /**
         * @param mixed $buy_it_now_paid
         */
        public function setBuyItNowPaid($buy_it_now_paid): void
        {
            $this->_buy_it_now_paid = $buy_it_now_paid;
        }
        
        /**
         * @return mixed
         */
        public function getOldBuyItNow()
        {
            return $this->_old_buy_it_now;
        }
        
        /**
         * @param mixed $old_buy_it_now
         */
        public function setOldBuyItNow($old_buy_it_now): void
        {
            $this->_old_buy_it_now = $old_buy_it_now;
        }
        
        /**
         * @return mixed
         */
        public function getOldBuyItNowPrice()
        {
            return $this->_old_buy_it_now_price;
        }
        
        /**
         * @param mixed $old_buy_it_now_price
         */
        public function setOldBuyItNowPrice($old_buy_it_now_price): void
        {
            $this->_old_buy_it_now_price = $old_buy_it_now_price;
        }
        
        /**
         * @return mixed
         */
        public function getCurrentBidAmount()
        {
            return $this->_current_bid_amount;
        }
        
        /**
         * @param mixed $current_bid_amount
         */
        public function setCurrentBidAmount($current_bid_amount): void
        {
            $this->_current_bid_amount = $current_bid_amount;
        }
        
        /**
         * @return mixed
         */
        public function getCurrentBidId()
        {
            return $this->_current_bid_id;
        }
        
        /**
         * @param mixed $current_bid_id
         */
        public function setCurrentBidId($current_bid_id): void
        {
            if (!is_numeric($current_bid_id) || $current_bid_id < 0) { $current_bid_id = null; }
            $this->_current_bid_id = $current_bid_id;
        }
        
        /**
         * @return mixed
         */
        public function getCurrentBidCustomerId()
        {
            return $this->_current_bid_customer_id;
        }
        
        /**
         * @param mixed $current_bid_customer_id
         */
        public function setCurrentBidCustomerId($current_bid_customer_id): void
        {
            if (!is_numeric($current_bid_customer_id) || $current_bid_customer_id < 0) { $current_bid_customer_id = null; }
            $this->_current_bid_customer_id = $current_bid_customer_id;
        }
        
        /**
         * @return mixed
         */
        public function getCustomerNotified()
        {
            return $this->_customer_notified;
        }
        
        /**
         * @param mixed $customer_notified
         */
        public function setCustomerNotified($customer_notified): void
        {
            $this->_customer_notified = $customer_notified;
        }
        
        /**
         * @return mixed
         */
        public function getFinalBidAmount()
        {
            return $this->_final_bid_amount;
        }
        
        /**
         * @param mixed $final_bid_amount
         */
        public function setFinalBidAmount($final_bid_amount): void
        {
            $this->_final_bid_amount = $final_bid_amount;
        }
        
        /**
         * @return mixed
         */
        public function getFinalBidCustomerId()
        {
            return $this->_final_bid_customer_id;
        }
        
        /**
         * @param mixed $final_bid_customer_id
         */
        public function setFinalBidCustomerId($final_bid_customer_id): void
        {
            if (!is_numeric($final_bid_customer_id) || $final_bid_customer_id < 0) { $final_bid_customer_id = null; }
            $this->_final_bid_customer_id = $final_bid_customer_id;
        }
        
        /**
         * @return mixed
         */
        public function getFinalBidCustomerDetails()
        {
            return $this->_final_bid_customer_details;
        }
        
        /**
         * @param mixed $final_bid_customer_details
         */
        public function setFinalBidCustomerDetails($final_bid_customer_details): void
        {
            $this->_final_bid_customer_details = $final_bid_customer_details;
        }
        
        /**
         * @return mixed
         */
        public function getAuctionStatus()
        {
            return $this->_auction_status;
        }
        
        /**
         * @param mixed $auction_status
         */
        public function setAuctionStatus($auction_status): void
        {
            $this->_auction_status = $auction_status;
        }
        

    }