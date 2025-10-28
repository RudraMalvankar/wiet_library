<?php
/**
 * AJAX Handler for Layout.php
 * Include this at the TOP of any page that can be loaded via AJAX
 * It will suppress HTML structure when loaded in layout.php
 */

// Check if this is an AJAX request from layout.php
$isAjax = isset($_GET['ajax']) && $_GET['ajax'] == '1';

if ($isAjax) {
    // Start output buffering to capture the page content
    ob_start();
    
    // Set a flag so the page knows it's in AJAX mode
    define('AJAX_MODE', true);
    
    // Register shutdown function to strip HTML structure but keep styles and scripts
    register_shutdown_function(function() {
        $content = ob_get_clean();
        
        // Extract styles from head
        $styles = '';
        if (preg_match_all('/<style[^>]*>(.*?)<\/style>/is', $content, $styleMatches)) {
            foreach ($styleMatches[0] as $styleBlock) {
                $styles .= $styleBlock . "\n";
            }
        }
        
        // Extract body content using regex
        if (preg_match('/<body[^>]*>(.*?)<\/body>/is', $content, $matches)) {
            $bodyContent = $matches[1];
        } else {
            // If no body tag, just remove html/head tags
            $bodyContent = preg_replace('/<\/?html[^>]*>/i', '', $content);
            $bodyContent = preg_replace('/<head[^>]*>.*?<\/head>/is', '', $bodyContent);
            $bodyContent = preg_replace('/<\/?body[^>]*>/i', '', $bodyContent);
        }
        
        // Wrap the content so DOMContentLoaded listeners execute immediately
        $wrapper = '<script>
(function() {
    // Store original addEventListener
    var originalAddEventListener = document.addEventListener;
    var pendingDOMContentLoadedCallbacks = [];
    
    // Override addEventListener temporarily
    document.addEventListener = function(event, callback, options) {
        if (event === "DOMContentLoaded") {
            // Execute immediately since DOM is already loaded
            setTimeout(callback, 0);
        } else {
            originalAddEventListener.call(document, event, callback, options);
        }
    };
    
    // Restore after a brief moment
    setTimeout(function() {
        document.addEventListener = originalAddEventListener;
    }, 100);
})();
</script>';
        
        // Combine styles, wrapper, and body content
        $output = $styles . "\n" . $wrapper . "\n" . $bodyContent;
        
        // Clean up extra whitespace
        $output = trim($output);
        
        // Output the content with styles
        echo $output;
    });
} else {
    // Not AJAX mode
    define('AJAX_MODE', false);
}
?>
