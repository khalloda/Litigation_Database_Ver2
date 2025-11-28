import { test, expect } from './fixtures';

test.describe('Cases CRUD', () => {
  test.use({ storageState: './tests/e2e/.auth/user.json' });

  const testCaseName = `Test Case ${Date.now()}`;

  test('1. Navigate to Cases list', async ({ page }) => {
    await page.goto('/cases');
    
    await expect(page.locator('text=Cases').first()).toBeVisible({ timeout: 15000 });
    await page.screenshot({ path: './tests/e2e/screenshots/cases-list.png' });
  });

  test('2. Create new Case', async ({ page }) => {
    await page.goto('/cases');
    
    const createButton = page.locator('button:has-text("Add"), button:has-text("New"), button:has-text("Create"), a:has-text("Add")').first();
    await createButton.click();
    
    await page.waitForSelector('input, select', { timeout: 10000 });
    
    // Fill case name
    const nameField = page.locator('input[name*="name" i], input[placeholder*="name" i], input[placeholder*="case" i]').first();
    if (await nameField.isVisible()) {
      await nameField.fill(testCaseName);
    }
    
    // Select client if dropdown exists
    const clientSelect = page.locator('select[name*="client" i]').first();
    if (await clientSelect.isVisible()) {
      const options = await clientSelect.locator('option').all();
      if (options.length > 1) {
        await clientSelect.selectOption({ index: 1 });
      }
    }
    
    await page.screenshot({ path: './tests/e2e/screenshots/cases-create-form.png' });
    
    await page.locator('button[type="submit"], button:has-text("Save"), button:has-text("Create")').first().click();
    await page.waitForTimeout(3000);
    
    await page.screenshot({ path: './tests/e2e/screenshots/cases-after-create.png' });
  });

  test('3. Read/View Case', async ({ page }) => {
    await page.goto('/cases');
    
    const row = page.locator('tr, [role="row"], .card').filter({ hasText: /\w+/ }).first();
    if (await row.isVisible()) {
      await row.click();
      await page.waitForTimeout(2000);
      await page.screenshot({ path: './tests/e2e/screenshots/cases-view.png' });
    }
  });

  test('4. Update Case', async ({ page }) => {
    await page.goto('/cases');
    
    const editButton = page.locator('button:has-text("Edit"), a:has-text("Edit"), [aria-label*="edit" i]').first();
    
    if (await editButton.isVisible({ timeout: 5000 })) {
      await editButton.click();
      await page.waitForTimeout(2000);
      await page.screenshot({ path: './tests/e2e/screenshots/cases-edit-form.png' });
      
      const saveButton = page.locator('button[type="submit"], button:has-text("Save")').first();
      if (await saveButton.isVisible()) {
        await saveButton.click();
        await page.waitForTimeout(2000);
      }
      
      await page.screenshot({ path: './tests/e2e/screenshots/cases-after-update.png' });
    }
  });

  test('5. Delete Case (visual check only)', async ({ page }) => {
    await page.goto('/cases');
    
    const deleteButton = page.locator('button:has-text("Delete"), [aria-label*="delete" i]').first();
    
    if (await deleteButton.isVisible({ timeout: 5000 })) {
      await page.screenshot({ path: './tests/e2e/screenshots/cases-delete-available.png' });
    } else {
      await page.screenshot({ path: './tests/e2e/screenshots/cases-no-delete.png' });
    }
  });
});
