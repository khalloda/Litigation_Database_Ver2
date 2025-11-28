import { test as setup, expect } from '@playwright/test';

const authFile = './tests/e2e/.auth/user.json';

setup('authenticate', async ({ page, context }) => {
  // Go to app root to initialize
  await page.goto('/');
  
  // Wait for loading screen to disappear
  await page.waitForSelector('#initial-loader', { state: 'hidden', timeout: 15000 }).catch(() => {});
  
  // Make API login request
  const response = await page.evaluate(async (credentials) => {
    try {
      const res = await fetch('/api/login', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: JSON.stringify(credentials),
      });
      const data = await res.json();
      return { status: res.status, data };
    } catch (error: any) {
      return { status: 500, error: error.message };
    }
  }, { email: 'khelmy@sarieldin.com', password: 'P@ssw0rd' });
  
  expect(response.status).toBe(200);
  
  // Store token if provided
  if (response.data?.token) {
    await page.evaluate((token) => {
      localStorage.setItem('auth_token', token);
    }, response.data.token);
  }
  
  // Reload to apply authentication
  await page.goto('/');
  await page.waitForSelector('#initial-loader', { state: 'hidden', timeout: 15000 }).catch(() => {});
  await page.waitForTimeout(2000);
  
  // Save storage state
  await context.storageState({ path: authFile });
});

