<?php

// Function to calculate distance between two points using Pythagorean theorem
function calculateDistance($lat1, $lon1, $lat2, $lon2) {
  $lat1 = deg2rad($lat1);
  $lon1 = deg2rad($lon1);
  $lat2 = deg2rad($lat2);
  $lon2 = deg2rad($lon2);

  $deltaLat = $lat2 - $lat1;
  $deltaLon = $lon2 - $lon1;

  $a = pow(sin($deltaLat / 2), 2) + cos($lat1) * cos($lat2) * pow(sin($deltaLon / 2), 2);
  $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

  $distance = 6371 * $c;

  return $distance;
}

// Set user's latitude and longitude
$userLat = $_POST['latitude'];
$userLon = $_POST['longitude'];

// Query the database for all locations

$hour = intval(substr(date('Hi'), 0, 2));
$minute = intval(substr(date('Hi'), 2, 2));

if (date('D') == 'Sun' || ($hour > 19) || ($hour < 9)){
	$query = "SELECT id, latitude, longitude FROM night_pharmacy";
} else {
	$query = "SELECT id, latitude, longitude FROM pharmacy_data";
}


$conn = mysqli_connect("localhost","mkaganc", "JJ8T_gubL?E_", "pharmacy","33060");

if (mysqli_connect_errno()) {
  // Connection failed
    echo "Connection failed " . mysqli_connect_error() . "\n";
    exit();
} else {
  // Connection succeeded
  // Continue with your code here...
}

$result = mysqli_query($conn, $query);

if (!$result) { // Check for query errors
  echo "Query failed: " . mysqli_error($conn) . "\n";
  exit();
}


// Initialize an array to store the distances from the user
$distances = array();

// Calculate the distance from the user for each location
while ($row = mysqli_fetch_assoc($result)) {
  $id = $row['id'];
  $lat = $row['latitude'];
  $lon = $row['longitude'];
  $distance = calculateDistance($userLat, $userLon, $lat, $lon);
  $distances[$distance] = array('id' => $id, 'latitude' => $lat, 'longitude' => $lon);
}

// Sort the distances in ascending order
ksort($distances);

// Get the nearest 3 locations
$nearestLocations = array_slice($distances, 0, 3, true);
$measure_keys = array_keys($nearestLocations);

$measure1 = $measure_keys[0];
$measure2 = $measure_keys[1];
$measure3 = $measure_keys[2];

$count = 1;
// Print the nearest location IDs
foreach ($nearestLocations as $distance => $location) {
  
	$id = $location['id'];
	$id_array[] = $id;
}

//  $get_query = "SELECT name, address, location_link, tel_link FROM pharmacy_data WHERE id IN $id_array";
  $get_query = "SELECT name, address, location_link, tel_link FROM pharmacy_data WHERE id IN (" . implode(',', $id_array) . ")";

  $final_result = mysqli_query($conn, $get_query);  


if (!$final_result) { // Check for query errors
    echo "Query failed: " . mysqli_error($conn) . "\n";
    die();
}


  while ($row = mysqli_fetch_assoc($final_result)) {
    
    $name = "name" . $count;
    $$name = $row['name'];

    $loc = "loc" . $count;
    $$loc = $row['address'];
 
    $loc_url = "loc_url" . $count;
    $$loc_url = $row['location_link'];

    $phone = "phone" . $count;
    $$phone = $row['tel_link'];

    $count++;
  }
  mysqli_free_result($result);
  
  $results = json_encode([$name1,$name2,$name3,$loc1,$loc2,$loc3,$loc_url1,$loc_url2,$loc_url3,$phone1,$phone2,$phone3,$measure1, $measure2, $measure3, $userLat, $userLon]);

  echo $results;
	  $conn -> close();
?>