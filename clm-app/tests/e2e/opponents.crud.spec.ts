import { test, expect } from './fixtures';

test.describe('Opponents CRUD', () => {
  test.use({ storageState: './tests/e2e/.auth/user.json' });

  const testOpponentName = `Test Opponent ${Date.now()}`;

  test('1. Navigate to Opponents list', async ({ page }) => {
    await page.goto('/opponents');
    
    await expect(page.locator('text=Opponents').first()).toBeVisible({ timeout: 15000 });
    await page.screenshot({ path: './tests/e2e/screenshots/opponents-list.png' });
  });

  test('2. Create new Opponent', async ({ page }) => {
    await page.goto('/opponents');
    
    // Click create button
    const createButton = page.locator('button:has-text("Add"), button:has-text("New"), button:has-text("Create"), a:has-text("Add")').first();
    await createButton.click();
    
    // Wait for form
    await page.waitForSelector('input', { timeout: 10000 });
    
    // Fill opponent name
    const nameField = page.locator('input[name*="name" i], input[placeholder*="name" i]').first();
    await nameField.fill(testOpponentName);
    
    await page.screenshot({ path: './tests/e2e/screenshots/opponents-create-form.png' });
    
    // Submit
    await page.locator('button[type="submit"], button:has-text("Save"), button:has-text("Create")').first().click();
    await page.waitForTimeout(3000);
    
    await page.screenshot({ path: './tests/e2e/screenshots/opponents-after-create.png' });
  });

  test('3. Read/View Opponent', async ({ page }) => {
    await page.goto('/opponents');
    
    const row = page.locator('tr, [role="row"], .card').filter({ hasText: /\w+/ }).first();
    if (await row.isVisible()) {
      await row.click();
      await page.waitForTimeout(2000);
      await page.screenshot({ path: './tests/e2e/screenshots/opponents-view.png' });
    }
  });

  test('4. Update Opponent', async ({ page }) => {
    await page.goto('/opponents');
    
    const editButton = page.locator('button:has-text("Edit"), a:has-text("Edit"), [aria-label*="edit" i]').first();
    
    if (await editButton.isVisible({ timeout: 5000 })) {
      await editButton.click();
      await page.waitForTimeout(2000);
      await page.screenshot({ path: './tests/e2e/screenshots/opponents-edit-form.png' });
      
      const saveButton = page.locator('button[type="submit"], button:has-text("Save")').first();
      if (await saveButton.isVisible()) {
        await saveButton.click();
        await page.waitForTimeout(2000);
      }
      
      await page.screenshot({ path: './tests/e2e/screenshots/opponents-after-update.png' });
    }
  });

  test('5. Delete Opponent (visual check only)', async ({ page }) => {
    await page.goto('/opponents');
    
    const deleteButton = page.locator('button:has-text("Delete"), [aria-label*="delete" i]').first();
    
    if (await deleteButton.isVisible({ timeout: 5000 })) {
      await page.screenshot({ path: './tests/e2e/screenshots/opponents-delete-available.png' });
    } else {
      await page.screenshot({ path: './tests/e2e/screenshots/opponents-no-delete.png' });
    }
  });
});
