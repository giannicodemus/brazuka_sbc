<template>
  <Teleport to="body">
    <Transition name="fade">
      <div v-if="open" class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center p-4 z-40">
        <div class="bg-white rounded-lg shadow-lg w-full dark:bg-gray-700 max-h-screen overflow-y-auto"
          :class="sizeClass">
          <ModalHeader @close="$emit('update:open', false)">
            {{ title }}
          </ModalHeader>
          <slot />
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  open: Boolean,
  title: String,
  size: { type: String, default: "medium" }
});

defineEmits(['update:open']);

const sizeClass = computed(() => {
  return {
    small: "max-w-sm",
    medium: "max-w-2xl",
    large: "max-w-7xl",
    big: "max-w-[2000]",
  }[props.size] || "max-w-2xl";
});
</script>

<style>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s;
}

.fade-enter,
.fade-leave-to {
  opacity: 0;
}
</style>
