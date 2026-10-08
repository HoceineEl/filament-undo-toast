const SEEN_KEY = 'undo-toast:seen'
const EXIT_MS = 120
const DONE_MIN_MS = 1800
const DONE_MAX_MS = 4000
const PEEK_PX = 10
const GAP_PX = 8

const isMac = () => /Mac|iPhone|iPad|iPod/i.test(navigator.platform || navigator.userAgent || '')

const prefersReducedMotion = () => window.matchMedia?.('(prefers-reduced-motion: reduce)').matches ?? false

const NON_TEXT_INPUTS = ['button', 'checkbox', 'color', 'file', 'image', 'radio', 'range', 'reset', 'submit']

const isTypingTarget = (element) => {
    if (!element || element === document.body) {
        return false
    }

    if (element.tagName === 'INPUT') {
        return !NON_TEXT_INPUTS.includes(element.type)
    }

    return element.isContentEditable || ['TEXTAREA', 'SELECT'].includes(element.tagName)
}

const parseBinding = (binding) => {
    const parts = String(binding).toLowerCase().split('+')

    return {
        key: parts.pop(),
        mod: parts.includes('mod'),
        ctrl: parts.includes('ctrl'),
        meta: parts.includes('meta') || parts.includes('command'),
        shift: parts.includes('shift'),
        alt: parts.includes('alt') || parts.includes('option'),
    }
}

const matchesBinding = (event, binding) => {
    const mac = isMac()
    const wantsMeta = binding.meta || (binding.mod && mac)
    const wantsCtrl = binding.ctrl || (binding.mod && !mac)

    return (
        event.key?.toLowerCase() === binding.key &&
        event.metaKey === wantsMeta &&
        event.ctrlKey === wantsCtrl &&
        event.shiftKey === binding.shift &&
        event.altKey === binding.alt
    )
}

const describeBinding = (binding, forAria = false) => {
    if (!binding) {
        return null
    }

    const mac = isMac()
    const parts = []

    if (binding.mod || binding.ctrl || binding.meta) {
        parts.push(forAria ? ((binding.meta || (binding.mod && mac)) ? 'Meta' : 'Control') : ((binding.meta || (binding.mod && mac)) ? '⌘' : 'Ctrl'))
    }

    if (binding.alt) {
        parts.push(forAria ? 'Alt' : mac ? '⌥' : 'Alt')
    }

    if (binding.shift) {
        parts.push(forAria ? 'Shift' : mac ? '⇧' : 'Shift')
    }

    parts.push(binding.key.length === 1 ? binding.key.toUpperCase() : binding.key)

    return parts.join(forAria || !mac ? '+' : '')
}

const readSeen = () => {
    try {
        return JSON.parse(window.sessionStorage.getItem(SEEN_KEY) || '[]')
    } catch (error) {
        return []
    }
}

const remember = (token) => {
    try {
        window.sessionStorage.setItem(SEEN_KEY, JSON.stringify([...readSeen(), token].slice(-50)))
    } catch (error) {}
}

export default function undoToast({ config }) {
    return {
        toasts: [],
        hovering: false,
        pinned: false,
        focusWithin: false,
        tabHidden: document.visibilityState === 'hidden',
        stackHeight: 0,
        layoutOffsets: {},
        labels: config.labels,
        bindings: (config.keyBindings || []).map(parseBinding),
        timers: {},

        init() {
            this.shortcutLabel = describeBinding(this.bindings[0])
            this.ariaShortcut = describeBinding(this.bindings[0], true)

            this.onKeydown = (event) => this.handleKeydown(event)
            this.onVisibility = () => {
                this.tabHidden = document.visibilityState === 'hidden'
                this.syncTimers()
            }

            window.addEventListener('keydown', this.onKeydown)
            document.addEventListener('visibilitychange', this.onVisibility)

            this.$watch('isPaused', () => this.syncTimers())
            this.$watch('isExpanded', () => this.$nextTick(() => this.layout()))

            ;(config.pending || []).forEach((toast) => this.push(toast))
        },

        destroy() {
            window.removeEventListener('keydown', this.onKeydown)
            document.removeEventListener('visibilitychange', this.onVisibility)
            Object.values(this.timers).forEach(clearTimeout)
        },

        get isPaused() {
            return this.isExpanded || this.tabHidden
        },

        get isExpanded() {
            return this.hovering || this.focusWithin || (this.pinned && this.visibleToasts.length > 1)
        },

        get visibleToasts() {
            return this.toasts.filter((toast) => !toast.leaving)
        },

        push(detail) {
            const payload = Array.isArray(detail) ? detail[0] : detail

            if (!payload?.token || this.toasts.some((toast) => toast.token === payload.token)) {
                return
            }

            if (readSeen().includes(payload.token)) {
                return
            }

            const seconds = Math.max(1, Number(payload.seconds) || config.duration)

            this.toasts.push({
                token: payload.token,
                message: payload.message,
                operation: payload.operation,
                seconds,
                total: Math.max(seconds, config.duration),
                remaining: seconds * 1000,
                state: 'idle',
                entered: false,
                leaving: false,
            })

            const toast = this.toasts[this.toasts.length - 1]

            this.$nextTick(() => {
                this.layout()

                requestAnimationFrame(() => requestAnimationFrame(() => {
                    toast.entered = true
                    this.schedule(toast)
                }))
            })
        },

        isPending(toast) {
            return toast.state === 'idle' || toast.state === 'undoing'
        },

        isNewest(toast) {
            const pending = this.visibleToasts.filter((item) => item.state === 'idle')

            return pending[pending.length - 1] === toast
        },

        isBeyondLimit(toast) {
            const index = this.stackIndex(toast)

            return index >= config.maxVisible
        },

        stackIndex(toast) {
            const visible = this.visibleToasts

            return visible.length - 1 - visible.indexOf(toast)
        },

        countdownText(toast) {
            return this.labels.countdown.replace(':seconds', toast.seconds)
        },

        progressStyle(toast) {
            return {
                animationDuration: `${toast.total}s`,
                animationDelay: `-${toast.total - toast.seconds}s`,
            }
        },

        styleFor(toast) {
            const index = toast.leaving ? 0 : Math.max(0, this.stackIndex(toast))
            const offset = this.isExpanded ? (this.layoutOffsets[toast.token] ?? 0) : index * PEEK_PX
            const reduced = prefersReducedMotion()
            let y = -offset
            let opacity = 1

            if (!toast.entered) {
                y = reduced ? -offset : 16
                opacity = 0
            } else if (toast.leaving) {
                y = reduced ? -offset : -offset + 8
                opacity = 0
            }

            if (index >= config.maxVisible && !toast.leaving) {
                opacity = 0
            }

            return {
                transform: `translateY(${y}px)`,
                opacity,
                zIndex: 100 - index,
            }
        },

        layout() {
            const elements = Array.from(this.$refs.stack?.querySelectorAll(':scope > .ut-toast') ?? [])
            const heights = {}

            elements.forEach((element, position) => {
                const toast = this.toasts[position]

                if (toast) {
                    heights[toast.token] = element.offsetHeight
                }
            })

            const visible = this.visibleToasts.slice(-config.maxVisible).reverse()
            const offsets = {}
            let total = 0

            visible.forEach((toast) => {
                offsets[toast.token] = total
                total += (heights[toast.token] ?? 0) + GAP_PX
            })

            this.layoutOffsets = offsets

            const front = heights[visible[0]?.token] ?? 0

            this.$refs.stack?.style.setProperty('--ut-front-height', `${front}px`)

            this.stackHeight = this.isExpanded
                ? Math.max(0, total - GAP_PX)
                : front + Math.max(0, visible.length - 1) * PEEK_PX
        },

        schedule(toast) {
            clearTimeout(this.timers[toast.token])

            if (this.isPaused || toast.leaving || ['undoing', 'error'].includes(toast.state)) {
                return
            }

            toast.startedAt = performance.now()
            this.timers[toast.token] = setTimeout(() => this.dismiss(toast), toast.remaining)
        },

        syncTimers() {
            this.toasts.forEach((toast) => {
                if (this.isPaused) {
                    if (this.timers[toast.token] && toast.startedAt) {
                        clearTimeout(this.timers[toast.token])
                        delete this.timers[toast.token]
                        toast.remaining = Math.max(0, toast.remaining - (performance.now() - toast.startedAt))
                        toast.startedAt = null
                    }

                    return
                }

                if (!this.timers[toast.token] && toast.entered) {
                    this.schedule(toast)
                }
            })
        },

        dismiss(toast) {
            if (toast.leaving) {
                return
            }

            clearTimeout(this.timers[toast.token])
            delete this.timers[toast.token]

            const hadFocus = this.$refs.stack?.contains(document.activeElement)

            remember(toast.token)
            toast.leaving = true

            if (this.visibleToasts.length <= 1) {
                this.pinned = false
            }
            this.layout()

            setTimeout(() => {
                this.toasts = this.toasts.filter((item) => item !== toast)
                this.$nextTick(() => this.layout())

                if (hadFocus && !this.toasts.length) {
                    this.focusWithin = false
                }
            }, prefersReducedMotion() ? 100 : EXIT_MS)
        },

        async undo(toast) {
            if (toast.state !== 'idle' || toast.leaving) {
                return
            }

            clearTimeout(this.timers[toast.token])
            delete this.timers[toast.token]
            toast.state = 'undoing'

            let result

            try {
                result = await this.$wire.undo(toast.token)
            } catch (error) {
                result = { ok: false, message: this.labels.error ?? '' }
            }

            toast.message = result?.message || toast.message
            toast.state = result?.ok ? 'done' : 'error'
            toast.remaining = Math.min(DONE_MAX_MS, Math.max(DONE_MIN_MS, toast.message.length * 50))

            this.$nextTick(() => this.layout())

            if (toast.state === 'done') {
                toast.startedAt = performance.now()
                this.timers[toast.token] = setTimeout(() => this.dismiss(toast), toast.remaining)
            }
        },

        handleKeydown(event) {
            if (event.defaultPrevented || event.repeat || isTypingTarget(event.target) || isTypingTarget(document.activeElement)) {
                return
            }

            if (!this.bindings.some((binding) => matchesBinding(event, binding))) {
                return
            }

            const pending = this.visibleToasts.filter((toast) => toast.state === 'idle')
            const newest = pending[pending.length - 1]

            if (!newest) {
                return
            }

            event.preventDefault()
            this.undo(newest)
        },

        refreshPage() {
            const ownId = this.$wire?.$id ?? this.$wire?.id

            ;(window.Livewire?.all?.() ?? []).forEach((component) => {
                const id = component.id ?? component.$wire?.$id

                if (id && id !== ownId) {
                    component.$wire?.$refresh?.()
                }
            })
        },
    }
}
