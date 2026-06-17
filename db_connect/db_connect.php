<?php
	//$db=mysqli_connect("localhost","zharfanc_webdev","4KM(dL9SJM}&","zharfanc_utv");
	$db=mysqli_connect("127.0.0.1","root","","utv");

	if (!$db)
	{
		die("Connection error: " . mysqli_connect_error());
	}	  
?>