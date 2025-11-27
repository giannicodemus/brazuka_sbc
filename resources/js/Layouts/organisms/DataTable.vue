<template>
  <div class="relative overflow-x-auto">
    <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
      <thead class="text-ls text-gray-700 bg-gray-200 dark:bg-gray-900 dark:text-gray-400">
        <tr class="font-MuseoModerno">

          <TableHeader v-for="(column, index) in columns" :key="index">
            {{ column.label }}
          </TableHeader>
          <th v-if="actions" class="text-center text-gray-500 dark:text-gray-400 w-20">Ações</th>
        </tr>
      </thead>
      <tbody>
        <TableRow v-for="(item, index) in items" :key="index" :checkboxId="`checkbox-${index}`" :actions="actions">
          <template #content>
            <td class="p-4" v-for="(column, i) in columns" :key="i">
              <template v-if="column.field === 'connectionStatus'">
                <span :class="{
                  'text-green-600 bg-green-100 rounded-full px-2 py-1 text-sm': item[column.field] === 'open',
                  'text-red-600 bg-red-100 rounded-full px-2 py-1 text-sm': item[column.field] === 'close',
                  'text-yellow-600 bg-yellow-100 rounded-full px-2 py-1 text-sm': item[column.field] === 'connecting'
                }">
                  {{ getStatusLabel(item[column.field]) }}
                </span>
              </template>
              <template v-else>
                <slot name="custom-column" :column="column" :item="item">
                  {{ item[column.field] }}
                </slot>
              </template>
            </td>
          </template>
          <template v-if="actions" #actions>
            <template v-for="(action, i) in typeof customActions === 'function' ? customActions(item) : customActions"
              :key="i">
              <Button :icon="action.icon" :iconColor="action.iconColor || 'text-gray-400 dark:text-white'"
                :buttonClass="action.buttonClass || 'd-flex justify-center items-center dark:bg-primary-brazuka dark:text-white dark:border-none'"
                :borderColor="action.borderColor || 'border-gray-300'" :textColor="action.textColor || 'text-gray-500'"
                :bgColor="action.bgColor || 'bg-gray-100'" @click="() => action.onClick(item)">
                {{ action.label }}
              </Button>
            </template>
          </template>
        </TableRow>
      </tbody>
    </table>
  </div>
</template>

<script setup>
const getStatusLabel = (status) => {
  switch (status) {
    case 'open': return 'Conectado'
    case 'close': return 'Fechado'
    case 'connecting': return 'Conectando'
    default: return 'Desconhecido'
  }
}

defineProps({
  columns: Array,
  items: Array,
  showCheckbox: { type: Boolean, default: true },
  actions: { type: Boolean, default: false },
  customActions: { type: Array, default: () => [] }
})
</script>
