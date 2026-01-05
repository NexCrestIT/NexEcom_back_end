# DeleteConfirmationModal Usage Examples

## Basic Usage with Composable

```vue
<script setup>
import DeleteConfirmationModal from '@/components/DeleteConfirmationModal.vue';
import { useDeleteConfirmation } from '@/composables/useDeleteConfirmation';
import { router } from '@inertiajs/vue3';

const deleteConfirmation = useDeleteConfirmation();

const handleDelete = (id, name) => {
    deleteConfirmation.show({
        title: 'Delete Item',
        message: `Are you sure you want to delete "${name}"?`,
        warningMessage: 'This action cannot be undone.',
        itemName: name,
        severity: 'danger',
        onConfirm: () => {
            router.delete(`/items/${id}`, {
                preserveScroll: true,
            });
        }
    });
};
</script>

<template>
    <div>
        <button @click="handleDelete(1, 'Item Name')">Delete</button>
        
        <DeleteConfirmationModal
            v-model:visible="deleteConfirmation.visible"
            :title="deleteConfirmation.options?.title || 'Confirm Delete'"
            :message="deleteConfirmation.options?.message || ''"
            :item-name="deleteConfirmation.options?.itemName || ''"
            :warning-message="deleteConfirmation.options?.warningMessage || ''"
            :loading="deleteConfirmation.loading"
            :severity="deleteConfirmation.options?.severity || 'danger'"
            @confirm="deleteConfirmation.handleConfirm"
            @cancel="deleteConfirmation.handleCancel"
        />
    </div>
</template>
```

## Direct Usage (Without Composable)

```vue
<script setup>
import DeleteConfirmationModal from '@/components/DeleteConfirmationModal.vue';
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

const showModal = ref(false);
const loading = ref(false);
const itemToDelete = ref(null);

const handleDelete = (id, name) => {
    itemToDelete.value = { id, name };
    showModal.value = true;
};

const confirmDelete = () => {
    loading.value = true;
    router.delete(`/items/${itemToDelete.value.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            showModal.value = false;
            loading.value = false;
        },
        onError: () => {
            loading.value = false;
        }
    });
};

const cancelDelete = () => {
    showModal.value = false;
    itemToDelete.value = null;
};
</script>

<template>
    <div>
        <button @click="handleDelete(1, 'Item Name')">Delete</button>
        
        <DeleteConfirmationModal
            v-model:visible="showModal"
            title="Delete Item"
            :message="`Are you sure you want to delete \"${itemToDelete?.name}\"?`"
            :item-name="itemToDelete?.name || ''"
            warning-message="This action cannot be undone."
            :loading="loading"
            severity="danger"
            @confirm="confirmDelete"
            @cancel="cancelDelete"
        />
    </div>
</template>
```

## Props

- `visible` (Boolean): Controls modal visibility
- `title` (String): Modal title (default: 'Confirm Delete')
- `message` (String): Main confirmation message
- `itemName` (String): Name of item being deleted (optional, can be used in message with {item} placeholder)
- `warningMessage` (String): Additional warning text
- `loading` (Boolean): Shows loading state on confirm button
- `confirmLabel` (String): Confirm button label (default: 'Delete')
- `cancelLabel` (String): Cancel button label (default: 'Cancel')
- `severity` (String): Button severity - 'danger', 'warning', or 'info' (default: 'danger')

## Events

- `update:visible`: Emitted when modal visibility changes
- `confirm`: Emitted when user clicks confirm
- `cancel`: Emitted when user clicks cancel or closes modal

