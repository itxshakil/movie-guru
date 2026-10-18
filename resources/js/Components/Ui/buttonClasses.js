const BASE = 'inline-flex items-center justify-center gap-2 rounded-xl font-semibold transition-colors press-spring '
    + 'focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary-500 '
    + 'disabled:pointer-events-none disabled:opacity-60';

const VARIANTS = {
    primary: 'bg-primary-600 text-white shadow-sm hover:bg-primary-500 active:bg-primary-700',
    secondary: 'bg-gray-100 text-gray-900 hover:bg-gray-200 dark:bg-gray-800 dark:text-white dark:hover:bg-gray-700',
    ghost: 'bg-transparent text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-800',
    danger: 'bg-red-600 text-white shadow-sm hover:bg-red-500 active:bg-red-700',
};

const SIZES = {
    sm: 'px-3 py-1.5 text-xs',
    md: 'px-4 py-2.5 text-sm',
    lg: 'px-6 py-3 text-base',
};

const CHIP_BASE = 'inline-flex items-center gap-1.5 rounded-full border font-medium transition-colors';
const CHIP_SIZES = {sm: 'px-2 py-0.5 text-[11px]', md: 'px-3.5 py-1.5 text-sm'};

export function buttonClasses({variant = 'primary', size = 'md', block = false} = {}) {
    return [BASE, VARIANTS[variant] ?? VARIANTS.primary, SIZES[size] ?? SIZES.md, block ? 'w-full' : ''].join(' ').trim();
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
    return [CHIP_BASE, CHIP_SIZES[size] ?? CHIP_SIZES.md, colours, hover].join(' ').trim();
}
