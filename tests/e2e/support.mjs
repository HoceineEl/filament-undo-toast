import { test as base, expect } from '@playwright/test'
import { execFileSync } from 'node:child_process'
import { fileURLToPath } from 'node:url'

const root = fileURLToPath(new URL('../..', import.meta.url))

export const resetDatabase = () => {
    execFileSync('php', ['vendor/bin/testbench', 'migrate:fresh', '--force', '--quiet'], { cwd: root, stdio: 'pipe' })
}

export const test = base.extend({
    page: async ({ page }, use) => {
        resetDatabase()

        const errors = []
        page.on('pageerror', (error) => errors.push(error.message))

        await use(page)

        expect(errors, 'no JavaScript errors').toEqual([])
    },
})

export { expect }

export const toasts = (page) => page.locator('.ut-toast:not([data-leaving="true"])')

export const newestToast = (page) => toasts(page).last()

export const filamentNotifications = (page) => page.locator('.fi-no-notification')

export const row = (page, text) => page.locator('tr', { hasText: text })

export const modal = (page) => page.locator('.fi-modal-window:visible').last()

export const confirm = async (page, label) => {
    await modal(page).getByRole('button', { name: label, exact: true }).click()
}

export const deleteRow = async (page, text) => {
    await row(page, text).getByRole('button', { name: /^(Delete|حذف|Supprimer)$/ }).click()
    await modal(page).locator('.fi-modal-footer-actions button').last().click()
}

export const waitForLivewire = (page) =>
    page.waitForFunction(() => window.Livewire && document.querySelector('.ut-region')?._x_dataStack)

export const contrastRatio = (page, locator, foregroundSelector = null) =>
    locator.evaluate((element, selector) => {
        const channel = (value) => {
            const ratio = value / 255

            return ratio <= 0.03928 ? ratio / 12.92 : ((ratio + 0.055) / 1.055) ** 2.4
        }

        const parse = (color) => {
            const canvas = document.createElement('canvas')
            canvas.width = canvas.height = 1
            const context = canvas.getContext('2d')
            context.fillStyle = color
            context.fillRect(0, 0, 1, 1)

            return Array.from(context.getImageData(0, 0, 1, 1).data).slice(0, 3)
        }

        const luminance = (rgb) => 0.2126 * channel(rgb[0]) + 0.7152 * channel(rgb[1]) + 0.0722 * channel(rgb[2])
        const foreground = selector ? element.querySelector(selector) : element
        const [a, b] = [luminance(parse(getComputedStyle(foreground).color)), luminance(parse(getComputedStyle(element).backgroundColor))]

        return (Math.max(a, b) + 0.05) / (Math.min(a, b) + 0.05)
    }, foregroundSelector)
