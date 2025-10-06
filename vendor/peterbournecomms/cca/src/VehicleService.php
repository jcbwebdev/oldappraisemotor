<?php
    
    namespace PeterBourneComms\CCA;
    
    use PDO;
    use Exception;
    use PeterBourneComms\CMS\Database;
    
    /**
     * @package PeterBourneComms\CCA
     * @author  Peter Bourne
     * @version 1.1
     *
     *  Deals with CCA Vehicles
     *  Relies on VehicleService in database
     *
     *  History
     *  23/04/2024  1.0     Initial version
     *  11/06/2024  1.1     Changed Dealer to Type
     *
     */
    
    class VehicleService
    {
        protected $_dbconn;
        /**
         * @var int|string
         */
        protected $_id;
        /**
         * @var
         */
        protected $_vehicle_id;
        protected $_service_date;
        protected $_mileage;
        protected $_type;
        protected $_comments;
        
        private $_type_options = array(array('Label' => 'Full Main Dealer', 'Value' => 'Full Main Dealer'),array('Label' => 'Full', 'Value' => 'Full'),array('Label' => 'Partial', 'Value' => 'Partial'),array('Label' => 'None', 'Value' => 'None'));
        
        
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
                throw new Exception('CCA\VehicleService->__construct() requires id to be specified as an integer - if it is specified at all');
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
                error_log('CCA\VehicleService->getItemById() Unable to retrieve details as no ID set');
                return false;
            }

            //Now retrieve the extra fields
            try {
                $stmt = $this->_dbconn->prepare("SELECT ID, VehicleID, ServiceDate, AES_DECRYPT(Mileage, :key) AS Mileage, AES_DECRYPT(Type, :key) AS `Type`, AES_DECRYPT(Comments, :key) AS Comments FROM VehicleService WHERE ID =  :id LIMIT 1");
                $stmt->execute([
                                   'key' => AES_ENCRYPTION_KEY,
                                   'id' => $id
                               ]);
                $item = $stmt->fetch(PDO::FETCH_ASSOC);

            } catch (Exception $e)
            {
                error_log("CCA\VehicleService->getItemById() Failed to retrieve details" . $e);
            }

            //Store details in relevant customers
            $this->_id = $item['ID'];
            $this->_vehicle_id = $item['VehicleID'];
            $this->_service_date = $item['ServiceDate'];
            $this->_mileage = $item['Mileage'];
            $this->_type = $item['Type'];
            $this->_comments = $item['Comments'];
                        
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
        public function listAllItems($passedNeedle = '', $passedMode = '', $passedVehicleID = null)
        {
            $basesql = "SELECT ID FROM VehicleService WHERE ";
            $order = " ORDER BY AES_DECRYPT(VehicleService.ServiceDate, :key) DESC, VehicleService.ID DESC";
            $params = array();
            $params['key'] = AES_ENCRYPTION_KEY;

            //Build SQL depending on passedMode and passedNeedle
            switch($passedMode) {
                    
                case 'vehicle-id':
                    $query = "VehicleService.VehicleID = :needle ";
                    $params['needle'] = $passedNeedle;
                    break;
                
                default:
                    $query = "ID = ID ";
                    break;
            }
            
            
            //OPTIONS
            if (isset($passedVehicleID) && is_numeric($passedVehicleID) && $passedVehicleID > 0) {
                $query .= " AND (VehicleService.VehicleID = :vehicle_id) ";
                $params['vehicle_id'] = $passedVehicleID;
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
                error_log('CCA\VehicleService->deleteItem() Unable to delete as no id set');

                return false;
            }
            
            
            //Now the actual record
            try {
                $stmt = $this->_dbconn->prepare("DELETE FROM VehicleService WHERE ID = :id LIMIT 1");
                $result = $stmt->execute([
                                             'id' => $id
                                         ]);
                if ($result === true) {
                    return true;
                }

            } catch (Exception $e) {
                error_log("CCA\VehicleService->deleteItem() Failed to delete record" . $e);
            }

            return false;
        }


        /**
         * Create new record stub
         */
        public function createNewItem()
        {
            try {
                $stmt = $this->_dbconn->prepare("INSERT INTO VehicleService SET Mileage = null");
                $stmt->execute();
                $id = $this->_dbconn->lastInsertId();
                
                //Store in the object
                $this->_id = $id;
                
            } catch (Exception $e) {
                error_log("CCA\VehicleService->createNewItem() Failed to create new stub".$e);
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
                $stmt = $this->_dbconn->prepare("UPDATE VehicleService SET VehicleID = :vehicle_id, ServiceDate = :service_date, Mileage = AES_ENCRYPT(:mileage, :key), Type = AES_ENCRYPT(:type, :key), Comments = AES_ENCRYPT(:comments, :key) WHERE ID = :id LIMIT 1");
                $result = $stmt->execute([
                    'key' => AES_ENCRYPTION_KEY,
                    'vehicle_id' => $this->_vehicle_id,
                    'service_date' => $this->_service_date,
                    'mileage' => $this->_mileage,
                    'type' => $this->_type,
                    'comments' => $this->_comments,
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
                error_log("CCA\VehicleService->saveItem() Failed to save record: " . $e);
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
                error_log('CCA\VehicleService->updateField() - requires record id to be passed');
                return false;
            }
            if (!is_string($field) || $field == '' ) {
                error_log('CCA\VehicleService->updateField() - requires field to be passed');
                return false;
            }
            
            //Check its not one of our special columns
            if ($field == 'ID') {
                error_log('CCA\VehicleService->updateField() - Attempted to update prohibited field');
                return false;
            }
            
            
            //Check if the field and record exists
            $stmt = $this->_dbconn->prepare("SELECT ID FROM VehicleService WHERE ID = :id LIMIT 1");
            $stmt->execute([
                'id' => $recid
            ]);
            $item = $stmt->fetch();
            
            if ($item['ID'] <= 0 ) {
                error_log('CCA\VehicleService->updateField() - Record does not exist');
                return false;
            }
            
            $stmt = $this->_dbconn->prepare("SHOW COLUMNS FROM VehicleService LIKE :field");
            $stmt->execute([
                'field' => $field
            ]);
            $items = $stmt->fetchAll();
            
            if (!is_array($items) || count($items) <= 0) {
                error_log('CCA\VehicleService->updateField() - Field does not exist');
                return false;
            }
            
            
            //Now carry out the update
            //Check if the field and record exists
            if ($field == 'VehicleID' || $field == 'ServiceDate') {
                //No encryption
                $sql = "UPDATE VehicleService SET ".$field." = :value WHERE ID = :id LIMIT 1";
                $params = array('id' => $recid, 'value' => $value);
            } else {
                $sql = "UPDATE VehicleService SET ".$field." = AES_ENCRYPT(:value, :key) WHERE ID = :id LIMIT 1";
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
        public function getServiceDate()
        {
            return $this->_service_date;
        }
        
        /**
         * @param mixed $service_date
         */
        public function setServiceDate($service_date): void
        {
            if ($service_date == ''|| $service_date == '0000-00-00') { $service_date = null; }
            $this->_service_date = $service_date;
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
            $this->_mileage = $mileage;
        }
        
        /**
         * @return mixed
         */
        public function getType()
        {
            return $this->_type;
        }
        
        /**
         * @param mixed $type
         */
        public function setType($type): void
        {
            $this->_type = $type;
        }
        
        /**
         * @return mixed
         */
        public function getComments()
        {
            return $this->_comments;
        }
        
        /**
         * @param mixed $comments
         */
        public function setComments($comments): void
        {
            $this->_comments = $comments;
        }
        
        public function getTypeOptions(): array
        {
            return $this->_type_options;
        }
        
        

    }