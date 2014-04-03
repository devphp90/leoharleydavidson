<?php 
include(dirname(__FILE__) . "/../../_includes/config.php");
include(dirname(__FILE__) . "/../../_includes/validate_session.php");

$id = (int)$_GET['id'];

// Find Scorm Name
if (!$result = $mysqli->query('SELECT 
df.id_product_downloadable_files
FROM
orders_item_product_downloadable_files AS df
INNER JOIN
orders_item_product AS oip
ON
(df.id_orders_item_product = oip.id)
INNER JOIN
orders_item AS oi
ON
(oip.id_orders_item = oi.id)
INNER JOIN
orders AS o
ON
(oi.id_orders = o.id)

WHERE
df.id  = "'.$id.'" 
AND
o.status NOT IN (-1,0)
LIMIT 1')) throw new Exception('An error occured while trying to check course.'."\r\n\r\n".$mysqli->error);

if ($result->num_rows) {
	$row = $result->fetch_assoc();
	$SCOInstanceID = $row['id_product_downloadable_files'];
}

if (!$SCOInstanceID) {
	exit('<script type="text/javascript">window.parent.location.href="/404?error=invalid_course";</script>');
}

$course_full_path = realpath(dirname(__FILE__).'/../../').'/courses/scorm/'.$SCOInstanceID.'/';		
$course_path = '/courses/scorm/'.$SCOInstanceID.'/';

// check manifesto
if (!is_file($course_full_path.'imsmanifest.xml')) exit('<script type="text/javascript">alert("Error, manifest file not found!");</script>');

$contents = file_get_contents($course_full_path.'imsmanifest.xml');

// get mastery_score
preg_match('/<adlcp:masteryscore>([0-9]+)<\/adlcp:masteryscore>/is',$contents,$matches);

$masteryscore = sizeof($matches) ? ($matches[1] ? $matches[1]:0):0;

// load xml
$manifest = new SimpleXMLElement($contents);

// get path to course
if (!is_file($course_full_path.$manifest->resources[0]->resource[0]['href'])) exit('<script type="text/javascript">alert("Error, course not found!");</script>');

$course_path .= $manifest->resources[0]->resource[0]['href'];

unset($manifest);
$manifest = NULL;

$id_customer_courses_scorm = (int)$_SESSION['customer']['id_customer_courses_scorm'];
$current_datetime = date('Y-m-d H:i:s');
$id_customer = (int)$_SESSION['customer']['id'];
$customer = $_SESSION['customer']['name'];

// check course in db
// if session check if exists
if ($id_customer_courses_scorm) { 
	if (!$result = $mysqli->query('SELECT * FROM customer_courses_scorm WHERE id = "'.$id_customer_courses_scorm.'" AND id_customer = "'.$id_customer.'" AND id_course = "'.$SCOInstanceID.'" LIMIT 1')) throw new Exception('An error occured while trying to lookup course table.');
	$row = $result->fetch_assoc();
	$result->free();
		
	if ($row['id'] && $row['date_end'] == '0000-00-00 00:00:00') {
		$id_customer_courses_scorm = $row['id'];
		$_SESSION['customer']['id_customer_courses_scorm'] = $id_customer_courses_scorm;		
	} else {
		$id_customer_courses_scorm = 0; 
		$_SESSION['customer']['id_customer_courses_scorm'] = 0;
		$row = array();
	}
} 

if (!$id_customer_courses_scorm) {
	if (!$result = $mysqli->query('SELECT * FROM customer_courses_scorm WHERE id_customer = "'.$id_customer.'" AND id_course = "'.$SCOInstanceID.'" AND date_end = "0000-00-00 00:00:00" LIMIT 1')) throw new Exception('An error occured while trying to lookup course table.');
	
	$row = $result->fetch_assoc();
	$result->free();
	
	if ($row['id'] && $row['date_end'] == '0000-00-00 00:00:00') {
		$id_customer_courses_scorm = $row['id'];
		$_SESSION['customer']['id_customer_courses_scorm'] = $id_customer_courses_scorm;		
	} else if (!$row['id']) {
		$id_customer_courses_scorm = 0; 
		$_SESSION['customer']['id_customer_courses_scorm'] = 0;		
		$row = array();		
	}
}

// check session
if (!$id_customer_courses_scorm && isset($_GET['task']) && $_GET['task'] == 'commit' || $row['id'] && $row['date_end'] && $row['date_end'] != '0000-00-00 00:00:00') {
	exit;
}

// set session variable
$data = !empty($row['data']) ? unserialize(base64_decode($row['data'])):array();

// no course in session
if (!$id_customer_courses_scorm) {	
	$mysqli->query('INSERT INTO 
	customer_courses_scorm 
	SET 
	id_customer = "'.$id_customer.'",
	id_course = "'.$SCOInstanceID.'",
	lesson_status = "not attempted",
	date_start = "'.$current_datetime.'"');
	
	$id_customer_courses_scorm = $mysqli->insert_id;
	$data = array();
	
	$_SESSION['customer']['id_customer_courses_scorm'] = $id_customer_courses_scorm;
}

// if data is empty, set default variables
if (empty($data)) {
	$data = array(
		// elements that tell the SCO which other elements are supported by this API
		'cmi.core._children' => 'student_id,student_name,lesson_location,credit,lesson_status,entry,score,total_time,exit,session_time',
		'cmi.core.score._children' => 'raw',
		
		// student information
		'cmi.core.student_name' => $customer,
		'cmi.core.student_id' => $id_customer,
		
		// test score
		'cmi.core.score.raw' => 0,
		
		// adlcp:masteryscore (get from manifest
		'cmi.student_data.mastery_score' => $masteryscore,				

		// SCO launch and suspend data
		'cmi.launch_data' => '',
		'cmi.suspend_data' => '',

		// progress and completion tracking
		'cmi.core.lesson_location' => '',
		'cmi.core.credit' => 'credit',
		'cmi.core.lesson_status' => 'not attempted',
		'cmi.core.entry' => 'ab-initio',
		'cmi.core.exit' => '',

		// seat time
		'cmi.core.total_time' => '0000:00:00',
		'cmi.core.session_time' => '',	
	);	
} 

if (isset($_GET['task'])) {
	switch ($_GET['task']) {
		case 'commit':
			// read SCOInstanceID			
			$data = $_POST['cache'];
			if (!is_array($data)) $data = array();			
			if ($data['cmi.core.exit'] == 'suspend') $data['cmi.core.exit'] = 'resume';
			
			$mysqli->query('UPDATE 
			customer_courses_scorm
			SET
			score = "'.$mysqli->escape_string($data['cmi.core.score.raw']).'",
			lesson_status = "'.$mysqli->escape_string($data['cmi.core.lesson_status']).'",
			data = "'.base64_encode(serialize($data)).'"
			WHERE
			id = "'.$id_customer_courses_scorm.'"
			LIMIT 1');
			
			// return value to the calling program
			echo "true";
			exit;
			break;
		case 'finish':		
			if ($data['cmi.core.lesson_status'] != 'incomplete' && $data['cmi.core.lesson_status'] != 'not attempted') {
				$mysqli->query('UPDATE customer_courses_scorm SET date_end = "'.date('Y-m-d H:i:s').'" WHERE id = "'.$id_customer_courses_scorm.'" LIMIT 1');
				
				// If scorm certificate is activate in the application config table, we will put code in this file
				include('certificate.php');
			}
				
			// return value to the calling program
			echo "true";	
			exit;	
			break;
	}
}
?>
<html>
<head>
<title></title>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.1/jquery.min.js"></script> 
<script language="javascript">

// ------------------------------------------
//   Status Flags
// ------------------------------------------
var flagFinished = false;
var flagInitialized = false;
// ------------------------------------------
//   SCO Data Cache - Initialization
// ------------------------------------------
var cache = new Object();
var closing=0;

<?php
if (isset($data) && is_array($data) && sizeof($data)) {	
	foreach ($data as $key => $row) {
		echo 'cache["'.$key.'"] = "'.$row.'";'."\r\n";
	}
}
?>

// ------------------------------------------
//   SCORM RTE Functions - Initialization
// ------------------------------------------
function LMSInitialize(dummyString) {
 
	// already initialized or already finished
	if ((flagInitialized) || (flagFinished)) { return "false"; }

	// set initialization flag
	flagInitialized = true;

	// return success value
	return "true";
 
}

// ------------------------------------------
//   SCORM RTE Functions - Getting and Setting Values
// ------------------------------------------
function LMSGetValue(varname) {
	// not initialized or already finished
	if ((! flagInitialized) || (flagFinished)) { return "false"; }

	// otherwise, return the requested data
	return cache[varname] ? cache[varname]:"";

}

function LMSSetValue(varname,varvalue) {
	// not initialized or already finished
	if ((! flagInitialized) || (flagFinished)) { return "false"; }

	// otherwise, set the requested data, and return success value
	cache[varname] = varvalue;
	return "true";

}

// ------------------------------------------
//   SCORM RTE Functions - Saving the Cache to the Database
// ------------------------------------------
function LMSCommit(dummyString) {	
	// not initialized or already finished
	if ((! flagInitialized) || (flagFinished)) { return "false"; }
	
	var result="false";

	// code to prevent caching
	var d = new Date();
	
	// create a POST-formatted list of cached data elements 
	// include only SCO-writeable data elements
	var params = [];
	
	$.each(cache, function(key, value){
		params.push("cache["+key+"]="+htmlEncode(value));		
	});
	
	$.ajax({
		url: '<?php echo $_SERVER['PHP_SELF']; ?>?SCOInstanceID=<?php echo $SCOInstanceID; ?>&task=commit',
		type: 'post',
		data: params.join("&"),
		async: false,
		cache: false,
		success: function(data){
			result = "true";
		},
		error: function(jqXHR, textStatus, errorThrown){
			if (jqXHR && jqXHR.responseText) alert(jqXHR.responseText);
			alert('Problem with AJAX Request in LMSCommit()');
			result = "false";
		}
	});
	
	return result;
}

// ------------------------------------------
//   SCORM RTE Functions - Closing The Session
// ------------------------------------------
function LMSFinish(dummyString) {
	// not initialized or already finished
	if ((! flagInitialized) || (flagFinished)) { return "false"; }
	
	var result="false";

	// commit cached values to the database
	//LMSCommit('');

	// code to prevent caching
	var d = new Date();
	
	$.ajax({
		url: '<?php echo $_SERVER['PHP_SELF']; ?>?SCOInstanceID=<?php echo $SCOInstanceID; ?>&task=finish',
		async: false,
		cache: false,
		success: function(data){
			// set finish flag
			flagFinished = true;
		
			// return to calling program
			result = "true";
		},
		error: function(jqXHR, textStatus, errorThrown){	
			if (jqXHR && jqXHR.responseText) alert(jqXHR.responseText);
			alert('Problem with AJAX Request in LMSFinish()');
			result = "false";
		}
	});	 
	
	return result;
}

// ------------------------------------------
//   SCORM RTE Functions - Error Handling
// ------------------------------------------
function LMSGetLastError() {
	return 0;
}

function LMSGetDiagnostic(errorCode) {
	return "diagnostic string";
}

function LMSGetErrorString(errorCode) {
	return "error string";
}

function htmlEncode(value) {    
	if (value) { return jQuery('<div />').text(value).html(); } 
	else { return ''; }
} 

$(function(){
	// load course
	setTimeout(function(){parent.document.getElementById("course").src = "<?php echo $course_path; ?>";},1500);
	/*
	window.onbeforeunload = function() { 
		if (!closing) {
			closing = 1;	
			
			LMSFinish('');	
		} else return false;
	}*/
});
</script>
</head>
<body>

<p>&nbsp;</p>

</body>
</html>