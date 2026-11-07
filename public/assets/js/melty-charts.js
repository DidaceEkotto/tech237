/**
 * Melty Offline Chart Engine
 * Lightweight, zero-dependency HTML5 Canvas chart renderer.
 */
window.MeltyChart = {
    drawLineChart: function(canvasId, labels, data, options = {}) {
        const canvas = document.getElementById(canvasId);
        if (!canvas) return;
        const ctx = canvas.getContext('2d');
        const dpr = window.devicePixelRatio || 1;
        const rect = canvas.getBoundingClientRect();
        
        canvas.width = rect.width * dpr;
        canvas.height = rect.height * dpr;
        ctx.scale(dpr, dpr);

        const width = rect.width;
        const height = rect.height;
        const padding = { top: 30, right: 25, bottom: 40, left: 45 };
        const chartW = width - padding.left - padding.right;
        const chartH = height - padding.top - padding.bottom;

        ctx.clearRect(0, 0, width, height);

        const maxVal = Math.max(...data, 10);
        const minVal = 0;

        // Draw horizontal grid lines & Y labels
        const gridSteps = 4;
        ctx.strokeStyle = '#e9ecef';
        ctx.lineWidth = 1;
        ctx.fillStyle = '#6c757d';
        ctx.font = '11px Poppins, sans-serif';
        ctx.textAlign = 'right';

        for (let i = 0; i <= gridSteps; i++) {
            const yVal = Math.round(minVal + (maxVal - minVal) * (i / gridSteps));
            const y = padding.top + chartH - (i / gridSteps) * chartH;
            
            ctx.beginPath();
            ctx.moveTo(padding.left, y);
            ctx.lineTo(width - padding.right, y);
            ctx.stroke();

            ctx.fillText(yVal, padding.left - 8, y + 4);
        }

        if (data.length < 2) return;

        // Compute points
        const points = data.map((val, idx) => {
            const x = padding.left + (idx / (data.length - 1)) * chartW;
            const y = padding.top + chartH - ((val - minVal) / (maxVal - minVal)) * chartH;
            return { x, y, val };
        });

        // Fill area gradient
        const gradient = ctx.createLinearGradient(0, padding.top, 0, padding.top + chartH);
        gradient.addColorStop(0, options.fillColor || 'rgba(225, 29, 72, 0.25)');
        gradient.addColorStop(1, 'rgba(225, 29, 72, 0.0)');

        ctx.beginPath();
        ctx.moveTo(points[0].x, padding.top + chartH);
        points.forEach((pt, idx) => {
            if (idx === 0) {
                ctx.lineTo(pt.x, pt.y);
            } else {
                const prev = points[idx - 1];
                const cx = (prev.x + pt.x) / 2;
                ctx.bezierCurveTo(cx, prev.y, cx, pt.y, pt.x, pt.y);
            }
        });
        ctx.lineTo(points[points.length - 1].x, padding.top + chartH);
        ctx.closePath();
        ctx.fillStyle = gradient;
        ctx.fill();

        // Stroke line
        ctx.beginPath();
        points.forEach((pt, idx) => {
            if (idx === 0) {
                ctx.moveTo(pt.x, pt.y);
            } else {
                const prev = points[idx - 1];
                const cx = (prev.x + pt.x) / 2;
                ctx.bezierCurveTo(cx, prev.y, cx, pt.y, pt.x, pt.y);
            }
        });
        ctx.strokeStyle = options.strokeColor || '#E11D48';
        ctx.lineWidth = 3;
        ctx.stroke();

        // Draw points & X labels
        ctx.textAlign = 'center';
        points.forEach((pt, idx) => {
            // Circle
            ctx.beginPath();
            ctx.arc(pt.x, pt.y, 4, 0, Math.PI * 2);
            ctx.fillStyle = '#FFFFFF';
            ctx.fill();
            ctx.strokeStyle = options.strokeColor || '#E11D48';
            ctx.lineWidth = 2;
            ctx.stroke();

            // Value badge on top
            ctx.fillStyle = '#1e293b';
            ctx.font = 'bold 10px Poppins, sans-serif';
            ctx.fillText(pt.val, pt.x, pt.y - 8);

            // X Label
            ctx.fillStyle = '#64748b';
            ctx.font = '11px Poppins, sans-serif';
            if (labels[idx]) {
                ctx.fillText(labels[idx], pt.x, padding.top + chartH + 20);
            }
        });
    },

    drawBarChart: function(canvasId, labels, data, colors = []) {
        const canvas = document.getElementById(canvasId);
        if (!canvas) return;
        const ctx = canvas.getContext('2d');
        const dpr = window.devicePixelRatio || 1;
        const rect = canvas.getBoundingClientRect();

        canvas.width = rect.width * dpr;
        canvas.height = rect.height * dpr;
        ctx.scale(dpr, dpr);

        const width = rect.width;
        const height = rect.height;
        const padding = { top: 25, right: 20, bottom: 40, left: 40 };
        const chartW = width - padding.left - padding.right;
        const chartH = height - padding.top - padding.bottom;

        ctx.clearRect(0, 0, width, height);

        const maxVal = Math.max(...data, 5);
        const barWidth = Math.min(36, chartW / (data.length * 1.5));
        const step = chartW / data.length;

        // Grid lines
        ctx.strokeStyle = '#f1f5f9';
        ctx.lineWidth = 1;
        for (let i = 0; i <= 3; i++) {
            const y = padding.top + (i / 3) * chartH;
            ctx.beginPath();
            ctx.moveTo(padding.left, y);
            ctx.lineTo(width - padding.right, y);
            ctx.stroke();
        }

        data.forEach((val, idx) => {
            const barH = (val / maxVal) * chartH;
            const x = padding.left + idx * step + (step - barWidth) / 2;
            const y = padding.top + chartH - barH;

            // Rounded bar top
            const r = Math.min(4, barH / 2);
            ctx.fillStyle = colors[idx % colors.length] || '#3b82f6';
            ctx.beginPath();
            ctx.moveTo(x, y + barH);
            ctx.lineTo(x, y + r);
            ctx.quadraticCurveTo(x, y, x + r, y);
            ctx.lineTo(x + barWidth - r, y);
            ctx.quadraticCurveTo(x + barWidth, y, x + barWidth, y + r);
            ctx.lineTo(x + barWidth, y + barH);
            ctx.closePath();
            ctx.fill();

            // Value text
            ctx.fillStyle = '#0f172a';
            ctx.font = 'bold 11px Poppins, sans-serif';
            ctx.textAlign = 'center';
            ctx.fillText(val, x + barWidth / 2, y - 6);

            // Label text
            ctx.fillStyle = '#64748b';
            ctx.font = '10px Poppins, sans-serif';
            const label = labels[idx] ? (labels[idx].length > 10 ? labels[idx].substring(0, 9) + '..' : labels[idx]) : '';
            ctx.fillText(label, x + barWidth / 2, padding.top + chartH + 18);
        });
    }
};
