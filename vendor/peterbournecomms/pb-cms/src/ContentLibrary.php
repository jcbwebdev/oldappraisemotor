<?php

    namespace PeterBourneComms\CMS;
    use PeterBourneComms\CMS\Database;
    use PeterBourneComms\CMS\FilenameSanitiser;
    use PeterBourneComms\CMS\ImageResizer;
    use imagick;
    use PDO;
    use PDOException;

    /**
     * Class that deals with ContentLibrary
     * Based on ContentLibrary from Harrison Thorn
     *
     *
     *
     * @author Peter Bourne
     * @version 1.2
     * @history
     *
     *      1.0     05.01.2021      Original version - ContentLibrary
     *      1.1     15.03.2023      Generic CMS version
     *      1.2     12.06.2024      Various additions for PHP 8.x compatibility
     *
     */
    class ContentLibrary
    {
        /**
         * @var mixed
         */
        protected $_dbconn;
        /**
         * @var int|string
         */
        protected $_image_max;
        /**
         * @var int|string
         */
        protected $_thumb_max;
        /**
         * @var string
         */
        protected $_media_path;
        /**
         * @var int|string
         */
        protected $_id;
        /**
         * @var
         */
        protected $_content_id;
        /**
         * @var
         */
        protected $_content_parent_table;
        protected $_media_type;
        protected $_media_filename;
        protected $_display_order;
        protected $_caption;
        protected $_last_edited;
        protected $_media_mime_type;
        protected $_media_extension;
        protected $_media_thumb;


        /**
         * ContentLibrary constructor.
         * @param null $id
         * @param int $imagemax
         * @param int $thumbmax
         * @param string $path
         * @throws Exception
         */
        public function __construct($id = null, $imagemax = 1200, $thumbmax = 560, $path = '/user_uploads/images/content-library/')
        {
            //Connect to database
            if (!$this->_dbconn) {
                try {
                    $conn = new Database();
                    $this->_dbconn = $conn->getConnection();
                } catch (Exception $e) {
                    //handle the exception
                    die;
                }

                //Assess passed id
                if ($id != null && !is_numeric($id)) {
                    throw new Exception('CMS\ContentLibrary->__construct() requires id to be specified as an integer');
                }

                //Assess passed maximum sizes
                if (($imagemax != null && !is_numeric($imagemax)) || ($thumbmax != null && !is_numeric($thumbmax))) {
                    throw new Exception('CMS\ContentLibrary->__construct() requires image and thumbnail maximum sizes to be specified as integers');
                }

                //Assess passed path
                if (isset($path) && !is_string($path)) {
                    throw new Exception('CMS\ContentLibrary->__construct() requires path to be specified as a string, eg: /property-images/');
                }

                //See if provided path exists - if not - create it
                if (!file_exists(DOCUMENT_ROOT . $path)) {
                    //Create it
                    $success = mkdir(DOCUMENT_ROOT . $path, 0777, true);
                    if (!$success) {
                        throw new Exception('CMS\ContentLibrary->__construct() Directory specified ('.$path.') does not exist - and cannot be created');
                    }
                }
                //See if subdirectories for 'large' and 'small' images exist in the path specified - if not create them. If fail - Throw Exception
                //error_log('path = '.$path);
                $large = $path . "/large";
                $small = $path . "/small";
                if (!file_exists(DOCUMENT_ROOT . $large)) {
                    //Create it
                    $success = mkdir(DOCUMENT_ROOT . $large, 0777, true);
                    if (!$success) {
                        throw new Exception('CMS\ContentLibrary->__construct() Directory specified ('.DOCUMENT_ROOT.$large.') does not exist - and cannot be created');
                    }
                }
                if (!file_exists(DOCUMENT_ROOT . $small)) {
                    //Create it
                    $success = mkdir(DOCUMENT_ROOT . $small, 0777, true);
                    if (!$success) {
                        throw new Exception('CMS\ContentLibrary->__construct() Directory specified ('.DOCUMENT_ROOT.$small.') does not exist - and cannot be created');
                    }
                }

                //Store the properties
                $this->_id = $id;
                $this->_image_max = $imagemax;
                $this->_thumb_max = $thumbmax;
                $this->_media_path = $path;
                $this->_media_thumb = 'N';

                //Retrieve current Image information
                if (isset($id)) {
                    $this->getItemById($id);
                }
            }

        }



        public function getItemById($id)
        {
            try {
                $stmt = $this->_dbconn->prepare("SELECT ID, ContentID, AES_DECRYPT(ContentParentTable, :key) AS ContentParentTable, AES_DECRYPT(MediaType, :key) AS MediaType, AES_DECRYPT(MediaPath, :key) AS MediaPath, AES_DECRYPT(MediaFilename, :key) AS MediaFilename, AES_DECRYPT(MediaExtension, :key) AS MediaExtension, AES_DECRYPT(MediaMimeType, :key) AS MediaMimeType, AES_DECRYPT(Caption, :key) AS Caption, DisplayOrder, LastEdited, AES_DECRYPT(MediaThumb, :key) AS MediaThumb FROM ContentLibrary WHERE ID =  :id LIMIT 1");
                $stmt->execute([
                    'id' => $id,
                    'key' => AES_ENCRYPTION_KEY
                               ]);
                $item = $stmt->fetch();
            } catch (Exception $e) {
                error_log("CMS\ContentLibrary->getItemById() Failed to retrieve Library item details when searching by ID" . $e);
            }

            if (is_array($item) && count($item) > 0) {
                //Store details in relevant members
                $this->_id = $item['ID'];
                $this->_content_id = $item['ContentID'];
                $this->_content_parent_table = $item['ContentParentTable'];
                $this->_media_type = $item['MediaType'];
                $this->_media_path = $item['MediaPath'];
                $this->_media_filename = $item['MediaFilename'];
                $this->_media_extension = $item['MediaExtension'];
                $this->_media_mime_type = $item['MediaMimeType'];
                $this->_caption = $item['Caption'];
                $this->_display_order = $item['DisplayOrder'];
                /*if ($this->_display_order <= 0)
                {
                    $this->_display_order = 10000;
                }*/
                $this->_last_edited = $item['LastEdited'];
                $this->_media_thumb = $item['MediaThumb'];
                
                //Form relative path and filename
                if ($this->_media_thumb == 'Y') {
                    $item['FullPath'] = $this->_media_path."large/".$this->_media_filename.".".$this->_media_extension;
                } else {
                    $item['FullPath'] = $this->_media_path.$this->_media_filename.".".$this->_media_extension;
                }
                
                //Return as an array
                return $item;
            } else {
                return false;
            }
        }


        /**
         * Function to return array of records (as assoc array)
         *
         * @param string $passedNeedle
         * @param string $passedMode
         *
         * @return array
         */
        public function listAllItems($passedNeedle = null, $passedMode = 'content-id', $passedParent = null)
        {
            $basesql = "SELECT ID FROM ContentLibrary WHERE ";
            $order = "ORDER BY ContentID ASC, DisplayOrder ASC";
            $params = array();

            //Build SQL depending on passedMode and passedNeedle
            switch ($passedMode) {
                case 'mediatype':
                    $query = "(CONVERT(AES_DECRYPT(MediaType, :key) USING utf8) = :needle) ";
                    $params['key'] = AES_ENCRYPTION_KEY;
                    $params['needle'] = $passedNeedle;
                    break;
    
                case 'content-id':
                    $query = "ContentID = :needle ";
                    $order = "ORDER BY DisplayOrder ASC, LastEdited DESC";
                    $params['needle'] = $passedNeedle;
                    break;
            
                case 'content-parent-table':
                    $query = "(CONVERT(AES_DECRYPT(ContentParentTable, :key) USING utf8) = :needle) ";
                    $params['key'] = AES_ENCRYPTION_KEY;
                    $params['needle'] = $passedNeedle;
                    break;

                default:
                    $query = "ContentID = :needle ";
                    $order = "ORDER BY AES_DECRYPT(MediaType, :key) ASC, DisplayOrder ASC";
                    $params['key'] = AES_ENCRYPTION_KEY;
                    $params['needle'] = $passedNeedle;
                    break;
            }
            
            if (isset($passedParent) && is_string($passedParent)) {
                $query .= " AND (CONVERT(AES_DECRYPT(ContentParentTable, :key) USING utf8) = :parent_table)";
                $params['key'] = AES_ENCRYPTION_KEY;
                $params['parent_table'] = $passedParent;
            }

            //echo $basesql.$query.$order;
            //echo $passedNeedle;
            
            //Carry out the query
            $stmt = $this->_dbconn->prepare($basesql . $query . $order);
            $stmt->execute($params);

            //Prepare results array
            $results = array();

            //Work through results from query
            while ($this_res = $stmt->fetch()) {
                //Now retrieve the full property record
                $mem = $this->getItemByID($this_res['ID']);
                $results[] = $mem;
            }

            return $results;
        }




        /**
         * Receive an image data stream
         *  - process the image based on path and size settings - using large and small as subdirs for file storage (these get created the first time the object is called)
         *  - store it in the file system
         *  - store it in the new object ahead of saving
         *
         * @param     $ImageStream
         *
         * @throws Exception
         */
        public function processImage($ImageStream, $filename = null)
        {
            //Check we have some useful data passed
            if ($ImageStream == '') {
                throw new Exception("CMS\ContentLibrary->processImage() You must supply a file stream (data:image/jpeg;base64) to this function.");
            }

            $ImageStream = str_replace('data:image/jpeg;base64,', '', $ImageStream);
            //$img = str_replace('data:image/jpeg;base64,', '', $img);
            $ImageStream = str_replace(' ', '+', $ImageStream);
            $data = base64_decode($ImageStream);
            $unid = uniqid();
            $file_jpg = $unid;  //At this stage its actually a PNG file from the Darkroom Canvas object
            file_put_contents($file_jpg, $data);

            //Sort out naming
            $now = date('YmdHis');
            if ($filename == '') {
                $NewFilename = new FilenameSanitiser($this->_content_id."_".$unid . "_" . $this->_caption);
                $NewFilename->sanitiseFilename();
                $filename = $NewFilename->getFilename();
                //$filename = $now . "_" . $filename . ".jpg";
                //$filename = $filename . ".jpg";
            } else {
                $NewFilename = new FilenameSanitiser($this->_content_id."_".$filename);
                $NewFilename->sanitiseFilename();
                $filename = $NewFilename->getFilename();
                //$filename = $now . "_" . $filename . ".jpg";
                //$filename = $filename . ".jpg";
            }

            $NewPath = DOCUMENT_ROOT.$this->_media_path;

            if ($this->_media_thumb == 'Y') {
                $large = $NewPath."large/".$filename.".".$this->_media_extension;
                $small = $NewPath."small/".$filename.".".$this->_media_extension;
                $img = new ImageResizer($file_jpg, 'jpg');
                $img->resizeImage($large,'jpg', $this->_image_max, null, 100, false, true);
                $img->resizeImage($small,'jpg', $this->_thumb_max, null, 100, false, true);
            } else {
                $img = new ImageResizer($file_jpg, 'jpg');
                $img->resizeImage($NewPath.$filename,'jpg', $this->_image_max, null, 100, false, true);
            }

            //Set the media type to jpeg for the avoidance of doubt
            $this->_media_extension = "jpg";
            $this->_media_mime_type = "image/jpg";

            //Delete the old image
            $this->deleteMedia();

            //Store the new filename
            $this->_media_filename = $filename;

            //Save the object in its current state
            $this->saveItem();

            //Now tidy up the tmp file
            unlink($file_jpg);
        }


        /**
         * Process non-image files
         *
         * @param $FileStream
         * @param $MimeType
         * @param $Filename
         * @throws Exception
         */
        public function processFile($FileStream, $MimeType, $Filename)
        {
            //Check we have some useful data passed
            if ($FileStream == '') {
                throw new Exception("CMS\ContentLibrary->processFile(): You must supply a file stream to this function.");
            }

            //Sort out naming
            $NewFilename = new FilenameSanitiser($this->_content_id . "_" . $Filename);
            $NewFilename->sanitiseFilename();
            $filename = $NewFilename->getFilename();
            $ext = $this->deriveExtension($MimeType);
            $fullfilename = $this->_media_path.$filename . ".".$ext;


            //Save file to filesystem
            $data = base64_decode($FileStream);
            file_put_contents(DOCUMENT_ROOT.$fullfilename, $data);

            //Store info on object
            $this->_media_filename = $filename;
            $this->_media_extension = $ext;
            $this->_media_mime_type = $MimeType;
            $this->_media_thumb = 'N';

            //Save
            $this->saveItem();

            //Create thumbnail
            $this->createPDFThumbnail($this->_media_path,$this->_media_filename);


            //Save stream to the DB
            /*$sql = "UPDATE `ContentLibrary` SET Filetype = :filetype, Filesize = :filesize, Filename = :filename, Fileblob = :fileblob WHERE ID = :id";
            $stmt = $this->_dbconn->prepare($sql);

            $stmt->bindParam(':filetype', $Filetype);
            $stmt->bindParam(':filesize', $Filesize);
            $stmt->bindParam(':filename', $filename);
            $stmt->bindParam(':fileblob', $FileStream, PDO::PARAM_LOB);
            $stmt->bindParam(':id', $this->_id);

            $result = $stmt->execute();

            //Set date and other information on the object
            if ($result == true)
            {
                //Check whether the doc has a title
                if ($this->_title == '')
                {
                    $this->setTitle($filename);
                }

                $this->setDateUploaded(date('Y-m-d H:i:s', time()));
                $this->setFileName($filename);
                $this->setFileType($Filetype);
                $this->setFileSize($Filesize);

                //Save the object in its current state
                $this->saveFileItem();
            }
            */
        }


        /**
         * Create new empty image item
         *
         * Sets the _id property accordingly
         */
        public function createItem()
        {
            //Only execute if the _id property isn't already set
            if (is_numeric($this->_id) || $this->_id > 0) {
                throw new Exception('CMS\ContentLibrary->createItem() You cannot create a new Media item at this stage - the id is already set as '.$this->_id);
            }

            //Create DB item
            try {
                $result = $this->_dbconn->prepare("INSERT INTO ContentLibrary SET LastEdited = NOW()");
                $result->execute();
                $lastID = $this->_dbconn->lastInsertId();

                if ($lastID > 0) {
                    $this->_id = $lastID;
                } else {
                    throw new Exception('CMS\ContentLibrary->createItem() Unable to create new Media item');
                }
            } catch (Exception $e) {
                error_log("CMS\ContentLibrary->createItem() Failed to create new Media record: " . $e);
            }
        }


        /**
         * Saves the current object to the ContentLibrary table in the database
         *
         * @throws Exception
         */
        public function saveItem()
        {
            //First need to determine if this is a new Image item
            if ($this->_id <= 0) {
                $this->createItem(); //_id should now be set
            }

            //Set display order default
            if (!is_numeric($this->_display_order) || $this->_display_order < 0 || $this->_display_order > 100 ) { $this->_display_order = 100; }

            try {
                $stmt = $this->_dbconn->prepare("UPDATE ContentLibrary SET ContentID = :contentid, ContentParentTable = AES_ENCRYPT(:content_parent_table, :key), MediaType = AES_ENCRYPT(:mediatype, :key), MediaPath = AES_ENCRYPT(:mediapath, :key), MediaFilename = AES_ENCRYPT(:mediafilename, :key), Caption = AES_ENCRYPT(:caption, :key), DisplayOrder = :displayorder, LastEdited = NOW(), MediaExtension = AES_ENCRYPT(:mediaextension, :key), MediaMimeType = AES_ENCRYPT(:mediamimetype, :key), MediaThumb = AES_ENCRYPT(:mediathumb, :key) WHERE ID = :id LIMIT 1");
                $result = $stmt->execute([
                                   'key' => AES_ENCRYPTION_KEY,
                                   'contentid' => $this->_content_id,
                                   'content_parent_table' => $this->_content_parent_table,
                                   'mediatype' => $this->_media_type,
                                   'mediapath' => $this->_media_path,
                                   'mediafilename' => $this->_media_filename,
                                   'caption' => $this->_caption,
                                   'displayorder' => $this->_display_order,
                                   'mediaextension' => $this->_media_extension,
                                   'mediamimetype' => $this->_media_mime_type,
                                   'mediathumb' => $this->_media_thumb,
                                   'id' => $this->_id
                               ]);
                if ($result == true) {
                    return true;
                } else {
                    //print_r($stmt->errorInfo()[2]);
                    error_log($stmt->errorInfo()[2]);
                    return false;
                }
            } catch (Exception $e) {
                error_log("CMS\ContentLibrary->saveItem() Failed to save Media record: " . $e);
            }
        }


        /**
         * Delete the image file for this Image - assuming _img_filename is set
         *
         * @return mixed
         */
        public function deleteMedia()
        {
            if (!is_string($this->_media_filename)) {
                return "CMS\ContentLibrary->deleteMedia() Sorry - there was no media to delete";
            }

            //Does the filename exist?
            $delete_complete = false;
            $delete_large = false;
            $delete_small = false;
            
            if ($this->_media_thumb == 'Y') {
                if (file_exists(DOCUMENT_ROOT . $this->_media_path . "large/" . $this->_media_filename.".".$this->_media_extension)) {
                    //remove the file
                    $delete_large = unlink(DOCUMENT_ROOT . $this->_media_path . "large/" . $this->_media_filename.".".$this->_media_extension);
                }
                if (file_exists(DOCUMENT_ROOT . $this->_media_path . "small/" . $this->_media_filename.".".$this->_media_extension)) {
                    //remove the file
                    $delete_small = unlink(DOCUMENT_ROOT . $this->_media_path . "small/" . $this->_media_filename.".".$this->_media_extension);
                }
            } else {
                if (file_exists(DOCUMENT_ROOT . $this->_media_path . $this->_media_filename.".".$this->_media_extension)) {
                    //remove the file
                    $delete_large = unlink(DOCUMENT_ROOT . $this->_media_path . $this->_media_filename.".".$this->_media_extension);
                }
            }

            //Type = PDF? If so, the thumbnail will be next to it- delete that also
            if ($this->_media_extension == 'pdf') {
                unlink(DOCUMENT_ROOT . $this->_media_path . $this->_media_filename.".jpg");
            }

            if ($delete_large || $delete_small) {
                $delete_complete = true;
                $this->_media_filename = null;
                $this->_media_extension = null;
                $this->_media_mime_type = null;
                $this->saveItem();
                return true;
            }
        }


        /**
         * Delete the complete Media item - including any media
         *
         * @return mixed
         */
        public function deleteItem()
        {
            if (!is_numeric($this->_id)) {
                throw new Exception('CMS\ContentLibrary->deleteItem() requires the Image item ID to be set if you are trying to delete the item');
            }

            //First delete the image files
            $this->deleteMedia();

            //Now delete the item from the DB
            try {
                $stmt = $this->_dbconn->prepare("DELETE FROM ContentLibrary WHERE ID = :id LIMIT 1");
                $result = $stmt->execute([
                                             'id' => $this->_id
                                         ]);
            } catch (Exception $e) {
                error_log("CMS\ContentLibrary->deleteItem() Failed to delete Media record: " . $e);
            }

            if ($result === true) {
                //Unset the properties
                $this->_id = null;
                $this->_content_id = null;
                $this->_content_parent_table = null;
                $this->_caption = null;
                $this->_display_order = null;
                $this->_media_type = null;
                $this->_media_filename = null;
                $this->_media_path = null;
                $this->_media_filename = null;
                $this->_media_extension = null;
                $this->_media_mime_type = null;
                $this->_media_thumb = null;

                return true;
            } else {
                return false;
            }

        }


        /**
         * Provide icon information for this object
         *
         * @return mixed    array('Path'=>path, 'File'=>filename, 'Type'=>text used for alt text etc);
         *
         */
        public function getIcon()
        {
            if ($this->_id > 0 && $this->_media_mime_type != '')
            {
                //First derive the extension
                $ext = $this->deriveExtension($this->_media_mime_type);

                //Hard code path for now
                $path = "/assets/img/icons/";

                switch ($ext) {
                    case 'pdf':
                        $file = "icon_acrobat.png";
                        $type = "Acrobat document";
                        break;
                    case 'doc':
                        $file = "icon_word.png";
                        $type = "MS Word document";
                        break;
                    case 'docx':
                        $file = "icon_word.png";
                        $type = "MS Word document";
                        break;
                    case 'xls':
                        $file = "icon_excel.png";
                        $type = "MS Excel document";
                        break;
                    case 'xlsx':
                        $file = "icon_excel.png";
                        $type = "MS Excel document";
                        break;
                    case 'jpg':
                        $file = "icon_image.png";
                        $type = "JPEG image";
                        break;
                    case 'image/png':
                        $file = "icon_image.png";
                        $type = "PNG image";
                        break;
                    default:
                        $file = "icon_unknown.png";
                        $type = "Unknown document";
                        break;
                }
                return array('Path'=>$path,'File'=>$file,'Type'=>$type);
            } else {
                return false;
            }
        }

        /**
         * Derive Extension based on passed in mime type
         *
         * @param   $mimeType
         *
         * @return string
         * @throws Exception
         */
        public function deriveExtension($mimeType)
        {
            switch ($mimeType) {
                case 'application/pdf':
                    $ext = "pdf";
                    break;
                case 'application/msword':
                    $ext = "doc";
                    break;
                case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
                    $ext = "docx";
                    break;
                case 'application/vnd.ms-excel':
                    $ext = "xls";
                    break;
                case 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet':
                    $ext = "xlsx";
                    break;
                case 'image/jpeg':
                    $ext = "jpg";
                    break;
                case 'image/jpg':
                    $ext = "jpg";
                    break;
                case 'image/png':
                    $ext = "png";
                    break;
                default:
                    error_log('CMS\ContentLibrary->deriveExtension() : incorrect mime type passed in: '.$mimeType);
                    $ext = "";
                    break;
            }

            return $ext;
        }


        /**
         * Create thumbnail for PDF - store as JPG next to te PDF
         *
         * @param $path
         * @param $filename
         * @return false
         */
        public function createPDFThumbnail($path, $filename)
        {
            if ($path == '') { $path = $this->_media_path; }
            if ($filename == '') { $filename = $this->_media_filename; }
            $complete_path = FixOutput(DOCUMENT_ROOT.$path.$filename.".".$this->_media_extension);

            $complete_thumb_path = FixOutput(DOCUMENT_ROOT.$path);


            //Does file exist?
            if ($filename == '' || !file_exists($complete_path)) {
                error_log('CMS\ContentLibrary->createPDFThumbnail() requires path and filename to be passed or set AND the file to exist');
                return false;
            }

            try {
                $pdfThumb = new imagick();
                $pdfThumb->setResolution(100, 100);
                $pdfThumb->readImage($complete_path);
                $pdfThumb->setImageFormat('jpg');
                
                $fp = $complete_thumb_path . $this->_media_filename.".jpg";
                $pdfThumb->writeImage($fp);
            } catch (Exception $e) {
                error_log('CMS\ContentLibrary->createPDFThumbnail() - could not read source PDF file to create thumbnail correctly. Not creating thumbnail.');
            }
        }



        /**
         * Delete all media for passed property id
         *
         * @param null $propertyid
         * @return false
         * @throws Exception
         */
        public function deleteAllMediaForContent($contentid = null)
        {
            if (!is_numeric($contentid) || $contentid <= 0) {
                $contentid = $this->_content_id;
            }
            if (!is_numeric($contentid) || $contentid <= 0) {
                error_log("CMS\ContentLibrary->deleteAllMediaForProperty() requires Content ID to be passed or set");
                return false;
            }

            $arr_all_items = $this->listAllItems($contentid,'contentid');
            if (is_array($arr_all_items) && count($arr_all_items) > 0) {
                foreach($arr_all_items as $item)
                {
                    $IO = new ContentLibrary($item['ID']);
                    $IO->deleteItem();
                }
            }
        }





        public function getID()
        {
            return $this->_id;
        }

        public function setID($id)
        {
            $this->_id = $id;
        }

        /**
         * @return int|string
         */
        public function getImageMax()
        {
            return $this->_image_max;
        }

        /**
         * @param int|string $image_max
         */
        public function setImageMax($image_max): void
        {
            $this->_image_max = $image_max;
        }

        /**
         * @return int|string
         */
        public function getThumbMax()
        {
            return $this->_thumb_max;
        }

        /**
         * @param int|string $thumb_max
         */
        public function setThumbMax($thumb_max): void
        {
            $this->_thumb_max = $thumb_max;
        }

        /**
         * @return string
         */
        public function getMediaPath(): string
        {
            return $this->_media_path;
        }

        /**
         * @param string $media_path
         */
        public function setMediaPath(string $media_path): void
        {
            $this->_media_path = $media_path;
        }

        /**
         * @return mixed
         */
        public function getContentId()
        {
            return $this->_content_id;
        }

        /**
         * @param mixed $content_id
         */
        public function setContentId($content_id): void
        {
            $this->_content_id = $content_id;
        }
    
        /**
         * @return mixed
         */
        public function getContentParentTable()
        {
            return $this->_content_parent_table;
        }
    
        /**
         * @param mixed $content_parent_table
         */
        public function setContentParentTable($content_parent_table): void
        {
            $this->_content_parent_table = $content_parent_table;
        }
    
    
    
        /**
         * @return mixed
         */
        public function getMediaType()
        {
            return $this->_media_type;
        }

        /**
         * @param mixed $media_type_title
         */
        public function setMediaType($media_type_title): void
        {
            $this->_media_type = $media_type_title;
        }

        /**
         * @return mixed
         */
        public function getMediaFilename()
        {
            return $this->_media_filename;
        }

        /**
         * @param mixed $media_filename
         */
        public function setMediaFilename($media_filename): void
        {
            $this->_media_filename = $media_filename;
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
        public function setDisplayOrder($display_order): void
        {
            $this->_display_order = $display_order;
        }

        /**
         * @return mixed
         */
        public function getCaption()
        {
            return $this->_caption;
        }

        /**
         * @param mixed $caption
         */
        public function setCaption($caption): void
        {
            $this->_caption = $caption;
        }

        /**
         * @return mixed
         */
        public function getLastEdited()
        {
            return $this->_last_edited;
        }


        /**
         * @return mixed
         */
        public function getMediaMimeType()
        {
            return $this->_media_mime_type;
        }

        /**
         * @param mixed $media_mime_type
         */
        public function setMediaMimeType($media_mime_type): void
        {
            $this->_media_mime_type = $media_mime_type;
        }

        /**
         * @return mixed
         */
        public function getMediaExtension()
        {
            return $this->_media_extension;
        }

        /**
         * @param mixed $media_extension
         */
        public function setMediaExtension($media_extension): void
        {
            $this->_media_extension = $media_extension;
        }

        /**
         * @return mixed
         */
        public function getMediaThumb()
        {
            return $this->_media_thumb;
        }

        /**
         * @param mixed $media_thumb
         */
        public function setMediaThumb($media_thumb): void
        {
            $this->_media_thumb = $media_thumb;
        }

    }