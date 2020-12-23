<?php
/**
 * Template Name: WordPress Form
 */
ob_start();
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

if (! function_exists('wf_get_steps')){
	function wf_get_steps($user_id){
		global $wpdb;

		$sql = "
		 SELECT date,distance,duration,time,exercise,notes
		 FROM wp_usersteps
		 WHERE  user_id=$user_id
	 ";

	  return $wpdb->get_results($sql);
		}

	}


?>
<!DOCTYPE>
<html>
<head>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.min.js" integrity="sha512-d9xgZrVZpmmQlfonhQUvTR7lMPtO7NkZMkA0ABN3PHCbKA5nqylQ/yWlFAyY6hYgdF1Qh6nYiuADWwKB4C2WSw==" crossorigin="anonymous"></script>
  <script>
  
	/*
*   Rounded Rectangle Extension for Bar Charts and Horizontal Bar Charts
*   Tested with Charts.js 2.7.0
*/
Chart.elements.Rectangle.prototype.draw = function() {

    var ctx = this._chart.ctx;
    var vm = this._view;
    var left, right, top, bottom, signX, signY, borderSkipped, radius;
    var borderWidth = vm.borderWidth;

    // If radius is less than 0 or is large enough to cause drawing errors a max
    //      radius is imposed. If cornerRadius is not defined set it to 0.
    var cornerRadius = this._chart.config.options.cornerRadius;
    if(cornerRadius < 0){ cornerRadius = 0; }
    if(typeof cornerRadius == 'undefined'){ cornerRadius = 0; }

    if (!vm.horizontal) {
        // bar
        left = vm.x - vm.width / 2;
        right = vm.x + vm.width / 2;
        top = vm.y;
        bottom = vm.base;
        signX = 1;
        signY = bottom > top? 1: -1;
        borderSkipped = vm.borderSkipped || 'bottom';
    } else {
        // horizontal bar
        left = vm.base;
        right = vm.x;
        top = vm.y - vm.height / 2;
        bottom = vm.y + vm.height / 2;
        signX = right > left? 1: -1;
        signY = 1;
        borderSkipped = vm.borderSkipped || 'left';
    }

    // Canvas doesn't allow us to stroke inside the width so we can
    // adjust the sizes to fit if we're setting a stroke on the line
    if (borderWidth) {
        // borderWidth shold be less than bar width and bar height.
        var barSize = Math.min(Math.abs(left - right), Math.abs(top - bottom));
        borderWidth = borderWidth > barSize? barSize: borderWidth;
        var halfStroke = borderWidth / 2;
        // Adjust borderWidth when bar top position is near vm.base(zero).
        var borderLeft = left + (borderSkipped !== 'left'? halfStroke * signX: 0);
        var borderRight = right + (borderSkipped !== 'right'? -halfStroke * signX: 0);
        var borderTop = top + (borderSkipped !== 'top'? halfStroke * signY: 0);
        var borderBottom = bottom + (borderSkipped !== 'bottom'? -halfStroke * signY: 0);
        // not become a vertical line?
        if (borderLeft !== borderRight) {
            top = borderTop;
            bottom = borderBottom;
        }
        // not become a horizontal line?
        if (borderTop !== borderBottom) {
            left = borderLeft;
            right = borderRight;
        }
    }

    ctx.beginPath();
    ctx.fillStyle = vm.backgroundColor;
    ctx.strokeStyle = vm.borderColor;
    ctx.lineWidth = borderWidth;

    // Corner points, from bottom-left to bottom-right clockwise
    // | 1 2 |
    // | 0 3 |
    var corners = [
        [left, bottom],
        [left, top],
        [right, top],
        [right, bottom]
    ];

    // Find first (starting) corner with fallback to 'bottom'
    var borders = ['bottom', 'left', 'top', 'right'];
    var startCorner = borders.indexOf(borderSkipped, 0);
    if (startCorner === -1) {
        startCorner = 0;
    }

    function cornerAt(index) {
        return corners[(startCorner + index) % 4];
    }

    // Draw rectangle from 'startCorner'
    var corner = cornerAt(0);
    ctx.moveTo(corner[0], corner[1]);

    for (var i = 1; i < 4; i++) {
        corner = cornerAt(i);
        nextCornerId = i+1;
        if(nextCornerId == 4){
            nextCornerId = 0
        }

        nextCorner = cornerAt(nextCornerId);

        width = corners[2][0] - corners[1][0];
        height = corners[0][1] - corners[1][1];
        x = corners[1][0];
        y = corners[1][1];

        var radius = cornerRadius;
        // Fix radius being too large
        if(radius > Math.abs(height)/2){
            radius = Math.floor(Math.abs(height)/2);
        }
        if(radius > Math.abs(width)/2){
            radius = Math.floor(Math.abs(width)/2);
        }

        if(height < 0){
            // Negative values in a standard bar chart
            x_tl = x;           x_tr = x+width;
            y_tl = y+height;    y_tr = y+height;

            x_bl = x;           x_br = x+width;
            y_bl = y;           y_br = y;

            // Draw
            ctx.moveTo(x_bl+radius, y_bl);
            ctx.lineTo(x_br-radius, y_br);
            ctx.quadraticCurveTo(x_br, y_br, x_br, y_br-radius);
            ctx.lineTo(x_tr, y_tr+radius);
            ctx.quadraticCurveTo(x_tr, y_tr, x_tr-radius, y_tr);
            ctx.lineTo(x_tl+radius, y_tl);
            ctx.quadraticCurveTo(x_tl, y_tl, x_tl, y_tl+radius);
            ctx.lineTo(x_bl, y_bl-radius);
            ctx.quadraticCurveTo(x_bl, y_bl, x_bl+radius, y_bl);

        }else if(width < 0){
            // Negative values in a horizontal bar chart
            x_tl = x+width;     x_tr = x;
            y_tl= y;            y_tr = y;

            x_bl = x+width;     x_br = x;
            y_bl = y+height;    y_br = y+height;

            // Draw
            ctx.moveTo(x_bl+radius, y_bl);
            ctx.lineTo(x_br-radius, y_br);
            ctx.quadraticCurveTo(x_br, y_br, x_br, y_br-radius);
            ctx.lineTo(x_tr, y_tr+radius);
            ctx.quadraticCurveTo(x_tr, y_tr, x_tr-radius, y_tr);
            ctx.lineTo(x_tl+radius, y_tl);
            ctx.quadraticCurveTo(x_tl, y_tl, x_tl, y_tl+radius);
            ctx.lineTo(x_bl, y_bl-radius);
            ctx.quadraticCurveTo(x_bl, y_bl, x_bl+radius, y_bl);

        }else{
            //Positive Value
            ctx.moveTo(x + radius, y);
            ctx.lineTo(x + width - radius, y);
            ctx.quadraticCurveTo(x + width, y, x + width, y + radius);
            ctx.lineTo(x + width, y + height);
            ctx.quadraticCurveTo(x + width, y + height, x + width - radius, y + height);
						//ctx.fillRect(x,y,x + radius, y + height);
            ctx.lineTo(x, y + height);
            ctx.quadraticCurveTo(x, y + height, x, y + height - radius);
            ctx.lineTo(x, y + radius);
            ctx.quadraticCurveTo(x, y, x + radius, y);
        }
    }

    ctx.fill();
    if (borderWidth) {
        ctx.stroke();
    }
};
	</script>
	<script>





// round corners
var rounded_corners = Chart.pluginService.register({
		id:"rounded_circle",
    afterUpdate: function (chart) {
        if (chart.config.options.elements.arc.roundedCornersFor !== undefined) {
            var arc = chart.getDatasetMeta(0).data[chart.config.options.elements.arc.roundedCornersFor];
            arc.round = {
                x: (chart.chartArea.left + chart.chartArea.right) / 2,
                y: (chart.chartArea.top + chart.chartArea.bottom) / 2,
                radius: (chart.outerRadius + chart.innerRadius) / 2,
                thickness: (chart.outerRadius - chart.innerRadius) / 2 - 1,
                backgroundColor: arc._model.backgroundColor
            }
        }
    },

    afterDraw: function (chart) {
        if (chart.config.options.elements.arc.roundedCornersFor !== undefined) {
            var ctx = chart.chart.ctx;
            var arc = chart.getDatasetMeta(0).data[chart.config.options.elements.arc.roundedCornersFor];
            var startAngle = Math.PI / 2 - arc._view.startAngle;
            var endAngle = Math.PI / 2 - arc._view.endAngle;

            ctx.save();
            ctx.translate(arc.round.x, arc.round.y);
            ctx.fillStyle = arc.round.backgroundColor;
            ctx.beginPath();
            ctx.arc(arc.round.radius * Math.sin(startAngle), arc.round.radius * Math.cos(startAngle), arc.round.thickness, 0, 2 * Math.PI);
            ctx.arc(arc.round.radius * Math.sin(endAngle), arc.round.radius * Math.cos(endAngle), arc.round.thickness, 0, 2 * Math.PI);
            ctx.closePath();
            ctx.fill();
            ctx.restore();
        }
    },
});

var draw_number = Chart.pluginService.register({
	id:"number",
  beforeDraw: function(chart) {
    var width = chart.chart.width,
        height = chart.chart.height,
        ctx = chart.chart.ctx;

    ctx.restore();
    var fontSize = (height / 114).toFixed(2);
    ctx.font = fontSize + "em sans-serif";
    ctx.textBaseline = "middle";

    var text = chart.config.options.number_text,
        textX = Math.round((width - ctx.measureText(text).width) / 2),
        textY = height / 2;

    ctx.fillText(text, textX, textY);
    ctx.save();
  }
});




	function graph(){
				const data = <?php echo json_encode(wf_get_steps($user_id));?>;
				//0=Sunday, 6=Saturday
				var week = [0,1,2,3,4,5,6];
				var data_steps = [];
				var data_date = [];
				var data_week = [0,0,0,0,0,0,0];

				for(var i=0; i < data.length;i++){
					data_steps.push(parseInt(data[i].distance));
					data_date.push(data[i].date);
				}
				//For display of date
				for(var i = 0; i < week.length; i++){
					for(var j = 0; j < data_date.length; j++){
						var day = new Date(data_date[j]);
						if(day.getDay() == week[i]){
							data_week[i]=data_steps[j];
							break;
						}
					}
				}

				const ctx = document.getElementById('bar-graph').getContext('2d');
				const myChart = new Chart(ctx, {
				    type: 'bar',

				    data: {
				        labels: ['S','M','T','W','Th','F','Sa'],
				        datasets: [{
				            label: '# of Miles',
				            data: data_week,
				            backgroundColor:"rgba(243,130,0,1)",
										barPercentage:0.1,
				            borderWidth: 0
				        }]
				    },
				    options:{
							 plugins:{
								 number:false,
								 rounded_circle:false
							 },
							 cornerRadius:100,
							 responsive:true,

				        scales: {
				            yAxes: [{
												gridLines:{
													display:false,
													drawBorder:false
												},
				                ticks: {
														display:false,
				                    beginAtZero: true
				                }
				            }],
										xAxes:[{
											gridLines:{
												display:false
											}

										}]
				        }
				    }
				});
			}
</script>
</head>


	<!--FORM -->
<body>
    <div id="is-member">
        <script>
            var is_member = <?php echo json_encode($is_member);?>;
            var wrapper = document.getElementById("is-member");
            var myHTML = ''
            console.log(is_member);
            if(!is_member){
                myHTML = '<h3>You are not a member. Only members are able to update and track their streak information. Consider starting your challenge today!</h3>';
            }
            wrapper.innerHTML = myHTML;
            
        </script>
    </div>

<h1>My Profile</h1>

<div class="circle_charts">
	<div class='chart-title'> <h3>Progress</h3>
	<button id="submit-workout" onclick="location.href='https://crushcovidstreakchallenge.com/add-new-workout/'">+ New Workout</button></div>
	<canvas class="circle" id="stats" width='50'></canvas>
	<canvas class="circle" id="myChart" width='50'></canvas>


	<!-- <form action="" method="post">
		<label for="user-progress">Miles
			<input id="user-progress" type="text" name="user-progress" value="">
		</label>
		<input type='submit' name='submit' value='Submit'>
	</form> -->
</div>

<div class="bar">
	<div class='chart-title'> <h3>Statistics</h3> </div>
	<canvas id="bar-graph" width='100' height = '50'></canvas>
</div>

<div class="feed">
	<div class='chart-title'><h3>Recent Activity</h3></div>

	<div class='feed-display' id='activity'></div>
	<script>
     
	const data = <?php echo json_encode(wf_get_steps($user_id));?>;

  var wrapper = document.getElementById("activity");

  var myHTML = '';

	var length = 0;

	if (data.length == 0){
		myHTML += `<div class="feed-items">
							 <div class = "feed-info">
							 <h3 class="feed-distance">0 Mi</h3>
							 <h3 class="feed-duration">0 min</h3>
							 </div>
							 <h4 class="feed-date">No Record</h4>
						 </div>`;
	}
	else if (data.length >= 3) {
		length = 3;
	}
	else{
		length = data.length;
	}

	  for (var i = data.length-1; i >= data.length-length; i--) {
	    myHTML += `<div class="feed-items">
									<div class = "feed-info">
									<h3 class="feed-distance">${data[i].distance} Mi</h3>
									<h3 class="feed-duration">${data[i].duration} min</h3>
									</div>
									<h4 class="feed-date">${data[i].date}</h4>
								</div>`;
	  }


      wrapper.innerHTML = myHTML;

	</script>
</div>
</body>
<script>
//DRAW CIRCLE CHARTS

function drawCircleDistance(data){
	var distance = 0;
    for(var i=0; i < data.length;i++){
    	distance = distance + parseInt(data[i].distance);
    	//data_date.push(data[i].date);
	}


	var distance_target = 30;
	var distance_total = distance;


	var data_distance = {
	  datasets: [
	    {
	      data: [distance_total, distance_target - distance_total],
	      backgroundColor: [
	        "rgba(0,255,0,1)",
	        "rgba(211,211,211,0.4)"
	      ],
				hoverBackgroundColor:[
					"rgba(0,255,0,1)",
	        "rgba(211,211,211,0.4)"
				],
				borderWidth:[0,0]
	    }]
	};

new Chart(document.getElementById('myChart'), {
  type: 'doughnut',
  data: data_distance,
  options: {
		number_text:distance + " miles",
		elements:{
			arc:{
				roundedCornersFor:0
			}
		},
		cutoutPercentage: 88,
		animation: {
				animationRotate: true,
				duration: 2000
		},
		legend: {
				display: false
		},
		tooltips: {
				enabled: false
		},
		responsive: true,
	}
})};

drawCircleDistance(data);


var days = 30;
var days_used = data.length;


var data_days = {
  datasets: [
    {
      data: [days_used, days - days_used],
      backgroundColor: [
        "rgba(0,255,0,1)",
        "rgba(211,211,211,0.4)"
      ],
			hoverBackgroundColor:[
				"rgba(0,255,0,1)",
        "rgba(211,211,211,0.4)"
			],
			borderWidth:[0,0]
    }]
};

//Days Circle Chart
var promisedDeliveryChart = new Chart(document.getElementById('stats'), {
  type: 'doughnut',
  data: data_days,
  options:{
		number_text:data.length + " days",
		elements:{
			arc:{
				roundedCornersFor:0
			}
		},
		cutoutPercentage: 88,
		animation: {
				animationRotate: true,
				duration: 2000
		},
		legend: {
				display: false
		},
		tooltips: {
				enabled: false
		},
		responsive: true,
	}

});



//DRAW BAR GRAPH
</script>
<script>
graph();
//circle();</script>
<?php
print_r($user_roles);
echo $is_member;
//$user = wp_get_current_user();

//print_r ($user->roles);
get_footer();
?>


</html>
