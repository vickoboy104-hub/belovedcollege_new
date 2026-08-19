import { chromium } from 'playwright';

const baseURL = process.env.APP_URL || 'http://127.0.0.1:8000';
const viewport = { width: 390, height: 844 };

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

  await page.locator('main').waitFor({ state: 'visible' });
  const visibleText = await page.locator('body').innerText();
  if (!visibleText.includes('Lesson Notes') || !visibleText.includes('Assignments')) {
    fail(`${label}: expected portal overview content is not visible.`);
  }

  await assertHealthyPage(page, label);
  await assertMobileNavigation(page, label);
}

async function runStudent(browser) {
  const context = await browser.newContext({ viewport, isMobile: true, hasTouch: true });
  const page = await context.newPage();
  const errors = captureBrowserErrors(page);

  await login(page, '/student/login', 'BVS-JSS1-GEN-001', 'password');
  await page.goto(`${baseURL}/portal`, { waitUntil: 'networkidle' });
  await assertPortalOverview(page, 'Student portal');

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

const browser = await chromium.launch({ headless: true, channel: 'chrome' });
try {
  await runStudent(browser);
  await runParent(browser);
  console.log('Mobile portal smoke test passed at 390x844 for student and parent portals.');
} finally {
  await browser.close();
}
