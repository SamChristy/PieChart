<?php

/**
 * Description of PieChart
 *
 * @author Sam
 */
abstract class PieChart {
    protected $slices;
    protected $width;
    protected $height;
    protected $title;
    protected $hasLegend;
    protected $titleFont;
    protected $legendFont;
    protected $textColor;
    protected $backgroundColor;
    protected $canvas;

    /** 
     * Constructs the PieChart.
     * @param int $width The width of the chart, in pixels.
     * @param int $height The chart's height, in pixels.
     * @param string [$title] The chart's title.
     * @param string|int|array [$textColor] The colour of the title and labels.
     * @param string|int|array [$backgroundColor] 
     */
    public function __construct($width = 0, $height = 0, $title = '', $textColor = 0x222222,
            $backgroundColor = 0xffffff) {
        $this->width  = $width;
        $this->height = $height;
        $this->title  = $title;
        $this->hasLegend = true;
        $this->slices = array();
        $this->textColor = new PieChartColor($textColor);
        $this->backgroundColor = new PieChartColor($backgroundColor);
        
        // Feel free to change these to your favourite fonts...
        $this->titleFont  = __DIR__ . '/fonts/Open_Sans/OpenSans-Semibold.ttf';
        $this->legendFont = __DIR__ . '/fonts/Open_Sans/OpenSans-Regular.ttf';
    }

    /**
     * Frees the memory that was allocated to the image. Use this function to
     * clean up after your pie chart when you're finished with it.
     */
    public function destroy() {}

    /**
     * Sets the title's text. To remove a title, set the title to ''.
     * @param string $title
     * @param string $titleFont [optional] The path the .ttf font file for the title.
     */
    public function setTitle($title, $titleFont = NULL) {
        $this->title = $title;
        
        if($titleFont)
            $this->titleFont = $titleFont;
    }

    /**
     * Add or remove the chart's legend (it's displayed default).
     * @param bool $displayLegend Specify false to remove the legend or true to add one.
     * @param string $legendFont [optional] The path the .ttf font file for the legend's text.
     */
    public function setLegend($displayLegend, $legendFont = NULL) {
        $this->hasLegend = $displayLegend;
        
        if($legendFont)
            $this->legendFont = $legendFont;
    }

    /**
     * Adds a new slice to the pie. This function can also be used to modify the
     * value of existing slices. It is recommended that pie charts do not exceed
     * 6 slices.
     * @param string $name The name of the slice (used for legend label).
     * @param float $value
     * @param string $color The CSS colour, e.g. '#FFFFFF', 'rgb(255, 255, 255).
     */
    public function addSlice($name, $value, $color) {
        $this->slices[$name] = array(
            'value' => $value,
            'color' => new PieChartColor($color)
        );
    }

    /**
     * Removes the specified slice.
     * @param string $name The name of the slice to be removed.
     */
    public function removeSlice($name) {
        unset($this->slices[$name]);
    }

    /**
     * Sorts the slices by their...
     */
    public function sortSlices() {
        kSort($this->slices);
        
        uaSort($this->slices, function ($a, $b){
                return ($a['value'] > $b['value']);
            }
        );
    }

    /**
     * Draws the chart so it is ready to be echoed to the client or saved.
     */
    public function draw() {}

    public function output($filename, $format) {
        
    }
    
    public function forceDownload($filename, $format) {
        $extension = strToLower(pathInfo($filename, PATHINFO_EXTENSION));
        
        switch ($extension) {
            case 'jpeg':
            case 'jpg':
                
                break;
            
            case 'png':

                break;

            case 'gif':
                
                break;
            
            default:
                return false;
        }
    }
    
    public function save($filename, $format) {
        
    }
    
    /**
     * Echos the chart in the PNG format, with the correct headers set to display in a browser.
     * @param string $filename [optional] The filename for the picture.
     * @return bool The success of the operation.
     */
    public function outputPNG($filename = 'pie-chart.png') {}

    /**
     * Echos the chart in the PNG format, the headers are set to force a download rather than be
     * displayed by the browser.
     * @param string [$filename] An optional filename for the picture.
     * @return bool The success of the operation.
     */
    public function forceDownloadPNG($filename = 'pie-chart.png') {}

    /**
     * Saves the chart in the specified location, in the PNG format.
     * @return bool The success of the operation.
     */
    public function savePNG($filename) {}
}

/**
 * Utility class for storing colours in a library-agnostic format.
 * @package PieChart
 */
class PieChartColor {
    public $r;
    public $g;
    public $b;
    
    /**
     * Sets the colour using {@link setColor()}, if an argument is provided.
     * @param array|int|string [$color]
     */
    public function __construct($color = NULL) {
        if (!is_null($color)) {
            $this->setColor($color);
        }
    }
    
    /**
     * Sets the colour, using one of the following formats: '#FFFFFF', '#fff',
     * 'rgb(255, 255, 255)', [$r, $g, $b] or ARGB
     * ({@link http://en.wikipedia.org/wiki/ARGB#ARGB}).
     * @param array|int|string $color
     */
    public function setColor($color) {
        
        switch (getType($color)) {
            case 'array':
                $this->r = $color[0];
                $this->g = $color[1];
                $this->b = $color[2];
                break;
            
            case 'integer': // ARGB format
                $this->r = $color >> 16 & 0xFF;
                $this->g = $color >>  8 & 0xFF;
                $this->b = $color       & 0xFF;
                break;
            
            case 'string':
                $length = strLen($color);

                if ($length == 7) {
                    // e.g. '#FFFFFF'.
                    $this->r = hexDec(subStr($color, 1, 2));
                    $this->g = hexDec(subStr($color, 3, 2));
                    $this->b = hexDec(subStr($color, 5, 2));

                } else if ($length == 4) {
                    // e.g. '#FFF'.
                    $this->r = hexDec(subStr($color, 1, 1)) * 17;
                    $this->g = hexDec(subStr($color, 2, 1)) * 17;
                    $this->b = hexDec(subStr($color, 3, 1)) * 17;

                } else if (strToLower(subStr($color, 0, 4)) == 'rgb(') {
                    // e.g. 'rgb(255, 255, 255)'.
                    $listOfColors = subStr($color, 4, -1);
                    $arrayOfColors = explode(',', $listOfColors);

                    $this->r = intVal($arrayOfColors[0]);
                    $this->g = intVal($arrayOfColors[1]);
                    $this->b = intVal($arrayOfColors[2]);
                }
                break;
        }
    }
    
    /**
     * Returns the colour as an integer, in the ARGB format
     * ({@link http://en.wikipedia.org/wiki/ARGB#ARGB}).
     * @return int
     */
    public function toInt() {
        $color = 0;
        
        $color |= $this->r << 16;
        $color |= $this->g << 8;
        $color |= $this->b;
        
        return $color;
    }
    
    /**
     * Returns the colour in the RGB string format, e.g. 'rgb(0,0,0)'.
     * @return string
     */
    public function toRGB() {
        return 'rgb(' . $this->r . ',' . $this->g . ',' . $this->b . ')';
    }
    
    /**
     * Returns the color in the CSS hexadecimal format, e.g. '#000000'.
     * @return string
     */
    public function toHex() {
        return sprintf('#%02s%02s%02s', decHex($this->r), decHex($this->g), decHex($this->b));
    }
}