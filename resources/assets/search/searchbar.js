import modals from './../modal/modals.js';

const searchbar = (function(window, document) {
    'use strict';
    
    function searchRequest(uri, event, searchbarName) {
        
        const dropdownEl = document.querySelector('[data-searchbar-dropdown="'+searchbarName+'"]');
        const documentClickListener = (e) => {
            if (e.target.closest('[data-searchbar-dropdown="'+searchbarName+'"]') === null) {
                dropdownEl.classList.remove('active');
            }
        };
        
        if (dropdownEl) {
            if (!dropdownEl.classList.contains('active')) {
                dropdownEl.classList.add('active');
                document.addEventListener('click', documentClickListener, false);
            }
        }
        
        fetch(uri, {
            method: 'GET',
            headers: {
                "X-Requested-With": "XMLHttpRequest",
                "Accept": "application/json"
            },
        }).then(response => {
            return response.json();
        }).then(data => {
            const resultsEl = document.querySelector('[data-searchbar-results="'+searchbarName+'"]');
            
            if (data.status === 200) {
                resultsEl.innerHTML = data.html;
            } else {
                resultsEl.innerHTML = data.message;
            }
        });
    }
    
    document.addEventListener('DOMContentLoaded', (e) => {
        let keyupTimeout = null;
        
        document.addEventListener('keyup', function(e) {
            const form = e.target.closest('form');

            if (form && form.hasAttribute('data-searchbar')) {
                const queryString = new URLSearchParams(new FormData(form)).toString();
                const uri = form.getAttribute('action')+'?'+queryString;
                
                if (keyupTimeout != null) {
                    clearTimeout(keyupTimeout);
                }
                
                if (event.key == "Tab") {
                    return;
                }
                
                keyupTimeout = setTimeout(() => {
                    keyupTimeout = null;
                    searchRequest(uri, e, form.getAttribute('data-searchbar'));
                }, 200);
            }

            return;
        });
        
        const modal = modals.get('searchbar');
        document.body.appendChild(modal.modalEl); // to fix css relative
        
        modal.listen('opened', (modal) => {
            modal.modalEl.querySelector('input').focus();
        });
    });
    
})(window, document);

export default searchbar;