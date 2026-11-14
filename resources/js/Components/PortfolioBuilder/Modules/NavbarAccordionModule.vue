<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';

const props = defineProps({
    content: { type: Object, required: true },
    portfolioUser: { type: Object, default: () => ({}) },
    pages: { type: Array, default: () => [] },
    activePageSlug: { type: String, default: '' },
    siteType: { type: String, default: 'onepage' },
    isEditor: { type: Boolean, default: false },
});

const openDropdown = ref(null);
const mobileOpen = ref(false);
const collapsedSidebar = ref(false);
const activeSectionId = ref('');
let observer = null;

const isVertical = computed(() => ['vertical_left', 'vertical_right', 'collapsible_sidebar'].includes(props.content.template));

const initials = computed(() => {
    const raw = props.portfolioUser?.name || props.portfolioUser?.username || 'P';
    return raw
        .split(/\s+/)
        .filter(Boolean)
        .slice(0, 2)
        .map((w) => w[0].toUpperCase())
        .join('');
});

const brandImage = computed(() => {
    if (props.content.logoUrl) return props.content.logoUrl;
    if (props.portfolioUser?.avatar) {
        return props.portfolioUser.avatar.startsWith('http') || props.portfolioUser.avatar.startsWith('/')
            ? props.portfolioUser.avatar
            : `/storage/${props.portfolioUser.avatar}`;
    }
    return null;
});

const navStyle = computed(() => {
    const mode = props.content.positionMode || 'sticky';
    const isDark = props.content.template === 'solid_dark';
    const isGrad = props.content.template === 'gradient_modern';
    const baseRadius = props.content.borderRadius ?? 999;

    const style = {
        background: isGrad
            ? `linear-gradient(135deg, ${props.content.accentColor || '#4f46e5'}, #06b6d4)`
            : isDark
                ? '#0f172a'
                : (props.content.bgColor || '#ffffff'),
        color: isDark || isGrad ? '#ffffff' : (props.content.textColor || '#0f172a'),
        borderRadius: mobileOpen.value || isVertical.value ? '16px' : `${baseRadius}px`,
        fontSize: `${props.content.fontSize ?? 14}px`,
        zIndex: 1030,
        backdropFilter: props.content.template === 'glass_floating' ? 'blur(14px)' : undefined,
    };

    if (!props.isEditor && !isVertical.value) {
        if (mode === 'fixed') {
            style.position = 'fixed';
            style.top = '12px';
            style.left = '16px';
            style.right = '16px';
        } else if (mode === 'sticky') {
            style.position = 'sticky';
            style.top = '12px';
        }
    }

    if (!props.isEditor && isVertical.value) {
        style.position = 'fixed';
        style.top = '16px';
        style.bottom = '16px';
        style[props.content.template === 'vertical_right' ? 'right' : 'left'] = '16px';
        style.width = collapsedSidebar.value ? '74px' : '240px';
    }

    return style;
});

function isItemActive(item) {
    const target = (item.target || '').trim();
    if (!target) return false;
    if (target.startsWith('#')) {
        return activeSectionId.value && target.slice(1) === activeSectionId.value;
    }
    if (props.activePageSlug && target.includes(props.activePageSlug)) {
        return true;
    }
    return false;
}

function itemActiveStyle(item) {
    if (!isItemActive(item)) return { color: 'inherit', fontSize: 'inherit' };
    return {
        backgroundColor: props.content.activeBgColor || props.content.accentColor || '#4f46e5',
        color: props.content.activeTextColor || '#ffffff',
        fontSize: 'inherit',
    };
}

function toggleAccordion(id) {
    openDropdown.value = openDropdown.value === id ? null : id;
}

function resolveHref(target) {
    if (!target) return '#';
    if (target.startsWith('#') || target.startsWith('http') || target.startsWith('/')) return target;
    return `#${target}`;
}

onMounted(() => {
    if (props.isEditor || typeof IntersectionObserver === 'undefined') return;
    const sections = document.querySelectorAll('section[id]');
    if (!sections.length) return;
    observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    activeSectionId.value = entry.target.id;
                }
            });
        },
        { rootMargin: '-30% 0px -55% 0px', threshold: 0.05 }
    );
    sections.forEach((el) => observer.observe(el));
});

onBeforeUnmount(() => {
    observer?.disconnect();
});
</script>

<template>
    <nav
        class="pb-navbar shadow-sm border px-3 py-2 transition-all"
        :class="{
            'border-bottom border-top-0 border-start-0 border-end-0 rounded-0': content.template === 'minimal_underline',
            'bg-opacity-75': content.template === 'glass_floating',
            'd-flex flex-column justify-content-between': isVertical,
        }"
        :style="navStyle"
    >
        <!-- VERTICAL SIDEBAR MODE (Left / Right / Collapsible) -->
        <template v-if="isVertical">
            <div>
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <a :href="isEditor ? 'javascript:void(0)' : '#'" class="d-flex align-items-center gap-2 text-decoration-none" :style="{ color: 'inherit' }">
                        <img v-if="brandImage" :src="brandImage" alt="Logo" class="rounded-circle object-fit-cover border" style="width: 36px; height: 36px;" />
                        <span v-else class="rounded-circle d-inline-flex align-items-center justify-content-center fw-bold text-white" style="width: 36px; height: 36px; font-size: 13px;" :style="{ backgroundColor: content.accentColor || '#4f46e5' }">
                            {{ initials }}
                        </span>
                    </a>
                    <button
                        v-if="content.template === 'collapsible_sidebar' || content.collapsible"
                        type="button"
                        class="btn btn-sm border-0"
                        :style="{ color: 'inherit' }"
                        title="Déplier / Replier"
                        @click.stop="collapsedSidebar = !collapsedSidebar"
                    >
                        <i class="bi" :class="collapsedSidebar ? 'bi-chevron-right' : 'bi-chevron-left'"></i>
                    </button>
                </div>

                <div class="d-flex flex-column gap-1">
                    <div v-for="item in (content.items || [])" :key="item.id">
                        <a
                            :href="isEditor ? 'javascript:void(0)' : resolveHref(item.target)"
                            class="btn btn-sm border-0 d-flex align-items-center gap-2 fw-medium px-3 py-2 rounded-3 text-decoration-none w-100 text-start"
                            :style="itemActiveStyle(item)"
                        >
                            <i v-if="item.icon" class="bi" :class="item.icon"></i>
                            <span v-show="!collapsedSidebar">{{ item.label }}</span>
                        </a>
                    </div>
                </div>
            </div>

            <a
                v-if="content.ctaLabel && !collapsedSidebar"
                :href="isEditor ? 'javascript:void(0)' : resolveHref(content.ctaHref)"
                class="btn btn-sm rounded-pill px-3 fw-semibold text-white border-0 w-100 mt-3"
                :style="{ backgroundColor: content.accentColor || '#4f46e5' }"
            >
                {{ content.ctaLabel }}
            </a>
        </template>

        <!-- HORIZONTAL NAVBAR MODE -->
        <template v-else>
            <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap">
                <!-- Brand (Logo -> Avatar -> Initials, no extra brand text clutter) -->
                <a
                    :href="isEditor ? 'javascript:void(0)' : '#'"
                    class="d-flex align-items-center text-decoration-none"
                    :style="{ color: 'inherit' }"
                >
                    <img
                        v-if="brandImage"
                        :src="brandImage"
                        alt="Logo"
                        class="rounded-circle object-fit-cover border"
                        style="width: 36px; height: 36px;"
                    />
                    <span
                        v-else
                        class="rounded-circle d-inline-flex align-items-center justify-content-center fw-bold text-white"
                        style="width: 36px; height: 36px; font-size: 13px;"
                        :style="{ backgroundColor: content.accentColor || '#4f46e5' }"
                    >
                        {{ initials }}
                    </span>
                </a>

                <!-- Desktop Menu -->
                <div class="d-none d-md-flex align-items-center gap-1 flex-wrap">
                    <div v-for="item in (content.items || [])" :key="item.id" class="position-relative">
                        <button
                            v-if="item.children && item.children.length"
                            type="button"
                            class="btn btn-sm border-0 d-inline-flex align-items-center gap-1 fw-medium px-3 py-1 rounded-pill"
                            :style="itemActiveStyle(item)"
                            @click.stop="toggleAccordion(item.id)"
                        >
                            <i v-if="item.icon" class="bi" :class="item.icon"></i>
                            <span>{{ item.label }}</span>
                            <i
                                class="bi bi-chevron-down small transition-transform"
                                :style="{ transform: openDropdown === item.id ? 'rotate(180deg)' : 'rotate(0deg)' }"
                            ></i>
                        </button>
                        <a
                            v-else
                            :href="isEditor ? 'javascript:void(0)' : resolveHref(item.target)"
                            class="btn btn-sm border-0 d-inline-flex align-items-center gap-1 fw-medium px-3 py-1 rounded-pill text-decoration-none"
                            :style="itemActiveStyle(item)"
                        >
                            <i v-if="item.icon" class="bi" :class="item.icon"></i>
                            <span>{{ item.label }}</span>
                        </a>

                        <transition name="pb-accordion">
                            <div
                                v-if="item.children && item.children.length && openDropdown === item.id"
                                class="position-absolute start-0 mt-2 p-2 bg-white text-dark rounded-4 shadow-lg border"
                                style="min-width: 210px; z-index: 1050;"
                            >
                                <a
                                    v-for="sub in item.children"
                                    :key="sub.id"
                                    :href="isEditor ? 'javascript:void(0)' : resolveHref(sub.target)"
                                    class="d-flex align-items-center gap-2 px-3 py-2 rounded-3 text-decoration-none text-dark small fw-medium pb-sub-link"
                                    @click="openDropdown = null"
                                >
                                    <i v-if="sub.icon" class="bi text-primary" :class="sub.icon"></i>
                                    <span>{{ sub.label }}</span>
                                </a>
                            </div>
                        </transition>
                    </div>
                </div>

                <!-- Right CTA + Mobile Toggle -->
                <div class="d-flex align-items-center gap-2">
                    <a
                        v-if="content.ctaLabel"
                        :href="isEditor ? 'javascript:void(0)' : resolveHref(content.ctaHref)"
                        class="btn btn-sm rounded-pill px-3 fw-semibold text-white border-0"
                        :style="{ backgroundColor: content.accentColor || '#4f46e5', fontSize: 'inherit' }"
                    >
                        {{ content.ctaLabel }}
                    </a>
                    <button
                        type="button"
                        class="btn btn-sm d-md-none border rounded-circle"
                        :style="{ color: 'inherit' }"
                        @click.stop="mobileOpen = !mobileOpen"
                    >
                        <i class="bi" :class="mobileOpen ? 'bi-x-lg' : 'bi-list'"></i>
                    </button>
                </div>
            </div>

            <!-- Mobile Accordion Drawer (always rectangular rounded-4, never giant oval) -->
            <transition name="pb-accordion">
                <div v-if="mobileOpen" class="d-md-none mt-3 pt-2 border-top">
                    <div v-for="item in (content.items || [])" :key="item.id" class="mb-1">
                        <div class="d-flex align-items-center justify-content-between">
                            <a
                                :href="isEditor ? 'javascript:void(0)' : resolveHref(item.target)"
                                class="d-flex align-items-center gap-2 py-2 px-2 text-decoration-none fw-medium flex-grow-1 rounded-3"
                                :style="itemActiveStyle(item)"
                                @click="mobileOpen = false"
                            >
                                <i v-if="item.icon" class="bi" :class="item.icon"></i>
                                <span>{{ item.label }}</span>
                            </a>
                            <button
                                v-if="item.children && item.children.length"
                                type="button"
                                class="btn btn-sm border-0"
                                :style="{ color: 'inherit' }"
                                @click.stop="toggleAccordion(item.id)"
                            >
                                <i class="bi" :class="openDropdown === item.id ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                            </button>
                        </div>
                        <div v-if="item.children && item.children.length && openDropdown === item.id" class="ps-4 pb-2">
                            <a
                                v-for="sub in item.children"
                                :key="sub.id"
                                :href="isEditor ? 'javascript:void(0)' : resolveHref(sub.target)"
                                class="d-flex align-items-center gap-2 py-1 text-decoration-none small opacity-75"
                                :style="{ color: 'inherit' }"
                                @click="mobileOpen = false"
                            >
                                <i v-if="sub.icon" class="bi" :class="sub.icon"></i>
                                <span>{{ sub.label }}</span>
                            </a>
                        </div>
                    </div>
                </div>
            </transition>
        </template>
    </nav>
</template>

<style scoped>
.pb-sub-link:hover {
    background-color: #f1f5f9;
}
.pb-accordion-enter-active,
.pb-accordion-leave-active {
    transition: all 0.2s ease;
}
.pb-accordion-enter-from,
.pb-accordion-leave-to {
    opacity: 0;
    transform: translateY(-6px);
}
@media (max-width: 767.98px) {
    .pb-navbar {
        border-radius: 16px !important;
    }
}
</style>
