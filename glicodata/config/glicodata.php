<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Development Authentication Bypass
    |--------------------------------------------------------------------------
    |
    | Keep disabled outside local design/debug sessions. When enabled, the
    | Keycloak guard resolves one local UBS record so API policies and tenant
    | scoping can still execute without an institutional token.
    |
    */

    'auth_disabled' => (bool) env('GLICODATA_AUTH_DISABLED', false),

    'auth_bypass_ubs_email' => env('GLICODATA_AUTH_BYPASS_UBS_EMAIL'),

];
