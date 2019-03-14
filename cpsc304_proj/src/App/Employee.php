<select id="classSelect" onchange="location = this.value;">
	<option value="Employee.php">Employee</option>
	<option value="Manager.php">Manager</option>
	<option value="Customer.php">Customer</option>
</select>	
<p><font size ="2"> Grab employee:</font></p>
<form method="POST" action="Employee.php">
	ID:<input type="number" name="eId" size="4"> Table:<input type="text" name ="table" size="18">
	<input type="submit" value="submit" name="employee">
</form></p>

<p><font size="2"> Grab order:</font></p>
<form method="POST" action="Employee.php">
	orderID:<input type="number" name="orderID" size="4"> Table:<input type="text" name ="table" size="18">
	<input type="submit" value="submit" name="order">
</form>

<?php

$error = False;
$db = "(DESCRIPTION=(ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = dbhost.ugrad.cs.ubc.ca)(PORT = 1522)))(CONNECT_DATA=(SID=ug)))";
$db_conn = OCILogon("ora_v5f0b", "a38894135", "$db");

function findEmployee($cmdstr) {
    global $db_conn, $error;
    $statement = OCIParse($db_conn, "select * from $cmdstr where id = $cmdstr");

    if (!$statement) {
    		echo "<br>Cannot parse the following command: " . $cmdstr . "<br>";
    		$e = OCI_Error($db_conn); // For OCIParse errors pass the
    		// connection handle
    		echo htmlentities($e['message']);
    		$error = True;
    	}

    	$r = OCIExecute($statement, OCI_DEFAULT);
    	if (!$r) {
    		echo "<br>Cannot execute the following command: " . $cmdstr . "<br>";
    		$e = oci_error($statement); // For OCIExecute errors pass the statementhandle
    		echo htmlentities($e['message']);
    		$error = True;
    	} else {

    	}
    	return $statement;
}

function executePlainSQL($cmdstr) { //takes a plain (no bound variables) SQL command and executes it
	//echo "<br>running ".$cmdstr."<br>";
	global $db_conn, $error;
	$statement = OCIParse($db_conn, $cmdstr); //There is a set of comments at the end of the file that describe some of the OCI specific functions and how they work

	if (!$statement) {
		echo "<br>Cannot parse the following command: " . $cmdstr . "<br>";
		$e = OCI_Error($db_conn); // For OCIParse errors pass the       
		// connection handle
		echo htmlentities($e['message']);
		$error = True;
	}

	$r = OCIExecute($statement, OCI_DEFAULT);
	if (!$r) {
		echo "<br>Cannot execute the following command: " . $cmdstr . "<br>";
		$e = oci_error($statement); // For OCIExecute errors pass the statementhandle
		echo htmlentities($e['message']);
		$error = True;
	} else {

	}
	return $statement;

}

function executeBoundSQL($cmdstr, $list) {
	/* Sometimes a same statement will be excuted for severl times, only
	 the value of variables need to be changed.
	 In this case you don't need to create the statement several times; 
	 using bind variables can make the statement be shared and just 
	 parsed once. This is also very useful in protecting against SQL injection. See example code below for       how this functions is used */

	global $db_conn, $error;
	$statement = OCIParse($db_conn, $cmdstr);

	if (!$statement) {
		echo "<br>Cannot parse the following command: " . $cmdstr . "<br>";
		$e = OCI_Error($db_conn);
		echo htmlentities($e['message']);
		$error = True;
	}

	foreach ($list as $tuple) {
		foreach ($tuple as $bind => $val) {
			//echo $val;
			//echo "<br>".$bind."<br>";
			OCIBindByName($statement, $bind, $val);
			unset ($val); //make sure you do not remove this. Otherwise $val will remain in an array object wrapper which will not be recognized by Oracle as a proper datatype

		}
		$r = OCIExecute($statement, OCI_DEFAULT);
		if (!$r) {
			echo "<br>Cannot execute the following command: " . $cmdstr . "<br>";
			$e = OCI_Error($statement); // For OCIExecute errors pass the statementhandle
			echo htmlentities($e['message']);
			echo "<br>";
			$error = True;
		}
	}

}

function printResult($result) { //prints results from a select statement
	//echo "<br>Got data from table employee:<br>";
	echo "<table>";
//	echo "<tr><th>ID</th><th>Name</th></tr>";

	while ($row = OCI_Fetch_Array($result, OCI_BOTH)) {
		echo "<tr><td>" . $row[0] . "</td><td>" 
		. $row[1] . "</td><td>" . $row[2] . "</td><td>" 
		. $row[3] . "</td><td>" . $row[4] . "</td><td>"
		. $row[5] . "</td><td>" . $row[6] . "</td><td>" 		
		. $row[7] . "</td></tr>"; //or just use "echo $row[0]"
	}
	echo "</table>";

}

if ($db_conn) {

	if (array_key_exists('employee', $_POST)) {
		$result = executePlainSQL("select * from " . $_POST['table'] . " where id =" . $_POST['eId'] ."");
		printResult($result);
	}
	if (array_key_exists('order', $_POST)) {
		$result = executePlainSQL("select * from " . $_POST['table'] . " where id =" . $_POST['orderID'] ."");
		printResult($result);
	}
	
echo "<br>Started Connection<br>";
	if ($_POST && $error) {
		//POST-REDIRECT-GET -- See http://en.wikipedia.org/wiki/Post/Redirect/Get
		header("location: Employee.php");
	} /*else {
	// Select data...
		$result = executePlainSQL($cmdstr);
		printResult($result);
	}*/

	//Commit to save changes...
	OCILogoff($db_conn);
} else {
	echo "cannot connect";
	$e = OCI_Error(); // For OCILogon errors pass no handle
	echo htmlentities($e['message']);
}


?>
