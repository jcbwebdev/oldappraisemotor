<?php

    namespace PeterBourneComms\Ecommerce;

    use PeterBourneComms\CMS\Database;
    use PeterBourneComms\CMS\ImageHandler;
    use PeterBourneComms\CMS\ImageLibrary;
    use PDO;
    use PDOException;
    use Exception;


    /**
     * Deals with ProductCategories items
     *
     * It will allow you to
     *  - specify the size of the ProductCategories image
     *  - specify the location of stored images
     *  - pass an image in and have it resized to that size
     *  - stored in the filesystem and database - along with caption, title and link
     *  - retrieve an individual ProductCategories item
     *  - retrieve an array of all ProductCategories items
     *  - delete ProductCategories item (including images)
     *
     * Relies on the Test table in this structure:
     *  ID
     *  Title
     *  Content
     *  AuthorID
     *  AuthorName
     *  URLText
     *  MetaDesc
     *  MetaKey
     *  ImgFilename
     *  MetaTitle
     *  ImgPath
     *  BGCol
     *  DisplayOrder
     *
     *
     * @author Peter Bourne
     * @version 1.0
     *
     *          1.0     19/03/19    Original version
     *
     *
     */
    class ProductCategory
    {
        /**
         * @var mixed
         */
        protected $_dbconn;
        /**
         * @var int|string
         */
        protected $_img_width;
        /**
         * @var int|string
         */
        protected $_img_height;
        /**
         * @var string
         */
        protected $_img_path;
        /**
         * @var int|string
         */
        protected $_id;
        /**
         * @var
         */
        protected $_title;
        /**
         * @var
         */
        protected $_img_filename;
        /**
         * @var
         */
        protected $_content;
        /**
         * @var
         */
        protected $_authorid;
        /**
         * @var
         */
        protected $_authorname;

        protected $_urltext;
        protected $_metadesc;
        protected $_metakey;
        protected $_metatitle;
        protected $_display_order;
        protected $_bg_col;
        protected $_menu_title;
        protected $_col2_content;
        /**
         * @var
         */
        protected $_allitems;


        /**
         * ProductCategory constructor.
         *
         * @param null   $id
         * @param int    $width
         * @param int    $height
         * @param string $path
         *
         * @throws Exception
         */
        public function __construct($id = null, $width = 500, $height = 500, $path = USER_UPLOADS.'/images/product-categories/')
        {
            //Connect to database
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
                //Assess passed carousel id
                if (isset($id) && !is_numeric($id))
                {
                    throw new Exception('Class ProductCategory requires id to be specified as an integer');
                }

                //Assess passed width & height
                if (!is_numeric($width) || !is_numeric($height))
                {
                    throw new Exception('Class ProductCategory requires width and height to be specified as integers');
                }

                //Assess passed path
                if (isset($path) && !is_string($path))
                {
                    throw new Exception('Class ProductCategory requires path to be specified as a string, eg: /user_uploads/images/ProductCategories/');
                }

                //See if provided path exists - if not - create it
                if (!file_exists(DOCUMENT_ROOT . $path))
                {
                    //Create it
                    $success = mkdir(DOCUMENT_ROOT . $path, 0777, true);
                    if (!$success)
                    {
                        throw new Exception('Directory specified (' . $path . ') does not exist - and cannot be created');
                    }
                }

                //See if subdirectories for 'large' and 'small' images exist in the path specified - if not create them. If fail - Throw Exception
                $large = $path . "large/";
                $small = $path . "small/";
                if (!file_exists(DOCUMENT_ROOT . $large))
                {
                    //Create it
                    $success = mkdir(DOCUMENT_ROOT . $large, 0777, true);
                    if (!$success)
                    {
                        throw new Exception('Directory specified (' . $large . ') does not exist - and cannot be created');
                    }
                }
                if (!file_exists(DOCUMENT_ROOT . $small))
                {
                    //Create it
                    $success = mkdir(DOCUMENT_ROOT . $small, 0777, true);
                    if (!$success)
                    {
                        throw new Exception('Directory specified (' . $small . ') does not exist - and cannot be created');
                    }
                }

                //Retrieve current ProductCategories information
                if (isset($id))
                {
                    $this->_id = $id;
                    $this->getItemById($id);
                }

                //Store the width/height/path etc
                $this->_img_width = $width;
                $this->_img_height = $height;
                $this->_img_path = $path;
            }
        }


        /**
         * Retrieves specified ProductCategories record ID from Test table
         * Populates object member elements
         *
         * @param $id
         */
        public function getItemById($id, $passedDetail = true)
        {
            if (!is_bool($passedDetail)) { $passedDetail = true; }
            
            try
            {
                $sql = "SELECT ID, AES_DECRYPT(Title, :key) AS Title, AES_DECRYPT(URLText, :key) AS URLText, AES_DECRYPT(ImgPath, :key) AS ImgPath, AES_DECRYPT(ImgFilename, :key) AS ImgFilename, DisplayOrder, AES_DECRYPT(BGCol, :key) AS BGCol";
                
                if ($passedDetail === true) {
                    $sql .= ", AES_DECRYPT(Content, :key) AS Content, AES_DECRYPT(MenuTitle, :key) AS MenuTitle, AES_DECRYPT(MetaTitle, :key) AS MetaTitle, AES_DECRYPT(MetaDesc, :key) AS MetaDesc, AES_DECRYPT(MetaKey, :key) AS MetaKey, AES_DECRYPT(AuthorName, :key) AS AuthorName, AuthorID, AES_DECRYPT(Col2Content, :key) AS Col2Content ";
                }
                
                $sql .= " FROM ProductCategories WHERE ID =  :id LIMIT 1";
                
                $stmt = $this->_dbconn->prepare($sql);
                $stmt->execute([
                    'key' => AES_ENCRYPTION_KEY,
                    'id' => $id
                ]);
                $item = $stmt->fetch();
            } catch (Exception $e)
            {
                error_log("Failed to retrieve ProductCategories item details when searching by ID" . $e);
            }

            //Store details in relevant members
            $this->_id = $item['ID'];
            $this->_title = $item['Title'];
            $this->_img_filename = $item['ImgFilename'];
            $this->_img_path = $item['ImgPath'];
            $this->_content = $item['Content'];
            $this->_authorid = $item['AuthorID'];
            $this->_authorname = $item['AuthorName'];
            $this->_urltext = $item['URLText'];
            $this->_metadesc = $item['MetaDesc'];
            $this->_metakey = $item['MetaKey'];
            $this->_metatitle = $item['MetaTitle'];
            $this->_display_order = $item['DisplayOrder'];
            $this->_bg_col = $item['BGCol'];
            $this->_menu_title = $item['MenuTitle'];
            $this->_col2_content = $item['Col2Content'];

            return $item;
        }

        public function getItemByUrl($urltext)
        {
            try
            {
                $stmt = $this->_dbconn->prepare("SELECT ID FROM ProductCategories WHERE CONVERT(AES_DECRYPT(URLText, :key) USING utf8) LIKE :urltext LIMIT 1");
                $stmt->execute([
                    'key' => AES_ENCRYPTION_KEY,
                    'urltext' => $urltext
                ]);
                $item = $stmt->fetch();
            } catch (Exception $e)
            {
                error_log("Failed to retrieve ProductCategories item details when searching by URLText" . $e);
            }
            //Use other function to populate rest of properties (no sense writing it twice!)_
            return $this->getItemById($item['ID']);
        }


        /**
         * Receive an image data stream
         *  - process the image based on path and size settings - using large and small as subdirs for file storage (these get created the first time the object is called)
         *  - store it in the file system
         *  - store it in the new object ahead of saving
         *  - save the object based on the current state of the data - DESIRABLE?
         *
         * @param     $ImageStream
         * @param int $thumbnailWidth
         * @param int $thumbnailHeight
         *
         * @throws Exception
         */
        public function uploadImage($ImageStream)
        {
            //Check we have some useful data passed
            if ($ImageStream == '')
            {
                throw new Exception("You must supply a file stream to this function.");
            }

            //Create ImageHandler
            $ImgObj = new ImageHandler($this->_img_path, true);
            //Set up some defaults
            $aspect = $this->_img_height / $this->_img_width;
            $thumb_width = 400;
            $thumb_height = floor(400 * $aspect);
            $ImgObj->setSourceFileType('png');
            $ImgObj->setDestFileType('png');
            $ImgObj->setFlagMaintainTransparency(true);
            $ImgObj->setImageWidth($this->_img_width);
            $ImgObj->setImageHeight($this->_img_height);
            $ImgObj->setThumbWidth($thumb_width);
            $ImgObj->setThumbHeight($thumb_height);
            $ImgObj->createFilename($this->_title);


            $result = $ImgObj->processImage($ImageStream);
            $newFilename = $ImgObj->getImgFilename();


            if ($result === true)
            {
                //Delete the old image
                $this->deleteImage();

                //Store the new filename
                $this->_img_filename = $newFilename;

                //Save the object in its current state
                $this->saveItem();
            }
        }


        /**
         * Delete the image for this ProductCategories item - assuming _img_filename is set
         *
         * @return mixed
         */
        public function deleteImage()
        {
            if (!is_string($this->_img_filename) || $this->_img_filename == '')
            {
                error_log("Sorry - there was no image to delete");

                return;
            }

            $OldImg = new ImageHandler($this->_img_path, true);
            $OldImg->setImgFilename($this->_img_filename);
            $OldImg->deleteImage();

            $this->_img_filename = '';
            $this->saveItem();
        }

        /**
         * Saves the current object to the ProductCategories table in the database
         *
         * @throws Exception
         */
        public function saveItem()
        {
            //First need to determine if this is a new ProductCategories item
            if ($this->_id <= 0)
            {
                $this->createNewItem(); //_id should now be set
            }

            try
            {
                $stmt = $this->_dbconn->prepare("UPDATE ProductCategories SET Title = AES_ENCRYPT(:title, :key), Content = AES_ENCRYPT(:content, :key), ImgFilename = AES_ENCRYPT(:imgfilename, :key), ImgPath = AES_ENCRYPT(:imgpath, :key), URLText = AES_ENCRYPT(:urltext, :key), MetaDesc = AES_ENCRYPT(:metadesc, :key), MetaKey = AES_ENCRYPT(:metakey, :key), MetaTitle = AES_ENCRYPT(:metatitle, :key), AuthorID = :authorid, AuthorName = AES_ENCRYPT(:authorname, :key), DisplayOrder = :displayorder, BGCol = AES_ENCRYPT(:bgcol, :key), Col2Content = AES_ENCRYPT(:col2content, :key) WHERE ID = :id LIMIT 1");
                $result = $stmt->execute([
                                            'key' => AES_ENCRYPTION_KEY,
                                             'title' => $this->_title,
                                             'content' => $this->_content,
                                             'imgfilename' => $this->_img_filename,
                                             'imgpath' => $this->_img_path,
                                             'urltext' => $this->_urltext,
                                             'metadesc' => $this->_metadesc,
                                             'metakey' => $this->_metakey,
                                             'metatitle' => $this->_metatitle,
                                             'authorid' => $this->_authorid,
                                             'authorname' => $this->_authorname,
                                             'bgcol' => $this->_bg_col,
                                             'displayorder' => $this->_display_order,
                                             'col2content' => $this->_col2_content,
                                             'id' => $this->_id
                                         ]);
                if ($result === true)
                {
                    return true;
                }
                else
                {
                    error_log($stmt->errorInfo()[2]);
                    return false;
                }
            } catch (Exception $e)
            {
                error_log("Failed to save ProductCategories record: " . $e);
            }
        }

        /**
         * Create new empty ProductCategories item
         *
         * Sets the _id property accordingly
         */
        public function createNewItem()
        {
            //Only execute if the _id property isn't already set
            if (is_numeric($this->_id) || $this->_id > 0)
            {
                throw new Exception('You cannot create a new ProductCategories item at this stage - the id property is already set as ' . $this->_id);
            }

            //Create DB item
            try
            {
                $result = $this->_dbconn->query("INSERT INTO ProductCategories SET Title = NULL");
                $lastID = $this->_dbconn->lastInsertId();

                if ($lastID > 0)
                {
                    $this->_id = $lastID;
                }
            } catch (Exception $e)
            {
                error_log("Failed to create new ProductCategories record: " . $e);
            }
        }

        /**
         * Returns all ProductCategories records and fields in Assoc array
         * Flags for retrieving just Panel items OR All future items OR All items OR Just past items
         *
         * @param string    Panel | Current | All | Past
         * @param int       Cut off in months for items
         *
         * @return mixed
         */
        public function listAllItems($passedDetail = true)
        {
            if (!is_bool($passedDetail)) { $passedDetail = true; }
            
            $sql = "SELECT ID FROM ProductCategories ORDER BY DisplayOrder ASC, AES_DECRYPT(Title, :key) ASC";

            try
            {
                $stmt = $this->_dbconn->prepare($sql);
                $stmt->execute([
                    'key' => AES_ENCRYPTION_KEY
                ]);
                $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $e)
            {
                error_log("Failed to retrieve ProductCategories items" . $e);
            }

            if (is_array($items)) {
                unset($this->_allitems);
                foreach($items as $item) {
                    //Store details in relevant member
                    $this->_allitems[] = $this->getItemById($item['ID'], $passedDetail);
                }
            }

            //return the array
            return $this->_allitems;
        }

        /**
         * Delete the complete ProductCategories item - including any images
         *
         * @return mixed
         */
        public function deleteItem()
        {
            if (!is_numeric($this->_id))
            {
                throw new Exception('Class ProductCategory requires the ProductCategories item ID to be set if you are trying to delete the item');
            }

            //First delete the image files
            $this->deleteImage();

            // Step through all images for this content item AND DELETE
            $ImgDel = new ImageLibrary('ProductCategories', $this->_id, null, null, null, USER_UPLOADS.'/images/gallery/');
            $ImgDel->deleteAllImagesForContent('ProductCategories',$this->_id);

            //Now delete the item from the DB
            try
            {
                $stmt = $this->_dbconn->prepare("DELETE FROM ProductCategories WHERE ID = :id LIMIT 1");
                $result = $stmt->execute([
                                             'id' => $this->_id
                                         ]);
            } catch (Exception $e)
            {
                error_log("Failed to delete ProductCategories record: " . $e);
            }

            if ($result === true)
            {
                //Unset the properties
                $this->_id = null;
                $this->_title = null;
                $this->_img_filename = null;
                $this->_content = null;
                $this->_authorid = null;
                $this->_authorname = null;
                $this->_urltext = null;
                $this->_metadesc = null;
                $this->_metakey = null;
                $this->_metatitle = null;
                $this->_display_order = null;
                $this->_bg_col = null;

                return true;
            }
            else
            {
                return false;
            }

        }


        /**
         * Function to check if a similar URL already exists in the ProductCategories table
         * Returns TRUE if VALID, ie: not present in database
         *
         *
         *
         * @param int $ID
         * @param     $ContentURL
         *
         * @return bool
         * @throws Exception
         */

        public function URLTextValid($ID = 0, $ContentURL)
        {
            if ($ID <= 0)
            {
                $ID = $this->_id;
            }
            if (!is_string($ContentURL))
            {
                throw new Exception('ProductCategory needs the new URL specifying as a string');
            }


            if (clean_int($ID) > 0)
            {
                $sql = "SELECT ID FROM ProductCategories WHERE CONVERT(AES_DECRYPT(URLText, :key) USING utf8) LIKE :urltext AND ID != :id";
                $vars = array('urltext' => $ContentURL, 'id' => $ID, 'key' => AES_ENCRYPTION_KEY);
            }
            else
            {
                $sql = "SELECT ID FROM ProductCategories WHERE CONVERT(AES_DECRYPT(URLText, :key) USING utf8) = :urltext AND (ID IS NULL OR ID <= 0)";
                $vars = array('urltext' => $ContentURL, 'key' => AES_ENCRYPTION_KEY);
            }


            // Execute query
            $stmt = $this->_dbconn->prepare($sql);
            $result = $stmt->execute($vars);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($rows) > 0)
            {
                return false;
            }
            else
            {
                return true;
            }
        }



        /**
         * Function to search all content for passed string. We will search the following fields:
         *  - Title         (weight: 20 - level 1)
         *  - Content       (weight: 10 - level 3)
         *
         * Will return array of arrays:
         * array('ID','Title,'SubTitle','FullURLText','Weight');  The Full URL will be provided - to cover lower level content items - this will need to be derived.
         *
         * The search will only be carried out where the parent item is present in a menu (ie there is an entry in the ContentByType table for the parent/Toplevel ContentID).
         *
         * @param   mixed   $needle
         * @return  mixed   array
         */
        function searchContent($needle = '')
        {
            if ($needle == '')
            {
                return array();
            }

            //$results = array('Level1'=>array(),'Level2'=>array(),'Level3'=>array());
            $search_field = strtolower(filter_var($needle, FILTER_SANITIZE_STRING));
            $search_criteria = "%" . $needle . "%";

            $search_results = array();

            //Search content as outlined above
            //Return IDs
            $sql = "SELECT ID FROM ProductCategories WHERE (CONVERT(AES_DECRYPT(Title, :key) USING utf8) LIKE :needle OR CONVERT(AES_DECRYPT(Content, :key) USING utf8) LIKE :needle)";
            $stmt = $this->_dbconn->prepare($sql);
            $stmt->execute([
                               'needle' => $search_criteria
                           ]);
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
            {
                //Retrieve full data
                $content = $this->getItemById($row['ID']);

                //Prepare link
                unset($link);
                if ($content['URLText'] != '')
                {
                    $link = "//" . SITEFQDN . "/cat/" . $content['URLText'];
                }
                else
                {
                    $link = "//" . SITEFQDN . "/content/product-category.php?id=" . $content['ID'];
                }

                //Prepare content
                $Content = FixOutput(substr(strip_tags($content['Content']), 0, 160));

                //Check weighting
                if (strtolower($content['Title']) == $search_field)
                {
                    $Weighting = 0;
                }
                elseif ($search_field == substr(strtolower($content['Title']), 0, strlen($search_field)))
                {
                    $Weighting = 10;
                }
                else
                {
                    $Weighting = 20;
                }
                $content['Weighting'] = $Weighting;

                //Add to search results
                $search_results[] = array('Title' => $content['Title'], 'Content' => $Content, 'Link' => $link, 'DateDisplay' => $content['DateDisplay'], 'Weighting' => $Weighting);
            }

            //Return results
            return $search_results;

        }



        ###########################################################
        # Getters and Setters
        ###########################################################

        /**
         * @return int|string
         */
        public function getID()
        {
            return $this->_id;
        }

        /**
         * @param $id
         */
        public function setID($id)
        {
            $this->_id = $id;
        }

        /**
         * @return mixed
         */
        public function getImgFilename()
        {
            return $this->_img_filename;
        }

        /**
         * @param $imgfilename
         */
        public function setImgFilename($imgfilename)
        {
            $this->_img_filename = $imgfilename;
        }

        /**
         * @return mixed
         */
        public function getTitle()
        {
            return $this->_title;
        }

        /**
         * @param $title
         */
        public function setTitle($title)
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
         * @param $content
         */
        public function setContent($content)
        {
            $this->_content = $content;
        }

        /**
         * @return mixed
         */
        public function getAuthorID()
        {
            return $this->_authorid;
        }

        /**
         * @param $authorid
         */
        public function setAuthorID($authorid)
        {
            $this->_authorid = $authorid;
        }

        /**
         * @return mixed
         */
        public function getAuthorName()
        {
            return $this->_authorname;
        }

        /**
         * @param $authorname
         */
        public function setAuthorName($authorname)
        {
            $this->_authorname = $authorname;
        }

        public function getURLText()
        {
            return $this->_urltext;
        }

        public function setURLText($urltext)
        {
            $this->_urltext = $urltext;
        }

        public function getMetaDesc()
        {
            return $this->_metadesc;
        }

        public function setMetaDesc($metadesc)
        {
            $this->_metadesc = $metadesc;
        }

        public function getMetaKey()
        {
            return $this->_metakey;
        }

        public function setMetaKey($metakey)
        {
            $this->_metakey = $metakey;
        }

        public function getMetaTitle()
        {
            return $this->_metatitle;
        }

        public function setMetaTitle($metatitle)
        {
            $this->_metatitle = $metatitle;
        }

        public function getImgPath()
        {
            return $this->_img_path;
        }

        public function setImgPath($image_path)
        {
            $this->_img_path = $image_path;
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
        public function setDisplayOrder($display_order)
        {
            $this->_display_order = $display_order;
        }

        /**
         * @return mixed
         */
        public function getBgCol()
        {
            return $this->_bg_col;
        }

        /**
         * @param mixed $bg_col
         */
        public function setBgCol($bg_col)
        {
            $this->_bg_col = $bg_col;
        }

        /**
         * @return int|string
         */
        public function getImgWidth()
        {
            return $this->_img_width;
        }

        /**
         * @param int|string $img_width
         */
        public function setImgWidth($img_width): void
        {
            $this->_img_width = $img_width;
        }

        /**
         * @return int|string
         */
        public function getImgHeight()
        {
            return $this->_img_height;
        }

        /**
         * @param int|string $img_height
         */
        public function setImgHeight($img_height): void
        {
            $this->_img_height = $img_height;
        }


    }