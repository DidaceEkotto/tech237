<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';

const props = defineProps({
    effect: { type: String, default: 'none' }, // none | snow | confetti | gold_stars | matrix | zen_bubbles
    mouseSpotlight: { type: Boolean, default: false },
    techRingCursor: { type: Boolean, default: false },
});

const canvasRef = ref(null);
const mouseX = ref(-200);
const mouseY = ref(-200);
let rafId = null;

function onMouseMove(e) {
    mouseX.value = e.clientX;
    mouseY.value = e.clientY;
}

onMounted(() => {
    window.addEventListener('mousemove', onMouseMove, { passive: true });
    if (props.effect && props.effect !== 'none' && canvasRef.value) {
        startParticles();
    }
});

onBeforeUnmount(() => {
    window.removeEventListener('mousemove', onMouseMove);
    if (rafId) cancelAnimationFrame(rafId);
});

function startParticles() {
    const canvas = canvasRef.value;
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    const resize = () => {
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;
    };
    resize();
    window.addEventListener('resize', resize);

    const count = props.effect === 'matrix' ? 45 : 55;
    const items = Array.from({ length: count }, () => ({
        x: Math.random() * canvas.width,
        y: Math.random() * canvas.height,
        r: Math.random() * 4 + 2,
        speed: Math.random() * 1.8 + 0.6,
        color: ['#4f46e5', '#f97316', '#10b981', '#eab308', '#ec4899'][Math.floor(Math.random() * 5)],
    }));

    const loop = () => {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        items.forEach((p) => {
            if (props.effect === 'matrix') {
                ctx.fillStyle = '#22c55e';
                ctx.font = '14px monospace';
                ctx.fillText(Math.random() > 0.5 ? '1' : '0', p.x, p.y);
            } else if (props.effect === 'snow') {
                ctx.fillStyle = 'rgba(255,255,255,0.8)';
                ctx.beginPath();
                ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
                ctx.fill();
            } else if (props.effect === 'gold_stars') {
                ctx.fillStyle = '#eab308';
                ctx.beginPath();
                ctx.arc(p.x, p.y, p.r * 0.8, 0, Math.PI * 2);
                ctx.fill();
            } else if (props.effect === 'zen_bubbles') {
                ctx.strokeStyle = 'rgba(56, 189, 248, 0.45)';
                ctx.beginPath();
                ctx.arc(p.x, p.y, p.r * 2.2, 0, Math.PI * 2);
                ctx.stroke();
            } else {
                ctx.fillStyle = p.color;
                ctx.fillRect(p.x, p.y, p.r * 2, p.r);
            }

            p.y += props.effect === 'zen_bubbles' ? -p.speed * 0.7 : p.speed;
            if (p.y > canvas.height + 10) p.y = -10;
            if (p.y < -10) p.y = canvas.height + 10;
        });
        rafId = requestAnimationFrame(loop);
    };
    loop();
}
</script>

<template>
    <div class="pointer-events-none">
        <canvas
            v-if="effect && effect !== 'none'"
            ref="canvasRef"
            class="position-fixed top-0 start-0 w-100 h-100"
            style="z-index: 1020; pointer-events: none;"
        ></canvas>

        <!-- Mouse Spotlight -->
        <div
            v-if="mouseSpotlight"
            class="position-fixed rounded-circle"
            :style="{
                left: `${mouseX - 160}px`,
                top: `${mouseY - 160}px`,
                width: '320px',
                height: '320px',
                background: 'radial-gradient(circle, rgba(79,70,229,0.16) 0%, transparent 70%)',
                pointerEvents: 'none',
                zIndex: 1019,
            }"
        ></div>

        <!-- Tech Ring Cursor (Desktop) -->
        <div
            v-if="techRingCursor"
            class="d-none d-lg-block position-fixed rounded-circle border border-2 border-primary"
            :style="{
                left: `${mouseX - 18}px`,
                top: `${mouseY - 18}px`,
                width: '36px',
                height: '36px',
                pointerEvents: 'none',
                zIndex: 2050,
                transition: 'transform 0.08s ease-out',
            }"
        ></div>
    </div>
</template>
