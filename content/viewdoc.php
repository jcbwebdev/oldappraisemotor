<?php
    include("../assets/dbfuncs.php");
    ini_set("memory_limit", "20M");
    
    use PeterBourneComms\CMS\FileLibrary;
    
    if (isset($_GET['id'])) {
        $id = clean_int($_GET['id']);
    } else { $id = null; }
    
    if (isset($_GET['contentid'])) {
        $contentid = clean_int($_GET['contentid']);
    } else { $contentid = null; }
    
    if (isset($_GET['contenttype'])) {
        $contenttype = $_GET['contenttype'];
    } else { $contenttype = null; }
    
    switch ($contenttype) {
        /*case 'govdocs':
            checkAdmin($_SERVER['PHP_SELF'], 'T');
            $table = "GovDocs";
            break;
        case 'sltdocs':
            checkAdmin($_SERVER['PHP_SELF'], 'SLT');
            $table = "SLTDocs";
            break;*/
        case 'policies':
            $table = "Policies";
            break;
        default:
            die();
    }
    
    if ($id <= 0 || $contentid <= 0) {
        die();
    }
    
    
    //Retrieve te document
    try {
        $FO = new FileLibrary($table, $contentid, $id);
    } catch (Exception $e) {
        error_log($e);
        die();
    }
    
    $File = $FO->getFileById($id);
    
    //Should be good to show the doc
    if ($File['ID'] >= 1) {
        
        header("Content-type: " . $File['Filetype']);
        header('Content-Disposition: inline; filename="' . $File['Filename'] . '"');
        header("Content-Description: PHP Generated Data");
        
        //To deal with older documents (not UTF8 encoded) - we need a clumsy DB hack:
        //echo "here we go";
        //echo utf8_decode($File['Fileblob']);
        echo $File['Fileblob'];
    } else {
        echo "oh dear";
    }
