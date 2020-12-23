<?php the_content(); ?>
<?php
/**
 * Template Name: Add New Workout
 */

get_header();
$user_id = get_current_user_id();
$user_meta=get_userdata($user_id);
$user_roles=$user_meta->roles;
$member_roles = ['member','administrator'];

$is_member = false;

foreach($user_roles as $role){
    if(in_array($role,$member_roles)){
        $is_member = true;
        break;
    }
}

$date = date('m/d/Y');



if (! function_exists('wf_insert_update_user_steps')){
	function wf_insert_update_user_steps($user_id,$date,$distance,$duration,$time, $exercise,$notes){
		global $wpdb;

		$sql = "
		 SELECT user_id,date
		 FROM wp_usersteps
		 WHERE  user_id=$user_id
		 AND date= %s
	 ";
	 $sql = $wpdb->prepare($sql,$date);
	 $date_exists = $wpdb->get_results($sql);

		if (count($date_exists) > 0){
			$sql = $wpdb->update(
			    'wp_usersteps',
			    array(
			         "distance" => $distance,  // integer (number)
							 "duration" => $duration,
							 "time" => $time,
							 "exercise" => $exercise,
							 "notes" => $notes
			    ),
			    array( "user_id" => $user_id,
				 				 "date" => $date),
			    array(
						//For user_id and date
						'%d',
						'%d',
						'%s',
						'%s',
						'%s'
			    ),
			    array(
						"%d",
						"%s"

						)
			);
		}
		else{
			$wpdb->insert("wp_usersteps", array("user_id" => $user_id, "date" => $date, "duration" => $duration, "distance" => $distance,"time" => $time,"exercise" => $exercise,"notes" => $notes), array("%d", "%s", "%d","%d","%s","%s","%s"));
		}

		//Add the user data to the database. If user data already exists, return false


		}
}
?>

<h1>Add New Workout</h1>


<div class="workout-form">
		<div class="workout-fields">
			<form action="" method="post">
			<label for="date">DATE</label>

			<input type="date" id="date-output" name="date"
       value= '<?php echo date('Y-m-d');?>'
       min="2020-12-01">


			<label for="time">TIME</label>
				<input id="time" type="time" name="time" value='<?php echo date("H:i")?>'>


			<label for="exercise">EXERCISE</label>
			<select id="exercise" name="exercise">
      <option value="Running">Running</option>
      <option value="Walking">Walking</option>
      <option value="Cyling">Cycling</option>
			<option value="Other Cardio">Other Cardio (Rowing, Elliptical, Zumba, etc)</option>
			<option value="Sports">Sports (Basketball, Tennis, Badminton, etc)</option>
			<option value="Resistance Training">Resistance Training (Crossfit, Bodyweight, Bands, etc)</option>
			<option value="Recharge">Recharge (Yoga, Stretching, etc)</option>
     </select>


			<label for="duration">DURATION</label>
				<input id="duration" type="number" name="duration" value="">


			<label for="distance">DISTANCE</label>
				<input id="distance" type="number" name="distance">


			<label for="notes">NOTES</label>
				<input id="notes" type="text" name="notes" value="Add description of workout">

			<input type='submit' name='submit' value="Submit">
			</form>
	</div>
</div>


<?php
//echo"<pre>";
//print_r ($user_roles);
if(isset($_POST['submit'])){
    if($is_member){
    	$date = (! empty($_POST['date'])) ? sanitize_text_field($_POST['date']) : "";
    	if(! empty($_POST['date'])){
    		$date = sanitize_text_field($_POST['date']);
    		$year = substr($date,0,4);
    		$month = substr($date,5,2);
    		$day = substr($date,8,2);
    
    		$date = $month."/".$day."/".$year;
    	}
    	else{
    		$date = "";
    	}
    	$duration = (! empty($_POST['duration'])) ? sanitize_text_field($_POST['duration']) : "";
    	$distance = (! empty($_POST['distance'])) ? sanitize_text_field($_POST['distance']) : "";
    	$time = (! empty($_POST['time'])) ? sanitize_text_field($_POST['time']) : "";
    	$exercise = (! empty($_POST['exercise'])) ? sanitize_text_field($_POST['exercise']) : "";
    	$notes = (! empty($_POST['notes'])) ? sanitize_text_field($_POST['notes']) : "";
    
    	wf_insert_update_user_steps($user_id,$date, $distance, $duration,$time,$exercise,$notes);
    
    	//$location = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    	//wp_safe_redirect($location);
    	exit;
    }
    else{
        echo '<script>alert("Only challenge members are able to log workout info!")</script>';
    }
}
get_footer();
?>
