<?php
/**
 * AJAX Handler for Layout.php
 * Include this at the TOP of any page that can be loaded via AJAX
 * It will suppress HTML structure when loaded in layout.php
 */

// Start session if not already started - MUST be before any output
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if this is an AJAX request from layout.php
$isAjax = isset($_GET['ajax']) && $_GET['ajax'] == '1';

if ($isAjax) {
    // Enable error display for debugging
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    
    // Start output buffering to capture the page content
    ob_start();
    
    // Set a flag so the page knows it's in AJAX mode
    define('AJAX_MODE', true);
    
    // Register shutdown function to strip HTML structure but keep styles and scripts
    register_shutdown_function(function() {
        // Check for fatal errors
        $error = error_get_last();
        if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
            ob_clean();
            echo '<div style="background: #dc3545; color: white; padding: 20px; border-radius: 8px; margin: 20px;">';
            echo '<h3><i class="fas fa-exclamation-triangle"></i> PHP Error</h3>';
            echo '<p><strong>Error:</strong> ' . htmlspecialchars($error['message']) . '</p>';
            echo '<p><strong>File:</strong> ' . htmlspecialchars($error['file']) . '</p>';
            echo '<p><strong>Line:</strong> ' . htmlspecialchars($error['line']) . '</p>';
            echo '</div>';
            return;
        }
        
        $content = ob_get_clean();
        
        // Check if content is empty or too short
        if (empty(trim($content)) || strlen(trim($content)) < 10) {
            echo '<div style="background: #ffc107; color: #000; padding: 20px; border-radius: 8px; margin: 20px;">';
            echo '<h3><i class="fas fa-exclamation-triangle"></i> Warning: Empty Content</h3>';
            echo '<p>The page loaded but returned no content. This might indicate a database connection issue or missing data.</p>';
            echo '<button onclick="location.reload()" style="margin-top: 10px; padding: 8px 16px; background: #263c79; color: white; border: none; border-radius: 5px; cursor: pointer;">';
            echo '<i class="fas fa-redo"></i> Reload Page</button>';
            echo '</div>';
            return;
        }
        
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
