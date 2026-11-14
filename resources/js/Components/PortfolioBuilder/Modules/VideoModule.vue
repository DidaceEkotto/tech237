<script setup>
import { ref, computed } from 'vue';

const props = defineProps({
    content: { type: Object, required: true },
});

const activeIndex = ref(0);
const tpl = computed(() => props.content.template || 'theater_wide');
const videos = computed(() => props.content.videos || []);

function toEmbedUrl(rawUrl) {
    if (!rawUrl) return '';
    try {
        const u = new URL(rawUrl);
        let videoId = '';
        if (u.hostname.includes('youtu.be')) {
            videoId = u.pathname.replace('/', '');
        } else if (u.searchParams.get('v')) {
            videoId = u.searchParams.get('v');
        } else if (u.pathname.includes('/embed/')) {
            videoId = u.pathname.split('/embed/')[1]?.split('/')[0] || '';
        } else if (u.pathname.includes('/shorts/')) {
            videoId = u.pathname.split('/shorts/')[1]?.split('/')[0] || '';
        }
        return videoId ? `https://www.youtube.com/embed/${videoId}` : '';
    } catch {
        return '';
    }
}
</script>

<template>
    <div
        class="pb-video rounded-4 p-3"
        :class="{ 'bg-dark text-white p-4': tpl === 'dark_cinema' }"
    >
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h3 v-if="content.title" class="fw-bold mb-0">{{ content.title }}</h3>
            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle">
                <i class="bi bi-youtube me-1"></i>{{ tpl }}
            </span>
        </div>

        <!-- Playlist Sidebar / Split Caption -->
        <div v-if="['playlist_sidebar', 'split_caption', 'chapter_list'].includes(tpl) && videos.length" class="row g-3 align-items-start">
            <div class="col-lg-8">
                <div class="ratio ratio-16x9 rounded-4 overflow-hidden shadow">
                    <iframe
                        v-if="toEmbedUrl(videos[activeIndex]?.youtubeUrl)"
                        :src="toEmbedUrl(videos[activeIndex]?.youtubeUrl)"
                        allowfullscreen
                        class="border-0"
                    ></iframe>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="list-group rounded-4 overflow-hidden shadow-sm">
                    <button
                        v-for="(v, i) in videos"
                        :key="v.id"
                        type="button"
                        class="list-group-item list-group-item-action d-flex align-items-center justify-content-between"
                        :class="{ active: activeIndex === i }"
                        @click.stop="activeIndex = i"
                    >
                        <span class="text-truncate fw-medium">{{ v.title }}</span>
                        <span class="badge bg-secondary ms-2">{{ v.duration || '03:00' }}</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Grid 2 or 3 Cols -->
        <div v-else-if="['grid_2_col', 'grid_3_col', 'reel_vertical'].includes(tpl)" class="row g-3">
            <div
                v-for="v in videos"
                :key="v.id"
                :class="tpl === 'grid_3_col' ? 'col-md-4' : 'col-md-6'"
            >
                <div class="rounded-4 overflow-hidden border bg-white text-dark shadow-sm">
                    <div :class="tpl === 'reel_vertical' ? 'ratio ratio-9x16' : 'ratio ratio-16x9'">
                        <iframe v-if="toEmbedUrl(v.youtubeUrl)" :src="toEmbedUrl(v.youtubeUrl)" allowfullscreen class="border-0"></iframe>
                    </div>
                    <div class="p-2 px-3 fw-semibold small">{{ v.title }}</div>
                </div>
            </div>
        </div>

        <!-- Theater Wide / Floating / Minimal -->
        <div v-else-if="videos.length" class="rounded-4 overflow-hidden shadow">
            <div class="ratio ratio-16x9">
                <iframe
                    v-if="toEmbedUrl(videos[0]?.youtubeUrl)"
                    :src="toEmbedUrl(videos[0]?.youtubeUrl)"
                    allowfullscreen
                    class="border-0"
                ></iframe>
            </div>
        </div>
    </div>
</template>
