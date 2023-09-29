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
            if (isset($data['g']) && (int)$data['g']) { // include global data
                $css = '.ipmdlwndwsc{position:fixed;z-index:10020000;top:0;left:0;width:100%;height:100%;}';
                $css .= '.ipmdlwndwsc>div{position:fixed;top:0;left:0;width:100%;height:100%;box-sizing:border-box;padding:15px;overflow:auto;-webkit-transition:transform 300ms;transition:transform 300ms;transform:translate(0,100vh);}';
                $css .= '.ipmdlwndwsc>div>div{display:flex;justify-content:center;align-items:center;min-height:100%;}';
                $css .= '.ipmdlwndwsc>div.ipmdlwndwv{transform:translate(0,0);}';
                $css .= '.ipmdlwndwsc>div.ipmdlwndwvh{transform:translate(-100vw,0);}';
                $css .= '.ipmdlwndwsc>div.ipmdlwndwh{transform:translate(0,100vh);}';
                $css .= '.ipmdlwndwsc>div>div>div{min-height:200px;min-width:200px;position:relative;}';
                $css .= '.ipmdlwndwsc>div[data-mw-disabled]>div>div:before{content:"";z-index:10030000;display:block;position:absolute;width:100%;height:100%;}';

                $fontFamily = 'Arial,Helvetica,sans-serif';
                $fontSize = '15px';
                $hintFontSize = '13px';
                $fieldBackground = '#f5f5f5';
                $textLineHeight = '26px';

                $closeButtonIcon = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="#999"><path d="M11.47 10l7.08-7.08c.4-.4.4-1.06 0-1.47-.4-.4-1.06-.4-1.47 0L10 8.53 2.92 1.45c-.4-.4-1.07-.4-1.47 0-.4.4-.4 1.06 0 1.47L8.53 10l-7.08 7.08c-.4.4-.4 1.07 0 1.47.2.2.47.3.74.3.23 0 .5-.1.7-.3l7.1-7.08 7.07 7.08c.2.2.47.3.73.3.3 0 .56-.1.76-.3.4-.4.4-1.06 0-1.47L11.46 10z"/></svg>';
                $radioButtonIcon = '<svg xmlns="http://www.w3.org/2000/svg" width="90.708664" height="90.708664" viewBox="0 0 24 24" fill="#111"><circle cx="12" cy="12" r="4.276312"/></svg>';
                $checkboxButtonIcon = '<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" stroke="#111" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" fill="none"><path d="M4 13l5 5L20 7"/></svg>';

                $nextIcon = '<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" stroke="#333" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"><path d="M7.495048 2.990047L16.505003 12l-9.009955 9.009953" stroke-width="2.5"/></svg>';
                $previousIcon = '<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" stroke="#333" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="none"><path d="M16.504952 21.009953L7.494996 12l9.009956-9.009953" stroke-width="2.5"/></svg>';
                $clearIcon = '<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" stroke="#999" stroke-width="2.5" stroke-linecap="square" fill="none" color="#333"><path d="M21.039 2.961H2.96m11.621-1.29H9.418M4.253 8.125V21.04c0 .86.43 1.29 1.29 1.29h12.913c.861 0 1.291-.43 1.291-1.29V8.126" stroke-width="3.228"/></svg>';

                $css .= '.ipmdlwndwsc>div>div>div{background-color:#fff;border-radius:var(--modal-window-border-radius);'
                    . '--modal-window-border-radius:6px;'
                    . '--modal-window-content-spacing:21px;'
                    . '--modal-window-content-spacing-half:calc(var(--modal-window-content-spacing)/2);'
                    . '--modal-window-content-field-border:1px solid #ccc;'
                    . '--modal-window-content-field-border-hover:1px solid #bbb;'
                    . '--modal-window-content-field-border-active:1px solid #aaa;'
                    . '--modal-window-content-field-border-radius:4px;'
                    . '--modal-window-content-separator-color:#ccc;'
                    . '}'; // container
                $css .= '.ipmdlwndwsc>div>div>div [data-modal-window-component="header"]{display:flex;flex-direction:row;}';
                $css .= '.ipmdlwndwsc>div>div>div [data-modal-window-component="header-buttons"]>*{width:42px;height:42px;border-bottom-left-radius:calc(var(--modal-window-border-radius) / 2);border-bottom-right-radius:calc(var(--modal-window-border-radius) / 2);cursor:pointer;background-repeat:no-repeat;background-position:center;outline:none;}';
                $css .= '.ipmdlwndwsc>div>div>div [data-modal-window-component="header-buttons"]>*:hover{background-color:#eee;}';
                $css .= '.ipmdlwndwsc>div>div>div [data-modal-window-component="header-buttons"]>*:active{background-color:#ddd;}';
                $css .= '.ipmdlwndwsc>div>div>div [data-modal-window-component="header-buttons"]>*:focus{background-color:#ddd;}';
                $css .= '.ipmdlwndwsc>div>div>div [data-modal-window-component="header-button-close"]{border-top-right-radius:var(--modal-window-border-radius);border-bottom-right-radius:0;background-image:url(data:image/svg+xml;base64,' . base64_encode($closeButtonIcon) . ');background-size:16px;}';
                $css .= '.ipmdlwndwsc>div>div>div [data-modal-window-component="header-title"]{flex:1 1 auto;min-height:42px;font-family:' . $fontFamily . ';font-size:14px;line-height:' . $textLineHeight . ';box-sizing:border-box;padding:15px var(--modal-window-content-spacing) 0 var(--modal-window-content-spacing);cursor:default;}'; // header
                $css .= '.ipmdlwndwsc>div>div>div>[data-modal-window-component="content"]{padding:var(--modal-window-content-spacing);font-family:' . $fontFamily . ';font-size:' . $fontSize . ';line-height:' . $textLineHeight . ';}'; // content
                $css .= '.ipmdlwndwsc>div>div>div>[data-modal-window-component="content"] .modal-window-content-separator{border-bottom:1px solid var(--modal-window-content-separator-color);margin-top:var(--modal-window-content-spacing);margin-bottom:var(--modal-window-content-spacing);}';

                $css .= '.ipmdlwndwsc [data-form-element-type="textbox"] [data-form-element-component="input"],
                .ipmdlwndwsc [data-form-element-type="password"] [data-form-element-component="input"],
                .ipmdlwndwsc [data-form-element-type="textarea"] [data-form-element-component="textarea"],
                .ipmdlwndwsc [data-form-element-type="select"] [data-form-element-component="select"],
                .ipmdlwndwsc [data-form-element-type="datetime"] [data-form-element-component="button"]{
                    width:100%;
                    font-size:' . $fontSize . ';
                    padding:0 17px;
                    line-height:48px;
                    font-family:' . $fontFamily . ';
                    background-color:' . $fieldBackground . ';
                    border:var(--modal-window-content-field-border);
                    border-radius:var(--modal-window-content-field-border-radius);
                    color:#000;
                    box-sizing:border-box;
                    display:block;
                    height:48px;
                    outline:none;
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
                    border-radius:var(--modal-window-content-field-border-radius);
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
                    border-radius:calc(var(--modal-window-content-field-border-radius) - 1px);
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
                .ipmdlwndwsc [data-form-element-type="datetime"] [data-form-element-component="button"]{
                    text-overflow:ellipsis;white-space:nowrap;overflow:hidden;
                }
                .ipmdlwndwsc [data-form-element-type="textbox"] [data-form-element-component="input"]:hover,
                .ipmdlwndwsc [data-form-element-type="password"] [data-form-element-component="input"]:hover,
                .ipmdlwndwsc [data-form-element-type="textarea"] [data-form-element-component="textarea"]:hover,
                .ipmdlwndwsc [data-form-element-type="select"] [data-form-element-component="select"]:hover,
                .ipmdlwndwsc [data-form-element-type="datetime"] [data-form-element-component="button"]:hover{
                    border:var(--modal-window-content-field-border-hover);
                }
                .ipmdlwndwsc [data-form-element-type="textbox"] [data-form-element-component="input"]:active,
                .ipmdlwndwsc [data-form-element-type="textbox"] [data-form-element-component="input"]:focus,
                .ipmdlwndwsc [data-form-element-type="password"] [data-form-element-component="input"]:active,
                .ipmdlwndwsc [data-form-element-type="password"] [data-form-element-component="input"]:focus,
                .ipmdlwndwsc [data-form-element-type="textarea"] [data-form-element-component="textarea"]:active,
                .ipmdlwndwsc [data-form-element-type="textarea"] [data-form-element-component="textarea"]:focus,
                .ipmdlwndwsc [data-form-element-type="select"] [data-form-element-component="select"]:active,
                .ipmdlwndwsc [data-form-element-type="select"] [data-form-element-component="select"]:focus,
                .ipmdlwndwsc [data-form-element-type="datetime"] [data-form-element-component="button"]:active,
                .ipmdlwndwsc [data-form-element-type="datetime"] [data-form-element-component="button"]:focus,
                .ipmdlwndwsc [data-form-element-type="datetime"][data-form-element-data-opened] [data-form-element-component="button"]{
                    border:var(--modal-window-content-field-border-active);
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
                .ipmdlwndwsc>div>div>div>[data-modal-window-component="content"] .modal-window-content-button{
                    box-sizing:border-box;
                    font-family:' . $fontFamily . ';
                    background-color:' . $fieldBackground . ';
                    border:var(--modal-window-content-field-border);
                    font-size:' . $fontSize . ';
                    border-radius:var(--modal-window-content-field-border-radius);
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
                    outline:none;
                }
                .ipmdlwndwsc>div>div>div>[data-modal-window-component="content"] .modal-window-content-button{
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
                .ipmdlwndwsc>div>div>div>[data-modal-window-component="content"] .modal-window-content-button:hover{
                    border:var(--modal-window-content-field-border-hover);
                }
                .ipmdlwndwsc [data-form-element-type="submit-button"] [data-form-element-component="button"]:not([disabled]):focus,
                .ipmdlwndwsc [data-form-element-type="button"] [data-form-element-component="button"]:not([disabled]):focus,
                .ipmdlwndwsc>div>div>div>[data-modal-window-component="content"] .modal-window-content-button:focus{
                    border:var(--modal-window-content-field-border-active);
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
                    border:var(--modal-window-content-field-border);
                    border-radius:var(--modal-window-content-field-border-radius);
                    cursor:pointer;
                    box-sizing:border-box;
                }
                .ipmdlwndwsc [data-form-element-type="radio"] [data-form-element-component="input"]{
                    border-radius:50%;
                }
                .ipmdlwndwsc [data-form-element-type="radio"] [data-form-element-component="input"]:hover,
                .ipmdlwndwsc [data-form-element-type="checkbox"] [data-form-element-component="input"]:hover{
                    border:var(--modal-window-content-field-border-hover);
                }
                .ipmdlwndwsc [data-form-element-type="radio"] [data-form-element-component="input"]:active,
                .ipmdlwndwsc [data-form-element-type="radio"] [data-form-element-component="input"]:focus,
                .ipmdlwndwsc [data-form-element-type="checkbox"] [data-form-element-component="input"]:active,
                .ipmdlwndwsc [data-form-element-type="checkbox"] [data-form-element-component="input"]:focus{
                    border:var(--modal-window-content-field-border-active);
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
                    margin-top:var(--modal-window-content-spacing);
                }
                .ipmdlwndwsc [data-form-element-type="radio"]+[data-form-element-type="radio"]{
                    margin-top:10px;
                }
                .ipmdlwndwsc [data-form-element-type="datetime"] [data-form-element-component="picker-tooltip"]{
                    --tooltip-background-color:' . $fieldBackground . ';
                    --tooltip-border:var(--modal-window-content-field-border-active);
                    --tooltip-border-radius:var(--modal-window-content-field-border-radius);
                    min-width:260px;
                    max-width:550px;
                    width:100%;
                }
                .ipmdlwndwsc [data-form-element-type="datetime"] [data-form-element-component="picker"]{
                    padding:7px;
                    box-sizing:border-box;
                    user-select:none;
                }
                .ipmdlwndwsc [data-form-element-type="datetime"] [data-form-element-component="header"]{
                    justify-content:right;
                }
                .ipmdlwndwsc [data-form-element-type="datetime"] [data-form-element-component="month-button"],
                .ipmdlwndwsc [data-form-element-type="datetime"] [data-form-element-component="year-button"]{
                    height:30px;
                    line-height:30px;
                    font-family:' . $fontFamily . ';
                    font-size:' . $fontSize . ';
                    border-radius:var(--modal-window-content-field-border-radius);
                    padding:0 9px;
                }
                .ipmdlwndwsc [data-form-element-type="datetime"] [data-form-element-component="previous-button"],
                .ipmdlwndwsc [data-form-element-type="datetime"] [data-form-element-component="next-button"],
                .ipmdlwndwsc [data-form-element-type="datetime"] [data-form-element-component="clear-button"]{
                    width:30px;
                    background-size:13px;
                    background-repeat:no-repeat;
                    background-position:center center;
                    border-radius:var(--modal-window-content-field-border-radius);
                }
                .ipmdlwndwsc [data-form-element-type="datetime"] [data-form-element-component="next-button"]{
                    background-image:url(data:image/svg+xml;base64,' . base64_encode($nextIcon) . ');
                }
                .ipmdlwndwsc [data-form-element-type="datetime"] [data-form-element-component="previous-button"]{
                    background-image:url(data:image/svg+xml;base64,' . base64_encode($previousIcon) . ');
                    margin-left:5px;
                }
                .ipmdlwndwsc [data-form-element-type="datetime"] [data-form-element-component="clear-button"]{
                    background-image:url(data:image/svg+xml;base64,' . base64_encode($clearIcon) . ');
                    margin-right:auto;
                }
                .ipmdlwndwsc [data-form-element-type="datetime"] [data-form-element-component="month-button"]:hover,
                .ipmdlwndwsc [data-form-element-type="datetime"] [data-form-element-component="year-button"]:hover,
                .ipmdlwndwsc [data-form-element-type="datetime"] [data-form-element-component="previous-button"]:hover,
                .ipmdlwndwsc [data-form-element-type="datetime"] [data-form-element-component="next-button"]:hover,
                .ipmdlwndwsc [data-form-element-type="datetime"] [data-form-element-component="clear-button"]:hover{
                    background-color:rgba(0,0,0,0.1);
                }
                .ipmdlwndwsc [data-form-element-type="datetime"] [data-form-element-component="month-button"]:active,
                .ipmdlwndwsc [data-form-element-type="datetime"] [data-form-element-component="month-button"]:focus,
                .ipmdlwndwsc [data-form-element-type="datetime"] [data-form-element-component="month-button"][data-form-element-data-opened],
                .ipmdlwndwsc [data-form-element-type="datetime"] [data-form-element-component="year-button"]:active,
                .ipmdlwndwsc [data-form-element-type="datetime"] [data-form-element-component="year-button"]:focus,
                .ipmdlwndwsc [data-form-element-type="datetime"] [data-form-element-component="year-button"][data-form-element-data-opened],
                .ipmdlwndwsc [data-form-element-type="datetime"] [data-form-element-component="previous-button"]:active,
                .ipmdlwndwsc [data-form-element-type="datetime"] [data-form-element-component="previous-button"]:focus,
                .ipmdlwndwsc [data-form-element-type="datetime"] [data-form-element-component="next-button"]:active,
                .ipmdlwndwsc [data-form-element-type="datetime"] [data-form-element-component="next-button"]:focus,
                .ipmdlwndwsc [data-form-element-type="datetime"] [data-form-element-component="clear-button"]:active,
                .ipmdlwndwsc [data-form-element-type="datetime"] [data-form-element-component="clear-button"]:focus{
                    background-color:rgba(0,0,0,0.15);
                }
                .ipmdlwndwsc [data-form-element-type="datetime"] [data-form-element-component="day"]{
                    text-align:center;
                    height:30px;
                    line-height:30px;
                    text-transform:uppercase;
                    font-family:' . $fontFamily . ';
                    font-size:10px;
                    color:#aaa;
                }
                .ipmdlwndwsc [data-form-element-type="datetime"] [data-form-element-component="date"]{
                    text-align:center;
                    height:30px;
                    line-height:30px;
                    font-family:' . $fontFamily . ';
                    font-size:' . $fontSize . ';
                    border-radius:var(--modal-window-content-field-border-radius);
                }
                .ipmdlwndwsc [data-form-element-type="datetime"] [data-form-element-component="date"]:not([data-form-element-data-selected]):hover{
                    background-color:rgba(0,0,0,0.1);
                }
                .ipmdlwndwsc [data-form-element-type="datetime"] [data-form-element-component="date"]:not([data-form-element-data-selected]):active,
                .ipmdlwndwsc [data-form-element-type="datetime"] [data-form-element-component="date"]:not([data-form-element-data-selected]):focus{
                    background-color:rgba(0,0,0,0.15);
                }
                .ipmdlwndwsc [data-form-element-type="datetime"] [data-form-element-data-month="other"]{
                    opacity:0.3;
                }
                .ipmdlwndwsc [data-form-element-type="datetime"] [data-form-element-component="date"][data-form-element-data-today]:not([data-form-element-data-selected]){
                    background-color:#999;
                    color:#fff;
                }
                .ipmdlwndwsc [data-form-element-type="datetime"] [data-form-element-component="date"][data-form-element-data-selected]{
                    background-color:#555;
                    color:#fff;
                }
                .ipmdlwndwsc [data-form-element-type="datetime"] [data-form-element-component="months-tooltip"],
                .ipmdlwndwsc [data-form-element-type="datetime"] [data-form-element-component="years-tooltip"]{
                    --tooltip-background-color:' . $fieldBackground . ';
                    --tooltip-border:var(--modal-window-content-field-border-active);
                    --tooltip-border-radius:var(--modal-window-content-field-border-radius);
                    width:170px;
                }
                .ipmdlwndwsc [data-form-element-type="datetime"] [data-form-element-component="months"],
                .ipmdlwndwsc [data-form-element-type="datetime"] [data-form-element-component="years"]{
                    padding:7px;
                }
                .ipmdlwndwsc [data-form-element-type="datetime"] [data-form-element-component="month"],
                .ipmdlwndwsc [data-form-element-type="datetime"] [data-form-element-component="year"]{
                    width:100%;
                    height:30px;
                    line-height:30px;
                    font-family:' . $fontFamily . ';
                    font-size:' . $fontSize . ';
                    text-align:center;
                    border-radius:var(--modal-window-content-field-border-radius);
                }
                .ipmdlwndwsc [data-form-element-type="datetime"] [data-form-element-component="month"]:not([data-form-element-data-selected]):hover,
                .ipmdlwndwsc [data-form-element-type="datetime"] [data-form-element-component="year"]:not([data-form-element-data-selected]):hover{
                    background-color:rgba(0,0,0,0.1);
                }
                .ipmdlwndwsc [data-form-element-type="datetime"] [data-form-element-component="month"]:not([data-form-element-data-selected]):active,
                .ipmdlwndwsc [data-form-element-type="datetime"] [data-form-element-component="month"]:not([data-form-element-data-selected]):focus,
                .ipmdlwndwsc [data-form-element-type="datetime"] [data-form-element-component="year"]:not([data-form-element-data-selected]):active,
                .ipmdlwndwsc [data-form-element-type="datetime"] [data-form-element-component="year"]:not([data-form-element-data-selected]):focus{
                    background-color:rgba(0,0,0,0.15);
                }
                .ipmdlwndwsc [data-form-element-type="datetime"] [data-form-element-component="month"][data-form-element-data-selected],
                .ipmdlwndwsc [data-form-element-type="datetime"] [data-form-element-component="year"][data-form-element-data-selected]{
                    background-color:#555;
                    color:#fff;
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

        $data = [__('ivopetkov.modalWindows.close')];
        $package->get = 'return ivoPetkov.bearFrameworkAddons.modalWindows.initialize(' . json_encode($data) . ');';
    });
