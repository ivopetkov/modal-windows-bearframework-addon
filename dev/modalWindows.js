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

    var globalHTMLAdded = false;
    var contentCache = {};

    var container = null;

    var closeButtonText = '';
    var errorText = '';
    var offlineText = '';

    var idCounter = 0;
    var idPrefix = 'mw-' + (new Date()).getTime().toString() + '-';
    var getID = function () {
        idCounter++;
        return idPrefix + idCounter;
    };

    var initialize = function (data) {
        closeButtonText = data[0];
        errorText = data[1];
        offlineText = data[2];
        return this;
    };

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

    // WINDOW NAVIGATION

    var windowNavigation = null; // loaded after first showLightboxLoading
    var isNavigationGoBackAfterHideLoading = false;
    var isNavigationGoBackOnCloseCurrent = false;
    var isNavigationOpenNewWindow = false;

    var onWindowNavigationChange = function () {
        if (isNavigationGoBackAfterHideLoading) {
            return false;
        }
        if (isNavigationGoBackOnCloseCurrent) {
            return false;
        }
        if (isNavigationOpenNewWindow) {
            return false;
        }
        var windowID = windowNavigation.getData('ipmw');
        if (windowID !== null) {
            if (windowID === 'l') {

            } else {
                if (document.getElementById(windowID) !== null) {
                    closeAll({ lastWindowID: windowID });
                } else {
                    if (windowID.indexOf(idPrefix) === -1) {
                        closeAll();
                    }
                }
            }
        } else {
            closeAll();
        }
    };

    var addToWindowNavigation = function (id) {
        windowNavigation.open(null, null, null, { ipmw: id }, false);
    };

    var addLoadingToNavigation = function () {
        addToWindowNavigation('l');
    };

    var hideLoadingFromNavigation = function () {
        if (windowNavigation.getData('ipmw') !== 'l') {
            return Promise.resolve();
        }
        isNavigationGoBackAfterHideLoading = true;
        return new Promise(function (resolve, reject) {
            windowNavigation.goBack()
                .then(function () {
                    isNavigationGoBackAfterHideLoading = false;
                    resolve();
                });
        });
    };

    var updateWindowNavigationOnCloseWindow = function (windowContainer) {
        if (windowContainer.id === windowNavigation.getData('ipmw')) {
            isNavigationGoBackOnCloseCurrent = true;
            windowNavigation.goBack()
                .then(function () {
                    isNavigationGoBackOnCloseCurrent = false;
                });
        }
    };

    var addWindowToNavigation = function (windowContainer) {
        addToWindowNavigation(windowContainer.id);
    };

    // LIGHTBOX

    var lightboxContext = null;
    var lightboxStatus = null; // 1 - loading, 2 - empty (no loading)
    var lightboxLoadingStatusCounter = 0;

    var setWindowVisibility = function (windowContainer, visible) {
        if (visible) {
            windowContainer.setAttribute('class', 'ipmdlwndwv');
            windowContainer.removeAttribute('inert');
        } else {
            windowContainer.setAttribute('class', 'ipmdlwndwvh');
            windowContainer.setAttribute('inert', 'true');
        }
    };

    var isVisibleWindow = function (windowContainer) {
        return windowContainer.getAttribute('class', 'ipmdlwndwv');
    };

    var onBeforeEscKeyClose = function () {
        if (container !== null && container.lastChild) {
            var lastWindowContainer = container.lastChild;
            if (lightboxStatus === 1) { // loading
                openVersion++;
                hideLightboxLoading(lastWindowContainer.mwCloseOnEscKey).then(function () {
                    setWindowVisibility(lastWindowContainer, true);
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
                var continueShow = function () {
                    clientPackages.get('lightbox', { timeout: 15 }).then(function (lightbox) { // its embeded
                        lightboxContext = lightbox.make({ showCloseButton: false, spacing: 0, closeOnEscKey: closeOnEscKey, onBeforeEscKeyClose: onBeforeEscKeyClose });
                        addLoadingToNavigation();
                        resolve();
                    });
                };
                if (windowNavigation === null) {
                    clientPackages.get('windowNavigation').then(function (wn) { // its embeded
                        windowNavigation = wn;
                        windowNavigation.addChangeHandler(onWindowNavigationChange);
                        continueShow();
                    });
                } else {
                    continueShow();
                }
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
            return new Promise(function (resolve, reject) {
                hideLoadingFromNavigation().then(function () {
                    lightboxContext.open('', { showCloseButton: false, spacing: 0, closeOnEscKey: closeOnEscKey, onBeforeEscKeyClose: onBeforeEscKeyClose, resolveBeforeHTMLAdded: true })
                        .then(resolve)
                        .catch(reject);
                });
            });
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

    var escapeText = (text) => {
        let div = document.createElement('div');
        div.innerText = text;
        return div.innerHTML;
    };

    var openVersion = 0;

    var make = function () {
        var windowContainer = null;

        var open = function (name, data, options, type) {
            if (typeof data === 'undefined') {
                data = {};
            }
            if (typeof options === 'undefined') {
                options = {};
            }
            if (windowContainer !== null) {
                return false;
            }

            openVersion++;
            var currentOpenVersion = openVersion;

            var onOpen = typeof options.onOpen !== 'undefined' ? options.onOpen : null;
            var onFail = typeof options.onFail !== 'undefined' ? options.onFail : null;
            var closeOnEscKey = typeof options.closeOnEscKey !== 'undefined' ? options.closeOnEscKey : true;
            var showErrors = typeof options.showErrors !== 'undefined' ? options.showErrors : false;
            var timeout = typeof options.timeout !== 'undefined' ? options.timeout : 30;

            if (container !== null) {
                var otherWindows = container.childNodes;
                for (var i = 0; i < otherWindows.length; i++) {
                    setWindowVisibility(otherWindows[i], false);
                }
            }
            var handleError = function () {
                hideLoading().then(function () {
                    if (showErrors) {
                        var windowNavigator = window.navigator;
                        if (typeof windowNavigator.onLine !== "undefined" && windowNavigator.onLine) {
                            openError();
                        } else {
                            openOffline();
                        }
                    }
                    if (onFail !== null) {
                        try {
                            onFail();
                        } catch (e) {

                        }
                    }
                });
                if (container !== null) {
                    var previousWindow = container.lastChild;
                    if (previousWindow !== null) {
                        setWindowVisibility(previousWindow, true);
                    }
                }
            };

            var addGlobalHTML = function (onDone, onFail, globalHTML) {
                if (globalHTMLAdded) {
                    onDone();
                    return;
                }
                clientPackages.get('html5DOMDocument', { timeout: 15 }).then(function (html5DOMDocument) {
                    if (globalHTML === null) {
                        var element = document.head.querySelector('[type="ipmwg"]');
                        if (element !== null) {
                            globalHTML = element.getAttribute('data-content');
                        }
                    }
                    if (globalHTML !== null) {
                        html5DOMDocument.insert(globalHTML);
                        globalHTMLAdded = true;
                        onDone();
                    } else {
                        onFail();
                    }
                }).catch(handleError);
            };

            var cacheKey = name + '$' + type + '$' + JSON.stringify(data);

            var create = function (contentData, addToCache) {
                if (container === null) {
                    container = document.createElement('div');
                    container.setAttribute('class', 'ipmdlwndwsc');
                    document.body.appendChild(container);
                }
                windowContainer = document.createElement('div');
                windowContainer.setAttribute('id', getID());
                windowContainer.setAttribute('data-form-tooltip-container', 'true'); // needed by ivopetkov/form-bearframework-addon
                container.appendChild(windowContainer);
                var html = '';
                html += '<div data-modal-window-component="header">';
                html += '<div data-modal-window-component="header-title"></div>';
                html += '<div data-modal-window-component="header-buttons">';
                if (typeof contentData.headerButtons !== 'undefined') {
                    html += contentData.headerButtons.join('');
                }
                html += '<div data-modal-window-component="header-button-close" tabindex="0" role="button" title="' + closeButtonText + '"></div>';
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
                if (typeof contentData.width !== 'undefined') {
                    windowElement.style.width = contentData.width;
                }
                if (typeof contentData.title !== 'undefined') {
                    titleElement.innerHTML = escapeText(contentData.title).split("\n").join("<br>");
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

                windowContainer.mwClose = close;
                windowContainer.mwCloseOnEscKey = closeOnEscKey;

                if (addToCache && typeof contentData.cacheTTL !== 'undefined') {
                    contentCache[cacheKey] = [contentData, (new Date()).getTime() + contentData.cacheTTL * 1000];
                }

                isNavigationOpenNewWindow = true;
                addWindowToNavigation(windowContainer);

                window.setTimeout(function () {
                    setWindowVisibility(windowContainer, true);
                    isNavigationOpenNewWindow = false;
                }, 16);

                if (onOpen !== null) {
                    onOpen(windowContainer);
                }
            };

            var contentData = null;
            if (type === 'm') { // message
                var messageHTML = '<div data-modal-window-component="content-message">' + escapeText(data.m).split("\n").join('<br>') + '</div><div data-modal-window-component="content-button-ok">OK</div>';
                contentData = { width: '400px', content: messageHTML };
            }

            var continueOpen1 = function () {
                showLightboxLoading(closeOnEscKey).then(function () {
                    if (typeof contentCache[cacheKey] !== 'undefined' && contentCache[cacheKey][1] > (new Date()).getTime()) {
                        addGlobalHTML(function () {
                            hideLightboxLoading(closeOnEscKey).then(function () {
                                if (openVersion !== currentOpenVersion || lightboxStatus !== 2) {
                                    return;
                                }
                                create(contentCache[cacheKey][0], false);
                            });
                        }, handleError, null); // there is preloaded global data or it's already added if there is cached content
                    } else {
                        var continueOpen3 = function () {
                            clientPackages.get('serverRequests', { timeout: 15 }).then(function (serverRequests) {
                                serverRequests.send('-modal-window-open', { i: name, d: JSON.stringify(contentData !== null ? {} : data), g: globalHTMLAdded ? 0 : 1 }, { timeout: timeout }).then(function (responseText) {
                                    hideLightboxLoading(closeOnEscKey).then(function () {
                                        if (openVersion !== currentOpenVersion || lightboxStatus !== 2) {
                                            return;
                                        }
                                        var result = JSON.parse(responseText);
                                        var continueOpen2 = function () {
                                            if (contentData !== null) {
                                                create(contentData, false);
                                            } else {
                                                if (typeof result.c !== 'undefined') {
                                                    create(result.c, true);
                                                } else {
                                                    handleError();
                                                }
                                            }
                                        };
                                        if (typeof result.g !== 'undefined') {
                                            addGlobalHTML(continueOpen2, handleError, result.g);
                                        } else {
                                            continueOpen2();
                                        }
                                    });
                                }).catch(handleError);
                            }).catch(handleError);
                        };
                        addGlobalHTML(continueOpen3, continueOpen3, null);
                    }
                });
            };

            if (contentData !== null) {
                showLightboxLoading(closeOnEscKey).then(function () {
                    addGlobalHTML(function () {
                        hideLightboxLoading(closeOnEscKey).then(function () {
                            create(contentData, false);
                        });
                    }, function () {
                        hideLightboxLoading(closeOnEscKey).then(function () {
                            continueOpen1();
                        });
                    }, null);
                });
            } else {
                if (type === 'd' && typeof contentCache[cacheKey] === 'undefined' && JSON.stringify(data) === '{}') {
                    var preloadedContentDataElement = document.head.querySelector('[type="ipmw"][data-name="' + name + '"]');
                    if (preloadedContentDataElement !== null) {
                        var contentCachedData = JSON.parse(preloadedContentDataElement.getAttribute('data-content'));
                        contentCache[cacheKey] = [contentCachedData, (new Date()).getTime() + contentCachedData.cacheTTL * 1000];
                    }
                }
                continueOpen1();
            }

            return true;
        };

        var close = function (options) { // return true if closed
            if (typeof options === "undefined") {
                options = {};
            }
            var returnPromise = typeof options.returnPromise !== "undefined" ? options.returnPromise : false;
            var expectOpen = typeof options.expectOpen !== "undefined" ? options.expectOpen : false;
            var expectShowLoading = typeof options.expectShowLoading !== "undefined" ? options.expectShowLoading : false;
            openVersion++;
            if (windowContainer !== null && container !== null) {
                var wasVisible = windowContainer.getAttribute('class') === 'ipmdlwndwv';
                var promiseResolve = null;
                var previousWindow = windowContainer.previousSibling;
                windowContainer.setAttribute('class', 'ipmdlwndwh');
                if (wasVisible) {
                    updateWindowNavigationOnCloseWindow(windowContainer);
                }
                window.setTimeout(function () {
                    container.removeChild(windowContainer);
                    windowContainer = null;
                    if (container.childNodes.length === 0) {
                        container.parentNode.removeChild(container);
                        container = null;
                    }
                    if (previousWindow !== null) {
                        setWindowVisibility(previousWindow, true);
                    }
                    if (expectOpen || expectShowLoading) {
                        if (container === null || container.childNodes.length === 0) {
                            closeLightbox();
                        }
                    }
                    if (promiseResolve !== null) {
                        promiseResolve(true);
                    }
                }, 300);
                if (!expectOpen && !expectShowLoading) {
                    if (container.childNodes.length - 1 === 0) {
                        closeLightbox();
                    }
                }
                if (returnPromise) {
                    return new Promise(function (resolve, reject) {
                        promiseResolve = resolve;
                    });
                } else {
                    return true;
                }
            }
            if (returnPromise) {
                return new Promise(function (resolve, reject) {
                    resolve(false);
                });
            } else {
                return false;
            }
        };

        var enable = function () {
            if (windowContainer !== null) {
                windowContainer.removeAttribute('data-mw-disabled');
                windowContainer.removeAttribute('inert');
            }
        };

        var disable = function () {
            if (windowContainer !== null) {
                windowContainer.setAttribute('data-mw-disabled', '1');
                windowContainer.setAttribute('inert', 'true');
            }
        };

        return {
            'open': open,
            'close': close, // Available options: expectOpen, expectShowLoading and returnPromise
            'enable': enable,
            'disable': disable,
        };
    };

    var open = function (name, data, options) {
        if (typeof options === 'undefined') {
            options = {};
        }
        var window = make();
        window.open(name, data, options, 'd'); // default
        return {
            'close': function (options) { // Available options: expectOpen, expectShowLoading and returnPromise
                return window.close(options);
            },
            'enable': function () {
                window.enable();
            },
            'disable': function () {
                window.disable();
            }
        };
    };

    var openMessage = function (message) { // todo open in new layer
        var options = {};
        options.onOpen = function (windowContainer) {
            windowContainer.querySelector('[data-modal-window-component="content-button-ok"]').addEventListener('click', closeCurrent);
        };
        var fallbackedToAlert = false;
        options.onFail = function () { // temp while deciding how to load the global css effiently while offline
            fallbackedToAlert = true;
            alert(message);
        };
        options.closeOnEscKey = true;
        var window = make();
        window.open('', { 'm': message }, options, 'm'); // message
        return {
            'close': function (options) { // Available options: expectOpen, expectShowLoading and returnPromise
                if (fallbackedToAlert) {
                    return false;
                }
                return window.close(options);
            }
        };
    };

    var openError = function (message) {
        if (typeof message === "undefined") {
            message = errorText;
        }
        return openMessage(message);
    };

    var openOffline = function (message) {
        if (typeof message === "undefined") {
            message = offlineText;
        }
        return openMessage(message);
    };

    var closeAll = function (options) { // Available options: expectOpen, expectShowLoading, lastWindowID
        if (typeof options === "undefined") {
            options = {};
        }
        var lastWindowID = typeof options.lastWindowID !== 'undefined' ? options.lastWindowID : null;
        return new Promise(function (resolve, reject) {
            if (container !== null && container.childNodes.length > 0) {
                var otherWindows = container.childNodes;
                if (lastWindowID !== null) {
                    var lastFound = false;
                    for (var i = 0; i < otherWindows.length; i++) {
                        var otherWindow = otherWindows[i];
                        if (otherWindow.id === lastWindowID) {
                            setWindowVisibility(otherWindow, true);
                            lastFound = true;
                        } else if (lastFound) {
                            if (isVisibleWindow(otherWindow)) {
                                otherWindow.mwClose();
                            } else {
                                container.removeChild(otherWindow);
                            }
                        }
                    }
                    if (lastFound) {
                        resolve();
                        return;
                    }
                }
                for (var i = otherWindows.length - 2; i >= 0; i--) {
                    container.removeChild(otherWindows[i]);
                }
                var lastWindow = container.firstChild;
                if (lastWindow !== null) {
                    options.returnPromise = true;
                    lastWindow.mwClose(options).then(resolve);
                } else {
                    resolve();
                }
            } else {
                openVersion++;
                hideLoading()
                    .then(resolve)
                    .catch(reject);
            }
        });
    };

    var closeCurrent = function (options) { // Available options: expectOpen and expectShowLoading
        if (typeof options === "undefined") {
            options = {};
        }
        return new Promise(function (resolve, reject) {
            if (container !== null) {
                var lastWindow = container.lastChild;
                if (lastWindow !== null) {
                    options.returnPromise = true;
                    lastWindow.mwClose(options).then(resolve);
                    return;
                }
            }
            resolve();
        });
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
        'initialize': initialize,
        'open': open,
        'openMessage': openMessage,
        'openError': openError,
        'openOffline': openOffline,
        'closeAll': closeAll,
        'closeCurrent': closeCurrent,
        'showLoading': showLoading,
        'hideLoading': hideLoading,
        'hasOpened': hasOpened
    };
}());