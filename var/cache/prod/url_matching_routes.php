<?php

/**
 * This file has been auto-generated
 * by the Symfony Routing Component.
 */

return [
    false, // $matchHost
    [ // $staticRoutes
    ],
    [ // $regexpList
        0 => '{^(?'
                .'|/api(?'
                    .'|/(?'
                        .'|docs(?:\\.([^/]++))?(*:37)'
                        .'|\\.well\\-known/genid/([^/]++)(*:72)'
                        .'|validation_errors/([^/]++)(*:105)'
                    .')'
                    .'|(?:/(index)(?:\\.([^/]++))?)?(*:142)'
                    .'|/(?'
                        .'|cont(?'
                            .'|exts/([^.]+)(?:\\.(jsonld))?(*:188)'
                            .'|acts(?'
                                .'|/([^/\\.]++)(?:\\.([^/]++))?(*:229)'
                                .'|(?:\\.([^/]++))?(?'
                                    .'|(*:255)'
                                .')'
                                .'|/([^/\\.]++)(?:\\.([^/]++))?(?'
                                    .'|(*:293)'
                                .')'
                            .')'
                        .')'
                        .'|errors/(\\d+)(?:\\.([^/]++))?(*:331)'
                        .'|validation_errors/([^/]++)(?'
                            .'|(*:368)'
                        .')'
                        .'|users(?'
                            .'|(?:\\.([^/]++))?(*:400)'
                            .'|/([^/\\.]++)(?:\\.([^/]++))?(*:434)'
                        .')'
                    .')'
                .')'
            .')/?$}sDu',
    ],
    [ // $dynamicRoutes
        37 => [[['_route' => 'api_doc', '_controller' => 'api_platform.action.documentation', '_format' => '', '_api_respond' => 'true'], ['_format'], ['GET' => 0, 'HEAD' => 1], null, false, true, null]],
        72 => [[['_route' => 'api_genid', '_controller' => 'api_platform.action.not_exposed', '_api_respond' => 'true'], ['id'], ['GET' => 0, 'HEAD' => 1], null, false, true, null]],
        105 => [[['_route' => 'api_validation_errors', '_controller' => 'api_platform.action.not_exposed'], ['id'], ['GET' => 0, 'HEAD' => 1], null, false, true, null]],
        142 => [[['_route' => 'api_entrypoint', '_controller' => 'api_platform.action.entrypoint', '_format' => '', '_api_respond' => 'true', 'index' => 'index'], ['index', '_format'], ['GET' => 0, 'HEAD' => 1], null, false, true, null]],
        188 => [[['_route' => 'api_jsonld_context', '_controller' => 'api_platform.jsonld.action.context', '_format' => 'jsonld', '_api_respond' => 'true'], ['shortName', '_format'], ['GET' => 0, 'HEAD' => 1], null, false, true, null]],
        229 => [[['_route' => '_api_/contacts/{id}{._format}_get', '_controller' => 'api_platform.symfony.main_controller', '_stateless' => true, '_api_resource_class' => 'App\\Entity\\Contact', '_api_operation_name' => '_api_/contacts/{id}{._format}_get', '_format' => null], ['id', '_format'], ['GET' => 0], null, false, true, null]],
        255 => [
            [['_route' => '_api_/contacts{._format}_get_collection', '_controller' => 'api_platform.symfony.main_controller', '_stateless' => true, '_api_resource_class' => 'App\\Entity\\Contact', '_api_operation_name' => '_api_/contacts{._format}_get_collection', '_format' => null], ['_format'], ['GET' => 0], null, false, true, null],
            [['_route' => '_api_/contacts{._format}_post', '_controller' => 'api_platform.symfony.main_controller', '_stateless' => true, '_api_resource_class' => 'App\\Entity\\Contact', '_api_operation_name' => '_api_/contacts{._format}_post', '_format' => null], ['_format'], ['POST' => 0], null, false, true, null],
        ],
        293 => [
            [['_route' => '_api_/contacts/{id}{._format}_put', '_controller' => 'api_platform.symfony.main_controller', '_stateless' => true, '_api_resource_class' => 'App\\Entity\\Contact', '_api_operation_name' => '_api_/contacts/{id}{._format}_put', '_format' => null], ['id', '_format'], ['PUT' => 0], null, false, true, null],
            [['_route' => '_api_/contacts/{id}{._format}_patch', '_controller' => 'api_platform.symfony.main_controller', '_stateless' => true, '_api_resource_class' => 'App\\Entity\\Contact', '_api_operation_name' => '_api_/contacts/{id}{._format}_patch', '_format' => null], ['id', '_format'], ['PATCH' => 0], null, false, true, null],
            [['_route' => '_api_/contacts/{id}{._format}_delete', '_controller' => 'api_platform.symfony.main_controller', '_stateless' => true, '_api_resource_class' => 'App\\Entity\\Contact', '_api_operation_name' => '_api_/contacts/{id}{._format}_delete', '_format' => null], ['id', '_format'], ['DELETE' => 0], null, false, true, null],
        ],
        331 => [[['_route' => '_api_errors', '_controller' => 'api_platform.symfony.main_controller', '_stateless' => null, '_api_resource_class' => 'ApiPlatform\\State\\ApiResource\\Error', '_api_operation_name' => '_api_errors', '_format' => null], ['status', '_format'], ['GET' => 0], null, false, true, null]],
        368 => [
            [['_route' => '_api_validation_errors_problem', '_controller' => 'api_platform.symfony.main_controller', '_stateless' => null, '_api_resource_class' => 'ApiPlatform\\Validator\\Exception\\ValidationException', '_api_operation_name' => '_api_validation_errors_problem', '_format' => null], ['id'], ['GET' => 0], null, false, true, null],
            [['_route' => '_api_validation_errors_hydra', '_controller' => 'api_platform.symfony.main_controller', '_stateless' => null, '_api_resource_class' => 'ApiPlatform\\Validator\\Exception\\ValidationException', '_api_operation_name' => '_api_validation_errors_hydra', '_format' => null], ['id'], ['GET' => 0], null, false, true, null],
            [['_route' => '_api_validation_errors_jsonapi', '_controller' => 'api_platform.symfony.main_controller', '_stateless' => null, '_api_resource_class' => 'ApiPlatform\\Validator\\Exception\\ValidationException', '_api_operation_name' => '_api_validation_errors_jsonapi', '_format' => null], ['id'], ['GET' => 0], null, false, true, null],
            [['_route' => '_api_validation_errors_xml', '_controller' => 'api_platform.symfony.main_controller', '_stateless' => null, '_api_resource_class' => 'ApiPlatform\\Validator\\Exception\\ValidationException', '_api_operation_name' => '_api_validation_errors_xml', '_format' => null], ['id'], ['GET' => 0], null, false, true, null],
        ],
        400 => [[['_route' => '_api_/users{._format}_post', '_controller' => 'api_platform.symfony.main_controller', '_stateless' => true, '_api_resource_class' => 'App\\Entity\\User', '_api_operation_name' => '_api_/users{._format}_post', '_format' => null], ['_format'], ['POST' => 0], null, false, true, null]],
        434 => [
            [['_route' => '_api_/users/{id}{._format}_get', '_controller' => 'api_platform.symfony.main_controller', '_stateless' => true, '_api_resource_class' => 'App\\Entity\\User', '_api_operation_name' => '_api_/users/{id}{._format}_get', '_format' => null], ['id', '_format'], ['GET' => 0], null, false, true, null],
            [null, null, null, null, false, false, 0],
        ],
    ],
    null, // $checkCondition
];
