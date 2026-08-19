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

async function login(page, path, identifier, password) {
  await page.goto(`${baseURL}${path}`, { waitUntil: 'networkidle' });

  const identifierInput = page.locator('input[name="identifier"], input[name="email"]').first();
  await identifierInput.fill(identifier);
  await page.locator('input[name="password"]').fill(password);

  await Promise.all([
    page.waitForLoadState('networkidle'),
    page.locator('button[type="submit"]').first().click(),
  ]);
}

async function runStudent(browser) {
  const context = await browser.newContext({ viewport, isMobile: true, hasTouch: true });
  const page = await context.newPage();
  const errors = [];

  page.on('pageerror', error => errors.push(`pageerror: ${error.message}`));
  page.on('console', message => {
    if (message.type() === 'error') errors.push(`console: ${message.text()}`);
  });
  page.on('response', response => {
    if (response.status() >= 500) errors.push(`HTTP ${response.status()}: ${response.url()}`);
  });

  await login(page, '/student/login', 'BVS-JSS1-GEN-001', 'password');
  await page.goto(`${baseURL}/portal`, { waitUntil: 'networkidle' });

  if (!page.url().includes('/portal')) fail(`Student login did not reach portal: ${page.url()}`);
  await page.getByText('Daniel Adeyemi', { exact: false }).first().waitFor();
  await assertHealthyPage(page, 'Student portal');

  if (errors.length) fail(`Student portal browser errors:\n${errors.join('\n')}`);
  await context.close();
}

async function runParent(browser) {
  const context = await browser.newContext({ viewport, isMobile: true, hasTouch: true });
  const page = await context.newPage();
  const errors = [];

  page.on('pageerror', error => errors.push(`pageerror: ${error.message}`));
  page.on('console', message => {
    if (message.type() === 'error') errors.push(`console: ${message.text()}`);
  });
  page.on('response', response => {
    if (response.status() >= 500) errors.push(`HTTP ${response.status()}: ${response.url()}`);
  });

  await login(page, '/login', 'parent.jss-1-general.1@belovedschool.test', 'password');
  await page.goto(`${baseURL}/portal`, { waitUntil: 'networkidle' });

  if (!page.url().includes('/portal')) fail(`Parent login did not reach portal: ${page.url()}`);
  await page.getByText('Daniel Adeyemi', { exact: false }).first().waitFor();
  await assertHealthyPage(page, 'Parent portal');

  await page.goto(`${baseURL}/portal?student=2`, { waitUntil: 'networkidle' });
  await page.getByText('Daniel Adeyemi', { exact: false }).first().waitFor();
  const body = await page.locator('body').innerText();
  if (!body.includes('Daniel Adeyemi')) fail('Parent authorization fallback lost the linked child context.');
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
