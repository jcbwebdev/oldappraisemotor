<?php
    
    namespace PeterBourneComms\CCA;
    
    use PDO;
    use Exception;
    use PeterBourneComms\CMS\Database;
    use PeterBourneComms\CMS\ImageHandler;
    use PeterBourneComms\CCA\User;
    
    /**
     * @package PeterBourneComms\CCA
     * @author  Peter Bourne
     * @version 1.0
     *
     *  Deals with CCA Auction Rooms
     *  Relies on AuctionRooms and CustomersByAuctionRoom tables in database
     *
     *  History
     *  18/03/2024   1.0     Initial version
     *
     *
     */
    //TODO: Come back and do all vehicle to Auction room stuff - on deletion etc. Most probably handled in Vehicle class or Auction class...?
    
    class AuctionRoom
    {
        protected $_dbconn;
        /**
         * @var int|string
         */
        protected $_id;
        /**
         * @var
         */
        protected $_title;
        protected $_content;
        /**
         * @var
         */
        protected $_img_filename;
        /**
         * @var string
         */
        protected $_img_path;
        /**
         * @var int|string
         */
        protected $_img_width;
        /**
         * @var int|string
         */
        protected $_img_height;
        
        
        public function __construct($id = null, $width = 600, $height = 600, $path = USER_UPLOADS . '/images/auction-room-logos/')
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
                throw new Exception('CCA\AuctionRoom->__construct() requires id to be specified as an integer - if it is specified at all');
            }

            //Assess passed width & height
            if (!is_numeric($width) || !is_numeric($height)) {
                throw new Exception('CCA\AuctionRoom->__construct() requires width and height to be specified as integers');
            }

            //Assess passed path
            if (isset($path) && !is_string($path)) {
                throw new Exception('CCA\AuctionRoom->__construct() requires path to be specified as a string, eg: /user_uploads/images/auction-room-logos/');
            }

            //See if provided path exists - if not - create it
            if (!file_exists(DOCUMENT_ROOT . $path)) {
                //Create it
                $success = mkdir(DOCUMENT_ROOT . $path, 0777, true);
                if (!$success) {
                    throw new Exception('CCA\AuctionRoom->__construct() Directory specified ('.$path.') does not exist - and cannot be created');
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
         * @param int $id
         *
         * @return array
         */
        public function getItemById($id = 0){
            if ($id == 0) {
                $id = $this->_id;
            }
            if (!is_numeric($id) || $id <= 0) {
                error_log('CCA\AuctionRoom->getItemById() Unable to retrieve details as no ID set');
                return false;
            }

            //Now retrieve the extra fields
            try {
                $stmt = $this->_dbconn->prepare("SELECT ID, AES_DECRYPT(Title, :key) AS Title, AES_DECRYPT(Content, :key) AS Content, AES_DECRYPT(ImgFilename, :key) AS ImgFilename, AES_DECRYPT(ImgPath, :key) AS ImgPath FROM AuctionRooms WHERE ID =  :id LIMIT 1");
                $stmt->execute([
                                   'key' => AES_ENCRYPTION_KEY,
                                   'id' => $id
                               ]);
                $item = $stmt->fetch(PDO::FETCH_ASSOC);

            } catch (Exception $e)
            {
                error_log("CCA\AuctionRoom->getItemById() Failed to retrieve auction room details" . $e);
            }

            //Store details in relevant customers
            $this->_id = $item['ID'];
            $this->_title = $item['Title'];
            $this->_content = $item['Content'];
            $this->_img_filename = $item['ImgFilename'];
            $this->_img_path = $item['ImgPath'];

            return $item;
        }


        /**
         * Function to return array of Customer records (as assoc array)
         *
         * @param string $passedNeedle
         * @param string $passedMode    Only accepts: email, surname [default]
         *
         * @return array
         */
        public function listAllItems($passedNeedle = '', $passedMode = '')
        {
            $basesql = "SELECT ID FROM AuctionRooms WHERE ";
            $params = array();

            //Build SQL depending on passedMode and passedNeedle
            switch($passedMode) {
                case 'title':
                    $query = "CONVERT(AES_DECRYPT(Title, :key) USING utf8) LIKE :needle ";
                    $order = "ORDER BY AES_DECRYPT(Title, :key) ASC";
                    $params['needle'] = "%".$passedNeedle."%";
                    $params['key'] = AES_ENCRYPTION_KEY;
                    break;

                default:
                    $query = "ID = ID ";
                    $order = "ORDER BY AES_DECRYPT(Title, :key) ASC";
                    $params['key'] = AES_ENCRYPTION_KEY;
                    break;
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
                throw new Exception("CCA\AuctionRoom->uploadImage() You must supply a file stream (data:image/png;base64) to this function.");
            }

            //Create ImageHandler
            $ImgObj = new ImageHandler($this->_img_path, false);
            //Set up some defaults
            $ImgObj->setSourceFileType('png');
            $ImgObj->setDestFileType('png');
            $ImgObj->setFlagMaintainTransparency(true);
            $ImgObj->setImageWidth($this->_img_width);
            $ImgObj->setImageHeight($this->_img_height);
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
                return "CCA\AuctionRoom->deleteImage() Sorry - there was no image to delete";
            }

            $OldImg = new ImageHandler($this->_img_path,false);
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
                error_log('CCA\AuctionRoom->deleteItem() Unable to delete customer as no id set');

                return false;
            }

            //Delete logo image
            $this->deleteImage();
            
            //Delete CustomersByAuctionRoom
            $stmt = $this->_dbconn->prepare("DELETE FROM CustomersByAuctionRoom WHERE CustomerID = :id");
            $result = $stmt->execute([
                'id' => $id
            ]);
            
            
            //Now the actual record
            try {
                $stmt = $this->_dbconn->prepare("DELETE FROM AuctionRooms WHERE ID = :id LIMIT 1");
                $result = $stmt->execute([
                                             'id' => $id
                                         ]);
                if ($result === true) {
                    return true;
                }

            } catch (Exception $e) {
                error_log("CCA\AuctionRoom->deleteItem() Failed to delete record" . $e);
            }

            return false;
        }


        /**
         * Create new record stub
         */
        public function createNewItem()
        {
            try {
                $stmt = $this->_dbconn->prepare("INSERT INTO AuctionRooms SET Title = null");
                $stmt->execute();
                $id = $this->_dbconn->lastInsertId();
                
                //Store in the object
                $this->_id = $id;
                
            } catch (Exception $e) {
                error_log("CCA\AuctionRoom->createNewItem() Failed to create new stub".$e);
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
                $stmt = $this->_dbconn->prepare("UPDATE AuctionRooms SET Title = AES_ENCRYPT(:title, :key), Content = AES_ENCRYPT(:content, :key), ImgPath = AES_ENCRYPT(:img_path, :key), ImgFilename = AES_ENCRYPT(:img_filename, :key) WHERE ID = :id LIMIT 1");
                $result = $stmt->execute([
                    'key' => AES_ENCRYPTION_KEY,
                    'title' => $this->_title,
                    'content' => $this->_content,
                    'img_path' => $this->_img_path,
                    'img_filename' => $this->_img_filename,
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
                error_log("CCA\AuctionRoom->saveItem() Failed to save AuctionRoom record: " . $e);
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
                error_log('CCA\AuctionRoom->updateField() - requires record id to be passed');
                return false;
            }
            if (!is_string($field) || $field == '' ) {
                error_log('CCA\AuctionRoom->updateField() - requires field to be passed');
                return false;
            }
            
            //Check its not one of our special columns
            if ($field == 'ID') {
                error_log('CCA\AuctionRoom->updateField() - Attempted to update prohibited field');
                return false;
            }
            
            
            //Check if the field and record exists
            $stmt = $this->_dbconn->prepare("SELECT ID FROM AuctionRooms WHERE ID = :id LIMIT 1");
            $stmt->execute([
                'id' => $recid
            ]);
            $item = $stmt->fetch();
            
            if ($item['ID'] <= 0 ) {
                error_log('CCA\AuctionRoom->updateField() - Record does not exist');
                return false;
            }
            
            $stmt = $this->_dbconn->prepare("SHOW COLUMNS FROM AuctionRooms LIKE :field");
            $stmt->execute([
                'field' => $field
            ]);
            $items = $stmt->fetchAll();
            
            if (!is_array($items) || count($items) <= 0) {
                error_log('CCA\AuctionRoom->updateField() - Field does not exist');
                return false;
            }
            
            
            //Now carry out the update
            //Check if the field and record exists
            /*if ($field == 'DateStart' || $field == 'DateApplicationDue' || $field == 'MES_MeetingID') {
                //No encryption
                $sql = "UPDATE Customers SET ".$field." = :value WHERE ID = :id LIMIT 1";
                $params = array('id' => $recid, 'value' => $value);
            } else {*/
                $sql = "UPDATE AuctionRooms SET ".$field." = AES_ENCRYPT(:value, :key) WHERE ID = :id LIMIT 1";
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
        public function getContent()
        {
            return $this->_content;
        }
        
        /**
         * @param mixed $content
         */
        public function setContent($content): void
        {
            $this->_content = $content;
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
        public function setImgFilename($img_filename): void
        {
            $this->_img_filename = $img_filename;
        }
        
        public function getImgPath(): string
        {
            return $this->_img_path;
        }
        
        public function setImgPath(string $img_path): void
        {
            $this->_img_path = $img_path;
        }

       




    }