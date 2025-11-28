import { chromium, FullConfig } from '@playwright/test';

/**
 * Global setup - authenticates via React SPA login form and saves session state
 */
async function globalSetup(config: FullConfig) {
  const { baseURL } = config.projects[0].use;
  const browser = await chromium.launch({ headless: false }); // Show browser for debugging
  const context = await browser.newContext();
  const page = await context.newPage();
  
  console.log('Navigating to React SPA...');
  
  // Go to the React SPA root - it will redirect to /login if not authenticated
  await page.goto(`${baseURL}/`);
  
  // Wait for loading screen to disappear (5 seconds)
  console.log('Waiting for loading screen to finish...');
  await page.waitForSelector('#initial-loader', { state: 'hidden', timeout: 20000 }).catch(() => {
    console.log('Loading screen already hidden or not present');
  });
  
  // Wait for React app to render
  await page.waitForTimeout(2000);
  
  // Check if we're on the login page or need to navigate there
  const currentUrl = page.url();
  console.log('Current URL:', currentUrl);
  
  // If not on login page, navigate to it
  if (!currentUrl.includes('/login')) {
    console.log('Navigating to login page...');
    await page.goto(`${baseURL}/login`);
    await page.waitForSelector('#initial-loader', { state: 'hidden', timeout: 20000 }).catch(() => {});
    await page.waitForTimeout(2000);
  }
  
  // Wait for React login form to be visible
  console.log('Waiting for React login form...');
  await page.waitForSelector('input#email, input[name="email"], input[type="email"]', { 
    state: 'visible', 
    timeout: 20000 
  });
  
  // Fill in login credentials
  console.log('Filling credentials...');
  await page.fill('input#email, input[name="email"], input[type="email"]', 'khelmy@sarieldin.com');
  await page.fill('input#password, input[name="password"], input[type="password"]', 'P@ssw0rd');
  
  // Take screenshot of login form
  await page.screenshot({ path: './tests/e2e/screenshots/login-form.png' });
  
  // Click login button
  console.log('Clicking login button...');
  await page.click('button[type="submit"]');
  
  // Wait for login to complete and redirect
  console.log('Waiting for login to complete...');
  await page.waitForTimeout(3000);
  
  // Take a screenshot to verify we're logged in
  await page.screenshot({ path: './tests/e2e/screenshots/after-login.png' });
  
  console.log('Final URL:', page.url());
  
  // Save authentication state (cookies + localStorage)
  await context.storageState({ path: './tests/e2e/.auth/user.json' });
  console.log('Authentication state saved!');
  
  await browser.close();
}

export default globalSetup;

