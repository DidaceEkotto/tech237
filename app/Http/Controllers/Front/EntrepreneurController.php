<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use App\Models\Entrepreneur;

class EntrepreneurController extends Controller
{
    public function showCurrent()
    {
        $entrepreneur = Entrepreneur::with(['startup.projects'])
            ->where('is_active', true)
            ->latest()
            ->first();

        if (!$entrepreneur) {
            // Create a default featured entrepreneur if none exists yet
            $entrepreneur = Entrepreneur::create([
                'name' => 'Arthur Zang',
                'role_title' => 'CEO & Fondateur - Himore Medical',
                'photo' => 'assets/images/author-default.png',
                'month_year' => 'Novembre 2026',
                'bio_fr' => 'Ingénieur camerounais et pionnier de la medtech en Afrique, inventeur du Cardiopad, la première tablette médicale d\'Afrique permettant de réaliser des électrocardiogrammes à distance.',
                'bio_en' => 'Cameroonian engineer and pioneer in African medtech, inventor of the Cardiopad.',
                'story_fr' => "Né à Mbankomo au Cameroun, Arthur Zang a conçu le Cardiopad après avoir constaté le manque criant de cardiologues dans les zones rurales camerounaises. Avec passion et détermination, il a prototypé l'appareil en assemblant lui-même les circuits électroniques avant de lever des fonds et d'industrialiser sa production locale.",
                'difficulties_fr' => "Le principal défi a été l'accès aux financements de prototypage et le manque de composants électroniques de pointe sur le marché local. Arthur a surmonté ces obstacles grâce à des partenariats internationaux et une persévérance exemplaire.",
                'is_active' => true,
            ]);
        }

        $previousEntrepreneurs = Entrepreneur::where('is_active', true)
            ->where('id', '!=', $entrepreneur->id)
            ->latest()
            ->take(6)
            ->get();

        return Inertia::render('Entrepreneurs/Show', [
            'entrepreneur' => $entrepreneur,
            'previousEntrepreneurs' => $previousEntrepreneurs,
        ]);
    }

    public function show(int $id)
    {
        $entrepreneur = Entrepreneur::with(['startup.projects'])
            ->where('is_active', true)
            ->find($id);

        if (!$entrepreneur) {
            return $this->showCurrent();
        }

        $previousEntrepreneurs = Entrepreneur::where('is_active', true)
            ->where('id', '!=', $entrepreneur->id)
            ->latest()
            ->take(6)
            ->get();

        return Inertia::render('Entrepreneurs/Show', [
            'entrepreneur' => $entrepreneur,
            'previousEntrepreneurs' => $previousEntrepreneurs,
        ]);
    }
}
