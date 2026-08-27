(() => {
    const catalogElement = document.getElementById('payment-gateway-catalog');
    if (!catalogElement) return;

    let gateways = [];
    try {
        gateways = JSON.parse(catalogElement.textContent || '[]').filter((gateway) => gateway.available);
    } catch (error) {
        gateways = [];
    }

    const providerPattern = '(paystack|palmpay|flutterwave|monnify|opay)';
    const bundlePattern = new RegExp(`/payments/checkout/${providerPattern}(?:\\?.*)?$`);
    const individualPattern = new RegExp(`/payments/[^/]+/checkout/${providerPattern}(?:\\?.*)?$`);

    const preferredGateway = () => gateways.find((gateway) => gateway.recommended) || gateways[0] || null;

    const providerAction = (sampleAction, provider) =>
        sampleAction.replace(new RegExp(`/${providerPattern}(?:\\?.*)?$`), `/${provider}`);

    const createGatewayChoice = (gateway, groupName, checked = false) => {
        const label = document.createElement('label');
        label.className = 'payment-gateway-choice';
        label.dataset.provider = gateway.value;

        const radio = document.createElement('input');
        radio.type = 'radio';
        radio.name = groupName;
        radio.value = gateway.value;
        radio.className = 'payment-gateway-radio';
        radio.checked = checked;

        const mark = document.createElement('span');
        mark.className = 'payment-gateway-mark';
        mark.textContent = gateway.initials || gateway.label.slice(0, 2).toUpperCase();

        const copy = document.createElement('span');
        copy.className = 'payment-gateway-copy';

        const titleRow = document.createElement('span');
        titleRow.className = 'payment-gateway-title-row';

        const name = document.createElement('span');
        name.className = 'payment-gateway-label';
        name.textContent = gateway.label;
        titleRow.append(name);

        if (gateway.recommended) {
            const badge = document.createElement('span');
            badge.className = 'payment-test-badge';
            badge.textContent = 'Recommended for testing';
            titleRow.append(badge);
        }

        const description = document.createElement('span');
        description.className = 'payment-gateway-description';
        description.textContent = gateway.description || 'Secure online payment method.';

        const meta = document.createElement('span');
        meta.className = 'payment-gateway-meta';
        meta.textContent = gateway.mode || 'Online checkout';

        copy.append(titleRow, description, meta);
        label.append(radio, mark, copy);

        const syncSelected = () => {
            label.classList.toggle('is-selected', radio.checked);
        };
        radio.addEventListener('change', () => {
            label.closest('.payment-method-grid')
                ?.querySelectorAll('.payment-gateway-choice')
                .forEach((choice) => choice.classList.remove('is-selected'));
            syncSelected();
        });
        syncSelected();

        return { label, radio };
    };

    const emptyNotice = (compact = false) => {
        const notice = document.createElement(compact ? 'span' : 'p');
        notice.className = compact ? 'payment-unavailable-inline' : 'payment-unavailable-notice';
        notice.textContent = compact
            ? 'Online payment is currently unavailable.'
            : 'Online payment is temporarily unavailable. Use the school bank-transfer instructions or contact the accounts office.';
        return notice;
    };

    const bundleForms = [...document.querySelectorAll('form')].filter((form) =>
        [...form.querySelectorAll('button[formaction]')].some((button) => bundlePattern.test(button.getAttribute('formaction') || '')),
    );

    const individualGroups = new Map();
    document.querySelectorAll('form[action*="/checkout/"]').forEach((form) => {
        const action = form.getAttribute('action') || '';
        if (!individualPattern.test(action)) return;
        const container = form.parentElement;
        if (!container) return;
        if (!individualGroups.has(container)) individualGroups.set(container, []);
        individualGroups.get(container).push(form);
    });

    if (gateways.length === 0) {
        bundleForms.forEach((form) => {
            form.querySelectorAll('button[formaction*="/payments/checkout/"]').forEach((button) => button.remove());
            form.append(emptyNotice());
        });
        individualGroups.forEach((forms, container) => {
            forms.forEach((form) => form.remove());
            container.append(emptyNotice(true));
        });
        return;
    }

    bundleForms.forEach((form, formIndex) => {
        const templateButton = [...form.querySelectorAll('button[formaction]')]
            .find((button) => bundlePattern.test(button.getAttribute('formaction') || ''));
        if (!templateButton) return;

        const sampleAction = templateButton.getAttribute('formaction') || '';
        form.querySelectorAll('button[formaction*="/payments/checkout/"]').forEach((button) => button.remove());

        const flow = document.createElement('section');
        flow.className = 'payment-checkout-flow';
        flow.setAttribute('aria-label', 'Choose payment method');

        const heading = document.createElement('div');
        heading.className = 'payment-step-heading';
        heading.innerHTML = '<span class="payment-step-number">2</span><span><strong>Choose payment method</strong><small>Select one secure checkout option for the bills you ticked above.</small></span>';

        const grid = document.createElement('div');
        grid.className = 'payment-method-grid';

        const selectedDefault = preferredGateway();
        let selectedProvider = selectedDefault?.value || '';
        gateways.forEach((gateway) => {
            const { label, radio } = createGatewayChoice(
                gateway,
                `payment_method_bundle_${formIndex}`,
                gateway.value === selectedProvider,
            );
            radio.addEventListener('change', () => {
                selectedProvider = radio.value;
                updateState();
            });
            grid.append(label);
        });

        const footer = document.createElement('div');
        footer.className = 'payment-selection-footer';

        const summary = document.createElement('div');
        summary.className = 'payment-selection-summary';

        const continueButton = document.createElement('button');
        continueButton.type = 'submit';
        continueButton.className = 'payment-continue-button';
        continueButton.textContent = 'Continue to Secure Payment →';

        footer.append(summary, continueButton);
        flow.append(heading, grid, footer);
        form.append(flow);

        const updateState = () => {
            const selectedBills = form.querySelectorAll('input[name="invoice_ids[]"]');
            const count = selectedBills.length;
            continueButton.disabled = count === 0 || !selectedProvider;
            continueButton.formAction = providerAction(sampleAction, selectedProvider || selectedDefault.value);
            summary.textContent = count === 0
                ? 'Step 1: Tick at least one unpaid bill to continue.'
                : `${count} bill${count === 1 ? '' : 's'} selected • ${gateways.find((gateway) => gateway.value === selectedProvider)?.label || 'Payment method'} chosen`;
            summary.classList.toggle('is-ready', count > 0 && Boolean(selectedProvider));
        };

        new MutationObserver(updateState).observe(form, { childList: true, subtree: true });
        updateState();
    });

    individualGroups.forEach((forms, container) => {
        const sampleForm = forms[0];
        const sampleAction = sampleForm.getAttribute('action') || '';
        const csrf = sampleForm.querySelector('input[name="_token"]')?.cloneNode(true);
        forms.forEach((form) => form.remove());

        const wrapper = document.createElement('div');
        wrapper.className = 'payment-single-invoice-flow';

        const select = document.createElement('select');
        select.className = 'payment-method-select';
        select.setAttribute('aria-label', 'Choose payment method for this bill');
        gateways.forEach((gateway) => {
            const option = document.createElement('option');
            option.value = gateway.value;
            option.textContent = `${gateway.label}${gateway.recommended ? ' — Recommended' : ''}`;
            if (gateway.recommended) option.selected = true;
            select.append(option);
        });

        const payForm = document.createElement('form');
        payForm.method = 'POST';
        payForm.action = providerAction(sampleAction, select.value || preferredGateway().value);
        payForm.className = 'payment-single-invoice-form';
        if (csrf) payForm.append(csrf);

        const button = document.createElement('button');
        button.type = 'submit';
        button.className = 'payment-single-invoice-button';
        button.textContent = 'Pay this bill →';
        payForm.append(button);

        select.addEventListener('change', () => {
            payForm.action = providerAction(sampleAction, select.value);
        });

        wrapper.append(select, payForm);
        container.append(wrapper);
    });
})();
