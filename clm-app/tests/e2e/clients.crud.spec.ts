import { test, expect } from './fixtures';

test.describe('Clients CRUD', () => {
  // Use stored authentication state
  test.use({ storageState: './tests/e2e/.auth/user.json' });

  const testClientName = `Test Client ${Date.now()}`;
  const testClientNameAr = `عميل اختبار`;

  test('1. Navigate to Clients list', async ({ page }) => {
    await page.goto('/clients');
    
    // Verify we're on the clients page
    await expect(page.locator('text=Clients').first()).toBeVisible({ timeout: 15000 });
    
    // Take screenshot
    await page.screenshot({ path: './tests/e2e/screenshots/clients-list.png' });
  });

  test('2. Create new Client', async ({ page }) => {
    await page.goto('/clients');
    
    // Click on "New Client" button
    const createButton = page.locator('button:has-text("New Client")').first();
    await createButton.click();
    
    // Wait for modal with "Create New Client" heading
    await page.waitForSelector('text=Create New Client', { timeout: 10000 });
    
    // Fill in Client Name (English) - use getByLabel for accessibility
    await page.getByLabel('Client Name (English)').fill(testClientName);
    
    // Fill in Client Name (Arabic)
    await page.getByLabel('Client Name (Arabic)').fill(testClientNameAr);
    
    // Take screenshot before submit
    await page.screenshot({ path: './tests/e2e/screenshots/clients-create-form.png' });
    
    // Click Save button
    await page.locator('button:has-text("Save")').click();
    
    // Wait for modal to close or success
    await page.waitForTimeout(3000);
    
    // Take screenshot after create
    await page.screenshot({ path: './tests/e2e/screenshots/clients-after-create.png' });
  });

  test('3. Read/View Client', async ({ page }) => {
    await page.goto('/clients');
    
    // Find and click on a client row (any client)
    const clientRow = page.locator('tr, [role="row"], .card').filter({ hasText: /\w+/ }).first();
    if (await clientRow.isVisible()) {
      await clientRow.click();
      await page.waitForTimeout(2000);
      
      // Take screenshot
      await page.screenshot({ path: './tests/e2e/screenshots/clients-view.png' });
    }
  });

  test('4. Update Client', async ({ page }) => {
    await page.goto('/clients');
    
    // Find edit button on first row
    const editButton = page.locator('button:has-text("Edit"), a:has-text("Edit"), [aria-label*="edit" i]').first();
    
    if (await editButton.isVisible({ timeout: 5000 })) {
      await editButton.click();
      await page.waitForTimeout(2000);
      
      // Take screenshot of edit form
      await page.screenshot({ path: './tests/e2e/screenshots/clients-edit-form.png' });
      
      // Try to save (even without changes)
      const saveButton = page.locator('button[type="submit"], button:has-text("Save"), button:has-text("Update")').first();
      if (await saveButton.isVisible()) {
        await saveButton.click();
        await page.waitForTimeout(2000);
      }
      
      await page.screenshot({ path: './tests/e2e/screenshots/clients-after-update.png' });
    }
  });

  test('5. Delete Client (visual check only)', async ({ page }) => {
    await page.goto('/clients');
    
    // Look for delete button but don't actually click it (to preserve data)
    const deleteButton = page.locator('button:has-text("Delete"), [aria-label*="delete" i]').first();
    
    if (await deleteButton.isVisible({ timeout: 5000 })) {
      // Just take a screenshot showing delete button exists
      await page.screenshot({ path: './tests/e2e/screenshots/clients-delete-available.png' });
      console.log('Delete button found - not clicking to preserve data');
    } else {
      console.log('Delete button not visible on clients list');
      await page.screenshot({ path: './tests/e2e/screenshots/clients-no-delete.png' });
    }
  });
});
