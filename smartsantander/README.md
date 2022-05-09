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


CONTENTS
