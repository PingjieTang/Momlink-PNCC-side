/*
 * Licensed to the Apache Software Foundation (ASF) under one
 * or more contributor license agreements.  See the NOTICE file
 * distributed with this work for additional information
 * regarding copyright ownership.  The ASF licenses this file
 * to you under the Apache License, Version 2.0 (the
 * "License"); you may not use this file except in compliance
 * with the License.  You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing,
 * software distributed under the License is distributed on an
 * "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
 * KIND, either express or implied.  See the License for the
 * specific language governing permissions and limitations
 * under the License.
 */

var db = null;
var data = null;
var isOffline;
var json_login;
var upload_data;
var client_id = null;
var pre_addr = null;
var client_visit_date = null;
var today_visit_date = null;
var reg_visit_date = null;
var d = new Date();
var year = d.getFullYear();
var month = d.getMonth();
var day = d.getDay();
var date = null;

var app = {
    SOME_CONSTANTS : false,  // some constant

    // Application Constructor
    initialize: function() {
        console.log("console log init");
        this.bindEvents();
        this.initFastClick();
    },
    // Bind Event Listeners
    //
    // Bind any events that are required on startup. Common events are:
    // 'load', 'deviceready', 'offline', and 'online'.
    bindEvents: function() {
        document.addEventListener('deviceready', this.onDeviceReady, false);
    },
    initFastClick : function() {
        window.addEventListener('load', function() {
            FastClick.attach(document.body);
        }, false);
    },
    // Phonegap is now ready...
    onDeviceReady: function() {
        console.log("device ready, start making you custom calls!");
        db = new PouchDB('momdb', {adapter : 'websql'});
    },

    formSubmit: function(data) {
        console.log(data);
    }
};

$(function() {

    $('.logout_cancel').on('click', function(){
        window.location = '#login-page';
        location.reload();
    });

    var more_reasons_count = 1;
    $('#more_reasons_btn').on('click', function(event) {
        event.preventDefault();
        $('#more_reasons').append('<input type="text" name="reasons_for_medication_' + more_reasons_count + '" id="reasons_for_medication_' + more_reasons_count + '" />');
        more_reasons_count ++;
        $('.ui-page').trigger('create');
    });

    var more_problems_count = 1;
    $('#more_problems_btn').on('click', function(event) {
        event.preventDefault();
        $('#pnv_problems').append('<input type="text" name="more_problems' + more_problems_count + '" id="problems_for_pnv' + more_problems_count + '" />');
        more_reasons_count ++;
        $('.ui-page').trigger('create');
    });

    $('#login_btn').on('click', function(event) {
        json_login = {};
        var array = $('form[id="login-form"]').serializeArray();
        $.each(array, function() {
            if (json_login[this.name] !== undefined) {
                if (!json_login[this.name].push) {
                    json_login[this.name] = [json_login[this.name]];
                }
                json_login[this.name].push(this.value || '');
            } 
            else {
                json_login[this.name] = this.value || '';
            }
        });
        //print user name and password
        console.log(json_login);
        //console.log(JSON.stringify(json_login));
        if($('#name').val().length > 0 && $('#psw').val().length > 0) {
            isOffline = (navigator.network.connection.type == Connection.NONE);
            if(isOffline) {
                alert('You are offline!');
                db.get(json_login['pncc-name'], function(err, doc) {
                    if (err) {//No such client in local database when offline, needs to login online at least once.
                        alert("There is no internet connection.");
                    }
                    if (json_login['psw'] != doc['psw']) {
                        alert("Password is not correct.");
                    }
                    alert(JSON.stringify(doc, null, 2));
                    data = doc['patients'];   
                    // if username and psw are exist from local database
                    $.each(doc.patients, function(key, value) {
                        var id_name = "client_" + key;
                        if(parseInt(key) > 0) { 
                            if($('#' + id_name).length == 0)
                                $('#client_list').append('<a href="#home-visit-page" id="client_' + key + '" class="client_btn_group" value="' + key + '" data-role="button">Client ' + parseInt(key) + '</a>');
                        }
                    });            
                });               
            }
            else {//Online and local database sends data to server through the Ajax call
                $.ajax({
                    url:'https://momlink.crc.nd.edu/~pingjie/get_data.php',
                    // url:'http://localhost/~pingjietang/get_data.php',
                    data:JSON.stringify(json_login),
                    type:'POST',
                    // dataType:'text',
                    dataType:'json',
                    success: onSuccess,
                    error: function(err) {
                        console.log(err);
                    }
                }); 
            }         
        }
        else {
            alert('Username or password is not valid!');
            event.preventDefault();
        }
    
        function onSuccess(response) {//online callback function, response is the result returned from PHP
            console.log('Login Success!\n');    
            // console.log(JSON.stringify(response));  
            data = response;  
            // alert (Object.keys(data));
            $.each(data, function(index, value) {//$response[pnccid][id]["trimester"]...
                $.each(data[index],function(key, value) {
                    var id_name = key; //key is the pncc id
                    if(parseInt(key) > 0) { //key == 0 denotes status
                        if($('#' + id_name).length == 0) {
                            $('#client_list').append('<a href="#home-visit-page" id="client_' + parseInt(key) + '" class="client_btn_group" value="' + parseInt(key) + '" data-role="button">Client ' + parseInt(key) + '</a>');
                        }
                    }
                });        
            });

            //Scheduled home visits section on main page
            var visitAddr = null;
            // var currentDay = null;
            for (var index in data) { //index is the pnccid
                for (var key in data[index]) { //for each client of each pnccid
                    visitAddr = data[index][key]['address'];
                    for (client_visit_date in data[index][key]['trimester']) {
                        var scheduled_visit_date = data[index][key]['trimester'][client_visit_date]['home_visit_date'];
                        // if (day.toString().length == 1) {
                        //     currentDay = '0' + day; 
                        // }
                        // else if (day.toString().length > 1) {
                        //     currentDay = day.toString();
                        // }
                        // if (scheduled_visit_date == monthNamesNum[month] + '/' + currentDay + '/' + year )
                        if (scheduled_visit_date == currentDate()) {
                            $('#reminder_checklist').append('<fieldset class="ui-grid-a"><div class="ui-block-a" style="margin: 5% 0 0 15%;"><input type="checkbox" id="client_' + key + '_reminder" data-role="none"></input><label for="client_' + key + '_reminder"></label></div><div class="ui-block-b" style="width: auto;"><a href="#home-visit-page" class="client_btn_group" value="' + key + '" data-role="button">client_' + key + ' ' + visitAddr + '</a></div></fieldset>').trigger('create');
                        }
                    }
                }
                
            }
            //Get address for each scheduled date      
            for (var index in data) {
                for (var key in data[index]) {
                    visitAddr = data[index][key]['address'];
                    for (var reg_date in data[index][key]['regular']) {
                        var reg_visit_date = data[index][key]['regular'][reg_date]['next_homevisit_date'];
                        // if (day.toString().length == 1) {
                        //     currentDay = '0' + day; 
                        // }
                        // else if (day.toString().length > 1) {
                        //     currentDay = day.toString();
                        // }
                        // if (reg_visit_date == monthNamesNum[month] + '/' + currentDay + '/' + year)
                        if (reg_visit_date == currentDate()) {
                            $('#reminder_checklist').append('<fieldset class="ui-grid-a"><div class="ui-block-a" style="margin: 5% 0 0 15%;"><input type="checkbox" id="client_' + key + '_reminder" data-role="none"></input><label for="client_' + key + '_reminder"></label></div><div class="ui-block-b" style="width: auto;"><a href="#home-visit-page" class="client_btn_group" value="' + key + '" data-role="button">client_' + key + ' ' + visitAddr + '</a></div></fieldset>').trigger('create');
                        }
                    }
                }
                
            }

            db.get(json_login['pncc-name'], function(err, doc) {//local database sync data from webserver
                if (err) {//If no such client in local database
                    // alert("error");
                    db.put({
                        _id: json_login['pncc-name'],
                        psw: json_login['psw'],
                        patients: data
                    }, function(err, response) {
                        if (err) { 
                            // console.log("PUT FAIL");
                            return console.log(err); 
                        }
                        // alert(JSON.stringify(response));
                    });                  
                    return console.log(err);               
                }
                
                if (json_login['psw'] != doc['psw']) {//Client could be found in local database, but password is incorrect
                    db.put({
                        _id: json_login['pncc-name'],
                        _rev:doc._rev,
                        psw: json_login['psw'],
                        patients: data
                    }, function(err, response) {
                        if (err) {
                            return console.log(err);
                        }
                        alert(JSON.stringify(response));
                    });
                }     
            });
        }
    });

//decoder for general option questions
    function select_decoder(choose) {
        if (choose == 0)
            return "Choose one";
        if (choose == 1)
            return "Yes";
        if (choose == 2)
            return "No";
        if (choose == "NULL")
            return "Choose one";
        else
            return "Choose one";
    }

//decoder for pe or pkg_e option question
    function pkg_e_decoder(choose) {
        if (choose == 1)
            return "PE";
        if (choose == 2)
            return "Pkg E";
        if (choose == "NULL")
            return "Choose one";
        else 
            return "Choose one";
    }

//decoder for subproblem of qualifies for medicate
    function medi_qualify_subselect(choose) {
        if (choose == 1)
            return "Assisting with Pkg E today";
        if (choose == 2)
            return "Refused medicate";
        if (choose == "NULL")
            return "Choose one";
        else
            return "Choose one";
    }

//decoder for do you have insurance question
    function have_insurance_select_decoder(type) {
        if (type == 1) 
            return "Private";
        if (type == 2)
            return "Self";
        if (type == 3)
            return "Medicate";
        if (type == 4)
            return "Qualifies for medicate";
        if (type == 5)
            return "Does not qualify for medicate";
        if (type == 6)
            return "None";
        if (type == "NULL")
            return "Choose one";
        else
            return "Choose one";
    }

//decoder for wic option question
    function wic_select(choose) {
        if (choose == 0)
            return "Choose one";
        if (choose == 1)
            return "Yes";
        if (choose == 2)
            return "No";
        if (choose == 3)
            return "Appt Scheduled";
        if (choose == 4)
            return "Refused";
        else
            return "Choose one";
    }

//Popout questions for insurance selection problem
    function pop_insurance_select(value) {
        if(value == "Private") {
            $('#insurance_name_id').show();
        }
        else {
            $('#insurance_name_id').hide();
        }
        if(value == "Medicate") {
            $('#medicate_id').show();
        }
        else {
            $('#medicate_id').hide();
        }
        if(value == "Qualifies for medicate") {
            $('#med_qualify').show();
        }
        else {
            $('#med_qualify').hide();
        }
        if(value == "Does not qualify for medicate") {
            $('#unqual_reason_id').show();
        }
        else {
            $('#unqual_reason_id').hide();
        }
    }

//Get current date
    function currentDate() {
        var currentDay = null;
        var thisMon = month + 1;
        if (day.toString().length == 1) {
        currentDay = '0' + day; 
        }
        else if (day.toString().length > 1) {
            currentDay = day.toString();
        }
        date = monthNamesNum[month] + '/' + currentDay + '/' + year
        return date;   
    } 
          
    // Original (didn't work): $('.client_btn_group').on('click', function(){});
    // Add listener to dynamically appended element has to use the following format
    $(document).on('click', '.client_btn_group', function() {
        //Get current client id for today's visit
        client_id = $(this).attr('value');
        var old_trim_visit_count = 0;
        var reg_assessment_id = 0;
        var old_reg_visit_count = 0;
        var pncc_id;
        for (var key in data) {
            if (key != 0) {
                pncc_id = key;
            }
        }       
        function dob(x) {//calculate dob for client
            var n = x.split("/");
            return n[n.length - 1];
        }

//Load old trimester visits to App
        for (var key in data[pncc_id][client_id]['trimester']) {//Key corresponds to each old trimester visit for this client
        	// console.log(key);
        	$('#trimvisit_id').append('<button class="trimester_date_class" id="old_trim_visit'+ old_trim_visit_count +'" value="'+key+'">' + key + '</button>'); 
        	++old_trim_visit_count;
        }
        //An old trimester visit is triggered 
        $("button[id^='old_trim_visit']").on('click', function(event) {
            event.preventDefault();
            window.location = "#navigation_page";
            client_visit_date = $(this).html(); //trimester visit date
            today_visit_date = client_visit_date;
            $('.nutri_class').show();
            $('.item_serving').hide();
            var patient_name = data[pncc_id][client_id]['name'];
            document.getElementById('patient_name').innerHTML = patient_name;
            var v_dob = data[pncc_id][client_id]['dob'];
            var age = year - dob(v_dob);
            document.getElementById('age_id').innerHTML = age; 
            var pre_pregnancy_pounds = data[pncc_id][client_id]['trimester'][client_visit_date]['pre_weight_lb'];
            var pre_pregnancy_ounces = data[pncc_id][client_id]['trimester'][client_visit_date]['pre_weight_oz'];
            $('#pre_weight_lb').val(pre_pregnancy_pounds);
            $('#pre_weight_oz').val(pre_pregnancy_ounces);
            $('#pre_bmi').val(data[pncc_id][client_id]['trimester'][client_visit_date]['prepregnancy_bmi']);
            $('#pounds').val(data[pncc_id][client_id]['trimester'][client_visit_date]['weight_pounds']);
            $('#ounces').val(data[pncc_id][client_id]['trimester'][client_visit_date]['weight_ounces']);
            var next_appt_date_db = data[pncc_id][client_id]['trimester'][client_visit_date]['next_appt_date'];
            if (next_appt_date_db != null) {
            	var split_appt_date= next_appt_date_db.split('/');
            	var appt_date_combine = split_appt_date[2] + '-' + split_appt_date[0] + '-' + split_appt_date[1];
            	$('#next_appt_date').val(appt_date_combine);	
            }       
            var residence = data[pncc_id][client_id]['trimester'][client_visit_date]['residence'];
            document.getElementById("residence").value = select_decoder(residence);
            var transportation = data[pncc_id][client_id]['trimester'][client_visit_date]['transportation'];
            document.getElementById('transportation').value = select_decoder(transportation);
            var fob = data[pncc_id][client_id]['trimester'][client_visit_date]['fob'];
            document.getElementById('fob').value = select_decoder(fob);
            var provider = data[pncc_id][client_id]['trimester'][client_visit_date]['med_provider'];
            document.getElementById('med_provider').value = select_decoder(provider);
            var have_insurance_db = data[pncc_id][client_id]['trimester'][client_visit_date]['insurance'];
            var have_insurance = have_insurance_select_decoder(have_insurance_db);
            $('#dropdown_insurance').val(have_insurance);
            $('#dropdown_insurance').selectmenu();
            $('#dropdown_insurance').selectmenu('refresh', true);
            var valueSelected = $('#dropdown_insurance').val();
            pop_insurance_select(valueSelected);
            $('#insurance_name').val(data[pncc_id][client_id]['trimester'][client_visit_date]['insurance_name']);
            var pkg = data[pncc_id][client_id]['trimester'][client_visit_date]['package_e'];
            var select_pkg = pkg_e_decoder(pkg);
            $('#package_id').val(select_pkg);
            $('#package_id').selectmenu();
            $('#package_id').selectmenu('refresh', true);

            var medi_qualify = data[pncc_id][client_id]['trimester'][client_visit_date]['medicate_qual_select'];
            var medi_qualify_choose = medi_qualify_subselect(medi_qualify);
            $('#medi_qual_select').val(medi_qualify_choose);
            $('#medi_qual_select').selectmenu();
            $('#medi_qual_select').selectmenu('refresh', true);
            var noqualify_reason = data[pncc_id][client_id]['trimester'][client_visit_date]['reason_noqualify'];
            $('#reason_noqualify_id').val(noqualify_reason);
            var conception_date = data[pncc_id][client_id]['trimester'][client_visit_date]['conception_date'];
            if (conception_date != null) {
            	var conception_date_split = conception_date.split('/');
            	//In the format of YYYY-MM-DD
            	var conception_date_combine = conception_date_split[2] + '-' + conception_date_split[0] + '-' + conception_date_split[1];
            	$('#conception_date').val(conception_date_combine);	
            }    
            var delivery_date = data[pncc_id][client_id]['trimester'][client_visit_date]['delivery_date'];
            if (delivery_date != null) {
            	var delivery_date_split = delivery_date.split('/');
            	var delivery_date_combine = delivery_date_split[2] + '-' + delivery_date_split[0] + '-' + delivery_date_split[1];
            	$('#delivery_date').val(delivery_date_combine);	
            }         
            var wic_db = data[pncc_id][client_id]['trimester'][client_visit_date]['wic_vouchers'];
            var wic_decode = wic_select(wic_db);
            // $('#wic').append('<option value="'+ wic_decode +'" selected>'+ wic_decode + '</option>');
            $('#wic').val(wic_decode);
            $('#wic').selectmenu();
            $('#wic').selectmenu('refresh', true);
            var when_appt_db = data[pncc_id][client_id]['trimester'][client_visit_date]['wic_appt'];
            if (when_appt_db != null) {
            	var when_appt_split = when_appt_db.split('/');
            	var when_appt_combine = when_appt_split[2] + '-' + when_appt_split[0] + '-' + when_appt_split[1];
            	$('#wic_appt').val(when_appt_combine);	
            } 
            var dairy_amount = data[pncc_id][client_id]['trimester'][client_visit_date]['dairy_amount'];
            $('#milk').val(dairy_amount);
            var protein_amount = data[pncc_id][client_id]['trimester'][client_visit_date]['proteins_amount'];
            $('#meat').val(protein_amount);
            var vegetable_amount = data[pncc_id][client_id]['trimester'][client_visit_date]['vegetable_amount'];
            $('#veg').val(vegetable_amount);
            var fluid_amount = data[pncc_id][client_id]['trimester'][client_visit_date]['fluid_amount'];
            $('#h2o').val(fluid_amount);
            var fruit_amount = data[pncc_id][client_id]['trimester'][client_visit_date]['fruit_amount'];
            $('#fruit').val(fruit_amount);
            var total_grains = data[pncc_id][client_id]['trimester'][client_visit_date]['total_grains'];
            $('grains_id').val(total_grains);
            var caffine_daily = data[pncc_id][client_id]['trimester'][client_visit_date]['caffine_daily'];
            $('#caffine').val(caffine_daily);
            var adequate_food_db = data[pncc_id][client_id]['trimester'][client_visit_date]['adequate_food'];
            var adequate_food_select = select_decoder(adequate_food_db);
            $('#adequate_food_supply').val(adequate_food_select);
            $('#adequate_food_supply').selectmenu();
            $('#adequate_food_supply').selectmenu('refresh', true);
            var food_stamps = data[pncc_id][client_id]['trimester'][client_visit_date]['food_stamps'];
            $('#food_stamps').val(food_stamps);
            var tanf = data[pncc_id][client_id]['trimester'][client_visit_date]['tanf'];
            $('#tanf').val(tanf);
            var pnv_db = data[pncc_id][client_id]['trimester'][client_visit_date]['pnv'];
            var pnv_db_select = select_decoder(pnv_db);
            $('#pnv_select').val(pnv_db_select);
            $('#pnv_select').selectmenu();
            $('#pnv_select').selectmenu('refresh', true);
            var pnv_problems = data[pncc_id][client_id]['trimester'][client_visit_date]['problem_for_pnv'];
            $('#problem_for_pnv').val(pnv_problems);
            var drugs_use_db = data[pncc_id][client_id]['trimester'][client_visit_date]['medication_select'];
            var drugs_use = select_decoder(drugs_use_db);
            $('#medication_select').val(drugs_use);
            $('#medication_select').selectmenu();
            $('#medication_select').selectmenu('refresh', true);
            var medication_name = data[pncc_id][client_id]['trimester'][client_visit_date]['medication_name'];
            $('#medication_name').val(medication_name);
            var medication_reason = data[pncc_id][client_id]['trimester'][client_visit_date]['medication_reason'];
            $('#reasons_for_medication').val(medication_reason);
            var smoking_db = data[pncc_id][client_id]['trimester'][client_visit_date]['smoke'];
            var smoking_db_select = select_decoder(smoking_db);
            $('#smoking_select').val(smoking_db_select);
            $('#smoking_select').selectmenu();
            $('#smoking_select').selectmenu('refresh', true);
            var smoke_number = data[pncc_id][client_id]['trimester'][client_visit_date]['amount_tobacco'];
            $('#smoking_number').val(smoke_number);
            var second_smoking_db = data[pncc_id][client_id]['trimester'][client_visit_date]['pregnancy_secondhand_tobacco'];
            var second_smoking_select = select_decoder(second_smoking_db);
            $('#2nd_hand_smoking_select').val(second_smoking_select);
            $('#2nd_hand_smoking_select').selectmenu();
            $('#2nd_hand_smoking_select').selectmenu('refresh', true);
            var alcohol_db = data[pncc_id][client_id]['trimester'][client_visit_date]['alcohol'];
            var alcohol_select = select_decoder(alcohol_db);
            $('#alcohol').val(alcohol_select);
            $('#alcohol').selectmenu();
            $('#alcohol').selectmenu('refresh', true);
            var fetal_db = data[pncc_id][client_id]['trimester'][client_visit_date]['fetal_movement'];
            var fetal_select = select_decoder(fetal_db);
            $('#fetal_movement').val(fetal_select);
            $('#fetal_movement').selectmenu();
            $('#fetal_movement').selectmenu('refresh', true);
            var contraction_db = data[pncc_id][client_id]['trimester'][client_visit_date]['contractions'];
            var contraction_select = select_decoder(contraction_db);
            $('#contractions').val(contraction_select);
            $('#contractions').selectmenu();
            $('#contractions').selectmenu('refresh', true);
            var headache_db = data[pncc_id][client_id]['trimester'][client_visit_date]['headaches'];
            var headache_select = select_decoder(headache_db);
            $('#headaches').val(headache_select);
            $('#headaches').selectmenu();
            $('#headaches').selectmenu('refresh', true);
            var swelling_db = data[pncc_id][client_id]['trimester'][client_visit_date]['swelling'];
            var swelling_select = select_decoder(swelling_db);
            $('#swelling').val(swelling_select);
            $('#swelling').selectmenu();
            $('#swelling').selectmenu('refresh', true);
            var bleeding_db = data[pncc_id][client_id]['trimester'][client_visit_date]['bleeding'];
            var bleeding_select = select_decoder(bleeding_db);
            $('#bleeding').val(bleeding_select);
            $('#bleeding').selectmenu();
            $('#bleeding').selectmenu('refresh', true);
            var pain_db = data[pncc_id][client_id]['trimester'][client_visit_date]['pain'];
            var pain_select = select_decoder(pain_db);
            $('#pain').val(pain_select);
            $('#pain').selectmenu();
            $('#pain').selectmenu('refresh', true);
            var infection_db = data[pncc_id][client_id]['trimester'][client_visit_date]['infections'];
            var infection_select = select_decoder(infection_db);
            $('#infections').val(infection_select);
            $('#infections').selectmenu();
            $('#infections').selectmenu('refresh', true);
            var infection_desc_db = data[pncc_id][client_id]['trimester'][client_visit_date]['infection_desc'];
            $('#what_infection').val(infection_desc_db);
            var assessment_date_db = data[pncc_id][client_id]['trimester'][client_visit_date]['home_visit_date'];
            if (assessment_date_db != null) {
            	var split_array = assessment_date_db.split('/');
            	var home_visit_date_split = split_array[2] + '-' + split_array[0] + '-' + split_array[1];
            	$('#home_visit_date').val(home_visit_date_split);	
            }      
            //Change weight status (gain/loss/unchange)
            var weight = Number($('#pounds').val()) + Number($('#ounces').val()) * 0.0625;
            $('.weight').on('change', function() {
                var new_pounds = $('#pounds').val();
                var new_ounces = $('#ounces').val();
                var new_weight = Number(new_pounds) + Number(new_ounces) * 0.0625;
                if(new_weight > weight) {
                    // $('#gain_loss').html('');
                    $('#gain_loss').remove();
                    $('#weight_status').append('<label for="gain_loss" id="gain_loss" style="margin-top: 5px">Weight gain</label>');
                }
                else if(new_weight < weight) {
                    $('#gain_loss').remove();
                    $('#weight_status').append('<label for="gain_loss" id="gain_loss" style="margin-top: 5px">Weight loss</label>');
                }
                else if(new_weight == weight) {
                    $('#gain_loss').remove();
                    $('#weight_status').append('<label for="gain_loss" id="gain_loss" style="margin-top: 5px">Weight unchanged</label>');
                }
                if(new_pounds.length > 0 || new_ounces.length > 0) {
                    $.ajax({
                        url:'http://192.168.1.110/~pingjietang/update_data.php',
                        data: {
                            n_pounds: new_pounds,
                            n_ounces: new_ounces
                        },
                        type:'POST',
                        dataType:'text',
                        success:Onsuccess
                    });    
                }
                function Onsuccess(data) {
                    // console.log("returned: " + data);
                }
            }); 
            $('#residence').val(data[pncc_id][client_id]['trimester'][client_visit_date]['residence']).change();
            residenceHelper(data[pncc_id][client_id]['trimester'][client_visit_date]['residence']);
            $('#utilities_working').val(data[pncc_id][client_id]['trimester'][client_visit_date]['utilities_working']).change();
            utilitiesHelper(data[pncc_id][client_id]['trimester'][client_visit_date]['utilities_working']);
            if (data[pncc_id][client_id]['trimester'][client_visit_date]['demo-checkbox-1a'] == 'true') {
                // $('#demo-checkbox-1a').prop('checked', true);
                $('#demo-checkbox-1a').trigger('click');
            }
            $('#demogra_back').attr('href', '#home-visit-page');
            $('#footer_1').show();
            $('#footer_2').hide();
        });
   
//A new trimester visit is triggered  
        $('#trim_plus_btn').on('click', function(event) {
            var trim_date = currentDate();
            var trimvisit_times = $('#trimvisit_id').children().length;
            if (trimvisit_times == 0) {//The very first trimester visit is ready to be inserted
                old_trim_visit_count++; 
                $('#trimvisit_id').append('<button class="trimester_date_class" id="old_trim_visit'+ old_trim_visit_count +'" value="'+trim_date+'">' + trim_date + '</button>'); 
            }
            if (trimvisit_times == 1) {
                if (document.getElementsByClassName('trimester_date_class')[0].value == trim_date) {//There is only one previous trimester visit and it is for today,you cannot add another new visit for today
                    document.getElementById("trim_plus_btn").disabled = true;
                }
                else {//There is one existing visit but not today
                   old_trim_visit_count++; 
                   $('#trimvisit_id').append('<button class="trimester_date_class" id="old_trim_visit'+ old_trim_visit_count +'" value="'+trim_date+'">' + trim_date + '</button>'); 
                }
            }
            else if (trimvisit_times > 1){
                var new_trimvisit_times = document.getElementsByClassName('trimester_date_class').length;
                if (document.getElementsByClassName('trimester_date_class')[new_trimvisit_times-1].value == document.getElementsByClassName('trimester_date_class')[new_trimvisit_times-2].value) { //Today's visit has already been created
                    document.getElementById("trim_plus_btn").disabled = true;
                } 
                else {
                    old_trim_visit_count++;
                    if(old_trim_visit_count > 3) {//Three times of trimester visit is the limit!
                        document.getElementById("trim_plus_btn").disabled = true;
                    } 
                    else {
                        $('#trimvisit_id').append('<button class="trimester_date_class" id="old_trim_visit'+ old_trim_visit_count +'" value="'+trim_date+'">' + trim_date + '</button>');      
                    }                   
                }                       
            }  
            event.preventDefault();  
            $('.ui-page').trigger('create');
            var id_name = "old_trim_visit" + old_trim_visit_count;
            $('#' + id_name).on ('click', function(e) {//Any trimester visit button is fired
                e.preventDefault();
                window.location.hash = "#demographic-page";
                var f_array = document.getElementsByTagName("FORM");
                for(var i = 1; i < f_array.length; i++) {
                    f_array[i].reset();
                }
                residenceHelper('No');
                utilitiesHelper('Yes');
                $('.nutri_class').hide();
                $('.item_serving').show();
                $('#footer_1').show();
                $('#footer_2').hide();
                
                var dob = data[pncc_id][client_id]['dob'];
                if (dob != null) {
                	var n = dob.split("/");
                	if (year >= n[n.length-1]) {
                    	var age = year - n[n.length-1];  
                    	$('#age_id').html(age);
                	}   	
                }
                         
                //Conversion to nutrition intake amount for new trimester assessment visit
                var milk_serving_a = Number($('#milk_cup').val());
                var milk_serving_b = Number($('#cheese').val())/1.5;
                var total_milk_serving = milk_serving_a + milk_serving_b;
                if(Math.round(total_milk_serving) < 3) {
                    $('#milk').val(Math.round(total_milk_serving));
                    $('#milk').css('color', 'red');
                }
                else {
                    $('#milk').val(Math.round(total_milk_serving));
                    $('#milk').css('color', 'green');
                }
                var meat_serving_a = Number($('#meat_ounce').val())/3;
                var meat_serving_b = Number($('#egg').val())/3;
                var meat_serving_c = Number($('#peanut').val())/2;
                var total_meat_serving = Math.round(meat_serving_a + meat_serving_b + meat_serving_c);
                if(total_meat_serving < 3) {
                    $('#meat').val(total_meat_serving);
                    $('#meat').css('color', 'red');
                }
                else {
                    $('#meat').val(total_meat_serving);
                    $('#meat').css('color', 'green');
                }
                var vege_serving_a = Number($('#salad').val());
                var vege_serving_b = Number($('#cooked_vege').val())*2;
                var vege_serving_c = (4*Number($('#salad').val()))/3;
                var total_vege_serving = Math.round(vege_serving_a + vege_serving_b + vege_serving_c);
                if(total_vege_serving < 4) {
                    $('#veg').val(total_meat_serving);
                    $('#veg').css('color', 'red');
                }
                else {
                    $('#veg').val(total_meat_serving);
                    $('#veg').css('color', 'green');
                }
                var h2o_serving = Number($('#h2o_glasses').val());
                var total_h2o_serving = Math.round(h2o_serving);
                if(total_h2o_serving < 7) {
                    $('#h2o').val(total_h2o_serving);
                    $('#h2o').css('color', 'red');
                }
                else {
                    $('#h2o').val(total_h2o_serving);
                    $('#h2o').css('color', 'green');
                }
                var fruit_serving_a = Number($('#medium_whole').val());
                var fruit_serving_b = Number($('#chopped').val())*2;
                var fruit_serving_c = Number($('#fruit_juice').val())*2;
                var fruit_serving_d = Number($('#dried_fruit').val())*4;
                var total_fruit_serving = Math.round(fruit_serving_a + fruit_serving_b + fruit_serving_c + fruit_serving_d);
                if(total_fruit_serving < 3) {
                    $('#fruit').val(total_fruit_serving);
                    $('#fruit').css('color', 'red');
                }
                else {
                    $('#fruit').val(total_fruit_serving);
                    $('#fruit').css('color', 'green');
                } 

                $('#submit_btn').on('click', function(e) {
                    upload_data = {};
                    var array = $('form').serializeArray();
                    upload_data["client_id"] = client_id;
                    upload_data["new_trimester_visit_date"] = $('#current_date_id').html();
                    //Insert an assessment_id (encounters table in DB) for a trimester visit
                    upload_data["which_assessmentvisit"] = old_trim_visit_count;
                    $.each(array, function() {  
                        if (upload_data[this.name] !== undefined) {
                            if (!upload_data[this.name].push) {
                                upload_data[this.name] = [upload_data[this.name]];
                            }
                            upload_data[this.name].push(this.value || '');
                        } 
                        else {
                            upload_data[this.name] = this.value || '';
                        }
                    });    
                    console.log(upload_data);

                    $.ajax({
                        url:'https://momlink.crc.nd.edu/~pingjie/upload_data.php',
                        data:JSON.stringify(upload_data),
                        type:'POST',
                        dataType:'json',
                        success: uploadSuccess,
                        error: function(err) {
                            console.log(err);
                        }
                    });  
                    function uploadSuccess(response) {
                        console.log(response);   
                    }           
                });     
            });         
        });

		//Load existing regular visits
		for (var key in data[pncc_id][client_id]['regular']) {//key corresponds to each existing regular visit 
			$('#reg_date').append('<button class="regular_date_class" id="old_reg_visit' + old_reg_visit_count +'">' + key + '</button>');	
		}
		//An old regular visit is triggered        
        $("button[id^='old_reg_visit']").on('click', function(e) {
            e.preventDefault();
            window.location = "#reg_visit";
            reg_visit_date = $(this).html();
            var reg_client_name = data[pncc_id][client_id]['reg_client_name'];
            $('#reg_client_name').val(reg_client_name);
            //Calculate gestation week
            var conception_date = data[pncc_id][client_id]['trimester'][client_visit_date]['conception_date'];
            var conception_date_split = null;
            if (conception_date != null) {
            	conception_date_split = conception_date.split('/');	
            }
            var month_diff;
            var day_diff;
            if (year == conception_date_split[2]) {
                month_diff = month - conception_date[0];
            } 
            else {
                month_diff = 12 - conception_date[0] + month;
            }
            day_diff = 31 - conception_date[1] + day;
            var gestation_week = (month_diff * 31 + day_diff) / 7;
            $('#gestation_week').val(gestation_week); 
            var next_reg_homevisit_date_db = data[pncc_id][client_id]['regular'][reg_visit_date]['next_homevisit_date'];
            if (next_reg_homevisit_date_db != null) {
                var split_reg_homevisit_date = next_reg_homevisit_date_db.split('/');
                var next_reg_homevisit_date = split_reg_homevisit_date[2] + '-' + split_reg_homevisit_date[0] + '-' + split_reg_homevisit_date[1];
                $('#next_reg_date').val(next_reg_homevisit_date);   
            }
            var narrative_notes = data[pncc_id][client_id]['regular'][reg_visit_date]['narrative_notes'];
            $('#narrative_notes').val(narrative_notes);
        });

//A new regular visit is triggered
        $('#reg_plus_btn').on('click', function() {
            $('#reg_next').on('click', function(event) {
                event.preventDefault();
                upload_data = {};
                var array = $('form#form_reg_visit').serializeArray();
                upload_data["client_id"] = client_id;
                upload_data["new_regular_visit_date"] = currentDate();
                upload_data["which_regularvisit"] = reg_assessment_id;
                $.each(array, function() {  
                    if (upload_data[this.name] !== undefined) {
                        if (!upload_data[this.name].push) {
                            upload_data[this.name] = [upload_data[this.name]];
                        }
                        upload_data[this.name].push(this.value || '');
                    } 
                    else {
                        upload_data[this.name] = this.value || '';
                    }
                });    
                console.log(upload_data);
                $.ajax({
                    url:'https://momlink.crc.nd.edu/~pingjie/upload_data.php',
                    data:JSON.stringify(upload_data),
                    type:'POST',
                    dataType:'json',
                    success: regUploadSuccess,
                    error: function(err) {
                        console.log(err);
                    }
                });  
                    function regUploadSuccess(response) {
                        console.log(response);   
                    }           
            });   
        });
        $('#demogra_back').attr('href','#home-visit-page');
    });

    function numVisits(obj) {
        var x = 0;
        for(var key in obj) {
            // alert(key); key include 'address','trimester' and 'regular'
            if(obj.hasOwnProperty(key)) {
                ++x;
            }
        }
        return x;
    }

    var new_visit_count = 1
    $('#new_clients_btn').on('click', function() {
        var f_array = document.getElementsByTagName("FORM");
        for(var i = 0; i < f_array.length; i++) {
            f_array[i].reset();
        }
        $('#demogra_back').attr('href', '#main-page');
        $('#footer_1').hide(); 
        $('#footer_2').show();         
    }); 

    $('.submit_stats').on('click', function(event) {
        alert("Submit Success!");
        var submit_button_id = null;
        $("button").click(function() {
            submit_button_id = this.id;
        });
        var json = {};
        var array = $('form').serializeArray();
        $.each(array, function() {
            if (json[this.name] !== undefined) {
                if (!json[this.name].push) {
                    json[this.name] = [json[this.name]];
                }
                json[this.name].push(this.value || '');
            } 
            else {
                json[this.name] = this.value || '';
            }
        });

        console.log(JSON.stringify(json, null, 2));

        isOffline = (navigator.network.connection.type == Connection.NONE);
        if(isOffline) {
            if(client_id === null) {//offline and wants to submit new patient data
                db.get(json_login['pncc-name'], function(err, doc) {
                    if(err) {
                        return console.log(err);
                    }
                    // NEW PATIENT ID
                    doc['patients']['new_patient_id'] = {
                        // 'home_visit_date': json
                    };
                    //check if the nurse submit complete/incomplete
                    if(submit_button_id === document.getElementsByClassName("submit_stats")[0].id){
                        db.put({
                            _id: json_login['pncc-name'],
                            _rev: doc._rev,
                            psw: json_login['psw'],
                            submit_stats: 1,
                            patients: doc
                        }, function(err, response) {
                            return console.log(err);
                        });  
                    }
                    if(submit_button_id === document.getElementsByClassName("submit_stats")[1].id) {
                        db.put({
                            _id: json_login['pncc-name'],
                            _rev: doc._rev,
                            psw: json_login['psw'],
                            submit_stats: 0,
                            patients: doc
                        }, function(err, response) {
                            return console.log(err);
                        });  
                    }
                });
            }
            else {
                if(today_visit_date === null) {//new home visit

                }
                else {

                }
            }

        }
        var postURL = null;
        if (client_id === null) { //New client
            // postURL = 'http://localhost/~pingjietang/add_data.php';
            postURL = 'http://54.152.10.110/add_data.php';
            
        }
        else { //old client
            if(today_visit_date === null) {//new home visit
                // postURL = 'http://localhost/~pingjietang/upload_data.php';
                postURL = 'http://54.152.10.110/upload_data.php';
            }
            else { //historic home visit
                // postURL = 'http://localhost/~pingjietang/update_data.php';
                postURL = 'http://54.152.10.110/update_data.php';
            }     
        };
        
        $.ajax({
            url: postURL,
            type: 'POST',
            data: json,
            dataType: 'text',
            success: submitSuccess
        });
        console.log(JSON.stringify(json, null, 2));
        
        function submitSuccess(server_response) {
            if (client_id === null) { //new client
                db.get(json_login['pncc-name'], function(err, doc) {
                    if (err) {
                        return console.log(err);
                    }
                    db.put({
                        _id: json_login['pncc-name'],
                        _rev:doc._rev,
                        psw: json_login['psw'],
                        patients: server_response,
                    }, function(err, response) {
                        if (err) {
                            return console.log(err);
                        }
                    });
                });
                
            }
        }
    });

    var monthNames = [
        "January", "February", "March",
        "April", "May", "June", "July",
        "August", "September", "October",
        "November", "December"
    ];
    var monthNamesNum = [
        "01", "02", "03",
        "04", "05", "06",
        "07", "08", "09",
        "10", "11", "12"
    ]
    var currentTime = new Date();
    var day = currentTime.getDate();
    var month = currentTime.getMonth(); 
    var year = currentTime.getFullYear();
    $("#date").html(monthNames[month] + ' ' + day + ' ' + year);
    $('#current_date_id').html(monthNamesNum[month] + '/' + day + '/' + year);
    $('#reg_visit_date').html(monthNamesNum[month] + '/' + day + '/' +year);

    $('#residence').on('change', function(e) {
        var valueSelected = this.value;
        residenceHelper(valueSelected);
    });

    residenceHelper = function($value) {
        if($value == 'Yes') {
            $('#moved_id').show();
            $('#utilities_id').show()
            $('#demographic-ref-id').show();          
        }
        else if($value == 'No') {
            $('#moved_id').hide();
            $('#address_id').hide();
            $('#utilities_id').hide();
            $('#demographic-ref-id').hide();
        }
    };

    $('#utilities_working').on('change', function(e) {
        var valueSelected = this.value;
        utilitiesHelper(valueSelected);
    });

    utilitiesHelper = function($value) {
        if($value == 'No') {
            $('#demographic-ref-id').show();
        }
        else if($value == 'Yes') {
            $('#demographic-ref-id').hide();
        }
    };

    $('#moved').on('change', function(e) {
        //var optionSelected = $("option:selected", this);
        var valueSelected = this.value;
        if(valueSelected == 'Yes') {
            $('#address_id').show(); 
        }
        else {
            $('#address_id').hide();
        }
    });

    $('#fob').on('change', function(e) {
        var valueSelected = this.value;
        if(valueSelected == 'No') {
            $('#demographic-ref2-id').show();
        }
        else {
            $('#demographic-ref2-id').hide();
        }
    });

    $("#med_provider").on('change', function(e) {
        var valueSelected = this.value;
        if(valueSelected == 'Yes') {
            $("#clinic_addr").show();
        }
        else {
            $("#clinic_addr").hide();
        }
    });

    $('#wic').on('change', function(e) {
        var valueSelected = this.value;
        if(valueSelected == 'Appt Scheduled') {
            $('#wic_appt_id').show();
        }
        else {
            $('#wic_appt_id').hide();
        }
    });

    // $('#has_appt').on('change', function(e) {
    //     var valueSelected = this.value;
    //     if(valueSelected == 'Yes') {
    //         $('#wic_appt_id').show();
    //     }
    //     else {
    //         $('#wic_appt_id').hide();
    //     }
    // });

    $('#adequate_food_supply').on('change', function(e) {
        var valueSelected = this.value;
        if(valueSelected == 'Yes') {
            $('#need_help').hide();
            $('#food_stamps_id').hide();
            $('#tanf').hide();
            $('#nutrition-ref-id').hide();
        }
        else {
            $('#need_help').show();
            $('#nutrition-ref-id').show();
            $('#food_issue_select').on('change', function(e){
                var value_select = this.value;
                if(value_select == 'Yes') {
                    $('#food_stamps_id').show();
                    $('#tanf').show();
                }
                else {
                    $('#food_stamps_id').hide();
                    $('#tanf').hide();
                }
            });
        }
    });

    $('#medication_select').on('change', function(e) {
        var valueSelected = this.value;
        if(valueSelected == 'Yes') {
            $('#medication_name_id').show();
            $('#medication_reason_id').show();
        }
        else {
            $('#medication_name_id').hide();
            $('#medication_reason_id').hide();
        }
    });

    $('#pnv_select').on('change', function(e) {
        var valueSelected = this.value;
        if(valueSelected == 'Yes') {
            $('#pnv_problem_id').show();
        }
        else {
            $('#pnv_problem_id').hide();
        }
    });

    $('#smoking_select').on('change', function(e) {
        var valueSelected = this.value;
        if(valueSelected == 'Yes') {
            $('#smoking-ref-id').show();
        }
        else {
            $('#smoking-ref-id').hide();
        }
    });

    $('.condition_info').on('change', function() {
        var condition_counter = 0;
        $('.condition_info').each(function() {
            if (this.value == 'No' || this.value == '') {
                condition_counter += 1;
            };
        });
        if (condition_counter == 7) {
            $('#condition-ref-id').hide();
        }
        else {
            $('#condition-ref-id').show();
        }
    });

    $("#infections").on('change', function(e){
        var valueSelected = this.value;
        if(valueSelected == 'Yes') {
            $('#other_desc').show();
        }
        else {
            $('#other_desc').hide();
        }
    });

   $('#dropdown_insurance').on('change', function() {
        var optionSelected = $("option:selected", this);
        var valueSelected = this.value;
        if(valueSelected == "Private") {
            $('#insurance_name_id').show();
        }
        else {
            $('#insurance_name_id').hide();
        }
        if(valueSelected == "Medicate") {
            $('#medicate_id').show();
        }
        else {
            $('#medicate_id').hide();
        }
        if(valueSelected == "Qualifies for medicate") {
            $('#med_qualify').show();
        }
        else {
            $('#med_qualify').hide();
        }
        if(valueSelected == "Does not qualify for medicate") {
            $('#unqual_reason_id').show();
        }
        else {
            $('#unqual_reason_id').hide();
        }
   });

    $('input[type="checkbox"]').on('change', function() {
        if($(this).is(':checked')) {
            $('#summary-page-content').append('<p>' + $(this).prev('label').text() + '</p>');
        }
    });    
});