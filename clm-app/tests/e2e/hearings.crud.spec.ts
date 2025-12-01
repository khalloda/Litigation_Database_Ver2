import { test, expect } from './fixtures';

test.describe('Hearings CRUD', () => {
  test.use({ storageState: './tests/e2e/.auth/user.json' });

  const testHearingSubject = `Test Hearing ${Date.now()}`;

  test('1. Navigate to Hearings list', async ({ page }) => {
    await page.goto('/hearings');
    
    await expect(page.locator('text=Hearings').first()).toBeVisible({ timeout: 15000 });
    await page.screenshot({ path: './tests/e2e/screenshots/hearings-list.png' });
  });

  test('2. Create new Hearing', async ({ page }) => {
    await page.goto('/hearings');
    
    const createButton = page.locator('button:has-text("Add"), button:has-text("New"), button:has-text("Create"), a:has-text("Add")').first();
    await createButton.click();
    
    await page.waitForSelector('input, select, textarea', { timeout: 10000 });
    
    // Fill hearing subject/description
    const subjectField = page.locator('input[name*="subject" i], input[placeholder*="subject" i], textarea[name*="description" i]').first();
    if (await subjectField.isVisible()) {
      await subjectField.fill(testHearingSubject);
    }
    
    // Select case if dropdown exists
    const caseSelect = page.locator('select[name*="case" i]').first();
    if (await caseSelect.isVisible()) {
      const options = await caseSelect.locator('option').all();
      if (options.length > 1) {
        await caseSelect.selectOption({ index: 1 });
      }
    }
    
    // Fill date if required
    const dateField = page.locator('input[type="date"], input[name*="date" i]').first();
    if (await dateField.isVisible()) {
      const today = new Date().toISOString().split('T')[0];
      await dateField.fill(today);
    }
    
    await page.screenshot({ path: './tests/e2e/screenshots/hearings-create-form.png' });
    
    await page.locator('button[type="submit"], button:has-text("Save"), button:has-text("Create")').first().click();
    await page.waitForTimeout(3000);
    
    await page.screenshot({ path: './tests/e2e/screenshots/hearings-after-create.png' });
  });

  test('3. Read/View Hearing', async ({ page }) => {
    await page.goto('/hearings');
    
    const row = page.locator('tr, [role="row"], .card').filter({ hasText: /\w+/ }).first();
    if (await row.isVisible()) {
      await row.click();
      await page.waitForTimeout(2000);
      await page.screenshot({ path: './tests/e2e/screenshots/hearings-view.png' });
    }
  });

  test('4. Update Hearing', async ({ page }) => {
    await page.goto('/hearings');
    
    const editButton = page.locator('button:has-text("Edit"), a:has-text("Edit"), [aria-label*="edit" i]').first();
    
    if (await editButton.isVisible({ timeout: 5000 })) {
      await editButton.click();
      await page.waitForTimeout(2000);
      await page.screenshot({ path: './tests/e2e/screenshots/hearings-edit-form.png' });
      
      const saveButton = page.locator('button[type="submit"], button:has-text("Save")').first();
      if (await saveButton.isVisible()) {
        await saveButton.click();
        await page.waitForTimeout(2000);
      }
      
      await page.screenshot({ path: './tests/e2e/screenshots/hearings-after-update.png' });
    }
  });

  test('5. Delete Hearing (visual check only)', async ({ page }) => {
    await page.goto('/hearings');
    
    const deleteButton = page.locator('button:has-text("Delete"), [aria-label*="delete" i]').first();
    
    if (await deleteButton.isVisible({ timeout: 5000 })) {
      await page.screenshot({ path: './tests/e2e/screenshots/hearings-delete-available.png' });
    } else {
      await page.screenshot({ path: './tests/e2e/screenshots/hearings-no-delete.png' });
    }
  });
});
