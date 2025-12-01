import { test as base, expect } from '@playwright/test';

/**
 * Extended test fixture that handles the 5-second loading screen
 */
export const test = base.extend({
  page: async ({ page }, use) => {
    // Override goto to always wait for loading screen
    const originalGoto = page.goto.bind(page);
    page.goto = async (url: string, options?: any) => {
      const response = await originalGoto(url, options);
      
      // Wait for loading screen to disappear
      await page.waitForSelector('#initial-loader', { state: 'hidden', timeout: 15000 }).catch(() => {
        // Loading screen might already be hidden or not present
      });
      
      // Wait for network to settle
      await page.waitForLoadState('networkidle').catch(() => {});
      
      return response;
    };
    
    await use(page);
  },
});

export { expect };

/**
 * Helper to wait for any modal/form to appear
 */
export async function waitForForm(page: any) {
  await page.waitForSelector('form, [role="dialog"], .modal', { timeout: 10000 }).catch(() => {});
}

/**
 * Helper to click a button by text content
 */
export async function clickButton(page: any, text: string) {
  const button = page.locator(`button:has-text("${text}"), a:has-text("${text}")`).first();
  await button.click();
}

