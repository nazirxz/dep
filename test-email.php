<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\Mail;

// Load Laravel environment
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    Mail::raw('Test email from UDKS - udkeluargasehati.com', function ($message) {
        $message->to('lizamnaufal4@gmail.com')
                ->subject('Test Email from udkeluargasehati.com')
                ->from('no-reply@udkeluargasehati.com', 'UDKS App');
    });
    
    echo "✅ Email sent successfully!\n";
} catch (Exception $e) {
    echo "❌ Email failed: " . $e->getMessage() . "\n";
}
?>