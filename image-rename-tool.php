<?php
set_time_limit(0); // Prevent timeouts for large directories
error_reporting(E_ALL);
ini_set('display_errors', 1);

function processImages($directory, $dryRun = true) {
    if (!is_dir($directory)) {
        return "<div class='error'>Error: Directory does not exist.</div>";
    }

    $images = [];
    $pattern = '/^(\d+)\s+\((\d+)\)\.(jpg|jpeg|png)$/i';
    
    foreach (scandir($directory) as $file) {
        if (preg_match($pattern, $file, $matches)) {
            $images[] = [
                'old_name' => $file,
                'new_name' => sprintf("%s-%s.%s", 
                    $matches[1], 
                    $matches[2], 
                    strtolower($matches[3]))
            ];
        }
    }

    if (empty($images)) {
        return "<div class='warning'>No matching images found. Files should match pattern like: '000028 (1).jpg'</div>";
    }

    $output = '';
    $counter = 0;
    $total = count($images);

    // Start output buffering if not already started
    if (ob_get_level() == 0) {
        ob_start();
    }

    foreach ($images as $image) {
        $counter++;
        $oldPath = $directory . DIRECTORY_SEPARATOR . $image['old_name'];
        $newPath = $directory . DIRECTORY_SEPARATOR . $image['new_name'];

        if ($dryRun) {
            $output .= "<div class='dry-run-item'>📝 DRY RUN: Would rename '<strong>{$image['old_name']}</strong>' to '<strong>{$image['new_name']}</strong>'</div>";
        } else {
            if (rename($oldPath, $newPath)) {
                $output .= "<div class='success-item'>✅ Successfully renamed '<strong>{$image['old_name']}</strong>' to '<strong>{$image['new_name']}</strong>'</div>";
            } else {
                $output .= "<div class='error-item'>❌ Error renaming '<strong>{$image['old_name']}</strong>'</div>";
            }
        }
        
        $progressPercent = round(($counter / $total) * 100);
        $output .= "<div class='progress'>Progress: {$counter}/{$total} ({$progressPercent}%)</div><br>";
        
        // Output progress and flush buffer safely
        echo $output;
        $output = ''; // Clear buffer for next iteration
        
        if (ob_get_level() > 0) {
            ob_flush();
        }
        flush();
        usleep(50000); // Small delay to show progress
    }

    return "<div class='success'>🎉 Processing complete! Total files processed: {$total}</div>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Image File Renamer Tool</title>
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            margin: 0; 
            padding: 20px; 
            background: #f0f2f5;
            color: #333;
        }
        .container { 
            max-width: 900px; 
            margin: 0 auto; 
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .header {
            background: #2c3e50;
            color: white;
            padding: 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .header .subtitle {
            opacity: 0.8;
            font-size: 14px;
            margin-top: 5px;
        }
        .content {
            padding: 25px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
        }
        input[type="text"] { 
            width: 100%; 
            padding: 12px; 
            border: 2px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            box-sizing: border-box;
            transition: border-color 0.3s;
        }
        input[type="text"]:focus {
            border-color: #3498db;
            outline: none;
        }
        .button-group {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        button { 
            flex: 1;
            padding: 12px; 
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn-dry-run {
            background: #f39c12;
            color: white;
        }
        .btn-dry-run:hover {
            background: #e67e22;
        }
        .btn-execute {
            background: #27ae60;
            color: white;
        }
        .btn-execute:hover {
            background: #219a52;
        }
        .output { 
            background: #f8f9fa; 
            padding: 20px; 
            border-radius: 5px;
            border-left: 4px solid #3498db;
            margin-top: 20px;
            max-height: 500px;
            overflow-y: auto;
            font-family: 'Courier New', monospace;
            font-size: 14px;
        }
        .success { color: #27ae60; font-weight: 600; }
        .error { color: #e74c3c; font-weight: 600; }
        .warning { color: #f39c12; font-weight: 600; }
        .success-item { color: #27ae60; margin: 5px 0; }
        .error-item { color: #e74c3c; margin: 5px 0; }
        .dry-run-item { color: #f39c12; margin: 5px 0; }
        .progress { 
            color: #3498db; 
            font-weight: 600;
            margin: 10px 0;
            padding: 5px;
            background: #ecf0f1;
            border-radius: 3px;
        }
        .info-box {
            background: #e8f4fc;
            border-left: 4px solid #3498db;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 0 5px 5px 0;
        }
        .pattern-example {
            background: #2c3e50;
            color: white;
            padding: 10px;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
            margin: 10px 0;
            display: inline-block;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🖼️ Image File Renamer Tool</h1>
            <div class="subtitle">Rename image files from "000028 (1).jpg" to "000028-1.jpg" format</div>
        </div>
        
        <div class="content">
            <div class="info-box">
                <strong>ℹ️ How to use:</strong>
                <ul>
                    <li>Enter the full path to the directory containing your images</li>
                    <li>Click <strong>Dry Run</strong> to preview changes without modifying files</li>
                    <li>Click <strong>Execute Rename</strong> to actually rename the files</li>
                    <li>Files matching this pattern will be processed: 
                        <div class="pattern-example">000028 (1).jpg → 000028-1.jpg</div>
                    </li>
                </ul>
            </div>
            
            <form method="post">
                <div class="form-group">
                    <label for="directory">📁 Directory Path:</label>
                    <input type="text" id="directory" name="directory" 
                           value="<?php echo htmlspecialchars($_POST['directory'] ?? ''); ?>" 
                           placeholder="Example: /var/www/images or C:\xampp\htdocs\images" required>
                </div>
                
                <div class="button-group">
                    <button type="submit" name="action" value="dryrun" class="btn-dry-run">🔍 Dry Run (Preview)</button>
                    <button type="submit" name="action" value="execute" class="btn-execute">⚡ Execute Rename</button>
                </div>
            </form>

            <div class="output">
                <?php
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $directory = rtrim($_POST['directory'], DIRECTORY_SEPARATOR);
                    $dryRun = ($_POST['action'] === 'dryrun');
                    
                    echo "<div class='info-box'>";
                    echo "<strong>🔧 Processing Information:</strong><br>";
                    echo "Directory: " . htmlspecialchars($directory) . "<br>";
                    echo "Mode: " . ($dryRun ? "🔍 Dry Run (Preview Only)" : "⚡ Actual Execution") . "<br>";
                    echo "</div><br>";
                    
                    $result = processImages($directory, $dryRun);
                    echo $result;
                } else {
                    echo "<div style='text-align: center; color: #7f8c8d; padding: 20px;'>";
                    echo "👆 Enter a directory path above and click a button to get started.";
                    echo "</div>";
                }
                ?>
            </div>
        </div>
    </div>
</body>
</html>