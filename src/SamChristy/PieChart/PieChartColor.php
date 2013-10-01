<?php
namespace SamChristy\PieChart;

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