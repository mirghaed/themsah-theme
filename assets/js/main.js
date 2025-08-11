(function($){
    $(function(){
        // placeholder for theme JS
        $('.header-btn .mytheme-btn').on('click', function(e){
            // noop
        });
    });
})(jQuery);

// Elementor Content Area Support
(function() {
    'use strict';
    
    // Function to ensure Elementor content area is available
    function ensureElementorContentArea() {
        // Check if we're in an Elementor context
        const isElementorContext = window.location.search.includes('elementor-preview') || 
                                  window.location.search.includes('elementor-iframe') ||
                                  window.location.search.includes('preview') ||
                                  window.location.search.includes('preview_id');
        
        if (isElementorContext) {
            // Look for existing content area
            let contentArea = document.querySelector('.elementor-content-area');
            
            // If no content area exists, create one
            if (!contentArea) {
                contentArea = document.createElement('div');
                contentArea.className = 'elementor-content-area';
                contentArea.style.cssText = 'min-height: 100vh; padding: 20px; text-align: center; color: #666;';
                contentArea.innerHTML = `
                    <p>محتوای المنتور در حال بارگذاری...</p>
                    <p>Elementor content is loading...</p>
                `;
                
                // Insert into the main content area
                const main = document.querySelector('main') || document.querySelector('.container') || document.body;
                if (main) {
                    main.appendChild(contentArea);
                }
            }
            
            // Ensure the content area is visible
            contentArea.style.display = 'block';
        }
    }
    
    // Run when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', ensureElementorContentArea);
    } else {
        ensureElementorContentArea();
    }
    
    // Also run after a short delay to catch late-loading content
    setTimeout(ensureElementorContentArea, 1000);
    
    // Listen for Elementor events
    if (window.elementorFrontend) {
        window.elementorFrontend.hooks.addAction('frontend/init', function() {
            ensureElementorContentArea();
        });
    }
    
    // Fallback for when Elementor is not loaded
    if (typeof window.elementorFrontend === 'undefined') {
        // Check periodically for Elementor content
        setInterval(function() {
            if (document.querySelector('.elementor')) {
                ensureElementorContentArea();
            }
        }, 2000);
    }
})();
