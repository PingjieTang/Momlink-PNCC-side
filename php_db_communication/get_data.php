<?php
ini_set('display_errors', '1');
error_reporting(E_ALL);

//connect database
function db_connect() {
    $_myserver = "localhost";
    $_user = "root";
    $_password = "MomLink-Root";
    $_database = "momlink";
    if (!mysql_connect($_myserver, $_user, $_password)) {
        echo"Could not connect";
    } else {
          mysql_select_db($_database);
      }
 }

// array for JSON response
$response = array();

// check for post data
if(isset($_REQUEST)) {
    db_connect();
    header('Content-Type: text/javascript');
    //Get data from app to server 
    $data = json_decode(file_get_contents('php://input'), true);
    $uName = $data['pncc-name'];
    $uPsw = $data['psw'];  
    //query the clients list corresponding to each pncc  
    $query_pnccid = "SELECT id FROM pncc WHERE username = '".$uName."' AND password = MD5('".$uPsw."')";
    $pnccid = mysql_fetch_array(mysql_query($query_pnccid), MYSQL_BOTH);
    $query_clients = "SELECT id FROM client WHERE pncc_id = '".$pnccid['id']."'";
    $client_id = mysql_query($query_clients);
    $response[$pnccid['id']] = array();
    
    while($client_id_array=mysql_fetch_array($client_id)) {//For each client corresponding to a pnccid
       //print_r($client_id_array);
       $response[$pnccid['id']][$client_id_array['id']] = array();
       $query_address = "SELECT address FROM contact WHERE client_id='".$client_id_array['id']."'";
       $client_address = mysql_query($query_address);
       $client_address_result = mysql_fetch_array($client_address);
       $response[$pnccid['id']][$client_id_array['id']]['address'] = $client_address_result['address'];
       $query_dob = "SELECT dob FROM client WHERE id='".$client_id_array['id']."'";
       $dob = mysql_query($query_dob);
       $dob_result = mysql_fetch_array($dob);
       $response[$pnccid['id']][$client_id_array['id']]['dob'] = $dob_result['dob'];
       //echo "birthday: ".$response[$pnccid['id']][$client_id_array['id']]['dob']."\n";
       $response[$pnccid['id']][$client_id_array['id']]['trimester'] = array();
       $response[$pnccid['id']][$client_id_array['id']]['regular'] = array();
       $query_assessmentId = "SELECT assessment_id FROM encounters WHERE client_id ='".$client_id_array['id']."'";
       $assessment_id = mysql_query($query_assessmentId);
       while($assessment_id_array=mysql_fetch_array($assessment_id)) {
           //print_r($assessment_id_array);
           //Deal with a trimester visit, the assessment_id for a trimester visit could be only out of 1, 2 and 3.
           if ($assessment_id_array['assessment_id'] != 0) {
               $query_trimester_date = "SELECT encounter_date FROM encounters WHERE client_id='".$client_id_array['id']."' AND assessment_id='".$assessment_id_array['assessment_id']."'";
               $trimester_date = mysql_query($query_trimester_date);
               $tri_date_array = mysql_fetch_array($trimester_date);
               $tri_date = $tri_date_array['encounter_date'];
               $response[$pnccid['id']][$client_id_array['id']]['trimester'][$tri_date] = array();
               //print_r($response[$pnccid['id']][$client_id_array['id']]['trimester'])."\n";
               $query_name = "SELECT GROUP_CONCAT(firstname,' ',middlename,' ',lastname) AS concat_name FROM client WHERE id = '".$client_id_array['id']."'";
               $name = mysql_query($query_name);
               $name_result = mysql_fetch_array($name);
               $response[$pnccid['id']][$client_id_array['id']]['name'] = $name_result['concat_name'];
               $query_prepregnancy_pounds = "SELECT weight_pounds FROM medical WHERE client_id='".$client_id_array['id']."'";
               $query_prepregnancy_ounces = "SELECT weight_ounces FROM medical WHERE client_id='".$client_id_array['id']."'";
               $prepregnancy_pounds = mysql_query($query_prepregnancy_pounds);
               $prepregnancy_ounces = mysql_query($query_prepregnancy_ounces);
               $prepregnancy_pounds_result = mysql_fetch_array($prepregnancy_pounds);
               $prepregnancy_ounces_result = mysql_fetch_array($prepregnancy_ounces);
       	       $response[$pnccid['id']][$client_id_array['id']]['trimester'][$tri_date]['pre_weight_lb'] = $prepregnancy_pounds_result['weight_pounds'];
               $response[$pnccid['id']][$client_id_array['id']]['trimester'][$tri_date]['pre_weight_oz'] = $prepregnancy_ounces_result['weight_ounces'];
               $query_bmi = "SELECT pre_bmi FROM medical WHERE client_id='".$client_id_array['id']."'";
               $bmi = mysql_query($query_bmi);
               $bmi_result = mysql_fetch_array($bmi);
               $response[$pnccid['id']][$client_id_array['id']]['trimester'][$tri_date]['prepregnancy_bmi'] = $bmi_result['pre_bmi'];
               //echo $response[$pnccid['id']][$client_id_array['id']]['trimester'][$tri_date]['prepregnancy_bmi']."\n";
               $query_current_pounds = "SELECT weight_pounds FROM weight WHERE weight_code = (SELECT weight_code FROM encounters WHERE client_id=".$client_id_array['id']." AND assessment_id=".$assessment_id_array['assessment_id'].")";
               $current_pounds = mysql_query($query_current_pounds);
               $current_pounds_result = mysql_fetch_array($current_pounds);
               $response[$pnccid['id']][$client_id_array['id']]['trimester'][$tri_date]['weight_pounds'] = $current_pounds_result['weight_pounds'];
               $query_current_ounces = "SELECT weight_ounces FROM weight WHERE weight_code = (SELECT weight_code FROM encounters WHERE client_id=".$client_id_array['id']." AND assessment_id=".$assessment_id_array['assessment_id'].")";
               $current_ounces = mysql_query($query_current_ounces);
               $current_ounces_result = mysql_fetch_array($current_ounces);
               $response[$pnccid['id']][$client_id_array['id']]['trimester'][$tri_date]['weight_ounces'] = $current_ounces_result['weight_ounces'];
                         	   
               $query_appt_date = "SELECT next_obvisit_date FROM pregnancy WHERE pregnancy_code = (SELECT pregnancy_code FROM encounters WHERE client_id=".$client_id_array['id']." AND assessment_id=".$assessment_id_array['assessment_id'].")";
               $appt_date = mysql_query($query_appt_date);
               $appt_date_result = mysql_fetch_array($appt_date);
               $response[$pnccid['id']][$client_id_array['id']]['trimester'][$tri_date]['next_appt_date'] = $appt_date_result['next_obvisit_date'];
               $query_provider = "SELECT provider FROM pregnancy WHERE pregnancy_code = (SELECT pregnancy_code FROM encounters WHERE client_id=".$client_id_array['id']." AND assessment_id=".$assessment_id_array['assessment_id'].")";
               $provider = mysql_query($query_provider);
               $provider_result = mysql_fetch_array($provider);
               $response[$pnccid['id']][$client_id_array['id']]['trimester'][$tri_date]['med_provider'] = $provider_result['provider'];
               $query_have_insurance = "SELECT insurance FROM pregnancy WHERE pregnancy_code = (SELECT pregnancy_code FROM encounters     WHERE client_id=".$client_id_array['id']." AND assessment_id=".$assessment_id_array['assessment_id'].")";
               $have_insurance = mysql_query($query_have_insurance);
               $have_insurance_result = mysql_fetch_array($have_insurance);
               $response[$pnccid['id']][$client_id_array['id']]['trimester'][$tri_date]['insurance'] = $have_insurance_result['insurance'];
               $query_insurance_name = "SELECT insurance_name FROM pregnancy WHERE pregnancy_code = (SELECT pregnancy_code FROM encounters     WHERE client_id=".$client_id_array['id']." AND assessment_id=".$assessment_id_array['assessment_id'].")";
               $insurance_name = mysql_query($query_insurance_name);
               $insurance_name_result = mysql_fetch_array($insurance_name);
               $response[$pnccid['id']][$client_id_array['id']]['trimester'][$tri_date]['insurance_name'] = $insurance_name_result['insurance_name'];
               $query_pkg_select = "SELECT medicaid_type FROM pregnancy WHERE pregnancy_code = (SELECT pregnancy_code FROM encounters     WHERE client_id=".$client_id_array['id']." AND assessment_id=".$assessment_id_array['assessment_id'].")";
               $pkg_select = mysql_query($query_pkg_select);
               $pkg_select_result = mysql_fetch_array($pkg_select);
               $response[$pnccid['id']][$client_id_array['id']]['trimester'][$tri_date]['package_e'] = $pkg_select_result['medicaid_type'];
               $query_medicate_qualify = "SELECT apply_type FROM pregnancy WHERE pregnancy_code = (SELECT pregnancy_code FROM encounters     WHERE client_id=".$client_id_array['id']." AND assessment_id=".$assessment_id_array['assessment_id'].")";
               $medicate_qualify = mysql_query($query_medicate_qualify);
               $medicate_qualify_result = mysql_fetch_array($medicate_qualify);
               $response[$pnccid['id']][$client_id_array['id']]['trimester'][$tri_date]['medicate_qual_select'] = $medicate_qualify_result['apply_type'];
               $query_noqualify_reason = "SELECT reason_noqualify FROM pregnancy WHERE pregnancy_code = (SELECT pregnancy_code FROM encounters     WHERE client_id=".$client_id_array['id']." AND assessment_id=".$assessment_id_array['assessment_id'].")";       
               $noqualify_reason = mysql_query($query_noqualify_reason);
               $noqualify_reason_result = mysql_fetch_array($noqualify_reason);
               $response[$pnccid['id']][$client_id_array['id']]['trimester'][$tri_date]['reason_noqualify'] = $noqualify_reason_result['reason_noqualify'];
 
               $query_material_code = "SELECT material_code FROM encounters WHERE client_id=".$client_id_array['id']." AND assessment_id=".$assessment_id_array['assessment_id'];
               $material_code = mysql_query($query_material_code);
               $material_code_result = mysql_fetch_array($material_code);
               $query_homeless = "SELECT homeless_current FROM material_needs WHERE material_code = ".$material_code_result['material_code'];
               $homeless = mysql_query($query_homeless);
               $homeless_result = mysql_fetch_array($homeless);
               $response[$pnccid['id']][$client_id_array['id']]['trimester'][$tri_date]['residence'] = $homeless_result['homeless_current'];
               $query_transportation = "SELECT transportation FROM material_needs WHERE material_code = ".$material_code_result['material_code'];
               $transportation = mysql_query($query_transportation);
               $transportation_result = mysql_fetch_array($transportation);
               $response[$pnccid['id']][$client_id_array['id']]['trimester'][$tri_date]['transportation'] = $transportation_result['transportation'];
               $query_adequate_food = "SELECT food FROM material_needs WHERE material_code = ".$material_code_result['material_code'];
               $adequate_food = mysql_query($query_adequate_food);
               $adequate_food_result = mysql_fetch_array($adequate_food);
               $response[$pnccid['id']][$client_id_array['id']]['trimester'][$tri_date]['adequate_food'] = $adequate_food_result['food'];
               $query_fob = "SELECT fob from client WHERE id = '".$client_id_array['id']."'";
               $fob = mysql_query($query_fob);
               $fob_result = mysql_fetch_array($fob);
               $response[$pnccid['id']][$client_id_array['id']]['trimester'][$tri_date]['fob']=$fob_result['fob'];
              $query_conception_date = "SELECT edcpt FROM medical WHERE client_id = '".$client_id_array['id']."'";
               $conception_date = mysql_query($query_conception_date);
               $conception_date_result = mysql_fetch_array($conception_date);
               $response[$pnccid['id']][$client_id_array['id']]['trimester'][$tri_date]['conception_date'] = $conception_date_result['edcpt'];
               $query_delivery_date = "SELECT edc FROM medical WHERE client_id = '".$client_id_array['id']."'";
               $delivery_date = mysql_query($query_delivery_date);
               $delivery_date_result = mysql_fetch_array($delivery_date);
               $response[$pnccid['id']][$client_id_array['id']]['trimester'][$tri_date]['delivery_date'] = $delivery_date_result['edc'];
               $query_wic = "SELECT wic_vouchers FROM eat_behavior WHERE behavior_code = (SELECT behavior_code FROM encounters WHERE client_id=".$client_id_array['id']." AND assessment_id=".$assessment_id_array['assessment_id'].")";
               $wic = mysql_query($query_wic);
               $wic_result = mysql_fetch_array($wic);
               $response[$pnccid['id']][$client_id_array['id']]['trimester'][$tri_date]['wic_vouchers'] = $wic_result['wic_vouchers'];
               $query_when_appt = "SELECT wic_appt FROM eat_behavior WHERE behavior_code = (SELECT behavior_code FROM encounters WHERE client_id=".$client_id_array['id']." AND assessment_id=".$assessment_id_array['assessment_id'].")";
               $when_appt = mysql_query($query_when_appt);
               $when_appt_result = mysql_fetch_array($when_appt);
               $response[$pnccid['id']][$client_id_array['id']]['trimester'][$tri_date]['wic_appt'] = $when_appt_result['wic_appt'];
               $query_pnv = "SELECT pnv FROM eat_behavior WHERE behavior_code = (SELECT behavior_code FROM encounters WHERE client_id=".$client_id_array['id']." AND assessment_id=".$assessment_id_array['assessment_id'].")";
               $pnv = mysql_query($query_pnv);
               $pnv_result = mysql_fetch_array($pnv);
               $response[$pnccid['id']][$client_id_array['id']]['trimester'][$tri_date]['pnv'] = $pnv_result['pnv'];
           	  //echo $response[$pnccid['id']][$client_id_array['id']]['trimester'][$tri_date]['pnv']."\n";  
               $query_food_stamps = "SELECT food_stamps FROM eat_behavior WHERE behavior_code = (SELECT behavior_code FROM encounters WHERE client_id=".$client_id_array['id']." AND assessment_id=".$assessment_id_array['assessment_id'].")";
               $food_stamps = mysql_query($query_food_stamps);
               $food_stamps_result = mysql_fetch_array($food_stamps);
               $response[$pnccid['id']][$client_id_array['id']]['trimester'][$tri_date]['food_stamps'] = $food_stamps_result['food_stamps'];
           	  //echo $response[$pnccid['id']][$client_id_array['id']]['trimester'][$tri_date]['food_stamps'];      
               $query_tanf = "SELECT tanf FROM eat_behavior WHERE behavior_code = (SELECT behavior_code FROM encounters WHERE client_id=".$client_id_array['id']." AND assessment_id=".$assessment_id_array['assessment_id'].")";
               $tanf = mysql_query($query_tanf);
               $tanf_result = mysql_fetch_array($tanf);
               $response[$pnccid['id']][$client_id_array['id']]['trimester'][$tri_date]['tanf'] = $tanf_result['tanf'];
           	  //echo $response[$pnccid['id']][$client_id_array['id']]['trimester'][$tri_date]['tanf']."\n";
               $query_pnv_problems = "SELECT pnv_notes FROM eat_behavior WHERE behavior_code = (SELECT behavior_code FROM encounters WHERE client_id=".$client_id_array['id']." AND assessment_id=".$assessment_id_array['assessment_id'].")";
               $pnv_problems = mysql_query($query_pnv_problems);
               $pnv_problems_result = mysql_fetch_array($pnv_problems);
               $response[$pnccid['id']][$client_id_array['id']]['trimester'][$tri_date]['problem_for_pnv'] = $pnv_problems_result['pnv_notes'];
           	  //echo $response[$pnccid['id']][$client_id_array['id']]['trimester'][$tri_date]['problem_for_pnv']."\n";
               $query_milk = "SELECT dairy_amount FROM nutrition WHERE nutrition_code = (SELECT nutrition_code FROM encounters WHERE client_id=".$client_id_array['id']." AND assessment_id=".$assessment_id_array['assessment_id'].")";
               $milk = mysql_query($query_milk);
               $milk_amount_result = mysql_fetch_array($milk);
               $response[$pnccid['id']][$client_id_array['id']]['trimester'][$tri_date]['dairy_amount'] = $milk_amount_result['dairy_amount'];
               $query_meat = "SELECT proteins_amount FROM nutrition WHERE nutrition_code = (SELECT nutrition_code FROM encounters WHERE client_id=".$client_id_array['id']." AND assessment_id=".$assessment_id_array['assessment_id'].")";
               $meat = mysql_query($query_meat);
               $meat_amount_result = mysql_fetch_array($meat);
               $response[$pnccid['id']][$client_id_array['id']]['trimester'][$tri_date]['proteins_amount'] = $meat_amount_result['proteins_amount'];
               $query_vegetable = "SELECT vegetable_amount FROM nutrition WHERE nutrition_code = (SELECT nutrition_code FROM encounters WHERE client_id=".$client_id_array['id']." AND assessment_id=".$assessment_id_array['assessment_id'].")";
               $vegetable = mysql_query($query_vegetable);
               $vegetable_amount_result = mysql_fetch_array($vegetable);
               $response[$pnccid['id']][$client_id_array['id']]['trimester'][$tri_date]['vegetable_amount'] = $vegetable_amount_result['vegetable_amount'];
               $query_fluid_amount = "SELECT fluid_amount FROM nutrition WHERE nutrition_code = (SELECT nutrition_code FROM encounters WHERE client_id=".$client_id_array['id']." AND assessment_id=".$assessment_id_array['assessment_id'].")";
               $fluid_amount = mysql_query($query_fluid_amount);
               $fluid_amount_result = mysql_fetch_array($fluid_amount);
               $response[$pnccid['id']][$client_id_array['id']]['trimester'][$tri_date]['fluid_amount'] = $fluid_amount_result['fluid_amount'];
               $query_grain = "SELECT grains_amount FROM nutrition WHERE nutrition_code = (SELECT nutrition_code FROM encounters WHERE client_id=".$client_id_array['id']." AND assessment_id=".$assessment_id_array['assessment_id'].")";
               $grains_amount = mysql_query($query_grain);
               $grains_amount_result = mysql_fetch_array($grains_amount);
               $response[$pnccid['id']][$client_id_array['id']]['trimester'][$tri_date]['total_grains'] = $grains_amount_result['grains_amount'];
               $query_fruit = "SELECT fruit_amount FROM nutrition WHERE nutrition_code = (SELECT nutrition_code FROM encounters WHERE client_id=".$client_id_array['id']." AND assessment_id=".$assessment_id_array['assessment_id'].")";
               $fruit_amount = mysql_query($query_fruit);
               $fruit_amount_result = mysql_fetch_array($fruit_amount);
               $response[$pnccid['id']][$client_id_array['id']]['trimester'][$tri_date]['fruit_amount'] = $fruit_amount_result['fruit_amount'];
               
               $query_caffine_daily = "SELECT hot_beverages_amount FROM nutrition WHERE nutrition_code = (SELECT nutrition_code FROM encounters WHERE client_id=".$client_id_array['id']." AND assessment_id=".$assessment_id_array['assessment_id'].")";
               $caffine_daily = mysql_query($query_caffine_daily);
               $caffine_daily_result = mysql_fetch_array($caffine_daily);
               $response[$pnccid['id']][$client_id_array['id']]['trimester'][$tri_date]['caffine_daily'] = $caffine_daily_result['hot_beverages_amount'];
           	  //echo $response[$pnccid['id']][$client_id_array['id']]['trimester'][$tri_date]['caffine_daily']."\n";
               $query_drugs_use = "SELECT drugs_pregnancy FROM drugs WHERE drugs_code = (SELECT drugs_code FROM encounters WHERE client_id=".$client_id_array['id']." AND assessment_id=".$assessment_id_array['assessment_id'].")";           	  
               $drugs_use = mysql_query($query_drugs_use);
               $drugs_use_result = mysql_fetch_array($drugs_use);
               $response[$pnccid['id']][$client_id_array['id']]['trimester'][$tri_date]['medication_select'] = $drugs_use_result['dp'];
               $query_medication_name = "SELECT medication_name FROM substance WHERE substance_code = (SELECT substance_code FROM encounters WHERE client_id=".$client_id_array['id']." AND assessment_id=".$assessment_id_array['assessment_id'].")";
               $medication_name = mysql_query($query_medication_name);
               $medication_name_result = mysql_fetch_array($medication_name);
               $response[$pnccid['id']][$client_id_array['id']]['trimester'][$tri_date]['medication_name'] = $medication_name_result['medication_name'];
           	  //echo $response[$pnccid['id']][$client_id_array['id']]['trimester'][$tri_date]['medication_name'];
               $query_medication_reason = "SELECT medication_reason FROM substance WHERE substance_code = (SELECT substance_code FROM encounters WHERE client_id=".$client_id_array['id']." AND assessment_id=".$assessment_id_array['assessment_id'].")";
               $medication_reason = mysql_query($query_medication_reason);
               $medication_reason_result = mysql_fetch_array($medication_reason);
               $response[$pnccid['id']][$client_id_array['id']]['trimester'][$tri_date]['medication_reason'] = $medication_reason_result;
               $query_smoking = "SELECT tobacco_pregnancy FROM tobacco WHERE tobacco_code = (SELECT tobacco_code FROM encounters WHERE client_id=".$client_id_array['id']." AND assessment_id=".$assessment_id_array['assessment_id'].")";           	  
               $smoking = mysql_query($query_smoking);
               $smoking_result = mysql_fetch_array($smoking);
               $response[$pnccid['id']][$client_id_array['id']]['trimester'][$tri_date]['smoke'] = $smoking_result['tobacco_pregnancy'];
               //echo $response[$pnccid['id']][$client_id_array['id']]['trimester'][$tri_date]['smoke'];
               $query_smoke_number = "SELECT tobacco_current FROM tobacco WHERE tobacco_code = (SELECT tobacco_code FROM encounters WHERE client_id=".$client_id_array['id']." AND assessment_id=".$assessment_id_array['assessment_id'].")";
               $smoke_number = mysql_query($query_smoke_number);
               $smoke_number_result = mysql_fetch_array($smoke_number);
               $response[$pnccid['id']][$client_id_array['id']]['trimester'][$tri_date]['amount_tobacco'] = $smoke_number_result['tobacco_current'];
               //echo $response[$pnccid['id']][$client_id_array['id']]['trimester'][$tri_date]['amount_tobacco'];     
               $query_second_hand_smoking = "SELECT tobacco_secondhand FROM tobacco WHERE tobacco_code = (SELECT tobacco_code FROM encounters WHERE client_id=".$client_id_array['id']." AND assessment_id=".$assessment_id_array['assessment_id'].")"; 
               $second_hand_smoking = mysql_query($query_second_hand_smoking);
               $second_hand_smoking_result = mysql_fetch_array($second_hand_smoking);
               $response[$pnccid['id']][$client_id_array['id']]['trimester'][$tri_date]['pregnancy_secondhand_tobacco'] = $second_hand_smoking_result['secondhand_smoking'];
               $query_alcohol = "SELECT alcohol_pregnancy FROM alcohol WHERE alcohol_code = (SELECT alcohol_code FROM encounters WHERE client_id=".$client_id_array['id']." AND assessment_id=".$assessment_id_array['assessment_id'].")";
               $alcohol = mysql_query($query_alcohol);
               $alcohol_result = mysql_fetch_array($alcohol);
               $response[$pnccid['id']][$client_id_array['id']]['trimester'][$tri_date]['alcohol'] = $alcohol_result['alcohol_pregnancy'];
               $query_fetal = "SELECT fetal_movement FROM concerns WHERE client_id =".$client_id_array['id']." AND assessment_id=".$assessment_id_array['assessment_id'];           	  
               $fetal = mysql_query($query_fetal);
               $fetal_result = mysql_fetch_array($fetal);
               $response[$pnccid['id']][$client_id_array['id']]['trimester'][$tri_date]['fetal_movement'] = $fetal_result['fetal_movement'];
               $query_contraction = "SELECT contractions FROM concerns WHERE client_id =".$client_id_array['id']." AND assessment_id=".$assessment_id_array['assessment_id'];
               $contraction = mysql_query($query_contraction);
               $contraction_result = mysql_fetch_array($contraction);
               $response[$pnccid['id']][$client_id_array['id']]['trimester'][$tri_date]['contractions'] = $contraction_result['contractions'];
               $query_headache = "SELECT headaches FROM concerns WHERE client_id =".$client_id_array['id']." AND assessment_id=".$assessment_id_array['assessment_id'];
               $headache = mysql_query($query_headache);
               $headache_result = mysql_fetch_array($headache);
               $response[$pnccid['id']][$client_id_array['id']]['trimester'][$tri_date]['headches'] = $headache_result['headches']; 
           	  //echo $response[$pnccid['id']][$client_id_array['id']]['trimester'][$tri_date]['headches'];
               $query_swelling = "SELECT swelling FROM concerns WHERE client_id =".$client_id_array['id']." AND assessment_id=".$assessment_id_array['assessment_id'];
               $swelling = mysql_query($query_swelling);
               $swelling_result = mysql_fetch_array($swelling);
               $response[$pnccid['id']][$client_id_array['id']]['trimester'][$tri_date]['swelling'] = $swelling_result['swelling']; 
           	  //echo $response[$pnccid['id']][$client_id_array['id']]['trimester'][$tri_date]['swelling'];
               $query_bleeding = "SELECT bleeding FROM concerns WHERE client_id =".$client_id_array['id']." AND assessment_id=".$assessment_id_array['assessment_id'];
               $bleeding = mysql_query($query_bleeding);
               $bleeding_result = mysql_fetch_array($bleeding);
               $response[$pnccid['id']][$client_id_array['id']]['trimester'][$tri_date]['bleeding'] = $bleeding_result['bleeding'];
           	  //echo $response[$pnccid['id']][$client_id_array['id']]['trimester'][$tri_date]['bleeding'];
               $query_pain = "SELECT pain FROM concerns WHERE client_id =".$client_id_array['id']." AND assessment_id=".$assessment_id_array['assessment_id'];
               $pain = mysql_query($query_pain);
               $pain_result = mysql_fetch_array($pain);
               $response[$pnccid['id']][$client_id_array['id']]['trimester'][$tri_date]['pain'] = $pain_result['pain'];
           	  //echo $response[$pnccid['id']][$client_id_array['id']]['trimester'][$tri_date]['pain'];
               $query_infection = "SELECT infections FROM concerns WHERE client_id =".$client_id_array['id']." AND assessment_id=".$assessment_id_array['assessment_id'];
               $infection = mysql_query($query_infection);
               $infection_result = mysql_fetch_array($infection);
               $response[$pnccid['id']][$client_id_array['id']]['trimester'][$tri_date]['infections'] = $infection_result['infections'];  
           	  //echo $response[$pnccid['id']][$client_id_array['id']]['trimester'][$tri_date]['infections'];
               $query_desc = "SELECT infection_desc FROM concerns WHERE client_id =".$client_id_array['id']." AND assessment_id=".$assessment_id_array['assessment_id'];
               $desc = mysql_query($query_desc);
               $desc_result = mysql_fetch_array($desc);
               $response[$pnccid['id']][$client_id_array['id']]['trimester'][$tri_date]['infection_desc'] = $desc_result['infection_desc'];
               $query_homevisit_date = "SELECT next_homevisit_date FROM encounters WHERE client_id=".$client_id_array['id']. " AND assessment_id=".$assessment_id_array['assessment_id'];
               $homevisit_date = mysql_query($query_homevisit_date);
               $homevisit_date_result = mysql_fetch_array($homevisit_date);
               $response[$pnccid['id']][$client_id_array['id']]['trimester'][$tri_date]['home_visit_date'] = $homevisit_date_result['next_homevisit_date'];
           }

           //Deal with a regular visit, the assessment_id for a regular visit is equal to zero
           else {
               $query_regular_date = "SELECT encounter_date FROM encounters WHERE client_id='".$client_id_array['id']."' AND assessment_id='".$assessment_id_array['assessment_id']."'";
               $regular_date = mysql_query($query_regular_date);
               while ($regular_visitDate = mysql_fetch_array($regular_date)) {
                   $reg_date = $regular_visitDate['encounter_date'];
                   $response[$pnccid['id']][$client_id_array['id']]['regular'][$reg_date] = array();
                   $query_reg_clientName = "SELECT GROUP_CONCAT(firstname,' ',middlename,' ',lastname) AS reg_client_name FROM client WHERE id = '".$client_id_array['id']."'";
                   $reg_client_name = mysql_query($query_reg_clientName);
                   $reg_clientName_result = mysql_fetch_array($reg_client_name);
                   $response[$pnccid['id']][$client_id_array['id']]['reg_client_name'] = $reg_clientName_result['reg_client_name']; 
                   $query_gestationWeek = "SELECT gestation_weeks FROM encounters WHERE client_id = '".$client_id_array['id']."' AND assessment_id = '".$assessment_id_array['assessment_id']."' AND encounter_date = '".$reg_date."'";
                   //echo $query_gestationWeek."\n";
                   $gestation_week = mysql_query($query_gestationWeek);
                   if ($gestation_week === FALSE) {die(mysql_error());}
                   $gestation_week_result = mysql_fetch_array($gestation_week);
                   $response[$pnccid['id']][$client_id_array['id']]['regular'][$reg_date]['gestation_week'] = $gestation_week_result['gestation_weeks'];
                   $query_next_reg_homevisit = "SELECT next_homevisit_date FROM encounters WHERE client_id='".$client_id_array['id']."' AND assessment_id='".$assessment_id_array['assessment_id']."' AND encounter_date='".$reg_date."'";
                   $next_reg_homevisit = mysql_query($query_next_reg_homevisit);
                   if ($next_reg_homevisit === FALSE) {die(mysql_error());}
                   $next_reg_homevisit_result = mysql_fetch_array($next_reg_homevisit);
                   $response[$pnccid['id']][$client_id_array['id']]['regular'][$reg_date]['next_homevisit_date'] = $next_reg_homevisit_result['next_homevisit_date'];
                   $query_narrative_notes = "SELECT notes FROM notes WHERE notes_code = (SELECT notes_code FROM encounters WHERE client_id='".$client_id_array['id']."' AND assessment_id= '".$assessment_id_array['assessment_id']."' AND encounter_date='".$reg_date."')";
                   //echo $query_narrative_notes."\n";
                   $narrative_notes = mysql_query($query_narrative_notes);
                   $narrative_notes_result = mysql_fetch_array($narrative_notes);
                   $response[$pnccid['id']][$client_id_array['id']]['regular'][$reg_date]['narrative_notes'] = $narrative_notes_result['notes'];
               }                                                            
           }  
       }
    
    }
       $response[0] = array();
       $response[0]["success"] = 1;
       // echoing JSON response
       echo json_encode($response);
}
	 
else {
	// required field is missing
	$response["success"] = 0;
	$response["message"] = "Required field(s) is missing";

	// echoing JSON response
	echo json_encode($response);
}
mysql_close();
?>
