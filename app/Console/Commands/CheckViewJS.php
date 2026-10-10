<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\View;

class CheckViewJS extends Command
{
    protected $signature = 'check:viewjs';
    protected $description = 'Render ocean-export.index and extract JS for syntax check';

    public function handle()
    {
        // Mock data to prevent undefined variable errors
        $oceanExport = new \App\Models\OceanExport();
        $oceanExport->id = 1;
        $oceanExport->is_direct_master = false;
        
        $html = View::make('ocean-export.index', [
            'oceanExport' => $oceanExport,
            'page' => 'edit',
            'businessReferredBy' => [],
            'salesTypes' => [],
            'tradePartners' => [],
            'billTypes' => [],
            'offices' => [],
            'carriers' => [],
            'cargoTypes' => [],
            'vessels' => [],
            'locations' => [],
            'freightTerms' => [],
            'incoterms' => [],
            'packageUnits' => [],
            'containerTypes' => [],
            'truckers' => [],
        ])->render();

        preg_match_all('/<script>(.*?)<\/script>/s', $html, $matches);
        
        foreach($matches[1] as $idx => $script) {
            file_put_contents("storage/app/script_{$idx}.js", $script);
            $this->info("Extracted script {$idx}");
        }
    }
}
