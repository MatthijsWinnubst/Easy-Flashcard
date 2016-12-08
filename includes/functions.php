<?php

// GENERAL FUNCTIONS

	function confirm_query($result_set) {
		if (!$result_set) {
			die("Database query failed.");
		}
	}
	
	function mysql_prep($string) {
	    global $connection;
	
	    $escaped_string = mysqli_real_escape_string($connection, $string);
	    return $escaped_string;
	}
	
	function redirect_to($new_location) {
	    header("Location: " . $new_location);
	    exit;
	}
	
	function redirect_actions($new_location, $current_location) {
	    global $connection;
	    
	    if (isset($connection)) {mysqli_close($connection);}
	    $_SESSION['source_of_request'] = $current_location;
	    redirect_to($new_location);
	}
	
	function form_errors($errors=array()) {
	    $output = "";
	    if (!empty($errors)) {
	        $output .= "<div class=\"error\">";
	        //$output .= "Please fix the following errors:";
	        $output .= "<ul>";
	        foreach ($errors as $key => $error) {
	            $output .= "<li>";
	            $output .= htmlentities($error);
	            $output .= "</li>";
	        }
	        $output .= "</ul>";
	        $output .= "</div>";
	    }
	    return $output;
	}

// USER FUNCTIONS
	
	function find_all_users() {
	    global $connection;
	
	    $query  = "SELECT * ";
	    $query .= "FROM users ";
	    $query .= "ORDER BY username ASC";
	    $admin_set = mysqli_query($connection, $query);
	    confirm_query($user_set);
	    
	    return $user_set;
	}
	
	function find_user_by_id($user_id) {
	    global $connection;
	
	    $safe_user_id = mysql_prep($user_id);
	    
	    $query  = "SELECT * ";
	    $query .= "FROM users ";
	    $query .= "WHERE id = {$safe_user_id} ";
	    $query .= "LIMIT 1";
	    $user_set = mysqli_query($connection, $query);
	    confirm_query($user_set);
	    if($user = mysqli_fetch_assoc($user_set)) {
	        return $user;
	    } else {
	        return null;
	    }
	}
	
	function find_user_by_username($username) {
	    global $connection;
	
	    $safe_username = mysql_prep($username);
	
	    $query  = "SELECT * ";
	    $query .= "FROM users ";
	    $query .= "WHERE username = '{$safe_username}' ";
	    $query .= "LIMIT 1";
	    $user_set = mysqli_query($connection, $query);
	    confirm_query($user_set);
	    if($user = mysqli_fetch_assoc($user_set)) {
	        return $user;
	    } else {
	        return null;
	    }
	}

// LOGIN FUNCTIONS	
	
	function password_encrypt($password) {
	    $hash_format = "$2y$10$";   // Tells PHP to use Blowfish with a "cost" of 10
	    $salt_length = 22; 					// Blowfish salts should be 22-characters or more
	    $salt = generate_salt($salt_length);
	    $format_and_salt = $hash_format . $salt;
	    $hash = crypt($password, $format_and_salt);
	    return $hash;
	}
	
	function generate_salt($length) {
	    // Not 100% unique, not 100% random, but good enough for a salt
	    // MD5 returns 32 characters
	    $unique_random_string = md5(uniqid(mt_rand(), true));
	     
	    // Valid characters for a salt are [a-zA-Z0-9./]
	    $base64_string = base64_encode($unique_random_string);
	     
	    // But not '+' which is valid in base64 encoding
	    $modified_base64_string = str_replace('+', '.', $base64_string);
	     
	    // Truncate string to the correct length
	    $salt = substr($modified_base64_string, 0, $length);
	     
	    return $salt;
	}
	
	function password_check($password, $existing_hash) {
	    // existing hash contains format and salt at start
	    $hash = crypt($password, $existing_hash);
	    if ($hash === $existing_hash) {
	        return true;
	    } else {
	        return false;
	    }
	}
	
	function attempt_login($username, $password) {
	    $user = find_user_by_username($username);
	    if ($user) {
	        // found user, now check password
	        if (password_check($password, $user["hashed_password"])) {
	            // password matches
	            return $user;
	        } else {
	            // password does not match
	            return false;
	        }
	    } else {
	        // user not found
	        return false;
	    }
	}
	
	function logged_in() {
	    return isset($_SESSION['user_id']);
	}
	
	function confirm_logged_in() {
	    if (!logged_in()) {
            $_SESSION["message"] = "Please login:";
	        redirect_to("login.php");
	    }
	}

	function update_database_with_new_session_id($user_id, $session_id) {
		global $connection;
		
		$safe_user_id = mysql_prep($user_id);
		$safe_session_id = mysql_prep($session_id);
		
		$query  = "UPDATE ";
		$query .= "users ";
		$query .= "SET session_id = '{$safe_session_id}' ";
		$query .= "WHERE ";
		$query .= "id = {$safe_user_id} ";
		$result = mysqli_query($connection, $query);
	    confirm_query($result);
	    mysqli_free_result($result);
	    return true;
	}
	
	
// CATEGORY FUNCTIONS	
	
	function fetch_available_categories($user_id) {
	    global $connection;
	    
	    $safe_user_id = mysql_prep($user_id);
	    
	    $query  = "SELECT cat.* FROM categories cat ";
	    $query .= "INNER JOIN cards_categories cc ON cc.category_id = cat.id ";
	    $query .= "INNER JOIN cards c ON c.id = cc.card_id ";
	    $query .= "WHERE cat.user_id = $safe_user_id ";
	    $query .= "GROUP BY cat.id ";
	    $result = mysqli_query($connection, $query);
	    confirm_query($result);
	    // Store query results in array
		while ($row = mysqli_fetch_assoc($result)) {
	       $available_categories[] = ($row);
	    }
	    mysqli_free_result($result);
	    	  
	    return $available_categories;
	}
	
	function fetch_available_statuses($user_id) {
	    global $connection;
	    
	    $safe_user_id = mysql_prep($user_id);
	    
        $query  = "SELECT id, status_name, status_text FROM status ";
        $query .= "WHERE allowed = 1";
	    $result = mysqli_query($connection, $query);
	    confirm_query($result);
	    // Store query results in array
		while ($row = mysqli_fetch_assoc($result)) {
	        $available_statuses[] = ($row);
	    }
	    mysqli_free_result($result);
	     
	    return $available_statuses;
	}

	function unselect_all_categories($user_id) {
	    global $connection;
	
	    $safe_user_id = mysql_prep($user_id);
	    
	    $query   = "UPDATE categories cat ";
	    $query  .= "SET cat.stack = 0 ";
	    $query  .= "WHERE cat.user_id = ".$safe_user_id;
	    $result = mysqli_query($connection, $query);
	    confirm_query($result);
	    mysqli_free_result($result);
	    return true;
	}
	
	function select_all_categories($user_id) {
	    global $connection;
	    
	    $safe_user_id = mysql_prep($user_id);
	    
	    $query   = "UPDATE categories cat ";
	    $query  .= "SET cat.stack = 1 ";
	    $query  .= "WHERE cat.user_id = ".$safe_user_id;
	    $result = mysqli_query($connection, $query);
	    confirm_query($result);
	    mysqli_free_result($result);
	    return true;
	}
	
	function create_categories_in_stack($selected_categories) {
	    global $connection;
	
        $cat_indexes = implode(',', $selected_categories);
        $safe_cat_indexes = mysql_prep($cat_indexes);
        
	    $query   = "UPDATE categories cat ";
	    $query  .= "SET cat.stack = 1 ";
	    $query  .= "WHERE cat.id IN ($safe_cat_indexes)";
	    $result = mysqli_query($connection, $query);
	    confirm_query($result);
	    mysqli_free_result($result);
	    return true;
	}
	
	function category_selected($category_id) {
	    global $connection;
	    
	    $safe_category_id = mysql_prep($category_id);
	     
	    
	    $query   = "SELECT cat.stack AS stack FROM categories cat ";
	    $query  .= "WHERE cat.id = $safe_category_id";
	    $result = mysqli_query($connection, $query);
	    confirm_query($result);
	    $row = mysqli_fetch_assoc($result);
	    if ($row['stack'] == 1) {$category_selected = true;} 
	    else {$category_selected = false;}    
	    mysqli_free_result($result);

	    return $category_selected ;
	}

	function number_of_cards_in_selected_cats($user_id, $selected_categories) {
		global $connection;
	
		$safe_user_id = mysql_prep($user_id);
		 
		$query   = "SELECT COUNT(*) AS number_of_cards FROM cards c ";
		$query  .= "INNER JOIN cards_categories cc ON cc.card_id = c.id ";
		$query  .= "INNER JOIN categories cat ON cat.id = cc.category_id ";
		$query  .= "WHERE c.user_id = ".$safe_user_id." ";
		$query  .= "AND (";
		// include selected categories into the query:
		$categorycounter = 0;
		foreach ($selected_categories as $category_id) {
			$safe_category_id = mysql_prep($category_id);
			$categorycounter += 1;
			if ($categorycounter == 1) { $query.= "cat.id = $safe_category_id "; }
			else { $query.= "OR cat.id = $safe_category_id "; }
		}
		$query  .= ") ";
		/*           $query  .= "AND (";
		 // include selected status(es) into the query:
		 $statuscounter = 0;
		 foreach ($selected_status as $status_id) {
		 $statuscounter += 1;
		 if ($statuscounter == 1) { $query.= "c.back_status = $status_id "; }
		 else { $query.= "OR c.back_status = $status_id "; }
		 }
		 $query  .= ") "; */
		 
		$result = mysqli_query($connection, $query);
		confirm_query($result);
		$row = mysqli_fetch_assoc($result);
		$number_of_cards = $row['number_of_cards']  ;
		mysqli_free_result($result);
	
		return $number_of_cards;
	}
	
// STACK FUNCTIONS

	function confirm_selected_stack($user_id) {
	    global $connection;
	    
		if (number_of_cards_in_selected_stack($user_id) == 0) {
	        $_SESSION["message"] = "Define category(s):";
	        if (isset($connection)) {mysqli_close($connection);}
	        redirect_to("cardbox.php");
	    }
    }
	
	function confirm_questionstack($user_id) {
	    global $connection;
	     
	    if (number_of_cards_in_questionstack($user_id) == 0) {
	        $_SESSION["message"] = "Define stack action:";
	        if (isset($connection)) {mysqli_close($connection);}
	        redirect_to("stackdashboard.php");
	    }
	}
	
	function reset_stack($user_id) {
	    global $connection;
	
	    $safe_user_id = mysql_prep($user_id);
	    
	    $query   = "UPDATE cards c ";
	    $query  .= "SET c.selected = 0, ";
	    $query  .= "c.stack = 0, ";
	    $query  .= "c.stack_order = 0 ";
	    $query  .= "WHERE c.user_id = ".$user_id;
	    $result = mysqli_query($connection, $query);
	    confirm_query($result);
	    mysqli_free_result($result);
	    return true;
	}

	function create_selected_stack($user_id, $selected_categories) {
	    global $connection;
	     
	    $safe_user_id = mysql_prep($user_id);
    	
    	$query   = "UPDATE cards c ";
    	$query  .= "INNER JOIN cards_categories cc ON cc.card_id = c.id ";
    	$query  .= "INNER JOIN categories cat ON cat.id = cc.category_id ";
    	$query  .= "SET c.selected = 1, ";
	    $query  .= "c.stack = 0, ";
	    $query  .= "c.stack_order = 0 ";
	    $query  .= "WHERE c.user_id = ".$safe_user_id." ";
    	$query  .= "AND (";
    	// include selected categories into the query:
    	$categorycounter = 0;
    	foreach ($selected_categories as $category_id) {
    	    $safe_category_id = mysql_prep($category_id);
    	    $categorycounter += 1;
    	    if ($categorycounter == 1) {
    	        $query.= "cat.id = $safe_category_id ";
    	    } else {
    	        $query.= "OR cat.id = $safe_category_id ";
    	    }
    	}
    	$query  .= ") ";
 /*   	$query  .= "AND (";
    	// include selected status(es) into the query:
    	$statuscounter = 0;
    	foreach ($selected_status as $status_id) {
    	    $statuscounter += 1;
    	    if ($statuscounter == 1) {
    	        $query.= "c.back_status = $status_id ";
    	    } else {
    	        $query.= "OR c.back_status = $status_id ";
    	    }
    	}
    	$query  .= ") ";*/
    	
    	$result = mysqli_query($connection, $query);
    	confirm_query($result);
	    mysqli_free_result($result);
	    return true;
	}

	/* vervallen, even laten staan
	function create_stack_all_categories($user_id) {
	    global $connection;
	    
	    $safe_user_id = mysql_prep($user_id);
	     
	    $query   = "UPDATE cards c ";
	    $query  .= "INNER JOIN cards_categories cc ON cc.card_id = c.id ";
	    $query  .= "INNER JOIN categories cat ON cat.id = cc.category_id ";
	    $query  .= "SET c.selected = 1, c.stack = 2 ";
	    $query  .= "WHERE c.user_id = ".$safe_user_id." ";
	    $query  .= "AND cat.stack = 1";
	    $result = mysqli_query($connection, $query);
	    confirm_query($result);
	    mysqli_free_result($result);
	    return true;
	}*/
	
	function activate_selected_stack($user_id) {
	    global $connection;
	
	    $safe_user_id = mysql_prep($user_id);
	    
	    $query  = "UPDATE cards ";
	    $query .= "SET stack = 1, ";
	    $query .= "stack_order = 0 ";
	    $query .= "WHERE user_id = {$safe_user_id} ";
	    $query .= "AND selected = 1 ";
	    $result = mysqli_query($connection, $query);
	    confirm_query($result);
	    mysqli_free_result($result);
	    return true;
	}
	
	function deactivate_stack($user_id) {
	    global $connection;
	
	    $safe_user_id = mysql_prep($user_id);
	    
	    $query  = "UPDATE cards ";
	    $query .= "SET stack = 0, ";
	    $query .= "stack_order = 0 ";
	    $query .= "WHERE user_id = {$safe_user_id} ";
	    $query .= "AND selected = 1 "; 
	    $result = mysqli_query($connection, $query);
	    confirm_query($result);
	    mysqli_free_result($result);
	    return true;
	}
	
	function activate_wrong($user_id) {
	    global $connection;
	    
	    $safe_user_id = mysql_prep($user_id);
	    
	    $query  = "UPDATE cards ";
	    $query .= "SET stack = 1, ";
	    $query .= "stack_order = 0 ";
	    $query .= "WHERE user_id = {$safe_user_id} ";
	    $query .= "AND selected = 1 "; 
	    $query .= "AND back_status = 1 ";
	    $result = mysqli_query($connection, $query);
	    confirm_query($result);
	    mysqli_free_result($result);
	    return true;
	    
	}

	function activate_new($user_id) {
	    global $connection;
	    
	    $safe_user_id = mysql_prep($user_id);
	    
	    $query  = "UPDATE cards ";
	    $query .= "SET stack = 1, ";
	    $query .= "stack_order = 0 ";
	    $query .= "WHERE user_id = {$safe_user_id} ";
	    $query .= "AND selected = 1 "; 
	    $query .= "AND back_status = 0 "; 
	    $result = mysqli_query($connection, $query);
	    confirm_query($result);
	    mysqli_free_result($result);
	    return true;
	    
	}
		
	function number_of_cards_in_questionstack($user_id) {
	    global $connection;
	    
	    $safe_user_id = mysql_prep($user_id);
	    
	    $query  = "SELECT COUNT(*) AS number_of_cards ";
	    $query .= "FROM cards ";
	    $query .= "WHERE user_id = {$safe_user_id} ";
	    $query .= "AND selected = 1 ";
	    $query .= "AND stack IN (1, 2) ";
	    $result = mysqli_query($connection, $query);
	    confirm_query($result);
	    $row = mysqli_fetch_assoc($result);
        $number_of_cards = $row['number_of_cards']  ;     
        mysqli_free_result($result);
        
        return $number_of_cards;
	}
	
	function number_of_ordered_cards_in_questionstack($user_id) {
		global $connection;
		 
		$safe_user_id = mysql_prep($user_id);
		 
		$query  = "SELECT COUNT(*) AS number_of_cards ";
		$query .= "FROM cards ";
		$query .= "WHERE user_id = {$safe_user_id} ";
	    $query .= "AND selected = 1 ";
		$query .= "AND stack = 1 ";
		$query .= "AND stack_order > 0 ";
		$result = mysqli_query($connection, $query);
		confirm_query($result);
		$row = mysqli_fetch_assoc($result);
		$number_of_cards = $row['number_of_cards']  ;
		mysqli_free_result($result);
	
		return $number_of_cards;
	}
	
	function number_of_cards_in_processedstack($user_id) {
		global $connection;
		 
		$safe_user_id = mysql_prep($user_id);
		 
		$query  = "SELECT COUNT(*) AS number_of_cards ";
		$query .= "FROM cards ";
		$query .= "WHERE user_id = {$safe_user_id} ";
	    $query .= "AND selected = 1 ";
		$query .= "AND stack = 3 ";
		$result = mysqli_query($connection, $query);
		confirm_query($result);
		$row = mysqli_fetch_assoc($result);
		$number_of_cards = $row['number_of_cards']  ;
		mysqli_free_result($result);
	
		return $number_of_cards;
	}
	
	function number_of_right_cards_in_processedstack($user_id) {
		global $connection;
			
		$safe_user_id = mysql_prep($user_id);
			
		$query  = "SELECT COUNT(*) AS number_of_cards ";
		$query .= "FROM cards ";
		$query .= "WHERE user_id = {$safe_user_id} ";
	    $query .= "AND selected = 1 ";
		$query .= "AND stack = 3 ";
		$query .= "AND back_status = 2 ";
		$result = mysqli_query($connection, $query);
		confirm_query($result);
		$row = mysqli_fetch_assoc($result);
		$number_of_cards = $row['number_of_cards']  ;
		mysqli_free_result($result);
	
		return $number_of_cards;
	}
	
	function number_of_wrong_cards_in_processedstack($user_id) {
		global $connection;
			
		$safe_user_id = mysql_prep($user_id);
			
		$query  = "SELECT COUNT(*) AS number_of_cards ";
		$query .= "FROM cards ";
		$query .= "WHERE user_id = {$safe_user_id} ";
	    $query .= "AND selected = 1 ";
		$query .= "AND stack = 3 ";
		$query .= "AND back_status = 1 ";
		$result = mysqli_query($connection, $query);
		confirm_query($result);
		$row = mysqli_fetch_assoc($result);
		$number_of_cards = $row['number_of_cards']  ;
		mysqli_free_result($result);
	
		return $number_of_cards;
	}

	function number_of_new_cards_in_processedstack($user_id) {
		global $connection;
			
		$safe_user_id = mysql_prep($user_id);
			
		$query  = "SELECT COUNT(*) AS number_of_cards ";
		$query .= "FROM cards ";
		$query .= "WHERE user_id = {$safe_user_id} ";
		$query .= "AND selected = 1 ";
		$query .= "AND stack = 3 ";
		$query .= "AND back_status = 0 ";
		$result = mysqli_query($connection, $query);
		confirm_query($result);
		$row = mysqli_fetch_assoc($result);
		$number_of_cards = $row['number_of_cards']  ;
		mysqli_free_result($result);
	
		return $number_of_cards;
	}
	
	function number_of_unclassified_cards_in_processedstack($user_id) {
		global $connection;
			
		$safe_user_id = mysql_prep($user_id);
			
		$query  = "SELECT COUNT(*) AS number_of_cards ";
		$query .= "FROM cards ";
		$query .= "WHERE user_id = {$safe_user_id} ";
	    $query .= "AND selected = 1 ";
		$query .= "AND stack = 3 ";
		$query .= "AND back_status = 0 ";
		$result = mysqli_query($connection, $query);
		confirm_query($result);
		$row = mysqli_fetch_assoc($result);
		$number_of_cards = $row['number_of_cards']  ;
		mysqli_free_result($result);
	
		return $number_of_cards;
	}
	
	function number_of_cards_in_selected_stack($user_id) {
	    global $connection;
	     
	    $safe_user_id = mysql_prep($user_id);
	     
	    $query  = "SELECT COUNT(*) AS number_of_cards ";
	    $query .= "FROM cards ";
	    $query .= "WHERE user_id = {$safe_user_id} ";
	    $query .= "AND selected = 1 ";
	    $result = mysqli_query($connection, $query);
	    confirm_query($result);
	    $row = mysqli_fetch_assoc($result);
	    $number_of_cards = $row['number_of_cards']  ;
	    mysqli_free_result($result);
	
	    return $number_of_cards;
	}
	
	function number_of_wrong_answers_in_stack($user_id) {
	    global $connection;
	     
	    $safe_user_id = mysql_prep($user_id);
	    
	    $query  = "SELECT COUNT(*) AS number_of_cards ";
	    $query .= "FROM cards ";
	    $query .= "WHERE user_id = {$safe_user_id} ";
	    $query .= "AND selected = 1 ";  // the complete stack 
	    $query .= "AND back_status = 1 ";  // wrong answers
	    $result = mysqli_query($connection, $query);
	    confirm_query($result);
	    $row = mysqli_fetch_assoc($result);
	    $number_of_cards = $row['number_of_cards']  ;
	    mysqli_free_result($result);
	     
	    return $number_of_cards;
	}
	
	function number_of_right_answers_in_stack($user_id) {
		global $connection;
	
		$safe_user_id = mysql_prep($user_id);
		 
		$query  = "SELECT COUNT(*) AS number_of_cards ";
		$query .= "FROM cards ";
		$query .= "WHERE user_id = {$safe_user_id} ";
		$query .= "AND selected = 1 ";  // the complete stack
		$query .= "AND back_status = 2 ";  // right answers
		$result = mysqli_query($connection, $query);
		confirm_query($result);
		$row = mysqli_fetch_assoc($result);
		$number_of_cards = $row['number_of_cards']  ;
		mysqli_free_result($result);
	
		return $number_of_cards;
	}
	
	function number_of_new_cards_in_stack($user_id) {
	    global $connection;
	
	    $safe_user_id = mysql_prep($user_id);
	    
	    $query  = "SELECT COUNT(*) AS number_of_cards ";
	    $query .= "FROM cards ";
	    $query .= "WHERE user_id = {$safe_user_id} ";
	    $query .= "AND selected = 1 ";  // the complete stack 
	    $query .= "AND back_status = 0 ";  // no answers yet
	    $result = mysqli_query($connection, $query);
	    confirm_query($result);
	    $row = mysqli_fetch_assoc($result);
	    $number_of_cards = $row['number_of_cards']  ;
	    mysqli_free_result($result);
	     
	    return $number_of_cards;
	}
	
// CARD FUNCTIONS
	
	function confirm_activecard($user_id) {
	    if (!activecard_exists($user_id)) {
	        $_SESSION["message"] = "Define stack action";
	        redirect_to("stackdashboard.php");
	    }
	}
	
	function activecard_exists($user_id) {
	    global $connection;
	     
	    $safe_user_id = mysql_prep($user_id);
	    
	    $query  = "SELECT COUNT(*) AS number_of_cards ";
	    $query .= "FROM cards ";
	    $query .= "WHERE user_id = {$safe_user_id} ";
	    $query .= "AND selected = 1 ";
	    $query .= "AND stack = 2 ";
	    $result = mysqli_query($connection, $query);
	    confirm_query($result);
	    $row = mysqli_fetch_assoc($result);
	    $number_of_cards = $row['number_of_cards'];
	    mysqli_free_result($result);
	    
	    if ($number_of_cards == 1) {
	        return true;
	    } else {
	       return false;
	    }
	}
	
	function get_activecard($user_id) {
	    global $connection;
	
	    $safe_user_id = mysql_prep($user_id);
	    
	    $query  = "SELECT id AS card_id ";
	    $query .= "FROM cards ";
	    $query .= "WHERE user_id = {$safe_user_id} ";
	    $query .= "AND selected = 1 ";
	    $query .= "AND stack = 2 ";
	    $result = mysqli_query($connection, $query);
	    confirm_query($result);
	    // Store query results in variable
	    while ($row = mysqli_fetch_assoc($result)) {
	        $card_id = $row['card_id'];
	    }
	    mysqli_free_result($result);
	    return $card_id;
	}
	
	function set_activecard($card_id) {
	    global $connection;
	     
	    $safe_card_id = mysql_prep($card_id);
	     
	    $query  = "UPDATE cards ";
	    $query .= "SET stack = 2 ";
	    $query .= "WHERE id = $safe_card_id ";
	    $result = mysqli_query($connection, $query);
	    confirm_query($result);
	    mysqli_free_result($result);
	    return true;
	}
	
	function set_ordernr($card_id, $user_id) {
		global $connection;
	
		$safe_card_id = mysql_prep($card_id);
		$safe_user_id = mysql_prep($user_id);
	
		$new_ordernr = get_max_ordernr($user_id) + 1;
		 
		$query  = "UPDATE cards ";
		$query .= "SET stack_order = $new_ordernr ";
		$query .= "WHERE id = $safe_card_id ";
		$result = mysqli_query($connection, $query);
		confirm_query($result);
		mysqli_free_result($result);
		return true;
	}	
	
	function get_max_ordernr($user_id) {
		global $connection;
	
		$safe_user_id = mysql_prep($user_id);
		 
		$query  = "SELECT max(stack_order) AS max_ordernr ";
		$query .= "FROM cards ";
		$query .= "WHERE user_id = {$safe_user_id} ";
	    $query .= "AND selected = 1 ";
		$query .= "AND stack <> 0 ";  // the complete stack
		$result = mysqli_query($connection, $query);
		confirm_query($result);
		// Store query results in variable
		while ($row = mysqli_fetch_assoc($result)) {
			$max_ordernr = $row['max_ordernr'];
		}
		mysqli_free_result($result);
		return $max_ordernr;
	}
	
	function get_ordernr($card_id) {
		global $connection;
	
		$safe_card_id = mysql_prep($card_id);
		
		$query  = "SELECT stack_order AS ordernr ";
		$query .= "FROM cards ";
		$query .= "WHERE id = {$safe_card_id} ";
		$result = mysqli_query($connection, $query);
		confirm_query($result);
		// Store query results in variable
		while ($row = mysqli_fetch_assoc($result)) {
			$ordernr = $row['ordernr'];
		}
		mysqli_free_result($result);
		return $ordernr;
	}
		
	function get_previous_card_id($user_id, $card_id) {
		global $connection;
	
		$safe_user_id = mysql_prep($user_id);
		$safe_card_id = mysql_prep($card_id);
		$prev_ordernr = get_ordernr($safe_card_id) - 1;		
		
		$query  = "SELECT id AS prev_card_id ";
		$query .= "FROM cards ";
		$query .= "WHERE user_id = {$safe_user_id} ";
	    $query .= "AND selected = 1 ";
		$query .= "AND stack_order = {$prev_ordernr} ";  // the previous card in the stack
		$result = mysqli_query($connection, $query);
		confirm_query($result);
		// Store query results in variable
		while ($row = mysqli_fetch_assoc($result)) {
			$prev_card_id = $row['prev_card_id'];
		}
		mysqli_free_result($result);
		return $prev_card_id;
	}
	
	function get_min_ordernr_in_questionstack($user_id) {
		global $connection;
	
		$safe_user_id = mysql_prep($user_id);
			
		$query  = "SELECT min(stack_order) AS min_ordernr ";
		$query .= "FROM cards ";
		$query .= "WHERE user_id = {$safe_user_id} ";
	    $query .= "AND selected = 1 ";
		$query .= "AND stack = 1 ";  // the question stack
		$query .= "AND stack_order > 0 "; // not the un-ordered cards
		$result = mysqli_query($connection, $query);
		confirm_query($result);
		// Store query results in variable
		while ($row = mysqli_fetch_assoc($result)) {
			$min_ordernr = $row['min_ordernr'];
		}
		mysqli_free_result($result);
		return $min_ordernr;
	}
	
	function get_min_order_card_id($user_id) {
		global $connection;
		
		$safe_user_id = mysql_prep($user_id);
		$min_ordernr = get_min_ordernr_in_questionstack($user_id);	
		
		$query  = "SELECT id AS card_id ";
		$query .= "FROM cards ";
		$query .= "WHERE user_id = {$safe_user_id} ";
	    $query .= "AND selected = 1 ";
		$query .= "AND stack_order = {$min_ordernr} ";  // the previous card in the stack
		$result = mysqli_query($connection, $query);
		confirm_query($result);
		// Store query results in variable
		while ($row = mysqli_fetch_assoc($result)) {
			$card_id = $row['card_id'];
		}
		mysqli_free_result($result);
		return $card_id;
	}

	function select_random_card($user_id) {
	    global $connection;
	
	    $safe_user_id = mysql_prep($user_id);
	    
	    $query   = "SELECT c.id AS card_id ";
	    $query  .= "FROM cards c ";
	    $query  .= "WHERE c.user_id = $safe_user_id ";
	    $query  .= "AND selected = 1 ";
	    $query  .= "AND c.stack = 1 ";
	    // select only one row, random:
	    $query  .= "ORDER BY RAND() LIMIT 1";
	    $result = mysqli_query($connection, $query);
	    confirm_query($result);
	    // Store query results in variable
	    while ($row = mysqli_fetch_assoc($result)) {
	        $card_id = $row['card_id'];
	    }
	    mysqli_free_result($result);
	     
	    return $card_id;
	}
	
	function fetch_card_properties($card_id) {
	    global $connection;
	     
	    $safe_card_id = mysql_prep($card_id);
	     
	    $query   = "SELECT front_main, back_main ";
	    $query  .= "FROM cards c ";
	    $query  .= "WHERE c.id = $safe_card_id";
	    $result = mysqli_query($connection, $query);
	    confirm_query($result);
	    // Store query results in array
	    $card = mysqli_fetch_assoc($result);
	    mysqli_free_result($result);
	     
	    return $card;
	}
	
	function deactivate_card($card_id) {
	    global $connection;
	
	    $safe_card_id = mysql_prep($card_id);
	    
	    $query  = "UPDATE cards ";
	    $query .= "SET stack = 3 ";
	    $query .= "WHERE id = $safe_card_id";
	    $result = mysqli_query($connection, $query);
	    confirm_query($result);
	    mysqli_free_result($result);
	    return true;
	}
	
	function put_card_back_to_questionstack($card_id) {
		global $connection;
	
		$safe_card_id = mysql_prep($card_id);
			
		$query  = "UPDATE cards ";
		$query .= "SET stack = 1 ";
		$query .= "WHERE id = $safe_card_id";
		$result = mysqli_query($connection, $query);
		confirm_query($result);
		mysqli_free_result($result);
		return true;
	}  
	
	function update_card_status($card_id, $new_status) {
	    global $connection;
	     
	    $safe_card_id = mysql_prep($card_id);
	    
	    // 0 = new / not processed yet
	    // 1 = wrong
	    // 2 = right
	    $query  = "UPDATE cards ";
	    $query .= "SET back_status =";
	    if ($new_status == "right") { $query .= "2 ";}
	    elseif ($new_status == "wrong") { $query .= "1 ";}
	    $query .= "WHERE id = $safe_card_id";
	    $result = mysqli_query($connection, $query);
	    confirm_query($result);
	    mysqli_free_result($result);
	    return true;
	}
		

	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	?>