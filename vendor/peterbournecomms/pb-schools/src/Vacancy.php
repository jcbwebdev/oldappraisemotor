<?php

    namespace PeterBourneComms\Schools;
    use PeterBourneComms\CMS\Database;
    use PDO;
    use PDOException;
    use Exception;

    /**
     * Deals with Vacancy items
     *
     * It will allow you to
     *  - retrieve an individual Vacancy
     *  - retrieve an array of all Vacancies
     *  - delete Vacancy
     *
     * Relies on the Vacancy table in this structure:
     *  ID
     *  Title
     *  Content
     *  DateExpires
     *  DateDisplay
     *  AuthorID
     *  AuthorName
     *  URLText
     *  MetaDesc
     *  MetaKey
     *  MetaTitle
     *
     *
     * @author Peter Bourne
     * @version 1.0
     *
     */
    class Vacancy
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
        protected $_dateexpires;
        /**
         * @var
         */
        protected $_datedisplay;
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


        /**
         * @var
         */
        protected $_allitems;


        /**
         * Vacancy constructor.
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
                    throw new Exception('Class Vacancy requires id to be specified as an integer');
                }

                //Retrieve current Vacancy information
                if (isset($id))
                {
                    $this->_id = $id;
                    $this->getItemById($id);
                }
            }
        }


        /**
         * Retrieves specified Vacancy record ID from Vacancy table
         * Populates object member elements
         *
         * @param $id
         */
        public function getItemById($id)
        {
            try
            {
                $stmt = $this->_dbconn->prepare("SELECT * FROM Vacancies WHERE ID =  :id LIMIT 1");
                $stmt->execute(['id' => $id]);
                $story = $stmt->fetch();
            } catch (Exception $e)
            {
                error_log("Failed to retrieve Vacancy item details when searching by ID" . $e);
            }

            //Store details in relevant users
            $this->_id = $story['ID'];
            $this->_title = $story['Title'];
            $this->_content = $story['Content'];
            $this->_dateexpires = $story['DateExpires'];
            $this->_datedisplay = $story['DateDisplay'];
            $this->_authorid = $story['AuthorID'];
            $this->_authorname = $story['AuthorName'];
            $this->_urltext = $story['URLText'];
            $this->_metadesc = $story['MetaDesc'];
            $this->_metakey = $story['MetaKey'];
            $this->_metatitle = $story['MetaTitle'];

            return $story;
        }

        public function getItemByUrl($urltext)
        {
            try
            {
                $stmt = $this->_dbconn->prepare("SELECT ID FROM Vacancies WHERE URLText LIKE :urltext LIMIT 1");
                $stmt->execute(['urltext' => $urltext]);
                $story = $stmt->fetch();
            } catch (Exception $e)
            {
                error_log("Failed to retrieve Vacancy item details when searching by URLText" . $e);
            }
            //Use other function to populate rest of properties (no sense writing it twice!)_
            return $this->getItemById($story['ID']);
        }



        /**
         * Saves the current object to the Vacancy table in the database
         *
         * @throws Exception
         */
        public function saveItem()
        {
            //First need to determine if this is a new Vacancy item
            if ($this->_id <= 0)
            {
                $this->createNewItem(); //_id should now be set
            }

            try
            {
                $stmt = $this->_dbconn->prepare("UPDATE Vacancies SET Title = :title, Content = :content, DateExpires = :dateexpires, DateDisplay = :datedisplay, AuthorID = :authorid, AuthorName = :authorname, URLText = :urltext, MetaTitle = :metatitle, MetaDesc = :metadesc, MetaKey = :metakey WHERE ID = :id LIMIT 1");
                $result = $stmt->execute([
                                             'title' => $this->_title,
                                             'content' => $this->_content,
                                             'dateexpires' => $this->_dateexpires,
                                             'datedisplay' => $this->_datedisplay,
                                             'authorid' => $this->_authorid,
                                             'authorname' => $this->_authorname,
                                             'urltext' => $this->_urltext,
                                             'metatitle' => $this->_metatitle,
                                             'metakey' => $this->_metakey,
                                             'metadesc' => $this->_metadesc,
                                             'id' => $this->_id
                                         ]);
                if ($result == true)
                {
                    return true;
                }
                else
                {
                    return $stmt->errorInfo();
                }
            } catch (Exception $e)
            {
                error_log("Failed to save Vacancy record: " . $e);
            }
        }

        /**
         * Create new empty Vacancy item
         *
         * Sets the _id property accordingly
         */
        public function createNewItem()
        {
            //Only execute if the _id property isn't already set
            if (is_numeric($this->_id) || $this->_id > 0)
            {
                throw new Exception('You cannot create a new Vacancy item at this stage - the id property is already set as ' . $this->_id);
            }

            //Create DB item
            try
            {
                $result = $this->_dbconn->query("INSERT INTO Vacancies SET Title = ''");
                $lastID = $this->_dbconn->lastInsertId();

                if ($lastID > 0)
                {
                    $this->_id = $lastID;
                }
            } catch (Exception $e)
            {
                error_log("Failed to create new Vacancy record: " . $e);
            }
        }

        /**
         * Returns all Vacancy records and fields in Assoc array
         * Flags for retrieving just Panel items OR All future items OR All items OR Just past items
         *
         * @param string    Panel | Current | All | Past
         * @param int       Cut off in months for items
         *
         * @return mixed
         */
        public function getAllVacancyItems($flagWhichItems = 'All', $CutOffInMonths = null)
        {
            switch ($flagWhichItems)
            {
                case 'Current':
                    $sql = "SELECT * FROM Vacancies WHERE DateDisplay <= NOW() AND DateExpires >= NOW()";
                    break;
                case 'All':
                    $sql = "SELECT * FROM Vacancies";
                    break;
                case 'Past':
                    $sql = "SELECT * FROM Vacancies WHERE DateExpires <= NOW()";
                    break;
                default:
                    $sql = "SELECT * FROM Vacancies WHERE DateDisplay <= NOW() AND DateExpires >= NOW()";
                    break;
            }


            //Limit the results returned? Only relevant for Current and Past items (commonly used on Vacancy index display page)
            if (is_numeric($CutOffInMonths) && $CutOffInMonths > 0)
            {
                //Convert to int
                $CutOffInMonths = floor($CutOffInMonths);
                switch ($flagWhichItems)
                {
                    case 'Current':
                        $sql .= " AND DATE_ADD(DateDisplay, INTERVAL " . $CutOffInMonths . " MONTH) > NOW() AND DateDisplay <= NOW()";
                        break;
                    case 'Past':
                        $sql .= " AND DATE_ADD(DateDisplay, INTERVAL " . $CutOffInMonths . " MONTH) <= NOW() AND DateDisplay <= NOW()";
                        break;
                }
            }
            $sql .= " ORDER BY DateDisplay DESC";


            try
            {
                $stmt = $this->_dbconn->query($sql);
                $stories = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $e)
            {
                error_log("Failed to retrieve Vacancy items" . $e);
            }

            //Store details in relevant member
            $this->_allitems = $stories;

            //return the array
            return $stories;
        }

        /**
         * Delete the complete Vacancy item - including any images
         *
         * @return mixed
         */
        public function deleteItem()
        {
            if (!is_numeric($this->_id))
            {
                throw new Exception('Class Vacancy requires the Vacancy item ID to be set if you are trying to delete the item');
            }

            //Now delete the item from the DB
            try
            {
                $stmt = $this->_dbconn->prepare("DELETE FROM Vacancies WHERE ID = :id LIMIT 1");
                $result = $stmt->execute([
                                             'id' => $this->_id
                                         ]);
            } catch (Exception $e)
            {
                error_log("Failed to delete Vacancy record: " . $e);
            }

            if ($result == true)
            {
                //Unset the properties
                $this->_id = null;
                $this->_title = null;
                $this->_content = null;
                $this->_dateexpires = null;
                $this->_datedisplay = null;
                $this->_authorid = null;
                $this->_authorname = null;

                return true;
            }
            else
            {
                return false;
            }

        }


        /**
         * Function to check if a similar URL already exists in the Vacancy table
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
                throw new Exception('Vacancy needs the new URL specifying as a string');
            }


            if (clean_int($ID) > 0)
            {
                $sql = "SELECT ID FROM Vacancies WHERE URLText = :urltext AND ID != :id";
                $vars = array('urltext' => $ContentURL, 'id' => $ID);
            }
            else
            {
                $sql = "SELECT ID FROM Vacancies WHERE URLText = :urltext AND (ID IS NULL OR ID <= 0)";
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
            $sql = "SELECT ID FROM Vacancies WHERE (Title LIKE :needle OR Content LIKE :needle) AND DateDisplay <= NOW() AND DateExpires >= NOW()";
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
                    $link = "//" . SITEFQDN . "/vacancy/" . $content['URLText'];
                }
                else
                {
                    $link = "//" . SITEFQDN . "/content/vacanciesview.php?id=" . $content['ID'];
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
        public function getDateExpires()
        {
            return $this->_dateexpires;
        }

        /**
         * @param $DateExpires
         */
        public function setDateExpires($DateExpires)
        {
            $this->_dateexpires = $DateExpires;
        }

        /**
         * @return mixed
         */
        public function getDateDisplay()
        {
            return $this->_datedisplay;
        }

        /**
         * @param $datedisplay
         */
        public function setDateDisplay($datedisplay)
        {
            $this->_datedisplay = $datedisplay;
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

    }