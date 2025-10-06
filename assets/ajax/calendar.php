<?php
    include("../dbfuncs.php");
	//include("../pnl-calendar.php");

	$m = clean_int($_GET['m']);
	$y = clean_int($_GET['y']);

	$cal = drawCalendar($m,$y);

	echo $cal;


    use PeterBourneComms\CMS\Calendar;

    // CALENDAR DISPLAY
    function drawCalendar($month, $year)
    {
        //Determine next and previous months
        $nextyear = $year;
        $prevyear = $year;
        $nextmonth = $month + 1;
        $prevmonth = $month - 1;
        if ($nextmonth == 13) {
            $nextmonth = 1;
            $nextyear = $nextyear + 1;
        }
        if ($prevmonth == 0) {
            $prevmonth = 12;
            $prevyear = $prevyear - 1;
        }

        // Draw table for Calendar
        $calendar = "<table cellpadding='0' cellspacing='0' class='calendar'>";

        //Draw next and back options
        $calendar .= "<tr class='calendar-row'><td class='nav' onClick=\"loadXMLDoc('/assets/ajax/calendar.php?m=$prevmonth&y=$prevyear')\"><i class='fi-arrow-left'></i></td><td colspan='5' class='monthname'>" . date('F', mktime(0, 0, 0, $month, 1, $year)) . " " . $year . "</td><td class='nav' onClick=\"loadXMLDoc('/assets/ajax/calendar.php?m=$nextmonth&y=$nextyear')\"><i class='fi-arrow-right'></i></td></tr>";

        //Need to retrieve an array of important dates with their information for this month - from the TLDs table.
        //create date strings
        $lastday = date ('t',mktime(0, 0, 0, $month, 1));
        #Retrieve content
        $startdate = $year . "-" . $month . "-01";
        $enddate = $year . "-" . $month . "-".$lastday;

        $CO = new Calendar();
        $rows = $CO->getAllCalendarEntries($startdate, $enddate);

        if (count($rows) > 0) {
            //We have some dates
            $startts = strtotime($startdate);
            $endts = strtotime($enddate);
            $date_arr = array(); //Array of arrays, containing: array('date','status','domaintype','suffix','country','phase','start','end','deadline','launchdate','typeoflaunch')
            //Need to decide the type of date
            foreach ($rows as $row_d) {
                //Create a single number for our date - day of the month
                $calDate = date('j', strtotime($row_d['DateDisplay']));
                $date_arr[] = array('id' => $row_d['ID'], 'date' => $calDate, 'title' => $row_d['Title'], 'Content' => $row_d['Content'], 'URLText' => $row_d['URLText']);
            }

            //Now step back through our array - and start creating some tool tip divs for any days that appear.
            if (count($date_arr) > 0) {
                $datepresent = array(); //will be an array of arrays - so we'll use the date as the key - and store an array of 'id','html' against the date.
                foreach ($date_arr as $daterec) {

                    //Determine the link - or indeed, if one is needed
                    if ($daterec['Content'] != '') {
                        if ($daterec['URLText'] != '') {
                            $link = "/calendar/" . $daterec['URLText'];
                        } else {
                            $link = "/content/calendarview.php?id=" . $daterec['id'];
                        }
                    } else {
                        unset($link);
                    }

                    //Create the html for this item
                    $thishtml = "<h3";
                    if ($link != '') {
                        $thishtml .= " onclick=\"location.href='" . $link . "'\" style='cursor: pointer;'><i class='fi-info'></i>&nbsp;";
                    } else {
                        $thishtml .= ">";
                    }
                    $thishtml .= $daterec['title'];
                    $thishtml .= "</h3>";

                    /*
                     * //Determine the class to use
                    if ($daterec['Junior'] == 'Y') { $bg_class = "gala-type-junior"; }
                    if ($daterec['Senior'] == 'Y') { $bg_class = "gala-type-senior"; }
                    if ($daterec['Internal'] == 'Y') { $bg_class = "gala-type-internal"; }
                    if ($daterec['Open'] == 'Y') { $bg_class = "gala-type-open"; }
                    if ($daterec['Master'] == 'Y') { $bg_class = "gala-type-master"; }
                    if ($daterec['Other'] == 'Y') { $bg_class = "gala-type-other"; }
                     */

                    //Add this date to the datepresent array
                    if (!array_key_exists($daterec['date'], $datepresent)) {
                        //add it
                        $newdate = array('id' => $daterec['id'], 'html' => $thishtml, 'background-class' => $bg_class);
                        $datepresent[$daterec['date']] = $newdate;
                    } else {
                        //Load up old html - and add to it.
                        $oldhtml = $datepresent[$daterec['date']]['html'];
                        $newhtml = $oldhtml . $thishtml;
                        $datepresent[$daterec['date']]['html'] = $newhtml;
                    }
                }
                //print_r($datepresent);
            }
        } else {
            unset($date_arr);
        }
        //$calendar .= "number of entries: ".count($date_arr)."<br/>";
        //$calendar .= print_r($date_arr);


        // Draw Calendar table headings
        $headings = array('S', 'M', 'T', 'W', 'T', 'F', 'S');
        $calendar .= '<tr class="calendar-row"><td class="calendar-day-head">' . implode('</td><td class="calendar-day-head">', $headings) . '</td></tr>';

        //days and weeks variable for now ...
        $running_day = date('w', mktime(0, 0, 0, $month, 1, $year));
        $days_in_month = date('t', mktime(0, 0, 0, $month, 1, $year));
        $days_in_this_week = 1;
        $day_counter = 0;
        $dates_array = array();

        // row for week one
        $calendar .= '<tr class="calendar-row">';

        // Display "blank" days until the first of the current week
        for ($x = 0; $x < $running_day; $x++) {
            $calendar .= '<td class="calendar-day-np">&nbsp;</td>';
            $days_in_this_week++;
        }

        // Show days....
        for ($list_day = 1; $list_day <= $days_in_month; $list_day++) {
            if ($list_day == date('d') && $month == date('n')) {
                $currentday = 'currentday ';
            } else {
                $currentday = '';
            }

            /**
             * Add by Peter - need to decide if this item needs highlighting - by stepping through our date_arr and looking to see if the date fits
             * Does $list_day appear in our array?
             */
            if (isset($datepresent)) {
                if (array_key_exists($list_day, $datepresent)) {
                    $currentday .= 'calendar-day-item';

                    //Also retrieve the actual class required
                    $class_to_use = $datepresent[$list_day]['background-class'];
                    $currentday .= ' ' . $class_to_use;
                }
            }
            /**
             * End of addition
             */


            $calendar .= '<td class="calendar-day ' . $currentday . '"';
            /**
             * Add by Peter - need to decide if this item needs highlighting - by stepping through our date_arr and looking to see if the date fits
             * Does $list_day appear in our array?
             */
            if (isset($datepresent)) {
                if (array_key_exists($list_day, $datepresent)) {
                    $calendar .= ' data-id="' . $list_day . '"';
                    //$calendar .= " onclick='location.href='/content/calendarview.php?id=".$datepresent[$list_day]['id']."''";
                }
            }
            /**
             * End of addition
             */
            $calendar .= '>';

            /**
             * Add by Peter - display the pop up div within the TD (hidden at first of course)
             */
            if (isset($datepresent)) {
                if (array_key_exists($list_day, $datepresent)) {
                    $calendar .= "<div style='position: relative;'><div class='eventpopup' id='popup" . $list_day . "'>";
                    $calendar .= $datepresent[$list_day]['html'];
                    $calendar .= "</div></div>\n";
                }
            }
            /**
             * End of addition
             * /*/

            // Add in the day number
            if ($list_day < date('d') && $month == date('n')) {
                $showtoday = '<span class="overday">' . $list_day . '</span>';
            } else {
                $showtoday = $list_day;
            }
            $calendar .= '<div class="day-number">' . $showtoday . '</div>';

            // Draw table end
            $calendar .= '</td>';
            if ($running_day == 6) {
                $calendar .= '</tr>';
                if (($day_counter + 1) != $days_in_month):
                    $calendar .= '<tr class="calendar-row">';
                endif;
                $running_day = -1;
                $days_in_this_week = 0;
            } else {
                $days_in_this_week++;
            }
            $running_day++;
            $day_counter++;
        }

        // Finish the rest of the days in the week //(($day_counter < $days_in_month) || $running_day < 6)
        if ($days_in_this_week < 8 && $days_in_this_week > 0) {
            for ($x = 1; $x <= (7 - $days_in_this_week); $x++):
                //echo "days in this week = ".$days_in_this_week."; days in month = ".$days_in_month."; running day = ".$running_day."<br/>";
                $calendar .= '<td class="calendar-day-np">&nbsp;</td>';
            endfor;
        }

        // Draw table final row
        $calendar .= '</tr>';

        // Draw table end the table
        $calendar .= '</table>';

        $calendar .= "<p><a href='/content/calendar.php'>Click here for our full calendar &gt;</a></p>";

        // Finally all done, return result
        return $calendar;
    }