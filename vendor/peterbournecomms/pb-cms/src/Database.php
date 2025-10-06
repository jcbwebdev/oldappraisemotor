<?php

    namespace PeterBourneComms\CMS;
    use PDO;
    use PDOException;
    use Exception;

    /**
     * Class to connect to MySQL / Maria database
     *
     * Takes PHP Constants for DB_HOST, DB_NAME, DB_USER, DB_PASS (and PASSWORD_SALT) - although you can override
     * them and connect to a different database to the config-specified one.
     *
     *
     *
     * @author Peter Bourne
     * @version 1.1
     *
     * $history
     *
     * ---          1.0     Original
     * 15.02.2023   1.1     Changed to persistent connection
     *
     */
    class Database
    {
        /**
         * @var mixed connection resource
         */
        protected $_dbconn = false;


        /**
         * Database constructor - instantiates the connection tot he database - or logs an error and dies.
         *
         * @param string $host
         * @param string $user
         * @param string $pass
         * @param string $db
         * @param string $charset
         */
        public function __construct($host = DB_HOST, $user = DB_USER, $pass = DB_PASS, $db = DB_NAME, $charset = DB_CHARSET)
        {
            try {
                $this->_dbconn = new PDO('mysql:host=' . $host . ';dbname=' . $db . ';charset=' . $charset, $user, $pass, array(PDO::ATTR_PERSISTENT => TRUE));
                $this->_dbconn->setAttribute( PDO::ATTR_EMULATE_PREPARES, true );
            } catch (PDOException $e) {
                error_log('Database connection failed with Exception: ' . $e);
                die('Sorry - there was a problem.');
            }
        }

        /**
         * Destroy DB connection
         */
        public function __destruct()
        {
            $this->_dbconn = null;
        }


        /**
         * Close connection to database manually
         *
         */
        public function closeConnection()
        {
            $this->_dbconn = null;
        }


        /**
         * Return the connection resource
         *
         * @return mixed
         */
        public function getConnection()
        {
            return $this->_dbconn;
        }



        public function getLastId()
        {
            return $this->lastInsertId();
        }

    }