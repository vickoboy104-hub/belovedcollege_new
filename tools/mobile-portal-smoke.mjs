import { chromium } from 'playwright';
import { mkdir } from 'node:fs/promises';

const baseURL = process.env.APP_URL || 'http://127.0.0.1:8000';
const viewport = { width: 390, height: 844 };
const screenshotDir = 'artifacts/finance-screenshots';

function fail(message) {
  throw new Error(message);
}

async function assertHealthyPage(page, label) {
  await page.waitForLoadState('networkidle');

  const dimensions = await page.evaluate(() => ({
    scrollWidth: document.documentElement.scrollWidth,
    clientWidth: document.documentElement.clientWidth,
    bodyScrollWidth: document.body?.scrollWidth ?? 0,
  }));

  const effectiveWidth = Math.max(dimensions.scrollWidth, dimensions.bodyScrollWidth);
  if (effectiveWidth > dimensions.clientWidth + 4) {
    fail(`${label}: horizontal overflow detected (${effectiveWidth}px > ${dimensions.clientWidth}px)`);
  }
}

async function assertMobileNavigation(page, label) {
  const toggle = page.locator('button[aria-label="Toggle sidebar navigation"]');
  await toggle.waitFor({ state: 'visible' });
  await toggle.click();

  const panel = page.locator('.mobile-sidebar-panel');
  await panel.waitFor({ state: 'visible' });
  await panel.getByText('Billing & Fees', { exact: true }).waitFor({ state: 'visible' });
  await panel.getByText('Assignments', { exact: true }).waitFor({ state: 'visible' });

  await panel.locator('button[aria-label="Close sidebar navigation"]').click();
  await panel.waitFor({ state: 'hidden' });
  await assertHealthyPage(page, `${label} after mobile navigation`);
}

async function login(page, path, identifier, password) {
  await page.goto(`${baseURL}${path}`, { waitUntil: 'networkidle' });
  await page.locator('input[name="login"]').fill(identifier);
  await page.locator('input[name="password"]').fill(password);
  await page.locator('button[type="submit"]').first().click();
  await page.waitForLoadState('networkidle');
}

function captureBrowserErrors(page) {
  const errors = [];

  page.on('pageerror', error => errors.push(`pageerror: ${error.message}`));
  page.on('console', message => {
    if (message.type() === 'error') errors.push(`console: ${message.text()}`);
  });
  page.on('response', response => {
    if (response.status() >= 500) errors.push(`HTTP ${response.status()}: ${response.url()}`);
  });

  return errors;
}

async function assertPortalOverview(page, label) {
  if (!page.url().includes('/portal')) fail(`${label} did not reach portal: ${page.url()}`);

  const main = page.locator('main');
  await main.waitFor({ state: 'visible' });
  await main.getByText('STUDENT PORTAL', { exact: true }).waitFor({ state: 'visible' });

  await assertHealthyPage(page, label);
  await assertMobileNavigation(page, label);
}

async function captureStudentPaymentFlow(page) {
  await page.goto(`${baseURL}/portal?section=billing`, { waitUntil: 'networkidle' });
  await page.getByText('Choose what you want to pay', { exact: true }).waitFor({ state: 'visible' });
  await assertHealthyPage(page, 'Student payment fee selection');
  await page.screenshot({ path: `${screenshotDir}/student-payment-page.png`, fullPage: true });

  const selectable = page.locator('input[type="checkbox"]:not([disabled]):visible').first();
  if (await selectable.count()) {
    await selectable.check();
    await page.getByRole('button', { name: 'Continue', exact: true }).click();
    await page.getByText('Choose Payment Method', { exact: true }).waitFor({ state: 'visible' });
    await assertHealthyPage(page, 'Student payment method selection');
    await page.screenshot({ path: `${screenshotDir}/payment-method-page.png`, fullPage: true });
  }
}

async function runStudent(browser) {
  const context = await browser.newContext({ viewport, isMobile: true, hasTouch: true });
  const page = await context.newPage();
  const errors = captureBrowserErrors(page);

  await login(page, '/student/login', 'BVS-JSS1-GEN-001', 'password');
  await page.goto(`${baseURL}/portal`, { waitUntil: 'networkidle' });
  await assertPortalOverview(page, 'Student portal');
  await captureStudentPaymentFlow(page);

  if (errors.length) fail(`Student portal browser errors:\n${errors.join('\n')}`);
  await context.close();
}

async function runParent(browser) {
  const context = await browser.newContext({ viewport, isMobile: true, hasTouch: true });
  const page = await context.newPage();
  const errors = captureBrowserErrors(page);

  await login(page, '/login', 'parent.jss-1-general.1@belovedschool.test', 'password');
  await page.goto(`${baseURL}/portal`, { waitUntil: 'networkidle' });
  await assertPortalOverview(page, 'Parent portal');

  const childSelect = page.locator('select[name="student"]');
  await childSelect.waitFor({ state: 'visible' });
  const initialChildren = await childSelect.locator('option').allTextContents();
  if (initialChildren.length !== 1 || !initialChildren[0].includes('Daniel Adeyemi')) {
    fail(`Parent portal: unexpected linked-child options: ${initialChildren.join(' | ')}`);
  }

  await page.goto(`${baseURL}/portal?student=2`, { waitUntil: 'networkidle' });
  const fallbackSelect = page.locator('select[name="student"]');
  await fallbackSelect.waitFor({ state: 'visible' });
  const fallbackChildren = await fallbackSelect.locator('option').allTextContents();
  if (fallbackChildren.length !== 1 || !fallbackChildren[0].includes('Daniel Adeyemi')) {
    fail(`Parent authorization fallback exposed an unexpected child: ${fallbackChildren.join(' | ')}`);
  }
  await assertHealthyPage(page, 'Parent authorization fallback');

  if (errors.length) fail(`Parent portal browser errors:\n${errors.join('\n')}`);
  await context.close();
}

async function runAdminFinance(browser) {
  const context = await browser.newContext({ viewport: { width: 1440, height: 1000 } });
  const page = await context.newPage();
  const errors = captureBrowserErrors(page);

  await login(page, '/login', 'admin@belovedschool.test', 'password');
  await page.goto(`${baseURL}/admin/finance/record-payment`, { waitUntil: 'networkidle' });
  await page.getByText('Payments and collections', { exact: true }).waitFor({ state: 'visible' });
  await page.screenshot({ path: `${screenshotDir}/admin-finance-page.png`, fullPage: true });

  await page.goto(`${baseURL}/admin/finance/recent-invoices`, { waitUntil: 'networkidle' });
  const printLink = page.getByRole('link', { name: 'Print / PDF' }).first();
  await printLink.waitFor({ state: 'visible' });
  const printHref = await printLink.getAttribute('href');
  if (!printHref) fail('Invoice register did not expose a printable invoice link.');

  await page.goto(new URL(printHref, baseURL).toString(), { waitUntil: 'networkidle' });
  await page.getByText('Student Invoice', { exact: true }).first().waitFor({ state: 'visible' });
  await page.screenshot({ path: `${screenshotDir}/invoice-print.png`, fullPage: true });

  if (errors.length) fail(`Admin finance browser errors:\n${errors.join('\n')}`);
  await context.close();
}

await mkdir(screenshotDir, { recursive: true });
const browser = await chromium.launch({ headless: true, channel: 'chrome' });
try {
  await runStudent(browser);
  await runParent(browser);
  await runAdminFinance(browser);
  console.log('Portal and finance smoke tests passed with all required finance screenshot evidence.');
} finally {
  await browser.close();
}
