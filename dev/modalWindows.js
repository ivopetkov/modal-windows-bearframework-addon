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
    var lightboxStatus = null; // 1 - loading, 2 - empty (no loading)
    var lightboxLoadingStatusCounter = 0;

    var onBeforeEscKeyClose = function () {
        if (container !== null && container.lastChild) {
            var lastWindowContainer = container.lastChild;
            if (lightboxStatus === 1) { // loading
                openVersion++;
                hideLightboxLoading(lastWindowContainer.mwCloseOnEscKey).then(function () {
                    lastWindowContainer.setAttribute('class', 'ipmdlwndwv');
                });
                return false; // cancel esc
            } else {
                if (lastWindowContainer.getAttribute('data-mw-disabled') !== null) {
                    return false; // cancel esc
                }
                if (lastWindowContainer.mwClose()) { // closeLightbox is called inside if needed
                    return false; // cancel esc
                }
            }
        } else {
            if (lightboxStatus !== null) {
                closeAll();
            }
        }
    };

    var showLightboxLoading = function (closeOnEscKey) { // show lightbox's loading screen
        lightboxLoadingStatusCounter++;
        return new Promise(function (resolve, reject) {
            if (lightboxStatus !== 1) { // not loading
                lightboxStatus = 1;
                clientPackages.get('lightbox').then(function (lightbox) { // its embeded
                    lightboxContext = lightbox.make({ showCloseButton: false, spacing: 0, closeOnEscKey: closeOnEscKey, onBeforeEscKeyClose: onBeforeEscKeyClose });
                    resolve();
                });
            } else {
                resolve();
            }
        });
    };

    var hideLightboxLoading = function (closeOnEscKey) { // hide loading but show empty screen
        lightboxLoadingStatusCounter--;
        if (lightboxLoadingStatusCounter < 0) {
            lightboxLoadingStatusCounter = 0;
        }
        if (lightboxLoadingStatusCounter > 0) {
            return new Promise(function (resolve, reject) {
                resolve();
            });
        }
        if (lightboxStatus === 1) { // loading
            lightboxStatus = 2;
            return lightboxContext.open('', { showCloseButton: false, spacing: 0, closeOnEscKey: closeOnEscKey, onBeforeEscKeyClose: onBeforeEscKeyClose, resolveBeforeHTMLAdded: true });
        } else {
            return new Promise(function (resolve, reject) {
                resolve();
            });
        }
    };

    var closeLightbox = function () {
        if (lightboxContext !== null) {
            lightboxLoadingStatusCounter = 0;
            lightboxStatus = null;
            lightboxContext.close();
            lightboxContext = null;
        }
    };

    var openVersion = 0;

    var make = function () {
        var windowContainer = null;

        var open = function (name, data, options) {
            if (typeof data === 'undefined') {
                data = {};
            }
            if (typeof options === 'undefined') {
                options = {};
            }

            openVersion++;
            var currentOpenVersion = openVersion;

            var onOpen = typeof options.onOpen !== 'undefined' ? options.onOpen : null;
            var closeOnEscKey = typeof options.closeOnEscKey !== 'undefined' ? options.closeOnEscKey : true;

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
                var html = '';
                html += '<div data-modal-window-component="header">';
                html += '<div data-modal-window-component="header-title"></div>';
                html += '<div data-modal-window-component="header-buttons">';
                html += '<div data-modal-window-component="header-button-close"></div>';
                html += '</div>';
                html += '</div>';
                html += '<div data-modal-window-component="content"></div>';
                html5DOMDocument.insert('<div><div data-modal-window-component="window">' + html + '</div></div>', [windowContainer]);
                var windowElement = windowContainer.firstChild.firstChild;
                var titleElement = windowElement.querySelector('[data-modal-window-component="header-title"]');
                var closeButtonElement = windowElement.querySelector('[data-modal-window-component="header-button-close"]');
                var contentElement = windowElement.querySelector('[data-modal-window-component="content"]');
                closeButtonElement.addEventListener('click', close);
                closeButtonElement.addEventListener('keydown', function (e) {
                    if (e.keyCode === 13) {
                        close();
                    }
                });
                closeButtonElement.setAttribute('tabindex', '0');
                if (typeof contentData.width !== 'undefined') {
                    windowElement.style.width = contentData.width;
                }
                if (typeof contentData.title !== 'undefined') {
                    titleElement.innerText = contentData.title;
                }
                if (typeof contentData.content !== 'undefined') {
                    html5DOMDocument.insert(contentData.content, [contentElement]);
                }
                var forms = windowElement.querySelectorAll('form');
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
                windowContainer.mwCloseOnEscKey = closeOnEscKey;

                if (addToCache && typeof contentData.cacheTTL !== 'undefined') {
                    contentCache[cacheKey] = [contentData, (new Date()).getTime() + contentData.cacheTTL * 1000];
                }
            };

            showLightboxLoading(closeOnEscKey).then(function () {
                if (typeof contentCache[cacheKey] !== 'undefined' && contentCache[cacheKey][1] > (new Date()).getTime()) {
                    hideLightboxLoading(closeOnEscKey).then(function () {
                        if (openVersion !== currentOpenVersion || lightboxStatus !== 2) {
                            return;
                        }
                        create(contentCache[cacheKey][0], false);
                    });
                    return;
                }

                clientPackages.get('serverRequests').then(function (serverRequests) {
                    clientPackages.get('html5DOMDocument').then(function (html5DOMDocument) {
                        serverRequests.send('-modal-window-open', { i: name, d: JSON.stringify(data), g: globalCssAdded ? 0 : 1 }).then(function (responseText) {
                            hideLightboxLoading(closeOnEscKey).then(function () {
                                if (openVersion !== currentOpenVersion || lightboxStatus !== 2) {
                                    return;
                                }
                                var result = JSON.parse(responseText);
                                if (typeof result.s !== 'undefined') {
                                    html5DOMDocument.insert(result.s);
                                    globalCssAdded = true;
                                }
                                if (typeof result.c !== 'undefined') {
                                    create(result.c, true);
                                } else {
                                    handleError();
                                }
                            });
                        }).catch(handleError);
                    }).catch(handleError);
                }).catch(handleError);
            });
        };

        var close = function () { // return true if closed
            openVersion++;
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
                var remainingWindowsCount = container.childNodes.length - 1;
                if (remainingWindowsCount === 0) {
                    closeLightbox();
                }
                return true;
            }
            return false;
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
        return new Promise(function (resolve, reject) {
            if (container !== null && container.childNodes.length > 0) {
                var otherWindows = container.childNodes;
                for (var i = otherWindows.length - 2; i >= 0; i--) {
                    container.removeChild(otherWindows[i]);
                }
                var lastWindow = container.firstChild;
                if (lastWindow !== null) {
                    lastWindow.mwClose();
                }
                resolve();
            } else {
                openVersion++;
                hideLoading()
                    .then(resolve)
                    .catch(reject);
            }
        });
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

    var showLoading = function (options) {
        if (typeof options === 'undefined') {
            options = {};
        }
        var closeOnEscKey = typeof options.closeOnEscKey !== 'undefined' ? options.closeOnEscKey : true;
        return new Promise(function (resolve, reject) {
            showLightboxLoading(closeOnEscKey)
                .then(resolve)
                .catch(reject);
        });
    };

    var hideLoading = function () {
        return new Promise(function (resolve, reject) {
            var closeOnEscKey = true;
            if (container !== null && container.lastChild) {
                var lastWindowContainer = container.lastChild;
                closeOnEscKey = lastWindowContainer.mwCloseOnEscKey;
            }
            hideLightboxLoading(closeOnEscKey).then(function () {
                if (container === null || container.childNodes.length === 0) {
                    closeLightbox();
                }
                resolve();
            }).catch(reject);
        });

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