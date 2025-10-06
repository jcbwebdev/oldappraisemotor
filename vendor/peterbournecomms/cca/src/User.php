<?php
    
    namespace PeterBourneComms\CCA;
    
    use PDO;
    use Exception;
    use PeterBourneComms\CMS\Database;
    use PeterBourneComms\CMS\ImageHandler;
    use PeterBourneComms\CMS\PBEmail;
    
    /**
     * @package PeterBourneComms\CCA
     * @author  Peter Bourne
     * @version 1.0
     *
     *  Deals with CCA Users
     *  Relies on Users table in database
     *
     *  History
     *  14/03/2024   1.0     Initial version
     *
     *
     */
    class User
    {
        protected $_dbconn;
        /**
         * @var int|string
         */
        protected $_id;
        protected $_customer_id;
        /**
         * @var
         */
        protected $_title;
        /**
         * @var
         */
        protected $_firstname;
        /**
         * @var
         */
        protected $_surname;
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
        protected $_password;
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
        protected $_admin_level;
        protected $_last_logged_in;
        protected $_status;
        
        private $_title_options = array(array('Value' => 'Mr', 'Label' => 'Mr'), array('Value' => 'Mrs', 'Label' => 'Mrs'), array('Value' => 'Miss', 'Label' => 'Miss'), array('Value' => 'Ms', 'Label' => 'Ms'), array('Value' => 'Dr', 'Label' => 'Dr'), array('Value' => 'Professor', 'Label' => 'Professor'));
        private $_admin_level_options = array(array('Label' => 'None', 'Value' => ''), array('Label' => 'Full Admin', 'Value' => 'F'));
        private $_status_options = array(array('Label' => 'Active', 'Value' => 'Active'), array('Label' => 'Disabled', 'Value' => 'Disabled'));
        
        
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
                throw new Exception('CCA\User->__construct() requires id to be specified as an integer - if it is specified at all');
            }
            
            //Retrieve current user information
            if (isset($id)) {
                $this->_id = $id;
                $this->getItemById($id);
            }
        }
        
        
        /**
         * @param int $id
         *
         * @return mixed
         */
        public function getItemById($id = 0)
        {
            if ($id == 0) {
                $id = $this->_id;
            }
            if (!is_numeric($id) || $id <= 0) {
                error_log('CCA\User->getItemById() Unable to retrieve user details as no id set');
                return false;
            }
            
            //Now retrieve the extra fields
            try {
                $stmt = $this->_dbconn->prepare("SELECT ID, CustomerID, AES_DECRYPT(Title, :key) AS Title, AES_DECRYPT(Firstname, :key) AS Firstname, AES_DECRYPT(Surname, :key) AS Surname, AES_DECRYPT(Email, :key) AS Email, AES_DECRYPT(Mobile, :key) AS Mobile, LastLoggedIn, LastEdited, AES_DECRYPT(LastEditedBy, :key) AS LastEditedBy, AES_DECRYPT(AdminLevel, :key) AS AdminLevel, AES_DECRYPT(`Status`, :key) AS `Status` FROM Users WHERE ID =  :id LIMIT 1");
                $stmt->execute([
                    'key' => AES_ENCRYPTION_KEY,
                    'id' => $id
                ]);
                $item = $stmt->fetch(PDO::FETCH_ASSOC);
                
            } catch (Exception $e) {
                error_log("CCA\User->getItemById() Failed to retrieve user details".$e);
            }
            
            //Store details in relevant customers
            $this->_id = $item['ID'];
            $this->_customer_id = $item['CustomerID'];
            $this->_title = $item['Title'];
            $this->_firstname = $item['Firstname'];
            $this->_surname = $item['Surname'];
            $this->_email = $item['Email'];
            $this->_mobile = $item['Mobile'];
            $this->_last_edited = $item['LastEdited'];
            $this->_last_edited_by = $item['LastEditedBy'];
            $this->_last_logged_in = $item['LastLoggedIn'];
            $this->_admin_level = $item['AdminLevel'];
            $this->_status = $item['Status'];
            
            return $item;
        }
        
        
        /**
         * Function to return array of User records (as assoc array)
         *
         * @param string $passedNeedle
         * @param string $passedMode Only accepts: email, surname [default]
         *
         * @return array
         */
        public function listAllItems($passedNeedle = '', $passedMode = null, $sortorder = null)
        {
            $basesql = "SELECT Users.ID FROM Users WHERE ";
            $params = array();
            
            //Build SQL depending on passedMode and passedNeedle
            switch ($passedMode) {
                case 'surname':
                    $query = "(CONVERT(AES_DECRYPT(Users.Surname, :key) USING utf8) LIKE :needle) ";
                    $order = "ORDER BY AES_DECRYPT(Users.Surname, :key) ASC";
                    $params['needle'] = "%".$passedNeedle."%";
                    $params['key'] = AES_ENCRYPTION_KEY;
                    break;
                
                case 'email':
                    $query = "(CONVERT(AES_DECRYPT(Users.Email, :key) USING utf8) LIKE :needle) ";
                    $order = "ORDER BY AES_DECRYPT(Users.Email, :key) ASC";
                    $params['needle'] = "%".$passedNeedle."%";
                    $params['key'] = AES_ENCRYPTION_KEY;
                    break;
                
                case 'mobile':
                    $query = "(CONVERT(AES_DECRYPT(Users.Mobile, :key) USING utf8) LIKE :needle) ";
                    $order = "ORDER BY AES_DECRYPT(Users.Surname, :key) ASC";
                    $params['needle'] = $passedNeedle."%";
                    $params['key'] = AES_ENCRYPTION_KEY;
                    break;
                
                case 'customer-id':
                    $query = "(CustomerID = :needle) ";
                    $order = "ORDER BY AES_DECRYPT(Users.Surname, :key) ASC";
                    $params['needle'] = $passedNeedle;
                    $params['key'] = AES_ENCRYPTION_KEY;
                    break;
                
                case 'company-name':
                    $basesql = "SELECT Users.ID FROM Users LEFT JOIN Customers ON Customers.ID = Users.CustomerID WHERE ";
                    $query = "(CONVERT(AES_DECRYPT(Customers.Company, :key) USING utf8) LIKE :needle) ";
                    $order = "ORDER BY AES_DECRYPT(Customers.Company, :key) ASC, AES_DECRYPT(Users.Surname, :key) ASC";
                    $params['needle'] = "%".$passedNeedle."%";
                    $params['key'] = AES_ENCRYPTION_KEY;
                    break;
                    
                case 'name-email':
                    $query = "(CONVERT(AES_DECRYPT(Users.Surname, :key) USING utf8) LIKE :needle OR CONVERT(AES_DECRYPT(Users.Email, :key) USING utf8) LIKE :needle) ";
                    $order = "ORDER BY AES_DECRYPT(Users.Email, :key) ASC";
                    $params['needle'] = "%".$passedNeedle."%";
                    $params['key'] = AES_ENCRYPTION_KEY;
                    break;
                
                
                default:
                    $query = "ID = ID ";
                    $order = "ORDER BY AES_DECRYPT(Users.Surname, :key) ASC";
                    $params['key'] = AES_ENCRYPTION_KEY;
                    break;
            }
            
            if ($sortorder != '') {
                switch ($sortorder) {
                    case 'email':
                        $order = " ORDER BY AES_DECRYPT(Users.Email, :key) ASC";
                        $params['key'] = AES_ENCRYPTION_KEY;
                        break;
                    case 'company-name':
                        $basesql = "SELECT Users.ID FROM Users LEFT JOIN Customers ON Customers.ID = Users.CustomerID WHERE ";
                        $order = " ORDER BY AES_DECRYPT(Customers.Company, :key) ASC, AES_DECRYPT(Users.Surname, :key) ASC";
                        $params['key'] = AES_ENCRYPTION_KEY;
                        break;
                    case 'surname':
                        $order = " ORDER BY AES_DECRYPT(Users.Surname, :key) ASC";
                        $params['key'] = AES_ENCRYPTION_KEY;
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
            while ($this_res = $stmt->fetch()) {
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
                error_log('CCA\User->deleteItem() Unable to delete customer as no id set');
                
                return false;
            }
            
            
            //Now the actual user record
            try {
                $stmt = $this->_dbconn->prepare("DELETE FROM Users WHERE ID = :id LIMIT 1");
                $result = $stmt->execute([
                    'id' => $id
                ]);
                if ($result === true) {
                    return true;
                }
                
            } catch (Exception $e) {
                error_log("CCA\User->deleteItem() Failed to delete User".$e);
            }
            
            return false;
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
                $stmt = $this->_dbconn->prepare("UPDATE Users SET CustomerID = :customer_id, Title = AES_ENCRYPT(:title, :key), Firstname = AES_ENCRYPT(:firstname, :key), Surname = AES_ENCRYPT(:surname, :key), Email = AES_ENCRYPT(:email, :key), Mobile = AES_ENCRYPT(:mobile, :key), LastEdited = NOW(), LastEditedBy = AES_ENCRYPT(:lasteditedby, :key),  AdminLevel = AES_ENCRYPT(:admin_level, :key), `Status` = AES_ENCRYPT(:status, :key) WHERE ID = :id LIMIT 1");
                $result = $stmt->execute([
                    'key' => AES_ENCRYPTION_KEY,
                    'customer_id' => $this->_customer_id,
                    'title' => $this->_title,
                    'firstname' => $this->_firstname,
                    'surname' => $this->_surname,
                    'email' => $this->_email,
                    'mobile' => $this->_mobile,
                    'lasteditedby' => $this->_last_edited_by,
                    'admin_level' => $this->_admin_level,
                    'status' => $this->_status,
                    'id' => $this->_id
                ]);
                if ($result == true) {
                    return true;
                } else {
                    error_log($stmt->errorInfo()[2]);
                    return false;
                }
            } catch (Exception $e) {
                error_log("CCA\User->saveItem() Failed to save User record: ".$e);
            }
            
            return false;
        }
        
        /**
         * Create new record stub
         */
        public function createNewItem()
        {
            try {
                $stmt = $this->_dbconn->prepare("INSERT INTO Users SET LastEdited = NOW()");
                $stmt->execute();
                $id = $this->_dbconn->lastInsertId();
                
                //Store in the object
                $this->_id = $id;
                
            } catch (Exception $e) {
                error_log("CCA\User->createNewItem() Failed to create new User stub".$e);
            }
        }
        
        /**
         * Update the specified field
         *
         * Need to check in the schema if the field exists. If not, fail.
         *
         */
        public function updateField($field, $value, $recid)
        {
            if (!is_numeric($recid)) {
                error_log('CCA\User->updateField() - requires record id to be passed');
                return false;
            }
            if (!is_string($field) || $field == '') {
                error_log('CCA\User->updateField() - requires field to be passed');
                return false;
            }
            
            //Check its not one of our special columns
            if ($field == 'ID') {
                error_log('CCA\User->updateField() - Attempted to update prohibited field');
                return false;
            }
            
            
            //Check if the field and record exists
            $stmt = $this->_dbconn->prepare("SELECT ID FROM Users WHERE ID = :id LIMIT 1");
            $stmt->execute([
                'id' => $recid
            ]);
            $item = $stmt->fetch();
            
            if ($item['ID'] <= 0) {
                error_log('CCA\User->updateField() - Record does not exist');
                return false;
            }
            
            $stmt = $this->_dbconn->prepare("SHOW COLUMNS FROM Users LIKE :field");
            $stmt->execute([
                'field' => $field
            ]);
            $items = $stmt->fetchAll();
            
            if (!is_array($items) || count($items) <= 0) {
                error_log('CCA\User->updateField() - Field does not exist');
                return false;
            }
            
            
            //Now carry out the update
            //Check if the field and record exists
            /*if ($field == 'DateStart' || $field == 'DateApplicationDue' || $field == 'MES_MeetingID') {
                //No encryption
                $sql = "UPDATE Customers SET ".$field." = :value WHERE ID = :id LIMIT 1";
                $params = array('id' => $recid, 'value' => $value);
            } else {*/
            $sql = "UPDATE Users SET ".$field." = AES_ENCRYPT(:value, :key) WHERE ID = :id LIMIT 1";
            $params = array('id' => $recid, 'key' => AES_ENCRYPTION_KEY, 'value' => $value);
            //}
            $stmt = $this->_dbconn->prepare($sql);
            $result = $stmt->execute($params);
            
            if ($result == true) {
                return true;
            } else {
                error_log($stmt->errorInfo()[2]);
                return false;
            }
        }
        
        
        /**
         * Checks Users table for a matching password and Email
         * Also check user and customer status to make sure active
         *
         * @param string $Email
         * @param string $Password
         *
         * @return mixed    ('Success','Message','ID')
         * @throws Exception
         */
        public function checkPassword($Email, $Password)
        {
            if ($Email == '' || $Password == '' || !$this->ValidEmail($Email)) {
                throw new Exception('CCA\User->checkPassword() You need to supply an email and password to be able to login');
            } else {
                //Prepare password
                $pass = $Password.PASSWORD_SALT;
                //Check the DB
                try {
                    $stmt = $this->_dbconn->prepare("SELECT Users.ID, AES_DECRYPT(Users.AdminLevel, :key) AS AdminLevel, AES_DECRYPT(Users.Status, :key) AS `Status`, AES_DECRYPT(Customers.Status, :key) AS CustomerStatus FROM Users LEFT JOIN Customers ON Customers.ID = Users.CustomerID WHERE Users.Email = AES_ENCRYPT(:email, :key) AND Users.Password = md5(AES_ENCRYPT(:password, :key)) LIMIT 1");
                    $stmt->execute([
                        'key' => AES_ENCRYPTION_KEY,
                        'email' => $Email,
                        'password' => $pass
                    ]);
                    $success = $stmt->fetch();
                    
                    if ($success['ID'] > 0 && $success['Status'] == 'Active' && $success['CustomerStatus'] == 'Active') {
                        $ret_arr = array('Success' => true, 'Message' => 'User found', 'ID' => $success['ID']);
                    } elseif ($success['ID'] > 0 && ($success['Status'] != 'Active' || $success['CustomerStatus'] != 'Active')) {
                        $ret_arr = array('Success' => false, 'Message' => 'User found, but user or customer account disabled', 'ID' => $success['ID']);
                    } else {
                        $ret_arr = array('Success' => false, 'Message' => 'User not found with that email address and password combination', 'ID' => null);
                    }
                    //print_r($ret_arr);
                    
                    //exit;
                    return $ret_arr;
                    
                } catch (Exception $e) {
                    error_log('CCA\User->checkPassword() could not complete the search of the database for that Email/Password combination '.$e);
                }
                
            }
            return false;
        }
        
        private function ValidEmail($email)
        {
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return true;
            } else {
                return false;
            }
        }
        
        /**
         * Set password on record
         * @param string $password
         * @param bool $flagNotifyByEmail
         *
         * @return bool
         * @throws Exception
         */
        public function setPassword($password, $flagNotifyByEmail = false)
        {
            //Check that the password has been supplied
            if ($password == '') {
                throw new Exception('CCA\User->setPassword() No password supplied');
            }
            
            //Check that we have a customerid set up
            if ($this->_id <= 0) {
                throw new Exception('CCA\User->setPassword()  No User ID set on the property');
            }
            
            $pass = $password.PASSWORD_SALT;
            
            $stmt = $this->_dbconn->prepare("UPDATE Users SET Password = md5(AES_ENCRYPT(:password, :key)) WHERE ID = :id LIMIT 1");
            $stmt->execute([
                'password' => $pass,
                'key' => AES_ENCRYPTION_KEY,
                'id' => $this->_id
            ]);
            
            if ($flagNotifyByEmail == true) {
                //Send an email to this customer
                try {
                    //Prepare email text
                    $body = "<h3>Your details have been updated.</h3>";
                    $body .= "<p>Your details have been updated on the ".SITENAME." website. Your new password is shown below.</p>\n";
                    $body .= "<p>To log on in future, <a href=\"https://".SITEFQDN."/\">visit the website</a> and select the Login option. Your username is your <strong>email address</strong>. Your password is as its been set. if you can't remember your password you can reset your password at login.</p>\n";
                    
                    $body .= "<p>Should you have enquiries please don't hesitate to <a href=\"https://".SITEFQDN."/contact/\">get in touch with us</a>.</p>";
                    
                    $text = "Your details have been updated.\r\n\r\nYour details have been updated on the ".SITENAME." website. Your new password is shown below.\r\n";
                    $text .= "To log on in future, visit the website and select the Login option. Your username is your email address. Your password is as its been set. if you can't remember your password you can reset your password at login.\r\n\r\n";
                    $text .= "Should you have enquiries please don't hesitate to get in touch with us.\r\n";
                    
                    //Set up the email object
                    $email = new PBEmail();
                    $email->setRecipient($this->_email);
                    $email->setSenderEmail(SITESENDEREMAIL);
                    $email->setSenderName(SITENAME.' Website');
                    $email->setSubject('Your details on the '.SITENAME.' website');
                    $email->setHtmlMessage($body);
                    $email->setTextMessage($text);
                    $email->setTemplateFile(DOCUMENT_ROOT.'/emails/template.htm');
                    
                    //Send it
                    $email->sendMail();
                    
                } catch (Exception $e) {
                    error_log('CCA\User->setPassword() Failed to send email: '.$e);
                }
                
            }
            
            if ($stmt->rowCount() == 1) {
                return true;
            } else {
                return false;
            }
        }
        
        /**
         * Update last logged in date on record
         * @return mixed
         * @throws Exception
         */
        public function updateLastLoggedIn($userid = null)
        {
            //Check that we have a userid set up
            if (!isset($userid) || (!is_numeric($userid) || $userid <= 0)) {
                $userid = $this->_id;
            }
            if (!is_numeric($userid) || $userid <= 0) {
                throw new Exception('CCA\User->updateLastLoggedIn()  No user ID set or provided');
            }
            
            $stmt = $this->_dbconn->prepare("UPDATE Users SET LastLoggedIn = NOW() WHERE ID = :id LIMIT 1");
            $result = $stmt->execute([
                'id' => $userid
            ]);
            
            if ($result === true) {
                return true;
            } else {
                error_log('CCA\User->updateLastLoggedIn() Unable to set LastLoggedIn field');
            }
            return false;
        }
        
        /**
         * Function to check if email address doesn't exist in DB
         * @param string    email     email address
         * @param int       id        ID of customer
         *
         * @return bool
         */
        public function emailExists($email, $id = 0)
        {
            if ($email == '') {
                return true;
            }
            //Now the db check
            try {
                $stmt = $this->_dbconn->prepare("SELECT ID, AES_DECRYPT(Email, :key) AS Email FROM Users WHERE CONVERT(AES_DECRYPT(Email, :key) USING utf8) LIKE :email");
                $stmt->execute([
                    'key' => AES_ENCRYPTION_KEY,
                    'email' => $email
                ]);
                $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
            } catch (Exception $e) {
                error_log("CCA\User->emailExists() Failed to retrieve user details".$e);
            }
            
            
            $retval = false;
            if (is_array($users) && count($users) > 0) {
                //if supplied an id - check the returned record(s) don't match the id (as that's just an edit)
                if (is_numeric($id) && $id > 0) {
                    foreach ($users as $item) {
                        if ($item['ID'] != $id) {
                            $retval = true;
                        }
                    }
                } else {
                    //We don't have an id provided - so this is a NEW record, and the email address exists. Therefore return true
                    $retval = true;
                }
            }
            
            return $retval;
        }
        
        
        
        ###########################################################
        # Getters and Setters
        ###########################################################
        
        public function getId(): float|int|string
        {
            return $this->_id;
        }
        
        public function setId(float|int|string $id): void
        {
            $this->_id = $id;
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
            if (!is_numeric($customer_id)) { $customer_id = null; }
            $this->_customer_id = $customer_id;
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
        public function getFirstname()
        {
            return $this->_firstname;
        }
        
        /**
         * @param mixed $firstname
         */
        public function setFirstname($firstname): void
        {
            $this->_firstname = $firstname;
        }
        
        /**
         * @return mixed
         */
        public function getSurname()
        {
            return $this->_surname;
        }
        
        /**
         * @param mixed $surname
         */
        public function setSurname($surname): void
        {
            $this->_surname = $surname;
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
        public function setEmail($email): void
        {
            $this->_email = $email;
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
        public function setMobile($mobile): void
        {
            $this->_mobile = $mobile;
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
        public function setLastEditedBy($last_edited_by): void
        {
            $this->_last_edited_by = $last_edited_by;
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
        public function setLastEdited($last_edited): void
        {
            if ($last_edited == '' || $last_edited == '0000-00-00' || $last_edited == '0000-00-00 00:00:00') { $last_edited = null; }
            $this->_last_edited = $last_edited;
        }
        
        /**
         * @return mixed
         */
        public function getAdminLevel()
        {
            return $this->_admin_level;
        }
        
        /**
         * @param mixed $admin_level
         */
        public function setAdminLevel($admin_level): void
        {
            $this->_admin_level = $admin_level;
        }
        
        /**
         * @return mixed
         */
        public function getLastLoggedIn()
        {
            return $this->_last_logged_in;
        }
        
        /**
         * @param mixed $last_logged_in
         */
        public function setLastLoggedIn($last_logged_in): void
        {
            if ($last_logged_in == '' || $last_logged_in == '0000-00-00' || $last_logged_in == '0000-00-00 00:00:00') { $last_logged_in = null; }
            $this->_last_logged_in = $last_logged_in;
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
        public function setStatus($status): void
        {
            $this->_status = $status;
        }
        
        public function getTitleOptions(): array
        {
            return $this->_title_options;
        }
        
        public function getAdminLevelOptions(): array
        {
            return $this->_admin_level_options;
        }
        
        public function getStatusOptions(): array
        {
            return $this->_status_options;
        }
        
        
    }