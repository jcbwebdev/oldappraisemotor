<?php
    /*
        This page should only ever be opened as part of an ajax request from the from the main member_details.php page

        If its opened any other way - it should fail gracefully
    */
    
    use PeterBourneComms\CMS\Member;
    use PeterBourneComms\CMS\Note;
    //use PeterBourneComms\TheARR\Log;

    if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
    {
        include("../../assets/dbfuncs.php");

        checkLoggedIn('FullAdmin', $_SERVER['PHP_SELF']);
    
        $mode = $_POST['mode'];
        $MemberID = $_POST['MemberID'];
        $Note = $_POST['Note'];
        $NoteID = $_POST['NoteID'];
        
        if (clean_int($MemberID) <= 0) { die(); }
    
        //Create object
        $MO = new Member();
        $NO = new Note();
        if (!is_object($MO) || !is_object($NO)) {
            $error = "Could not create objects";
        } else {
            $Member = $MO->getItemById($MemberID);
            if (!is_array($Member)) {
                $error = "Could not find Member";
            } else {
                if ($mode == 'delete' && clean_int($NoteID) <= 0) {
                    $error = "No note specified";
                } elseif ($mode == 'delete') {
                    $Note = $NO->getNoteDetails($NoteID);
                    if (!is_array($Note)) {
                        $error = "Could not find note";
                    } else {
                        if (isset($MemberID) && $Note['MemberID'] != $MemberID) {
                            $error = "You cannot delete this note";
                        } else {
                            //continue
                        }
                    }
                }
            }
        }
        
        if ($error == '') {
            switch ($mode) {
                case 'delete':
                    $result = $NO->deleteNote($NoteID);
                    break;
                    
                case 'insert':
                    $NO->createNewNote();
                    $NO->setMemberID($MemberID);
                    $NO->setNote($Note);
                    $NO->setNoteBy($_SESSION['UserDetails']['FullName']);
                    $result = $NO->saveNote();
                    $NoteID = $NO->getID();
                    $thisnote = $NO->getNoteDetails($NoteID);
                    
                    if ($result == true) {
                        //Create return data
                        $output = "";
                        $output .= "<div class='quick-note-div' id='note" . $thisnote['ID'] . "' data-noteid='" . $thisnote['ID'] . "' data-memberid='" . $MemberID . "'><div class='note-note'>" . $thisnote['Note'] . "</div>";
                        $output .= "<div class='note-attrib'>By: <span class='note-by'>" . $thisnote['NoteBy'] . "</span><br/>";
                        $output .= "<span class='note-date'>" . format_date($thisnote['DateEdited']) . "</span></div>";
                        $output .= "<div class='note-del'><i class='fi-x note-del-link' data-noteid='" . $thisnote['ID'] . "'> </i></div>";
                        $output .= "</div>";
                    }
                    break;
            }
    
            if ($result != true) {
                $error = "There was a problem carrying out your request. Please try again.";
            }
        }
        
    
    
        if (isset($error) && $error !== '') {
            $retdata = array('err' => $error);
        } else {
            $retdata = array('success' => true, 'detail' => $output);
        }
        
        echo json_encode($retdata);
    
    }