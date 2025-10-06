<?php
    include("../assets/dbfuncs.php");
    
    use PeterBourneComms\CCA\Vehicle;

    //Deal with any referrer information
    $VOmpany = $_POST['Company'] ?? null;
    $CustomerID = $_POST['CustomerID'] ?? null;
    $Reg = $_POST['Reg'] ?? null;
    
    
    $_SESSION['PostedForm']     = $_POST;
    
    $VO = new Vehicle();
    
    if (!is_object($VO)) {
        die();
    }
    
    //Check that some data was passed
    $tempObj = new Vehicle();
    $duplicate_reg = $tempObj->checkDuplicateReg($Reg);
    
    if (!is_numeric($CustomerID) || $CustomerID <= 0 || $Reg  == '' || $duplicate_reg === true) {
        if ( !is_numeric($CustomerID) || $CustomerID <= 0 ) { $_SESSION['companyerror'] = "<p class='error'>Please enter the company name:</p>"; }
        if ($Reg == '' ) { $_SESSION['regerror'] = "<p class='error'>Please enter the vehicle registration details:</p>"; }
        if ($duplicate_reg === true ) { $_SESSION['regerror'] = "<p class='error'>We already have a record for this vehicle. Please edit that or use a different VRM:</p>"; }
        
        $_SESSION['error'] = true;
        $_SESSION['Message'] = array('Type'=>'alert','Message'=>"Please check the details - we need the following information:");
        header("Location:./vehicle_new.php");
        exit;
    }
    
    
    //Store Customer details
    $VO->createNewItem();
    
    $VO->setCustomerId($CustomerID);
    $VO->setReg($Reg);
    $VO->saveItem();
    
    $newID = $VO->getId();
    
    //NOW LOOKUP ADDITIONAL DATA
    
    
    /**
     * Data to pass
     *
     * vrm=M1CDL
     * &mileage=
     * &feedName=YOUR_FEED
     * &versionNumber=1
     * &userName=YOUR_USERNAME
     * &password=3429^mtH%22Ibnz%GXGv8J1vC3EDA$3a3@H_89HB0U%Web%22
     *
     */
    $url = "https://jarvis.prod.cdlcloud.co.uk/jarvis-webapp/search";
    $post = array();
    $post['vrm'] = str_replace(' ', '', $Reg); //Need to strip spaces
    $post['feedName'] = "BUYINGBUDDY2";
    //$post['versionNumber'] = '1';
    $post['userName'] = CDL_USERNAME;
    $post['password'] = CDL_PASSWORD;
    
    $data = http_build_query($post);
    
    //Now post the data and receive the feed
    //Start Curl
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_VERBOSE, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "Content-Type: application/x-www-form-urlencoded"
    ));
    
    $curlResponse = curl_exec($ch);
    curl_close($ch);
    
    /**
     * OPTION 2 - FROM HARRY
     */
    /*$xmlData = file_get_contents('https://jarvis.cdlis.co.uk/jarvis-webapp/search?feedName=BUYINGBUDDY2&versionNumber=1&userName=BUYINGBUDDY2&password=%5ej3gSFghs7YQkA23UE7xW3yrO(i,VpK@;%3C2w$QE7&vrm='.$Reg);
    
    $xml = simplexml_load_string($xmlData);
    $array = json_decode(json_encode($xml), true);
    */
    /**
     * END OF OPTION 2
     */
    
    //print_r($post);
    //print_r($array);
    /**
     * Example result:
     *
     * Array ( [dvla] => Array ( [keeper] => Array ( [previous_keepers] => 0 ) [vehicle] => Array ( [vrm] => FG23XLD [make] => SKODA [model] => ENYAQ IV 80X SPORTLINE [manufactured_date] => 2023-06-29 [registration_date] => 2023-06-29 [colour] => SILVER [cc] => 0 [fuel_type] => ELECTRICITY [cdl_id] => 2366237770 ) ) [mvris] => Array ( [mvris_record] => Array ( [first_reg_date] => 2023-06-29 [cc] => Array ( ) [bhp_count] => 261.5 [model_variant_name] => 80X SPORTLINE [door_count] => 5 [transmission] => AUTOMATIC [make] => SKODA [model] => ENYAQ IV [fuel_type] => ELECTRIC ) ) )
     * */

    //Decode the data and store in a vehicle record
    error_log($curlResponse);
    $VehicleData = simplexml_load_string($curlResponse);
    
    $VO->setMake($VehicleData->dvla->vehicle->make);
    $VO->setModel($VehicleData->dvla->vehicle->model);
    $VO->setDateOfFirstReg($VehicleData->dvla->vehicle->registration_date);
    $VO->setManufacturerColour($VehicleData->dvla->vehicle->colour);
    $VO->setEngineSize($VehicleData->dvla->vehicle->cc);
    $VO->setNoOfOwners($VehicleData->dvla->keeper->previous_keepers);
    $VO->setNoOfDoors($VehicleData->mvris->mvris_record->door_count);
    $VO->setFuel($VehicleData->mvris->mvris_record->fuel_type);
    $VO->setTransmission($VehicleData->mvris->mvris_record->transmission);
    $result1 = $VO->saveItem();
    
    
    if ($result1 != true) {
        $error = true;
    }
    
    if ($error === true) {
        $_SESSION['Message'] = array('Type'=>'alert','Message'=>"Sorry - there was a problem updating the database. Please try again.");
        header("Location: ./vehicle_new.php");
        exit;
    } else {
        unset($_SESSION['PostedForm']);
        //Move onto password screen
        header("Location:./vehicle_edit.php?id=".$newID);
        exit;
    }