<script setup>
import {computed, useId} from 'vue';

const props = defineProps({
    align: {
        default: 'right',
    },
    width: {
        default: '48',
    },
    contentClasses: {
        default: () => ['py-1', 'bg-white', 'dark:bg-gray-700', 'text-gray-900', 'dark:text-white'],
    },
});

const popoverId = useId();

const widthClass = computed(() => {
    return {
        '48': 'w-48',
        '56': 'w-56',
        '64': 'w-64',
        '96': 'w-96',
    }[props.width.toString()];
});

const alignmentClasses = computed(() => {
    if (props.align === 'left') {
        return 'origin-top-left left-0 anchor-align-left';
    } else if (props.align === 'right') {
        return 'origin-top-right right-0 anchor-align-right';
    } else {
        return 'origin-top anchor-align-center';
    }
});
</script>

<template>
    <div class="relative">
        <button :popovertarget="popoverId" class="contents" type="button">
            <slot name="trigger"/>
        </button>

        <div
            :id="popoverId"
            :class="[widthClass, alignmentClasses]"
            class="absolute z-50 mt-2 rounded-md shadow-lg m-0 p-0 border-0 bg-transparent"
            popover="auto"
        >
            <div :class="contentClasses" class="rounded-md ring-1 ring-black ring-opacity-5">
                <slot name="content"/>
            </div>
        </div>
    </div>
</template>

<style scoped>
[popover] {
    opacity: 0;
    transform: scale(0.95) translateY(-4px);
    transition: opacity 0.2s ease-out,
    transform 0.2s ease-out,
    display 0.2s allow-discrete,
    overlay 0.2s allow-discrete;
}

/* CSS anchor positioning: replaces the manual mt-2/left-0/right-0 offsets with a
   declarative position tethered to the trigger button (implicit anchor via
   popovertarget), with automatic viewport-edge flipping. Browsers without
   support keep the existing absolute + Tailwind-offset positioning above. */
@supports (position-area: block-end) {
    [popover] {
        inset: auto;
        margin: 0;
        margin-block-start: 0.5rem;
        position-try-fallbacks: flip-block, flip-inline;
    }

    [popover].anchor-align-left {
        position-area: block-end span-inline-end;
    }

    [popover].anchor-align-right {
        position-area: block-end span-inline-start;
    }

    [popover].anchor-align-center {
        position-area: block-end center;
    }
}

[popover]:popover-open {
    opacity: 1;
    transform: scale(1) translateY(0);
}

@starting-style {
    [popover]:popover-open {
        opacity: 0;
        transform: scale(0.95) translateY(-4px);
    }
}
</style>
