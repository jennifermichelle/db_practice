<?php

include 'functions.php';

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
              // If "id" key exists
              case array_key_exists("id", $_POST):
              // Send http 400 response if required "password" key doesn't or values are empty
                  if ( empty($_POST["id"]) ) {
                    http_response_code(400);
                    echo "You did not enter a value. Please enter a valid ID <br />";
                  } elseif ( !array_key_exists("password", $_POST) ) {
                    http_response_code(400);
                    echo "Passsword is required to change comment. <br />";
                  } elseif ( empty($_POST["password"]) ) {
                    http_response_code(400);
                    echo "You did not enter a value. Please enter password <br />";
                  } else { // If required key exists and values are not empty
                    // Validates pwd and changes comment in case pwd is valid
                    validate_password();
                  }
                  break;
              // If "password" key exists
              case array_key_exists("password", $_POST):
                  // If var "password" is empty send http 400 response
                  if ( empty($_POST["password"]) ) {
                    http_response_code(400);
                    echo "You did not enter a value. Please enter password <br />";
                  } else { // If var "password" is not empty
                    dbupdate(); // Save new record AND save password
                  }
                  break;
              default:
                  dbupdate(); // Save new record
                  break;
          }
} else { // If request method is neither GET nor POST
  http_response_code(400);
  echo "Invalid request method. Request method must be GET or POST <br />";
}
?>
