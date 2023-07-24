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

$app->shortcuts
    ->add('modalWindows', function () {
        return new IvoPetkov\BearFrameworkAddons\ModalWindows();
    });

$app->serverRequests
    ->add('-modal-window-open', function ($data) {
        $result = [];
        if (isset($data['i'], $data['d'])) {
            $name = $data['i'];
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
            if (isset($data['g']) && (int)$data['g']) { // include globa data
                $css = '.ipmdlwndwsc{position:fixed;z-index:10020000;top:0;left:0;width:100%;height:100%;}';
                $css .= '.ipmdlwndwsc>div{position:fixed;top:0;left:0;width:100%;height:100%;box-sizing:border-box;padding:15px;overflow:auto;-webkit-transition:transform 300ms;transition:transform 300ms;transform:translate(0,100vh);}';
                $css .= '.ipmdlwndwsc>div>div{display:flex;justify-content:center;align-items:center;min-height:100%;}';
                $css .= '.ipmdlwndwsc>div.ipmdlwndwv{transform:translate(0,0);}';
                $css .= '.ipmdlwndwsc>div.ipmdlwndwvh{transform:translate(-100vw,0);}';
                $css .= '.ipmdlwndwsc>div.ipmdlwndwh{transform:translate(0,100vh);}';
                $css .= '.ipmdlwndwsc>div>div>div{min-height:200px;min-width:200px;position:relative;}';
                $css .= '.ipmdlwndwsc>div[data-mw-disabled]>div>div:before{content:"";z-index:10030000;display:block;position:absolute;width:100%;height:100%;}';

                $fieldBorderColor = '#ccc';
                $fieldBorderColorHover = '#bbb';
                $fieldBorderColorActive = '#aaa';
                $fieldsSpacing = '21px';
                $fontFamily = 'Arial,Helvetica,sans-serif';
                $fontSize = '15px';
                $hintFontSize = '13px';
                $fieldBackground = '#f5f5f5';
                $fieldBorder = '1px solid ' . $fieldBorderColor;
                $fieldBorderHover = '1px solid ' . $fieldBorderColorHover;
                $fieldBorderActive = '1px solid ' . $fieldBorderColorActive;
                $fieldBorderRadius = '4px';
                $textLineHeight = '26px';

                $closeButtonIcon = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="#999"><path d="M11.47 10l7.08-7.08c.4-.4.4-1.06 0-1.47-.4-.4-1.06-.4-1.47 0L10 8.53 2.92 1.45c-.4-.4-1.07-.4-1.47 0-.4.4-.4 1.06 0 1.47L8.53 10l-7.08 7.08c-.4.4-.4 1.07 0 1.47.2.2.47.3.74.3.23 0 .5-.1.7-.3l7.1-7.08 7.07 7.08c.2.2.47.3.73.3.3 0 .56-.1.76-.3.4-.4.4-1.06 0-1.47L11.46 10z"/></svg>';
                $radioButtonIcon = '<svg xmlns="http://www.w3.org/2000/svg" width="90.708664" height="90.708664" viewBox="0 0 24 24" fill="#111"><circle cx="12" cy="12" r="4.276312"/></svg>';
                $checkboxButtonIcon = '<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" stroke="#111" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" fill="none"><path d="M4 13l5 5L20 7"/></svg>';

                $css .= '.ipmdlwndwsc>div>div>div{background-color:#fff;border-radius:6px;'
                    . '--modal-window-content-spacing:' . $fieldsSpacing . ';'
                    . '--modal-window-content-spacing-half:calc(var(--modal-window-content-spacing)/2);'
                    . '--modal-window-content-field-border:' . $fieldBorder . ';'
                    . '--modal-window-content-field-border-radius:' . $fieldBorderRadius . ';'
                    . '--modal-window-content-field-border-hover:' . $fieldBorderHover . ';'
                    . '--modal-window-content-field-border-active:' . $fieldBorderActive . ';'
                    . '--modal-window-content-separator-color:' . $fieldBorderColor . ';'
                    . '}'; // container
                $css .= '.ipmdlwndwsc>div>div>div>div:nth-child(1){width:42px;height:42px;position:absolute;top:0;right:0;border-top-right-radius:6px;border-bottom-left-radius:4px;cursor:pointer;background-image:url(data:image/svg+xml;base64,' . base64_encode($closeButtonIcon) . ');background-repeat:no-repeat;background-position:center;background-size:16px;}'; // close button
                $css .= '.ipmdlwndwsc>div>div>div>div:nth-child(1):hover{background-color:#eee;}';
                $css .= '.ipmdlwndwsc>div>div>div>div:nth-child(1):active{background-color:#ddd;}';
                $css .= '.ipmdlwndwsc>div>div>div>div:nth-child(1):focus{background-color:#ddd;}';
                $css .= '.ipmdlwndwsc>div>div>div>div:nth-child(2){min-height:42px;font-family:' . $fontFamily . ';font-size:14px;line-height:14px;box-sizing:border-box;padding:20px ' . $fieldsSpacing . ' 0 ' . $fieldsSpacing . ';cursor:default;}'; // header
                $css .= '.ipmdlwndwsc>div>div>div>div:nth-child(3){padding:' . $fieldsSpacing . ';font-family:' . $fontFamily . ';font-size:' . $fontSize . ';line-height:' . $textLineHeight . ';}'; // content
                $css .= '.ipmdlwndwsc>div>div>div>div:nth-child(3) .modal-window-content-separator{border-bottom:1px solid var(--modal-window-content-separator-color);margin-top:var(--modal-window-content-spacing);margin-bottom:var(--modal-window-content-spacing);}';

                $css .= '.ipmdlwndwsc [data-form-element-type="textbox"] [data-form-element-component="input"],
                .ipmdlwndwsc [data-form-element-type="password"] [data-form-element-component="input"],
                .ipmdlwndwsc [data-form-element-type="textarea"] [data-form-element-component="textarea"],
                .ipmdlwndwsc [data-form-element-type="select"] [data-form-element-component="select"]{
                    width:100%;
                    font-size:' . $fontSize . ';
                    padding:0 17px;
                    line-height:48px;
                    font-family:' . $fontFamily . ';
                    background-color:' . $fieldBackground . ';
                    border:' . $fieldBorder . ';
                    border-radius:' . $fieldBorderRadius . ';
                    color:#000;
                    box-sizing:border-box;
                    display:block;
                    height:48px;
                }
                .ipmdlwndwsc [data-form-element-type="textarea"] [data-form-element-component="textarea"]{
                    padding:12px 17px;
                    line-height:24px;
                    height:100px;
                }
                .ipmdlwndwsc [data-form-element-type="select"] [data-form-element-component="select"]{
                    appearance:none;
                    -webkit-appearance:none;
                }
                .ipmdlwndwsc [data-form-element-type="image"] [data-form-element-component="button"]{
                    width:100%;
                    aspect-ratio:1/1;
                    box-shadow:0 0 0 1px rgba(0,0,0,0.2) inset;
                    border-radius:' . $fieldBorderRadius . ';
                    background-color:' . $fieldBackground . ';
                }
                .ipmdlwndwsc [data-form-element-type="image"] [data-form-element-component="button"]:hover{
                    box-shadow:0 0 0 1px rgba(0,0,0,0.25) inset;
                }
                .ipmdlwndwsc [data-form-element-type="image"] [data-form-element-component="button"]:active,
                .ipmdlwndwsc [data-form-element-type="image"] [data-form-element-component="button"]:focus{
                    box-shadow:0 0 0 1px rgba(0,0,0,0.3) inset;
                }
                .ipmdlwndwsc [data-form-element-type="image"] [data-form-element-component="text"]{
                    font-size:' . $fontSize . ';
                    padding:0 17px;
                    line-height:48px;
                    font-family:' . $fontFamily . ';
                    width:100%;
                    height:100%;
                    display:flex;
                    align-items:center;
                    justify-content:center;
                }
                .ipmdlwndwsc [data-form-element-type="image"] [data-form-element-component="clear-button"]{
                    width:32px;
                    height:32px;
                    right:5px;
                    top:5px;
                    background-image:url(data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" stroke="#fff" stroke-width="2.5" stroke-linecap="square" fill="none" color="#333"><path d="M21.039 2.961H2.96m11.621-1.29H9.418M4.253 8.125V21.04c0 .86.43 1.29 1.29 1.29h12.913c.861 0 1.291-.43 1.291-1.29V8.126" stroke-width="3.228"/></svg>') . ');
                    border-radius:calc(' . $fieldBorderRadius . ' - 1px);
                    background-size:13px;
                    background-repeat:no-repeat;
                    background-position:center center;
                    background-color:rgba(0,0,0,0.7);
                }
                .ipmdlwndwsc [data-form-element-type="image"] [data-form-element-component="clear-button"]:hover{
                    background-color:rgba(0,0,0,0.75);
                }
                .ipmdlwndwsc [data-form-element-type="image"] [data-form-element-component="clear-button"]:active{
                    background-color:rgba(0,0,0,0.8);
                }
                .ipmdlwndwsc [data-form-element-type="textbox"] [data-form-element-component="input"]:hover,
                .ipmdlwndwsc [data-form-element-type="password"] [data-form-element-component="input"]:hover,
                .ipmdlwndwsc [data-form-element-type="textarea"] [data-form-element-component="textarea"]:hover,
                .ipmdlwndwsc [data-form-element-type="select"] [data-form-element-component="select"]:hover{
                    border:' . $fieldBorderHover . ';
                }
                .ipmdlwndwsc [data-form-element-type="textbox"] [data-form-element-component="input"]:active,
                .ipmdlwndwsc [data-form-element-type="textbox"] [data-form-element-component="input"]:focus,
                .ipmdlwndwsc [data-form-element-type="password"] [data-form-element-component="input"]:active,
                .ipmdlwndwsc [data-form-element-type="password"] [data-form-element-component="input"]:focus,
                .ipmdlwndwsc [data-form-element-type="textarea"] [data-form-element-component="textarea"]:active,
                .ipmdlwndwsc [data-form-element-type="textarea"] [data-form-element-component="textarea"]:focus,
                .ipmdlwndwsc [data-form-element-type="select"] [data-form-element-component="select"]:active,
                .ipmdlwndwsc [data-form-element-type="select"] [data-form-element-component="select"]:focus{
                    border:' . $fieldBorderActive . ';
                }
                .ipmdlwndwsc [data-form-element-type] [data-form-element-component="label"]{
                    font-family:' . $fontFamily . ';
                    font-size:' . $fontSize . ';
                    color:#000;
                    padding-bottom:4px;
                    cursor:default;
                    display:block;
                    margin-top:-2px;
                }
                .ipmdlwndwsc [data-form-element-type] [data-form-element-component="hint"]{
                    font-family:' . $fontFamily . ';
                    font-size:' . $fontSize . ';
                    color:#555;
                    padding-bottom:4px;
                    cursor:default;
                    display:block;
                    margin-top:-2px;
                }
                .ipmdlwndwsc [data-form-element-type] [data-form-element-component="hint-after"]{
                    font-family:' . $fontFamily . ';
                    font-size:' . $hintFontSize . ';
                    color:#555;
                    padding-top:2px;
                    cursor:default;
                    display:block;
                }
                .ipmdlwndwsc [data-form-element-type="submit-button"] [data-form-element-component="button"],
                .ipmdlwndwsc [data-form-element-type="button"] [data-form-element-component="button"],
                .ipmdlwndwsc>div>div>div>div:nth-child(3) .modal-window-content-button{
                    box-sizing:border-box;
                    font-family:' . $fontFamily . ';
                    background-color:' . $fieldBackground . ';
                    border:' . $fieldBorder . ';
                    font-size:' . $fontSize . ';
                    border-radius:' . $fieldBorderRadius . ';
                    padding:0 17px;
                    line-height:48px;
                    color:#000;
                    text-align:center;
                    height:48px;
                    position:relative;
                    text-decoration:none;
                }
                .ipmdlwndwsc [data-form-element-type="submit-button"] [data-form-element-component="button"],
                .ipmdlwndwsc [data-form-element-type="button"] [data-form-element-component="button"]{
                    display:block;
                }
                .ipmdlwndwsc>div>div>div>div:nth-child(3) .modal-window-content-button{
                    display:inline-block;
                }
                .ipmdlwndwsc [data-form-element-type="submit-button"] [data-form-element-component="button"]:before{
                    content:"";display:block;
                    animation:ipmdlwndwsc-fsb 1.5s infinite linear;position:absolute;width:24px;height:24px;box-sizing:border-box;right:11px;top:11px;border-radius:50%;border:3px solid rgba(0,0,0,0.2);cursor:default;user-select:none;-moz-user-select:none;-khtml-user-select:none;-webkit-user-select:none;-o-user-select:none;opacity:0;-webkit-transition:opacity 300ms;transition:opacity 300ms;border-right:3px solid rgba(0,0,0,0.8);
                }
                .ipmdlwndwsc>div[data-mw-disabled] [data-form-element-type="submit-button"] [data-form-element-component="button"]:before{
                    opacity:1;
                }
                @keyframes ipmdlwndwsc-fsb{from{transform:rotate(0deg);}to{transform:rotate(360deg);}}
                .ipmdlwndwsc [data-form-element-type="submit-button"] [data-form-element-component="button"][disabled],
                .ipmdlwndwsc [data-form-element-type="button"] [data-form-element-component="button"][disabled]{
                    color:#999;
                }
                .ipmdlwndwsc [data-form-element-type="submit-button"] [data-form-element-component="button"]:not([disabled]):hover,
                .ipmdlwndwsc [data-form-element-type="button"] [data-form-element-component="button"]:not([disabled]):hover,
                .ipmdlwndwsc>div>div>div>div:nth-child(3) .modal-window-content-button:hover{
                    border:' . $fieldBorderHover . ';
                }
                .ipmdlwndwsc [data-form-element-type="submit-button"] [data-form-element-component="button"]:not([disabled]):focus,
                .ipmdlwndwsc [data-form-element-type="button"] [data-form-element-component="button"]:not([disabled]):focus,
                .ipmdlwndwsc>div>div>div>div:nth-child(3) .modal-window-content-button:focus{
                    border:' . $fieldBorderActive . ';
                }
                .ipmdlwndwsc [data-form-element-type="radio"] > label,
                .ipmdlwndwsc [data-form-element-type="checkbox"] > label{
                    display:flex;
                    flex-direction:row;
                    text-align:left;
                    line-height:42px;
                    box-sizing:border-box;
                    font-family:' . $fontFamily . ';
                    font-size:' . $fontSize . ';
                }
                .ipmdlwndwsc [data-form-element-type="radio"] [data-form-element-component="label"],
                .ipmdlwndwsc [data-form-element-type="checkbox"] [data-form-element-component="label"]{
                    padding-top:9px;
                    padding-bottom:7px;
                    padding-left:18px;
                    margin-top:0;
                    line-height:' . $textLineHeight . ';
                    display:inline-block;
                    align-self:flex-start;
                }
                .ipmdlwndwsc [data-form-element-type="radio"] [data-form-element-component="label"] a,
                .ipmdlwndwsc [data-form-element-type="checkbox"] [data-form-element-component="label"] a{
                    font-family:' . $fontFamily . ';
                    font-size:' . $fontSize . ';
                    color:#000;
                    line-height:' . $textLineHeight . ';
                    text-decoration:underline;
                }
                .ipmdlwndwsc [data-form-element-type="radio"] [data-form-element-component="label"] a,
                .ipmdlwndwsc [data-form-element-type="checkbox"] [data-form-element-component="label"] a:focus{
                    background-color:' . $fieldBackground . ';
                }
                .ipmdlwndwsc [data-form-element-type="radio"] [data-form-element-component="input"],
                .ipmdlwndwsc [data-form-element-type="checkbox"] [data-form-element-component="input"]{
                    align-self:flex-start;
                    margin:0;
                    appearance:none;
                    -webkit-appearance:none;
                    flex:0 0 auto;
                    width:42px;
                    height:42px;
                    background:' . $fieldBackground . ';
                    border:' . $fieldBorder . ';
                    border-radius:' . $fieldBorderRadius . ';
                    cursor:pointer;
                    box-sizing:border-box;
                }
                .ipmdlwndwsc [data-form-element-type="radio"] [data-form-element-component="input"]{
                    border-radius:50%;
                }
                .ipmdlwndwsc [data-form-element-type="radio"] [data-form-element-component="input"]:hover,
                .ipmdlwndwsc [data-form-element-type="checkbox"] [data-form-element-component="input"]:hover{
                    border:' . $fieldBorderHover . ';
                }
                .ipmdlwndwsc [data-form-element-type="radio"] [data-form-element-component="input"]:active,
                .ipmdlwndwsc [data-form-element-type="radio"] [data-form-element-component="input"]:focus,
                .ipmdlwndwsc [data-form-element-type="checkbox"] [data-form-element-component="input"]:active,
                .ipmdlwndwsc [data-form-element-type="checkbox"] [data-form-element-component="input"]:focus{
                    border:' . $fieldBorderActive . ';
                }
                .ipmdlwndwsc [data-form-element-type="radio"] [data-form-element-component="input"]:checked{
                    background-image:url(data:image/svg+xml;base64,' . base64_encode($radioButtonIcon) . ');
                    background-size:30px;
                    background-repeat:no-repeat;
                    background-position:center;
                }
                .ipmdlwndwsc [data-form-element-type="checkbox"] [data-form-element-component="input"]:checked{
                    background-image:url(data:image/svg+xml;base64,' . base64_encode($checkboxButtonIcon) . ');
                    background-size:20px;
                    background-repeat:no-repeat;
                    background-position:11px 10px;
                }
                .ipmdlwndwsc [data-form-element-type]+[data-form-element-type]{
                    margin-top:' . $fieldsSpacing . ';
                }
                .ipmdlwndwsc [data-form-element-type="radio"]+[data-form-element-type="radio"]{
                    margin-top:10px;
                }
                .ipmdlwndwsc [data-form-component="tooltip"]{
                    border-radius:4px;
                    --form-tooltip-background-color:#c30000;
                    font-size:14px;
                    line-height:24px;
                    font-family:Arial,Helvetica,sans-serif;
                    color:#fff;
                    font-weight:bold;
                    padding:15px 18px;
                }';
                $result['s'] = '<head><style>' . $css . '</style></head>';
            }
        }
        return json_encode($result);
    });

$app->clientPackages
    ->add('modalWindows', function (IvoPetkov\BearFrameworkAddons\ClientPackage $package) use ($context) {
        $package->addJSCode(include $context->dir . '/assets/modalWindows.min.js.php');
        //$package->addJSCode(file_get_contents($context->dir . '/dev/modalWindows.js'));
        $package->embedPackage('lightbox');
        $package->get = 'return ivoPetkov.bearFrameworkAddons.modalWindows;';
    });
