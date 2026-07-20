<script setup lang="ts">
import { ref } from 'vue'
const emit = defineEmits<{ file: [File] }>()
const props = withDefaults(defineProps<{ accept?: string; label?: string }>(), {
    accept: '.pdf,.doc,.docx',
    label: 'Drop your resume here or click to browse'
})
const isDragOver = ref(false)
const fileInput = ref<HTMLInputElement>()

function onDrop(e: DragEvent) {
    isDragOver.value = false
    const file = e.dataTransfer?.files[0]
    if (file) emit('file', file)
}
function onChange(e: Event) {
    const file = (e.target as HTMLInputElement).files?.[0]
    if (file) emit('file', file)
}
</script>

<template>
  <div
    :class="['border-2 border-dashed rounded-xl p-10 text-center cursor-pointer transition-all duration-200', isDragOver ? 'dropzone-active border-blue-500' : 'border-white/10 hover:border-blue-500/50']"
    @dragover.prevent="isDragOver = true"
    @dragleave="isDragOver = false"
    @drop.prevent="onDrop"
    @click="fileInput?.click()"
  >
    <svg class="w-10 h-10 mx-auto mb-3 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
    </svg>
    <p class="text-slate-300 font-medium">{{ label }}</p>
    <p class="text-slate-500 text-sm mt-1">PDF, DOC, DOCX supported</p>
    <input ref="fileInput" type="file" :accept="accept" class="hidden" @change="onChange" />
  </div>
</template>
