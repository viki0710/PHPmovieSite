<!DOCTYPE html>
<html>

<head>
    <title>
        PHP Practice
    </title>
    <link rel="stylesheet" href="assets/styles/style.css">
    <script type="text/javascript" src="assets/scripts/script.js">
    </script>
    <?php
        // SETTING VARIABLES
        $MODE = check_parameters("MODE");
        $NEXT_MODE = "";
        $TITLE = "MOVIE TRACKER";
        $REGISTER = "Register";
        $LOGIN = "Login";
        $LOGOUT = "Logout";
        $ACCOUNT = "Account";
        $EMAIL = check_parameters("EMAIL");
        $ID = 0;
        $USERNAME = check_parameters("USERNAME");
        $PASSWORD = check_parameters("PASSWORD");
        $CONFIRM_PASSWORD = check_parameters("CONFIRM_PASSWORD");
        // MULTI-PURPOSE USE TO CHECK IF FORM SUCCESSFULLY POSTED
        $POSTED = [false, [""]];
        $REGISTERED = false;
        $LOGGED_IN = check_parameters("LOGGED_IN");
        $SERIES_NUMBER = check_parameters("SERIES_NUMBER");
        $EPISODE_NUMBER = check_parameters("EPISODE_NUMBER");
        $ADD_TITLE = check_parameters("ADD_TITLE");
        $ADD_YEAR = check_parameters("ADD_YEAR");
        $ADD_DATE = check_parameters("ADD_DATE");
        $ADD_RATING = check_parameters("ADD_RATING");
        $ADD_DESCRIPTION = check_parameters("ADD_DESCRIPTION");
        $ADD_IMG_URL = check_parameters("ADD_IMG_URL");
        $PAGE = check_parameters("PAGE");

        // FILE VARIABLES
        define('USERS_FILE', './assets/users.json');
        define('SERIES_FILE', './assets/series.json');
        define('EPISODES_FILE', './assets/episodes.json');

        // FUNCTIONS

        function print_success($expression){
            return "<span class='approve'>".$expression."</span>";
        }

        function print_failure($expression){
            return "<span class='alert'>".$expression."</span>";
        }

        function echo_invisible(){
            echo "style='display:none;'";
        }

        function check_parameters($name){
            // echo print_success($name." : ".isset($_POST[$name])."<br>");
            // echo print_success($name." : ".empty($_POST[$name])."<br>");

            if(isset($_POST[$name])){
                //posted
                if(empty($_POST[$name])){
                    //posted but empty
                }else{
                    //posted and has value
                    return $_POST[$name];
                }
            }else{
                //not even posted
            }
            return "";
        }

        function require_component($name, $return_mode="echo"){
            // echo print_success("isset : ".isset($GLOBALS["_POST"][$name]));
            // echo print_success("empty : ".empty($GLOBALS["_POST"][$name]));
            if($return_mode == "echo"){
                if(isset($GLOBALS["_POST"][$name]) && empty($GLOBALS["_POST"][$name])){
                    // UNIQUE ERROR MESSAGES
                    if($name == "CONFIRM_PASSWORD") $name = "a matching PASSWORD";
                    if($name == "ADD_IMG_URL") $name = "A URL";
                    // IF ADD_[WHATEVERNAME] -> REMOVE ADD AND MAKE IT: [WHATEVERNAME]
                    if(str_contains($name, "ADD_")) $name = str_replace('ADD_', '', $name);
                    echo print_failure($name." is required!");
                }
            }else if($return_mode == "string"){
                if(isset($GLOBALS["_POST"][$name]) && !empty($GLOBALS["_POST"][$name])) return "POSTED_GOOD";
                if(isset($GLOBALS["_POST"][$name]) && empty($GLOBALS["_POST"][$name])) return "POSTED_EMPTY";
                else return "NOT_POSTED";
            }
        }

        function verify_email($name){
            // pattern="^[a-zA-Z0-9.!#$&'*+/=?^_`{|}~-]+@[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(?:\.[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)+$"
            // JUST MAKING SURE THAT THE VALUE IS ACTUALLY POSTED
            if(require_component($name, "string") == "POSTED_GOOD"){
                $email_arr = explode('@', $_POST[$name]);
                // COMPONENTS TO CHECK
                // 1. count($email_arr) == 2 || MAKE SURE THE EMAIL IS SPLIT TO TWO BETWEEN "@" JUST ONCE SO FORMAT WOULD BE ____@____ HERE.
                // 2. strlen($email_arr[0]) > 0 || MAKE SURE STRING IS NOT EMPTY (EMAIL EXISTS)
                // 3. strlen($email_arr[1]) > 0 || MAKE SURE STRING IS NOT EMPTY (DOMAIN EXISTS)
                // $domain_arr = explode('.', $email_arr[1]); || SPLIT THE DOMAIN BY "."
                // 4. count($domain_arr) == 2 || MAKE SURE THE DOMAIN IS SPLIT TO TWO BETWEEN "." JUST ONCE SO FORMAT WOULD BE ____@____.___ HERE.
                // 5. strlen($domain_arr[0]) > 0 || MAKE SURE STRING IS NOT EMPTY (DOMAIN NAME EXISTS)
                // 6. strlen($domain_arr[1]) > 0 || MAKE SURE STRING IS NOT EMPTY (TOP LEVEL DOMAIN EXISTS)

                if(count($email_arr) != 2) return false;
                if(strlen($email_arr[0]) == 0 || strlen($email_arr[1]) == 0) return false;
                $domain_arr = explode('.', $email_arr[1]);
                if(count($domain_arr) != 2) return false;
                if(strlen($domain_arr[0]) == 0 || strlen($domain_arr[1]) == 0) return false;
                // IF NO ERROR, REQUIREMENTS MET.
                return true;

            }

            return false;
        }

        function is_admin(){
            global $LOGGED_IN; global $USERNAME; global $PASSWORD;
            if($LOGGED_IN && $USERNAME == encode("admin") && $PASSWORD == get_password($USERNAME)) return true;
            else return false;
        }
        
        function write_on_file($fname, $contents, $expression1 = "SUCCESS!", $expression2 = "FAILED!"){
            if(file_put_contents($fname, $contents)){
                echo print_success($expression1);
            }else{
                echo print_success($expression2);
            }
        }

        function alert_log($expression){
            echo "<script> alert('".$expression."'); </script>";
        }

        function encode($str){
            return $str;
        }

        function get_json_arr($fpath){
            $json_contents = file_get_contents($fpath);
            $json_arr = json_decode($json_contents, true);
            return $json_arr;
        }

        function login(){
            // AFTER REGISTRATION WE WANT THE USER TO AUTOMATICALLY LOGIN.
            $expression1="LOGIN SUCCESS!";
            $expression2="Email or Username not found!";
            $expression3="Password is wrong!";
            global $USERNAME; global $PASSWORD;
            if($USERNAME != "" && $PASSWORD != ""){
                // CHECK THE USERS DATA
                global $LOGGED_IN; global $ID;
                $user_arr = get_json_arr(USERS_FILE);
                // CHECKS IF THERE IS NO DUPLICATION IN EMAIL AND USERNAME
                // THIS WILL BE USED WHEN LOGGING IN TOO
                $found = false;
                foreach($user_arr['data'] as $ud){
                    if($ud['username'] == $USERNAME || $ud['email'] == $USERNAME){
                        $found = true;
                        if($ud['password'] == $PASSWORD){
                            $LOGGED_IN = true;
                            $ID = $ud['id'];
                            global $REGISTERED;
                            $REGISTERED = true;
                            echo print_success($expression1." ".return_home(true));
                        }else{
                            echo print_failure($expression3);
                        }
                        break;
                    }
                }
                if(!$found) echo print_failure($expression2);
                // if(!$LOGGED_IN && $msg) echo print_failure($expression2);
            }else{
                if($USERNAME == "" && $PASSWORD != "") echo print_failure("Password is wrong!");
                else if($USERNAME != "" && $PASSWORD == "") echo print_failure($expression2);
            }
        }

        function get_password($identifier){
            $user_arr = get_json_arr(USERS_FILE);
            foreach($user_arr['data'] as $ud){
                if($ud['username'] == $identifier || $ud['email'] == $identifier){
                    return $ud['password'];
                }
            }
            // COULDN'T FIND USERNAME OR EMAIL
            return false;
        }

        function return_home($return_str=false){
            $str = "<span>RETURNING IN <span id='return_sec'></span> SECONDS...</span><script type='text/javascript' src='assets/scripts/return.js' defer></script>";
            if($return_str) return $str;
            else echo $str;
        }

        function find_series_by_id($arr, $id){
            $return_arr = array();
            if($arr && $id){
                foreach($arr as $v){
                    // echo var_dump($v);
                    if($v["series_id"] == $id){
                        $return_arr[] = $v;
                    }
                }
                return $return_arr;
            }else echo print_failure("Please pass correct parameter!");
            return false;
        }

        function find_episode_index_by_id($arr, $series_id, $episode_id){
            $index = 0;
            if($arr && $series_id && $episode_id){
                foreach($arr as $v){
                    if($v["series_id"] == $series_id && $v["episode_id"] == $episode_id){
                        return $index;
                    }
                    $index++;
                }
            }else echo print_failure("Please pass correct parameter!");
            return false;
        }

        // "r"eturn "t"rue "i"f "t"rue -> rtit
        function rtit($expressions){
            foreach($expressions as $expression){
                if($expression) return true;
            }
            return false;
        }

        function rfif($expressions){
            foreach($expressions as $expression){
                if(!$expression) return false;
            }
            return true;
        }

        function complex_rfif($expressions){
            $new_return = [true, []];
            foreach($expressions as $expression){
                if(!$expression[0]){
                    $new_return[0] = false;
                    $new_return[1][] = $expression[1];
                }
            }
            return $new_return;
        }

        function limit($min=false, $num=false, $max=false){
            // ONLY 2 VALUES GIVEN
            if(!$max){
                if($min > $num) return $num;
                else return $min;
            }
            // 3 VALUES GIVEN
            else{
                if($num > $max) return $max;
                else if($num < $min) return $min;
                else return $num;
            }
        }

        // IF MODE IS LOGOUT
        if($MODE == "LOGOUT"){
            $MODE = "";
            $LOGGED_IN = false;
            $USERNAME = "";
            $PASSWORD = "";
        }

        // IF MODE IS EMPTY ITS MAIN INDEX
        if($MODE == ""){
            // READ THE SERIES DATA
            if(file_exists(SERIES_FILE)){
                $SERIES = get_json_arr(SERIES_FILE)["data"];
                $SERIES = array_reverse($SERIES);
            }else{
                $SERIES = array();
            }
        }
    ?>
</head>
<body>
    <!-- HEADER -->
    <header class="flex">
        <nav class="flex justify-end white">
            <!-- TITLE -->
            <h4 class="clickable button margin-vertical margin-right"><?php echo $TITLE; ?></h4>
            <?php if(!$LOGGED_IN): ?>
            <!-- REGISTER BUTTON -->
            <div id="register" class="flex clickable button">
                <img class="invert margin-vertical margin-left" src="assets/images/register.png" />
                <span class="margin-vertical margin-right"><?php echo $REGISTER; ?></span>
            </div>
            <!-- LOGIN BUTTON -->
            <div id="login" class="flex clickable button">
                <img class="invert margin-vertical margin-left" src="assets/images/login.png" />
                <span class="margin-vertical margin-right"><?php echo $LOGIN; ?></span>
            </div>
            <?php endif; if($LOGGED_IN): ?>
            <!-- LOGOUT -->
            <div id="logout" class="flex clickable button">
                <img class="invert margin-vertical margin-left" src="assets/images/logout.png" />
                <span class="margin-vertical margin-right"><?php echo $LOGOUT; ?></span>
            </div>
            <!-- ACCOUNT BUTTON -->
            <div id="account" class="flex clickable button">
                <img class="invert margin-vertical margin-left" src="assets/images/account.png" />
                <span class="margin-vertical margin-right"><?php echo $ACCOUNT; ?></span>
            </div>
            <?php endif; ?>
        </nav>
    </header>
    <!-- MOTHER BOX. THIS WORKS LIKE BODY -->
    <div class="mother-box flex column">
        <?php
            // MODE = EMPTY = TITLE PAGE, LOAD START
            if($MODE == ""):
        ?>
        <!-- HOME START -->
        <div id="home-box">
            <!-- TOP SECTION -->
            <div class="top-box flex column" style="background-color: var(--placeholder-color); background-image: url(https://miro.medium.com/max/1400/1*P6N-6gs3BAc2ne-gXSdh7Q.jpeg);">
                <!-- SPACER -->
                <div class="spacer"></div>
                <!-- TEXT BOX -->
                <div class="top-text-box flex column margin">
                    <h1 class="title-text observable">
                        <?php echo $TITLE; ?>
                    </h1>
                    <h5 class="sub-text observable">
                        Website made by Viktoria Balla
                    </h5>
                </div>
                <!-- DOWN ARROW -->
                <div class="arrow down"></div>
            </div>
            <!-- SERIES -->
            <div class="series-box flex column">
                <?php if($LOGGED_IN && $USERNAME == encode("admin") && $PASSWORD == get_password($USERNAME)): ?>
                    <!-- [HOME] ADMIN ONLY (ADD SERIES) START -->
                    <form method="post" id="home-add-series" class="add-series-episode flex column margin-side margin-bottom" novalidate>
                        <input name="MODE" value="ADD_SERIES" class="invisible" />
                        <input name="LOGGED_IN" value="<?php echo $LOGGED_IN; ?>" class="invisible" />
                        <input name="USERNAME" value="<?php echo $USERNAME; ?>" class="invisible" />
                        <input name="PASSWORD" value="<?php echo $PASSWORD; ?>" class="invisible" />
                        <input type="submit" value="ADD SERIES" class="add-series-episode-button" />
                    </form>
                    <!-- [HOME] ADMIN ONLY (ADD SERIES) END -->
                <?php endif; ?>
                <?php
                    if(empty($PAGE)) $PAGE = 1;
                    $MAX_PAGES = ceil(count($SERIES)/5);
                    echo "<h1>".$PAGE." out of ".$MAX_PAGES." pages</h1>";
                    for($i = 5*($PAGE-1); $i < limit((5*($PAGE-1))+5, count($SERIES)); $i++):
                        $s = $SERIES[$i];                        
                ?>
                <div class="observable series flex clickable card white"
                    style="background-image: url(<?php echo $s['img_url'] ?>)"
                    id="series<?php echo $s['series_id'] ?>">
                    <!-- TITLE -->
                    <h2 class="series-title-text flex justify-center grow"><?php echo $s['title']; ?></h2>
                    <button class="arrow right dark margin"></button>
                </div>
                <?php
                    echo "<script type='text/javascript'> setSeriesDetail(".$s['series_id'].") </script>";
                    endfor;
                    unset($s); unset($i);
                    if($PAGE > 1):
                ?>
                <form method="post" class="flex column" novalidate>
                    <?php if($LOGGED_IN): ?>
                        <input name="USERNAME" value="<?php echo $USERNAME; ?>" class="invisible" />
                        <input name="PASSWORD" value="<?php echo $PASSWORD; ?>" class="invisible" />
                        <input name="LOGGED_IN" value="<?php echo $LOGGED_IN; ?>" class="invisible" />
                    <?php endif; ?>
                    <input name="PAGE" value="<?php echo $PAGE - 1; ?>" class="invisible" />
                    <input type="submit" class="add-series-episode-button" value="PREVIOUS 5 SERIES" />
                </form>
                <?php
                    endif;
                    if($PAGE < $MAX_PAGES):
                ?>
                <form method="post" class="flex column" novalidate>
                    <?php if($LOGGED_IN): ?>
                        <input name="USERNAME" value="<?php echo $USERNAME; ?>" class="invisible" />
                        <input name="PASSWORD" value="<?php echo $PASSWORD; ?>" class="invisible" />
                        <input name="LOGGED_IN" value="<?php echo $LOGGED_IN; ?>" class="invisible" />
                    <?php endif; ?>
                    <input name="PAGE" value="<?php echo $PAGE + 1; ?>" class="invisible" />
                    <input type="submit" class="add-series-episode-button" value="NEXT 5 SERIES" />
                </form>
                <?php
                    endif;
                ?>
            </div>
        </div>
        <!-- HOME END -->
        <?php
            endif;
            // MODE = EMPTY = TITLE PAGE, LOAD END
            // MODE: REGISTER = REGISTER PAGE, LOGIN = LOGIN PAGE, ADD_SERIES = ADD SERIES PAGE, ADD_EPISODE = ADD EPISODE PAGE
            if($MODE == "REGISTER" || $MODE == "LOGIN" || $MODE == "ADD_SERIES" || $MODE == "ADD_EPISODE"):            
        ?>
        <!-- MULTIPURPOSE-FORMS START -->
        <div class="multipurpose-forms-box card margin flex column">
            <h1 class="title-text margin-side white"><?php echo $MODE; ?></h1>
            <form method="post" id="multipurpose-forms" class="flex column margin-side margin-bottom" novalidate>
                <input name="MODE" value="<?php echo $MODE; ?>" class="invisible" />

                <?php

                    // VALIDATES THE FORM.
                    // $varr IS USED FOR THE VALIDATION WETHER ALL REQUIRED COMPONENTS HAVE INPUT OR NOT
                    // THE REST IS FOR CORRECT VALIDATION FOR EACH $MODE
                    
                    // FOR $varr
                    $varr;
                    switch($MODE){
                        case 'REGISTER': $varr = ["EMAIL", "USERNAME", "PASSWORD", "CONFIRM_PASSWORD"]; break;
                        case 'LOGIN': $varr = ["USERNAME", "PASSWORD"]; break;
                        case 'ADD_SERIES': $varr = ["ADD_TITLE", "ADD_YEAR", "ADD_DESCRIPTION", "ADD_IMG_URL"]; break;
                        case 'ADD_EPISODE': $varr = ["ADD_TITLE", "ADD_DATE", "ADD_DESCRIPTION", "ADD_RATING"]; break;
                    }
                    // IF ANYTHING IS POSTED EMPTY, PRINT ERROR MESSAGE
                    if(rtit(array_map(function($n){ return require_component($n, "string") == "POSTED_EMPTY"; }, $varr))) echo print_failure("PLEASE INPUT ALL FIELDS!");
                    else if(rtit(array_map(function($n){ return require_component($n, "string") == "NOT_POSTED"; }, $varr))){}
                    else{
                        // FOR $narr. MAKES AN ARRAY TO CHECK IF EVERYTHING IS POSTED GOOD
                        $narr = array_map(function($n){ return [require_component($n, "string") == "POSTED_GOOD", ""]; }, $varr);
                        // SPECIAL CONDITIONS.
                        switch($MODE){
                            case 'REGISTER':
                                // WE WON'T SHOW ERROR MESSAGE BECAUSE THE ERROR IS CHECKED AND PRINTED IN THE <FORM> (DUE TO FORMAT ISSUE)
                                // EMAIL NEEDS TO BE IN FORM
                                $narr[] = [verify_email("EMAIL"), ""];
                                // PASSWORD AND CONFIRM_PASSWORD SHOULD MATCH
                                $narr[] = [$PASSWORD == $CONFIRM_PASSWORD, ""];
                                break;
                            case 'ADD_SERIES': $narr[] = [is_admin(), "user not admin!"]; break;
                            case 'ADD_EPISODE':
                                $narr[] = [is_admin(), "user not admin!"];
                                // ADD_RATING SHOULD BE FLOAT OF (0 <= $ADD_RATING <= 10)
                                $narr[] = [($ADD_RATING <= 10 && $ADD_RATING >= 0), "rating must be within 0 ~ 10!"];
                                break;
                        }
                        // IF ALL GOOD, APPROVED ($POSTED = true)
                        $POSTED = complex_rfif($narr);
                        unset($narr);
                    }
                    unset($varr); 
                ?>

                <?php if($MODE == "REGISTER"): ?>
                <input name="EMAIL" 
                    type="email" 
                    placeholder="email"
                    value="<?php echo $EMAIL ?>"
                    class="<?php if($MODE != "REGISTER" && $MODE != "LOGIN") echo "invisible"; ?>"
                />
                <?php 
                    if($MODE == "REGISTER" || $MODE == "LOGIN"){
                        if(require_component("EMAIL", "string") == "POSTED_EMPTY") require_component("EMAIL");
                        elseif(require_component("EMAIL", "string") == "POSTED_GOOD" && !verify_email("EMAIL")) echo print_failure("Please input a valid email format!");
                    } 
                    endif; 
                ?>

                <input name="USERNAME" type="text"
                    placeholder="<?php 
                        if($MODE == 'REGISTER') echo 'username';
                        else echo 'email or username'; 
                    ?>" 
                    value="<?php echo $USERNAME ?>" 
                    class="<?php if($MODE != "REGISTER" && $MODE != "LOGIN") echo "invisible"; ?>"
                />
                <?php if($MODE == "REGISTER" || $MODE == "LOGIN") require_component("USERNAME"); ?>
                
                <input name="PASSWORD" type="password" placeholder="new password" value="<?php echo $PASSWORD ?>" class="<?php if($MODE != "REGISTER" && $MODE != "LOGIN") echo "invisible"; ?>" />
                <?php if($MODE == "REGISTER" || $MODE == "LOGIN") require_component("PASSWORD"); ?>

                <?php if($MODE == "REGISTER"): ?>
                <input name="CONFIRM_PASSWORD" type="password" placeholder="confirm password" value="<?php echo $CONFIRM_PASSWORD ?>" class="<?php if($MODE != "REGISTER" && $MODE != "LOGIN") echo "invisible"; ?>" />
                <?php 
                    if(require_component("CONFIRM_PASSWORD", "string") == "POSTED_GOOD" && $PASSWORD != $CONFIRM_PASSWORD) echo print_failure("Password does not match!");
                    else require_component("CONFIRM_PASSWORD");
                    endif; 
                ?>

                <?php 
                    if($MODE == "ADD_SERIES" || $MODE == "ADD_EPISODE"):
                ?>
                    <input name="LOGGED_IN" value="<?php echo $LOGGED_IN; ?>" class="invisible" />

                    <?php if($MODE == "ADD_EPISODE"): ?>
                    <input name="SERIES_NUMBER" value="<?php echo $SERIES_NUMBER; ?>" class="invisible" />
                    <?php endif; ?>

                    <input name="ADD_TITLE"
                    placeholder="title of the <?php if($MODE == 'ADD_SERIES') echo 'series'; else if($MODE == 'ADD_EPISODE') echo 'episode'; ?>" 
                    value="<?php echo $ADD_TITLE; ?>" />
                    <?php require_component("ADD_TITLE"); ?>
                    
                    <?php if($MODE == "ADD_SERIES"): ?>
                    <input name="ADD_YEAR" type="number" placeholder="year published" value="<?php echo $ADD_YEAR; ?>" />
                    <?php require_component("ADD_YEAR"); endif; ?>

                    <?php if($MODE == "ADD_EPISODE"): ?>
                    <input name="ADD_DATE" placeholder="date broadcasted" value="<?php echo $ADD_DATE; ?>" />
                    <?php require_component("ADD_DATE"); endif; ?>
                    
                    <input name="ADD_DESCRIPTION" value="<?php echo $ADD_DESCRIPTION; ?>" placeholder="description" />
                    <?php require_component("ADD_DESCRIPTION"); ?>

                    <?php if($MODE == "ADD_EPISODE"): ?>
                    <input name="ADD_RATING" placeholder="rating" type="number" value="<?php echo $ADD_RATING; ?>" step="0.1" />
                    <?php require_component("ADD_RATING"); endif; ?>
                    
                    <?php if($MODE == "ADD_SERIES"): ?>
                    <input name="ADD_IMG_URL" value="<?php echo $ADD_IMG_URL; ?>" placeholder="thumbnail image url" />
                    <?php require_component("ADD_IMG_URL"); endif; ?>

                <?php
                    endif;
                    // THIS CODE IS PLACED HERE FOR ERROR MESSAGE PLACEMENT
                    if($POSTED[0]){
                        // FOR LOGIN PAGE
                        if($MODE == "LOGIN") login();
                        // FOR REGISTER PAGE
                        elseif($MODE == "REGISTER"){
                            $REGISTERED = true;
                            // ENCODE USERNAME AND PASSWORD
                            $USERNAME = encode($USERNAME);
                            $PASSWORD = encode($PASSWORD);
                            // IF PASSWORDS MATCH = GO!
                            // GENERATE DATA TO REGISTER
                            $REGISTER_DATA = array(
                                "id" => 0,
                                "email" => $EMAIL,
                                "username" => $USERNAME,
                                "password" => $PASSWORD
                            );
    
                            // GETS THE USERS.JSON. IF EXIST, WRITE ON IT. OTHERWISE, GENERATE FILE
                            if(file_exists(USERS_FILE)){
                                // GET FILE CONTENTS AND DECODE JSON TO KEY-VALUE ARRAY
                                $USER_ARR = get_json_arr(USERS_FILE);
    
                                // SET THE ID
                                $REGISTER_DATA['id'] = end($USER_ARR['data'])["id"] + 1;
    
                                // CHECKS IF THERE IS NO DUPLICATION IN EMAIL AND USERNAME
                                foreach($USER_ARR['data'] as $ud){
                                    // MAKE SURE THERE IS NO DUPLICATION IN EMAIL AND USERNAME
                                    // IF TRUE, THERE IS DUPLICATION = CAUSE ERROR ($REGISTERED = false)
                                    if($ud['email'] == $REGISTER_DATA['email'] || $ud['username'] == $REGISTER_DATA['username']){
                                        $REGISTERED = false;
                                        echo print_failure("username or email already used!");
                                        break;
                                    }
                                    
                                }
                                unset($ud);
                            }else $USER_ARR['data'] = array("data" => array());
                            
                            // STORES THE DATA
                            if($REGISTERED){
                                $USER_ARR['data'][] = $REGISTER_DATA;
                                $NEXT_MODE = "LOGIN";
                                write_on_file(
                                    USERS_FILE,
                                    json_encode($USER_ARR, JSON_PRETTY_PRINT),
                                    "REGISTERED!<br>".return_home(true),
                                    "SOMETHING WENT WRONG... PLEASE TRY AGAIN!"
                                );
                            }
                        }elseif($MODE == "ADD_SERIES"){
                            $REGISTER_DATA = array(
                                "series_id" => 0,
                                "title" => $ADD_TITLE,
                                "year" => intval($ADD_YEAR),
                                "description" => $ADD_DESCRIPTION,
                                "img_url" => $ADD_IMG_URL
                            );
                            if(file_exists(SERIES_FILE)){
                                // GET FILE CONTENTS
                                $SERIES_ARR = get_json_arr(SERIES_FILE);
                                // CHECKS IF THERE IS TITLE DUPLICATION
                                $same_file_exists = false;
                                foreach($SERIES_ARR["data"] as $s){
                                    if($ADD_YEAR == $s["title"]) $same_file_exists = true;
                                }
                                if(!$same_file_exists){
                                    $REGISTER_DATA['series_id'] = end($SERIES_ARR["data"])["series_id"] + 1;
                                    $SERIES_ARR["data"][] = $REGISTER_DATA;
                                    write_on_file(
                                        SERIES_FILE,
                                        json_encode($SERIES_ARR, JSON_PRETTY_PRINT),
                                        "REGISTERED!<br>".return_home(true),
                                        "SOMETHING WENT WRONG... PLEASE TRY AGAIN!"
                                    );
                                }else echo print_failure("SAME TITLE ALREADY EXISTS!");
                                unset($same_file_exists); unset($s);
                            }
                        }elseif($MODE == "ADD_EPISODE"){
                            $REGISTER_DATA = array(
                                "episode_id" => 0,
                                "series_id" => intval($SERIES_NUMBER),
                                "title" => $ADD_TITLE,
                                "date" => $ADD_DATE,
                                "description" => $ADD_DESCRIPTION,
                                "rating" => floatval($ADD_RATING)
                            );
                            if(file_exists(EPISODES_FILE)){
                                // GET FILE CONTENTS
                                $EPISODES_ARR = get_json_arr(EPISODES_FILE);
                                $REGISTER_DATA['episode_id'] = end(find_series_by_id($EPISODES_ARR["data"], $SERIES_NUMBER))["episode_id"] + 1;
                                $EPISODES_ARR['data'][] = $REGISTER_DATA;
                                $NEXT_MODE = "SERIES";
                                write_on_file(
                                    EPISODES_FILE,
                                    json_encode($EPISODES_ARR, JSON_PRETTY_PRINT),
                                    "REGISTERED!<br>".return_home(true),
                                    "SOMETHING WENT WRONG... PLEASE TRY AGAIN!"
                                );
                            }
                        }   
                    }else{
                        foreach($POSTED[1] as $err_msg){
                            if(!empty($err_msg)) echo print_failure($err_msg);
                        }
                    }
                ?>
                <input type="submit" <?php if((($MODE == "LOGIN" || $MODE == "REGISTER") && ($REGISTERED || $LOGGED_IN)) || (($MODE == "ADD_SERIES" || $MODE == "ADD_EPISODE") && ($POSTED[0]))) echo "disabled"; ?> />
            </form>
        </div>
        <!-- MULTIPURPOSE-FORMS END -->
        <?php 
            endif;
            if($MODE == "ACCOUNT"):
        ?>
        <!-- ACCOUNT START -->
        
        <!-- ACCOUNT END -->
        <?php
            endif;
            if($MODE == "SERIES"):
                // CHECKS IF ALL POSTED CORRECTLY
                $varr = ["SERIES_NUMBER", "EPISODE_NUMBER", "ADD_TITLE", "ADD_DATE", "ADD_RATING", "ADD_DESCRIPTION"];
                // IF NOT POSTED DON'T CAUSE ANY ERROR
                if(rtit(array_map(function($n){ return require_component($n, "string") == "NOT_POSTED"; }, $varr))){}
                else{
                    // FOR $narr. MAKES AN ARRAY TO CHECK IF EVERYTHING IS POSTED GOOD
                    $narr = array_map(
                        function($n){
                            return [
                                require_component($n, "string") == "POSTED_GOOD", 
                                str_contains($n, "ADD_") ? str_replace('ADD_', '', $n)." needs to be in correct format!" : $n." needs to be in correct format!"
                            ]; 
                        }, 
                    $varr);
                    // SPECIAL CONDITIONS.
                    // HAS TO BE ADMIN
                    $narr[] = [is_admin(), "user not admin!"];
                    // ADD_RATING SHOULD BE FLOAT OF (0 <= $ADD_RATING <= 10)
                    $narr[] = [($ADD_RATING <= 10 && $ADD_RATING >= 0), "rating must be within 0 ~ 10!"];
                    // IF ALL GOOD, APPROVED ($POSTED = true)
                    $POSTED = complex_rfif($narr);
                    unset($narr);
                }
                unset($varr);

                // IF ALL POSTED CORRECTLY
                if(file_exists(SERIES_FILE) && !empty($SERIES_NUMBER)):
                    $SERIES_ARR = get_json_arr(SERIES_FILE);
                    $CURRENT_SERIES_DATA = find_series_by_id($SERIES_ARR["data"], $SERIES_NUMBER)[0];
                    if(file_exists(EPISODES_FILE)):
                        $EPISODES_ARR = get_json_arr(EPISODES_FILE);
                        
                        // IF POSTED (EDIT EPISODE)
                        if($POSTED[0]){
                            // GET INDEX
                            $episode_index = find_episode_index_by_id($EPISODES_ARR["data"], $SERIES_NUMBER, $EPISODE_NUMBER);
                            // REGISTER DATA
                            $REGISTER_DATA = array(
                                "episode_id" => intval($EPISODE_NUMBER),
                                "series_id" => intval($SERIES_NUMBER),
                                "title" => $ADD_TITLE,
                                "date" => $ADD_DATE,
                                "description" => $ADD_DESCRIPTION,
                                "rating" => floatval($ADD_RATING)
                            );
                            // MODIFY DATA
                            $EPISODES_ARR["data"][$episode_index] = $REGISTER_DATA;
                            // WRITE ON FILE
                            write_on_file(
                                EPISODES_FILE,
                                json_encode($EPISODES_ARR, JSON_PRETTY_PRINT),
                                "SUCCESSFULLY REGISTERED!<br>",
                                "SOMETHING WENT WRONG... PLEASE TRY AGAIN!"
                            );
                            unset($episode_index);
                        }else{
                            foreach($POSTED[1] as $err_msg){
                                if(!empty($err_msg)) echo print_failure($err_msg);
                            }
                        }

                        $CURRENT_SERIES_EPISODES_DATA = find_series_by_id($EPISODES_ARR["data"], $SERIES_NUMBER);
        ?>
        <!-- SERIES START -->
            <div id="series-box">
                <!-- TOP BOX SECTION -->
                <div class="top-box flex column">
                    <div class="top-box-bgimage" style="background-image: url(<?php echo $CURRENT_SERIES_DATA["img_url"]; ?>);"></div>
                    <!-- SPACER -->
                    <div class="spacer"></div>
                    <!-- TEXT BOX -->
                    <div class="top-text-box flex column margin">
                        <h1 class="title-text observable">
                            <?php echo $CURRENT_SERIES_DATA["title"]." (".$CURRENT_SERIES_DATA["year"].")"; ?>
                        </h1>
                        <h5 class="sub-text observable">
                            <?php echo $CURRENT_SERIES_DATA["description"]; ?>
                        </h5>
                    </div>
                    <!-- DOWN ARROW -->
                    <div class="arrow down"></div>
                </div>
                <!-- EPISODES SECTION -->
                <div class="episodes-text-box">
                    <h1 class="episodes-title"><?php echo count($CURRENT_SERIES_EPISODES_DATA); ?> Episodes</h1>
                    <h5 class="episodes-subtitle">Last Broadcasted in <?php if(count($CURRENT_SERIES_EPISODES_DATA) > 0) echo "<span class='episodes-last-broadcast-date'>".end($CURRENT_SERIES_EPISODES_DATA)["date"]."</span>"; ?></h5>
                </div>
                <?php if(is_admin()): ?>
                    <!-- [SERIES] ADMIN ONLY (ADD SERIES) START -->
                    <form method="post" id="add-episode" class="add-series-episode flex column margin-side margin-bottom" novalidate>
                        <input name="MODE" value="ADD_EPISODE" class="invisible" />
                        <input name="SERIES_NUMBER" value="<?php echo $SERIES_NUMBER ?>" class="invisible" />
                        <input name="USERNAME" value="<?php echo $USERNAME; ?>" class="invisible" />
                        <input name="PASSWORD" value="<?php echo $PASSWORD; ?>" class="invisible" />
                        <input name="LOGGED_IN" value="<?php echo $LOGGED_IN; ?>" class="invisible" />
                        <input type="submit" value="ADD EPISODE" class="add-series-episode-button" />
                    </form>
                    <!-- [SERIES] ADMIN ONLY (ADD SERIES) END -->
                <?php endif; ?>
                
                <table class="episodes-box flex column">
                    <tr class="episodes-headings flex clickable">
                        <th class="episode-epn flex"><span>Num.</span></th>
                        <th class="episode-title flex"><span>Title</span></th>
                        <th class="episode-date flex"><span>Date</span></th>
                        <th class="episode-rating flex"><span>Rating</span></th>
                        <th class="episode-description flex"><span>Description</span></th>
                        <?php if(is_admin()): ?>
                        <th class="episode-submit flex"><span>Submit</span></th>
                        <?php endif; ?>
                    </tr>
                    <?php foreach($CURRENT_SERIES_EPISODES_DATA as $e): ?>
                    <tr class="observable episode-tr flex clickable"
                        id="episode<?php echo $e['episode_id']; ?>">
                        <?php if(is_admin()): ?>
                        <form method="post" class="flex column margin-side margin-bottom" novalidate>
                            <input name="MODE" value="SERIES" class="invisible" />
                            <input name="SERIES_NUMBER" value="<?php echo $SERIES_NUMBER ?>" class="invisible" />
                            <input name="USERNAME" value="<?php echo $USERNAME; ?>" class="invisible" />
                            <input name="PASSWORD" value="<?php echo $PASSWORD; ?>" class="invisible" />
                            <input name="LOGGED_IN" value="<?php echo $LOGGED_IN; ?>" class="invisible" />
                        <?php endif; ?>
                        <!-- TITLE -->
                        <td class="episode-epn flex">
                            <?php if(is_admin()): ?>
                            <input type="number" class="episode-input" name="EPISODE_NUMBER" value="<?php echo $e['episode_id']; ?>" readonly />
                            <?php endif; if(!is_admin()): ?>
                            <span>
                                <?php echo $e['episode_id']; ?>
                            </span>
                            <?php endif; ?>
                        </td>
                        <td class="episode-title flex">
                            <?php if(is_admin()): ?>
                            <input type="text" class="episode-input" name="ADD_TITLE" value="<?php echo $e['title']; ?>" />
                            <?php endif; if(!is_admin()): ?>
                            <span>
                                <?php echo $e['title']; ?>
                            </span>
                            <?php endif; ?>
                        </td>
                        <td class="episode-date flex">
                            <?php if(is_admin()): ?>
                            <input type="text" class="episode-input" name="ADD_DATE" value="<?php echo $e['date']; ?>" />
                            <?php endif; if(!is_admin()): ?>
                            <span>
                                <?php echo $e['date']; ?>
                            </span>
                            <?php endif; ?>
                        </td>
                        <td class="episode-rating flex">
                            <?php if(is_admin()): ?>
                            <input type="number" class="episode-input" step="0.1" name="ADD_RATING" value="<?php echo number_format($e['rating'], 1); ?>" />
                            <?php endif; if(!is_admin()): ?>
                            <span>
                                <?php echo number_format($e['rating'], 1); ?>
                            </span>
                            <?php endif; ?>
                        </td>
                        <td class="episode-description flex">
                            <?php if(is_admin()): ?>
                            <input type="text" class="episode-input" name="ADD_DESCRIPTION" value="<?php echo $e['description']; ?>" />
                            <?php endif; if(!is_admin()): ?>
                                <span>
                                <?php echo $e['description']; ?>
                            </span>
                            <?php endif; ?>
                        </td>
                        <?php if(is_admin()): ?>
                        <td class="episode-submit flex">
                            <input type="submit" class="episode-input-submit" />
                        </td>
                        </form>
                        <?php endif; ?>
                    </tr>
                    <?php
                        // echo "<script type='text/javascript'> setSeriesDetail(".$s['series_id'].") </script>"; 
                        unset($e); 
                        endforeach; 
                    ?>
                </table>
                <div class="spacer"></div>
            </div>
        <!-- SERIES END -->
        <?php
                    endif;
                endif;
                if(!file_exists(SERIES_FILE) && empty($SERIES_NUMBER)):
        ?>
        <!-- [SERIES] IF FILE DOES NOT EXIST START -->
            <div class="spacer"></div>
            <h1 class="alert">FILE NOT FOUND! PLEASE CONTACT ADMINISTRATOR</h1>
        <!-- [SERIES] IF FILE DOES NOT EXIST END -->
        <?php
                endif;
            endif;
        ?>
    </div>
    <form method="post" id="form" class="invisible" novalidate>
        <input name="MODE" id="mode" value="<?php echo $NEXT_MODE ?>"/>
        <?php if($LOGGED_IN): ?>
            <input name="USERNAME" value="<?php echo $USERNAME; ?>" />
            <input name="PASSWORD" value="<?php echo $PASSWORD; ?>" />
            <input name="LOGGED_IN" value="<?php echo $LOGGED_IN; ?>" />
        <?php endif; ?>   
        <?php if($MODE == "ADD_EPISODE"): ?>
            <input name="SERIES_NUMBER" value="<?php echo $SERIES_NUMBER ?>" />
        <?php endif; ?>
    </form>
</body>

</html>