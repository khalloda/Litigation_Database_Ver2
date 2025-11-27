import { test, expect } from './fixtures';

test.describe('Courts CRUD', () => {
  test.use({ storageState: './tests/e2e/.auth/user.json' });

  const testCourtName = `Test Court ${Date.now()}`;

  test('1. Navigate to Courts list', async ({ page }) => {
    await page.goto('/courts');
    
    await expect(page.locator('text=Courts').first()).toBeVisible({ timeout: 15000 });
    await page.screenshot({ path: './tests/e2e/screenshots/courts-list.png' });
  });

  test('2. Create new Court', async ({ page }) => {
    await page.goto('/courts');
    
    const createButton = page.locator('button:has-text("Add"), button:has-text("New"), button:has-text("Create"), a:has-text("Add")').first();
    await createButton.click();
    
    await page.waitForSelector('input', { timeout: 10000 });
    
    const nameField = page.locator('input[name*="name" i], input[placeholder*="name" i]').first();
    await nameField.fill(testCourtName);
    
    await page.screenshot({ path: './tests/e2e/screenshots/courts-create-form.png' });
    
    await page.locator('button[type="submit"], button:has-text("Save"), button:has-text("Create")').first().click();
    await page.waitForTimeout(3000);
    
    await page.screenshot({ path: './tests/e2e/screenshots/courts-after-create.png' });
  });

  test('3. Read/View Court', async ({ page }) => {
    await page.goto('/courts');
    
    const row = page.locator('tr, [role="row"], .card').filter({ hasText: /\w+/ }).first();
    if (await row.isVisible()) {
      await row.click();
      await page.waitForTimeout(2000);
      await page.screenshot({ path: './tests/e2e/screenshots/courts-view.png' });
    }
  });

  test('4. Update Court', async ({ page }) => {
    await page.goto('/courts');
    
    const editButton = page.locator('button:has-text("Edit"), a:has-text("Edit"), [aria-label*="edit" i]').first();
    
    if (await editButton.isVisible({ timeout: 5000 })) {
      await editButton.click();
      await page.waitForTimeout(2000);
      await page.screenshot({ path: './tests/e2e/screenshots/courts-edit-form.png' });
      
      const saveButton = page.locator('button[type="submit"], button:has-text("Save")').first();
      if (await saveButton.isVisible()) {
        await saveButton.click();
        await page.waitForTimeout(2000);
      }
      
      await page.screenshot({ path: './tests/e2e/screenshots/courts-after-update.png' });
    }
  });

  test('5. Delete Court (visual check only)', async ({ page }) => {
    await page.goto('/courts');
    
    const deleteButton = page.locator('button:has-text("Delete"), [aria-label*="delete" i]').first();
    
    if (await deleteButton.isVisible({ timeout: 5000 })) {
      await page.screenshot({ path: './tests/e2e/screenshots/courts-delete-available.png' });
    } else {
      await page.screenshot({ path: './tests/e2e/screenshots/courts-no-delete.png' });
    }
  });
});
