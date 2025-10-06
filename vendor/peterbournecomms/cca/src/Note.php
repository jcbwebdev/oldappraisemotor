<?php

    namespace PeterBourneComms\CCA;
    
    use PDO;
    use PDOException;
    use Exception;
    use PeterBourneComms\CMS\Database;
    
    
    /**
     * Class to manage customer and user notes
     *
     *
     *
     * @author Peter Bourne
     * @version 1.0
     *
     */
    class Note
    {
        protected $_dbconn;
        protected $_id;
        protected $_parent_id;
        protected $_parent_table;
        protected $_content;
        protected $_note_by;
        protected $_note_by_id;
        protected $_date_edited;



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
                throw new Exception('CCA\Note->__construct() requires id to be specified as an integer - if it is specified at all');
            }

            //Retrieve current member information
            if (isset($id)) {
                $this->_id= $id;
                $this->getItemById($id);
            }
        }


        public function getItemById($id)
        {
            if ($id == 0) {
                $id = $this->_id;
            }
            if (!is_numeric($id) || $id <= 0) {
                error_log('CCA\Note->getItemById() Unable to retrieve Note details as no NoteID set');
                return false;
            }
            
            try {
                $stmt = $this->_dbconn->prepare("SELECT ID, ParentID, AES_DECRYPT(ParentTable, :key) AS ParentTable, AES_DECRYPT(Content, :key) AS Content, AES_DECRYPT(NoteBy, :key) AS NoteBy, NoteByID, DateEdited FROM Notes WHERE ID =  :id LIMIT 1");
                $stmt->execute([
                                   'key' => AES_ENCRYPTION_KEY,
                                   'id' => $id
                               ]);
                $item = $stmt->fetch();
            } catch (Exception $e) {
                error_log("CCA\Note->getItemByID() Failed to retrieve Note details" . $e);
            }

            //Store details in relevant members
            if (is_array($item) && count($item) > 0) {
                $this->_id = $item['ID'];
                $this->_parent_id = $item['ParentID'];
                $this->_parent_table = $item['ParentTable'];
                $this->_content = $item['Content'];
                $this->_note_by = $item['NoteBy'];
                $this->_note_by_id = $item['NoteByID'];
                $this->_date_edited = $item['DateEdited'];
            } else {
                $item = false;
            }

            //Return note info as an array also
            return $item;
        }


        public function createNewItem()
        {
            try {
                $stmt = $this->_dbconn->prepare("INSERT INTO Notes SET DateEdited = NOW()");
                $stmt->execute();
                $id = $this->_dbconn->lastInsertId();

                //Store in the object
                $this->_id = $id;

            } catch (Exception $e) {
                error_log("CCA\Note->createNewItem() Failed to create new Note stub" . $e);
            }
        }

        public function saveItem()
        {
            try {
                $stmt = $this->_dbconn->prepare("UPDATE Notes SET ParentID = :parent_id, ParentTable = AES_ENCRYPT(:parent_table, :key), Content = AES_ENCRYPT(:content, :key), NoteBy = AES_ENCRYPT(:noteby, :key), NoteByID = :note_by_id, DateEdited = NOW() WHERE ID = :id LIMIT 1");
                $result = $stmt->execute([
                                             'key' => AES_ENCRYPTION_KEY,
                                             'parent_id' => $this->_parent_id,
                                             'parent_table' => $this->_parent_table,
                                             'content' => $this->_content,
                                             'noteby' => $this->_note_by,
                                             'note_by_id' => $this->_note_by_id,
                                             'id' => $this->_id
                                         ]);
                if (!$result) {
                    error_log($stmt->errorInfo()[2]);
                }
            } catch (Exception $e) {
                error_log("CCA\Note->saveItem() Failed to save Note record: " . $e);
            }

            if ($result === true) { return true; } else { return false; }
        }


        public function listAllItems($needle, $mode, $sortmode = 'desc')
        {
            if ($needle <= 0 || !is_numeric($needle))
            {
                error_log('CCA\Note->listAllItems() requires record ID to be passed');
                return false;
            }
            
            $params = array();
            
            switch ($mode) {
                case 'customer-id':
                    $sql = "SELECT ID FROM Notes WHERE ParentID = :needle AND CONVERT(AES_DECRYPT(ParentTable, :key) USING utf8) = 'Customers' ";
                    $params['key'] = AES_ENCRYPTION_KEY;
                    $params['needle'] = $needle;
                    break;

                case 'user-id':
                    $sql = "SELECT ID FROM Notes WHERE ParentID = :needle AND CONVERT(AES_DECRYPT(ParentTable, :key) USING utf8) = 'Users' ";
                    $params['key'] = AES_ENCRYPTION_KEY;
                    $params['needle'] = $needle;
                    break;
                    
                default:
                    error_log('CCA\Note->listAllItems() Unknown mode');
                    return false;
                    break;
            }
            
            if ($sortmode != '') {
                switch($sortmode) {
                    case 'asc':
                        $sort = " ORDER BY DateEdited ASC";
                        break;
                        
                    case 'desc':
                        $sort = " ORDER BY DateEdited DESC";
                        break;
                    
                    default:
                        $sort = "";
                        break;
                }
            }
            
            //Retrieve notes
            $stmt = $this->_dbconn->prepare($sql.$sort);
            $stmt->execute($params);
            
            //Prepare results array
            $results = array();
            
            //Work through results from query
            while($this_res = $stmt->fetch())
            {
                //Now retrieve the full customer record
                $note = $this->getItemById($this_res['ID']);
                $results[] = $note;
            }
            
            return $results;
        }


        public function deleteItem($id = 0)
        {
            if ($id == 0 || !is_numeric($id)) {
                $id = $this->_id;
            }
            //Check that we have a noteid set up
            if (!is_numeric($id) || $id <= 0) {
                error_log('CCA\Note->deleteItem()n No Note ID set on the property - cannot delete this note');
                return false;
            }
            $stmt = $this->_dbconn->prepare("DELETE FROM Notes WHERE ID = :id LIMIT 1");
            $result = $stmt->execute([
                'id' => $id
                   ]);

            if ($result === true) {
                return true;
            } else {
                error_log("CCA\Note->deleteItem() Could not delete the note as requested");
                return false;
            }
        }
        
        public function deleteAllNotesFor($needle,$mode)
        {
            if ($needle <= 0 || !is_numeric($needle))
            {
                error_log('CCA\Note->deleteAllNotesFor() requires record ID to be passed');
                return false;
            }
            
            $params = array();
            
            switch ($mode) {
                case 'customer-id':
                    $sql = "DELETE FROM Notes WHERE ParentID = :needle AND CONVERT(AES_DECRYPT(ParentTable, :key) USING utf8) = 'Customers' ";
                    $params['key'] = AES_ENCRYPTION_KEY;
                    $params['needle'] = $needle;
                    break;
                
                case 'user-id':
                    $sql = "DELETE FROM Notes WHERE ParentID = :needle AND CONVERT(AES_DECRYPT(ParentTable, :key) USING utf8) = 'Users' ";
                    $params['key'] = AES_ENCRYPTION_KEY;
                    $params['needle'] = $needle;
                    break;
                
                default:
                    error_log('CCA\Note->deleteAllNotesFor() Unknown mode');
                    return false;
                    break;
            }
            
            //Delete notes
            $result = $stmt = $this->_dbconn->prepare($sql);
            $stmt->execute($params);
            
            return $result;
        }
        
        
        
        
        
        ###########################################################
        # Getters and Setters
        ###########################################################
        
        
        public function getId(): float|int|string
        {
            return $this->_id;
        }
        
        
        /**
         * @return mixed
         */
        public function getParentId()
        {
            return $this->_parent_id;
        }
        
        /**
         * @param mixed $parent_id
         */
        public function setParentId($parent_id): void
        {
            if (!is_numeric($parent_id) || $parent_id <= 0) { $parent_id = null; }
            $this->_parent_id = $parent_id;
        }
        
        /**
         * @return mixed
         */
        public function getParentTable()
        {
            return $this->_parent_table;
        }
        
        /**
         * @param mixed $parent_table
         */
        public function setParentTable($parent_table): void
        {
            $this->_parent_table = $parent_table;
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
        public function getNoteBy()
        {
            return $this->_note_by;
        }
        
        /**
         * @param mixed $note_by
         */
        public function setNoteBy($note_by): void
        {
            $this->_note_by = $note_by;
        }
        
        /**
         * @return mixed
         */
        public function getNoteById()
        {
            return $this->_note_by_id;
        }
        
        /**
         * @param mixed $note_by_id
         */
        public function setNoteById($note_by_id): void
        {
            if (!is_numeric($note_by_id) || $note_by_id <= 0) { $note_by_id = null; }
            $this->_note_by_id = $note_by_id;
        }
        
        /**
         * @return mixed
         */
        public function getDateEdited()
        {
            return $this->_date_edited;
        }
        
        /**
         * @param mixed $date_edited
         */
        public function setDateEdited($date_edited): void
        {
            if ($date_edited == '' || $date_edited == '0000-00-00' || $date_edited == '0000-00-00 00:00:00') { $date_edited = null; }
            $this->_date_edited = $date_edited;
        }

    }