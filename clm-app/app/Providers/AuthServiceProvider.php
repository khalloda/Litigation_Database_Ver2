<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        \App\Models\Client::class => \App\Policies\ClientPolicy::class,
        \App\Models\CaseModel::class => \App\Policies\CasePolicy::class,
        \App\Models\Hearing::class => \App\Policies\HearingPolicy::class,
        \App\Models\Lawyer::class => \App\Policies\LawyerPolicy::class,
        \App\Models\EngagementLetter::class => \App\Policies\EngagementLetterPolicy::class,
        \App\Models\Contact::class => \App\Policies\ContactPolicy::class,
        \App\Models\PowerOfAttorney::class => \App\Policies\PowerOfAttorneyPolicy::class,
        \App\Models\ClientDocument::class => \App\Policies\DocumentPolicy::class,
        \App\Models\DeletionBundle::class => \App\Policies\TrashPolicy::class,
        \App\Models\AdminTask::class => \App\Policies\AdminTaskPolicy::class,
        \App\Models\AdminSubtask::class => \App\Policies\AdminSubtaskPolicy::class,
        \App\Models\ImportSession::class => \App\Policies\ImportSessionPolicy::class,
        \App\Models\OptionSet::class => \App\Policies\OptionSetPolicy::class,
        \App\Models\OptionValue::class => \App\Policies\OptionValuePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        //
    }
}
