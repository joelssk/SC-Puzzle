<?php
// Example PHP class for Sister's Seating
// Joel McCauley - 11/16/16 - For Showclix
include_once 'class.seating.php';
$map_data = new map();
echo "<pre>";
$map_data = new map();

//execute initial build
$map = $map_data->build(3, 11,["R1C4","R1C6","R2C3","R2C7","R3C9","R3C10"]);

print_r($map);

//reserve 7 seats
$reserve = $map_data->reserve($map, 7);

print_r($reserve);
?>
