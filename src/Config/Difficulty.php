<?php

    namespace App\Config;

    enum Difficulty: string{
        case VeryEasy = 'very easy';
        case Easy = 'easy';
        case Average = 'average';
        case Hard = 'hard';
        case VeryHard = 'very hard';
    }