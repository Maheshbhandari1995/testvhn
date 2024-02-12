
<?php

// Replace these variables with your actual database credentials.
// $hostname = '172.30.10.102';
// $username = 'dbvahan';
// $password = 'EDAS@vahan1';
// $database = 'edas_vahan';

// // Establish the database connection.
// try {
//     $connection = new PDO("mysql:host=$hostname;dbname=$database", $username, $password);
//     $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
// } catch (PDOException $e) {
//     echo "Connection failed: " . $e->getMessage();
//     die();
// }

// function getInput($module, $vendor, $jsonData) {

    
//     if (($module == 'rc' || $module == 'challan') && $vendor == 'authbridge') {
//         if (isset($jsonData['docNumber'])) {
//             $input = $jsonData['docNumber'];
//         } else {
//             $input = $jsonData[0];
//         }
//     } else if (($module == 'rc' || $module == 'challan') && $vendor == 'signzy') {
//         $input = $jsonData['essentials']['vehicleNumber'];
//     } else if ($module == 'license' && $vendor == 'signzy') {
//         $input = $jsonData['essentials']['number'];
//     } else if ($module == 'license' && $vendor == 'authbridge') {
//         $input = $jsonData['essentials']['number'];
//     } else if ($module == 'challan' && $vendor == 'rto') {
//         $input = $jsonData['vehicle_number'];
//     } else if ($module == 'rc_logic' && $vendor == 'edas_internal') {
//         $input = $jsonData['Vehicle_No'];
//     } else if ($module == 'rc_chassis') {
//         $input = $jsonData['chassisNumber'];
//     } else {
//         // Set a default value for $input in case none of the conditions match.
//         $input = '';
//     }

//     return $input; // Add this line to return the fetched value.
// }


// // Fetch data from the table.
// $query = "SELECT id, api_name as Module, vender as vendor, request FROM api_log WHERE input IS NULL OR input = '' ";
// $statement = $connection->prepare($query);
// $statement->execute();
// $items = $statement->fetchAll(PDO::FETCH_OBJ);

// // Update the 'input' column for each row.
// foreach ($items as $item) {
//     $jsonData = json_decode($item->request, true);
//     $input = getInput($item->Module, $item->vendor, $jsonData);

//     // Update the 'input' column in the same row with the fetched $input value.
//     $updateQuery = "UPDATE api_log SET input = :input WHERE id = :id ";
//     $updateStatement = $connection->prepare($updateQuery);
//     $updateStatement->bindParam(':input', $input);
//     $updateStatement->bindParam(':id', $item->id);
//     $updateStatement->execute();
// }

?>
