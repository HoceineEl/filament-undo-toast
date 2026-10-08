import { fileURLToPath } from 'node:url'
import { confirm, deleteRow, expect, modal, newestToast, row, test, toasts } from './support.mjs'

const directory = process.env.UT_SCREENSHOTS_DIR || fileURLToPath(new URL('../../docs/images', import.meta.url))

test.skip(!process.env.UT_SCREENSHOTS, 'Set UT_SCREENSHOTS=1 to retake the README screenshots.')

test.use({ deviceScaleFactor: 2 })

const shoot = (page, name, clip = null) => page.screenshot({ path: `${directory}/${name}.png`, ...(clip ? { clip } : {}) })

const regionClip = async (page, { x: padX = 48, y: padY = 32 } = {}) => {
    const box = await page.locator('.ut-stack').boundingBox()
    const viewport = page.viewportSize()
    const x = Math.max(0, box.x - padX)
    const y = Math.max(0, box.y - padY)

    return { x, y, width: Math.min(viewport.width - x, box.width + padX * 2), height: Math.min(viewport.height - y, box.height + padY * 2) }
}

const bulkDelete = async (page, names) => {
    for (const name of names) {
        await row(page, name).locator('input[type="checkbox"]').check()
    }

    await page.getByRole('button', { name: /^(Delete selected|حذف المحدد|Ausgewählte löschen)$/ }).click()
    await modal(page).locator('.fi-modal-footer-actions button').last().click()
}

const freeze = (page) =>
    page.evaluate(() => {
        const data = window.Alpine.$data(document.querySelector('.ut-region'))
        data.tabHidden = true
    })

const settle = (page) => page.waitForTimeout(450)

const scrollTablesToStart = (page) =>
    page.evaluate(() => document.querySelectorAll('.fi-ta-content-ctn, .fi-ta-content').forEach((element) => element.scrollTo({ left: 0 })))

const away = async (page) => {
    await page.mouse.move(0, 0)
    await settle(page)
}

test('hero: bulk delete', async ({ page }) => {
    await page.goto('/admin/customers')
    await bulkDelete(page, ['Blue Harbor Studio', 'Cedar & Co', 'Dune Logistics'])
    await expect(newestToast(page)).toContainText('Deleted 3 customers')
    await away(page)
    await shoot(page, 'hero')
})

test('keyboard hint', async ({ page }) => {
    await page.goto('/admin/customers')
    await deleteRow(page, 'Acme Corporation')
    const toast = newestToast(page)
    await expect(toast).toContainText('Customer deleted')
    await expect(toast.locator('.ut-kbd')).toBeVisible()
    await page.mouse.move(0, 0)
    await page.waitForTimeout(3200)
    await freeze(page)
    const actions = await toast.locator('.ut-actions').boundingBox()
    const box = await toast.boundingBox()
    await shoot(page, 'keyboard-hint', { x: actions.x - 24, y: box.y - 20, width: box.x + box.width - actions.x + 44, height: box.height + 40 })
})

test('stack expanded', async ({ page }) => {
    await page.goto('/admin/tags')
    await row(page, 'Design').getByRole('button', { name: 'Edit' }).click()
    await modal(page).getByLabel('Name').fill('Brand')
    await confirm(page, 'Save changes')
    await expect(newestToast(page)).toContainText('Tag updated')

    await deleteRow(page, 'Urgent')
    await expect(newestToast(page)).toContainText('Tag deleted')

    await bulkDelete(page, ['Research', 'Launch'])
    await expect(newestToast(page)).toContainText('Deleted 2 tags')

    await away(page)
    await freeze(page)
    await page.locator('.ut-toast[data-depth="0"] .ut-message').hover()
    await settle(page)
    await shoot(page, 'stack-expanded', await regionClip(page, { x: 48, y: 40 }))
})

test('edit', async ({ page }) => {
    await page.goto('/admin/tags')
    await row(page, 'Design').getByRole('button', { name: 'Edit' }).click()
    await modal(page).getByLabel('Name').fill('Brand')
    await confirm(page, 'Save changes')
    await expect(newestToast(page)).toContainText('Tag updated')
    await away(page)
    await freeze(page)
    await shoot(page, 'edit', await regionClip(page))
})

test('hard delete in dark mode', async ({ page }) => {
    await page.addInitScript(() => window.localStorage.setItem('theme', 'dark'))
    await page.goto('/admin/projects')
    await deleteRow(page, 'Website redesign')
    await expect(newestToast(page)).toContainText('Project deleted')
    await away(page)
    await shoot(page, 'dark')
})

test('undone', async ({ page }) => {
    await page.goto('/admin/customers')
    await deleteRow(page, 'Acme Corporation')
    const toast = newestToast(page)
    await expect(toast).toContainText('Customer deleted')
    await toast.getByRole('button', { name: 'Undo' }).click()
    await expect(toast).toHaveAttribute('data-state', 'done')
    await freeze(page)
    await expect(page.locator('.fi-no-notification').getByRole('link', { name: 'Open' })).toBeVisible()
    await expect(row(page, 'Acme Corporation')).toHaveCount(1)
    await page.waitForTimeout(250)
    await shoot(page, 'undone')
})

test('failed undo', async ({ page }) => {
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
    await page.waitForTimeout(250)
    await shoot(page, 'error', await regionClip(page))
})

test('arabic rtl', async ({ page }) => {
    await page.goto('/admin/customers?locale=ar')
    await bulkDelete(page, ['Acme Corporation', 'Cedar & Co'])
    await expect(newestToast(page)).toContainText('العملاء')
    await away(page)
    await shoot(page, 'rtl')
})

test('german', async ({ page }) => {
    await page.goto('/admin/customers?locale=de')
    await bulkDelete(page, ['Acme Corporation', 'Blue Harbor Studio', 'Cedar & Co'])
    await expect(newestToast(page)).toContainText('3 Kunden gelöscht')
    await away(page)
    await freeze(page)
    await shoot(page, 'locale-de', await regionClip(page))
})

test.describe('phone', () => {
    test.use({ viewport: { width: 390, height: 780 }, deviceScaleFactor: 2, hasTouch: true, isMobile: true })

    test('mobile', async ({ page }) => {
        await page.goto('/admin/customers')
        await deleteRow(page, 'Acme Corporation')
        await expect(newestToast(page)).toContainText('Customer deleted')
        await scrollTablesToStart(page)
        await settle(page)
        await shoot(page, 'mobile')
    })

})
