<div class="entity-preview-modal" id="entityPreviewModal" hidden>
    <div class="entity-preview-backdrop" data-preview-close></div>

    <div class="entity-preview-card" role="dialog" aria-modal="true" aria-labelledby="entityPreviewTitle">
        <div class="entity-preview-header">
            <div class="entity-preview-avatar" data-preview-avatar></div>
            <div>
                <h2 id="entityPreviewTitle" data-preview-title></h2>
                <p data-preview-subtitle></p>
            </div>
            <button type="button" class="modal-close-btn" data-preview-close aria-label="Close preview">&times;</button>
        </div>

        <div class="entity-preview-grid" data-preview-fields></div>

        <div class="entity-preview-footer">
            <button type="button" class="btn btn-secondary" data-preview-close>Close</button>
            <a href="#" class="btn btn-primary" data-preview-profile>View Full Details</a>
        </div>
    </div>
</div>

@once
    <script>
        (() => {
            const modal = document.getElementById('entityPreviewModal');
            if (!modal || modal.dataset.bound === 'true') return;

            modal.dataset.bound = 'true';

            const avatar = modal.querySelector('[data-preview-avatar]');
            const title = modal.querySelector('[data-preview-title]');
            const subtitle = modal.querySelector('[data-preview-subtitle]');
            const fields = modal.querySelector('[data-preview-fields]');
            const profile = modal.querySelector('[data-preview-profile]');

            const setText = (node, value) => {
                if (node) node.textContent = value || '';
            };

            const close = () => {
                modal.hidden = true;
                document.body.classList.remove('entity-preview-open');
            };

            const open = (payload) => {
                const data = payload || {};

                setText(avatar, data.avatar || 'BS');
                setText(title, data.title || 'Record preview');
                setText(subtitle, data.subtitle || data.type || 'Beloved Schools');

                fields.innerHTML = '';
                (Array.isArray(data.fields) ? data.fields : []).forEach((field) => {
                    const item = document.createElement('div');
                    const label = document.createElement('span');
                    const value = document.createElement('strong');

                    label.textContent = field.label || '';
                    value.textContent = field.value ?? 'Not available';
                    item.append(label, value);
                    fields.append(item);
                });

                profile.href = data.profileUrl || '#';
                profile.textContent = data.ctaLabel || (['student', 'parent', 'staff'].includes(data.type) ? 'View Full Profile' : 'View Full Details');
                profile.hidden = !data.profileUrl;

                modal.hidden = false;
                document.body.classList.add('entity-preview-open');
            };

            document.addEventListener('click', (event) => {
                const trigger = event.target.closest('[data-preview]');
                if (trigger) {
                    event.preventDefault();

                    try {
                        open(JSON.parse(trigger.dataset.preview || '{}'));
                    } catch (error) {
                        console.warn('Preview data could not be parsed', error);
                    }

                    return;
                }

                if (event.target.closest('[data-preview-close]')) {
                    close();
                }
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && !modal.hidden) {
                    close();
                }
            });
        })();
    </script>
@endonce
