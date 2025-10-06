<?php

    namespace PeterBourneComms\CMS;
    use PDO;
    use PDOException;
    use Exception;


    /**
     * Class to manage member notes
     *
     * It will listAllNotes for a given member
     * It will also allow you to create, populate and retrieve a single note (and delete same note
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
        protected $_memberID;
        protected $_note;
        protected $_note_by;
        protected $_date_edited;



        public function __construct($noteid = null)
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
            if (isset($noteid) && !is_numeric($noteid))
            {
                throw new Exception('Class Note requires noteid to be specified as an integer - if it is specified at all');
            }

            //Retrieve current member information
            if (isset($noteid))
            {
                $this->_id= $noteid;
                $this->getNoteDetails($noteid);
            }
        }


        public function getNoteDetails($noteid)
        {
            if ($noteid == 0)
            {
                $noteid = $this->_noteID;
            }
            if (!is_numeric($noteid) || $noteid <= 0)
            {
                error_log('Note: Unable to retrieve Note details as no NoteID set');
                return false;
            }
            try
            {
                $stmt = $this->_dbconn->prepare("SELECT ID, MemberID, AES_DECRYPT(Note, :key) AS Note, AES_DECRYPT(NoteBy, :key) AS NoteBy, DateEdited FROM MemberNotes WHERE ID =  :id LIMIT 1");
                $stmt->execute([
                                   'key' => AES_ENCRYPTION_KEY,
                                   'id' => $noteid
                               ]);
                $note = $stmt->fetch();
            } catch (Exception $e)
            {
                error_log("Failed to retrieve Note details" . $e);
            }

            //Store details in relevant members
            $this->_id = $note['ID'];
            $this->_memberID = $note['MemberID'];
            $this->_note = $note['Note'];
            $this->_note_by = $note['NoteBy'];
            $this->_date_edited = $note['DateEdited'];

            //Return note info as an array also
            return $note;
        }


        public function createNewNote()
        {
            try
            {
                $stmt = $this->_dbconn->prepare("INSERT INTO MemberNotes SET DateEdited = NOW()");
                $stmt->execute();
                $id = $this->_dbconn->lastInsertId();

                //Store in the object
                $this->_id = $id;

            } catch (Exception $e)
            {
                error_log("Note: Failed to create new Note stub" . $e);
            }
        }

        public function saveNote()
        {
            try
            {
                $stmt = $this->_dbconn->prepare("UPDATE MemberNotes SET MemberID = :memberid, Note = AES_ENCRYPT(:note, :key), NoteBy = AES_ENCRYPT(:noteby, :key), DateEdited = NOW() WHERE ID = :noteid LIMIT 1");
                $result = $stmt->execute([
                                             'key' => AES_ENCRYPTION_KEY,
                                             'memberid' => $this->_memberID,
                                             'note' => $this->_note,
                                             'noteby' => $this->_note_by,
                                             'noteid' => $this->_id
                                         ]);
                error_log($stmt->errorInfo()[2]);
            } catch (Exception $e)
            {
                error_log("Failed to save Note record: " . $e);
            }

            if ($result === true) { return true; } else { return $stmt->ErrorInfo(); }
        }


        public function listAllNotes($memberid = 0)
        {
            if ($memberid == 0 || !is_numeric($memberid))
            {
                $memberid = $this->_id;
            }
            //Check that we have a parentid set up
            if (!is_numeric($memberid) || $memberid <= 0)
            {
                throw new Exception('Note object: No Member ID set on the property - cannot return notes for this member');
            }

            //Query database for all Members who have a ParentID linked to the their Member record that matches THIS ParentID
            $stmt = $this->_dbconn->prepare("SELECT ID, MemberID, AES_DECRYPT(Note, :key) AS Note, AES_DECRYPT(NoteBy, :key) AS NoteBy, DateEdited FROM MemberNotes WHERE MemberID =  :memberid ORDER BY DateEdited DESC");
            $stmt->execute([
                               'key' => AES_ENCRYPTION_KEY,
                               'memberid' => $memberid
                           ]);
            $notes = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $notes;
        }


        public function deleteNote($noteid = 0)
        {
            if ($noteid == 0 || !is_numeric($noteid))
            {
                $noteid = $this->_id;
            }
            //Check that we have a noteid set up
            if (!is_numeric($noteid) || $noteid <= 0)
            {
                throw new Exception('Note object: No Note ID set on the property - cannot delete this note');
            }
            $stmt = $this->_dbconn->prepare("DELETE FROM MemberNotes WHERE ID = :id LIMIT 1");
            $result = $stmt->execute([
                'id' => $noteid
                           ]);

            if ($result === true)
            {
                return true;
            }
            else
            {
                throw new Exception("Note: Could not delete the note as requested");
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

        public function getMemberID()
        {
            return $this->_memberID;
        }

        public function setMemberID($memberID)
        {
            $this->_memberID = $memberID;
        }

        public function getNote()
        {
            return $this->_note;
        }

        public function setNote($note)
        {
            $this->_note = $note;
        }

        public function getNoteBy()
        {
            return $this->_note_by;
        }

        public function setNoteBy($note_by)
        {
            $this->_note_by = $note_by;
        }

        public function getDateEdited()
        {
            return $this->_date_edited;
        }

        public function setDateEdited($date_edited)
        {
            $this->_date_edited = $date_edited;
        }
    }