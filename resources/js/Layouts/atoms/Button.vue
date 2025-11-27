<template>
  <button :type="type" :class="[
    'flex items-center justify-center gap-2 transition-all duration-200',
    paddingClass,
    rounded,
    bgColor,
    textColor,
    borderClass,
    widthClass,
    buttonClass,
    { 'opacity-50 cursor-not-allowed': disabled }
  ]" :disabled="disabled" @click="$emit('click')">
    <Icon v-if="icon" :icon="icon" :class="iconColor, iconWidth" />
    <slot></slot>
  </button>
</template>

<script setup>
import { computed } from "vue";
import { Icon } from "@iconify/vue";
import { Link, useForm, usePage } from '@inertiajs/vue3';

const props = defineProps({
  type: { type: String, default: "button" },
  color: { type: String, default: "blue" },
  textColor: { type: String, default: "text-white" },
  bgColor: { type: String, default: "bg-primary-brazuka hover:bg-primary-hover" },
  rounded: { type: String, default: " rounded-md" },
  borderColor: { type: String, default: "border-transparent" },
  icon: String,
  iconWidth: { type: String, default: "w-5 h-5" },
  iconColor: { type: String, default: "text-white" },
  buttonClass: String,
  width: { type: String, default: "auto" },
  padding: { type: String, default: "px-4 py-2" },
  disabled: { type: Boolean, default: false }
});

defineEmits(["click"]);

const widthClass = computed(() => (props.width === "auto" ? "" : `w-${props.width}`));
const borderClass = computed(() => `border ${props.borderColor}`);
const paddingClass = computed(() => props.padding);
</script>
