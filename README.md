PieChart
========

A reasonably efficient class for drawing pie charts with ImageMagick or GD in PHP. Intended as a 
learning exercise for using the NetBeans IDE and the Xdebug profiler and debugger. The code is 
available under the [GNU GPL v3.0](http://www.gnu.org/licenses/gpl-3.0.html), so feel free to use it
with attribution. I recommend using the Imagick version, `imagick/PieChart.php` over the GD version,
 `gd/PieChart.php`.

### Demonstration ###
Below is the code required to generate a pie chart and echo it to the client's browser. The example 
uses the method `outputPNG()` to tell the browser to render the image. Alternatively, the function 
`forceDownloadPNG()` can be used to instruct the browser to bring up the save dialog.

#### Code ####
````php
<?php
include 'imagick/PieChart.php';

$width  = 400 * 1.5;
$height = 250 * 1.5;
$title = 'Browser Usage Statistics (January - April)';

$chart = new PieChart($width, $height);

$chart->setTitle($title, 'fonts/Oswald/Oswald-Regular.ttf');

$chart->addSlice('Google Chrome',   27, '#4A7EBB');
$chart->addSlice('Mozilla Firefox', 23, '#DA8137');
$chart->addSlice('Apple Safari',    11, '#9BBB59');
$chart->addSlice('Opera',            3, '#BE4B48');
$chart->addSlice('Other',            5, '#7D60A0');

$chart->draw();

$chart->outputPNG('Browser Statistics 2012 Q1.png');
````
#### Output ####
![Pie Chart](https://github.com/SamChristy/PieChart/raw/master/saved-charts/Browser%20Statistics%202012%20Q1.png)