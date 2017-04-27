<?php
    require('../db/volunteerCheck.php');
    require('../errorReporter.php');
    require('../db/volunteerConnection.php');

    if(!isset($_GET['tableName'])) {
        //goback to the selection
        header("Location: uploadDashboard.php"); //volunteers uploadDashboard
    }else {
        $tableName = $_GET['tableName']; //get the table name
    }

    //  check for file erros in the file uploaded
    if ($_FILES["file"]["error"] > 0)
    {
        echo "Error: " . $_FILES["file"]["error"] . "<br>";
    }
    //  else get all other attributes of the file
    else
    {
        echo "Upload: " . $_FILES["file"]["name"] . "<br>";
        echo "Type: " . $_FILES["file"]["type"] . "<br>";
        echo "Size: " . ($_FILES["file"]["size"] / 1024) . " kB<br>";
        echo "Stored in: " . $_FILES["file"]["tmp_name"];
    }

    //  link to return to the specific table
    echo "<br /><a href='uploadDashboard.php'>Return</a><br />";

    //  get path of file
    $filePath = $_FILES["file"]["tmp_name"];
    $fileName = $_FILES['file']['name'];
    $fileErrors = $_FILES['file']['error'];

    //  match the regular expression for file name
    if (preg_match('/\.csv$/i', $_FILES["file"]["name"]))
    {
        
        //Opens the specified csv file and reads from it line by line.       
        if (($handle = fopen($filePath, "r")) !== FALSE) 
        {
            //$data is an array that contains the data of the current row being read from the csv file.
            //This while loop will continue looping until there is no more data being read from the file.
            while (($data = fgetcsv($handle, ",")) !== FALSE) 
            {

                //Place the current data array into a variable, fields are seperated by commas.
                $values = implode("', '" , $data);

                //$placeholders = implode(', ', $placeholders);

                //Insert the values into the specified table.
                $sql = "INSERT INTO $tableName VALUES ($values);";

                //Echo the sql statment for debugging purposes.
                echo $sql . "<br>";

                //execute the statement
                $stmt = sqlsrv_query($conn, $sql);
            }

            //Close the csv file.
            fclose($handle);
        }
        else
        {
            echo "An error occured while reading from the file. Ensure you've specified the correct file.";
        }
                
        //  if execute could not be executed give errors and kill the script
        if( $stmt === false)
        {
            $_SESSION['error'] = 'There was an error uploading bulk into ' . $tableName . '. Please check to see if any of the rows were uploaded before you try again because the file could have been partially uploaded.';
            header("Location:uploadDashboard.php");
        }
        else{
            $_SESSION['message'] = 'You have uploaded bulk successfully into ' . $tableName;
            header("Location:uploadDashboard.php");
        }
        
    }

    //  if file name does not match the regex, show this message
    else
    {
        $_SESSION['error'] = 'The file must be .csv for bulk upload into ' . $tableName;
        header("Location:uploadDashboard.php");
    }
?>