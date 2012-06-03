<?php
/*
 * This benchmark is designed to measure the performance of the PieChart class.
 */

include '../PieChart.php';

$testSize = 20;    // The number of pie charts to be drawn.
$drawingTime = 0;  // The time spent drawing the pie charts.
$writingTime = 0;  // The time spend writing the pie charts to disk (saving).
$totalTime = 0;    // The benchmark's total execution time.

if(isset($_GET['size'])) $testSize = $_GET['size'];

// Create a new, unique directory.
$outputFolder = getcwd() . '/output/' . time();
mkdir($outputFolder);

// Total time taken start.
$totalStart = microtime(true);

for($i = 0; $i < $testSize; $i++){
	$width = 500;
	$height = 300;
	$title = 'Browser Usage Statistics (January - April)';
	
	$chart = new PieChart($width, $height, $title);
	
	// 1. Create 5 slices with random values.
	$chart->addSlice('Google Chrome',   rand(0, 5000), '#4A7EBB');
	$chart->addSlice('Mozilla Firefox', rand(0, 5000), '#DA8137');
	$chart->addSlice('Apple Safari',    rand(0, 5000), '#9BBB59');
	$chart->addSlice('Opera',           rand(0, 5000), '#7D60A0');
	$chart->addSlice('Other',           rand(0, 5000), '#BE4B48');
	
	// 2. Draw the pie chart and measure the ammount of time that it takes.
	$chartDrawStart = microtime(true);
	$chart->draw();
	$chartDrawTime = microtime(true) - $chartDrawStart;
	
	// 3. Save the pie chart and measure the ammount of time that it takes.
	$chartWriteStart = microtime(true);
	$chart->savePNG("$outputFolder/Pie Chart #$i");
	$chartWriteTime = microtime(true) - $chartWriteStart;
	
	$chart->destroy();
	
	$drawingTime += $chartDrawTime;
	$writingTime += $chartWriteTime;
}

$totalFinish = microtime(true);
$totalTime = $totalFinish - $totalStart;

// 4. Finally, echo the results to the browser.

$reportPrecision = 0;
$reportUnit = 1000;   // milliseconds

$reportDrawingTime = round($drawingTime * $reportUnit, $reportPrecision);
$reportDrawingTimePerChart = round($drawingTime / $testSize * $reportUnit, $reportPrecision);

$reportWritingTime = round($writingTime * $reportUnit, $reportPrecision);
$reportWritingTimePerChart = round($writingTime / $testSize * $reportUnit, $reportPrecision);

$reportTotalTime = round($totalTime * $reportUnit, $reportPrecision);
$reportTotalTimePerChart = round($totalTime / $testSize * $reportUnit, $reportPrecision);

?>

<table style="text-align: left; font: 11pt Calibri">
	<thead>
		<tr>
			<th colspan="2" style="font-size:120%">Results (milliseconds)</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<th>Test size:</th>
			<td><?=$testSize?></td>
		</tr>
		<tr>
			<th>Total drawing time:</th>
			<td><?=$reportDrawingTime?></td>
		</tr>
		<tr>
			<th>Drawing time per chart:</th>
			<td><?=$reportDrawingTimePerChart?></td>
		</tr>
		<tr>
			<th>Total writing time:</th>
			<td><?=$reportWritingTime?></td>
		</tr>
		<tr>
			<th>Writing time per chart:</th>
			<td><?=$reportWritingTimePerChart?></td>
		</tr>
		<tr>
			<th>Total time taken:</th>
			<td><?=$reportTotalTime?></td>
		</tr>
		<tr>
			<th>Total time per chart:</th>
			<td><?=$reportTotalTimePerChart?></td>
		</tr>
	</tbody>
</table>