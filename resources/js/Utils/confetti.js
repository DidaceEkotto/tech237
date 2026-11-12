/**
 * Lightweight Zero-Dependency Canvas Confetti
 * Perfect for celebration milestones, kanban completions, level-ups, etc.
 */
export function fireConfetti(options = {}) {
  const {
    particleCount = 60,
    spread = 70,
    origin = { x: 0.5, y: 0.6 },
    colors = ['#00A859', '#FF004B', '#FCD116', '#3b82f6', '#8b5cf6', '#ec4899', '#f97316']
  } = options;

  if (typeof window === 'undefined' || !document.body) return;

  const canvas = document.createElement('canvas');
  canvas.style.position = 'fixed';
  canvas.style.top = '0';
  canvas.style.left = '0';
  canvas.style.width = '100vw';
  canvas.style.height = '100vh';
  canvas.style.pointerEvents = 'none';
  canvas.style.zIndex = '99999';
  document.body.appendChild(canvas);

  const ctx = canvas.getContext('2d');
  const dpr = window.devicePixelRatio || 1;
  const width = window.innerWidth;
  const height = window.innerHeight;
  canvas.width = width * dpr;
  canvas.height = height * dpr;
  ctx.scale(dpr, dpr);

  const particles = [];
  const startX = width * origin.x;
  const startY = height * origin.y;

  for (let i = 0; i < particleCount; i++) {
    const angle = (Math.PI / 180) * (270 + (Math.random() - 0.5) * spread);
    const velocity = 8 + Math.random() * 12;
    particles.push({
      x: startX,
      y: startY,
      vx: Math.cos(angle) * velocity,
      vy: Math.sin(angle) * velocity,
      size: 6 + Math.random() * 6,
      color: colors[Math.floor(Math.random() * colors.length)],
      rotation: Math.random() * 360,
      rotationSpeed: (Math.random() - 0.5) * 12,
      opacity: 1,
      gravity: 0.35 + Math.random() * 0.15,
      friction: 0.96,
      wobble: Math.random() * 10
    });
  }

  let animationFrameId;
  const startTime = Date.now();
  const maxDuration = 3000; // 3 seconds max

  function update() {
    ctx.clearRect(0, 0, width, height);

    let activeParticles = 0;
    const elapsed = Date.now() - startTime;

    for (const p of particles) {
      p.x += p.vx;
      p.y += p.vy;
      p.vy += p.gravity;
      p.vx *= p.friction;
      p.vy *= p.friction;
      p.rotation += p.rotationSpeed;
      p.wobble += 0.1;

      if (elapsed > 1200) {
        p.opacity = Math.max(0, p.opacity - 0.025);
      }

      if (p.opacity > 0 && p.y < height + 50) {
        activeParticles++;
        ctx.save();
        ctx.translate(p.x, p.y);
        ctx.rotate((p.rotation * Math.PI) / 180);
        ctx.globalAlpha = p.opacity;
        ctx.fillStyle = p.color;
        
        // Rectangular confetti with perspective wobble
        const scaleX = Math.cos(p.wobble);
        ctx.fillRect(-p.size / 2, (-p.size * scaleX) / 2, p.size, p.size * Math.abs(scaleX));
        ctx.restore();
      }
    }

    if (activeParticles > 0 && elapsed < maxDuration) {
      animationFrameId = requestAnimationFrame(update);
    } else {
      cancelAnimationFrame(animationFrameId);
      if (canvas.parentNode) {
        canvas.parentNode.removeChild(canvas);
      }
    }
  }

  animationFrameId = requestAnimationFrame(update);
}
