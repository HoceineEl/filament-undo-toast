import { confirm, contrastRatio, deleteRow, expect, filamentNotifications, modal, newestToast, row, test, toasts } from './support.mjs'

test('deleting a record offers undo and brings it back', async ({ page }) => {
    await page.goto('/admin/customers')
    await deleteRow(page, 'Acme Corporation')

    const toast = newestToast(page)

    await expect(toast).toContainText('Customer deleted')
    await expect(row(page, 'Acme Corporation')).toHaveCount(0)

    await toast.getByRole('button', { name: 'Undo' }).click()

    await expect(toast).toHaveAttribute('data-state', 'done')
    await expect(toast).toContainText('Customer restored')
    await expect(row(page, 'Acme Corporation')).toHaveCount(1)
    await expect(toasts(page)).toHaveCount(0, { timeout: 4_000 })
})

test('mod+z undoes a bulk delete even with a checkbox focused', async ({ page }) => {
    await page.goto('/admin/customers')

    for (const name of ['Cedar & Co', 'Dune Logistics', 'Evergreen Clinic']) {
        await row(page, name).locator('input[type="checkbox"]').check()
    }

    await page.getByRole('button', { name: 'Delete selected' }).click()
    await modal(page).locator('.fi-modal-footer-actions button').last().click()

    await expect(newestToast(page)).toContainText('Deleted 3 customers')
    await expect(row(page, 'Cedar & Co')).toHaveCount(0)

    await page.locator('tbody input[type="checkbox"]').first().focus()
    await page.keyboard.press('ControlOrMeta+z')

    await expect(newestToast(page)).toContainText('Restored 3 customers')
    await expect(row(page, 'Cedar & Co')).toHaveCount(1)
    await expect(row(page, 'Evergreen Clinic')).toHaveCount(1)
})

test('mod+z leaves text undo alone inside inputs', async ({ page }) => {
    await page.goto('/admin/customers')
    await deleteRow(page, 'Acme Corporation')
    await expect(newestToast(page)).toContainText('Customer deleted')

    const search = page.getByPlaceholder('Search')
    await search.focus()
    await page.keyboard.press('ControlOrMeta+z')

    await expect(newestToast(page)).toHaveAttribute('data-state', 'idle')
})

test('reverting an edit made in a modal', async ({ page }) => {
    await page.goto('/admin/tags')
    await row(page, 'Design').getByRole('button', { name: 'Edit' }).click()
    await modal(page).getByLabel('Name').fill('Brand')
    await confirm(page, 'Save changes')

    await expect(newestToast(page)).toContainText('Tag updated')
    await expect(row(page, 'Brand')).toHaveCount(1)

    await newestToast(page).getByRole('button', { name: 'Undo' }).click()

    await expect(newestToast(page)).toContainText('Changes to the tag undone')
    await expect(row(page, 'Design')).toHaveCount(1)
    await expect(row(page, 'Brand')).toHaveCount(0)
})

test('re-attaching a detached record with its pivot data', async ({ page }) => {
    await page.goto('/admin/projects/2/edit')
    await row(page, 'Backend').getByRole('button', { name: 'Detach' }).click()
    await modal(page).locator('.fi-modal-footer-actions button').last().click()

    await expect(newestToast(page)).toContainText('Tag detached')
    await expect(row(page, 'Backend')).toHaveCount(0)

    await newestToast(page).getByRole('button', { name: 'Undo' }).click()

    await expect(newestToast(page)).toContainText('Tag attached again')
    await expect(row(page, 'Backend')).toContainText('secondary')
})

test('undoing a hard delete restores the row and its relations', async ({ page }) => {
    await page.goto('/admin/projects')
    await expect(row(page, 'Website redesign')).toContainText('2')
    await deleteRow(page, 'Website redesign')

    await expect(newestToast(page)).toContainText('Project deleted')
    await expect(row(page, 'Website redesign')).toHaveCount(0)

    await newestToast(page).getByRole('button', { name: 'Undo' }).click()
    await expect(newestToast(page)).toContainText('Project restored')

    const restored = row(page, 'Website redesign')
    await expect(restored.locator('td').nth(2)).toHaveText('2')
    await expect(restored.locator('td').nth(3)).toHaveText('3')

    await page.goto('/admin/projects/1/edit')
    await expect(row(page, 'Urgent')).toContainText('primary')
    await expect(row(page, 'Design')).toContainText('secondary')
})

test('deleting from the edit page shows the toast after the redirect', async ({ page }) => {
    await page.goto('/admin/customers/1/edit')
    await page.locator('.fi-header').getByRole('button', { name: 'Delete' }).click()
    await modal(page).locator('.fi-modal-footer-actions button').last().click()

    await expect(page).toHaveURL(/\/admin\/customers$/)
    await expect(newestToast(page)).toContainText('Customer deleted')

    await newestToast(page).getByRole('button', { name: 'Undo' }).click()

    await expect(row(page, 'Acme Corporation')).toHaveCount(1)
    await expect(filamentNotifications(page).getByRole('link', { name: 'Open' })).toHaveAttribute('href', /\/admin\/customers\/1\/edit$/)
})

test('a toast is not shown twice after reloading', async ({ page }) => {
    await page.goto('/admin/customers/1/edit')
    await page.locator('.fi-header').getByRole('button', { name: 'Delete' }).click()
    await modal(page).locator('.fi-modal-footer-actions button').last().click()
    await expect(newestToast(page)).toContainText('Customer deleted')

    await newestToast(page).getByRole('button', { name: 'Dismiss' }).click()
    await expect(toasts(page)).toHaveCount(0)

    await page.reload()
    await page.waitForTimeout(600)
    await expect(toasts(page)).toHaveCount(0)
})

test('Filament success notification is replaced, not duplicated', async ({ page }) => {
    await page.goto('/admin/customers')
    await deleteRow(page, 'Acme Corporation')

    await expect(newestToast(page)).toContainText('Customer deleted')
    await page.waitForTimeout(800)
    await expect(filamentNotifications(page)).toHaveCount(0)

    await newestToast(page).getByRole('button', { name: 'Undo' }).click()

    await expect(filamentNotifications(page)).toHaveCount(1)
    await expect(filamentNotifications(page)).toContainText('Customer restored')
})

test('older toasts stack behind the newest and fan out on hover', async ({ page }) => {
    await page.goto('/admin/customers')

    for (const name of ['Acme Corporation', 'Blue Harbor Studio', 'Cedar & Co', 'Dune Logistics']) {
        await deleteRow(page, name)
        await expect(newestToast(page)).toContainText('Customer deleted')
    }

    await expect(toasts(page)).toHaveCount(4)
    await expect(page.locator('.ut-toast[data-hidden="true"]')).toHaveCount(1)

    const collapsed = await page.locator('.ut-stack').boundingBox()
    await page.locator('.ut-toast[data-depth="0"]').hover()
    await expect(page.locator('.ut-region')).toHaveClass(/ut-expanded/)
    await page.waitForTimeout(300)

    const expanded = await page.locator('.ut-stack').boundingBox()
    expect(expanded.height).toBeGreaterThan(collapsed.height * 2)

    const boxes = await page.locator('.ut-toast:not([data-hidden="true"])').evaluateAll((elements) =>
        elements.map((element) => element.getBoundingClientRect()).map(({ top, bottom }) => ({ top, bottom })),
    )

    for (let index = 1; index < boxes.length; index++) {
        expect(boxes[index - 1].bottom).toBeLessThanOrEqual(boxes[index].top + 1)
    }
})

test('a failed undo stays on screen as an alert until dismissed', async ({ page }) => {
    await page.goto('/admin/customers')
    await deleteRow(page, 'Acme Corporation')

    const toast = newestToast(page)
    await expect(toast).toContainText('Customer deleted')

    await page.evaluate(() => {
        const data = window.Alpine.$data(document.querySelector('.ut-region'))
        data.toasts[data.toasts.length - 1].token = 'x'.repeat(40)
    })
    await toast.getByRole('button', { name: 'Undo' }).click()

    await expect(toast).toHaveAttribute('data-state', 'error')
    await expect(toast).toHaveAttribute('role', 'alert')
    await expect(toast).toContainText('expired')
    await page.mouse.move(0, 0)
    await page.waitForTimeout(4_500)
    await expect(toast).toBeVisible()

    await toast.getByRole('button', { name: 'Dismiss' }).focus()
    await page.keyboard.press('Escape')
    await expect(toasts(page)).toHaveCount(0)
})

test('right to left in Arabic', async ({ page }) => {
    await page.goto('/admin/customers?locale=ar')
    await expect(page.locator('html')).toHaveAttribute('dir', 'rtl')

    await deleteRow(page, 'Acme Corporation')

    const toast = newestToast(page)
    await expect(toast).toContainText('تم حذف العميل')

    const message = await toast.locator('.ut-message').boundingBox()
    const undo = await toast.getByRole('button', { name: 'تراجع' }).boundingBox()
    const icon = await toast.locator('.ut-icon').boundingBox()

    expect(icon.x).toBeGreaterThan(message.x)
    expect(undo.x).toBeLessThan(message.x)
    await expect(toast.locator('.ut-progress > span')).toHaveCSS('transform-origin', /^\d+(\.\d+)?px /)

    const origin = await toast.locator('.ut-progress > span').evaluate((element) => {
        const [x] = getComputedStyle(element).transformOrigin.split(' ')

        return parseFloat(x) / element.getBoundingClientRect().width
    })
    expect(origin).toBeGreaterThan(0.9)

    await toast.getByRole('button', { name: 'تراجع' }).click()
    await expect(toast).toContainText('تمت استعادة العميل')
})

test('dark mode keeps the toast readable', async ({ page }) => {
    await page.addInitScript(() => window.localStorage.setItem('theme', 'dark'))
    await page.goto('/admin/customers')
    await expect(page.locator('html')).toHaveClass(/dark/)

    await deleteRow(page, 'Acme Corporation')
    const toast = newestToast(page)
    await expect(toast).toContainText('Customer deleted')

    expect(await contrastRatio(page, toast, '.ut-message')).toBeGreaterThanOrEqual(7)
    expect(await contrastRatio(page, toast, '.ut-undo')).toBeGreaterThanOrEqual(4.5)

    const background = await toast.evaluate((element) => getComputedStyle(element).backgroundColor)
    const pageBackground = await page.evaluate(() => getComputedStyle(document.body).backgroundColor)
    expect(background).not.toBe(pageBackground)
})

test('light mode contrast', async ({ page }) => {
    await page.goto('/admin/customers')
    await deleteRow(page, 'Acme Corporation')
    const toast = newestToast(page)
    await expect(toast).toContainText('Customer deleted')

    expect(await contrastRatio(page, toast, '.ut-message')).toBeGreaterThanOrEqual(7)
    expect(await contrastRatio(page, toast, '.ut-undo')).toBeGreaterThanOrEqual(4.5)
})

test.describe('on a 375px phone', () => {
    test.use({ viewport: { width: 375, height: 740 }, hasTouch: true, isMobile: true })

    test('fits the screen with large touch targets', async ({ page }) => {
        await page.goto('/admin/customers')
        await deleteRow(page, 'Acme Corporation')

        const toast = newestToast(page)
        await expect(toast).toContainText('Customer deleted')

        const box = await toast.boundingBox()
        expect(box.x).toBeGreaterThanOrEqual(8)
        expect(box.x + box.width).toBeLessThanOrEqual(375 - 8)

        for (const name of ['Undo', 'Dismiss']) {
            const button = await toast.getByRole('button', { name }).boundingBox()
            expect(button.width).toBeGreaterThanOrEqual(44)
            expect(button.height).toBeGreaterThanOrEqual(44)
        }

        await expect(toast.locator('.ut-kbd')).toBeHidden()
        expect(await page.evaluate(() => document.documentElement.scrollWidth)).toBeLessThanOrEqual(375)

        await toast.getByRole('button', { name: 'Undo' }).tap()
        await expect(row(page, 'Acme Corporation')).toHaveCount(1)
    })

    test('tapping a stacked toast fans the stack out', async ({ page }) => {
        await page.goto('/admin/customers')

        for (const name of ['Acme Corporation', 'Blue Harbor Studio']) {
            await deleteRow(page, name)
            await expect(newestToast(page)).toContainText('Customer deleted')
        }

        await expect(page.locator('.ut-region')).not.toHaveClass(/ut-expanded/)

        const back = page.locator('.ut-toast[data-depth="1"]')
        const box = await back.boundingBox()
        await page.touchscreen.tap(box.x + box.width / 2, box.y + 4)

        await expect(page.locator('.ut-region')).toHaveClass(/ut-expanded/)
        await back.getByRole('button', { name: 'Undo' }).tap()
        await expect(row(page, 'Acme Corporation')).toHaveCount(1)
        await expect(row(page, 'Blue Harbor Studio')).toHaveCount(0)
    })
})

test.describe('with reduced motion', () => {
    test.use({ reducedMotion: 'reduce' })

    test('fades without moving', async ({ page }) => {
        await page.goto('/admin/customers')
        await deleteRow(page, 'Acme Corporation')

        const toast = newestToast(page)
        await expect(toast).toBeVisible()

        const motion = await toast.evaluate((element) => ({
            property: getComputedStyle(element).transitionProperty,
            duration: getComputedStyle(element).transitionDuration,
            transform: element.style.transform,
        }))

        expect(motion.property).toBe('opacity')
        expect(parseFloat(motion.duration)).toBeLessThanOrEqual(0.1)
        expect(motion.transform).toMatch(/translateY\((-)?0px\)/)

        await toast.getByRole('button', { name: 'Dismiss' }).click()
        await expect(toasts(page)).toHaveCount(0)
    })
})
