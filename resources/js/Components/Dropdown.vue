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
        return 'origin-top-left left-0';
    } else if (props.align === 'right') {
        return 'origin-top-right right-0';
    } else {
        return 'origin-top';
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
