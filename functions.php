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

// Create validate_password function
function validate_password() {
    $conn = dbconnect();
    $usrID = $_POST['id'];
    $pwdusr = md5($_POST['password']);
    $sqlpwd = "SELECT * FROM Passwords WHERE PostID = '$usrID' AND Password = '$pwdusr'";
    $result = $conn->query($sqlpwd);
    $pwdusr = mysqli_real_escape_string($conn, $pwdusr);

    switch (true) {
        // If sql query result contains more than 0 rows
        case $result->num_rows > 0:
            $row = $result->fetch_row();
            $dbPWD = $row[1];
        // If hash of given password matches password hash in db
        case $pwdusr === $dbPWD:
            dbupdate(); // Update data in database
            $conn->close();
            break;
        default: // Else send http 400 response
            http_response_code(400);
            echo 'Invalid password.';
    }
}

// Create function to insert or update data in db
function dbupdate() {

  switch (true) {
      // If "id" key exists change comment after validate_password()
      case array_key_exists("id", $_POST):
          $conn = dbconnect();
          $ip = $_SERVER['REMOTE_ADDR'];
          $id = $_POST['id'];
          $comment = $_POST['comment'];
          $update = "UPDATE Gegevens SET IP = '$ip', Date = NOW(), Comment = '$comment' WHERE PostID = '$id'";
          $comment = mysqli_real_escape_string($conn, $comment);

          // Try to UPDATE record, or show error
          if ( $conn->query($update) === TRUE ) {
            echo "Record updated successfully <br />";
            $conn->close();
          } else {
            echo "Error: " . $update . "<br />" . $conn->error;
          }
          break;

      // If "password" key exists create new record AND save password
      case array_key_exists("password", $_POST):
        $conn = dbconnect();
        $ip = $_SERVER['REMOTE_ADDR'];
        $comment = $_POST['comment'];
        $insert = "INSERT INTO Gegevens (IP, DATE, COMMENT) VALUES ('$ip', NOW(), '$comment') ";
        $comment = mysqli_real_escape_string($conn, $comment);

        // Try to INSERT record, or show error
        if ( $conn->query($insert) === TRUE ) {
          echo "New record created successfully <br />";
          $Postid = $conn->insert_id;
        } else {
          echo "Error: " . $insert . "<br />" . $conn->error;
        }

        // Use md5() function for hashing pwd before saving
        $password = md5($_POST['password']);
        $pwdsql = "INSERT INTO Passwords (PostID, Password) VALUES ('$Postid', '$password')";
        $password = mysqli_real_escape_string($conn, $password);

        // Try to save password, or show error
        if ( $conn->query($pwdsql) === TRUE ) {
          echo "Password saved successfully <br />";
          $conn->close();
        } else {
          echo "Error: " . $pwdsql . "<br />" . $conn->error;
        }
        break;

      // If "id" and "password" vars don't exist, just create new record
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
  }
}
?>
