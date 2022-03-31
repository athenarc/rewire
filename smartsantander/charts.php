<?php

$urn_id = $_GET["urn_id"];
if(isset($_GET["start"])){
    $start = $_GET["start"];
} else {
    $start = 0;
}
if(isset($_GET["limit"])){
    $limit = $_GET["limit"];
} else {
    $limit = 10000;
}




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

$sql = "SELECT id, TIME_FORMAT(`timestamp`,'%H:%i') as mnts ,UNIX_TIMESTAMP(`timestamp`) as ut, `uom`, `phenomenon`, `value` FROM `measurements` WHERE `urn_id` = ? AND `phenomenon` != 'batteryLevel' LIMIT $start, $limit";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $urn_id);
$stmt->execute();
printf("%s<br>", $stmt->error);

$result = $stmt->get_result();
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $unixtime_as_index = $row["ut"];
        $minutes[$unixtime_as_index] = $row["mnts"];
        $data[$row["phenomenon"]."(".$row["uom"].")"][$unixtime_as_index] = $row["value"];
    }
}
$conn->close();

$colors_array = ['green','pink','red','black','silver','brown','blue','yellow','orange','magenta'];

$csv_filename = "csv_files/".$urn_id.".csv";
$f = fopen($csv_filename, 'w');
if ($f === false) {
    die('Error opening the file ' . $csv_filename);
}
?>
<html>
<header>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</header>
<body>
<p><a href="<?php echo $csv_filename;?>">Download data in csv</a></p>
<form action="charts.php" method="get">
    Start: <input type="text" value="<?php echo $start;?>" name="start">
    <br>
    Limit:  <input type="text" value="<?php echo $limit;?>" name="limit">
    <br>
    <input type="hidden" name="urn_id" value="<?php echo $urn_id?>">
    <input type="submit">
</form>
<div>
    <canvas id="myChart"></canvas>
</div>
<script>

    const labels = [<?php
        foreach ($minutes as $label) {
            echo "'$label',";
        } ?>];

    const data = {
        labels: labels,
        datasets: [
            <?php
            $count=0;
            $csv_headers_array[0] = "time";

            foreach ($data as $data_label => $data_values) {
                $csv_headers_array[] = $data_label;
                $csv_data_array[] = $data_values;
                ?>
            {
                label: '<?php echo $data_label;?>',
                backgroundColor: '<?php echo $colors_array[$count];?>',
                borderColor: '<?php echo $colors_array[$count];?>',
                data: [<?php echo implode(",",$data_values);?>],
            },
                <?php
                $count++;
            }
            ?>

        ]
    };

    const config = {
        type: 'line',
        data: data,
        options: {
            legend: {
                onClick: function(e, legendItem) {
                    var index = legendItem.datasetIndex;
                    var ci = this.chart;
                    var alreadyHidden = (ci.getDatasetMeta(index).hidden === null) ? false : ci.getDatasetMeta(index).hidden;

                    ci.data.datasets.forEach(function(e, i) {
                        var meta = ci.getDatasetMeta(i);

                        if (i !== index) {
                            if (!alreadyHidden) {
                                meta.hidden = meta.hidden === null ? !meta.hidden : null;
                            } else if (meta.hidden === null) {
                                meta.hidden = true;
                            }
                        } else if (i === index) {
                            meta.hidden = null;
                        }
                    });

                    ci.update();
                },
            },
        }
    };
    // === include 'setup' then 'config' above ===

    const myChart = new Chart(
        document.getElementById('myChart'),
        config
    );
</script>
<?php
//array_unshift($csv_data_array,array_keys($minutes));
$timestamps = array_keys($minutes);
$csv_data_array = flip($csv_data_array);
//print_r($csv_data_array);
//$csv_data[] = $csv_headers_array;
//array_keys($minutes);
//$csv_data_array[0] = $minutes;
fputcsv($f, $csv_headers_array);
$csv_count=0;
foreach ($csv_data_array as $row) {
    array_unshift($row,$timestamps[$csv_count]);
    fputcsv($f, $row);
    $csv_count++;
}
fclose($f);

function flip($arr)
{
    $out = array();
    foreach ($arr as $key => $subarr)
    {
        foreach ($subarr as $subkey => $subvalue)
        {
            $out[$subkey][$key] = $subvalue;
        }
    }
    return $out;
}
?>
</body>
</html>