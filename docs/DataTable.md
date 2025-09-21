# DataTable Component Documentation

This is a flexible and customizable DataTable component for Vue 3 applications.

## Installation

Make sure you have dayjs installed:
```bash
npm install dayjs
```

## Basic Usage

```vue
<script setup lang="ts">
import { ref } from 'vue';
import dayjs from 'dayjs';
import DataTable from '@/components/DataTable.vue';
import Column from '@/components/Column.vue';
import { getNoTable } from '@/utils/table';

const products = ref([
  {
    id: 1,
    nama: 'Product 1',
    createdBy: 'john doe',
    createdAt: '2024-01-15T10:30:00Z'
  },
  // ... more data
]);

const pagination = ref({
  page: 1,
  rows: 10
});
</script>

<template>
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
</template>
```

## DataTable Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `value` | `any[]` | - | Array of data to display in the table |
| `tableStyle` | `string` | - | CSS style string for the table element |
| `scrollable` | `boolean` | `false` | Enable horizontal scrolling |
| `stripedRows` | `boolean` | `false` | Apply striped row styling |
| `rowHover` | `boolean` | `true` | Enable row hover effects |
| `showGridlines` | `boolean` | `true` | Show table gridlines |
| `size` | `'small' \| 'normal' \| 'large'` | `'normal'` | Table size |

## Column Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `field` | `string` | - | Field name to display from data object |
| `header` | `string` | - | Column header text |
| `sortable` | `boolean` | `false` | Enable sorting for this column |
| `class` | `string` | - | CSS classes to apply to the column |

## Column Slots

### `#body`
Custom body content for each cell in the column.

**Slot Props:**
- `data`: The row data object
- `index`: Zero-based row index

```vue
<Column header="Custom Content">
  <template #body="slotProps">
    <span class="font-bold">{{ slotProps.data.name }}</span>
    <small class="text-gray-500">Row: {{ slotProps.index }}</small>
  </template>
</Column>
```

### `#header`
Custom header content for the column.

```vue
<Column>
  <template #header>
    <div class="flex items-center gap-2">
      <Icon name="user" />
      <span>User Name</span>
    </div>
  </template>
</Column>
```

## Helper Functions

### `getNoTable(index, page, rowsPerPage)`
Calculates the display number for table rows based on pagination.

**Parameters:**
- `index` (number): Zero-based row index
- `page` (number): Current page number (1-based)
- `rowsPerPage` (number): Number of rows per page

**Returns:** The display number for the table row

```typescript
import { getNoTable } from '@/utils/table';

// For row index 0, page 2, 10 rows per page
// Returns: 11
const rowNumber = getNoTable(0, 2, 10);
```

## Features

- **Responsive Design**: Automatically adapts to different screen sizes
- **Dark Mode Support**: Fully compatible with dark/light themes
- **Flexible Column Definition**: Define columns with fields or custom content
- **Custom Styling**: Extensive customization options
- **TypeScript Support**: Full TypeScript support with proper type definitions
- **Empty State**: Built-in empty state handling
- **Accessibility**: Proper semantic markup and keyboard navigation

## Styling Options

The component uses Tailwind CSS classes and supports:
- Custom table styles via `tableStyle` prop
- Column-specific CSS classes via `class` prop
- Built-in responsive and theme variations
- Customizable row and cell styling

## Advanced Usage

### Custom Row Numbering with Pagination
```vue
<Column header="No">
  <template #body="{ index }">
    {{ getNoTable(index, currentPage, pageSize) }}
  </template>
</Column>
```

### Date Formatting
```vue
<Column field="createdAt" header="Created Date">
  <template #body="{ data }">
    {{ dayjs(data.createdAt).format('DD/MM/YYYY HH:mm') }}
  </template>
</Column>
```

### Conditional Styling
```vue
<Column field="status" header="Status">
  <template #body="{ data }">
    <span :class="{
      'text-green-600': data.status === 'active',
      'text-red-600': data.status === 'inactive'
    }">
      {{ data.status }}
    </span>
  </template>
</Column>
```

### Action Buttons
```vue
<Column header="Actions">
  <template #body="{ data }">
    <div class="flex gap-2">
      <button @click="edit(data.id)" class="text-blue-600">Edit</button>
      <button @click="delete(data.id)" class="text-red-600">Delete</button>
    </div>
  </template>
</Column>
```

## Requirements

- Vue 3
- TypeScript (optional but recommended)
- Tailwind CSS for styling
- dayjs for date formatting (when using date formatting features)
