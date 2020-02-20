<?php
    $message = [
        'players' => [
            'x' => [
                'id' => '',
                'name'=> '',
            ],

            'o' => [
                'id' => '',
                'name' => '',
            ]
        ],

        'board' => [
            'x' => 0,
            'o' => 0,
            'state' => 0
        ],

        'viewers' => [],

        'can_exit' => 0,

        'can_sit' => [
            'x' => 0,
            'y' => 0
        ],

        'can_play' => 0,

        'ready' => [
            'x' => 0,
            'o' => 0
        ],

        'log' => ''
    ];

    var_dump(json_encode($message));