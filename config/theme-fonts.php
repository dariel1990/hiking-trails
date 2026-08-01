<?php

/*
|--------------------------------------------------------------------------
| Curated Theme Fonts
|--------------------------------------------------------------------------
|
| Font families selectable on the admin Theme settings tab. All families
| are served by Bunny Fonts (privacy-friendly Google Fonts mirror, already
| used by the app). Keys are the values stored in settings.
|
|   label   => shown in the admin dropdown
|   bunny   => family slug in the fonts.bunny.net css URL
|   weights => weights to load when this family is selected
|   stack   => full font-family stack emitted into the CSS variable
*/

return [
    'Inter' => [
        'label' => 'Inter',
        'bunny' => 'inter',
        'weights' => '300,400,500,600,700,800',
        'stack' => "'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif",
    ],
    'Playfair Display' => [
        'label' => 'Playfair Display',
        'bunny' => 'playfair-display',
        'weights' => '400,500,600,700',
        'stack' => "'Playfair Display', Georgia, serif",
    ],
    'Oswald' => [
        'label' => 'Oswald',
        'bunny' => 'oswald',
        'weights' => '300,400,500,600,700',
        'stack' => "'Oswald', 'Arial Narrow', sans-serif",
    ],
    'Montserrat' => [
        'label' => 'Montserrat',
        'bunny' => 'montserrat',
        'weights' => '300,400,500,600,700,800',
        'stack' => "'Montserrat', 'Segoe UI', sans-serif",
    ],
    'Poppins' => [
        'label' => 'Poppins',
        'bunny' => 'poppins',
        'weights' => '300,400,500,600,700',
        'stack' => "'Poppins', 'Segoe UI', sans-serif",
    ],
    'Lora' => [
        'label' => 'Lora',
        'bunny' => 'lora',
        'weights' => '400,500,600,700',
        'stack' => "'Lora', Georgia, serif",
    ],
    'Merriweather' => [
        'label' => 'Merriweather',
        'bunny' => 'merriweather',
        'weights' => '300,400,700',
        'stack' => "'Merriweather', Georgia, serif",
    ],
    'Nunito' => [
        'label' => 'Nunito',
        'bunny' => 'nunito',
        'weights' => '300,400,600,700,800',
        'stack' => "'Nunito', 'Segoe UI', sans-serif",
    ],
    'Raleway' => [
        'label' => 'Raleway',
        'bunny' => 'raleway',
        'weights' => '300,400,500,600,700',
        'stack' => "'Raleway', 'Segoe UI', sans-serif",
    ],
    'Source Sans 3' => [
        'label' => 'Source Sans 3',
        'bunny' => 'source-sans-3',
        'weights' => '300,400,600,700',
        'stack' => "'Source Sans 3', 'Segoe UI', sans-serif",
    ],
    'Work Sans' => [
        'label' => 'Work Sans',
        'bunny' => 'work-sans',
        'weights' => '300,400,500,600,700',
        'stack' => "'Work Sans', 'Segoe UI', sans-serif",
    ],
];
