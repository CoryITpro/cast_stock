<?php

$DB_HOST        = "localhost";
$DB_USER        = "root";
$DB_PASSWORD    = "casting01";
$DB_NAME        = "tooling";

//Table Name
$tblWipShiftSummary     = "t_wipshiftsummary";
$tblExportScanData      = "t_exportedscanData";
$tblPriorityTools       = "t_prioritytools";
$tblToolMainData        = "t_toolmaindata";
$tblShiftSetting        = "t_shiftsetting";

$tblUsers               = "users";
$tblSelection           = "selection";
$tblMelt                = "melt";
$tblMeltInput           = "melt_input";


$db = new mysqli($DB_HOST,$DB_USER,$DB_PASSWORD,$DB_NAME);

// Check connection
if (mysqli_connect_errno())
{
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
}

$current = date('Y-m-d H:i:s');


function convert_date_string($date)
{
    $string = explode("-", $date);
    return $string[2] . '-' . $string[1] . '-' . $string[0];
}