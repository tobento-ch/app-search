const search = (function(window, document) {
    'use strict';

    const search = {
        registerFilters: function() {
            let keyupTimeout = null;
            
            // register events globally as not to loose listeners on update filter DOM.
            ['keyup', 'change'].forEach(evt => {
                document.addEventListener(evt, function(e) {
                    
                    const form = e.target.closest('form');
                    
                    if (form && form.hasAttribute('data-search-filters')) {
                        const queryString = new URLSearchParams(new FormData(form)).toString();
                        const uri = form.getAttribute('action')+'?'+queryString;
                        
                        if (e.type === 'keyup') {
                            if (keyupTimeout != null) {
                                clearTimeout(keyupTimeout);
                            }
                            
                            keyupTimeout = setTimeout(() => {
                                keyupTimeout = null;
                                search.updateFilter(uri, e);
                            }, 200);
                        } else {
                            search.updateFilter(uri, e);
                        }
                    }

                    return;
                });
            });
            
            document.addEventListener('click', function(e) {
                if (! e.target.hasAttribute('href') || ! e.target.closest('[data-search-filter]')) {
                    return;
                }
                e.preventDefault();
                search.updateFilter(e.target.getAttribute('href'), e);
            });
        },
        updateFilter: function(uri, event) {
            fetch(uri, {
                method: 'GET',
            }).then(response => {
                return response.text();
            }).then(string => {
                const doc = (new DOMParser()).parseFromString(string, 'text/html');
                const action = event.target.closest('[data-search-action]');
                const actionName = action?.getAttribute('data-search-action');
                const updateName = event.target.closest('[data-search-update]')?.getAttribute('data-search-update');
                
                if (actionName === 'append-items') {
                    const appendTo = action.getAttribute('data-search-append-to');
                    const appendEnd = action.getAttribute('data-search-append-end');
                    const newEl = doc.querySelector('[data-search-results="'+appendTo+'"]');
                    const oldEl = document.querySelector('[data-search-results="'+appendTo+'"]');
                    if (newEl && oldEl) {
                        oldEl.append(...newEl.childNodes);
                    }
                    if (appendEnd === '1') {
                        event.target.closest('[data-search-update]')?.remove();
                    }
                } else {
                    const newEl = doc.querySelector('[data-search-results="all"]');
                    const oldEl = document.querySelector('[data-search-results="all"]');
                    if (newEl && oldEl) {
                        oldEl.parentNode.replaceChild(newEl, oldEl);
                    }
                }

                if (actionName === 'clear') {
                    document.querySelectorAll('[data-search-filters]').forEach(filters => {
                        const newEl = doc.querySelector('[data-search-filters="'+filters.getAttribute('data-search-filters')+'"]');

                        if (newEl) {
                            filters.replaceWith(newEl);
                        }
                    });
                    return;
                }
                
                document.querySelectorAll('[data-search-update]').forEach(filters => {
                    if (filters.getAttribute('data-search-update') === updateName && actionName !== 'append-items') {
                        return;
                    }

                    const newEl = doc.querySelector('[data-search-update="'+filters.getAttribute('data-search-update')+'"]');

                    if (newEl) {
                        filters.replaceWith(newEl);
                    }
                });
            });
        }
    };
    
    document.addEventListener('DOMContentLoaded', (e) => {
        search.registerFilters();
    });
    
    return search;
    
})(window, document);

export default search;