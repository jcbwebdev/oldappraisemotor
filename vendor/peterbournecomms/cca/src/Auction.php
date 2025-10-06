<?php
    
    namespace PeterBourneComms\CCA;
    
    use PDO;
    use Exception;
    use PeterBourneComms\CMS\Database;
    use PeterBourneComms\CCA\AuctionRoom;
    use PeterBourneComms\CCA\AuctionVehicle;
    
    /**
     * @package PeterBourneComms\CCA
     * @author  Peter Bourne
     * @version 1.2
     *
     *  Deals with CCA Vehicles in Auctions
     *  Relies on Auctions and Auctions in database
     *
     *  History
     *  23/04/2024   1.0     Initial version
     *  19/06/2024   1.1     Added delete AuctionVehicles functionality
     *  21/06/2024   1.2     Added AuctionEnd and NumberOfLots property
     *
     */
    
    class Auction
    {
        protected $_dbconn;
        /**
         * @var int|string
         */
        protected $_id;
        /**
         * @var
         */
        protected $_auction_room_id;
        protected $_auction_start;
        protected $_auction_start_date;
        protected $_auction_start_time;
        protected $_seller_percent;
        protected $_seller_upto_max;
        protected $_seller_fixed;
        protected $_buyer_percent;
        protected $_buyer_upto_max;
        protected $_buyer_fixed;
        protected $_bid_extension_time; //seconds
        protected $_lot_minimum_length; //seconds
        protected $_lot_bid_increment; //pounds and pence
        protected $_auction_end;
        
        private $_number_of_lots;
        private $_remaining_lots;
        private $_auction_status;
        
        
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
                throw new Exception('CCA\Auction->__construct() requires id to be specified as an integer - if it is specified at all');
            }

            //Set some defaults
            $this->_seller_percent = 0.15; //percent
            $this->_seller_upto_max = 100; //pounds
            $this->_buyer_percent = 0.15; //percent
            $this->_buyer_upto_max = 100; //pounds
            $this->_bid_extension_time = 60; //seconds
            $this->_lot_minimum_length = 120; //seconds
            $this->_lot_bid_increment = 100; //pounds;
            
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
                error_log('CCA\Auction->getItemById() Unable to retrieve details as no ID set');
                return false;
            }

            //Now retrieve the extra fields
            try {
                $stmt = $this->_dbconn->prepare("SELECT ID, AuctionRoomID, AES_DECRYPT(AuctionStart, :key) AS AuctionStart, AES_DECRYPT(Seller_Percent, :key) AS Seller_Percent, AES_DECRYPT(Seller_UptoMax, :key) AS Seller_UptoMax, AES_DECRYPT(Seller_Fixed, :key) AS Seller_Fixed, AES_DECRYPT(Buyer_Percent, :key) AS Buyer_Percent, AES_DECRYPT(Buyer_UptoMax, :key) AS Buyer_UptoMax, AES_DECRYPT(Buyer_Fixed, :key) AS Buyer_Fixed, AES_DECRYPT(BidExtensionTime, :key) AS BidExtensionTime, AES_DECRYPT(LotMinimumLength, :key) AS LotMinimumLength, AES_DECRYPT(LotBidIncrement, :key) AS LotBidIncrement, AES_DECRYPT(AuctionEnd, :key) AS AuctionEnd FROM Auctions WHERE ID =  :id LIMIT 1");
                $stmt->execute([
                                   'key' => AES_ENCRYPTION_KEY,
                                   'id' => $id
                               ]);
                $item = $stmt->fetch(PDO::FETCH_ASSOC);

            } catch (Exception $e)
            {
                error_log("CCA\Auction->getItemById() Failed to retrieve details" . $e);
            }

            //Store details in relevant customers
            $this->_id = $item['ID'];
            $this->_auction_room_id = $item['AuctionRoomID'];
            $this->_auction_start = $item['AuctionStart'];
            $this->_seller_percent = $item['Seller_Percent'];
            $this->_seller_upto_max = $item['Seller_UptoMax'];
            $this->_seller_fixed = $item['Seller_Fixed'];
            $this->_buyer_percent = $item['Buyer_Percent'];
            $this->_buyer_upto_max = $item['Buyer_UptoMax'];
            $this->_buyer_fixed = $item['Buyer_Fixed'];
            $this->_bid_extension_time = $item['BidExtensionTime'];
            $this->_lot_minimum_length = $item['LotMinimumLength'];
            $this->_lot_bid_increment = $item['LotBidIncrement'];
            $this->_auction_end = $item['AuctionEnd'];
            
            //Split the AuctionStart into Date and Time
            $date_arr = explode(" ", $item['AuctionStart']);
            $item['AuctionStartDate'] = $date_arr[0];
            $item['AuctionStartTime'] = $date_arr[1];
            $this->_auction_start_date = $date_arr[0];
            $this->_auction_start_time = $date_arr[1];
            
            //Return AuctionRoom info
            $ARO = new AuctionRoom();
            if (is_object($ARO)) {
                $AuctionRoom = $ARO->getItemById($item['AuctionRoomID']);
                if (is_array($AuctionRoom) && count($AuctionRoom) > 0) {
                    $item['RoomInfo'] = $AuctionRoom;
                }
            }
            
            //Count the number of vehicles in this auction
            $Lots = $this->countLots($this->_id);
            if (is_numeric($Lots)) {
                $this->_number_of_lots = $Lots;
                $item['NumberOfLots'] = $Lots;
            }
            
            $Remaining = $this->countRemaining($this->_id);
            if (is_numeric($Remaining)) {
                $this->_remaining_lots = $Remaining;
                $item['RemainingLots'] = $Remaining;
            }
            
            $AuctionStatus = $this->determineAuctionStatus($this->_id);
            if (is_array($AuctionStatus)) {
                $this->_auction_status = $AuctionStatus;
                $item['AuctionStatus'] = $AuctionStatus;
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
        public function listAllItems($passedNeedle = '', $passedMode = '', $futureonlyflag = false, $sortorder = 'asc')
        {
            $basesql = "SELECT Auctions.ID FROM Auctions WHERE ";
            $order = " ORDER BY AES_DECRYPT(Auctions.AuctionStart, :key) DESC";
            $params = array();
            $params['key'] = AES_ENCRYPTION_KEY;

            //Build SQL depending on passedMode and passedNeedle
            switch($passedMode) {
                
                case 'auction-room-id':
                    $query = "Auctions.AuctionRoomID = :needle ";
                    $params['needle'] = $passedNeedle;
                    break;
                    
                case 'current-future':
                    $query = " (CONVERT(AES_DECRYPT(Auctions.AuctionStart, :key) USING utf8) >= CURDATE()) ";
                    $params['key'] = AES_ENCRYPTION_KEY;
                    break;
                    
                case 'archive':
                    $query = " (CONVERT(AES_DECRYPT(Auctions.AuctionStart, :key) USING utf8) < NOW()) ";
                    $params['key'] = AES_ENCRYPTION_KEY;
                    break;
                    
                case 'vehicle-id':
                    $query = "Auctions.VehicleID = :needle ";
                    $params['needle'] = $passedNeedle;
                    break;
                    
                default:
                    $query = "ID = ID ";
                    break;
            }
            
            
            //OPTIONS
            if (isset($futureonlyflag) && $futureonlyflag === true) {
                $query .= " AND (CONVERT(AES_DECRYPT(Auctions.AuctionStart, :key) USING utf8) >= NOW() AND CONVERT(AES_DECRYPT(Auctions.AuctionEnd, :key) USING utf8) = '') ";
                $params['key'] = AES_ENCRYPTION_KEY;
            }
            
            switch($sortorder) {
                case 'asc':
                    $order = " ORDER BY AES_DECRYPT(Auctions.AuctionStart, :key) ASC";
                    $params['key'] = AES_ENCRYPTION_KEY;
                    break;
                case 'desc':
                    $order = " ORDER BY AES_DECRYPT(Auctions.AuctionStart, :key) DESC";
                    $params['key'] = AES_ENCRYPTION_KEY;
                    break;
                default:
                    break;
            }
            
            //Carry out the query
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
                error_log('CCA\Auction->deleteItem() Unable to delete as no id set');

                return false;
            }
            
            //REMOVE ALL VEHICLES FROM THIS AUCTION
            $AVO = new AuctionVehicle();
            if (is_object($AVO)) {
                $Vehicles = $AVO->listAllItems($id,'auction-id');
                if (is_array($Vehicles) && count($Vehicles) > 0) {
                    foreach ($Vehicles as $Vehicle) {
                        $AVO->deleteItem($Vehicle['ID']);
                    }
                }
            }
            
            //Now the actual record
            try {
                $stmt = $this->_dbconn->prepare("DELETE FROM Auctions WHERE ID = :id LIMIT 1");
                $result = $stmt->execute([
                                             'id' => $id
                                         ]);
                if ($result === true) {
                    return true;
                }

            } catch (Exception $e) {
                error_log("CCA\Auction->deleteItem() Failed to delete record" . $e);
            }

            return false;
        }


        /**
         * Create new record stub
         */
        public function createNewItem()
        {
            try {
                $stmt = $this->_dbconn->prepare("INSERT INTO Auctions SET AuctionRoomID = null");
                $stmt->execute();
                $id = $this->_dbconn->lastInsertId();
                
                //Store in the object
                $this->_id = $id;
                
            } catch (Exception $e) {
                error_log("CCA\Auction->createNewItem() Failed to create new stub".$e);
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
                $stmt = $this->_dbconn->prepare("UPDATE Auctions SET AuctionRoomID = :auction_room_id, AuctionStart = AES_ENCRYPT(:auction_start, :key), Seller_Percent = AES_ENCRYPT(:seller_percent, :key), Seller_UptoMax = AES_ENCRYPT(:seller_upto_max, :key), Seller_Fixed = AES_ENCRYPT(:seller_fixed, :key), Buyer_Percent = AES_ENCRYPT(:buyer_percent, :key), Buyer_UptoMax = AES_ENCRYPT(:buyer_upto_max, :key), Buyer_Fixed = AES_ENCRYPT(:buyer_fixed, :key), BidExtensionTime = AES_ENCRYPT(:bid_extension_time, :key), LotMinimumLength = AES_ENCRYPT(:lot_minimum_length, :key), LotBidIncrement = AES_ENCRYPT(:lot_bid_increment, :key), AuctionEnd = AES_ENCRYPT(:auction_end, :key) WHERE ID = :id LIMIT 1");
                $result = $stmt->execute([
                    'key' => AES_ENCRYPTION_KEY,
                    'auction_room_id' => $this->_auction_room_id,
                    'auction_start' => $this->_auction_start,
                    'seller_percent' => $this->_seller_percent,
                    'seller_upto_max' => $this->_seller_upto_max,
                    'seller_fixed' => $this->_seller_fixed,
                    'buyer_percent' => $this->_buyer_percent,
                    'buyer_upto_max' => $this->_buyer_upto_max,
                    'buyer_fixed' => $this->_buyer_fixed,
                    'bid_extension_time' => $this->_bid_extension_time,
                    'lot_minimum_length' => $this->_lot_minimum_length,
                    'lot_bid_increment' => $this->_lot_bid_increment,
                    'auction_end' => $this->_auction_end,
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
                error_log("CCA\Auction->saveItem() Failed to save record: " . $e);
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
                error_log('CCA\Auction->updateField() - requires record id to be passed');
                return false;
            }
            if (!is_string($field) || $field == '' ) {
                error_log('CCA\Auction->updateField() - requires field to be passed');
                return false;
            }
            
            //Check its not one of our special columns
            if ($field == 'ID') {
                error_log('CCA\Auction->updateField() - Attempted to update prohibited field');
                return false;
            }
            
            
            //Check if the field and record exists
            $stmt = $this->_dbconn->prepare("SELECT ID FROM Auctions WHERE ID = :id LIMIT 1");
            $stmt->execute([
                'id' => $recid
            ]);
            $item = $stmt->fetch();
            
            if ($item['ID'] <= 0 ) {
                error_log('CCA\Auction->updateField() - Record does not exist');
                return false;
            }
            
            $stmt = $this->_dbconn->prepare("SHOW COLUMNS FROM Auctions LIKE :field");
            $stmt->execute([
                'field' => $field
            ]);
            $items = $stmt->fetchAll();
            
            if (!is_array($items) || count($items) <= 0) {
                error_log('CCA\Auction->updateField() - Field does not exist');
                return false;
            }
            
            
            //Now carry out the update
            //Check if the field and record exists
            if ($field == 'AuctionRoomID') {
                //No encryption
                $sql = "UPDATE Auctions SET ".$field." = :value WHERE ID = :id LIMIT 1";
                $params = array('id' => $recid, 'value' => $value);
            } else {
                $sql = "UPDATE Auctions SET ".$field." = AES_ENCRYPT(:value, :key) WHERE ID = :id LIMIT 1";
                $params = array('id' => $recid, 'key' => AES_ENCRYPTION_KEY, 'value' => $value);
            }
            $stmt = $this->_dbconn->prepare($sql);
            $result = $stmt->execute($params);
            
            if ($result == true) { return true; } else {
                error_log($stmt->errorInfo()[2]);
                return false;
            }
        }
        
        
        
        /**
         * Count number of lots in this auction
         * @param int $auctionid
        
         *
         * @return int
         */
        public function countLots($auctionid = null)
        {
            if (!isset($auctionid) || !is_numeric($auctionid)) {
                $auctionid = $this->_id;
            }
            if (!isset($auctionid) || !is_numeric($auctionid)) {
                error_log("CCA\Auction->countLots() - no auctionID set or passed");
                return false;
            }
            
            $basesql = "SELECT COUNT(AuctionVehicles.ID) AS Lots FROM AuctionVehicles WHERE AuctionVehicles.AuctionID = :needle ";
            $params = array(
                'needle' => $auctionid
            );
            $stmt = $this->_dbconn->prepare($basesql);
            $result = $stmt->execute($params);
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $Lots = 0;
            if (is_array($items) && count($items) > 0) {
                $Lots = $items[0]['Lots'];
            }
            return $Lots;
        }
        
        
        /**
         * Count number of remaining lots in passed auction
         * @param $auctionid
         * @return int|mixed
         */
        public function countRemaining($auctionid = null)
        {
            if (!isset($auctionid) || !is_numeric($auctionid)) {
                $auctionid = $this->_id;
            }
            if (!isset($auctionid) || !is_numeric($auctionid)) {
                error_log("CCA\Auction->countRemaining() - no auctionID set or passed");
                return false;
            }
            
            $basesql = "SELECT COUNT(AuctionVehicles.ID) AS Remaining FROM AuctionVehicles WHERE AuctionVehicles.AuctionID = :needle AND (AuctionVehicles.AuctionStatus IS NULL OR CONVERT(AES_DECRYPT(AuctionVehicles.AuctionStatus, :key) USING utf8) = '' OR CONVERT(AES_DECRYPT(AuctionVehicles.AuctionStatus, :key) USING utf8) = 'Active') ";
            $params = array(
                'needle' => $auctionid,
                'key' => AES_ENCRYPTION_KEY
            );
            $stmt = $this->_dbconn->prepare($basesql);
            $result = $stmt->execute($params);
            $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $Remaining = 0;
            if (is_array($items) && count($items) > 0) {
                $Remaining = $items[0]['Remaining'];
            }
            return $Remaining;
        }
        
        
        /**
         * Determine status of Auction - and return array of status.Detail(Status, Starts, StartLabel)
         * @param $auctionid
         * @return array
         */
        public function determineAuctionStatus()
        {
            $auctionid = $this->_id;
            
            if (!isset($auctionid) || !is_numeric($auctionid)) {
                $auctionid = $this->_id;
            }
            if (!isset($auctionid) || !is_numeric($auctionid)) {
                error_log("CCA\Auction->determineAuctionStatus() - no auctionID set");
                $success = false;
                $reason = "No auction ID passed";
                $ret_arr = array('Result'=>false, 'Reason'=>$reason);
                return $ret_arr;
            }
            
            if ($this->_auction_start_date == date('Y-m-d', time())) {
                $starts = format_prettytime($this->_auction_start_time);
            } else {
                $starts = format_shortdate($this->_auction_start_date);
            }
            if ($this->_auction_start <= date('Y-m-d H:i:s', time()) && (!isset($this->_auction_end) || $this->_auction_end !== '')) {
                $status = 'current';
                $starts_label = "Started:";
                $starts = format_prettytime($this->_auction_start_time);
            } else {
                $status = 'future';
                $starts_label = "Starts:";
            }
            
            $ret_arr = array('Result'=>true, 'Detail' => array('Status'=>$status, 'Starts'=>$starts, 'StartLabel'=>$starts_label));
            return $ret_arr;
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
        public function getAuctionRoomId()
        {
            return $this->_auction_room_id;
        }
        
        /**
         * @param mixed $auction_room_id
         */
        public function setAuctionRoomId($auction_room_id): void
        {
            if (!is_numeric($auction_room_id) || $auction_room_id < 0) { $auction_room_id = null; }
            $this->_auction_room_id = $auction_room_id;
        }
        
        /**
         * @return mixed
         */
        public function getAuctionStart()
        {
            return $this->_auction_start;
        }
        
        /**
         * @param mixed $auction_start
         */
        public function setAuctionStart($auction_start): void
        {
            $this->_auction_start = $auction_start;
        }
        
        /**
         * @return mixed
         */
        public function getSellerPercent()
        {
            return $this->_seller_percent;
        }
        
        /**
         * @param mixed $seller_percent
         */
        public function setSellerPercent($seller_percent): void
        {
            $this->_seller_percent = $seller_percent;
        }
        
        /**
         * @return mixed
         */
        public function getSellerUptoMax()
        {
            return $this->_seller_upto_max;
        }
        
        /**
         * @param mixed $seller_upto_max
         */
        public function setSellerUptoMax($seller_upto_max): void
        {
            $this->_seller_upto_max = $seller_upto_max;
        }
        
        /**
         * @return mixed
         */
        public function getSellerFixed()
        {
            return $this->_seller_fixed;
        }
        
        /**
         * @param mixed $seller_fixed
         */
        public function setSellerFixed($seller_fixed): void
        {
            $this->_seller_fixed = $seller_fixed;
        }
        
        /**
         * @return mixed
         */
        public function getBuyerPercent()
        {
            return $this->_buyer_percent;
        }
        
        /**
         * @param mixed $buyer_percent
         */
        public function setBuyerPercent($buyer_percent): void
        {
            $this->_buyer_percent = $buyer_percent;
        }
        
        /**
         * @return mixed
         */
        public function getBuyerUptoMax()
        {
            return $this->_buyer_upto_max;
        }
        
        /**
         * @param mixed $buyer_upto_max
         */
        public function setBuyerUptoMax($buyer_upto_max): void
        {
            $this->_buyer_upto_max = $buyer_upto_max;
        }
        
        /**
         * @return mixed
         */
        public function getBuyerFixed()
        {
            return $this->_buyer_fixed;
        }
        
        /**
         * @param mixed $buyer_fixed
         */
        public function setBuyerFixed($buyer_fixed): void
        {
            $this->_buyer_fixed = $buyer_fixed;
        }
        
        /**
         * @return mixed
         */
        public function getBidExtensionTime()
        {
            return $this->_bid_extension_time;
        }
        
        /**
         * @param mixed $bid_extension_time
         */
        public function setBidExtensionTime($bid_extension_time): void
        {
            $this->_bid_extension_time = $bid_extension_time;
        }
        
        /**
         * @return mixed
         */
        public function getLotMinimumLength()
        {
            return $this->_lot_minimum_length;
        }
        
        /**
         * @param mixed $lot_minimum_length
         */
        public function setLotMinimumLength($lot_minimum_length): void
        {
            $this->_lot_minimum_length = $lot_minimum_length;
        }
        
        /**
         * @return mixed
         */
        public function getLotBidIncrement()
        {
            return $this->_lot_bid_increment;
        }
        
        /**
         * @param mixed $lot_bid_increment
         */
        public function setLotBidIncrement($lot_bid_increment): void
        {
            $this->_lot_bid_increment = $lot_bid_increment;
        }
        
        /**
         * @return mixed
         */
        public function getAuctionEnd()
        {
            return $this->_auction_end;
        }
        
        /**
         * @param mixed $auction_end
         */
        public function setAuctionEnd($auction_end): void
        {
            $this->_auction_end = $auction_end;
        }
        
        
        
        
        

    }