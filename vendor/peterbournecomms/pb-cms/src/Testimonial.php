<?php

    namespace PeterBourneComms\CMS;
    use PDO;
    use PDOException;
    use Exception;


    /**
     * Deals with Testimonial items
     *
     * It will allow you to
     *  - retrieve an individual testimonial item
     *  - retrieve an array of all testimonial items
     *  - delete testimonial item
     *
     * Relies on the Testimonials table in this structure:
     *  ID
     *  ContentID
     *  Quote
     *  Content
     *  Attribution
     *  URLText
     *  MetaTitle
     *  MetaDesc
     *  MetaKey
     *  AuthorID
     *  AuthorName
     *
     * Created by PhpStorm.
     * User: peterbourne
     * Date: 04/10/2017
     * Time: 11:53
     */
    class Testimonial
    {
        /**
         * @var mixed
         */
        protected $_dbconn;
        /**
         * @var int|string
         */
        protected $_id;
        /**
         * @var
         */
        protected $_contentid;
        protected $_quote;
        /**
         * @var
         */
        protected $_content;
        /**
         * @var
         */
        protected $_attribution;
        protected $_authorid;
        /**
         * @var
         */
        protected $_authorname;

        protected $_metadesc;
        protected $_metakey;
        protected $_metatitle;
        protected $_urltext;


        /**
         * @var
         */
        protected $_allitems;


        /**
         * Testimonial constructor.
         *
         * @param null   $id
         *
         * @throws Exception
         */
        public function __construct($id = null)
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
                    throw new Exception('Class Testimonial requires id to be specified as an integer');
                }

                //Retrieve current testimonial information
                if (isset($id))
                {
                    $this->_id = $id;
                    $this->getItemById($id);
                }

            }
        }


        /**
         * Retrieves specified record ID from table
         * Populates object member elements
         *
         * @param $id
         */
        public function getItemById($id)
        {
            try
            {
                $stmt = $this->_dbconn->prepare("SELECT * FROM Testimonials WHERE ID =  :id LIMIT 1");
                $stmt->execute(['id' => $id]);
                $story = $stmt->fetch();
            } catch (Exception $e)
            {
                error_log("Failed to retrieve Testimonial item details when searching by ID" . $e);
            }

            //Store details in relevant members
            $this->_id = $story['ID'];
            $this->_contentid = $story['ContentID'];
            $this->_quote = $story['Quote'];
            $this->_content = $story['Content'];
            $this->_authorid = $story['AuthorID'];
            $this->_authorname = $story['AuthorName'];
            $this->_attribution = $story['Attribution'];
            $this->_metatitle = $story['MetaTitle'];
            $this->_metadesc = $story['MetaDesc'];
            $this->_metakey = $story['MetaKey'];
            $this->_urltext = $story['URLText'];

            //Retrieve information on Parent Content item
            $PO = new Content($this->_contentid);
            $ContentTitle = $PO->getTitle();

            $story['ContentTitle'] = $ContentTitle;

            return $story;
        }

        public function getItemByUrl($urltext)
        {
            try
            {
                $stmt = $this->_dbconn->prepare("SELECT ID FROM Testimonials WHERE URLText LIKE :urltext LIMIT 1");
                $stmt->execute(['urltext' => $urltext]);
                $story = $stmt->fetch();
            } catch (Exception $e)
            {
                error_log("Failed to retrieve Testimonial item details when searching by URLText" . $e);
            }
            //Use other function to populate rest of properties (no sense writing it twice!)_
            $this->getItemById($story['ID']);
        }



        /**
         * Create new empty testimonial item
         *
         * Sets the _id property accordingly
         */
        public function createNewItem()
        {
            //Only execute if the _id property isn't already set
            if (is_numeric($this->_id) || $this->_id > 0)
            {
                throw new Exception('You cannot create a new Testimonial item at this stage - the id property is already set as '.$this->_id);
            }

            //Create DB item
            try
            {
                $result = $this->_dbconn->query("INSERT INTO Testimonials SET Quote = ''");
                $lastID = $this->_dbconn->lastInsertId();

                if ($lastID > 0)
                {
                    $this->_id = $lastID;
                }
            }
            catch (Exception $e) {
                error_log("Failed to create new Testimonial record: " . $e);
            }
        }


        /**
         * Saves the current object to the Testimonials table in the database
         *
         * @throws Exception
         */
        public function saveItem()
        {
            //First need to determine if this is a new Test item
            if ($this->_id <= 0)
            {
                $this->createNewItem(); //_id should now be set
            }

            try {
                $stmt = $this->_dbconn->prepare("UPDATE Testimonials SET ContentID = :contentid, Quote = :quote, Content = :content, Attribution = :attribution, URLText = :urltext, MetaDesc = :metadesc, MetaKey = :metakey, MetaTitle = :metatitle, AuthorID = :authorid, AuthorName = :authorname WHERE ID = :id LIMIT 1");
                $result = $stmt->execute([
                    'quote' => $this->_quote,
                    'contentid' => $this->_contentid,
                    'content' => $this->_content,
                    'attribution' => $this->_attribution,
                    'metatitle' => $this->_metatitle,
                    'metadesc' => $this->_metadesc,
                    'metakey' => $this->_metakey,
                    'urltext' => $this->_urltext,
                    'authorid' => $this->_authorid,
                    'authorname' => $this->_authorname,
                    'id' => $this->_id
                               ]);
                if ($result == true) { return true;  } else { error_log($stmt->errorInfo()[2]); return false; }
            } catch (Exception $e) {
                error_log("Failed to save Testimonial record: " . $e);
                return false;
            }
        }


        /**
         * Returns all testimonial records and fields in Assoc array
         *
         * @param int $contentid ContentID (if null - get all in random order)
         * @param str $sortorder ('rand' | 'quote' | 'attribution')
         *
         * @return mixed
         * @throws Exception
         */
        public function getAllTestimonials($contentid = 0, $sortorder = '')
        {

            if (isset($contentid) && !is_numeric($contentid))
            {
                throw new Exception('Testimonials: Sorry - cannot retrieve testimonials.');
            }

            $sql = "SELECT ID FROM Testimonials";
            $query_vars = array();
            if ($contentid > 0)
            {
                $sql .= " WHERE ContentID = :contentid";
                $query_vars = array('contentid' => $contentid);
            }

            switch($sortorder)
            {
                case 'rand':
                    $sql .= " ORDER BY RAND()";
                    break;
                case 'quote':
                    $sql .= " ORDER BY Quote ASC";
                    break;
                case 'attribution':
                    $sql .= " ORDER BY Attribution ASC";
                    break;
                default:
                    $sql .= " ORDER BY RAND()";
                    break;
            }

            try
            {
                $stmt = $this->_dbconn->prepare($sql);
                $result = $stmt->execute($query_vars);
                $stories = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $e)
            {
                error_log("Failed to retrieve testimonial items" . $e);
            }

            if (is_array($stories) && count($stories) > 0)
            {
                $all = array();
                foreach ($stories as $story)
                {
                    $testy = $this->getItemById($story['ID']);
                    $all[] = $testy;
                }
            }
            //Store details in relevant member
            $this->_allitems = $all;

            //return the array
            return $all;
        }

        /**
         * Delete the complete news item - including any images
         *
         * @return mixed
         * @throws Exception
         */
        public function deleteItem()
        {
            if (!is_numeric($this->_id))
            {
                throw new Exception('Class Testimonial requires the news item ID to be set if you are trying to delete the item');
            }

            //Now delete the item from the DB
            try {
                $stmt = $this->_dbconn->prepare("DELETE FROM Testimonials WHERE ID = :id LIMIT 1");
                $result = $stmt->execute([
                                   'id' => $this->_id
                               ]);
            } catch (Exception $e) {
                error_log("Failed to delete testimonial record: " . $e);
            }

            if ($result == true)
            {
                //Unset the properties
                $this->_id = null;
                $this->_quote = null;
                $this->_contentid = null;
                $this->_content = null;
                $this->_authorid = null;
                $this->_authorname = null;
                $this->_attribution = null;
                $this->_metatitle = null;
                $this->_metadesc = null;
                $this->_metakey = null;
                $this->_urltext = null;

                return true;
            }
            else
            {
                return false;
            }
        }

        /**
         * Function to check if a similar URL already exists in the News table
         * Returns TRUE if VALID, ie: not present in database
         *
         *
         *
         * @param     $ContentURL
         * @param int $ID
         *
         * @return bool
         * @throws Exception
         */

        public function URLTextValid($ContentURL, $ID = 0)
        {
            if ($ID <= 0)
            {
                $ID = $this->_id;
            }
            if (!is_string($ContentURL))
            {
                throw new Exception('Testimonial needs the new URL specifying as a string');
            }


            if (clean_int($ID) > 0)
            {
                $sql = "SELECT ID FROM Testimonials WHERE URLText = :urltext AND ID != :id";
                $vars = array('urltext' => $ContentURL, 'id' => $ID);
            }
            else
            {
                $sql = "SELECT ID FROM Testimonials WHERE URLText = :urltext AND (ID IS NULL OR ID <= 0)";
                $vars = array('urltext' => $ContentURL);
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
        public function getContentid()
        {
            return $this->_contentid;
        }

        /**
         * @param mixed $contentid
         */
        public function setContentid($contentid)
        {
            $this->_contentid = $contentid;
        }

        /**
         * @return mixed
         */
        public function getQuote()
        {
            return $this->_quote;
        }

        /**
         * @param mixed $quote
         */
        public function setQuote($quote)
        {
            $this->_quote = $quote;
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
        public function setContent($content)
        {
            $this->_content = $content;
        }

        /**
         * @return mixed
         */
        public function getAttribution()
        {
            return $this->_attribution;
        }

        /**
         * @param mixed $attribution
         */
        public function setAttribution($attribution)
        {
            $this->_attribution = $attribution;
        }

        /**
         * @return mixed
         */
        public function getAuthorid()
        {
            return $this->_authorid;
        }

        /**
         * @param mixed $authorid
         */
        public function setAuthorid($authorid)
        {
            $this->_authorid = $authorid;
        }

        /**
         * @return mixed
         */
        public function getAuthorname()
        {
            return $this->_authorname;
        }

        /**
         * @param mixed $authorname
         */
        public function setAuthorname($authorname)
        {
            $this->_authorname = $authorname;
        }

        /**
         * @return mixed
         */
        public function getMetadesc()
        {
            return $this->_metadesc;
        }

        /**
         * @param mixed $metadesc
         */
        public function setMetadesc($metadesc)
        {
            if (strlen($metadesc) > 255) {
                $metadesc = substr($metadesc,0,255);
            }
            $this->_metadesc = $metadesc;
        }

        /**
         * @return mixed
         */
        public function getMetakey()
        {
            return $this->_metakey;
        }

        /**
         * @param mixed $metakey
         */
        public function setMetakey($metakey)
        {
            if (strlen($metakey) > 255) {
                $metakey = substr($metakey,0,255);
            }
            $this->_metakey = $metakey;
        }

        /**
         * @return mixed
         */
        public function getMetatitle()
        {
            return $this->_metatitle;
        }

        /**
         * @param mixed $metatitle
         */
        public function setMetatitle($metatitle)
        {
            if (strlen($metatitle) > 255) {
                $metatitle = substr($metatitle,0,255);
            }
            $this->_metatitle = $metatitle;
        }

        /**
         * @return mixed
         */
        public function getUrltext()
        {
            return $this->_urltext;
        }

        /**
         * @param mixed $urltext
         */
        public function setUrltext($urltext)
        {
            $this->_urltext = $urltext;
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

    }