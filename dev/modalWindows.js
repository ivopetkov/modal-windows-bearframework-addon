/*
 * Modal windows addon for Bear Framework
 * https://github.com/ivopetkov/modal-windows-bearframework-addon
 * Copyright (c) Ivo Petkov
 * Free to use under the MIT license.
 */

/* global clientPackages */

var ivoPetkov = ivoPetkov || {};
ivoPetkov.bearFrameworkAddons = ivoPetkov.bearFrameworkAddons || {};
ivoPetkov.bearFrameworkAddons.modalWindows = ivoPetkov.bearFrameworkAddons.modalWindows || (function () {

    Promise = window.Promise || function (callback) {
        var thenCallbacks = [];
        var catchCallback = null;
        this.then = function (f) {
            thenCallbacks.push(f);
            return this;
        };
        this.catch = function (f) {
            if (catchCallback === null) {
                catchCallback = f;
            }
            return this;
        };
        var resolve = function () {
            for (var i in thenCallbacks) {
                thenCallbacks[i].apply(null, arguments);
            }
        };
        var reject = function () {
            if (catchCallback !== null) {
                catchCallback.apply(null, arguments);
            }
        };
        window.setTimeout(function () {
            callback(resolve, reject);
        }, 16);
    };

    var lightboxContext = null;
    var lightboxStatus = null; // 1 - loading, 2 - no loading
    var lightboxLoadingStatusCounter = 0;

    var showLightboxLoading = function () {
        lightboxLoadingStatusCounter++;
        if (lightboxStatus !== 1) {
            lightboxStatus = 1;
            clientPackages.get('lightbox').then(function (lightbox) { // its embeded
                lightboxContext = lightbox.make({ showCloseButton: false });
            });
        }
    };

    var hideLightboxLoading = function () {
        lightboxLoadingStatusCounter--;
        if (lightboxLoadingStatusCounter !== 0) {
            return new Promise(function (resolve, reject) {
                resolve(); // what to return ??
            });
        }
        if (lightboxStatus === 1) {
            lightboxStatus = 2;
            return lightboxContext.open('', { showCloseButton: false, spacing: 0 });
        } else {
            return new Promise(function (resolve, reject) {
                resolve();
            });
        }
    };

    var closeLightbox = function () {
        if (lightboxContext !== null) {
            lightboxStatus = null;
            lightboxContext.close();
            lightboxContext = null;
        }
    };

    var globalCssAdded = false;

    var container = null;

    var make = function () {
        var windowContainer = null;

        var open = function (name, data) {
            if (typeof data === 'undefined') {
                data = {};
            }

            if (container !== null) {
                var otherWindows = container.childNodes;
                for (var i = 0; i < otherWindows.length; i++) {
                    otherWindows[i].setAttribute('class', 'ipmdlwndwvh');
                }
            }

            var handleError = function () {
                hideLoading();
                if (container !== null) {
                    var previousWindow = container.lastChild;
                    if (previousWindow !== null) {
                        previousWindow.setAttribute('class', 'ipmdlwndwv');
                    }
                }
            };

            showLoading();

            clientPackages.get('serverRequests').then(function (serverRequests) {
                clientPackages.get('-ivopetkov-js-modal-windows-html5domdocument').then(function (html5DOMDocument) {
                    serverRequests.send('-modal-window-open', { i: name, d: JSON.stringify(data), g: globalCssAdded ? 0 : 1 }).then(function (responseText) {
                        var result = JSON.parse(responseText);
                        if (typeof result.s !== 'undefined') {
                            html5DOMDocument.insert(result.s);
                            globalCssAdded = true;
                        }
                        if (typeof result.c !== 'undefined') {
                            hideLightboxLoading().then(function () {
                                if (container === null) {
                                    container = document.createElement('div');
                                    container.setAttribute('class', 'ipmdlwndwsc');
                                    document.body.appendChild(container);
                                }
                                windowContainer = document.createElement('div');
                                windowContainer.setAttribute('data-form-tooltip-container', 'true'); // needed by ivopetkov/form-bearframework-addon
                                container.appendChild(windowContainer);
                                html5DOMDocument.insert('<div><div><div></div><div></div><div></div></div></div>', [windowContainer]);
                                var windowPartsElement = windowContainer.firstChild.firstChild;
                                windowPartsElement.firstChild.addEventListener('click', close);
                                windowPartsElement.firstChild.addEventListener('keydown', function (e) {
                                    if (e.keyCode === 13) {
                                        close();
                                    }
                                });
                                windowPartsElement.firstChild.setAttribute('tabindex', '0');
                                if (typeof result.c.width !== 'undefined') {
                                    windowPartsElement.style.width = result.c.width;
                                }
                                if (typeof result.c.title !== 'undefined') {
                                    windowPartsElement.childNodes[1].innerText = result.c.title;
                                }
                                if (typeof result.c.content !== 'undefined') {
                                    html5DOMDocument.insert(result.c.content, [windowPartsElement.lastChild]);
                                }
                                var forms = windowPartsElement.querySelectorAll('form');
                                for (var i = 0; i < forms.length; i++) {
                                    var form = forms[i];
                                    form.addEventListener('submitstart', disable);
                                    form.addEventListener('submitend', enable);
                                }
                                window.setTimeout(function () {
                                    windowContainer.setAttribute('class', 'ipmdlwndwv');
                                }, 16);

                                windowContainer.mwClose = close;
                            });
                        } else {
                            handleError();
                        }
                    }).catch(handleError);
                }).catch(handleError);
            }).catch(handleError);
        };

        var close = function () {
            if (windowContainer !== null) {
                var previousWindow = windowContainer.previousSibling;
                windowContainer.setAttribute('class', 'ipmdlwndwh');
                window.setTimeout(function () {
                    container.removeChild(windowContainer);
                    if (container.childNodes.length === 0) {
                        container.parentNode.removeChild(container);
                        container = null;
                    }
                    if (previousWindow !== null) {
                        previousWindow.setAttribute('class', 'ipmdlwndwv');
                    }
                }, 300);
                if (container.childNodes.length === 1) {
                    closeLightbox();
                }
            }
        };

        var enable = function () {
            if (windowContainer !== null) {
                windowContainer.removeAttribute('data-mw-disabled');
            }
        };

        var disable = function () {
            if (windowContainer !== null) {
                windowContainer.setAttribute('data-mw-disabled', '1');
            }
        };

        return {
            'open': open,
            'close': close,
            'enable': enable,
            'disable': disable,
        };
    };

    var open = function (name, data) {
        var window = make();
        window.open(name, data);
        return window;
    };

    var closeAll = function () {
        if (container !== null) {
            var otherWindows = container.childNodes;
            for (var i = otherWindows.length - 2; i >= 0; i--) {
                container.removeChild(otherWindows[i]);
            }
            var lastWindow = container.firstChild;
            if (lastWindow !== null) {
                lastWindow.mwClose();
            }
        }
    };

    var showLoading = function () {
        showLightboxLoading();
    };

    var hideLoading = function () {
        hideLightboxLoading();
        if (container === null || container.childNodes.length === 0) {
            closeLightbox();
        } else {
            // todo
        }
    };

    return {
        'open': open,
        'closeAll': closeAll,
        'showLoading': showLoading,
        'hideLoading': hideLoading
    };
}());