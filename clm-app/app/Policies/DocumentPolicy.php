<?php

namespace App\Policies;

use App\Models\User;

class DocumentPolicy
{
    /**
     * Determine whether the user can view any documents.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('documents.view');
    }

    /**
     * Determine whether the user can view the document.
     */
    public function view(User $user, $document): bool
    {
        return $user->can('documents.view');
    }

    /**
     * Determine whether the user can upload documents.
     */
    public function upload(User $user): bool
    {
        return $user->can('documents.upload');
    }

    /**
     * Determine whether the user can download the document.
     */
    public function download(User $user, $document): bool
    {
        return $user->can('documents.download');
    }

    /**
     * Determine whether the user can delete the document.
     */
    public function delete(User $user, $document): bool
    {
        return $user->can('documents.delete');
    }
}
