<?php
namespace SamChristy\PieChart;

/**
 * A lightweight class for drawing pie charts, using the GD library.
 * @author    Sam Christy <sam_christy@hotmail.co.uk>
 * @licence   GNU GPL v3.0 <http://www.gnu.org/licenses/gpl-3.0.html>
 * @copyright © Sam Christy 2013
 * @package   PieChart
 * @version   v2.0.0
 */
class PieChartGD extends PieChart {
    public function destroy() {
        imageDestroy($this->canvas);
    }

    /**
     * Draws the pie chart, with optional supersampled anti-aliasing.
     * @param int $aa
     */
    public function draw($aa = 4) {
        $this->canvas = imageCreateTrueColor($this->width, $this->height);

        // Set anti-aliasing for the pie chart.
        imageAntiAlias($this->canvas, true);

        imageFilledRectangle($this->canvas, 0, 0, $this->width, $this->height,
                $this->_convertColor($this->backgroundColor));
        
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
        
        // If anti-aliasing is enabled, we supersample the pie to work around
        // the fact that GD does not provide anti-aliasing natively.
        if ($aa > 0) {
            $ssDiameter = $pieDiameter * $aa;
            $ssCentreX = $ssCentreY = $ssDiameter / 2 ;
            $superSample = imageCreateTrueColor($ssDiameter, $ssDiameter);
            imageFilledRectangle($superSample, 0, 0, $ssDiameter, $ssDiameter,
                $this->_convertColor($this->backgroundColor));
            
            foreach ($this->slices as $slice) {
                $sliceWidth = 360 * $slice['value'] / $total;

                // Skip slices that are too small to draw / be visible.
                if ($sliceWidth == 0)
                    continue;
                
                $sliceEnd = $sliceStart + $sliceWidth;

                imageFilledArc(
                    $superSample,
                    $ssCentreX,
                    $ssCentreY,
                    $ssDiameter,
                    $ssDiameter,
                    $sliceStart,
                    $sliceEnd,
                    $this->_convertColor($slice['color']),
                    IMG_ARC_PIE
                );

                // Move along to the next slice.
                $sliceStart = $sliceEnd;
            }
            
            imageCopyResampled(
                $this->canvas, $superSample,
                $pieCentreX - $pieDiameter / 2, $pieCentreY - $pieDiameter / 2,
                0, 0,
                $pieDiameter, $pieDiameter,
                $ssDiameter, $ssDiameter
            );
            
            imageDestroy($superSample);
        }
        else {
            // Draw the slices.
            foreach ($this->slices as $slice) {
                $sliceWidth = 360 * $slice['value'] / $total;

                // Skip slices that are too small to draw / be visible.
                if ($sliceWidth == 0)
                    continue;

                $sliceEnd = $sliceStart + $sliceWidth;

                imageFilledArc(
                    $this->canvas,
                    $pieCentreX,
                    $pieCentreY,
                    $pieDiameter,
                    $pieDiameter,
                    $sliceStart,
                    $sliceEnd,
                    $this->_convertColor($slice['color']),
                    IMG_ARC_PIE
                );

                // Move along to the next slice.
                $sliceStart = $sliceEnd;
            }
        }
    }
    
    protected function _output($method, $format, $filename) {
        switch ($format) {
            case parent::FORMAT_GIF:
                if ($method == parent::OUTPUT_INLINE || $method == parent::OUTPUT_DOWNLOAD) {
                    return imageGIF($this->canvas);
                }
                else if ($method == parent::OUTPUT_SAVE) {
                    return imageGIF($this->canvas, $filename);
                }
                break;
                
            case parent::FORMAT_JPEG:
                if ($method == parent::OUTPUT_INLINE || $method == parent::OUTPUT_DOWNLOAD) {
                    return imageJPEG($this->canvas, NULL, $this->quality);
                }
                else if ($method == parent::OUTPUT_SAVE) {
                    return imageJPEG($this->canvas, $filename, $this->quality);
                }
                break;
            
            case parent::FORMAT_PNG:
                if ($method == parent::OUTPUT_INLINE || $method == parent::OUTPUT_DOWNLOAD) {
                    return imagePNG($this->canvas);
                }
                else if ($method == parent::OUTPUT_SAVE) {
                    return imagePNG($this->canvas, $filename);
                }
                break;
        }
        
        return false;  // The output method or format is missing!
    }

    /**
     * Draws the legend for the pieChart, if $this->hasLegend is true.
     * @param int $legendOffset The number of pixels the legend is offset by the title.
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
           $this->canvas, $x, $y, $x + $squareSize, $y + $squareSize, $this->_convertColor($color)
        );

        imageTTFText(
            $this->canvas,
            $fontSize,
            0,
            $labelX + abs($labelBBox[0]), // Eliminate left overhang.
            $labelY + abs($labelBBox[7]), // Eliminate area above the baseline.
            $this->_convertColor($this->textColor),
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
     * @var float x location
     * @var float y location
     * @return int The height of the title + padding.
     */
    protected function _drawTitle($x = 0, $y = 0) {
        if (!$this->title)
            return 0;

        $titleColor = $this->_convertColor($this->textColor);

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
            $this->canvas, $titleSize, 
            0,
            $x + abs($titleBBox[0]),  // Account for left overhang.
            $y + abs($titleBBox[7]),  // Account for the area above the baseline.
            $titleColor, 
            $this->titleFont, 
            $this->title
        );

        return $titleHeight + $titleTopPadding;
    }
    
    /**
     * A convenience function for converting PieChartColor objects to the format
     * that GD requires.
     */
    private function _convertColor(PieChartColor $color) {
        // Interestingly, GD uses the ARGB format internally, so 
        // PieChartColor::toInt() would actually work for everything but GIFs...
        return imageColorAllocate($this->canvas, $color->r, $color->g, $color->b);
    }
}