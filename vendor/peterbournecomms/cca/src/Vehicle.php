<?php
    
    namespace PeterBourneComms\CCA;
    
    use PDO;
    use Exception;
    use PeterBourneComms\CMS\Database;
    use PeterBourneComms\CMS\ContentLibrary;
    use PeterBourneComms\CCA\VehicleFeature;
    use PeterBourneComms\CCA\VehicleAppraisal;
    use PeterBourneComms\CCA\VehicleService;
    use PeterBourneComms\CCA\AuctionVehicle;
    
    /**
     * @package PeterBourneComms\CCA
     * @author  Peter Bourne
     * @version 1.1
     *
     *  Deals with CCA Vehicles
     *  Relies on Vehicles and possibly Auctions in database
     *
     *  History
     *  22/04/2024  1.0     Initial version
     *  05/06/2024  1.1     Added Service History options
     *
     */
    
    class Vehicle
    {
        protected $_dbconn;
        /**
         * @var int|string
         */
        protected $_id;
        /**
         * @var
         */
        protected $_customer_id;
        protected $_make;
        protected $_model;
        protected $_vehicle_type;
        protected $_reg;
        protected $_short_desc;
        protected $_date_of_first_reg;
        protected $_mileage;
        protected $_manufacturer_colour;
        protected $_finish_type;
        protected $_trim_colour;
        protected $_trim_type;
        protected $_transmission;
        protected $_fuel;
        protected $_no_of_doors;
        protected $_no_of_keys;
        protected $_no_of_owners;
        protected $_engine_size;
        protected $_wheel_size;
        protected $_alloy_spec;
        protected $_mot_expires;
        protected $_v5_present;
        protected $_service_history;
        protected $_description;
        protected $_tyre_fos;
        protected $_tyre_fns;
        protected $_tyre_ros;
        protected $_tyre_rns;
        protected $_updates;
        protected $_date_added;
        protected $_date_updated;
        protected $_record_complete;
        protected $_override_auction_fees;
        protected $_seller_percent;
        protected $_seller_upto_max;
        protected $_seller_fixed;
        protected $_buyer_percent;
        protected $_buyer_upto_max;
        protected $_buyer_fixed;
        protected $_reserve_price;
        protected $_starting_bid;
        protected $_buy_it_now;
        protected $_buy_it_now_price;
        protected $_vehicle_status;
        
        protected $_images;
        
        private $_status_options = array(array('Label' => 'Waiting', 'Value' => 'Waiting'),array('Label' => 'In auction', 'Value' => 'In auction'), array('Label' => 'Sold', 'Value' => 'Sold'), array('Label' => 'Not sold', 'Value' => 'Not sold'));
        
        private $_transmission_options = array(array('Label' => 'Manual', 'Value' => 'MANUAL'),array('Label' => 'Automatic', 'Value' => 'AUTOMATIC'));
        
        private $_fuel_options = array(array('Label' => 'Petrol', 'Value' => 'PETROL'),array('Label' => 'Diesel', 'Value' => 'HEAVY OIL'),array('Label' => 'Hybrid', 'Value' => 'HYBRID ELECTRIC'),array('Label' => 'Petrol / Gas', 'Value' => 'PETROL/GAS'),array('Label' => 'Gas Bi-Fuel', 'Value' => 'GAS BI-FUEL'),array('Label' => 'Fuel Cells', 'Value' => 'FUEL CELLS'), array('Label' => 'Electric Diesel', 'Value' => 'ELECTRIC DIESEL'), array('Label' => 'Electric', 'Value' => 'ELECTRIC'));
        
        private $_service_history_options = array(array('Label' => 'Full Main Dealer', 'Value' => 'Full Main Dealer'),array('Label' => 'Full', 'Value' => 'Full'),array('Label' => 'Partial', 'Value' => 'Partial'),array('Label' => 'None', 'Value' => 'None'));
        
        private $_vehicle_type_options = array(array('Label' => 'Hatchback', 'Value' => 'Hatchback'),array('Label' => 'Saloon', 'Value' => 'Saloon'),array('Label' => 'Estate', 'Value' => 'Estate'),array('Label' => 'Convertible', 'Value' => 'Convertible'));
        
        
        
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
                throw new Exception('CCA\Vehicle->__construct() requires id to be specified as an integer - if it is specified at all');
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
                error_log('CCA\Vehicle->getItemById() Unable to retrieve details as no ID set');
                return false;
            }

            //Now retrieve the extra fields
            try {
                $stmt = $this->_dbconn->prepare("SELECT ID, CustomerID, AES_DECRYPT(Make, :key) AS Make, AES_DECRYPT(Model, :key) AS Model, AES_DECRYPT(VehicleType, :key) AS VehicleType, AES_DECRYPT(Reg, :key) AS Reg, AES_DECRYPT(ShortDesc, :key) AS ShortDesc, DateOfFirstReg, Mileage, AES_DECRYPT(ManufacturerColour, :key) AS ManufacturerColour, AES_DECRYPT(FinishType, :key) AS FinishType, AES_DECRYPT(TrimColour, :key) AS TrimColour, AES_DECRYPT(TrimType, :key) AS TrimType, AES_DECRYPT(Transmission, :key) AS Transmission, AES_DECRYPT(Fuel, :key) AS Fuel, AES_DECRYPT(NoOfDoors, :key) AS NoOfDoors, AES_DECRYPT(NoOfKeys, :key) AS NoOfKeys, AES_DECRYPT(NoOfOwners, :key) AS NoOfOwners, AES_DECRYPT(EngineSize, :key) AS EngineSize, AES_DECRYPT(WheelSize, :key) AS WheelSize, AES_DECRYPT(AlloySpec, :key) AS AlloySpec, MOTExpires, AES_DECRYPT(V5Present, :key) AS V5Present, AES_DECRYPT(ServiceHistory, :key) AS ServiceHistory, AES_DECRYPT(Description, :key) AS Description, AES_DECRYPT(TyreFOS, :key) AS TyreFOS, AES_DECRYPT(TyreFNS, :key) AS TyreFNS, AES_DECRYPT(TyreROS, :key) AS TyreROS, AES_DECRYPT(TyreRNS, :key) AS TyreRNS, AES_DECRYPT(Updates, :key) AS Updates, DateAdded, DateUpdated, AES_DECRYPT(RecordComplete, :key) AS RecordComplete, AES_DECRYPT(OverrideAuctionFees, :key) AS OverrideAuctionFees, AES_DECRYPT(Seller_Percent, :key) AS Seller_Percent, AES_DECRYPT(Seller_UptoMax, :key) AS Seller_UptoMax, AES_DECRYPT(Seller_Fixed, :key) AS Seller_Fixed, AES_DECRYPT(Buyer_Percent, :key) AS Buyer_Percent, AES_DECRYPT(Buyer_UptoMax, :key) AS Buyer_UptoMax, AES_DECRYPT(Buyer_Fixed, :key) AS Buyer_Fixed, AES_DECRYPT(ReservePrice, :key) AS ReservePrice, AES_DECRYPT(StartingBid, :key) AS StartingBid, AES_DECRYPT(BuyItNow, :key) AS BuyItNow, AES_DECRYPT(BuyItNowPrice, :key) AS BuyItNowPrice, AES_DECRYPT(VehicleStatus, :key) AS VehicleStatus FROM Vehicles WHERE ID =  :id LIMIT 1");
                $stmt->execute([
                                   'key' => AES_ENCRYPTION_KEY,
                                   'id' => $id
                               ]);
                $item = $stmt->fetch(PDO::FETCH_ASSOC);

            } catch (Exception $e)
            {
                error_log("CCA\Vehicle->getItemById() Failed to retrieve details" . $e);
            }

            //Store details in relevant customers
            $this->_id = $item['ID'];
            $this->_customer_id = $item['CustomerID'];
            $this->_make = $item['Make'];
            $this->_model = $item['Model'];
            $this->_vehicle_type = $item['VehicleType'];
            $this->_reg = $item['Reg'];
            $this->_short_desc = $item['ShortDesc'];
            $this->_date_of_first_reg = $item['DateOfFirstReg'];
            $this->_mileage = $item['Mileage'];
            $this->_manufacturer_colour = $item['ManufacturerColour'];
            $this->_finish_type = $item['FinishType'];
            $this->_trim_colour = $item['TrimColour'];
            $this->_trim_type = $item['TrimType'];
            $this->_transmission = $item['Transmission'];
            $this->_fuel = $item['Fuel'];
            $this->_no_of_doors = $item['NoOfDoors'];
            $this->_no_of_keys = $item['NoOfKeys'];
            $this->_no_of_owners = $item['NoOfOwners'];
            $this->_engine_size = $item['EngineSize'];
            $this->_wheel_size = $item['WheelSize'];
            $this->_alloy_spec = $item['AlloySpec'];
            $this->_mot_expires = $item['MOTExpires'];
            $this->_v5_present = $item['V5Present'];
            $this->_service_history = $item['ServiceHistory'];
            $this->_description = $item['Description'];
            $this->_tyre_fos = $item['TyreFOS'];
            $this->_tyre_fns = $item['TyreFNS'];
            $this->_tyre_ros = $item['TyreROS'];
            $this->_tyre_rns = $item['TyreRNS'];
            $this->_updates = $item['Updates'];
            $this->_date_added = $item['DateAdded'];
            $this->_date_updated = $item['DateUpdated'];
            $this->_record_complete = $item['RecordComplete'];
            $this->_override_auction_fees = $item['OverrideAuctionFees'];
            $this->_seller_percent = $item['Seller_Percent'];
            $this->_seller_upto_max = $item['Seller_UptoMax'];
            $this->_seller_fixed = $item['Seller_Fixed'];
            $this->_buyer_percent = $item['Buyer_Percent'];
            $this->_buyer_upto_max = $item['Buyer_UptoMax'];
            $this->_buyer_fixed = $item['Buyer_Fixed'];
            $this->_reserve_price = $item['ReservePrice'];
            $this->_starting_bid = $item['StartingBid'];
            $this->_buy_it_now = $item['BuyItNow'];
            $this->_buy_it_now_price = $item['BuyItNowPrice'];
            $this->_vehicle_status = $item['VehicleStatus'];
            
            //Retrieve vehicle images
            $this->_images = $this->listVehicleImages();
            $item['Images'] = $this->_images;
            
            //Now retrieve the customer information
            $CO = new Customer();
            if (is_object($CO)) {
                $Customer = $CO->getItemById($this->_customer_id);
                if (is_array($Customer)) {
                    $item['CustomerInfo'] = $Customer;
                }
            }
            
            //TODO: Decide whether this is listed here - maybe circular......?!
            $AVO = new AuctionVehicle();
            if (is_object($AVO)) {
                $Auctions = $AVO->listAllItems($item['ID'],'vehicle-id');
                if (is_array($Auctions) && count($Auctions) > 0) {
                    $item['Auctions'] = $Auctions;
                }
            }
            
            $VAO = new VehicleAppraisal();
            if (is_object($VAO)) {
                $AppraisalItems = $VAO->listAllItems($item['ID'],'vehicle-id');
                if (is_array($AppraisalItems) && count($AppraisalItems) > 0) {
                    $item['VehicleAppraisal'] = $AppraisalItems;
                }
            }
            
            $SHO = new VehicleService();
            if (is_object($SHO)) {
                $ServiceHistory = $SHO->listAllItems($item['ID'],'vehicle-id');
                if (is_array($ServiceHistory) && count($ServiceHistory) > 0) {
                    $item['ServiceHistoryItems'] = $ServiceHistory;
                }
            }
            
            $VFO = new VehicleFeature();
            if (is_object($VFO)) {
                $Features = $VFO->listAllItems($item['ID'],'vehicle-id');
                if (is_array($Features) && count($Features) > 0) {
                    $item['VehicleFeatures'] = $Features;
                }
            }
            
            //Get dereived fuel type
            $item['FuelType'] = $item['Fuel'];
            if ($item['FuelType'] === 'HEAVY OIL') {
                $item['FuelType'] = 'Diesel';
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
        public function listAllItems($passedNeedle = '', $passedMode = '', $passedSortOrder = null, $passedStatus = null, $passedCustomerID = null, $passedAuctionAssignedFilter = false)
        {
            $basesql = "SELECT Vehicles.ID FROM Vehicles WHERE ";
            $order = " ORDER BY Vehicles.DateAdded DESC";
            $params = array();

            //Build SQL depending on passedMode and passedNeedle
            switch($passedMode) {
                case 'reg':
                    $query = " (CONVERT(AES_DECRYPT(Vehicles.Reg, :key) USING utf8) LIKE :needle) ";
                    $order = "ORDER BY AES_DECRYPT(Vehicles.Reg, :key) ASC";
                    $params['needle'] = $passedNeedle."%";
                    $params['key'] = AES_ENCRYPTION_KEY;
                    break;
                    
                case 'customer-id':
                    $query = " (Vehicles.CustomerID = :needle) ";
                    $params['needle'] = $passedNeedle;
                    break;
                
                case 'customer-company':
                    $basesql = "SELECT Vehicles.ID FROM Vehicles LEFT JOIN Customers ON Customers.ID = Vehicles.CustomerID WHERE ";
                    $query = "(CONVERT(AES_DECRYPT(Customers.Company, :key) USING utf8) LIKE :needle) ";
                    $params['needle'] = $passedNeedle."%";
                    $params['key'] = AES_ENCRYPTION_KEY;
                    break;
                    
                case 'reg-customer':
                    $basesql = "SELECT Vehicles.ID FROM Vehicles LEFT JOIN Customers ON Customers.ID = Vehicles.CustomerID WHERE ";
                    $query = " ((CONVERT(AES_DECRYPT(Customers.Company, :key) USING utf8) LIKE :needle) OR (CONVERT(AES_DECRYPT(Vehicles.Reg, :key) USING utf8) LIKE :needle)) ";
                    $params['needle'] = $passedNeedle."%";
                    $params['key'] = AES_ENCRYPTION_KEY;
                    break;
                
                case 'make':
                    $query = " (CONVERT(AES_DECRYPT(Vehicles.Make, :key) USING utf8) LIKE :needle) ";
                    $params['needle'] = $passedNeedle."%";
                    $params['key'] = AES_ENCRYPTION_KEY;
                    break;
                
                case 'model':
                    $query = " (CONVERT(AES_DECRYPT(Vehicles.Model, :key) USING utf8) LIKE :needle) ";
                    $params['needle'] = $passedNeedle."%";
                    $params['key'] = AES_ENCRYPTION_KEY;
                    break;
                    
                case 'available-for-auction':
                    $query = " ((CONVERT(AES_DECRYPT(Vehicles.VehicleStatus, :key) USING utf8) = 'Waiting') || (CONVERT(AES_DECRYPT(Vehicles.VehicleStatus, :key) USING utf8) = 'Not sold')) ";
                    $params['key'] = AES_ENCRYPTION_KEY;
                    break;
                    
                default:
                    $query = " (Vehicles.ID = Vehicles.ID) ";
                    break;
            }
            
            
            //OPTIONS
            if (isset($passedSortOrder)) {
                switch ($passedSortOrder) {
                    case 'date-added-asc':
                        $order = " ORDER BY Vehicles.DateAdded ASC";
                        break;
                    
                    case 'date-added-desc':
                        $order = " ORDER BY Vehicles.DateAdded DESC";
                        break;
                    
                    case 'date-firstreg-asc':
                        $order = " ORDER BY Vehicles.DateOfFirstReg ASC";
                        break;
                    
                    case 'date-firstreg-desc':
                        $order = " ORDER BY Vehicles.DateOfFirstReg DESC";
                        break;
                        
                    default:
                        $order = " ORDER BY Vehicles.DateAdded DESC";
                        break;
                }
            }
            
            if (isset($passedStatus)) {
                $query .= " AND (CONVERT(AES_DECRYPT(Vehicles.VehicleStatus, :key) USING utf8) = :status) ";
                $params['status'] = $passedStatus;
                $params['key'] = AES_ENCRYPTION_KEY;
            }
            
            if (isset($passedCustomerID) && is_numeric($passedCustomerID) && $passedCustomerID > 0) {
                $query .= " AND (Vehicles.CustomerID = :customer_id) ";
                $params['customer_id'] = $passedCustomerID;
            }
            
            if (isset($passedAuctionAssignedFilter) && $passedAuctionAssignedFilter === true) {
                $basesql = "SELECT AuctionVehicles.VehicleID FROM AuctionVehicles LEFT JOIN Vehicles ON AuctionVehicles.VehicleID = Vehicles.ID LEFT JOIN Customers ON Customers.ID = Vehicles.CustomerID WHERE ";
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
            
            //TODO: Delete images of vehicle - possibly store elsewhere - and/or copy of vehicle data for XX months
            
            if (!is_numeric($id) || $id <= 0) {
                error_log('CCA\Vehicle->deleteItem() Unable to delete as no id set');

                return false;
            }
            
            
            //Now the actual record
            try {
                $stmt = $this->_dbconn->prepare("DELETE FROM Vehicles WHERE ID = :id LIMIT 1");
                $result = $stmt->execute([
                                             'id' => $id
                                         ]);
                if ($result === true) {
                    return true;
                }

            } catch (Exception $e) {
                error_log("CCA\Vehicle->deleteItem() Failed to delete record" . $e);
            }

            return false;
        }


        /**
         * Create new record stub
         */
        public function createNewItem()
        {
            try {
                $stmt = $this->_dbconn->prepare("INSERT INTO Vehicles SET DateAdded = NOW()");
                $stmt->execute();
                $id = $this->_dbconn->lastInsertId();
                
                //Store in the object
                $this->_id = $id;
                
            } catch (Exception $e) {
                error_log("CCA\Vehicle->createNewItem() Failed to create new stub".$e);
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
                $stmt = $this->_dbconn->prepare("UPDATE Vehicles SET CustomerID = :customer_id, Make = AES_ENCRYPT(:make, :key), Model = AES_ENCRYPT(:model, :key), VehicleType = AES_ENCRYPT(:vehicle_type, :key), Reg = AES_ENCRYPT(:reg, :key), ShortDesc = AES_ENCRYPT(:short_desc, :key), DateOfFirstReg = :date_of_first_reg, Mileage = :mileage, ManufacturerColour = AES_ENCRYPT(:manufacturer_colour, :key), FinishType = AES_ENCRYPT(:finish_type, :key), TrimColour = AES_ENCRYPT(:trim_colour, :key), TrimType = AES_ENCRYPT(:trim_type, :key), Transmission = AES_ENCRYPT(:transmission, :key), Fuel = AES_ENCRYPT(:fuel, :key), NoOfOwners = AES_ENCRYPT(:no_of_owners, :key), NoOfKeys = AES_ENCRYPT(:no_of_keys, :key), NoOfDoors = AES_ENCRYPT(:no_of_doors, :key), EngineSize = AES_ENCRYPT(:engine_size, :key), WheelSize = AES_ENCRYPT(:wheel_size, :key), AlloySpec = AES_ENCRYPT(:alloy_spec, :key), MOTExpires = :mot_expires, V5Present = AES_ENCRYPT(:v5_present, :key), ServiceHistory = AES_ENCRYPT(:service_history, :key), Description = AES_ENCRYPT(:description, :key), TyreFOS = AES_ENCRYPT(:tyre_fos, :key), TyreFNS = AES_ENCRYPT(:tyre_fns, :key), TyreRNS = AES_ENCRYPT(:tyre_rns, :key), TyreROS = AES_ENCRYPT(:tyre_ros, :key), Updates = AES_ENCRYPT(:updates, :key), RecordComplete = AES_ENCRYPT(:record_complete, :key), OverrideAuctionFees = AES_ENCRYPT(:override_auction_fees, :key), Seller_Percent = AES_ENCRYPT(:seller_percent, :key), Seller_UptoMax = AES_ENCRYPT(:seller_upto_max, :key), Seller_Fixed = AES_ENCRYPT(:seller_fixed, :key), Buyer_Percent = AES_ENCRYPT(:buyer_percent, :key), Buyer_UptoMax = AES_ENCRYPT(:buyer_upto_max, :key), Buyer_Fixed = AES_ENCRYPT(:buyer_fixed, :key), ReservePrice = AES_ENCRYPT(:reserve_price, :key), StartingBid = AES_ENCRYPT(:starting_bid, :key), BuyItNow = AES_ENCRYPT(:buy_it_now, :key), BuyItNowPrice = AES_ENCRYPT(:buy_it_now_price, :key), VehicleStatus = AES_ENCRYPT(:vehicle_status, :key), DateUpdated = NOW() WHERE ID = :id LIMIT 1");
                $result = $stmt->execute([
                    'key' => AES_ENCRYPTION_KEY,
                    'customer_id' => $this->_customer_id,
                    'make' => $this->_make,
                    'model' => $this->_model,
                    'vehicle_type' => $this->_vehicle_type,
                    'reg' => $this->_reg,
                    'short_desc' => $this->_short_desc,
                    'date_of_first_reg' => $this->_date_of_first_reg,
                    'mileage' => $this->_mileage,
                    'manufacturer_colour' => $this->_manufacturer_colour,
                    'finish_type' => $this->_finish_type,
                    'trim_type' => $this->_trim_type,
                    'trim_colour' => $this->_trim_colour,
                    'transmission' => $this->_transmission,
                    'fuel' => $this->_fuel,
                    'no_of_doors' => $this->_no_of_doors,
                    'no_of_keys' => $this->_no_of_keys,
                    'no_of_owners' => $this->_no_of_owners,
                    'engine_size' => $this->_engine_size,
                    'wheel_size' => $this->_wheel_size,
                    'alloy_spec' => $this->_alloy_spec,
                    'mot_expires' => $this->_mot_expires,
                    'v5_present' => $this->_v5_present,
                    'service_history' => $this->_service_history,
                    'description' => $this->_description,
                    'tyre_fos' => $this->_tyre_fos,
                    'tyre_fns' => $this->_tyre_fns,
                    'tyre_ros' => $this->_tyre_ros,
                    'tyre_rns' => $this->_tyre_rns,
                    'updates' => $this->_updates,
                    'record_complete' => $this->_record_complete,
                    'override_auction_fees' => $this->_override_auction_fees,
                    'seller_percent' => $this->_seller_percent,
                    'seller_upto_max' => $this->_seller_upto_max,
                    'seller_fixed' => $this->_seller_fixed,
                    'buyer_percent' => $this->_buyer_percent,
                    'buyer_upto_max' => $this->_buyer_upto_max,
                    'buyer_fixed' => $this->_buyer_fixed,
                    'reserve_price' => $this->_reserve_price,
                    'starting_bid' => $this->_starting_bid,
                    'buy_it_now' => $this->_buy_it_now,
                    'buy_it_now_price' => $this->_buy_it_now_price,
                    'vehicle_status' => $this->_vehicle_status,
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
                error_log("CCA\Vehicle->saveItem() Failed to save record: " . $e);
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
                error_log('CCA\Vehicle->updateField() - requires record id to be passed');
                return false;
            }
            if (!is_string($field) || $field == '' ) {
                error_log('CCA\Vehicle->updateField() - requires field to be passed');
                return false;
            }
            
            //Check its not one of our special columns
            if ($field == 'ID' || $field == 'DateAdded' || $field == 'DateUpdated') {
                error_log('CCA\Vehicle->updateField() - Attempted to update prohibited field');
                return false;
            }
            
            
            //Check if the field and record exists
            $stmt = $this->_dbconn->prepare("SELECT ID FROM Vehicles WHERE ID = :id LIMIT 1");
            $stmt->execute([
                'id' => $recid
            ]);
            $item = $stmt->fetch();
            
            if ($item['ID'] <= 0 ) {
                error_log('CCA\Vehicle->updateField() - Record does not exist');
                return false;
            }
            
            $stmt = $this->_dbconn->prepare("SHOW COLUMNS FROM Vehicles LIKE :field");
            $stmt->execute([
                'field' => $field
            ]);
            $items = $stmt->fetchAll();
            
            if (!is_array($items) || count($items) <= 0) {
                error_log('CCA\Vehicle->updateField() - Field does not exist');
                return false;
            }
            
            
            //Now carry out the update
            //Check if the field and record exists
            if ($field == 'CustomerID' || $field == 'DateOfFirstReg' || $field == 'Mileage' || $field == 'MOTExpires') {
                //No encryption
                $sql = "UPDATE Vehicles SET ".$field." = :value WHERE ID = :id LIMIT 1";
                $params = array('id' => $recid, 'value' => $value);
            } else {
                $sql = "UPDATE Vehicles SET ".$field." = AES_ENCRYPT(:value, :key) WHERE ID = :id LIMIT 1";
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
         * Lookup Makes
         */
        public function lookupMakes()
        {
            try
            {
                $stmt = $this->_dbconn->prepare("SELECT DISTINCT AES_DECRYPT(Make, :key) AS Make FROM Vehicles ORDER BY AES_DECRYPT(Vehicles.Make, :key) ASC");
                $stmt->execute([
                    'key' => AES_ENCRYPTION_KEY
                ]);
                $makes = $stmt->fetchAll(PDO::FETCH_ASSOC);
                return $makes;
                
            } catch (Exception $e)
            {
                error_log("CCA\Vehicle->lookupMakes() Failed to retrieve vehicle make details" . $e);
                return false;
            }
        }
        
        /**
         * Lookup all models stored in the database - and return as an array - to populate future entries
         */
        public function lookupModels()
        {
            try
            {
                $stmt = $this->_dbconn->prepare("SELECT DISTINCT AES_DECRYPT(Model, :key) AS Model FROM Vehicles ORDER BY AES_DECRYPT(Vehicles.Model, :key) ASC");
                $stmt->execute([
                    'key' => AES_ENCRYPTION_KEY
                ]);
                $models = $stmt->fetchAll(PDO::FETCH_ASSOC);
                return $models;
                
            } catch (Exception $e)
            {
                error_log("CCA\Vehicle->lookupModels() Failed to retrieve vehicle model details" . $e);
                return false;
            }
        }
        
        /**
         * Lookup all manufacturer colours stored in the database - and return as an array - to populate future entries
         */
        public function lookupManufacturerColours()
        {
            try
            {
                $stmt = $this->_dbconn->prepare("SELECT DISTINCT AES_DECRYPT(ManufacturerColour, :key) AS ManufacturerColour FROM Vehicles ORDER BY AES_DECRYPT(Vehicles.ManufacturerColour, :key) ASC");
                $stmt->execute([
                    'key' => AES_ENCRYPTION_KEY
                ]);
                $colours = $stmt->fetchAll(PDO::FETCH_ASSOC);
                return $colours;
                
            } catch (Exception $e)
            {
                error_log("CCA\Vehicle->lookupManufacturerColours() Failed to retrieve vehicle colour details" . $e);
                return false;
            }
        }
        
        
        
        
        /**
         * Retrieve all images for this vehicle
         *
         */
        public function listVehicleImages($vehicleid = 0)
        {
            if (!is_numeric($vehicleid) || clean_int($vehicleid) <= 0)
            {
                $vehicleid = $this->_id;
            }
            if (!is_numeric($vehicleid) || clean_int($vehicleid) <= 0)
            {
                error_log("CCA\Vehicle->listVehicleImages() requires vehicle id to be passed or set");
                return false;
            }
            //error_log("CREATING CLO");
            $CLO = new ContentLibrary($vehicleid, 1200, 400, '/vehicle-images');
            if (is_object($CLO))
            {
                //error_log("LISTING IMAGES");
                $Images = $CLO->listAllItems($vehicleid, 'content-id');
                if (isset($Images) && is_array($Images) && count($Images) > 0) {
                    return $Images;
                }
            }
            return false;
        }
        
        
        
        /**
         * Check that user can edit the Vehicle record supplied - ie: Does the CustomerID of the vehicle record MATCH the supplied ID?
         *
         */
        public function checkCanEdit($recid, $customerid)
        {
            if (!is_numeric($recid) || !is_numeric($customerid) || $customerid <= 0 || $recid <= 0)
            {
                error_log("CCA\Vehicle->checkCanEdit() requires recID and customerID to be passed as ints");
                return false;
            }
            
            try
            {
                $stmt = $this->_dbconn->prepare("SELECT ID FROM Vehicles WHERE ID = :recid AND CustomerID = :customerid LIMIT 1");
                $stmt->execute([
                    'recid' => $recid,
                    'customerid' => $customerid
                ]);
                $success = $stmt->fetch();
                
                if ($success['ID'] == $recid) {
                    $ret_arr = array('Success'=>true,'Message'=>'Record found - can edit','ID'=>$success['ID']);
                } else {
                    $ret_arr = array('Success'=>false,'Message'=>'Record not found or logged-in user cannot edit','ID'=>null);
                }
                
                return $ret_arr;
                
            } catch (Exception $e) {
                error_log('CCA\Vehicle->checkCanEdit() could not complete the search of the database for that record '.$e);
            }
        }
        
        
        /**
         * Check if the reg already exists in database
         */
        public function checkDuplicateReg($passedReg)
        {
            if ($passedReg == '') {
                error_log("CCA\Vehicle->checkNoDuplicateReg() requires passed reg");
                return false;
            }
            $reg = str_replace(" ", "", $passedReg);
            
            //Now look up in DB
            $stmt = $this->_dbconn->prepare("SELECT ID FROM Vehicles WHERE REPLACE(CONVERT(AES_DECRYPT(Reg, :key) USING utf8), ' ', '') = :needle");
            $stmt->execute([
                'key' => AES_ENCRYPTION_KEY,
                'needle' => $reg
            ]);
            $results = $stmt->fetch();
            if (is_array($results) && count($results) > 0) {
                return true;
            } else {
                //No duplicates
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
        public function getCustomerId()
        {
            return $this->_customer_id;
        }
        
        /**
         * @param mixed $customer_id
         */
        public function setCustomerId($customer_id): void
        {
            if (!is_numeric($customer_id) || $customer_id < 0) { $customer_id = null; }
            $this->_customer_id = $customer_id;
        }
        
        /**
         * @return mixed
         */
        public function getMake()
        {
            return $this->_make;
        }
        
        /**
         * @param mixed $make
         */
        public function setMake($make): void
        {
            $this->_make = $make;
        }
        
        /**
         * @return mixed
         */
        public function getModel()
        {
            return $this->_model;
        }
        
        /**
         * @param mixed $model
         */
        public function setModel($model): void
        {
            $this->_model = $model;
        }
        
        /**
         * @return mixed
         */
        public function getVehicleType()
        {
            return $this->_vehicle_type;
        }
        
        /**
         * @param mixed $vehicle_type
         */
        public function setVehicleType($vehicle_type): void
        {
            $this->_vehicle_type = $vehicle_type;
        }
        
        /**
         * @return mixed
         */
        public function getReg()
        {
            return $this->_reg;
        }
        
        /**
         * @param mixed $reg
         */
        public function setReg($reg): void
        {
            $this->_reg = $reg;
        }
        
        /**
         * @return mixed
         */
        public function getShortDesc()
        {
            return $this->_short_desc;
        }
        
        /**
         * @param mixed $short_desc
         */
        public function setShortDesc($short_desc): void
        {
            $this->_short_desc = $short_desc;
        }
        
        /**
         * @return mixed
         */
        public function getDateOfFirstReg()
        {
            return $this->_date_of_first_reg;
        }
        
        /**
         * @param mixed $date_of_first_reg
         */
        public function setDateOfFirstReg($date_of_first_reg): void
        {
            if ($date_of_first_reg == '' || $date_of_first_reg == '0000-00-00') { $date_of_first_reg = null; }
            $this->_date_of_first_reg = $date_of_first_reg;
        }
        
        /**
         * @return mixed
         */
        public function getMileage()
        {
            return $this->_mileage;
        }
        
        /**
         * @param mixed $mileage
         */
        public function setMileage($mileage): void
        {
            if (!is_numeric($mileage) || $mileage < 0) { $mileage = null; }
            $this->_mileage = $mileage;
        }
        
        /**
         * @return mixed
         */
        public function getManufacturerColour()
        {
            return $this->_manufacturer_colour;
        }
        
        /**
         * @param mixed $manufacturer_colour
         */
        public function setManufacturerColour($manufacturer_colour): void
        {
            $this->_manufacturer_colour = $manufacturer_colour;
        }
        
        /**
         * @return mixed
         */
        public function getFinishType()
        {
            return $this->_finish_type;
        }
        
        /**
         * @param mixed $finish_type
         */
        public function setFinishType($finish_type): void
        {
            $this->_finish_type = $finish_type;
        }
        
        /**
         * @return mixed
         */
        public function getTrimColour()
        {
            return $this->_trim_colour;
        }
        
        /**
         * @param mixed $trim_colour
         */
        public function setTrimColour($trim_colour): void
        {
            $this->_trim_colour = $trim_colour;
        }
        
        /**
         * @return mixed
         */
        public function getTrimType()
        {
            return $this->_trim_type;
        }
        
        /**
         * @param mixed $trim_type
         */
        public function setTrimType($trim_type): void
        {
            $this->_trim_type = $trim_type;
        }
        
        /**
         * @return mixed
         */
        public function getTransmission()
        {
            return $this->_transmission;
        }
        
        /**
         * @param mixed $transmission
         */
        public function setTransmission($transmission): void
        {
            $this->_transmission = $transmission;
        }
        
        /**
         * @return mixed
         */
        public function getFuel()
        {
            return $this->_fuel;
        }
        
        /**
         * @param mixed $fuel
         */
        public function setFuel($fuel): void
        {
            $this->_fuel = $fuel;
        }
        
        /**
         * @return mixed
         */
        public function getNoOfDoors()
        {
            return $this->_no_of_doors;
        }
        
        /**
         * @param mixed $no_of_doors
         */
        public function setNoOfDoors($no_of_doors): void
        {
            $this->_no_of_doors = $no_of_doors;
        }
        
        /**
         * @return mixed
         */
        public function getNoOfKeys()
        {
            return $this->_no_of_keys;
        }
        
        /**
         * @param mixed $no_of_keys
         */
        public function setNoOfKeys($no_of_keys): void
        {
            $this->_no_of_keys = $no_of_keys;
        }
        
        /**
         * @return mixed
         */
        public function getNoOfOwners()
        {
            return $this->_no_of_owners;
        }
        
        /**
         * @param mixed $no_of_owners
         */
        public function setNoOfOwners($no_of_owners): void
        {
            $this->_no_of_owners = $no_of_owners;
        }
        
        /**
         * @return mixed
         */
        public function getEngineSize()
        {
            return $this->_engine_size;
        }
        
        /**
         * @param mixed $engine_size
         */
        public function setEngineSize($engine_size): void
        {
            $this->_engine_size = $engine_size;
        }
        
        /**
         * @return mixed
         */
        public function getWheelSize()
        {
            return $this->_wheel_size;
        }
        
        /**
         * @param mixed $wheel_size
         */
        public function setWheelSize($wheel_size): void
        {
            $this->_wheel_size = $wheel_size;
        }
        
        /**
         * @return mixed
         */
        public function getAlloySpec()
        {
            return $this->_alloy_spec;
        }
        
        /**
         * @param mixed $alloy_spec
         */
        public function setAlloySpec($alloy_spec): void
        {
            $this->_alloy_spec = $alloy_spec;
        }
        
        /**
         * @return mixed
         */
        public function getMotExpires()
        {
            return $this->_mot_expires;
        }
        
        /**
         * @param mixed $mot_expires
         */
        public function setMotExpires($mot_expires): void
        {
            if ($mot_expires == '' || $mot_expires == '0000-00-00') { $mot_expires = null; }
            $this->_mot_expires = $mot_expires;
        }
        
        /**
         * @return mixed
         */
        public function getV5Present()
        {
            return $this->_v5_present;
        }
        
        /**
         * @param mixed $v5_present
         */
        public function setV5Present($v5_present): void
        {
            $this->_v5_present = $v5_present;
        }
        
        /**
         * @return mixed
         */
        public function getServiceHistory()
        {
            return $this->_service_history;
        }
        
        /**
         * @param mixed $service_history
         */
        public function setServiceHistory($service_history): void
        {
            $this->_service_history = $service_history;
        }
        
        /**
         * @return mixed
         */
        public function getDescription()
        {
            return $this->_description;
        }
        
        /**
         * @param mixed $description
         */
        public function setDescription($description): void
        {
            $this->_description = $description;
        }
        
        /**
         * @return mixed
         */
        public function getTyreFos()
        {
            return $this->_tyre_fos;
        }
        
        /**
         * @param mixed $tyre_fos
         */
        public function setTyreFos($tyre_fos): void
        {
            $this->_tyre_fos = $tyre_fos;
        }
        
        /**
         * @return mixed
         */
        public function getTyreFns()
        {
            return $this->_tyre_fns;
        }
        
        /**
         * @param mixed $tyre_fns
         */
        public function setTyreFns($tyre_fns): void
        {
            $this->_tyre_fns = $tyre_fns;
        }
        
        /**
         * @return mixed
         */
        public function getTyreRos()
        {
            return $this->_tyre_ros;
        }
        
        /**
         * @param mixed $tyre_ros
         */
        public function setTyreRos($tyre_ros): void
        {
            $this->_tyre_ros = $tyre_ros;
        }
        
        /**
         * @return mixed
         */
        public function getTyreRns()
        {
            return $this->_tyre_rns;
        }
        
        /**
         * @param mixed $tyre_rns
         */
        public function setTyreRns($tyre_rns): void
        {
            $this->_tyre_rns = $tyre_rns;
        }
        
        /**
         * @return mixed
         */
        public function getUpdates()
        {
            return $this->_updates;
        }
        
        /**
         * @param mixed $updates
         */
        public function setUpdates($updates): void
        {
            $this->_updates = $updates;
        }
        
        /**
         * @return mixed
         */
        public function getDateAdded()
        {
            return $this->_date_added;
        }
        
        /**
         * @param mixed $date_added
         */
        
        /**
         * @return mixed
         */
        public function getDateUpdated()
        {
            return $this->_date_updated;
        }
        
        /**
         * @param mixed $date_updated
         */
        
        /**
         * @return mixed
         */
        public function getRecordComplete()
        {
            return $this->_record_complete;
        }
        
        /**
         * @param mixed $record_complete
         */
        public function setRecordComplete($record_complete): void
        {
            $this->_record_complete = $record_complete;
        }
        
        /**
         * @return mixed
         */
        public function getOverrideAuctionFees()
        {
            return $this->_override_auction_fees;
        }
        
        /**
         * @param mixed $override_auction_fees
         */
        public function setOverrideAuctionFees($override_auction_fees): void
        {
            $this->_override_auction_fees = $override_auction_fees;
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
        public function getReservePrice()
        {
            return $this->_reserve_price;
        }
        
        /**
         * @param mixed $reserve_price
         */
        public function setReservePrice($reserve_price): void
        {
            $this->_reserve_price = $reserve_price;
        }
        
        /**
         * @return mixed
         */
        public function getStartingBid()
        {
            return $this->_starting_bid;
        }
        
        /**
         * @param mixed $starting_bid
         */
        public function setStartingBid($starting_bid): void
        {
            $this->_starting_bid = $starting_bid;
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
        public function getVehicleStatus()
        {
            return $this->_vehicle_status;
        }
        
        /**
         * @param mixed $vehicle_status
         */
        public function setVehicleStatus($vehicle_status): void
        {
            $this->_vehicle_status = $vehicle_status;
        }
        
        
        
        
        
        public function getStatusOptions(): array
        {
            return $this->_status_options;
        }
        
        
        public function getTransmissionOptions(): array
        {
            return $this->_transmission_options;
        }
        
        
        public function getFuelOptions(): array
        {
            return $this->_fuel_options;
        }
        
        
        public function getVehicleTypeOptions(): array
        {
            return $this->_vehicle_type_options;
        }
        
        public function getServiceHistoryOptions(): array
        {
            return $this->_service_history_options;
        }



    }