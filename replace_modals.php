<?php

function replaceModal($file, $replacement) {
    $content = file_get_contents($file);
    
    // Find the start of the modal
    $startToken = '<div x-show="showAiModal" class="modal-overlay"';
    // Find the end of the modal (we know it ends before the script tag or main div closure)
    // Actually, looking at the code, it ends with:
    //             </div>
    //         </div>
    //     </div>
    // We can use a regex or string extraction.
    $startPos = strpos($content, '<!-- AI Auto-Fill Modal (Advanced Split Pane) -->');
    if ($startPos === false) {
        $startPos = strpos($content, '<div x-show="showAiModal" class="modal-overlay"');
    }
    
    if ($startPos !== false) {
        // Find the matching end div
        $divCount = 0;
        $inModal = false;
        $endPos = false;
        
        for ($i = $startPos; $i < strlen($content); $i++) {
            if (substr($content, $i, 4) === '<div') {
                $divCount++;
                $inModal = true;
            } elseif (substr($content, $i, 6) === '</div') {
                $divCount--;
            }
            
            if ($inModal && $divCount === 0) {
                // Found the end of the main modal div
                $endPos = strpos($content, '>', $i) + 1;
                break;
            }
        }
        
        if ($endPos !== false) {
            $before = substr($content, 0, $startPos);
            $after = substr($content, $endPos);
            file_put_contents($file, $before . $replacement . "\n" . $after);
            echo "Replaced in $file\n";
        } else {
            echo "Failed to find end div in $file\n";
        }
    } else {
        echo "Failed to find start in $file\n";
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

