<?php

/*
 * Modal windows addon for Bear Framework
 * https://github.com/ivopetkov/modal-windows-bearframework-addon
 * Copyright (c) Ivo Petkov
 * Free to use under the MIT license.
 */

namespace IvoPetkov\BearFrameworkAddons;

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
}
