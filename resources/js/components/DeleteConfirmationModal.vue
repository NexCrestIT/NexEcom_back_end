<script setup>
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';

const props = defineProps({
    visible: {
        type: Boolean,
        default: false
    },
    title: {
        type: String,
        default: 'Confirm Delete'
    },
    message: {
        type: String,
        required: true
    },
    warningMessage: {
        type: String,
        default: ''
    },
    loading: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['update:visible', 'confirm', 'cancel']);

const handleConfirm = () => {
    emit('confirm');
};

const handleCancel = () => {
    emit('update:visible', false);
    emit('cancel');
};

const handleClose = () => {
    if (!props.loading) {
        emit('update:visible', false);
        emit('cancel');
    }
};
</script>

<template>
    <Dialog 
        :visible="visible" 
        :modal="true" 
        :closable="!loading"
        :draggable="false"
        :style="{ width: '500px' }"
        :header="title"
        @update:visible="handleClose"
    >
        <div class="flex flex-col gap-4 py-2">
            <!-- Icon and Message -->
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0">
                    <i class="pi pi-exclamation-triangle text-4xl" style="color: #ef4444;"></i>
                </div>
                <div class="flex-1">
                    <p class="text-base mb-3" style="color: #111827; line-height: 1.5;">
                        {{ message }}
                    </p>
                    <p v-if="warningMessage" class="text-sm" style="color: #6b7280; line-height: 1.5;">
                        {{ warningMessage }}
                    </p>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-end gap-2 pt-3" style="border-top: 1px solid #e5e7eb;">
                <Button 
                    label="Cancel" 
                    severity="secondary"
                    outlined
                    :disabled="loading"
                    @click="handleCancel"
                />
                <Button 
                    label="Delete" 
                    severity="danger"
                    :loading="loading"
                    :disabled="loading"
                    @click="handleConfirm"
                />
            </div>
        </div>
    </Dialog>
</template>

