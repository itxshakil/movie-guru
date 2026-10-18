const FOCUS_RING = 'focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-500';
const BASE = 'inline-flex items-center justify-center gap-2 font-semibold transition-colors press-spring '
    + FOCUS_RING + ' disabled:pointer-events-none disabled:opacity-60';

const VARIANTS = {
    primary: 'bg-primary-600 text-white shadow-sm hover:bg-primary-500 active:bg-primary-700',
    secondary: 'bg-gray-100 text-gray-900 hover:bg-gray-200 dark:bg-gray-800 dark:text-white dark:hover:bg-gray-700',
    ghost: 'bg-transparent text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-800',
    danger: 'bg-red-600 text-white shadow-sm hover:bg-red-500 active:bg-red-700',
};

// A toggle that is on swaps its variant colours for a tint of its tone, mirroring the filled icon toggles on cards.
const PRESSED_TONES = {
    primary: 'bg-primary-50 text-primary-700 ring-1 ring-inset ring-primary-200 hover:bg-primary-100 '
        + 'dark:bg-primary-950 dark:text-primary-100 dark:ring-primary-800 dark:hover:bg-primary-900',
    success: 'bg-emerald-50 text-emerald-800 ring-1 ring-inset ring-emerald-200 hover:bg-emerald-100 '
        + 'dark:bg-emerald-950 dark:text-emerald-100 dark:ring-emerald-800 dark:hover:bg-emerald-900',
};

// Each size owns its corner radius so an icon-only button can be round without a class conflict.
const SIZES = {
    sm: 'rounded-xl px-3 py-1.5 text-xs',
    md: 'rounded-xl px-4 py-2.5 text-sm',
    lg: 'rounded-xl px-6 py-3 text-base',
    icon: 'size-10 shrink-0 rounded-full',
};

const CHIP_BASE = 'inline-flex items-center gap-1.5 rounded-full border font-medium transition-colors';
const CHIP_SIZES = {sm: 'px-2 py-0.5 text-[11px]', md: 'px-3.5 py-1.5 text-sm'};

export function buttonClasses({variant = 'primary', size = 'md', block = false, pressed = false, pressedTone = 'primary'} = {}) {
    const colours = pressed
        ? PRESSED_TONES[pressedTone] ?? PRESSED_TONES.primary
        : VARIANTS[variant] ?? VARIANTS.primary;
    return [BASE, colours, SIZES[size] ?? SIZES.md, block ? 'w-full' : ''].join(' ').trim();
}

export function buttonTag({href, disabled, external = false} = {}) {
    if (!href || disabled) {
        return 'button';
    }
    return external ? 'external' : 'link';
}

export function chipClasses({active = false, size = 'md', interactive = true} = {}) {
    const colours = active
        ? 'border-primary-600 bg-primary-600 text-white'
        : 'border-gray-200 bg-white text-gray-700 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300';
    const hover = interactive && !active
        ? 'hover:border-primary-400 hover:text-primary-700 dark:hover:border-primary-500 dark:hover:text-white'
        : '';
    return [CHIP_BASE, CHIP_SIZES[size] ?? CHIP_SIZES.md, colours, hover, interactive ? FOCUS_RING : ''].join(' ').trim();
}

/**
 * A button chip that is given an active state is a toggle, so it reports that state as aria-pressed.
 * Action chips (no active binding) and static chips get no aria-pressed at all.
 */
export function chipPressed({as = 'button', active} = {}) {
    return as === 'button' && typeof active === 'boolean' ? active : undefined;
}
