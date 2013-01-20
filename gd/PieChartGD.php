<?php
/**
 * Super cool pie chart drawing class that uses GD, despite the fact that it's inferior 
 * to ImageMagick...
 * 
 * <b>Reasons to dislike GD:</b>
 * <ul>
 * <li>No anti-aliasing</li>
 * <li>No font pre-loading</li>
 * <li>Procedural interface</li>
 * </ul>
 */
class PieChartGD {
// TODO: Add anti-aliasing for GD.
    const POSITION_LEFT = 0;
    const POSITION_TOP = 1;
    const POSITION_RIGHT = 2;
    const POSITION_BOTTOM = 3;

    protected $slices;
    protected $width;
    protected $height;
    protected $title;
    protected $image;
    protected $hasLegend;
    protected $titleFont;
    protected $legendFont;
    protected $textColor;

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
        $this->image = imageCreateTrueColor($width, $height);
        
        // Feel free to change these to your favourite fonts...
        $this->titleFont  = __DIR__ . '/../fonts/OpenSans-Semibold.ttf';
        $this->legendFont = __DIR__ . '/../fonts/OpenSans-Regular.ttf';

        $this->textColor = imageColorAllocate($this->image, 34, 34, 34);

        // Set anti-aliasing for the pie chart.
        imageAntiAlias($this->image, true);

        $bgColor = imageColorAllocate($this->image, 255, 255, 255);
        //imageFilledRectangle($this->image, 1, 1, $width - 2, $height - 2, $bgColor);
        imageFilledRectangle($this->image, 0, 0, $width, $height, $bgColor);
        imageRectangle($this->image, 0, 0, $width - 1, $height - 1, $this->textColor);
    }

    /**
     * Frees the memory that was allocated to the image. You must call this function to clean up
     * after your pie chart once you're finished with it.
     */
    public function destroy() {
        imageDestroy($this->image);
    }

    /**
     * Sets the title's text. To remove a title, set the title to ''.
     * @param string $title The title's text.
     * @param string $titleFont [optional] The .ttf font file for the legend's font.
     */
    public function setTitle($title, $titleFont = NULL) {
        $this->title = $title;
        
        if($titleFont)
            $this->titleFont = $titleFont;
    }

    /**
     * Add or remove the chart's legend (it's displayed default).
     * @param bool $displayLegend Specify false to remove the legend or true to add one.
     * @param string $legendFont [optional] The .ttf font file for the legend's font.
     */
    public function setLegend($displayLegend, $legendFont = NULL) {
        $this->hasLegend = $displayLegend;
        
        if($legendFont)
            $this->legendFont = $legendFont;
    }

    /**
     * Adds a new slice to the pie. This function can also be used to modify the value of 
     * existing slices. It is recommended that pie charts do not exceed 6 slices.
     * @param string $name The name of the slice (used for legend label).
     * @param float $value
     * @param string $color The CSS colour, e.g. '#FFFFFF', 'rgb(255, 255, 255).
     */
    public function addSlice($name, $value, $color) {
        
        $processedColor = is_array($color) ? $color : PieChartGD::processColor($color);

        $red   = $processedColor[0];
        $green = $processedColor[1];
        $blue  = $processedColor[2];

        $this->slices[$name] = array(
            'value' => $value,
            'color' => imageColorAllocate($this->image, $red, $green, $blue)
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
     * Sorts the slices by their values.
     * @param bool [$sortByValues] True (default) to sort by values, false to sort by keys.
     * @param bool [$descending] True (default) for descending order, false for ascending.
     */
    public function sortSlices($sortByValues = true, $descending = true) {
        // TODO Write sortSlices()
    }

    /**
     * Draws the chart so it is ready to be echoed to the client or saved.
     */
    public function draw() {
        $total = 0;
        $sliceStart = -90;  // Start at 12 o'clock.

        $titleHeight = $this->_drawTitle();
        $legendWidth = $this->_drawLegend($titleHeight);

        // Account for the space occupied by the legend and its padding.
        $pieCentreX = ($this->width - $legendWidth) / 2;

        // Account for the space occupied by the title.
        $pieCentreY = $titleHeight + ($this->height - $titleHeight) / 2;

        // 10% padding on the top and bottom of the pie.
        $pieDiameter = round(
                min($this->width - $legendWidth, $this->height - $titleHeight) * 0.85
        );

        foreach ($this->slices as $slice)
            $total += $slice['value'];

        // Draw the slices.
        foreach ($this->slices as &$slice) {
            $sliceWidth = 360 * $slice['value'] / $total;

            // Skip slices that are too small to draw / be visible.
            if ($sliceWidth == 0)
                continue;

            $sliceEnd = $sliceStart + $sliceWidth;

            imageFilledArc(
                $this->image,
                $pieCentreX,
                $pieCentreY,
                $pieDiameter,
                $pieDiameter,
                $sliceStart,
                $sliceEnd,
                $slice['color'],
                IMG_ARC_PIE
            );
            
            // Unfortunatley this function is just too inefficient!
            // It also isn't compatible with the GD colour format...
//            imageSmoothArc(
//                $this->image,
//                $pieCentreX,
//                $pieCentreY,
//                $pieDiameter,
//                $pieDiameter,
//                array(rand(0, 255), rand(0, 255), rand(0, 255), 0),
//                $sliceStart,
//                $sliceEnd
//            );
            

            // Move along to the next slice.
            $sliceStart = $sliceEnd;
        }
    }

    // TODO Decide what I want to do with file extensions with the outputting functions.
    
    /**
     * Echos the chart in the PNG format, with the correct headers set to display in a browser.
     * @param string $filename [optional] The filename for the picture.
     * @return bool The success of the operation.
     */
    public function outputPNG($filename = 'pie-chart') {
        header('Content-Type: image/png');
        header("Content-Disposition: inline; filename=\"$filename.png\"");

        return imagePNG($this->image);
    }

    /**
     * Echos the chart in the PNG format, the headers are set to force a download rather than be
     * displayed by the browser.
     * @param string [$filename] An optional filename for the picture.
     * @return bool The success of the operation.
     */
    public function forceDownloadPNG($filename = 'pie-chart') {
        // TODO Find out which 'Content-Type' I really should be using for forceDownloadPNG.
        
//        header('Content-Type: application/octet-stream');
        header('Content-Type: image/png');
        header("Content-Disposition: attachment; filename=\"$filename\"");

        return imagePNG($this->image);
    }

    /**
     * Saves the chart in the specified location, in the PNG format.
     * @return bool The success of the operation.
     */
    public function savePNG($filename) {
        return imagePNG($this->image, "$filename.png");
    }
    
    /**
     * Turns a CSS colour format, e.g. '#FFFFFF', '#fff', 'rgb(255, 255, 255)', etc. into an 
     * array of colours. This is a utility function and can be used outside of the class.
     * @param string $color The colour in a CSS format.
     * @return array array(red, green, blue)
     */
    public static function processColor($color) {
        $length = strLen($color);
        $red = $green = $blue = 0;

        if ($length == 7) {
            // e.g. '#FFFFFF'.
            $red   = hexDec(subStr($color, 1, 2));
            $green = hexDec(subStr($color, 3, 2));
            $blue  = hexDec(subStr($color, 5, 2));
            
        } else if ($length == 4) {
            // e.g. '#FFF'.
            $red   = hexDec(subStr($color, 1, 1)) * 17;
            $green = hexDec(subStr($color, 2, 1)) * 17;
            $blue  = hexDec(subStr($color, 3, 1)) * 17;
            
        } else if (strToLower(subStr($color, 0, 4)) == 'rgb(') {
            // e.g. 'rgb(255, 255, 255)'.
            $listOfColors  = subStr($color, 4, -1);
            $arrayOfColors = explode(',', $listOfColors);

            $red   = intVal($arrayOfColors[0]);
            $green = intVal($arrayOfColors[1]);
            $blue  = intVal($arrayOfColors[2]);
        }

        return array($red, $green, $blue);
    }

    /**
     * Draws the legend for the pieChart, if $this->hasLegend is true.
     * @param int $legendOffset The number of pixels the legend is offset by the title.
     * @param int $legendPadding The legend's padding, in pixels.
     * @return int The width of the legend and its padding.
     */
    protected function _drawLegend($legendOffset) {
        if (!$this->hasLegend)
            return 0;

        // Determine the ideal font size for the legend text;
        $legendFontSize = $this->width * 0.022;

        // If the legend's font size is too small, we won't bother drawing it.
        if (ceil($legendFontSize) < 8)
            return 0;

        // Calculate the size and padding for the color squares.
        $squareSize    = $this->height * 0.060;
        $squarePadding = $this->height * 0.025;
        $labelPadding  = $this->height * 0.025;

        $sliceCount = count($this->slices);

        $legendPadding = 0.05 * $this->width;

        // Determine the width and height of the legend.
        $legendWidth = $squareSize + $labelPadding + $this->_maxLabelWidth($legendFontSize);
        $legendHeight = $sliceCount * ($squareSize + $squarePadding) - $squarePadding;

        // If the legend and its padding occupy too much space, we will not draw it.		
        if ($legendWidth + $legendPadding * 2 > $this->width / 2)  // Too wide.
            return 0;

        if ($legendHeight > $this->height - $legendOffset - $legendPadding * 2)  // Too high.
            return 0;

        $legendX = $this->width - $legendWidth - $legendPadding;
        $legendY = ($this->height - $legendOffset) / 2 + $legendOffset - $legendHeight / 2;

        $i = 0;
        foreach ($this->slices as $sliceName => $slice) {
            // Move down...
            $OffsetY = $i++ * ($squareSize + $squarePadding);

            $this->_drawLegendKey(
                $legendX,
                $legendY + $OffsetY,
                $slice['color'],
                $sliceName,
                $squareSize,
                $labelPadding,
                $legendFontSize
            );
        }

        return $legendWidth + $legendPadding * 2;
    }

    /**
     * Draws the legend key at the specific location.
     * @param int $x The x coordinate for the key's top, left corner.
     * @param int $y The y coordinate for the key's top, left corner.
     * @param object $color The GD colour identifier, created with imageColorAllocate().
     * @param string $label
     * @param int $squareSize The size of the square, in pixels.
     * @param int $labelPadding
     * @param int $fontSize
     */
    protected function _drawLegendKey($x, $y, $color, $label, $squareSize, $labelPadding,
            $fontSize) {
        $labelX = $x + $squareSize + $labelPadding;

        // Centre the label vertically to the square.
        $labelBBox = imageTTFBBox($fontSize, 0, $this->legendFont, $label);
        $labelHeight = abs($labelBBox[7] - $labelBBox[1]);

        $labelY = $y + $squareSize / 2 - $labelHeight / 2;

        imageFilledRectangle(
           $this->image, $x, $y, $x + $squareSize, $y + $squareSize, $color
        );

        imageTTFText(
            $this->image,
            $fontSize,
            0,
            $labelX + abs($labelBBox[0]), // Eliminate left overhang.
            $labelY + abs($labelBBox[7]), // Eliminate area above the baseline.
            $this->textColor,
            $this->legendFont,
            $label
        );
    }

    /**
     * Returns the width, in pixels, of the chart's widest label.
     * @return int
     */
    protected function _maxLabelWidth($fontSize) {
        $widestLabelWidth = 0;

        foreach ($this->slices as $sliceName => $slice) {
            // Measure the label.
            $boundingBox = imageTTFBBox($fontSize, 0, $this->legendFont, $sliceName);
            $labelWidth = $boundingBox[2] - $boundingBox[0];

            if ($labelWidth > $widestLabelWidth)
                $widestLabelWidth = $labelWidth;
        }

        return $widestLabelWidth;
    }

    /**
     * Draws and returns the height of the title and its padding (in pixels). If no title is 
     * specified, then nothing is drawn and 0 is returned.
     * @return int The height of the title + padding.
     */
    protected function _drawTitle($x = 0, $y = 0, $orientation = 0) {
        if (!$this->title)
            return 0;

        $titleColor = imageColorAllocate($this->image, 34, 34, 34);

        // Determine ideal font size for the title.
        $titleSize = 0.0675 * $this->height;  // The largest sensible value.
        $minTitleSize = 10;                   // The smallest legible value.

        do {
            $titleBBox = imageTTFBBox($titleSize, 0, $this->titleFont, $this->title);
            $titleWidth = $titleBBox[2] - $titleBBox[0];

            // If we can fit the title in, with 5% padding on each side, then we can 
            // draw it.
            if ($titleWidth <= ($this->width * 0.9))
                break;

            $titleSize -= 0.5; // Try a smaller font size.
        } while ($titleSize >= $minTitleSize);

        // If the title is simply too long to be drawn legibly, then we will simply not 
        // draw it.
        if ($titleSize < $minTitleSize)
            return 0;

        $titleHeight = abs($titleBBox[7] - $titleBBox[1]);

        // Give the title 7.5% top padding.
        $titleTopPadding = 0.075 * $this->height;

        // Centre the title.
        $x = $this->width / 2 - $titleWidth / 2;
        $y = $titleTopPadding;

        imageTtfText(
            $this->image, $titleSize, 
            0,
            $x + abs($titleBBox[0]),  // Account for left overhang.
            $y + abs($titleBBox[7]),  // Account for the area above the baseline.
            $titleColor, 
            $this->titleFont, 
            $this->title
        );

        return $titleHeight + $titleTopPadding;
    }

}