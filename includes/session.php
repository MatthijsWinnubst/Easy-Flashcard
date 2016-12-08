<?php

    session_start();
    
    if (!isset($_SESSION['source_of_request'])) {
    	$source_of_request = "";
    } else {
    	$source_of_request = $_SESSION['source_of_request'];
    }
    $_SESSION['source_of_request']="";
    
	function message() {
		if (isset($_SESSION["message"])) {
			
			$output = "<div class=\"message\">";
			$output .= htmlentities($_SESSION["message"]);
			$output .= "</div>";
			
			// clear message after use
			$_SESSION["message"] = null;
			
			return $output;
		}
	}

	function errors() {
		if (isset($_SESSION["errors"])) {
			
			$errors = "<div class=\"error\">";
			$errors = $_SESSION["errors"];
			$errors = "</div>";
			
			// clear message after use
			$_SESSION["errors"] = null;
			
			return $errors;
		}
	}

	
?>