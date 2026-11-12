/**
 * Copy text to clipboard with modern styled feedback toast
 */
export function copyTextWithToast(text, message = 'Lien copié dans le presse-papier !') {
  if (typeof window === 'undefined') return;

  const performCopy = () => {
    if (navigator.clipboard && window.isSecureContext) {
      return navigator.clipboard.writeText(text);
    } else {
      const textarea = document.createElement('textarea');
      textarea.value = text;
      textarea.style.position = 'fixed';
      textarea.style.left = '-9999px';
      textarea.style.top = '0';
      document.body.appendChild(textarea);
      textarea.focus();
      textarea.select();
      try {
        document.execCommand('copy');
      } catch (err) {
        console.error('Fallback copy error:', err);
      }
      document.body.removeChild(textarea);
      return Promise.resolve();
    }
  };

  performCopy().then(() => {
    showToast(message);
  }).catch((err) => {
    console.error('Copy error:', err);
    showToast('Erreur lors de la copie', 'danger');
  });
}

function showToast(message, type = 'success') {
  const existing = document.getElementById('tech237-global-toast');
  if (existing) {
    existing.remove();
  }

  const toast = document.createElement('div');
  toast.id = 'tech237-global-toast';
  toast.className = `d-flex align-items-center gap-2 px-4 py-3 rounded-pill shadow-lg text-white ${type === 'danger' ? 'bg-danger' : 'bg-dark'}`;
  toast.style.position = 'fixed';
  toast.style.bottom = '28px';
  toast.style.left = '50%';
  toast.style.transform = 'translateX(-50%) translateY(20px)';
  toast.style.opacity = '0';
  toast.style.transition = 'all 0.25s cubic-bezier(0.16, 1, 0.3, 1)';
  toast.style.zIndex = '999999';
  toast.style.fontSize = '14px';
  toast.style.fontWeight = '600';
  toast.style.letterSpacing = '0.2px';
  toast.style.border = '1px solid rgba(255, 255, 255, 0.15)';

  const icon = document.createElement('i');
  icon.className = type === 'danger' ? 'bi bi-exclamation-circle-fill text-white fs-5' : 'bi bi-check-circle-fill text-success fs-5';
  
  const span = document.createElement('span');
  span.innerText = message;

  toast.appendChild(icon);
  toast.appendChild(span);
  document.body.appendChild(toast);

  // Trigger enter animation
  requestAnimationFrame(() => {
    toast.style.transform = 'translateX(-50%) translateY(0)';
    toast.style.opacity = '1';
  });

  // Auto dismiss
  setTimeout(() => {
    toast.style.transform = 'translateX(-50%) translateY(20px)';
    toast.style.opacity = '0';
    setTimeout(() => {
      if (toast.parentNode) toast.parentNode.removeChild(toast);
    }, 300);
  }, 2600);
}

export const copyLinkWithToast = copyTextWithToast;

export function buildWhatsAppShareUrl(title, url) {
  const cleanTitle = (title || '').trim();
  const cleanUrl = (url || (typeof window !== 'undefined' ? window.location.href : '')).trim();
  const text = cleanTitle ? `${cleanTitle}\n${cleanUrl}` : cleanUrl;
  return `https://api.whatsapp.com/send?text=${encodeURIComponent(text)}`;
}
