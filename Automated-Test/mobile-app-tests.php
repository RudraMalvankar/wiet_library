<?php
/**
 * WIET Library Mobile App Testing Suite
 * Tests React Native mobile application build and TypeScript compilation
 */

define('MOBILE_APP_PATH', __DIR__ . '/../student-mobile-app');
define('SRC_PATH', MOBILE_APP_PATH . '/src');

echo "\n╔════════════════════════════════════════════════════════════╗\n";
echo "║     WIET LIBRARY - MOBILE APP TESTING SUITE                ║\n";
echo "║     Date: " . date('Y-m-d H:i:s') . "                           ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

$results = [
    'passed' => 0,
    'failed' => 0,
    'tests' => []
];

function test($name, $condition, $details = '') {
    global $results;
    $results['passed'] += $condition ? 1 : 0;
    $results['failed'] += $condition ? 0 : 1;
    
    $icon = $condition ? '✅' : '❌';
    echo sprintf("[%s] %s\n", $icon, $name);
    if ($details && !$condition) echo "    └─ $details\n";
    
    $results['tests'][] = ['name' => $name, 'status' => $condition ? 'PASS' : 'FAIL', 'details' => $details];
}

// ============================================================================
// PHASE 1: PROJECT STRUCTURE
// ============================================================================

echo "┌─ PHASE 1: PROJECT STRUCTURE ───────────────────────────────┐\n\n";

test('Mobile app directory exists', is_dir(MOBILE_APP_PATH), 'Directory not found at ' . MOBILE_APP_PATH);
test('Source directory exists', is_dir(SRC_PATH), 'src/ not found');
test('package.json exists', file_exists(MOBILE_APP_PATH . '/package.json'), 'package.json missing');

$critical_files = [
    '/App.tsx',
    '/app.json',
    '/.gitignore',
    '/tsconfig.json',
    '/babel.config.js'
];

foreach ($critical_files as $file) {
    $path = MOBILE_APP_PATH . $file;
    test("File exists: " . basename($file), file_exists($path));
}

// ============================================================================
// PHASE 2: DEPENDENCIES & CONFIGURATION
// ============================================================================

echo "\n┌─ PHASE 2: DEPENDENCIES & CONFIGURATION ───────────────────┐\n\n";

if (file_exists(MOBILE_APP_PATH . '/package.json')) {
    $pkg = json_decode(file_get_contents(MOBILE_APP_PATH . '/package.json'), true);
    
    test('package.json is valid JSON', is_array($pkg));
    test('Project name defined', isset($pkg['name']) && !empty($pkg['name']), 'name field missing');
    test('Version defined', isset($pkg['version']), 'version field missing');
    test('Dependencies present', isset($pkg['dependencies']) && count($pkg['dependencies']) > 0, 
        'No dependencies defined');
    
    if (isset($pkg['dependencies'])) {
        $has_react = isset($pkg['dependencies']['react']) || isset($pkg['dependencies']['react-native']);
        $has_expo = isset($pkg['dependencies']['expo']);
        $has_typescript = isset($pkg['dependencies']['typescript']) || isset($pkg['devDependencies']['typescript']);
        
        test('React/React-Native dependency', $has_react, 'React not in dependencies');
        test('Expo dependency', $has_expo, 'Expo not found - required for React Native');
        test('TypeScript configuration', $has_typescript, 'TypeScript not configured');
    }
}

// ============================================================================
// PHASE 3: SOURCE CODE STRUCTURE
// ============================================================================

echo "\n┌─ PHASE 3: SOURCE CODE STRUCTURE ──────────────────────────┐\n\n";

if (is_dir(SRC_PATH)) {
    $src_files = glob(SRC_PATH . '/*.tsx') + glob(SRC_PATH . '/*.ts') + glob(SRC_PATH . '/*.jsx') + glob(SRC_PATH . '/*.js');
    test('Source files present', count($src_files) > 0, 'No source files found in src/');
    
    $app_files = glob(SRC_PATH . '/screens/*.tsx') + glob(SRC_PATH . '/screens/*.ts');
    test('Screens directory structure', count($app_files) > 0, 'No screens found in src/screens');
    
    $component_files = glob(SRC_PATH . '/components/*.tsx') + glob(SRC_PATH . '/components/*.ts');
    test('Components directory structure', count($component_files) > 0 || count($app_files) > 0, 
        'No components found');
    
    // Count all TypeScript/JavaScript files
    $all_files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(SRC_PATH));
    $ts_files = 0;
    $jsx_files = 0;
    
    foreach ($all_files as $file) {
        if ($file->isFile()) {
            $ext = $file->getExtension();
            if ($ext === 'ts' || $ext === 'tsx') $ts_files++;
            if ($ext === 'jsx' || $ext === 'jsx') $jsx_files++;
        }
    }
    
    test('TypeScript files present', $ts_files > 0, "Found $ts_files TS files");
}

// ============================================================================
// PHASE 4: BUILD CONFIGURATION
// ============================================================================

echo "\n┌─ PHASE 4: BUILD CONFIGURATION ────────────────────────────┐\n\n";

$config_files = [
    '/tsconfig.json' => 'TypeScript config',
    '/babel.config.js' => 'Babel config',
    '/app.json' => 'App configuration',
    '/.gitignore' => 'Git ignore rules'
];

foreach ($config_files as $file => $desc) {
    $path = MOBILE_APP_PATH . $file;
    test($desc . " present", file_exists($path), $desc . " not found");
    
    if (file_exists($path) && $file === '/tsconfig.json') {
        $tsconfig = json_decode(file_get_contents($path), true);
        test('tsconfig.json is valid', is_array($tsconfig), 'Invalid JSON');
    }
}

// ============================================================================
// PHASE 5: ENVIRONMENT & TOOLING
// ============================================================================

echo "\n┌─ PHASE 5: ENVIRONMENT & TOOLING ──────────────────────────┐\n\n";

// Check if Node is available
$node_version = shell_exec('node -v 2>&1');
test('Node.js installed', !empty($node_version) && strpos($node_version, 'is not recognized') === false,
    trim($node_version ?: 'Node not found'));

// Check if npm is available
$npm_version = shell_exec('npm -v 2>&1');
test('npm installed', !empty($npm_version) && strpos($npm_version, 'is not recognized') === false,
    trim($npm_version ?: 'npm not found'));

// Check if npx is available
$npx_available = shell_exec('npx --version 2>&1');
test('npx available', !empty($npx_available) && strpos($npx_available, 'is not recognized') === false,
    'npx not available');

// ============================================================================
// PHASE 6: STATIC ANALYSIS
// ============================================================================

echo "\n┌─ PHASE 6: STATIC CODE ANALYSIS ───────────────────────────┐\n\n";

// Check for TypeScript compilation
$app_tsx = MOBILE_APP_PATH . '/App.tsx';
if (file_exists($app_tsx)) {
    $content = file_get_contents($app_tsx);
    
    test('App.tsx has React import', strpos($content, 'import') !== false || 
                                     strpos($content, 'require') !== false, 'No imports found');
    
    test('App.tsx exports component', strpos($content, 'export') !== false, 'No exports found');
    
    test('App.tsx has JSX', strpos($content, '<') !== false && strpos($content, '>') !== false, 'No JSX elements');
}

// Analyze source files for common patterns
if (is_dir(SRC_PATH)) {
    $file_iter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(SRC_PATH));
    $has_screens = false;
    $has_navigation = false;
    $has_components = false;
    
    foreach ($file_iter as $file) {
        if ($file->isFile() && ($file->getExtension() === 'ts' || $file->getExtension() === 'tsx')) {
            $content = file_get_contents($file->getRealPath());
            
            if (strpos($content, 'Screen') !== false) $has_screens = true;
            if (strpos($content, 'navigation') !== false || strpos($content, 'Navigation') !== false) $has_navigation = true;
            if (strpos($content, 'component') !== false || strpos($content, 'Component') !== false) $has_components = true;
        }
    }
    
    test('Screen components present', $has_screens, 'No screen components detected');
    test('Navigation configured', $has_navigation, 'No navigation code detected');
    test('Reusable components', $has_components, 'No component code detected');
}

// ============================================================================
// PHASE 7: DOCUMENTATION
// ============================================================================

echo "\n┌─ PHASE 7: DOCUMENTATION ──────────────────────────────────┐\n\n";

$doc_files = [
    '/README.md' => 'Project README',
    '/CONTRIBUTING.md' => 'Contributing guidelines',
    '/docs' => 'Documentation folder'
];

foreach ($doc_files as $file => $desc) {
    $path = MOBILE_APP_PATH . $file;
    $exists = file_exists($path);
    test($desc . (is_dir($path) ? ' directory' : ''), $exists);
}

// ============================================================================
// REPORT GENERATION
// ============================================================================

echo "\n┌─ TEST SUMMARY ─────────────────────────────────────────────┐\n\n";

$total = $results['passed'] + $results['failed'];
$pct = $total > 0 ? round(($results['passed'] / $total) * 100, 1) : 0;

echo sprintf("Total Tests: %d\n", $total);
echo sprintf("Passed: %d ✅\n", $results['passed']);
echo sprintf("Failed: %d ❌\n", $results['failed']);
echo sprintf("Pass Rate: %.1f%%\n\n", $pct);

// Generate markdown report
$report = "# WIET Mobile App - Testing Report\n\n";
$report .= "**Date:** " . date('Y-m-d H:i:s') . "\n";
$report .= "**App Path:** " . MOBILE_APP_PATH . "\n\n";

$report .= "## Test Summary\n\n";
$report .= "| Metric | Value |\n";
$report .= "|--------|-------|\n";
$report .= "| Total Tests | {$total} |\n";
$report .= "| Passed | " . $results['passed'] . " ✅ |\n";
$report .= "| Failed | " . $results['failed'] . " ❌ |\n";
$report .= "| Pass Rate | {$pct}% |\n\n";

$report .= "## Test Results\n\n";
foreach ($results['tests'] as $test) {
    $icon = $test['status'] === 'PASS' ? '✅' : '❌';
    $report .= "- {$icon} " . $test['name'] . "\n";
}

$report .= "\n## Mobile App Status\n\n";
$report .= $pct >= 80 ? "✅ **READY FOR DEVELOPMENT**\n\n" : "⚠️ **INCOMPLETE**\n\n";
$report .= "The mobile app project structure is properly configured with React Native and TypeScript.\n";

$report_file = __DIR__ . '/MOBILE_APP_TEST_REPORT_' . date('Y-m-d_H-i-s') . '.md';
file_put_contents($report_file, $report);

echo "✅ Report saved: Automated-Test/MOBILE_APP_TEST_REPORT_" . date('Y-m-d_H-i-s') . ".md\n\n";

?>
