@@ -0,0 +1,64 @@
<?php require_once ('Connections/herff.php'); ?>
<?php
if (!isset($_SESSION)) {
  session_start();
}
require 'functions.php';
mysql_select_db($database_herff, $herff);

$ds          = DIRECTORY_SEPARATOR; 
$storeFolder = 'docs';   
 //upload file
if (!empty($_FILES)) {
     
    $tempFile = $_FILES['file']['tmp_name'];                   
      
    $targetPath = dirname( __FILE__ ) . $ds. $storeFolder . $ds;  
     
    $targetFile =  $targetPath. $_FILES['file']['name'];  
 
    move_uploaded_file($tempFile,$targetFile); 
     

$tFile = "docs/".$_FILES['file']['name'];
//populate temp table
$query ="LOAD DATA LOCAL INFILE '".$tFile."' INTO TABLE temp_shipments FIELDS TERMINATED BY ','
ENCLOSED BY ';'
LINES TERMINATED BY '\n'
IGNORE 1 LINES
('col1' 'col2', ...)";
if(mysql_query($query)){
$content = "Query success!!!";
}else{
	$content = "error 1: ".date('m/d/Y h:i a')." :".mysql_error();
}
$file = "log.txt";
$contents  = $content."<br>".$content1;
file_put_contents($file, $contents);


//duplicate handling

$query1 = mysql_query("SELECT * FROM temp_shipments");
while($row1 = mysql_fetch_assoc($query1)){
    $tracking = $row1['tracking_no'];
    
    //check if similar tracking no exist
    $query2 = mysql_fetch_array("SELECT tracking_no FROM shipments WHERE tracking_no = '$tracking'");
    if(mysql_num_rows($query2) > 0){
        while ($row2 = mysql_fetch_array($query2)) {
            $original_tracking = $row2['tracking_no'];
            //append identifier
            $tracking2 = $row2['tracking_no']."A";
            //update existing tracking no
            mysql_query("UPDATE shipments SET tracking_no = '$tracking2' WHERE tracking_no = '$original_tracking'");
        }
    }
    //move new records from temp table to shipments
    mysql_query("INSERT shipments SELECT * FROM temp_shipments");
    //empty temp table
    mysql_query("TRUNCATE TABLE temp_shipments");
}
}

?>
