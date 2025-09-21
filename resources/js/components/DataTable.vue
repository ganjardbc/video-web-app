<script setup lang="ts">
import { computed, useSlots } from 'vue';

export interface DataTableProps {
  value: any[];
  tableStyle?: string;
  scrollable?: boolean;
  stripedRows?: boolean;
  rowHover?: boolean;
  showGridlines?: boolean;
  size?: 'small' | 'normal' | 'large';
}

const props = withDefaults(defineProps<DataTableProps>(), {
  scrollable: false,
  stripedRows: false,
  rowHover: true,
  showGridlines: true,
  size: 'normal',
});

const slots = useSlots();

// Table styling classes
const tableClasses = computed(() => {
  const baseClasses = 'w-full border-collapse';
  const sizeClasses = {
    small: 'text-sm',
    normal: 'text-base',
    large: 'text-lg',
  };

  return [
    baseClasses,
    sizeClasses[props.size],
    props.showGridlines ? 'border border-gray-200 dark:border-gray-700' : '',
  ].filter(Boolean).join(' ');
});

const rowClasses = computed(() => {
  return [
    props.stripedRows ? 'even:bg-gray-50 dark:even:bg-gray-800/50' : '',
    props.rowHover ? 'hover:bg-gray-100 dark:hover:bg-gray-700/50' : '',
    'transition-colors duration-200',
  ].filter(Boolean).join(' ');
});

// Extract column definitions from slot nodes
const columns = computed(() => {
  if (!slots.default) return [];

  const nodes = slots.default();
  const extractedColumns: any[] = [];

  const processNodes = (vnodes: any[]) => {
    vnodes.forEach(vnode => {
      if (vnode.type?.name === 'Column' || vnode.type?.__name === 'Column') {
        const props = vnode.props || {};
        const children = vnode.children || {};

        extractedColumns.push({
          field: props.field,
          header: props.header,
          class: props.class,
          sortable: props.sortable,
          bodySlot: children.body,
          headerSlot: children.header,
          defaultSlot: children.default,
        });
      } else if (Array.isArray(vnode.children)) {
        processNodes(vnode.children);
      }
    });
  };

  processNodes(nodes);
  return extractedColumns;
});
</script>

<template>
  <div class="relative overflow-hidden">
    <div :class="scrollable ? 'overflow-x-auto' : ''">
      <table :class="tableClasses" :style="tableStyle">
        <thead class="bg-gray-50 dark:bg-gray-800">
          <tr>
            <th
              v-for="(column, index) in columns"
              :key="`header-${index}`"
              :class="[
                'px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider',
                showGridlines ? 'border-b border-gray-200 dark:border-gray-700' : '',
                column.class || ''
              ]"
            >
              <template v-if="column.headerSlot">
                <component :is="column.headerSlot" />
              </template>
              <template v-else>
                {{ column.header }}
              </template>
            </th>
          </tr>
        </thead>
        <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
          <tr
            v-for="(item, rowIndex) in value"
            :key="`row-${rowIndex}`"
            :class="rowClasses"
          >
            <td
              v-for="(column, colIndex) in columns"
              :key="`cell-${rowIndex}-${colIndex}`"
              :class="[
                'px-4 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100',
                showGridlines && colIndex > 0 ? 'border-l border-gray-200 dark:border-gray-700' : '',
                column.class || ''
              ]"
            >
              <template v-if="column.bodySlot">
                <component
                  :is="column.bodySlot"
                  :data="item"
                  :index="rowIndex"
                />
              </template>
              <template v-else-if="column.field">
                {{ item[column.field] }}
              </template>
              <template v-else-if="column.defaultSlot">
                <component
                  :is="column.defaultSlot"
                  :data="item"
                  :index="rowIndex"
                />
              </template>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Empty state -->
    <div
      v-if="!value || value.length === 0"
      class="text-center py-8 text-gray-500 dark:text-gray-400"
    >
      No data available
    </div>
  </div>
</template>
