# Payment gateway setup

Beloved Schools includes an administrator-managed gateway registry for Paystack, PalmPay, Flutterwave, and Monnify.

Open **Dashboard → Payment Gateways** or visit:

```text
/admin/payment-gateways
```

Only a gateway that is both **enabled** and **fully configured** appears to students and parents. The default enabled choices are Paystack and PalmPay, but an incomplete gateway is automatically hidden from checkout.

## Security rules

- Never paste a secret key into GitHub, source code, a support ticket, email, or chat.
- Enter credentials only in the authenticated Payment Gateways administration screen or a protected production secret manager.
- Secret values are encrypted in the database using Laravel's `APP_KEY`.
- Leaving a secret field blank preserves the existing encrypted value.
- Keep `APP_KEY` backed up securely. Changing it without re-encrypting stored secrets makes them unreadable.
- Configure and test sandbox/test credentials before enabling live credentials.
- A browser callback is never enough to mark a fee as paid. The application verifies the payment with the provider's server and checks reference, amount, currency, and successful status.

## Paystack

Required:

- Public key
- Secret key
- Webhook URL shown on the settings page

The application initializes the transaction from the backend, sends the amount in kobo, redirects the payer to Paystack Checkout, and verifies the returned reference from the backend before settling an invoice.

Official documentation:

- https://paystack.com/docs/api/transaction/
- https://paystack.com/docs/payments/webhooks/

## PalmPay

PalmPay merchant API access is supplied through merchant onboarding and may differ by merchant product or contract. The administration screen accepts the values commonly supplied during onboarding:

- Merchant ID
- App ID
- Official checkout endpoint
- Public key
- Private key
- Webhook secret

PalmPay is intentionally fail-closed in the current adapter. It can start only when the required merchant configuration exists, and it cannot mark an invoice as paid until PalmPay supplies the school's exact server-verification endpoint, request-signing algorithm, response schema, and webhook-signature rules. Do not substitute callback query parameters for server verification.

## Flutterwave

Required for the hosted Standard integration:

- Public key
- Secret key
- Webhook secret hash

Additional dashboard fields are available for:

- Encryption key
- OAuth client ID
- OAuth client secret
- Checkout payment options

The current checkout adapter creates a hosted Flutterwave payment, receives a transaction ID on return, re-queries Flutterwave, and verifies the reference, amount, currency, and successful status. Webhooks are signature-checked and then re-verified before settlement.

Official documentation:

- https://developer.flutterwave.com/docs/flutterwave-standard-1
- https://developer.flutterwave.com/docs/webhooks
- https://developer.flutterwave.com/docs/authentication

## Monnify

Required:

- API key
- Secret key
- Contract code
- Sandbox or live environment
- Allowed payment methods, such as `CARD,ACCOUNT_TRANSFER,USSD`

The adapter obtains an access token using the API key and secret, initializes hosted checkout, and verifies the payment reference before settlement. Webhook notifications are signature-checked and re-verified through Monnify before any invoice is updated.

Official documentation:

- https://developers.monnify.com/api/

## Callback and webhook configuration

The administration page displays the exact environment-specific URLs to copy into each provider dashboard. Production URLs must use HTTPS and the public production domain, not `127.0.0.1`, `localhost`, or a temporary private Codespace URL.

Typical paths are:

```text
/payments/callback/paystack
/webhooks/paystack
/payments/callback/flutterwave
/webhooks/flutterwave
/payments/callback/monnify
/webhooks/monnify
/payments/callback/palmpay
/webhooks/palmpay
```

## Launch sequence

1. Deploy to a public HTTPS staging domain.
2. Enter test or sandbox credentials in the Payment Gateways screen.
3. Copy the displayed callback and webhook URLs into the provider dashboard.
4. Enable one gateway at a time.
5. Pay a small test invoice.
6. Confirm the payment, invoice balance, receipt, audit log, and provider dashboard all agree.
7. Test failed, abandoned, duplicate, underpaid, overpaid, and replayed notifications.
8. Replace test credentials with live credentials only after the complete workflow passes.
9. Keep PalmPay disabled until merchant-specific verification is implemented and tested.
