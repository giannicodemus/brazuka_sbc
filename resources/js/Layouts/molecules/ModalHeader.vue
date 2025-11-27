<template>
  <div class="flex items-center justify-between p-4 border-b rounded-t border-gray-200 dark:border-gray-700">
    <h3 class="text-xl font-semibold text-gray-900 dark:text-white">
      <slot />
    </h3>
    <button  iconColor="text-black"
      @click="$emit('close')" 
      class="text-black bg-transparent p-0 hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 flex justify-center items-center dark:hover:bg-gray-700 dark:hover:text-white"
    >
    <Icon  icon="material-symbols:close-rounded"/>
    </button>
  </div>
</template>

<script setup>
defineEmits(['close']);
</script>
