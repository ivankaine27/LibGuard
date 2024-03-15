
<?php include 'includes/session.php'; ?>
<?php


if(isset($_GET["link"])) {
   $link = $_GET["link"]; // get temperature value from HTTP GET
   echo $link;
   $sql = "INSERT INTO link (link)
   SELECT * FROM (SELECT '$link') AS tmp
   WHERE NOT EXISTS (
       SELECT link FROM link WHERE link = '$link'
   ) LIMIT 1;";
   echo $sql;

   if ($connection->query($sql) === TRUE) {
      echo "New record created successfully";
   } else {
      echo "Error: " . $sql . " => " . $connection->error;
   }

   $connection->close();
} else {
   echo "temperature is not set in the HTTP request";
}
?>