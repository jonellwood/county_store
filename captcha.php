<?php

session_start();

$permitted_chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";

// This code creates a randomly generated string of length $strength (defaulted to 10) from the characters passed in $input. It does this by looping through each character passed in and picking it randomly, adding it to an empty string which is then returned.
// The function takes two parameters:
// The $input parameter contains the set of characters from which the random output string will be generated i.e. uppercase letters, lowercase letters, numbers etc.
// The $strength parameter allows you to specify a desired length for your random string. By default it is set to 10.
// It starts by getting the length of the input string using the strlen($input) method, then it uses the mt_rand(0, $input_length - 1) function to generate a random number between 0 and one less than the length of the input string which is used as the index for the character it will choose from the $input string. The chosen character is added to the empty string, and then looped over until it reaches the $strength limit. Finally, the resulting string is returned by the function.

// Generates a random string
function generate_string($input, $strength = 10)
{
    // Gets the length of the input
    $input_length = strlen($input);

    // Creates the empty random string
    $random_string = '';

    // Iterate over the strength of the output
    for ($i = 0; $i < $strength; $i++) {
        // Get a random character of the input
        $random_character = $input[mt_rand(0, $input_length - 1)];

        // Add the character to the string
        $random_string .= $random_character;
    }

    // Return the generated string
    return $random_string;
}



// Set the image width and height
$image = imagecreatetruecolor(200, 50);
// If creating a new GD image stream fails, throw an error message
if (!$image) die('Cannot Initialize new GD image stream');


// Enable image anti-aliasing
imageantialias($image, true);


// assign random color values to the colors array 
$colors = [];

// generate random red value       
$red = rand(125, 175);

// generate random green value       
$green = rand(125, 175);

// generate random blue value       
$blue = rand(125, 175);


// Loop through the colors array five times
for ($i = 0; $i < 5; $i++) {

    // Subtract 20 from red, green and blue values each time to get different colors
    $colors[] = imagecolorallocate($image, $red - 20 * $i, $green - 20 * $i, $blue - 20 * $i);
}


imagefill($image, 0, 0, $colors[0]);

for ($i = 0; $i < 10; $i++) {
    imagesetthickness($image, rand(2, 10));
    $rect_color = $colors[rand(1, 4)];
    imagerectangle($image, rand(-10, 190), rand(-10, 10), rand(-10, 190), rand(40, 60), $rect_color);
}

$black = imagecolorallocate($image, 0, 0, 0);
$white = imagecolorallocate($image, 255, 255, 255);
$textcolors = [$black, $white];

$fonts = [dirname(__FILE__) . './fonts/Acme-Regular.ttf', dirname(__FILE__) . './fonts/Ubuntu-Regular.ttf'];
$string_length = 6;
$captcha_string = generate_string($permitted_chars, $string_length);
// $_SESSION['captcha_text'] = $captcha_string;


for ($i = 0; $i < $string_length; $i++) {
    $letter_space = 170 / $string_length;
    $initial = 15;

    imagettftext($image, 20, rand(-15, 15), $initial + $i * $letter_space, rand(20, 40), $textcolors[rand(0, 1)], $fonts[array_rand($fonts)], $captcha_string[$i]);
}
// echo var_dump($captcha_string);

header('Content-type: image/png');
imagepng($image);
imagedestroy($image);
