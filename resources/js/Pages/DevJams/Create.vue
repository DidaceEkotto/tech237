<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import FrontLayout from '@/Layouts/FrontLayout.vue';

const now = new Date();
const nextWeek = new Date(now.getTime() + 7 * 24 * 60 * 60 * 1000);

function toLocalInput(d) {
    const pad = (n) => String(n).padStart(2, '0');
    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
}

const form = useForm({
    title: '',
    theme: '',
    description: '',
    rules: '',
    starts_at: toLocalInput(now),
    ends_at: toLocalInput(nextWeek),
    prize_text: '',
    allow_external_links: true,
});

function submit() {
    form.post('/dev-jams/creer');
}
</script>

<template>
    <Head title="Créer une Dev Jam Communautaire • Tech237" />
    <FrontLayout>
        <div class="container py-5" style="max-width: 780px;">
            <div class="mb-3">
                <Link href="/dev-jams" class="text-decoration-none small fw-semibold">
                    <i class="bi bi-arrow-left me-1"></i>Retour aux Dev Jams
                </Link>
            </div>

            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-primary text-white p-4">
                    <span class="badge bg-white text-primary rounded-pill mb-2">Mode Jam Communautaire</span>
                    <h3 class="fw-bold mb-1">Lancer une Dev Jam sur Tech237</h3>
                    <p class="small mb-0 opacity-75">
                        Chaque participant disposera de l'éditeur HTML/CSS/JS sécurisé, de l'aperçu en direct et du Live Chat.
                    </p>
                </div>

                <form class="card-body p-4" @submit.prevent="submit">
                    <div class="row g-3">
                        <div class="col-md-7">
                            <label class="form-label fw-semibold small">Titre de la Jam <span class="text-danger">*</span></label>
                            <input v-model="form.title" type="text" class="form-control" placeholder="ex: Challenge UI Dashboard en 48h" required />
                            <div v-if="form.errors.title" class="text-danger small mt-1">{{ form.errors.title }}</div>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-semibold small">Thème / Mot-clé</label>
                            <input v-model="form.theme" type="text" class="form-control" placeholder="ex: Néon, Santé, Fintech..." />
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Date &amp; heure de début <span class="text-danger">*</span></label>
                            <input v-model="form.starts_at" type="datetime-local" class="form-control" required />
                            <div v-if="form.errors.starts_at" class="text-danger small mt-1">{{ form.errors.starts_at }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold small">Date &amp; heure de fin <span class="text-danger">*</span></label>
                            <input v-model="form.ends_at" type="datetime-local" class="form-control" required />
                            <div v-if="form.errors.ends_at" class="text-danger small mt-1">{{ form.errors.ends_at }}</div>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold small">Description du défi <span class="text-danger">*</span></label>
                            <textarea v-model="form.description" rows="4" class="form-control" placeholder="Expliquez l'objectif, le public cible et ce que les participants doivent construire..." required></textarea>
                            <div v-if="form.errors.description" class="text-danger small mt-1">{{ form.errors.description }}</div>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold small">Règlement &amp; critères</label>
                            <textarea v-model="form.rules" rows="3" class="form-control" placeholder="ex: Code original uniquement, pas de framework lourd, design responsive..."></textarea>
                        </div>

                        <div class="col-md-7">
                            <label class="form-label fw-semibold small">Récompense / Mention (optionnel)</label>
                            <input v-model="form.prize_text" type="text" class="form-control" placeholder="ex: Mise en avant + Badge Communauté" />
                        </div>
                        <div class="col-md-5 d-flex align-items-end pb-2">
                            <label class="form-check-label small fw-semibold">
                                <input v-model="form.allow_external_links" type="checkbox" class="form-check-input me-1" />
                                Autoriser aussi les liens GitHub / Démo externes
                            </label>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-top d-flex justify-content-end gap-2">
                        <Link href="/dev-jams" class="btn btn-outline-secondary rounded-pill px-4">Annuler</Link>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold" :disabled="form.processing">
                            <span v-if="form.processing" class="spinner-border spinner-border-sm me-1"></span>
                            Publier la Dev Jam
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </FrontLayout>
</template>
