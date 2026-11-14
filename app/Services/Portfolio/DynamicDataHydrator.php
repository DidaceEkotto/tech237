<?php

namespace App\Services\Portfolio;

use App\Models\Form;
use App\Models\HeartbeatMonitor;
use App\Models\User;

class DynamicDataHydrator
{
    public function hydrateForUser(User $user): array
    {
        $projects = $user->projects()
            ->where('status', 'published')
            ->latest()
            ->get();

        $pocs = $user->forumTopics()
            ->with('rubrique')
            ->latest()
            ->take(12)
            ->get();

        $badges = $user->badges()
            ->latest()
            ->get();

        $publicForms = Form::where('user_id', $user->id)
            ->where('status', 'published')
            ->select('id', 'title', 'slug', 'description', 'theme_color')
            ->latest()
            ->get();

        $publicMonitors = HeartbeatMonitor::where('user_id', $user->id)
            ->where('is_active', true)
            ->select('id', 'name', 'url', 'is_currently_down', 'uptime_percentage', 'last_response_time_ms')
            ->latest()
            ->get();

        return [
            'projects' => $projects,
            'pocs' => $pocs,
            'badges' => $badges,
            'publicForms' => $publicForms,
            'publicMonitors' => $publicMonitors,
        ];
    }
}
