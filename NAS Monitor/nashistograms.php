
<!DOCTYPE html>
<!/**
 * Created by PhpStorm.
 * Author: Theodoros Giannakopoulos (theodoros.giannakopoulos@cern.ch)
 * Date: 06/12/2016
 */>
<html lang="en">
<head>
  <title>NAS Monitor</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
  <link href="http://cdn.oesmith.co.uk/morris-0.4.3.min.css" rel="stylesheet" />
  <script src="http://cdn.oesmith.co.uk/morris-0.5.0.min.js"></script>
  <script src="http://cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js"></script>
</head>
<body>
        <div class="container">
        <div id="area-chart"></div>
                <div class="header clearfix">
                        <div>
                                <nav>
                                        <ul class="nav nav-pills pull-right">
                                                <li role="presentation"><a href="/nasmonitor">Home</a></li>
                                                <li role="presentation"><a href="/nasmonitor/userquotas.php">User Quotas</a></li>
                                                <li role="presentation" class="active"><a href="#">Histograms</a></li
>
                                        </ul>
                                </nav>
                                <h3 class="text-muted">NAS01 Monitor</h3>
                        </div>
                </div>
                <div>
                        <?php
                                $quotas = file_get_contents("neut-quotas.txt");
                                $used_space = file_get_contents("used-space.txt");

                                $parts_uspace = explode("\n",$used_space);
                                $array_uspace = array();

                                foreach($parts_uspace as $uspace){
                                                $parts = preg_split('/\s+/', $uspace);
                                                $folder_name = explode("/users/",$uspace);
                                                //echo $parts[0]." ".$folder_name[1]."<br>";
                                                if(count($parts) != 1){
                                                        $array_uspace[$folder_name[1]] = $parts[0];
                                                }
                                }

                                $parts_uquotas = explode("\n",$quotas);
                                $allocated_space = array();
                                $quota_nodes = array();
                                foreach($parts_uquotas as $uquotas){
                                                $parts = preg_split('/\s+/', $uquotas);
                                                if(array_key_exists($parts[0], $array_uspace)){
                                                        #echo "<b>User:</b> ". $parts[0]." <br><b>Quota: </b>".$parts[1]." <b>Used space:</b> ".$array_uspace[$parts[0]]."<br>";
                                                        if(strpos($array_uspace[$parts[0]],'K') !== FALSE){
                                                                #echo "Kilobtyes ".$array_uspace[$parts[0]] ."<br>";
                                                                $get_space = explode("K",$array_uspace[$parts[0]]);
                                                                $space = floatval($get_space[0]);
                                                                $max_quota = explode("GB",$parts[1]);
                                                                $mquota = intval($max_quota[0]);
                                                                $percentage = 0;
                                                                //echo "Kilobtyes ".$array_uspace[$parts[0]] ." 0.00000".$space." ".$parts[1]." ".$percentage."<br>";
                                                        }else if(strpos($array_uspace[$parts[0]],'M') !== FALSE){
                                                                $get_space = explode("M",$array_uspace[$parts[0]]);
                                                                $space = floatval($get_space[0]);
                                                                $max_quota = explode("GB",$parts[1]);
                                                                $mquota = intval($max_quota[0])*1000;
                                                                $percentage = ($space/$mquota)*100;
                                                                //echo "MB ".$array_uspace[$parts[0]] ." ".($space/1000)." ".$parts[1]." ".$percentage."<br>";

                                                        }else{
                                                                $get_space = explode("G",$array_uspace[$parts[0]]);
                                                                $space = floatval($get_space[0]);
                                                                $max_quota = explode("GB",$parts[1]);
                                                                $mquota = intval($max_quota[0]);
                                                                $percentage = ($space/$mquota)*100;
                                                                //echo "GB ".$array_uspace[$parts[0]] ." ".$space." ".$percentage."<br>";
                                                        }
                                                        if(array_key_exists(number_format($percentage,2),$allocated_space)){
                                                                $allocated_space[number_format($percentage,2)]++;
                                                        }else{
                                                                $allocated_space[number_format($percentage,2)] = 1;
                                                        }
                                                        if(array_key_exists(intval($max_quota[0]),$quota_nodes)){
                                                                $quota_nodes[intval($max_quota[0])]++;
                                                        }else{
                                                                $quota_nodes[intval($max_quota[0])] = 1;
                                                        }
                                                        #echo '<div class="progress"><div class="progress-bar" role="progressbar" aria-valuenow="70" aria-valuemin="0" aria-valuemax="100" style="width:'.$percentage.'%">'.number_format($percentage,2).'% used'.'</div></div>';
                                                }
                                }
                                $allocated = "[";
                                krsort($allocated_space);
                                foreach(array_reverse($allocated_space) as $key => $value){
                                        //echo $key." ".$value."<br>";
                                        $allocated .= '{"label": "'.$key."%".'", "usage": "'.$value.'"},';
                                }
                                $allocated .= ']';
                                /**
                                 * 0 = 0~9%
                                 * 1 = 10~19%
                                 * 2 = 20~29%
                                 * 3 = 30~39%
                                 * 4 = 40~49%
                                 * 5 = 50~59%
                                 * 6 = 60~69%
                                 * 7 = 70~79%
                                 * 8 = 80~89%
                                 * 9 = 90~100%
                                 */
                                $perTenBlocks = array();
                                foreach(array_reverse($allocated_space) as $key => $value){
                                        if($key < 9){
                                                if(array_key_exists('0~9',$perTenBlocks)){
                                                        $perTenBlocks['0~9'] += $value;
                                                }else{
                                                        $perTenBlocks['0~9'] = $value;
                                                }
                                        }else if($key < 19){
                                                if(array_key_exists('10~19',$perTenBlocks)){
                                                        $perTenBlocks['10~19'] += $value;
                                                }else{
                                                        $perTenBlocks['10~19'] = $value;
                                                }
                                        }else if($key < 29){
                                                if(array_key_exists('20~29',$perTenBlocks)){
                                                        $perTenBlocks['20~29'] += $value;
                                                }else{
                                                        $perTenBlocks['20~29'] = $value;
                                                }
                                        }else if($key < 39){
                                                if(array_key_exists('30~39',$perTenBlocks)){
                                                        $perTenBlocks['30~39'] += $value;
                                                }else{
                                                        $perTenBlocks['30~39'] = $value;
                                                }
                                        }else if($key < 49){
                                                if(array_key_exists('40~49',$perTenBlocks)){
                                                        $perTenBlocks['40~49'] += $value;
                                                }else{
                                                        $perTenBlocks['40~49'] = $value;
                                                }
                                        }else if($key < 59){
                                                if(array_key_exists('50~59',$perTenBlocks)){
                                                        $perTenBlocks['50~59'] += $value;
                                                }else{
                                                        $perTenBlocks['50~59'] = $value;
                                                }
                                        }else if($key < 69){
                                                if(array_key_exists('60~69',$perTenBlocks)){
                                                        $perTenBlocks['60~69'] += $value;
                                                }else{
                                                        $perTenBlocks['60~69'] = $value;
                                                }
                                        }else if($key < 79){
                                                if(array_key_exists('70~79',$perTenBlocks)){
                                                        $perTenBlocks['70~79'] += $value;
                                                }else{
                                                        $perTenBlocks['70~79'] = $value;
                                                }
                                        }else if($key < 89){
                                                if(array_key_exists('80~89',$perTenBlocks)){
                                                        $perTenBlocks['80~89'] += $value;
                                                }else{
                                                        $perTenBlocks['80~89'] = $value;
                                                }
                                        }else{
                                                if(array_key_exists('90~100',$perTenBlocks)){
                                                        $perTenBlocks['90~100'] += $value;
                                                }else{
                                                        $perTenBlocks['90~100'] = $value;
                                                }
                                        }
                                }
                                #var_dump($perTenBlocks);

                                $pBlock = "[";
                                foreach($perTenBlocks as $key => $value){
                                        //echo $key." ".$value."<br>";
                                        $pBlock .= '{"label": "'.$key."%".'", "usage": "'.$value.'"},';
                                }
                                $pBlock .= ']';

                                $sorted_array = array();
                                foreach($quota_nodes as $key => $value){
                                        array_push($sorted_array,intval($key));
                                }
                                sort($sorted_array);
                                $qNodes = "[";
                                for($i=0; $i<count($sorted_array);$i++){

                                        $qNodes .= '{"label": "'.$sorted_array[$i]."GB".'", "usage": "'.$quota_nodes[$sorted_array[$i]].'"},';
                                }
                                $qNodes .= ']';

                        ?>
                </div>
                <h3 class="box-title">Used Quota Space - Users</h3>
                <div class="chart" id="revenue-chart2" style="height: 300px;"></div>
                <h3 class="box-title">Detailed Used Quota Space - Users</h3>
                <div class="chart" id="revenue-chart" style="height: 300px;"></div>
                <h3 class="box-title">Given Quota - Users</h3>
                <div class="chart" id="revenue-chart3" style="height: 300px;"></div>
                <script>
                $(function () {
                        "use strict";
                        var allocated_data = <?php echo $allocated?>;
                        // AREA CHART
                        var area = new Morris.Area({
                        element: 'revenue-chart',
                        resize: true,
                        data: allocated_data,
                        xkey: 'label',
                        ykeys: ['usage'],
                        labels: ['Users'],
                        hideHover: 'auto',
                        parseTime: false,
                        });

                        var block_data = <?php echo $pBlock?>;
                        var area = new Morris.Bar({
                        element: 'revenue-chart2',
                        resize: true,
                        data: block_data,
                        xkey: 'label',
                        ykeys: ['usage'],
                        labels: ['Users'],
                        hideHover: 'auto',
                        parseTime: false,
                        });

                        var node = <?php echo $qNodes?>;
                        var area = new Morris.Area({
                        element: 'revenue-chart3',
                        resize: true,
                        data: node,
                        xkey: 'label',
                        ykeys: ['usage'],
                        labels: ['Users'],
                        hideHover: 'auto',
                        parseTime: false,
                        });
                });
                </script>
        </div>
</body>
</html>
