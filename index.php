<?php

/*
 * Modal windows addon for Bear Framework
 * https://github.com/ivopetkov/modal-windows-bearframework-addon
 * Copyright (c) Ivo Petkov
 * Free to use under the MIT license.
 */

use \BearFramework\App;
use IvoPetkov\BearFrameworkAddons\ModalWindows\Internal\Utilities;

$app = App::get();
$context = $app->contexts->get(__DIR__);

$context->classes
    ->add('IvoPetkov\BearFrameworkAddons\ModalWindows', 'classes/ModalWindows.php')
    ->add('IvoPetkov\BearFrameworkAddons\ModalWindows\Internal\Utilities', 'classes/ModalWindows/Internal/Utilities.php');

$app->localization
    ->addDictionary('en', function () use ($context) {
        return include $context->dir . '/locales/en.php';
    })
    ->addDictionary('bg', function () use ($context) {
        return include $context->dir . '/locales/bg.php';
    });

$app->shortcuts
    ->add('modalWindows', function () {
        return new IvoPetkov\BearFrameworkAddons\ModalWindows();
    });

$app->serverRequests
    ->add('-modal-window-open', function ($data) {
        $result = [];
        if (isset($data['i'], $data['d'])) {
            $name = $data['i'];
            if ($name !== '') {
                $callData = json_decode($data['d'], true);
                if (!is_array($callData)) {
                    $callData = [];
                }
                if (isset(Utilities::$modalWindows[$name])) {
                    $result['c'] = call_user_func(Utilities::$modalWindows[$name][0], $callData);
                    if (!is_array($result['c'])) {
                        $result['c'] = [];
                    }
                }
            }
            if (isset($data['g']) && (int)$data['g']) { // include global data
                $result['g'] = Utilities::getGlobalHTML();
            }
        }
        return json_encode($result);
    });

$app->clientPackages
    ->add('modalWindows', function (IvoPetkov\BearFrameworkAddons\ClientPackage $package) use ($context) {
        $package->addJSCode(include $context->dir . '/assets/modalWindows.min.js.php');
        //$package->addJSCode(file_get_contents($context->dir . '/dev/modalWindows.js'));
        $package->embedPackage('lightbox');

        $data = [
            __('ivopetkov.modalWindows.close'),
            __('ivopetkov.modalWindows.error'),
            __('ivopetkov.modalWindows.offline')
        ];
        $package->get = 'return ivoPetkov.bearFrameworkAddons.modalWindows.initialize(' . json_encode($data) . ');';
    });
