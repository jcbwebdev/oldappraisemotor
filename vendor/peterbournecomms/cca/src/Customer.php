<?php
    
    namespace PeterBourneComms\CCA;
    
    use PDO;
    use Exception;
    use PeterBourneComms\CMS\Database;
    use PeterBourneComms\CMS\ImageHandler;
    use PeterBourneComms\CCA\User;
    use PeterBourneComms\CCA\AuctionRoom;
    
    /**
     * @package PeterBourneComms\CCA
     * @author  Peter Bourne
     * @version 1.1
     *
     *  Deals with CCA Customers
     *  Relies on Customers table in database
     *
     *  History
     *  14/03/2024   1.0     Initial version
     *  18/03/2024   1.1     Added AuctionRooms
     *
     */
    class Customer
    {
        protected $_dbconn;
        /**
         * @var int|string
         */
        protected $_id;
        /**
         * @var
         */
        protected $_company;
        /**
         * @var
         */
        //protected $_firstname;
        /**
         * @var
         */
        //protected $_surname;
        /**
         * @var
         */
        protected $_address1;
        /**
         * @var
         */
        protected $_address2;
        /**
         * @var
         */
        protected $_address3;
        /**
         * @var
         */
        protected $_town;
        /**
         * @var
         */
        protected $_county;
        /**
         * @var
         */
        protected $_postcode;
        /**
         * @var
         */
        protected $_tel;
        /**
         * @var
         */
        protected $_email;
        /**
         * @var
         */
        protected $_mobile;
        /**
         * @var
         */
        protected $_date_registered;
        /**
         * @var
         */
        protected $_last_edited_by;
        /**
         * @var
         */
        protected $_last_edited;
        /**
         * @var
         */
        protected $_status;
        /**
         * @var
         */
        protected $_img_filename;
        /**
         * @var string
         */
        protected $_img_path;
        /**
         * @var
         */
        protected $_location_info;

        /**
         * @var int|string
         */
        protected $_img_width;
        /**
         * @var int|string
         */
        protected $_img_height;
        /**
         * @var
         */
        protected $_thumb_width;
        /**
         * @var
         */
        protected $_thumb_height;
        protected $_csrf_token;
        
        protected $_auction_rooms;
        
        
        private $_status_options = array(array('Label' => 'Applied', 'Value' => 'Applied'), array('Label' => 'Active', 'Value' => 'Active'), array('Label' => 'Disabled', 'Value' => 'Disabled'));

        
        public function __construct($id = null, $width = 600, $height = 800, $path = USER_UPLOADS . '/images/customer-avatars/')
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
                throw new Exception('CCA\Customer->__construct() requires id to be specified as an integer - if it is specified at all');
            }

            //Assess passed width & height
            if (!is_numeric($width) || !is_numeric($height)) {
                throw new Exception('CCA\Customer->__construct() requires width and height to be specified as integers');
            }

            //Assess passed path
            if (isset($path) && !is_string($path)) {
                throw new Exception('CCA\Customer->__construct() requires path to be specified as a string, eg: /user_uploads/images/customer-avatars/');
            }

            //See if provided path exists - if not - create it
            if (!file_exists(DOCUMENT_ROOT . $path)) {
                //Create it
                $success = mkdir(DOCUMENT_ROOT . $path, 0777, true);
                if (!$success) {
                    throw new Exception('CCA\Customer->__construct() Directory specified ('.$path.') does not exist - and cannot be created');
                }
            }

            //Retrieve current customer information
            if (isset($id)) {
                $this->_id = $id;
                $this->getItemById($id);
            }

            //Store the width/height/path etc
            $this->_img_width = $width;
            $this->_img_height = $height;
            $this->_img_path = $path;
        }


        /**
         * @param int $customerid
         *
         * @return array
         */
        public function getItemById($customerid = 0){
            if ($customerid == 0) {
                $customerid = $this->_id;
            }
            if (!is_numeric($customerid) || $customerid <= 0) {
                error_log('CCA\Customer->getItemById() Unable to retrieve customer details as no customerID set');
                return false;
            }

            //Now retrieve the extra fields
            try {
                $stmt = $this->_dbconn->prepare("SELECT ID, AES_DECRYPT(Company, :key) AS Company, AES_DECRYPT(Address1, :key) AS Address1, AES_DECRYPT(Address2, :key) AS Address2, AES_DECRYPT(Address3, :key) AS Address3, AES_DECRYPT(Town, :key) AS Town, AES_DECRYPT(County, :key) AS County, AES_DECRYPT(Postcode, :key) AS Postcode, AES_DECRYPT(Tel, :key) AS Tel, AES_DECRYPT(Email, :key) AS Email, AES_DECRYPT(Mobile, :key) AS Mobile, LastEdited, AES_DECRYPT(LastEditedBy, :key) AS LastEditedBy, DateRegistered, AES_DECRYPT(ImgFilename, :key) AS ImgFilename, AES_DECRYPT(ImgPath, :key) AS ImgPath, AES_DECRYPT(`Status`, :key) AS `Status`, AES_DECRYPT(LocationInfo, :key) AS LocationInfo, AES_DECRYPT(CSRFToken, :key) AS CSRFToken FROM Customers WHERE ID =  :id LIMIT 1");
                $stmt->execute([
                                   'key' => AES_ENCRYPTION_KEY,
                                   'id' => $customerid
                               ]);
                $item = $stmt->fetch(PDO::FETCH_ASSOC);

            } catch (Exception $e)
            {
                error_log("CCA\Customer->getItemById() Failed to retrieve customer details" . $e);
            }

            //Store details in relevant customers
            $this->_id = $item['ID'];
            $this->_company = $item['Company'];
            /*$this->_firstname = $item['Firstname'];
            $this->_surname = $item['Surname'];*/
            $this->_address1 = $item['Address1'];
            $this->_address2 = $item['Address2'];
            $this->_address3 = $item['Address3'];
            $this->_town = $item['Town'];
            $this->_county = $item['County'];
            $this->_postcode = $item['Postcode'];
            $this->_tel = $item['Tel'];
            $this->_email = $item['Email'];
            $this->_mobile = $item['Mobile'];
            $this->_date_registered = $item['DateRegistered'];
            $this->_last_edited = $item['LastEdited'];
            $this->_last_edited_by = $item['LastEditedBy'];
            $this->_status = $item['Status'];
            $this->_img_filename = $item['ImgFilename'];
            $this->_location_info = $item['LocationInfo'];
            $this->_img_path = $item['ImgPath'];
            $this->_csrf_token = $item['CSRFToken'];

            //Users
            $UO = new User();
            if (is_object($UO)) {
               $Users = $UO->listAllItems($this->_id, 'customer-id');
               if (is_array($Users) && count($Users) > 0) {
                   $item['Users'] = $Users;
               }
            }
            
            //Update the auction rooms for this item
            $auctionrooms = $this->populateAuctionRooms();
            $item['AuctionRooms'] = $auctionrooms;

            return $item;
        }
        
        /**
         * Retrieves all AuctionRooms table data from the database
         *
         * @return mixed
         */
        public function getAllAuctionRooms()
        {
           $ARO = new AuctionRoom();
           if (is_object($ARO)) {
               $AuctionRooms = $ARO->listAllItems();
               if (is_array($AuctionRooms) && count($AuctionRooms) > 0) {
                   return $AuctionRooms;
               } else {
                   return false;
               }
           }
           return false;
        }
        
        
        /**
         * Checks the CustomersByAuctionRoom table - with the two provided IDs to see if a record exists.
         * Return true if it does
         *
         * @param $customer_id
         * @param $auction_room_id
         *
         * @return bool
         * @throws Exception
         */
        public function checkAuctionRoomMatch($customer_id, $auction_room_id)
        {
            if (is_numeric($customer_id) && is_numeric($auction_room_id) && $customer_id > 0 && $auction_room_id > 0) {
                try {
                    $stmt = $this->_dbconn->prepare("SELECT ID FROM CustomersByAuctionRoom WHERE CustomerID = :customer_id AND AuctionRoomID = :auction_room_id LIMIT 1");
                    $stmt->execute([
                        'customer_id' => $customer_id,
                        'auction_room_id' => $auction_room_id
                    ]);
                    $match = $stmt->fetch();
                    if (is_array($match) && $match['ID'] >= 1) { return true; } else { return false; }
                } catch (Exception $e) {
                    error_log("CCA\Customer->checkAuctionRoomMatch() Failed to retrieve Customer records" . $e);
                    return false;
                }
            } else {
                return false;
            }
        }
        
        
        
        /**
         * Populates the _auction_rooms property of the object with an array of the AuctionRoomIDs
         */
        public function populateAuctionRooms()
        {
            $id = $this->_id;
            
            try {
                $stmt = $this->_dbconn->prepare("SELECT AuctionRoomID FROM CustomersByAuctionRoom WHERE CustomerID = :customer_id");
                $stmt->execute([
                    'customer_id' => $id
                ]);
                $sections = $stmt->fetchAll();
            } catch (Exception $e) {
                error_log("CCA\Customer->populateAuctionRooms() Failed to save record: " . $e);
            }
            
            $this->_auction_rooms = $sections;
            
            return $sections;
        }
        
        /**
         * Takes the content of the _auction_rooms property and updates the CustomersByAuctionRoom table
         *
         */
        public function updateAuctionRooms()
        {
            /*print_r($this->_auction_rooms);
            die();*/
            if (is_array($this->_auction_rooms) && $this->_id > 0) {
                //First we need to delete all entries for this content
                try {
                    $stmt = $this->_dbconn->prepare("DELETE FROM CustomersByAuctionRoom WHERE CustomerID = :customer_id");
                    $stmt->execute([
                        'customer_id' => $this->_id
                    ]);
                } catch (Exception $e) {
                    error_log("CCA\Customer->updateAuctionRooms() Failed to delete records.");
                }
                
                //Then we need to post the new records
                //reset($this_>_categories);
                $stmt = $this->_dbconn->prepare("INSERT INTO CustomersByAuctionRoom SET CustomerID = :customer_id, AuctionRoomID = :auction_room_id");
                for ($i=0; $i < count($this->_auction_rooms); $i++)
                {
                    $stmt->execute([
                        'customer_id' => $this->_id,
                        'auction_room_id' => $this->_auction_rooms[$i]
                    ]);
                }
            }
        }
        
        

        /**
         * Function to return array of Customer records (as assoc array)
         *
         * @param string $passedNeedle
         * @param string $passedMode    Only accepts: email, surname [default]
         *
         * @return array
         */
        public function listAllItems($passedNeedle = '', $passedMode = 'company', $sortorder = null, $ignoreapplicants = null)
        {
            $basesql = "SELECT DISTINCT Customers.ID, Customers.DateRegistered, AES_DECRYPT(Customers.Company, :key)  FROM Customers WHERE ";
            $params = array();

            //Build SQL depending on passedMode and passedNeedle
            switch($passedMode)
            {
                case 'email':
                    $query = "(CONVERT(AES_DECRYPT(Customers.Email, :key) USING utf8) LIKE :needle) ";
                    $order = "ORDER BY AES_DECRYPT(Customers.Email, :key) ASC";
                    $params['needle'] = "%".$passedNeedle."%";
                    $params['key'] = AES_ENCRYPTION_KEY;
                    break;

                case 'tel':
                    $query = "(CONVERT(AES_DECRYPT(Customers.Tel, :key) USING utf8) LIKE :needle) ";
                    $order = "ORDER BY AES_DECRYPT(Customers.Company, :key) ASC";
                    $params['needle'] = $passedNeedle."%";
                    $params['key'] = AES_ENCRYPTION_KEY;
                    break;
                
                case 'new':
                    $query = "(CONVERT(AES_DECRYPT(Customers.`Status`, :key) USING utf8) = 'Applied') ";
                    $order = "ORDER BY Customers.DateRegistered DESC, AES_DECRYPT(Customers.Company, :key) ASC";
                    $params['key'] = AES_ENCRYPTION_KEY;
                    break;
                    
                case 'auction-room':
                    $basesql = "SELECT DISTINCT Customers.ID, Customers.DateRegistered, AES_DECRYPT(Customers.Company, :key) FROM Customers LEFT JOIN CustomersByAuctionRoom ON CustomersByAuctionRoom.CustomerID = Customers.ID LEFT JOIN AuctionRooms ON AuctionRooms.ID = CustomersByAuctionRoom.AuctionRoomID WHERE ";
                    $query = "(CONVERT(AES_DECRYPT(AuctionRooms.Title, :key) USING utf8) LIKE :needle) ";
                    $order = "ORDER BY AES_DECRYPT(Customers.Company, :key) ASC";
                    $params['needle'] = "%".$passedNeedle."%";
                    $params['key'] = AES_ENCRYPTION_KEY;
                    break;
                
                case 'auction-room-id':
                    $basesql = "SELECT DISTINCT Customers.ID, Customers.DateRegistered, AES_DECRYPT(Customers.Company, :key) FROM Customers LEFT JOIN CustomersByAuctionRoom ON CustomersByAuctionRoom.CustomerID = Customers.ID LEFT JOIN AuctionRooms ON AuctionRooms.ID = CustomersByAuctionRoom.AuctionRoomID WHERE ";
                    $query = "(CustomersByAuctionRoom.AuctionRoomID = :needle) ";
                    $order = "ORDER BY AES_DECRYPT(Customers.Company, :key) ASC";
                    $params['needle'] = $passedNeedle;
                    $params['key'] = AES_ENCRYPTION_KEY;
                    break;
                    
                case 'company-fuzzy':
                    //Just take the first 2 words and display similar
                    //Chunk down the string first
                    $words = explode(" ",$passedNeedle);
                    if (is_array($words) && count($words) > 2) {
                        $passedNeedle = $words[0]." ".$words[1]."%";
                    }
                    $query = "(CONVERT(AES_DECRYPT(Customers.Company, :key) USING utf8) LIKE :needle) ";
                    $order = "ORDER BY AES_DECRYPT(Customers.Company, :key) ASC";
                    $params['needle'] = $passedNeedle."%";
                    $params['key'] = AES_ENCRYPTION_KEY;
                    break;
                    
                case 'name-email':
                    //Used to check company name AND/OR ALL User surnames
                    $basesql = "SELECT DISTINCT Customers.ID, Customers.DateRegistered, AES_DECRYPT(Customers.Company, :key) FROM Customers LEFT JOIN Users ON Users.CustomerID = Customers.ID WHERE ";
                    $query = "(CONVERT(AES_DECRYPT(Customers.Company, :key) USING utf8) LIKE :needle OR CONVERT(AES_DECRYPT(Users.Surname, :key) USING utf8) LIKE :needle OR CONVERT(AES_DECRYPT(Customers.Email, :key) USING utf8) LIKE :needle OR CONVERT(AES_DECRYPT(Users.Email, :key) USING utf8) LIKE :needle) ";
                    $order = "ORDER BY AES_DECRYPT(Customers.Company, :key) ASC";
                    $params['needle'] = "%".$passedNeedle."%";
                    $params['key'] = AES_ENCRYPTION_KEY;
                    break;

                default:
                    $query = "(CONVERT(AES_DECRYPT(Customers.Company, :key) USING utf8) LIKE :needle) ";
                    $order = "ORDER BY AES_DECRYPT(Customers.Company, :key) ASC";
                    $params['needle'] = $passedNeedle."%";
                    $params['key'] = AES_ENCRYPTION_KEY;
                    break;
            }

            if ($sortorder != '') {
                switch ($sortorder) {
                    case 'company':
                        $order = " ORDER BY AES_DECRYPT(Customers.Company, :key) ASC";
                        $params['key'] = AES_ENCRYPTION_KEY;
                        break;
                    case 'date-registered-desc':
                        $order = " ORDER BY Customers.DateRegistered DESC";
                        break;
                    case 'date-registered-asc':
                        $order = " ORDER BY Customers.DateRegistered ASC";
                        break;
                }
            }
            
            if (isset($ignoreapplicants) && $ignoreapplicants === true) {
                $query .= " AND CONVERT(AES_DECRYPT(Customers.Status, :key) USING utf8) != 'Applied' ";
                $params['key'] = AES_ENCRYPTION_KEY;
            }

            //Carry out the query
            //echo $basesql.$query.$order;
            //print_r($params);
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
         * Receive an image data stream
         *  - process the image - width, height and path need to be specified at creation
         *  - store it in the file system
         *  - store it in the new object ahead of saving
         *  - save the object
         *
         * @param     $ImageStream
         *
         * @throws Exception
         */
        public function uploadImage($ImageStream)
        {
            //Check we have some useful data passed
            if ($ImageStream == '') {
                throw new Exception("CCA\Customer->uploadImage() You must supply a file stream (data:image/png;base64) to this function.");
            }

            //Create ImageHandler
            $ImgObj = new ImageHandler($this->_img_path, true);
            //Set up some defaults
            $ImgObj->setSourceFileType('png');
            $ImgObj->setDestFileType('png');
            $ImgObj->setFlagMaintainTransparency(true);
            $ImgObj->setImageWidth($this->_img_width);
            $ImgObj->setImageHeight($this->_img_height);
            $ImgObj->setThumbWidth((int)($this->_img_width/2));
            $ImgObj->setThumbHeight((int)($this->_img_height/2));
            $ImgObj->createFilename();


            $result = $ImgObj->processImage($ImageStream);
            $newFilename = $ImgObj->getImgFilename();

            if ($result === true) {
                //Delete the old image
                $this->deleteImage();

                //Store the new filename
                $this->_img_filename = $newFilename;

                //Save the object in its current state
                $this->saveItem();
            }
        }

        /**
         * Delete the image for this customer- assuming _img_filename is set
         *
         * @return mixed
         */
        public function deleteImage()
        {
            if (!is_string($this->_img_filename) || $this->_img_filename == '') {
                return "CCA\Customer->deleteImage() Sorry - there was no image to delete";
            }

            $OldImg = new ImageHandler($this->_img_path,true);
            $OldImg->setImgFilename($this->_img_filename);
            $OldImg->deleteImage();

            $this->_img_filename = '';
            $this->saveItem();
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
                error_log('CCA\Customer->deleteItem() Unable to delete customer as no id set');

                return false;
            }

            //Delete logo image
            $this->deleteImage();
            
            //Delete Users?
            $stmt = $this->_dbconn->prepare("DELETE FROM Users WHERE CustomerID = :id");
            $result = $stmt->execute([
                'id' => $id
            ]);
            
            //Delete from CustomersByAuctionRoom
            $stmt = $this->_dbconn->prepare("DELETE FROM CustomersByAuctionRoom WHERE CustomerID = :id");
            $result = $stmt->execute([
                'id' => $id
            ]);
            
            
            //Now the actual customerrecord
            try {
                $stmt = $this->_dbconn->prepare("DELETE FROM Customers WHERE ID = :id LIMIT 1");
                $result = $stmt->execute([
                                             'id' => $id
                                         ]);
                if ($result === true) {
                    return true;
                }

            } catch (Exception $e) {
                error_log("CCA\Customer->deleteItem() Failed to delete Customer.php" . $e);
            }

            return false;
        }


        /**
         * Create new record stub
         */
        public function createNewItem()
        {
            try {
                $stmt = $this->_dbconn->prepare("INSERT INTO Customers SET Company = null, LastEdited = NOW()");
                $stmt->execute();
                $id = $this->_dbconn->lastInsertId();
                
                //Store in the object
                $this->_id = $id;
                
            } catch (Exception $e) {
                error_log("CCA\Customer->createNewItem() Failed to create new Customer stub".$e);
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
                $stmt = $this->_dbconn->prepare("UPDATE Customers SET Company = AES_ENCRYPT(:company, :key), Address1 = AES_ENCRYPT(:address1, :key), Address2 = AES_ENCRYPT(:address2, :key), Address3 = AES_ENCRYPT(:address3, :key), Town = AES_ENCRYPT(:town, :key), County = AES_ENCRYPT(:county, :key), Postcode = AES_ENCRYPT(:postcode, :key), Tel = AES_ENCRYPT(:tel, :key), Email = AES_ENCRYPT(:email, :key), Mobile = AES_ENCRYPT(:mobile, :key), DateRegistered = :dateregistered,  LastEdited = NOW(), LastEditedBy = AES_ENCRYPT(:lasteditedby, :key),  `Status` = AES_ENCRYPT(:status, :key), ImgFilename = AES_ENCRYPT(:imgfilename, :key), ImgPath = AES_ENCRYPT(:imgpath, :key), LocationInfo = AES_ENCRYPT(:locationinfo, :key), CSRFToken = AES_ENCRYPT(:csrftoken, :key) WHERE ID = :customerid LIMIT 1");
                $result = $stmt->execute([
                                             'key' => AES_ENCRYPTION_KEY,
                                             'company' => $this->_company,
                                             'address1' => $this->_address1,
                                             'address2' => $this->_address2,
                                             'address3' => $this->_address3,
                                             'town' => $this->_town,
                                             'county' => $this->_county,
                                             'postcode' => $this->_postcode,
                                             'tel' => $this->_tel,
                                             'email' => $this->_email,
                                             'mobile' => $this->_mobile,
                                             'dateregistered' => $this->_date_registered,
                                             'lasteditedby' => $this->_last_edited_by,
                                             'status' => $this->_status,
                                             'imgfilename' => $this->_img_filename,
                                             'imgpath' => $this->_img_path,
                                             'locationinfo' => $this->_location_info,
                                             'csrftoken' => $this->_csrf_token,
                                             'customerid' => $this->_id
                                         ]);
                if ($result == true) {
                    //Update AuctionRooms
                    $this->updateAuctionRooms();
                    return true;
                }
                else {
                    error_log($stmt->errorInfo()[2]);
                    return false;
                }
            } catch (Exception $e) {
                error_log("CCA\Customer->saveItem() Failed to save Customer record: " . $e);
            }

            return false;
        }


        


        public function createCSRFToken() {
            if (function_exists('mcrypt_create_iv')) {
                return bin2hex(mcrypt_create_iv(32, MCRYPT_DEV_URANDOM));
            } else {
                return bin2hex(openssl_random_pseudo_bytes(32));
            }
        }
        
        
        
        /**
         * Update the specified field
         *
         * Need to check in the schema if the field exists. If not, fail.
         *
         */
        public function updateField($field, $value, $recid) {
            if (!is_numeric($recid)) {
                error_log('CCA\Customer->updateField() - requires record id to be passed');
                return false;
            }
            if (!is_string($field) || $field == '' ) {
                error_log('CCA\Customer->updateField() - requires field to be passed');
                return false;
            }
            
            //Check its not one of our special columns
            if ($field == 'ID') {
                error_log('CCA\Customer->updateField() - Attempted to update prohibited field');
                return false;
            }
            
            
            //Check if the field and record exists
            $stmt = $this->_dbconn->prepare("SELECT ID FROM Customers WHERE ID = :id LIMIT 1");
            $stmt->execute([
                'id' => $recid
            ]);
            $item = $stmt->fetch();
            
            if ($item['ID'] <= 0 ) {
                error_log('CCA\Customer->updateField() - Record does not exist');
                return false;
            }
            
            $stmt = $this->_dbconn->prepare("SHOW COLUMNS FROM Customers LIKE :field");
            $stmt->execute([
                'field' => $field
            ]);
            $items = $stmt->fetchAll();
            
            if (!is_array($items) || count($items) <= 0) {
                error_log('CCA\Customer->updateField() - Field does not exist');
                return false;
            }
            
            
            //Now carry out the update
            //Check if the field and record exists
            /*if ($field == 'DateStart' || $field == 'DateApplicationDue' || $field == 'MES_MeetingID') {
                //No encryption
                $sql = "UPDATE Customers SET ".$field." = :value WHERE ID = :id LIMIT 1";
                $params = array('id' => $recid, 'value' => $value);
            } else {*/
                $sql = "UPDATE Customers SET ".$field." = AES_ENCRYPT(:value, :key) WHERE ID = :id LIMIT 1";
                $params = array('id' => $recid, 'key' => AES_ENCRYPTION_KEY, 'value' => $value);
            //}
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
        public function getCompany()
        {
            return $this->_company;
        }

        /**
         * @param mixed $company
         */
        public function setCompany($company)
        {
            $this->_company = $company;

            return $this;
        }
        

        /**
         * @return mixed
         */
        public function getAddress1()
        {
            return $this->_address1;
        }

        /**
         * @param mixed $address1
         */
        public function setAddress1($address1)
        {
            $this->_address1 = $address1;

            return $this;
        }

        /**
         * @return mixed
         */
        public function getAddress2()
        {
            return $this->_address2;
        }

        /**
         * @param mixed $address2
         */
        public function setAddress2($address2)
        {
            $this->_address2 = $address2;

            return $this;
        }

        /**
         * @return mixed
         */
        public function getAddress3()
        {
            return $this->_address3;
        }

        /**
         * @param mixed $address3
         */
        public function setAddress3($address3)
        {
            $this->_address3 = $address3;

            return $this;
        }

        /**
         * @return mixed
         */
        public function getTown()
        {
            return $this->_town;
        }

        /**
         * @param mixed $town
         */
        public function setTown($town)
        {
            $this->_town = $town;

            return $this;
        }

        /**
         * @return mixed
         */
        public function getCounty()
        {
            return $this->_county;
        }

        /**
         * @param mixed $county
         */
        public function setCounty($county)
        {
            $this->_county = $county;

            return $this;
        }

        /**
         * @return mixed
         */
        public function getPostcode()
        {
            return $this->_postcode;
        }

        /**
         * @param mixed $postcode
         */
        public function setPostcode($postcode)
        {
            $this->_postcode = $postcode;

            return $this;
        }

        /**
         * @return mixed
         */
        public function getTel()
        {
            return $this->_tel;
        }

        /**
         * @param mixed $tel
         */
        public function setTel($tel)
        {
            $this->_tel = $tel;

            return $this;
        }

        /**
         * @return mixed
         */
        public function getEmail()
        {
            return $this->_email;
        }

        /**
         * @param mixed $email
         */
        public function setEmail($email)
        {
            $this->_email = $email;

            return $this;
        }

        /**
         * @return mixed
         */
        public function getMobile()
        {
            return $this->_mobile;
        }

        /**
         * @param mixed $mobile
         */
        public function setMobile($mobile)
        {
            $this->_mobile = $mobile;

            return $this;
        }

        /**
         * @return mixed
         */
        public function getDateRegistered()
        {
            return $this->_date_registered;
        }

        /**
         * @param mixed $date_registered
         */
        public function setDateRegistered($date_registered)
        {
            if ($date_registered == '0000-00-00' || $date_registered == '0000-00-00 00:00:00' || $date_registered == '') { $date_registered = null; }
            $this->_date_registered = $date_registered;

            return $this;
        }

        /**
         * @return mixed
         */
        public function getLastEditedBy()
        {
            return $this->_last_edited_by;
        }

        /**
         * @param mixed $last_edited_by
         */
        public function setLastEditedBy($last_edited_by)
        {
            $this->_last_edited_by = $last_edited_by;

            return $this;
        }

        /**
         * @return mixed
         */
        public function getLastEdited()
        {
            return $this->_last_edited;
        }

        /**
         * @param mixed $last_edited
         */
        public function setLastEdited($last_edited)
        {
            if ($last_edited == '0000-00-00' || $last_edited == '0000-00-00 00:00:00' || $last_edited == '') { $last_edited = null; }
            $this->_last_edited = $last_edited;

            return $this;
        }

        

        /**
         * @return mixed
         */
        public function getStatus()
        {
            return $this->_status;
        }

        /**
         * @param mixed $status
         */
        public function setStatus($status)
        {
            $this->_status = $status;

            return $this;
        }

        /**
         * @return mixed
         */
        public function getImgFilename()
        {
            return $this->_img_filename;
        }

        /**
         * @param mixed $img_filename
         */
        public function setImgFilename($img_filename)
        {
            $this->_img_filename = $img_filename;

            return $this;
        }

        /**
         * @return string
         */
        public function getImgPath()
        {
            return $this->_img_path;
        }

        /**
         * @param string $img_path
         */
        public function setImgPath($img_path)
        {
            $this->_img_path = $img_path;

            return $this;
        }

        /**
         * @return int|string
         */
        public function getImgWidth()
        {
            return $this->_img_width;
        }

        /**
         * @param int|string $image_width
         */
        public function setImgWidth($image_width)
        {
            $this->_img_width = $image_width;

            return $this;
        }

        /**
         * @return int|string
         */
        public function getImgHeight()
        {
            return $this->_img_height;
        }

        /**
         * @param int|string $image_height
         */
        public function setImgHeight($image_height)
        {
            $this->_img_height = $image_height;

            return $this;
        }

        /**
         * @return mixed
         */
        public function getThumbWidth()
        {
            return $this->_thumb_width;
        }

        /**
         * @param mixed $thumb_width
         */
        public function setThumbWidth($thumb_width)
        {
            $this->_thumb_width = $thumb_width;

            return $this;
        }

        /**
         * @return mixed
         */
        public function getThumbHeight()
        {
            return $this->_thumb_height;
        }

        /**
         * @param mixed $thumb_height
         */
        public function setThumbHeight($thumb_height)
        {
            $this->_thumb_height = $thumb_height;

            return $this;
        }

        /**
         * @return mixed
         */
        public function getLocationInfo()
        {
            return $this->_location_info;
        }

        /**
         * @param mixed $location_info
         */
        public function setLocationInfo($location_info)
        {
            $this->_location_info = $location_info;

            return $this;
        }

        /**
         * @return mixed
         */
        public function getCsrfToken()
        {
            return $this->_csrf_token;
        }

        /**
         * @param mixed $csrf_token
         * @return TVA_Customer
         */
        public function setCsrfToken($csrf_token)
        {
            $this->_csrf_token = $csrf_token;
            return $this;
        }
        
        /**
         * @return mixed
         */
        public function getAuctionRooms()
        {
            return $this->_auction_rooms;
        }
        
        /**
         * @param mixed $auction_rooms
         */
        public function setAuctionRooms($auction_rooms): void
        {
            $this->_auction_rooms = $auction_rooms;
            $this->updateAuctionRooms();
        }
        
        public function getStatusOptions(): array
        {
            return $this->_status_options;
        }




    }