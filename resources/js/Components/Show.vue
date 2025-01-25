<script setup>
import {onMounted, ref} from 'vue';
import DialogModal from '@/Components/DialogModal.vue';
import DetailCard from '@/Components/DetailCard.vue';

const props = defineProps({
  imdbID: String,
  title: {
    type: String,
    required: false
  }
})

const emit = defineEmits(['close']);

const detail = ref(null);
const loading = ref(false);
const timeout = ref(null);
const maxRetry  = ref(3);

const loadDetail = async () => {
    loading.value = true;
    clearTimeout(timeout.value)
    if (!props.imdbID) {
        alert("Something went wrong. Error -S-100")
        loading.value = false;
        emit('close');
        return;
    }
    try {
        const response = await axios.get(`/i/${props.imdbID}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });
        if (response.status === 200) {
            if (response.data.detail && typeof response.data.detail === 'object' && response.data.detail.hasOwnProperty('Title')) {
                detail.value = response.data.detail;
            } else {
                alert('Unexpected data format');

                loading.value = false;
                emit('close');
            }
        } else {
            alert(`Something went wrong. Error -RE-${response.status}`)
            loading.value = false;
            emit('close');
        }
    } catch (error) {
        if (error.response) {
            if(error.response.status === 429 && maxRetry.value){
                const retryAfter = error.response.headers['retry-after'];
                timeout.value = setTimeout(()=>{
                    loadDetail()
                }, retryAfter * 1000);
                maxRetry.value = maxRetry.value - 1;
            }else{
                alert(`Something went wrong. Error -RE-${response.status}`)
                loading.value = false;
                emit('close');
            }
        } else if (error.request) {
            alert(`No response received from server`)
            loading.value = false;
            emit('close');
        } else {
            alert(error.message)
            loading.value = false;
            emit('close');
        }
    } finally {
        loading.value = false;
    }
};

onMounted(loadDetail);

const onClose = () => {
    emit('close');
};
</script>

<template>
    <DialogModal @close="onClose" max-width=" w-full md:max-w-2xl lg:max-w-4xl">
      <DetailCard :detail="detail" :title="title"/>
    </DialogModal>
</template>

