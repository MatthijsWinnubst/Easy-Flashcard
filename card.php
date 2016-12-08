<?php require_once("includes/headers.php"); ?>
<?php require_once("includes/session.php"); ?>
<?php require_once("includes/db_connection.php"); ?>
<?php require_once("includes/functions.php"); ?>
<?php require_once("includes/validation_functions.php"); ?>
<?php confirm_logged_in(); ?>
<?php $user_id = $_SESSION['user_id'];?>
<?php confirm_questionstack($user_id); ?>
<?php $nr_questioncards = number_of_cards_in_questionstack($user_id)-1;         // -1 because that is the active card  ?>
<?php $nr_processedcards = number_of_cards_in_processedstack($user_id);?>
<?php $nr_rightcards = number_of_right_cards_in_processedstack($user_id);?>
<?php $nr_wrongcards = number_of_wrong_cards_in_processedstack($user_id);?>
<?php $nr_newcards = number_of_new_cards_in_processedstack($user_id);?>
<?php $nr_unclassifiedcards = number_of_unclassified_cards_in_processedstack($user_id);?>

<?php
// multi purpose form //

// process POST

	if (isset($_POST['submit'])) {
	    // Process request
	    $card_id = get_activecard($user_id);
	    $card = fetch_card_properties($card_id);

	    	// validation op submit-values nog maken... 
	    if ($_POST['submit']=="next") {
	        deactivate_card($card_id);
	        if ($nr_questioncards == 0) {
	    		// no next cards -> redirect to dashboard
	        	redirect_actions("stackdashboard.php", "card");
	        } else {
	            redirect_actions("card.php", "card");
	        }
	    }
	    elseif ($_POST['submit']=="prev") {
	    	if ($nr_processedcards == 0) {    // gaat niet goed bij subset, bijv bij alleen wrong answers
	    		// no previous cards -> redirect to dashboard
	    		redirect_actions("stackdashboard.php", "card");
	    	} else {
	    		put_card_back_to_questionstack($card_id);
	    		// define previous card and activate it
	    		$prev_card_id = get_previous_card_id($user_id, $card_id);
	    		set_activecard($prev_card_id);   
	    		// open this previous card will happen in the body
	    		redirect_actions("card.php", "card");
	    	}
	    }
	    else {
	        $new_status = $_POST['submit'];
	        update_card_status($card_id, $new_status);
	        deactivate_card($card_id);
	    }
	    redirect_actions("card.php", "card");

	} else { 
		
// new page request (not a POST)

	    // redirect 'refresh' or 'back' to stackdashboard
	        if ($source_of_request=="") {
	             redirect_actions("stackdashboard.php", "card");
	        }   
	    // data-actions
	    if (activecard_exists($user_id)) {
	        // the session has previously been interrupted, or it is a 'previous' card (stack=2 is set)
	        $card_id = get_activecard($user_id);
	    } elseif (number_of_ordered_cards_in_questionstack($user_id) > 0) {
	    	// there exist ordered cards in the questionstack (because of previous-actions) -> handle these first
	    	$card_id = get_min_order_card_id($user_id);
	        set_activecard($card_id);
	    } else {
	        // Select 1 random card from the questionstack (stack=1) and add as highest order to the list
	        $card_id = select_random_card($user_id);
	        set_activecard($card_id);
	        set_ordernr($card_id, $user_id);
	    }
	    $card = fetch_card_properties($card_id);

// javascript

    include("includes/layouts/header.php");      // js libraries in header ?>
	<script>
	$(document).ready(function () {
		
		$(".fc_card-container").hide().fadeIn(700);
		$('.speakme').click(function (e) {
	
			var text = $('.answer').text();
			responsiveVoice.speak("" + text +"", "Greek Female");
			
			e.preventDefault();
			e.stopPropagation();
			
		});
	
	//$(".question").fitText(1, { minFontSize: '15px', maxFontSize: '40px' });
	//	$(".answer").fitText(1, { minFontSize: '15px', maxFontSize: '40px' });
	//	
	//	$('.answer').hide();
	//	$('.choose').hide();
	//	
	//	$('.turn').click(function (e) {
	//		e.preventDefault();
	//		$('.answer').show();
	//		$('.choose').show();
	//		$('.turn').hide();
	//		$('.flashcard').addClass('answered');
	//	});
	});
	</script>

<?php 
// process HTML
?>
	
	<body class="card">
	<div class="container"> <a href="stackdashboard.php">View stack dashboard</a>
		<form action="card.php" method="post">
			<div class="fc_card-container" data-ratio="2:1">
				<div class="fc_card fc_click" data-direction="top">
					<div class="fc_front fb">
						<div class="qena">
							<p class="question"><?php echo htmlentities($card['front_main']);?></p>
						</div>
					</div>
					<div class="fc_back fb">
						<a href="#" class="speakme"><i class="fa fa-volume-up"></i></a>
						<div class="qena">
							<p class="question"><?php echo htmlentities($card['front_main']);?></p>
							<p class="answer"><?php echo htmlentities($card['back_main']);?></p>
						</div>
					</div>
				</div>
				<div class="status stright">
					<button type="submit" name="submit" value="right" class="right choose" title="I knew this one"><i class="fa fa-thumbs-up"></i></button>
					<button type="submit" name="submit" value="wrong" class="wrong choose" title="I got it wrong"><i class="fa fa-thumb-tack"></i></button>
					<button type="submit" name="submit" value="next" class="next choose" title="Next"><?php echo $nr_questioncards ;?><i class="fa fa-arrow-right"></i></button>
				</div>
				<div class="status stleft">
					<label  class="right choose" ><?php echo $nr_rightcards;?><i class="fa fa-thumbs-up"></i></label>
					<label  class="wrong choose" ><?php echo $nr_wrongcards;?><i class="fa fa-thumb-tack"></i></label>
					<label  class="new choose" ><?php echo $nr_newcards;?><i class="fa-circle-o"></i></label>
					<button type="submit" name="submit" value="prev" class="prev choose" title="Previous"><?php echo $nr_processedcards;?><i class="fa fa-arrow-left"></i></button>
				</div>
			</div>
		</form>
	</div>

<?php include("includes/layouts/footer.php");  ?>
<?php } ?>
