<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Account Type
    |--------------------------------------------------------------------------
    |
    | Account type applied to self-service signups when the request does not
    | specify one. Accepted values: "buyer", "producer".
    |
    */

    'default_type' => env('ACCOUNT_DEFAULT_TYPE', 'buyer'),

];
