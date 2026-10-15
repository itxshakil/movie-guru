<script setup>
import {onMounted, ref} from 'vue';
import DialogModal from '@/Components/DialogModal.vue';
import DetailCard from '@/Components/DetailCard.vue';
import {fetchDetail, takePrefetchedDetail} from '@/Composables/useDetailPrefetch.js';

const props = defineProps({
  imdbID: String,
  title: {
    type: String,
    required: false
  },
})

const emit = defineEmits(['close', 'share']);

const detail = ref(null);
const sources = ref([]);
const loading = ref(false);
const timeout = ref(null);
const affiliateLink = ref(null);
const maxRetry  = ref(3);

const loadDetail = async () => {
    loading.value = true;
    detail.value = null; // Reset detail to trigger skeleton when loading new ID
    sources.value = [];
    clearTimeout(timeout.value)
    if (!props.imdbID) {
        alert("Something went wrong. Error -S-100")
        loading.value = false;
        emit('close');
        return;
    }
    try {
        const response = await (takePrefetchedDetail(props.imdbID) ?? fetchDetail(props.imdbID));

        if (response.status === 429 && maxRetry.value) {
            const retryAfter = response.headers.get('retry-after');
            timeout.value = setTimeout(() => {
                loadDetail()
            }, retryAfter * 1000);
            maxRetry.value = maxRetry.value - 1;
            return;
        }

        if (!response.ok) {
            alert(`Something went wrong. Error -RE-${response.status}`)
            emit('close');
            return;
        }

        const data = await response.json();
        if (data.detail && typeof data.detail === 'object' && data.detail.hasOwnProperty('Title')) {
            detail.value = data.detail;
            sources.value = data.sources;
            affiliateLink.value = data.affiliateLink;
        } else {
            alert('Unexpected data format');
            emit('close');
        }
    } catch (error) {
        alert('No response received from server');
        emit('close');
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
        <DetailCard :affiliateLink="affiliateLink" :detail="detail" :sources="sources" :title="title"
                    @share="emit('share', detail)"/>
    </DialogModal>
</template>

