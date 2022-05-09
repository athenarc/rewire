The REWIRE API is a PHP-based API giving access to the measurements of SmartSantander for the requested node as well as producing predictions on the arrival times 
of the follow-up measurements.

The structure of REWIRE API is as follows: 
/smartsantander/api/{nodeid}/{phenomenon} 
where {nodeid} is the id of the SmartSantander node and {phenomenon} is the phenomenon we are interested in.

Example of REWIRE API URL: /smartsantander/api/22/relativeHumidity

API RESPONSE:
<div class="highlight highlight-source-json position-relative overflow-auto">
<pre>
{
  "uom":"percent",
  "value":"59",
  "predicted_next_interval":80
}
</pre>
</div>


FOLDER CONTENTS:

1. <strong>ARIMA</strong>: The PHP implementation of ARIMA model based on https://github.com/Yusser95/ARIMA-PHP scripts
<strong>

2. NDN Producer script</strong>: The NDN Producer source code wich uses the REWIRE API to retrieve mesurements from SmartSantander and also the FreshnessPeriod from the "predicted_next_interval" response of the API.

3. <strong>api</strong>: The REWIRE API source code.

4. <strong>database_structure.sql</strong>: The MySQL database structure where stored the SmartSantander's API push notitifications.
