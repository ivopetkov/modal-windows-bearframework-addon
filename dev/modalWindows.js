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

    var globalCssAdded = false;
    var contentCache = {};

    var container = null;

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

    var onBeforeEscKeyClose = function () {
        setTimeout(function () {
            if (container !== null && container.lastChild) {
                var lastWindowContainer = container.lastChild;
                if (lastWindowContainer.getAttribute('data-mw-disabled') !== null) {
                    return null;
                }
                lastWindowContainer.mwClose();
            }
        }, 1);
        return false;
    };

    var showLightboxLoading = function () {
        lightboxLoadingStatusCounter++;
        return new Promise(function (resolve, reject) {
            if (lightboxStatus !== 1) {
                lightboxStatus = 1;
                clientPackages.get('lightbox').then(function (lightbox) { // its embeded
                    lightboxContext = lightbox.make({ showCloseButton: false, onBeforeEscKeyClose: onBeforeEscKeyClose });
                    resolve();
                });
            } else {
                resolve();
            }
        });
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
            return lightboxContext.open('', { showCloseButton: false, spacing: 0, onBeforeEscKeyClose: onBeforeEscKeyClose });
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

    var make = function () {
        var windowContainer = null;

        var open = function (name, data, options) {
            if (typeof data === 'undefined') {
                data = {};
            }
            if (typeof options === 'undefined') {
                options = {};
            }

            var onOpen = typeof options.onOpen !== 'undefined' ? options.onOpen : null;

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

            var cacheKey = name + '$' + JSON.stringify(data);

            var create = function (contentData, addToCache) {
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
                if (typeof contentData.width !== 'undefined') {
                    windowPartsElement.style.width = contentData.width;
                }
                if (typeof contentData.title !== 'undefined') {
                    windowPartsElement.childNodes[1].innerText = contentData.title;
                }
                if (typeof contentData.content !== 'undefined') {
                    html5DOMDocument.insert(contentData.content, [windowPartsElement.lastChild]);
                }
                var forms = windowPartsElement.querySelectorAll('form');
                for (var i = 0; i < forms.length; i++) {
                    var form = forms[i];
                    form.addEventListener('submitstart', disable); // todo fix for multiple
                    form.addEventListener('submitend', enable);
                }
                window.setTimeout(function () {
                    windowContainer.setAttribute('class', 'ipmdlwndwv');
                }, 16);

                if (onOpen !== null) {
                    onOpen(windowContainer);
                }

                windowContainer.mwClose = close;

                if (addToCache && typeof contentData.cacheTTL !== 'undefined') {
                    contentCache[cacheKey] = [contentData, (new Date()).getTime() + contentData.cacheTTL * 1000];
                }
            };

            showLightboxLoading().then(function () {
                if (typeof contentCache[cacheKey] !== 'undefined' && contentCache[cacheKey][1] > (new Date()).getTime()) {
                    hideLightboxLoading().then(function () {
                        create(contentCache[cacheKey][0], false);
                    });
                    return;
                }

                clientPackages.get('serverRequests').then(function (serverRequests) {
                    clientPackages.get('html5DOMDocument').then(function (html5DOMDocument) {
                        serverRequests.send('-modal-window-open', { i: name, d: JSON.stringify(data), g: globalCssAdded ? 0 : 1 }).then(function (responseText) {
                            var result = JSON.parse(responseText);
                            if (typeof result.s !== 'undefined') {
                                html5DOMDocument.insert(result.s);
                                globalCssAdded = true;
                            }
                            if (typeof result.c !== 'undefined') {
                                hideLightboxLoading().then(function () {
                                    create(result.c, true);
                                });
                            } else {
                                handleError();
                            }
                        }).catch(handleError);
                    }).catch(handleError);
                }).catch(handleError);
            });
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

    var open = function (name, data, options) {
        if (typeof options === 'undefined') {
            options = {};
        }
        var window = make();
        window.open(name, data, options);
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

    var closeCurrent = function () {
        if (container !== null) {
            var lastWindow = container.lastChild;
            if (lastWindow !== null) {
                lastWindow.mwClose();
            }
        }
    };

    var hasOpened = function () {
        return lightboxContext !== null;
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
        'closeCurrent': closeCurrent,
        'showLoading': showLoading,
        'hideLoading': hideLoading,
        'hasOpened': hasOpened
    };
}());