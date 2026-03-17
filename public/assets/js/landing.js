document.addEventListener("DOMContentLoaded", function() {
    const payload = window.__DASHBOARD_DATA__;
    if (!payload || !payload.summary) return; // Exit if data is not present

    // ── Shared Tooltip DOM ───────────────────────────────────────────────────
    const tooltip = (() => {
        const el = document.createElement('div');
        el.id = 'customChartTooltip';
        Object.assign(el.style, {
            position: 'fixed', pointerEvents: 'none', display: 'none',
            background: '#1f2937', color: '#f9fafb', borderRadius: '8px',
            padding: '8px 12px', fontSize: '12px', lineHeight: '1.6',
            boxShadow: '0 4px 12px rgba(0,0,0,.35)', zIndex: '9999',
            maxWidth: '180px', whiteSpace: 'pre-line', transition: 'opacity .1s'
        });
        document.body.appendChild(el);
        return el;
    })();

    const showTooltip = (x, y, html) => {
        tooltip.innerHTML = html;
        tooltip.style.display = 'block';
        const tw = tooltip.offsetWidth, th = tooltip.offsetHeight;
        tooltip.style.left = (x + 14 + tw > window.innerWidth  ? x - tw - 14 : x + 14) + 'px';
        tooltip.style.top  = (y + 14 + th > window.innerHeight ? y - th - 14 : y + 14) + 'px';
    };
    const hideTooltip = () => { tooltip.style.display = 'none'; };

    // ── Canvas Utilities & Drawing Functions ─────────────────────────────────
    function fitCanvas(canvas) {
        if (!canvas) return null;
        const dpr = window.devicePixelRatio || 1;
        const rect = canvas.getBoundingClientRect();
        const cssW = Math.max(1, Math.floor(rect.width)), cssH = Math.max(1, Math.floor(rect.height));
        const needW = Math.floor(cssW * dpr), needH = Math.floor(cssH * dpr);
        if (canvas.width !== needW || canvas.height !== needH) {
            canvas.width = needW; canvas.height = needH;
        }
        const ctx = canvas.getContext("2d");
        ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
        return { ctx, w: cssW, h: cssH };
    }

    const drawBar = (canvas, a, b) => {
        const fitted = fitCanvas(canvas);
        if (!fitted) return;
        const { ctx, w, h } = fitted;

        ctx.clearRect(0, 0, w, h);
        ctx.fillStyle = "transparent";
        ctx.fillRect(0, 0, w, h);

        const padL = 38, padR = 52, padT = 28, padB = 32;
        const baseY = h - padB, usableH = h - padT - padB;
        const FILL = 0.85;
        const bh1 = usableH * FILL, bh2 = usableH * FILL;
        const barW = Math.max(36, Math.min(80, Math.floor(w * 0.16)));
        const plotW = w - padL - padR;
        const x1 = padL + Math.floor(plotW * 0.18), x2 = padL + Math.floor(plotW * 0.57);
        const TICKS = 4;

        const drawLeftAxis = () => {
            ctx.strokeStyle = "rgba(99,102,241,.5)"; ctx.lineWidth = 1; ctx.beginPath();
            ctx.moveTo(padL, padT); ctx.lineTo(padL, baseY); ctx.stroke();
            ctx.fillStyle = "rgba(99,102,241,.9)"; ctx.font = "9px Poppins, Arial"; ctx.textAlign = "right";
            for (let i = 0; i <= TICKS; i++) {
                const frac = i / TICKS, y = baseY - frac * usableH * FILL, val = Math.round(frac * a);
                ctx.strokeStyle = "rgba(0,0,0,.06)"; ctx.beginPath(); ctx.moveTo(padL, y); ctx.lineTo(w - padR, y); ctx.stroke();
                ctx.strokeStyle = "rgba(99,102,241,.5)"; ctx.beginPath(); ctx.moveTo(padL - 4, y); ctx.lineTo(padL, y); ctx.stroke();
                ctx.fillText(String(val), padL - 6, y + 3);
            }
        };

        const drawRightAxis = () => {
            ctx.strokeStyle = "rgba(214,173,0,.7)"; ctx.lineWidth = 1; ctx.beginPath();
            ctx.moveTo(w - padR, padT); ctx.lineTo(w - padR, baseY); ctx.stroke();
            ctx.fillStyle = "rgba(160,120,0,.9)"; ctx.font = "9px Poppins, Arial"; ctx.textAlign = "left";
            for (let i = 0; i <= TICKS; i++) {
                const frac = i / TICKS, y = baseY - frac * usableH * FILL, val = Math.round(frac * b);
                ctx.strokeStyle = "rgba(214,173,0,.7)"; ctx.beginPath(); ctx.moveTo(w - padR, y); ctx.lineTo(w - padR + 4, y); ctx.stroke();
                ctx.fillText(val + " W", w - padR + 6, y + 3);
            }
        };

        drawLeftAxis(); drawRightAxis();

        // Baseline
        ctx.strokeStyle = "rgba(0,0,0,.2)"; ctx.lineWidth = 1; ctx.beginPath();
        ctx.moveTo(padL, baseY); ctx.lineTo(w - padR, baseY); ctx.stroke();

        // Bars
        ctx.fillStyle = "rgba(99,102,241,.65)"; ctx.fillRect(x1, baseY - bh1, barW, bh1);
        ctx.fillStyle = "rgba(245,196,0,.80)"; ctx.fillRect(x2, baseY - bh2, barW, bh2);

        // Labels
        ctx.fillStyle = "#1f2937"; ctx.textAlign = "center"; ctx.font = "700 11px Poppins, Arial";
        ctx.fillText(String(a), x1 + barW / 2, baseY - bh1 - 7);
        ctx.fillText(String(b) + " W", x2 + barW / 2, baseY - bh2 - 7);
        ctx.font = "10px Poppins, Arial"; ctx.fillStyle = "#374151";
        ctx.fillText("Total LPJU", x1 + barW / 2, baseY + 14); ctx.fillText("Total Daya", x2 + barW / 2, baseY + 14);
        ctx.font = "italic 8px Poppins, Arial"; ctx.fillStyle = "rgba(99,102,241,.7)"; ctx.textAlign = "left"; ctx.fillText("(unit)", padL, padT - 8);
        ctx.fillStyle = "rgba(160,120,0,.7)"; ctx.textAlign = "right"; ctx.fillText("(Watt)", w - padR, padT - 8);

        return { bars: [
            { label: 'Total LPJU', value: a, x: x1, y: baseY - bh1, w: barW, h: bh1 },
            { label: 'Total Daya', value: b, x: x2, y: baseY - bh2, w: barW, h: bh2 }
        ]};
    };

    const drawPie = (canvas, obj, centerLabel, showLabels = true, outerLabels = false) => {
        const fitted = fitCanvas(canvas);
        if (!fitted) return;
        const { ctx, w, h } = fitted;

        ctx.clearRect(0, 0, w, h);
        const entries = Object.entries(obj).filter(([, v]) => Number(v) > 0).sort((a, b) => Number(b[1]) - Number(a[1]));
        const total = entries.reduce((s, [, v]) => s + Number(v), 0) || 1;
        const cx = w / 2, cy = h / 2, r = outerLabels ? Math.min(w, h) * 0.24 : Math.min(w, h) * 0.40;
        const colors = ["#0b57b7", "#f5c400", "#f59e0b", "#ec4899", "#22c55e", "#8b5cf6"];
        let startAbs = 0, start = -Math.PI / 2;
        const innerR = r * 0.58, labelR = (r + innerR) / 2, minAngForLabel = 0.22;
        const slices = [];

        entries.forEach(([k, v], idx) => {
            const val = Number(v), ang = (val / total) * Math.PI * 2;
            ctx.beginPath(); ctx.moveTo(cx, cy); ctx.arc(cx, cy, r, start, start + ang); ctx.closePath();
            const color = colors[idx % colors.length];
            ctx.fillStyle = color; ctx.fill();

            if (showLabels && !outerLabels && ang >= minAngForLabel) {
                const mid = start + ang / 2, x = cx + Math.cos(mid) * labelR, y = cy + Math.sin(mid) * labelR;
                let label = String(k ?? "").trim(); if (label.length > 14) label = label.slice(0, 14) + "…";
                ctx.save(); ctx.textAlign = "center"; ctx.textBaseline = "middle"; ctx.font = "700 10px Poppins, Arial";
                ctx.fillStyle = "#111827"; ctx.fillText(label, x, y); ctx.restore();
            }
            slices.push({ label: String(k ?? "").trim() || "Lainnya", value: val, pct: Math.round((val / total) * 100), color, start: startAbs, end: startAbs + ang });
            start += ang; startAbs += ang;
        });

        // Center Hole
        ctx.beginPath(); ctx.arc(cx, cy, innerR, 0, Math.PI * 2); ctx.fillStyle = "#fff"; ctx.fill(); // Match background if not white
        ctx.fillStyle = "#111827"; ctx.textAlign = "center"; ctx.textBaseline = "middle"; ctx.font = "12px Poppins, Arial"; ctx.fillText("Total", cx, cy - 8);
        ctx.font = "700 16px Poppins, Arial"; ctx.fillText(String(centerLabel ?? total), cx, cy + 10);

        // ── Outer polyline callout labels ──────────────────────────────────────────
        if (outerLabels && slices.length) {
            const SPOKE = 10, SHELF = 22, VPAD = 12, LINE_H = 12, TEXT_GAP = 3, VAL_H = 12, MIN_GAP = 6, FONT_SZ = 9;

            const wrapText = (text, maxWidth) => {
                const words = text.split(' '), lines = [];
                let current = '';
                ctx.font = `700 ${FONT_SZ}px Poppins, Arial`;
                for (const word of words) {
                    const test = current ? current + ' ' + word : word;
                    if (ctx.measureText(test).width <= maxWidth) { current = test; } 
                    else { if (current) lines.push(current); current = word; }
                }
                if (current) lines.push(current);
                return lines.length ? lines : [text];
            };

            const annotated = slices.map((s) => {
                const mid = (s.start + s.end) / 2, cAngle = mid - Math.PI / 2;
                const rimX = cx + Math.cos(cAngle) * r, rimY = cy + Math.sin(cAngle) * r;
                const elbX = cx + Math.cos(cAngle) * (r + SPOKE), elbY = cy + Math.sin(cAngle) * (r + SPOKE);
                const isRight = Math.cos(cAngle) >= 0;
                const shelfX = isRight ? elbX + SHELF : elbX - SHELF;
                const availW = isRight ? Math.max(30, w - shelfX - 8) : Math.max(30, shelfX - 8);
                const nameLines = wrapText(s.label, availW);
                const blockH = nameLines.length * LINE_H + TEXT_GAP + VAL_H;
                return { ...s, cAngle, rimX, rimY, elbX, elbY, isRight, shelfX, availW, nameLines, blockH };
            });

            const rightGroup = annotated.filter((s) => s.isRight).sort((a, b) => a.rimY - b.rimY);
            const leftGroup = annotated.filter((s) => !s.isRight).sort((a, b) => a.rimY - b.rimY);

            const assignY = (group) => {
                const n = group.length;
                if (n === 0) return;
                if (n === 1) { group[0].labelY = cy - group[0].blockH / 2; return; }
                const totalH = group.reduce((sum, s) => sum + s.blockH, 0) + (n - 1) * MIN_GAP;
                let startY = Math.max(VPAD, (h - totalH) / 2);
                if (startY + totalH > h - VPAD) startY = h - VPAD - totalH;
                group.forEach((s) => { s.labelY = startY; startY += s.blockH + MIN_GAP; });
            };
            assignY(rightGroup); assignY(leftGroup);

            const drawCallout = (s) => {
                const anchorY = s.labelY + s.blockH / 2;
                ctx.save();
                ctx.strokeStyle = s.color; ctx.lineWidth = 1; ctx.globalAlpha = 0.80;
                ctx.beginPath(); ctx.moveTo(s.rimX, s.rimY); ctx.lineTo(s.elbX, s.elbY); ctx.lineTo(s.shelfX, anchorY); ctx.stroke();
                
                ctx.fillStyle = s.color; ctx.globalAlpha = 1;
                ctx.beginPath(); ctx.arc(s.rimX, s.rimY, 2.5, 0, Math.PI * 2); ctx.fill();

                const textX = s.isRight ? s.shelfX + 4 : s.shelfX - 4;
                ctx.textAlign = s.isRight ? 'left' : 'right'; ctx.textBaseline = 'top';
                
                ctx.font = `700 ${FONT_SZ}px Poppins, Arial`; ctx.fillStyle = '#111827';
                s.nameLines.forEach((line, i) => { ctx.fillText(line, textX, s.labelY + i * LINE_H); });
                
                ctx.font = `${FONT_SZ}px Poppins, Arial`; ctx.fillStyle = s.color;
                ctx.fillText(s.value.toLocaleString('id-ID') + ' (' + s.pct + '%)', textX, s.labelY + s.nameLines.length * LINE_H + TEXT_GAP);
                ctx.restore();
            };

            rightGroup.forEach(drawCallout); leftGroup.forEach(drawCallout);
        }

        return { cx, cy, innerR, outerR: r, slices };
    };

    // ── Tooltip Event Handlers ───────────────────────────────────────────────
    const attachBarTooltip = (canvas, bars) => {
        if (canvas._barMove) canvas.removeEventListener('mousemove', canvas._barMove);
        if (canvas._barLeave) canvas.removeEventListener('mouseleave', canvas._barLeave);
        canvas._barMove = (e) => {
            const rect = canvas.getBoundingClientRect();
            const cssMx = e.clientX - rect.left, csMy = e.clientY - rect.top;
            const hit = bars.find((b) => cssMx >= b.x && cssMx <= b.x + b.w && csMy >= b.y && csMy <= b.y + b.h);
            if (!hit) { hideTooltip(); return; }
            let html = `<strong>${hit.label}</strong><br>${hit.value.toLocaleString('id-ID')} ${hit.label === 'Total Daya' ? 'W' : ''}`;
            showTooltip(e.clientX, e.clientY, html);
        };
        canvas._barLeave = () => hideTooltip();
        canvas.addEventListener('mousemove', canvas._barMove); canvas.addEventListener('mouseleave', canvas._barLeave);
    };

    const attachPieTooltip = (canvas, slices, cx, cy, innerR, outerR) => {
        if (canvas._pieMove) canvas.removeEventListener('mousemove', canvas._pieMove);
        if (canvas._pieLeave) canvas.removeEventListener('mouseleave', canvas._pieLeave);
        canvas._pieMove = (e) => {
            const rect = canvas.getBoundingClientRect(), mx = e.clientX - rect.left, my = e.clientY - rect.top;
            const dx = mx - cx, dy = my - cy, dist = Math.sqrt(dx * dx + dy * dy);
            if (dist < innerR || dist > outerR) { hideTooltip(); return; }
            let angle = Math.atan2(dy, dx) + Math.PI / 2; if (angle < 0) angle += Math.PI * 2;
            const hit = slices.find((s) => angle >= s.start && angle < s.end);
            if (!hit) { hideTooltip(); return; }
            showTooltip(e.clientX, e.clientY, `<span style="display:inline-block;width:10px;height:10px;border-radius:50%;background:${hit.color};margin-right:6px;"></span><strong>${hit.label}</strong><br>Jumlah: <strong>${hit.value}</strong><br>Persentase: <strong>${hit.pct}%</strong>`);
        };
        canvas._pieLeave = () => hideTooltip();
        canvas.addEventListener('mousemove', canvas._pieMove); canvas.addEventListener('mouseleave', canvas._pieLeave);
    };

    // ── Rendering Initialization ─────────────────────────────────────────────
    function renderDashboardCharts() {
        const totalLpju = payload.summary.total_lpju || 0;
        const totalDaya = payload.summary.total_daya || 0;

        // FIXED: Combine the separated arrays from PHP into the key-value object format required by drawPie()
        const countByDaya = {};
        (payload.doughnutLabels || []).forEach((label, i) => {
            countByDaya[label] = Number(payload.doughnutData[i] || 0);
        });

        const countByNama = {};
        (payload.pieLabels || []).forEach((label, i) => {
            countByNama[label] = Number(payload.pieData[i] || 0);
        });

        // DO NOT TOUCH BAR CHART
        const barCanvas = document.getElementById("chartBar");
        if (barCanvas) {
            const barResult = drawBar(barCanvas, totalLpju, totalDaya);
            if (barResult) attachBarTooltip(barCanvas, barResult.bars);
        }

        // DOUGHNUT CHARTS WILL NOW RENDER
        const dayaCanvas = document.getElementById("chartDoughnut");
        if (dayaCanvas) {
            const dayaResult = drawPie(dayaCanvas, countByDaya, totalLpju, true, false);
            if (dayaResult) attachPieTooltip(dayaCanvas, dayaResult.slices, dayaResult.cx, dayaResult.cy, dayaResult.innerR, dayaResult.outerR);
        }

        const namaCanvas = document.getElementById("chartPie");
        if (namaCanvas) {
            // Changed the 4th param to false and 5th param to true for polyline callouts
            const namaResult = drawPie(namaCanvas, countByNama, totalLpju, false, true);
            if (namaResult) attachPieTooltip(namaCanvas, namaResult.slices, namaResult.cx, namaResult.cy, namaResult.innerR, namaResult.outerR);
        }
    }

    renderDashboardCharts();
    window.addEventListener("resize", renderDashboardCharts);
});