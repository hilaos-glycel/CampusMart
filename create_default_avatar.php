<?php
/**
 * Create Default Avatar PNG
 * Generate a simple default avatar image
 */

// Create a simple avatar image
$width = 40;
$height = 40;

// Create image
$image = imagecreatetruecolor($width, $height);

// Colors
$background = imagecolorallocate($image, 107, 114, 128); // Gray background
$white = imagecolorallocate($image, 255, 255, 255);

// Fill background
imagefill($image, 0, 0, $background);

// Draw head (circle)
$head_x = 20;
$head_y = 16;
$head_radius = 6;
imagefilledellipse($image, $head_x, $head_y, $head_radius * 2, $head_radius * 2, $white);

// Draw body (arc/semicircle)
$body_x = 20;
$body_y = 32;
$body_width = 24;
$body_height = 16;
imagefilledarc($image, $body_x, $body_y, $body_width, $body_height, 0, 180, $white, IMG_ARC_PIE);

// Save as PNG
$filename = 'assets/images/default-avatar.png';
imagepng($image, $filename);
imagedestroy($image);

echo "<h1>✅ Default Avatar Created</h1>";
echo "<p>Created default avatar at: <strong>{$filename}</strong></p>";
echo "<img src='{$filename}' alt='Default Avatar' style='border: 1px solid #ddd; border-radius: 50%;'>";
echo "<p><a href='messages.php'>Test Messages Page</a></p>";
?>

<style>
body { font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; text-align: center; }
</style>
