<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Report Path
    |--------------------------------------------------------------------------
    | This option determines where all the generated and temp report files will
    | be stored.
    |
    */
    'folder'            => storage_path('reports'),
    'temp_folder'       => storage_path('reports/temp'),

    /*
    |--------------------------------------------------------------------------
    | Limitation Number of Generating report
    |--------------------------------------------------------------------------
    | This option determines how many messages that should get from database
    | and will be stored in report for each file. The per_batch should be lower than
    | max_row value.
    |
    */
    'per_batch'         => 5000,
    'max_row'           => 200000,
];
