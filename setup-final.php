<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
set_time_limit(120);

echo "<!DOCTYPE html><html><head><title>Setup</title>";
echo "<style>body{font-family:Arial;padding:20px;background:#f5f5f5;}";
echo ".ok{color:green;}.err{color:red;}.warn{color:orange;}pre{background:#fff;padding:15px;border-radius:5px;}</style>";
echo "</head><body>";
echo "<h2>🎱 RenzBilliard Setup</h2><pre>";

try {
    // Step 1: Bootstrap
    echo "1. Bootstrapping Laravel...\n";
    require __DIR__.'/vendor/autoload.php';
    $app = require_once __DIR__.'/bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    echo "   <span class='ok'>✅ Bootstrap OK</span>\n\n";
    
    // Step 2: Database Connection
    echo "2. Testing database connection...\n";
    try {
        $pdo = Illuminate\Support\Facades\DB::connection()->getPdo();
        $dbName = Illuminate\Support\Facades\DB::connection()->getDatabaseName();
        echo "   <span class='ok'>✅ Connected to: {$dbName}</span>\n\n";
    } catch (Exception $e) {
        echo "   <span class='err'>❌ DB Error: " . $e->getMessage() . "</span>\n";
        throw $e;
    }
    
    // Step 3: Run Migrations
    echo "3. Running migrations...\n";
    try {
        ob_start();
        $kernel->call('migrate', ['--force' => true]);
        ob_get_clean();
        echo "   <span class='ok'>✅ Migrations completed</span>\n\n";
    } catch (Exception $e) {
        ob_end_clean();
        echo "   <span class='err'>❌ Error: " . $e->getMessage() . "</span>\n\n";
    }
    
    // Step 4: Seed Database
    echo "4. Seeding database...\n";
    try {
        ob_start();
        $kernel->call('db:seed', ['--force' => true]);
        ob_get_clean();
        echo "   <span class='ok'>✅ Seeding completed</span>\n\n";
    } catch (Exception $e) {
        ob_end_clean();
        echo "   <span class='warn'>⚠️  Seeding skipped: " . $e->getMessage() . "</span>\n\n";
    }
    
    // Step 5: Cache Config (SKIP storage:link - not supported on InfinityFree)
    echo "5. Caching configuration...\n";
    try {
        ob_start();
        $kernel->call('config:cache');
        $kernel->call('route:cache');
        ob_get_clean();
        echo "   <span class='ok'>✅ Cache created</span>\n\n";
    } catch (Exception $e) {
        ob_end_clean();
        echo "   <span class='err'>❌ Cache error: " . $e->getMessage() . "</span>\n\n";
    }
    
    // Success
    echo "================================\n";
    echo "<span class='ok'>🎉 SETUP COMPLETE!</span>\n";
    echo "================================\n\n";
    echo "Next steps:\n";
    echo "1. <strong>DELETE this setup.php file!</strong>\n";
    echo "2. Visit: <a href='/public'>https://renz-billiard.ct.ws/public</a>\n";
    echo "3. Login:\n";
    echo "   Email: admin@renzbilliard.com\n";
    echo "   Password: password\n\n";
    echo "Note: Storage link skipped (not needed for core functionality)\n";
    
} catch (Throwable $e) {
    echo "\n<span class='err'>================================</span>\n";
    echo "<span class='err'>❌ FATAL ERROR</span>\n";
    echo "<span class='err'>================================</span>\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}

echo "</pre></body></html>";
