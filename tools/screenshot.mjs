import playwright from 'playwright';
import fs from 'fs';

async function autoScroll(page) {
  await page.evaluate(async () => {
    await new Promise((resolve) => {
      const distance = window.innerHeight / 2;
      let totalHeight = 0;
      const timer = setInterval(() => {
        window.scrollBy(0, distance);
        totalHeight += distance;
        if (totalHeight >= document.body.scrollHeight) {
          clearInterval(timer);
          resolve();
        }
      }, 250);
    });
  });
}

// If CHROME_PATH is set, use that Chrome/Chromium executable instead of Playwright-managed browsers.
(async ()=>{
  const chromePath = process.env.CHROME_PATH || process.env.CHROME || null;
  const launchOptions = { headless: true };
  if (chromePath) {
    launchOptions.executablePath = chromePath;
  } else {
    launchOptions.channel = 'chrome';
  }
  const browser = await playwright.chromium.launch(launchOptions);
  const context = await browser.newContext({ viewport: { width: 1365, height: 768 } });
  const page = await context.newPage();
  const pages = [
    {name:'dashboard', url:'http://127.0.0.1:8000/admin'},
    {name:'students', url:'http://127.0.0.1:8000/admin/people/students?view=directory'},
    {name:'parents', url:'http://127.0.0.1:8000/admin/people/parents'},
    {name:'welcome', url:'http://127.0.0.1:8000/'},
  ];
  const outDir = 'tools/screenshots';
  if(!fs.existsSync(outDir)) fs.mkdirSync(outDir, {recursive:true});

  for(const p of pages){
    try{
      await page.goto(p.url, {waitUntil:'networkidle', timeout:30000});
      await page.waitForTimeout(1000);
      await autoScroll(page);
      await page.waitForTimeout(500);
      await page.screenshot({path:`${outDir}/${p.name}.png`, fullPage:true});
      console.log('Captured',p.url);
    }catch(e){
      console.error('Failed',p.url,e.message);
      fs.writeFileSync(`${outDir}/${p.name}.error.txt`, e.stack);
    }
  }

  await browser.close();
})();