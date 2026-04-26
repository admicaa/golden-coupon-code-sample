<?php
return  [
    'view-admins' => [],
    'create-admins' => [],
    'edit-admins' => ['view-admins', 'view-roles', 'edit-his-profile-data'],
    'edit-admins-passwords' => ['view-admins', 'view-roles', 'edit-his-password'],
    'delete-admins' => ['view-admins', 'view-roles'],
    //roles management

    'view-roles' => [],
    'edit-roles' => ['view-roles'],
    'create-roles' => [],
    'delete-roles' => ['view-roles'],

    // language settings
    'view-languages' => [],
    'create-languages' => ['view-languages'],
    'edit-languages' => ['view-languages'],

    //profile setting
    'edit-his-profile-data' => [],
    'edit-his-password' => [],

    // translation files

    'translate-{lang}' => [],
    // countries
    'view-countries' => [],
    'edit-countries-{lang}' => ['view-countries'],
    'create-countries' => ['view-countries'],
    'delete-countries' => ['view-countries'],
    // stores
    'view-stores' => ['view-countries'],
    'create-stores' => ['view-stores'],
    'edit-stores-{lang}' => ['view-stores'],
    'delete-stores' => ['view-stores'],

    // coupons
    'view-coupons' => ['view-stores'],
    'edit-coupons-{lang}' => ['view-coupons'],
    'create-coupons' => ['view-coupons'],
    'delete-coupons' => ['view-coupons'],
    // main page
    'view-main-page' => [],
    'edit-main-page' => ['view-main-page'],

];
