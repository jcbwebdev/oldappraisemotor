<?php

    namespace PeterBourneComms\CMS;
    use PDO;
    use PDOException;
    use Exception;


    /**
     * Deals with Homepage content
     *
     * It will allow you to
     *  - retrieve from the HomePage table
     *  - update the HomePage table (always record ID = 1)
     *
     * Relies on the Content table in this structure:
     *  ID
     *  Title
     *  SubTitle
     *  Content
     *  Col2Content
     *  AuthorID
     *  AuthorName
     *  MetaDesc
     *  MetaKey
     *  MetaTitle
     *
     * @author Peter Bourne
     * @version 1.0
     *
     */
    class HomePage
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
        protected $_title;
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

        protected $_metadesc;
        protected $_metakey;
        protected $_metatitle;

        protected $_subtitle;
        protected $_col2_content;

        /**
         * HomePage constructor.
         *
         * @throws Exception
         */
        public function __construct()
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

                //Retrieve current home page
                $this->_id = 1;
                $this->getItem();
            }
        }


        /**
         * Retrieves specified content record ID from Content table
         * Populates object member elements
         *
         * @param $id
         */
        public function getItem()
        {
            try
            {
                $stmt = $this->_dbconn->prepare("SELECT * FROM HomePage WHERE ID =  :id LIMIT 1");
                $stmt->execute(['id' => $this->_id]);
                $story = $stmt->fetch();
            } catch (Exception $e)
            {
                error_log("Failed to retrieve HomePage item details when searching by ID" . $e);
            }

            //Store details in relevant members
            $this->_id = $story['ID'];
            $this->_title = $story['Title'];
            $this->_subtitle = $story['SubTitle'];
            $this->_content = $story['Content'];
            $this->_col2_content = $story['Col2Content'];
            $this->_authorid = $story['AuthorID'];
            $this->_authorname = $story['AuthorName'];
            $this->_metadesc = $story['MetaDesc'];
            $this->_metakey = $story['MetaKey'];
            $this->_metatitle = $story['MetaTitle'];

            return $story;
        }


        /**
         * Saves the current object to the HomePage table in the database
         *
         * @throws Exception
         */
        public function saveItem()
        {
            //First need to determine if this is a new Content item
            if ($this->_id <= 0)
            {
                throw new Exception('Unable to save Homepage record - as no ID set in object');
            }

            try {
                $stmt = $this->_dbconn->prepare("UPDATE HomePage SET Title = :title, SubTitle = :subtitle, Content = :content, Col2Content = :col2content, MetaDesc = :metadesc, MetaKey = :metakey, MetaTitle = :metatitle, AuthorID = :authorid, AuthorName = :authorname WHERE ID = :id LIMIT 1");
                $result = $stmt->execute([
                    'title' => $this->_title,
                    'subtitle' => $this->_subtitle,
                    'content' => $this->_content,
                    'col2content' => $this->_col2_content,
                    'metadesc' => $this->_metadesc,
                    'metakey' => $this->_metakey,
                    'metatitle' => $this->_metatitle,
                    'authorid' => $this->_authorid,
                    'authorname' => $this->_authorname,
                    'id' => $this->_id
                               ]);
                if ($result === true) {
                    return true;
                } else {
                    return $stmt->errorInfo();
                }
            } catch (Exception $e) {
                error_log("Failed to save Home page record: " . $e);
            }
        }


        ###########################################################
        # Getters and Setters
        ###########################################################


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

        public function getSubTitle()
        {
            return $this->_subtitle;
        }

        public function setSubTitle($subtitle)
        {
            $this->_subtitle = $subtitle;
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

        public function getCol2Content()
        {
            return $this->_col2_content;
        }

        public function setCol2Content($col2_content)
        {
            $this->_col2_content = $col2_content;
        }

        /**
         * @return mixed
         */
        /*public function getDateDisplay()
        {
            return $this->_datedisplay;
        }*/

        /**
         * @param $datedisplay
         *//*
        public function setDateDisplay($datedisplay)
        {
            $this->_datedisplay = $datedisplay;
        }*/

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
    }