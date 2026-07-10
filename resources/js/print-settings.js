/**
 * PrintSettings — Advanced Print Configuration System
 * Works without Alpine.js dependency. Pure vanilla JS.
 * Supports: receipts, fee lists, any printable card.
 */
(function () {
    'use strict';

    /* ── State ─────────────────────────────────────────────── */
    let state = {
        items: [],        // Array of { html: string } objects
        perPage: 1,
        paperSize: 'A4',       // 'A4' | 'A5' | 'Letter' | 'Legal'
        orientation: 'portrait', // 'portrait' | 'landscape'
        duplicate: false,  // duplicate single item to fill page
        mode: 'direct',    // 'direct' | 'pdf' (both use browser dialog)
        customPerPage: null,
    };

    /* ── Paper Dimensions (in mm) ────────────────────────── */
    const PAPER_DIMENSIONS = {
        A4: { width: 210, height: 297, label: 'A4' },
        A5: { width: 148, height: 210, label: 'A5' },
        Letter: { width: 215.9, height: 279.4, label: 'Letter' },
        Legal: { width: 215.9, height: 355.6, label: 'Legal' }
    };

    function getSelectedDimensions() {
        const base = PAPER_DIMENSIONS[state.paperSize] || PAPER_DIMENSIONS.A4;
        if (state.orientation === 'landscape') {
            return { width: base.height, height: base.width };
        }
        return { width: base.width, height: base.height };
    }

    /* ── Grid Calculation Engine ──────────────────────────── */
    function getGridDimensions(count, orientation) {
        if (count === 1) return { cols: 1, rows: 1 };
        if (count === 2) {
            return orientation === 'portrait' ? { cols: 1, rows: 2 } : { cols: 2, rows: 1 };
        }
        if (count === 4) return { cols: 2, rows: 2 };
        if (count === 6) {
            return orientation === 'portrait' ? { cols: 2, rows: 3 } : { cols: 3, rows: 2 };
        }
        if (count === 8) {
            return orientation === 'portrait' ? { cols: 2, rows: 4 } : { cols: 4, rows: 2 };
        }
        if (count === 9) return { cols: 3, rows: 3 };
        
        // General custom grid logic: try to build a square-ish aspect ratio grid
        const cols = Math.ceil(Math.sqrt(count));
        const rows = Math.ceil(count / cols);
        return orientation === 'portrait' ? { cols, rows } : { cols: rows, rows: cols };
    }

    /* ── Inject modal HTML ──────────────────────────────────── */
    function injectModal() {
        if (document.getElementById('ps-modal-overlay')) return;

        const html = `
<div id="ps-modal-overlay" role="dialog" aria-modal="true" aria-labelledby="ps-modal-title">
  <div id="ps-modal">
    <!-- Header -->
    <div id="ps-modal-header">
      <h2 id="ps-modal-title">
        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
        Advanced Print Settings
      </h2>
      <button class="ps-close-btn" id="ps-close-btn" aria-label="Close">
        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>

    <!-- Body: settings + preview -->
    <div id="ps-modal-body">

      <!-- Left: settings panel -->
      <div id="ps-settings-panel">

        <!-- Items per page -->
        <div class="ps-field-group">
          <div class="ps-label">Items per Page</div>
          <div class="ps-layout-grid">
            <button class="ps-layout-btn ps-active" data-pp="1">
              <div class="ps-layout-icon" style="display:grid;grid-template-columns:1fr;grid-template-rows:1fr;">
                <div class="ps-cell" style="grid-column:1;grid-row:1;"></div>
              </div>
              1 per page
            </button>
            <button class="ps-layout-btn" data-pp="2">
              <div class="ps-layout-icon" style="display:grid;grid-template-columns:1fr;grid-template-rows:1fr 1fr;gap:1px;">
                <div class="ps-cell"></div><div class="ps-cell"></div>
              </div>
              2 per page
            </button>
            <button class="ps-layout-btn" data-pp="4">
              <div class="ps-layout-icon" style="display:grid;grid-template-columns:1fr 1fr;grid-template-rows:1fr 1fr;gap:1px;">
                <div class="ps-cell"></div><div class="ps-cell"></div><div class="ps-cell"></div><div class="ps-cell"></div>
              </div>
              4 per page
            </button>
            <button class="ps-layout-btn" data-pp="6">
              <div class="ps-layout-icon" style="display:grid;grid-template-columns:1fr 1fr;grid-template-rows:1fr 1fr 1fr;gap:1px;">
                <div class="ps-cell"></div><div class="ps-cell"></div><div class="ps-cell"></div><div class="ps-cell"></div><div class="ps-cell"></div><div class="ps-cell"></div>
              </div>
              6 per page
            </button>
            <button class="ps-layout-btn" data-pp="8">
              <div class="ps-layout-icon" style="display:grid;grid-template-columns:1fr 1fr;grid-template-rows:repeat(4,1fr);gap:1px;">
                <div class="ps-cell"></div><div class="ps-cell"></div><div class="ps-cell"></div><div class="ps-cell"></div><div class="ps-cell"></div><div class="ps-cell"></div><div class="ps-cell"></div><div class="ps-cell"></div>
              </div>
              8 per page
            </button>
            <button class="ps-layout-btn" data-pp="9">
              <div class="ps-layout-icon" style="display:grid;grid-template-columns:1fr 1fr 1fr;grid-template-rows:1fr 1fr 1fr;gap:1px;">
                <div class="ps-cell"></div><div class="ps-cell"></div><div class="ps-cell"></div><div class="ps-cell"></div><div class="ps-cell"></div><div class="ps-cell"></div><div class="ps-cell"></div><div class="ps-cell"></div><div class="ps-cell"></div>
              </div>
              9 per page
            </button>
          </div>
          <!-- Custom -->
          <div style="display:flex;gap:0.5rem;align-items:center;margin-top:0.3rem;">
            <input type="number" id="ps-custom-input" class="ps-input" min="1" max="50" placeholder="Custom number…" style="flex:1;" />
            <button class="ps-btn ps-btn-secondary" id="ps-custom-apply" style="flex-shrink:0;padding:0.45rem 0.75rem;font-size:0.68rem;">Apply</button>
          </div>
          <div class="ps-hint">Select how many items should appear on each sheet.</div>
        </div>

        <hr class="ps-divider">

        <!-- Duplicate option -->
        <div class="ps-field-group" id="ps-duplicate-group">
          <div class="ps-label">Fill Page Option</div>
          <label style="display:flex;align-items:flex-start;gap:0.6rem;cursor:pointer;">
            <input type="checkbox" id="ps-duplicate-cb" style="margin-top:2px;flex-shrink:0;accent-color:#1d4ed8;">
            <div>
              <div style="font-size:0.78rem;font-weight:700;color:#0f172a;">Duplicate to fill page</div>
              <div class="ps-hint" style="margin-top:2px;">Repeat selected item to fill all slots on each sheet.</div>
            </div>
          </label>
        </div>

        <hr class="ps-divider">

        <!-- Paper Settings -->
        <div class="ps-field-group">
          <div class="ps-label">Paper Size</div>
          <select id="ps-paper-size-select" class="ps-select">
            <option value="A4" selected>A4 (210 x 297 mm)</option>
            <option value="A5">A5 (148 x 210 mm)</option>
            <option value="Letter">Letter (8.5" x 11")</option>
            <option value="Legal">Legal (8.5" x 14")</option>
          </select>
        </div>

        <div class="ps-field-group">
          <div class="ps-label">Orientation</div>
          <div class="ps-mode-toggle" id="ps-orientation-toggle">
            <button class="ps-mode-btn ps-active" data-orientation="portrait">Portrait</button>
            <button class="ps-mode-btn" data-orientation="landscape">Landscape</button>
          </div>
        </div>

        <hr class="ps-divider">

        <!-- Print mode -->
        <div class="ps-field-group">
          <div class="ps-label">Print Destination</div>
          <div class="ps-mode-toggle">
            <button class="ps-mode-btn ps-active" data-mode="direct">
              🖨 Direct Printer
            </button>
            <button class="ps-mode-btn" data-mode="pdf">
              📄 Save as PDF
            </button>
          </div>
          <div class="ps-hint" id="ps-mode-hint">Uses the browser's print dialog. Select your printer to print directly.</div>
        </div>

        <!-- Item count info -->
        <div id="ps-item-info" style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:0.65rem;padding:0.6rem 0.75rem;font-size:0.75rem;font-weight:600;color:#1e40af;margin-top:0.5rem;">
          📋 <span id="ps-item-count-text">0 item(s) selected</span>
        </div>

      </div><!-- /settings panel -->

      <!-- Right: preview panel -->
      <div id="ps-preview-panel">
        <div id="ps-preview-label" id="ps-preview-title">A4 Page Preview</div>
        <div id="ps-a4-preview">
          <div id="ps-preview-content"></div>
        </div>
        <div class="ps-hint" style="text-align:center;margin-top:0.4rem;">This preview shows how items will look on the selected paper layout.</div>
      </div>

    </div><!-- /modal-body -->

    <!-- Footer -->
    <div id="ps-modal-footer">
      <div class="ps-footer-info" id="ps-footer-summary">Ready to print</div>
      <div class="ps-footer-actions">
        <button class="ps-btn ps-btn-secondary" id="ps-cancel-btn">Cancel</button>
        <button class="ps-btn ps-btn-secondary" id="ps-preview-btn">
          <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
          Refresh Preview
        </button>
        <button class="ps-btn ps-btn-accent" id="ps-print-btn">
          <svg width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
          Print / Save PDF
        </button>
      </div>
    </div>

  </div><!-- /modal -->
</div><!-- /overlay -->`;

        const div = document.createElement('div');
        div.innerHTML = html;
        document.body.appendChild(div.firstElementChild);
        bindEvents();
    }

    /* ── Bind modal events ───────────────────────────────────── */
    function bindEvents() {
        const overlay = document.getElementById('ps-modal-overlay');

        // Close
        document.getElementById('ps-close-btn').addEventListener('click', close);
        document.getElementById('ps-cancel-btn').addEventListener('click', close);
        overlay.addEventListener('click', function (e) {
            if (e.target === overlay) close();
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && overlay.classList.contains('ps-open')) close();
        });

        // Layout buttons (presets)
        overlay.querySelectorAll('.ps-layout-btn[data-pp]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const pp = parseInt(btn.dataset.pp, 10);
                setPerPage(pp, false);
            });
        });

        // Custom apply
        document.getElementById('ps-custom-apply').addEventListener('click', function () {
            const val = parseInt(document.getElementById('ps-custom-input').value, 10);
            if (val && val >= 1 && val <= 100) {
                setPerPage(val, true);
            }
        });
        document.getElementById('ps-custom-input').addEventListener('keydown', function (e) {
            if (e.key === 'Enter') document.getElementById('ps-custom-apply').click();
        });

        // Duplicate checkbox
        document.getElementById('ps-duplicate-cb').addEventListener('change', function () {
            state.duplicate = this.checked;
            renderPreview();
            updateSummary();
        });

        // Paper Size select
        document.getElementById('ps-paper-size-select').addEventListener('change', function () {
            state.paperSize = this.value;
            renderPreview();
            updateSummary();
        });

        // Orientation toggle
        document.getElementById('ps-orientation-toggle').querySelectorAll('.ps-mode-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                document.getElementById('ps-orientation-toggle').querySelectorAll('.ps-mode-btn').forEach(function (b) {
                    b.classList.remove('ps-active');
                });
                btn.classList.add('ps-active');
                state.orientation = btn.dataset.orientation;
                renderPreview();
                updateSummary();
            });
        });

        // Mode buttons
        overlay.querySelectorAll('.ps-mode-btn[data-mode]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                overlay.querySelectorAll('.ps-mode-btn[data-mode]').forEach(function (b) { b.classList.remove('ps-active'); });
                btn.classList.add('ps-active');
                state.mode = btn.dataset.mode;
                const hint = document.getElementById('ps-mode-hint');
                if (state.mode === 'pdf') {
                    hint.textContent = 'Opens browser print dialog. Select "Save as PDF" as the destination.';
                } else {
                    hint.textContent = 'Uses the browser\'s print dialog. Select your printer to print directly.';
                }
                updateSummary();
            });
        });

        // Preview button
        document.getElementById('ps-preview-btn').addEventListener('click', renderPreview);

        // Print button
        document.getElementById('ps-print-btn').addEventListener('click', function () {
            executePrint();
        });
    }

    /* ── Set per-page count ──────────────────────────────────── */
    function setPerPage(pp, isCustom) {
        state.perPage = pp;
        state.customPerPage = isCustom ? pp : null;

        // Update layout button active states
        document.querySelectorAll('.ps-layout-btn[data-pp]').forEach(function (btn) {
            btn.classList.toggle('ps-active', !isCustom && parseInt(btn.dataset.pp, 10) === pp);
        });

        // If custom, clear all preset buttons
        if (isCustom) {
            document.querySelectorAll('.ps-layout-btn[data-pp]').forEach(function (btn) {
                btn.classList.remove('ps-active');
            });
        }

        renderPreview();
        updateSummary();
    }

    /* ── Sanitize item HTML ──────────────────────────────────── */
    function sanitizeHtml(html) {
        const tmp = document.createElement('div');
        tmp.innerHTML = html;

        // Remove no-print and control elements
        tmp.querySelectorAll('.no-print, .ps-controls-bar, #ps-modal-overlay, nav, header, footer, .sidebar').forEach(function (el) { el.remove(); });

        // Remove max-width constraints
        ['mx-auto','max-w-4xl','max-w-6xl','container','min-h-screen'].forEach(function (cls) {
            tmp.querySelectorAll('.' + cls).forEach(function (el) { el.classList.remove(cls); });
        });

        return tmp.innerHTML;
    }

    /* ── Build preview ───────────────────────────────────────── */
    function renderPreview() {
        const container = document.getElementById('ps-preview-content');
        const a4 = document.getElementById('ps-a4-preview');
        const previewTitle = document.getElementById('ps-preview-label');
        if (!container || !a4) return;

        // Get dynamic sheet dimensions
        const dimsMM = getSelectedDimensions();

        if (previewTitle) {
            previewTitle.textContent = state.paperSize + ' Page Preview (' + state.orientation + ')';
        }

        const pp = state.perPage;

        // Calculate dynamic grid dimensions (cols and rows)
        const gridDims = getGridDimensions(pp, state.orientation);

        // Build the items to show
        const previewItems = buildItemList(pp);

        // Build grid
        const grid = document.createElement('div');
        grid.className = 'ps-preview-grid';
        grid.style.cssText = 'width:100%;height:100%;position:absolute;inset:0;box-sizing:border-box;display:grid;gap:6px;padding:8px;';
        grid.style.gridTemplateColumns = 'repeat(' + gridDims.cols + ', 1fr)';
        grid.style.gridTemplateRows = 'repeat(' + gridDims.rows + ', 1fr)';

        previewItems.forEach(function (item) {
            const cell = document.createElement('div');
            cell.className = 'ps-preview-cell';

            if (item && item.html) {
                const inner = document.createElement('div');
                inner.className = 'ps-preview-cell-inner';
                inner.innerHTML = sanitizeHtml(item.html);
                cell.appendChild(inner);

                // Scale to fit after render
                requestAnimationFrame(function () {
                    scaleToFit(inner, cell);
                });
            } else {
                // Placeholder
                const ph = document.createElement('div');
                ph.className = 'ps-preview-placeholder';
                ph.innerHTML = '<div class="ps-preview-placeholder-bar" style="width:70%;"></div><div class="ps-preview-placeholder-bar" style="width:50%;"></div><div class="ps-preview-placeholder-bar" style="width:60%;"></div>';
                cell.appendChild(ph);
            }

            grid.appendChild(cell);
        });

        a4.style.position = 'relative';
        a4.style.overflow = 'hidden';
        container.innerHTML = '';
        container.style.cssText = 'position:absolute;inset:0;';
        container.appendChild(grid);

        // Make A4 preview container adapt to dynamic aspect ratios
        const panel = document.getElementById('ps-preview-panel');
        if (panel) {
            const panelW = panel.clientWidth - 40;
            const a4H = panelW * (dimsMM.height / dimsMM.width);
            const maxH = panel.clientHeight - 80;
            const actualW = a4H > maxH ? maxH * (dimsMM.width / dimsMM.height) : panelW;
            
            a4.style.width = actualW + 'px';
            a4.style.height = (actualW * dimsMM.height / dimsMM.width) + 'px';
            a4.style.margin = '0 auto';
        }
    }

    /* ── Scale element to fit container ─────────────────────── */
    function scaleToFit(inner, cell) {
        if (!inner || !cell) return;
        
        function apply() {
            inner.style.transform = '';
            const cW = cell.clientWidth;
            const cH = cell.clientHeight;
            const iW = 900; // Fixed natural rendering width
            const iH = inner.scrollHeight; // Natural height at 900px width
            if (iW === 0 || iH === 0 || cW === 0 || cH === 0) return;
            const scale = Math.min(cW / iW, cH / iH);
            
            // Calculate offsets to center the scaled item inside the cell
            const xOffset = (cW - (iW * scale)) / 2;
            const yOffset = (cH - (iH * scale)) / 2;
            
            inner.style.transform = 'translate(' + xOffset + 'px, ' + yOffset + 'px) scale(' + scale + ')';
            inner.style.transformOrigin = 'top left';
        }
        
        apply();
        setTimeout(apply, 80);
    }

    /* ── Build item list for a page ──────────────────────────── */
    function buildItemList(count) {
        const items = state.items;
        if (!items || items.length === 0) {
            return new Array(count).fill(null);
        }
        if (state.duplicate) {
            const out = [];
            for (let i = 0; i < count; i++) {
                out.push(items[i % items.length]);
            }
            return out;
        }
        // Return up to `count` items
        const out = [];
        for (let i = 0; i < count; i++) {
            out.push(items[i] || null);
        }
        return out;
    }

    /* ── Update summary footer text ──────────────────────────── */
    function updateSummary() {
        const el = document.getElementById('ps-footer-summary');
        const countEl = document.getElementById('ps-item-count-text');
        if (!el) return;

        const total = state.items.length;
        const pp = state.perPage;

        if (countEl) {
            countEl.textContent = total + ' item' + (total !== 1 ? 's' : '') + ' loaded';
        }

        let pages;
        if (state.duplicate) {
            pages = 1;
            el.textContent = '1 page · ' + state.paperSize + ' (' + state.orientation + ') · ' + pp + ' cop' + (pp !== 1 ? 'ies' : 'y') + '/sheet';
        } else {
            pages = Math.ceil(total / pp);
            el.textContent = pages + ' sheet(s) · ' + state.paperSize + ' (' + state.orientation + ') · ' + pp + ' item' + (pp !== 1 ? 's' : '') + '/sheet';
        }
    }

    /* ── Execute print ───────────────────────────────────────── */
    function executePrint() {
        const pp = state.perPage;
        const dimsMM = getSelectedDimensions();
        const gridDims = getGridDimensions(pp, state.orientation);

        // Build list of all items to print
        let allItems = [];
        if (state.duplicate) {
            for (let i = 0; i < pp; i++) {
                allItems.push(state.items[i % state.items.length]);
            }
        } else {
            allItems = state.items.slice();
        }

        // Chunk into pages
        const pages = [];
        for (let i = 0; i < allItems.length; i += pp) {
            pages.push(allItems.slice(i, i + pp));
        }

        // Build print wrapper
        const wrapper = document.createElement('div');
        wrapper.id = 'ps-print-wrapper';
        wrapper.style.width = dimsMM.width + 'mm';

        pages.forEach(function (pageItems) {
            const sheet = document.createElement('div');
            sheet.className = 'ps-sheet';
            sheet.style.width = dimsMM.width + 'mm';
            sheet.style.height = dimsMM.height + 'mm';

            const grid = document.createElement('div');
            grid.className = 'ps-sheet-grid';
            grid.style.cssText = 'display:grid !important;width:100%;height:100%;box-sizing:border-box;gap:5mm;padding:8mm;';
            grid.style.gridTemplateColumns = 'repeat(' + gridDims.cols + ', 1fr)';
            grid.style.gridTemplateRows = 'repeat(' + gridDims.rows + ', 1fr)';

            pageItems.forEach(function (item) {
                const cell = document.createElement('div');
                cell.className = 'ps-item-cell';

                const inner = document.createElement('div');
                inner.className = 'ps-item-cell-inner';
                inner.innerHTML = item ? sanitizeHtml(item.html) : '';
                cell.appendChild(inner);
                grid.appendChild(cell);
            });

            // Pad with empty cells if needed
            while (grid.children.length < pp) {
                const empty = document.createElement('div');
                empty.className = 'ps-item-cell';
                grid.appendChild(empty);
            }

            sheet.appendChild(grid);
            wrapper.appendChild(sheet);
        });

        // Add dynamic style block to configure paper size & margins dynamically in print layout
        const styleBlock = document.createElement('style');
        styleBlock.id = 'ps-dynamic-print-style';
        styleBlock.innerHTML = '@media print { @page { size: ' + dimsMM.width + 'mm ' + dimsMM.height + 'mm; margin: 0; } }';
        document.head.appendChild(styleBlock);

        document.body.appendChild(wrapper);

        // Close modal before printing
        close();

        // Allow browser to render, then compute scale transforms
        setTimeout(function () {
            wrapper.querySelectorAll('.ps-item-cell').forEach(function (cell) {
                const inner = cell.querySelector('.ps-item-cell-inner');
                if (!inner) return;
                inner.style.transform = '';
                const cW = cell.clientWidth;
                const cH = cell.clientHeight;
                const iW = 900; // Fixed natural rendering width
                const iH = inner.scrollHeight;
                if (iW > 0 && iH > 0 && cW > 0 && cH > 0) {
                    const scale = Math.min(cW / iW, cH / iH);
                    const xOffset = (cW - (iW * scale)) / 2;
                    const yOffset = (cH - (iH * scale)) / 2;
                    inner.style.transform = 'translate(' + xOffset + 'px, ' + yOffset + 'px) scale(' + scale + ')';
                    inner.style.transformOrigin = 'top left';
                }
            });

            setTimeout(function () {
                window.print();
                
                // Cleanup print wrapper & dynamic style block
                setTimeout(function () {
                    if (document.body.contains(wrapper)) {
                        document.body.removeChild(wrapper);
                    }
                    const dynamicStyle = document.getElementById('ps-dynamic-print-style');
                    if (dynamicStyle) {
                        dynamicStyle.remove();
                    }
                }, 500);
            }, 250);
        }, 200);
    }

    /* ── Open modal ──────────────────────────────────────────── */
    function open(itemsInput, options) {
        options = options || {};
        injectModal();

        // Normalize items to array of { html: string }
        state.items = [];
        if (typeof itemsInput === 'string') {
            if (itemsInput.trim().startsWith('<')) {
                state.items = [{ html: itemsInput }];
            } else {
                // CSS selector
                const nodes = Array.from(document.querySelectorAll(itemsInput));
                state.items = nodes.map(function (n) { return { html: n.outerHTML }; });
            }
        } else if (Array.isArray(itemsInput)) {
            state.items = itemsInput.map(function (item) {
                if (typeof item === 'string') return { html: item };
                return item;
            });
        }

        // Apply options
        state.perPage = parseInt(options.itemsPerPage, 10) || 1;
        state.duplicate = options.duplicate !== undefined ? !!options.duplicate : (state.items.length <= 1);
        state.mode = options.mode || 'direct';
        state.paperSize = options.paperSize || 'A4';
        state.orientation = options.orientation || 'portrait';

        // Restore UI state
        document.querySelectorAll('.ps-layout-btn[data-pp]').forEach(function (btn) {
            btn.classList.toggle('ps-active', parseInt(btn.dataset.pp, 10) === state.perPage);
        });
        const dupCb = document.getElementById('ps-duplicate-cb');
        if (dupCb) dupCb.checked = state.duplicate;

        const sizeSelect = document.getElementById('ps-paper-size-select');
        if (sizeSelect) sizeSelect.value = state.paperSize;

        document.getElementById('ps-orientation-toggle').querySelectorAll('.ps-mode-btn').forEach(function (btn) {
            btn.classList.toggle('ps-active', btn.dataset.orientation === state.orientation);
        });

        // Show
        document.getElementById('ps-modal-overlay').classList.add('ps-open');

        // Render preview after layout is visible
        setTimeout(renderPreview, 50);
        updateSummary();
    }

    /* ── Close modal ─────────────────────────────────────────── */
    function close() {
        const overlay = document.getElementById('ps-modal-overlay');
        if (overlay) overlay.classList.remove('ps-open');
    }

    /* ── Public API ──────────────────────────────────────────── */
    window.PrintSettings = { open, close };

    // Backwards-compatible shim for existing `openPrintSettings(...)` calls
    window.openPrintSettings = function (itemsInput, options) {
        window.PrintSettings.open(itemsInput, options);
    };

    // Listen for custom event (Alpine-style compat)
    window.addEventListener('open-print-settings', function (e) {
        const detail = e.detail || {};
        const items = detail.html || detail.receipts || detail.selector;
        window.PrintSettings.open(items, detail.options || {});
    });

    // Auto-init: if page has a .ps-trigger-btn, wire it up
    document.addEventListener('DOMContentLoaded', function () {
        injectModal();

        // Auto-wire any button with data-ps-selector attribute
        document.querySelectorAll('[data-ps-selector]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                window.PrintSettings.open(btn.dataset.psSelector, {
                    itemsPerPage: parseInt(btn.dataset.psPerPage || '1', 10),
                    duplicate: btn.dataset.psDuplicate === 'true',
                });
            });
        });
    });

})();
