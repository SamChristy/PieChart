<?php

/**
 * Abstract class that is designed to be extended for drawing pie charts with
 * different graphics libraries. Use PieChartGD or PieChartImagick to actually
 * draw your charts. 
 * @author    Sam Christy <sam_christy@hotmail.co.uk>
 * @licence   GNU GPL v3.0 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @copyright © Sam Christy 2013
 * @package   PieChart
 * @version   v1.2
 */
abstract class PieChart {
    const FORMAT_GIF = 1;
    const FORMAT_JPEG = 2;
    const FORMAT_PNG = 3;
    const OUTPUT_DOWNLOAD = 1;
    const OUTPUT_INLINE = 2;
    const OUTPUT_SAVE = 3;
    
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
    protected $quality;

    /** 
     * Constructs the PieChart.
     * @param int $width The width of the chart, in pixels.
     * @param int $height The chart's height, in pixels.
     * @param string [$title] The chart's title.
     * @param string|int|array [$textColor] The colour of the title and labels.
     * @param string|int|array [$backgroundColor] The color for the background.
     */
    public function __construct($width = 0, $height = 0, $title = '', $textColor = 0x222222,
            $backgroundColor = 0xffffff) {
        $this->width  = $width;
        $this->height = $height;
        $this->title  = $title;
        $this->hasLegend = true;
        $this->slices = array();
        $this->quality = 100;
        $this->textColor = new PieChartColor($textColor);
        $this->backgroundColor = new PieChartColor($backgroundColor);
        $this->titleFont  = __DIR__ . '/fonts/Open_Sans/OpenSans-Semibold.ttf';
        $this->legendFont = __DIR__ . '/fonts/Open_Sans/OpenSans-Regular.ttf';
    }

    /**
     * Frees the memory that was allocated to the image. Use this function to
     * clean up after your pie chart when you're finished with it.
     */
    public function destroy() {}

    /**
     * Sets the title's text. To remove the title, set it to ''.
     * @param string $title
     * @param string [$titleFont] The name of the font file for the title.
     */
    public function setTitle($title, $titleFont = NULL) {
        $this->title = $title;
        
        if($titleFont)
            $this->titleFont = $titleFont;
    }

    /**
     * Add or remove the chart's legend (it is displayed default).
     * @param bool $displayLegend Specify false to remove the legend or true to 
     * add one.
     * @param string [$legendFont] The name of the font for the legend's text.
     */
    public function setLegend($displayLegend, $legendFont = NULL) {
        $this->hasLegend = $displayLegend;
        
        if($legendFont)
            $this->legendFont = $legendFont;
    }

    /**
     * Set the quality for generating output in lossy formats.
     * @param int $quality An integer between 0 and 100 (inclusive).
     */
    public function setOutputQuality($quality) {
        $this->quality = $quality;
    }
    
    /**
     * Adds a new slice to the pie chart.
     * @param string $name The name of the slice (used for legend label).
     * @param float $value
     * @param string|int|array $color The CSS colour, e.g. '#FFFFFF', 'rgb(255, 255, 255)'.
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
     * Draws the chart so that it is ready for output.
     */
    public function draw() {}
    
    /**
     * For child classes to override, so that the output functions work.
     * @param int $method
     * @param int $format
     * @param string $filename
     */
    protected function _output($method, $format, $filename) {}
    
    /**
     * Echos the chart as a GIF and instructs the browser to display it inline.
     * @param string [$filename] The filename for the picture.
     * @return bool true if successful, false otherwise (implementation-dependent).
     */
    public function outputGIF($filename = 'pie-chart.gif') {
        header('Content-Type: image/gif');
        header("Content-Disposition: inline; filename=\"$filename\"");
        
        return $this->_output(self::OUTPUT_INLINE, self::FORMAT_GIF, $filename);
    }
    
    /**
     * Echos the chart as a JPEG and instructs the browser to display it inline.
     * @param string [$filename] The filename for the picture.
     * @return bool true if successful, false otherwise (implementation-dependent).
     */
    public function outputJPEG($filename = 'pie-chart.jpg') {
        header('Content-Type: image/jpeg');
        header("Content-Disposition: inline; filename=\"$filename\"");
        
        return $this->_output(self::OUTPUT_INLINE, self::FORMAT_JPEG, $filename);
    }
    
    /**
     * Echos the chart as a PNG and instructs the browser to display it inline.
     * @param string [$filename] The filename for the picture.
     * @return bool true if successful, false otherwise (implementation-dependent).
     */
    public function outputPNG($filename = 'pie-chart.png') {
        header('Content-Type: image/png');
        header("Content-Disposition: inline; filename=\"$filename\"");
        
        return $this->_output(self::OUTPUT_INLINE, self::FORMAT_PNG, $filename);
    }

    /**
     * Echos the chart as a GIF and instructs the browser to force the user to
     * save it.
     * @param string [$filename] The filename for the picture.
     * @return bool true if successful, false otherwise (implementation-dependent).
     */
    public function forceDownloadGIF($filename = 'pie-chart.gif') {
        header("Pragma: public");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.$filename.'"');
        
        return $this->_output(self::OUTPUT_INLINE, self::FORMAT_GIF, $filename);
    }
    
    /**
     * Echos the chart as a JPEG and instructs the browser to force the user to
     * save it.
     * @param string [$filename] The filename for the picture.
     * @return bool true if successful, false otherwise (implementation-dependent).
     */
    public function forceDownloadJPEG($filename = 'pie-chart.jpg') {
        header("Pragma: public");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.$filename.'"');
        
        return $this->_output(self::OUTPUT_INLINE, self::FORMAT_JPEG, $filename);
    }
    
    /**
     * Echos the chart as a PNG and instructs the browser to force the user to
     * save it.
     * @param string [$filename] The filename for the picture.
     * @return bool true if successful, false otherwise (implementation-dependent).
     */
    public function forceDownloadPNG($filename = 'pie-chart.png') {
        header("Pragma: public");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="'.$filename.'"');
        
        return $this->_output(self::OUTPUT_DOWNLOAD, self::FORMAT_PNG, $filename);
    }

    /**
     * Saves the chart as a GIF, in the specified location.
     * @param string $filename
     * @return int true if successful, false otherwise (implementation-dependent)..
     */
    public function saveGIF($filename) {
        return $this->_output(self::OUTPUT_SAVE, self::FORMAT_GIF, $filename);
    }
    
    /**
     * Saves the chart as a JPEG, in the specified location.
     * @param string $filename
     * @return int true if successful, false otherwise (implementation-dependent).
     */
    public function saveJPEG($filename) {
        return $this->_output(self::OUTPUT_SAVE, self::FORMAT_JPEG, $filename);
    }
    
    /**
     * Saves the chart as a PNG, in the specified location.
     * @param string $filename
     * @return int true if successful, false otherwise (implementation-dependent).
     */
    public function savePNG($filename) {
        return $this->_output(self::OUTPUT_SAVE, self::FORMAT_PNG, $filename);
    }
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