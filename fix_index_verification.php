<?php
$content = file_get_contents('app/Http/Controllers/InvoiceController.php');

$search = <<<EOD
    public function index(Request \$request)
    {
        \$companyId = auth()->user()->company_id ?? 1;
        \$query = Invoice::with(['billTo', 'currency', 'office', 'issuer'])->where('company_id', \$companyId);
EOD;

$replace = <<<EOD
    public function index(Request \$request)
    {
        \$companyId = auth()->user()->company_id ?? 1;
        // Only show verified invoices in the list
        \$query = Invoice::with(['billTo', 'currency', 'office', 'issuer'])
            ->where('company_id', \$companyId)
            ->where('verification_status', 'Verified');
EOD;

$newContent = str_replace($search, $replace, $content);
file_put_contents('app/Http/Controllers/InvoiceController.php', $newContent);
echo "Replaced in InvoiceController.php\n";
