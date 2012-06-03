<?php
include 'imagick/PieChart.php';

// TODO Add more default colours for the Pie Chart. Possibly include them as class constants?
// TODO Finish this... :)
$defaultColors = array(
    '#4A7EBB',  // blue
    '#9BBB59',  // green
    '#DA8137',  // orange
    '#7D60A0',  // purple
    '#BE4B48',  // red
    '#F6B915'   // gold
);

// TODO Create a form for this!
$chartWidth    = get('width',  400);
$chartHeight   = get('height', 250);
$chartTitle    = get('title',  '');
$chartFilename = get('filename', 'pie-chart');
$chartFiletype = get('filetype', 'png');
$chartLegend   = get('legend', true);
$chartData     = get('data');
$sliceColors   = get('colors', $defaultColors);

if(!$chartData) die('The pie chart needs data!');

$chart = new PieChart($chartWidth, $chartHeight, $chartTitle);

$i = 0;
foreach($chartData as $sliceName => $sliceValue)
    $chart->addSlice($sliceName, $sliceValue, $sliceColors[$i++]);

$chart->draw();

switch (strToLower($chartLegend)) {
    case 'gif':
        // $chart->outputGIF($chartFilename);
        break;
    case 'jpeg':
        // $chart->outputJPEG($chartFilename);
        break;
    case 'png':
    default:
        $chart->outputPNG($chartFilename);
        break;
}

$chart->destroy();

function get($inputName, $defaultValue = NULL){
    return isset($_GET[$inputName]) ? $_GET[$inputName] : $defaultValue;
}
?>