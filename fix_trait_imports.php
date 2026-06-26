<?php
$dir = 'src/models/';
$files = scandir($dir);

foreach ($files as $file) {
    if (strpos($file, '.php') === false) continue;
    
    $path = $dir . $file;
    $content = file_get_contents($path);
    
    $changed = false;
    
    // Check if it uses Tenantable inside the class
    if (strpos($content, 'use Tenantable;') !== false || strpos($content, 'use \App\Traits\Tenantable;') !== false) {
        
        // Ensure the trait is imported at the top
        if (strpos($content, 'use App\Traits\Tenantable;') === false) {
            // Replace the use Illuminate... line with both imports, ignoring line endings
            $content = preg_replace('/use Illuminate\\\\Database\\\\Eloquent\\\\Model;/', "use App\Traits\Tenantable;\nuse Illuminate\Database\Eloquent\Model;", $content);
            $changed = true;
        }

        // Ensure the inside class trait call is correct (no fully qualified namespace)
        if (strpos($content, 'use \App\Traits\Tenantable;') !== false) {
            $content = str_replace('use \App\Traits\Tenantable;', 'use Tenantable;', $content);
            $changed = true;
        }
    }

    if ($changed) {
        file_put_contents($path, $content);
        echo "Updated $file\n";
    }
}
