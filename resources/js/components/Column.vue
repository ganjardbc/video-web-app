<script setup lang="ts">
export interface ColumnProps {
  field?: string;
  header?: string;
  sortable?: boolean;
  class?: string;
}

withDefaults(defineProps<ColumnProps>(), {
  sortable: false,
});

defineSlots<{
  default?: (props: any) => any;
  body?: (props: { data: any; index: number }) => any;
  header?: () => any;
}>();
</script>

<template>
  <!-- This component is just a configuration container for DataTable -->
  <slot />
</template>
