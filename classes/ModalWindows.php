<?php

/*
 * Modal windows addon for Bear Framework
 * https://github.com/ivopetkov/modal-windows-bearframework-addon
 * Copyright (c) Ivo Petkov
 * Free to use under the MIT license.
 */

namespace IvoPetkov\BearFrameworkAddons;

use IvoPetkov\HTML5DOMDocument;
use IvoPetkov\BearFrameworkAddons\ModalWindows\Internal\Utilities;

/**
 *
 */
class ModalWindows
{

    /**
     * Registers a new modal window
     * 
     * @param string $name The name of the modal window
     * @param callable $callback A callback that will be called to get the content of the modal window
     * @return self Returns a instance to itself.
     * @throws \Exception
     */
    public function add(string $name, callable $callback): self
    {
        if (isset(Utilities::$modalWindows[$name])) {
            throw new \Exception('A modal window named "' . $name . '" is alread added!');
        }
        Utilities::$modalWindows[$name] = [$callback];
        return $this;
    }

    /**
     * Updates the Modal windows related code for the HTML string provided.
     * 
     * @param string $html The HTML string to update. 
     * @return string Returns the updated HTML string.
     */
    public function process(string $html): string
    {
        if (strpos($html, 'modal-windows') !== false) {
            $splitIndex = strpos($html, '</head>');
            if ($splitIndex !== false) {
                $htmlToUpdate = substr($html, 0, $splitIndex) . '</head><body></body></html>';
                $dom = new HTML5DOMDocument();
                $dom->loadHTML($htmlToUpdate, HTML5DOMDocument::ALLOW_DUPLICATE_IDS);
                $head = $dom->querySelector('head');
                if ($head !== null) {
                    $elements = $head->querySelectorAll('link[rel^="modal-windows"]');
                    $addGlobalHTML = false;
                    $modalWindowsToPreload = [];
                    if ($elements->length > 0) {
                        foreach ($elements as $element) {
                            $rel = $element->getAttribute('rel');
                            if ($rel === 'modal-windows-preload') {
                                $name = trim((string)$element->getAttribute('name'));
                                if (isset($name[0])) {
                                    $modalWindowsToPreload[] = $name;
                                }
                                $addGlobalHTML = true;
                                $elementsToRemove[] = $element;
                            }
                        }
                    }
                    $hasChange = false;
                    if ($addGlobalHTML) {
                        $element = $dom->createElement('script');
                        $element->setAttribute('type', 'ipmwg');
                        $element->setAttribute('data-content', Utilities::getGlobalHTML());
                        $head->appendChild($element);
                        $hasChange = true;
                    }
                    if (!empty($modalWindowsToPreload)) {
                        foreach ($modalWindowsToPreload as $name) {
                            if (isset(Utilities::$modalWindows[$name])) {
                                $content = call_user_func(Utilities::$modalWindows[$name][0], []);
                                if (is_array($content) && isset($content['cacheTTL'])) {
                                    $element = $dom->createElement('script');
                                    $element->setAttribute('type', 'ipmw');
                                    $element->setAttribute('data-name', $name);
                                    $element->setAttribute('data-content', json_encode($content));
                                    $head->appendChild($element);
                                    $hasChange = true;
                                }
                            }
                        }
                    }
                    foreach ($elementsToRemove as $elementToRemove) {
                        $elementToRemove->parentNode->removeChild($elementToRemove);
                        $hasChange = true;
                    }
                    if ($hasChange) {
                        $resultHTML = $dom->saveHTML();
                        $html = substr($resultHTML, 0, strpos($resultHTML, '</head>')) . substr($html, $splitIndex);
                    }
                }
            }
        }
        return $html;
    }
}
