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
     * @param int    $width  The width of the chart, in pixels.
     * @param int    $height The chart's height, in pixels.
     * @param string $title  The chart's title.
     */
    public function __construct($width = 0, $height = 0, $title = '') {
        $this->width  = $width;
        $this->height = $height;
        $this->title  = $title;

        $this->hasLegend = true;
        $this->slices = array();
        
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
            'color' => $this->processColor($color)
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
    
    /**
     * Turns a CSS colour format (e.g. '#FFFFFF', '#fff', 'rgb(255, 255, 255)')
     * into an array of colours.
     * @param string $color The colour in a CSS format.
     * @return array array($red, $green, $blue)
     */
    public static function processColor($color) {
        $length = strLen($color);

        switch ($length) {
            case 7:  // e.g. '#FFFFFF'.
                $red   = hexDec(subStr($color, 1, 2));
                $green = hexDec(subStr($color, 3, 2));
                $blue  = hexDec(subStr($color, 5, 2));
                break;
            
            case 4:  // e.g. '#FFF'.
                $red   = hexDec(subStr($color, 1, 1)) * 17;
                $green = hexDec(subStr($color, 2, 1)) * 17;
                $blue  = hexDec(subStr($color, 3, 1)) * 17;
            
            default: // e.g. 'rgb(255, 255, 255)'.
                $listOfColors  = subStr($color, 4, -1);
                $arrayOfColors = explode(',', $listOfColors);

                $red   = intVal($arrayOfColors[0]);
                $green = intVal($arrayOfColors[1]);
                $blue  = intVal($arrayOfColors[2]);
                break;
        }

        return array($red, $green, $blue);
    }
    
}