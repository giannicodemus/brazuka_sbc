<template>
  <div :class="['flex flex-col', widthClass]">
    <label v-if="label" class="block mb-1 text-sm font-medium text-gray-700 dark:text-white font-MuseoModerno">
      {{ label }}
    </label>
    <div class="relative">
      <span v-if="icon" class="absolute left-3 top-1/2 transform -translate-y-1/2">
        <Icon :icon="icon" :class="['w-5 h-5', iconColor]" />
      </span>
      <select :value="modelValue" @change="$emit('update:modelValue', $event.target.value)" :disabled="disabled" :class="[
        'w-full p-2 focus:outline-none focus:ring-2 border dark:bg-gray-800 dark:text-white dark:placeholder-gray-400',
        bgColor,
        borderClass,
        rounded,
        textColor,
        paddingClass,
        { 'opacity-50 cursor-not-allowed': disabled }
      ]">
        <option v-if="placeholder" value="" disabled hidden>{{ placeholder }}</option>
        <option v-for="option in formattedOptions" :key="option.value" :value="option.value">
          {{ option.label }}
        </option>
      </select>
      <span v-if="rightIcon" class="absolute right-3 top-1/2 transform -translate-y-1/2">
        <Icon :icon="rightIcon" :class="['w-5 h-5', rightIconColor]" />
      </span>
    </div>
  </div>
</template>

<script setup>
import { computed } from "vue";

const props = defineProps({
  label: String,
  placeholder: String,
  modelValue: [String, Number],
  icon: String,
  options: { type: Array, required: true },
  width: { type: String, default: "full" },
  borderColor: { type: String, default: "border-primary-brazuka" },
  textColor: { type: String, default: "text-gray-700" },
  bgColor: { type: String, default: "bg-white" },
  iconColor: { type: String, default: "text-gray-400" },
  rightIcon: String,
  rightIconColor: { type: String, default: "text-gray-500" },
  rounded: { type: String, default: "rounded-md" },
  disabled: { type: Boolean, default: false }
});

const formattedOptions = computed(() => {
  return props.options.map(option => ({
    value: option.value || option.id,
    label: option.label || option.name || option.display_name
  }));
});

const widthClass = computed(() => (props.width === "full" ? "w-full" : `w-${props.width}`));
const borderClass = computed(() => `border ${props.borderColor}`);
const paddingClass = computed(() => ({ "pl-10": props.icon, "pr-10": props.rightIcon }));

defineEmits(["update:modelValue"]);
</script>