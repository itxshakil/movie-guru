<script setup>
import {FIELD_CLASSES} from '@/Components/Ui/fieldClasses.js';

const model = defineModel({type: [String, Number, null], default: ''});
</script>

<template>
    <select
        v-model="model"
        :class="FIELD_CLASSES"
        class="pr-9"
    >
        <slot/>
    </select>
</template>
