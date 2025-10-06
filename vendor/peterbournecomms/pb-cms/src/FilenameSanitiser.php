<?php

    namespace PeterBourneComms\CMS;
    use PDO;
    use PDOException;
    use Exception;


    /**
     * This function will simply receive a string and then sanitise for characters that should not be used in the file system
     *
     * @author Peter Bourne
     * @version 1.2
     *
     *  1.1     21.12.2021  Added iconv function to remove accented characters
     *  1.2     07.09.2023  Added % check
     *
     */
    class FilenameSanitiser
    {
        protected $_filename;

        public function __construct($passedFilename = null)
        {
            //Set up the property
            $this->_filename = $passedFilename;
        }

        public function sanitiseFilename()
        {
            $NewFilename = strip_tags($this->_filename);
            $NewFilename = str_replace('&amp;', '-', $NewFilename);
            $NewFilename = str_replace('&', '-', $NewFilename);
            $NewFilename = str_replace('(', '', $NewFilename);
            $NewFilename = str_replace(')', '', $NewFilename);
            $NewFilename = str_replace('!', '', $NewFilename);
            $NewFilename = str_replace('\'', '', $NewFilename);
            $NewFilename = str_replace('\/', '-', $NewFilename);
            $NewFilename = str_replace('/', '-', $NewFilename);
            $NewFilename = str_replace('\\', '-', $NewFilename);
            $NewFilename = str_replace('?', '-', $NewFilename);
            $NewFilename = str_replace('#', '-', $NewFilename);
            $NewFilename = str_replace(' ', '-', $NewFilename);
            $NewFilename = str_replace('"', '', $NewFilename);
            $NewFilename = str_replace('%', 'pc', $NewFilename);
            //New Dec 2021 addition
            setlocale(LC_ALL, 'en_GB.UTF-8');
            $NewFilename = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $NewFilename);

            $this->_filename = $NewFilename;
        }


        //Getters and setters
        public function getFilename()
        {
            return $this->_filename;
        }

        public function setFilename($filename)
        {

            $this->sanitiseFilename($filename);
        }

    }