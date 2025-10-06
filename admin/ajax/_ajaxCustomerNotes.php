<?php
    /*
        This page should only ever be opened as part of an ajax request from the from the main member_details.php page

        If its opened any other way - it should fail gracefully
    */
    
    use PeterBourneComms\CCA\Note;

    if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
    {
        include("../../assets/dbfuncs.php");

        checkLoggedIn('FullAdmin', $_SERVER['PHP_SELF']);
    
        $id = $_POST["id"] ?? '';
        $mode = $_POST['mode'] ?? '';
        $note = $_POST['note'] ?? '';
        $parentid = $_POST['parentid'] ?? '';
        
        /*if (isset($_POST['m'])) {
            $m = $_POST['m']; //Mode of operation
        } else { $m = null; }*/
        
        //Prepare object
        $NO = new Note();
        
        if (!is_object($NO)) {
            $error = "NO OBJECTS";
        }

        //Mode options
        switch($mode) {
            case 'delete':
                if (clean_int($id) <= 0) {
                    $error = "No ID passed";
                    break;
                }
                $Note = $NO->getItemById($id);
                if (is_array($Note) && count($Note) > 0) {
                    $NO->deleteItem($id);
                    $newid = 0;
                } else {
                    $error = "Could not find note";
                }
                break;
                
            case 'add':
                if (clean_int($parentid) <= 0) {
                    $error = "No parent ID passed";
                    break;
                }
                if ($note == '') {
                    $error = "No note to save";
                    break;
                }
                $NO->createNewItem();
                $NO->setParentTable('Customers');
                $NO->setParentId($parentid);
                $NO->setContent($note);
                $NO->setNoteBy($_SESSION['UserDetails']['FullName']);
                $NO->setNoteById($_SESSION['UserDetails']['ID']);
                $result = $NO->saveItem();
                $newid = $NO->getId();
                
                if (!$result) {
                    $error = "Problem saving note";
                }
                break;
                
            default:
                $error = "No mode specified";
                break;
        }
        
        if (isset($error) && $error !== '') {
            $retdata = array('success' => false, 'err' => $error);
        } else {
            $retdata = array('success' => true, 'newid'=> $newid);
        }
    
        echo json_encode($retdata);
    }