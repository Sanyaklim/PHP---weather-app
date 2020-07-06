<?php
require_once('baza.php');


function curl($url) {
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);	
curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);	
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$data = curl_exec($ch);
curl_close($ch);
return $data;	
}
$city = '';
$weather = '';
$error = '';
if(isset($_POST['city'])) {
	$urlContents=curl("https://api.openweathermap.org/data/2.5/weather?q=".$_POST['city']."&appid=8d6f5d3654d302cbcf7f64549504c0a0");
	$weatherArray = json_decode($urlContents, true);
	if($urlContents == false) {
			$error = 'Last update 1 hour ago';
		  $stmt = $conn->prepare("SELECT city, weather FROM api WHERE city=:city ORDER BY id DESC LIMIT 1");
			$stmt->bindParam(':city', $_POST['city']);
		 $stmt->execute();	
		 if($row = $stmt->fetch()) {
			$weather = $row['weather'];
		 } else {
			$error = 'Please enter an existing city';
		 }
		
	} else {
		if(isset($weatherArray['weather'])) {
			$weather = "The weather in ".$_POST['city']." is currently ".$weatherArray['weather'][0]['description'].".";
			$tempInFahrenheit = ceil($weatherArray['main']['temp']* 9/5 - 506.67);
			$weather .= " The temperature is " .$tempInFahrenheit. " &deg;C. " ;
		} else {
			$error = 'Please enter an existing city';
		}
	}
}

if(isset($_POST['city']) && $error==''){
	$city = $_POST['city'];
  $stmt = $conn->prepare("INSERT INTO api (city,weather) VALUES (:city, :weather)");
	$stmt->bindParam(':city', $city);
	$stmt->bindParam(':weather', $weather);
 $stmt->execute();	
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Weather API</title>
	<link rel="stylesheet" href="style.css">
	<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300&display=swap" rel="stylesheet">
</head>
<body>
	<div>
		<h1>Local Weather Application</h1>
		<div id="app">
			<form method="post">
				<div class="form-group">
					<label for="city"></label>
					<input type="text" class="form-control" id="city" name="city" aria-describedby="city" placeholder="E.g New York, Tokyo"><button type="submit" class="btn btn-primary">OK</button>
				</div>
			</form>
			<div id="weather">
				<?php
				if($weather) {
					echo '<div class="alert alert-success" role="alert">'.$weather.'</div>';
				}
				if($error) {
					echo '<div class="alert alert-danger" role="alert">'.$error.'</div>';
				}
				?>
			</div>
		</div>
	</div>
</body>
</html>
