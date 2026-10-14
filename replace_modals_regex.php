<?php

function replaceModal($file, $replacement) {
    $content = file_get_contents($file);
    
    // Use regex to find the modal block.
    // It starts with <!-- AI Auto-Fill Modal (Advanced Split Pane) -->
    // and ends with </div>\n        </div>\n    </div> (3 closing divs) or similar
    // Since it's a large chunk, we can capture from the comment to the exact closing divs.
    
    $pattern = '/\s*<!-- AI Auto-Fill Modal \(Advanced Split Pane\) -->.*?<div x-show="showAiModal".*?<div class="modal-footer".*?<\/button>\s*<\/div>\s*<\/div>\s*<\/div>/s';
    
    if (preg_match($pattern, $content, $matches)) {
        $content = str_replace($matches[0], "\n        " . $replacement . "\n", $content);
        file_put_contents($file, $content);
        echo "Successfully replaced in $file\n";
    } else {
        echo "Failed to match regex in $file\n";
    }
}

replaceModal('resources/views/ocean-import/index.blade.php', "@include('partials.ai-modal')");
replaceModal('resources/views/air-import/index.blade.php', "@include('partials.ai-modal')");
replaceModal('resources/views/ocean-export/index.blade.php', "@include('partials.ai-modal', [
    'loadingVar' => 'aiLoading',
    'uploadMethod' => 'uploadAiDocument',
    'triggerMethod' => 'uploadAiDocument()',
    'fileInputRef' => '',
    'fileInputId' => 'aiDocumentFile'
])");

