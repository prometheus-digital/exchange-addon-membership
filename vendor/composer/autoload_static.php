<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit5aa020621c0c97d06a77e68f5218e50c
{
    public static $prefixLengthsPsr4 = array (
        'i' => 
        array (
            'iThemes\\Exchange\\Membership\\REST\\' => 33,
        ),
        'I' => 
        array (
            'IronBound\\WP_Notifications\\' => 27,
            'ITEGMS\\' => 7,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'iThemes\\Exchange\\Membership\\REST\\' => 
        array (
            0 => __DIR__ . '/../..' . '/lib/REST',
        ),
        'IronBound\\WP_Notifications\\' => 
        array (
            0 => __DIR__ . '/..' . '/ironbound/wp-notifications/src',
        ),
        'ITEGMS\\' => 
        array (
            0 => __DIR__ . '/../..' . '/umbrella-memberships/src',
        ),
    );

    public static $classMap = array (
        'ITEGMS\\Container' => __DIR__ . '/../..' . '/umbrella-memberships/src/Container.php',
        'ITEGMS\\DB\\Purchases' => __DIR__ . '/../..' . '/umbrella-memberships/src/DB/Purchases.php',
        'ITEGMS\\DB\\Relationships' => __DIR__ . '/../..' . '/umbrella-memberships/src/DB/Relationships.php',
        'ITEGMS\\Emails' => __DIR__ . '/../..' . '/umbrella-memberships/src/Emails.php',
        'ITEGMS\\Hooks' => __DIR__ . '/../..' . '/umbrella-memberships/src/Hooks.php',
        'ITEGMS\\Product_Feature\\Umbrella_Membership' => __DIR__ . '/../..' . '/umbrella-memberships/src/Product_Feature/Umbrella_Membership.php',
        'ITEGMS\\Purchase\\Purchase' => __DIR__ . '/../..' . '/umbrella-memberships/src/Purchase/Purchase.php',
        'ITEGMS\\Purchase\\Purchase_Query' => __DIR__ . '/../..' . '/umbrella-memberships/src/Purchase/Purchase_Query.php',
        'ITEGMS\\Relationship\\Relationship' => __DIR__ . '/../..' . '/umbrella-memberships/src/Relationship/Relationship.php',
        'ITEGMS\\Relationship\\Relationship_Query' => __DIR__ . '/../..' . '/umbrella-memberships/src/Relationship/Relationship_Query.php',
        'ITEGMS\\Settings' => __DIR__ . '/../..' . '/umbrella-memberships/src/Settings.php',
        'IronBound\\WP_Notifications\\Contract' => __DIR__ . '/..' . '/ironbound/wp-notifications/src/Contract.php',
        'IronBound\\WP_Notifications\\Notification' => __DIR__ . '/..' . '/ironbound/wp-notifications/src/Notification.php',
        'IronBound\\WP_Notifications\\Queue\\Manager' => __DIR__ . '/..' . '/ironbound/wp-notifications/src/Queue/Manager.php',
        'IronBound\\WP_Notifications\\Queue\\Mandrill' => __DIR__ . '/..' . '/ironbound/wp-notifications/src/Queue/Mandrill.php',
        'IronBound\\WP_Notifications\\Queue\\Queue' => __DIR__ . '/..' . '/ironbound/wp-notifications/src/Queue/Queue.php',
        'IronBound\\WP_Notifications\\Queue\\Storage\\Contract' => __DIR__ . '/..' . '/ironbound/wp-notifications/src/Queue/Storage/Contract.php',
        'IronBound\\WP_Notifications\\Queue\\Storage\\Options' => __DIR__ . '/..' . '/ironbound/wp-notifications/src/Queue/Storage/Options.php',
        'IronBound\\WP_Notifications\\Queue\\WP_Cron' => __DIR__ . '/..' . '/ironbound/wp-notifications/src/Queue/WP_Cron.php',
        'IronBound\\WP_Notifications\\Strategy\\EDD' => __DIR__ . '/..' . '/ironbound/wp-notifications/src/Strategy/EDD.php',
        'IronBound\\WP_Notifications\\Strategy\\Mandrill' => __DIR__ . '/..' . '/ironbound/wp-notifications/src/Strategy/Mandrill.php',
        'IronBound\\WP_Notifications\\Strategy\\Null' => __DIR__ . '/..' . '/ironbound/wp-notifications/src/Strategy/Null.php',
        'IronBound\\WP_Notifications\\Strategy\\Strategy' => __DIR__ . '/..' . '/ironbound/wp-notifications/src/Strategy/Strategy.php',
        'IronBound\\WP_Notifications\\Strategy\\WP_Mail' => __DIR__ . '/..' . '/ironbound/wp-notifications/src/Strategy/WP_Mail.php',
        'IronBound\\WP_Notifications\\Strategy\\iThemes_Exchange' => __DIR__ . '/..' . '/ironbound/wp-notifications/src/Strategy/iThemes_Exchange.php',
        'IronBound\\WP_Notifications\\Template\\Editor' => __DIR__ . '/..' . '/ironbound/wp-notifications/src/Template/Editor.php',
        'IronBound\\WP_Notifications\\Template\\Factory' => __DIR__ . '/..' . '/ironbound/wp-notifications/src/Template/Factory.php',
        'IronBound\\WP_Notifications\\Template\\Listener' => __DIR__ . '/..' . '/ironbound/wp-notifications/src/Template/Listener.php',
        'IronBound\\WP_Notifications\\Template\\Manager' => __DIR__ . '/..' . '/ironbound/wp-notifications/src/Template/Manager.php',
        'IronBound\\WP_Notifications\\Template\\Null_Listener' => __DIR__ . '/..' . '/ironbound/wp-notifications/src/Template/Null_Listener.php',
        'iThemes\\Exchange\\Membership\\REST\\v1\\Memberships\\Downgrades' => __DIR__ . '/../..' . '/lib/REST/v1/Memberships/Downgrades.php',
        'iThemes\\Exchange\\Membership\\REST\\v1\\Memberships\\Membership' => __DIR__ . '/../..' . '/lib/REST/v1/Memberships/Membership.php',
        'iThemes\\Exchange\\Membership\\REST\\v1\\Memberships\\Serializer' => __DIR__ . '/../..' . '/lib/REST/v1/Memberships/Serializer.php',
        'iThemes\\Exchange\\Membership\\REST\\v1\\Memberships\\Upgrades' => __DIR__ . '/../..' . '/lib/REST/v1/Memberships/Upgrades.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit5aa020621c0c97d06a77e68f5218e50c::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit5aa020621c0c97d06a77e68f5218e50c::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit5aa020621c0c97d06a77e68f5218e50c::$classMap;

        }, null, ClassLoader::class);
    }
}
