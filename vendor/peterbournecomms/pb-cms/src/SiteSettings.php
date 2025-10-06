<?php

    namespace PeterBourneComms\CMS;
    use PDO;
    use PDOException;
    use Exception;


    /**
     * Class to manage website settings
     *
     * For the Peter Bourne Communications Website Packages
     *
     * @author Peter Bourne
     * @version 1.0
     *
     */
    class SiteSettings
    {
        protected $_dbconn;
        protected $_ID;
        protected $_fqdn;
        protected $_title;
        protected $_address1;
        protected $_address2;
        protected $_address3;
        protected $_town;
        protected $_county;
        protected $_postcode;
        protected $_telephone;
        protected $_mobile;
        protected $_email;
        protected $_date_setup;
        protected $_imgfilename;
        protected $_image_path;
        protected $_image_width;
        protected $_image_height;
        protected $_primary_colour;
        protected $_secondary_colour;
        protected $_strapline;
        protected $_reg_number;
        protected $_reg_jurisdiction;
        protected $_reg_address1;
        protected $_reg_address2;
        protected $_reg_address3;
        protected $_reg_town;
        protected $_reg_county;
        protected $_reg_postcode;
        protected $_social_facebook;
        protected $_social_linkedin;
        protected $_social_twitter;
        protected $_social_pinterest;
        protected $_social_instagram;
        protected $_social_google;
        protected $_add_this_code;
        protected $_enable_map;
        protected $_map_embed;
        protected $_default_meta_desc;
        protected $_default_meta_key;
        protected $_ga_code;
        protected $_g_recaptcha_site;
        protected $_g_recaptcha_secret;


        public function __construct($id = null, $width = 400, $height = 200, $path = USER_UPLOADS.'/images/')
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
            if (isset($id) && !is_numeric($id))
            {
                throw new Exception('Class SiteSettings requires id to be specified as an integer - if it is specified at all');
            }

            //Assess passed width & height
            if (!is_numeric($width) || !is_numeric($height))
            {
                throw new Exception('Class SiteSettings requires width and height to be specified as integers');
            }

            //Assess passed path
            if (isset($path) && !is_string($path))
            {
                throw new Exception('Class SiteSettings requires path to be specified as a string, eg: /user_uploads/images/');
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
            if (isset($id))
            {
                $this->_ID = $id;
                $this->getItem($id);
            }

            //Store the width/height/path etc
            $this->_image_width = $width;
            $this->_image_height = $height;
            $this->_image_path = $path;
        }


        public function getItem($id = 0)
        {
            if ($id == 0)
            {
                $id = $this->_ID;
            }
            if (!is_numeric($id) || $id <= 0)
            {
                error_log('SiteSettings: Unable to retrieve member details as no ID set');
                return false;
            }

            try
            {
                $stmt = $this->_dbconn->prepare("SELECT ID, AES_DECRYPT(Title, :key) AS Title, AES_DECRYPT(FQDN, :key) AS FQDN, PrimaryColour, SecondaryColour, AES_DECRYPT(Strapline, :key) AS Strapline, AES_DECRYPT(Address1, :key) AS Address1, AES_DECRYPT(Address2, :key) AS Address2, AES_DECRYPT(Address3, :key) AS Address3, AES_DECRYPT(Town, :key) AS Town, AES_DECRYPT(County, :key) AS County, AES_DECRYPT(Postcode, :key) AS Postcode, AES_DECRYPT(Telephone, :key) AS Telephone, AES_DECRYPT(Email, :key) AS Email, AES_DECRYPT(Mobile, :key) AS Mobile, DateSetup, AES_DECRYPT(ImgFilename, :key) AS ImgFilename, AES_DECRYPT(ImgPath, :key) AS ImgPath, AES_DECRYPT(RegAddress1, :key) AS RegAddress1, AES_DECRYPT(RegAddress2, :key) AS RegAddress2, AES_DECRYPT(RegAddress3, :key) AS RegAddress3, AES_DECRYPT(RegTown, :key) AS RegTown, AES_DECRYPT(RegCounty, :key) AS RegCounty, AES_DECRYPT(RegPostcode, :key) AS RegPostcode, AES_DECRYPT(RegJurisdiction, :key) AS RegJurisdiction, AES_DECRYPT(RegNumber, :key) AS RegNumber, AES_DECRYPT(Social_Facebook, :key) AS Social_Facebook, AES_DECRYPT(Social_LinkedIn, :key) AS Social_LinkedIn, AES_DECRYPT(Social_Twitter, :key) AS Social_Twitter, AES_DECRYPT(Social_Pinterest, :key) AS Social_Pinterest, AES_DECRYPT(Social_Instagram, :key) AS Social_Instagram, AES_DECRYPT(Social_Google, :key) AS Social_Google, AES_DECRYPT(AddThisCode, :key) AS AddThisCode, EnableMap, AES_DECRYPT(MapEmbed, :key) AS MapEmbed, AES_DECRYPT(DefaultMetaDesc, :key) AS DefaultMetaDesc, AES_DECRYPT(DefaultMetaKey, :key) AS DefaultMetaKey, AES_DECRYPT(GA_Code, :key) AS GA_Code, AES_DECRYPT(G_RecaptchaSite, :key) AS G_RecaptchaSite, AES_DECRYPT(G_RecaptchaSecret, :key) AS G_RecaptchaSecret FROM SiteSettings WHERE ID =  :id LIMIT 1");
                $result = $stmt->execute([
                                   'key' => AES_ENCRYPTION_KEY,
                                   'id' => $id
                               ]);
                if ($result == true) {
                    $mem = $stmt->fetch(PDO::FETCH_ASSOC);
                }
                else
                {
                    error_log($stmt->errorInfo()[2]);
                }
                //print_r($stmt->ErrorInfo());

            } catch (Exception $e)
            {
                error_log("Failed to retrieve Site details" . $e);
            }

            //Store details in relevant members
            if (is_array($mem) && count($mem) > 0) {
                $this->_ID = $mem['ID'];
                $this->_title = $mem['Title'];
                $this->_fqdn = $mem['FQDN'];
                $this->_primary_colour = $mem['PrimaryColour'];
                $this->_secondary_colour = $mem['SecondaryColour'];
                $this->_strapline = $mem['Strapline'];
                $this->_address1 = $mem['Address1'];
                $this->_address2 = $mem['Address2'];
                $this->_address3 = $mem['Address3'];
                $this->_town = $mem['Town'];
                $this->_county = $mem['County'];
                $this->_postcode = $mem['Postcode'];
                $this->_telephone = $mem['Telephone'];
                $this->_email = $mem['Email'];
                $this->_mobile = $mem['Mobile'];
                $this->_date_setup = $mem['DateSetup'];
                $this->_reg_number = $mem['RegNumber'];
                $this->_reg_jurisdiction = $mem['RegJurisdiction'];
                $this->_social_facebook = $mem['Social_Facebook'];
                $this->_social_linkedin = $mem['Social_LinkedIn'];
                $this->_social_twitter = $mem['Social_Twitter'];
                $this->_social_pinterest = $mem['Social_Pinterest'];
                $this->_social_instagram = $mem['Social_Instagram'];
                $this->_social_google = $mem['Social_Google'];
                $this->_add_this_code = $mem['AddThisCode'];
                $this->_enable_map = $mem['EnableMap'];
                $this->_map_embed = $mem['MapEmbed'];
    
                $this->_reg_address1 = $mem['RegAddress1'];
                $this->_reg_address2 = $mem['RegAddress2'];
                $this->_reg_address3 = $mem['RegAddress3'];
                $this->_reg_town = $mem['RegTown'];
                $this->_reg_county = $mem['RegCounty'];
                $this->_reg_postcode = $mem['RegPostcode'];
    
                $this->_imgfilename = $mem['ImgFilename'];
                $this->_image_path = $mem['ImgPath'];
    
                $this->_default_meta_desc = $mem['DefaultMetaDesc'];
                $this->_default_meta_key = $mem['DefaultMetaKey'];
    
                $this->_ga_code = $mem['GA_Code'];
                $this->_g_recaptcha_site = $mem['G_RecaptchaSite'];
                $this->_g_recaptcha_secret = $mem['G_RecaptchaSecret'];
            }
            
            //Also return the member
            return $mem;

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
            $ImgObj = new ImageHandler($this->_image_path, false);
            //Set up some defaults
            $ImgObj->setSourceFileType('png');
            $ImgObj->setDestFileType('png');
            $ImgObj->setImageWidth($this->_image_width);
            $ImgObj->setImageHeight($this->_image_height);
            //$ImgObj->setThumbWidth(300);
            //$ImgObj->setThumbHeight(400);
            $ImgObj->setFlagMaintainTransparency(true);
            $ImgObj->createFilename($this->_title);


            $result = $ImgObj->processImage($ImageStream);
            $newFilename = $ImgObj->getImgFilename();

            if ($result == true)
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

            $OldImg = new ImageHandler($this->_image_path,false);
            //$OldImg->setImgFilename($this->_img_filename);
            $result = $OldImg->deleteImage($this->_imgfilename);

            //error_log('result of delete = '. $result);

            $this->_imgfilename = '';
            $this->saveItem();
        }



        public function createNewItem()
        {
            try
            {
                $stmt = $this->_dbconn->prepare("INSERT INTO SiteSettings SET DateSetup = NOW()");
                $stmt->execute();
                $id = $this->_dbconn->lastInsertId();

                //Store in the object
                $this->_ID = $id;

            } catch (Exception $e)
            {
                error_log("SiteSettings: Failed to create new Settings stub" . $e);
            }
        }




        public function saveItem()
        {
            //First need to determine if this is a new member item
            if ($this->_ID <= 0)
            {
                $this->createNewItem(); //_id should now be set
            }

            try
            {
                $stmt = $this->_dbconn->prepare("UPDATE SiteSettings SET Title = AES_ENCRYPT(:title, :key), FQDN = AES_ENCRYPT(:fqdn, :key), PrimaryColour = :primarycolour, SecondaryColour = :secondarycolour, Strapline = AES_ENCRYPT(:strapline, :key), Address1 = AES_ENCRYPT(:address1, :key), Address2 = AES_ENCRYPT(:address2, :key), Address3 = AES_ENCRYPT(:address3, :key), Town = AES_ENCRYPT(:town, :key), County = AES_ENCRYPT(:county, :key), Postcode = AES_ENCRYPT(:postcode, :key), Telephone = AES_ENCRYPT(:telephone, :key), Email = AES_ENCRYPT(:email, :key), Mobile = AES_ENCRYPT(:mobile, :key), ImgFilename = AES_ENCRYPT(:imgfilename, :key), ImgPath = AES_ENCRYPT(:imgpath, :key), RegNumber = AES_ENCRYPT(:regnumber, :key), RegJurisdiction = AES_ENCRYPT(:regjurisdiction, :key), RegAddress1 = AES_ENCRYPT(:regaddress1, :key), RegAddress2 = AES_ENCRYPT(:regaddress2, :key), RegAddress3 = AES_ENCRYPT(:regaddress3, :key), RegTown = AES_ENCRYPT(:regtown, :key), RegCounty = AES_ENCRYPT(:regcounty, :key), RegPostcode = AES_ENCRYPT(:regpostcode, :key), Social_Facebook = AES_ENCRYPT(:social_facebook, :key), Social_LinkedIn = AES_ENCRYPT(:social_linkedin, :key), Social_Twitter = AES_ENCRYPT(:social_twitter, :key), Social_Pinterest = AES_ENCRYPT(:social_pinterest, :key), Social_Instagram = AES_ENCRYPT(:social_instagram, :key), Social_Google = AES_ENCRYPT(:social_google, :key), AddThisCode = AES_ENCRYPT(:addthiscode, :key), EnableMap = :enablemap, MapEmbed = AES_ENCRYPT(:mapembed, :key), DefaultMetaDesc = AES_ENCRYPT(:defaultmetadesc, :key), DefaultMetaKey = AES_ENCRYPT(:defaultmetakey, :key), GA_Code = AES_ENCRYPT(:gacode, :key), G_RecaptchaSite = AES_ENCRYPT(:grecaptchasite, :key), G_RecaptchaSecret = AES_ENCRYPT(:grecaptchasecret, :key) WHERE ID = :id LIMIT 1");
                $result = $stmt->execute([
                                             'key' => AES_ENCRYPTION_KEY,
                                             'title' => $this->_title,
                                             'fqdn' => $this->_fqdn,
                                             'primarycolour' => $this->_primary_colour,
                                             'secondarycolour' => $this->_secondary_colour,
                                             'strapline' => $this->_strapline,
                                             'address1' => $this->_address1,
                                             'address2' => $this->_address2,
                                             'address3' => $this->_address3,
                                             'town' => $this->_town,
                                             'county' => $this->_county,
                                             'postcode' => $this->_postcode,
                                             'telephone' => $this->_telephone,
                                             'mobile' => $this->_mobile,
                                             'email' => $this->_email,
                                             'imgfilename' => $this->_imgfilename,
                                             'imgpath' => $this->_image_path,
                                             'regnumber' => $this->_reg_number,
                                             'regjurisdiction' => $this->_reg_jurisdiction,
                                             'regaddress1' => $this->_reg_address1,
                                             'regaddress2' => $this->_reg_address2,
                                             'regaddress3' => $this->_reg_address3,
                                             'regtown' => $this->_reg_town,
                                             'regcounty' => $this->_reg_county,
                                             'regpostcode' => $this->_reg_postcode,
                                             'social_facebook' => $this->_social_facebook,
                                             'social_linkedin' => $this->_social_linkedin,
                                             'social_twitter' => $this->_social_twitter,
                                             'social_pinterest' => $this->_social_pinterest,
                                             'social_instagram' => $this->_social_instagram,
                                             'social_google' => $this->_social_google,
                                             'addthiscode' => $this->_add_this_code,
                                             'enablemap' => $this->_enable_map,
                                             'mapembed' => $this->_map_embed,
                                             'defaultmetadesc' => $this->_default_meta_desc,
                                             'defaultmetakey' => $this->_default_meta_key,
                                             'gacode' => $this->_ga_code,
                                             'grecaptchasite' => $this->_g_recaptcha_site,
                                             'grecaptchasecret' => $this->_g_recaptcha_secret,
                                             'id' => $this->_ID
                                         ]);
                if ($result == true) { return true; } else { print_r( $stmt->errorInfo()); error_log($stmt->errorInfo()[2]); exit; }
            } catch (Exception $e)
            {
                error_log("Failed to save Settings record: " . $e);
            }

            return false;
        }


        /*
        public function deleteItem($id = 0)
        {
            if ($id == 0)
            {
                $id = $this->_ID;
            }
            if (!is_numeric($id) || $id <= 0)
            {
                error_log('SiteSettings: Unable to delete as no id set');

                return false;
            }

            try
            {
                //Now the memebr record
                $stmt = $this->_dbconn->prepare("DELETE FROM SiteSettings WHERE ID = :id LIMIT 1");
                $result = $stmt->execute([
                                             'id' => $id
                                         ]);
                if ($result == true) { return true; }
            } catch (Exception $e)
            {
                error_log("Failed to delete Settings" . $e);
            }

            return false;
        }
*/









        ###########################################################
        # Getters and Setters
        ###########################################################

        /**
         * @return int|string
         */
        public function getID()
        {
            return $this->_ID;
        }

        /**
         * @param int|string $ID
         */
        public function setID($ID)
        {
            $this->_ID = $ID;
        }

        /**
         * @return mixed
         */
        public function getFqdn()
        {
            return $this->_fqdn;
        }

        /**
         * @param mixed $fqdn
         */
        public function setFqdn($fqdn)
        {
            $this->_fqdn = $fqdn;
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
        }

        /**
         * @return mixed
         */
        public function getTelephone()
        {
            return $this->_telephone;
        }

        /**
         * @param mixed $telephone
         */
        public function setTelephone($telephone)
        {
            $this->_telephone = $telephone;
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
        }

        /**
         * @return mixed
         */
        public function getDateSetup()
        {
            return $this->_date_setup;
        }

        /**
         * @param mixed $date_setup
         */
        public function setDateSetup($date_setup)
        {
            $this->_date_setup = $date_setup;
        }

        /**
         * @return mixed
         */
        public function getImgfilename()
        {
            return $this->_imgfilename;
        }

        /**
         * @param mixed $imgfilename
         */
        public function setImgfilename($imgfilename)
        {
            $this->_imgfilename = $imgfilename;
        }

        /**
         * @return string
         */
        public function getImagePath()
        {
            return $this->_image_path;
        }

        /**
         * @param string $image_path
         */
        public function setImagePath($image_path)
        {
            $this->_image_path = $image_path;
        }

        /**
         * @return int|string
         */
        public function getImageWidth()
        {
            return $this->_image_width;
        }

        /**
         * @param int|string $image_width
         */
        public function setImageWidth($image_width)
        {
            $this->_image_width = $image_width;
        }

        /**
         * @return int|string
         */
        public function getImageHeight()
        {
            return $this->_image_height;
        }

        /**
         * @param int|string $image_height
         */
        public function setImageHeight($image_height)
        {
            $this->_image_height = $image_height;
        }

        /**
         * @return mixed
         */
        public function getPrimaryColour()
        {
            return $this->_primary_colour;
        }

        /**
         * @param mixed $primary_colour
         */
        public function setPrimaryColour($primary_colour)
        {
            $this->_primary_colour = $primary_colour;
        }

        /**
         * @return mixed
         */
        public function getSecondaryColour()
        {
            return $this->_secondary_colour;
        }

        /**
         * @param mixed $secondary_colour
         */
        public function setSecondaryColour($secondary_colour)
        {
            $this->_secondary_colour = $secondary_colour;
        }

        /**
         * @return mixed
         */
        public function getStrapline()
        {
            return $this->_strapline;
        }

        /**
         * @param mixed $strapline
         */
        public function setStrapline($strapline)
        {
            $this->_strapline = $strapline;
        }

        /**
         * @return mixed
         */
        public function getRegNumber()
        {
            return $this->_reg_number;
        }

        /**
         * @param mixed $reg_number
         */
        public function setRegNumber($reg_number)
        {
            $this->_reg_number = $reg_number;
        }

        /**
         * @return mixed
         */
        public function getRegJurisdiction()
        {
            return $this->_reg_jurisdiction;
        }

        /**
         * @param mixed $reg_jurisdiction
         */
        public function setRegJurisdiction($reg_jurisdiction)
        {
            $this->_reg_jurisdiction = $reg_jurisdiction;
        }

        /**
         * @return mixed
         */
        public function getRegAddress1()
        {
            return $this->_reg_address1;
        }

        /**
         * @param mixed $reg_address1
         */
        public function setRegAddress1($reg_address1)
        {
            $this->_reg_address1 = $reg_address1;
        }

        /**
         * @return mixed
         */
        public function getRegAddress2()
        {
            return $this->_reg_address2;
        }

        /**
         * @param mixed $reg_address2
         */
        public function setRegAddress2($reg_address2)
        {
            $this->_reg_address2 = $reg_address2;
        }

        /**
         * @return mixed
         */
        public function getRegAddress3()
        {
            return $this->_reg_address3;
        }

        /**
         * @param mixed $reg_address3
         */
        public function setRegAddress3($reg_address3)
        {
            $this->_reg_address3 = $reg_address3;
        }

        /**
         * @return mixed
         */
        public function getRegTown()
        {
            return $this->_reg_town;
        }

        /**
         * @param mixed $reg_town
         */
        public function setRegTown($reg_town)
        {
            $this->_reg_town = $reg_town;
        }

        /**
         * @return mixed
         */
        public function getRegCounty()
        {
            return $this->_reg_county;
        }

        /**
         * @param mixed $reg_county
         */
        public function setRegCounty($reg_county)
        {
            $this->_reg_county = $reg_county;
        }

        /**
         * @return mixed
         */
        public function getRegPostcode()
        {
            return $this->_reg_postcode;
        }

        /**
         * @param mixed $reg_postcode
         */
        public function setRegPostcode($reg_postcode)
        {
            $this->_reg_postcode = $reg_postcode;
        }

        /**
         * @return mixed
         */
        public function getSocialFacebook()
        {
            return $this->_social_facebook;
        }

        /**
         * @param mixed $social_facebook
         */
        public function setSocialFacebook($social_facebook)
        {
            $this->_social_facebook = $social_facebook;
        }

        /**
         * @return mixed
         */
        public function getSocialLinkedin()
        {
            return $this->_social_linkedin;
        }

        /**
         * @param mixed $social_linkedin
         */
        public function setSocialLinkedin($social_linkedin)
        {
            $this->_social_linkedin = $social_linkedin;
        }

        /**
         * @return mixed
         */
        public function getSocialTwitter()
        {
            return $this->_social_twitter;
        }

        /**
         * @param mixed $social_twitter
         */
        public function setSocialTwitter($social_twitter)
        {
            $this->_social_twitter = $social_twitter;
        }

        /**
         * @return mixed
         */
        public function getSocialPinterest()
        {
            return $this->_social_pinterest;
        }

        /**
         * @param mixed $social_pinterest
         */
        public function setSocialPinterest($social_pinterest)
        {
            $this->_social_pinterest = $social_pinterest;
        }

        /**
         * @return mixed
         */
        public function getSocialInstagram()
        {
            return $this->_social_instagram;
        }

        /**
         * @param mixed $social_instagram
         */
        public function setSocialInstagram($social_instagram)
        {
            $this->_social_instagram = $social_instagram;
        }

        /**
         * @return mixed
         */
        public function getSocialGoogle()
        {
            return $this->_social_google;
        }

        /**
         * @param mixed $social_google
         */
        public function setSocialGoogle($social_google)
        {
            $this->_social_google = $social_google;
        }

        /**
         * @return mixed
         */
        public function getAddThisCode()
        {
            return $this->_add_this_code;
        }

        /**
         * @param mixed $add_this_code
         */
        public function setAddThisCode($add_this_code)
        {
            $this->_add_this_code = $add_this_code;
        }

        /**
         * @return mixed
         */
        public function getEnableMap()
        {
            return $this->_enable_map;
        }

        /**
         * @param mixed $enable_map
         */
        public function setEnableMap($enable_map)
        {
            $this->_enable_map = $enable_map;
        }

        /**
         * @return mixed
         */
        public function getMapEmbed()
        {
            return $this->_map_embed;
        }

        /**
         * @param mixed $map_embed
         */
        public function setMapEmbed($map_embed)
        {
            $this->_map_embed = $map_embed;
        }

        /**
         * @return mixed
         */
        public function getDefaultMetaDesc()
        {
            return $this->_default_meta_desc;
        }

        /**
         * @param mixed $default_meta_desc
         */
        public function setDefaultMetaDesc($default_meta_desc)
        {
            $this->_default_meta_desc = $default_meta_desc;
        }

        /**
         * @return mixed
         */
        public function getDefaultMetaKey()
        {
            return $this->_default_meta_key;
        }

        /**
         * @param mixed $default_meta_key
         */
        public function setDefaultMetaKey($default_meta_key)
        {
            $this->_default_meta_key = $default_meta_key;
        }

        /**
         * @return mixed
         */
        public function getGaCode()
        {
            return $this->_ga_code;
        }

        /**
         * @param mixed $ga_code
         */
        public function setGaCode($ga_code)
        {
            $this->_ga_code = $ga_code;
        }

        /**
         * @return mixed
         */
        public function getGRecaptchaSite()
        {
            return $this->_g_recaptcha_site;
        }

        /**
         * @param mixed $g_recaptcha_site
         */
        public function setGRecaptchaSite($g_recaptcha_site)
        {
            $this->_g_recaptcha_site = $g_recaptcha_site;
        }

        /**
         * @return mixed
         */
        public function getGRecaptchaSecret()
        {
            return $this->_g_recaptcha_secret;
        }

        /**
         * @param mixed $g_recaptcha_secret
         */
        public function setGRecaptchaSecret($g_recaptcha_secret)
        {
            $this->_g_recaptcha_secret = $g_recaptcha_secret;
        }






    }