<?php

namespace App\Support\DeletionBundles\Collectors;

use Illuminate\Database\Eloquent\Model;

class DocumentCollector extends BaseCollector
{
    public function collect(Model $model): array
    {
        $fileDescriptor = $this->getDocumentFileDescriptor($model);

        return [
            'document' => [
                'attributes' => $this->getAttributes($model),
                'references' => [
                    'client_id' => $model->client_id,
                    'matter_id' => $model->matter_id,
                ],
                'file' => $fileDescriptor,
            ],
        ];
    }

    public function getRootLabel(Model $model): string
    {
        $desc = $model->document_description ?? 'Document';
        return substr($desc, 0, 60) . (strlen($desc) > 60 ? '...' : '');
    }

    public function getFileDescriptors(array $snapshot): array
    {
        $files = [];

        if (isset($snapshot['document']['file']) && !empty($snapshot['document']['file'])) {
            $files[] = $snapshot['document']['file'];
        }

        return $files;
    }

    /**
     * Get file descriptor for a document.
     * This would be implemented based on your actual file storage structure.
     */
    protected function getDocumentFileDescriptor(Model $document): array
    {
        // Placeholder - implement based on your file storage strategy
        // Example:
        // if ($document->file_path) {
        //     return $this->createFileDescriptor(
        //         'secure',
        //         $document->file_path,
        //         $document->file_size,
        //         $document->mime_type
        //     );
        // }

        return [];
    }
}

