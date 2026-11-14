<script setup>
import { ref, computed } from 'vue';
import apiClient from '@/Composables/PortfolioBuilder/apiClient';

const props = defineProps({
    content: { type: Object, required: true },
    portfolioUser: { type: Object, default: () => ({}) },
    isEditor: { type: Boolean, default: false },
});

const tpl = computed(() => props.content.template || 'split_info_form');
const isDark = computed(() => tpl.value === 'dark_neon');

const form = ref({
    name: '',
    email: '',
    subject: '',
    message: '',
});
const sending = ref(false);
const feedback = ref('');
const errorMsg = ref('');

async function submitContact() {
    if (props.isEditor) {
        feedback.value = 'Aperçu éditeur : le formulaire enverra les messages en ligne.';
        return;
    }
    if (!props.portfolioUser?.username) return;
    sending.value = true;
    feedback.value = '';
    errorMsg.value = '';
    try {
        await apiClient.post(`/portfolio/${props.portfolioUser.username}/contact`, form.value);
        feedback.value = 'Message envoyé avec succès !';
        form.value = { name: '', email: '', subject: '', message: '' };
    } catch (e) {
        errorMsg.value = e.response?.data?.message || 'Erreur lors de l’envoi du message.';
    } finally {
        sending.value = false;
    }
}
</script>

<template>
    <div
        class="pb-contact rounded-4 p-4 p-md-5 border"
        :class="isDark ? 'bg-dark text-white border-secondary' : 'bg-white text-dark'"
    >
        <div class="row g-4 align-items-center">
            <div :class="tpl === 'centered_card' ? 'col-12 text-center' : 'col-lg-5'">
                <span v-if="tpl === 'availability_badge'" class="badge bg-success bg-opacity-10 text-success border border-success-subtle rounded-pill px-3 py-1 mb-2">
                    <i class="bi bi-circle-fill me-1 small"></i>Disponible maintenant
                </span>
                <h3 class="fw-bold mb-2">{{ content.title }}</h3>
                <p class="mb-4" :class="isDark ? 'text-light opacity-75' : 'text-secondary'">{{ content.subtitle }}</p>

                <div class="d-flex flex-column gap-2 small" :class="{ 'align-items-center': tpl === 'centered_card' }">
                    <div v-if="content.email"><i class="bi bi-envelope-fill text-primary me-2"></i>{{ content.email }}</div>
                    <div v-if="content.phone"><i class="bi bi-telephone-fill text-primary me-2"></i>{{ content.phone }}</div>
                    <div v-if="content.location"><i class="bi bi-geo-alt-fill text-primary me-2"></i>{{ content.location }}</div>
                </div>
            </div>

            <div :class="tpl === 'centered_card' ? 'col-12 col-md-8 mx-auto' : 'col-lg-7'">
                <form class="row g-3" @submit.prevent="submitContact">
                    <div class="col-md-6">
                        <input v-model="form.name" type="text" required class="form-control rounded-3" placeholder="Votre nom" />
                    </div>
                    <div class="col-md-6">
                        <input v-model="form.email" type="email" required class="form-control rounded-3" placeholder="Votre email" />
                    </div>
                    <div class="col-12">
                        <input v-model="form.subject" type="text" class="form-control rounded-3" placeholder="Sujet du message" />
                    </div>
                    <div class="col-12">
                        <textarea v-model="form.message" rows="3" required class="form-control rounded-3" placeholder="Décrivez votre besoin..."></textarea>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold" :disabled="sending">
                            <span v-if="sending" class="spinner-border spinner-border-sm me-2"></span>
                            {{ content.buttonLabel || 'Envoyer' }}
                        </button>
                    </div>
                </form>
                <div v-if="feedback" class="alert alert-success mt-3 py-2 small mb-0">{{ feedback }}</div>
                <div v-if="errorMsg" class="alert alert-danger mt-3 py-2 small mb-0">{{ errorMsg }}</div>
            </div>
        </div>
    </div>
</template>
