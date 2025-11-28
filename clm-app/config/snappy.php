<?php

$defaultPdfBinary = base_path('wkhtmltopdf/bin/wkhtmltopdf.exe');
$defaultImageBinary = base_path('wkhtmltopdf/bin/wkhtmltoimage.exe');

// Resolve relative paths from .env to absolute paths
$pdfBinary = env('WKHTMLTOPDF_BINARY', $defaultPdfBinary);
$imageBinary = env('WKHTMLTOIMAGE_BINARY', $defaultImageBinary);

// If the path is relative (doesn't start with / or drive letter), make it absolute
if ($pdfBinary && !preg_match('/^([A-Z]:)?[\/\\\\]/', $pdfBinary)) {
    $pdfBinary = base_path($pdfBinary);
    // If it's a directory path (ends with /bin), append the executable name
    if (str_ends_with($pdfBinary, '/bin') || str_ends_with($pdfBinary, '\\bin')) {
        $pdfBinary .= DIRECTORY_SEPARATOR . 'wkhtmltopdf.exe';
    }
}
if ($imageBinary && !preg_match('/^([A-Z]:)?[\/\\\\]/', $imageBinary)) {
    $imageBinary = base_path($imageBinary);
    // If it's a directory path (ends with /bin), append the executable name
    if (str_ends_with($imageBinary, '/bin') || str_ends_with($imageBinary, '\\bin')) {
        $imageBinary .= DIRECTORY_SEPARATOR . 'wkhtmltoimage.exe';
    }
}

return [
    'pdf' => [
        'enabled' => true,
        'binary' => $pdfBinary,
        'timeout' => false,
        'options' => [
            'encoding' => 'utf-8',
            'margin-top' => 10,
            'margin-right' => 10,
            'margin-bottom' => 10,
            'margin-left' => 10,
            'orientation' => 'Portrait',
            'title' => config('app.name') . ' Report',
            'enable-local-file-access' => true,
        ],
        'env' => [],
    ],
    'image' => [
        'enabled' => true,
        'binary' => $imageBinary,
        'timeout' => false,
        'options' => [],
        'env' => [],
    ],
];

