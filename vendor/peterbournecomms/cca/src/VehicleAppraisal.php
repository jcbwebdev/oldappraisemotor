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
     *  Relies on VehicleAppraisal in database
     *
     *  History
     *  23/04/2024   1.0     Initial version
     *  11/06/2024   1.1     Added AppraisalItems
     *
     */
    
    class VehicleAppraisal
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
        protected $_code;
        protected $_title;
        protected $_loc_x;
        protected $_loc_y;
        
        private $_appraisal_items = array(
            'S'=> array('Code'=>'S','Type'=>'Scuff','Icon'=>'/assets/img/icons/appraisal-s.svg'),
            'MS' => array('Code'=>'MS','Type'=>'Multiple Scuffs','Icon'=>'/assets/img/icons/appraisal-ms.svg'),
            'D' => array('Code'=>'D','Type'=>'Ding','Icon'=>'/assets/img/icons/appraisal-d.svg'),
            'MD' => array('Code'=>'MD','Type'=>'Multiple Dings','Icon'=>'/assets/img/icons/appraisal-md.svg'),
            'Ch' => array('Code'=>'Ch','Type'=>'Chip','Icon'=>'/assets/img/icons/appraisal-ch.svg'),
            'MCh' =>array('Code'=>'MCh','Type'=>'Multiple Chips','Icon'=>'/assets/img/icons/appraisal-mch.svg'),
            'Scr' =>array('Code'=>'Scr','Type'=>'Scratch','Icon'=>'/assets/img/icons/appraisal-scr.svg'),
            'SR' => array('Code'=>'SR','Type'=>'Smart Repair','Icon'=>'/assets/img/icons/appraisal-sr.svg'),
            'Cr' => array('Code'=>'Cr','Type'=>'Crack','Icon'=>'/assets/img/icons/appraisal-cr.svg'),
            'B' => array('Code'=>'B','Type'=>'Broken','Icon'=>'/assets/img/icons/appraisal-b.svg'),
            'T' => array('Code'=>'T','Type'=>'Torn','Icon'=>'/assets/img/icons/appraisal-t.svg'),
            'PR' => array('Code'=>'PR','Type'=>'Previous Repair','Icon'=>'/assets/img/icons/appraisal-pr.svg')
        );
        
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
                throw new Exception('CCA\VehicleAppraisal->__construct() requires id to be specified as an integer - if it is specified at all');
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
                error_log('CCA\VehicleAppraisal->getItemById() Unable to retrieve details as no ID set');
                return false;
            }

            //Now retrieve the extra fields
            try {
                $stmt = $this->_dbconn->prepare("SELECT ID, VehicleID, AES_DECRYPT(Code, :key) AS Code, AES_DECRYPT(Title, :key) AS Title, AES_DECRYPT(LocX, :key) AS LocX, AES_DECRYPT(LocY, :key) AS LocY FROM VehicleAppraisal WHERE ID =  :id LIMIT 1");
                $stmt->execute([
                                   'key' => AES_ENCRYPTION_KEY,
                                   'id' => $id
                               ]);
                $item = $stmt->fetch(PDO::FETCH_ASSOC);

            } catch (Exception $e)
            {
                error_log("CCA\VehicleAppraisal->getItemById() Failed to retrieve details" . $e);
            }

            //Store details in relevant customers
            $this->_id = $item['ID'];
            $this->_vehicle_id = $item['VehicleID'];
            $this->_code = $item['Code'];
            $this->_title = $item['Title'];
            $this->_loc_x = $item['LocX'];
            $this->_loc_y = $item['LocY'];
            
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
            $basesql = "SELECT ID FROM VehicleAppraisal WHERE ";
            $order = " ORDER BY VehicleAppraisal.ID ASC";
            $params = array();

            //Build SQL depending on passedMode and passedNeedle
            switch($passedMode) {
                case 'title':
                    $query = "CONVERT(AES_DECRYPT(VehicleAppraisal.Title, :key) USING utf8) LIKE :needle ";
                    $order = "ORDER BY AES_DECRYPT(VehicleAppraisal.Title, :key) ASC";
                    $params['needle'] = $passedNeedle."%";
                    $params['key'] = AES_ENCRYPTION_KEY;
                    break;
                
                case 'code':
                    $query = "CONVERT(AES_DECRYPT(VehicleAppraisal.Code, :key) USING utf8) LIKE :needle ";
                    $order = "ORDER BY AES_DECRYPT(VehicleAppraisal.Code, :key) ASC";
                    $params['needle'] = $passedNeedle."%";
                    $params['key'] = AES_ENCRYPTION_KEY;
                    break;
                    
                case 'vehicle-id':
                    $query = "VehicleAppraisal.VehicleID = :needle ";
                    $params['needle'] = $passedNeedle;
                    break;
                
                default:
                    $query = "ID = ID ";
                    break;
            }
            
            
            //OPTIONS
            if (isset($passedVehicleID) && is_numeric($passedVehicleID) && $passedVehicleID > 0) {
                $query .= " AND (VehicleAppraisal.VehicleID = :vehicle_id) ";
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
                error_log('CCA\VehicleAppraisal->deleteItem() Unable to delete as no id set');

                return false;
            }
            
            
            //Now the actual record
            try {
                $stmt = $this->_dbconn->prepare("DELETE FROM VehicleAppraisal WHERE ID = :id LIMIT 1");
                $result = $stmt->execute([
                                             'id' => $id
                                         ]);
                if ($result === true) {
                    return true;
                }

            } catch (Exception $e) {
                error_log("CCA\VehicleAppraisal->deleteItem() Failed to delete record" . $e);
            }

            return false;
        }


        /**
         * Create new record stub
         */
        public function createNewItem()
        {
            try {
                $stmt = $this->_dbconn->prepare("INSERT INTO VehicleAppraisal SET Title = null");
                $stmt->execute();
                $id = $this->_dbconn->lastInsertId();
                
                //Store in the object
                $this->_id = $id;
                
            } catch (Exception $e) {
                error_log("CCA\VehicleAppraisal->createNewItem() Failed to create new stub".$e);
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
                $stmt = $this->_dbconn->prepare("UPDATE VehicleAppraisal SET VehicleID = :vehicle_id, Code = AES_ENCRYPT(:code, :key), Title = AES_ENCRYPT(:title, :key), LocX = AES_ENCRYPT(:loc_x, :key), LocY = AES_ENCRYPT(:loc_y, :key) WHERE ID = :id LIMIT 1");
                $result = $stmt->execute([
                    'key' => AES_ENCRYPTION_KEY,
                    'vehicle_id' => $this->_vehicle_id,
                    'code' => $this->_code,
                    'title' => $this->_title,
                    'loc_x' => $this->_loc_x,
                    'loc_y' => $this->_loc_y,
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
                error_log("CCA\VehicleAppraisal->saveItem() Failed to save record: " . $e);
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
                error_log('CCA\VehicleAppraisal->updateField() - requires record id to be passed');
                return false;
            }
            if (!is_string($field) || $field == '' ) {
                error_log('CCA\VehicleAppraisal->updateField() - requires field to be passed');
                return false;
            }
            
            //Check its not one of our special columns
            if ($field == 'ID') {
                error_log('CCA\VehicleAppraisal->updateField() - Attempted to update prohibited field');
                return false;
            }
            
            
            //Check if the field and record exists
            $stmt = $this->_dbconn->prepare("SELECT ID FROM VehicleAppraisal WHERE ID = :id LIMIT 1");
            $stmt->execute([
                'id' => $recid
            ]);
            $item = $stmt->fetch();
            
            if ($item['ID'] <= 0 ) {
                error_log('CCA\VehicleAppraisal->updateField() - Record does not exist');
                return false;
            }
            
            $stmt = $this->_dbconn->prepare("SHOW COLUMNS FROM VehicleAppraisal LIKE :field");
            $stmt->execute([
                'field' => $field
            ]);
            $items = $stmt->fetchAll();
            
            if (!is_array($items) || count($items) <= 0) {
                error_log('CCA\VehicleAppraisal->updateField() - Field does not exist');
                return false;
            }
            
            
            //Now carry out the update
            //Check if the field and record exists
            if ($field == 'VehicleID') {
                //No encryption
                $sql = "UPDATE VehicleAppraisal SET ".$field." = :value WHERE ID = :id LIMIT 1";
                $params = array('id' => $recid, 'value' => $value);
            } else {
                $sql = "UPDATE VehicleAppraisal SET ".$field." = AES_ENCRYPT(:value, :key) WHERE ID = :id LIMIT 1";
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
        public function getCode()
        {
            return $this->_code;
        }
        
        /**
         * @param mixed $code
         */
        public function setCode($code): void
        {
            $this->_code = $code;
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
        public function setTitle($title): void
        {
            $this->_title = $title;
        }
        
        /**
         * @return mixed
         */
        public function getLocX()
        {
            return $this->_loc_x;
        }
        
        /**
         * @param mixed $loc_x
         */
        public function setLocX($loc_x): void
        {
            $this->_loc_x = $loc_x;
        }
        
        /**
         * @return mixed
         */
        public function getLocY()
        {
            return $this->_loc_y;
        }
        
        /**
         * @param mixed $loc_y
         */
        public function setLocY($loc_y): void
        {
            $this->_loc_y = $loc_y;
        }
        
        public function getAppraisalItems(): array
        {
            return $this->_appraisal_items;
        }
        

    }