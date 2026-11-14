<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    content: { type: Object, required: true },
    portfolioUser: { type: Object, default: () => ({}) },
    hydratedData: { type: Object, default: () => ({}) },
    isEditor: { type: Boolean, default: false },
});

const tpl = computed(() => props.content.template || 'modern_sidebar');
const isDark = computed(() => tpl.value === 'tech_dark');
const exporting = ref(false);

const shareUrl = computed(() => {
    const uname = props.portfolioUser?.username || 'profil';
    return `tech237.net/@${uname}`;
});

const groupedSkills = computed(() => {
    const groups = {};
    (props.content.skills || []).forEach((s) => {
        const cat = s.category || 'Général';
        if (!groups[cat]) groups[cat] = [];
        groups[cat].push(s);
    });
    return groups;
});

async function exportPdf() {
    exporting.value = true;
    try {
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        const res = await fetch('/espace-utilisateur/portfolio-builder/cv/export-pdf', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
            },
            body: JSON.stringify(props.content),
        });
        if (!res.ok) throw new Error('Erreur PDF');
        const blob = await res.blob();
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `CV-${(props.content.fullName || 'portfolio').replace(/\s+/g, '-')}.pdf`;
        a.click();
        URL.revokeObjectURL(url);
    } catch {
        window.print();
    } finally {
        exporting.value = false;
    }
}
</script>

<template>
    <div
        class="pb-cv rounded-4 border overflow-hidden shadow-sm"
        :class="isDark ? 'bg-dark text-white border-secondary' : 'bg-white text-dark'"
    >
        <!-- Header -->
        <div
            class="p-4 d-flex flex-wrap align-items-center justify-content-between gap-3"
            :style="{
                background: tpl === 'creative_gradient'
                    ? `linear-gradient(135deg, ${content.accentColor || '#4f46e5'}, #06b6d4)`
                    : (isDark ? '#0f172a' : '#f8fafc'),
                color: tpl === 'creative_gradient' || isDark ? '#ffffff' : '#0f172a',
            }"
        >
            <div class="d-flex align-items-center gap-3">
                <img
                    v-if="content.photoUrl"
                    :src="content.photoUrl"
                    alt="Photo CV"
                    class="rounded-circle object-fit-cover border border-2 border-white shadow"
                    style="width: 76px; height: 76px;"
                />
                <div>
                    <h3 class="fw-bold mb-1">{{ content.fullName }}</h3>
                    <div class="fw-semibold" :style="{ color: tpl === 'creative_gradient' ? '#fef08a' : (content.accentColor || '#4f46e5') }">
                        {{ content.jobTitle }}
                        <span v-if="content.experienceYears" class="ms-2 badge bg-dark bg-opacity-25">
                            {{ content.experienceYears }} ans d'expérience
                        </span>
                    </div>
                    <div class="small opacity-75 mt-1">
                        {{ content.email }} <span v-if="content.phone">• {{ content.phone }}</span> <span v-if="content.location">• {{ content.location }}</span>
                    </div>
                </div>
            </div>

            <button
                type="button"
                class="btn btn-sm btn-primary rounded-pill px-3 fw-semibold"
                :disabled="exporting"
                @click.stop="exportPdf"
            >
                <span v-if="exporting" class="spinner-border spinner-border-sm me-1"></span>
                <i v-else class="bi bi-file-earmark-pdf me-1"></i>Exporter en PDF
            </button>
        </div>

        <div class="p-4">
            <p v-if="content.summary" class="mb-4 opacity-75">{{ content.summary }}</p>

            <div class="row g-4">
                <!-- Left Column: Formations & Projects -->
                <div class="col-lg-7">
                    <h6 class="fw-bold text-uppercase small mb-3" :style="{ color: content.accentColor || '#4f46e5' }">
                        <i class="bi bi-mortarboard-fill me-2"></i>Formations
                    </h6>
                    <div v-for="f in (content.formations || [])" :key="f.id" class="mb-3 ps-3 border-start border-2" :style="{ borderColor: content.accentColor || '#4f46e5' }">
                        <div class="fw-bold">{{ f.title }}</div>
                        <div class="small opacity-75">{{ f.school }} • {{ f.period }}</div>
                    </div>

                    <h6 class="fw-bold text-uppercase small mt-4 mb-3" :style="{ color: content.accentColor || '#4f46e5' }">
                        <i class="bi bi-kanban-fill me-2"></i>Travaux &amp; Projets
                    </h6>
                    <div class="row g-2">
                        <div v-for="p in (content.projects || [])" :key="p.id" class="col-12">
                            <div class="p-3 rounded-3 border" :class="isDark ? 'border-secondary bg-secondary bg-opacity-10' : 'bg-light'">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="fw-bold">{{ p.name }}</div>
                                    <a v-if="p.url" :href="isEditor ? 'javascript:void(0)' : p.url" target="_blank" class="btn btn-xs btn-outline-primary rounded-pill px-2 py-0" style="font-size:11px;">
                                        Voir <i class="bi bi-box-arrow-up-right"></i>
                                    </a>
                                </div>
                                <p class="small mb-0 opacity-75 mt-1">{{ p.description }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Categorized Skills & Hobbies -->
                <div class="col-lg-5">
                    <h6 class="fw-bold text-uppercase small mb-3" :style="{ color: content.accentColor || '#4f46e5' }">
                        <i class="bi bi-cpu-fill me-2"></i>Compétences
                    </h6>
                    <div v-for="(skills, cat) in groupedSkills" :key="cat" class="mb-3">
                        <div class="small fw-bold opacity-75 mb-1">{{ cat }}</div>
                        <div v-for="s in skills" :key="s.id" class="mb-2">
                            <div class="d-flex justify-content-between small">
                                <span>{{ s.name }}</span>
                                <span class="fw-semibold">{{ s.level }}%</span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar" :style="{ width: `${s.level}%`, backgroundColor: content.accentColor || '#4f46e5' }"></div>
                            </div>
                        </div>
                    </div>

                    <h6 class="fw-bold text-uppercase small mt-4 mb-2" :style="{ color: content.accentColor || '#4f46e5' }">
                        <i class="bi bi-heart-fill me-2"></i>Loisirs
                    </h6>
                    <div class="d-flex flex-wrap gap-2">
                        <span v-for="h in (content.hobbies || [])" :key="h.id" class="badge rounded-pill px-3 py-2 bg-light text-dark border">
                            <i v-if="h.icon" class="bi me-1 text-primary" :class="h.icon"></i>{{ h.label }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- LinkedIn / WhatsApp Share Card Preview (matches user screenshot) -->
            <div class="mt-4 pt-3 border-top">
                <span class="badge bg-light text-secondary border rounded-pill px-3 py-1 mb-2">
                    Aperçu de la carte WhatsApp / LinkedIn :
                </span>
                <div class="p-3 rounded-4 border bg-white text-dark d-flex align-items-center gap-3 shadow-sm">
                    <img
                        v-if="content.photoUrl"
                        :src="content.photoUrl"
                        alt="Avatar"
                        class="rounded-circle object-fit-cover border"
                        style="width: 52px; height: 52px;"
                    />
                    <span
                        v-else
                        class="rounded-circle d-inline-flex align-items-center justify-content-center fw-bold text-white"
                        style="width: 52px; height: 52px;"
                        :style="{ backgroundColor: content.accentColor || '#4f46e5' }"
                    >
                        {{ (content.fullName || 'P').slice(0, 2).toUpperCase() }}
                    </span>
                    <div class="lh-sm">
                        <div class="fw-bold">{{ content.fullName }} • Portfolio Tech237</div>
                        <div class="small text-secondary">{{ content.jobTitle }}</div>
                        <div class="small fw-semibold text-success">{{ shareUrl }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
