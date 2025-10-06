<?php

    namespace PeterBourneComms\CMS;
    use PDO;
    use PDOException;
    use Exception;


    /**
     * A user-friendly class for handling dates.
     *
     * Extends the DateTime class in >= PHP 5.2 to build and format dates without the
     * need to memorize PHP date format specifiers. Eliminates inaccurate results when
     * modifying dates to add or subtract days, weeks, months, or years.
     * Static dateDiff() method calculates number of days between two dates.
     *
     * All retyped by Peter and Repackaged as and prefixed with PB (March 2017).
     *
     * @package PB
     * @author David Powers
     * @copyright David Powers 2008
     * @version 1.0.2
     *
     */
    class PBDate extends DateTime
    {
        ##########################
        # PROPERTIES
        ##########################
        /**
         * @var int
         */
        protected $_year;
        /**
         * @var int
         */
        protected $_month;
        /**
         * @var int
         */
        protected $_day;


        ##########################
        # STATIC
        ##########################

        /**
         * @param PBDate $startDate
         * @param PBDate $endDate
         *
         * @return float|int
         */
        static public function dateDiff(PBDate $startDate, PBDate $endDate)
        {
            $start = gmmktime(0,0,0, $startDate->_month, $startDate->_day, $startDate->_year);
            $end = gmmktime(0,0,0, $endDate->_month, $endDate->_day, $endDate->_year);
            return ($end - $start) / (60 * 60 * 24);
        }


        /**
         * @return string
         */
        public function __toString()
        {
            return $this->format('l, F jS, Y');
        }


        ##########################
        # CONSTRUCTOR
        ##########################
        /**
         * PBDate constructor.
         *
         * @param null $timezone
         */
        public function __construct($timezone = null)
        {
            // call the parent constructor
            if ($timezone) {
                parent::__construct('now', $timezone);
            } else {
                parent::__construct('now');
            }


            //assign the values to the class properties
            $this->_year = (int)$this->format('Y');
            $this->_month = (int)$this->format('n');
            $this->_day = (int)$this->format('j');
        }


        ##########################
        # OVERRIDDEN
        ##########################
        /**
         * Sets Date on object
         *
         * Overrides the default method provided by PHP - by checking
         * that the provided params are _numeric_ - and that the resulting
         * date is a _valid date_.
         *
         *
         * @param int $year
         * @param int $month
         * @param int $day
         *
         * @throws Exception
         */
        public function setDate($year, $month, $day)
        {
            if (!is_numeric($month) || !is_numeric($day) || !is_numeric($year)) {
                throw new Exception('setDate() expects three numbers separated by commas in the order: year, month, day');
            }
            if (!checkDate($month, $day, $year)) {
                throw new Exception('Non-existent date');
            }
            parent::setDate($year, $month, $day);
            $this->_year = (int)$year;
            $this->_month = (int)$month;
            $this->_day = (int)$day;
        }


        /**
         * @param int $hours
         * @param int $minutes
         * @param int $seconds
         *
         * @throws Exception
         */
        public function setTime($hours, $minutes, $seconds = 0)
        {
            if (!is_numeric($hours) || !is_numeric($minutes) || !is_numeric($seconds)) {
                throw new Exception('setTime() expects two or three numbers separated by comms in the order: hours, minutes, seconds');
            }
            $outOfRange = false;
            if ($hours < 0 || $hours > 23) {
                $outOfRange = true;
            }
            if ($minutes < 0 || $minutes > 59) {
                $outOfRange = true;
            }
            if ($seconds < 0 || $seconds > 59) {
                $outOfRange = true;
            }
            if ($outOfRange) {
                throw new Exception('Invalid time');
            }
            parent::setTime($hours, $minutes, $seconds);
        }

        /**
         * @param string $null
         *
         * @throws Exception
         */
        public function modify($null)
        {
            throw new Exception('modify() has been disabled');
        }




        /**
         * @param $USDate
         *
         * @throws Exception
         */
        public function setMDY($USDate)
        {
            $dateParts = preg_split('{[-/ :.]}', $USDate);
            if (!is_array($dateParts) || count($dateParts) != 3) {
                throw new Exception('setMDY() expects a date as "MM/DD/YYYY"');
            }
            $this->setDate($dateParts[2], $dateParts[0], $dateParts[1]);
        }

        /**
         * @param $EuroDate
         *
         * @throws Exception
         */
        public function setDMY($EuroDate)
        {
            $dateParts = preg_split('{[-/ :.]}', $EuroDate);
            if (!is_array($dateParts) || count($dateParts) != 3) {
                throw new Exception('setDMY() expects a date as "DD/MM/YYYY"');
            }
            $this->setDate($dateParts[2], $dateParts[1], $dateParts[0]);
        }

        /**
         * @param $MySQLDate
         *
         * @throws Exception
         */
        public function setFromMySQL($MySQLDate)
        {
            $dateParts = preg_split('{[-/ :.]}', $MySQLDate);
            if (!is_array($dateParts) || count($dateParts) != 3) {
                throw new Exception('setFromMySQL() expects a date as "YYYY-MM-DD"');
            }
            $this->setDate($dateParts[0], $dateParts[1], $dateParts[2]);
        }


        /**
         * @param bool $leadingZeros
         *
         * @return string
         */
        public function getMDY($leadingZeros = false)
        {
            if ($leadingZeros) {
                return $this->format('m/d/Y');
            } else {
                return $this->format('n/j/Y');
            }
        }

        /**
         * @param bool $leadingZeros
         *
         * @return string
         */
        public function getDMY($leadingZeros = false)
        {
            if ($leadingZeros) {
                return $this->format('d/m/Y');
            } else {
                return $this->format('j/n/Y');
            }
        }

        /**
         * @return string
         */
        public function getMySQLFormat()
        {
            return $this->format('Y-m-d');
        }


        /**
         * @return int
         */
        public function getFullYear()
        {
            return $this->_year;
        }

        /**
         * @return string
         */
        public function getYear()
        {
            return $this->format('y');
        }

        /**
         * @param bool $leadingZero
         *
         * @return int|string
         */
        public function getMonth($leadingZero = false)
        {
            return $leadingZero ? $this->format('m') : $this->_month;
        }

        /**
         * @return string
         */
        public function getMonthName()
        {
            return $this->format('F');
        }

        /**
         * @return string
         */
        public function getMonthAbbr()
        {
            return $this->format('M');
        }

        /**
         * @param bool $leadingZero
         *
         * @return int|string
         */
        public function getDay($leadingZero = false)
        {
            return $leadingZero ? $this->format('d') : $this->_day;
        }

        /**
         * @return string
         */
        public function getDayOrdinal()
        {
            return $this->format('jS');
        }

        /**
         * @return string
         */
        public function getDayName()
        {
            return $this->format('l');
        }

        /**
         * @return string
         */
        public function getDayAbbr()
        {
            return $this->format('D');
        }


        /**
         * @param $numDays
         *
         * @throws Exception
         */
        public function addDays($numDays)
        {
            if (!is_numeric($numDays) || $numDays < 1) {
                throw new Exception('addDays() expects a positive integer');
            }
            parent::modify('+' . intval($numDays) . ' days');
        }

        /**
         * @param $numDays
         *
         * @throws Exception
         */
        public function subDays($numDays)
        {
            if (!is_numeric($numDays)) {
                throw new Exception('subDays() expects an integer');
            }
            parent::modify('-' . abs(intval($numDays)) . ' days');
        }

        /**
         * @param $numWeeks
         *
         * @throws Exception
         */
        public function addWeeks($numWeeks)
        {
            if (!is_numeric($numWeeks) || $numWeeks < 1) {
                throw new Exception('addWeeks() expects a positive integer');
            }
            parent::modify('+'.intval($numWeeks) . ' weeks');
        }

        /**
         * @param $numWeeks
         *
         * @throws Exception
         */
        public function subWeeks($numWeeks)
        {
            if (!is_numeric($numWeeks)) {
                throw new Exception('subWeeks() expects an integer');
            }
            parent::modify('-' . abs(intval($numWeeks)) . ' weeks');
        }


        /**
         * @param $numMonths
         *
         * @throws Exception
         */
        public function addMonths($numMonths)
        {
            if(!is_numeric($numMonths) || $numMonths < 1) {
                throw new Exception('addMonths() expects a positive integer');
            }
            $numMonths = (int) $numMonths;

            // Add the months to the current month number
            $newValue = $this->_month + $numMonths;

            // If the new value is less than or equal to 12, the year
            // doesn't change, so just assign the new value to the month
            if ($newValue <= 12) {
                $this->_month = $newValue;
            } else {
                // A new value greater than 12 means calculating both
                // the month and the year. Calculating the year is
                // different for December, so do modulo division
                // by 12 on the new value. If the remainder is not 0,
                // the new month is not December.
                $notDecember = $newValue % 12;
                if ($notDecember) {
                    // The remainder of the modulo division is the new month
                    $this->_month = $notDecember;
                    // Divide the new value by 12 and round down to get the
                    // number of years to add
                    $this->_year += floor($newValue / 12);
                } else {
                    // The new month must be December
                    $this->_month = 12;
                    $this->_year += ($newValue / 12) - 1;
                }
            }

            $this->checkLastDayOfMonth();
            parent::setDate($this->_year, $this->_month, $this->_day);
        }



        /**
         * @return bool
         */
        public function isLeap()
        {
            if ($this->_year % 400 == 0 || ($this->_year % 4 == 0 && $this->_year % 100 != 0)) {
                return true;
            } else {
                return false;
            }
        }


        /**
         * @param $numMonths
         *
         * @throws Exception
         */
        public function subMonths($numMonths)
        {
            if (!is_numeric($numMonths)) {
                throw new Exception('subMonths() expects an integer');
            }
            $numMonths = abs(intval($numMonths));
            // Subtract the months  from the current month number
            $newValue = $this->_month - $numMonths;
            // If the result is greater than 0, its still the same year,
            // and you can assign the new value to the month.
            if ($newValue > 0) {
                $this->_month = $newValue;
            } else {
                // Create an array of the months in reverse
                $months = range(12, 1);
                // Get the absolute value of $newValue
                $newValue = abs($newValue);
                //Get the array position of the resulting month
                $monthPosition = $newValue % 12;
                $this->_month = $months[$monthPosition];
                // Arrays begin at 0, so if $monthPosition is 0,
                // it must be December
                if ($monthPosition) {
                    $this->_year -= ceil($newValue / 12);
                } else {
                    $this->_year -= ceil($newValue / 12) + 1;
                }
            }

            $this->checkLastDayOfMonth();
            parent::setDate($this->_year, $this->_month, $this->_day);
        }


        /**
         * @param $numYears
         *
         * @throws Exception
         */
        public function addYears($numYears)
        {
            if (!is_numeric($numYears) || $numYears < 1) {
                throw new Exception ('addYears() expects a positive integer');
            }

            $this->_year += (int) $numYears;
            $this->checkLastDayOfMonth();
            parent::setDate($this->_year, $this->_month, $this->_day);
        }


        /**
         * @param $numYears
         *
         * @throws Exception
         */
        public function subYears($numYears)
        {
            if (!is_numeric($numYears)) {
                throw new Exception('subYears() expects an integer');
            }
            $this->_year -= abs(intval($numYears));
            $this->checkLastDayOfMonth();
            parent::setDate($this->_year, $this->_month, $this->_day);
        }



        ##########################
        # PROTECTED/PRIVATE
        ##########################

        /**
         *
         */
        final protected function checkLastDayOfMonth()
        {
            if (!checkdate($this->_month, $this->_day, $this->_year)) {
                $use30 = array(4, 6, 9, 11);
                if (in_array($this->_month, $use30)) {
                    $this->_day = 30;
                } else {
                    $this->_day = $this->isLeap() ? 29 : 28;
                }
            }
        }


    }