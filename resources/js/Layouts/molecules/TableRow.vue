<template>
  <tr
    class=" bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600">

    <slot name="content"></slot>

    <td v-if="actions" class="p-2 flex items-center justify-center space-x-2 w-auto">
      <slot name="actions"></slot>
    </td>
  </tr>
</template>

<script setup>
defineProps({
  checkboxId: String,
  actions: Boolean
});
</script>
