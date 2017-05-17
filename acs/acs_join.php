<?php 
	/*Limits are:
		1. The first two rows need to be name and descriptor of column.
		2. The codenames need to match a table storing the codenames to the actual name. The codenames in the table need to match to match the codenames in the database.
		3. The zip code column in the uploaded file needs to be named "zip", "zip code", "zip-code", or "zipcode" in the first row. See line 73.
	*/


	// MySQL Connection stuff:
	$servername = "localhost";
	$username = "root";
	$password = "";
	$dbname = "acs";
	//The uploaded spreadsheet
	$spreadsheet = $_FILES["leFile"];
	// Create connection
	$conn = mysqli_connect($servername, $username, $password, $dbname);

	//Test for error in connection
	if ($conn->connect_error) {
		die("Connection failed: " . $conn->connect_error);
	}

	//Generate a unique hash for the spreadsheet file uploaded.
	$spreadName = md5_file($_FILES["leFile"]["tmp_name"]).'_'.time();

	//Upload csv
	$target_dir = "csvUploads/";
	$target_file = $target_dir . $spreadName . basename($_FILES["leFile"]["name"]);
	$uploadOk = 1;
	$spreadFileType = pathinfo($target_file,PATHINFO_EXTENSION);
	
	// Check if file already exists
	if (file_exists($target_file)) {
	    echo "Sorry, file already exists.";
	    $uploadOk = 0;
	}
	// Allow certain file formats
	if($spreadFileType != "csv") {
		  echo "Sorry, only CSV files are allowed.";
		  $uploadOk = 0;
	}
	// Check if $uploadOk is set to 0 by an error
	if ($uploadOk == 0) {
		  echo "Sorry, your file was not uploaded.";
	// if everything is ok, try to upload file
	} else {
		if (move_uploaded_file($_FILES["leFile"]["tmp_name"], $target_file)) {
			   echo "The file ". basename( $_FILES["leFile"]["name"]). " has been uploaded.";
		} else {
			  echo "Sorry, there was an error uploading your file.";
		}
	}

	//Keeps track of rows, starting with row 1.
	$row = 1;
	//Reads the file at the target loaction to ensure it is there.
	if (($handle = fopen($target_file, "r")) !== FALSE) {
		//Creates the 2D array to hold the CSV data.
		$data2DArray = array();
		//Gets the CSV array setting the limit to infinite, and the delimiter (the thing that divides the array entries) to a comma.
	    while (($data = fgetcsv($handle, 0, ",")) !== FALSE) {
	        $data2DArray[] = $data;//Once everything in first row of CSV is added to 2D array, implement next row of 2D array to hold the next row of CSV.
	  
	    }
	    fclose($handle);
	}
	//Prints out what is entered in the $data array field.
	            echo implode(",", array_column($data2DArray, 0)) . '<br>';
	        	echo implode(",", array_column($data2DArray, 1)) . '<br>';

	
	//Check if they title the column "zip" or "zipcode"
	$zipKey;
	if (in_array('zip', $data2DArray[0], TRUE)) {
		//Find key of zipcode 
		$zipKey = array_search('zip', $data2DArray[0]);
	} elseif (in_array('zipcode', $data2DArray[0], TRUE)) {
		//Find key of zipcode 
		$zipKey = array_search('zipcode', $data2DArray[0]);
	} elseif (in_array('zip-code', $data2DArray[0], TRUE)) {
		//Find key of zipcode 
		$zipKey = array_search('zip-code', $data2DArray[0]);
	}
	//Gets the variables selected from the dropdown
	$selectedVariables = $_POST["acs_variables"];
	
	
	//select the row with corresponding codenames and what it's a codename for.
	$codeSQL = mysqli_query($conn, "SELECT * FROM code"); 
	//create 2D array for codenames.
	$codearray = array();
		while($codeRow =mysqli_fetch_assoc($codeSQL))
		{
		    $codearray[] = $codeRow;
		}
	for ($code = 0; $code < count($codearray); $code++) { 
		for ($i = 0; $i < count($selectedVariables); $i++) {
			if ($selectedVariables[$i] == $codearray[$code]['codename']) {
				//Find the key in the array for for race, nativity, poverty status, etc. because that signifies the index to look 
				//at for each entry in the array for the number corresponding to area.
				//Checks to see if there is a column in the uploaded csv for race, nativity, poverty status, etc.
				
					for ($s=0; $s < count($data2DArray); $s++) { 
						//Entry for first row, title of column.
						if ($s == 0) {
							//Add value to column for respective variable. 
							array_push($data2DArray[$s], $codearray[$code]['title']);
						} elseif ($s == 1) { //Entry for second row, descriptor of column
							//Add value to column for respective variable. 
							array_push($data2DArray[$s], $codearray[$code]['title'] . " description");
						} elseif ($s >= 2) { //Entry for all other rows, value for zipcode
							//Add value to column for respective variable. 
							array_push($data2DArray[$s], 'No Data');
						}
						
					}
					
					//Get index of new column
					$theKey = array_search($codearray[$code]['title'], $data2DArray[0]);

					//iterate through the rows in the data2Darray 2D array.
					for ($r=2; $r < count($data2DArray); $r++) { //Start at 2 to skip rows with column names and column descriptions.
						//select the row with corresponding zip codes to the uploaded CSV from the big ass database
						$zipSQL1 = mysqli_query($conn, 'SELECT * FROM acs1 WHERE zip = ' . $data2DArray[$r][$zipKey]);
						//Create a 2D array of returned results from the query.
						$emparray1 = array();
						while($leRow1 =mysqli_fetch_assoc($zipSQL1))
						{
						    $emparray1[] = $leRow1;
						}

						$zipSQL2 = mysqli_query($conn, 'SELECT * FROM acs2 WHERE zip = ' . $data2DArray[$r][$zipKey]);
						//Create a 2D array of returned results from the query.
						$emparray2 = array();
						while($leRow2 =mysqli_fetch_assoc($zipSQL2))
						{
						    $emparray2[] = $leRow2;
						}

						$zipSQL3 = mysqli_query($conn, 'SELECT * FROM acs3 WHERE zip = ' . $data2DArray[$r][$zipKey]);
						//Create a 2D array of returned results from the query.
						$emparray3 = array();
						while($leRow3 =mysqli_fetch_assoc($zipSQL3))
						{
						    $emparray3[] = $leRow3;
						}

						$zipSQL4 = mysqli_query($conn, 'SELECT * FROM acs4 WHERE zip = ' . $data2DArray[$r][$zipKey]);
						//Create a 2D array of returned results from the query.
						$emparray4 = array();
						while($leRow4 =mysqli_fetch_assoc($zipSQL4))
						{
						    $emparray4[] = $leRow4;
						}

						$zipSQL5 = mysqli_query($conn, 'SELECT * FROM acs5 WHERE zip = ' . $data2DArray[$r][$zipKey]);
						//Create a 2D array of returned results from the query.
						$emparray5 = array();
						while($leRow5 =mysqli_fetch_assoc($zipSQL5))
						{
						    $emparray5[] = $leRow5;
						}

						$zipSQL6 = mysqli_query($conn, 'SELECT * FROM acs6 WHERE zip = ' . $data2DArray[$r][$zipKey]);
						//Create a 2D array of returned results from the query.
						$emparray6 = array();
						while($leRow6 =mysqli_fetch_assoc($zipSQL6))
						{
						    $emparray6[] = $leRow6;
						}

						$zipSQL7 = mysqli_query($conn, 'SELECT * FROM acs7 WHERE zip = ' . $data2DArray[$r][$zipKey]);
						//Create a 2D array of returned results from the query.
						$emparray7 = array();
						while($leRow7 =mysqli_fetch_assoc($zipSQL7))
						{
						    $emparray7[] = $leRow7;
						}

						$zipSQL8 = mysqli_query($conn, 'SELECT * FROM acs8 WHERE zip = ' . $data2DArray[$r][$zipKey]);
						//Create a 2D array of returned results from the query.
						$emparray8 = array();
						while($leRow8 =mysqli_fetch_assoc($zipSQL8))
						{
						    $emparray8[] = $leRow8;
						}

						$zipSQL9 = mysqli_query($conn, 'SELECT * FROM acs9 WHERE zip = ' . $data2DArray[$r][$zipKey]);
						//Create a 2D array of returned results from the query.
						$emparray9 = array();
						while($leRow9 =mysqli_fetch_assoc($zipSQL9))
						{
						    $emparray9[] = $leRow9;
						}

						$zipSQL10 = mysqli_query($conn, 'SELECT * FROM acs10 WHERE zip = ' . $data2DArray[$r][$zipKey]);
						//Create a 2D array of returned results from the query.
						$emparray10 = array();
						while($leRow10 =mysqli_fetch_assoc($zipSQL10))
						{
						    $emparray10[] = $leRow10;
						}

						//Make sure one of the selected variables is Hispanic, Poverty, etc. before entering in 2D array.
						if (in_array($codearray[$code]['codename'], $selectedVariables, TRUE)) { 
							//Check to see which table the code is in.
							if(array_key_exists($codearray[$code]['codename'], $emparray1[0])) {
								$multipleRows = 0;
								for ($zips = 0; $zips < count($emparray1); $zips++) {
									$multipleRows += $emparray1[$zips][$codearray[$code]['codename']]; 
								}
								$data2DArray[$r][$theKey] = $multipleRows;
							} elseif (array_key_exists($codearray[$code]['codename'], $emparray2[0])) {
								$multipleRows = 0;
								for ($zips = 0; $zips < count($emparray2); $zips++) {
									$multipleRows += $emparray2[$zips][$codearray[$code]['codename']]; 
								}
								$data2DArray[$r][$theKey] = $multipleRows;
							} elseif (array_key_exists($codearray[$code]['codename'], $emparray3[0])) {
								$multipleRows = 0;
								for ($zips = 0; $zips < count($emparray3); $zips++) {
									$multipleRows += $emparray3[$zips][$codearray[$code]['codename']];
								}
								$data2DArray[$r][$theKey] = $multipleRows;
							} elseif (array_key_exists($codearray[$code]['codename'], $emparray4[0])) {
								$multipleRows = 0;
								for ($zips = 0; $zips < count($emparray4); $zips++) {
									$multipleRows += $emparray4[$zips][$codearray[$code]['codename']]; 
								$data2DArray[$r][$theKey] = $multipleRows;
							} elseif (array_key_exists($codearray[$code]['codename'], $emparray5[0])) {
								$multipleRows = 0;
								for ($zips = 0; $zips < count($emparray5); $zips++) {
									$multipleRows += $emparray5[$zips][$codearray[$code]['codename']]; 
								}
								$data2DArray[$r][$theKey] = $multipleRows;
							} elseif (array_key_exists($codearray[$code]['codename'], $emparray6[0])) {
								$multipleRows = 0;
								for ($zips = 0; $zips < count($emparray6); $zips++) {
									$multipleRows += $emparray6[$zips][$codearray[$code]['codename']]; 
								}
								$data2DArray[$r][$theKey] = $multipleRows;
							} elseif (array_key_exists($codearray[$code]['codename'], $emparray7[0])) {
								$multipleRows = 0;
								for ($zips = 0; $zips < count($emparray7); $zips++) {
									$multipleRows += $emparray7[$zips][$codearray[$code]['codename']]; 
								}
								$data2DArray[$r][$theKey] = $multipleRows;
							} elseif (array_key_exists($codearray[$code]['codename'], $emparray8[0])) {
								$multipleRows = 0;
								for ($zips = 0; $zips < count($emparray8); $zips++) {
									$multipleRows += $emparray8[$zips][$codearray[$code]['codename']]; 
								}
								$data2DArray[$r][$theKey] = $multipleRows;
							} elseif (array_key_exists($codearray[$code]['codename'], $emparray9[0])) {
								$multipleRows = 0;
								for ($zips = 0; $zips < count($emparray9); $zips++) {
									$multipleRows += $emparray9[$zips][$codearray[$code]['codename']]; 
								}
								$data2DArray[$r][$theKey] = $multipleRows;
							} elseif (array_key_exists($codearray[$code]['codename'], $emparray10[0])) {
								$multipleRows = 0;
								for ($zips = 0; $zips < count($emparray10); $zips++) {
									$multipleRows += $emparray10[$zips][$codearray[$code]['codename']]; 
								}
								$data2DArray[$r][$theKey] = $multipleRows;
							}
						}
						
					}
			}
		}
	}
	
	

	//Rewrite file with updated information
	$fp = fopen($target_file, 'w');
	foreach ($data2DArray as $fields) {
	    fputcsv($fp, $fields);
	}

	fclose($fp);

	//Download file automatically
	header('Content-Type: text/csv');
	header("Content-Transfer-Encoding: Binary"); 
	header("Content-disposition: attachment; filename=\"" . basename($target_file) . "\""); 
	ob_clean(); 
	flush();
	readfile($target_file); 

	?>
