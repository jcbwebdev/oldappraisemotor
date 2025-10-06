<?php
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    date_default_timezone_set('Europe/London');
    session_start();

    require_once(__DIR__.'/../inc/_config-cca-prod.php');
    require __DIR__ . '/../vendor/autoload.php';

    use PeterBourneComms\CMS\SiteSettings;

    //TODO: Remove this
    /*$_SESSION['UserDetails']['ID'] = 1;
    $_SESSION['UserDetails']['FullName'] = "Peter Bourne";
    $_SESSION['UserDetails']['Firstname'] = "Peter";
    $_SESSION['UserDetails']['Surname'] = "Bourne";
    $_SESSION['UserDetails']['Email'] = "hello@peterbourne.co.uk";
    $_SESSION['UserDetails']['Mobile'] = "07788743007";
    $_SESSION['UserDetails']['AdminLevel'] = "FullAdmin";
*/
    /*
     * SITE PERSONALISATION INFORMATION
     */

     if (!isset($_SESSION['SiteSettings']))
     {
         try {
             $SSO = new SiteSettings(1);
             $SS = $SSO->getItem();
         
             //VAT
             //$VAT = array('ENABLED'=>true,'MULTIPLIER'=>0.2,'RATE'=>'20%','CORRECTOR'=>1.2,'REGNUMBER'=>'247788843');
             //$SS['VAT'] = $VAT;
 
             $_SESSION['SiteSettings'] = $SS;
         } catch (Exception $e) {
             die('<h1>Database Error</h1><p><strong>Error:</strong> ' . $e->getMessage() . '</p><p><strong>File:</strong> ' . $e->getFile() . '</p><p><strong>Line:</strong> ' . $e->getLine() . '</p><hr><p>Please make sure you have imported the database. See instructions in the config file.</p>');
         }
         //TEMP:
         //TODO: Delete this - and uncomment above
         /*$_SESSION['SiteSettings']['Title'] = "Click Car Auction";
         $_SESSION['SiteSettings']['Telephone'] = "01283 123456";
         $_SESSION['SiteSettings']['Email'] = "info@clickcarauction.co.uk";
         $_SESSION['SiteSettings']['Strapline'] = "";
         $_SESSION['SiteSettings']['DefaultMetaDesc'] = "";
         $_SESSION['SiteSettings']['DefaultMetaKey'] = "";*/
     }

    $sitename = $_SESSION['SiteSettings']['Title'];
    $sitetitle = $_SESSION['SiteSettings']['Title'];

    $sitetelephone = $_SESSION['SiteSettings']['Telephone'];
    $siteemail = $_SESSION['SiteSettings']['Email'];
    $siteadministrator = $_SESSION['SiteSettings']['Email'];

    $sitedefaultstrapline = $_SESSION['SiteSettings']['Strapline'];


    //Other global variables
    $globalMetaDesc = $_SESSION['SiteSettings']['DefaultMetaDesc'];
    $globalMetaKey = $_SESSION['SiteSettings']['DefaultMetaKey'];




    //$globalTemplate = array(array('Value'=>'','Label'=>''),array('Value'=>'','Label'=>''),array('Value'=>'','Label'=>''),array('Value'=>'','Label'=>''),array('Value'=>'','Label'=>''),array('Value'=>'','Label'=>''),array('Value'=>'','Label'=>''),array('Value'=>'','Label'=>''),array('Value'=>'','Label'=>''),array('Value'=>'','Label'=>''),array('Value'=>'','Label'=>''),array('Value'=>'','Label'=>''),array('Value'=>'','Label'=>''));
    
    //$globalTitles = array(array('Value'=>'','Label'=>'Please select...'),array('Value'=>'Mr','Label'=>'Mr'),array('Value'=>'Mrs','Label'=>'Mrs'),array('Value'=>'Miss','Label'=>'Miss'),array('Value'=>'Ms','Label'=>'Ms'),array('Value'=>'Dr','Label'=>'Dr'),array('Value'=>'Professor','Label'=>'Professor'));
    
    //$globalPeopleSections = array(array('Value'=>'','Label'=>'Please select...'),array('Value'=>'Staff','Label'=>'Staff'),array('Value'=>'Governors','Label'=>'Governors'));
    
    //$globalSpecialContentAreas = array(array('Value'=>'---','Label'=>'---'),array('Value'=>'People - Staff','Label'=>'People - Staff'),array('Value'=>'People - Governors','Label'=>'People - Governors'),array('Value'=>'Policies','Label'=>'Policies'));
    
    //$globalGender = array(array('Value'=>'', 'Label'=>'Please select...'), array('Value'=>'Man', 'Label'=>'Man'), array('Value'=>'Woman', 'Label'=>'Woman'), array('Value'=>'Non-binary', 'Label'=>'Non-binary'), array('Value'=>'Prefer not to say', 'Label'=>'Prefer not to say'));
    

    
    //Settings
    //$globalOrganisationStatus = array(array('Value'=>'','Label'=>'Please select...'),array('Value'=>'Active','Label'=>'Active'),array('Value'=>'Disabled','Label'=>'Disabled'));
    //$globalStaffTypes = array(array('Value'=>'','Label'=>'Please select...'),array('Value'=>'Senior Leaders','Label'=>'Senior Leaders'), array('Value'=>'Teachers','Label'=>'Teachers'), array('Value'=>'Classroom Support','Label'=>'Classroom Support'), array('Value'=>'SEN Support','Label'=>'SEN Support'), array('Value'=>'Admin Team','Label'=>'Admin Team'), array('Value'=>'Other Support','Label'=>'Other Support'));
    //$globalGovernorTypes = array(array('Value'=>'','Label'=>'Please select...'),array('Value'=>'Parent','Label'=>'Parent'), array('Value'=>'Co-opted','Label'=>'Co-opted'), array('Value'=>'Staff','Label'=>'Staff'));
    
    
    //$globalUserStatus = array(array('Value'=>'','Label'=>'Please select...'),array('Value'=>'Active','Label'=>'Active'),array('Value'=>'Disabled','Label'=>'Disabled'));
    $globalAdminLevel = array(array('Value'=>'','Label'=>'None'),array('Value'=>'F','Label'=>'Full administrator'));
    
    
    ############################################################################
    # No more changes after this point
    ############################################################################




    /*
     * FUNCTIONS TO ****DEFINITELY**** RETAIN
     */
    function clean_int( $i )
    {
        if ( is_numeric( $i ) )
        {
            return ( int ) $i;
        }
        // return False if we don't get a number
        else
        {
            return false;
        }
    }


    function check_output($value)
    {
        // Encode HTML entities to prevent XSS
        $value = htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
        return $value;
    }
    
    function FixOutput($str)
    {
        $search = array(chr(145),
            chr(146),
            chr(147),
            chr(148),
            chr(151),
            chr(255),
            '’',
            "‘",
            '£',
            ' ',
            '&nbsp;');
        
        $replace = array("'",
            "'",
            '"',
            '"',
            '-',
            "'",
            "'",
            "'",
            "&pound;",
            ' ',
            ' ');
        
        return str_replace($search, $replace, $str);
    }

    
    function checkLoggedIn($passedLevel = 'User', $prevurl = '')
    {
        //Already authenticated?
        if (!isset($_SESSION['UserDetails'])) {
            $retval = false;
        } elseif ($_SESSION['UserDetails']['Authenticated'] !== true) {
            $retval = false;
        } else {
            if ($passedLevel == "FullAdmin") {
                if ($_SESSION['UserDetails']['AdminLevel'] === "F") {
                    $retval = true;
                }
            } elseif ($passedLevel == 'User') {
                if ($_SESSION['UserDetails']['AdminLevel'] === "F" || $_SESSION['UserDetails']['AdminLevel'] === "User") {
                    $retval = true;
                }
            } else {
                $retval = false;
            }
        }
        
        if ($retval == true) {
            return $retval;
        } else {
            $_SESSION['Message'] = array('Type'=>'alert','Message'=>"You need a higher level of access for this area.");
            header("Location: /login/?referrer=$prevurl");
            exit;
        }
    }
    

    function isValidSubmission()
    {
        try {

            $url = 'https://www.google.com/recaptcha/api/siteverify';
            $data = ['secret'   => $_SESSION['SiteSettings']['G_RecaptchaSecret'],
                     'response' => $_POST['g-recaptcha-response'],
                     'remoteip' => $_SERVER['REMOTE_ADDR']];

            $options = [
                'http' => [
                    'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method'  => 'POST',
                    'content' => http_build_query($data)
                ]
            ];

            $context  = stream_context_create($options);
            $result = file_get_contents($url, false, $context);
            return json_decode($result)->success;
        }
        catch (Exception $e) {
            return null;
        }
    }



    function in_array_r($needle, $haystack, $strict = false) 
    {
        foreach ($haystack as $item) {
            if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && in_array_r($needle, $item, $strict))) {
                return true;
            }
        }

        return false;
    }

    function checkEnteredDate( $passedDate )
    {
        $exploded_date = explode('/', $passedDate);
        $date = $exploded_date[0];
        $month = $exploded_date[1];
        $year = $exploded_date[2];
        //Check year is 4 digits
        if (strlen($year) != 4)
        {
            $retval = false;
        }
        else
        {
            $retval = checkdate($month, $date, $year);
        }
        return $retval;
    }

    function convert_jquery_date( $dateIn )
    {
        //Converts the jquery date provided (dd/mm/YYYY) into a date that can be stored in mySQL
        if ($dateIn != '' && $dateIn != '00/00/0000')
        {
            $datearr = explode('/',$dateIn);
            $formatted_date = $datearr[2].'-'.$datearr['1'].'-'.$datearr[0];
        }
        return $formatted_date ?? null;
    }
    function format_jquery_date( $dateIn )
    {
        //Converts the MySQL date that is passed into a timestamp, and then returns a formatted date
        if ($dateIn != '0000-00-00 00:00:00' && $dateIn != '0000-00-00' && $dateIn != '') { $formatted_date = date("d/m/Y",strtotime($dateIn)); }
        return $formatted_date ?? null;
    }

    function format_RM_date( $dateIn )
    {
        //Converts the MySQL date that is passed into a timestamp, and then returns a formatted date
        if ($dateIn != '0000-00-00 00:00:00' && $dateIn != '0000-00-00' && $dateIn != '') { $formatted_date = date("d-m-Y H:i:s",strtotime($dateIn)); }
        return $formatted_date ?? null;
    }

    function format_date( $dateIn )
    {
        //Converts the MySQL date that is passed into a timestamp, and then returns a formatted date
        if ($dateIn != '0000-00-00 00:00:00' && $dateIn != '0000-00-00' && $dateIn != '') { $formatted_date = date("d M Y",strtotime($dateIn)); }
        return $formatted_date ?? null;
    }

    function format_prettydate( $dateIn )
    {
        //Converts the MySQL date that is passed into a timestamp, and then returns a formatted date
        if ($dateIn != '0000-00-00 00:00:00' && $dateIn != '0000-00-00' && $dateIn != '') { $formatted_date = date("d F Y",strtotime($dateIn)); }
        return $formatted_date ?? null;
    }

    function format_newsdate( $dateIn )
    {
        //Converts the MySQL date that is passed into a timestamp, and then returns a formatted date
        if ($dateIn != '0000-00-00 00:00:00' && $dateIn != '0000-00-00' && $dateIn != '') { $formatted_date = date("F Y",strtotime($dateIn)); }
        return $formatted_date ?? null;
    }

    function format_datetime( $dateIn )
    {
        //Converts the MySQL date that is passed into a timestamp, and then returns a formatted date
        if ($dateIn != '0000-00-00 00:00:00' && $dateIn != '0000-00-00' && $dateIn != '') { $formatted_date = date("d M Y H:i",strtotime($dateIn)); }
        return $formatted_date ?? null;
    }

    function format_shortdate( $dateIn )
    {
        //Converts the MySQL date that is passed into a timestamp, and then returns a formatted date
        if ($dateIn != '0000-00-00 00:00:00' && $dateIn != '0000-00-00' && $dateIn != '') { $formatted_date = date("d.m.Y",strtotime($dateIn)); }
        return $formatted_date ?? null;
    }

    function format_longdate( $dateIn )
    {
        //Converts the MySQL date that is passed into a timestamp, and then returns a formatted date
        if ($dateIn != '0000-00-00 00:00:00' && $dateIn != '0000-00-00' && $dateIn != '') { $formatted_date = date("jS F Y",strtotime($dateIn)); }
        return $formatted_date ?? null;
    }

    function format_time( $timeIn )
    {
        //Converts the MySQL time that is passed into a timestamp, and then returns a formatted time
        if ($timeIn != '')
        {
            $formatted_time = date("H:i",strtotime($timeIn));
            return $formatted_time ?? null;
        }
        else
        {
            return false;
        }
    }
    
    function format_prettytime( $timeIn )
    {
        if ($timeIn != '') {
            $time_arr = explode(':', $timeIn);
            if ($time_arr[1] == '00' || $time_arr[1] == '0') {
                $formatted_time = date("ga",strtotime($timeIn));
            } else {
                $formatted_time = date("g:ia",strtotime($timeIn));
            }
            return $formatted_time ?? null;
        } else {
            return false;
        }
    }

    function convert_datetime($str)
    {

        list($date, $time) = explode(' ', $str);
        list($year, $month, $day) = explode('-', $date);
        if ($time == '' || $time == ' ') { $time = '00:00:00'; }
        list($hour, $minute, $second) = explode(':', $time);

        $timestamp = mktime($hour, $minute, $second, $month, $day, $year);

        return $timestamp;
    }

    function ValidEmail($email)
    {
        if ( filter_var( $email, FILTER_VALIDATE_EMAIL ))
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    function pb_xml_encode($value)
    {
        $value = check_output($value);
        $value = str_replace('&amp;', 'and', $value);

        return $value;
    }


    function chooseIcon($filetype)
    {
        if ($filetype == 'application/pdf') { return "icon_acrobat.jpg"; }
        elseif ($filetype == '"application/pdf"') { return "icon_acrobat.jpg"; }
        else if ($filetype == 'application/msword' || $filetype == 'application/vnd.openxmlformats-officedocument.wordprocessingml.document') { return "icon_word.png"; }
        else if ($filetype == 'application/vnd.ms-excel') { return "icon_excel.png"; }
        else if ($filetype == 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') { return "icon_excel.png"; }
        else if ($filetype == 'application/vnd.ms-powerpoint') { return "icon_powerpoint.png"; }
    }

    function getAlt($filetype)
    {
        if ($filetype == 'application/pdf') { return "Adobe Acrobat Document"; }
        else if ($filetype == 'application/msword' || $filetype == 'application/vnd.openxmlformats-officedocument.wordprocessingml.document') { return "Microsoft Word Document"; }
        else if ($filetype == 'application/vnd.ms-excel') { return "Microsoft Excel Document"; }
        else if ($filetype == 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') { return "Microsoft Excel Document"; }
        else if ($filetype == 'application/vnd.ms-powerpoint') { return "Microsoft PowerPoint Document"; }
        else if ($filetype == 'image/pjpeg') { return "JPEG Image"; }
    }


    function ConvertURLText($passedURLText)
    {
        //Function to strip spaces and any other characters we don't want from the passed text - replace spaces with a -
        $newURL = strtolower(preg_replace('/[^\w]+/', '-', $passedURLText));

        return $newURL;
    }


    function addHttp($passedURL)
    {
        if ($passedURL !='' && $passedURL != 'http:///' && $passedURL != 'http://')
        {
            $url_array = parse_url($passedURL);
            if ($url_array['scheme'] != 'http' || $url_array['scheme'] != 'https')
            {
                $url_array['scheme'] = 'https';
            }

            return $url_array['scheme'] . "://" . $url_array['host'] . $url_array['path'];
        }
        else
        {
            return false;
        }
    }




    /**
     * ### getBaseUrl function
     * // utility function that returns base url for
     * // determining return/cancel urls
     * @return string
     */
    function getBaseUrl() 
    {

        $protocol = 'http';
        if ($_SERVER['SERVER_PORT'] == 443 || (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) == 'on')) {
            $protocol .= 's';
            $protocol_port = $_SERVER['SERVER_PORT'];
        } else {
            $protocol_port = 80;
        }

        $host = $_SERVER['HTTP_HOST'];
        $port = $_SERVER['SERVER_PORT'];
        $request = $_SERVER['PHP_SELF'];
        return dirname($protocol . '://' . $host . ($port == $protocol_port ? '' : ':' . $port) . $request);
    }

    //******************************************************
    //
    // FILE FUNCTIONS
    //
    function checkFiletype($filetype)
    {
        //strip quote marks
        $filetype = str_replace("\"","",$filetype);


        if ($filetype == 'application/pdf' ||
            $filetype == 'application/msword' ||
            $filetype == 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' ||
            $filetype == 'application/vnd.ms-excel' ||
            $filetype == 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' ||
            $filetype == 'application/vnd.ms-powerpoint' ||
            $filetype == 'application/vnd.openxmlformats-officedocument.presentationml.presentation' ||
            $filetype == 'image/jpeg' || $filetype == 'image/pjpeg')
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    function AcceptedFileType($FileType)
    {
        if ( $FileType == 'image/pjpeg' || $FileType == 'image/jpeg' || $FileType == 'image/png')
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    
    function AcceptedFileDownloadType($FileType)
    {
        if ( $FileType == 'application/pdf' || $FileType == '"application/pdf"' || $FileType == 'application/msword' || $FileType == 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' || $FileType == 'application/vnd.ms-excel' || $FileType == 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' || $FileType == 'application/vnd.ms-powerpoint' || $FileType == 'image/pjpeg' || $FileType == 'image/jpeg' || $FileType == 'image/png' )
        {
            return true;
        }
        else
        {
            return false;
        }
    }

    function CheckDimension($File)
    {
        if ($File != '')
        {
            $im_details = getimagesize($File);
            if ($im_details[0] >= 9000 || $im_details[1] >= 9000) { return false; } else { return true; }
        }
    }