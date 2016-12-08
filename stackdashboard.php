<?php require_once("includes/headers.php"); ?>
<?php require_once("includes/session.php"); ?>
<?php require_once("includes/db_connection.php"); ?>
<?php require_once("includes/functions.php"); ?>
<?php require_once("includes/validation_functions.php"); ?>
<?php confirm_logged_in(); ?>
<?php $user_id = $_SESSION['user_id']; ?>
<?php confirm_selected_stack($user_id); ?>
<?php $available_categories = fetch_available_categories($user_id); ?>

<?php  
// multi purpose form:
if (isset($_POST['submit'])) {
    // Process the GET- en POST-variables
    $next_step = $_POST['submit'];
    if ($next_step == "all") {
        activate_selected_stack($user_id);
        redirect_actions("card.php", "stackdashboard");
    }
    if ($next_step == "wrong") {
        deactivate_stack($user_id);
        activate_wrong($user_id);
        redirect_actions("card.php", "stackdashboard");
    }    
    if ($next_step == "new") {
        deactivate_stack($user_id);
        activate_new($user_id);
        redirect_actions("card.php", "stackdashboard");
    }
    if ($next_step == "resume") {
        redirect_actions("card.php", "stackdashboard");
    }
    if ($next_step == "change") {
        if (isset($connection)) {mysqli_close($connection);}
        redirect_actions("cardbox.php", "stackdashboard");
    }
     redirect_actions("stackdashboard.php", "stackdashboard");// (PRG)
} else { // new page request (not a POST):    
    // redirect 'refresh or back' to stackdashboard (because not approached by another page)
    // redirect to login.php not needed, because login already comfirmed at top of page 
	if ($source_of_request=="") {
	      redirect_actions("stackdashboard.php", "stackdashboard");
	}   
	?>
    <?php include("includes/layouts/header.php"); ?>
    
    	<body class="stack dashboard">
        <div class="container">
            <form method="post" action="stackdashboard.php" >
            <label class="">These are the categories you selected:</label>           
            <ul>
    	  	<?php 
    	  	foreach ($available_categories as $category) {
    	  	    if (category_selected($category['id'])) {           
    	  	            echo "<li>&nbsp;".htmlentities($category['category_name'])."</li>";
    	  	    }
            } 
            ?> 
            </ul>
			<div><button type="submit" name="submit" value="change" class="" title="Change">Select other categories</button></div>
            <br />
            <label class="">Quiz yourself!</label>
      			<?php if (number_of_cards_in_questionstack($user_id) > 0) {?>
              		      <div><button type="submit" name="submit" value="resume" class="" title="Resume interrupted">Resume interrupted</button></div>
                <?php } ?>
                <br/>
                <div><button type="submit" name="submit" value="all" class="" title="Quiz my whole stack">Quiz my whole stack</button></div>
      			<?php  // deactivate submit button if no wrong answers in stack:   
                      if (number_of_wrong_answers_in_stack($user_id) == 0) {?>
                          <div><button type="button" name="submit" value="wrong" class="" title="No wrong answers in your stack">No wrong answers in your stack</button></div>
                <?php } else { ?>        
                          <div><button type="submit" name="submit" value="wrong" class="" title="Quiz me, but only previous wrong answers">Quiz me, but only previous wrong answers</button></div>
                <?php } ?> 
      			<?php  // deactivate submit button if no new answers in stack:   
                      if (number_of_new_cards_in_stack($user_id) == 0) {?>
                          <div><button type="button" name="submit" value="new" class="" title="No new cards in your stack">No new cards in your stack</button></div>
                <?php } else { ?>        
                          <div><button type="submit" name="submit" value="new" class="" title="Quiz me, but only new cards">Quiz me, but only new cards</button></div>
                 <?php } ?> 
              </form>
        </div>    
            
    <?php include("includes/layouts/footer.php"); ?>
    <?php 
} 
?>
