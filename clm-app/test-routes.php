<?php
/**
 * Quick test script to verify API routes are registered
 * Run: php test-routes.php
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Bootstrap the application
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Get all routes
$routes = Route::getRoutes();

echo "=== API Routes ===\n\n";

$apiRoutes = [];
foreach ($routes as $route) {
    $uri = $route->uri();
    if (strpos($uri, 'api/') === 0 || strpos($uri, 'api') !== false) {
        $methods = implode('|', $route->methods());
        $apiRoutes[] = [
            'methods' => $methods,
            'uri' => $uri,
            'name' => $route->getName(),
            'action' => $route->getActionName(),
        ];
    }
}

if (empty($apiRoutes)) {
    echo "❌ NO API ROUTES FOUND!\n";
    echo "This means routes/api.php is not being loaded.\n\n";
} else {
    echo "✅ Found " . count($apiRoutes) . " API routes:\n\n";
    foreach ($apiRoutes as $route) {
        echo sprintf("%-8s %-40s %s\n", 
            $route['methods'], 
            $route['uri'], 
            $route['action']
        );
    }
}

echo "\n=== Testing Options Route ===\n";
$optionsRoute = null;
foreach ($routes as $route) {
    if ($route->uri() === 'api/options/{setKey}') {
        $optionsRoute = $route;
        break;
    }
}

if ($optionsRoute) {
    echo "✅ Options route found: api/options/{setKey}\n";
    echo "   Methods: " . implode(', ', $optionsRoute->methods()) . "\n";
    echo "   Action: " . $optionsRoute->getActionName() . "\n";
} else {
    echo "❌ Options route NOT found!\n";
}

echo "\n=== Testing Cases Route ===\n";
$casesRoute = null;
foreach ($routes as $route) {
    if ($route->uri() === 'api/cases') {
        $casesRoute = $route;
        break;
    }
}

if ($casesRoute) {
    echo "✅ Cases route found: api/cases\n";
    echo "   Methods: " . implode(', ', $casesRoute->methods()) . "\n";
    echo "   Action: " . $casesRoute->getActionName() . "\n";
} else {
    echo "❌ Cases route NOT found!\n";
}

