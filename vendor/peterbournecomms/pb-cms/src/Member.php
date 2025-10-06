<?php

    namespace PeterBourneComms\CMS;
    use PDO;
    use PDOException;
    use Exception;


    /**
     * Class to manage website members/users
     *
     * It will have functions to retrieve membership details from database - check valid users, check type of member etc.
     * There will also be functionality to update member details
     * It will create its own DB connection based on the Database.class.php
     *
     * @author Peter Bourne
     * @version 1.1
     * @history
     *
     * 1.0      --          Original version
     * 1.1      24/06/2022  Added doesEmailExist() method
     *
     */
    class Member
    {
        protected $_dbconn;
        protected $_id;
        protected $_firstname;
        protected $_surname;
        protected $_address1;
        protected $_address2;
        protected $_address3;
        protected $_town;
        protected $_county;
        protected $_postcode;
        protected $_telephone;
        protected $_email;
        protected $_registration;
        protected $_lastEdited;
        protected $_lastEditedBy;
        protected $_lastLoggedIn;
        protected $_membershipDisabled;
        protected $_imgfilename;
        protected $_admin_level;

        protected $_image_path;
        protected $_image_width;
        protected $_image_height;
        protected $_thumb_width;
        protected $_thumb_height;



        public function __construct($memberid = null, $width = 600, $height = 800, $path = USER_UPLOADS.'/images/member-avatars/')
        {
            // Make connection to database
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
            }
            if (isset($memberid) && !is_numeric($memberid))
            {
                throw new Exception('Class Member requires memberid to be specified as an integer - if it is specified at all');
            }

            //Assess passed width & height
            if (!is_numeric($width) || !is_numeric($height))
            {
                throw new Exception('Class Member requires width and height to be specified as integers');
            }

            //Assess passed path
            if (isset($path) && !is_string($path))
            {
                throw new Exception('Class Member requires path to be specified as a string, eg: /user_uploads/images/member-avatars/');
            }

            //See if provided path exists - if not - create it
            if (!file_exists(DOCUMENT_ROOT . $path))
            {
                //Create it
                $success = mkdir(DOCUMENT_ROOT . $path, 0777, true);
                if (!$success)
                {
                    throw new Exception('Directory specified ('.$path.') does not exist - and cannot be created');
                }
            }


            //Retrieve current member information
            if (isset($memberid))
            {
                $this->_id = $memberid;
                $this->getItemById($memberid);
            }

            //Store the width/height/path etc
            $this->_image_width = $width;
            $this->_image_height = $height;
            $this->_image_path = $path;
        }


        public function getItemById($memberid = 0)
        {
            if ($memberid == 0)
            {
                $memberid = $this->_id;
            }
            if (!is_numeric($memberid) || $memberid <= 0)
            {
                error_log('Member: Unable to retrieve member details as no memberID set');
                return false;
            }

            try
            {
                $stmt = $this->_dbconn->prepare("SELECT ID, AES_DECRYPT(Firstname, :key) AS Firstname, AES_DECRYPT(Surname, :key) AS Surname, AES_DECRYPT(Address1, :key) AS Address1, AES_DECRYPT(Address2, :key) AS Address2, AES_DECRYPT(Address3, :key) AS Address3, AES_DECRYPT(Town, :key) AS Town, AES_DECRYPT(County, :key) AS County, AES_DECRYPT(Postcode, :key) AS Postcode, AES_DECRYPT(Telephone, :key) AS Telephone, AES_DECRYPT(Email, :key) AS Email, Registration, LastEdited, LastEditedBy, LastLoggedIn, AES_DECRYPT(MembershipDisabled, :key) AS MembershipDisabled, AES_DECRYPT(ImgFilename, :key) AS ImgFilename, AES_DECRYPT(ImgPath, :key) AS ImgPath, AES_DECRYPT(AdminLevel, :key) AS AdminLevel FROM MemberDetails WHERE ID =  :id LIMIT 1");
                $stmt->execute([
                                   'key' => AES_ENCRYPTION_KEY,
                                   'id' => $memberid
                               ]);
                $mem = $stmt->fetch(PDO::FETCH_ASSOC);
                //print_r($stmt->ErrorInfo());
            } catch (Exception $e)
            {
                error_log("Failed to retrieve Member details" . $e);
            }

            //Store details in relevant members
            $this->_id = $mem['ID'];
            $this->_firstname = $mem['Firstname'];
            $this->_surname = $mem['Surname'];
            $this->_address1 = $mem['Address1'];
            $this->_address2 = $mem['Address2'];
            $this->_address3 = $mem['Address3'];
            $this->_town = $mem['Town'];
            $this->_county = $mem['County'];
            $this->_postcode = $mem['Postcode'];
            $this->_telephone = $mem['Telephone'];
            $this->_email = $mem['Email'];
            $this->_registration = $mem['Registration'];
            $this->_lastEdited = $mem['LastEdited'];
            $this->_lastEditedBy = $mem['LastEditedBy'];
            $this->_lastLoggedIn = $mem['LastLoggedIn'];
            $this->_membershipDisabled = $mem['MembershipDisabled'];
            $this->_imgfilename = $mem['ImgFilename'];
            $this->_admin_level = $mem['AdminLevel'];

            $this->_image_path = $mem['ImgPath'];

            //Also return the member
            return $mem;

        }

        /**
         * Function to return array of Member records (as assoc array)
         *
         * @param string $passedNeedle
         * @param string $passedMode    Only accepts: email, surname [default]
         *
         * @return array
         */
        public function listAllItems($passedNeedle = '', $passedMode = 'surname')
        {
            $basesql = "SELECT ID FROM MemberDetails WHERE ";

            //Build SQL depending on passedMode and passedNeedle
            switch($passedMode)
            {
                case 'email':
                    $query = "CONVERT(AES_DECRYPT(Email, :key) USING utf8) LIKE :needle ORDER BY AES_DECRYPT(Email, :key) ASC";
                    $passedNeedle = "%".$passedNeedle."%";
                    break;

                default:
                    $query = "CONVERT(AES_DECRYPT(Surname, :key) USING utf8) LIKE :needle ORDER BY AES_DECRYPT(Surname, :key) ASC";
                    $passedNeedle = "%".$passedNeedle."%";
                    break;
            }

            //Carry out the query
            $stmt = $this->_dbconn->prepare($basesql.$query);
            $stmt->execute([
                'key' => AES_ENCRYPTION_KEY,
                'needle' => $passedNeedle
                           ]);

            //Prepare results array
            $results = array();

            //Work through results from query
            while($this_res = $stmt->fetch())
            {
                //Now retrieve the full member record
                $mem = $this->getItemById($this_res['ID']);
                $results[] = $mem;
            }

            return $results;
        }


        /**
         * Checks MemberDetails table for a matching password and Email
         * Then verifies Membership is not disabled.
         *
         *
         * @param string    $Email
         * @param string    $Password
         *
         * @return array    ('Success','Message','ID')
         * @throws Exception
         */
        public function checkPassword($Email, $Password)
        {
            if ($Email == '' || $Password == ''  || !ValidEmail($Email))
            {
                throw new Exception('You need to supply an email and password to be able to login');
            }
            else
            {
                //Prepare password
                $pass = $Password.PASSWORD_SALT;
                //Check the DB
                try
                {
                    $stmt = $this->_dbconn->prepare("SELECT ID, AES_DECRYPT(MembershipDisabled, :key) AS MembershipDisabled FROM MemberDetails WHERE CONVERT(AES_DECRYPT(Email, :key) USING utf8) = :email AND Password = md5(AES_ENCRYPT(:password, :key)) LIMIT 1");
                    $stmt->execute([
                        'key' => AES_ENCRYPTION_KEY,
                        'email' => $Email,
                        'password' => $pass
                                   ]);
                    $success = $stmt->fetch();

                    if ($success['ID'] > 0 && $success['MembershipDisabled'] != 'Y')
                    {
                        $ret_arr = array('Success'=>true,'Message'=>'Member found','ID'=>$success['ID']);
                    }
                    elseif ($success['ID'] > 0 && $success['MembershipDisabled'] == 'Y')
                    {
                        $ret_arr = array('Success'=>false,'Message'=>'Member found, but account disabled','ID'=>$success['ID']);
                    }
                    else
                    {
                        $ret_arr = array('Success'=>false,'Message'=>'Member not found with that email address and password combination','ID'=>null);
                    }
                    return $ret_arr;

                } catch (Exception $e)
                {
                    error_log('Member could not complete the search of the database for that Email/Password combination '.$e);
                }

            }
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
            if ($ImageStream == '')
            {
                throw new Exception("You must supply a file stream (data:image/png;base64) to this function.");
            }

            //Create ImageHandler
            $ImgObj = new ImageHandler($this->_image_path, true);
            //Set up some defaults
            $ImgObj->setSourceFileType('png');
            $ImgObj->setDestFileType('jpg');
            $ImgObj->setImageWidth($this->_image_width);
            $ImgObj->setImageHeight($this->_image_height);
            $ImgObj->setThumbWidth(300);
            $ImgObj->setThumbHeight(400);
            $ImgObj->createFilename();


            $result = $ImgObj->processImage($ImageStream);
            $newFilename = $ImgObj->getImgFilename();

            if ($result === true)
            {
                //Delete the old image
                $this->deleteImage();

                //Store the new filename
                $this->_imgfilename = $newFilename;

                //Save the object in its current state
                $this->saveItem();
            }
        }


        /**
         * Delete the image for this member- assuming _img_filename is set
         *
         * @return mixed
         */
        public function deleteImage()
        {
            if (!is_string($this->_imgfilename) || $this->_imgfilename == '')
            {
                return "Sorry - there was no image to delete";
            }

            $OldImg = new ImageHandler($this->_image_path,true);
            $OldImg->setImgFilename($this->_imgfilename);
            $OldImg->deleteImage();

            $this->_imgfilename = '';
            $this->saveItem();
        }



        public function createNewItem()
        {
            try
            {
                $stmt = $this->_dbconn->prepare("INSERT INTO MemberDetails SET Firstname = AES_ENCRYPT(:firstname, :key), LastEdited = NOW()");
                $stmt->execute([
                                   'firstname' => '',
                                   'key' => AES_ENCRYPTION_KEY
                               ]);
                $id = $this->_dbconn->lastInsertId();

                //Store in the object
                $this->_id = $id;

            } catch (Exception $e)
            {
                error_log("Member: Failed to create new Member stub" . $e);
            }
        }


        public function setPassword($password, $flagNotifyByEmail = false)
        {
            //Check that the password has been supplied
            if ($password == '')
            {
                throw new Exception('Member object: No password supplied');
            }

            //Check that we have a memberid set up
            if ($this->_id <= 0)
            {
                throw new Exception('Member object: No Member ID set on the property');
            }

            $pass = $password . PASSWORD_SALT;

            $stmt = $this->_dbconn->prepare("UPDATE MemberDetails SET Password = md5(AES_ENCRYPT(:password, :key)) WHERE ID = :id LIMIT 1");
            $stmt->execute([
                               'password' => $pass,
                               'key' => AES_ENCRYPTION_KEY,
                               'id' => $this->_id
                           ]);

            if ($flagNotifyByEmail == true)
            {
                //Send an email to this member
                try
                {
                    //Prepare email text
                    $body = "<h3>Your details have been updated.</h3>";
                    $body .= "<p>Your details have been updated on the ".SITENAME." website. Your new password is shown below.</p>\n";
                    $body .= "<p>To log on in future, <a href=\"https://".SITEFQDN."/\">visit the website</a> and select the Login option. Your username is your <strong>email address</strong>. Your password is: <strong>".$password."</strong></p>\n";

                    $body .= "<p>Should you have enquiries please don't hesitate to <a href=\"http://".SITEFQDN."/_contact/\">get in touch with us</a>.</p>";

                    //Set up the email object
                    $email = new PBEmail();
                    $email->setRecipient($this->_email);
                    $email->setSenderEmail(SITESENDEREMAIL);
                    $email->setSenderName(SITENAME.' Website');
                    $email->setSubject('Your details on the '.SITENAME. ' website');
                    $email->setHtmlMessage($body);
                    $email->setTemplateFile(DOCUMENT_ROOT.'/emails/template.htm');

                    //Send it
                    $email->sendMail();

                } catch (Exception $e)
                {
                    error_log('Member: Failed to send email: ' . $e);
                }

            }

            if ($stmt->rowCount() == 1)
            {
                return true;
            }
            else
            {
                return false;
            }
        }


        public function updateLastLoggedIn()
        {
            //Check that we have a memberid set up
            if ($this->_id <= 0)
            {
                throw new Exception('Member object: No Member ID set on the property');
            }

            $stmt = $this->_dbconn->prepare("UPDATE MemberDetails SET LastLoggedIn = NOW() WHERE ID = :id LIMIT 1");
            $result = $stmt->execute([
                               'id' => $this->_id
                           ]);

            if ($result === true) { return true; } else { error_log('Unable to set LastLoggedIn field'); }
        }

        public function saveItem()
        {
            //First need to determine if this is a new member item
            if ($this->_id <= 0)
            {
                $this->createNewItem(); //_id should now be set
            }

            try
            {
                $stmt = $this->_dbconn->prepare("UPDATE MemberDetails SET Firstname = AES_ENCRYPT(:firstname, :key), Surname = AES_ENCRYPT(:surname, :key), Address1 = AES_ENCRYPT(:address1, :key), Address2 = AES_ENCRYPT(:address2, :key), Address3 = AES_ENCRYPT(:address3, :key), Town = AES_ENCRYPT(:town, :key), County = AES_ENCRYPT(:county, :key), Postcode = AES_ENCRYPT(:postcode, :key), Telephone = AES_ENCRYPT(:telephone, :key), Email = AES_ENCRYPT(:email, :key), Registration = :registration,  LastEdited = NOW(), LastEditedBy = :lasteditedby, MembershipDisabled = AES_ENCRYPT(:membershipdisabled, :key), ImgFilename = AES_ENCRYPT(:imgfilename, :key), ImgPath = AES_ENCRYPT(:imgpath, :key), AdminLevel = AES_ENCRYPT(:adminlevel, :key)  WHERE ID = :memberid LIMIT 1");
                $result = $stmt->execute([
                                             'key' => AES_ENCRYPTION_KEY,
                                             'firstname' => $this->_firstname,
                                             'surname' => $this->_surname,
                                             'address1' => $this->_address1,
                                             'address2' => $this->_address2,
                                             'address3' => $this->_address3,
                                             'town' => $this->_town,
                                             'county' => $this->_county,
                                             'postcode' => $this->_postcode,
                                             'telephone' => $this->_telephone,
                                             'email' => $this->_email,
                                             'registration' => $this->_registration,
                                             'lasteditedby' => $this->_lastEditedBy,
                                             'membershipdisabled' => $this->_membershipDisabled,
                                             'imgfilename' => $this->_imgfilename,
                                             'imgpath' => $this->_image_path,
                                             'adminlevel' => $this->_admin_level,
                                             'memberid' => $this->_id
                                         ]);
                if ($result == true) { return true; } //else { print_r( $stmt->errorInfo()); exit; }
            } catch (Exception $e)
            {
                error_log("Failed to save Member record: " . $e);
            }
            error_log("Failed to save Member record: " . $e);
            error_log($stmt->errorInfo()[2]);
            return false;
        }


        public function deleteItem($id = 0)
        {
            if ($id == 0)
            {
                $id = $this->_id;
            }
            if (!is_numeric($id) || $id <= 0)
            {
                error_log('Member: Unable to delete member as no id set');

                return false;
            }

            try
            {
                //Now the memebr record
                $stmt = $this->_dbconn->prepare("DELETE FROM MemberDetails WHERE ID = :id LIMIT 1");
                $result = $stmt->execute([
                                             'id' => $id
                                         ]);
                if ($result === true) { return true; }
            } catch (Exception $e)
            {
                error_log("Failed to delete Member" . $e);
            }

            return false;
        }
    
        /**
         * Checks whether this is a valid email address - and whether its a unique email address (not used by other Member records)
         *
         * @param $Email
         * @param $ID
         *
         * @return bool
         */
        public function doesEmailExist($Email, $ID = 0)
        {
            if (!ValidEmail($Email)) {
                error_log('CMS\Member->doesEmailExist() Invalid email address provided to checkEmail function');
            
                return false;
            }
        
            if (clean_int($ID) > 0) {
                $stmt = $this->_dbconn->prepare("SELECT ID FROM MemberDetails WHERE CONVERT(AES_DECRYPT(Email, :key) USING utf8) = :email AND ID != :id");
                $stmt->execute([
                    'key' => AES_ENCRYPTION_KEY,
                    'email' => $Email,
                    'id' => $ID
                ]);
                $matches = $stmt->fetchAll();
            } else {
                $stmt = $this->_dbconn->prepare("SELECT ID FROM MemberDetails WHERE CONVERT(AES_DECRYPT(Email, :key) USING utf8) = :email");
                $stmt->execute([
                    'key' => AES_ENCRYPTION_KEY,
                    'email' => $Email
                ]);
                $matches = $stmt->fetchAll();
            }
            if (is_array($matches) && count($matches) > 0) {
                error_log('CMS\Member->doesEmailExist() Email already exists in the database');
                return true;
            } else {
                return false;
            }
        }
        


        ###########################################################
        # Getters and Setters
        ###########################################################

        public function getID()
        {
            return $this->_id;
        }

        public function setID($id)
        {
            $this->_id = $id;
        }

        public function getFirstname()
        {
            return $this->_firstname;
        }

        public function setFirstname($firstname)
        {
            $this->_firstname = $firstname;
        }

        public function getSurname()
        {
            return $this->_surname;
        }

        public function setSurname($surname)
        {
            $this->_surname = $surname;
        }

        public function getAddress1()
        {
            return $this->_address1;
        }

        public function setAddress1($address1)
        {
            $this->_address1 = $address1;
        }

        public function getAddress2()
        {
            return $this->_address2;
        }

        public function setAddress2($address2)
        {
            $this->_address2 = $address2;
        }

        public function getAddress3()
        {
            return $this->_address3;
        }

        public function setAddress3($address3)
        {
            $this->_address3 = $address3;
        }

        public function getTown()
        {
            return $this->_town;
        }

        public function setTown($town)
        {
            $this->_town = $town;
        }

        public function getCounty()
        {
            return $this->_county;
        }

        public function setCounty($county)
        {
            $this->_county = $county;
        }

        public function getPostcode()
        {
            return $this->_postcode;
        }

        public function setPostcode($postcode)
        {
            $this->_postcode = $postcode;
        }

        public function getTelephone()
        {
            return $this->_telephone;
        }

        public function setTelephone($telephone)
        {
            $this->_telephone = $telephone;
        }

        public function getEmail()
        {
            return $this->_email;
        }

        public function setEmail($email)
        {
            $this->_email = $email;
        }

        public function getRegistration()
        {
            return $this->_registration;
        }

        public function setRegistration($registration)
        {
            if ($registration == '') { $registration = null; }
            $this->_registration = $registration;
        }

        public function getLastEdited()
        {
            return $this->_lastEdited;
        }

        public function setLastEdited($lastEdited)
        {
            if ($lastEdited == '') { $lastEdited = null; }
            $this->_lastEdited = $lastEdited;
        }

        public function getLastEditedBy()
        {
            return $this->_lastEditedBy;
        }

        public function setLastEditedBy($lastEditedBy)
        {
            $this->_lastEditedBy = $lastEditedBy;
        }


        public function getLastLoggedIn()
        {
            return $this->_lastLoggedIn;
        }


        public function getMembershipDisabled()
        {
            return $this->_membershipDisabled;
        }

        public function setMembershipDisabled($membershipDisabled)
        {
            $this->_membershipDisabled = $membershipDisabled;
        }

        public function getImgFilename()
        {
            return $this->_imgfilename;
        }

        public function setImgFilename($imgfilename)
        {
            $this->_imgfilename = $imgfilename;
        }


        public function getImagePath()
        {
            return $this->_image_path;
        }

        public function setImagePath($image_path)
        {
            $this->_image_path = $image_path;
        }

        public function getAdminLevel()
        {
            return $this->_admin_level;
        }

        public function setAdminLevel($admin_level)
        {
            $this->_admin_level = $admin_level;
        }

    }