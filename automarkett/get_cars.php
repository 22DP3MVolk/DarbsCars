<?php
     header('Content-Type: application/json');
     $conn = new mysqli("localhost", "root", "", "automarkett");
     if ($conn->connect_error) {
         echo json_encode(["error" => "Connection failed: " . $conn->connect_error]);
         exit;
     }

     $sql = "SELECT * FROM cars";
     $result = $conn->query($sql);

     $cars = [];
     while ($row = $result->fetch_assoc()) {
         $cars[] = $row;
     }

     echo json_encode($cars);
     $conn->close();
     ?>