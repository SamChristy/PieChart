<?php
$width  = 400 * 1.5;
$height = 250 * 1.5;
$title = 'Browser Usage Statistics (January - April)';

if (extension_loaded('imagick'))
{
    require 'imagick/PieChart.php';
    $chart = new PieChart($width, $height);
}
else
{
    require 'gd/PieChartGD.php';
    $chart = new PieChartGD($width, $height);    
}

$chart->setTitle($title, 'fonts/Oswald/Oswald-Regular.ttf');

$chart->addSlice('Google Chrome',   27, '#4A7EBB');
$chart->addSlice('Mozilla Firefox', 23, '#DA8137');
$chart->addSlice('Apple Safari',    11, '#9BBB59');
$chart->addSlice('Opera',            3, '#BE4B48');
$chart->addSlice('Other',            5, '#7D60A0');

$chart->draw();

$chart->outputPNG('Browser Statistics 2012 Q1.png');
exit;