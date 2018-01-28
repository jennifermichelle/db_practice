<?php
// Expose single db connection as object
function dbconnect() {
  // This creates a new object based on the mysqli class
  // Creating the object is analogous to initializing the connection
  $dbconnection = new mysqli("127.0.0.1", "jennifermichelle", "Wns56?0j06igKq&1", "JM_kutdb");

  // Check if there was an error initializing the connection
  if ( $dbconnection->connect_error ) {
    die("Connection failed: " . $dbconnection->connect_error);
  }

// Return the connection object
return $dbconnection;
}

// Test if request method is GET
if ($_SERVER['REQUEST_METHOD'] == "GET") {

  // If the GET requests query string is exactly "all"
  if ( $_SERVER['QUERY_STRING'] == "all") {
    $conn = dbconnect();
    $selectall = "SELECT * FROM Gegevens";
    $echoall = $conn->query($selectall);

    // Show all comments from all IPs
    while($row = $echoall->fetch_row()) {
      echo "<br /> IP address (" . $row[1] . ") placed the following comments:
      <br /> PostID: " . $row[0] . "<br /> Date: " . $row[2] . "<br /> Comment: " . $row[3] . "<br />";
    }
    $conn->close();

  } else { // If there is no query string, or it is anything but exactly "all"

    $conn = dbconnect();
    $ip = $_SERVER['REMOTE_ADDR'];
    $sqlselect = "SELECT postid, date, comment FROM Gegevens WHERE ip = '$ip' ";
    $result = $conn->query($sqlselect);

      // Show comments by that IP, if any
      if ($result->num_rows > 0) {
        echo "You ($ip) placed the following comments: <br />";
        while($row = $result->fetch_row()) {
          echo "<br /> PostID: " . $row[0] . "<br /> Date: " . $row[1] . "<br /> Comment: " . $row[2] . "<br />";
        }
        $conn->close();

      } else { // Otherwise, say there are none
        echo "You have not yet placed a comment with the following IP address: '$ip' <br />";
        echo "Please leave a comment by typing: comment=thisismycomment<br />";
      }
  }

} elseif ( $_SERVER['REQUEST_METHOD'] == "POST" ) { // Test if request method is POST

          switch (true) {
              // If there are no POST variables, send http 400 response
              case empty($_POST):
                  http_response_code(400);
                  echo "You did not enter a query. Please enter a comment by typing: comment=thisismycomment <br />";
                  break;
              // If "comment" key doesn't exist, send http 400 response
              case !array_key_exists("comment", $_POST):
                  http_response_code(400);
                  echo "You did not enter the right key. Please enter a comment by typing: comment=thisismycomment <br />";
                  break;
              // If "comment" var is empty
              case empty($_POST['comment']):
                  http_response_code(400);
                  echo "You did not enter a value. Please enter a comment by typing: comment=thisismycomment <br />";
                  break;
              // If "comment" var is not empty
              default:
              $conn = dbconnect();
              $ip = $_SERVER['REMOTE_ADDR'];
              $comment = $_POST['comment'];
              $insert = "INSERT INTO Gegevens (IP, DATE, COMMENT) VALUES ('$ip', NOW(), '$comment') ";
              $comment = mysqli_real_escape_string($conn, $comment);

              // Try to INSERT record, or show error
              if ( $conn->query($insert) === TRUE ) {
                echo "New record created successfully <br />";
                $conn->close();
              } else {
                echo "Error: " . $insert . "<br />" . $conn->error;
              }
              break;
          }
} else { // If request method is neither GET nor POST
  http_response_code(400);
  echo "Invalid request method. Request method must be GET or POST <br />";
}
?>
