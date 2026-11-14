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
        $host = strtolower($request->getHost());

        $byDomain = PortfolioSite::where('is_published', true)
            ->where('domain', $host)
            ->first();

        if ($byDomain) {
            return $byDomain;
        }

        // Subdomain matching: {slug}.tech237.com or {slug}.localhost
        $appHost = strtolower(preg_replace('/^www\./', '', (string) parse_url((string) config('app.url'), PHP_URL_HOST)));
        foreach (array_filter([$appHost, 'tech237.com', 'localhost']) as $baseHost) {
            $suffix = '.' . $baseHost;
            if (str_ends_with($host, $suffix)) {
                $sub = substr($host, 0, -strlen($suffix));
                if ($sub && !in_array($sub, ['www', 'admin', 'api'], true) && !str_contains($sub, '.')) {
                    $bySub = PortfolioSite::where('is_published', true)->where('slug', $sub)->first();
                    if ($bySub) {
                        return $bySub;
                    }
                }
            }
        }

        if ($request->route('siteSlug')) {
            return PortfolioSite::where('is_published', true)
                ->where('slug', $request->route('siteSlug'))
                ->first();
        }

        return null;
    }
}
