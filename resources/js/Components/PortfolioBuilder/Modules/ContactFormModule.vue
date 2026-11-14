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

const fields = computed(() => {
    if (Array.isArray(props.content.fields) && props.content.fields.length) {
        return props.content.fields;
    }
    return [
        { id: 'cf_name', name: 'name', label: 'Votre nom', type: 'text', required: true },
        { id: 'cf_email', name: 'email', label: 'Votre email', type: 'email', required: true },
        { id: 'cf_subject', name: 'subject', label: 'Sujet du message', type: 'text', required: false },
        { id: 'cf_message', name: 'message', label: 'Décrivez votre besoin...', type: 'textarea', required: true },
    ];
});

const formValues = ref({});
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
        await apiClient.post(`/portfolio/${props.portfolioUser.username}/contact`, {
            name: formValues.value.name || 'Visiteur',
            email: formValues.value.email || '',
            subject: formValues.value.subject || '',
            message: formValues.value.message || JSON.stringify(formValues.value),
        });
        feedback.value = 'Message envoyé avec succès !';
        formValues.value = {};
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
                    <div v-for="f in fields" :key="f.id" :class="f.type === 'textarea' ? 'col-12' : 'col-md-6'">
                        <textarea
                            v-if="f.type === 'textarea'"
                            v-model="formValues[f.name]"
                            rows="3"
                            :required="f.required"
                            class="form-control rounded-3"
                            :placeholder="f.label"
                        ></textarea>
                        <input
                            v-else
                            v-model="formValues[f.name]"
                            :type="f.type || 'text'"
                            :required="f.required"
                            class="form-control rounded-3"
                            :placeholder="f.label"
                        />
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
