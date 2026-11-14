<?php

use App\Models\PortfolioSite;
use App\Multitenancy\PortfolioSiteTenantFinder;
use Spatie\Multitenancy\Actions\ForgetCurrentTenantAction;
use Spatie\Multitenancy\Actions\MakeQueueTenantAwareAction;
use Spatie\Multitenancy\Actions\MakeTenantCurrentAction;
use Spatie\Multitenancy\Actions\MigrateTenantAction;

return [
    'tenant_finder' => PortfolioSiteTenantFinder::class,
    'tenant_artisan_search_fields' => ['id', 'slug', 'domain'],
    'switch_tenant_tasks' => [],
    'tenant_model' => PortfolioSite::class,
    'ignore_sequential_ids' => false,
    'queues_are_tenant_aware_by_default' => false,
    'tenant_database_connection_name' => null,
    'landlord_database_connection_name' => null,
    'current_tenant_context_key' => 'tenantId',
    'current_tenant_container_key' => 'currentTenant',
    'shared_routes_cache' => false,
    'actions' => [
        'make_tenant_current_action' => MakeTenantCurrentAction::class,
        'forget_current_tenant_action' => ForgetCurrentTenantAction::class,
        'make_queue_tenant_aware_action' => MakeQueueTenantAwareAction::class,
        'migrate_tenant_action' => MigrateTenantAction::class,
    ],
    'queueable_to_job' => [],
    'tenant_aware_jobs' => [],
    'not_tenant_aware_jobs' => [],
];
