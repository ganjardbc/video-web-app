<script setup lang="ts">
import { ref } from 'vue';
import dayjs from 'dayjs';
import DataTable from '@/components/DataTable.vue';
import Column from '@/components/Column.vue';
import { getNoTable } from '@/utils/table';

// Sample data structure
interface Product {
  id: number;
  nama: string;
  createdBy: string;
  createdAt: string;
}

// Sample products data
const products = ref<Product[]>([
  {
    id: 1,
    nama: 'Product 1',
    createdBy: 'john doe',
    createdAt: '2024-01-15T10:30:00Z'
  },
  {
    id: 2,
    nama: 'Product 2',
    createdBy: 'jane smith',
    createdAt: '2024-01-16T14:20:00Z'
  },
  {
    id: 3,
    nama: 'Product 3',
    createdBy: 'bob wilson',
    createdAt: '2024-01-17T09:15:00Z'
  }
]);

// Pagination state
const pagination = ref({
  page: 1,
  rows: 10
});
</script>

<template>
  <div class="p-6">
    <h1 class="text-2xl font-bold mb-6">DataTable Example</h1>

    <!-- This is exactly how you wanted to use the DataTable -->
    <DataTable :value="products" tableStyle="min-width: 50rem">
      <Column header="No">
        <template #body="slotProps">
          {{ getNoTable(slotProps.index, pagination.page, pagination.rows) }}
        </template>
      </Column>
      <Column field="nama" header="Nama"></Column>
      <Column field="createdBy" header="Created By" class="capitalize"></Column>
      <Column field="createdAt" header="Created At">
        <template #body="slotProps">
          {{ dayjs(slotProps.data.createdAt).format('DD/MM/YYYY') }}
        </template>
      </Column>
    </DataTable>
  </div>
</template>
