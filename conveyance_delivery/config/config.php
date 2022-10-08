<?php

$DB_HOST        = "localhost";
$DB_USER        = "root";
//$DB_PASSWORD    = "casting01";
$DB_PASSWORD    = "123123";
$DB_NAME        = "tooling";

//Table Name
$tblWipShiftSummary     = "t_wipshiftsummary";
$tblExportScanData      = "t_ExportedScanData";
$tblPriorityTools       = "t_prioritytools";
$tblToolMainData        = "t_toolmaindata";
$tblShiftSetting        = "t_shiftsetting";
$tblCountDown           = "count_down";

$tblUsers               = "users";
$tblSelection           = "selection";
$tblMelt                = "melt";
$tblMeltInput           = "melt_input";
$tblReports             = "reports";

$tblLoginHistory        = "login_history";

$db = new mysqli($DB_HOST,$DB_USER,$DB_PASSWORD,$DB_NAME);

// Check connection
if (mysqli_connect_errno())
{
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
}

$current    = date('Y-m-d H:i:s');
$today      = date('Y-m-d');
$tomorrow   = date('Y-m-d', strtotime("+1 days"));
$yesterday  = date('Y-m-d', strtotime("-1 days"));
$weekToday  = date('N');

session_start();

function convert_date_string($date)
{
    $string = explode("-", $date);
    return $string[2] . '-' . $string[1] . '-' . $string[0];
}

function get_day_data($graph_date, $shift)
{
    global $tblExportScanData, $tblToolMainData, $db;

    $time_set = get_start_end_time($graph_date, $shift);
    $start = $time_set['start'];
    $end = $time_set['end'];

    $start_time = $start;

    $total_in = 0;
    $total_out = 0;

    $shift0_total_in = 0;
    $shift2_total_in = 0;
    $shift3_total_in = 0;
    $shift4_total_in = 0;
    $shift6_total_in = 0;
    $shift12_total_in = 0;

    $shift0_total_out = 0;
    $shift2_total_out = 0;
    $shift3_total_out = 0;
    $shift4_total_out = 0;
    $shift6_total_out = 0;
    $shift12_total_out = 0;

    $data = array();

    $i = 0;
    while(strtotime($start_time) < strtotime($end) ) {

        $end_time = date("Y-m-d H:i:s", strtotime("+1 hours", strtotime($start_time)));

        $data['in'][$i]['time'] = $end_time;
        $data['in'][$i]['0shift'] = 0;
        $data['in'][$i]['2shift'] = 0;
        $data['in'][$i]['3shift'] = 0;
        $data['in'][$i]['4shift'] = 0;
        $data['in'][$i]['6shift'] = 0;
        $data['in'][$i]['12shift'] = 0;

        $data['out'][$i]['time'] = $end_time;
        $data['out'][$i]['0shift'] = 0;
        $data['out'][$i]['2shift'] = 0;
        $data['out'][$i]['3shift'] = 0;
        $data['out'][$i]['4shift'] = 0;
        $data['out'][$i]['6shift'] = 0;
        $data['out'][$i]['12shift'] = 0;


        //Booked IN
        $query = "SELECT * FROM {$tblExportScanData} WHERE DateTimeStamp <= '{$end_time}' AND DateTimeStamp >= '{$start_time}'";
        $result = $db->query($query);
        while($row = mysqli_fetch_object($result)) {

            $barcode = $row->Barcode;

            $sql = "SELECT * FROM {$tblToolMainData} WHERE machine_number = '{$barcode}' limit 1";
            $res = $db->query($sql);
            $tool = mysqli_fetch_object($res);
            if($tool){
                $total_in ++;
                switch ($tool->priority) {
                    case 0: $data['in'][$i]['0shift'] ++; $shift0_total_in ++; break;
                    case 2: $data['in'][$i]['2shift'] ++; $shift2_total_in ++; break;
                    case 3: $data['in'][$i]['3shift'] ++; $shift3_total_in ++; break;
                    case 4: $data['in'][$i]['4shift'] ++; $shift4_total_in ++; break;
                    case 6: $data['in'][$i]['6shift'] ++; $shift6_total_in ++; break;
                    case 12: $data['in'][$i]['12shift'] ++; $shift12_total_in ++; break;
                }
            }
        }

        //Booked out
        $query = "SELECT * FROM {$tblExportScanData} WHERE updated_left_time <= '{$end_time}' AND updated_left_time >= '{$start_time}' AND Bookin = 0";
        $result = $db->query($query);
        while($row = mysqli_fetch_object($result)) {
            $barcode = $row->Barcode;
            $sql = "SELECT * FROM {$tblToolMainData} WHERE machine_number = '{$barcode}' limit 1";
            $res = $db->query($sql);
            $tool = mysqli_fetch_object($res);
            if($tool){
                $total_out ++;
                switch ($tool->priority) {
                    case 0: $data['out'][$i]['0shift'] ++; $shift0_total_out ++; break;
                    case 2: $data['out'][$i]['2shift'] ++; $shift2_total_out ++; break;
                    case 3: $data['out'][$i]['3shift'] ++; $shift3_total_out ++; break;
                    case 4: $data['out'][$i]['4shift'] ++; $shift4_total_out ++; break;
                    case 6: $data['out'][$i]['6shift'] ++; $shift6_total_out ++; break;
                    case 12: $data['out'][$i]['12shift'] ++; $shift12_total_out ++; break;
                }
            }
        }

        $start_time = $end_time;
        $i++;
    }

    $g_data['total_in'] = $total_in;
    $g_data['total_out'] = $total_out;
    $g_data['shift0_total_in'] = $shift0_total_in;
    $g_data['shift2_total_in'] = $shift2_total_in;
    $g_data['shift3_total_in'] = $shift3_total_in;
    $g_data['shift4_total_in'] = $shift4_total_in;
    $g_data['shift6_total_in'] = $shift6_total_in;
    $g_data['shift12_total_in'] = $shift12_total_in;
    $g_data['shift0_total_out'] = $shift0_total_out;
    $g_data['shift2_total_out'] = $shift2_total_out;
    $g_data['shift3_total_out'] = $shift3_total_out;
    $g_data['shift4_total_out'] = $shift4_total_out;
    $g_data['shift6_total_out'] = $shift6_total_out;
    $g_data['shift12_total_out'] = $shift12_total_out;

    $g_data['graph'] = $data;


    return $g_data;


}

function get_member_data($from_date, $to_date, $users, $shift)
{
    global $tblExportScanData, $tblToolMainData, $db;

    $g_data = array();

    if($from_date == $to_date) {
        if($shift != 'all_shift') {
            $time_set = get_start_end_time($from_date, $shift);
            $start = $time_set['start'];
            $end = $time_set['end'];
        } else {
            $time_set0 = get_start_end_time($from_date, "shift1");
            $time_set1 = get_start_end_time($from_date, "shift3");
            $start = $time_set0['start'];
            $end = $time_set1['end'];
        }

        foreach($users as $user) {
            $start_time = $start;

            $total_in = 0;
            $total_out = 0;

            $shift0_total_in = 0;
            $shift2_total_in = 0;
            $shift3_total_in = 0;
            $shift4_total_in = 0;
            $shift6_total_in = 0;
            $shift12_total_in = 0;

            $shift0_total_out = 0;
            $shift2_total_out = 0;
            $shift3_total_out = 0;
            $shift4_total_out = 0;
            $shift6_total_out = 0;
            $shift12_total_out = 0;

            $data = array();

            $i = 0;
            while(strtotime($start_time) <= strtotime($end) ) {

                $end_time = date("Y-m-d H:i:s", strtotime("+8 hours", strtotime($start_time)));
                $data['in'][$i]['time'] = $end_time;
                $data['out'][$i]['time'] = $end_time;

                $data['in'][$i]['0shift'] = 0;
                $data['in'][$i]['2shift'] = 0;
                $data['in'][$i]['3shift'] = 0;
                $data['in'][$i]['4shift'] = 0;
                $data['in'][$i]['6shift'] = 0;
                $data['in'][$i]['12shift'] = 0;

                $data['out'][$i]['0shift'] = 0;
                $data['out'][$i]['2shift'] = 0;
                $data['out'][$i]['3shift'] = 0;
                $data['out'][$i]['4shift'] = 0;
                $data['out'][$i]['6shift'] = 0;
                $data['out'][$i]['12shift'] = 0;

                //Booked In
                $query = "SELECT * FROM {$tblExportScanData} WHERE DateTimeStamp <= '{$end_time}' AND DateTimeStamp >= '{$start_time}' AND booked_in_user = '{$user}'";
                $result = $db->query($query);
                while($row = mysqli_fetch_object($result)) {

                    $barcode = $row->Barcode;

                    $sql = "SELECT * FROM {$tblToolMainData} WHERE machine_number = '{$barcode}' limit 1";
                    $res = $db->query($sql);
                    $tool = mysqli_fetch_object($res);
                    if($tool){
                        $total_in ++;
                        switch ($tool->priority) {
                            case 0: $data['in'][$i]['0shift'] ++; $shift0_total_in ++; break;
                            case 2: $data['in'][$i]['2shift'] ++; $shift2_total_in ++; break;
                            case 3: $data['in'][$i]['3shift'] ++; $shift3_total_in ++; break;
                            case 4: $data['in'][$i]['4shift'] ++; $shift4_total_in ++; break;
                            case 6: $data['in'][$i]['6shift'] ++; $shift6_total_in ++; break;
                            case 12: $data['in'][$i]['12shift'] ++; $shift12_total_in ++; break;
                        }
                    }
                }

                //Booked out
                $query = "SELECT * FROM {$tblExportScanData} WHERE updated_left_time <= '{$end_time}' AND updated_left_time >= '{$start_time}' AND booked_out_user = '{$user}'";
                $result = $db->query($query);
                while($row = mysqli_fetch_object($result)) {
                    $barcode = $row->Barcode;
                    $sql = "SELECT * FROM {$tblToolMainData} WHERE machine_number = '{$barcode}' limit 1";
                    $res = $db->query($sql);
                    $tool = mysqli_fetch_object($res);
                    if($tool){
                        $total_out ++;
                        switch ($tool->priority) {
                            case 0: $data['out'][$i]['0shift'] ++; $shift0_total_out ++; break;
                            case 2: $data['out'][$i]['2shift'] ++; $shift2_total_out ++; break;
                            case 3: $data['out'][$i]['3shift'] ++; $shift3_total_out ++; break;
                            case 4: $data['out'][$i]['4shift'] ++; $shift4_total_out ++; break;
                            case 6: $data['out'][$i]['6shift'] ++; $shift6_total_out ++; break;
                            case 12: $data['out'][$i]['12shift'] ++; $shift12_total_out ++; break;
                        }
                    }
                }

                $start_time = $end_time;
                $i++;
            }

            $g_data[$user]['total_in'] = $total_in;
            $g_data[$user]['total_out'] = $total_out;
            $g_data[$user]['shift0_total_in'] = $shift0_total_in;
            $g_data[$user]['shift2_total_in'] = $shift2_total_in;
            $g_data[$user]['shift3_total_in'] = $shift3_total_in;
            $g_data[$user]['shift4_total_in'] = $shift4_total_in;
            $g_data[$user]['shift6_total_in'] = $shift6_total_in;
            $g_data[$user]['shift12_total_in'] = $shift12_total_in;
            $g_data[$user]['shift0_total_out'] = $shift0_total_out;
            $g_data[$user]['shift2_total_out'] = $shift2_total_out;
            $g_data[$user]['shift3_total_out'] = $shift3_total_out;
            $g_data[$user]['shift4_total_out'] = $shift4_total_out;
            $g_data[$user]['shift6_total_out'] = $shift6_total_out;
            $g_data[$user]['shift12_total_out'] = $shift12_total_out;
            $g_data[$user]['graph'] = $data;
        }
    } else {
        foreach($users as $user) {

            $total_in = 0;
            $total_out = 0;

            $shift0_total_in = 0;
            $shift2_total_in = 0;
            $shift3_total_in = 0;
            $shift4_total_in = 0;
            $shift6_total_in = 0;
            $shift12_total_in = 0;

            $shift0_total_out = 0;
            $shift2_total_out = 0;
            $shift3_total_out = 0;
            $shift4_total_out = 0;
            $shift6_total_out = 0;
            $shift12_total_out = 0;

            $data = array();

            $i = 0;

            $end_date = $from_date;
            while(strtotime($end_date) <= strtotime($to_date) ) {

                if($shift != 'all_shift') {
                    $time_set = get_start_end_time($end_date, $shift);
                    $start_time = $time_set['start'];
                    $end_time = $time_set['end'];
                } else {
                    $time_set0 = get_start_end_time($end_date, "shift1");
                    $time_set1 = get_start_end_time($end_date, "shift3");
                    $start_time = $time_set0['start'];
                    $end_time = $time_set1['end'];
                }

                $data['in'][$i]['time'] = $end_date;
                $data['out'][$i]['time'] = $end_date;

                $data['in'][$i]['0shift'] = 0;
                $data['in'][$i]['2shift'] = 0;
                $data['in'][$i]['3shift'] = 0;
                $data['in'][$i]['4shift'] = 0;
                $data['in'][$i]['6shift'] = 0;
                $data['in'][$i]['12shift'] = 0;


                $data['out'][$i]['0shift'] = 0;
                $data['out'][$i]['2shift'] = 0;
                $data['out'][$i]['3shift'] = 0;
                $data['out'][$i]['4shift'] = 0;
                $data['out'][$i]['6shift'] = 0;
                $data['out'][$i]['12shift'] = 0;


                //Booked In
                $query = "SELECT * FROM {$tblExportScanData} WHERE DateTimeStamp <= '{$end_time}' AND DateTimeStamp >= '{$start_time}' AND booked_in_user = '{$user}'";
                $result = $db->query($query);
                while($row = mysqli_fetch_object($result)) {

                    $barcode = $row->Barcode;

                    $sql = "SELECT * FROM {$tblToolMainData} WHERE machine_number = '{$barcode}' limit 1";
                    $res = $db->query($sql);
                    $tool = mysqli_fetch_object($res);
                    if($tool){
                        $total_in ++;
                        switch ($tool->priority) {
                            case 0: $data['in'][$i]['0shift'] ++; $shift0_total_in ++; break;
                            case 2: $data['in'][$i]['2shift'] ++; $shift2_total_in ++; break;
                            case 3: $data['in'][$i]['3shift'] ++; $shift3_total_in ++; break;
                            case 4: $data['in'][$i]['4shift'] ++; $shift4_total_in ++; break;
                            case 6: $data['in'][$i]['6shift'] ++; $shift6_total_in ++; break;
                            case 12: $data['in'][$i]['12shift'] ++; $shift12_total_in ++; break;
                        }
                    }
                }

                //Booked out
                $query = "SELECT * FROM {$tblExportScanData} WHERE updated_left_time <= '{$end_time}' AND updated_left_time >= '{$start_time}' AND booked_out_user = '{$user}'";
                $result = $db->query($query);
                while($row = mysqli_fetch_object($result)) {
                    $barcode = $row->Barcode;
                    $sql = "SELECT * FROM {$tblToolMainData} WHERE machine_number = '{$barcode}' limit 1";
                    $res = $db->query($sql);
                    $tool = mysqli_fetch_object($res);
                    if($tool){
                        $total_out ++;
                        switch ($tool->priority) {
                            case 0: $data['out'][$i]['0shift'] ++; $shift0_total_out ++; break;
                            case 2: $data['out'][$i]['2shift'] ++; $shift2_total_out ++; break;
                            case 3: $data['out'][$i]['3shift'] ++; $shift3_total_out ++; break;
                            case 4: $data['out'][$i]['4shift'] ++; $shift4_total_out ++; break;
                            case 6: $data['out'][$i]['6shift'] ++; $shift6_total_out ++; break;
                            case 12: $data['out'][$i]['12shift'] ++; $shift12_total_out ++; break;
                        }
                    }
                }

                $end_date = date('Y-m-d', strtotime("+1 days", strtotime($end_date)));
                $i++;
            }

            $g_data[$user]['total_in'] = $total_in;
            $g_data[$user]['total_out'] = $total_out;
            $g_data[$user]['shift0_total_in'] = $shift0_total_in;
            $g_data[$user]['shift2_total_in'] = $shift2_total_in;
            $g_data[$user]['shift3_total_in'] = $shift3_total_in;
            $g_data[$user]['shift4_total_in'] = $shift4_total_in;
            $g_data[$user]['shift6_total_in'] = $shift6_total_in;
            $g_data[$user]['shift12_total_in'] = $shift12_total_in;
            $g_data[$user]['shift0_total_out'] = $shift0_total_out;
            $g_data[$user]['shift2_total_out'] = $shift2_total_out;
            $g_data[$user]['shift3_total_out'] = $shift3_total_out;
            $g_data[$user]['shift4_total_out'] = $shift4_total_out;
            $g_data[$user]['shift6_total_out'] = $shift6_total_out;
            $g_data[$user]['shift12_total_out'] = $shift12_total_out;

            $g_data[$user]['graph'] = $data;
        }
    }

    return $g_data;
}

function get_tools_data($from_date, $to_date, $tools, $shift)
{
    global $tblExportScanData, $tblToolMainData, $db;

    $g_data = array();

    if($from_date == $to_date) {
        if($shift != 'all_shift') {
            $time_set = get_start_end_time($from_date, $shift);
            $start_time = $time_set['start'];
            $end_time = $time_set['end'];
        } else {
            $time_set0 = get_start_end_time($from_date, "shift1");
            $time_set1 = get_start_end_time($from_date, "shift3");
            $start_time = $time_set0['start'];
            $end_time = $time_set1['end'];
        }

        foreach($tools as $tool) {

            $total_in = 0;
            $total_out = 0;

            $i = 0;
            $data = array();

            //Bookedin
            $query = "SELECT * FROM {$tblExportScanData} WHERE DateTimeStamp <= '{$end_time}' AND DateTimeStamp >= '{$start_time}' AND Barcode = '{$tool}'";
            $result = $db->query($query);
            while($row = mysqli_fetch_object($result)) {
                $total_in ++;
                $data[$i]['in'] = $row->DateTimeStamp;
                if($row->booked_out_user > 0 && $row->updated_left_time <= $end_time && $row->updated_left_time >= $start_time){
                    $total_out ++;
                    $data[$i]['out'] = $row->updated_left_time;
                }
                $i++;
            }

            $query = "SELECT * FROM {$tblExportScanData} WHERE updated_left_time <= '{$end_time}' AND updated_left_time >= '{$start_time}' AND booked_out_user > 0 AND Barcode = '{$tool}'";
            $result = $db->query($query);

            while($row = mysqli_fetch_object($result)) {
                if($row->DateTimeStamp < $start_time) {
                    $total_out ++;
                    $data[$i]['in'] = $row->DateTimeStamp;
                    $data[$i]['out'] = $row->updated_left_time;
                    $i++;
                }

            }

            $q = "SELECT * FROM {$tblToolMainData} WHERE machine_number = '{$tool}' limit 1";
            $r = $db->query($q);
            $t = mysqli_fetch_object($r);

            switch($t->priority) {
                case 0: $g_data[$tool]['color'] = "#ff0500"; break;
                case 2: $g_data[$tool]['color'] = "#df02a4"; break;
                case 3: $g_data[$tool]['color'] = "#0557ff"; break;
                case 4: $g_data[$tool]['color'] = "#ff8f00"; break;
                case 6: $g_data[$tool]['color'] = "#fff200"; break;
                case 12: $g_data[$tool]['color'] = "#00ff08"; break;
            }

            $g_data[$tool]['data'] = $data;
            $g_data[$tool]['total_in'] = $total_in;
            $g_data[$tool]['total_out'] = $total_out;
        }


    } else {
        $end_date = $from_date;
        while($end_date <= $to_date) {

            if($shift != 'all_shift') {
                $time_set = get_start_end_time($end_date, $shift);
                $start_time = $time_set['start'];
                $end_time = $time_set['end'];
            } else {
                $time_set0 = get_start_end_time($end_date, "shift1");
                $time_set1 = get_start_end_time($end_date, "shift3");
                $start_time = $time_set0['start'];
                $end_time = $time_set1['end'];
            }

            foreach($tools as $tool) {

                $total_in = 0;
                $total_out = 0;

                $i = 0;
                $data = array();

                //Bookedin
                $query = "SELECT * FROM {$tblExportScanData} WHERE DateTimeStamp <= '{$end_time}' AND DateTimeStamp >= '{$start_time}' AND Barcode = '{$tool}'";
                $result = $db->query($query);
                while($row = mysqli_fetch_object($result)) {
                    $total_in ++;
                    $data[$i]['in'] = $row->DateTimeStamp;
                    if($row->booked_out_user > 0 && $row->updated_left_time <= $end_time && $row->updated_left_time >= $start_time){
                        $total_out ++;
                        $data[$i]['out'] = $row->updated_left_time;
                    }
                    $i++;
                }

                $query = "SELECT * FROM {$tblExportScanData} WHERE updated_left_time <= '{$end_time}' AND updated_left_time >= '{$start_time}' AND booked_out_user > 0 AND Barcode = '{$tool}'";
                $result = $db->query($query);

                while($row = mysqli_fetch_object($result)) {
                    if($row->DateTimeStamp < $start_time) {
                        $total_out ++;
                        $data[$i]['in'] = $row->DateTimeStamp;
                        $data[$i]['out'] = $row->updated_left_time;
                        $i++;
                    }

                }

                $q = "SELECT * FROM {$tblToolMainData} WHERE machine_number = '{$tool}' limit 1";
                $r = $db->query($q);
                $t = mysqli_fetch_object($r);

                switch($t->priority) {
                    case 0: $g_data[$tool]['color'] = "#ff0500"; break;
                    case 2: $g_data[$tool]['color'] = "#df02a4"; break;
                    case 3: $g_data[$tool]['color'] = "#0557ff"; break;
                    case 4: $g_data[$tool]['color'] = "#ff8f00"; break;
                    case 6: $g_data[$tool]['color'] = "#fff200"; break;
                    case 12: $g_data[$tool]['color'] = "#00ff08"; break;
                }

                if(isset($g_data[$tool]['data']))
                    array_merge($g_data[$tool]['data'],$data);
                else
                    $g_data[$tool]['data'] = $data;

                if(isset($g_data[$tool]['total_in']))
                    $g_data[$tool]['total_in'] += $total_in;
                else
                    $g_data[$tool]['total_in'] = $total_in;

                if(isset($g_data[$tool]['total_out']))
                    $g_data[$tool]['total_out'] += $total_out;
                else
                    $g_data[$tool]['total_out'] = $total_out;
            }

            $end_date = date('Y-m-d', strtotime("+1 days", strtotime($end_date)));
        }
    }

    return $g_data;
}

function get_start_end_time($g_date, $g_shift)
{
    global $tblShiftSetting, $db;
    $shift = str_replace("shift", "", $g_shift);
    $date = $g_date;
    $week = date('N', strtotime($date));

    //GET SHIFT SETTING
    $query = "SELECT * FROM {$tblShiftSetting} WHERE date = '{$week}'";
    $result = $db->query($query);
    $shift_row = mysqli_fetch_object($result);

    if($shift_row) {
        $shift_setting = json_decode($shift_row->timeset, true);
        $start = $date. " ". $shift_setting[$shift]['start'].":00";
        $end = $date. " ". $shift_setting[$shift]['end'].":00";
    } else{
        $start = $date." 00:00:00";
        $end = $date." 23:59:59";
    }

    if(strtotime($start) > strtotime($end)) {
        $end = date('Y-m-d H:i:s', strtotime("+1 days", strtotime($end)));
    }

    $timeset['start'] = $start;
    $timeset['end'] = $end;
    return $timeset;
}

function get_end_shift($date)
{
    global $db, $tblShiftSetting;
    $week = date('N', strtotime($date));

    //Get time setting data
    $time_query = "SELECT * FROM {$tblShiftSetting} WHERE date = {$week}";
    $time_result = $db->query($time_query);
    $time_set = mysqli_fetch_object($time_result);

    $time_setting = json_decode($time_set->timeset, true);

    $last_shift = 1;

    if($time_setting[1]['start'] != "00:00" || $time_setting[1]['end'] != "00:00") {
        $last_shift = 1;
    }

    if($time_setting[2]['start'] != "00:00" || $time_setting[2]['end'] != "00:00") {
        $last_shift = 2;
    }

    if($time_setting[3]['start'] != "00:00" || $time_setting[3]['end'] != "00:00") {
        $last_shift = 3;
    }

    return $last_shift;
}

//================Report Page=======================

function get_report_section1_data($report_start_date, $report_end_date, $shift)
{
    global $tblExportScanData, $tblToolMainData, $db;

    $total_in = 0;
    $total_out = 0;

    $shift0_total_in = 0;
    $shift2_total_in = 0;
    $shift3_total_in = 0;
    $shift4_total_in = 0;
    $shift6_total_in = 0;
    $shift12_total_in = 0;

    $shift0_total_out = 0;
    $shift2_total_out = 0;
    $shift3_total_out = 0;
    $shift4_total_out = 0;
    $shift6_total_out = 0;
    $shift12_total_out = 0;

    if($report_start_date == $report_end_date) {
        if($shift != 'all_shift') {
            $time_set = get_start_end_time($report_start_date, $shift);
            $start_time = $time_set['start'];
            $end_time = $time_set['end'];
        } else {
            $time_set0 = get_start_end_time($report_start_date, "shift1");
            $time_set1 = get_start_end_time($report_start_date, "shift3");
            $start_time = $time_set0['start'];
            $end_time = $time_set1['end'];
        }

        //BOOKED IN
        $query = "SELECT * FROM {$tblExportScanData} WHERE DateTimeStamp <= '{$end_time}' AND DateTimeStamp >= '{$start_time}'";
        $result = $db->query($query);
        while($row = mysqli_fetch_object($result)) {
            $barcode = $row->Barcode;
            $sql = "SELECT * FROM {$tblToolMainData} WHERE machine_number = '{$barcode}' limit 1";
            $res = $db->query($sql);
            $tool = mysqli_fetch_object($res);
            if($tool){
                $total_in ++;
                switch ($tool->priority) {
                    case 0: $shift0_total_in ++; break;
                    case 2: $shift2_total_in ++; break;
                    case 3: $shift3_total_in ++; break;
                    case 4: $shift4_total_in ++; break;
                    case 6: $shift6_total_in ++; break;
                    case 12: $shift12_total_in ++; break;
                }
            }
        }

        //BOOKED OUT
        $query = "SELECT * FROM {$tblExportScanData} WHERE updated_left_time <= '{$end_time}' AND updated_left_time >= '{$start_time}' AND Bookin = 0 AND booked_out_user > 0";
        $result = $db->query($query);
        while($row = mysqli_fetch_object($result)) {
            $barcode = $row->Barcode;
            $sql = "SELECT * FROM {$tblToolMainData} WHERE machine_number = '{$barcode}' limit 1";
            $res = $db->query($sql);
            $tool = mysqli_fetch_object($res);
            if($tool){
                $total_out ++;
                switch ($tool->priority) {
                    case 0: $shift0_total_out ++; break;
                    case 2: $shift2_total_out ++; break;
                    case 3: $shift3_total_out ++; break;
                    case 4: $shift4_total_out ++; break;
                    case 6: $shift6_total_out ++; break;
                    case 12: $shift12_total_out ++; break;
                }
            }
        }
    } else {
        $end_date = $report_start_date;
        while($end_date <= $report_end_date) {

            if($shift != 'all_shift') {
                $time_set = get_start_end_time($end_date, $shift);
                $start_time = $time_set['start'];
                $end_time = $time_set['end'];
            } else {
                $time_set0 = get_start_end_time($end_date, "shift1");
                $time_set1 = get_start_end_time($end_date, "shift3");
                $start_time = $time_set0['start'];
                $end_time = $time_set1['end'];
            }

            $query = "SELECT * FROM {$tblExportScanData} WHERE DateTimeStamp <= '{$end_time}' AND DateTimeStamp >= '{$start_time}'";
            $result = $db->query($query);
            while($row = mysqli_fetch_object($result)) {
                $barcode = $row->Barcode;
                $sql = "SELECT * FROM {$tblToolMainData} WHERE machine_number = '{$barcode}' limit 1";
                $res = $db->query($sql);
                $tool = mysqli_fetch_object($res);
                if($tool){
                    $total_in ++;
                    switch ($tool->priority) {
                        case 0: $shift0_total_in ++; break;
                        case 2: $shift2_total_in ++; break;
                        case 3: $shift3_total_in ++; break;
                        case 4: $shift4_total_in ++; break;
                        case 6: $shift6_total_in ++; break;
                        case 12: $shift12_total_in ++; break;
                    }
                }
            }

            //BOOKED OUT
            $query = "SELECT * FROM {$tblExportScanData} WHERE updated_left_time <= '{$end_time}' AND updated_left_time >= '{$start_time}' AND Bookin = 0 AND booked_out_user > 0";
            $result = $db->query($query);
            while($row = mysqli_fetch_object($result)) {
                $barcode = $row->Barcode;
                $sql = "SELECT * FROM {$tblToolMainData} WHERE machine_number = '{$barcode}' limit 1";
                $res = $db->query($sql);
                $tool = mysqli_fetch_object($res);
                if($tool){
                    $total_out ++;
                    switch ($tool->priority) {
                        case 0: $shift0_total_out ++; break;
                        case 2: $shift2_total_out ++; break;
                        case 3: $shift3_total_out ++; break;
                        case 4: $shift4_total_out ++; break;
                        case 6: $shift6_total_out ++; break;
                        case 12: $shift12_total_out ++; break;
                    }
                }
            }

            $end_date = date('Y-m-d', strtotime("+1 days", strtotime($end_date)));
        }
    }

    //========in===========
    $g_data['in'][0]['key'] = 'total in';
    $g_data['in'][0]['value'] = $total_in;

    $g_data['in'][1]['key'] = 'this shift in';
    $g_data['in'][1]['value'] = $shift0_total_in;

    $g_data['in'][2]['key'] = '2 shift in';
    $g_data['in'][2]['value'] = $shift2_total_in;

    $g_data['in'][3]['key'] = '3 shift in';
    $g_data['in'][3]['value'] = $shift3_total_in;

    $g_data['in'][4]['key'] = '4/6 shift in';
    $g_data['in'][4]['value'] = $shift4_total_in;

    $g_data['in'][5]['key'] = '3/4 days in';
    $g_data['in'][5]['value'] = $shift6_total_in;

    $g_data['in'][6]['key'] = '5 days+ in';
    $g_data['in'][6]['value'] = $shift12_total_in;

    //========out===========
    $g_data['out'][0]['key'] = 'total out';
    $g_data['out'][0]['value'] = $total_out;

    $g_data['out'][1]['key'] = 'this shift out';
    $g_data['out'][1]['value'] = $shift0_total_out;

    $g_data['out'][2]['key'] = '2 shift out';
    $g_data['out'][2]['value'] = $shift2_total_out;

    $g_data['out'][3]['key'] = '3 shift out';
    $g_data['out'][3]['value'] = $shift3_total_out;

    $g_data['out'][4]['key'] = '4/6 shift out';
    $g_data['out'][4]['value'] = $shift4_total_out;

    $g_data['out'][5]['key'] = '3/4 days out';
    $g_data['out'][5]['value'] = $shift6_total_out;

    $g_data['out'][6]['key'] = '5 days+ out';
    $g_data['out'][6]['value'] = $shift12_total_out;

    return $g_data;
}

function get_report_section2_data($report_start_date, $report_end_date, $shift)
{

    global $tblExportScanData, $tblToolMainData, $db;

    $data = array();

    if($report_start_date == $report_end_date) {
        if($shift != 'all_shift') {
            $time_set = get_start_end_time($report_start_date, $shift);
            $start_time = $time_set['start'];
            $end_time = $time_set['end'];
        } else {
            $time_set0 = get_start_end_time($report_start_date, "shift1");
            $time_set1 = get_start_end_time($report_end_date, "shift3");
            $start_time = $time_set0['start'];
            $end_time = $time_set1['end'];
        }

        $i = 0;
        while(strtotime($start_time) < strtotime($end_time) ) {

            $end = date("Y-m-d H:i:s", strtotime("+1 hours", strtotime($start_time)));

            $data['in'][$i]['time'] = date("H:i", strtotime($end));
            $data['in'][$i]['0shift'] = 0;
            $data['in'][$i]['2shift'] = 0;
            $data['in'][$i]['3shift'] = 0;
            $data['in'][$i]['4shift'] = 0;
            $data['in'][$i]['6shift'] = 0;
            $data['in'][$i]['12shift'] = 0;

            $data['out'][$i]['time'] = date("H:i", strtotime($end));;
            $data['out'][$i]['0shift'] = 0;
            $data['out'][$i]['2shift'] = 0;
            $data['out'][$i]['3shift'] = 0;
            $data['out'][$i]['4shift'] = 0;
            $data['out'][$i]['6shift'] = 0;
            $data['out'][$i]['12shift'] = 0;

            //BOOKED IN
            $query = "SELECT * FROM {$tblExportScanData} WHERE DateTimeStamp <= '{$end}' AND DateTimeStamp >= '{$start_time}'";
            $result = $db->query($query);
            while($row = mysqli_fetch_object($result)) {
                $barcode = $row->Barcode;
                $sql = "SELECT * FROM {$tblToolMainData} WHERE machine_number = '{$barcode}' limit 1";
                $res = $db->query($sql);
                $tool = mysqli_fetch_object($res);
                switch ($tool->priority) {
                    case 0: $data['in'][$i]['0shift'] ++; break;
                    case 2: $data['in'][$i]['2shift'] ++; break;
                    case 3: $data['in'][$i]['3shift'] ++; break;
                    case 4: $data['in'][$i]['4shift'] ++; break;
                    case 6: $data['in'][$i]['6shift'] ++; break;
                    case 12: $data['in'][$i]['12shift'] ++; break;
                }
            }

            //BOOKED OUT
            $query = "SELECT * FROM {$tblExportScanData} WHERE updated_left_time <= '{$end}' AND updated_left_time >= '{$start_time}' AND Bookin = 0";
            $result = $db->query($query);
            while($row = mysqli_fetch_object($result)) {
                $barcode = $row->Barcode;
                $sql = "SELECT * FROM {$tblToolMainData} WHERE machine_number = '{$barcode}' limit 1";
                $res = $db->query($sql);
                $tool = mysqli_fetch_object($res);
                if($tool){
                    switch ($tool->priority) {
                        case 0: $data['out'][$i]['0shift'] ++; break;
                        case 2: $data['out'][$i]['2shift'] ++; break;
                        case 3: $data['out'][$i]['3shift'] ++; break;
                        case 4: $data['out'][$i]['4shift'] ++; break;
                        case 6: $data['out'][$i]['6shift'] ++; break;
                        case 12: $data['out'][$i]['12shift'] ++; break;
                    }
                }
            }

            $start_time = $end;
            $i++;
        }

    } else {
        $end_date = $report_start_date;
        $i = 0;
        while(strtotime($end_date) <= strtotime($report_end_date)) {

            if($shift != 'all_shift') {
                $time_set = get_start_end_time($end_date, $shift);
                $start_time = $time_set['start'];
                $end_time = $time_set['end'];
            } else {
                $time_set0 = get_start_end_time($end_date, "shift1");
                $time_set1 = get_start_end_time($end_date, "shift3");
                $start_time = $time_set0['start'];
                $end_time = $time_set1['end'];
            }

            $data['in'][$i]['time'] = date("d/m/y", strtotime($end_date));
            $data['in'][$i]['0shift'] = 0;
            $data['in'][$i]['2shift'] = 0;
            $data['in'][$i]['3shift'] = 0;
            $data['in'][$i]['4shift'] = 0;
            $data['in'][$i]['6shift'] = 0;
            $data['in'][$i]['12shift'] = 0;

            $data['out'][$i]['time'] = date("d/m/y", strtotime($end_date));
            $data['out'][$i]['0shift'] = 0;
            $data['out'][$i]['2shift'] = 0;
            $data['out'][$i]['3shift'] = 0;
            $data['out'][$i]['4shift'] = 0;
            $data['out'][$i]['6shift'] = 0;
            $data['out'][$i]['12shift'] = 0;

            $query = "SELECT * FROM {$tblExportScanData} WHERE DateTimeStamp <= '{$end_time}' AND DateTimeStamp >= '{$start_time}'";
            $result = $db->query($query);
            while($row = mysqli_fetch_object($result)) {
                $barcode = $row->Barcode;
                $sql = "SELECT * FROM {$tblToolMainData} WHERE machine_number = '{$barcode}' limit 1";
                $res = $db->query($sql);
                $tool = mysqli_fetch_object($res);
                if($tool){
                    switch ($tool->priority) {
                        case 0: $data['in'][$i]['0shift'] ++; break;
                        case 2: $data['in'][$i]['2shift'] ++; break;
                        case 3: $data['in'][$i]['3shift'] ++; break;
                        case 4: $data['in'][$i]['4shift'] ++; break;
                        case 6: $data['in'][$i]['6shift'] ++; break;
                        case 12: $data['in'][$i]['12shift'] ++; break;
                    }
                }
            }

            //BOOKED OUT
            $query = "SELECT * FROM {$tblExportScanData} WHERE updated_left_time <= '{$end_time}' AND updated_left_time >= '{$start_time}' AND Bookin = 0";
            $result = $db->query($query);
            while($row = mysqli_fetch_object($result)) {
                $barcode = $row->Barcode;
                $sql = "SELECT * FROM {$tblToolMainData} WHERE machine_number = '{$barcode}' limit 1";
                $res = $db->query($sql);
                $tool = mysqli_fetch_object($res);
                if($tool){
                    switch ($tool->priority) {
                        case 0: $data['out'][$i]['0shift'] ++; break;
                        case 2: $data['out'][$i]['2shift'] ++; break;
                        case 3: $data['out'][$i]['3shift'] ++; break;
                        case 4: $data['out'][$i]['4shift'] ++; break;
                        case 6: $data['out'][$i]['6shift'] ++; break;
                        case 12: $data['out'][$i]['12shift'] ++; break;
                    }
                }
            }

            $end_date = date('Y-m-d', strtotime("+1 days", strtotime($end_date)));
            $i++;
        }
    }
    return $data;
}

function get_report_section6_data($from_date, $to_date, $users, $shift)
{

    global $tblExportScanData;
    global $tblUsers, $tblLoginHistory, $db;

    $data = array();

    if($from_date == $to_date) {
        if($shift != 'all_shift') {
            $time_set = get_start_end_time($from_date, $shift);
            $start_time = $time_set['start'];
            $end_time = $time_set['end'];
        } else {
            $time_set0 = get_start_end_time($from_date, "shift1");
            $time_set1 = get_start_end_time($from_date, "shift3");
            $start_time = $time_set0['start'];
            $end_time = $time_set1['end'];
        }
        $i = 0;
        foreach($users as $member) {
            //Get User name
            $query = "SELECT * FROM {$tblUsers} WHERE id = {$member}";
            $result = $db->query($query);
            $user = mysqli_fetch_object($result);

            //Get Booked in and out log
            $query = "SELECT * FROM {$tblExportScanData} WHERE booked_in_user = {$member} AND DateTimeStamp >= '{$start_time}' AND DateTimeStamp <= '{$end_time}'";
            $result = $db->query($query);
            $booked_in = mysqli_num_rows($result);

            //booked out
            $query = "SELECT * FROM {$tblExportScanData} WHERE booked_out_user = {$member} AND updated_left_time >= '{$start_time}' AND updated_left_time <= '{$end_time}'";
            $result = $db->query($query);
            $booked_out = mysqli_num_rows($result);

            $data[$i]['user'] = $user->username;
            $data[$i]['booked_in'] = $booked_in;
            $data[$i]['booked_out'] = $booked_out;

            $data[$i]['login'] = array();
            $data[$i]['logout'] = array();

            //Get login history
            $query = "SELECT * FROM {$tblLoginHistory} WHERE created_at >= '{$start_time}' AND created_at <= '{$end_time}' AND user_id = {$member}";
            $result = $db->query($query);
            while($row = mysqli_fetch_object($result)){
                if($row->login_out == "login") {
                    array_push($data[$i]['login'], $row->created_at);
                } else {
                    array_push($data[$i]['logout'], $row->created_at);
                }
            }
            $i++;
        }
    } else {
        $i = 0;
        foreach($users as $member) {
            //Get User name
            $query = "SELECT * FROM {$tblUsers} WHERE id = {$member}";
            $result = $db->query($query);
            $user = mysqli_fetch_object($result);

            $booked_in = 0;
            $booked_out = 0;

            $data[$i]['login'] = array();
            $data[$i]['logout'] = array();

            $end_date = $from_date;

            while(strtotime($end_date) <= strtotime($to_date) ) {
                if($shift != 'all_shift') {
                    $time_set = get_start_end_time($end_date, $shift);
                    $start_time = $time_set['start'];
                    $end_time = $time_set['end'];
                } else {
                    $time_set0 = get_start_end_time($end_date, "shift1");
                    $time_set1 = get_start_end_time($end_date, "shift3");
                    $start_time = $time_set0['start'];
                    $end_time = $time_set1['end'];
                }

                //Get Booked in and out log
                $query = "SELECT * FROM {$tblExportScanData} WHERE booked_in_user = {$member} AND DateTimeStamp >= '{$start_time}' AND DateTimeStamp <= '{$end_time}'";
                $result = $db->query($query);
                $booked_in += mysqli_num_rows($result);

                //booked out
                $query = "SELECT * FROM {$tblExportScanData} WHERE booked_out_user = {$member} AND updated_left_time >= '{$start_time}' AND updated_left_time <= '{$end_time}'";
                $result = $db->query($query);
                $booked_out += mysqli_num_rows($result);



                //Get login history
                $query = "SELECT * FROM {$tblLoginHistory} WHERE created_at >= '{$start_time}' AND created_at <= '{$end_time}' AND user_id = {$member}";
                $result = $db->query($query);
                while($row = mysqli_fetch_object($result)){
                    if($row->login_out == "login") {
                        array_push($data[$i]['login'], $row->created_at);
                    } else {
                        array_push($data[$i]['logout'], $row->created_at);
                    }
                }

                $end_date = date('Y-m-d', strtotime("+1 days", strtotime($end_date)));
            }


            $data[$i]['user'] = $user->username;
            $data[$i]['booked_in'] = $booked_in;
            $data[$i]['booked_out'] = $booked_out;

            $i++;
        }
    }


    return $data;
}

function getWorkingDays($startDate, $endDate)
{
    $begin = strtotime($startDate);
    $end   = strtotime($endDate);
    if ($begin > $end) {
        return 0;
    } else {
        $no_days  = 0;
        $weekends = 0;
        while ($begin <= $end) {
            $no_days++; // no of days in the given interval
            $what_day = date("N", $begin);
            if ($what_day > 5) { // 6 and 7 are weekend days
                $weekends++;
            };
            $begin += 86400; // +1 day
        };
        $working_days = $no_days - $weekends;

        return $working_days;
    }
}

function get_shift_date_number($datetime)
{
    global $tblShiftSetting, $db;
    $s_date = date('Y-m-d', strtotime($datetime));
    $next_date   = date('Y-m-d', strtotime("+1 days", strtotime($s_date)));
    $before_date   = date('Y-m-d', strtotime("-1 days", strtotime($s_date)));
    $week = date('N', strtotime($s_date));

    //GET SHIFT SETTING
    $query = "SELECT * FROM {$tblShiftSetting} WHERE date = '{$week}'";
    $result = $db->query($query);
    $time_set = mysqli_fetch_object($result);

    $time_setting = json_decode($time_set->timeset, true);

    $data = array();
    if ($datetime > $s_date . " " . $time_setting[1]['start'] . ":00" && $datetime < $s_date . " " . $time_setting[1]['end'] . ":59") {
        $data['date'] = $s_date;
        $data['shift'] = 1;
    } else if ($datetime > $s_date . " " . $time_setting[2]['start'] . ":00" && $datetime < $s_date . " " . $time_setting[2]['end'] . ":59") {
        $data['date'] = $s_date;
        $data['shift'] = 2;
    } else if ($datetime > $before_date . " " . $time_setting[3]['start'] . ":00" && $datetime < $s_date . " " . $time_setting[3]['end'] . ":59") {
        $data['date'] = $before_date;
        $data['shift'] = 3;
    } else if ($datetime > $s_date . " " . $time_setting[3]['start'] . ":00" && $datetime < $next_date . " " . $time_setting[3]['end'] . ":59"){
        if($time_setting[3]['start'] == "00:00" &&  $time_setting[3]['end'] == "00:00") {
            $data['date'] = $s_date;
            $data['shift'] = 0;
        } else {
            $data['date'] = $next_date;
            $data['shift'] = 3;
        }
    } else {
        $data['date'] = $s_date;
        $data['shift'] = "no_set";
    }

    return $data;
}

$query = "SELECT * FROM {$tblCountDown} Limit 1";
$result = $db->query($query);
$row = mysqli_fetch_object($result);
$count_down = $row->count_down;