<template>
    <div>
        <div class="modal fade" ref="modal" tabindex="-1" aria-hidden="true" data-bs-keyboard="false"
            data-bs-focus="false" style="z-index: 1055;">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div
                        :class="['modal-header fs-5 py-2', { 'text-bg-success': messageType == 1, 'text-bg-secondary': messageType == 2, 'text-bg-danger': messageType > 2 }]">
                        <i
                            :class="['bi', { 'bi-check-circle': messageType == 1, 'bi-exclamation-circle': messageType == 2, 'bi-x-circle': messageType > 2 }]"></i>&nbsp;{{
                                messageTitle }}
                    </div>
                    <div class="modal-body text-center fs-5">
                        {{ messageText }}
                    </div>
                    <div class="modal-footer justify-content-center">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-lg"></i> NO
                        </button>
                        <button v-if="messageType == 4" class="btn btn-primary px-3" data-bs-dismiss="modal"
                            @click="deleteFunction">
                            <i class="bi bi-check-lg"></i> YES
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { Modal } from 'bootstrap';
import { onMounted, onUnmounted, ref } from 'vue';

const modal = ref(null);
const modalInstance = ref(null);

onMounted(() => {
    if (modal.value) {
        modalInstance.value = new Modal(modal.value);
        modal.value.addEventListener("hide.bs.modal", () => {
            document.activeElement?.blur();
        });
    }
});

onUnmounted(() => {
    if (modal) {
        modalInstance.value.dispose();
    }
});

const messageType = ref(1);
const messageTitle = ref(null);
const messageText = ref(null);
const deleteFunction = ref(null);

/**
 * Show greeting
 * @param type - type of modal (1: Success, 2: Info, 3: Error, 4: Confirm Delete)
 */
const showModal = (type, callback = null, title = null, message = null) => {
    messageType.value = type;
    if (type == 1) {
        messageTitle.value = "SUCCESS";
        messageText.value = "Your data has been saved successfully";
    } else if (type == 2) {
        messageTitle.value = "ALERT";
    }
    else if (type == 3) {
        messageTitle.value = "ERROR";
    }
    else if (type == 4) {
        messageTitle.value = "DELETE";
        messageText.value = "Are you sure want to delete?";
        deleteFunction.value = callback;
    }
    if (title)
        messageTitle.value = title;
    if (message)
        messageText.value = message;
    modalInstance.value?.show();
}

defineExpose({ showModal });

</script>
