<!doctype html>
<html lang="en">
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Spiderman discovered hosts</title>
<meta name="description" content="List of hosts discovered by spiderman">
<meta name="author" content="Matthias">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
<link rel="stylesheet" href="spiderman.css?v=1.0">
</head>

<body>
<h1>List of discovered hosts</h1>

<div>
This is the list of hosts yet discovered by spiderman web spider.
</div>
<?php
$servername = "";
$username = "";
$password = "";
$dbname = "";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT * FROM active_hosts";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
  echo "<table class='table'>";
  echo "<tr><th>Timestamp</th><th>IPv4</th><th>Port</th><th>Hostname</th><tr>";
  echo "<tbody>";
  // output data of each row
  while($row = $result->fetch_assoc()) {
    echo "<tr><td>" . $row["timestamp"] . "</td><td><a href=\"http://" . $row["ip"] . ":" . $row["port"] . "\">" . $row["ip"] . "</a></td><td> " . $row["port"] . "</td><td>" . $row["hostname"] . "</td></tr>";
  }
  echo "</tbody>";
} else {
  echo "<div>No hosts discovered</div>";
}
$conn->close();
?>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js" integrity="sha384-IQsoLXl5PILFhosVNubq5LC7Qb9DXgDA9i+tQ8Zj3iwWAwPtgFTxbJ8NT4GN1R8p" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js" integrity="sha384-cVKIPhGWiC2Al4u+LWgxfKTRIcfu0JTxR+EQDz/bgldoEyl4H0zUF0QKbrJ0EcQF" crossorigin="anonymous"></script>
<script src="spiderman.js"></script>
</body>