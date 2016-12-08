<?php require_once("includes/headers.php"); ?>
<?php require_once("includes/session.php"); ?>
<?php require_once("includes/db_connection.php"); ?>
<?php require_once("includes/functions.php"); ?>
<?php require_once("includes/validation_functions.php"); ?>

<?php
$username = "";

if (isset($_POST['submit'])) {
  // Process the form
  
  // validations
  $required_fields = array("username", "password");
  validate_presences($required_fields);
  
  if (empty($errors)) {
    // Attempt Login

	$username = $_POST["username"];
	$password = $_POST["password"];
	
	$found_user = attempt_login($username, $password);

    if ($found_user) {
      // Success
      
    	// Close eventual previous session in the same or other browsers for this user account
    	// to avoid multiple sessions and data mixup.
    	// Open session with session_id from the database
    	session_id($found_user["session_id"]);
    	session_start();
    	session_destroy();
    	// Now start a new session with a new session_id
    	session_start();
    	session_regenerate_id();
    	update_database_with_new_session_id($found_user["id"], session_id()); 
    	 
    	// Mark user as logged in
 			$_SESSION["user_id"] = $found_user["id"];
			$_SESSION["username"] = $found_user["username"];
			$_SESSION["session_id"] = $found_user["session_id"];
			if (isset($connection)) {mysqli_close($connection);}
            
 			redirect_actions("cardbox.php", "login.php");
    } else {
      // Failure
      $_SESSION["message"] = "Username/password not found.";
    }
  }
} else {
  // This is probably a GET request
  
} // end: if (isset($_POST['submit']))

?>

<?php include("includes/layouts/header.php"); ?>
<div id="main">
  <div id="page">
    <?php echo message(); ?>
    <?php echo form_errors($errors); ?>
    
    <h2>Login</h2>
    <form action="login.php" method="post">
      <p>Username:
        <input type="text" name="username" value="<?php echo htmlentities($username); ?>" />
      </p>
      <p>Password:
        <input type="password" name="password" value="" />
      </p>
      <input type="submit" name="submit" value="Submit" />
    </form>
   
   <?php
    // onderstaande gebruiken om tijdelijk users aan te maken. Weer uitzetten om misbruik te voorkomen.
    // <a href="new_user.php">Create new account</a>
    ?>
  </div>
</div>

<?php include("includes/layouts/footer.php"); ?>
