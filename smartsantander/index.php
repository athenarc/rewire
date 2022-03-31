<?php
ob_start("ob_gzhandler");
?>
<!DOCTYPE html>
<html>
<head>
    <style>
        table {
            font-family: arial, sans-serif;
            border-collapse: collapse;
            width: 100%;
        }

        td, th {
            border: 1px solid #dddddd;
            text-align: left;
            padding: 8px;
        }

        tr:nth-child(even) {
            background-color: #dddddd;
        }
    </style>
</head>
<body>
<?php

require_once 'ARIMA/src/arimaModel.php';
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

if(isset($_POST["measurements"])) {
	
	$measurements_array = $_POST["measurements"];
	$urn = $_POST["urn"];
	$time = $_POST["timestamp"];
	$location_array = $_POST["location"];
	$coordinates = $location_array["coordinates"][0].",".$location_array["coordinates"][1];
	$location_type = $location_array["type"];
    $urn_id=0;
	$sql0 = "SELECT id FROM `urn` WHERE `urn_data` = '$urn'";
    $result = $conn->query($sql0);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $urn_id = $row["id"];
    } else {
        $sql1 = "INSERT INTO `urn` (urn_data,location_type,coordinates ) VALUES ('$urn','$location_type','$coordinates')";
        if ($conn->query($sql1) === TRUE) {
            $urn_id = $conn->insert_id;
        }
    }
    foreach ($measurements_array as $measurement) {
        $uom = $measurement["uom"];
        $phenomenon = $measurement["phenomenon"];
        $value = $measurement["value"];
        if ($urn_id > 0) {
            $sql2 = "INSERT INTO `measurements` (urn_id,timestamp,coordinates,location_type,uom,phenomenon,value) VALUES('$urn_id','$time','$coordinates','$location_type','$uom','$phenomenon','$value')";
            if (!$conn->query($sql2) === TRUE) {
                error_log("Error:".$conn->error, TRUE);
            }
        } else {
            error_log("Error with urn id", TRUE);
        }
    }



} else {
    $sql0 = "SELECT * FROM urn";
    $stmt = $conn->prepare($sql0);
    $stmt->execute();
    $result0 = $stmt->get_result();
    $count=1;
    echo '<table><tr><th>A/A</th><th>ID</th><th>urn</th><th>most freq. delay</th><th>min delay</th><th>max delay</th><th>avg delay</th><th>ARIMA prediction</th></tr>';
    if ($result0->num_rows > 0) {
        while($row0 = $result0->fetch_assoc()) {
            $urn_type = "";
            $urn_id = $row0["id"];
            $urn_data = $row0["urn_data"];
            /*
            $sql1 = "SELECT id FROM `measurements` WHERE `urn_id` = '$urn_id' AND phenomenon = 'mileage:total' LIMIT 1";
            $result1 = $conn->query($sql1);
            if ($result1->num_rows > 0) {
                $urn_type = 'mobile sensor';
            }
*/
            $sql2 = "SELECT DISTINCT UNIX_TIMESTAMP(`timestamp`) as ut FROM `measurements` WHERE `urn_id` = ? ORDER BY ut ASC ";
            $stmt2 = $conn->prepare($sql2);
            $stmt2->bind_param("i", $urn_id);
            $stmt2->execute();
            //printf("%s<br>", $stmt2->error);

            $result2 = $stmt2->get_result();
            $unixtime_array = array();
            if ($result2->num_rows > 0) {
                while($row = $result2->fetch_assoc()) {
                    $unixtime_array[] = $row["ut"];
                }
            }
            $diffs_array = create_interval_diffs($unixtime_array);
            $arima_prediction_array = array();
            if(count($diffs_array) > 50){
                $arima_prediction_array = arimaModel::auto_arima($diffs_array);
            }

            //print_r($diffs_array);
            //exit;
            $count_values = array_count_values($diffs_array);
            arsort($count_values);

            echo "<tr><td>$count.</td> ";
            echo "<td>[<a href='charts.php?urn_id=$urn_id' target='_blank'>$urn_id</a>]</td>";
            echo "<td>$urn_data</td>";
            echo "<td>".round(key($count_values)/60,2)." min</td>";
            echo "<td>".round(min($diffs_array)/60,2)." min</td>";
            echo "<td>".round(max($diffs_array)/60,2)." min</td>";
            //echo "<td>-</td>";
            echo "<td>".round( (array_sum($diffs_array) / count($diffs_array))/60,2 )." min</td>";
            echo "<td>".$arima_prediction_array[0]."sec (".round(($arima_prediction_array[0])/60,2)." min)</td>";
            //echo "Type: $urn_type";
            echo "</tr>";
/*
            $sql = "SELECT id, TIME_FORMAT(`timestamp`,'%H:%i') as mnts ,UNIX_TIMESTAMP(`timestamp`) as ut, `uom`, `phenomenon`, `value` FROM `measurements` WHERE `urn_id` = ? ";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $urn_id);
            $stmt->execute();

            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $unixtime_as_index = $row["ut"];
                    $minutes[$unixtime_as_index] = $row["mnts"];
                    $data[$row["phenomenon"]."(".$row["uom"].")"][$unixtime_as_index] = $row["value"];
                }
            }
*/
            $count++;
//if($count==50) exit;
        }
    }
    echo "</table>";

    $conn->close();
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
</body>
</html>
<?php
ob_end_flush();
