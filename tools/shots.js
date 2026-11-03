const puppeteer = require('puppeteer-core');
const path = require('path');
const root = path.resolve(__dirname, '..');
const url = 'file:///' + path.join(root, 'frontend', 'registration.html').replace(/\\/g, '/');
const out = (n) => path.join(root, 'shots', n);

(async () => {
  const b = await puppeteer.launch({
    executablePath: 'C:/Program Files/Google/Chrome/Application/chrome.exe',
    headless: true,
  });
  const p = await b.newPage();
  await p.setViewport({ width: 1100, height: 900 });
  await p.goto(url);
  await p.screenshot({ path: out('q1_1_empty_form.png') });

  // intercept the real POST so submitting a valid form doesn't navigate away
  await p.click('button[type=submit]');
  await p.screenshot({ path: out('q1_2_required_errors.png') });

  await p.type('#name', 'Joe P Joy');
  await p.type('#roll_no', '24CS084');
  await p.type('#email', 'joe@@mail');
  await p.select('#course', 'B.E. CSE');
  await p.type('#password', 'Joe@12345');
  await p.type('#confirm', 'Joe@1234');
  await p.click('button[type=submit]');
  await p.screenshot({ path: out('q1_3_email_and_mismatch_errors.png') });

  // strong password + all valid
  await p.$eval('#email', (e) => (e.value = ''));
  await p.type('#email', 'joe@example.com');
  await p.$eval('#confirm', (e) => (e.value = ''));
  await p.type('#confirm', 'Joe@12345');
  await p.click('#name'); // blur
  await p.screenshot({ path: out('q1_4_strong_password.png') });

  await p.setViewport({ width: 390, height: 844, isMobile: true, deviceScaleFactor: 2 });
  await p.reload();
  await p.click('button[type=submit]');
  await p.screenshot({ path: out('q1_5_mobile_view.png'), fullPage: true });
  await b.close();
})();
