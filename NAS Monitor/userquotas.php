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
</head>
<body>
        <div class="container">
                <div class="header clearfix">
                        <div>
                                <nav>
                                        <ul class="nav nav-pills pull-right">
                                                <li role="presentation"><a href="/nasmonitor">Home</a></li>
                                                <li role="presentation" class="active"><a href="#">User Quotas</a></li>
                                                <li role="presentation"><a href="/nasmonitor/nashistograms.php">Histograms</a></li>
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
                                foreach($parts_uquotas as $uquotas){
                                                $parts = preg_split('/\s+/', $uquotas);
                                                if(array_key_exists($parts[0], $array_uspace)){
                                                        echo "<b>User:</b> ". $parts[0]." <br><b>Quota: </b>".$parts[1]." <b>Used space:</b> ".$array_uspace[$parts[0]]."<br>";
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
                                                        echo '<div class="progress"><div class="progress-bar" role="progressbar" aria-valuenow="70" aria-valuemin="0" aria-valuemax="100" style="width:'.$percentage.'%">'.number_format($percentage,2).'% used'.'</div></div>';
                                                }
                                }
                        ?>
                </div>
        </div>
</body>
</html>
