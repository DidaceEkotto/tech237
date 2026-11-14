<?php

namespace App\Multitenancy;

use App\Models\PortfolioSite;
use Illuminate\Http\Request;
use Spatie\Multitenancy\Contracts\IsTenant;
use Spatie\Multitenancy\TenantFinder\TenantFinder;

class PortfolioSiteTenantFinder extends TenantFinder
{
    public function findForRequest(Request $request): ?IsTenant
    {
        $host = $request->getHost();

        $byDomain = PortfolioSite::where('is_published', true)
            ->where('domain', $host)
            ->first();

        if ($byDomain) {
            return $byDomain;
        }

        if ($request->route('siteSlug')) {
            return PortfolioSite::where('is_published', true)
                ->where('slug', $request->route('siteSlug'))
                ->first();
        }

        return null;
    }
}
