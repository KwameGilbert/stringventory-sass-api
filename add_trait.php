<?php
$dir = 'src/models/';
$files = scandir($dir);
$modelsToSkip = ['Business.php', 'Plan.php', 'Subscription.php', 'EmailVerificationToken.php', 'PasswordReset.php', 'RefreshToken.php', 'PushSubscription.php'];

foreach ($files as $file) {
    if (strpos($file, '.php') === false) continue;
    if (in_array($file, $modelsToSkip)) continue;
    
    $path = $dir . $file;
    $content = file_get_contents($path);
    
    // Check if it's already using Tenantable
    if (strpos($content, 'use \App\Traits\Tenantable;') !== false) continue;
    
    // Check if the model has a businessId column in phpdoc or fillable to be sure it's tenantable
    if (strpos($content, 'businessId') !== false) {
        // Insert the trait at the beginning of the class
        $content = preg_replace('/(class\s+[a-zA-Z0-9_]+\s+extends\s+Model(?:[^{]*)\{)/s', "$1\n    use \App\Traits\Tenantable;\n", $content);
        file_put_contents($path, $content);
        echo "Updated $file\n";
    }
}
