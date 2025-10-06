<?php

    namespace PeterBourneComms\CMS;
    use PDO;
    use PDOException;
    use Exception;


    /**
     * Class that deals with FileLibraries for Enquiries etc. But could be extended to any parent content
     *
     * The Files table will be independent of parent - but will contain a field that indicates what type of content.
     *
     * The structure of the table shall be:
     *  - ID
     *  - ContentID
     *  - ContentParentTable (Test | Content | etc)
     *  - Filename
     *  - Filepath
     *  - Filesize
     *  - Filetype
     *  - DisplayOrder
     *  - Caption
     *  - Title
     *  - DateUploaded
     *
     * Class needs to be able to:
     *  - Check upload is of accepted type (PDF, XLS, DOC, XLSX, DOCX)
     *  - Check that is an accepted max-size
     *  - stored in the database
     *  - retrieve an individual file
     *  - delete file item (from db)
     *  - Return array of all files for this content Type (Test | Content | etc)
     *  - also needs to be able to just update various elements - Title, Caption and DisplayOrder
     *
     *  - DELETE ALL Files (file and db record) for a particular contentID
     *
     *
     * @author Peter Bourne
     * @version 1.1		13.09.2021		TABLE_NAME modification in checkParent for mySQL 8 issue
     *
     */
    class FileLibrary
    {
        /**
         * @var mixed
         */
        protected $_dbconn;
        protected $_id;
        protected $_content_id;
        protected $_content_parent_table;
        protected $_display_order;
        protected $_caption;
        protected $_title;
        protected $_date_uploaded;
        protected $_file_name;
        protected $_file_type;
        protected $_file_size;
        protected $_file_blob;
        protected $_filemaxsize;
        protected $_extension;
        protected $_mime_type;
        protected $_author_id;
        protected $_author_name;


        public function __construct($contenttype, $contentid, $id = null, $filemaxsize = 6000)
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

                //Assess Contenttype and ID - they NEED to be provided
                if (!is_numeric($contentid) || $contentid <= 0 || $contentid == '')
                {
                    throw new Exception('Class FileLibrary requires you to supply the ID of the Content item');
                }
                if ($contenttype == '' || !is_string($contenttype))
                {
                    throw new Exception(('Class FileLibrary requires you to specify the table that the ContentID and this file relates to, eg: Test, Content etc.'));
                }

                //Assess passed id
                if ($id != null && !is_numeric($id))
                {
                    throw new Exception('Class FileLibrary requires id to be specified as an integer');
                }

                //Assess passed maximum sizes
                if ($filemaxsize != null && !is_numeric($filemaxsize))
                {
                    throw new Exception('Class FileLibrary requires file maximum sizes to be specified as integers in bytes, eg: 10000 for 10Mb');
                }

                //Store the properties
                $this->_id = $id;
                $this->_filemaxsize = $filemaxsize;
                $this->_content_parent_table = $contenttype;
                $this->_content_id = $contentid;

                //Check the table (and record) exists for the ParentContent
                if (!$this->checkParent())
                {
                    throw new Exception('Class FileLibrary has found that the table specified, or the parent content ID specified - does not exist in the database. Table: ' . $contenttype . ' and ID: ' . $contentid);
                }

                //Retrieve current Image information
                if (isset($id))
                {
                    $this->getFileById($id);
                }
            }

        }

        /**
         * Function that checks the Object's $_property_id and $_media_type_id to see if they exist in the DB
         */
        private function checkParent($contenttable = null, $parentid = null)
        {
            //Populate with object properties if nothing supplied - then check there is soemthing present!
            if ($contenttable == '')
            {
                $contenttable = $this->_content_parent_table;
            }
            if ($parentid <= 0)
            {
                $parentid = $this->_content_id;
            }

            //Now check for valid values
            if ($contenttable == '')
            {
                throw new Exception('Sorry - FileLibrary requires the checkParent function to be passed a table reference - or for it to be set in the object already');
            }
            if ($parentid <= 0)
            {
                throw new Exception('Sorry - FileLibrary requires the checkParent function to be passed a content parent ID - or for it to be present in the object already');
            }

            //Get complete list of tables
            $tables = $this->_dbconn->query("SELECT TABLE_NAME FROM information_schema.tables WHERE table_schema = '" . DB_NAME . "'");
            $tables_rs = $tables->fetchAll(PDO::FETCH_ASSOC);
            $tables_rsi = array();
            foreach ($tables_rs as $row)
            {
                $tables_rsi[] = $row['TABLE_NAME'];
            }

            if (in_array($contenttable, $tables_rsi))
            {
                //$tablename = $tablesRS[$this->_media_type_id]
                //Now check for the actual record
                $stmt = $this->_dbconn->prepare("SELECT ID FROM " . $contenttable . " WHERE ID = :id LIMIT 1");
                $result = $stmt->execute([
                    'id' => $parentid
                                         ]);
                $row = $stmt->fetch();
                if ($row['ID'] == $parentid)
                {
                    return true;
                }
            }
            return false;
        }

        public function getFileById($id)
        {
            try
            {
                $stmt = $this->_dbconn->prepare("SELECT * FROM Files WHERE ID =  :id AND ContentID = :contentid AND ContentParentTable = :contenttable LIMIT 1");
                $stmt->execute([
                                   'id' => $id,
                                   'contentid' => $this->_content_id,
                                   'contenttable' => $this->_content_parent_table
                               ]);
                $file = $stmt->fetch();
            } catch (Exception $e)
            {
                error_log("Class FileLibrary: Failed to retrieve item details when searching by ID" . $e);
            }

            //Store details in relevant members
            $this->_id = $file['ID'];
            $this->_content_id = $file['ContentID'];
            $this->_content_parent_table = $file['ContentParentTable'];
            $this->_title = $file['Title'];
            $this->_caption = $file['Caption'];
            $this->_date_uploaded = $file['DateUploaded'];
            $this->_file_name = $file['Filename'];
            $this->_file_type = $file['Filetype'];
            $this->_file_size = $file['Filesize'];
            $this->_file_blob = $file['Fileblob'];
            $this->_display_order = $file['DisplayOrder'];
            $this->_author_id = $file['AuthorID'];
            $this->_author_name = $file['AuthorName'];

            if ($this->_display_order <= 0)
            {
                $this->_display_order = 1000;
            }

            //Derive icon info
            $Icon = $this->getIcon();
            $file['Icon'] = $Icon;

            return $file;

        }

        /**
         * Provide icon information for this object
         *
         * @return mixed    array('Path'=>path, 'File'=>filename, 'Type'=>text used for alt text etc);
         *
         */
        public function getIcon()
        {
            if ($this->_id > 0 && $this->_file_type != '')
            {
                //First derive the extension
                $ext = $this->deriveExtension($this->_file_type);

                //Hard code path for now
                $path = "/assets/img/icons/";

                switch ($ext)
                {
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
            }
            else
            {
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
            switch ($mimeType)
            {
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
                    //throw new Exception('FileLibrary->deriveExtension : incorrect mime type passed in');
                    error_log('FileLibrary->deriveExtension : incorrect mime type passed in: '.$mimeType);
                    $ext = "";
                    break;
            }

            return $ext;
        }

        /**
         * Receive a data stream
         *
         *  - check the filetype is acceptable
         *  - check the filesize is acceptable
         *  - store it in the new object ahead of saving to DB
         *
         * @param     $FileStream
         *
         * @throws Exception
         */
        public function processFile($FileStream, $MimeType, $Filetype, $Filesize, $Filename)
        {
            //Check we have some useful data passed
            if ($FileStream == '')
            {
                throw new Exception("FileLibrary: You must supply a file stream to this function.");
            }

            //Sort out naming
            $NewFilename = new FilenameSanitiser($this->_content_id . "_" . $Filename);
            $NewFilename->sanitiseFilename();
            $filename = $NewFilename->getFilename();
            //$ext = $this->deriveExtension($MimeType);
            //$filename = $filename . $ext;

            //Save stream to the DB
            $sql = "UPDATE `Files` SET Filetype = :filetype, Filesize = :filesize, Filename = :filename, Fileblob = :fileblob WHERE ID = :id";
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
        }

        /**
         * Saves the current object to the table in the database
         *
         * @throws Exception
         */
        public function saveFileItem()
        {
            //First need to determine if this is a new item
            if ($this->_id <= 0)
            {
                $this->createFileItem(); //_id should now be set
            }

            try
            {
                $stmt = $this->_dbconn->prepare("UPDATE `Files` SET ContentID = :contentid, ContentParentTable = :contentparenttable, Caption = :caption, DisplayOrder = :displayorder, Filename = :filename, Filetype = :filetype, Filesize = :filesize, DateUploaded = :dateuploaded, Title = :title, AuthorID = :authorid, AuthorName = :authorname WHERE ID = :id LIMIT 1");
                $stmt->execute([
                                   'contentid' => $this->_content_id,
                                   'contentparenttable' => $this->_content_parent_table,
                                   'caption' => $this->_caption,
                                   'displayorder' => $this->_display_order,
                                   'filename' => $this->_file_name,
                                   'filetype' => $this->_file_type,
                                   'filesize' => $this->_file_size,
                                   'dateuploaded' => $this->_date_uploaded,
                                   'title' => $this->_title,
                                   'authorname' => $this->_author_name,
                                   'authorid' => $this->_author_id,
                                   'id' => $this->_id
                               ]);
                if ($stmt->rowCount() == 1)
                {
                    return true;
                }
                else
                {
                    //print_r($stmt->errorInfo());
                    return false;
                }
            } catch (Exception $e)
            {
                error_log("Failed to save File record: " . $e);
            }
        }

        /**
         * Create new empty file item
         *
         * Sets the _id property accordingly
         */
        public function createFileItem()
        {
            //Only execute if the _id property isn't already set
            if (is_numeric($this->_id) || $this->_id > 0)
            {
                throw new Exception('You cannot create a new File item at this stage - the id property is already set as ' . $this->_id);
            }

            //Create DB item
            try
            {
                $result = $this->_dbconn->prepare("INSERT INTO Files SET ContentID = :contentid, ContentParentTable = :contentparenttable");
                $result->execute([
                                     'contentid' => $this->_content_id,
                                     'contentparenttable' => $this->_content_parent_table
                                 ]);
                $lastID = $this->_dbconn->lastInsertId();

                if ($lastID > 0)
                {
                    $this->_id = $lastID;
                }
                else
                {
                    throw new Exception('Unable to create new File item');
                }
            } catch (Exception $e)
            {
                error_log("Failed to create new File record: " . $e);
            }
        }

        public function deleteAllFilesForContent($contenttable = null, $contentid = null)
        {
            $arr_all_images = $this->retrieveAllFiles($contenttable, $contentid);
            if (is_array($arr_all_images) && count($arr_all_images) > 0)
            {
                foreach ($arr_all_images as $image)
                {
                    $IO = new FileLibrary($image['ContentParentTable'], $image['ContentID'], $image['ID']);
                    $IO->deleteItem();
                }
            }
        }

        /**
         * Returns an array of all Files in the table specified in the function call - with the same ContentParentID
         * Used by pages to output the file library typically
         *
         * @param $contentid
         * @param $contenttable
         *
         * @return mixed
         */
        public function retrieveAllFiles($contenttable = null, $contentid = null)
        {
            if ($contenttable == '')
            {
                $contenttable = $this->_content_parent_table;
            }
            if ($contentid <= 0)
            {
                $contentid = $this->_content_id;
            }

            //Table and parent check first
            if ($this->checkParent($contenttable, $contentid))
            {
                //echo "Found table and id";
                //Proceed - the table and parent content do exist!
                $stmt = $this->_dbconn->prepare("SELECT ID FROM `Files` WHERE ContentID = :contentid AND ContentParentTable = :contenttable ORDER BY DisplayOrder ASC, DateUploaded DESC");
                $stmt->execute([
                                     'contentid' => $contentid,
                                     'contenttable' => $contenttable
                                 ]);
                $allfiles = array();
                while ($file = $stmt->fetch(PDO::FETCH_ASSOC))
                {
                    $thisfile = $this->getFileById($file['ID']);
                    $allfiles[] = $thisfile;
                    unset ($thisfile);
                }

                return $allfiles;
            }

            return false;
        }

        /**
         * Delete the complete File item
         *
         * @return mixed
         */
        public function deleteItem()
        {
            if (!is_numeric($this->_id))
            {
                throw new Exception('Class FileLibrary requires the File item ID to be set if you are trying to delete the item');
            }

            //Delete the item from the DB
            try
            {
                $stmt = $this->_dbconn->prepare("DELETE FROM `Files` WHERE ID = :id LIMIT 1");
                $result = $stmt->execute([
                                             'id' => $this->_id
                                         ]);
            } catch (Exception $e)
            {
                error_log("Failed to delete File record: " . $e);
            }

            if ($result == true)
            {
                //Unset the properties
                $this->_id = null;
                $this->_caption = null;
                $this->_display_order = null;
                $this->_caption = null;
                $this->_file_size = null;
                $this->_file_type = null;
                $this->_file_name = null;
                $this->_file_blob = null;
                $this->_content_parent_table = null;
                $this->_content_id = null;
                $this->_author_name = null;
                $this->_author_id = null;

                return true;
            }
            else
            {
                return false;
            }

        }

        /**
         * @return int|string
         */
        public function getId()
        {
            return $this->_id;
        }

        /**
         * @param int|string $id
         */
        public function setId($id)
        {
            $this->_id = $id;
        }

        /**
         * @return int|string
         */
        public function getContentId()
        {
            return $this->_content_id;
        }

        /**
         * @param int|string $content_id
         */
        public function setContentId($content_id)
        {
            $this->_content_id = $content_id;
        }

        /**
         * @return string
         */
        public function getContentParentTable()
        {
            return $this->_content_parent_table;
        }

        /**
         * @param string $content_parent_table
         */
        public function setContentParentTable($content_parent_table)
        {
            $this->_content_parent_table = $content_parent_table;
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
        public function setCaption($caption)
        {
            $this->_caption = $caption;
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
        public function getDateUploaded()
        {
            return $this->_date_uploaded;
        }

        /**
         * @param mixed $date_uploaded
         */
        public function setDateUploaded($date_uploaded)
        {
            $this->_date_uploaded = $date_uploaded;
        }

        /**
         * @return mixed
         */
        public function getFileName()
        {
            return $this->_file_name;
        }

        /**
         * @param mixed $file_name
         */
        public function setFileName($file_name)
        {
            $this->_file_name = $file_name;
        }

        /**
         * @return mixed
         */
        public function getFileType()
        {
            return $this->_file_type;
        }

        /**
         * @param mixed $file_type
         */
        public function setFileType($file_type)
        {
            $this->_file_type = $file_type;
        }

        /**
         * @return mixed
         */
        public function getFileSize()
        {
            return $this->_file_size;
        }

        /**
         * @param mixed $file_size
         */
        public function setFileSize($file_size)
        {
            $this->_file_size = $file_size;
        }

        /**
         * @return mixed
         */
        public function getFileBlob()
        {
            return $this->_file_blob;
        }

        /**
         * @param mixed $file_blob
         */
        public function setFileBlob($file_blob)
        {
            $this->_file_blob = $file_blob;
        }

        /**
         * @return int|string
         */
        public function getFilemaxsize()
        {
            return $this->_filemaxsize;
        }

        /**
         * @param int|string $filemaxsize
         */
        public function setFilemaxsize($filemaxsize)
        {
            $this->_filemaxsize = $filemaxsize;
        }

        /**
         * @return mixed
         */
        public function getExtension()
        {
            return $this->_extension;
        }

        /**
         * @param mixed $extension
         */
        public function setExtension($extension)
        {
            $this->_extension = $extension;
        }

        /**
         * @return mixed
         */
        public function getMimeType()
        {
            return $this->_mime_type;
        }

        /**
         * @param mixed $mime_type
         */
        public function setMimeType($mime_type)
        {
            $this->_mime_type = $mime_type;
        }

        /**
         * @return mixed
         */
        public function getAuthorId()
        {
            return $this->_author_id;
        }

        /**
         * @param mixed $author_id
         */
        public function setAuthorId($author_id)
        {
            $this->_author_id = $author_id;
        }

        /**
         * @return mixed
         */
        public function getAuthorName()
        {
            return $this->_author_name;
        }

        /**
         * @param mixed $author_name
         */
        public function setAuthorName($author_name)
        {
            $this->_author_name = $author_name;
        }

        public function setDisplayOrder($display_order)
        {
            if ($display_order <= 0)
            {
                $display_order = 1000;
            }
            $this->_display_order = $display_order;
        }

        /**
         * @return mixed
         */
        public function getDisplayOrder()
        {
            return $this->_display_order;
        }



    }