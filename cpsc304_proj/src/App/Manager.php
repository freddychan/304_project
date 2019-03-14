<select id="classSelect" onchange="location = this.value;">
	<option value="Manager.php">Manager</option>
	<option value="Employee.php">Employee</option>
	<option value="Customer.php">Customer</option>
</select>
<p><font size ="2"> Find employee:</font></p>
<form method="POST" action="Manager.php">
<!--refresh page when submit-->
	ID:<input type="number" name="eId" size="4"> Table:<input type="text" name ="table" size="18">
	<!--define one variable to pass the value-->
	<input type="submit" value="submit" name="employee">
	
</form></p>
<p><font size="2"> Grab menu:</font></p>
<form method="POST" action="Manager.php">
	menuID:<input type="number" name="menuID" size="4"> itemID:<input type="number" name="itemID" size="4">
	Table:<input type="text" name ="table" size="18">
	<input type="submit" value="submit" name="menu">
</form>

<p><font size="2"> Grab inventory:</font></p>
<form method="POST" action="Manager.php">
	itemID:<input type="number" name="itemID" size="4"> Table:<input type="text" name ="table" size="18">
	<input type="submit" value="submit" name="inventory">
</form>

<p><font size="2"> Grab orders:</font></p>
<form method="POST" action="Manager.php">
	orderID:<input type="number" name="orderID" size="4"> menuID:<input type="number" name="menuID" size="4">
	Table:<input type="text" name ="table" size="18">
	<input type="submit" value="submit" name="orders">
</form>

<p><font size="2"> Grab account:</font></p>
<form method="POST" action="Manager.php">
	userID:<input type="text" name="userID" size="20"> diningID:<input type="number" name="diningID" size="4">
	Table:<input type="text" name ="table" size="18">
	<input type="submit" value="submit" name="account">
</form>

<p><font size="2"> Grab customer:</font></p>
<form method="POST" action="Manager.php">
	orderID:<input type="text" name="orderID" size="4"> Table:<input type="text" name ="table" size="18">
	<input type="submit" value="submit" name="customer">
</form>

<p><font size="2"> Find menu items below average price:</font></p>
<form method="POST" action="Manager.php">
<input type="submit" value="Perform nested query" name="testquery">
</form>

<p><font size="2"> Join menu and inventory:</font></p>
<form method="POST" action="Manager.php">
	menuID:<input type="text" name = "menuID" size="4">
	<input type="submit" value="submit" name="jMenuInv">
</form>

<p><font size="2"> Change wage:</font></p>
<form method="POST" action="Manager.php">
	eID:<input type="number" name="ewId"  size="4"> Wage:<input type="number" name="wage" size="4">
	<input type="submit" value="submit" name="updateWage">
</form>
	

<p><font size="2"> Fire Lazy Deliverer</font></p>
<form method="POST" action="Manager.php">
	<input type="submit" value="delete" name="delDel">
</form>

<?php

$error = False;
$db = "(DESCRIPTION=(ADDRESS_LIST = (ADDRESS = (PROTOCOL = TCP)(HOST = dbhost.ugrad.cs.ubc.ca)(PORT = 1522)))(CONNECT_DATA=(SID=ug)))";
$db_conn = OCILogon("ora_v5f0b", "a38894135", "$db");

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

function testNestedQuery() {
		$result = executePlainSQL("select itemName,AVG(price)
									from menu
									where price < (select AVG(price) from menu)
									group by itemName");	
		printResult($result);
}

if ($db_conn) {

	if (array_key_exists('employee', $_POST)) {
		$result = executePlainSQL("select * from " . $_POST['table'] . " where id =" . $_POST['eId'] ."");
		printResult($result);
	}
	if (array_key_exists('menu', $_POST)) {
		$result = executePlainSQL("select * from " . $_POST['table'] . " where menuID =" . $_POST['menuID'] .
																		"AND itemID =" . $_POST['itemID'] . "");
		printResult($result);
	}
	if (array_key_exists('inventory', $_POST)) {
		$result = executePlainSQL("select * from " . $_POST['table'] . " where itemID =" . $_POST['itemID'] . "");
		printResult($result);
	}
	if (array_key_exists('orders', $_POST)) {
		$result = executePlainSQL("select * from " . $_POST['table'] . " where orderID =" . $_POST['orderID'] . 
																		"AND menuID =" . $_POST['menuID'] . "");
		printResult($result);
	}
	if (array_key_exists('account', $_POST)) {
		$result = executePlainSQL("select * from " . $_POST['table'] . " where diningID =" . $_POST['diningID'] . 
																		"AND userID =" . $_POST['userID'] . "");
		printResult($result);
	}
	if (array_key_exists('customer', $_POST)) {
		$result = executePlainSQL("select * from " . $_POST['table'] . " where orderID =" . $_POST['orderID'] ."");
		printResult($result);
	}
	
	if (array_key_exists('testquery', $_POST)) {
		testNestedQuery();
	}

	if(array_key_exists('jMenuInv', $_POST)) {
		$result = executePlainSQL("select * from menu, inventory where menu.menuid =" . $_POST['menuID'] . 
																" AND menu.itemid = inventory.itemid");
		printResult($result);
	}
	if(array_key_exists('updateWage', $_POST)) {
		
		//if (10 < $wage < 20) {
			$result = executePlainSQL("update employee set wage =" . $_POST['wage'] . " where id=" . $_POST['ewId'] ."");
			OCICommit($db_conn);
			$employee = executePlainSQL("select * from employee");
			printResult($employee);
		//}
	}
	if(array_key_exists('delDel', $_POST)) {
		$before = executePlainSQL("select * from employee");
		printResult($before);
		$result = executePlainSQL("delete from deliverer where id=2");
		$after = executePlainSQL("select * from employee");
		printResult($after);
	}
	
echo "<br>Started Connection<br>";
	if ($_POST && $error) {
		//POST-REDIRECT-GET -- See http://en.wikipedia.org/wiki/Post/Redirect/Get
		header("location: Manager.php");
	}/* else {
	// Select data...
		$result = executePlainSQL("");
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



