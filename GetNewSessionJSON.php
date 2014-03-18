<html>
<body>

<?php

/** Gets the URL, changes it to JSON, then displays the values 
 *
 * SessionID
 * sessionToken
 * Status
**/

class XmlToJson {

	public function Parse ($url) {

		$fileContents= file_get_contents($url);

		$fileContents = str_replace(array("\n", "\r", "\t"), '', $fileContents);

		$fileContents = trim(str_replace('"', "'", $fileContents));

		$simpleXml = simplexml_load_string($fileContents);

		$json = json_encode($simpleXml);

		return $json;

	}

}

error_reporting(0); ?>

<script> var arr =


<?php 

//get from your store
$url = "http://stores.inksoft.com/GetNewSession/foo";

print XmlToJson::Parse($url);
?>


</script>

<script>
document.write("<p>Session ID: " + arr.SessionID+"</p>");

document.write("<p>sessionToken: " + arr.SessionToken+"</p>");

document.write("<p>Status: " + arr.Status+"</p>");

</script>




</body>
</html>
