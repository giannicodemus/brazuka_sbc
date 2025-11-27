<template>
  <div class="flex justify-between items-center mb-4">
    <Button v-if="addbtn" icon="material-symbols:add"
      buttonClass="d-flex justify-center items-center gap-2 bg-primary-brazuka"
      @click="$emit('update:open', true)">
      Adicionar {{ routeName }}
    </Button>

    <div v-if="filtersEnable != false" :class="{ 'ml-auto': !addbtn, 'flex': true, 'gap-2': true }">
      <Input icon="mdi-magnify" placeholder="Buscar..." borderColor="border-gray-300 dark:border-gray-500"
        textColor="text-gray-500 dark:text-gray-300" classInput="py-2 w-40 dark:bg-gray-700" />
      <Button
      buttonClass="d-flex justify-center items-center gap-2 bg-primary-brazuka"
       textColor="dark:text-gray-200 text-white">Exportar</Button>
    </div>
  </div>
</template>

<script setup>
defineProps({
  routeName: String,
  open: Boolean,
  addbtn: { type: Boolean, default: true },
  filtersEnable: Boolean
});

defineEmits(['update:open']);
</script>
