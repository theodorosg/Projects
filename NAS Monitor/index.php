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
                                                <li role="presentation" class="active"><a href="#">Home</a></li>
                                                <li role="presentation"><a href="userquotas.php">User Quotas</a></li>
                                                <li role="presentation"><a href="nashistograms.php">Histograms</a></l
i>
                                        </ul>
                                </nav>
                                <h3 class="text-muted">NAS01 Monitor</h3>
                        </div>
                </div>
                <p></p>
                <p align="center"><b>Total Allocated Space</b></p>
                <div>
                        <?php
                                $quotas = file_get_contents("neut-quotas.txt");
                                $used_space = file_get_contents("used-space.txt");
                                $parts_uspace = explode("\n",$used_space);
                                $array_uspace = array();
                                foreach($parts_uspace as $uspace){
                                                $parts = preg_split('/\s+/', $uspace);
                                                $folder_name = explode("/users/",$uspace);
                                                if(count($parts) != 1){
                                                        $array_uspace[$folder_name[1]] = $parts[0];
                                                }
                                }

                                $space_allocated = 0;
                                $parts_uquotas = explode("\n",$quotas);
                                foreach($parts_uquotas as $uquotas){
                                                $parts = preg_split('/\s+/', $uquotas);
                                                if(array_key_exists($parts[0], $array_uspace)){
                                                        if(strpos($array_uspace[$parts[0]],'K') !== FALSE){
                                                                $get_space = explode("K",$array_uspace[$parts[0]]);
                                                                $space = floatval($get_space[0]);
                                                                $max_quota = explode("GB",$parts[1]);
                                                                $mquota = intval($max_quota[0]);
                                                        }else if(strpos($array_uspace[$parts[0]],'M') !== FALSE){
                                                                $get_space = explode("M",$array_uspace[$parts[0]]);
                                                                $space = floatval($get_space[0]);
                                                                $max_quota = explode("GB",$parts[1]);
                                                                $mquota = intval($max_quota[0]);
                                                        }else{
                                                                $get_space = explode("G",$array_uspace[$parts[0]]);
                                                                $space = floatval($get_space[0]);
                                                                $max_quota = explode("GB",$parts[1]);
                                                                $mquota = intval($max_quota[0]);
                                                        }
                                                        $space_allocated = $space_allocated + $mquota;
                                                }
                                }
                                $total_space = 39370;
                                $percentage = ($space_allocated/$total_space)*100;
                                echo '<div class="progress"><div class="progress-bar" role="progressbar" aria-valuenow="70" aria-valuemin="0" aria-valuemax="100" style="width:'.$percentage.'%">'.number_format($percentage,2).'% Allocated'.'</div></div>';
                        ?>
                </div>
        </div>
</body>
</html>
