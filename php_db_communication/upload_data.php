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

//check for post data
if(isset($_REQUEST)) {   
    db_connect();
    header('Content-Type:text/javascript');
    $data = json_decode(file_get_contents('php://input'), true);
    if(!isset($data)) {echo "DATA IS NOT SET YET!";}
    
    function checkNull($index) {
        $data = json_decode(file_get_contents('php://input'), true);
        //echo $data[$index];
        if(!isset($data[$index]) || is_null($data[$index]) || trim($data[$index]) === '') {    
            $data[$index] = 'NULL';
        }
        else {
            $data[$index] = '"'.$data[$index].'"';
        }
        return $data[$index];     
    }
    
    $uName = checkNull('pncc-name');
    $uPsw = checkNull('psw');
    $client_id = checkNull('client_id');
    $which_assess_visit = checkNull('which_assessmentvisit');
    $new_trim_visit_date = checkNull('new_trimester_visit_date');
    $which_reg_visit = checkNull('which_regularvisit');
    $new_reg_visit_date = checkNull('new_regular_visit_date');
    //$next_homevisit denotes the next regular visit
    $next_reg_homevisit = checkNull('next_homevisit_date');
    $narrative_notes = checkNull('narrative_notes');
    $gestation_week = checkNull('gestation_week');
    $address = checkNull('address');
    $mon_week = checkNull('how_many_months_weeks');
    $pre_pound = checkNull('pre_pounds');
    $pre_oz = checkNull('pre_ounces');
    $bmi = checkNull('pre_bmi');
    $weight_pound = checkNull('weight_pounds');
    $weight_oz = checkNull('weight_ounces');
    $next_appt = checkNull('next_appt_date');
    $fob = checkNull('fob');
    $residence = checkNull('residence');
    $transportation = checkNull('transportation');
    $medical_provider = checkNull('med_provider');
    $check_insurance = checkNull('insurance');
    $insurance_name = checkNull('insurance_name');
    $package = checkNull('package_e');
    $medic_qualify = checkNull('medicate_qual_select');
    $noqualify_reason = checkNull('reason_noqualify');
    $conception_date = checkNull('conception_date');
    $delivery_date = checkNull('delivery_date');
    $enrollment_date = checkNull('enroll_date');
    $rid = checkNull('rid');
    $conception_date = checkNull('conception_date');
    $delivery_date = checkNull('delivery_date');
    $wic = checkNull('wic_vouchers');
    $wic_when = checkNull('wic_appt');
    $milk_amount = checkNull('cup');
    //echo gettype($milk_amount);
    $cheese_amount = checkNull('cheese');
    $meat_amount = checkNull('meat_ounce');
    $egg_amount = checkNull('egg');
    $peanutbutter_amount = checkNull('peanutbutter');
    $salad_amount = checkNull('salad');
    $vege_amount = checkNull('cooked_vege');
    $juice_amount = checkNull('vege_juice');
    $water_amount = checkNull('h2o');
    $medi_whole_fruit_amount = checkNull('medium_whole');
    $chopped_fruit_amount = checkNull('chopped');
    $fruit_juice_amount = checkNull('fruit_juice');
    $driedfruit_amount = checkNull('dried_fruit');
    $bread_slice = checkNull('bread');
    $cereal_cups = checkNull('cereal');
    $caffine_intake = checkNull('caffine_daily');
    $adequate_food = checkNull('adequate_food');
    $inadequate_food_issue = checkNull('food_issue_select');
    $food_stamp = checkNull('food_stamps');
    $tanf = checkNull('tanf');
    $pnv = checkNull('pnv');
    $pnv_problem = checkNull('problem_for_pnv');
    $drug_use = checkNull('medication_select');
    $medication_name = checkNull('medication_name');
    $medication_reason = checkNull('medication_reason');
    $smoking = checkNull('smoke');
    $smoking_num = checkNull('amount_tobacco');
    $secondhand_smoking = checkNull('pregnancy_secondhand_tobacco');
    $alcohol = checkNull('alcohol');
    $fetal_movement = checkNull('fetal_movement');
    $contraction = checkNull('contractions');
    $headache = checkNull('headaches');
    $swelling = checkNull('swelling');
    $bleeding = checkNull('bleeding');
    $pain = checkNull('pain');
    $infection = checkNull('infections');
    $other_desc = checkNull('infection_desc');
    $trim_homevisit_schedule = checkNull('home_visit_date');
    
    function db_error($query) {
        if(!$query) {
            die('Invalid query:'.mysql_error());
        }
    }

//Insert "home visit scheduled for" to encounters table for a trimester visit
    $insert_trim_hv = "INSERT INTO encounters (client_id,assessment_id,encounter_date,next_homevisit_date) VALUES (".$client_id.",".$which_assess_visit.",".$new_trim_visit_date.",".$trim_homevisit_schedule.")";    
    db_error($insert_trim_hv);
    mysql_query($insert_trim_hv);
    //$new_trim_notes_code = mysql_insert_id();
    //$insert_trim_notesCode = "INSERT INTO encounters (client_id,encounter_date,assessment_id,notes_code) VALUES (".$client_id.",".$new_trim_visit_date.",".$which_assess_visit.",".$new_trim_notes_code.")";
    //db_error($insert_trim_notesCode);
    //mysql_query($insert_trim_notesCode);
   
//Insert values into medical table
    $insert_medical = "INSERT INTO medical (client_id,weight_pounds,weight_ounces,pre_bmi,edcpt,edc) VALUES (".$client_id.",".$pre_pound.",".$pre_oz.",".$bmi.",".$conception_date.",".$delivery_date.")";
    db_error($insert_medical);
    mysql_query($insert_medical);
    $new_medical_code = mysql_insert_id();
    
//Insert value into pregnancy table
    $insert_obvisit = "INSERT INTO pregnancy(next_obvisit_date,provider,insurance,insurance_name,medicaid_type,apply_type,reason_noqualify) VALUES (".$next_appt.",".$medical_provider.",".$check_insurance.",".$insurance_name.",".$package.",".$medic_qualify.",".$noqualify_reason.")";
    db_error($insert_obvisit);
    $new_pregnancy_code = mysql_insert_id();
    //Insert new generated pregnancy code into encounters table
    $insert_pregnancyCode = "INSERT INTO encounters(client_id,encounter_date,assessment_id,pregnancy_code) VALUES (".$client_id.",".$new_trim_visit_date.",".$which_assess_visit.",".$new_pregnancy_code.")";
    db_error($insert_pregnancyCode);
    mysql_query($insert_pregnancyCode);

//Update fob value into client table
    $update_fob = "UPDATE client SET fob=".$fob." WHERE id=".$client_id."";
    //echo $update_fob."\n";
    db_error($update_fob);
    mysql_query($update_fob);
    
//Insert current weight into weight table
    $insert_current_weight = "INSERT INTO weight (weight_pounds,weight_ounces) VALUES (".$weight_pound.",".$weight_oz.")";
    db_error($insert_current_weight);
    mysql_query($insert_current_weight);
    $new_weight_code = mysql_insert_id();
    $insert_weight_code = "INSERT INTO encounters(client_id,encounter_date,assessment_id,weight_code)VALUES(".$client_id.",".$new_trim_visit_date.",".$which_assess_visit.",".$new_weight_code.")";
    db_error($insert_weight_code);
    mysql_query($insert_weight_code);
    
//Insert address for the client
    $insert_address = "INSERT INTO contact (client_id,address) VALUES (".$client_id.",'".$address."')";
    //echo $insert_address;
    db_error($insert_address);
    mysql_query($insert_address);

//Insert values into material_needs table
    $insert_materialNeeds = "INSERT INTO material_needs (homeless_current,transportation,food) VALUES (".$residence.",".$transportation.",".$adequate_food.")";
    db_error($insert_materialNeeds);
    mysql_query($insert_materialNeeds);
    $new_material_code = mysql_insert_id();
    $insert_materialCode = "INSERT INTO encounters (client_id,encounter_date,assessment_id,material_code) VALUES (".$client_id.",".$new_trim_visit_date.",".$which_assess_visit.",".$new_material_code.")";
    db_error($insert_materialCode);
    mysql_query($insert_materialCode);

//Insert values into eat_behavior table
    $insert_eatBehavior = "INSERT INTO eat_behavior (wic_vouchers,wic_appt,pnv,food_stamps,tanf,pnv_notes) VALUES (".$wic.",".$wic_when.",".$pnv.",".$food_stamp.",".$tanf.",".$pnv_problem.")";
    db_error($insert_eatBehavior);
    mysql_query($insert_eatBehavior);
    $new_behavior_code = mysql_insert_id();
    $insert_behaviorCode = "INSERT INTO encounters (client_id,encounter_date,assessment_id,behavior_code) VALUES (".$client_id.",".$new_trim_visit_date.",".$which_assess_visit.",".$new_behavior_code.")";
    db_error($insert_behaviorCode);
    mysql_query($insert_behaviorCode);

//Insert values into nutrition table
//Check if each nutrition intake is null or not   
   if ($milk_amount == 'NULL' || $cheese_amount == 'NULL') {
       $dairy_amount = 'NULL';
   }
   else if ($milk_amount != 'NULL' && $cheese_amount != 'NULL') {
       $dairy_amount = (int)$data['cup'] + (int)$data['cheese']; 
   }
   if ($meat_amount == 'NULL' || $egg_amount == 'NULL' || $peanutbutter_amount == 'NULL') {
       $proteins_amount = 'NULL';
   }
   else if ($meat_amount != 'NULL' && $egg_amount != 'NULL' && $peanutbutter_amount != 'NULL'){
       $proteins_amount = (int)$data['meat_ounce'] + (int)$data['egg'] + (int)$data['penutbutter'];
   } 
   if ($salad_amount == 'NULL' || $vege_amount == 'NULL' || $juice_amount == 'NULL') {
       $vegetable_amount = 'NULL';
   }
   else if ($salad_amount != 'NULL' && $vege_amount != 'NULL' && $juice_amount != 'NULL') {
       $vegetable_amount = (int)$data['salad'] + (int)$data['cooked_vege'] + (int)$data['vege_juice'];
   }
   if ($water_amount == 'NULL') {
       $fluid_amount = 'NULL';
   }
   else {
       $fluid_amount = (int)$data['h2o'];
   }
   if ($medi_whole_fruit_amount == 'NULL' || $chopped_fruit_amount == 'NULL' || $fruit_juice_amount == 'NULL' || $driedfruit_amount == 'NULL') {
       $fruit_amount = 'NULL';
   }
   else if ($medi_whole_fruit_amount != 'NULL' && $chopped_fruit_amount != 'NULL' && $fruit_juice_amount != 'NULL' && $driedfruit_amount != 'NULL') {
       $fruit_amount = (int)$data['medium_whole'] + (int)$data['chopped'] + (int)$data['fruit_juice'] + (int)$data['dried_fruit'];
   }
   if ($bread_slice == 'NULL' || $cereal_cups == 'NULL') {
       $grains_amount = 'NULL';
   }
   else if ($bread_slice != 'NULL' || $cereal_cups != 'NULL') {
       $grains_amount = (int)$data['bread'] + (int)$data['cereal'];
   }
   if ($caffine_intake == 'NULL') {
       $hot_beverages_amount = 'NULL';
   }
   else {
       $hot_beverages_amount = (int)$data['caffine_daily'];
   }

   $insert_nutrition = "INSERT INTO nutrition (dairy_amount,proteins_amount,vegetable_amount,fluid_amount,fruit_amount,grains_amount,hot_beverages_amount) VALUES (".$dairy_amount.",".$proteins_amount.",".$vegetable_amount.",".$fluid_amount.",".$fruit_amount.",".$grains_amount.",".$hot_beverages_amount.")";
   db_error($insert_nutrition);
   mysql_query($insert_nutrition);
   $new_nutrition_code = mysql_insert_id();
   $insert_nutrition_code = "INSERT INTO encounters (client_id,encounter_date,assessment_id,nutrition_code) VALUES (".$client_id.",".$new_trim_visit_date.",".$which_assess_visit.",".$new_nutrition_code.")";
   db_error($insert_nutrition_code);
   mysql_query($insert_nutrition_code);

//Insert value to drugs table
   $insert_drugs = "INSERT INTO drugs (drugs_pregnancy) VALUES (".$drug_use.")";
   db_error($insert_drugs);
   mysql_query($insert_drugs);
   $new_drugs_code = mysql_insert_id();
   $insert_drugsCode = "INSERT INTO encounters (client_id,encounter_date,assessment_id,drugs_code) VALUES (".$client_id.",".$new_trim_visit_date.",".$which_assess_visit.",".$new_drugs_code.")";
   db_error($insert_drugsCode);
   mysql_query($insert_drugsCode);
      
//Insert values into substance table
    $insert_substance = "INSERT INTO substance (medication_name,medication_reason) VALUES (".$medication_name.",".$medication_reason.")";
    db_error($insert_substance);
    mysql_query($insert_substance);
    $new_substance_code = mysql_insert_id();
    $insert_substanceCode = "INSERT INTO encounters (client_id,encounter_date,assessment_id,substance_code) VALUES (".$client_id.",".$new_trim_visit_date.",".$which_assess_visit.",".$new_substance_code.")";
    db_error($insert_substanceCode);
    mysql_query($insert_substanceCode); 
//Insert values into tobacco table
    $insert_tobacco = "INSERT INTO tobacco (tobacco_pregnancy,tobacco_current,tobacco_secondhand) VALUES (".$smoking.",".$smoking_num.",".$secondhand_smoking.")";
    db_error($insert_tobacco);
    mysql_query($insert_tobacco);
    $new_tobacco_code = mysql_insert_id();
    $insert_tobaccoCode = "INSERT INTO encounters (client_id,encounter_date,assessment_id,tobacco_code) VALUES (".$client_id.",".$new_trim_visit_date.",".$which_assess_visit.",".$new_tobacco_code.")";
    db_error($insert_tobaccoCode);
    mysql_query($insert_tobaccoCode);
//Insert value into alcohol table
    $insert_alcohol = "INSERT INTO alcohol (alcohol_pregnancy) VALUES (".$alcohol.")";
    db_error($insert_alcohol);
    mysql_query($insert_alcohol);
    $new_alcohol_code = mysql_insert_id();
    $insert_alcoholCode = "INSERT INTO encounters (client_id,encounter_date,assessment_id,alcohol_code) VALUES (".$client_id.",".$new_trim_visit_date.",".$which_assess_visit.",".$new_alcohol_code.")";
    db_error($insert_alcoholCode);
    mysql_query($insert_alcoholCode); 
//Insert values into concerns table
    $insert_concerns = "INSERT INTO concerns (client_id,assessment_id,fetal_movement,contractions,headaches,swelling,bleeding,pain,infections,infection_desc) VALUES (".$client_id.",".$which_assess_visit.",".$fetal_movement.",".$contraction.",".$headache.",".$swelling.",".$bleeding.",".$pain.",".$infection.",".$other_desc.")";
    db_error($insert_concerns);
    mysql_query($insert_concerns);

//Handle regular visits, assessment_id for regular visit should be equal to zero
//Insert narrative notes into notes table
    $insert_notes = "INSERT INTO notes(notes) VALUES (".$narrative_notes.")";
    db_error($insert_notes);
    mysql_query($insert_notes);
    $notes_code = mysql_insert_id();
    $insert_notesCode = "INSERT INTO encounters(client_id,assessment_id,encounter_date,notes_code) VALUES (".$client_id.",".$which_reg_visit.",".$new_reg_visit_date.",".$notes_code.")";
 //Insert gestation_weeks and next_home_visit_date into encounters table
    $insert_reg_others = "INSERT INTO encounters(client_id,assessment_id,encounter_date,gestation_weeks,next_homevisit_date) VALUES (".$client_id.",".$which_reg_visit.",".$new_reg_visit_date.",".$gestation_week.",".$next_reg_homevisit.")";
    db_error($insert_reg_others);
    mysql_query($insert_reg_others);
  
   $response[0] = array();
   $response[0]["success"] = 1;
   echo "UPLOADING IS WORKING!"."\n";
}

else {
    $response["success"] = 0;
    echo "SOMETHING IS WRONG";
}



?>
