// Shared media playback bus so starting a YouTube or Vimeo video pauses all others on the page.
const listeners = new Set();
let listenerAttached = false;

export function broadcastMediaPlay(sourceId) {
    listeners.forEach((cb) => cb(sourceId));
}

export function onMediaPlayOther(callback) {
    ensureGlobalIframeMessageListener();
    listeners.add(callback);
    return () => listeners.delete(callback);
}

function ensureGlobalIframeMessageListener() {
    if (listenerAttached || typeof window === 'undefined') return;
    listenerAttached = true;

    window.addEventListener('message', (event) => {
        if (!event?.data) return;
        let payload = event.data;
        if (typeof payload === 'string') {
            try {
                payload = JSON.parse(payload);
            } catch {
                return;
            }
        }
        if (!payload || typeof payload !== 'object') return;

        // YouTube IFrame API: event === 'onStateChange' && info === 1 (playing)
        // or infoDelivery with info.playerState === 1
        const isYtPlay =
            (payload.event === 'onStateChange' && payload.info === 1) ||
            (payload.event === 'infoDelivery' && payload.info?.playerState === 1);

        // Vimeo Player API: event === 'play'
        const isVimeoPlay = payload.event === 'play';

        if (!isYtPlay && !isVimeoPlay) return;

        // Identify which iframe sent this message
        const iframes = document.querySelectorAll('iframe[data-pb-media-id]');
        for (const frame of iframes) {
            if (frame.contentWindow === event.source) {
                const mediaId = frame.getAttribute('data-pb-media-id');
                if (mediaId) {
                    broadcastMediaPlay(mediaId);
                }
                break;
            }
        }
    });
}
