<?php
    error_reporting(E_ALL);
    date_default_timezone_set("Asia/ShangHai");

    /* db config */
    $db_config = array(
        121 => array(
            'host' => '120.26.16.57',
            'user' => 'root',
            'password' => 'd7f900952f6c4235cec0244e03a8e191',
            'database' => 'oa3',
            'table' => 'oa_checktime'
        ),
        128 => array(
        	'host' => '192.168.32.128',
            'user' => 'root',
            'password' => '123456',
            'database' => 'oa3',
            'table' => 'oa_checktime'
        ),
        123 => array(
            'host' => '192.168.32.121',
            'user' => 'root',
            'password' => '123456',
            'database' => 'testoa3',
            'table' => 'oa_checktime'
        )
    );

    /* validate param */
    if ($argc < 3) {
        printf("Error param");
        exit();
    }

    $db_index = (int)$argv[1];
    $badgenumber = $argv[2];
    $checktime = $argv[3];

    if (!$db_index || !$badgenumber || !$checktime) {
        printf("Error param");
        exit();
    }
    if (isset($db_config[$db_index])) {
        $db = $db_config[$db_index];
    } else {
        printf("Error param");
        exit();
    }

    $checktime = strtotime($checktime);
	$daystr = date("Y-m-d", $checktime);
    $day = strtotime($daystr);
    $tmp_time_stamp = strtotime($daystr.' 6:00:00');
    //如果在0点至早6点打卡  视为前一天的下班打卡时间
    if($checktime >= $day && $checktime < $tmp_time_stamp) {
        $day = strtotime('-1 day',$day);
    }

	$mysqli = new mysqli($db['host'], $db['user'], $db['password'], $db['database']);
    $file_name = 'missing_data_'.$db_index.'.csv';
    /* check connection */
    if ($mysqli->connect_errno) {
    	/* save record in csv */
    	writeCsv($file_name,$badgenumber, $checktime, $day);
    	/* error log */
        printf("Connect failed: %s\n", $mysqli->connect_error);
        exit();
    }

    $mysqli->query("set names utf8");

    /* insert data from csv */
    $csv_data = readCsv($file_name);
    if(!empty($csv_data)){
    	foreach ($csv_data as $key => &$value) {
    		$sql = sprintf("INSERT INTO %s (badgenumber, checktime, day) VALUES (%d, %d, %d)", $db['table'], $value[0], $value[1], $value[2]);
    		if ($mysqli->query($sql)) {
    			unset($csv_data[$key]);
		        printf("New record created successfully");
		    } else {
		        echo "Error: " . $sql . "<br>" . $mysqli->error;
		    }
    	}
    	/* overwrite csv data */
    	overWriteCsv($file_name,$csv_data);
    }
    

    $sql = sprintf("INSERT INTO %s (badgenumber, checktime, day) VALUES (%d, %d, %d)", $db['table'], $badgenumber, $checktime, $day);
    /* Select queries return a resultset */
    if ($mysqli->query($sql)) {
        printf("New record created successfully");
    } else {
        echo "Error: " . $sql . "<br>" . $mysqli->error;
    }
    /* free result set */
    $mysqli->close();

    //write file
    function writeCsv($file_name,$badgenumber, $checktime, $day){
    	$file = fopen($file_name, 'a') or die("Unable to open file!");
		$content = $badgenumber.','.$checktime.','.$day.PHP_EOL;
		fwrite($file, $content);
		fclose($file);
    }

    //overwrite file
    function overWriteCsv($file_name,$data){
    	$content = '';
    	foreach ($data as $key => $value) {
    		$content += $value[0].','.$value[1].','.$value[2].PHP_EOL;
    	}
    	$file = fopen($file_name, 'w') or die("Unable to open file!");
		fwrite($file, $content);
		fclose($file);
    }

    //read file
    function readCsv($file_name){
        if(!file_exists($file_name)) {
            return false;
        }
    	$data = array();
	    $file = fopen($file_name, 'r');
	    while(!feof($file)){
	    	$row = fgetcsv($file);
	    	if($row){
	    		$data[] = $row;
	    	}
	    }
		fclose($file); 
		return $data;
    }
    

