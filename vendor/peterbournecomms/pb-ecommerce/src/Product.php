<?php

    namespace PeterBourneComms\Ecommerce;

    use PeterBourneComms\CMS\Database;
    use PeterBourneComms\CMS\ImageHandler;
    use PeterBourneComms\CMS\ImageLibrary;
    use PeterBourneComms\Ecommerce\ProductType;
    use PDO;
    use PDOException;
    use Exception;

    /**
     * Deals with Product items
     *
     *
     *
     * @author Peter Bourne
     * @version 1.5
     *
     *          1.0     20/07/20    Original version
     *          1.1     07.06.21    Composer version
     *          1.2     05.10.21    Added PreviousPrice functionality
     *          1.3     11.11.21    Added ProductType functionality for filtering with ProductType class
     *          1.4     15.11.21    added 'fuzzy' mode to listAllItems - to emulate searchContent
     *          1.5     01.11.23    added UKOnly property to ensure some products can only be sold/delivered in UK (for FXPP predominantly)
     *
     */
    class Product
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
        protected $_category_id;
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
        protected $_urltext;
        protected $_metadesc;
        protected $_metakey;
        protected $_metatitle;
        protected $_display_order;

        protected $_one_line_desc;
        protected $_price;
        protected $_vat;
        protected $_related_product_1;
        protected $_related_product_2;
        protected $_related_product_3;
        protected $_related_product_4;
        protected $_new_product;
        protected $_weight;
        protected $_option_title;
        protected $_product_identifier;
        protected $_colour;
        protected $_brand;
        protected $_availability;
        protected $_personalisation;

        protected $_authorname;
        protected $_authorid;
        
        protected $_previous_price;
        protected $_per_cent_discount;
        protected $_saving;

        /**
         * @var
         */
        protected $_allitems;
        protected $_images;
        
        protected $_types;
        protected $_types_detail;
        protected $_uk_only;


        /**
         * Product constructor.
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
                    throw new Exception('Class Product requires id to be specified as an integer');
                }

                //Assess passed width & height
                if (!is_numeric($width) || !is_numeric($height))
                {
                    throw new Exception('Class Product requires width and height to be specified as integers');
                }

                //Assess passed path
                if (isset($path) && !is_string($path))
                {
                    throw new Exception('Class Product requires path to be specified as a string, eg: /user_uploads/images/Products/');
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

                //Retrieve current Products information
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
         * Retrieves specified Products record ID
         * Populates object member elements
         *
         * @param $id
         */
        public function getItemById($id, $passedDetail = true)
        {
            if (!is_bool($passedDetail)) { $passedDetail = true; }
            
            try
            {
                $sql = "SELECT ID, CategoryID, AES_DECRYPT(Title, :key) AS Title, AES_DECRYPT(URLText, :key) AS URLText, AES_DECRYPT(ImgPath, :key) AS ImgPath, AES_DECRYPT(ImgFilename, :key) AS ImgFilename, AES_DECRYPT(OneLineDesc, :key) AS OneLineDesc, AES_DECRYPT(Price, :key) AS Price, AES_DECRYPT(VAT, :key) AS VAT, AES_DECRYPT(NewProduct, :key) AS NewProduct, AES_DECRYPT(Brand, :key) AS Brand, AES_DECRYPT(Availability, :key) AS Availability, DisplayOrder, AES_DECRYPT(PreviousPrice, :key) AS PreviousPrice, AES_DECRYPT(UKOnly, :key) AS UKOnly";
                if ($passedDetail === true) {
                    $sql .= ", AES_DECRYPT(Content, :key) AS Content, AES_DECRYPT(MetaTitle, :key) AS MetaTitle, AES_DECRYPT(MetaDesc, :key) AS MetaDesc, AES_DECRYPT(MetaKey, :key) AS MetaKey, AES_DECRYPT(AuthorName, :key) AS AuthorName, AES_DECRYPT(Weight, :key) AS Weight, AES_DECRYPT(OptionTitle, :key) AS OptionTitle, AES_DECRYPT(ProductIdentifier, :key) AS ProductIdentifier, AES_DECRYPT(Colour, :key) AS Colour, AES_DECRYPT(Personalisation, :key) AS Personalisation, RelatedProduct1, RelatedProduct2, RelatedProduct3, RelatedProduct4, AuthorID, AES_DECRYPT(UKOnly, :key) AS UKOnly ";
                }
                
                $sql .= " FROM Products WHERE ID =  :id LIMIT 1";
                $stmt = $this->_dbconn->prepare($sql);
                $stmt->execute(['id' => $id, 'key'=>AES_ENCRYPTION_KEY]);
                $item = $stmt->fetch();
            } catch (Exception $e)
            {
                error_log("Failed to retrieve Products item details when searching by ID" . $e);
            }

            //Store details in relevant members
            $this->_id = $item['ID'];
            $this->_category_id = $item['CategoryID'];
            $this->_title = $item['Title'];
            $this->_content = $item['Content'];
            $this->_img_filename = $item['ImgFilename'];
            $this->_img_path = $item['ImgPath'];
            $this->_authorid = $item['AuthorID'];
            $this->_authorname = $item['AuthorName'];
            $this->_urltext = $item['URLText'];
            $this->_metadesc = $item['MetaDesc'];
            $this->_metakey = $item['MetaKey'];
            $this->_metatitle = $item['MetaTitle'];
            $this->_display_order = $item['DisplayOrder'];
            $this->_one_line_desc = $item['OneLineDesc'];
            $this->_price = $item['Price'];
            $this->_vat = $item['VAT'];
            $this->_new_product = $item['NewProduct'];
            $this->_weight = $item['Weight'];
            $this->_option_title = $item['OptionTitle'];
            $this->_product_identifier = $item['ProductIdentifier'];
            $this->_colour = $item['Colour'];
            $this->_brand = $item['Brand'];
            $this->_availability = $item['Availability'];
            $this->_personalisation = $item['Personalisation'];
            $this->_related_product_1 = $item['RelatedProduct1'];
            $this->_related_product_2 = $item['RelatedProduct2'];
            $this->_related_product_3 = $item['RelatedProduct3'];
            $this->_related_product_4 = $item['RelatedProduct4'];
            $this->_previous_price = $item['PreviousPrice'];
            $this->_uk_only = $item['UKOnly'];
            
    
            //Now retrieve category info
            $PCO = new ProductCategory();
            if (is_object($PCO)) {
                $CategoryInfo = $PCO->getItemById($this->_category_id, $passedDetail);
                if (is_array($CategoryInfo) && count($CategoryInfo) > 0) {
                    $item['CategoryInfo'] = $CategoryInfo;
                }
            }
            
            //Further detail stuff
            if ($passedDetail === true) {
                
                //Retrieve product images
                $this->_images = $this->listProductImages();
                $item['Images'] = $this->_images;
    
                //Options
                $options = $this->updateProductOption('select-all',array('ProductID'=>$this->_id));
                if (is_array($options) && count($options) > 0) {
                    $item['ProductOptions'] = $options;
                }
    
                //Optional Extras
                $extras = $this->updateProductOptionalExtras('select-all',array('ProductID'=>$this->_id));
                if (is_array($extras) && count($extras) > 0) {
                    $item['ProductOptionalExtras'] = $extras;
                }
            }
            
            
            //Previous price
            if (is_numeric($this->_previous_price) && $this->_previous_price > 0) {
                $this->_saving = number_format($this->_previous_price - $this->_price,2);
                $this->_per_cent_discount = number_format(($this->_saving/$this->_previous_price) * 100,0);
                $item['Saving'] = $this->_saving;
                $item['PerCentDiscount'] = $this->_per_cent_discount;
            }
            
            //Product Types
            $types = $this->populateTypes();
            $types_detail = $this->populateTypesDetail();
            
            $item['ProductTypes'] = $types;
            $item['ProductTypesDetail'] = $types_detail;


            return $item;
        }

        public function getItemByUrl($urltext)
        {
            try
            {
                $stmt = $this->_dbconn->prepare("SELECT ID FROM Products WHERE CONVERT(AES_DECRYPT(URLText, :key) USING utf8) LIKE :urltext LIMIT 1");
                $stmt->execute(['urltext' => $urltext, 'key'=>AES_ENCRYPTION_KEY]);
                $item = $stmt->fetch();
            } catch (Exception $e)
            {
                error_log("Failed to retrieve Products item details when searching by URLText" . $e);
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
            $ImgObj->setSourceFileType('png');
            $ImgObj->setDestFileType('png');
            $ImgObj->setFlagMaintainTransparency(true);
            $ImgObj->setImageWidth($this->_img_width);
            $ImgObj->setImageHeight($this->_img_height);
            $ImgObj->setThumbWidth(floor($this->_img_width / 3));
            $ImgObj->setThumbHeight(floor($this->_img_height / 3));
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
         * Delete the image for this Products item - assuming _img_filename is set
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
         * Saves the current object to the Products table in the database
         *
         * @throws Exception
         */
        public function saveItem()
        {
            if ($this->_id <= 0)
            {
                $this->createNewItem(); //_id should now be set
            }

            try
            {
                $stmt = $this->_dbconn->prepare("UPDATE Products SET CategoryID = :categoryid, Title = AES_ENCRYPT(:title, :key), Content = AES_ENCRYPT(:content, :key), URLText = AES_ENCRYPT(:urltext, :key), MetaTitle = AES_ENCRYPT(:metatitle, :key), MetaDesc = AES_ENCRYPT(:metadesc, :key), MetaKey = AES_ENCRYPT(:metakey, :key), ImgPath = AES_ENCRYPT(:imgpath, :key), ImgFilename = AES_ENCRYPT(:imgfilename, :key), AuthorName = AES_ENCRYPT(:authorname, :key), OneLineDesc = AES_ENCRYPT(:onelinedesc, :key), Price = AES_ENCRYPT(:price, :key), VAT = AES_ENCRYPT(:vat, :key), NewProduct = AES_ENCRYPT(:newproduct, :key), Weight = AES_ENCRYPT(:weight, :key), OptionTitle = AES_ENCRYPT(:optiontitle, :key), Productidentifier = AES_ENCRYPT(:productidentifier, :key), Colour = AES_ENCRYPT(:colour, :key), Brand = AES_ENCRYPT(:brand, :key), Availability = AES_ENCRYPT(:availability, :key), Personalisation = AES_ENCRYPT(:personalisation, :key), RelatedProduct1 = :relatedproduct1, RelatedProduct2 = :relatedproduct2, RelatedProduct3 = :relatedproduct3, RelatedProduct4 = :relatedproduct4, AuthorID = :authorid, DisplayOrder = :displayorder, PreviousPrice = AES_ENCRYPT(:previous_price, :key), UKOnly = AES_ENCRYPT(:uk_only, :key) WHERE ID = :id LIMIT 1");
                $result = $stmt->execute([
                                             'key'=>AES_ENCRYPTION_KEY,
                                             'categoryid' => $this->_category_id,
                                             'title' => $this->_title,
                                             'content' => $this->_content,
                                            'urltext' => $this->_urltext,
                                            'imgfilename' => $this->_img_filename,
                                            'imgpath' => $this->_img_path,
                                             'metadesc' => $this->_metadesc,
                                             'metakey' => $this->_metakey,
                                             'metatitle' => $this->_metatitle,
                                             'onelinedesc' => $this->_one_line_desc,
                                             'price' => $this->_price,
                                             'vat' => $this->_vat,
                                             'newproduct' => $this->_new_product,
                                             'weight' => $this->_weight,
                                             'optiontitle' => $this->_option_title,
                                             'productidentifier' => $this->_product_identifier,
                                             'colour' => $this->_colour,
                                             'brand' => $this->_brand,
                                             'availability' => $this->_availability,
                                             'personalisation' => $this->_personalisation,
                                             'relatedproduct1' => $this->_related_product_1,
                                             'relatedproduct2' => $this->_related_product_2,
                                             'relatedproduct3' => $this->_related_product_3,
                                             'relatedproduct4' => $this->_related_product_4,
                                             'authorid' => $this->_authorid,
                                             'authorname' => $this->_authorname,
                                             'displayorder' => $this->_display_order,
                                             'previous_price' => $this->_previous_price,
                                             'uk_only' => $this->_uk_only,
                                             'id' => $this->_id
                                         ]);
                if ($result === true) {
                    //Update the types
                    $this->updateTypes();
                    return true;
                } else {
                    error_log($stmt->errorInfo()[2]);
                    return false;
                }
            } catch (Exception $e)
            {
                error_log("Failed to save Products record: " . $e);
            }
        }

        /**
         * Create new empty Products item
         *
         * Sets the _id property accordingly
         */
        public function createNewItem()
        {
            //Only execute if the _id property isn't already set
            if (is_numeric($this->_id) || $this->_id > 0)
            {
                throw new Exception('You cannot create a new Products item at this stage - the id property is already set as ' . $this->_id);
            }

            //Create DB item
            try
            {
                $result = $this->_dbconn->query("INSERT INTO Products SET DisplayOrder = 10000");
                $lastID = $this->_dbconn->lastInsertId();

                if ($lastID > 0)
                {
                    $this->_id = $lastID;
                }
            } catch (Exception $e)
            {
                error_log("Failed to create new Products record: " . $e);
            }
        }

        /**
         * Returns all Products records and fields in Assoc array
         * Flags for retrieving just Panel items OR All future items OR All items OR Just past items
         *
         * @param string    Panel | Current | All | Past
         * @param int       Cut off in months for items
         *
         * @return mixed
         */
        public function listAllItems($passedNeedle = null, $passedMode = null, $passedCatIDFilter = 0, $passedDetail = true)
        {
            if (!is_bool($passedDetail)) { $passedDetail = true; }
            if (!is_numeric($passedCatIDFilter)) { $passedCatIDFilter = 0; }
            
            $basesql = "SELECT Products.ID FROM Products LEFT JOIN ProductCategories ON ProductCategories.ID = Products.CategoryID ";
            $sort = " ORDER BY AES_DECRYPT(ProductCategories.Title, :key) ASC, Products.DisplayOrder ASC, AES_DECRYPT(Products.Title, :key) ASC";
            $params = array('key' => AES_ENCRYPTION_KEY);

            //Build SQL depending on passedMode and passedNeedle
            switch($passedMode) {
                case 'id':
                    //echo "1<br/>";
                    $query = " WHERE (Products.ID = :needle) ";
                    $params['needle'] = $passedNeedle;
                    break;

                case 'category-id':
                    //echo "2<br/>";
                    $query = " WHERE (Products.CategoryID = :needle) ";
                    $params['needle'] = $passedNeedle;
                    break;

                case 'category-title':
                    //echo "3<br/>";
                    $basesql = "SELECT Products.ID FROM Products LEFT JOIN ProductCategories ON ProductCategories.ID = Products.CategoryID ";
                    $query = " WHERE (CONVERT(AES_DECRYPT(Categories.Title, :key) USING utf8) LIKE :needle)";
                    $params['needle'] = $passedNeedle . "%";
                    break;

                case 'title':
                    //echo "4<br/>";
                    $query = " WHERE (CONVERT(AES_DECRYPT(Products.Title, :key) USING utf8) LIKE :needle)";
                    $params['needle'] = "%" . $passedNeedle . "%";
                    break;

                case 'latest':
                    //echo "5<br/>";
                    $query = " WHERE (CONVERT(AES_DECRYPT(Products.NewProduct, :key) USING utf8) = 'Y')";
                    break;
                    
                case 'type-id':
                    //echo "6<br/>";
                    $query = " LEFT JOIN ProductsByType ON ProductsByType.ProductID = Products.ID WHERE (ProductsByType.TypeID = :needle) ";
                    $params['needle'] = $passedNeedle;
                    break;
                    
                case 'type-title':
                    //echo "7<br/>";
                    $query = " LEFT JOIN ProductsByType ON ProductsByType.ProductID = Products.ID LEFT JOIN ProductTypes ON ProductTypes.ID = ProductsByType.TypeID WHERE (CONVERT(AES_DECRYPT(ProductTypes.Title, :key) USING utf8) LIKE :needle) ";
                    $params['needle'] = $passedNeedle."%";
                    break;
                    
                case 'fuzzy':
                    //echo "8<br/>";
                    $query = " WHERE (CONVERT(AES_DECRYPT(Products.Title, :key) USING utf8) LIKE :needle OR CONVERT(AES_DECRYPT(Products.Content, :key) USING utf8) LIKE :needle OR CONVERT(AES_DECRYPT(Products.OneLineDesc, :key) USING utf8) LIKE :needle)";
                    $params['needle'] = "%".$passedNeedle."%";
                    break;

                default:
                    //echo "9<br/>";
                    $query = "";
                    break;
            }
            
            //CategoryID Filter?
            if (is_numeric($passedCatIDFilter) && $passedCatIDFilter > 0) {
                $query .= " AND (CategoryID = :catid) ";
                $params['catid'] = $passedCatIDFilter;
            }

            $sql = $basesql.$query.$sort;
            
            //echo $sql."<br/><br/>";
            //echo "needle = ".$passedNeedle."<br/><br/>";

            try
            {
                $stmt = $this->_dbconn->prepare($sql);
                $stmt->execute($params);
                $items = array();
                while ($item = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $items[] = $this->getItemById($item['ID'], $passedDetail);
                }
            } catch (Exception $e)
            {
                error_log("Failed to retrieve Products items" . $e);
            }

            //Store details in relevant member
            $this->_allitems = $items;

            //return the array
            return $items;
        }



        /**
         * Delete the complete Products item - including any images
         *
         * @return mixed
         */
        public function deleteItem()
        {
            if (!is_numeric($this->_id))
            {
                throw new Exception('Class Product requires the Products item ID to be set if you are trying to delete the item');
            }

            //First delete the image files
            $this->deleteImage();

            // Step through all images for this content item AND DELETE
            $ImgDel = new ImageLibrary('Products', $this->_id, null, null, null, USER_UPLOADS.'/images/gallery/');
            $ImgDel->deleteAllImagesForContent('Products',$this->_id);

            //Delete product options
            $this->updateProductOption('delete-all',array('ProductID' => $this->_id));

            //Delete product optional extras
            $this->updateProductOptionalExtras('delete-all',array('ProductID' => $this->_id));

            //Now delete the item from the DB
            try
            {
                $stmt = $this->_dbconn->prepare("DELETE FROM Products WHERE ID = :id LIMIT 1");
                $result = $stmt->execute([
                                             'id' => $this->_id
                                         ]);
            } catch (Exception $e)
            {
                error_log("Failed to delete Products record: " . $e);
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

                return true;
            }
            else
            {
                return false;
            }

        }


        /**
         * Function to check if a similar URL already exists in the Products table
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
                throw new Exception('Product needs the new URL specifying as a string');
            }


            if (clean_int($ID) > 0)
            {
                $sql = "SELECT ID FROM Products WHERE CONVERT(AES_DECRYPT(URLText, :key) USING utf8) LIKE :urltext AND ID != :id";
                $vars = array('urltext' => $ContentURL, 'id' => $ID, 'key'=>AES_ENCRYPTION_KEY);
            }
            else
            {
                $sql = "SELECT ID FROM Products WHERE CONVERT(AES_DECRYPT(URLText, :key) USING utf8) LIKE :urltext  AND (ID IS NULL OR ID <= 0)";
                $vars = array('urltext' => $ContentURL, 'key'=>AES_ENCRYPTION_KEY);
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
            $sql = "SELECT ID FROM Products WHERE (CONVERT(AES_DECRYPT(Title, :key) USING utf8) LIKE :needle OR CONVERT(AES_DECRYPT(Content, :key) USING utf8) LIKE :needle OR CONVERT(AES_DECRYPT(OneLineDesc, :key) USING utf8) LIKE :needle)";
            $stmt = $this->_dbconn->prepare($sql);
            $stmt->execute([
                                'needle' => $search_criteria,
                                'key' => AES_ENCRYPTION_KEY
                           ]);
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC))
            {
                //Retrieve full data
                $content = $this->getItemById($row['ID']);

                //Prepare link
                unset($link);
                if ($content['URLText'] != '')
                {
                    $link = "//" . SITEFQDN . "/product/" . $content['URLText'];
                }
                else
                {
                    $link = "//" . SITEFQDN . "/content/product-detail.php?id=" . $content['ID'];
                }

                //Prepare content
                $Content = FixOutput(substr(strip_tags($content['OneLineDesc']), 0, 160));

                //Check weighting
                if (strtolower($content['Title']) == $search_field)
                {
                    $Weighting = 0;
                }
                elseif ($search_field == substr(strtolower($content['Title']), 0, strlen($search_field)) || $search_field == substr(strtolower($content['OneLineDesc']), 0, strlen($search_field)))
                {
                    $Weighting = 10;
                }
                else
                {
                    $Weighting = 20;
                }
                $content['Weighting'] = $Weighting;

                //Add to search results
                $search_results[] = array('Title' => $content['Title'], 'Content' => $Content, 'Link' => $link, 'DateDisplay' => $content['DateDisplay'], 'Weighting' => $Weighting, 'ImgPath' => $content['ImgPath'], 'ImgFilename' => $content['ImgFilename'], 'Price' => $content['Price']);
            }

            //Return results
            return $search_results;

        }



        /**
         * Retrieve all images for this product
         *
         */
        public function listProductImages($productid = 0)
        {
            if (!is_numeric($productid) || clean_int($productid) <= 0)
            {
                $productid = $this->_id;
            }
            if (!is_numeric($productid) || clean_int($productid) <= 0)
            {
                error_log("Product->listProductImages() requires product id to be passed or set");
                return false;
            }

            $IO = new ImageLibrary('Products',$productid,null,1200,360,'/user_uploads/images/products/');
            if (is_object($IO))
            {
                $Images = $IO->retrieveAllImages('Products',$productid);
                if (is_array($Images) && count($Images) > 0)
                {
                    return $Images;
                }
            }
            return false;
        }




        /**
         * Set product option
         * @param string $mode
         * @param null $option
         * @return array|bool
         */
        public function updateProductOption($mode = 'insert', $option = null) {

            //Check option array
            if (($mode == 'standardColours' || $mode == 'insert' || $mode == 'update' || $mode == 'delete' || $mode == 'delete-all' || $mode == 'select') && (!is_array($option) || count($option) <= 0)) {
                error_log("Product->setProductOption() requires option to be an array");
                return false;
            }

            $retval = true;
            if ($mode == 'delete' || $mode == 'delete-all') {
                if ($mode == 'delete' && $option['RecordID'] <= 0) {
                    error_log("Product->setProductOption() requires option['RecordID'] to be set for DELETE");
                    $retval = false;
                }
                if ($mode == 'delete-all' && $option['ProductID'] <= 0) {
                    error_log("Product->setProductOption() requires option['ProductID'] to be set for DELETE ALL");
                    $retval = false;
                }
            } elseif ($mode == 'insert' || $mode == 'update') {
                if ($option['ProductID'] <= 0) {
                    error_log("Product->setProductOption() requires option['ProductID'] to be set");
                    $retval = false;
                }
                if ($option['Title'] == '') {
                    error_log("Product->setProductOption() requires option['Title'] to be set");
                    $retval = false;
                }
                if ($option['Price'] < 0 || $option['Price'] == '') {
                    error_log("Product->setProductOption() requires option['Price'] to be set");
                    $retval = false;
                }
                if ($mode == 'update' && $option['RecordID'] == '') {
                    error_log("Product->setProductOption() requires option['RecordID'] to be set if UPDATE");
                    $retval = false;
                }
            } elseif ($mode == 'standardColours') {
                if ($option['ProductID'] <= 0) {
                    error_log("Product->setProductOption() requires option['ProductID'] to be set");
                    $retval = false;
                }
            }

            if ($retval === false) { return false; }

            if ($mode == 'standardColours') {

                $colours = array();
                $colours[] = array('Title'=>'Black matt (Black)','Price'=>'59.00','SKU'=>'','DisplayOrder'=>'10','Availability'=>'Y');
                $colours[] = array('Title'=>'Pebble Grey matt (Sage Green)','Price'=>'59.00','SKU'=>'','DisplayOrder'=>'20','Availability'=>'Y');
                $colours[] = array('Title'=>'Honey Beige matt (Cream)','Price'=>'59.00','SKU'=>'','DisplayOrder'=>'30','Availability'=>'Y');
                $colours[] = array('Title'=>'Blue Mink matt (Powder Blue)','Price'=>'59.00','SKU'=>'','DisplayOrder'=>'40','Availability'=>'Y');
                $colours[] = array('Title'=>'Grey Beige matt (Light Brown)','Price'=>'59.00','SKU'=>'','DisplayOrder'=>'50','Availability'=>'Y');

                $sql = "INSERT INTO ProductOptions SET ProductID = :productid, Title = AES_ENCRYPT(:title, :key), Price = AES_ENCRYPT(:price, :key), DisplayOrder = :displayorder, Availability = AES_ENCRYPT(:availability, :key) ";

                try
                {
                    $stmt = $this->_dbconn->prepare($sql);
                    foreach($colours as $colour) {
                        $params = array();
                        $params['key'] = AES_ENCRYPTION_KEY;
                        $params['productid'] = $option['ProductID'];
                        $params['title'] = $colour['Title'];
                        $params['price'] = $colour['Price'];
                        $params['sku'] = $colour['SKU'];
                        $params['displayorder'] = $colour['DisplayOrder'];
                        $params['availability'] = $colour['Availability'];

                        $stmt->execute($params);
                    }
                    return true;
                } catch (Exception $e)
                {
                    error_log("Failed to store standard Colour Options " . $e);
                }
            } else {

                $params = array();

                switch ($mode) {
                    case 'insert':
                        $action = "INSERT INTO ProductOptions ";
                        $where = "";
                        break;

                    case 'update':
                        $action = "UPDATE ProductOptions ";
                        $where = " WHERE ID = :recid";
                        $params['recid'] = $option['RecordID'];
                        break;

                    case 'delete':
                        $action = "DELETE FROM ProductOptions ";
                        $where = " WHERE ID = :recid LIMIT 1";
                        $params['recid'] = $option['RecordID'];
                        break;

                    case 'delete-all':
                        $action = "DELETE FROM ProductOptions ";
                        $where = " WHERE ProductID = :productid";
                        $params['productid'] = $option['ProductID'];
                        break;

                    case 'select':
                        $action = "SELECT ID, AES_DECRYPT(Title, :key) AS Title, AES_DECRYPT(Price, :key) AS Price, AES_DECRYPT(SKU, :key) AS SKU, AES_DECRYPT(Availability, :key) AS Availability, DisplayOrder FROM ProductOptions ";
                        $where = " WHERE ID = :recid";
                        $order = " ORDER BY DisplayOrder ASC";
                        $params['key'] = AES_ENCRYPTION_KEY;
                        $params['recid'] = $option['RecordID'];
                        break;

                    case 'select-all':
                        $action = "SELECT ID, AES_DECRYPT(Title, :key) AS Title, AES_DECRYPT(Price, :key) AS Price, AES_DECRYPT(SKU, :key) AS SKU, AES_DECRYPT(Availability, :key) AS Availability, DisplayOrder FROM ProductOptions ";
                        $where = " WHERE ProductID = :productid ";
                        $order = " ORDER BY DisplayOrder ASC";
                        $params['key'] = AES_ENCRYPTION_KEY;
                        $params['productid'] = $option['ProductID'];
                        break;
                }

                if ($mode == 'insert' || $mode == 'update') {
                    $query = " SET ProductID = :productid, Title = AES_ENCRYPT(:title, :key), Price = AES_ENCRYPT(:price, :key), SKU = AES_ENCRYPT(:sku, :key), DisplayOrder = :displayorder, Availability = AES_ENCRYPT(:availability, :key) ";
                    $params['productid'] = $option['ProductID'];
                    $params['title'] = $option['Title'];
                    $params['price'] = $option['Price'];
                    $params['sku'] = $option['SKU'];
                    if ($option['DisplayOrder'] <= 0) {
                        $option['DisplayOrder'] = 10000;
                    }
                    $params['displayorder'] = $option['DisplayOrder'];
                    if ($option['Availability'] != 'N') {
                        $option['Availability'] = 'Y';
                    }
                    $params['availability'] = $option['Availability'];
                    $params['key'] = AES_ENCRYPTION_KEY;
                } else {
                    $query = '';
                }

                $sql = $action . $query . $where . $order;

                try {
                    $stmt = $this->_dbconn->prepare($sql);
                    $result = $stmt->execute($params);
                    //print_r($stmt->errorInfo()[2]);
                    if ($mode == 'standardColours' || $mode == 'insert' || $mode == 'update' || $mode == 'delete' || $mode == 'delete-all') {
                        if ($result == true) {
                            return true;
                        } else {
                            error_log('Product->updateProductOptions() - an error occurred inserting up updating');
                            error_log($stmt->errorInfo()[2]);
                            return false;
                        }
                    } else {
                        $items = array();
                        while ($item = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            $items[] = $item;
                        }

                        return $items;
                    }
                } catch (Exception $e) {
                    error_log("Failed to store/update/delete Product Options " . $e);
                }
            }
        }




        /**
         * Set product option
         * @param string $mode
         * @param null $option
         * @return array|bool
         */
        public function updateProductOptionalExtras($mode = 'insert', $option = null) {

            //Check option array
            if (($mode == 'standardColours' || $mode == 'insert' || $mode == 'update' || $mode == 'delete' || $mode == 'delete-all' || $mode == 'select') && (!is_array($option) || count($option) <= 0)) {
                error_log("Product->setProductOptionalExtras() requires option to be an array");
                return false;
            }

            $retval = true;
            if ($mode == 'delete' || $mode == 'delete-all') {
                if ($mode == 'delete' && $option['RecordID'] <= 0) {
                    error_log("Product->setProductOptionalExtras() requires option['RecordID'] to be set for DELETE");
                    $retval = false;
                }
                if ($mode == 'delete-all' && $option['ProductID'] <= 0) {
                    error_log("Product->setProductOptionalExtras() requires option['ProductID'] to be set for DELETE ALL");
                    $retval = false;
                }
            } elseif ($mode == 'insert' || $mode == 'update') {
                if ($option['ProductID'] <= 0) {
                    error_log("Product->setProductOptionalExtras() requires option['ProductID'] to be set");
                    $retval = false;
                }
                if ($option['Title'] == '') {
                    error_log("Product->setProductOptionalExtras() requires option['Title'] to be set");
                    $retval = false;
                }
                if ($option['Price'] < 0 || $option['Price'] == '') {
                    error_log("Product->setProductOptionalExtras() requires option['Price'] to be set");
                    $retval = false;
                }
                if ($mode == 'update' && $option['RecordID'] == '') {
                    error_log("Product->setProductOptionalExtras() requires option['RecordID'] to be set if UPDATE");
                    $retval = false;
                }
            } elseif ($mode == 'standardColours') {
                if ($option['ProductID'] <= 0) {
                    error_log("Product->setProductOption() requires option['ProductID'] to be set");
                    $retval = false;
                }
            }

            if ($retval === false) { return false; }

            if ($mode == 'standardColours') {

                $colours = array();
                $colours[] = array('Title'=>'Black matt (Black)','Price'=>'0','DisplayOrder'=>'10','Availability'=>'Y');
                $colours[] = array('Title'=>'Pebble Grey matt (Sage Green)','Price'=>'0','DisplayOrder'=>'20','Availability'=>'Y');
                $colours[] = array('Title'=>'Honey Beige matt (Cream)','Price'=>'0','DisplayOrder'=>'30','Availability'=>'Y');
                $colours[] = array('Title'=>'Blue Mink matt (Powder Blue)','Price'=>'0','DisplayOrder'=>'40','Availability'=>'Y');
                $colours[] = array('Title'=>'Grey Beige matt (Light Brown)','Price'=>'0','DisplayOrder'=>'50','Availability'=>'Y');

                $sql = "INSERT INTO ProductOptionalExtras SET ProductID = :productid, Title = AES_ENCRYPT(:title, :key), Price = AES_ENCRYPT(:price, :key), DisplayOrder = :displayorder, Availability = AES_ENCRYPT(:availability, :key) ";

                try
                {
                    $stmt = $this->_dbconn->prepare($sql);
                    foreach($colours as $colour) {
                        $params = array();
                        $params['key'] = AES_ENCRYPTION_KEY;
                        $params['productid'] = $option['ProductID'];
                        $params['title'] = $colour['Title'];
                        $params['price'] = $colour['Price'];
                        $params['displayorder'] = $colour['DisplayOrder'];
                        $params['availability'] = $colour['Availability'];

                        $stmt->execute($params);
                    }
                    return true;
                } catch (Exception $e)
                {
                    error_log("Failed to store standard Colour Options " . $e);
                }
            } else {

                $params = array();

                switch ($mode) {
                    case 'insert':
                        $action = "INSERT INTO ProductOptionalExtras ";
                        $where = "";
                        break;

                    case 'update':
                        $action = "UPDATE ProductOptionalExtras ";
                        $where = " WHERE ID = :recid";
                        $params['recid'] = $option['RecordID'];
                        break;

                    case 'delete':
                        $action = "DELETE FROM ProductOptionalExtras ";
                        $where = " WHERE ID = :recid LIMIT 1";
                        $params['recid'] = $option['RecordID'];
                        break;

                    case 'delete-all':
                        $action = "DELETE FROM ProductOptionalExtras ";
                        $where = " WHERE ProductID = :productid";
                        $params['productid'] = $option['ProductID'];
                        break;

                    case 'select':
                        $action = "SELECT ID, AES_DECRYPT(Title, :key) AS Title, AES_DECRYPT(Price, :key) AS Price, DisplayOrder, AES_DECRYPT(Availability, :key) AS Availability FROM ProductOptionalExtras ";
                        $where = " WHERE ID = :recid";
                        $order = " ORDER BY DisplayOrder ASC";
                        $params['key'] = AES_ENCRYPTION_KEY;
                        $params['recid'] = $option['RecordID'];
                        break;

                    case 'select-all':
                        $action = "SELECT ID, AES_DECRYPT(Title, :key) AS Title, AES_DECRYPT(Price, :key) AS Price, DisplayOrder, AES_DECRYPT(Availability, :key) AS Availability FROM ProductOptionalExtras ";
                        $where = " WHERE ProductID = :productid ";
                        $order = " ORDER BY DisplayOrder ASC";
                        $params['key'] = AES_ENCRYPTION_KEY;
                        $params['productid'] = $option['ProductID'];
                        break;
                }

                if ($mode == 'insert' || $mode == 'update') {
                    $query = " SET ProductID = :productid, Title = AES_ENCRYPT(:title, :key), Price = AES_ENCRYPT(:price, :key), DisplayOrder = :displayorder, Availability = AES_ENCRYPT(:availability, :key)";
                    $params['productid'] = $option['ProductID'];
                    $params['title'] = $option['Title'];
                    $params['price'] = $option['Price'];
                    $params['sku'] = $option['SKU'];
                    if ($option['DisplayOrder'] <= 0) {
                        $option['DisplayOrder'] = 10000;
                    }
                    $params['displayorder'] = $option['DisplayOrder'];
                    if ($option['Availability'] != 'N') {
                        $option['Availability'] = 'Y';
                    }
                    $params['availability'] = $option['Availability'];
                    $params['key'] = AES_ENCRYPTION_KEY;
                } else {
                    $query = '';
                }

                $sql = $action.$query.$where.$order;

                try
                {
                    $stmt = $this->_dbconn->prepare($sql);
                    $result = $stmt->execute($params);
                    //print_r($stmt->errorInfo()[2]);
                    if ($mode == 'standardColours' || $mode == 'insert' || $mode == 'update' || $mode == 'delete' || $mode == 'delete-all') {
                        if ($result == true) {
                            return true;
                        } else {
                            error_log('Product->updateProductOptionalExtras() - an error occurred inserting up updating');
                            error_log($stmt->errorInfo()[2]);
                            return false;
                        }
                    } else {
                        $items = array();
                        while ($item = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            $items[] = $item;
                        }

                        return $items;
                    }
                } catch (Exception $e)
                {
                    error_log("Failed to store/update/delete Product Optional Extras " . $e);
                }
            }

        }
        
        
        
        /**
         * Retrieve all product types
         *
         */
        public function getAllProductTypes() {
            $PTO = new ProductType();
            if (!is_object($PTO)) {
                return false;
            }
            
            $Types = $PTO->listAllItems();
            
            return $Types;
        }
    
    
        /**
         * Checks the ProductsByType table - with the two provided IDs to see if a record exists.
         * Return true if it does
         *
         * @param $productid
         * @param $typeid
         *
         * @return bool
         */
        public function checkTypeMatch($productid, $typeid)
        {
            if (is_numeric($productid) && is_numeric($typeid) && $productid > 0 && $typeid > 0)
            {
                try {
                    $stmt = $this->_dbconn->prepare("SELECT ID FROM ProductsByType WHERE ProductID = :productid AND TypeID = :typeid LIMIT 1");
                    $stmt->execute([
                        'productid' => $productid,
                        'typeid' => $typeid
                    ]);
                    $match = $stmt->fetch();
                    if ($match['ID'] >= 1) { return true; } else { return false; }
                } catch (Exception $e)
                {
                    error_log("Failed to retrieve ProductByType records" . $e);
                }
            }
            else
            {
                //throw new Exception("Class Content, Function checkSectionMatch requires contentid and typeid to be integers.");
                return false;
            }
        }
    
    
    
        /**
         * Populates the _types property of the object with an array of the PropertyTypeIDs
         */
        public function populateTypes($id = 0)
        {
            if (!is_numeric($id) || $id <= 0) {
                $id = $this->_id;
            }
            if (!is_numeric($id) || $id <= 0) {
                error_log('Product->populateTypes required product ID to be set or passed');
                return false;
            }
            
            try {
                $stmt = $this->_dbconn->prepare("SELECT TypeID FROM ProductsByType WHERE ProductID = :productid");
                $stmt->execute([
                    'productid' => $id
                ]);
                $types = $stmt->fetchAll();
            } catch (Exception $e) {
                error_log("Failed to populate Products Types: " . $e);
            }
        
            $this->_types = $types;
        
            return $types;
        }
    
        /**
         * Populates the _types_detail property of the object with an array of the PropertyTypeIDs
         */
        public function populateTypesDetail($id = 0)
        {
            if (!is_numeric($id) || $id <= 0) {
                $id = $this->_id;
            }
            if (!is_numeric($id) || $id <= 0) {
                error_log('Product->populateTypesDetail required product ID to be set or passed');
                return false;
            }
        
            try {
                $stmt = $this->_dbconn->prepare("SELECT ProductTypes.ID, AES_DECRYPT(ProductTypes.Title, :key) AS Title, ProductTypes.DisplayOrder, AES_DECRYPT(ProductTypes.URLText, :key) AS URLText FROM ProductTypes LEFT JOIN ProductsByType ON ProductsByType.TypeID = ProductTypes.ID WHERE ProductsByType.ProductID = :productid");
                $stmt->execute([
                    'key' => AES_ENCRYPTION_KEY,
                    'productid' => $id
                ]);
                $types_detail = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                error_log("Failed to populate Products Types: " . $e);
            }
        
            $this->_types_detail = $types_detail;
        
            return $types_detail;
        }
        
        
    
        /**
         * Takes the content of the _types property and updates the ProductsByType table
         *
         */
        public function updateTypes()
        {
            if (is_array($this->_types))
            {
                //First we need to delete all entries for this content
                try {
                    $stmt = $this->_dbconn->prepare("DELETE FROM ProductsByType WHERE ProductID = :productid");
                    $stmt->execute([
                        'productid' => $this->_id
                    ]);
                } catch (Exception $e) {
                    error_log("Failed to delete ProductsByType records.");
                }
            
                //Then we need to post the new records
                //reset($this_>_types);
                $stmt = $this->_dbconn->prepare("INSERT INTO ProductsByType SET ProductID = :productid, TypeID = :typeid");
                for ($i=0; $i < count($this->_types); $i++)
                {
                    $stmt->execute([
                        'productid' => $this->_id,
                        'typeid' => $this->_types[$i]
                    ]);
                }
            }
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
        public function getCategoryId()
        {
            return $this->_category_id;
        }

        /**
         * @param mixed $category_id
         */
        public function setCategoryId($category_id)
        {
            $this->_category_id = $category_id;
        }

        /**
         * @return mixed
         */
        public function getOneLineDesc()
        {
            return $this->_one_line_desc;
        }

        /**
         * @param mixed $one_line_desc
         */
        public function setOneLineDesc($one_line_desc)
        {
            $this->_one_line_desc = $one_line_desc;
        }

        /**
         * @return mixed
         */
        public function getPrice()
        {
            return $this->_price;
        }

        /**
         * @param mixed $price
         */
        public function setPrice($price)
        {
            $this->_price = $price;
        }

        /**
         * @return mixed
         */
        public function getVat()
        {
            return $this->_vat;
        }

        /**
         * @param mixed $vat
         */
        public function setVat($vat)
        {
            $this->_vat = $vat;
        }

        /**
         * @return mixed
         */
        public function getRelatedProduct1()
        {
            return $this->_related_product_1;
        }

        /**
         * @param mixed $related_product_1
         */
        public function setRelatedProduct1($related_product_1)
        {
            $this->_related_product_1 = $related_product_1;
        }

        /**
         * @return mixed
         */
        public function getRelatedProduct2()
        {
            return $this->_related_product_2;
        }

        /**
         * @param mixed $related_product_2
         */
        public function setRelatedProduct2($related_product_2)
        {
            $this->_related_product_2 = $related_product_2;
        }

        /**
         * @return mixed
         */
        public function getRelatedProduct3()
        {
            return $this->_related_product_3;
        }

        /**
         * @param mixed $related_product_3
         */
        public function setRelatedProduct3($related_product_3)
        {
            $this->_related_product_3 = $related_product_3;
        }

        /**
         * @return mixed
         */
        public function getRelatedProduct4()
        {
            return $this->_related_product_4;
        }

        /**
         * @param mixed $related_product_4
         */
        public function setRelatedProduct4($related_product_4)
        {
            $this->_related_product_4 = $related_product_4;
        }

        /**
         * @return mixed
         */
        public function getNewProduct()
        {
            return $this->_new_product;
        }

        /**
         * @param mixed $new_product
         */
        public function setNewProduct($new_product)
        {
            $this->_new_product = $new_product;
        }

        /**
         * @return mixed
         */
        public function getWeight()
        {
            return $this->_weight;
        }

        /**
         * @param mixed $weight
         */
        public function setWeight($weight)
        {
            $this->_weight = $weight;
        }

        /**
         * @return mixed
         */
        public function getOptionTitle()
        {
            return $this->_option_title;
        }

        /**
         * @param mixed $option_title
         */
        public function setOptionTitle($option_title)
        {
            $this->_option_title = $option_title;
        }

        /**
         * @return mixed
         */
        public function getProductIdentifier()
        {
            return $this->_product_identifier;
        }

        /**
         * @param mixed $product_identifier
         */
        public function setProductIdentifier($product_identifier)
        {
            $this->_product_identifier = $product_identifier;
        }

        /**
         * @return mixed
         */
        public function getColour()
        {
            return $this->_colour;
        }

        /**
         * @param mixed $colour
         */
        public function setColour($colour)
        {
            $this->_colour = $colour;
        }

        /**
         * @return mixed
         */
        public function getBrand()
        {
            return $this->_brand;
        }

        /**
         * @param mixed $brand
         */
        public function setBrand($brand)
        {
            $this->_brand = $brand;
        }

        /**
         * @return mixed
         */
        public function getAvailability()
        {
            return $this->_availability;
        }

        /**
         * @param mixed $availability
         */
        public function setAvailability($availability)
        {
            $this->_availability = $availability;
        }

        /**
         * @return mixed
         */
        public function getPersonalisation()
        {
            return $this->_personalisation;
        }

        /**
         * @param mixed $personalisation
         */
        public function setPersonalisation($personalisation)
        {
            $this->_personalisation = $personalisation;
        }

        /**
         * @return mixed
         */
        public function getAllitems()
        {
            return $this->_allitems;
        }

        /**
         * @param mixed $allitems
         */
        public function setAllitems($allitems)
        {
            $this->_allitems = $allitems;
        }

        /**
         * @return mixed
         */
        public function getImages()
        {
            return $this->_images;
        }

        /**
         * @param mixed $images
         */
        public function setImages($images)
        {
            $this->_images = $images;
        }
    
        /**
         * @return mixed
         */
        public function getPreviousPrice()
        {
            return $this->_previous_price;
        }
    
        /**
         * @param mixed $previous_price
         */
        public function setPreviousPrice($previous_price): void
        {
            if (!is_numeric($previous_price) || $previous_price <= 0) { $previous_price = null; }
            $this->_previous_price = $previous_price;
        }
    
        /**
         * @return mixed
         */
        public function getPerCentDiscount()
        {
            return $this->_per_cent_discount;
        }
    
    
        /**
         * @return mixed
         */
        public function getSaving()
        {
            return $this->_saving;
        }
    
        /**
         * @return mixed
         */
        public function getTypes()
        {
            return $this->_types;
        }
    
        /**
         * @param mixed $types
         */
        public function setTypes($types): void
        {
            $this->_types = $types;
        }
        
        /**
         * @return mixed
         */
        public function getUkOnly()
        {
            return $this->_uk_only;
        }
        
        /**
         * @param mixed $uk_only
         */
        public function setUkOnly($uk_only): void
        {
            $this->_uk_only = $uk_only;
        }


        

        

    }