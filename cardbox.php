<?php require_once("includes/headers.php"); ?>
<?php require_once("includes/session.php"); ?>
<?php require_once("includes/db_connection.php"); ?>
<?php require_once("includes/functions.php"); ?>
<?php require_once("includes/validation_functions.php"); ?>
<?php confirm_logged_in(); ?>
<?php $user_id = $_SESSION['user_id'];?>

<?php 
// Process the GET- and POST- variables

// if POSTed

if (isset($_POST['submit'])) {
    if($_POST['submit'] == "Select all") {
        select_all_categories($user_id);
        // anders oplossen, binnen elke functionele eenheid:
        // create stack at the same time, to avoid discrepancies between cats and stack:
        // reset_stack($user_id);
        // create_stack_all_categories($user_id);
    } elseif ($_POST['submit'] == "Unselect all") {
        unselect_all_categories($user_id);
        // anders oplossen, binnen elke functionele eenheid:
        // create stack at the same time, to avoid discrepancies between cats and stack:
        // reset_stack($user_id); 
    } elseif ($_POST['submit'] == "Quiz me!") {
         // validations
                                // later via onderstaande functie, als die ook arrays aankan
                                // $required_fields = array('categories_in_stack');
                                // validate_presences($required_fields);
    
        if (!isset($_POST['categories_in_stack'])) { 
            $_SESSION['message']="Select at least one category"; 
        } else {   
            $selected_categories = $_POST['categories_in_stack'];
            if (number_of_cards_in_selected_cats($user_id, $selected_categories/*, $selected_status*/) > 0) {        
                    unselect_all_categories($user_id);  // reset first
                    create_categories_in_stack($selected_categories);
                    reset_stack($user_id); 
                    create_selected_stack($user_id, $selected_categories/*, $selected_status*/);
                    redirect_actions("stackdashboard.php", "cardbox");
            } else {
                $_SESSION['message'] = "No cards in the selected categories. Try another selection.";
            }   // end if else --- stack > 0 cards 
        }       // end if else --- categories in stack
    }           // end elseif --- $POST['submit'] 
    redirect_actions("cardbox.php", "cardbox"); // (PRG)
}               // end if --- POSTed

// new page request (not a POST):

else { 
    // redirect 'refresh or back' to cardbox (because not approached by another page)
    // redirect to login.php not needed, because login already comfirmed at top of page 
	if ($source_of_request=="") {
	      redirect_actions("cardbox.php", "cardbox");
	}   
}
// get form-values from the database
    $available_categories = fetch_available_categories($user_id);
    //$available_statuses = fetch_available_statuses($user_id);
?>

<?php include("includes/layouts/header.php"); ?>
	
<body class="cardbox">
	<div class="container">
    <header>
    <div style="height:150px; width:800px;background:grey;">
        <h1 style="font-size:300%;">Easy Flashcard</h1>
        <nav>   
        	<ul style="font-size:150%;">
			    <li><a href="/mycategories.php">My Categories</a>
			    <li><a href="/myprofile.php">My Profile</a>
			    <li><a href="/explanation.php">How does it work?</a>
			    <li><a href="/about.php">About</a>
			</ul>
   		</nav>
    </div></header>
    <?php echo message(); ?>
    <?php echo form_errors($errors); ?>

    <form method="post" action="cardbox.php" >
    <header>
	    <h1 style="font-size:300%;">Your categories</h1> 
	    
	    <p style="font-size:180%;">Select one or more categories you want to work with</p> 
    </header>
    <fieldset style="font-size:180%;">
	  	<input type="submit" name="submit" id="submit" value="Select all">
	  	<input type="submit" name="submit" id="submit" value="Unselect all">
	  	<br />
	  	<?php 
	  	foreach ($available_categories as $category) {
    	   echo "<input type=\"checkbox\" id=\"checkbox".($category['id'])."\" name=\"categories_in_stack[]\" value=\"".($category['id'])."\" "; 
    	   if (category_selected($category['id'])) {echo "checked";}
       	   echo  ">";
           echo "<label for=\"checkbox".($category['id'])."\">&nbsp;".htmlentities($category['category_name'])."</label>";
           echo "<br />";
           } 
        ?> 
    </fieldset>
    <p><input type="submit" name="submit" id="submit" value="Quiz me!"></p>
	</form>
	</div>
	<?php include("includes/layouts/footer.php"); ?>