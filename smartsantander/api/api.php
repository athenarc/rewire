<?php
header("Content-Type: application/json; charset=UTF-8");
require_once '../ARIMA/src/arimaModel.php';
$servername = "";
$username = "";
$password = "";
$dbname = "";
// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
//error_log($servername.$username.$password.$dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$nodeid = $_GET['nodeid'];
$phenomenon = $_GET['phenomenon'];

if($phenomenon == ""){
    //$sql = ("SELECT UNIX_TIMESTAMP(timestamp) as timestamp,`uom`,`phenomenon`,`value` FROM  measurements
    //             WHERE urn_id = ?
    //             ORDER BY `timestamp` DESC LIMIT 1");
    $sql = ("SELECT phenomenon FROM `measurements` WHERE `urn_id` = ? AND timestamp in (SELECT max(`timestamp`) FROM `measurements` WHERE `urn_id` = ?);");
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $nodeid,$nodeid);
    $stmt->execute();
    //printf("%s", $stmt->error);
    $phenomenon = array();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $row = $result->fetch_assoc();
            $phenomenon[] = $row["phenomenon"];
        }
    }
    echo json_encode($phenomenon);
} else {
    $sql = ("SELECT UNIX_TIMESTAMP(timestamp) as timestamp,`uom`,`phenomenon`,`value` FROM  measurements 
                 WHERE urn_id = ? AND phenomenon = ?
                 ORDER BY `timestamp` DESC LIMIT 1");
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $nodeid,$phenomenon);
    $stmt->execute();
    //printf("%s", $stmt->error);

    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $value = $row["value"];
        $uom = $row["uom"];
    }
    //echo json_encode($value);

    $sql2 = "SELECT DISTINCT UNIX_TIMESTAMP(`timestamp`) as ut FROM `measurements` WHERE `urn_id` = ? ORDER BY ut ASC ";
    $stmt2 = $conn->prepare($sql2);
    $stmt2->bind_param("i", $nodeid);
    $stmt2->execute();
    $result2 = $stmt2->get_result();
    $unixtime_array = array();
    if ($result2->num_rows > 0) {
        while($row = $result2->fetch_assoc()) {
            $unixtime_array[] = $row["ut"];
        }
    }
    $diffs_array = create_interval_diffs($unixtime_array);
    $arima_prediction_array = arimaModel::auto_arima($diffs_array);

    $return_data = array("uom"=>$uom,"value"=>$value,"predicted_next_interval"=>$arima_prediction_array[0],"diffs"=>$diffs_array);
    echo json_encode($return_data);
}

function create_interval_diffs($unixtime_array) {
    $diffs = array();
    for($i=1;$i<count($unixtime_array);$i++){
        $diff = $unixtime_array[$i] - $unixtime_array[$i-1];
        if($diff > 0 && $diff < 3600){
            $diffs[] =  $diff;
        }
    }
    return $diffs;
}
?>
